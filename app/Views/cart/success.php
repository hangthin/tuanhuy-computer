<?php $pageTitle='Đặt hàng thành công'; require_once __DIR__.'/../layouts/header.php'; ?>

<style>
@keyframes checkDraw{0%{stroke-dashoffset:60}100%{stroke-dashoffset:0}}
@keyframes circlePop{0%{transform:scale(0);opacity:0}70%{transform:scale(1.1)}100%{transform:scale(1);opacity:1}}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}

.sc-bg{background:linear-gradient(160deg,#f8f9fa 0%,#fff 60%);min-height:calc(100vh - 140px);display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
.sc-box{width:100%;max-width:860px;animation:fadeUp .5s ease both}

/* Header banner */
.sc-banner{background:linear-gradient(135deg,#16a34a,#15803d);border-radius:18px 18px 0 0;padding:2.5rem 2rem 2rem;text-align:center;position:relative;overflow:hidden}
.sc-banner::before{content:'';position:absolute;top:-40px;right:-40px;width:180px;height:180px;background:rgba(255,255,255,.06);border-radius:50%}
.sc-banner::after{content:'';position:absolute;bottom:-30px;left:-30px;width:120px;height:120px;background:rgba(255,255,255,.04);border-radius:50%}
.sc-check{width:72px;height:72px;margin:0 auto 1rem;animation:circlePop .5s cubic-bezier(.175,.885,.32,1.275) both}
.sc-check circle{fill:rgba(255,255,255,.15);stroke:rgba(255,255,255,.4);stroke-width:2}
.sc-check path{fill:none;stroke:#fff;stroke-width:3.5;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:60;stroke-dashoffset:60;animation:checkDraw .4s .4s ease forwards}
.sc-banner h1{color:#fff;font-size:1.6rem;font-weight:900;margin:0 0 .4rem;position:relative}
.sc-banner p{color:rgba(255,255,255,.82);font-size:.875rem;margin:0;position:relative}
.sc-ordercode{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:99px;padding:.3rem .9rem;font-size:.82rem;font-weight:700;margin-top:.85rem;position:relative}

/* Body */
.sc-body{background:#fff;border-radius:0 0 18px 18px;box-shadow:0 8px 40px rgba(0,0,0,.08)}
.sc-grid{display:grid;grid-template-columns:1fr 1fr;gap:0}
.sc-panel{padding:1.6rem 1.8rem}
.sc-panel+.sc-panel{border-left:1px solid #f3f4f6}
.sc-panel-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#aaa;margin-bottom:1rem;display:flex;align-items:center;gap:.4rem}
.sc-panel-title i{font-size:.7rem}

/* Info rows */
.sc-inf-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.42rem 0;border-bottom:1px solid #f8f8f8;font-size:.83rem}
.sc-inf-row:last-child{border:none}
.sc-inf-row .k{color:#999;flex-shrink:0;width:110px}
.sc-inf-row .v{color:#222;font-weight:600;text-align:right}
.sc-inf-row .v.red{color:var(--red);font-size:.95rem}

/* Items */
.sc-item{display:flex;align-items:center;gap:.7rem;padding:.5rem 0;border-bottom:1px solid #f8f8f8}
.sc-item:last-child{border:none}
.sc-item-img{width:46px;height:46px;border-radius:8px;object-fit:cover;border:1px solid #eee;flex-shrink:0;background:#f5f5f5}
.sc-item-info{flex:1;min-width:0}
.sc-item-info .name{font-size:.82rem;font-weight:600;color:#222;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sc-item-info .qty{font-size:.72rem;color:#aaa;margin-top:2px}
.sc-item-price{font-weight:700;color:var(--red);font-size:.82rem;flex-shrink:0}

/* Total strip */
.sc-total{background:#fafafa;border-top:1px solid #f0f0f0;padding:1rem 1.8rem;display:flex;align-items:center;justify-content:space-between;border-radius:0 0 18px 18px}
.sc-total-lbl{font-size:.8rem;color:#888}
.sc-total-val{font-size:1.35rem;font-weight:900;color:var(--red)}

/* Email note */
.sc-email{background:#f0fdf4;border-top:1px solid #dcfce7;padding:.75rem 1.8rem;display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#166534}

/* Actions */
.sc-actions{padding:1.4rem 1.8rem;display:flex;gap:.65rem;justify-content:center;flex-wrap:wrap;border-top:1px solid #f3f4f6}
.sc-btn{display:inline-flex;align-items:center;gap:.45rem;padding:.62rem 1.5rem;border-radius:10px;font-size:.85rem;font-weight:700;text-decoration:none;transition:all .18s;cursor:pointer}
.sc-btn-primary{background:var(--red);color:#fff}
.sc-btn-primary:hover{background:var(--red-dk);transform:translateY(-1px);box-shadow:0 4px 16px rgba(227,0,0,.28)}
.sc-btn-outline{background:#fff;color:#444;border:1.5px solid #ddd}
.sc-btn-outline:hover{border-color:#999;color:#111;background:#f9f9f9}
.sc-btn-blue{background:#1d4ed8;color:#fff}
.sc-btn-blue:hover{background:#1e40af;transform:translateY(-1px)}

@media(max-width:640px){
  .sc-grid{grid-template-columns:1fr}
  .sc-panel+.sc-panel{border-left:none;border-top:1px solid #f3f4f6}
  .sc-panel{padding:1.2rem 1.2rem}
  .sc-total,.sc-actions,.sc-email{padding-left:1.2rem;padding-right:1.2rem}
  .sc-banner h1{font-size:1.3rem}
}
</style>

<?php
$payMap = array('cod'=>'COD - Thu tiền khi nhận','bank'=>'Chuyển khoản ngân hàng','momo'=>'Ví MoMo','vnpay'=>'VNPay');
$payLabel = isset($payMap[strtolower($order['payment_method']??'')]) ? $payMap[strtolower($order['payment_method'])] : strtoupper($order['payment_method']??'');
$addrParts = array_filter(array($order['address']??'',$order['district']??'',$order['city']??''));
$addr = implode(', ', $addrParts);
?>

<div class="sc-bg">
<div class="sc-box">

  <!-- Banner -->
  <div class="sc-banner">
    <svg class="sc-check" viewBox="0 0 72 72">
      <circle cx="36" cy="36" r="34"/>
      <path d="M22 37l10 10 18-20"/>
    </svg>
    <h1>Đặt hàng thành công!</h1>
    <p>Cảm ơn bạn đã mua hàng tại <strong>Tuấn Huy Computer</strong></p>
    <div class="sc-ordercode">
      <i class="fa-solid fa-receipt"></i>
      Mã đơn: <?= htmlspecialchars($order['order_code']) ?>
    </div>
  </div>

  <!-- Body -->
  <div class="sc-body">

    <?php if(!empty($order['email'])): ?>
    <div class="sc-email">
      <i class="fa-solid fa-circle-check"></i>
      Email xác nhận đã được gửi đến <strong style="margin:0 3px"><?= htmlspecialchars($order['email']) ?></strong>
    </div>
    <?php endif; ?>

    <div class="sc-grid">

      <!-- Cột trái: thông tin giao hàng -->
      <div class="sc-panel">
        <div class="sc-panel-title"><i class="fa-solid fa-location-dot"></i> Thông tin giao hàng</div>
        <div class="sc-inf-row"><span class="k">Khách hàng</span><span class="v"><?= htmlspecialchars($order['fullname']) ?></span></div>
        <div class="sc-inf-row"><span class="k">Điện thoại</span><span class="v"><?= htmlspecialchars($order['phone']) ?></span></div>
        <?php if($addr): ?>
        <div class="sc-inf-row"><span class="k">Địa chỉ</span><span class="v" style="font-weight:400;font-size:.78rem;max-width:180px"><?= htmlspecialchars($addr) ?></span></div>
        <?php endif; ?>
        <div class="sc-inf-row"><span class="k">Thanh toán</span><span class="v"><?= $payLabel ?></span></div>
        <div class="sc-inf-row"><span class="k">Trạng thái</span>
          <span class="v"><span style="background:#fef9c3;color:#854d0e;padding:2px 9px;border-radius:99px;font-size:.72rem;font-weight:700"><i class="fa-solid fa-clock" style="font-size:.65rem"></i> Chờ xác nhận</span></span>
        </div>
        <?php if(!empty($order['notes'])): ?>
        <div class="sc-inf-row"><span class="k">Ghi chú</span><span class="v" style="font-weight:400;font-style:italic;color:#888;max-width:180px"><?= htmlspecialchars($order['notes']) ?></span></div>
        <?php endif; ?>
      </div>

      <!-- Cột phải: sản phẩm -->
      <div class="sc-panel">
        <div class="sc-panel-title"><i class="fa-solid fa-box"></i> Sản phẩm đã đặt <?php if(!empty($order['items'])): ?><span style="background:#f3f4f6;color:#888;padding:1px 7px;border-radius:99px;font-size:.68rem;font-weight:700"><?= count($order['items']) ?></span><?php endif; ?></div>
        <?php if(!empty($order['items'])): ?>
          <?php foreach($order['items'] as $it): ?>
          <div class="sc-item">
            <?php if(!empty($it['image'])): ?>
            <img class="sc-item-img" src="<?= UPLOAD_URL.htmlspecialchars($it['image']) ?>" alt="" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2246%22 height=%2246%22><rect fill=%22%23f5f5f5%22 width=%2246%22 height=%2246%22/><text x=%2223%22 y=%2229%22 text-anchor=%22middle%22 font-size=%2218%22>📦</text></svg>'">
            <?php else: ?>
            <div class="sc-item-img" style="display:flex;align-items:center;justify-content:center;font-size:1.3rem">📦</div>
            <?php endif; ?>
            <div class="sc-item-info">
              <div class="name"><?= htmlspecialchars($it['product_name']) ?></div>
              <div class="qty">Số lượng: <?= $it['quantity'] ?></div>
            </div>
            <div class="sc-item-price"><?= formatPrice($it['subtotal']) ?></div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>

    <!-- Tổng tiền -->
    <div class="sc-total">
      <div>
        <div class="sc-total-lbl">Phí vận chuyển: <?= ($order['shipping_fee']??0)>0 ? formatPrice($order['shipping_fee']) : '<span style="color:#22c55e;font-weight:600">Miễn phí</span>' ?></div>
        <div class="sc-total-lbl" style="margin-top:2px">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
      </div>
      <div style="text-align:right">
        <div style="font-size:.75rem;color:#aaa;margin-bottom:2px">Tổng thanh toán</div>
        <div class="sc-total-val"><?= formatPrice($order['total']) ?></div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="sc-actions">
      <a href="<?= APP_URL ?>/" class="sc-btn sc-btn-primary"><i class="fa-solid fa-house"></i> Về trang chủ</a>
      <a href="<?= APP_URL ?>/products" class="sc-btn sc-btn-outline"><i class="fa-solid fa-bag-shopping"></i> Tiếp tục mua sắm</a>
      <?php if(isLoggedIn()): ?>
      <a href="<?= APP_URL ?>/account/orders" class="sc-btn sc-btn-blue"><i class="fa-solid fa-box-open"></i> Xem đơn hàng</a>
      <?php endif; ?>
    </div>

  </div>
</div>
</div>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
