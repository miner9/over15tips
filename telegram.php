<?php
/*
 * Telegram bot για το κανάλι.
 *   php telegram.php tips      → στέλνει τα tips της ημέρας
 *   php telegram.php results   → στέλνει τα αποτελέσματα των χθεσινών tips
 * Χωρίς TG_BOT_TOKEN τυπώνει το μήνυμα στην οθόνη (δοκιμή) αντί να το στείλει.
 *
 * Cron (ώρα Ελλάδας):
 *   0 9  * * *  php /path/telegram.php results
 *   30 10 * * * php /path/telegram.php tips
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__ . '/lib.php';

const POSTED_DIR = __DIR__ . '/data/posted';

function tg_send(string $html): bool {
    if (!TG_BOT_TOKEN || !TG_CHAT_ID) { echo "---- DRY RUN (χωρίς token) ----\n$html\n"; return true; }
    $ch = curl_init('https://api.telegram.org/bot' . TG_BOT_TOKEN . '/sendMessage');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'chat_id' => TG_CHAT_ID, 'text' => $html, 'parse_mode' => 'HTML',
            'disable_web_page_preview' => 'true',
        ],
    ]);
    $res = json_decode((string)curl_exec($ch), true);
    curl_close($ch);
    if (empty($res['ok'])) { fwrite(STDERR, 'Telegram error: ' . json_encode($res) . "\n"); return false; }
    return true;
}

function footer(): string {
    $f = "\n";
    if (AFF_URL) $f .= "🎯 <a href=\"" . e(AFF_URL) . "\">" . e(AFF_OFFER) . "</a>\n";
    $f .= "📊 Όλη η ανάλυση: " . e(SITE_URL) . "\n";
    $f .= "<i>21+ | Παίξε υπεύθυνα | Γραμμή βοήθειας 1114</i>";
    return $f;
}

function cmd_tips(bool $next = false): void {
    $all = array_values(array_filter(analyse(load_data()), fn($t) => $t['conf'] >= TG_MIN_CONF));
    if ($next && $all) { // επόμενη μέρα με αγώνες (π.χ. σε διακοπή εθνικών)
        $today = min(array_map(fn($t) => date('Y-m-d', $t['ts']), $all));
    } else {
        $today = date('Y-m-d');
    }
    $tips = array_values(array_filter($all, fn($t) => date('Y-m-d', $t['ts']) === $today));
    usort($tips, fn($a, $b) => $b['conf'] <=> $a['conf']);
    $tips = array_slice($tips, 0, TG_MAX_TIPS);
    if (!$tips) { echo "Καμία επιλογή για σήμερα.\n"; return; }
    usort($tips, fn($a, $b) => $a['ts'] <=> $b['ts']);

    $ts0 = strtotime($today);
    $lbl = gr_day($ts0);
    if (in_array($lbl, ['Σήμερα', 'Αύριο'], true)) $lbl .= ' ' . date('d/m', $ts0);
    $msg = "⚽ <b>OVER 1.5 — " . e($lbl) . "</b>\n";
    $msg .= "<i>Με βάση τα τελευταία 3 παιχνίδια κάθε ομάδας</i>\n\n";
    foreach ($tips as $t) {
        $icon = $t['conf'] >= 85 ? '🔥' : '✅';
        $msg .= "$icon <b>" . e($t['home']['n']) . ' – ' . e($t['away']['n']) . "</b>\n";
        $msg .= '🕒 ' . date('H:i', $t['ts']) . ' · ' . e($t['leagueName']) . "\n";
        $msg .= "📈 {$t['conf']}% · Over σε {$t['hits']}/6 · Δίκαιη απόδ. " . number_format($t['fair'], 2) . "\n\n";
    }
    $msg .= footer();

    if (tg_send($msg)) {
        @mkdir(POSTED_DIR, 0755, true);
        file_put_contents(POSTED_DIR . "/$today.json", json_encode(array_map(fn($t) => [
            'id' => $t['id'], 'league' => $t['league'], 'home' => $t['home']['n'], 'away' => $t['away']['n'], 'conf' => $t['conf'],
        ], $tips)));
        echo "Στάλθηκαν " . count($tips) . " tips.\n";
    }
}

function cmd_results(): void {
    $day = date('Y-m-d', strtotime('-1 day'));
    $file = POSTED_DIR . "/$day.json";
    if (!is_file($file)) { echo "Δεν υπάρχουν tips για $day.\n"; return; }
    $posted = json_decode(file_get_contents($file), true);
    if (!is_demo()) refresh_cache();
    $data = load_data();

    $byId = [];
    foreach ($data['leagues'] as $ms) foreach ($ms as $m) $byId[$m['id']] = $m;

    $won = 0; $settled = 0; $lines = '';
    foreach ($posted as $p) {
        $m = $byId[$p['id']] ?? null;
        if (!$m || $m['status'] !== 'FINISHED') { $lines .= "⏳ " . e($p['home']) . ' – ' . e($p['away']) . " (εκκρεμεί)\n"; continue; }
        $settled++;
        $ok = $m['hg'] + $m['ag'] >= 2;
        if ($ok) $won++;
        $lines .= ($ok ? '✅ ' : '❌ ') . e($p['home']) . " {$m['hg']}-{$m['ag']} " . e($p['away']) . "\n";
    }
    if (!$settled) { echo "Κανένας αγώνας δεν έχει τελειώσει ακόμα.\n"; return; }

    // συνολικό ιστορικό (όλες οι μέρες που έχουν αποτελέσματα)
    $histFile = POSTED_DIR . '/history.json';
    $hist = is_file($histFile) ? json_decode(file_get_contents($histFile), true) : [];
    $hist[$day] = ['won' => $won, 'n' => $settled];
    file_put_contents($histFile, json_encode($hist));
    $tw = array_sum(array_column($hist, 'won')); $tn = array_sum(array_column($hist, 'n'));

    $msg = "📋 <b>Αποτελέσματα χθες (" . date('d/m', strtotime($day)) . ")</b>\n\n$lines\n";
    $msg .= "<b>Χθες: $won/$settled</b>" . ($won === $settled ? ' 🔥' : '') . "\n";
    $msg .= "Συνολικά: $tw/$tn (" . round(100 * $tw / $tn) . "%)\n";
    $msg .= footer();
    tg_send($msg);
}

match ($argv[1] ?? '') {
    'tips'    => cmd_tips(),
    'next'    => cmd_tips(true),
    'results' => cmd_results(),
    default   => print("Χρήση: php telegram.php tips|next|results\n"),
};
