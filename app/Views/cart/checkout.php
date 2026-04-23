<?php $pageTitle='Thanh toán'; require_once __DIR__.'/../layouts/header.php'; ?>

<style>
/* ── Checkout-specific styles ── */
.co-wrap{max-width:1080px;margin:1.5rem auto;padding:0 1rem}
.co-steps{display:flex;align-items:center;gap:0;margin-bottom:1.5rem;font-size:.75rem;font-weight:600}
.co-step{display:flex;align-items:center;gap:.35rem;color:#bbb}
.co-step.done{color:#22c55e}.co-step.active{color:var(--red)}
.co-step-dot{width:24px;height:24px;border-radius:50%;border:2px solid currentColor;display:flex;align-items:center;justify-content:center;font-size:.6rem;flex-shrink:0}
.co-step.done .co-step-dot{background:#22c55e;border-color:#22c55e;color:#fff}
.co-step.active .co-step-dot{background:var(--red);border-color:var(--red);color:#fff}
.co-sep{flex:1;height:1px;background:linear-gradient(90deg,#ddd,#eee);margin:0 .4rem;max-width:60px}
.co-card{background:#fff;border-radius:14px;box-shadow:0 2px 16px rgba(0,0,0,.06);border:1px solid #f0f0f0;margin-bottom:.9rem}
.co-card-head{padding:.9rem 1.25rem;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;gap:.5rem}
.co-card-title{font-weight:800;font-size:.88rem;color:#111}
.co-card-body{padding:1.1rem 1.25rem}
.co-grid2{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
.co-label{font-size:.75rem;font-weight:700;color:#444;display:block;margin-bottom:.25rem}
.co-input{width:100%;padding:.6rem .85rem;border:1.5px solid #e8e8e8;border-radius:9px;outline:none;font-family:var(--font);font-size:.875rem;background:#fafafa;color:#111;transition:border-color .15s,background .15s}
.co-input:focus{border-color:var(--red);background:#fff;box-shadow:0 0 0 3px rgba(227,0,0,.06)}
.co-input::placeholder{color:#bbb}

/* Payment options */
.pm-opt{display:flex;align-items:center;gap:.7rem;padding:.8rem 1rem;border:1.5px solid #eee;border-radius:10px;margin-bottom:.45rem;cursor:pointer;transition:all .18s;background:#fff;user-select:none}
.pm-opt:hover{border-color:#ddd;background:#fafafa}
.pm-opt.active{border-color:var(--red);background:#fff8f8}
.pm-ico{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0}
.pm-txt-main{font-weight:700;font-size:.84rem;color:#111}
.pm-txt-sub{font-size:.72rem;color:#999;margin-top:1px}
.pm-check{width:18px;height:18px;border-radius:50%;border:2px solid #ddd;margin-left:auto;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .18s}
.pm-opt.active .pm-check{background:var(--red);border-color:var(--red)}
.pm-opt.active .pm-check::after{content:'';width:6px;height:6px;background:#fff;border-radius:50%;display:block}

/* Order summary */
.co-item{display:flex;align-items:center;gap:.7rem;padding:.6rem 0;border-bottom:1px solid #f5f5f5}
.co-item:last-child{border-bottom:none}
.co-item-img{width:52px;height:52px;border-radius:9px;overflow:hidden;flex-shrink:0;border:1px solid #f0f0f0;background:#f8f8f8;display:flex;align-items:center;justify-content:center;position:relative}
.co-item-img img{width:100%;height:100%;object-fit:cover}
.co-item-qty{position:absolute;top:-4px;right:-4px;background:var(--red);color:#fff;font-size:.58rem;font-weight:800;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.co-item-name{font-size:.8rem;font-weight:600;color:#222;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.co-item-price{font-weight:800;font-size:.82rem;color:var(--red);flex-shrink:0;white-space:nowrap}
.co-total-row{display:flex;justify-content:space-between;align-items:center;padding:.35rem 0}
.co-total-row + .co-total-row{border-top:1px solid #f5f5f5}
.co-trust{display:flex;align-items:center;gap:.4rem;font-size:.7rem;color:#888;justify-content:center;margin-top:.6rem}
.co-trust span{display:flex;align-items:center;gap:.25rem}
</style>

<div class="co-wrap">

  <!-- Steps -->
  <div class="co-steps">
    <div class="co-step done"><div class="co-step-dot"><i class="fa-solid fa-check" style="font-size:.55rem"></i></div> Giỏ hàng</div>
    <div class="co-sep"></div>
    <div class="co-step active"><div class="co-step-dot">2</div> Thông tin</div>
    <div class="co-sep"></div>
    <div class="co-step"><div class="co-step-dot">3</div> Xác nhận</div>
  </div>

  <form method="POST" action="<?= APP_URL ?>/checkout/place" id="co-form">
    <input type="hidden" name="checkout_mode" value="<?= htmlspecialchars($checkoutMode ?? 'cart') ?>">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.1rem;align-items:start">

      <!-- ── LEFT ── -->
      <div>

        <!-- Shipping info -->
        <div class="co-card">
          <div class="co-card-head">
            <div style="width:30px;height:30px;background:#fff1f1;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fa-solid fa-location-dot" style="color:var(--red);font-size:.8rem"></i>
            </div>
            <span class="co-card-title">Thông tin giao hàng</span>
          </div>
          <div class="co-card-body">
            <div class="co-grid2">
              <div style="grid-column:1/-1">
                <label class="co-label">Họ và tên <span style="color:var(--red)">*</span></label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" required class="co-input" placeholder="Nguyễn Văn A">
              </div>
              <div>
                <label class="co-label">Số điện thoại <span style="color:var(--red)">*</span></label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required class="co-input" placeholder="0901 234 567">
              </div>
              <div>
                <label class="co-label">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="co-input" placeholder="email@example.com">
              </div>
              <div>
                <label class="co-label">Tỉnh / Thành phố <span style="color:var(--red)">*</span></label>
                <select name="city" required class="co-input" style="cursor:pointer">
                  <option value="">-- Chọn tỉnh/thành --</option>
                  <?php foreach(['TP.HCM','Hà Nội','Đà Nẵng','Cần Thơ','Bình Dương','Đồng Nai','Hải Phòng','An Giang','Bắc Giang','Bắc Ninh','Bình Định','Bình Thuận','Cà Mau','Đắk Lắk','Đồng Tháp','Gia Lai','Hà Nam','Hà Tĩnh','Hải Dương','Hậu Giang','Hòa Bình','Khánh Hòa','Kiên Giang','Lâm Đồng','Long An','Nam Định','Nghệ An','Ninh Bình','Quảng Bình','Quảng Nam','Quảng Ngãi','Quảng Ninh','Sóc Trăng','Tây Ninh','Thái Bình','Thanh Hóa','Tiền Giang','Trà Vinh','Vĩnh Long','Vĩnh Phúc','Yên Bái'] as $c): ?>
                  <option value="<?= $c ?>" <?= (($user['city'] ?? '') === $c) ? 'selected' : '' ?>><?= $c ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="co-label">Quận / Huyện</label>
                <input type="text" name="district" class="co-input" placeholder="Nhập quận/huyện">
              </div>
              <div style="grid-column:1/-1">
                <label class="co-label">Địa chỉ cụ thể <span style="color:var(--red)">*</span></label>
                <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required class="co-input" placeholder="Số nhà, tên đường, phường/xã...">
              </div>
              <div style="grid-column:1/-1">
                <label class="co-label">Ghi chú đơn hàng</label>
                <textarea name="notes" rows="2" class="co-input" style="resize:vertical" placeholder="Giao giờ hành chính, gọi trước 30 phút..."></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment -->
        <div class="co-card">
          <div class="co-card-head">
            <div style="width:30px;height:30px;background:#fff1f1;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fa-solid fa-credit-card" style="color:var(--red);font-size:.8rem"></i>
            </div>
            <span class="co-card-title">Phương thức thanh toán</span>
          </div>
          <div class="co-card-body" style="padding-top:.8rem">
            <?php
            $pmList = [
              ['cod',   '#fff3e0', '#f97316', 'fa-money-bill-wave',      'Thanh toán khi nhận hàng (COD)',  'Kiểm tra hàng trước khi thanh toán'],
              ['bank',  '#eff6ff', '#3b82f6', 'fa-building-columns',     'Chuyển khoản ngân hàng',           BANK_NAME.' — '.BANK_NO],
              ['momo',  '#fdf2f8', '#a21caf', 'fa-mobile-screen',        'Ví MoMo',                          'Số: '.MOMO_NO.' — '.MOMO_ACCOUNT],
              ['vnpay', '#f0f9ff', '#0284c7', 'fa-credit-card',          'VNPay',                            'Thẻ ATM / VISA / Mastercard / JCB'],
            ];
            foreach($pmList as [$pv, $bg, $clr, $icon, $label, $desc]):
            ?>
            <div class="pm-opt <?= $pv === 'cod' ? 'active' : '' ?>" id="pml-<?= $pv ?>" onclick="selPM('<?= $pv ?>')">
              <div class="pm-ico" style="background:<?= $bg ?>">
                <i class="fa-solid <?= $icon ?>" style="color:<?= $clr ?>"></i>
              </div>
              <input type="radio" name="payment_method" value="<?= $pv ?>" <?= $pv === 'cod' ? 'checked' : '' ?> style="display:none">
              <div style="flex:1;min-width:0">
                <div class="pm-txt-main"><?= $label ?></div>
                <div class="pm-txt-sub"><?= $desc ?></div>
              </div>
              <div class="pm-check"></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <!-- ── RIGHT: Order summary ── -->
      <div style="position:sticky;top:72px">
        <div class="co-card">
          <div class="co-card-head" style="justify-content:space-between">
            <span class="co-card-title"><i class="fa-solid fa-bag-shopping" style="color:var(--red);margin-right:.3rem"></i>Đơn hàng</span>
            <span style="font-size:.72rem;color:#999;font-weight:600"><?= count($items) ?> sản phẩm</span>
          </div>
          <div class="co-card-body" style="padding-top:.6rem;padding-bottom:.6rem">
            <!-- Items -->
            <div style="max-height:300px;overflow-y:auto;margin-bottom:.5rem;padding-right:2px">
              <?php foreach($items as $item):
                $imgUrl = !empty($item['image']) ? UPLOAD_URL . $item['image'] : '';
              ?>
              <div class="co-item">
                <div class="co-item-img">
                  <?php if($imgUrl): ?>
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center">
                      <i class="fa-solid fa-box" style="color:#ccc;font-size:.85rem"></i>
                    </div>
                  <?php else: ?>
                    <i class="fa-solid fa-box" style="color:#ccc;font-size:.85rem"></i>
                  <?php endif; ?>
                  <span class="co-item-qty"><?= (int)$item['quantity'] ?></span>
                </div>
                <div style="flex:1;min-width:0">
                  <div class="co-item-name"><?= htmlspecialchars($item['name']) ?></div>
                  <div style="font-size:.71rem;color:#aaa;margin-top:2px"><?= formatPrice($item['unit_price']) ?> / cái</div>
                </div>
                <div class="co-item-price"><?= formatPrice($item['unit_price'] * $item['quantity']) ?></div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Coupon (cart mode only) -->
            <?php if(($checkoutMode ?? 'cart') !== 'buynow'): ?>
            <div style="border-top:1px solid #f0f0f0;padding-top:.7rem;margin-bottom:.3rem" id="co-coupon-wrap">
              <?php if(!empty($appliedCoupon['code'])): ?>
              <div id="co-coupon-applied" style="display:flex;align-items:center;justify-content:space-between;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.5rem .75rem">
                <div>
                  <span style="font-size:.78rem;color:#16a34a;font-weight:700"><i class="fa-solid fa-tag" style="font-size:.68rem;margin-right:.2rem"></i><span id="co-coupon-badge"><?= htmlspecialchars($appliedCoupon['code']) ?></span></span>
                  <div id="co-coupon-badge-msg" style="font-size:.7rem;color:#22c55e;margin-top:1px"><?= htmlspecialchars($appliedCoupon['message'] ?? '') ?></div>
                </div>
                <button type="button" onclick="coRemoveCoupon()" style="background:none;border:1px solid #fca5a5;color:#ef4444;border-radius:6px;padding:2px 8px;font-size:.7rem;cursor:pointer;flex-shrink:0"><i class="fa-solid fa-times"></i> Xóa</button>
              </div>
              <div id="co-coupon-input" style="display:none;gap:.35rem;margin-top:.4rem"></div>
              <?php else: ?>
              <div id="co-coupon-applied" style="display:none;align-items:center;justify-content:space-between;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.5rem .75rem">
                <div>
                  <span style="font-size:.78rem;color:#16a34a;font-weight:700"><i class="fa-solid fa-tag" style="font-size:.68rem;margin-right:.2rem"></i><span id="co-coupon-badge"></span></span>
                  <div id="co-coupon-badge-msg" style="font-size:.7rem;color:#22c55e;margin-top:1px"></div>
                </div>
                <button type="button" onclick="coRemoveCoupon()" style="background:none;border:1px solid #fca5a5;color:#ef4444;border-radius:6px;padding:2px 8px;font-size:.7rem;cursor:pointer;flex-shrink:0"><i class="fa-solid fa-times"></i> Xóa</button>
              </div>
              <div id="co-coupon-input" style="display:flex;gap:.35rem;margin-top:.4rem">
                <input type="text" id="co-coupon-inp" placeholder="Mã giảm giá..." style="flex:1;padding:.45rem .7rem;border:1.5px solid #e8e8e8;border-radius:8px;font-size:.79rem;outline:none;font-family:inherit;text-transform:uppercase;background:#fafafa" oninput="this.value=this.value.toUpperCase()">
                <button type="button" onclick="coApplyCoupon()" style="padding:.45rem .8rem;background:var(--red);color:#fff;border:none;border-radius:8px;font-size:.79rem;font-weight:700;cursor:pointer;white-space:nowrap">Áp dụng</button>
              </div>
              <?php endif; ?>
              <div id="co-coupon-msg" style="font-size:.72rem;margin-top:.3rem"></div>
            </div>
            <?php endif; ?>

            <!-- Totals -->
            <div style="border-top:1px solid #f0f0f0;padding-top:.6rem">
              <div class="co-total-row">
                <span style="font-size:.82rem;color:#666">Tạm tính</span>
                <span style="font-size:.82rem;font-weight:600" id="co-subtotal-v"><?= formatPrice($subtotal) ?></span>
              </div>
              <div class="co-total-row" style="border-top:none">
                <span style="font-size:.82rem;color:#666;display:flex;align-items:center;gap:.3rem">
                  <i class="fa-solid fa-truck" style="font-size:.7rem"></i> Phí vận chuyển
                </span>
                <span style="font-size:.82rem;font-weight:600;color:<?= $shipping === 0 ? '#22c55e' : '#111' ?>" id="co-shipping-v">
                  <?= $shipping === 0 ? 'Miễn phí' : formatPrice($shipping) ?>
                </span>
              </div>
              <?php if($shipping === 0): ?>
              <div style="font-size:.7rem;color:#22c55e;text-align:right;margin-top:-2px;margin-bottom:2px">
                <i class="fa-solid fa-circle-check" style="font-size:.65rem"></i> Đơn &ge;500K được miễn phí ship
              </div>
              <?php else: ?>
              <div style="font-size:.7rem;color:#999;text-align:right;margin-top:-2px;margin-bottom:2px">
                Mua thêm <?= formatPrice(500000 - $subtotal) ?> để miễn phí ship
              </div>
              <?php endif; ?>
              <div id="co-discount-row" style="display:<?= ($couponDiscount ?? 0) > 0 ? 'flex' : 'none' ?>;justify-content:space-between;align-items:center;padding:.3rem 0;font-size:.82rem">
                <span style="color:#22c55e;display:flex;align-items:center;gap:.3rem"><i class="fa-solid fa-tag" style="font-size:.68rem"></i> Giảm giá</span>
                <span style="color:#22c55e;font-weight:700" id="co-discount-v"><?= ($couponDiscount ?? 0) > 0 ? '-'.formatPrice($couponDiscount) : '' ?></span>
              </div>
              <div style="display:flex;justify-content:space-between;align-items:center;padding:.7rem 0 .3rem;border-top:2px solid #f0f0f0;margin-top:.3rem">
                <span style="font-weight:800;font-size:.9rem">Tổng cộng</span>
                <span style="font-weight:900;font-size:1.2rem;color:var(--red)" id="co-total-v"><?= formatPrice($total) ?></span>
              </div>
            </div>
          </div>

          <!-- CTA -->
          <div style="padding:.75rem 1.25rem 1rem">
            <button type="submit" class="btn-red" style="width:100%;padding:.75rem;font-size:.95rem;font-weight:800;letter-spacing:.01em;justify-content:center;border-radius:10px">
              <i class="fa-solid fa-lock" style="font-size:.8rem"></i> Đặt hàng ngay
            </button>
            <div class="co-trust">
              <span><i class="fa-solid fa-shield-halved" style="color:#22c55e"></i> Bảo mật SSL</span>
              <span style="color:#ddd">|</span>
              <span><i class="fa-solid fa-rotate-left" style="color:#3b82f6"></i> Đổi trả 7 ngày</span>
              <span style="color:#ddd">|</span>
              <span><i class="fa-solid fa-headset" style="color:#f59e0b"></i> Hỗ trợ 24/7</span>
            </div>
            <a href="<?= APP_URL ?>/cart" style="display:block;text-align:center;color:#bbb;font-size:.75rem;margin-top:.5rem;text-decoration:none;transition:color .15s" onmouseover="this.style.color='#555'" onmouseout="this.style.color='#bbb'">
              <i class="fa-solid fa-arrow-left" style="font-size:.65rem"></i> Quay lại giỏ hàng
            </a>
          </div>
        </div>
      </div>

    </div>
  </form>
</div>

<?php
$_fmtTotal = formatPrice($total);
$_bankQr   = 'https://img.vietqr.io/image/'.BANK_BIN.'-'.BANK_NO.'-compact2.png?amount='.$total.'&addInfo=TUANHUY&accountName='.urlencode(BANK_ACCOUNT);
?>

<!-- Bank Transfer Modal -->
<div id="paymodal-bank" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(4px)">
  <div style="background:#1a1a1a;border-radius:18px;width:100%;max-width:540px;position:relative;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.7)">
    <div style="background:#141414;padding:1rem 1.3rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #2a2a2a">
      <div style="display:flex;align-items:center;gap:.55rem">
        <div style="width:32px;height:32px;background:#e30000;border-radius:8px;display:flex;align-items:center;justify-content:center">
          <i class="fa-solid fa-building-columns" style="color:#fff;font-size:.8rem"></i>
        </div>
        <div>
          <div style="font-weight:800;font-size:.93rem;color:#fff">Chuyển khoản ngân hàng</div>
          <div style="font-size:.7rem;color:#666">Quét QR hoặc chuyển thủ công</div>
        </div>
      </div>
      <button onclick="closePayModal('bank')" style="background:#2a2a2a;border:none;width:30px;height:30px;border-radius:50%;cursor:pointer;color:#aaa;font-size:1.1rem;display:flex;align-items:center;justify-content:center">&times;</button>
    </div>
    <div style="display:flex;min-height:0">
      <div style="flex:0 0 190px;background:#111;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.3rem 1rem;border-right:1px solid #222">
        <div style="background:#fff;padding:8px;border-radius:12px;display:inline-block;margin-bottom:.6rem">
          <img src="<?= $_bankQr ?>" alt="QR" style="width:148px;height:148px;display:block;border-radius:6px">
        </div>
        <div style="font-size:.68rem;color:#555;text-align:center;line-height:1.5">Quét bằng app ngân hàng<br>hoặc ví điện tử</div>
      </div>
      <div style="flex:1;padding:1.1rem 1.25rem;display:flex;flex-direction:column;justify-content:space-between">
        <div>
          <div style="display:inline-flex;align-items:center;gap:.35rem;background:#e30000;color:#fff;font-size:.7rem;font-weight:700;padding:.22rem .55rem;border-radius:6px;margin-bottom:.9rem"><?= htmlspecialchars(BANK_NAME) ?></div>
          <?php foreach([
            ['Số tài khoản', BANK_NO, 'bank-no-txt', true],
            ['Chủ tài khoản', BANK_ACCOUNT, null, false],
            ['Số tiền', $_fmtTotal, null, false, '#e30000'],
            ['Nội dung CK', 'TUANHUY', 'bank-note-txt', true],
          ] as $r): ?>
          <div style="margin-bottom:.5rem">
            <div style="font-size:.65rem;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.12rem"><?= $r[0] ?></div>
            <div style="display:flex;align-items:center;gap:.35rem">
              <span id="<?= $r[2] ?? '' ?>" style="font-weight:700;font-size:.86rem;color:<?= $r[4] ?? '#f0f0f0' ?>;font-family:<?= $r[3] ? 'monospace' : 'inherit' ?>"><?= htmlspecialchars($r[1]) ?></span>
              <?php if($r[3] && $r[2]): ?>
              <button onclick="copyTxt('<?= $r[2] ?>')" style="background:#2a2a2a;border:1px solid #3a3a3a;color:#888;border-radius:5px;padding:2px 7px;font-size:.64rem;cursor:pointer;transition:all .15s" onmouseover="this.style.borderColor='#e30000';this.style.color='#e30000'" onmouseout="this.style.borderColor='#3a3a3a';this.style.color='#888'"><i class="fa-solid fa-copy" style="font-size:.6rem"></i> Copy</button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <p style="font-size:.7rem;color:#555;line-height:1.5;margin:0;padding:.55rem .7rem;background:#111;border-radius:8px;border-left:2px solid #e30000">
          Đơn xác nhận trong <b style="color:#e30000">1–2 giờ</b> sau khi nhận được thanh toán.
        </p>
      </div>
    </div>
    <div style="padding:.9rem 1.3rem;border-top:1px solid #2a2a2a;background:#111">
      <button onclick="confirmPay()" style="width:100%;padding:.7rem;background:#e30000;color:#fff;border:none;border-radius:10px;font-weight:800;cursor:pointer;font-size:.88rem;transition:opacity .15s" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-check-circle"></i> Đã chuyển khoản — Đặt hàng ngay
      </button>
    </div>
  </div>
</div>

<!-- MoMo Modal -->
<div id="paymodal-momo" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(3px)">
  <div style="background:#fff;border-radius:16px;padding:0;max-width:360px;width:100%;position:relative;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.25)">
    <div style="background:#a50064;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:.5rem">
        <i class="fa-solid fa-mobile-screen" style="color:#fff;font-size:1.1rem"></i>
        <span style="font-weight:800;font-size:.93rem;color:#fff">Thanh toán MoMo</span>
      </div>
      <button onclick="closePayModal('momo')" style="background:rgba(255,255,255,.15);border:none;width:28px;height:28px;border-radius:50%;cursor:pointer;color:#fff;font-size:1.1rem;display:flex;align-items:center;justify-content:center">&times;</button>
    </div>
    <div style="padding:1.25rem">
      <div style="background:#fdf2f8;border-radius:11px;padding:.9rem;font-size:.83rem;margin-bottom:1rem">
        <?php
        $momoRows = [
          ['Số điện thoại', MOMO_NO, 'momo-no-txt'],
          ['Tên tài khoản', MOMO_ACCOUNT, null],
          ['Số tiền', $_fmtTotal, null],
          ['Nội dung', 'TUANHUY', 'momo-note-txt'],
        ];
        $momoLast = count($momoRows) - 1;
        foreach($momoRows as $mi => $mr):
        ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.25rem 0;<?= $mi < $momoLast ? 'border-bottom:1px solid #f0d6e8' : '' ?>">
          <span style="color:#888"><?= $mr[0] ?></span>
          <div style="display:flex;align-items:center;gap:.3rem">
            <b id="<?= $mr[2] ?? '' ?>" style="color:<?= $mr[0]==='Số tiền'?'#a50064':'#111' ?>"><?= htmlspecialchars($mr[1]) ?></b>
            <?php if($mr[2]): ?><button onclick="copyTxt('<?= $mr[2] ?>')" style="background:none;border:1px solid #ddd;border-radius:4px;padding:1px 6px;font-size:.68rem;cursor:pointer;color:#888">Copy</button><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <p style="font-size:.74rem;color:#888;margin-bottom:1rem;line-height:1.55">Mở ứng dụng MoMo → Chuyển tiền → nhập số điện thoại trên. Đơn hàng xác nhận sau khi nhận được thanh toán.</p>
      <button onclick="confirmPay()" style="width:100%;padding:.7rem;background:#a50064;color:#fff;border:none;border-radius:10px;font-weight:800;cursor:pointer;font-size:.9rem">
        <i class="fa-solid fa-check"></i> Đã chuyển MoMo — Đặt hàng
      </button>
    </div>
  </div>
</div>

<!-- VNPay Modal -->
<div id="paymodal-vnpay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(3px)">
  <div style="background:#fff;border-radius:16px;padding:0;max-width:380px;width:100%;position:relative;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.25)">
    <div style="background:#0062cc;padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:.5rem">
        <i class="fa-solid fa-credit-card" style="color:#fff;font-size:1.1rem"></i>
        <span style="font-weight:800;font-size:.93rem;color:#fff">Thanh toán VNPay</span>
      </div>
      <button onclick="closePayModal('vnpay')" style="background:rgba(255,255,255,.15);border:none;width:28px;height:28px;border-radius:50%;cursor:pointer;color:#fff;font-size:1.1rem;display:flex;align-items:center;justify-content:center">&times;</button>
    </div>
    <div style="padding:1.25rem">
      <div style="background:#eff6ff;border-radius:10px;padding:.9rem;margin-bottom:.9rem;font-size:.83rem;line-height:1.6">
        <p style="margin:0;color:#1d4ed8">Sau khi đặt hàng, chúng tôi gửi link thanh toán VNPay qua email và SMS trong <b>15 phút</b>.</p>
        <p style="margin:.5rem 0 0;color:#333">Số tiền: <b style="color:#e30000"><?= $_fmtTotal ?></b></p>
      </div>
      <div style="background:#f8f8f8;border-radius:10px;padding:.8rem;font-size:.8rem;color:#555;margin-bottom:.9rem">
        <i class="fa-solid fa-circle-check" style="color:#22c55e;margin-right:.25rem"></i> Thẻ ATM nội địa, VISA, Mastercard, JCB, Ví điện tử
      </div>
      <button onclick="confirmPay()" style="width:100%;padding:.7rem;background:#0062cc;color:#fff;border:none;border-radius:10px;font-weight:800;cursor:pointer;font-size:.9rem">
        <i class="fa-solid fa-check"></i> Đồng ý — Đặt hàng
      </button>
    </div>
  </div>
</div>

<script>
var _coBase = '<?= APP_URL ?>';
var _coCouponDisc = <?= (float)($couponDiscount ?? 0) ?>;

function coApplyCoupon(){
  var inp = document.getElementById('co-coupon-inp');
  var code = inp ? inp.value.trim() : '';
  if(!code) return;
  fetch(_coBase+'/api/coupon/check',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({code:code})})
  .then(function(r){return r.json();}).then(function(d){
    var msg = document.getElementById('co-coupon-msg');
    if(d.success){
      _coCouponDisc = d.discount;
      msg.textContent = '';
      document.getElementById('co-coupon-badge').textContent = d.code || code.toUpperCase();
      document.getElementById('co-coupon-badge-msg').textContent = d.message || '';
      document.getElementById('co-coupon-applied').style.display = 'flex';
      document.getElementById('co-coupon-input').style.display = 'none';
      document.getElementById('co-discount-row').style.display = 'flex';
      document.getElementById('co-discount-v').textContent = '-' + fmtPrice(d.discount);
      document.getElementById('co-total-v').textContent = fmtPrice(d.new_total);
    } else {
      msg.style.color = '#ef4444'; msg.textContent = d.message;
    }
  });
}

function coRemoveCoupon(){
  fetch(_coBase+'/api/coupon/remove',{method:'POST',headers:{'Content-Type':'application/json'}})
  .then(function(r){return r.json();}).then(function(d){
    _coCouponDisc = 0;
    document.getElementById('co-coupon-applied').style.display = 'none';
    document.getElementById('co-coupon-input').style.display = 'flex';
    var inp = document.getElementById('co-coupon-inp');
    if(inp) inp.value = '';
    document.getElementById('co-coupon-msg').textContent = '';
    document.getElementById('co-discount-row').style.display = 'none';
    document.getElementById('co-discount-v').textContent = '';
    if(d.success){
      document.getElementById('co-total-v').textContent = fmtPrice(d.new_total);
    }
  });
}

var _coForm = document.getElementById('co-form');
_coForm.addEventListener('submit', function(e){
  var pm = (document.querySelector('input[name=payment_method]:checked') || {}).value || 'cod';
  if(pm !== 'cod'){
    e.preventDefault();
    if(pm === 'bank'){
      var phone = (_coForm.querySelector('input[name=phone]') || {}).value || '';
      var suffix = phone.replace(/\D/g,'').slice(-4);
      var noteEl = document.getElementById('bank-note-txt');
      if(noteEl) noteEl.textContent = 'TUANHUY' + (suffix ? ' ' + suffix : '');
    }
    document.getElementById('paymodal-'+pm).style.display = 'flex';
  }
});
function closePayModal(pm){ document.getElementById('paymodal-'+pm).style.display = 'none'; }
function confirmPay(){ _coForm.submit(); }
function copyTxt(id){
  var el = document.getElementById(id);
  if(!el) return;
  var t = document.createElement('textarea');
  t.value = el.textContent; document.body.appendChild(t); t.select();
  try{ document.execCommand('copy'); }catch(e){}
  document.body.removeChild(t);
  var orig = el.style.color;
  el.style.color = '#22c55e';
  setTimeout(function(){ el.style.color = orig; }, 1200);
}
function selPM(v){
  ['cod','bank','momo','vnpay'].forEach(function(m){
    var el = document.getElementById('pml-'+m);
    var rd = el ? el.querySelector('input[type=radio]') : null;
    if(!el) return;
    if(m === v){
      el.classList.add('active');
      if(rd) rd.checked = true;
    } else {
      el.classList.remove('active');
      if(rd) rd.checked = false;
    }
  });
}
// Close modal on backdrop click
document.querySelectorAll('[id^="paymodal-"]').forEach(function(modal){
  modal.addEventListener('click', function(e){ if(e.target === modal) modal.style.display='none'; });
});
</script>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
