<?php
// Φτιάχνει στατικό site στο dist/ (για Cloudflare Pages). Χρήση: php build.php
if (PHP_SAPI !== 'cli') exit;
require_once __DIR__ . '/lib.php';
if (!is_demo() && (!is_file(CACHE_FILE) || time() - filemtime(CACHE_FILE) > 600)) refresh_cache();
$out = __DIR__ . '/dist';
@mkdir($out, 0755, true);
$pages = ['home' => 'index.html', 'method' => 'method.html', 'privacy' => 'privacy.html', 'terms' => 'terms.html'];
foreach ($pages as $p => $file) {
    $_GET['p'] = $p;
    ob_start();
    include __DIR__ . '/index.php';
    $html = ob_get_clean();
    foreach ($pages as $p2 => $f2) {
        $html = str_replace(['href="?p=' . $p2 . '"', '/?p=' . $p2 . '"'], ['href="' . $f2 . '"', '/' . $f2 . '"'], $html);
    }
    file_put_contents("$out/$file", $html);
    echo "dist/$file\n";
}
copy(__DIR__ . '/ads.txt', "$out/ads.txt");
copy(__DIR__ . '/robots.txt', "$out/robots.txt");
$urls = '';
foreach ($pages as $f) $urls .= '<url><loc>' . SITE_URL . '/' . ($f === 'index.html' ? '' : $f) . '</loc></url>';
file_put_contents("$out/sitemap.xml", '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $urls . '</urlset>');
echo "OK\n";
