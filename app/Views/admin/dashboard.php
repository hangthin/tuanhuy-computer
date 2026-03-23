<?php require_once __DIR__.'/layout_top.php';
$today=$stats['today'];$month=$stats['month'];$year=$stats['year'];$total=$stats['total'];
$statusMap=array('pending'=>array('Chờ XN','bdg-pending'),'confirmed'=>array('Đã XN','bdg-confirmed'),'processing'=>array('Xử lý','bdg-processing'),'shipping'=>array('Đang giao','bdg-shipping'),'delivered'=>array('Đã giao','bdg-delivered'),'cancelled'=>array('Đã hủy','bdg-cancelled'));
?>
<!-- STAT CARDS -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr));gap:.9rem;margin-bottom:1.25rem">
<?php
$cards = array(
  array('💰','Doanh thu hôm nay',formatPrice($today['rev']??0),'var(--red)'),
  array('📦','Đơn hàng hôm nay',(int)($today['cnt']??0),'#60a5fa'),
  array('👥','Khách hàng',$totalCustomers,'#4ade80'),
  array('🛍️','Sản phẩm',$totalProducts,'#fbbf24'),
  array('📅','DT tháng này',formatPrice($month['rev']??0),'#a78bfa'),
  array('📊','DT cả năm',formatPrice($year['rev']??0),'#f97316'),
);
foreach($cards as $c): $ci=$c[0];$cl=$c[1];$cv=$c[2];$cc=$c[3];
?>
<div class="stat-card">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem"><span style="font-size:.7rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.4px"><?= $cl ?></span><span style="font-size:1.2rem"><?= $ci ?></span></div>
  <div style="font-size:1.4rem;font-weight:900;color:<?= $cc ?>"><?= is_numeric($cv)?number_format($cv):$cv ?></div>
</div>
<?php endforeach; ?>
</div>

<!-- CHARTS ROW -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:.9rem;margin-bottom:1.25rem">
  <div class="card" style="padding:1.1rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem">
      <span style="font-weight:700;font-size:.875rem;color:#fff">Doanh thu <?= date('Y') ?> (triệu đ)</span>
    </div>
    <div style="position:relative;height:200px">
      <canvas id="revenueChart"></canvas>
    </div>
  </div>
  <div class="card" style="padding:1.1rem">
    <span style="font-weight:700;font-size:.875rem;color:#fff;display:block;margin-bottom:.9rem">🏆 Top sản phẩm</span>
    <?php foreach($stats['top_products'] as $i=>$tp): ?>
    <div style="display:flex;align-items:center;gap:.5rem;padding:.35rem 0;border-bottom:1px solid #222">
      <span style="width:18px;height:18px;background:<?= $i===0?'var(--red)':($i===1?'#fbbf24':'#555') ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:700;color:#fff;flex-shrink:0"><?= $i+1 ?></span>
      <div style="flex:1;min-width:0"><div style="font-size:.73rem;color:#ddd;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars(mb_substr($tp['name'],0,26)) ?>...</div><div style="font-size:.68rem;color:#555">Bán: <?= $tp['total_sold'] ?></div></div>
      <span style="font-size:.7rem;color:#4ade80;font-weight:600"><?= number_format($tp['revenue']/1000000,1) ?>M</span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- RECENT ORDERS + LOW STOCK -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:.9rem">
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
  <div class="card" style="padding:1.1rem">
    <span style="font-weight:700;font-size:.875rem;color:#fff;display:block;margin-bottom:.9rem">⚠️ Sắp hết hàng</span>
    <?php foreach($lowStock as $ls): ?>
    <div style="padding:.4rem 0;border-bottom:1px solid #222;display:flex;justify-content:space-between;font-size:.78rem">
      <span style="color:#ccc;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%"><?= htmlspecialchars(mb_substr($ls['name'],0,22)) ?>...</span>
      <span style="color:<?= $ls['stock_quantity']<=0?'#f87171':'#fbbf24' ?>;font-weight:700"><?= $ls['stock_quantity'] ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
<?php
$months=array('T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12');
$revData=array_fill(0,12,0);
foreach($stats['monthly_chart'] as $r) $revData[$r['m']-1]=round($r['rev']/1000000,1);
?>
(function(){
  var ctx=document.getElementById('revenueChart');
  if(!ctx)return;
  new Chart(ctx.getContext('2d'),{
    type:'bar',
    data:{labels:<?= json_encode($months) ?>,datasets:[{label:'Triệu đ',data:<?= json_encode($revData) ?>,backgroundColor:'rgba(227,0,0,.65)',borderColor:'rgba(227,0,0,.9)',borderWidth:0,borderRadius:4,borderSkipped:false}]},
    options:{
      responsive:true,maintainAspectRatio:false,
      animation:{duration:500},
      plugins:{legend:{display:false},tooltip:{backgroundColor:'#1a1a1a',borderColor:'#333',borderWidth:1,titleColor:'#888',bodyColor:'#ddd',callbacks:{label:function(c){return ' '+c.parsed.y.toFixed(1)+' triệu đ';}}}},
      scales:{
        y:{grid:{color:'rgba(255,255,255,.03)'},ticks:{color:'#444',font:{size:10}},border:{dash:[4,4]}},
        x:{grid:{display:false},ticks:{color:'#444',font:{size:10}}}
      }
    }
  });
})();
</script>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
