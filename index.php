<?php
require_once __DIR__ . '/lib.php';

$page = $_GET['p'] ?? 'home';
if (!in_array($page, ['home', 'method', 'privacy', 'terms'], true)) $page = 'home';

$data = load_data();
$tips = $page === 'home' ? analyse($data) : [];
$top3 = array_slice($tips, 0, 3);
$updated = $data['fetched'] ? date('d/m H:i', $data['fetched']) : '—';
$totalUpcoming = 0;
$__now = gmdate('Y-m-d\TH:i:s\Z'); $__until = gmdate('Y-m-d\TH:i:s\Z', time() + DAYS_AHEAD * 86400);
foreach ($data['leagues'] as $ms) foreach ($ms as $m) if (in_array($m['status'], ['SCHEDULED', 'TIMED']) && $m['utc'] >= $__now && $m['utc'] <= $__until) $totalUpcoming++;

$titles = [
    'home'    => 'Over 1.5 Προγνωστικά Σήμερα — Στατιστικά Τελευταίων 3 Αγώνων | ' . SITE_NAME,
    'method'  => 'Πώς βγαίνουν τα προγνωστικά Over 1.5 | ' . SITE_NAME,
    'privacy' => 'Πολιτική Απορρήτου & Cookies | ' . SITE_NAME,
    'terms'   => 'Όροι Χρήσης & Υπεύθυνο Παιχνίδι | ' . SITE_NAME,
];

?><!DOCTYPE html>
<html lang="el">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titles[$page]) ?></title>
<meta name="description" content="Δωρεάν προγνωστικά Over 1.5 γκολ για σήμερα και αύριο. Ανάλυση των τελευταίων 3 αγώνων κάθε ομάδας σε Premier League, Serie A, La Liga, Bundesliga και άλλα.">
<link rel="canonical" href="<?= e(SITE_URL . ($page === 'home' ? '/' : '/?p=' . $page)) ?>">
<meta property="og:title" content="<?= e($titles[$page]) ?>">
<meta property="og:type" content="website">
<meta name="theme-color" content="#0a0e13">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<?php if (ADSENSE_CLIENT): ?><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= e(ADSENSE_CLIENT) ?>" crossorigin="anonymous"></script><?php endif; ?>
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
.wrap{max-width:1200px;margin:0 auto;padding:0 20px}

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
.main{display:grid;grid-template-columns:1fr 320px;gap:28px;padding:28px 0 60px}
@media(max-width:980px){.main{grid-template-columns:1fr}}
@media(max-width:600px){.hd{gap:12px}.logo{white-space:nowrap;font-size:16px}nav a{padding:6px 8px;font-size:13px}nav a+a{display:none}.btn-tg{padding:8px 10px}.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.stat{min-width:0;padding:10px 12px}.stat .v{font-size:18px}.stat .l{font-size:11px}.hero{padding:30px 0 22px}.wrap{padding:0 14px}}

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
.side{display:flex;flex-direction:column;gap:16px}
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

/* footer */
footer{border-top:1px solid var(--line);background:var(--bg2);padding:32px 0;font-size:13px;color:var(--mut)}
.ft{display:flex;flex-wrap:wrap;gap:24px;justify-content:space-between;align-items:flex-start}
.ft nav{flex:none}
.rg{display:flex;gap:12px;align-items:center;max-width:640px}
.r18{flex:none;width:40px;height:40px;border-radius:50%;border:2px solid var(--red);display:grid;place-items:center;font-weight:800;color:var(--red);font-size:13px}
.legal{margin-top:20px;font-size:11px;color:var(--mut2)}

/* cookies */
#ck{position:fixed;left:16px;right:16px;bottom:16px;max-width:560px;margin:auto;background:var(--card2);border:1px solid var(--line2);border-radius:12px;padding:16px;display:none;gap:12px;align-items:center;z-index:50;font-size:13px;box-shadow:0 20px 50px rgba(0,0,0,.5)}
#ck button{flex:none;padding:8px 16px;border-radius:8px;border:0;background:var(--acc);color:#04140b;font:800 13px Manrope,sans-serif;cursor:pointer}
#ck a{text-decoration:underline}
</style>
</head>
<body>
<header><div class="wrap hd">
  <a href="./" class="logo"><span class="logo-mark">1.5</span><span><?= preg_replace('/(\d[\d.]*)/', '<b>$1</b>', e(SITE_NAME)) ?></span></a>
  <nav>
    <a href="./" class="<?= $page === 'home' ? 'on' : '' ?>">Προγνωστικά</a>
    <a href="?p=method" class="<?= $page === 'method' ? 'on' : '' ?>">Μεθοδολογία</a>
  </nav>
  <?php if (TELEGRAM_URL): ?><a class="btn-tg" href="<?= e(TELEGRAM_URL) ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.8 15.3 9.6 19c.4 0 .6-.2.8-.4l2-1.9 4.1 3c.8.4 1.3.2 1.5-.7L20.9 5c.3-1.2-.4-1.7-1.2-1.4L3.4 9.9c-1.1.4-1.1 1.1-.2 1.4l4.2 1.3 9.7-6.1c.5-.3.9-.1.5.2"/></svg>Telegram</a><?php endif; ?>
