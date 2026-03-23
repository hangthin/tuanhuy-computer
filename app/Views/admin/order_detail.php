<?php require_once __DIR__.'/layout_top.php';
$statusMap=array('pending'=>array('Chờ xác nhận','bdg-pending'),'confirmed'=>array('Đã xác nhận','bdg-confirmed'),'processing'=>array('Đang xử lý','bdg-processing'),'shipping'=>array('Đang giao','bdg-shipping'),'delivered'=>array('Đã giao','bdg-delivered'),'cancelled'=>array('Đã hủy','bdg-cancelled'));
$payMap=array('cod'=>'COD - Nhận hàng TT','bank'=>'Chuyển khoản','momo'=>'MoMo','vnpay'=>'VNPay');
?>
<div style="max-width:820px">
  <a href="<?= APP_URL ?>/admin/orders" class="btn-g" style="text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;margin-bottom:1rem">← Quay lại</a>
  <div style="display:grid;grid-template-columns:1fr 280px;gap:1rem">
    <div>
      <div class="card" style="padding:1.1rem;margin-bottom:.9rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.9rem">Thông tin đơn hàng</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.35rem;font-size:.82rem">
          <?php foreach(array(array('Mã đơn',$order['order_code'],'var(--red)'),array('Khách hàng',$order['fullname'],null),array('SĐT',$order['phone'],null),array('Email',$order['email']??'-',null),array('Địa chỉ',$order['address'].', '.($order['district']??'').', '.($order['city']??''),null),array('Thanh toán',isset($payMap[$order['payment_method']])?$payMap[$order['payment_method']]:$order['payment_method'],null),array('TT thanh toán',$order['payment_status']==='paid'?'✅ Đã thanh toán':'⏳ Chưa thanh toán',null),array('Ngày đặt',date('d/m/Y H:i',strtotime($order['created_at'])),null)) as $row): ?>
          <div style="color:#555"><?= $row[0] ?></div>
          <div style="color:<?= $row[2]??'#ddd' ?>;font-weight:500"><?= htmlspecialchars($row[1]) ?></div>
          <?php endforeach; ?>
        </div>
        <?php if($order['notes']): ?><div style="margin-top:.65rem;padding:.5rem;background:#111;border-radius:7px;font-size:.78rem;color:#777">💬 <?= htmlspecialchars($order['notes']) ?></div><?php endif; ?>
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
      <div class="card" style="padding:1.1rem">
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
          <div style="width:16px;height:16px;border-radius:50%;background:<?= $done?'var(--red)':'#222' ?>;border:1.5px solid <?= $done?'var(--red)':'#333' ?>;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.5rem;color:<?= $done?'#fff':'#555' ?>"><?= $done?'✓':($i+1) ?></div>
          <span style="color:<?= $done?'#ddd':'#555' ?>"><?= isset($statusMap[$st])?$statusMap[$st][0]:$st ?></span>
        </div>
        <?php endforeach; endif; ?>
        <?php if((int)($_SESSION['user_role']??0) !== 3): ?>
        <form method="POST" action="<?= APP_URL ?>/admin/orders/status?id=<?= $order['id'] ?>" style="margin-top:.9rem">
          <select name="status" class="form-inp" style="margin-bottom:.5rem">
            <?php foreach($statusMap as $sv=>$sl): ?><option value="<?= $sv ?>" <?= $order['status']===$sv?'selected':'' ?>><?= $sl[0] ?></option><?php endforeach; ?>
          </select>
          <button type="submit" class="btn-r" style="width:100%">Cập nhật</button>
        </form>
        <?php else: ?>
        <p style="color:#555;font-size:.75rem;margin-top:.75rem">Staff không có quyền cập nhật trạng thái.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
