<?php $pageTitle='Giỏ hàng'; require_once __DIR__.'/../layouts/header.php'; ?>
<div style="max-width:1280px;margin:1.25rem auto;padding:0 1rem">
  <h1 style="font-size:1.2rem;font-weight:800;color:#111;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem"><i class="fa-solid fa-shopping-cart" style="color:var(--red)"></i> Giỏ hàng <span style="font-size:.85rem;font-weight:400;color:#999">(<?= count($items) ?> sản phẩm)</span></h1>
  <?php if(empty($items)): ?>
  <div style="text-align:center;padding:5rem 1rem;background:#fff;border-radius:14px">
    <div style="font-size:4.5rem;margin-bottom:1rem">🛒</div>
    <p style="font-size:1rem;color:#555;margin-bottom:1.25rem">Giỏ hàng trống!</p>
    <a href="<?= APP_URL ?>/products" class="btn-red">Tiếp tục mua sắm</a>
  </div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;align-items:start">
    <!-- Items -->
    <div style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05)">
      <div style="padding:.85rem 1.1rem;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center">
        <span style="font-weight:700;font-size:.875rem">Sản phẩm</span>
        <a href="<?= APP_URL ?>/cart/clear" onclick="return confirm('Xóa toàn bộ giỏ?')" style="color:#ef4444;font-size:.78rem;text-decoration:none"><i class="fa-solid fa-trash mr-1"></i>Xóa tất cả</a>
      </div>
      <?php foreach($items as $item): ?>
      <div style="display:flex;gap:.9rem;padding:1rem 1.1rem;border-bottom:1px solid #f9f9f9;align-items:center" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
        <div style="width:72px;height:72px;background:#f5f5f5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2.2rem;flex-shrink:0">📦</div>
        <div style="flex:1;min-width:0">
          <a href="<?= APP_URL ?>/products/detail/<?= $item['slug'] ?>" style="font-weight:600;font-size:.875rem;color:#111;text-decoration:none;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= htmlspecialchars($item['name']) ?></a>
          <div style="color:var(--red);font-weight:700;font-size:.875rem;margin-top:.25rem"><?= formatPrice($item['unit_price']) ?></div>
        </div>
        <div style="display:flex;align-items:center;border:1.5px solid #e5e5e5;border-radius:7px;overflow:hidden">
          <button onclick="updateQty(<?= $item['id'] ?>,-1)" style="padding:.3rem .6rem;background:#f8f8f8;border:none;cursor:pointer;font-size:.9rem">−</button>
          <span id="qty-<?= $item['id'] ?>" style="padding:.3rem .6rem;font-weight:600;font-size:.875rem;min-width:30px;text-align:center"><?= $item['quantity'] ?></span>
          <button onclick="updateQty(<?= $item['id'] ?>,1)" style="padding:.3rem .6rem;background:#f8f8f8;border:none;cursor:pointer;font-size:.9rem">+</button>
        </div>
        <div id="stotal-<?= $item['id'] ?>" style="font-weight:700;min-width:90px;text-align:right;font-size:.875rem"><?= formatPrice($item['unit_price']*$item['quantity']) ?></div>
        <a href="<?= APP_URL ?>/cart/remove/<?= $item['id'] ?>" onclick="return confirm('Xóa?')" style="color:#ccc;transition:color .2s;margin-left:.25rem" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#ccc'"><i class="fa-solid fa-times"></i></a>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Summary -->
    <div>
      <div style="background:#fff;border-radius:12px;padding:1.1rem;box-shadow:0 2px 12px rgba(0,0,0,.05);margin-bottom:.85rem">
        <h3 style="font-weight:700;font-size:.875rem;margin-bottom:.65rem"><i class="fa-solid fa-tag" style="color:var(--red);margin-right:.35rem"></i>Mã giảm giá</h3>
        <div style="display:flex;gap:.4rem">
          <input type="text" id="coupon-inp" placeholder="Nhập mã..." class="form-input" style="font-size:.82rem;text-transform:uppercase">
          <button onclick="applyCoupon()" class="btn-red" style="padding:.4rem .8rem;font-size:.82rem;white-space:nowrap">Áp dụng</button>
        </div>
        <div id="coupon-msg" style="font-size:.75rem;margin-top:.35rem"></div>
      </div>
      <div style="background:#fff;border-radius:12px;padding:1.1rem;box-shadow:0 2px 12px rgba(0,0,0,.05)">
        <h3 style="font-weight:700;font-size:.875rem;margin-bottom:.9rem">Tóm tắt đơn hàng</h3>
        <div style="display:flex;justify-content:space-between;font-size:.875rem;padding:.35rem 0;border-bottom:1px solid #f5f5f5"><span style="color:#555">Tạm tính</span><span id="subtotal-v" style="font-weight:600"><?= formatPrice($subtotal) ?></span></div>
        <div style="display:flex;justify-content:space-between;font-size:.875rem;padding:.35rem 0;border-bottom:1px solid #f5f5f5"><span style="color:#555">Phí vận chuyển</span><span id="shipping-v" style="font-weight:600;color:<?= $shipping===0?'#22c55e':'#111' ?>"><?= $shipping===0?'Miễn phí':formatPrice($shipping) ?></span></div>
        <div id="discount-row" style="display:none;justify-content:space-between;font-size:.875rem;padding:.35rem 0;border-bottom:1px solid #f5f5f5"><span style="color:#22c55e">Giảm giá</span><span id="discount-v" style="color:#22c55e;font-weight:600"></span></div>
        <div style="display:flex;justify-content:space-between;padding:.6rem 0;margin-top:.2rem">
          <span style="font-weight:800;font-size:.95rem">Tổng cộng</span>
          <span id="total-v" style="font-weight:900;font-size:1.15rem;color:var(--red)"><?= formatPrice($total) ?></span>
        </div>
        <?php if($shipping>0): ?><p style="font-size:.75rem;color:#22c55e;margin-bottom:.65rem">🚚 Mua thêm <?= formatPrice(500000-$subtotal) ?> để free ship!</p><?php endif; ?>
        <a href="<?= APP_URL ?>/checkout" class="btn-red" style="display:block;text-align:center;text-decoration:none;padding:.65rem;font-size:.95rem;border-radius:9px;animation:pulseRed 2s infinite"><i class="fa-solid fa-lock mr-1"></i>Thanh toán ngay</a>
        <a href="<?= APP_URL ?>/products" style="display:block;text-align:center;color:#999;font-size:.8rem;margin-top:.6rem;text-decoration:none">← Tiếp tục mua sắm</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<script>
