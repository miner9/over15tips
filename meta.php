<?php
/*
 * Αυτόματα posts σε Instagram (post + story) και Facebook Page.
 *   php meta.php        → δημοσιεύει τα tips της ημέρας (αν υπάρχουν)
 *   php meta.php check  → ελέγχει token / σύνδεση, χωρίς δημοσίευση
 * Τρέχει ΜΕΤΑ το deploy, γιατί το Instagram κατεβάζει την εικόνα από δημόσιο URL.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__ . '/lib.php';

function meta(string $method, string $path, array $params): array {
    $ch = curl_init(META_API . $path . ($method === 'GET' ? '?' . http_build_query($params) : ''));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60]);
    if ($method === 'POST') { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $params); }
    $res = json_decode((string)curl_exec($ch), true) ?: [];
    curl_close($ch);
    if (isset($res['error'])) fwrite(STDERR, "Meta $path: " . ($res['error']['message'] ?? 'error') . "\n");
    return $res;
}

if (!META_TOKEN) { echo "META_TOKEN λείπει — παράλειψη.\n"; exit(0); }

// Page token (δεν λήγει όταν προέρχεται από long-lived user token) + Instagram business id
$page = meta('GET', '/' . META_PAGE_ID, ['fields' => 'name,access_token,instagram_business_account{id,username}', 'access_token' => META_TOKEN]);
$pageToken = $page['access_token'] ?? '';
$igId = $page['instagram_business_account']['id'] ?? '';
echo "Page: " . ($page['name'] ?? '?') . " | Instagram: @" . ($page['instagram_business_account']['username'] ?? '—') . "\n";
if (!$pageToken) exit(1);
if (($argv[1] ?? '') === 'check') {
    $dbg = meta('GET', '/debug_token', ['input_token' => META_TOKEN, 'access_token' => META_TOKEN]);
    $exp = $dbg['data']['expires_at'] ?? 0;
    echo 'Token λήγει: ' . ($exp ? date('d/m/Y', $exp) : 'ποτέ / άγνωστο') . "\n";
    echo 'Δικαιώματα: ' . implode(', ', $dbg['data']['scopes'] ?? []) . "\n";
    exit(0);
}

// Μόνο αν υπάρχουν tips για σήμερα
$pk = pick_day_tips(analyse(load_data()), 75, 7);
if ($pk['day'] !== date('Y-m-d')) { echo "Δεν υπάρχουν tips για σήμερα — καμία δημοσίευση.\n"; exit(0); }

$caption = is_file(__DIR__ . '/dist/share/caption.txt') ? file_get_contents(__DIR__ . '/dist/share/caption.txt') : 'Προγνωστικά Over 1.5 — ' . SITE_URL;
$v = time();
$postUrl = SITE_URL . "/share/post.png?v=$v";
$storyUrl = SITE_URL . "/share/story.png?v=$v";

// Περιμένουμε να ανέβει η νέα εικόνα στο site (σύγκριση με το τοπικό αρχείο)
$local = md5_file(__DIR__ . '/dist/share/post.png');
for ($i = 0; $i < 30; $i++) {
    $remote = @file_get_contents($postUrl . "&w=$i");
    if ($remote !== false && md5($remote) === $local) { echo "Εικόνα live.\n"; break; }
    sleep(10);
}

function ig_publish(string $igId, string $token, array $params): bool {
    $c = meta('POST', "/$igId/media", $params + ['access_token' => $token]);
    if (empty($c['id'])) return false;
    for ($i = 0; $i < 20; $i++) { // περιμένουμε να επεξεργαστεί το Instagram την εικόνα
        $s = meta('GET', '/' . $c['id'], ['fields' => 'status_code', 'access_token' => $token]);
        if (($s['status_code'] ?? '') === 'FINISHED') break;
        if (($s['status_code'] ?? '') === 'ERROR') return false;
        sleep(3);
    }
    $p = meta('POST', "/$igId/media_publish", ['creation_id' => $c['id'], 'access_token' => $token]);
    return !empty($p['id']);
}

if ($igId) {
    echo 'Instagram post: '  . (ig_publish($igId, $pageToken, ['image_url' => $postUrl, 'caption' => $caption]) ? 'OK' : 'ΑΠΟΤΥΧΙΑ') . "\n";
    echo 'Instagram story: ' . (ig_publish($igId, $pageToken, ['image_url' => $storyUrl, 'media_type' => 'STORIES']) ? 'OK' : 'ΑΠΟΤΥΧΙΑ') . "\n";
}
$fb = meta('POST', '/' . META_PAGE_ID . '/photos', ['url' => $postUrl, 'caption' => $caption, 'access_token' => $pageToken]);
echo 'Facebook post: ' . (!empty($fb['id']) ? 'OK' : 'ΑΠΟΤΥΧΙΑ') . "\n";
