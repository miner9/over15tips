<?php
// Πρότυπο εικόνας για social: php share.php story|post  (το build το κάνει PNG με headless Chrome)
require_once __DIR__ . '/lib.php';
$fmt = $argv[1] ?? ($_GET['f'] ?? 'story');
$W = 1080; $H = $fmt === 'post' ? 1350 : 1920;
$max = $fmt === 'post' ? 5 : 7;
$pick = pick_day_tips(analyse(load_data()), 75, $max);
$tips = $pick['tips'];
$st = track_stats(load_tracked(), 30, 70);
$tg = TELEGRAM_URL ? preg_replace('#^https?://#', '', TELEGRAM_URL) : '';
$site = preg_replace('#^https?://#', '', SITE_URL);
?><!DOCTYPE html><html lang="el"><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:<?= $W ?>px;height:<?= $H ?>px;overflow:hidden}
body{font-family:Manrope,sans-serif;color:#e9eef4;background:#0a0e13;
 background-image:radial-gradient(900px 600px at 0% 0%,rgba(43,213,118,.22),transparent 60%),radial-gradient(800px 600px at 100% 100%,rgba(34,158,217,.18),transparent 60%);
 padding:<?= $fmt === 'post' ? '70px 70px' : '120px 70px 110px' ?>;display:flex;flex-direction:column}
.top{display:flex;align-items:center;gap:18px}
.mark{width:76px;height:76px;border-radius:20px;background:linear-gradient(135deg,#2bd576,#13a35a);display:grid;place-items:center;font:700 28px 'JetBrains Mono','DejaVu Sans Mono',monospace;color:#04140b}
.brand{font-size:42px;font-weight:800;letter-spacing:-.02em}.brand b{color:#2bd576}
h1{font-size:<?= $fmt === 'post' ? 72 : 92 ?>px;line-height:1;font-weight:800;letter-spacing:-.03em;margin:<?= $fmt === 'post' ? '40px 0 8px' : '70px 0 10px' ?>}
h1 span{color:#2bd576}
.day{font-size:34px;font-weight:700;color:#8796a8;letter-spacing:.08em}
.list{margin-top:<?= $fmt === 'post' ? 34 : 54 ?>px;display:flex;flex-direction:column;gap:<?= $fmt === 'post' ? 14 : 20 ?>px;flex:1}
.row{display:flex;align-items:center;gap:22px;background:rgba(18,25,34,.92);border:2px solid #1f2a37;border-radius:24px;padding:<?= $fmt === 'post' ? '20px 26px' : '26px 30px' ?>}
.row.top3{border-color:rgba(244,183,64,.5)}
.t{flex:1;min-width:0}
.m{font-size:<?= $fmt === 'post' ? 32 : 36 ?>px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.s{font-size:24px;color:#8796a8;font-weight:600;margin-top:4px}
.p{font:700 <?= $fmt === 'post' ? 40 : 46 ?>px 'JetBrains Mono','DejaVu Sans Mono',monospace;color:#2bd576}
.row.top3 .p{color:#f4b740}
.trk{margin-top:26px;font-size:28px;color:#8796a8;font-weight:700}.trk b{color:#2bd576}
.cta{margin-top:<?= $fmt === 'post' ? 26 : 40 ?>px;background:#229ED9;border-radius:24px;padding:26px 30px;display:flex;justify-content:space-between;align-items:center}
.cta b{font-size:34px}.cta span{font-size:30px;font-weight:700;opacity:.95}
.foot{margin-top:22px;display:flex;justify-content:space-between;font-size:24px;color:#5d6b7c;font-weight:700}
.empty{font-size:40px;color:#8796a8;margin-top:80px}
</style></head><body>
<div class="top"><div class="mark">1.5</div><div class="brand">Over<b>15</b> Tips</div></div>
<h1>Προγνωστικά<br><span>Over 1.5</span> γκολ</h1>
<div class="day"><?= $pick['day'] ? e(gr_full_day(strtotime($pick['day']))) : '' ?></div>
<div class="list">
<?php if (!$tips): ?><div class="empty">Νέα προγνωστικά με την επόμενη αγωνιστική.</div><?php endif; ?>
<?php foreach ($tips as $t): ?>
  <div class="row <?= $t['conf'] >= 85 ? 'top3' : '' ?>">
    <div class="t"><div class="m"><?= e($t['home']['g']) ?> – <?= e($t['away']['g']) ?></div>
    <div class="s"><?= date('H:i', $t['ts']) ?> · <?= e($t['leagueName']) ?> · Over σε <?= $t['hits'] ?>/6</div></div>
    <div class="p"><?= $t['conf'] ?>%</div>
  </div>
<?php endforeach; ?>
</div>
<?php if ($st['n'] >= 10): ?><div class="trk">📈 Επιτυχία 30 ημερών: <b><?= pct($st['w'], $st['n']) ?></b> σε <?= $st['n'] ?> tips</div><?php endif; ?>
<?php if ($tg): ?><div class="cta"><b>Δωρεάν κάθε πρωί</b><span>Telegram: <?= e($tg) ?></span></div><?php endif; ?>
<div class="foot"><span><?= e($site) ?></span><span>21+ | Παίξε υπεύθυνα</span></div>
</body></html>
