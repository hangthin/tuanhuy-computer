<?php require_once __DIR__.'/layout_top.php';
$statusMap=array('pending'=>array('Chờ xác nhận','bdg-pending'),'pending_payment'=>array('Chờ thanh toán','bdg-pending'),'confirmed'=>array('Đã xác nhận','bdg-confirmed'),'processing'=>array('Đang xử lý','bdg-processing'),'shipping'=>array('Đang giao','bdg-shipping'),'delivered'=>array('Đã giao','bdg-delivered'),'cancelled'=>array('Đã hủy','bdg-cancelled'));
$nextTransitions=array('pending'=>array('confirmed','cancelled'),'pending_payment'=>array('confirmed','cancelled'),'confirmed'=>array('shipping','cancelled'),'shipping'=>array('delivered'),'delivered'=>array(),'cancelled'=>array());
$payMap=array('cod'=>'COD - Nhận hàng TT','bank'=>'Chuyển khoản','momo'=>'MoMo','vnpay'=>'VNPay');
?>
<div style="max-width:820px">
  <a href="<?= APP_URL ?>/admin/orders" class="btn-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;margin-bottom:1rem">← Quay lại</a>
  <div style="display:grid;grid-template-columns:1fr 280px;gap:1rem">
    <div>
      <div class="card" style="padding:1.1rem;margin-bottom:.9rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.9rem">Thông tin đơn hàng</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.35rem;font-size:.82rem">
          <?php foreach(array(array('Mã đơn',$order['order_code'],'var(--red)'),array('Khách hàng',$order['fullname'],null),array('SĐT',$order['phone'],null),array('Email',$order['email']??'-',null),array('Địa chỉ',$order['address'].', '.($order['district']??'').', '.($order['city']??''),null),array('Thanh toán',isset($payMap[$order['payment_method']])?$payMap[$order['payment_method']]:$order['payment_method'],null),array('TT thanh toán',$order['payment_status']==='paid'?'<i class="fa-solid fa-circle-check" style="color:#4ade80"></i> Đã thanh toán':'<i class="fa-solid fa-clock" style="color:#fbbf24"></i> Chưa thanh toán',null,true),array('Ngày đặt',date('d/m/Y H:i',strtotime($order['created_at'])),null)) as $row): ?>
          <div style="color:#555"><?= $row[0] ?></div>
          <div style="color:<?= $row[2]??'#ddd' ?>;font-weight:500"><?= !empty($row[3]) ? $row[1] : htmlspecialchars($row[1]) ?></div>
          <?php endforeach; ?>
        </div>
        <?php if($order['notes']): ?><div style="margin-top:.65rem;padding:.5rem;background:#111;border-radius:7px;font-size:.78rem;color:#777"><i class="fa-solid fa-comment-dots" style="color:#555"></i> <?= htmlspecialchars($order['notes']) ?></div><?php endif; ?>
      </div>
      <div class="card" style="padding:1.1rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.9rem">Sản phẩm</h3>
        <table class="adm-table" style="font-size:.82rem">
          <thead><tr><th style="width:50px">Ảnh</th><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
          <tbody>
            <?php
            $catIcoD=['1'=>'fa-desktop','2'=>'fa-laptop','3'=>'fa-tv','4'=>'fa-computer-mouse','5'=>'fa-keyboard','6'=>'fa-memory','7'=>'fa-bolt','8'=>'fa-gamepad','9'=>'fa-hard-drive','10'=>'fa-screwdriver-wrench','11'=>'fa-headphones'];
            foreach($order['items'] as $item):
              $pImg = $item['image'] ?? '';
              $pCat = $item['category_id'] ?? '';
              $dIc  = isset($catIcoD[$pCat]) ? $catIcoD[$pCat] : 'fa-microchip';
            ?>
            <tr>
              <td>
                <div style="width:44px;height:44px;background:#0f0f0f;border-radius:7px;overflow:hidden;border:1px solid #1e1e1e;display:flex;align-items:center;justify-content:center">
                  <?php if(!empty($pImg)&&$pImg!=='default.jpg'): ?>
                  <img src="<?= UPLOAD_URL.htmlspecialchars($pImg) ?>" alt=""
                       style="width:44px;height:44px;object-fit:cover"
                       onerror="this.style.display='none';this.nextSibling.style.display='flex'">
                  <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;color:#2a2a2a;font-size:.8rem"><i class="fas <?= $dIc ?>"></i></div>
                  <?php else: ?>
                  <i class="fas <?= $dIc ?>" style="color:#2a2a2a;font-size:.9rem"></i>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div style="color:#ddd;font-weight:600;font-size:.83rem"><?= htmlspecialchars($item['product_name']) ?></div>
                <div style="color:#555;font-size:.7rem">SKU: <?= htmlspecialchars($item['product_sku']??'-') ?></div>
              </td>
              <td style="color:#aaa"><?= formatPrice($item['price']) ?></td>
              <td style="color:#aaa">x<?= $item['quantity'] ?></td>
              <td style="color:#4ade80;font-weight:700"><?= formatPrice($item['subtotal']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div style="border-top:1px solid #222;padding-top:.65rem;margin-top:.2rem">
          <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:.2rem 0;color:#777"><span>Tạm tính</span><span><?= formatPrice($order['subtotal']) ?></span></div>
          <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:.2rem 0;color:#777"><span>Ship</span><span><?= $order['shipping_fee']>0?formatPrice($order['shipping_fee']):'Miễn phí' ?></span></div>
          <div style="display:flex;justify-content:space-between;font-size:.95rem;padding:.45rem 0;font-weight:800;border-top:1px solid #333;margin-top:.2rem"><span style="color:#fff">Tổng</span><span style="color:var(--red)"><?= formatPrice($order['total']) ?></span></div>
        </div>
      </div>
    </div>
    <div>
      <div class="card" id="status-card" style="padding:1.1rem;position:relative">
        <!-- spinner moved to full-screen overlay below -->
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.9rem">Trạng thái đơn</h3>
        <span class="badge <?= isset($statusMap[$order['status']])?$statusMap[$order['status']][1]:'bdg-pending' ?>" style="font-size:.82rem;padding:.3rem .8rem;margin-bottom:.9rem;display:inline-block"><?= isset($statusMap[$order['status']])?$statusMap[$order['status']][0]:$order['status'] ?></span>
        <?php
        $timeline=array('pending','confirmed','processing','shipping','delivered');
        $curIdx=array_search($order['status'],$timeline);
        if($order['status']!=='cancelled'):
          foreach($timeline as $i=>$st):
            $done=($curIdx!==false&&$i<=$curIdx);
        ?>
        <div style="display:flex;align-items:center;gap:.5rem;padding:.3rem 0;font-size:.78rem">
          <div style="width:16px;height:16px;border-radius:50%;background:<?= $done?'var(--red)':'#222' ?>;border:1.5px solid <?= $done?'var(--red)':'#333' ?>;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.5rem;color:<?= $done?'#fff':'#555' ?>"><?= $done?'<i class="fa-solid fa-check"></i>':($i+1) ?></div>
          <span style="color:<?= $done?'#ddd':'#555' ?>"><?= isset($statusMap[$st])?$statusMap[$st][0]:$st ?></span>
        </div>
        <?php endforeach; endif; ?>
        <?php if((int)($_SESSION['user_role']??0) !== 3): ?>
        <?php $nextOpts=$nextTransitions[$order['status']]??[]; ?>
        <?php if($nextOpts): ?>
        <form id="status-form" method="POST" action="<?= APP_URL ?>/admin/orders/status?id=<?= $order['id'] ?>" style="margin-top:.9rem">
          <select name="status" class="form-inp" style="margin-bottom:.5rem">
            <?php foreach($nextOpts as $sv): $sl=$statusMap[$sv]??array($sv,''); ?>
            <option value="<?= $sv ?>"><?= $sl[0] ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn-r" style="width:100%">Cập nhật</button>
        </form>
        <?php else: ?>
        <p style="color:#555;font-size:.75rem;margin-top:.75rem">Đơn hàng đã kết thúc.</p>
        <?php endif; ?>
        <?php else: ?>
        <p style="color:#555;font-size:.75rem;margin-top:.75rem">Staff không có quyền cập nhật trạng thái.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- Full-screen overlay -->
<div id="det-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:99990;flex-direction:column;align-items:center;justify-content:center;gap:.85rem">
  <i class="fa-solid fa-spinner fa-spin" style="color:#e30000;font-size:2.2rem"></i>
  <div style="color:#ccc;font-size:.88rem;font-weight:600">Đang cập nhật...</div>
</div>

<!-- Centered toast -->
<div id="det-toast" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-55%);z-index:99999;min-width:300px;max-width:400px;background:#1a1a1a;border:1px solid #333;border-radius:16px;box-shadow:0 28px 64px rgba(0,0,0,.9);padding:2rem 1.75rem;text-align:center;opacity:0;transition:opacity .22s ease,transform .22s ease">
  <div id="det-t-ico" style="font-size:2.4rem;margin-bottom:.7rem"></div>
  <div id="det-t-msg" style="font-size:1rem;font-weight:800;color:#fff;margin-bottom:.3rem"></div>
  <div id="det-t-sub" style="font-size:.75rem;color:#555;margin-bottom:1.1rem"></div>
  <div style="height:3px;background:#222;border-radius:99px;overflow:hidden">
    <div id="det-t-bar" style="height:100%;border-radius:99px;width:100%;transition:width 3s linear"></div>
  </div>
