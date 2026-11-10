<?php
require_once __DIR__ . '/config.php';
date_default_timezone_set(TIMEZONE);

const CACHE_FILE = __DIR__ . '/data/cache.json';
const LOCK_FILE  = __DIR__ . '/data/refresh.lock';

function is_demo(): bool { return API_KEY === ''; }

/* ---------- Λήψη δεδομένων ---------- */

function api_get(string $path): ?array {
    $ch = curl_init('https://api.football-data.org/v4' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['X-Auth-Token: ' . API_KEY],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$body) { error_log("football-data $path -> HTTP $code"); return null; }
    return json_decode($body, true);
}

/** Μία κλήση ανά πρωτάθλημα: φέρνει ΟΛΟΥΣ τους αγώνες της σεζόν (παιγμένους + επόμενους). */
function refresh_cache(): bool {
    @mkdir(dirname(CACHE_FILE), 0755, true);
    $lock = fopen(LOCK_FILE, 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) return false; // κάποιος άλλος ανανεώνει ήδη
    $leagues = [];
    foreach (LEAGUES as $code => $name) {
        $data = api_get("/competitions/$code/matches");
        if (!$data || empty($data['matches'])) continue;
        $leagues[$code] = array_map(fn($m) => [
            'id' => $m['id'], 'utc' => $m['utcDate'], 'status' => $m['status'],
            'h' => ['id' => $m['homeTeam']['id'], 'n' => $m['homeTeam']['shortName'] ?? $m['homeTeam']['name'], 'c' => $m['homeTeam']['crest'] ?? ''],
            'a' => ['id' => $m['awayTeam']['id'], 'n' => $m['awayTeam']['shortName'] ?? $m['awayTeam']['name'], 'c' => $m['awayTeam']['crest'] ?? ''],
            'hg' => $m['score']['fullTime']['home'], 'ag' => $m['score']['fullTime']['away'],
        ], $data['matches']);
    }
    if ($leagues) {
        file_put_contents(CACHE_FILE . '.tmp', json_encode(['fetched' => time(), 'leagues' => $leagues]));
        rename(CACHE_FILE . '.tmp', CACHE_FILE);
    }
    flock($lock, LOCK_UN);
    return (bool)$leagues;
}

function load_data(): array {
    if (is_demo()) return demo_data();
    $cache = is_file(CACHE_FILE) ? json_decode(file_get_contents(CACHE_FILE), true) : null;
    if (!$cache || time() - $cache['fetched'] > CACHE_TTL) {
        if (refresh_cache()) $cache = json_decode(file_get_contents(CACHE_FILE), true);
    }
    return $cache ?: ['fetched' => 0, 'leagues' => []];
}

/* ---------- Ανάλυση ---------- */

function team_last3(array $matches, int $teamId, string $beforeUtc): array {
    $played = array_filter($matches, fn($m) =>
        $m['status'] === 'FINISHED' && $m['utc'] < $beforeUtc &&
        ($m['h']['id'] === $teamId || $m['a']['id'] === $teamId));
    usort($played, fn($x, $y) => strcmp($y['utc'], $x['utc']));
    $out = [];
    foreach (array_slice($played, 0, 3) as $m) {
        $home = $m['h']['id'] === $teamId;
        $out[] = [
            'opp' => $home ? $m['a']['n'] : $m['h']['n'],
            'venue' => $home ? 'H' : 'A',
            'gf' => $home ? $m['hg'] : $m['ag'],
            'ga' => $home ? $m['ag'] : $m['hg'],
        ];
    }
    return $out;
}

function team_stats(array $games): array {
    $n = count($games);
    $hits = count(array_filter($games, fn($g) => $g['gf'] + $g['ga'] >= 2));
    $gf = array_sum(array_column($games, 'gf'));
    $ga = array_sum(array_column($games, 'ga'));
    return ['games' => $games, 'hits' => $hits, 'avgFor' => $gf / $n, 'avgAg' => $ga / $n, 'avgTot' => ($gf + $ga) / $n];
}

