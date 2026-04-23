<?php require_once __DIR__.'/layout_top.php';
$sMap = array(
    'pending'         => array('Chờ xác nhận',   '#fbbf24'),
    'pending_payment' => array('Chờ thanh toán', '#f59e0b'),
    'confirmed'       => array('Đã xác nhận',    '#3b82f6'),
    'processing'      => array('Đang xử lý',     '#a78bfa'),
    'shipping'        => array('Đang giao hàng', '#f97316'),
    'delivered'       => array('Đã giao hàng',   '#22c55e'),
    'cancelled'       => array('Đã hủy',         '#ef4444'),
);
$nextBtns = array(
    'pending'         => array(array('confirmed','Xác nhận','#3b82f6'), array('cancelled','Hủy','#ef4444')),
    'pending_payment' => array(array('confirmed','Xác nhận TT','#3b82f6'), array('cancelled','Hủy','#ef4444')),
    'confirmed'       => array(array('processing','Xử lý','#a78bfa'), array('shipping','Giao hàng','#f97316'), array('cancelled','Hủy','#ef4444')),
    'processing'      => array(array('shipping','Giao hàng','#f97316'), array('cancelled','Hủy','#ef4444')),
    'shipping'        => array(array('delivered','Đã giao','#22c55e')),
    'delivered'       => array(),
    'cancelled'       => array(),
);
$canEdit = (int)($_SESSION['user_role'] ?? 0) !== 3;
function orderAge($t) {
    $d = time() - strtotime($t);
    if ($d < 60)    return $d . 'g trước';
    if ($d < 3600)  return (int)($d/60)  . ' phút trước';
    if ($d < 86400) return (int)($d/3600) . ' giờ trước';
    return (int)($d/86400) . ' ngày trước';
}
function ordInitials($n) { $w = explode(' ', trim($n)); return mb_strtoupper(mb_substr(end($w), 0, 1, 'UTF-8')); }
$catIco = array('1'=>'fa-desktop','2'=>'fa-laptop','3'=>'fa-tv','4'=>'fa-computer-mouse','5'=>'fa-keyboard','6'=>'fa-memory','7'=>'fa-bolt','8'=>'fa-gamepad','9'=>'fa-hard-drive','10'=>'fa-screwdriver-wrench','11'=>'fa-headphones');
$db2 = Database::getInstance();
?>
<style>
.ord-stats{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem}
.ord-stat{background:#141414;border:1px solid #1e1e1e;border-radius:10px;padding:.65rem 1rem;flex:1;min-width:110px}
.ord-stat .num{font-size:1.5rem;font-weight:900;line-height:1}
.ord-stat .lbl{font-size:.67rem;color:#555;margin-top:.18rem}
.ord-tabs{display:flex;gap:.3rem;flex-wrap:wrap;margin-bottom:.75rem}
.ord-tab{padding:.28rem .72rem;border-radius:99px;font-size:.75rem;font-weight:600;text-decoration:none;border:1px solid #252525;color:#555;transition:.15s;white-space:nowrap}
.ord-tab:hover{color:#ddd;border-color:#444}
.ord-tab.on{color:#fff;border-color:transparent}
.ord-filter{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.85rem;align-items:center}
.ord-filter input[type=text]{flex:1;min-width:160px}
.ord-av{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.78rem;flex-shrink:0}
.nxt-btn{border:none;border-radius:5px;padding:.2rem .52rem;font-size:.68rem;font-weight:700;cursor:pointer;color:#fff;font-family:inherit;transition:.12s;opacity:.88;margin-right:3px}
.nxt-btn:hover{opacity:1}
#sse-bar{position:fixed;bottom:1.2rem;right:1.2rem;z-index:9999;display:flex;flex-direction:column;gap:.4rem;pointer-events:none;max-width:320px}
.sse-pill{background:#111;border:1px solid #22c55e;border-radius:10px;padding:.5rem .85rem;font-size:.78rem;color:#22c55e;pointer-events:auto;animation:sseIn .3s ease;box-shadow:0 8px 24px rgba(0,0,0,.6)}
@keyframes sseIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ord-new-row{animation:sseIn .4s ease}
@media(max-width:680px){
  .adm-table thead{display:none}
  .adm-table tr{display:block;border-bottom:1px solid #1a1a1a;padding:.4rem 0}
  .adm-table td{display:flex;justify-content:space-between;align-items:center;padding:.22rem .5rem;font-size:.76rem;border:none}
  .adm-table td::before{content:attr(data-label);color:#555;font-size:.67rem;flex-shrink:0;margin-right:.5rem}
}
</style>

<!-- Stats bar -->
<div class="ord-stats">
  <div class="ord-stat"><div class="num" style="color:#ddd"><?= (int)($stats['today_total']??0) ?></div><div class="lbl"><i class="fa-solid fa-box" style="color:#f59e0b"></i> Đơn hôm nay</div></div>
  <div class="ord-stat"><div class="num" style="color:#fbbf24"><?= (int)($stats['today_pending']??0) ?></div><div class="lbl"><i class="fa-solid fa-clock" style="color:#fbbf24"></i> Chờ xác nhận</div></div>
  <div class="ord-stat"><div class="num" style="color:#f97316"><?= (int)($stats['today_processing']??0) ?></div><div class="lbl"><i class="fa-solid fa-truck" style="color:#f97316"></i> Đang xử lý</div></div>
  <div class="ord-stat"><div class="num" style="color:#22c55e"><?= (int)($stats['today_delivered']??0) ?></div><div class="lbl"><i class="fa-solid fa-circle-check" style="color:#22c55e"></i> Đã giao hôm nay</div></div>
</div>

<!-- Status tabs -->
<div class="ord-tabs">
<?php
$tabs = array(
    ''                => array('Tất cả',                                                     '#888'),
    'pending'         => array('<i class="fa-solid fa-clock"></i> Chờ xác nhận',             '#fbbf24'),
    'pending_payment' => array('<i class="fa-solid fa-credit-card"></i> Chờ thanh toán',     '#f59e0b'),
    'confirmed'       => array('<i class="fa-solid fa-circle-check"></i> Đã xác nhận',       '#3b82f6'),
    'processing'      => array('<i class="fa-solid fa-gear"></i> Đang xử lý',                '#a78bfa'),
    'shipping'        => array('<i class="fa-solid fa-truck"></i> Đang giao hàng',           '#f97316'),
    'delivered'       => array('<i class="fa-solid fa-box-check"></i> Đã giao hàng',         '#22c55e'),
    'cancelled'       => array('<i class="fa-solid fa-circle-xmark"></i> Đã hủy',            '#ef4444'),
);
$qBase = http_build_query(array_filter(array('s'=>$search,'date_from'=>$dateFrom,'date_to'=>$dateTo)));
foreach ($tabs as $sv => $tInfo):
    $isOn = ($status ?? '') === $sv;
    $col  = $tInfo[1];
?>
<a href="?status=<?= $sv ?>&<?= $qBase ?>"
   class="ord-tab <?= $isOn?'on':'' ?>"
   style="<?= $isOn ? "background:$col" : "color:$col" ?>">
  <?= $tInfo[0] ?>
</a>
<?php endforeach; ?>
</div>

<!-- Filter form -->
<form method="GET" class="ord-filter">
  <?php if ($status): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>"><?php endif; ?>
  <input type="text" name="s" value="<?= htmlspecialchars($search??'') ?>" placeholder="Mã đơn, tên, SĐT..." class="form-inp">
  <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom??'') ?>" class="form-inp" title="Từ ngày">
  <input type="date" name="date_to"   value="<?= htmlspecialchars($dateTo??'') ?>"   class="form-inp" title="Đến ngày">
  <button type="submit" class="btn-r" style="padding:.38rem .75rem"><i class="fas fa-search"></i></button>
  <?php if ($search || $dateFrom || $dateTo): ?>
  <a href="?status=<?= htmlspecialchars($status??'') ?>" class="btn-r" style="padding:.38rem .65rem;background:#1a1a1a;border-color:#333;color:#777">✕ Xóa</a>
  <?php endif; ?>
  <span style="flex:1"></span>
  <div class="btn-export-group" style="display:flex;gap:.3rem;margin-left:auto">
    <button onclick="window.print()" class="btn-g" style="font-size:.75rem;display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .65rem">
      <i class="fa-solid fa-print"></i> In
    </button>
    <?php $pdfQ = http_build_query(array_filter(['type'=>'orders','s'=>$search,'status'=>$status,'date_from'=>$dateFrom,'date_to'=>$dateTo])); ?>
    <a href="<?= APP_URL ?>/admin/export-pdf?<?= $pdfQ ?>" target="_blank" class="btn-g" style="font-size:.75rem;display:inline-flex;align-items:center;gap:.3rem;padding:.35rem .65rem;text-decoration:none">
      <i class="fa-solid fa-file-pdf" style="color:#ef4444"></i> PDF
    </a>
  </div>
</form>

<!-- Table -->
<div style="overflow-x:auto">
<table class="adm-table" id="ord-table">
  <thead>
    <tr>
      <th>Khách hàng</th>
      <th>Mã đơn</th>
      <th>Sản phẩm</th>
      <th>Tổng tiền</th>
      <th>Thanh toán</th>
      <th>Trạng thái</th>
      <?php if ($canEdit): ?><th>Hành động</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($orders as $o):
    $oSt   = $o['status'] ?? 'pending';
    $oInfo = $sMap[$oSt] ?? array('?', '#555');
    $oItems = $db2->fetchAll(
        "SELECT od.quantity,p.name,p.image,p.category_id FROM order_details od
         JOIN products p ON od.product_id=p.id WHERE od.order_id=? LIMIT 3",
        array($o['id'])
    );
  ?>
  <tr id="ord-row-<?= $o['id'] ?>">
    <td data-label="Khách hàng">
      <div style="display:flex;align-items:center;gap:.5rem">
        <div class="ord-av" style="background:<?= $oInfo[1] ?>22;color:<?= $oInfo[1] ?>"><?= ordInitials($o['fullname']) ?></div>
        <div>
          <div style="color:#ddd;font-size:.81rem;font-weight:600"><?= htmlspecialchars($o['fullname']) ?></div>
          <div style="color:#555;font-size:.69rem"><?= htmlspecialchars($o['phone']) ?></div>
        </div>
      </div>
    </td>
    <td data-label="Mã đơn">
      <a href="<?= APP_URL ?>/admin/orders/detail?id=<?= $o['id'] ?>" style="color:var(--red);font-weight:700;text-decoration:none;font-size:.81rem"><?= htmlspecialchars($o['order_code']) ?></a>
      <div style="color:#444;font-size:.67rem"><?= orderAge($o['created_at']) ?></div>
    </td>
    <td data-label="Sản phẩm">
      <div style="display:flex;align-items:center;gap:.3rem;flex-wrap:nowrap">
        <?php foreach ($oItems as $oi):
          $oIc = $catIco[$oi['category_id']] ?? 'fa-microchip'; ?>
        <div style="position:relative;flex-shrink:0" title="<?= htmlspecialchars($oi['name']) ?> x<?= $oi['quantity'] ?>">
          <div style="width:34px;height:34px;background:#0f0f0f;border-radius:6px;overflow:hidden;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center">
            <?php if (!empty($oi['image']) && $oi['image'] !== 'default.jpg'): ?>
            <img src="<?= UPLOAD_URL.htmlspecialchars($oi['image']) ?>" alt="" loading="lazy"
                 style="width:34px;height:34px;object-fit:cover"
                 onerror="this.style.display='none';this.nextSibling.style.display='flex'">
            <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;color:#333;font-size:.7rem"><i class="fas <?= $oIc ?>"></i></div>
            <?php else: ?>
            <i class="fas <?= $oIc ?>" style="color:#333;font-size:.75rem"></i>
            <?php endif; ?>
          </div>
          <?php if ($oi['quantity'] > 1): ?>
          <span style="position:absolute;top:-5px;right:-5px;background:var(--red);color:#fff;font-size:.5rem;font-weight:800;min-width:14px;height:14px;border-radius:99px;display:flex;align-items:center;justify-content:center;border:1.5px solid #141414"><?= $oi['quantity'] ?></span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if ($o['item_count'] > 3): ?><span style="color:#555;font-size:.7rem">+<?= $o['item_count']-3 ?></span><?php endif; ?>
      </div>
    </td>
    <td data-label="Tổng tiền" style="color:#4ade80;font-weight:700;font-size:.85rem"><?= formatPrice($o['total']) ?></td>
    <td data-label="Thanh toán">
      <span style="color:<?= $o['payment_status']==='paid'?'#4ade80':'#fbbf24' ?>;font-size:.74rem">
        <?= $o['payment_status']==='paid' ? '<i class="fa-solid fa-circle-check"></i> Đã TT' : '<i class="fa-solid fa-clock"></i> Chờ TT' ?>
      </span>
    </td>
    <td data-label="Trạng thái">
      <span style="background:<?= $oInfo[1] ?>22;color:<?= $oInfo[1] ?>;border:1px solid <?= $oInfo[1] ?>44;font-size:.71rem;font-weight:700;padding:3px 9px;border-radius:99px;white-space:nowrap">
        <?= $oInfo[0] ?>
      </span>
    </td>
    <?php if ($canEdit): ?>
    <td data-label="Hành động">
      <?php $nextList = $nextBtns[$oSt] ?? array(); ?>
      <?php
        $stIcons = array('confirmed'=>'fa-circle-check','shipping'=>'fa-truck','delivered'=>'fa-box-check','cancelled'=>'fa-circle-xmark');
        if (empty($nextList)): ?>
      <span style="color:#333;font-size:.72rem">—</span>
      <?php else: foreach ($nextList as $btn):
        $bIco = $stIcons[$btn[0]] ?? 'fa-arrow-right'; ?>
      <button class="nxt-btn status-btn" style="background:<?= $btn[2] ?>"
              data-id="<?= $o['id'] ?>"
              data-status="<?= $btn[0] ?>"
              data-code="<?= htmlspecialchars($o['order_code']) ?>">
        <i class="fa-solid <?= $bIco ?>" style="font-size:.6rem"></i> <?= $btn[1] ?>
      </button>
      <?php endforeach; endif; ?>
    </td>
    <?php endif; ?>
  </tr>
  <?php endforeach; ?>
  <?php if (empty($orders)): ?>
  <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:#444">Không có đơn hàng nào.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<!-- Pagination -->
<?php if (($totalPagesAdmin??1) > 1): ?>
<div style="display:flex;gap:.3rem;margin-top:.9rem;justify-content:center;flex-wrap:wrap">
  <?php for ($i=1; $i<=($totalPagesAdmin??1); $i++): ?>
  <a href="?page=<?= $i ?>&s=<?= urlencode($search??'') ?>&status=<?= urlencode($status??'') ?>&date_from=<?= urlencode($dateFrom??'') ?>&date_to=<?= urlencode($dateTo??'') ?>"
     style="padding:.3rem .65rem;border-radius:5px;text-decoration:none;font-size:.78rem;<?= $i==($page??1)?'background:var(--red);color:#fff':'background:#1a1a1a;color:#aaa;border:1px solid #252525' ?>">
    <?= $i ?>
  </a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<!-- SSE toast container -->
<div id="sse-bar"></div>

<script>
(function(){
  var lastId  = <?= (int)($maxOrderId ?? 0) ?>;
  if (!lastId) return;
  var canEdit = <?= $canEdit ? 'true' : 'false' ?>;
  var appUrl  = '<?= APP_URL ?>';
  var bar     = document.getElementById('sse-bar');
  var tbody   = document.querySelector('#ord-table tbody');

  function toast(msg, color) {
    var p = document.createElement('div');
    p.className = 'sse-pill';
    p.style.borderColor = color || '#22c55e';
    p.style.color       = color || '#22c55e';
    p.textContent = msg;
    bar.appendChild(p);
    setTimeout(function(){ if (p.parentNode) p.parentNode.removeChild(p); }, 7000);
  }

  function esc(s) { var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
  function fmtVND(n) { return parseInt(n).toLocaleString('vi-VN')+'đ'; }
  function initials(n) { var w=n.trim().split(' '); return w[w.length-1].charAt(0).toUpperCase(); }

  function prependRow(o) {
    if (document.getElementById('ord-row-'+o.id)) return;
    // Remove "no orders" placeholder row if present
    var ph = tbody.querySelector('td[colspan]');
    if (ph) ph.parentNode.remove();

    var acts = '';
    if (canEdit) {
      acts = '<form method="POST" action="'+appUrl+'/admin/orders/status?id='+o.id+'" style="display:inline" onsubmit="return confirm(\'Xác nhận đơn này?\')">'
           + '<input type="hidden" name="status" value="confirmed">'
           + '<button type="submit" class="nxt-btn" style="background:#3b82f6">Xác nhận</button></form>'
           + '<form method="POST" action="'+appUrl+'/admin/orders/status?id='+o.id+'" style="display:inline" onsubmit="return confirm(\'Hủy đơn này?\')">'
           + '<input type="hidden" name="status" value="cancelled">'
           + '<button type="submit" class="nxt-btn" style="background:#ef4444">Hủy</button></form>';
    }
    var tr = document.createElement('tr');
    tr.id = 'ord-row-'+o.id;
    tr.className = 'ord-new-row';
    tr.innerHTML =
      '<td data-label="Khách hàng"><div style="display:flex;align-items:center;gap:.5rem">'
      +'<div class="ord-av" style="background:#22c55e22;color:#22c55e">'+initials(o.fullname)+'</div>'
      +'<div><div style="color:#ddd;font-size:.81rem;font-weight:600">'+esc(o.fullname)+'</div>'
      +'<div style="color:#555;font-size:.69rem">'+esc(o.phone)+'</div></div></div></td>'
      +'<td data-label="Mã đơn"><a href="'+appUrl+'/admin/orders/detail?id='+o.id+'" style="color:var(--red);font-weight:700;text-decoration:none;font-size:.81rem">'+esc(o.order_code)+'</a>'
      +'<div style="color:#444;font-size:.67rem">Vừa xong</div></td>'
      +'<td data-label="Sản phẩm"><span style="color:#555;font-size:.75rem">Xem chi tiết →</span></td>'
      +'<td data-label="Tổng tiền" style="color:#4ade80;font-weight:700;font-size:.85rem">'+fmtVND(o.total)+'</td>'
      +'<td data-label="Thanh toán"><span style="color:#fbbf24;font-size:.74rem"><i class="fa-solid fa-clock"></i> Chờ TT</span></td>'
      +'<td data-label="Trạng thái"><span style="background:#fbbf2422;color:#fbbf24;border:1px solid #fbbf2444;font-size:.71rem;font-weight:700;padding:3px 9px;border-radius:99px"><i class="fa-solid fa-clock" style="font-size:.6rem"></i> Chờ XN</span></td>'
      +(canEdit ? '<td data-label="Hành động">'+acts+'</td>' : '');
    tbody.insertBefore(tr, tbody.firstChild);
  }

  function connect() {
    var es = new EventSource(appUrl+'/sse/orders?last_id='+lastId);
    es.onmessage = function(e) {
      try {
        var d = JSON.parse(e.data);
        if (d.type === 'orders' && d.orders && d.orders.length) {
          lastId = d.last_id;
          d.orders.forEach(function(o) {
            prependRow(o);
            toast('Đơn mới: '+o.order_code+' — '+o.fullname, '#22c55e');
          });
        }
      } catch(ex) {}
    };
    es.onerror = function() {
      es.close();
      setTimeout(connect, 15000);
    };
  }
  connect();
})();
</script>

<!-- Full-screen overlay spinner -->
<div id="ord-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:99990;flex-direction:column;align-items:center;justify-content:center;gap:.85rem">
  <i class="fa-solid fa-spinner fa-spin" style="color:#e30000;font-size:2.2rem"></i>
  <div style="color:#ccc;font-size:.88rem;font-weight:600;letter-spacing:.02em">Đang cập nhật...</div>
</div>

<!-- Centered toast -->
<div id="ord-toast" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-55%);z-index:99999;min-width:300px;max-width:400px;background:#1a1a1a;border:1px solid #333;border-radius:16px;box-shadow:0 28px 64px rgba(0,0,0,.9);padding:2rem 1.75rem;text-align:center;opacity:0;transition:opacity .22s ease,transform .22s ease">
  <div id="ord-t-ico" style="font-size:2.4rem;margin-bottom:.7rem"></div>
  <div id="ord-t-msg" style="font-size:1rem;font-weight:800;color:#fff;margin-bottom:.3rem"></div>
  <div id="ord-t-sub" style="font-size:.75rem;color:#555;margin-bottom:1.1rem"></div>
  <div style="height:3px;background:#222;border-radius:99px;overflow:hidden">
    <div id="ord-t-bar" style="height:100%;border-radius:99px;width:100%;transition:width 3s linear"></div>
  </div>
</div>

<script>
(function(){
  var overlay = document.getElementById('ord-overlay');
  var toast   = document.getElementById('ord-toast');
  var APP_URL = '<?= APP_URL ?>';

  var stCfg = {
    confirmed: {ico:'<i class="fa-solid fa-circle-check" style="color:#4ade80"></i>', col:'#4ade80', msg:'Đã xác nhận đơn hàng',  bdr:'#14532d'},
    shipping:  {ico:'<i class="fa-solid fa-truck"        style="color:#60a5fa"></i>', col:'#60a5fa', msg:'Đơn hàng đang giao',    bdr:'#1e3a5f'},
    delivered: {ico:'<i class="fa-solid fa-box-check"    style="color:#4ade80"></i>', col:'#4ade80', msg:'Giao hàng thành công',  bdr:'#14532d'},
    cancelled: {ico:'<i class="fa-solid fa-circle-xmark" style="color:#f87171"></i>', col:'#f87171', msg:'Đã hủy đơn hàng',      bdr:'#4c1414'},
  };

  function setOverlay(on) {
    overlay.style.display = on ? 'flex' : 'none';
    document.querySelectorAll('.status-btn').forEach(function(b){ b.disabled = on; });
  }

  function showToastCenter(status, code) {
    var cfg = stCfg[status] || {ico:'<i class="fa-solid fa-check" style="color:#aaa"></i>', col:'#aaa', msg:'Cập nhật thành công', bdr:'#333'};
    document.getElementById('ord-t-ico').innerHTML = cfg.ico;
    document.getElementById('ord-t-msg').textContent = cfg.msg;
    document.getElementById('ord-t-sub').textContent = code ? 'Đơn hàng #' + code : '';
    document.getElementById('ord-t-bar').style.background = cfg.col;
    document.getElementById('ord-t-bar').style.width = '100%';
    toast.style.borderColor = cfg.bdr;
    toast.style.display = 'block';
    requestAnimationFrame(function(){
      requestAnimationFrame(function(){
        toast.style.opacity = '1';
        toast.style.transform = 'translate(-50%,-50%)';
        document.getElementById('ord-t-bar').style.width = '0';
      });
    });
    setTimeout(function(){
      toast.style.opacity = '0';
      toast.style.transform = 'translate(-50%,-55%)';
      setTimeout(function(){ window.location.reload(); }, 250);
    }, 3100);
  }

  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.status-btn');
    if (!btn) return;
    var id     = btn.dataset.id;
    var status = btn.dataset.status;
    var code   = btn.dataset.code || '';
    if (!id || !status) return;
    setOverlay(true);
    fetch(APP_URL + '/admin/orders/status?id=' + id + '&ajax=1', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'status=' + encodeURIComponent(status)
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
      setOverlay(false);
      if (d.success) {
        showToastCenter(status, code);
      } else {
        alert(d.error || 'Có lỗi xảy ra');
      }
    })
    .catch(function(){
      setOverlay(false);
      alert('Lỗi kết nối');
    });
  });
})();
</script>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
