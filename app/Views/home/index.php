<?php $pageTitle='Trang chủ'; require_once __DIR__.'/../layouts/header.php'; ?>
<style>
.wrap{max-width:1280px;margin:0 auto;padding:0 1rem}

/* HERO */
.hero-sec{background:#0d0d0d;overflow:hidden;position:relative}
.hero-sec::before{content:'';position:absolute;right:0;top:0;width:55%;height:100%;background:radial-gradient(ellipse at 70% 50%,rgba(227,0,0,.07),transparent 65%)}
.hero-inner{display:grid;grid-template-columns:1fr 420px;gap:2.5rem;align-items:center;padding:4rem 1rem;max-width:1280px;margin:0 auto;position:relative;z-index:1}
.hero-tag{display:inline-flex;align-items:center;gap:.45rem;background:rgba(227,0,0,.12);border:1px solid rgba(227,0,0,.18);color:var(--red);font-size:.7rem;font-weight:700;letter-spacing:2px;padding:.3rem .8rem;border-radius:99px;margin-bottom:1.1rem;text-transform:uppercase}
.hero-h1{font-size:clamp(1.9rem,3.8vw,3rem);font-weight:900;color:#fff;line-height:1.12;margin-bottom:.9rem;letter-spacing:-.5px}
.hero-h1 mark{background:none;color:var(--red)}
.hero-p{color:#666;font-size:.88rem;line-height:1.75;margin-bottom:1.6rem;max-width:460px}
.hero-btns{display:flex;gap:.65rem;flex-wrap:wrap;margin-bottom:2rem}
.hero-stats{display:flex;gap:2rem;padding-top:1.5rem;border-top:1px solid #1c1c1c}
.hs-num{font-size:1.4rem;font-weight:900;color:#fff;line-height:1}.hs-num b{color:var(--red)}
.hs-lbl{font-size:.68rem;color:#444;text-transform:uppercase;letter-spacing:.5px;margin-top:2px}

/* Hero card */
.hero-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:18px;overflow:hidden;transition:transform .3s,box-shadow .3s}
.hero-card:hover{transform:translateY(-6px);box-shadow:0 20px 56px rgba(227,0,0,.12)}
.hc-img{background:linear-gradient(135deg,#1a0808,#111);padding:1.75rem;text-align:center}
.hc-icon{width:72px;height:72px;background:linear-gradient(135deg,var(--red),#ff3333);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;font-size:1.8rem;color:#fff;box-shadow:0 8px 24px rgba(227,0,0,.3)}
.hc-body{padding:1.25rem}
.hc-tag{font-size:.68rem;color:#555;text-transform:uppercase;letter-spacing:1px;margin-bottom:.35rem}
.hc-name{color:#ddd;font-weight:700;font-size:.88rem;line-height:1.4;margin-bottom:.6rem}
.hc-price{color:var(--red);font-size:1.25rem;font-weight:900}
.hc-old{color:#444;text-decoration:line-through;font-size:.78rem;margin-left:6px}
.hc-cta{display:flex;align-items:center;justify-content:center;gap:.4rem;background:var(--red);color:#fff;padding:.5rem;border-radius:9px;font-size:.78rem;font-weight:700;margin-top:.75rem;transition:.2s}
.hc-cta:hover{background:var(--red-dk)}

/* Flash bar */
.flash-bar{background:#0d0d0d;border-top:1px solid #1c1c1c;border-bottom:1px solid #1c1c1c;padding:.6rem 1rem}
.fb-inner{max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:center;gap:1.25rem;flex-wrap:wrap}
.fb-label{display:flex;align-items:center;gap:.4rem;color:var(--red);font-weight:700;font-size:.82rem}
.cd-row{display:flex;align-items:center;gap:.35rem}
.cd-b{background:#1a1a1a;border:1px solid #252525;border-radius:7px;padding:.35rem .6rem;text-align:center;min-width:42px}
.cd-n{display:block;font-size:1.15rem;font-weight:900;color:#fff;line-height:1}
.cd-l{display:block;font-size:.52rem;color:#444;text-transform:uppercase;letter-spacing:.5px;margin-top:1px}
.cd-d{color:#252525;font-size:1.1rem;font-weight:900;line-height:1}

/* Section */
.sec{padding:2.5rem 1rem;max-width:1280px;margin:0 auto}
.sec-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.35rem}
.sec-ttl{font-size:1.15rem;font-weight:900;color:#111;display:flex;align-items:center;gap:.5rem}
.sec-ttl::before{content:'';display:block;width:3px;height:1.15rem;background:var(--red);border-radius:2px;flex-shrink:0}
.sec-more{display:flex;align-items:center;gap:.35rem;color:var(--red);font-size:.8rem;font-weight:600;transition:gap .2s}
.sec-more:hover{gap:.55rem}

/* Product grid */
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.9rem}

/* Categories */
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(96px,1fr));gap:.7rem}
.cat-card{background:#fff;border-radius:12px;padding:.85rem .5rem;text-align:center;border:1.5px solid transparent;transition:all .2s;position:relative;overflow:hidden}
.cat-card:hover{border-color:var(--red);transform:translateY(-3px);box-shadow:0 6px 20px rgba(227,0,0,.1)}
.cat-ico{width:42px;height:42px;background:#f4f4f5;border-radius:9px;display:flex;align-items:center;justify-content:center;margin:0 auto .55rem;font-size:1.1rem;color:#555;transition:all .2s}
.cat-card:hover .cat-ico{background:rgba(227,0,0,.08);color:var(--red)}
.cat-nm{font-size:.7rem;font-weight:600;color:#333;line-height:1.3}
.cat-cnt{font-size:.62rem;color:#9ca3af;margin-top:2px}

/* Promo */
.promo-grid{display:grid;grid-template-columns:2fr 1fr;gap:.9rem}
.promo-main{background:linear-gradient(135deg,#0a0000,#180303);border-radius:var(--r-lg);padding:1.75rem;position:relative;overflow:hidden}
.promo-main::after{content:'\f11b';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:-10px;top:50%;transform:translateY(-50%);font-size:6.5rem;color:rgba(227,0,0,.05)}
.promo-sm{display:flex;flex-direction:column;gap:.7rem}
.promo-item{border-radius:var(--r-lg);padding:1rem 1.1rem;text-decoration:none;display:flex;flex-direction:column;justify-content:center;transition:transform .2s}
.promo-item:hover{transform:translateX(4px)}
.pi-blue{background:linear-gradient(135deg,#06061a,#0c1535)}
.pi-green{background:linear-gradient(135deg,#030f05,#071508)}

/* Why us */
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.9rem}
.why-item{background:#fff;border-radius:var(--r-lg);padding:1.4rem 1rem;text-align:center;border:1px solid var(--border);transition:all .2s}
.why-item:hover{border-color:rgba(227,0,0,.18);box-shadow:0 6px 20px rgba(227,0,0,.07);transform:translateY(-3px)}
.why-ico{width:48px;height:48px;background:rgba(227,0,0,.07);border-radius:11px;display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;color:var(--red);font-size:1.15rem}
.why-ttl{font-weight:700;font-size:.84rem;color:#111;margin-bottom:.28rem}
.why-dsc{font-size:.73rem;color:#6b7280;line-height:1.55}

/* BG sections */
.bg-white{background:#fff}
.bg-gray{background:var(--bg)}
.bg-dark{background:#0d0d0d}

/* Login CTA */
.login-cta{background:linear-gradient(135deg,#0d0d0d 0%,#1a0000 50%,#0d0d0d 100%);padding:3rem 1rem;text-align:center}

@media(max-width:900px){
  .hero-inner{grid-template-columns:1fr;gap:1.75rem}
  .hero-card{display:none}
  .promo-grid{grid-template-columns:1fr}
}
@media(max-width:580px){
  .hero-h1{font-size:1.75rem}
  .prod-grid{grid-template-columns:repeat(2,1fr)}
}
</style>

<!-- HERO -->
<section class="hero-sec">
  <div class="hero-inner">
    <div>
      <div class="hero-tag"><i class="fa-solid fa-fire"></i> Flash Sale — Giá Hủy Diệt</div>
      <h1 class="hero-h1">Công nghệ <mark>đỉnh cao</mark><br>Giá tốt nhất <mark>Việt Nam</mark></h1>
      <p class="hero-p">PC Gaming, Laptop, Linh kiện chính hãng.<br>Bảo hành tận nơi — Giao hàng toàn quốc 24h.</p>
      <div class="hero-btns">
        <a href="<?= APP_URL ?>/products" class="btn-red" style="animation:pulseRed 2.5s infinite"><i class="fa-solid fa-bolt"></i>Mua ngay</a>
        <a href="<?= APP_URL ?>/products/may-tinh-pc" class="btn-secondary"><i class="fa-solid fa-desktop"></i>PC Gaming</a>
      </div>
      <div class="hero-stats">
        <?php foreach(array(array('10K','Khách hàng'),array('5K+','Sản phẩm'),array('100%','Chính hãng'),array('24h','Giao hàng')) as $s): ?>
        <div><div class="hs-num"><?= $s[0] ?></div><div class="hs-lbl"><?= $s[1] ?></div></div>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- Featured product card -->
    <?php if(!empty($featured[0])): ?>
    <a href="<?= APP_URL ?>/products/detail/<?= $featured[0]['slug'] ?>" class="hero-card" style="display:block">
      <div class="hc-img">
        <div class="hc-icon"><i class="fa-solid fa-desktop"></i></div>
        <span style="background:rgba(227,0,0,.18);color:var(--red);font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:99px">NỔI BẬT</span>
      </div>
      <div class="hc-body">
        <div class="hc-tag">Sản phẩm được xem nhiều nhất</div>
        <div class="hc-name"><?= htmlspecialchars(mb_substr($featured[0]['name'],0,55)) ?>...</div>
        <div>
          <span class="hc-price"><?= formatPrice($featured[0]['final_price']) ?></span>
          <?php if(!empty($featured[0]['sale_price'])&&$featured[0]['sale_price']<$featured[0]['price']): ?>
          <span class="hc-old"><?= formatPrice($featured[0]['price']) ?></span>
          <?php endif; ?>
        </div>
        <div class="hc-cta"><i class="fa-solid fa-eye"></i>Xem chi tiết</div>
      </div>
    </a>
    <?php endif; ?>
  </div>
</section>

<!-- FLASH SALE BAR -->
<div class="flash-bar">
  <div class="fb-inner">
    <div class="fb-label"><i class="fa-solid fa-fire-flame-curved"></i>FLASH SALE kết thúc sau:</div>
    <div class="cd-row">
      <div class="cd-b"><span class="cd-n" id="cd-h">00</span><span class="cd-l">Giờ</span></div>
      <span class="cd-d">:</span>
      <div class="cd-b"><span class="cd-n" id="cd-m">00</span><span class="cd-l">Phút</span></div>
      <span class="cd-d">:</span>
      <div class="cd-b"><span class="cd-n" id="cd-s">00</span><span class="cd-l">Giây</span></div>
    </div>
    <?php if(!isLoggedIn()): ?>
    <a href="javascript:void(0)" onclick="openLM()" style="display:inline-flex;align-items:center;gap:.4rem;background:var(--red);color:#fff;padding:.3rem .75rem;border-radius:7px;font-size:.76rem;font-weight:700">
      <i class="fa-solid fa-right-to-bracket"></i>Đăng nhập để mua
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- CATEGORIES -->
<div class="bg-white">
  <div class="sec reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl">Danh mục sản phẩm</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <?php
    $catIcons=array('may-tinh-pc'=>'fa-desktop','laptop'=>'fa-laptop','man-hinh'=>'fa-tv','chuot'=>'fa-computer-mouse','ban-phim'=>'fa-keyboard','ram'=>'fa-memory','cpu'=>'fa-microchip','card-do-hoa'=>'fa-hard-drive','ssd-o-cung'=>'fa-hdd','mainboard'=>'fa-server','phu-kien'=>'fa-headphones');
    ?>
    <div class="cat-grid">
      <?php foreach($categories as $cat): ?>
      <a href="<?= APP_URL ?>/products/<?= $cat['slug'] ?>" class="cat-card">
        <div class="cat-ico"><i class="fa-solid <?= isset($catIcons[$cat['slug']])?$catIcons[$cat['slug']]:'fa-box' ?>"></i></div>
        <div class="cat-nm"><?= htmlspecialchars($cat['name']) ?></div>
        <div class="cat-cnt"><?= $cat['product_count'] ?> SP</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- PROMO BANNERS -->
<div class="bg-gray">
  <div class="sec reveal">
    <div class="promo-grid">
      <div class="promo-main">
        <div style="color:var(--red);font-size:.68rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:.45rem"><i class="fa-solid fa-gamepad" style="margin-right:4px"></i>PC GAMING</div>
        <h3 style="color:#fff;font-size:1.3rem;font-weight:900;margin-bottom:.5rem;letter-spacing:-.3px">RTX 4090 — Sức mạnh<br>không giới hạn</h3>
        <p style="color:#555;font-size:.79rem;margin-bottom:1rem;line-height:1.6">Chinh phục mọi tựa game với cấu hình đỉnh nhất</p>
        <a href="<?= APP_URL ?>/products/may-tinh-pc" class="btn-red" style="font-size:.8rem"><i class="fa-solid fa-arrow-right"></i>Khám phá ngay</a>
      </div>
      <div class="promo-sm">
        <a href="<?= APP_URL ?>/products/laptop" class="promo-item pi-blue">
          <div style="color:#60a5fa;font-size:.66rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:.2rem"><i class="fa-solid fa-laptop" style="margin-right:3px"></i>LAPTOP</div>
          <div style="color:#fff;font-weight:700;font-size:.88rem">Mỏng nhẹ — Mạnh mẽ</div>
          <div style="color:#60a5fa;font-size:.73rem;display:flex;align-items:center;gap:.25rem;margin-top:.25rem">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.6rem"></i></div>
        </a>
        <a href="<?= APP_URL ?>/products/cpu" class="promo-item pi-green">
          <div style="color:#4ade80;font-size:.66rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:.2rem"><i class="fa-solid fa-microchip" style="margin-right:3px"></i>CPU & GPU</div>
          <div style="color:#fff;font-weight:700;font-size:.88rem">Intel Gen14 / AMD Zen4</div>
          <div style="color:#4ade80;font-size:.73rem;display:flex;align-items:center;gap:.25rem;margin-top:.25rem">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.6rem"></i></div>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- FEATURED -->
<div class="bg-white">
  <div class="sec reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl"><i class="fa-solid fa-fire" style="color:var(--red);font-size:.95rem"></i>Sản phẩm nổi bật</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="prod-grid"><?php foreach($featured as $p): include __DIR__.'/../products/product_card.php'; endforeach; ?></div>
  </div>
</div>

<!-- NEW -->
<div class="bg-gray">
  <div class="sec reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl"><i class="fa-solid fa-star" style="color:#f59e0b;font-size:.95rem"></i>Hàng mới về</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="prod-grid"><?php foreach($newProducts as $p): include __DIR__.'/../products/product_card.php'; endforeach; ?></div>
  </div>
</div>

<!-- BESTSELLER -->
<div class="bg-white">
  <div class="sec reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl"><i class="fa-solid fa-trophy" style="color:#f59e0b;font-size:.95rem"></i>Bán chạy nhất</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="prod-grid"><?php foreach($bestSellers as $p): include __DIR__.'/../products/product_card.php'; endforeach; ?></div>
  </div>
</div>

<!-- WHY US -->
<div class="bg-gray">
  <div class="sec reveal">
    <div class="sec-hd" style="justify-content:center">
      <h2 class="sec-ttl">Tại sao chọn Tuấn Huy Computer?</h2>
    </div>
    <div class="why-grid">
      <?php foreach(array(array('fa-certificate','Hàng chính hãng','Tem niêm phong, hóa đơn VAT đầy đủ'),array('fa-truck-fast','Giao hàng 24h','Ship toàn quốc, nhận hàng nhanh'),array('fa-screwdriver-wrench','BH tận nơi','Hỗ trợ kỹ thuật miễn phí'),array('fa-tag','Giá tốt nhất','Cam kết giá cạnh tranh nhất'),array('fa-rotate-left','Đổi trả 30 ngày','Không cần lý do'),array('fa-headset','Hỗ trợ 24/7','Tư vấn kỹ thuật mọi lúc')) as $w): ?>
      <div class="why-item">
        <div class="why-ico"><i class="fa-solid <?= $w[0] ?>"></i></div>
        <div class="why-ttl"><?= $w[1] ?></div>
        <div class="why-dsc"><?= $w[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- LOGIN CTA for guests -->
<?php if(!isLoggedIn()): ?>
<div class="login-cta reveal">
  <div style="max-width:600px;margin:0 auto">
    <div style="width:56px;height:56px;background:rgba(227,0,0,.12);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;font-size:1.3rem;color:var(--red)"><i class="fa-solid fa-user-lock"></i></div>
    <h2 style="color:#fff;font-size:1.35rem;font-weight:900;margin-bottom:.55rem">Đăng nhập để mua sắm ngay</h2>
    <p style="color:#555;font-size:.85rem;margin-bottom:1.35rem;line-height:1.7">Đăng nhập để thêm sản phẩm vào giỏ, theo dõi đơn hàng<br>và nhận ưu đãi đặc biệt dành riêng cho thành viên.</p>
    <div style="display:flex;gap:.65rem;justify-content:center;flex-wrap:wrap">
      <button onclick="openLM()" class="btn-red" style="animation:pulseRed 2s infinite"><i class="fa-solid fa-right-to-bracket"></i>Đăng nhập ngay</button>
      <button onclick="openLM();switchLMTab('r')" class="btn-secondary"><i class="fa-solid fa-user-plus"></i>Tạo tài khoản</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function updateCD(){
  var now=new Date(),end=new Date();end.setHours(23,59,59,0);
  var d=Math.max(0,Math.floor((end-now)/1000));
  var h=Math.floor(d/3600);d%=3600;var m=Math.floor(d/60),s=d%60;
  var eh=document.getElementById('cd-h'),em=document.getElementById('cd-m'),es=document.getElementById('cd-s');
  if(eh)eh.textContent=String(h).padStart(2,'0');
  if(em)em.textContent=String(m).padStart(2,'0');
  if(es)es.textContent=String(s).padStart(2,'0');
}
setInterval(updateCD,1000);updateCD();
</script>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
