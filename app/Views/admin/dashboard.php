<?php require_once __DIR__.'/layout_top.php';
$today=$stats['today']; $month=$stats['month']; $year=$stats['year']; $total=$stats['total'];
$todayRev = (float)($today['rev'] ?? 0);
$todayCnt = (int)($today['cnt']   ?? 0);
$yestRev  = (float)($yesterday['rev'] ?? 0);
$yestCnt  = (int)($yesterday['cnt']   ?? 0);
$revPct   = $yestRev > 0 ? round(($todayRev - $yestRev) / $yestRev * 100, 1) : ($todayRev > 0 ? 100 : 0);
$cntPct   = $yestCnt > 0 ? round(($todayCnt - $yestCnt) / $yestCnt * 100, 1) : ($todayCnt > 0 ? 100 : 0);
$statusMap=array('pending'=>array('Chờ XN','bdg-pending'),'confirmed'=>array('Đã XN','bdg-confirmed'),'processing'=>array('Xử lý','bdg-processing'),'shipping'=>array('Đang giao','bdg-shipping'),'delivered'=>array('Đã giao','bdg-delivered'),'cancelled'=>array('Đã hủy','bdg-cancelled'));
?>

<!-- Quick actions bar -->
<div style="display:flex;gap:.5rem;margin-bottom:.85rem;flex-wrap:wrap;align-items:center">
  <?php if ($pendingCount > 0): ?>
  <button id="btn-confirm-all" class="btn-r" style="font-size:.75rem;padding:.3rem .75rem;display:flex;align-items:center;gap:.4rem">
    <i class="fa-solid fa-circle-check"></i> Xác nhận tất cả đơn chờ
    <span style="background:rgba(255,255,255,.15);border-radius:99px;padding:0 .4rem;font-size:.68rem"><?= $pendingCount ?></span>
  </button>
  <?php endif; ?>
  <?php if (!empty($outOfStock)): ?>
  <a href="<?= APP_URL ?>/admin/inventory?status=out" class="btn-r" style="font-size:.75rem;padding:.3rem .75rem;background:#1a1a1a;border:1px solid rgba(248,113,113,.3);color:#f87171;text-decoration:none;display:flex;align-items:center;gap:.4rem">
    <i class="fa-solid fa-box-open"></i> Xem kho hết hàng
    <span style="background:rgba(248,113,113,.2);border-radius:99px;padding:0 .4rem;font-size:.68rem"><?= count($outOfStock) ?></span>
  </a>
  <?php else: ?>
  <a href="<?= APP_URL ?>/admin/inventory?status=out" class="btn-r" style="font-size:.75rem;padding:.3rem .75rem;background:#1a1a1a;border:1px solid #222;color:#555;text-decoration:none;display:flex;align-items:center;gap:.4rem">
    <i class="fa-solid fa-box-open"></i> Xem kho hết hàng
  </a>
  <?php endif; ?>
  <span id="refresh-indicator" style="font-size:.68rem;color:#333;margin-left:.25rem"><i class="fa-solid fa-rotate-right"></i> tự động làm mới</span>
</div>