var BASE='<?= APP_URL ?>';
function updateQty(id,delta){
  var span=document.getElementById('qty-'+id);
  var qty=parseInt(span.textContent)+delta;
  if(qty<1) return;
  span.textContent=qty;
  fetch(BASE+'/api/cart/update',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({cart_id:id,quantity:qty})})
  .then(function(r){return r.json();}).then(function(d){
    if(d.success){
      var st=document.getElementById('stotal-'+id);
      if(st) st.textContent=fmtPrice(d.item_subtotal);
      document.getElementById('subtotal-v').textContent=fmtPrice(d.subtotal);
      document.getElementById('shipping-v').textContent=d.shipping===0?'Miễn phí':fmtPrice(d.shipping);
      document.getElementById('shipping-v').style.color=d.shipping===0?'#22c55e':'#111';
      document.getElementById('total-v').textContent=fmtPrice(d.total);
      updateCartBadge(d.cart_count);
    }
  });
}
function applyCoupon(){
  var code=document.getElementById('coupon-inp').value.trim();
  if(!code)return;
  fetch(BASE+'/api/coupon/check',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({code:code})})
  .then(function(r){return r.json();}).then(function(d){
    var msg=document.getElementById('coupon-msg');
    if(d.success){
      msg.style.color='#22c55e';msg.textContent='✅ '+d.message;
      document.getElementById('discount-row').style.display='flex';
      document.getElementById('discount-v').textContent='-'+fmtPrice(d.discount);
      document.getElementById('total-v').textContent=fmtPrice(d.new_total);
    } else {msg.style.color='#ef4444';msg.textContent='❌ '+d.message;}
  });
}
</script>
<?php require_once __DIR__.'/../layouts/footer.php'; ?>
