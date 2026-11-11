<?php
// Φτιάχνει στατικό site στο dist/ (για Cloudflare Pages). Χρήση: php build.php
if (PHP_SAPI !== 'cli') exit;
define('STATIC_BUILD', true);
require_once __DIR__ . '/lib.php';
if (!is_demo() && (!is_file(CACHE_FILE) || time() - filemtime(CACHE_FILE) > 600)) refresh_cache();

$out = __DIR__ . '/dist';
@mkdir("$out/agonas", 0755, true);

// καταγραφή προτάσεων + αποτελεσμάτων (ιστορικό επιτυχίας)
$data = load_data();
$tips = analyse($data);
track_tips(['o15' => $tips, 'o25' => analyse($data, 'o25'), 'gg' => analyse($data, 'gg')], $data);

function render(array $get): string {
    $_GET = $get;
    ob_start();
    include __DIR__ . '/index.php';
    $html = ob_get_clean();
    foreach (['method', 'privacy', 'terms', 'results', 'over25', 'gg', 'live'] as $p) $html = str_replace('href="?p=' . $p . '"', 'href="/' . $p . '"', $html);
    return str_replace('href="./"', 'href="/"', $html);
}

$pages = ['home' => 'index.html', 'over25' => 'over25.html', 'gg' => 'gg.html', 'method' => 'method.html', 'privacy' => 'privacy.html', 'terms' => 'terms.html', 'results' => 'results.html', 'live' => 'live.html'];
$urls = [];
foreach ($pages as $p => $file) {
    file_put_contents("$out/$file", render(['p' => $p]));
    $urls[] = SITE_URL . '/' . ($file === 'index.html' ? '' : basename($file, '.html'));
}
// βαθμολογίες
@mkdir("$out/vathmologia", 0755, true);
foreach (LEAGUES as $code => $name) {
    if (empty($data['tables'][$code])) continue;
    $f = 'vathmologia/' . slugify($name) . '.html';
    file_put_contents("$out/$f", render(['p' => 'table', 'lg' => $code]));
    $urls[] = SITE_URL . '/' . substr($f, 0, -5);
}
// σελίδα για κάθε αγώνα που εμφανίζεται σε τουλάχιστον μία αγορά
$matchPages = array_filter(analyse($data, 'all', null, true), function ($t) {
    foreach (MARKETS as $k => $info) if ($t['m'][$k]['conf'] >= $info['min']) return true;
    return false;
});
foreach ($matchPages as $t) {
    $f = 'agonas/' . match_slug($t) . '.html';
    file_put_contents("$out/$f", render(['p' => 'match', 'id' => $t['id']]));
    $urls[] = SITE_URL . '/' . substr($f, 0, -5);
}
echo count($matchPages) . " σελίδες αγώνων\n";

// ---- Εικόνες για Instagram/TikTok (το PNG το βγάζει το GitHub Action με headless Chrome)
@mkdir("$out/share", 0755, true);
foreach (['story', 'post'] as $f) file_put_contents("$out/share/$f.html", shell_exec('php ' . escapeshellarg(__DIR__ . '/share.php') . ' ' . $f));
$pk = pick_day_tips($tips, 75, 7);
$dayLbl = $pk['day'] ? ['Κυριακή','Δευτέρα','Τρίτη','Τετάρτη','Πέμπτη','Παρασκευή','Σάββατο'][(int)date('w', strtotime($pk['day']))] . ' ' . date('d/m', strtotime($pk['day'])) : '';
$cap = "⚽ Προγνωστικά Over 1.5 γκολ" . ($dayLbl ? " — $dayLbl" : '') . "\n\n";
foreach ($pk['tips'] as $t) $cap .= "✅ {$t['home']['n']} – {$t['away']['n']} ({$t['conf']}%)\n";
$cap .= "\n📲 Δωρεάν κάθε πρωί στο Telegram — link στο bio\n🔎 Ανάλυση: " . preg_replace('#^https?://#', '', SITE_URL) . "\n\n21+ | Παίξε υπεύθυνα\n#στοιχημα #προγνωστικα #over15 #ποδοσφαιρο #footballtips #bettingtips";
file_put_contents("$out/share/caption.txt", $cap);
$v = date('YmdHi');
file_put_contents("$out/share/index.html", '<!DOCTYPE html><html lang="el"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Social — ' . e(SITE_NAME) . '</title>
<style>body{font-family:system-ui,sans-serif;background:#0a0e13;color:#e9eef4;margin:0;padding:18px;max-width:560px;margin:auto}h1{font-size:20px}h2{font-size:15px;color:#8796a8;margin:22px 0 8px}img{width:100%;border-radius:12px;border:1px solid #283545}a.b,button{display:block;width:100%;text-align:center;background:#2bd576;color:#04140b;font-weight:800;padding:12px;border-radius:10px;margin-top:8px;text-decoration:none;border:0;font-size:15px}pre{white-space:pre-wrap;background:#121922;border:1px solid #283545;border-radius:10px;padding:12px;font-size:14px}p{color:#8796a8;font-size:13px}</style></head><body>
<h1>📲 Εικόνες για social — ' . e($dayLbl ?: 'επόμενη αγωνιστική') . '</h1><p>Στο κινητό: πάτα παρατεταμένα την εικόνα → Αποθήκευση. Ανανεώνεται αυτόματα κάθε 3 ώρες.</p>
<h2>Story (Instagram / TikTok / Facebook)</h2><img src="story.png?v=' . $v . '" alt=""><a class="b" href="story.png?v=' . $v . '" download="over15-story.png">Κατέβασμα story</a>
<h2>Post (Instagram feed)</h2><img src="post.png?v=' . $v . '" alt=""><a class="b" href="post.png?v=' . $v . '" download="over15-post.png">Κατέβασμα post</a>
<h2>Κείμενο για τη λεζάντα</h2><pre id="c">' . e($cap) . '</pre><button onclick="navigator.clipboard.writeText(document.getElementById(\'c\').innerText);this.textContent=\'Αντιγράφηκε ✓\'">Αντιγραφή κειμένου</button>
</body></html>');

copy(__DIR__ . '/ads.txt', "$out/ads.txt");
// Cloudflare Pages Functions (π.χ. /api/live)
@mkdir("$out/functions/api", 0755, true);
foreach (glob(__DIR__ . '/functions/api/*.js') as $f) copy($f, "$out/functions/api/" . basename($f));
@mkdir("$out/assets", 0755, true);
copy(__DIR__ . '/_headers', "$out/_headers");
foreach (glob(__DIR__ . '/assets/*') as $a) copy($a, "$out/assets/" . basename($a));
file_put_contents("$out/robots.txt", "User-agent: *\nAllow: /\nSitemap: " . SITE_URL . "/sitemap.xml\n");
$x = '';
foreach ($urls as $u) $x .= '<url><loc>' . htmlspecialchars($u) . '</loc><lastmod>' . date('Y-m-d') . '</lastmod></url>';
file_put_contents("$out/sitemap.xml", '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $x . '</urlset>');
file_put_contents("$out/404.html", render(['p' => 'home']));
echo "OK\n";
