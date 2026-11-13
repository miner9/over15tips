<?php
// Αναφορά επισκεψιμότητας από Cloudflare Web Analytics (GraphQL).
// Χρειάζεται: CF_API_TOKEN (secret, Account Analytics: Read), CF_ACCOUNT_ID, CF_SITE_TAG.
// Χρήση: php stats.php [ημέρες=7]
$token = getenv('CF_API_TOKEN'); $acc = getenv('CF_ACCOUNT_ID'); $tag = getenv('CF_SITE_TAG');
if (!$token || !$acc || !$tag) { fwrite(STDERR, "Λείπει CF_API_TOKEN / CF_ACCOUNT_ID / CF_SITE_TAG\n"); exit(1); }
$days = max(1, min(30, (int)($argv[1] ?? 7)));
$from = gmdate('Y-m-d\TH:i:s\Z', time() - $days * 86400); $to = gmdate('Y-m-d\TH:i:s\Z');

function gql(string $q, array $vars, string $token): array {
    $ch = curl_init('https://api.cloudflare.com/client/v4/graphql');
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['query' => $q, 'variables' => $vars])]);
    $r = json_decode(curl_exec($ch) ?: '{}', true) ?: [];
    if (!empty($r['errors'])) { fwrite(STDERR, 'GraphQL: ' . json_encode($r['errors'], JSON_UNESCAPED_UNICODE) . "\n"); exit(1); }
    return $r['data']['viewer']['accounts'][0] ?? [];
}
$f = '{siteTag: $tag, datetime_geq: $from, datetime_leq: $to}';
$grp = fn($alias, $dim, $n) => "$alias: rumPageloadEventsAdaptiveGroups(limit: $n, orderBy: [sum_visits_DESC], filter: $f) { count sum { visits } dimensions { $dim } }";
$q = 'query($acc: String!, $tag: String!, $from: Time!, $to: Time!) { viewer { accounts(filter: {accountTag: $acc}) {'
   . "total: rumPageloadEventsAdaptiveGroups(limit: 1, filter: $f) { count sum { visits } } "
   . "days: rumPageloadEventsAdaptiveGroups(limit: 40, orderBy: [date_ASC], filter: $f) { count sum { visits } dimensions { date } } "
   . $grp('countries', 'countryName', 10) . ' ' . $grp('paths', 'requestPath', 15) . ' ' . $grp('refs', 'refererHost', 10) . ' ' . $grp('devices', 'deviceType', 5)
   . '} } }';
$d = gql($q, compact('acc', 'tag', 'from', 'to'), $token);

$tot = $d['total'][0] ?? ['count' => 0, 'sum' => ['visits' => 0]];
$out = "📊 over15tips.com — τελευταίες $days ημέρες\n";
$out .= "Επισκέψεις: {$tot['sum']['visits']} · Προβολές σελίδων: {$tot['count']}\n\nΑνά ημέρα (επισκέψεις / προβολές):\n";
foreach ($d['days'] ?? [] as $r) $out .= "  {$r['dimensions']['date']}  {$r['sum']['visits']} / {$r['count']}\n";
foreach (['countries' => ['Χώρες', 'countryName'], 'refs' => ['Πηγές', 'refererHost'], 'devices' => ['Συσκευές', 'deviceType'], 'paths' => ['Σελίδες', 'requestPath']] as $k => [$lbl, $dim]) {
    $out .= "\n$lbl:\n";
    foreach ($d[$k] ?? [] as $r) $out .= '  ' . str_pad($r['dimensions'][$dim] ?: '(απευθείας)', 44) . " {$r['sum']['visits']} επισκ. / {$r['count']} προβ.\n";
}
echo $out;
if ($s = getenv('GITHUB_STEP_SUMMARY')) file_put_contents($s, "```\n$out```\n", FILE_APPEND);
