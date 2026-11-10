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
track_tips($tips, $data);

function render(array $get): string {
    $_GET = $get;
    ob_start();
    include __DIR__ . '/index.php';
    $html = ob_get_clean();
    foreach (['method', 'privacy', 'terms', 'results'] as $p) $html = str_replace('href="?p=' . $p . '"', 'href="/' . $p . '.html"', $html);
    return str_replace('href="./"', 'href="/"', $html);
}

$pages = ['home' => 'index.html', 'method' => 'method.html', 'privacy' => 'privacy.html', 'terms' => 'terms.html', 'results' => 'results.html'];
$urls = [];
foreach ($pages as $p => $file) {
    file_put_contents("$out/$file", render(['p' => $p]));
    $urls[] = SITE_URL . '/' . ($file === 'index.html' ? '' : $file);
}
foreach ($tips as $t) {
    $f = 'agonas/' . match_slug($t) . '.html';
    file_put_contents("$out/$f", render(['p' => 'match', 'id' => $t['id']]));
    $urls[] = SITE_URL . '/' . $f;
}
echo count($tips) . " σελίδες αγώνων\n";

copy(__DIR__ . '/ads.txt', "$out/ads.txt");
file_put_contents("$out/robots.txt", "User-agent: *\nAllow: /\nSitemap: " . SITE_URL . "/sitemap.xml\n");
$x = '';
foreach ($urls as $u) $x .= '<url><loc>' . htmlspecialchars($u) . '</loc><lastmod>' . date('Y-m-d') . '</lastmod></url>';
file_put_contents("$out/sitemap.xml", '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $x . '</urlset>');
file_put_contents("$out/404.html", render(['p' => 'home']));
echo "OK\n";
