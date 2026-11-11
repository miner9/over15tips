<?php
require_once __DIR__ . '/lib.php';

$page = $_GET['p'] ?? 'home';
if (!in_array($page, ['home', 'over25', 'gg', 'method', 'privacy', 'terms', 'match', 'results', 'live'], true)) $page = 'home';

$LIST_PAGES = ['home' => 'o15', 'over25' => 'o25', 'gg' => 'gg'];
$mk = $LIST_PAGES[$page] ?? 'o15';
$ML = MARKETS[$mk]['label'];
$data = load_data();
$tips = isset($LIST_PAGES[$page]) ? analyse($data, $mk, null, true) : ($page === 'match' ? analyse($data, 'all', null, true) : []);
$top3 = array_slice(array_values(array_filter($tips, fn($t) => $t['ts'] > time())), 0, 3);
$stats = track_stats(load_tracked(), 30, $mk === 'o15' ? 70 : 55, $mk);
$M = null;
if ($page === 'match') {
    foreach ($tips as $t) if ((string)$t['id'] === (string)($_GET['id'] ?? '')) $M = $t;
    if (!$M) $page = 'home';
}
$updated = $data['fetched'] ? date('d/m H:i', $data['fetched']) : '—';
$totalUpcoming = 0;
$__now = gmdate('Y-m-d\TH:i:s\Z'); $__until = gmdate('Y-m-d\TH:i:s\Z', time() + DAYS_AHEAD * 86400);
foreach ($data['leagues'] as $ms) foreach ($ms as $m) if (in_array($m['status'], ['SCHEDULED', 'TIMED']) && $m['utc'] >= $__now && $m['utc'] <= $__until) $totalUpcoming++;

$titles = [
    'home'    => 'Over 1.5 Προγνωστικά Σήμερα — Στατιστικά Τελευταίων 3 Αγώνων | ' . SITE_NAME,
    'method'  => 'Πώς βγαίνουν τα προγνωστικά Over 1.5 | ' . SITE_NAME,
    'privacy' => 'Πολιτική Απορρήτου & Cookies | ' . SITE_NAME,
    'terms'   => 'Όροι Χρήσης & Υπεύθυνο Παιχνίδι | ' . SITE_NAME,
    'results' => 'Ιστορικό Επιτυχίας Προγνωστικών Over 1.5, Over 2.5, Γκολ-Γκολ | ' . SITE_NAME,
    'over25'  => 'Over 2.5 Προγνωστικά Σήμερα — Αγώνες με 3+ Γκολ | ' . SITE_NAME,
    'gg'      => 'Γκολ-Γκολ (GG) Προγνωστικά Σήμερα — Σκοράρουν και οι δύο | ' . SITE_NAME,
    'live'    => 'Live Score Σήμερα — Ζωντανά Αποτελέσματα & Προγνωστικά | ' . SITE_NAME,
];
$HERO = [
    'o15' => ['Over 1.5 γκολ — τα πιο σίγουρα παιχνίδια της αγωνιστικής', 'για 2+ γκολ'],
    'o25' => ['Over 2.5 γκολ — οι αγώνες με τα περισσότερα γκολ', 'για 3+ γκολ'],
    'gg'  => ['Γκολ-Γκολ — σκοράρουν και οι δύο ομάδες', 'για γκολ και από τις δύο ομάδες'],
];
$desc = $page === 'over25' ? 'Δωρεάν προγνωστικά Over 2.5 γκολ για σήμερα και αύριο, με βάση τα τελευταία 3 παιχνίδια κάθε ομάδας σε 8 ευρωπαϊκά πρωταθλήματα.'
     : ($page === 'live' ? 'Live score για Premier League, La Liga, Serie A, Bundesliga, Ligue 1 και άλλα, μαζί με τα προγνωστικά Over 1.5, Over 2.5 και Γκολ-Γκολ για κάθε αγώνα.'
     : ($page === 'gg' ? 'Δωρεάν προγνωστικά Γκολ-Γκολ (GG) για σήμερα και αύριο, με βάση τα τελευταία 3 παιχνίδια κάθε ομάδας σε 8 ευρωπαϊκά πρωταθλήματα.'
     : 'Δωρεάν προγνωστικά Over 1.5 γκολ για σήμερα και αύριο. Ανάλυση των τελευταίων 3 αγώνων κάθε ομάδας σε Premier League, Serie A, La Liga, Bundesliga και άλλα.'));
$canon = SITE_URL . ($page === 'home' ? '/' : '/' . $page);
if ($M) {
    $mn = $M['home']['g'] . ' – ' . $M['away']['g'];
    $mnL = $M['home']['n'] . ' – ' . $M['away']['n'];
    $titles['match'] = $mn . ' Προγνωστικά (' . date('d/m', $M['ts']) . ') Over 1.5, Over 2.5, GG | ' . SITE_NAME;
    $desc = $mn . ' (' . $mnL . ') ' . date('d/m/Y', $M['ts']) . ', ' . $M['leagueName'] . ': Over 1.5 ' . $M['m']['o15']['conf'] . '%, Over 2.5 ' . $M['m']['o25']['conf'] . '%, Γκολ-Γκολ ' . $M['m']['gg']['conf'] . '%. Ανάλυση των τελευταίων 3 αγώνων.';
    $canon = SITE_URL . '/agonas/' . match_slug($M);
}

?><!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titles[$page]) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($titles[$page]) ?>">
<meta property="og:type" content="website">
<meta name="theme-color" content="#0a0e13">
<link rel="icon" type="image/png" href="/assets/favicon.png">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<meta property="og:image" content="<?= e(SITE_URL) ?>/assets/og.png">
<meta property="og:url" content="<?= e($canon) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<?php if (ADSENSE_CLIENT): ?><meta name="google-adsense-account" content="<?= e(ADSENSE_CLIENT) ?>"><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e(ADSENSE_CLIENT) ?>" crossorigin="anonymous"></script><?php endif; ?>
<?php if (GA_ID): ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(GA_ID) ?>"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e(GA_ID) ?>');</script><?php endif; ?>
<style>
:root{
  --bg:#0a0e13;--bg2:#0e131a;--card:#121922;--card2:#161f2a;--line:#1f2a37;--line2:#283545;
  --tx:#e9eef4;--mut:#8796a8;--mut2:#5d6b7c;
  --acc:#2bd576;--acc-d:#1a9e55;--acc-bg:rgba(43,213,118,.10);
  --gold:#f4b740;--gold-bg:rgba(244,183,64,.10);--blue:#5aa9ff;--red:#ef5d5d;
  --r:14px;--mono:'JetBrains Mono',ui-monospace,monospace;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Manrope,system-ui,sans-serif;background:var(--bg);color:var(--tx);font-size:15px;line-height:1.5;-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}
.wrap{max-width:1480px;margin:0 auto;padding:0 28px}

