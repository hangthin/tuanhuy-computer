<?php
$pageTitle = 'Trang chủ';
require_once __DIR__.'/../layouts/header.php';

// ── Asset images — read dynamically from approved.json ─────────────
$_approvedJson = __DIR__.'/../../../assets/images/approved.json';
$_approved     = file_exists($_approvedJson) ? (json_decode(file_get_contents($_approvedJson), true) ?: array()) : array();
if (!function_exists('_assetUrl')) {
    function _assetUrl($a, $k) { return !empty($a[$k]['url']) ? $a[$k]['url'] : ''; }
}
$_heroImg = _assetUrl($_approved, 'hero-banner');
$_gpcImg  = _assetUrl($_approved, 'gaming-pc');
$_lapImg  = _assetUrl($_approved, 'laptop');
$_monImg  = _assetUrl($_approved, 'monitor');
$_kbImg   = _assetUrl($_approved, 'keyboard');
$_mouImg  = _assetUrl($_approved, 'mouse');
$_hsImg   = _assetUrl($_approved, 'headset');
$_ramImg  = _assetUrl($_approved, 'ram');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<style>
.wrap{max-width:1280px;margin:0 auto;padding:0 1rem}

/* ── Hero ──────────────────────────────────────────────────────── */
.g-hero{position:relative;min-height:92vh;display:flex;align-items:center;overflow:hidden;background:#080808}
.g-hero-bg{position:absolute;inset:0;background-size:cover;background-position:center;transform-origin:center}
.g-hero-fog{position:absolute;inset:0;background:linear-gradient(120deg,rgba(0,0,0,.82) 0%,rgba(0,0,0,.45) 60%,rgba(0,0,0,.25) 100%)}
.g-hero-glow{position:absolute;inset:0;background:radial-gradient(ellipse at 70% 50%,rgba(227,0,0,.09),transparent 65%)}
.g-hero-inner{position:relative;z-index:2;max-width:1280px;margin:0 auto;padding:5rem 1rem}
.g-tag{display:inline-flex;align-items:center;gap:.45rem;background:rgba(227,0,0,.12);border:1px solid rgba(227,0,0,.2);color:#E30000;font-size:.68rem;font-weight:700;letter-spacing:2px;padding:.28rem .85rem;border-radius:99px;text-transform:uppercase;margin-bottom:1.1rem}
.g-h1{font-size:clamp(2rem,4.2vw,3.4rem);font-weight:900;color:#fff;line-height:1.1;margin-bottom:.9rem;letter-spacing:-.6px}
.g-h1 mark{background:none;color:#E30000}
.g-p{color:#555;font-size:.9rem;line-height:1.8;margin-bottom:1.7rem;max-width:480px}
.g-btns{display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:2rem}
.g-stats{display:flex;gap:2.2rem;padding-top:1.6rem;border-top:1px solid #1c1c1c}
.gs-n{font-size:1.45rem;font-weight:900;color:#fff;line-height:1}.gs-n b{color:#E30000}
.gs-l{font-size:.65rem;color:#444;text-transform:uppercase;letter-spacing:.6px;margin-top:2px}

/* ── Flash bar ─────────────────────────────────────────────────── */
.flash-bar{background:#0d0d0d;border-top:1px solid #1c1c1c;border-bottom:1px solid #1c1c1c;padding:.65rem 1rem}
.fb-inner{max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:center;gap:1.3rem;flex-wrap:wrap}
.fb-label{display:flex;align-items:center;gap:.4rem;color:#E30000;font-weight:700;font-size:.82rem}
.cd-row{display:flex;align-items:center;gap:.35rem}
.cd-b{background:#1a1a1a;border:1px solid #252525;border-radius:7px;padding:.35rem .65rem;text-align:center;min-width:44px}
.cd-n{display:block;font-size:1.2rem;font-weight:900;color:#fff;line-height:1}
.cd-l{display:block;font-size:.5rem;color:#444;text-transform:uppercase;letter-spacing:.5px;margin-top:1px}
.cd-d{color:#252525;font-size:1.1rem;font-weight:900}

/* ── Showcase strip ────────────────────────────────────────────── */
.g-strip{background:#0a0a0a;padding:1.5rem 1rem;border-bottom:1px solid #111}
.g-strip-inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:repeat(6,1fr);gap:.65rem}
.g-strip-item{border-radius:10px;overflow:hidden;aspect-ratio:1;position:relative;cursor:pointer;border:1px solid #1e1e1e;background:#111;transition:transform .3s,box-shadow .3s,border-color .3s;text-decoration:none;display:block}
.g-strip-item:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.5);border-color:#333}
.g-strip-img{width:100%;height:100%;object-fit:contain;display:block;padding:.75rem;transition:transform .4s;box-sizing:border-box}
.g-strip-item:hover .g-strip-img{transform:scale(1.08)}
.g-strip-cap{position:absolute;bottom:0;left:0;right:0;padding:.35rem .6rem;background:linear-gradient(to top,rgba(0,0,0,.8),transparent);font-size:.65rem;font-weight:700;color:#fff;letter-spacing:.5px;text-transform:uppercase}

/* ── Sections ──────────────────────────────────────────────────── */
.sec{padding:2.5rem 1rem;max-width:1280px;margin:0 auto}
.sec-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.35rem}
.sec-ttl{font-size:1.12rem;font-weight:900;color:#111;display:flex;align-items:center;gap:.5rem}
.sec-ttl::before{content:'';display:block;width:3px;height:1.1rem;background:#E30000;border-radius:2px;flex-shrink:0}
.sec-more{display:flex;align-items:center;gap:.35rem;color:#E30000;font-size:.8rem;font-weight:600;transition:gap .2s}
.sec-more:hover{gap:.55rem}
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.9rem}
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(96px,1fr));gap:.7rem}
.cat-card{background:#fff;border-radius:12px;padding:.85rem .5rem;text-align:center;border:1.5px solid transparent;transition:all .2s;position:relative;overflow:hidden;text-decoration:none;display:block}
.cat-card:hover{border-color:#E30000;transform:translateY(-3px);box-shadow:0 6px 20px rgba(227,0,0,.1)}
.cat-ico{width:42px;height:42px;background:#f4f4f5;border-radius:9px;display:flex;align-items:center;justify-content:center;margin:0 auto .55rem;font-size:1.1rem;color:#555;transition:all .2s}
.cat-card:hover .cat-ico{background:rgba(227,0,0,.08);color:#E30000}
.cat-nm{font-size:.7rem;font-weight:600;color:#333;line-height:1.3}
.cat-cnt{font-size:.62rem;color:#9ca3af;margin-top:2px}
.promo-grid{display:grid;grid-template-columns:2fr 1fr;gap:.9rem}
.promo-main{background:linear-gradient(135deg,#0a0000,#180303);border-radius:12px;padding:1.75rem;position:relative;overflow:hidden}
.promo-main::after{content:'\f11b';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:-10px;top:50%;transform:translateY(-50%);font-size:6.5rem;color:rgba(227,0,0,.05)}
.promo-sm{display:flex;flex-direction:column;gap:.7rem}
.promo-item{border-radius:12px;padding:1rem 1.1rem;text-decoration:none;display:flex;flex-direction:column;justify-content:center;transition:transform .2s}
.promo-item:hover{transform:translateX(4px)}
.pi-blue{background:linear-gradient(135deg,#06061a,#0c1535)}
.pi-green{background:linear-gradient(135deg,#030f05,#071508)}
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.9rem}
.why-item{background:#fff;border-radius:12px;padding:1.4rem 1rem;text-align:center;border:1px solid #f0f0f0;transition:all .2s}
.why-item:hover{border-color:rgba(227,0,0,.18);box-shadow:0 6px 20px rgba(227,0,0,.07);transform:translateY(-3px)}
.why-ico{width:48px;height:48px;background:rgba(227,0,0,.07);border-radius:11px;display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;color:#E30000;font-size:1.15rem}
.why-ttl{font-weight:700;font-size:.84rem;color:#111;margin-bottom:.28rem}
.why-dsc{font-size:.73rem;color:#6b7280;line-height:1.55}
.login-cta{background:linear-gradient(135deg,#0d0d0d 0%,#1a0000 50%,#0d0d0d 100%);padding:3rem 1rem;text-align:center}
.bg-white{background:#fff}.bg-gray{background:#f9f9fb}.bg-dark{background:#0d0d0d}
/* gsap initial states */
.g-reveal{opacity:0;transform:translateY(40px)}
.g-reveal-left{opacity:0;transform:translateX(-40px)}
.g-reveal-right{opacity:0;transform:translateX(40px)}
.strip-init{opacity:0;transform:translateY(30px)}
@media(max-width:960px){
  .promo-grid{grid-template-columns:1fr}.g-strip-inner{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:580px){
  .g-h1{font-size:1.8rem}.prod-grid{grid-template-columns:repeat(2,1fr)}
  .g-strip-inner{grid-template-columns:repeat(2,1fr)}
}
</style>

<!-- ── HERO ──────────────────────────────────────────────────────── -->
<section class="g-hero" id="gHero">
  <div class="g-hero-bg" id="gHeroBg" <?php if($_heroImg): ?>style="background-image:url('<?= htmlspecialchars($_heroImg) ?>')"<?php endif; ?>></div>
  <div class="g-hero-fog"></div>
  <div class="g-hero-glow"></div>
  <div class="g-hero-inner">
    <div>
      <div class="g-tag" id="gTag"><i class="fa-solid fa-fire"></i> Flash Sale — Giá Hủy Diệt</div>
      <h1 class="g-h1" id="gH1">Công nghệ <mark>đỉnh cao</mark><br>Giá tốt nhất <mark>Việt Nam</mark></h1>
      <p class="g-p" id="gP">PC Gaming, Laptop, Linh kiện chính hãng.<br>Bảo hành tận nơi — Giao hàng toàn quốc 24h.</p>
      <div class="g-btns" id="gBtns">
        <a href="<?= APP_URL ?>/products" class="btn-red" style="animation:pulseRed 2.5s infinite"><i class="fa-solid fa-bolt"></i>Mua ngay</a>
        <a href="<?= APP_URL ?>/products/may-tinh-pc" class="btn-secondary"><i class="fa-solid fa-desktop"></i>PC Gaming</a>
      </div>
      <div class="g-stats" id="gStats">
        <?php foreach(array(array('10K','Khách hàng'),array('5K+','Sản phẩm'),array('100%','Chính hãng'),array('24h','Giao hàng')) as $s): ?>
        <div><div class="gs-n"><?= $s[0] ?></div><div class="gs-l"><?= $s[1] ?></div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── FLASH SALE BAR ──────────────────────────────────────────── -->
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
    <a href="javascript:void(0)" onclick="openLM()" style="display:inline-flex;align-items:center;gap:.4rem;background:#E30000;color:#fff;padding:.3rem .75rem;border-radius:7px;font-size:.76rem;font-weight:700">
      <i class="fa-solid fa-right-to-bracket"></i>Đăng nhập để mua
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ── ASSET SHOWCASE STRIP ───────────────────────────────────── -->
<?php
/* Map approved images to category slugs — uses real DB slugs so links always work */
$_imgBySlug = array(
  'may-tinh-pc'=>$_gpcImg, 'laptop'=>$_lapImg, 'man-hinh'=>$_monImg,
  'ban-phim'=>$_kbImg,     'chuot'=>$_mouImg,  'phu-kien'=>$_hsImg,
  'headset'=>$_hsImg,      'keyboard'=>$_kbImg,'monitor'=>$_monImg,
  'ram'=>$_ramImg,
);
$_strip = array();
foreach(array_slice($categories, 0, 6) as $_cat) {
    $_slug = $_cat['slug'];
    $_img  = isset($_imgBySlug[$_slug]) ? $_imgBySlug[$_slug] : '';
    $_strip[] = array('img'=>$_img, 'label'=>$_cat['name'], 'link'=>'/products/'.$_slug);
}
if(count($categories) > 0):
?>
<div class="g-strip">
  <div class="g-strip-inner" id="gStrip">
    <?php foreach($_strip as $_si): ?>
    <a href="<?= APP_URL . $_si['link'] ?>" class="g-strip-item strip-init" title="<?= htmlspecialchars($_si['label']) ?>">
      <?php if($_si['img']): ?>
        <img class="g-strip-img" src="<?= htmlspecialchars($_si['img']) ?>" alt="">
      <?php else: ?>
        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#2a2a2a;font-size:1.8rem"><i class="fa-solid fa-image"></i></div>
      <?php endif; ?>
      <div class="g-strip-cap"><?= htmlspecialchars($_si['label']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ── CATEGORIES ─────────────────────────────────────────────── -->
<div class="bg-white">
  <div class="sec g-reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl">Danh mục sản phẩm</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <?php $catIcons=array('may-tinh-pc'=>'fa-desktop','laptop'=>'fa-laptop','man-hinh'=>'fa-tv','chuot'=>'fa-computer-mouse','ban-phim'=>'fa-keyboard','ram'=>'fa-memory','cpu'=>'fa-microchip','card-do-hoa'=>'fa-hard-drive','ssd-o-cung'=>'fa-hdd','mainboard'=>'fa-server','phu-kien'=>'fa-headphones'); ?>
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

<!-- ── PROMO BANNERS ──────────────────────────────────────────── -->
<div class="bg-gray">
  <div class="sec">
    <div class="promo-grid">
      <div class="promo-main g-reveal-left">
        <div style="color:#E30000;font-size:.68rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:.45rem"><i class="fa-solid fa-gamepad" style="margin-right:4px"></i>PC GAMING</div>
        <h3 style="color:#fff;font-size:1.3rem;font-weight:900;margin-bottom:.5rem;letter-spacing:-.3px">RTX 4090 — Sức mạnh<br>không giới hạn</h3>
        <p style="color:#555;font-size:.79rem;margin-bottom:1rem;line-height:1.6">Chinh phục mọi tựa game với cấu hình đỉnh nhất</p>
        <a href="<?= APP_URL ?>/products/may-tinh-pc" class="btn-red" style="font-size:.8rem"><i class="fa-solid fa-arrow-right"></i>Khám phá ngay</a>
      </div>
      <div class="promo-sm g-reveal-right">
        <a href="<?= APP_URL ?>/products/laptop" class="promo-item pi-blue">
          <div style="color:#60a5fa;font-size:.66rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:.2rem"><i class="fa-solid fa-laptop" style="margin-right:3px"></i>LAPTOP</div>
          <div style="color:#fff;font-weight:700;font-size:.88rem">Mỏng nhẹ — Mạnh mẽ</div>
          <div style="color:#60a5fa;font-size:.73rem;display:flex;align-items:center;gap:.25rem;margin-top:.25rem">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.6rem"></i></div>
        </a>
        <a href="<?= APP_URL ?>/products/cpu" class="promo-item pi-green">
          <div style="color:#4ade80;font-size:.66rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:.2rem"><i class="fa-solid fa-microchip" style="margin-right:3px"></i>CPU &amp; GPU</div>
          <div style="color:#fff;font-weight:700;font-size:.88rem">Intel Gen14 / AMD Zen4</div>
          <div style="color:#4ade80;font-size:.73rem;display:flex;align-items:center;gap:.25rem;margin-top:.25rem">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.6rem"></i></div>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ── FEATURED ───────────────────────────────────────────────── -->
<div class="bg-white">
  <div class="sec g-reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl"><i class="fa-solid fa-fire" style="color:#E30000;font-size:.95rem"></i>Sản phẩm nổi bật</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="prod-grid"><?php foreach($featured as $p): include __DIR__.'/../products/product_card.php'; endforeach; ?></div>
  </div>
</div>

<!-- ── NEW ARRIVALS ───────────────────────────────────────────── -->
<div class="bg-gray">
  <div class="sec g-reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl"><i class="fa-solid fa-star" style="color:#f59e0b;font-size:.95rem"></i>Hàng mới về</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="prod-grid"><?php foreach($newProducts as $p): include __DIR__.'/../products/product_card.php'; endforeach; ?></div>
  </div>
</div>

<!-- ── BESTSELLERS ────────────────────────────────────────────── -->
<div class="bg-white">
  <div class="sec g-reveal">
    <div class="sec-hd">
      <h2 class="sec-ttl"><i class="fa-solid fa-trophy" style="color:#f59e0b;font-size:.95rem"></i>Bán chạy nhất</h2>
      <a href="<?= APP_URL ?>/products" class="sec-more">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="prod-grid"><?php foreach($bestSellers as $p): include __DIR__.'/../products/product_card.php'; endforeach; ?></div>
  </div>
</div>

<!-- ── WHY US ─────────────────────────────────────────────────── -->
<div class="bg-gray">
  <div class="sec g-reveal">
    <div class="sec-hd" style="justify-content:center">
      <h2 class="sec-ttl">Tại sao chọn Tuấn Huy Computer?</h2>
    </div>
    <div class="why-grid">
      <?php foreach(array(array('fa-certificate','Hàng chính hãng','Tem niêm phong, hóa đơn VAT đầy đủ'),array('fa-truck-fast','Giao hàng 24h','Ship toàn quốc, nhận hàng nhanh'),array('fa-screwdriver-wrench','BH tận nơi','Hỗ trợ kỹ thuật miễn phí'),array('fa-tag','Giá tốt nhất','Cam kết giá cạnh tranh nhất'),array('fa-rotate-left','Đổi trả 30 ngày','Không cần lý do'),array('fa-headset','Hỗ trợ 24/7','Tư vấn kỹ thuật mọi lúc')) as $w): ?>
      <div class="why-item g-reveal">
        <div class="why-ico"><i class="fa-solid <?= $w[0] ?>"></i></div>
        <div class="why-ttl"><?= $w[1] ?></div>
        <div class="why-dsc"><?= $w[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── LOGIN CTA ──────────────────────────────────────────────── -->
<?php if(!isLoggedIn()): ?>
<div class="login-cta g-reveal">
  <div style="max-width:600px;margin:0 auto">
    <div style="width:56px;height:56px;background:rgba(227,0,0,.12);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;font-size:1.3rem;color:#E30000"><i class="fa-solid fa-user-lock"></i></div>
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
// ── Countdown ─────────────────────────────────────────────────────
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

// ── GSAP Animations ───────────────────────────────────────────────
if(typeof gsap === 'undefined') {
  // GSAP failed to load — make all animated elements visible immediately
  document.querySelectorAll('.g-reveal,.g-reveal-left,.g-reveal-right,.strip-init').forEach(function(el){
    el.style.opacity='1'; el.style.transform='none';
  });
} else {
  gsap.registerPlugin(ScrollTrigger);

  // Hero entrance
  var tl = gsap.timeline({ defaults:{ ease:'power3.out' } });
  tl.from('#gHeroBg', { scale:1.12, opacity:0, duration:1.4, ease:'power2.out' }, 0)
    .from('#gTag',   { opacity:0, y:25, duration:.55 }, .3)
    .from('#gH1',    { opacity:0, y:40, duration:.65 }, .45)
    .from('#gP',     { opacity:0, y:30, duration:.55 }, .6)
    .from('#gBtns',  { opacity:0, y:20, duration:.5  }, .72)
    .from('#gStats', { opacity:0, y:20, duration:.5  }, .82);

  // Hero parallax on scroll
  gsap.to('#gHeroBg', {
    yPercent: 22, ease:'none',
    scrollTrigger:{ trigger:'#gHero', start:'top top', end:'bottom top', scrub:1.5 }
  });

  // Strip items stagger
  var stripItems = document.querySelectorAll('.strip-init');
  if(stripItems.length) {
    gsap.to(stripItems, {
      opacity:1, y:0, duration:.55, stagger:.08, ease:'power3.out',
      scrollTrigger:{ trigger:'#gStrip', start:'top 88%' }
    });
  }

  // Section reveals
  gsap.utils.toArray('.g-reveal').forEach(function(el) {
    gsap.fromTo(el, { opacity:0, y:45 }, {
      opacity:1, y:0, duration:.7, ease:'power3.out',
      scrollTrigger:{ trigger:el, start:'top 82%', toggleActions:'play none none reverse' }
    });
  });
  gsap.utils.toArray('.g-reveal-left').forEach(function(el) {
    gsap.fromTo(el, { opacity:0, x:-50 }, {
      opacity:1, x:0, duration:.7, ease:'power3.out',
      scrollTrigger:{ trigger:el, start:'top 82%', toggleActions:'play none none reverse' }
    });
  });
  gsap.utils.toArray('.g-reveal-right').forEach(function(el) {
    gsap.fromTo(el, { opacity:0, x:50 }, {
      opacity:1, x:0, duration:.7, ease:'power3.out',
      scrollTrigger:{ trigger:el, start:'top 82%', toggleActions:'play none none reverse' }
    });
  });

  // Product cards stagger
  gsap.utils.toArray('.prod-grid').forEach(function(grid) {
    var cards = grid.querySelectorAll('.prod-card');
    if(!cards.length) return;
    gsap.fromTo(cards, { opacity:0, y:35 }, {
      opacity:1, y:0, duration:.45, stagger:.06, ease:'power2.out',
      scrollTrigger:{ trigger:grid, start:'top 85%' }
    });
  });

  // Why-us items
  gsap.utils.toArray('.why-item').forEach(function(item, i) {
    gsap.fromTo(item, { opacity:0, y:30 }, {
      opacity:1, y:0, duration:.5, delay:i*.07, ease:'power2.out',
      scrollTrigger:{ trigger:item, start:'top 88%' }
    });
  });
}
</script>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