/**
 * Βαθμολογία:
 *  50% = πόσα από τα 6 παιχνίδια (3+3) βγήκαν Over 1.5
 *  50% = Poisson πιθανότητα για 2+ γκολ με αναμενόμενα γκολ από επίθεση/άμυνα των δύο ομάδων
 */
function analyse(array $data): array {
    $now = gmdate('Y-m-d\TH:i:s\Z');
    $until = gmdate('Y-m-d\TH:i:s\Z', time() + DAYS_AHEAD * 86400);
    $tips = [];
    foreach ($data['leagues'] as $code => $matches) {
        foreach ($matches as $m) {
            if (!in_array($m['status'], ['SCHEDULED', 'TIMED'], true)) continue;
            if ($m['utc'] < $now || $m['utc'] > $until) continue;
            $h3 = team_last3($matches, $m['h']['id'], $m['utc']);
            $a3 = team_last3($matches, $m['a']['id'], $m['utc']);
            if (count($h3) < 3 || count($a3) < 3) continue;
            $H = team_stats($h3); $A = team_stats($a3);
            $lambda = ($H['avgFor'] + $A['avgAg']) / 2 + ($A['avgFor'] + $H['avgAg']) / 2;
            $pPois = 1 - exp(-$lambda) * (1 + $lambda);
            $hitRate = ($H['hits'] + $A['hits']) / 6;
            $conf = (int)round(100 * (0.5 * $hitRate + 0.5 * $pPois));
            if ($conf < MIN_SHOW) continue;
            $tips[] = [
                'id' => $m['id'], 'league' => $code, 'leagueName' => LEAGUES[$code] ?? $code,
                'ts' => strtotime($m['utc']), 'home' => $m['h'], 'away' => $m['a'],
                'H' => $H, 'A' => $A, 'xg' => $lambda, 'hits' => $H['hits'] + $A['hits'],
                'conf' => $conf, 'fair' => 100 / $conf,
            ];
        }
    }
    usort($tips, fn($x, $y) => $y['conf'] <=> $x['conf'] ?: $x['ts'] <=> $y['ts']);
    return $tips;
}

function tier(int $c): array {
    if ($c >= 85) return ['top', 'Κορυφαία επιλογή'];
    if ($c >= 75) return ['strong', 'Ισχυρή'];
    return ['good', 'Καλή'];
}

/* ---------- Βοηθητικά ---------- */

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function gr_day(int $ts): string {
    $days = ['Κυρ', 'Δευ', 'Τρί', 'Τετ', 'Πέμ', 'Παρ', 'Σάβ'];
    $today = date('Y-m-d'); $d = date('Y-m-d', $ts);
    if ($d === $today) return 'Σήμερα';
    if ($d === date('Y-m-d', strtotime('+1 day'))) return 'Αύριο';
    return $days[(int)date('w', $ts)] . ' ' . date('d/m', $ts);
}

function initials(string $n): string {
    $w = preg_split('/\s+/u', trim($n));
    return mb_strtoupper(mb_substr($w[0], 0, 1) . (isset($w[1]) ? mb_substr($w[1], 0, 1) : mb_substr($w[0], 1, 1)));
}

/* ---------- DEMO (όταν δεν υπάρχει API key) ---------- */