/* header */
header{position:sticky;top:0;z-index:20;background:rgba(10,14,19,.86);backdrop-filter:blur(12px);border-bottom:1px solid var(--line)}
.hd{display:flex;align-items:center;gap:28px;height:64px}
.logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:18px;letter-spacing:-.02em}
.logo-mark{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--acc),#13a35a);display:grid;place-items:center;font-family:var(--mono);font-size:13px;font-weight:700;color:#04140b}
.logo b{color:var(--acc)}
nav{display:flex;gap:4px;flex:1}
nav a{padding:8px 14px;border-radius:8px;color:var(--mut);font-weight:600;font-size:14px}
nav a:hover,nav a.on{color:var(--tx);background:var(--card)}
.btn-tg{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:9px;background:#229ED9;color:#fff;font-weight:700;font-size:14px}
.btn-tg svg{width:16px;height:16px}

/* hero */
.hero{padding:44px 0 28px;border-bottom:1px solid var(--line);background:radial-gradient(900px 300px at 15% -20%,rgba(43,213,118,.12),transparent 70%)}
.eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--acc)}
.dot{width:7px;height:7px;border-radius:50%;background:var(--acc);box-shadow:0 0 0 4px var(--acc-bg);animation:p 2s infinite}
@keyframes p{50%{box-shadow:0 0 0 7px transparent}}
h1{font-size:clamp(28px,4.2vw,44px);line-height:1.1;letter-spacing:-.03em;font-weight:800;margin:14px 0 10px;max-width:760px}
.hero p.sub{color:var(--mut);max-width:620px;font-size:16px}
.stats{display:flex;gap:12px;margin-top:26px;flex-wrap:wrap}
.stat{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:12px 18px;min-width:140px}
.stat .v{font-family:var(--mono);font-size:22px;font-weight:700}
.stat .l{font-size:12px;color:var(--mut);font-weight:600}
.demo{margin-top:18px;padding:10px 14px;border-radius:10px;background:var(--gold-bg);border:1px solid rgba(244,183,64,.3);color:var(--gold);font-size:13px;font-weight:600}

/* layout */
.main{display:grid;grid-template-columns:1fr 360px;gap:32px;padding:28px 28px 60px;align-items:start}
@media(max-width:980px){.main{grid-template-columns:1fr}}
@media(max-width:600px){.hd{gap:12px}.logo{white-space:nowrap;font-size:16px}nav a{padding:6px 8px;font-size:13px}nav{overflow-x:auto;white-space:nowrap;scrollbar-width:none}header .btn-tg{display:none}.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.stat{min-width:0;padding:10px 12px}.stat .v{font-size:18px}.stat .l{font-size:11px}.hero{padding:30px 0 22px}.wrap{padding:0 14px}.main{padding:20px 14px 60px}}

/* filters */
.filters{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:18px}
.chips{display:flex;gap:6px;flex-wrap:wrap}
.chip{padding:7px 13px;border-radius:999px;border:1px solid var(--line2);background:transparent;color:var(--mut);font:600 13px Manrope,sans-serif;cursor:pointer}
.chip:hover{color:var(--tx)}
.chip.on{background:var(--tx);color:var(--bg);border-color:var(--tx)}
select{background:var(--card);color:var(--tx);border:1px solid var(--line2);border-radius:9px;padding:8px 12px;font:600 13px Manrope,sans-serif}
.spacer{flex:1}
.count{font-size:13px;color:var(--mut)}

