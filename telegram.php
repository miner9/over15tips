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

/** Στέλνει εικόνα (αρχείο) με λεζάντα. Αν η λεζάντα ξεπερνά το όριο του Telegram, στέλνει εικόνα + ξεχωριστό κείμενο. */
function tg_photo(string $file, string $html): bool {
    $plainLen = mb_strlen(strip_tags($html));
    if (!TG_BOT_TOKEN || !TG_CHAT_ID) { echo "---- DRY RUN photo: $file ($plainLen chars) ----\n"; return tg_send($html); }
    $fits = $plainLen <= 1000;
    $ch = curl_init('https://api.telegram.org/bot' . TG_BOT_TOKEN . '/sendPhoto');
    $fields = ['chat_id' => TG_CHAT_ID, 'photo' => new CURLFile($file, 'image/png', 'over15.png')];
    if ($fits) { $fields['caption'] = $html; $fields['parse_mode'] = 'HTML'; }
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $fields]);
    $res = json_decode((string)curl_exec($ch), true);
    curl_close($ch);
    if (empty($res['ok'])) { fwrite(STDERR, 'Telegram photo error: ' . json_encode($res) . "\n"); return tg_send($html); }
    return $fits ? true : tg_send($html);
}

function footer(): string {
    $f = "\n";
    if (AFF_URL) $f .= "🎯 <a href=\"" . e(AFF_URL) . "\">" . e(AFF_OFFER) . "</a>\n";
    $f .= "📊 Όλη η ανάλυση: " . e(SITE_URL) . "\n";
    $st = track_stats(load_tracked(), 30, 70);
    if ($st['n'] >= 10) $f .= "📈 Επιτυχία 30 ημερών: " . pct($st['w'], $st['n']) . " (" . $st['n'] . " tips) · " . e(SITE_URL) . "/results\n";
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
        $msg .= "$icon <b>" . e($t['home']['g']) . ' – ' . e($t['away']['g']) . "</b>\n";
        $msg .= '🕒 ' . date('H:i', $t['ts']) . ' · ' . e($t['leagueName']) . "\n";
        $msg .= "📈 {$t['conf']}% · Over σε {$t['hits']}/6 · Δίκαιη απόδ. " . number_format($t['fair'], 2) . "\n\n";
    }
    $msg .= footer();

    $img = __DIR__ . '/dist/share/post.png';
    $sent = is_file($img) ? tg_photo($img, $msg) : tg_send($msg);
    if ($sent) {
        @mkdir(POSTED_DIR, 0755, true);
        file_put_contents(POSTED_DIR . "/$today.json", json_encode(array_map(fn($t) => [
            'id' => $t['id'], 'league' => $t['league'], 'home' => $t['home']['g'], 'away' => $t['away']['g'], 'conf' => $t['conf'],
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

/** Live ειδοποίηση: όταν ένα σημερινό tip του καναλιού «περάσει» (2+ γκολ), στέλνει ✅ μία φορά. */
function cmd_live(): void {
    $day = date('Y-m-d');
    $file = POSTED_DIR . "/$day.json";
    if (!is_file($file)) { echo "Δεν στάλθηκαν tips σήμερα.\n"; return; }
    $posted = json_decode(file_get_contents($file), true) ?: [];
    $alFile = POSTED_DIR . "/alerts-$day.json";
    $al = is_file($alFile) ? (json_decode(file_get_contents($alFile), true) ?: []) : [];
    $todo = array_filter($posted, fn($p) => empty($al[$p['id']]));
    if (!$todo) { echo "Όλα τα tips έχουν ήδη περάσει.\n"; return; }
    $res = api_get('/matches?ids=' . implode(',', array_column($todo, 'id')));
    if (!$res) { echo "Σφάλμα API.\n"; return; }
    $byId = array_column($todo, null, 'id');
    $sent = 0;
    foreach ($res['matches'] ?? [] as $m) {
        $p = $byId[$m['id']] ?? null;
        $hg = $m['score']['fullTime']['home'] ?? null; $ag = $m['score']['fullTime']['away'] ?? null;
        if (!$p || $hg === null || !in_array($m['status'], ['IN_PLAY', 'PAUSED', 'FINISHED'], true) || $hg + $ag < 2) continue;
        $min = !empty($m['minute']) ? " ({$m['minute']}')" : ($m['status'] === 'PAUSED' ? ' (ημίχρονο)' : '');
        $msg = "✅ <b>ΠΕΡΑΣΕ! Over 1.5</b>\n⚽ " . e($p['home']) . " <b>$hg-$ag</b> " . e($p['away']) . "$min\n📈 Το είχαμε στο {$p['conf']}%\n\n🔴 Live σκορ: " . e(SITE_URL) . "/live";
        if (tg_send($msg)) { $al[$m['id']] = time(); $sent++; }
    }
    @mkdir(POSTED_DIR, 0755, true);
    file_put_contents($alFile, json_encode($al));
    echo "Ειδοποιήσεις: $sent\n";
}

match ($argv[1] ?? '') {
    'live'    => cmd_live(),
    'tips'    => cmd_tips(),
    'next'    => cmd_tips(true),
    'results' => cmd_results(),
    default   => print("Χρήση: php telegram.php tips|next|results|live\n"),
};
