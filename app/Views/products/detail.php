<?php require_once __DIR__.'/../layouts/header.php'; ?>
<style>
.det-wrap{max-width:1280px;margin:1.25rem auto;padding:0 1rem}
.bc{font-size:.73rem;color:#9ca3af;margin-bottom:1rem;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap}
.bc a{color:#9ca3af;transition:color .18s}.bc a:hover{color:var(--red)}
.bc i{font-size:.55rem;color:#d1d5db}
.det-card{background:#fff;border-radius:var(--r-lg);padding:1.75rem;display:grid;grid-template-columns:1fr 1fr;gap:2.25rem;box-shadow:var(--shadow-md);margin-bottom:1.25rem}
.det-img{background:linear-gradient(135deg,#f9fafb,#f3f4f6);border-radius:var(--r-lg);min-height:320px;max-height:400px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;border:1px solid var(--border)}
.det-img i{font-size:6rem;color:#d1d5db}
/* Gallery */
.gallery-wrap{display:flex;flex-direction:column;gap:.65rem}
.thumb-row{display:flex;gap:.5rem;flex-wrap:nowrap;overflow-x:auto;padding-bottom:2px}
.thumb-row::-webkit-scrollbar{height:3px}.thumb-row::-webkit-scrollbar-thumb{background:#ddd;border-radius:99px}
.thumb-item{width:72px;height:72px;flex-shrink:0;border-radius:8px;overflow:hidden;border:2px solid #e5e7eb;cursor:pointer;background:#f9fafb;display:flex;align-items:center;justify-content:center;transition:border-color .18s}
.thumb-item:hover{border-color:var(--red)}
.thumb-item.active{border-color:var(--red);box-shadow:0 0 0 1px var(--red)}
.thumb-item img{width:100%;height:100%;object-fit:cover}
/* Lightbox */
#lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out}
.det-badges{display:flex;gap:.35rem;margin-bottom:.6rem;flex-wrap:wrap}
.det-brand{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;background:#f4f4f5;color:#6b7280;padding:2px 8px;border-radius:99px}
.det-h1{font-size:1.2rem;font-weight:800;color:#111;line-height:1.35;margin-bottom:.7rem}
.det-stars{display:flex;align-items:center;gap:.5rem;margin-bottom:.85rem}
.price-box{background:linear-gradient(135deg,#fff0f0,#fff7f7);border:1px solid #ffd0d0;border-radius:var(--r-lg);padding:1.1rem;margin-bottom:.9rem}
.price-main{font-size:1.85rem;font-weight:900;color:var(--red)}
.price-save{display:inline-flex;align-items:center;gap:.3rem;background:rgba(227,0,0,.08);color:var(--red);font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:99px;margin-left:.5rem}
.info-pills{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.9rem}
.info-pill{display:inline-flex;align-items:center;gap:.3rem;font-size:.75rem;color:#6b7280;background:#f9fafb;border:1px solid var(--border);padding:.25rem .6rem;border-radius:99px}
.info-pill i{color:var(--red);font-size:.72rem}
.qty-box{display:flex;align-items:center;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;width:fit-content}
.qty-box button{padding:.35rem .75rem;background:#f9fafb;border:none;cursor:pointer;font-size:1rem;color:#555;transition:.18s;width:36px;font-family:var(--font)}
.qty-box button:hover{background:#f0f0f0}
.qty-box input{width:46px;text-align:center;border:none;border-left:1px solid var(--border);border-right:1px solid var(--border);font-weight:700;font-size:.9rem;font-family:var(--font);outline:none;padding:.35rem 0}
.cta-row{display:flex;gap:.65rem;flex-wrap:wrap;margin-bottom:1.1rem}
.trust-row{display:flex;gap:.35rem;flex-wrap:wrap}
.trust-pill{background:#f9fafb;border:1px solid var(--border);padding:.22rem .65rem;border-radius:99px;font-size:.7rem;color:#6b7280}

/* Tabs */
.tabs-nav{display:flex;border-bottom:2px solid #f0f0f0}
.tab-btn{padding:.7rem 1.25rem;border:none;background:none;cursor:pointer;font-weight:600;font-size:.84rem;color:#9ca3af;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.18s;font-family:var(--font)}
.tab-btn.on{color:var(--red);border-bottom-color:var(--red)}
.tab-content{padding:1.25rem}
.spec-table{width:100%;border-collapse:collapse}
.spec-table tr:nth-child(even) td{background:#f9fafb}
.spec-table td{padding:.55rem .75rem;font-size:.85rem;border-bottom:1px solid #f0f0f0}
.spec-table td:first-child{font-weight:600;color:#374151;min-width:180px}
.spec-table td:last-child{color:#6b7280}

/* Review */
.rv-item{padding:.9rem 0;border-bottom:1px solid #f5f5f5}
.rv-av{width:36px;height:36px;background:var(--red);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.82rem;flex-shrink:0}

@media(max-width:768px){
  .det-card{grid-template-columns:1fr;gap:1.25rem}
  .det-img{height:240px}
}
</style>

<div class="det-wrap">
  <!-- Breadcrumb -->
  <div class="bc">
    <a href="<?= APP_URL ?>/">Trang chủ</a><i class="fa-solid fa-chevron-right"></i>
    <a href="<?= APP_URL ?>/products/<?= $product['category_slug'] ?>"><?= htmlspecialchars($product['category_name']) ?></a><i class="fa-solid fa-chevron-right"></i>
    <span style="color:#374151"><?= htmlspecialchars(mb_substr($product['name'],0,55)) ?>...</span>
  </div>

  <!-- Main -->
  <div class="det-card">
    <!-- Image Gallery -->
    <div class="gallery-wrap">
      <?php
      $ci=array('1'=>'fa-desktop','2'=>'fa-laptop','3'=>'fa-tv','4'=>'fa-computer-mouse','5'=>'fa-keyboard','6'=>'fa-memory','7'=>'fa-microchip','8'=>'fa-hard-drive','9'=>'fa-hdd','10'=>'fa-server','11'=>'fa-headphones');
      $icon=isset($ci[$product['category_id']])?$ci[$product['category_id']]:'fa-box';
      // Build all images: main image + extra images from product_images table
      $allImgs = array();
      // Main product image
      if(!empty($product['image'])&&$product['image']!=='default.jpg'&&$product['image']!==null){
          $mainSrc = UPLOAD_URL.htmlspecialchars($product['image']);
          $allImgs[] = array('src'=>$mainSrc,'alt'=>htmlspecialchars($product['name']),'is_main'=>true);
      }
      // Extra images from product_images table
      if(!empty($productImages)){
          foreach($productImages as $pi){
              if(!empty($pi['image'])){
                  $allImgs[]=array('src'=>UPLOAD_URL.htmlspecialchars($pi['image']),'alt'=>htmlspecialchars($product['name']),'is_main'=>false);
              }
          }
      }
      $hasImgs = !empty($allImgs);
      // Debug: log image count (remove in production)
      // error_log("Product {$product['id']} has " . count($allImgs) . " images, productImages count: " . count($productImages));
      ?>

      <!-- Main image display -->
      <div class="det-img" style="position:relative;height:340px">
        <?php if(!empty($product['sale_price'])&&(float)$product['sale_price']>0&&(float)$product['sale_price']<(float)$product['price']): ?>
        <span class="badge-sale" style="position:absolute;top:12px;left:12px;font-size:.8rem;z-index:2">
          -<?= isset($product['discount_pct'])&&$product['discount_pct']>0 ? $product['discount_pct'] : round((1-(float)$product['sale_price']/(float)$product['price'])*100) ?>%
        </span>
        <?php endif; ?>
        <?php if($hasImgs): ?>
          <img id="main-img" src="<?= $allImgs[0]['src'] ?>" alt="<?= $allImgs[0]['alt'] ?>"
               style="width:100%;height:100%;object-fit:contain;padding:16px;cursor:zoom-in;transition:opacity .25s;position:absolute;inset:0"
               onclick="openLightbox(this.src)"
               onerror="this.style.display='none';document.getElementById('main-icon').style.display='flex'">
          <div id="main-icon" style="display:none;width:100%;height:100%;align-items:center;justify-content:center">
            <i class="fa-solid <?= $icon ?>"></i>
          </div>
        <?php else: ?>
          <i class="fa-solid <?= $icon ?>"></i>
        <?php endif; ?>
      </div>

      <!-- Thumbnails -->
      <?php if(count($allImgs)>=1): ?>
      <div class="thumb-row" id="thumb-row">
        <?php foreach($allImgs as $i=>$img): ?>
        <div class="thumb-item <?= $i===0?'active':'' ?>" onclick="switchImg('<?= $img['src'] ?>',this)">
          <img src="<?= $img['src'] ?>" alt="<?= $img['alt'] ?>" loading="lazy"
               onerror="this.parentElement.style.display='none'">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out" onclick="closeLightbox()">
      <img id="lb-img" src="" alt="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px">
      <button onclick="closeLightbox()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:2rem;cursor:pointer;line-height:1">&times;</button>
    </div>

    <!-- Info -->
    <div>
      <div class="det-badges">
        <?php if(!empty($product['brand_name'])): ?>
        <span class="det-brand"><i class="fa-solid fa-tag" style="font-size:.6rem;color:var(--red)"></i><?= htmlspecialchars($product['brand_name']) ?></span>
        <?php endif; ?>
        <span class="det-brand"><i class="fa-solid fa-layer-group" style="font-size:.6rem;color:var(--red)"></i><?= htmlspecialchars($product['category_name']) ?></span>
        <?php if($product['is_new']): ?><span class="badge-new"><i class="fa-solid fa-bolt" style="font-size:.6rem"></i> MỚI</span><?php endif; ?>
      </div>

      <h1 class="det-h1"><?= htmlspecialchars($product['name']) ?></h1>

      <div class="det-stars">
        <div class="stars" style="font-size:.82rem">
          <?php for($i=1;$i<=5;$i++): ?><i class="fa-<?= $i<=$product['rating']?'solid':'regular' ?> fa-star"></i><?php endfor; ?>
        </div>
        <span style="font-size:.8rem;color:#9ca3af"><?= $product['rating'] ?>/5 (<?= $product['review_count'] ?> đánh giá)</span>
        <span style="color:#d1d5db;font-size:.7rem">|</span>
        <span style="font-size:.8rem;color:#9ca3af"><i class="fa-solid fa-bag-shopping" style="font-size:.72rem;margin-right:3px"></i>Đã bán <?= $product['sold'] ?></span>
      </div>

      <!-- Price -->
      <div class="price-box">
        <div>
          <span class="price-main"><?= formatPrice($product['final_price']) ?></span>
          <?php if(!empty($product['sale_price'])&&$product['sale_price']>0&&$product['sale_price']<$product['price']): ?>
          <span class="price-save"><i class="fa-solid fa-bolt" style="font-size:.6rem"></i>Tiết kiệm <?= formatPrice($product['price']-$product['sale_price']) ?></span>
          <?php endif; ?>
        </div>
        <?php if(!empty($product['sale_price'])&&$product['sale_price']>0&&$product['sale_price']<$product['price']): ?>
        <div style="font-size:.82rem;color:#9ca3af;text-decoration:line-through;margin-top:.2rem"><?= formatPrice($product['price']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Short desc -->
      <?php if(!empty($product['short_desc'])): ?>
      <p style="color:#6b7280;font-size:.84rem;line-height:1.7;margin-bottom:.9rem"><?= nl2br(htmlspecialchars($product['short_desc'])) ?></p>
      <?php endif; ?>

      <!-- Info pills -->
      <div class="info-pills">
        <span class="info-pill" style="color:<?= ($product['stock']??0)>0?'#16a34a':'#dc2626' ?>">
          <i class="fa-solid <?= ($product['stock']??0)>0?'fa-circle-check':'fa-circle-xmark' ?>"></i>
          <?= ($product['stock']??0)>0?'Còn hàng ('.$product['stock'].')':'Hết hàng' ?>
        </span>
        <span class="info-pill"><i class="fa-solid fa-shield-halved"></i>BH <?= $product['warranty'] ?> tháng</span>
        <span class="info-pill"><i class="fa-solid fa-truck-fast"></i>Free ship &ge;500K</span>
        <span class="info-pill"><i class="fa-solid fa-rotate-left"></i>Đổi trả 30 ngày</span>
      </div>

      <!-- Quantity -->
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem">
        <span style="font-size:.84rem;font-weight:600;color:#374151">Số lượng:</span>
        <div class="qty-box">
          <button onclick="qtyChg(-1)">−</button>
          <input type="number" id="qty" value="1" min="1" max="<?= $product['stock']??99 ?>">
          <button onclick="qtyChg(1)">+</button>
        </div>
        <span style="font-size:.78rem;color:#9ca3af">Còn <?= $product['stock']??0 ?> SP</span>
      </div>

      <!-- CTA -->
      <div class="cta-row">
        <button onclick="detGuardCart(<?= $product['id'] ?>)" <?= ($product['stock']??0)<1?'disabled':'' ?>
          id="btn-cart"
          class="btn-red" style="flex:1;min-width:160px;padding:.68rem;font-size:.88rem;justify-content:center<?= ($product['stock']??0)<1?';opacity:.45;cursor:not-allowed':'' ?>">
          <i class="fa-solid fa-cart-plus"></i>Thêm vào giỏ hàng
        </button>
        <button onclick="detGuardBuyNow(<?= $product['id'] ?>)" <?= ($product['stock']??0)<1?'disabled':'' ?>
          class="btn-dark" style="flex:1;min-width:120px;padding:.68rem;font-size:.88rem;justify-content:center<?= ($product['stock']??0)<1?';opacity:.45;cursor:not-allowed':'' ?>">
          <i class="fa-solid fa-bolt"></i>Mua ngay
        </button>
      </div>

      <!-- Trust -->
      <div class="trust-row">
        <?php foreach(array('✅ Hàng chính hãng','🔄 Đổi trả 30 ngày','🛡️ BH chính hãng','🚚 Free ship') as $t): ?>
        <span class="trust-pill"><?= $t ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="bg-white" style="background:#fff;border-radius:var(--r-lg);box-shadow:var(--shadow);margin-bottom:1.25rem;overflow:hidden">
    <div class="tabs-nav">
      <?php foreach(array(array('desc','Mô tả'),array('specs','Thông số'),array('reviews','Đánh giá ('.$product['review_count'].')) ')) as $idx=>$tab): ?>
      <button class="tab-btn <?= $idx===0?'on':'' ?>" onclick="switchTab('<?= $tab[0] ?>')" id="tab-<?= $tab[0] ?>"><?= $tab[1] ?></button>
      <?php endforeach; ?>
    </div>

    <!-- Desc -->
    <div class="tab-content" id="tc-desc">
      <p style="color:#6b7280;line-height:1.8;font-size:.875rem"><?= nl2br(htmlspecialchars($product['short_desc']??'Chưa có mô tả.')) ?></p>
      <?php if(!empty($product['description'])): ?>
      <p style="color:#6b7280;line-height:1.8;font-size:.875rem;margin-top:.75rem"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
      <?php endif; ?>
    </div>

    <!-- Specs -->
    <div class="tab-content" id="tc-specs" style="display:none">
      <?php if(!empty($product['specs'])): ?>
      <table class="spec-table">
        <?php $specs=json_decode($product['specs'],true)??array(); foreach($specs as $k=>$v): ?>
        <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars($v) ?></td></tr>
        <?php endforeach; ?>
      </table>
      <?php else: ?><p style="color:#9ca3af;font-size:.875rem">Chưa có thông số kỹ thuật chi tiết.</p><?php endif; ?>
    </div>

    <!-- Reviews -->
    <div class="tab-content" id="tc-reviews" style="display:none">
      <?php if(empty($reviews)): ?>
      <div style="text-align:center;padding:1.5rem;color:#9ca3af">
        <i class="fa-regular fa-comment-dots" style="font-size:2rem;margin-bottom:.65rem;display:block"></i>
        Chưa có đánh giá. Hãy là người đầu tiên!
      </div>
      <?php else: ?>
      <?php foreach($reviews as $rv): ?>
      <div class="rv-item">
        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.4rem">
          <div class="rv-av"><?= strtoupper(mb_substr($rv['fullname'],0,1)) ?></div>
          <div>
            <div style="font-weight:600;font-size:.84rem"><?= htmlspecialchars($rv['fullname']) ?></div>
            <div class="stars" style="font-size:.68rem"><?= str_repeat('★',$rv['rating']) ?></div>
          </div>
          <span style="margin-left:auto;font-size:.72rem;color:#9ca3af"><?= date('d/m/Y',strtotime($rv['created_at'])) ?></span>
        </div>
        <?php if($rv['title']): ?><div style="font-weight:600;font-size:.82rem;margin-bottom:.2rem"><?= htmlspecialchars($rv['title']) ?></div><?php endif; ?>
        <p style="color:#6b7280;font-size:.82rem;line-height:1.6"><?= htmlspecialchars($rv['content']) ?></p>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

      <?php if(isLoggedIn()): ?>
      <div style="margin-top:1.1rem;padding:1.1rem;background:#f9fafb;border-radius:var(--r-lg);border:1px solid var(--border)">
        <h4 style="font-weight:700;margin-bottom:.85rem;font-size:.9rem">Viết đánh giá của bạn</h4>
        <form method="POST" action="<?= APP_URL ?>/api/review/add">
          <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
          <div style="margin-bottom:.65rem">
            <div style="font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem">Đánh giá:</div>
            <div id="sp" style="display:flex;gap:.2rem;font-size:1.5rem">
              <?php for($i=1;$i<=5;$i++): ?><span onclick="setRv(<?=$i?>)" style="cursor:pointer;opacity:.3;transition:.18s" data-v="<?=$i?>">★</span><?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rv-r" value="5">
          </div>
          <input name="title" type="text" placeholder="Tiêu đề đánh giá" class="form-input" style="margin-bottom:.5rem">
          <textarea name="content" rows="3" placeholder="Nội dung..." class="form-input" style="resize:vertical"></textarea>
          <button type="submit" class="btn-red" style="margin-top:.65rem">Gửi đánh giá</button>
        </form>
      </div>
      <?php else: ?>
      <div style="text-align:center;padding:1rem;color:#9ca3af;font-size:.875rem">
        <a href="javascript:void(0)" onclick="openLM()" style="color:var(--red);font-weight:600">Đăng nhập</a> để viết đánh giá
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Related -->
  <?php if(!empty($related)): ?>
  <div style="margin-bottom:1.5rem">
    <h2 style="font-size:1.1rem;font-weight:800;color:#111;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem">
      <span style="display:block;width:3px;height:1.1rem;background:var(--red);border-radius:2px"></span>Sản phẩm liên quan
    </h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.9rem">
      <?php foreach($related as $p): include __DIR__.'/product_card.php'; endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
// Tab switch
function switchTab(id){
  ['desc','specs','reviews'].forEach(function(t){
    document.getElementById('tc-'+t).style.display=t===id?'block':'none';
    var b=document.getElementById('tab-'+t);
    b.classList.toggle('on',t===id);
  });
}
// Qty
function qtyChg(d){var i=document.getElementById('qty');i.value=Math.max(1,Math.min(parseInt(i.max)||99,parseInt(i.value)+d));}
// Rating
function setRv(v){
  document.getElementById('rv-r').value=v;
  document.querySelectorAll('#sp span').forEach(function(s,i){s.style.opacity=i<v?'1':'.3';s.style.color=i<v?'#f59e0b':'';});
}
setRv(5);

// Guard functions cho trang detail
function detGuardCart(id){
  <?php if(isLoggedIn()): ?>
  var qty=parseInt(document.getElementById('qty').value)||1;
  var btn=document.getElementById('btn-cart');
  addToCart(id,qty,btn);
  <?php else: ?>
  openLM(id,parseInt(document.getElementById('qty').value)||1);
  <?php endif; ?>
}
function detGuardBuyNow(id){
  <?php if(isLoggedIn()): ?>
  var qty=parseInt(document.getElementById('qty').value)||1;
  window.location.href='<?= APP_URL ?>/checkout/buynow/'+id+'?qty='+qty;
  <?php else: ?>
  openLM(id,parseInt(document.getElementById('qty').value)||1);
  <?php endif; ?>
}
// ── Gallery functions ──────────────────────────────
function switchImg(src, el){
  var main=document.getElementById('main-img');
  if(main){ main.style.opacity='0'; setTimeout(function(){ main.src=src; main.style.opacity='1'; },150); }
  document.querySelectorAll('.thumb-item').forEach(function(t){ t.classList.remove('active'); });
  if(el) el.classList.add('active');
}
function openLightbox(src){
  var lb=document.getElementById('lightbox');
  var img=document.getElementById('lb-img');
  if(lb&&img){ img.src=src; lb.style.display='flex'; document.body.style.overflow='hidden'; }
}
function closeLightbox(){
  var lb=document.getElementById('lightbox');
  if(lb){ lb.style.display='none'; document.body.style.overflow=''; }
}
document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeLightbox(); });


</script>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