</div></header>

<?php if ($page === 'home'): ?>
<section class="hero"><div class="wrap">
  <span class="eyebrow"><span class="dot"></span>Ενημέρωση <?= e($updated) ?></span>
  <h1>Over 1.5 γκολ — τα πιο σίγουρα παιχνίδια της αγωνιστικής</h1>
  <p class="sub">Αναλύουμε αυτόματα τα τελευταία 3 παιχνίδια κάθε ομάδας σε <?= count(LEAGUES) ?> κορυφαία πρωταθλήματα και βαθμολογούμε κάθε αγώνα για 2+ γκολ.</p>
  <div class="stats">
    <div class="stat"><div class="v"><?= $totalUpcoming ?></div><div class="l">Αγώνες αναλύθηκαν</div></div>
    <div class="stat"><div class="v"><?= count($tips) ?></div><div class="l">Προτάσεις Over 1.5</div></div>
    <div class="stat"><div class="v"><?= count(array_filter($tips, fn($t) => $t['conf'] >= 85)) ?></div><div class="l">Κορυφαίες (85%+)</div></div>
  </div>
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
      <select id="mn"><option value="60">60%+</option><option value="70" selected>70%+</option><option value="80">80%+</option><option value="85">85%+</option></select>
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
          <span class="badge <?= $tc ?>"><?= e($tl) ?></span>
        </div>
        <div class="c-body">
          <div class="team home"><?= crest($t['home']) ?><div><div class="nm"><?= e($t['home']['n']) ?></div><div class="hint">Γηπεδούχος</div></div></div>
          <div class="ring" style="--p:<?= $t['conf'] ?>;--rc:<?= $rc ?>"><div><span><b><?= $t['conf'] ?>%</b><small>OVER 1.5</small></span></div></div>
          <div class="team away"><?= crest($t['away']) ?><div><div class="nm"><?= e($t['away']['n']) ?></div><div class="hint">Φιλοξενούμενος</div></div></div>
        </div>
        <div class="form">
          <div><h4>Τελευταία 3 · <?= e($t['home']['n']) ?> <span><?= $t['H']['hits'] ?>/3</span></h4><ul><?= form_rows($t['H']) ?></ul></div>
          <div><h4>Τελευταία 3 · <?= e($t['away']['n']) ?> <span><?= $t['A']['hits'] ?>/3</span></h4><ul><?= form_rows($t['A']) ?></ul></div>
        </div>
        <div class="c-foot">
          <span>Over σε <b><?= $t['hits'] ?>/6</b></span>
          <span>Αναμ. γκολ <b><?= number_format($t['xg'], 2) ?></b></span>
          <span>Δίκαιη απόδοση <b><?= number_format($t['fair'], 2) ?></b></span>
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
      <p class="s">Οι 3 υψηλότερες επιλογές Over 1.5</p>
      <ul class="slip"><?php foreach ($top3 as $t): ?>
        <li><div><div class="t"><?= e($t['home']['n']) ?> – <?= e($t['away']['n']) ?></div><div class="m"><?= e(gr_day($t['ts'])) ?> <?= date('H:i', $t['ts']) ?> · <?= $t['conf'] ?>%</div></div><span class="o">Over 1.5</span></li>
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
    <nav><a href="?p=method">Μεθοδολογία</a><a href="?p=terms">Όροι & Υπεύθυνο Παιχνίδι</a><a href="?p=privacy">Απόρρητο</a></nav>
  </div>
  <p class="legal">© <?= date('Y') ?> <?= e(SITE_NAME) ?> · Τα προγνωστικά είναι στατιστικές εκτιμήσεις, όχι εγγυήσεις. Δεδομένα: football-data.org · Ορισμένοι σύνδεσμοι είναι διαφημιστικοί.</p>
</div></footer>

<div id="ck"><span>Χρησιμοποιούμε cookies για διαφημίσεις και στατιστικά. <a href="?p=privacy">Περισσότερα</a></span><button id="ckok">Αποδοχή</button></div>

<script>
(function(){
  var list=document.getElementById('list'); if(!list) return initCk();
  var st={d:'all',l:'all',m:70};
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
</body>
</html>
