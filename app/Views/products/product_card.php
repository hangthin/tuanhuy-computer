<?php
$ci=array('1'=>'fa-desktop','2'=>'fa-laptop','3'=>'fa-tv','4'=>'fa-computer-mouse','5'=>'fa-keyboard','6'=>'fa-memory','7'=>'fa-microchip','8'=>'fa-hard-drive','9'=>'fa-hdd','10'=>'fa-server','11'=>'fa-headphones');
$icon=isset($ci[$p['category_id']])?$ci[$p['category_id']]:'fa-box';
$sale=!empty($p['sale_price'])&&(float)$p['sale_price']>0&&(float)$p['sale_price']<(float)$p['price'];
$ok=($p['stock']??0)>0;
// Tính discount_pct nếu query không trả về
if($sale && empty($p['discount_pct'])){
    $p['discount_pct']=round((1-(float)$p['sale_price']/(float)$p['price'])*100);
}
$discPct = $sale ? (int)($p['discount_pct']??0) : 0;
?>
<div class="product-card">
  <!-- Badges -->
  <div style="position:absolute;top:8px;left:8px;z-index:3;display:flex;flex-direction:column;gap:3px">
    <?php if($sale&&$discPct>0): ?><span class="badge-sale">-<?= $discPct ?>%</span><?php endif; ?>
    <?php if($p['is_new']): ?><span class="badge-new"><i class="fa-solid fa-bolt" style="font-size:.55rem"></i> MỚI</span><?php endif; ?>
    <?php if($p['is_featured']): ?><span style="background:#f59e0b;color:#fff;font-size:.63rem;font-weight:700;padding:2px 6px;border-radius:4px"><i class="fa-solid fa-fire" style="font-size:.55rem"></i> HOT</span><?php endif; ?>
  </div>
  <!-- Image -->
  <a href="<?= APP_URL ?>/products/detail/<?= $p['slug'] ?>" style="display:block">
    <div style="height:clamp(130px,18vw,172px);background:#f5f6f8;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden">
      <?php if(!empty($p['image']) && $p['image']!=='default.jpg'): ?>
        <img src="<?= UPLOAD_URL.htmlspecialchars($p['image']) ?>"
             alt="<?= htmlspecialchars($p['name']) ?>"
             loading="lazy"
             style="width:100%;height:100%;object-fit:contain;padding:8px"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:#f5f6f8">
          <i class="fa-solid <?= $icon ?>" style="font-size:3.5rem;color:#c8cdd5"></i>
        </div>
      <?php else: ?>
        <i class="fa-solid <?= $icon ?>" style="font-size:3.5rem;color:#c8cdd5"></i>
      <?php endif; ?>
      <div class="card-actions">
        <button onclick="event.preventDefault();guardedAddToCart(<?= $p['id'] ?>,1,this)" <?= !$ok?'disabled':'' ?>
          style="display:flex;align-items:center;gap:.3rem;background:var(--red);color:#fff;border:none;padding:.38rem .75rem;border-radius:7px;font-size:.74rem;font-weight:600;cursor:pointer;font-family:var(--font)<?= !$ok?';opacity:.5;cursor:not-allowed':'' ?>">
          <i class="fa-solid fa-cart-plus"></i>Thêm giỏ
        </button>
        <a href="<?= APP_URL ?>/products/detail/<?= $p['slug'] ?>"
          style="display:flex;align-items:center;background:rgba(255,255,255,.18);color:#fff;padding:.38rem .55rem;border-radius:7px;font-size:.78rem">
          <i class="fa-solid fa-eye"></i>
        </a>
      </div>
    </div>
  </a>
  <!-- Info -->
  <div style="padding:.85rem">
    <?php if(!empty($p['brand_name'])): ?>
    <div style="font-size:.66rem;color:#9ca3af;margin-bottom:.18rem;display:flex;align-items:center;gap:.28rem">
      <i class="fa-solid fa-tag" style="font-size:.58rem;color:var(--red);opacity:.7"></i><?= htmlspecialchars($p['brand_name']) ?>
    </div>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/products/detail/<?= $p['slug'] ?>" style="text-decoration:none">
      <h3 style="font-size:.82rem;font-weight:600;color:#111;line-height:1.42;margin-bottom:.3rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
        <?= htmlspecialchars($p['name']) ?>
      </h3>
    </a>
    <?php if($p['review_count']>0): ?>
    <div style="display:flex;align-items:center;gap:.28rem;margin-bottom:.28rem">
      <div class="stars" style="font-size:.63rem">
        <?php for($i=1;$i<=5;$i++): ?><i class="fa-<?= $i<=$p['rating']?'solid':'regular' ?> fa-star"></i><?php endfor; ?>
      </div>
      <span style="font-size:.66rem;color:#9ca3af">(<?= $p['review_count'] ?>)</span>
    </div>
    <?php endif; ?>
    <div style="margin-bottom:.42rem">
      <span class="price-final" style="font-size:1rem"><?= formatPrice($p['final_price']) ?></span>
      <?php if($sale): ?><span class="price-old" style="margin-left:5px"><?= formatPrice($p['price']) ?></span><?php endif; ?>
    </div>
    <div style="font-size:.66rem;display:flex;align-items:center;gap:.28rem;color:<?= $ok?'#16a34a':'#dc2626' ?>;margin-bottom:.5rem">
      <i class="fa-solid <?= $ok?'fa-circle-check':'fa-circle-xmark' ?>"></i>
      <?= $ok?'Còn hàng ('.$p['stock'].')':'Hết hàng' ?>
    </div>
    <div style="display:flex;gap:.35rem">
      <button onclick="guardedAddToCart(<?= $p['id'] ?>,1,this)" <?= !$ok?'disabled':'' ?>
        style="flex:1;display:flex;align-items:center;justify-content:center;gap:.32rem;background:var(--red);color:#fff;border:none;padding:.44rem;border-radius:8px;font-size:.74rem;font-weight:600;cursor:pointer;font-family:var(--font);transition:background .18s<?= !$ok?';opacity:.45;cursor:not-allowed':'' ?>"
        onmouseover="if(!this.disabled)this.style.background='var(--red-dk)'" onmouseout="if(!this.disabled)this.style.background='var(--red)'">
        <i class="fa-solid fa-cart-plus"></i>Thêm giỏ
      </button>
      <button onclick="guardedBuyNow(<?= $p['id'] ?>)" <?= !$ok?'disabled':'' ?>
        title="Mua ngay"
        style="display:flex;align-items:center;justify-content:center;background:#111;color:#fff;border:none;padding:.44rem .6rem;border-radius:8px;font-size:.8rem;cursor:pointer;transition:background .18s<?= !$ok?';opacity:.45;cursor:not-allowed':'' ?>"
        onmouseover="if(!this.disabled)this.style.background='#2a2a2a'" onmouseout="if(!this.disabled)this.style.background='#111'">
        <i class="fa-solid fa-bolt"></i>
      </button>
    </div>
  </div>
</div>