<!-- STAT CARDS -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.75rem;margin-bottom:1rem">

  <!-- Today revenue — auto-refresh -->
  <div class="stat-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
      <span style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Doanh thu hôm nay</span>
      <i class="fa-solid fa-sack-dollar" style="font-size:1rem;color:#555"></i>
    </div>
    <div id="kpi-today-rev" style="font-size:1.35rem;font-weight:900;color:var(--red)"><?= formatPrice($todayRev) ?></div>
    <div style="font-size:.7rem;margin-top:.25rem;display:flex;align-items:center;gap:.3rem">
      <?php $pc=$revPct; $up=$pc>=0; ?>
      <span style="color:<?= $up?'#4ade80':'#f87171' ?>;font-weight:700"><?= $up?'▲':'▼' ?> <?= abs($pc) ?>%</span>
      <span style="color:#333">vs hôm qua</span>
    </div>
  </div>

  <!-- Today orders — auto-refresh -->
  <div class="stat-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
      <span style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Đơn hàng hôm nay</span>
      <i class="fa-solid fa-box" style="font-size:1rem;color:#555"></i>
    </div>
    <div id="kpi-today-cnt" style="font-size:1.35rem;font-weight:900;color:#60a5fa"><?= $todayCnt ?></div>
    <div style="font-size:.7rem;margin-top:.25rem;display:flex;align-items:center;gap:.3rem">
      <?php $pc=$cntPct; $up=$pc>=0; ?>
      <span style="color:<?= $up?'#4ade80':'#f87171' ?>;font-weight:700"><?= $up?'▲':'▼' ?> <?= abs($pc) ?>%</span>
      <span style="color:#333">vs hôm qua</span>
    </div>
  </div>

  <!-- Pending — auto-refresh, red if has >2hr orders -->
  <div class="stat-card" id="kpi-pending-card" style="<?= $pendingOldCnt > 0 ? 'border-color:rgba(248,113,113,.35)' : '' ?>">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
      <span style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Đơn chờ xác nhận</span>
      <i class="fa-solid fa-hourglass-half" style="font-size:1rem;color:#555"></i>
    </div>
    <div style="display:flex;align-items:baseline;gap:.5rem">
      <span id="kpi-pending-cnt" style="font-size:1.35rem;font-weight:900;color:<?= $pendingOldCnt > 0 ? '#f87171' : '#fbbf24' ?>"><?= $pendingCount ?></span>
      <?php if ($pendingOldCnt > 0): ?>
      <span id="kpi-pending-old" style="font-size:.65rem;background:rgba(248,113,113,.15);color:#f87171;padding:1px 5px;border-radius:99px"><?= $pendingOldCnt ?> &gt;2h</span>
      <?php else: ?>
      <span id="kpi-pending-old" style="display:none"></span>
      <?php endif; ?>
    </div>
    <div style="font-size:.7rem;color:#444;margin-top:.25rem">đơn đang chờ xử lý</div>
  </div>

  <!-- Customers -->
  <div class="stat-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem"><span style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Khách hàng</span><i class="fa-solid fa-users" style="font-size:1rem;color:#555"></i></div>
    <div style="font-size:1.35rem;font-weight:900;color:#4ade80"><?= number_format($totalCustomers) ?></div>
  </div>

  <!-- Products -->
  <div class="stat-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem"><span style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Sản phẩm</span><i class="fa-solid fa-bag-shopping" style="font-size:1rem;color:#555"></i></div>
    <div style="font-size:1.35rem;font-weight:900;color:#fbbf24"><?= number_format($totalProducts) ?></div>
  </div>

  <!-- Month rev -->
  <div class="stat-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem"><span style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px">DT tháng này</span><i class="fa-solid fa-calendar-days" style="font-size:1rem;color:#555"></i></div>
    <div style="font-size:1.35rem;font-weight:900;color:#a78bfa"><?= formatPrice($month['rev']??0) ?></div>
  </div>

  <!-- Year rev -->
  <div class="stat-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem"><span style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px">DT cả năm</span><i class="fa-solid fa-chart-column" style="font-size:1rem;color:#555"></i></div>
    <div style="font-size:1.35rem;font-weight:900;color:#f97316"><?= formatPrice($year['rev']??0) ?></div>
  </div>
</div>

<!-- CHARTS ROW: 12-month bar | 7-day line -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.9rem;margin-bottom:.9rem">
  <div class="card" style="padding:1.1rem">
    <div style="font-weight:700;font-size:.82rem;color:#fff;margin-bottom:.9rem">Doanh thu <?= date('Y') ?> (triệu đ)</div>
    <div style="position:relative;height:180px"><canvas id="revenueChart"></canvas></div>
  </div>
  <div class="card" style="padding:1.1rem">
    <div style="font-weight:700;font-size:.82rem;color:#fff;margin-bottom:.9rem">7 ngày gần nhất (triệu đ)</div>
    <div style="position:relative;height:180px"><canvas id="week7Chart"></canvas></div>
  </div>
</div>

