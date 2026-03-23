<?php $pageTitle='Thanh toán'; require_once __DIR__.'/../layouts/header.php'; ?>
<div style="max-width:1100px;margin:1.25rem auto;padding:0 1rem">
  <h1 style="font-size:1.2rem;font-weight:800;color:#111;margin-bottom:1.1rem;display:flex;align-items:center;gap:.5rem"><i class="fa-solid fa-lock" style="color:var(--red)"></i> Thanh toán đơn hàng</h1>
  <form method="POST" action="<?= APP_URL ?>/checkout/place">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem">
      <!-- Left -->
      <div>
        <div style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.05);margin-bottom:.9rem">
          <h3 style="font-weight:800;font-size:.9rem;margin-bottom:1rem;display:flex;align-items:center;gap:.4rem"><span style="color:var(--red)">📍</span> Thông tin giao hàng</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem">
            <div style="grid-column:1/-1">
              <label style="font-size:.78rem;font-weight:600;color:#333;display:block;margin-bottom:.25rem">Họ và tên *</label>
              <input type="text" name="fullname" value="<?= htmlspecialchars(isset($user['fullname'])?$user['fullname']:'') ?>" required class="form-input">
            </div>
            <div>
              <label style="font-size:.78rem;font-weight:600;color:#333;display:block;margin-bottom:.25rem">Số điện thoại *</label>
              <input type="tel" name="phone" value="<?= htmlspecialchars(isset($user['phone'])?$user['phone']:'') ?>" required class="form-input">
            </div>
            <div>
              <label style="font-size:.78rem;font-weight:600;color:#333;display:block;margin-bottom:.25rem">Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars(isset($user['email'])?$user['email']:'') ?>" class="form-input">
            </div>
            <div>
              <label style="font-size:.78rem;font-weight:600;color:#333;display:block;margin-bottom:.25rem">Tỉnh / Thành phố *</label>
              <select name="city" required class="form-input">
                <option value="">-- Chọn --</option>
                <?php foreach(array('TP.HCM','Hà Nội','Đà Nẵng','Cần Thơ','Bình Dương','Đồng Nai','Hải Phòng','An Giang','Bắc Giang','Bắc Ninh','Bình Định','Bình Thuận','Cà Mau','Đắk Lắk','Đồng Tháp','Gia Lai','Hà Nam','Hà Tĩnh','Hải Dương','Hậu Giang','Hòa Bình','Khánh Hòa','Kiên Giang','Lâm Đồng','Long An','Nam Định','Nghệ An','Ninh Bình','Quảng Bình','Quảng Nam','Quảng Ngãi','Quảng Ninh','Sóc Trăng','Tây Ninh','Thái Bình','Thanh Hóa','Tiền Giang','Trà Vinh','Vĩnh Long','Vĩnh Phúc','Yên Bái') as $c): ?>
                <option value="<?= $c ?>" <?= (isset($user['city'])&&$user['city']===$c)?'selected':'' ?>><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="font-size:.78rem;font-weight:600;color:#333;display:block;margin-bottom:.25rem">Quận / Huyện</label>
              <input type="text" name="district" class="form-input" placeholder="Nhập quận/huyện">
            </div>
            <div style="grid-column:1/-1">
              <label style="font-size:.78rem;font-weight:600;color:#333;display:block;margin-bottom:.25rem">Địa chỉ cụ thể *</label>
              <input type="text" name="address" value="<?= htmlspecialchars(isset($user['address'])?$user['address']:'') ?>" required placeholder="Số nhà, tên đường..." class="form-input">
            </div>
            <div style="grid-column:1/-1">
              <label style="font-size:.78rem;font-weight:600;color:#333;display:block;margin-bottom:.25rem">Ghi chú</label>
              <textarea name="notes" rows="2" class="form-input" style="resize:vertical" placeholder="Giao giờ hành chính, gọi trước..."></textarea>
            </div>
          </div>
        </div>
        <!-- Payment -->
        <div style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.05)">
          <h3 style="font-weight:800;font-size:.9rem;margin-bottom:1rem;display:flex;align-items:center;gap:.4rem"><span style="color:var(--red)">💳</span> Phương thức thanh toán</h3>
          <?php foreach(array(array('cod','💵','Thanh toán khi nhận hàng (COD)','Kiểm tra hàng trước khi thanh toán'),array('bank','🏦','Chuyển khoản ngân hàng','MB Bank: 1234567890 - Tuấn Huy Computer'),array('momo','📱','Ví MoMo','Quét QR hoặc số 0909999888'),array('vnpay','💳','VNPay','Thẻ ATM/VISA/Mastercard')) as $pm): $pv=$pm[0];$pi=$pm[1];$pl=$pm[2];$pd=$pm[3]; ?>
          <label id="pml-<?= $pv ?>" style="display:flex;align-items:center;gap:.65rem;padding:.75rem;border:1.5px solid <?= $pv==='cod'?'var(--red)':'#eee' ?>;border-radius:9px;margin-bottom:.45rem;cursor:pointer;background:<?= $pv==='cod'?'#fff5f5':'#fff' ?>;transition:all .2s" onclick="selPM('<?= $pv ?>')">
            <input type="radio" name="payment_method" value="<?= $pv ?>" <?= $pv==='cod'?'checked':'' ?> style="accent-color:var(--red)">
            <span style="font-size:1.2rem"><?= $pi ?></span>
            <div><div style="font-weight:600;font-size:.85rem"><?= $pl ?></div><div style="font-size:.75rem;color:#999"><?= $pd ?></div></div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- Right -->
      <div style="position:sticky;top:75px">
        <div style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.05)">
          <h3 style="font-weight:700;font-size:.9rem;margin-bottom:.9rem">Đơn hàng của bạn</h3>
          <div style="max-height:280px;overflow-y:auto;margin-bottom:.9rem">
            <?php foreach($items as $item): ?>
            <div style="display:flex;gap:.65rem;padding:.45rem 0;border-bottom:1px solid #f5f5f5">
              <div style="width:40px;height:40px;background:#f5f5f5;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">📦</div>
              <div style="flex:1;min-width:0">
                <div style="font-size:.78rem;font-weight:600;color:#333;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($item['name']) ?></div>
                <div style="font-size:.73rem;color:#999">x<?= $item['quantity'] ?></div>
              </div>
              <div style="font-weight:700;font-size:.82rem;color:var(--red);flex-shrink:0"><?= formatPrice($item['unit_price']*$item['quantity']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <div style="font-size:.85rem;padding:.3rem 0;border-bottom:1px solid #f5f5f5;display:flex;justify-content:space-between"><span style="color:#555">Tạm tính</span><span><?= formatPrice($subtotal) ?></span></div>
          <div style="font-size:.85rem;padding:.3rem 0;border-bottom:1px solid #f5f5f5;display:flex;justify-content:space-between"><span style="color:#555">Ship</span><span style="color:<?= $shipping===0?'#22c55e':'#111' ?>"><?= $shipping===0?'Miễn phí':formatPrice($shipping) ?></span></div>
          <div style="display:flex;justify-content:space-between;padding:.6rem 0;margin-top:.2rem">
            <span style="font-weight:800">Tổng cộng</span>
            <span style="font-weight:900;font-size:1.1rem;color:var(--red)"><?= formatPrice($total) ?></span>
          </div>
          <button type="submit" class="btn-red" style="width:100%;padding:.7rem;font-size:.95rem;display:flex;align-items:center;justify-content:center;gap:.4rem;animation:pulseRed 2s infinite"><i class="fa-solid fa-check-circle"></i> Đặt hàng ngay</button>
          <a href="<?= APP_URL ?>/cart" style="display:block;text-align:center;color:#999;font-size:.78rem;margin-top:.5rem;text-decoration:none">← Quay lại giỏ hàng</a>
        </div>
      </div>
    </div>
  </form>
</div>
<script>
function selPM(v){
  var methods=['cod','bank','momo','vnpay'];
  for(var i=0;i<methods.length;i++){
    var el=document.getElementById('pml-'+methods[i]);
    if(!el) continue;
    el.style.borderColor=methods[i]===v?'var(--red)':'#eee';
    el.style.background=methods[i]===v?'#fff5f5':'#fff';
  }
}
</script>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