function demo_data(): array {
    mt_srand(42);
    $teams = [
        'PL'  => ['Arsenal', 'Liverpool', 'Man City', 'Chelsea', 'Newcastle', 'Spurs', 'Aston Villa', 'Brighton'],
        'SA'  => ['Inter', 'Milan', 'Napoli', 'Juventus', 'Atalanta', 'Roma', 'Lazio', 'Bologna'],
        'BL1' => ['Bayern', 'Dortmund', 'Leverkusen', 'Leipzig', 'Stuttgart', 'Frankfurt', 'Freiburg', 'Wolfsburg'],
        'PD'  => ['Real Madrid', 'Barcelona', 'Atlético', 'Athletic', 'Villarreal', 'Betis', 'Sociedad', 'Sevilla'],
    ];
    $leagues = []; $id = 1;
    foreach ($teams as $code => $names) {
        $ms = []; $ids = [];
        foreach ($names as $i => $n) $ids[$n] = crc32($n) % 100000;
        $attack = []; foreach ($names as $n) $attack[$n] = 0.6 + mt_rand(0, 140) / 100;
        for ($r = 4; $r >= 1; $r--) {               // 4 αγωνιστικές πίσω
            $sh = $names; shuffle($sh);
            for ($k = 0; $k < 8; $k += 2) {
                [$h, $a] = [$sh[$k], $sh[$k + 1]];
                $ms[] = ['id' => $id++, 'utc' => gmdate('Y-m-d\TH:i:s\Z', time() - $r * 7 * 86400), 'status' => 'FINISHED',
                    'h' => ['id' => $ids[$h], 'n' => $h, 'c' => ''], 'a' => ['id' => $ids[$a], 'n' => $a, 'c' => ''],
                    'hg' => poisson_rand($attack[$h]), 'ag' => poisson_rand($attack[$a] * 0.85)];
            }
        }
        $sh = $names; shuffle($sh);
        for ($k = 0; $k < 8; $k += 2) {
            $t = strtotime('today') + (mt_rand(0, 4) * 86400) + [64800, 70200, 77400, 79200][mt_rand(0, 3)];
            $ms[] = ['id' => $id++, 'utc' => gmdate('Y-m-d\TH:i:s\Z', $t), 'status' => 'TIMED',
                'h' => ['id' => $ids[$sh[$k]], 'n' => $sh[$k], 'c' => ''], 'a' => ['id' => $ids[$sh[$k + 1]], 'n' => $sh[$k + 1], 'c' => ''],
                'hg' => null, 'ag' => null];
        }
        $leagues[$code] = $ms;
    }
    return ['fetched' => time(), 'leagues' => $leagues];
}

function poisson_rand(float $l): int {
    $L = exp(-$l); $k = 0; $p = 1.0;
    do { $k++; $p *= mt_rand() / mt_getrandmax(); } while ($p > $L);
    return $k - 1;
}

/* ---------- Render helpers ---------- */

function ad_slot(string $slot, string $cls = ''): string {
    if (ADSENSE_CLIENT && $slot) {
        return '<div class="ad ' . $cls . '"><span class="ad-label">Διαφήμιση</span><ins class="adsbygoogle" style="display:block" data-ad-client="' . e(ADSENSE_CLIENT) . '" data-ad-slot="' . e($slot) . '" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle=window.adsbygoogle||[]).push({});</script></div>';
    }
    return '<div class="ad ad-ph ' . $cls . '"><span class="ad-label">Διαφήμιση</span><div>Θέση AdSense</div></div>';
}

function aff_btn(string $label, string $cls = 'btn-aff'): string {
    if (!AFF_URL) return '';
    return '<a class="' . $cls . '" href="' . e(AFF_URL) . '" target="_blank" rel="sponsored noopener">' . e($label) . ' <span aria-hidden="true">→</span></a>';
}

function crest(array $t): string {
    return $t['c']
        ? '<img class="crest" src="' . e($t['c']) . '" alt="" loading="lazy" width="36" height="36">'
        : '<span class="crest crest-i">' . e(initials($t['n'])) . '</span>';
}

function form_rows(array $S): string {
    $o = '';
    foreach ($S['games'] as $g) {
        $over = $g['gf'] + $g['ga'] >= 2;
        $o .= '<li class="' . ($over ? 'ov' : 'un') . '"><span class="sc">' . $g['gf'] . '-' . $g['ga'] . '</span><span class="op">' . ($g['venue'] === 'H' ? 'vs ' : '@ ') . e($g['opp']) . '</span><span class="tick">' . ($over ? '✓' : '✕') . '</span></li>';
    }
    return $o;
}