<!-- TOP PRODUCTS + RECENT ORDERS + STOCK -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:.9rem;margin-bottom:.9rem">
  <div class="card" style="padding:1.1rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem">
      <span style="font-weight:700;font-size:.875rem;color:#fff">Đơn hàng gần đây</span>
      <a href="<?= APP_URL ?>/admin/orders" style="font-size:.75rem;color:var(--red);text-decoration:none">Xem tất cả →</a>
    </div>
    <div style="overflow-x:auto">
      <table class="adm-table">
        <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th></tr></thead>
        <tbody>
          <?php foreach($recentOrders as $o): ?>
          <tr onclick="window.location='<?= APP_URL ?>/admin/orders/detail?id=<?= $o['id'] ?>'" style="cursor:pointer">
            <td style="color:var(--red);font-weight:600"><?= $o['order_code'] ?></td>
            <td><?= htmlspecialchars($o['fullname']) ?></td>
            <td style="color:#4ade80;font-weight:700"><?= formatPrice($o['total']) ?></td>
            <td><span class="badge <?= isset($statusMap[$o['status']])?$statusMap[$o['status']][1]:'bdg-pending' ?>"><?= isset($statusMap[$o['status']])?$statusMap[$o['status']][0]:$o['status'] ?></span></td>
            <td style="color:#555;font-size:.75rem"><?= date('d/m H:i',strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top products -->
  <div class="card" style="padding:1.1rem">
    <span style="font-weight:700;font-size:.875rem;color:#fff;display:block;margin-bottom:.9rem"><i class="fa-solid fa-trophy" style="color:#fbbf24;margin-right:.3rem"></i> Top sản phẩm</span>
    <?php foreach($stats['top_products'] as $i=>$tp): ?>
    <div style="display:flex;align-items:center;gap:.5rem;padding:.35rem 0;border-bottom:1px solid #222">
      <span style="width:18px;height:18px;background:<?= $i===0?'var(--red)':($i===1?'#fbbf24':'#555') ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:#fff;flex-shrink:0"><?= $i+1 ?></span>
      <div style="flex:1;min-width:0"><div style="font-size:.73rem;color:#ddd;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars(mb_substr($tp['name'],0,26)) ?>...</div><div style="font-size:.68rem;color:#555">Bán: <?= $tp['total_sold'] ?></div></div>
      <span style="font-size:.7rem;color:#4ade80;font-weight:600"><?= number_format($tp['revenue']/1000000,1) ?>M</span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- STOCK ALERTS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:.9rem">
  <!-- Out of stock (red) -->
  <div class="card" style="padding:1.1rem;<?= !empty($outOfStock) ? 'border-color:rgba(248,113,113,.2)' : '' ?>">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
      <span style="font-weight:700;font-size:.875rem;color:#f87171"><i class="fa-solid fa-circle-xmark" style="margin-right:.3rem"></i>Hết hàng (<?= count($outOfStock) ?>)</span>
      <a href="<?= APP_URL ?>/admin/inventory?status=out" style="font-size:.72rem;color:#555;text-decoration:none">Quản lý →</a>
    </div>
    <?php if (empty($outOfStock)): ?>
    <div style="color:#333;font-size:.78rem;padding:.5rem 0">Không có sản phẩm hết hàng <i class="fa-solid fa-check" style="color:#4ade80"></i></div>
    <?php else: foreach($outOfStock as $ls): ?>
    <div style="padding:.35rem 0;border-bottom:1px solid #1e1e1e;display:flex;justify-content:space-between;font-size:.78rem">
      <span style="color:#ccc;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:75%"><?= htmlspecialchars(mb_strlen($ls['name'])>24 ? mb_substr($ls['name'],0,22).'…' : $ls['name']) ?></span>
      <span style="color:#f87171;font-weight:700">0</span>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Low stock (yellow) -->
  <div class="card" style="padding:1.1rem;<?= !empty($lowStockItems) ? 'border-color:rgba(251,191,36,.2)' : '' ?>">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
      <span style="font-weight:700;font-size:.875rem;color:#fbbf24"><i class="fa-solid fa-triangle-exclamation" style="margin-right:.3rem"></i>Sắp hết (<?= count($lowStockItems) ?>)</span>
      <a href="<?= APP_URL ?>/admin/inventory?status=low" style="font-size:.72rem;color:#555;text-decoration:none">Quản lý →</a>
    </div>
    <?php if (empty($lowStockItems)): ?>
    <div style="color:#333;font-size:.78rem;padding:.5rem 0">Kho ổn định <i class="fa-solid fa-check" style="color:#4ade80"></i></div>
    <?php else: foreach($lowStockItems as $ls): ?>
    <div style="padding:.35rem 0;border-bottom:1px solid #1e1e1e;display:flex;justify-content:space-between;font-size:.78rem">
      <span style="color:#ccc;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:75%"><?= htmlspecialchars(mb_strlen($ls['name'])>24 ? mb_substr($ls['name'],0,22).'…' : $ls['name']) ?></span>
      <span style="color:#fbbf24;font-weight:700"><?= $ls['stock_quantity'] ?></span>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<script>
<?php
$months  = array('T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12');
$revData = array_fill(0, 12, 0);
foreach ($stats['monthly_chart'] as $r) $revData[$r['m']-1] = round($r['rev']/1000000, 1);
$tooltipDefaults = "{backgroundColor:'#1a1a1a',borderColor:'#333',borderWidth:1,titleColor:'#888',bodyColor:'#ddd'}";
?>
// 12-month bar chart
(function(){
  var ctx=document.getElementById('revenueChart');
  if(!ctx) return;
  new Chart(ctx.getContext('2d'),{
    type:'bar',
    data:{labels:<?= json_encode($months) ?>,datasets:[{label:'Triệu đ',data:<?= json_encode($revData) ?>,backgroundColor:'rgba(227,0,0,.65)',borderColor:'rgba(227,0,0,.9)',borderWidth:0,borderRadius:4,borderSkipped:false}]},
    options:{responsive:true,maintainAspectRatio:false,animation:{duration:500},
      plugins:{legend:{display:false},tooltip:<?= $tooltipDefaults ?>},
      scales:{y:{grid:{color:'rgba(255,255,255,.03)'},ticks:{color:'#444',font:{size:10}},border:{dash:[4,4]}},x:{grid:{display:false},ticks:{color:'#444',font:{size:10}}}}}
  });
})();

// 7-day line chart
(function(){
  var ctx=document.getElementById('week7Chart');
  if(!ctx) return;
  new Chart(ctx.getContext('2d'),{
    type:'line',
    data:{
      labels:<?= json_encode($last7Labels) ?>,
      datasets:[{
        label:'Triệu đ',data:<?= json_encode($last7Rev) ?>,
        borderColor:'#E30000',backgroundColor:'rgba(227,0,0,.08)',
        fill:true,tension:.35,pointBackgroundColor:'#E30000',
        pointRadius:4,pointHoverRadius:6,borderWidth:2
      }]
    },
    options:{responsive:true,maintainAspectRatio:false,animation:{duration:500},
      plugins:{legend:{display:false},tooltip:<?= $tooltipDefaults ?>},
      scales:{y:{grid:{color:'rgba(255,255,255,.03)'},ticks:{color:'#444',font:{size:10}},border:{dash:[4,4]}},x:{grid:{display:false},ticks:{color:'#444',font:{size:10}}}}}
  });
})();

var APP_URL = '<?= APP_URL ?>';

// Auto-refresh today stats every 60s
(function(){
  function fmtPrice(n) {
    n = parseInt(n)||0;
    if(n>=1000000) return (n/1000000).toFixed(1).replace('.0','')+'M đ';
    if(n>=1000)    return Math.round(n/1000)+'K đ';
    return n.toLocaleString('vi-VN')+' đ';
  }
  function refresh() {
    fetch(APP_URL+'/admin/api/dashboard-stats')
      .then(function(r){return r.json();})
      .then(function(d) {
        if (!d.success) return;
        document.getElementById('kpi-today-rev').textContent = fmtPrice(d.today_rev);
        document.getElementById('kpi-today-cnt').textContent = d.today_cnt;
        var pEl  = document.getElementById('kpi-pending-cnt');
        var pOld = document.getElementById('kpi-pending-old');
        var card = document.getElementById('kpi-pending-card');
        if (pEl) {
          pEl.textContent = d.pending_cnt;
          pEl.style.color = d.pending_old > 0 ? '#f87171' : '#fbbf24';
        }
        if (pOld) {
          if (d.pending_old > 0) {
            pOld.textContent = d.pending_old + ' >2h';
            pOld.style.display = 'inline';
          } else {
            pOld.style.display = 'none';
          }
        }
        if (card) card.style.borderColor = d.pending_old > 0 ? 'rgba(248,113,113,.35)' : '';
        // Flash indicator
        var ind = document.getElementById('refresh-indicator');
        if (ind) { ind.style.color='#4ade80'; setTimeout(function(){ ind.style.color='#333'; }, 1000); }
      })
      .catch(function(){});
  }
  setInterval(refresh, 60000);
})();

// Confirm all pending
var btnConfirm = document.getElementById('btn-confirm-all');
if (btnConfirm) {
  btnConfirm.addEventListener('click', function() {
    var cnt = parseInt(btnConfirm.querySelector('span').textContent) || 0;
    if (!confirm('Xác nhận tất cả '+cnt+' đơn đang chờ?')) return;
    btnConfirm.disabled = true;
    btnConfirm.textContent = 'Đang xử lý…';
    fetch(APP_URL+'/admin/api/confirm-all-pending', {method:'POST'})
      .then(function(r){return r.json();})
      .then(function(d) {
        if (d.success) { location.reload(); }
        else { btnConfirm.disabled=false; btnConfirm.textContent='Lỗi, thử lại'; }
      })
      .catch(function(){ btnConfirm.disabled=false; });
  });
}
</script>
<?php require_once __DIR__.'./layout_bottom.php'; ?>