/* card */
.list{display:flex;flex-direction:column;gap:12px}
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden;transition:border-color .15s}
.card:hover{border-color:var(--line2)}
.card.top{border-color:rgba(244,183,64,.35);box-shadow:0 0 0 1px rgba(244,183,64,.08) inset}
.c-top{display:flex;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px solid var(--line);font-size:12px;color:var(--mut);font-weight:600}
.lg{display:inline-flex;align-items:center;gap:6px;color:var(--tx)}
.lg i{width:6px;height:6px;border-radius:2px;background:var(--blue)}
.when{font-family:var(--mono)}
.lv{margin-left:auto;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:800;font-family:ui-monospace,Menlo,monospace;background:rgba(255,255,255,.06);color:var(--tx);white-space:nowrap}
.lv.on{background:rgba(255,77,77,.14);color:#ff6b6b}.lv.on::before{content:'';display:inline-block;width:6px;height:6px;border-radius:50%;background:#ff4d4d;margin-right:6px;vertical-align:1px;animation:pl 1.2s infinite}
.lv.win{background:var(--acc-bg);color:var(--acc)}.lv.lose{background:rgba(255,77,77,.1);color:#ff8080}
.lv:not([hidden])+.badge{margin-left:8px}
@keyframes pl{50%{opacity:.25}}
.ldot{display:inline-block;width:7px;height:7px;border-radius:50%;background:#ff4d4d;margin-right:6px;vertical-align:1px;animation:pl 1.6s infinite}
.lvt{width:100%;border-collapse:collapse;margin:8px 0 26px;table-layout:fixed}.lvt .p{width:230px}.lvt tr.lvon .t{color:#ff6b6b;font-weight:700}h1 .ldot{width:12px;height:12px;margin-right:12px;vertical-align:6px}.lvt .h,.lvt .a{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.lvt td{padding:11px 10px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:middle}
.lvt .t{color:var(--mut);font-family:ui-monospace,Menlo,monospace;width:58px}.lvt .h{text-align:right;font-weight:600}.lvt .a{font-weight:600}
.lvt .s{text-align:center;width:96px}.lvt .s b{font-family:ui-monospace,Menlo,monospace;font-size:15px}.lvt .p{text-align:right;white-space:nowrap}
.lvt .p a{display:inline-block;margin-left:4px;padding:2px 7px;border-radius:5px;font-size:11px;font-weight:700;background:var(--card);color:var(--mut)}
.lvt .p a.hi{background:var(--acc-bg);color:var(--acc)}.lvt tr.lvon .s b{color:#ff6b6b}
.lvh{font-size:15px;margin:22px 0 4px;color:var(--mut);text-transform:uppercase;letter-spacing:.04em}
@media(max-width:600px){.lvt .p{display:none;width:0}.lvt td{padding:10px 4px;font-size:13px}.lvt .t{width:44px}}
.badge{margin-left:auto;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;letter-spacing:.02em}
.badge.top{background:var(--gold-bg);color:var(--gold)}
.badge.strong{background:var(--acc-bg);color:var(--acc)}
.badge.good{background:rgba(90,169,255,.1);color:var(--blue)}
.c-body{display:grid;grid-template-columns:1fr auto 1fr;gap:16px;align-items:center;padding:18px}
.team{display:flex;align-items:center;gap:12px;min-width:0}
.team.away{flex-direction:row-reverse;text-align:right}
.team .nm{font-weight:700;font-size:16px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.team .hint{font-size:12px;color:var(--mut)}
.crest{width:36px;height:36px;flex:none;object-fit:contain}
.crest-i{border-radius:50%;background:var(--card2);border:1px solid var(--line2);display:grid;place-items:center;font-size:12px;font-weight:800;color:var(--mut)}
.ring{--p:0;width:76px;height:76px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(var(--rc) calc(var(--p)*1%),var(--line) 0)}
.ring>div{width:62px;height:62px;border-radius:50%;background:var(--card);display:grid;place-items:center;text-align:center;line-height:1}
.ring b{font-family:var(--mono);font-size:19px}
.ring small{display:block;font-size:9px;color:var(--mut);font-weight:700;letter-spacing:.06em;margin-top:3px}
.form{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--line);border-top:1px solid var(--line)}
.form>div{background:var(--bg2);padding:12px 18px}
.form h4{font-size:11px;color:var(--mut2);text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:8px;display:flex;justify-content:space-between}
.form h4 span{color:var(--acc);font-family:var(--mono)}
.form ul{list-style:none;display:flex;flex-direction:column;gap:5px}
.form li{display:grid;grid-template-columns:44px 1fr 16px;gap:8px;align-items:center;font-size:13px}
.form .sc{font-family:var(--mono);font-weight:700;text-align:center;border-radius:5px;padding:2px 0}
.form .ov .sc{background:var(--acc-bg);color:var(--acc)}
.form .un .sc{background:rgba(239,93,93,.1);color:var(--red)}
.form .op{color:var(--mut);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.form .tick{font-size:12px;color:var(--mut2)}
.form .ov .tick{color:var(--acc)}
.c-foot{display:flex;flex-wrap:wrap;gap:18px;align-items:center;padding:12px 18px;border-top:1px solid var(--line);font-size:12px;color:var(--mut)}
.c-foot b{color:var(--tx);font-family:var(--mono);font-size:13px;margin-left:4px}
.btn-aff{margin-left:auto;padding:8px 14px;border-radius:8px;background:var(--acc);color:#04140b;font-weight:800;font-size:13px}
.btn-aff:hover{background:#3be585}
@media(max-width:600px){
  .c-body{grid-template-columns:1fr auto;grid-template-areas:"h r" "a r"}
  .team.home{grid-area:h}.team.away{grid-area:a;flex-direction:row;text-align:left}.ring{grid-area:r}
  .form{grid-template-columns:1fr}.btn-aff{margin-left:0;width:100%;text-align:center}
}

/* ads */
.ad{border-radius:var(--r);position:relative;min-height:100px}
.ad-label{position:absolute;top:6px;left:10px;font-size:10px;color:var(--mut2);text-transform:uppercase;letter-spacing:.08em}
.ad-ph{border:1px dashed var(--line2);display:grid;place-items:center;color:var(--mut2);font-size:13px;background:var(--bg2)}
.ad-top{margin-bottom:18px;min-height:90px}
.ad-side{min-height:250px}

/* sidebar */
.side{display:flex;flex-direction:column;gap:16px;position:sticky;top:84px;max-height:calc(100vh - 100px);overflow-y:auto;scrollbar-width:none}
@media(max-width:980px){.side{position:static;max-height:none}}
@media(min-width:1500px){.wrap.main{max-width:none;grid-template-columns:minmax(0,1fr) minmax(0,1000px) minmax(0,1fr)}.main>section{grid-column:2}.main>aside{grid-column:3;width:100%;max-width:360px}}
.box{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px}
.box h3{font-size:14px;font-weight:800;margin-bottom:4px;display:flex;align-items:center;gap:8px}
.box .s{font-size:12px;color:var(--mut);margin-bottom:14px}
.slip li{list-style:none;display:flex;justify-content:space-between;gap:10px;padding:10px 0;border-top:1px solid var(--line);font-size:13px}
.slip li:first-child{border-top:0}
.slip .t{font-weight:700}.slip .m{font-size:11px;color:var(--mut)}
.slip .o{font-size:11px;font-weight:800;color:var(--acc);background:var(--acc-bg);padding:4px 8px;border-radius:6px;align-self:center;white-space:nowrap}
.slip-tot{display:flex;justify-content:space-between;margin:12px 0;padding-top:12px;border-top:1px solid var(--line2);font-weight:700}
.slip-tot span:last-child{font-family:var(--mono);color:var(--gold)}
.box .btn-aff{display:block;text-align:center;margin:0}
.tgbox{background:linear-gradient(160deg,rgba(34,158,217,.18),var(--card) 60%)}
.tgbox .btn-tg{width:100%;justify-content:center}
.aff-banner{background:linear-gradient(135deg,#123d26,#0f1a14);border:1px solid rgba(43,213,118,.3)}
.aff-banner .nm{font-size:20px;font-weight:800}
.aff-banner p{font-size:13px;color:var(--mut);margin:6px 0 14px}
.aff-banner .tc{font-size:10px;color:var(--mut2);margin-top:10px}
.empty{padding:50px 20px;text-align:center;color:var(--mut);background:var(--card);border:1px dashed var(--line2);border-radius:var(--r)}

/* content pages */
.page{max-width:760px;padding:44px 0 70px}
.page h1{margin-top:0}
.page h2{font-size:20px;margin:28px 0 8px}
.page p,.page li{color:#c3ccd7;margin-bottom:10px}
.page ul{padding-left:20px}
.formula{font-family:var(--mono);background:var(--card);border:1px solid var(--line);border-radius:10px;padding:14px 16px;font-size:13px;color:var(--acc);margin:12px 0;overflow-x:auto}

/* track record */
.trk{display:flex;align-items:center;gap:10px;margin-top:14px;font-size:13px;color:var(--mut)}
.trk b{color:var(--acc);font-family:var(--mono);font-size:15px}
.trk a{color:var(--acc);font-weight:700}
.rtab{width:100%;border-collapse:collapse;font-size:14px;margin-top:10px}
.rtab th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--mut2);padding:8px 10px;border-bottom:1px solid var(--line2)}
.rtab td{padding:9px 10px;border-bottom:1px solid var(--line)}
.rtab .sc{font-family:var(--mono);font-weight:700}
.rtab .ok{color:var(--acc);font-weight:800}.rtab .no{color:var(--red);font-weight:800}
.tiers{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin:18px 0}
/* match page */
.crumbs{font-size:13px;color:var(--mut);margin-bottom:14px}.crumbs a{color:var(--acc)}
.mhead{display:grid;grid-template-columns:1fr auto 1fr;gap:20px;align-items:center;background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:26px;margin:18px 0}
.mhead .team{flex-direction:column;text-align:center;gap:8px}.mhead .team.away{flex-direction:column;text-align:center}
.mhead .crest{width:56px;height:56px}.mhead .crest-i{font-size:16px}
.mhead .nm{font-size:18px;white-space:normal}
.mhead .ring{width:110px;height:110px}.mhead .ring>div{width:92px;height:92px}.mhead .ring b{font-size:26px}
.kv{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin:14px 0}
.cta-tg{display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap;background:linear-gradient(135deg,rgba(34,158,217,.18),var(--card));border:1px solid rgba(34,158,217,.35);border-radius:var(--r);padding:18px;margin:22px 0}
.cta-tg b{font-size:16px}.cta-tg p{color:var(--mut);font-size:13px;margin:0}
.more{color:var(--acc);font-weight:700;font-size:13px}
@media(max-width:600px){.mhead{grid-template-areas:none;padding:16px 10px;gap:6px}.mhead .team.home,.mhead .team.away,.mhead .ring{grid-area:auto}.mhead .ring{width:84px;height:84px}.mhead .ring>div{width:70px;height:70px}.mhead .ring b{font-size:19px}.mhead .nm{font-size:14px}.mhead .crest{width:40px;height:40px}}
.page .form ul{padding-left:0}.page .form li{margin-bottom:0;color:inherit}
/* sticky telegram (mobile) */
#tgbar{display:none}
@media(max-width:600px){#tgbar{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:40;gap:10px;align-items:center;justify-content:space-between;padding:10px 14px;background:#0f2633;border-top:1px solid rgba(34,158,217,.4);font-size:13px;font-weight:700}#tgbar a{background:#229ED9;color:#fff;padding:8px 14px;border-radius:8px}body{padding-bottom:58px}}

/* footer */
footer{border-top:1px solid var(--line);background:var(--bg2);padding:32px 0;font-size:13px;color:var(--mut)}
.ft{display:flex;flex-wrap:wrap;gap:24px;justify-content:space-between;align-items:flex-start}
.ft nav{flex:none}
.rg{display:flex;gap:12px;align-items:center;max-width:640px}
.r18{flex:none;width:40px;height:40px;border-radius:50%;border:2px solid var(--red);display:grid;place-items:center;font-weight:800;color:var(--red);font-size:13px}
.legal{margin-top:20px;font-size:11px;color:var(--mut2)}

/* cookies */
@media(max-width:600px){#ck{bottom:68px!important}}
#ck{position:fixed;left:16px;right:16px;bottom:16px;max-width:560px;margin:auto;background:var(--card2);border:1px solid var(--line2);border-radius:12px;padding:16px;display:none;gap:12px;align-items:center;z-index:50;font-size:13px;box-shadow:0 20px 50px rgba(0,0,0,.5)}
#ck button{flex:none;padding:8px 16px;border-radius:8px;border:0;background:var(--acc);color:#04140b;font:800 13px Manrope,sans-serif;cursor:pointer}
#ck a{text-decoration:underline}
</style>
</head>
<body>
<header><div class="wrap hd">
  <a href="./" class="logo"><span class="logo-mark">1.5</span><span><?= preg_replace('/(\d[\d.]*)/', '<b>$1</b>', e(SITE_NAME)) ?></span></a>
  <nav>
    <a href="./" class="<?= $page === 'home' ? 'on' : '' ?>">Over 1.5</a>
    <a href="?p=over25" class="<?= $page === 'over25' ? 'on' : '' ?>">Over 2.5</a>
    <a href="?p=gg" class="<?= $page === 'gg' ? 'on' : '' ?>">Γκολ-Γκολ</a>
    <a href="?p=live" class="<?= $page === 'live' ? 'on' : '' ?>"><span class="ldot"></span>Live</a>
    <a href="?p=results" class="<?= $page === 'results' ? 'on' : '' ?>">Ιστορικό</a>
    <a href="?p=method" class="<?= $page === 'method' ? 'on' : '' ?>">Μεθοδολογία</a>
  </nav>
  <?php if (TELEGRAM_URL): ?><a class="btn-tg" href="<?= e(TELEGRAM_URL) ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.8 15.3 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.8.4 1.3.2 1.5-.7L20.9 5c.3-1.2-.4-1.7-1.2-1.4L3.4 9.9c-1.1.4-1.1 1.1-.2 1.4l4.2 1.3 9.7-6.1c.5-.3.9-.1.5.2"/></svg>Telegram</a><?php endif; ?>
</div></header>

<?php if (isset($LIST_PAGES[$page])): ?>
<section class="hero"><div class="wrap">
  <span class="eyebrow"><span class="dot"></span>Ενημέρωση <?= e($updated) ?></span>
  <h1><?= e($HERO[$mk][0]) ?></h1>
  <p class="sub">Αναλύουμε αυτόματα τα τελευταία 3 παιχνίδια κάθε ομάδας σε <?= count(LEAGUES) ?> κορυφαία πρωταθλήματα και βαθμολογούμε κάθε αγώνα <?= e($HERO[$mk][1]) ?>.</p>
  <div class="stats">
    <div class="stat"><div class="v"><?= $totalUpcoming ?></div><div class="l">Αγώνες αναλύθηκαν</div></div>
    <div class="stat"><div class="v"><?= count($tips) ?></div><div class="l">Προτάσεις <?= e($ML) ?></div></div>
    <div class="stat"><div class="v"><?= count(array_filter($tips, fn($t) => $t['conf'] >= 85)) ?></div><div class="l">Κορυφαίες (85%+)</div></div>
  </div>
  <?php if ($stats['n'] >= 5): ?><div class="trk">📊 Επιτυχία <?= e($ML) ?> τελευταίων 30 ημερών (<?= $mk === 'o15' ? 70 : 55 ?>%+): <b><?= pct($stats['w'], $stats['n']) ?></b> σε <?= $stats['n'] ?> προτάσεις · <a href="?p=results">Δες όλο το ιστορικό →</a></div>
  <?php else: ?><div class="trk">📊 Κάθε πρόταση καταγράφεται δημόσια με το αποτέλεσμά της · <a href="?p=results">Ιστορικό →</a></div><?php endif; ?>
  <?php if (is_demo()): ?><div class="demo">DEMO δεδομένα — βάλε το API key στο config.php για πραγματικούς αγώνες.</div><?php endif; ?>
</div></section>

<div class="wrap main">
  <section>
    <?= ad_slot(ADSENSE_SLOT_TOP, 'ad-top') ?>
    <div class="filters">
      <div class="chips" id="days">
        <button class="chip on" data-d="all">Όλες</button>
        <?php $seen = []; foreach ($tips as $t) { $k = date('Y-m-d', $t['ts']); if (isset($seen[$k])) continue; $seen[$k] = gr_day($t['ts']); }
        ksort($seen); foreach ($seen as $k => $lbl): ?><button class="chip" data-d="<?= $k ?>"><?= e($lbl) ?></button><?php endforeach; ?>
      </div>
      <span class="spacer"></span>
      <select id="lg"><option value="all">Όλα τα πρωταθλήματα</option><?php foreach (LEAGUES as $c => $n): ?><option value="<?= e($c) ?>"><?= e($n) ?></option><?php endforeach; ?></select>
      <select id="mn"><?php foreach ([55, 60, 70, 80, 85] as $v): if ($v < MARKETS[$mk]['min']) continue; ?><option value="<?= $v ?>" <?= $v === ($mk === 'o15' ? 70 : 55) ? 'selected' : '' ?>><?= $v ?>%+</option><?php endforeach; ?></select>
    </div>
    <p class="count" id="cnt" style="margin:-6px 0 12px"></p>

    <div class="list" id="list">
    <?php if (!$tips): ?><div class="empty">Δεν βρέθηκαν αγώνες αυτή τη στιγμή. Δοκίμασε ξανά αργότερα.</div><?php endif; ?>
    <?php foreach ($tips as $i => $t): [$tc, $tl] = tier($t['conf']);
      $rc = $tc === 'top' ? 'var(--gold)' : ($tc === 'strong' ? 'var(--acc)' : 'var(--blue)'); ?>
      <article class="card <?= $tc === 'top' ? 'top' : '' ?>" data-d="<?= date('Y-m-d', $t['ts']) ?>" data-l="<?= e($t['league']) ?>" data-c="<?= $t['conf'] ?>">
        <div class="c-top">
          <span class="lg"><i></i><?= e($t['leagueName']) ?></span>
          <span class="when"><?= e(gr_day($t['ts'])) ?> · <?= date('H:i', $t['ts']) ?></span>
          <?= live_badge($t, $mk) ?>
          <span class="badge <?= $tc ?>"><?= e($tl) ?></span>
        </div>
        <div class="c-body">
          <div class="team home"><?= crest($t['home']) ?><div><div class="nm"><?= e($t['home']['g']) ?></div><div class="hint">Γηπεδούχος</div></div></div>
          <div class="ring" style="--p:<?= $t['conf'] ?>;--rc:<?= $rc ?>"><div><span><b><?= $t['conf'] ?>%</b><small><?= e(MARKETS[$mk]['short']) ?></small></span></div></div>
          <div class="team away"><?= crest($t['away']) ?><div><div class="nm"><?= e($t['away']['g']) ?></div><div class="hint">Φιλοξενούμενος</div></div></div>
        </div>
        <div class="form">
          <div><h4>Τελευταία 3 · <?= e($t['home']['g']) ?> <span><?= $t['m'][$mk]['H'] ?>/3</span></h4><ul><?= form_rows($t['H'], $mk) ?></ul></div>
          <div><h4>Τελευταία 3 · <?= e($t['away']['g']) ?> <span><?= $t['m'][$mk]['A'] ?>/3</span></h4><ul><?= form_rows($t['A'], $mk) ?></ul></div>
        </div>
        <div class="c-foot">
          <span><?= e($ML) ?> σε <b><?= $t['hits'] ?>/6</b></span>
          <span>Αναμ. γκολ <b><?= number_format($t['xg'], 2) ?></b></span>
          <span>Δίκαιη απόδοση <b><?= number_format($t['fair'], 2) ?></b></span>
          <a class="more" href="<?= e(match_url($t)) ?>">Πλήρης ανάλυση →</a>
          <?= aff_btn(AFF_OFFER) ?>
        </div>
      </article>
      <?php if (($i + 1) % ADS_EVERY_N_CARDS === 0): ?><?= ad_slot(ADSENSE_SLOT_INFEED, 'feed') ?><?php endif; ?>
    <?php endforeach; ?>
    </div>
    <div style="margin-top:20px"><?= ad_slot(ADSENSE_SLOT_BOTTOM) ?></div>
  </section>

  <aside class="side">
    <?php if ($top3): $prod = array_product(array_column($top3, 'fair')); ?>
    <div class="box">
      <h3>⚡ Κουπόνι ημέρας</h3>
      <p class="s">Οι 3 υψηλότερες επιλογές <?= e($ML) ?></p>
      <ul class="slip"><?php foreach ($top3 as $t): ?>
        <li><div><div class="t"><?= e($t['home']['g']) ?> – <?= e($t['away']['g']) ?></div><div class="m"><?= e(gr_day($t['ts'])) ?> <?= date('H:i', $t['ts']) ?> · <?= $t['conf'] ?>%</div></div><span class="o"><?= e($ML) ?></span></li>
      <?php endforeach; ?></ul>
      <div class="slip-tot"><span>Συνδυαστική πιθανότητα</span><span><?= round(100 / $prod) ?>%</span></div>
      <?= aff_btn('Παίξε το κουπόνι') ?>
    </div>
    <?php endif; ?>

    <?php if (AFF_URL && AFF_NAME): ?>
    <a class="box aff-banner" href="<?= e(AFF_URL) ?>" target="_blank" rel="sponsored noopener">
      <div class="nm"><?= e(AFF_NAME) ?></div><p><?= e(AFF_BANNER_TEXT) ?></p>
      <span class="btn-aff">Εγγραφή <span aria-hidden="true">→</span></span>
      <div class="tc">18+ | Διαφήμιση | Παίξε υπεύθυνα. Ισχύουν όροι.</div>
    </a>
    <?php endif; ?>

    <?php if (TELEGRAM_URL): ?>
    <div class="box tgbox">
      <h3>Τα tips κάθε πρωί στο κινητό</h3>
      <p class="s">Μπες στο δωρεάν κανάλι μας στο Telegram.</p>
      <a class="btn-tg" href="<?= e(TELEGRAM_URL) ?>" target="_blank" rel="noopener">Μπες στο κανάλι</a>
    </div>
    <?php endif; ?>

    <?= ad_slot(ADSENSE_SLOT_INFEED, 'ad-side') ?>

    <div class="box">
      <h3>Πώς διαβάζεται</h3>
      <p class="s" style="margin:0">Το % συνδυάζει πόσα από τα 6 πρόσφατα ματς των δύο ομάδων έβγαλαν 2+ γκολ με τα αναμενόμενα γκολ (επίθεση/άμυνα). Αν η απόδοση του bookmaker είναι πάνω από τη «δίκαιη», έχει αξία. <a href="?p=method" style="color:var(--acc)">Περισσότερα →</a></p>
    </div>
  </aside>
</div>

<?php elseif ($page === 'match'): $t = $M; [$tc, $tl] = tier($t['conf']);
  $rc = $tc === 'top' ? 'var(--gold)' : ($tc === 'strong' ? 'var(--acc)' : 'var(--blue)');
  $H = $t['H']; $A = $t['A']; $hn = $t['home']['g']; $an = $t['away']['g']; ?>
<div class="wrap page" style="max-width:860px">
  <div class="crumbs"><a href="./">Προγνωστικά</a> › <?= e($t['leagueName']) ?> › <?= e($hn) ?> – <?= e($an) ?></div>
  <h1><?= e($hn) ?> – <?= e($an) ?>: Προγνωστικά</h1>
  <p style="color:var(--mut)"><?= e($t['home']['n']) ?> – <?= e($t['away']['n']) ?> · <?= e($t['leagueName']) ?> · <?= ['Κυριακή','Δευτέρα','Τρίτη','Τετάρτη','Πέμπτη','Παρασκευή','Σάββατο'][(int)date('w', $t['ts'])] ?> <?= date('d/m/Y H:i', $t['ts']) ?> (ώρα Ελλάδας) <?= live_badge($t, 'o15') ?></p>
  <div class="mhead">
    <div class="team home"><?= crest($t['home']) ?><div class="nm"><?= e($hn) ?></div><div class="hint">Γηπεδούχος</div></div>
    <div class="ring" style="--p:<?= $t['conf'] ?>;--rc:<?= $rc ?>"><div><span><b><?= $t['conf'] ?>%</b><small>OVER 1.5</small></span></div></div>
    <div class="team away"><?= crest($t['away']) ?><div class="nm"><?= e($an) ?></div><div class="hint">Φιλοξενούμενος</div></div>
  </div>
  <div class="kv">
    <div class="stat"><div class="v"><?= $t['hits'] ?>/6</div><div class="l">Over 1.5 στα πρόσφατα</div></div>
    <div class="stat"><div class="v"><?= number_format($t['xg'], 2) ?></div><div class="l">Αναμενόμενα γκολ</div></div>
    <div class="stat"><div class="v"><?= number_format($t['fair'], 2) ?></div><div class="l">Δίκαιη απόδοση</div></div>
    <div class="stat"><div class="v" style="font-size:16px"><?= e($tl) ?></div><div class="l">Κατηγορία</div></div>
  </div>
  <h2>Όλες οι αγορές</h2>
  <div style="overflow-x:auto"><table class="rtab"><tr><th>Αγορά</th><th>Πιθανότητα</th><th>Στα πρόσφατα</th><th>Δίκαιη απόδοση</th></tr>
  <?php foreach (MARKETS as $k => $info): $x = $t['m'][$k]; ?>
    <tr><td><b><?= e($info['label']) ?></b></td><td class="sc" style="color:<?= $x['conf'] >= 75 ? 'var(--acc)' : ($x['conf'] >= 60 ? 'var(--tx)' : 'var(--mut)') ?>"><?= $x['conf'] ?>%</td><td><?= $x['hits'] ?>/6</td><td class="sc"><?= number_format($x['fair'], 2) ?></td></tr>
  <?php endforeach; ?></table></div>
  <?= ad_slot(ADSENSE_SLOT_TOP, 'ad-top') ?>
  <h2>Ανάλυση</h2>
  <p>Η <b><?= e($hn) ?></b> είδε 2+ γκολ σε <?= $H['hits'] ?> από τα τελευταία 3 παιχνίδια πρωταθλήματος, με μέσο όρο <?= number_format($H['avgTot'], 1) ?> γκολ ανά αγώνα (<?= number_format($H['avgFor'], 1) ?> υπέρ, <?= number_format($H['avgAg'], 1) ?> κατά).
  Η <b><?= e($an) ?></b> είδε Over 1.5 σε <?= $A['hits'] ?> από τα 3, με μέσο όρο <?= number_format($A['avgTot'], 1) ?> γκολ (<?= number_format($A['avgFor'], 1) ?> υπέρ, <?= number_format($A['avgAg'], 1) ?> κατά).</p>
  <p>Συνδυάζοντας την επίθεση κάθε ομάδας με την άμυνα του αντιπάλου, τα αναμενόμενα γκολ του αγώνα βγαίνουν <b><?= number_format($t['xg'], 2) ?></b>. Η τελική μας εκτίμηση για <b>Over 1.5 γκολ είναι <?= $t['conf'] ?>%</b>, που αντιστοιχεί σε δίκαιη απόδοση <?= number_format($t['fair'], 2) ?>. Αν η απόδοση που βρίσκεις είναι μεγαλύτερη, το στοίχημα έχει αξία σύμφωνα με το μοντέλο.</p>
  <div class="form" style="border:1px solid var(--line);border-radius:var(--r);overflow:hidden;margin:18px 0">
    <div><h4>Τελευταία 3 · <?= e($hn) ?> <span><?= $H['hits'] ?>/3</span></h4><ul><?= form_rows($H) ?></ul></div>
    <div><h4>Τελευταία 3 · <?= e($an) ?> <span><?= $A['hits'] ?>/3</span></h4><ul><?= form_rows($A) ?></ul></div>
  </div>
  <?php if (TELEGRAM_URL): ?><div class="cta-tg"><div><b>Θες τα tips κάθε πρωί στο κινητό;</b><p>Δωρεάν κανάλι Telegram με τις ισχυρότερες επιλογές και τα αποτελέσματα.</p></div><a class="btn-tg" href="<?= e(TELEGRAM_URL) ?>" target="_blank" rel="noopener">Μπες στο κανάλι</a></div><?php endif; ?>
  <?php if (AFF_URL): ?><p><?= aff_btn(AFF_OFFER) ?></p><?php endif; ?>
  <?php $tips = array_values(array_filter($tips, fn($o) => $o['conf'] >= MIN_SHOW));
  $others = array_slice(array_values(array_filter($tips, fn($o) => $o['id'] !== $t['id'] && date('Y-m-d', $o['ts']) === date('Y-m-d', $t['ts']))), 0, 8);
  if (!$others) $others = array_slice(array_values(array_filter($tips, fn($o) => $o['id'] !== $t['id'])), 0, 8);
  if ($others): ?>
  <h2>Άλλα προγνωστικά Over 1.5</h2>
  <ul><?php foreach ($others as $o): ?>
    <li><a href="<?= e(match_url($o)) ?>" style="color:var(--acc)"><?= e($o['home']['g']) ?> – <?= e($o['away']['g']) ?></a> · <?= e(gr_day($o['ts'])) ?> · <?= $o['conf'] ?>%</li>
  <?php endforeach; ?></ul><?php endif; ?>
  <?= ad_slot(ADSENSE_SLOT_BOTTOM) ?>
  <p style="font-size:12px;color:var(--mut2);margin-top:18px">Στατιστική εκτίμηση, όχι εγγύηση. 21+ | Παίξε υπεύθυνα.</p>
  <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'SportsEvent', 'name' => "$hn vs $an", 'startDate' => date('c', $t['ts']), 'sport' => 'Soccer', 'homeTeam' => ['@type' => 'SportsTeam', 'name' => $hn], 'awayTeam' => ['@type' => 'SportsTeam', 'name' => $an], 'location' => ['@type' => 'Place', 'name' => $hn . ' stadium'], 'eventStatus' => 'https://schema.org/EventScheduled'], JSON_UNESCAPED_UNICODE) ?></script>
</div>

<?php elseif ($page === 'live'):
  $byId = []; foreach (analyse($data, 'all', 0, true) as $x) $byId[$x['id']] = $x;
  $days = [];
  foreach ($data['leagues'] as $code => $ms) foreach ($ms as $m) {
    if (!in_array($m['status'], ['SCHEDULED', 'TIMED', 'IN_PLAY', 'PAUSED', 'FINISHED'], true)) continue;
    $ts = strtotime($m['utc']); if ($ts < strtotime('today')) continue;
    $days[date('Y-m-d', $ts)][] = $m + ['lg' => $code, 'ts' => $ts];
  }
  ksort($days); $days = array_slice($days, 0, 2, true); ?>
<div class="wrap page" style="max-width:980px">
  <h1><span class="ldot"></span>Live score</h1>
  <p>Ζωντανά σκορ για τα <?= count(LEAGUES) ?> πρωταθλήματα που αναλύουμε, μαζί με τις πιθανότητές μας για κάθε αγώνα. Ανανεώνεται αυτόματα κάθε λεπτό, με μικρή καθυστέρηση.</p>
  <?= ad_slot(ADSENSE_SLOT_TOP, 'ad-top') ?>
  <?php if (!$days): ?><div class="empty">Δεν υπάρχουν προγραμματισμένοι αγώνες τις επόμενες ημέρες.</div><?php endif; ?>
  <?php foreach ($days as $day => $ms): usort($ms, fn($a, $b) => $a['ts'] <=> $b['ts'] ?: strcmp($a['lg'], $b['lg'])); ?>
  <h2 class="lvh"><?= e(gr_day(strtotime($day))) ?> <?= date('d/m', strtotime($day)) ?></h2>
  <div style="overflow-x:auto"><table class="lvt">
  <?php foreach ($ms as $m): $live = in_array($m['status'], ['IN_PLAY', 'PAUSED'], true); $t = $byId[$m['id']] ?? null; ?>
    <tr data-id="<?= (int)$m['id'] ?>" data-ts="<?= $m['ts'] ?>" class="<?= $live ? 'lvon' : '' ?>">
      <td class="t"><?= $m['status'] === 'FINISHED' ? 'Τελ.' : ($m['status'] === 'PAUSED' ? 'Ημ.' : ($live ? 'LIVE' : date('H:i', $m['ts']))) ?></td>
      <td class="h"><?= e(gr_team($m['h']['n'])) ?></td>
      <td class="s"><b><?= $m['hg'] !== null && $m['status'] !== 'TIMED' && $m['status'] !== 'SCHEDULED' ? $m['hg'] . ' - ' . $m['ag'] : '–' ?></b><div style="font-size:11px;color:var(--mut2)"><?= e(LEAGUES[$m['lg']] ?? $m['lg']) ?></div></td>
      <td class="a"><?= e(gr_team($m['a']['n'])) ?></td>
      <td class="p"><?php if ($t): foreach (['o15' => 'O1.5', 'o25' => 'O2.5', 'gg' => 'GG'] as $k => $lb): $c = $t['m'][$k]['conf']; ?><a href="<?= e(match_url($t)) ?>" class="<?= $c >= ($k === 'o15' ? 75 : 65) ? 'hi' : '' ?>"><?= $lb ?> <?= $c ?>%</a><?php endforeach; endif; ?></td>
    </tr>
  <?php endforeach; ?></table></div>
  <?php endforeach; ?>
  <?php if (TELEGRAM_URL): ?><div class="cta-tg"><div><b>Θες τα tips κάθε πρωί στο κινητό;</b><p>Δωρεάν κανάλι Telegram με τις ισχυρότερες επιλογές και τα αποτελέσματα.</p></div><a class="btn-tg" href="<?= e(TELEGRAM_URL) ?>" target="_blank" rel="noopener">Μπες στο κανάλι</a></div><?php endif; ?>
  <?= ad_slot(ADSENSE_SLOT_BOTTOM) ?>
</div>

<?php elseif ($page === 'results'): $tr = load_tracked();
  $settled = array_filter($tr, fn($r) => isset($r['res'])); uasort($settled, fn($a, $b) => $b['ts'] <=> $a['ts']); ?>
<div class="wrap page" style="max-width:900px">
  <h1>Ιστορικό επιτυχίας</h1>
  <p>Κάθε πρόταση που εμφανίζεται στο site καταγράφεται αυτόματα πριν τη σέντρα και ελέγχεται μετά το τέλος του αγώνα. Δεν σβήνουμε τίποτα.</p>
  <div style="overflow-x:auto"><table class="rtab" style="margin:18px 0"><tr><th>Αγορά</th><th>30 ημέρες</th><th>85%+</th><th>75–84%</th><th>60–74%</th><th>Συνολικά</th></tr>
  <?php foreach (MARKETS as $k => $info): $m30 = track_stats($tr, 30, 0, $k); $all = track_stats($tr, 3650, 0, $k); ?>
    <tr><td><b><?= e($info['label']) ?></b></td><td class="sc"><?= pct($m30['w'], $m30['n']) ?> <small style="color:var(--mut)">(<?= $m30['w'] ?>/<?= $m30['n'] ?>)</small></td>
    <?php foreach ($m30['tiers'] as $x): ?><td class="sc"><?= pct($x['w'], $x['n']) ?> <small style="color:var(--mut)">(<?= $x['n'] ?>)</small></td><?php endforeach; ?>
    <td class="sc"><?= pct($all['w'], $all['n']) ?> <small style="color:var(--mut)">(<?= $all['n'] ?>)</small></td></tr>
  <?php endforeach; ?></table></div>
  <h2>Τελευταίες προτάσεις</h2>
  <?php if (!$settled): ?><p>Οι πρώτες προτάσεις θα κριθούν με την επόμενη αγωνιστική.</p><?php else: ?>
  <div style="overflow-x:auto"><table class="rtab"><tr><th>Ημ/νία</th><th>Αγώνας</th><th>Αγορά</th><th>%</th><th>Σκορ</th><th></th></tr>
  <?php foreach (array_slice($settled, 0, 200, true) as $r): ?>
    <tr><td><?= date('d/m', $r['ts']) ?></td><td><?= e(gr_team($r['h'])) ?> – <?= e(gr_team($r['a'])) ?></td><td><?= e(MARKETS[$r['mk'] ?? 'o15']['label']) ?></td><td><?= $r['conf'] ?>%</td><td class="sc"><?= $r['res'][0] ?>-<?= $r['res'][1] ?></td><td class="<?= $r['hit'] ? 'ok' : 'no' ?>"><?= $r['hit'] ? '✓' : '✕' ?></td></tr>
  <?php endforeach; ?></table></div><?php endif; ?>
</div>

<?php elseif ($page === 'method'): ?>
<div class="wrap page">
  <h1>Πώς βγαίνουν τα προγνωστικά Over 1.5</h1>
  <p>Κάθε λίγες ώρες το σύστημα κατεβάζει όλα τα αποτελέσματα και το πρόγραμμα των πρωταθλημάτων που καλύπτουμε. Για κάθε επερχόμενο αγώνα κοιτάμε τα τελευταία 3 παιχνίδια πρωταθλήματος κάθε ομάδας.</p>
  <h2>1. Ποσοστό Over στα πρόσφατα</h2>
  <p>Μετράμε σε πόσα από τα 6 παιχνίδια (3 + 3) μπήκαν 2 ή περισσότερα γκολ.</p>
  <h2>2. Αναμενόμενα γκολ</h2>
  <p>Συνδυάζουμε τα γκολ που σκοράρει η κάθε ομάδα με τα γκολ που δέχεται ο αντίπαλος:</p>
  <div class="formula">λ = (Γκολ_υπέρ_Γηπ + Γκολ_κατά_Φιλ)/2 + (Γκολ_υπέρ_Φιλ + Γκολ_κατά_Γηπ)/2<br>P(2+ γκολ) = 1 − e^(−λ)·(1 + λ)</div>
  <h2>3. Τελικό σκορ</h2>
  <div class="formula">Βαθμός = 50% × (Over/6) + 50% × P(2+ γκολ)</div>
  <ul><li><b>85%+</b> Κορυφαία επιλογή</li><li><b>75–84%</b> Ισχυρή</li><li><b>60–74%</b> Καλή</li></ul>
  <h2>Over 2.5 και Γκολ-Γκολ</h2>
  <p>Με την ίδια λογική: για το <b>Over 2.5</b> μετράμε τα πρόσφατα παιχνίδια με 3+ γκολ και την πιθανότητα Poisson για 3+ γκολ. Για το <b>Γκολ-Γκολ</b> μετράμε σε πόσα παιχνίδια σκόραραν και οι δύο ομάδες και την πιθανότητα να σκοράρει η καθεμία ξεχωριστά (P γηπεδούχος σκοράρει × P φιλοξενούμενος σκοράρει).</p>
  <h2>Δίκαιη απόδοση</h2>
  <p>Είναι 100 / Βαθμός. Αν ο bookmaker δίνει μεγαλύτερη απόδοση από αυτή, το στοίχημα έχει θετική αξία με βάση το μοντέλο.</p>
  <h2>Περιορισμοί</h2>
  <p>Τα 3 παιχνίδια είναι μικρό δείγμα, δεν λαμβάνονται υπόψη τραυματισμοί, κίνητρα ή καιρός. Κανένα προγνωστικό δεν είναι σίγουρο.</p>
</div>

<?php elseif ($page === 'privacy'): ?>
<div class="wrap page">
  <h1>Πολιτική Απορρήτου & Cookies</h1>
  <p>Το <?= e(SITE_NAME) ?> δεν ζητά εγγραφή και δεν συλλέγει προσωπικά στοιχεία απευθείας.</p>
  <h2>Cookies τρίτων</h2>
  <p>Χρησιμοποιούμε Google AdSense για διαφημίσεις και Google Analytics για στατιστικά επισκεψιμότητας. Η Google και οι συνεργάτες της μπορεί να χρησιμοποιούν cookies για την προβολή διαφημίσεων βάσει προηγούμενων επισκέψεών σας. Μπορείτε να απενεργοποιήσετε τις εξατομικευμένες διαφημίσεις στο <a href="https://adssettings.google.com" style="text-decoration:underline">adssettings.google.com</a>.</p>
  <h2>Σύνδεσμοι συνεργατών</h2>
  <p>Ορισμένοι σύνδεσμοι είναι συνεργατικοί (affiliate). Αν εγγραφείτε μέσω αυτών μπορεί να λάβουμε προμήθεια χωρίς επιπλέον κόστος για εσάς.</p>
  <h2>Επικοινωνία</h2>
  <p><?= e(SITE_EMAIL) ?></p>
</div>

<?php else: ?>
<div class="wrap page">
  <h1>Όροι Χρήσης & Υπεύθυνο Παιχνίδι</h1>
  <p>Το περιεχόμενο είναι αποκλειστικά ενημερωτικό και βασίζεται σε στατιστικά. Δεν αποτελεί συμβουλή ή εγγύηση αποτελέσματος.</p>
  <h2>18+</h2>
  <p>Το site απευθύνεται μόνο σε ενήλικες. Η συμμετοχή σε τυχερά παίγνια επιτρέπεται μόνο σε άτομα άνω των 21 ετών στην Ελλάδα και μόνο σε εταιρείες αδειοδοτημένες από την ΕΕΕΠ.</p>
  <h2>Παίξε υπεύθυνα</h2>
  <ul><li>Βάλε όριο χρημάτων και χρόνου πριν ξεκινήσεις.</li><li>Μην κυνηγάς τις απώλειες.</li><li>Ο τζόγος δεν είναι τρόπος βιοπορισμού.</li></ul>
  <p>Αν χρειάζεσαι βοήθεια: ΚΕΘΕΑ ΑΛΦΑ — Γραμμή βοήθειας για τον τζόγο 1114.</p>
</div>
<?php endif; ?>

<footer><div class="wrap">
  <div class="ft">
    <div class="rg"><span class="r18">21+</span><span>Παίξε υπεύθυνα. Το στοίχημα ενέχει κίνδυνο εθισμού και απώλειας περιουσίας. Γραμμή βοήθειας: 1114.</span></div>
    <nav><a href="?p=live">Live score</a><a href="?p=over25">Over 2.5</a><a href="?p=gg">Γκολ-Γκολ</a><a href="?p=results">Ιστορικό</a><a href="?p=method">Μεθοδολογία</a><a href="?p=terms">Όροι & Υπεύθυνο Παιχνίδι</a><a href="?p=privacy">Απόρρητο</a></nav>
  </div>
  <p class="legal">© <?= date('Y') ?> <?= e(SITE_NAME) ?> · Τα προγνωστικά είναι στατιστικές εκτιμήσεις, όχι εγγυήσεις. Δεδομένα: football-data.org · Ορισμένοι σύνδεσμοι είναι διαφημιστικοί.</p>
</div></footer>

<?php if (TELEGRAM_URL): ?><div id="tgbar"><span>📲 Δωρεάν tips κάθε πρωί</span><a href="<?= e(TELEGRAM_URL) ?>" target="_blank" rel="noopener">Telegram</a></div><?php endif; ?>
<div id="ck"><span>Χρησιμοποιούμε cookies για διαφημίσεις και στατιστικά. <a href="?p=privacy">Περισσότερα</a></span><button id="ckok">Αποδοχή</button></div>

<script>
(function(){
  var list=document.getElementById('list'); if(!list) return initCk();
  var st={d:'all',l:'all',m:+document.getElementById('mn').value};
  function apply(){
    var n=0;
    list.querySelectorAll('.card').forEach(function(c){
      var ok=(st.d==='all'||c.dataset.d===st.d)&&(st.l==='all'||c.dataset.l===st.l)&&(+c.dataset.c>=st.m);
      c.style.display=ok?'':'none'; if(ok)n++;
    });
    document.getElementById('cnt').textContent=n+' αγώνες ταιριάζουν στα φίλτρα';
  }
  document.querySelectorAll('#days .chip').forEach(function(b){b.onclick=function(){
    document.querySelectorAll('#days .chip').forEach(function(x){x.classList.remove('on')});b.classList.add('on');st.d=b.dataset.d;apply();}});
  document.getElementById('lg').onchange=function(){st.l=this.value;apply()};
  document.getElementById('mn').onchange=function(){st.m=+this.value;apply()};
  apply(); initCk();
  function initCk(){try{if(!localStorage.getItem('ck')){var k=document.getElementById('ck');k.style.display='flex';document.getElementById('ckok').onclick=function(){localStorage.setItem('ck',1);k.style.display='none'}}}catch(e){}}
})();
</script>
<script>
/* Live σκορ: ρωτά το /api/live κάθε λεπτό όσο κάποιος αγώνας της σελίδας είναι σε εξέλιξη */
(function(){
  var els=[].slice.call(document.querySelectorAll('[data-id][data-ts]')); if(!els.length||!window.fetch) return;
  function hit(mk,h,a){return mk==='o25'?h+a>=3:(mk==='gg'?(h>0&&a>0):h+a>=2)}
  function want(){var n=Date.now()/1e3;return els.some(function(e){var t=+e.dataset.ts;return e.dataset.done!=='1'&&n>t-300&&n<t+3*3600})}
  function upd(d){els.forEach(function(e){
    var m=d.m[e.dataset.id]; if(!m||m[1]==null) return;
    var st=m[0],h=m[1],a=m[2],on=st==='IN_PLAY'||st==='PAUSED',fin=st==='FINISHED',min=m[3]?m[3]+"' ":'';
    if(!on&&!fin) return; if(fin) e.dataset.done='1';
    if(e.classList.contains('lv')){var ok=hit(e.dataset.mk,h,a);e.hidden=false;
      if(fin){e.className='lv '+(ok?'win':'lose');e.textContent='Τελικό '+h+'-'+a+(ok?' ✅':' ❌')}
      else{e.className='lv on';e.textContent=(st==='PAUSED'?'Ημίχρ. ':'LIVE '+min)+h+'-'+a+(ok?' ✅':'')}
    }else{var s=e.querySelector('.s b'),t=e.querySelector('.t');if(s)s.textContent=h+' - '+a;
      e.classList.toggle('lvon',on);if(t)t.textContent=fin?'Τελ.':(st==='PAUSED'?'Ημ.':(m[3]?m[3]+"'":'LIVE'))}
  })}
  function go(){
    if(want()&&!document.hidden) fetch('/api/live').then(function(r){return r.ok?r.json():null}).then(function(d){if(d&&d.m)upd(d)}).catch(function(){});
    setTimeout(go,60000);
  }
  go();
})();
</script>
</body>
</html>