</div>

<script>
(function(){
  var APP_URL = '<?= APP_URL ?>';
  var orderId = <?= (int)($order['id']??0) ?>;
  var overlay = document.getElementById('det-overlay');
  var toast   = document.getElementById('det-toast');

  var stCfg = {
    confirmed: {ico:'<i class="fa-solid fa-circle-check" style="color:#4ade80"></i>', col:'#4ade80', msg:'Đã xác nhận đơn hàng', bdr:'#14532d'},
    shipping:  {ico:'<i class="fa-solid fa-truck"        style="color:#60a5fa"></i>', col:'#60a5fa', msg:'Đơn hàng đang giao',   bdr:'#1e3a5f'},
    delivered: {ico:'<i class="fa-solid fa-box-check"    style="color:#4ade80"></i>', col:'#4ade80', msg:'Giao hàng thành công', bdr:'#14532d'},
    cancelled: {ico:'<i class="fa-solid fa-circle-xmark" style="color:#f87171"></i>', col:'#f87171', msg:'Đã hủy đơn hàng',     bdr:'#4c1414'},
  };

  function setOverlay(on) {
    overlay.style.display = on ? 'flex' : 'none';
    var els = document.querySelectorAll('#status-form button, #status-form select');
    els.forEach(function(el){ el.disabled = on; });
  }

  function showCenteredToast(status) {
    var cfg = stCfg[status] || {ico:'<i class="fa-solid fa-check" style="color:#aaa"></i>', col:'#aaa', msg:'Cập nhật thành công', bdr:'#333'};
    document.getElementById('det-t-ico').innerHTML = cfg.ico;
    document.getElementById('det-t-msg').textContent = cfg.msg;
    document.getElementById('det-t-sub').textContent = '<?= htmlspecialchars($order['order_code']??'') ?>';
    var bar = document.getElementById('det-t-bar');
    bar.style.background = cfg.col;
    bar.style.width = '100%';
    toast.style.borderColor = cfg.bdr;
    toast.style.display = 'block';
    requestAnimationFrame(function(){
      requestAnimationFrame(function(){
        toast.style.opacity = '1';
        toast.style.transform = 'translate(-50%,-50%)';
        bar.style.width = '0';
      });
    });
    setTimeout(function(){
      toast.style.opacity = '0';
      toast.style.transform = 'translate(-50%,-55%)';
      setTimeout(function(){ window.location.reload(); }, 250);
    }, 3100);
  }

  function attachForm() {
    var form = document.getElementById('status-form');
    if (!form) return;
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var sel = form.querySelector('select[name="status"]');
      if (!sel) return;
      var newStatus = sel.value;
      setOverlay(true);
      fetch(APP_URL + '/admin/orders/status?id=' + orderId + '&ajax=1', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'status=' + encodeURIComponent(newStatus)
      })
      .then(function(r){ return r.json(); })
      .then(function(d){
        setOverlay(false);
        if (d.success) {
          showCenteredToast(newStatus);
        } else {
          alert(d.error || 'Có lỗi xảy ra');
        }
      })
      .catch(function(){
        setOverlay(false);
        alert('Lỗi kết nối');
      });
    });
  }
  attachForm();
})();
</script>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
