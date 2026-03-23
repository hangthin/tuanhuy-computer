<?php require_once __DIR__.'/layout_top.php'; ?>
<style>
.st-kpi{background:#1a1a1a;border:1px solid #222;border-radius:12px;padding:1.1rem 1.2rem;display:flex;flex-direction:column;gap:.45rem;transition:border-color .2s}
.st-kpi:hover{border-color:#333}
.st-kpi-lbl{font-size:.68rem;color:#444;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.st-kpi-val{font-size:1.5rem;font-weight:900;line-height:1}
.st-kpi-sub{font-size:.72rem;color:#555}
.st-section{background:#1a1a1a;border:1px solid #222;border-radius:12px;padding:1.1rem}
.st-sec-title{font-size:.82rem;font-weight:700;color:#ddd;margin-bottom:.9rem;display:flex;align-items:center;gap:.4rem}
.st-sec-title i{color:#444;font-size:.75rem}
.st-mbar-wrap{background:#111;border-radius:99px;height:6px;overflow:hidden;flex:1}
.st-mbar{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--red),#f97316);transition:width .6s ease}
.st-mrow{display:grid;grid-template-columns:52px 1fr 90px 90px 120px;gap:.4rem;align-items:center;padding:.42rem .6rem;border-radius:7px;font-size:.77rem;transition:background .15s}
.st-mrow:hover{background:rgba(255,255,255,.025)}
.st-mrow-head{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#333;padding:.3rem .6rem;grid-template-columns:52px 1fr 90px 90px 120px}
</style>

<?php
$sm = $stats;
$today_rev  = $sm['today']['rev']  ?? 0;
$today_cnt  = $sm['today']['cnt']  ?? 0;
$month_rev  = $sm['month']['rev']  ?? 0;
$month_cnt  = $sm['month']['cnt']  ?? 0;
$year_rev   = $sm['year']['rev']   ?? 0;
$year_cnt   = $sm['year']['cnt']   ?? 0;
$total_rev  = $sm['total']['rev']  ?? 0;
$total_cnt  = $sm['total']['cnt']  ?? 0;

// Build month arrays
$months12 = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
$revArr   = array_fill(0, 12, 0);
$cntArr   = array_fill(0, 12, 0);
foreach ($yearlyData as $r) {
    $revArr[$r['m']-1] = (float)$r['rev'];
    $cntArr[$r['m']-1] = (int)$r['cnt'];
}
$maxRev = max(max($revArr), 1);
$curMonth = (int)date('n');

// Average per order (year)
$avg_per_order = $year_cnt > 0 ? round($year_rev / $year_cnt) : 0;

// Category chart data
$catNames = []; $catVals = [];
foreach ($topCats as $tc) { $catNames[] = $tc['name']; $catVals[] = (int)$tc['total_sold']; }
$totalCatSold = array_sum($catVals) ?: 1;
?>

<!-- KPI Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1rem">
  <div class="st-kpi">
    <div class="st-kpi-lbl">Hôm nay</div>
    <div class="st-kpi-val" style="color:var(--red)"><?= formatPrice($today_rev) ?></div>
    <div class="st-kpi-sub"><?= $today_cnt ?> đơn hàng</div>
  </div>
  <div class="st-kpi">
    <div class="st-kpi-lbl">Tháng <?= date('n/Y') ?></div>
    <div class="st-kpi-val" style="color:#60a5fa"><?= formatPrice($month_rev) ?></div>
    <div class="st-kpi-sub"><?= $month_cnt ?> đơn hàng</div>
  </div>
  <div class="st-kpi">
    <div class="st-kpi-lbl">Năm <?= date('Y') ?></div>
    <div class="st-kpi-val" style="color:#4ade80"><?= formatPrice($year_rev) ?></div>
    <div class="st-kpi-sub"><?= $year_cnt ?> đơn · TB <?= formatPrice($avg_per_order) ?>/đơn</div>
  </div>
  <div class="st-kpi">
    <div class="st-kpi-lbl">Tổng tích lũy</div>
    <div class="st-kpi-val" style="color:#fbbf24"><?= formatPrice($total_rev) ?></div>
    <div class="st-kpi-sub"><?= number_format($total_cnt) ?> đơn hàng</div>
  </div>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:1fr 300px;gap:.75rem;margin-bottom:.75rem">

  <!-- Line chart -->
  <div class="st-section">
    <div class="st-sec-title"><i class="fa-solid fa-chart-line"></i> Doanh thu &amp; đơn hàng theo tháng — <?= date('Y') ?></div>
    <div style="position:relative;height:220px">
      <canvas id="lineChart"></canvas>
    </div>
  </div>

  <!-- Category doughnut -->
  <div class="st-section">
    <div class="st-sec-title"><i class="fa-solid fa-chart-pie"></i> Bán theo danh mục</div>
    <div style="position:relative;height:150px">
      <canvas id="doughnutChart"></canvas>
    </div>
    <div style="margin-top:.65rem;display:flex;flex-direction:column;gap:.15rem">
      <?php
      $catColors = ['#E30000','#f97316','#fbbf24','#4ade80','#60a5fa','#a78bfa'];
      foreach (array_slice($topCats, 0, 6) as $i => $tc):
        $pct = round($tc['total_sold'] / $totalCatSold * 100);
      ?>
      <div style="display:flex;align-items:center;gap:.5rem;padding:.2rem 0">
        <span style="width:7px;height:7px;border-radius:50%;background:<?= $catColors[$i] ?>;flex-shrink:0"></span>
        <span style="font-size:.72rem;color:#777;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($tc['name']) ?></span>
        <span style="font-size:.7rem;color:#555"><?= $pct ?>%</span>
        <span style="font-size:.7rem;color:#ddd;font-weight:600;min-width:36px;text-align:right"><?= $tc['total_sold'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Monthly Table -->
<div class="st-section">
  <div class="st-sec-title"><i class="fa-solid fa-table-list"></i> Chi tiết theo tháng — <?= date('Y') ?></div>
  <!-- Header -->
  <div class="st-mrow st-mrow-head" style="display:grid">
    <span>Tháng</span><span>Biểu đồ</span><span style="text-align:right">Đơn hàng</span><span style="text-align:right">TB/đơn</span><span style="text-align:right">Doanh thu</span>
  </div>
  <?php
  $hasData = false;
  foreach ($yearlyData as $r):
    $pct  = round($r['rev'] / $maxRev * 100);
    $avg  = $r['cnt'] > 0 ? round($r['rev'] / $r['cnt']) : 0;
    $isCur = (int)$r['m'] === $curMonth;
    $hasData = true;
  ?>
  <div class="st-mrow" style="display:grid;<?= $isCur ? 'background:rgba(227,0,0,.04);border:1px solid rgba(227,0,0,.12);' : '' ?>">
    <span style="font-weight:700;color:<?= $isCur ? 'var(--red)' : '#ddd' ?>"><?= $months12[$r['m']-1] ?><?= $isCur ? ' ●' : '' ?></span>
    <div style="display:flex;align-items:center;gap:.4rem">
      <div class="st-mbar-wrap"><div class="st-mbar" style="width:<?= $pct ?>%"></div></div>
      <span style="font-size:.65rem;color:#444;width:28px;text-align:right"><?= $pct ?>%</span>
    </div>
    <span style="text-align:right;color:#60a5fa;font-weight:600"><?= $r['cnt'] ?></span>
    <span style="text-align:right;color:#777;font-size:.74rem"><?= formatPrice($avg) ?></span>
    <span style="text-align:right;color:#4ade80;font-weight:700"><?= formatPrice($r['rev']) ?></span>
  </div>
  <?php endforeach; ?>
  <?php if (!$hasData): ?>
  <div style="text-align:center;padding:2rem;color:#444;font-size:.82rem">Chưa có dữ liệu doanh thu năm <?= date('Y') ?></div>
  <?php endif; ?>
  <!-- Total row -->
  <?php if ($year_cnt > 0): ?>
  <div style="display:grid;grid-template-columns:52px 1fr 90px 90px 120px;gap:.4rem;align-items:center;padding:.5rem .6rem;border-top:1px solid #2a2a2a;margin-top:.2rem">
    <span style="font-size:.68rem;color:#444;font-weight:700;text-transform:uppercase">Tổng</span>
    <span></span>
    <span style="text-align:right;color:#60a5fa;font-weight:700"><?= $year_cnt ?></span>
    <span style="text-align:right;color:#777;font-size:.74rem"><?= formatPrice($avg_per_order) ?></span>
    <span style="text-align:right;color:#4ade80;font-weight:800;font-size:.88rem"><?= formatPrice($year_rev) ?></span>
  </div>
  <?php endif; ?>
</div>

<script>
<?php
$revMil  = array_map(function($v){ return round($v/1000000, 2); }, $revArr);
$revFull = $revArr; // raw for tooltip
?>
// Line chart
(function(){
  var labels = <?= json_encode($months12) ?>;
  var revMil = <?= json_encode($revMil) ?>;
  var cntArr = <?= json_encode($cntArr) ?>;
  var ctx = document.getElementById('lineChart');
  if(!ctx) return;
  new Chart(ctx.getContext('2d'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Doanh thu (triệu đ)',
          data: revMil,
          borderColor: '#E30000',
          backgroundColor: 'rgba(227,0,0,.07)',
          fill: true,
          tension: .4,
          pointBackgroundColor: '#E30000',
          pointRadius: 4,
          pointHoverRadius: 6,
          borderWidth: 2,
          yAxisID: 'y'
        },
        {
          label: 'Số đơn',
          data: cntArr,
          borderColor: 'rgba(96,165,250,.8)',
          backgroundColor: 'transparent',
          fill: false,
          tension: .4,
          pointBackgroundColor: '#60a5fa',
          pointRadius: 3,
          pointHoverRadius: 5,
          borderWidth: 1.5,
          borderDash: [4,3],
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 600 },
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { labels: { color: '#555', font: { size: 10 }, boxWidth: 10, padding: 10 } },
        tooltip: {
          backgroundColor: '#1a1a1a',
          borderColor: '#2a2a2a',
          borderWidth: 1,
          titleColor: '#888',
          bodyColor: '#ddd',
          padding: 10
        }
      },
      scales: {
        y:  { grid: { color: 'rgba(255,255,255,.03)' }, ticks: { color: '#444', font: { size: 10 } }, border: { dash: [4,4] } },
        y1: { position: 'right', grid: { display: false }, ticks: { color: '#444', font: { size: 10 } } },
        x:  { grid: { display: false }, ticks: { color: '#444', font: { size: 10 } } }
      }
    }
  });
})();

// Doughnut chart
(function(){
  var ctx = document.getElementById('doughnutChart');
  if(!ctx) return;
  new Chart(ctx.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($catNames) ?>,
      datasets: [{
        data: <?= json_encode($catVals) ?>,
        backgroundColor: ['#E30000','#f97316','#fbbf24','#4ade80','#60a5fa','#a78bfa'],
        borderWidth: 0,
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      animation: { duration: 500 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1a1a1a',
          borderColor: '#2a2a2a',
          borderWidth: 1,
          titleColor: '#888',
          bodyColor: '#ddd'
        }
      }
    }
  });
})();

// Animate bars on scroll-in
document.querySelectorAll('.st-mbar').forEach(function(b){
  var w = b.style.width; b.style.width='0';
  requestAnimationFrame(function(){ requestAnimationFrame(function(){ b.style.width=w; }); });
});
</script>
<?php require_once __DIR__.'./layout_bottom.php'; ?>
