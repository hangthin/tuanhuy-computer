<?php
$pageTitle = 'Trang chủ';
require_once __DIR__.'/../layouts/header.php';

$_approvedJson = __DIR__.'/../../../assets/images/approved.json';
$_approved     = file_exists($_approvedJson) ? (json_decode(file_get_contents($_approvedJson),true)?:array()) : array();
if(!function_exists('_assetUrl')){ function _assetUrl($a,$k){ return !empty($a[$k]['url'])?$a[$k]['url']:''; } }
// Load banners.json — format: {main:[{img,label,title,url},...], side:[{img,label,title,url},...]}
$_bj = ($f=__DIR__.'/../../../storage/banners.json') && file_exists($f) ? (json_decode(file_get_contents($f),true)?:array()) : array();
$_bjMain = !empty($_bj['main']) ? $_bj['main'] : array();
$_bjSide = !empty($_bj['side']) ? $_bj['side'] : array();
// Slide defaults (banners.json → approved.json → local files)
$_defSlides = array(
    array('img'=>_assetUrl($_approved,'hero-banner')?:APP_URL.'/assets/images/hero-banner.png','label'=>'HOT DEAL','title'=>'PC Gaming RTX 4090<br>Sức mạnh vô giới hạn','cta'=>'Mua ngay','url'=>APP_URL.'/products/may-tinh-pc'),
    array('img'=>_assetUrl($_approved,'gaming-pc')  ?:APP_URL.'/assets/images/gaming-pc.jpg',  'label'=>'BÁN CHẠY','title'=>'Cấu hình gaming<br>Giá tốt nhất thị trường','cta'=>'Khám phá','url'=>APP_URL.'/products/may-tinh-pc'),
    array('img'=>_assetUrl($_approved,'laptop')     ?:APP_URL.'/assets/images/laptop.png',      'label'=>'MỚI VỀ', 'title'=>'Laptop mỏng nhẹ<br>Hiệu năng vượt trội',    'cta'=>'Xem ngay','url'=>APP_URL.'/products/laptop'),
);
$_slides = array();
foreach(array(0,1,2) as $_si){
    $_bm = $_bjMain[$_si] ?? array();
    $_slides[] = array(
        'img'   => !empty($_bm['img'])   ? $_bm['img']   : $_defSlides[$_si]['img'],
        'label' => !empty($_bm['label']) ? $_bm['label'] : $_defSlides[$_si]['label'],
        'title' => !empty($_bm['title']) ? $_bm['title'] : $_defSlides[$_si]['title'],
        'cta'   => $_defSlides[$_si]['cta'],
        'url'   => !empty($_bm['url'])   ? $_bm['url']   : $_defSlides[$_si]['url'],
    );
}
// Side banner defaults
$_defSide = array(
    array('img'=>APP_URL.'/assets/images/monitor.png','label'=>'Màn hình','title'=>'Gaming 4K<br>144Hz+',             'url'=>APP_URL.'/products/man-hinh'),
    array('img'=>APP_URL.'/assets/images/mouse.png',  'label'=>'Phụ kiện', 'title'=>'Bàn phím<br>Cơ Gaming','url'=>APP_URL.'/products/chuot'),
);
$_sideBans = array();
foreach(array(0,1) as $_si){
    $_bs = $_bjSide[$_si] ?? array();
    $_sideBans[] = array(
        'img'   => !empty($_bs['img'])   ? $_bs['img']   : $_defSide[$_si]['img'],
        'label' => !empty($_bs['label']) ? $_bs['label'] : $_defSide[$_si]['label'],
        'title' => !empty($_bs['title']) ? $_bs['title'] : $_defSide[$_si]['title'],
        'url'   => !empty($_bs['url'])   ? $_bs['url']   : $_defSide[$_si]['url'],
    );
}
$_catIconMap = array('may-tinh-pc'=>'fa-desktop','laptop'=>'fa-laptop','man-hinh'=>'fa-tv','chuot'=>'fa-computer-mouse','ban-phim'=>'fa-keyboard','ram'=>'fa-memory','cpu'=>'fa-microchip','card-do-hoa'=>'fa-hard-drive','ssd-o-cung'=>'fa-hdd','mainboard'=>'fa-server','phu-kien'=>'fa-headphones');

// Collect unique sale categories for tabs
$_fsCats = array(); $_fsAll = !empty($saleProducts);
foreach($saleProducts??array() as $_sp){
    $sl=$_sp['category_slug']??''; $nm=$_sp['category_name']??'';
    if($sl && !isset($_fsCats[$sl])) $_fsCats[$sl]=$nm;
}
?>
<style>
.wrap{max-width:1280px;margin:0 auto;padding:0 1rem}

/* ── Announcement bar ── */
.ann-bar{background:var(--red);height:30px;overflow:hidden;display:flex;align-items:center}
.ann-track{display:flex;white-space:nowrap;animation:ann-scroll 28s linear infinite}
.ann-track:hover{animation-play-state:paused}
.ann-item{padding:0 2.5rem;font-size:.73rem;font-weight:600;color:#fff;display:inline-flex;align-items:center;gap:.45rem}
.ann-sep{color:rgba(255,255,255,.35);font-size:.8rem}
@keyframes ann-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ── Section base ── */
.bg-w{background:#fff}.bg-g{background:#f7f7f8}
.sec-wrap{max-width:1280px;margin:0 auto;padding:1.75rem 1rem}
.sec-hd{display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap}
.sec-bar{width:3px;height:18px;background:var(--red);border-radius:2px;flex-shrink:0}
.sec-ttl{font-size:1.05rem;font-weight:900;color:#111;flex:1}
.sec-more{display:flex;align-items:center;gap:.3rem;color:var(--red);font-size:.79rem;font-weight:600;white-space:nowrap;transition:gap .18s}
.sec-more:hover{gap:.5rem}

/* ── Flash sale header ── */
.fs-hdr{background:var(--red);border-radius:10px 10px 0 0;display:flex;align-items:center;gap:1rem;padding:.6rem 1rem;flex-wrap:wrap}
.fs-title{font-size:1rem;font-weight:900;color:#fff;letter-spacing:.5px;display:flex;align-items:center;gap:.4rem;flex-shrink:0}
.fs-cd{display:flex;align-items:center;gap:.3rem;flex:1}
.fs-cd-b{background:rgba(0,0,0,.25);border-radius:5px;padding:.2rem .45rem;text-align:center;min-width:36px}
.fs-cd-n{display:block;font-size:1rem;font-weight:900;color:#fff;line-height:1.1}
.fs-cd-l{display:block;font-size:.48rem;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.5px}
.fs-cd-d{color:rgba(255,255,255,.5);font-weight:900;font-size:.95rem}
.fs-more-btn{display:flex;align-items:center;gap:.3rem;color:rgba(255,255,255,.9);font-size:.78rem;font-weight:600;white-space:nowrap;margin-left:auto;transition:color .15s}
.fs-more-btn:hover{color:#fff}

/* ── Flash sale tabs ── */
.fs-tabs{display:flex;gap:.4rem;padding:.65rem 0 .4rem;flex-wrap:nowrap;overflow-x:auto;scrollbar-width:none}
.fs-tabs::-webkit-scrollbar{display:none}
.fs-tab{padding:.28rem .8rem;border-radius:99px;font-size:.78rem;font-weight:600;border:1.5px solid #e0e0e0;color:#555;cursor:pointer;white-space:nowrap;background:#fff;transition:all .15s;flex-shrink:0}
.fs-tab.active,.fs-tab:hover{border-color:var(--red);color:var(--red);background:#fff0f0}

/* ── Flash sale card ── */
.fs-card{width:168px;flex-shrink:0;background:#fff;border-radius:10px;border:1px solid #ebebeb;overflow:hidden;cursor:pointer;transition:transform .2s,box-shadow .2s;text-decoration:none;display:block}
.fs-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.09)}
.fs-img-wrap{height:148px;background:#f5f6f8;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.fs-img-wrap img{width:100%;height:100%;object-fit:contain;padding:6px;transition:transform .3s}
.fs-card:hover .fs-img-wrap img{transform:scale(1.06)}
.fs-disc{position:absolute;top:6px;left:6px;background:var(--red);color:#fff;font-size:.62rem;font-weight:800;padding:2px 6px;border-radius:4px}
.fs-card-body{padding:.55rem .65rem .65rem}
.fs-name{font-size:.75rem;font-weight:600;color:#111;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:.3rem}
.fs-price{color:var(--red);font-weight:900;font-size:.9rem}
.fs-old{color:#aaa;font-size:.72rem;text-decoration:line-through;margin-left:.3rem}
.fs-sold-wrap{margin-top:.4rem}
.fs-sold-bar{height:4px;background:#f0f0f0;border-radius:99px;overflow:hidden;margin-top:2px}
.fs-sold-fill{height:100%;background:linear-gradient(90deg,#f97316,var(--red));border-radius:99px}
.fs-sold-txt{font-size:.62rem;color:#999}

/* ── Category grid ── */
.cg-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:.65rem}
.cg-item{background:#fff;border-radius:11px;padding:.8rem .4rem .65rem;text-align:center;border:1.5px solid transparent;text-decoration:none;display:block;transition:all .2s;position:relative;overflow:hidden}
.cg-item:hover{border-color:var(--red);transform:translateY(-3px);box-shadow:0 6px 18px rgba(227,0,0,.1)}
.cg-ico{width:44px;height:44px;background:#f4f4f5;border-radius:9px;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;font-size:1.1rem;color:#555;transition:all .2s}
.cg-item:hover .cg-ico{background:rgba(227,0,0,.08);color:var(--red)}
.cg-nm{font-size:.7rem;font-weight:700;color:#333;line-height:1.3}
.cg-cnt{font-size:.62rem;color:#bbb;margin-top:2px}

/* ── Horizontal scroll container ── */
.hs-cont{position:relative}
.hs-row{display:flex;gap:.8rem;overflow-x:auto;scroll-behavior:smooth;scrollbar-width:none;padding:.35rem 0 .5rem}
.hs-row::-webkit-scrollbar{display:none}
.hs-item{width:196px;flex-shrink:0}
.hs-arr{position:absolute;top:50%;transform:translateY(-50%);z-index:3;width:34px;height:34px;border-radius:50%;border:1px solid #e0e0e0;background:#fff;color:#333;font-size:1.1rem;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.12);transition:all .18s;line-height:1}
.hs-arr:hover{background:var(--red);color:#fff;border-color:var(--red)}
.hs-p{left:-14px}.hs-n{right:-14px}

/* ── Brand filter tabs ── */
.brand-tabs{display:flex;gap:.35rem;flex-wrap:wrap;margin-left:auto}
.brand-tab{padding:.2rem .65rem;border-radius:99px;font-size:.72rem;font-weight:600;border:1.5px solid #e0e0e0;color:#666;cursor:pointer;background:#fff;transition:all .15s;white-space:nowrap}
.brand-tab.active,.brand-tab:hover{border-color:var(--red);color:var(--red);background:#fff0f0}

/* ── Banner row ── */
.ban-row{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
.ban-card{border-radius:12px;overflow:hidden;position:relative;min-height:180px;display:flex;align-items:flex-end;text-decoration:none;background:#0d0d0d}
.ban-bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform .4s}
.ban-card:hover .ban-bg{transform:scale(1.04)}
.ban-fog{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.75) 0%,rgba(0,0,0,.15) 60%,transparent 100%)}
.ban-txt{position:relative;z-index:1;padding:1.1rem}
.ban-label{font-size:.65rem;font-weight:700;color:var(--red);letter-spacing:2px;text-transform:uppercase;margin-bottom:.25rem}
.ban-title{font-size:1.1rem;font-weight:900;color:#fff;line-height:1.2;margin-bottom:.35rem}
.ban-cta{display:inline-flex;align-items:center;gap:.3rem;background:var(--red);color:#fff;font-size:.74rem;font-weight:700;padding:.3rem .7rem;border-radius:6px}

/* ── Promo grid ── */
.promo-grid{display:grid;grid-template-columns:1fr 1fr;grid-template-rows:auto auto;gap:.9rem}
.promo-item{border-radius:12px;padding:1.3rem 1.1rem;text-decoration:none;display:block;position:relative;overflow:hidden;transition:transform .2s}
.promo-item:hover{transform:translateY(-2px)}
.promo-item .pi-icon{font-size:4rem;position:absolute;right:-.5rem;bottom:-.5rem;opacity:.06}

/* ── Why us ── */
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem}
.why-item{background:#fff;border-radius:11px;padding:1.1rem .8rem;text-align:center;border:1px solid #f0f0f0;transition:all .2s}
.why-item:hover{border-color:rgba(227,0,0,.18);box-shadow:0 6px 18px rgba(227,0,0,.07);transform:translateY(-3px)}
.why-ico{width:44px;height:44px;background:rgba(227,0,0,.07);border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto .65rem;color:var(--red);font-size:1.05rem}
.why-ttl{font-weight:700;font-size:.82rem;color:#111;margin-bottom:.2rem}
.why-dsc{font-size:.71rem;color:#888;line-height:1.5}

/* ── Hero layout ── */
.hero-wrap{background:#111;padding:.6rem 0}
.hero-inner{max-width:1280px;margin:0 auto;padding:0 1rem;display:grid;grid-template-columns:220px 1fr 300px;gap:.55rem;align-items:stretch;height:400px;box-sizing:border-box}
/* Category menu — fixed 220px, no overflow */
.cmenu{width:220px;min-width:0;background:#1a1a1a;border-radius:8px;overflow:hidden;display:flex;flex-direction:column;height:400px}
.cmenu-hd{background:var(--red);padding:.5rem .85rem;font-size:.76rem;font-weight:800;color:#fff;display:flex;align-items:center;gap:.4rem;flex-shrink:0}
.cmenu-list{overflow-y:auto;overflow-x:hidden;flex:1;scrollbar-width:none}
.cmenu-list::-webkit-scrollbar{display:none}
.cmenu-a{display:flex;align-items:center;gap:.5rem;padding:.5rem .85rem;color:#bbb;font-size:.77rem;text-decoration:none;transition:background .15s,padding-left .15s,color .15s;border-bottom:1px solid #242424;white-space:nowrap;overflow:hidden}
.cmenu-a:hover{background:var(--red);color:#fff;padding-left:1.05rem}
.cmenu-a:last-child{border-bottom:none}
.cmenu-a>.fa-solid:first-child{width:15px;text-align:center;font-size:.73rem;flex-shrink:0}
.cmenu-nm{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cmenu-arr{font-size:.52rem;opacity:.35;flex-shrink:0;transition:opacity .15s}
.cmenu-a:hover .cmenu-arr{opacity:1}
/* Main slider — height:400px, background-size:cover fills the div */
.slider{border-radius:8px;overflow:hidden;position:relative;height:400px;width:100%;background:#0a0a0a}
.slide{position:absolute;inset:0;opacity:0;transition:opacity .55s ease;background-size:cover;background-position:center;background-repeat:no-repeat}
.slide.active{opacity:1;z-index:1}
.slide-fog{position:absolute;inset:0;background:linear-gradient(105deg,rgba(0,0,0,.72) 0%,rgba(0,0,0,.25) 55%,transparent 100%);z-index:1}
.slide-txt{position:absolute;bottom:1.5rem;left:1.4rem;z-index:2}
.slide-tag{display:inline-block;background:var(--red);color:#fff;font-size:.58rem;font-weight:800;padding:2px 7px;border-radius:3px;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:.35rem}
.slide-h{font-size:1.25rem;font-weight:900;color:#fff;line-height:1.2;margin-bottom:.5rem;max-width:340px;text-shadow:0 1px 8px rgba(0,0,0,.5)}
.slide-cta{display:inline-flex;align-items:center;gap:.3rem;background:var(--red);color:#fff;font-size:.75rem;font-weight:700;padding:.32rem .8rem;border-radius:5px;text-decoration:none;transition:opacity .15s}
.slide-cta:hover{opacity:.85}
.slider-dots{position:absolute;bottom:.65rem;right:.8rem;display:flex;gap:.3rem;z-index:3}
.sd{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.35);cursor:pointer;transition:all .22s;border:none;padding:0}
.sd.active{background:#fff;width:20px;border-radius:3px}
.sl-arr{position:absolute;top:50%;transform:translateY(-50%);z-index:3;width:30px;height:30px;background:rgba(0,0,0,.45);border:none;color:#fff;font-size:1rem;cursor:pointer;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background .15s}
.sl-arr:hover{background:var(--red)}
.sl-prev{left:.6rem}.sl-next{right:.6rem}
/* Right banners — 300px wide, 2×198px + 4px gap = 400px */
.side-bans{width:300px;display:flex;flex-direction:column;gap:4px;height:400px;flex-shrink:0}
.side-ban{height:198px;flex:0 0 198px;border-radius:8px;overflow:hidden;position:relative;text-decoration:none;display:block;background:#1a1a1a}
.side-ban-bg{position:absolute;inset:0;background-size:cover;background-position:center;background-repeat:no-repeat;transition:transform .35s}
.side-ban:hover .side-ban-bg{transform:scale(1.06)}
.side-ban-fog{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.82) 0%,rgba(0,0,0,.15) 60%,transparent 100%)}
.side-ban-txt{position:absolute;bottom:.65rem;left:.75rem;z-index:1}
.side-ban-label{font-size:.55rem;font-weight:800;color:var(--red);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:.1rem}
.side-ban-title{font-size:.8rem;font-weight:800;color:#fff;line-height:1.25}
/* Responsive */
@media(max-width:1024px){.hero-inner{grid-template-columns:1fr 280px;height:360px}.cmenu{display:none}.slider{height:360px}.side-bans{width:280px;height:360px;flex-shrink:0}.side-ban{height:178px;flex:0 0 178px}}
@media(max-width:768px){.hero-inner{grid-template-columns:1fr;height:auto}.slider{height:240px}.side-bans{display:none}.cmenu{display:none}}

/* ── Fade-in reveal ── */
.reveal{opacity:0;transform:translateY(28px);transition:opacity .55s ease,transform .55s ease}
.reveal.visible{opacity:1;transform:none}

@media(max-width:900px){
  .cg-grid{grid-template-columns:repeat(4,1fr)}
  .ban-row{grid-template-columns:1fr}
  .promo-grid{grid-template-columns:1fr}
  .hs-p{left:-8px}.hs-n{right:-8px}
}
@media(max-width:600px){
  .cg-grid{grid-template-columns:repeat(3,1fr)}
  .fs-cd-n{font-size:.88rem}
}
</style>

<!-- ── ANNOUNCEMENT BAR ── -->
<div class="ann-bar">
  <div class="ann-track">
    <?php $annItems = array(
      array('fa-truck-fast',  'Miễn phí giao hàng đơn từ 500K'),
      array('fa-certificate', 'Bảo hành chính hãng 100%'),
      array('fa-rotate-left', 'Đổi trả 7 ngày không điều kiện'),
      array('fa-headset',     'Tư vấn kỹ thuật 24/7'),
      array('fa-shield-halved','Thanh toán an toàn bảo mật'),
      array('fa-bolt',        'Giao hàng toàn quốc trong 24h'),
    );
    // Double for seamless loop
    $annDouble = array_merge($annItems,$annItems);
    foreach($annDouble as $ai): ?>
    <span class="ann-item"><i class="fa-solid <?= $ai[0] ?>"></i><?= $ai[1] ?></span>
    <span class="ann-sep">•</span>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── HERO ── -->
<div class="hero-wrap">
  <div class="hero-inner">

    <!-- Left: category menu -->
    <div class="cmenu">
      <div class="cmenu-hd"><i class="fa-solid fa-bars"></i> Danh mục sản phẩm</div>
      <div class="cmenu-list">
        <?php foreach($categories as $_cm): ?>
        <a href="<?= APP_URL ?>/products/<?= $_cm['slug'] ?>" class="cmenu-a">
          <i class="fa-solid <?= isset($_catIconMap[$_cm['slug']])?$_catIconMap[$_cm['slug']]:'fa-box' ?>"></i>
          <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($_cm['name']) ?></span>
          <i class="fa-solid fa-chevron-right cmenu-arr"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Center: main slider -->
    <div class="slider" id="mainSlider">
      <?php
      foreach($_slides as $si => $sl): ?>
      <div class="slide <?= $si===0?'active':'' ?>" style="background-image:url('<?= htmlspecialchars($sl['img']) ?>')">
        <div class="slide-fog"></div>
        <div class="slide-txt">
          <div class="slide-tag"><?= htmlspecialchars($sl['label']) ?></div>
          <div class="slide-h"><?= $sl['title'] ?></div>
          <a href="<?= htmlspecialchars($sl['url']) ?>" class="slide-cta"><i class="fa-solid fa-bolt" style="font-size:.65rem"></i><?= htmlspecialchars($sl['cta']) ?></a>
        </div>
      </div>
      <?php endforeach; ?>
      <button class="sl-arr sl-prev" onclick="slGo(-1)" aria-label="Trước">&#8249;</button>
      <button class="sl-arr sl-next" onclick="slGo(1)"  aria-label="Tiếp">&#8250;</button>
      <div class="slider-dots">
        <?php foreach($_slides as $si=>$_): ?>
        <button class="sd <?= $si===0?'active':'' ?>" onclick="slGoTo(<?= $si ?>)"></button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Right: 2 stacked mini banners -->
    <div class="side-bans">
      <?php foreach($_sideBans as $_sbn): ?>
      <a href="<?= htmlspecialchars($_sbn['url']) ?>" class="side-ban">
        <div class="side-ban-bg" style="background-image:url('<?= htmlspecialchars($_sbn['img']) ?>')"></div>
        <div class="side-ban-fog"></div>
        <div class="side-ban-txt">
          <div class="side-ban-label"><?= htmlspecialchars($_sbn['label']) ?></div>
          <div class="side-ban-title"><?= $_sbn['title'] ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<!-- ── FLASH SALE ── -->
<?php if(!empty($saleProducts)): ?>
<div class="bg-w" style="padding:0 0 1.5rem">
  <div class="wrap" style="padding-top:1.25rem">
    <!-- Header bar -->
    <div class="fs-hdr">
      <span class="fs-title"><i class="fa-solid fa-fire-flame-curved"></i> FLASH SALE</span>
      <div class="fs-cd">
        <div class="fs-cd-b"><span class="fs-cd-n" id="fcd-h">00</span><span class="fs-cd-l">Giờ</span></div>
        <span class="fs-cd-d">:</span>
        <div class="fs-cd-b"><span class="fs-cd-n" id="fcd-m">00</span><span class="fs-cd-l">Phút</span></div>
        <span class="fs-cd-d">:</span>
        <div class="fs-cd-b"><span class="fs-cd-n" id="fcd-s">00</span><span class="fs-cd-l">Giây</span></div>
      </div>
      <a href="<?= APP_URL ?>/products" class="fs-more-btn">Xem thêm <i class="fa-solid fa-arrow-right" style="font-size:.7rem"></i></a>
    </div>

    <!-- Category tabs -->
    <div class="fs-tabs" id="fsTabs">
      <button class="fs-tab active" onclick="fsTab(this,'all')">Tất cả</button>
      <?php foreach($_fsCats as $_fsl => $_fsn): ?>
      <button class="fs-tab" onclick="fsTab(this,'<?= htmlspecialchars($_fsl) ?>')"><?= htmlspecialchars($_fsn) ?></button>
      <?php endforeach; ?>
    </div>

    <!-- Scroll row -->
    <div class="hs-cont">
      <button class="hs-arr hs-p" onclick="scr('fs-row',-1)" aria-label="Trước">&#8249;</button>
      <div class="hs-row" id="fs-row">
        <?php foreach($saleProducts as $_sp):
          $_sale  = (float)$_sp['sale_price'];
          $_orig  = (float)$_sp['price'];
          $_disc  = (int)$_sp['discount_pct'];
          $_sold  = (int)($_sp['sold']??0);
          $_stock = (int)($_sp['stock']??1);
          $_pct   = $_stock>0 ? min(100, round($_sold/($_sold+$_stock)*100)) : 100;
        ?>
        <a href="<?= APP_URL ?>/products/detail/<?= htmlspecialchars($_sp['slug']) ?>" class="fs-card" data-fcat="<?= htmlspecialchars($_sp['category_slug']??'') ?>">
          <div class="fs-img-wrap">
            <?php if(!empty($_sp['image']) && $_sp['image']!=='default.jpg'): ?>
            <img src="<?= UPLOAD_URL.htmlspecialchars($_sp['image']) ?>" alt="<?= htmlspecialchars($_sp['name']) ?>" loading="lazy">
            <?php else: ?>
            <i class="fa-solid fa-box" style="font-size:2.5rem;color:#ccc"></i>
            <?php endif; ?>
            <?php if($_disc>0): ?><span class="fs-disc">-<?= $_disc ?>%</span><?php endif; ?>
          </div>
          <div class="fs-card-body">
            <div class="fs-name"><?= htmlspecialchars($_sp['name']) ?></div>
            <div>
              <span class="fs-price"><?= formatPrice($_sale) ?></span>
              <?php if($_orig>$_sale): ?><span class="fs-old"><?= formatPrice($_orig) ?></span><?php endif; ?>
            </div>
            <div class="fs-sold-wrap">
              <div class="fs-sold-txt">Đã bán <?= $_sold ?></div>
              <div class="fs-sold-bar"><div class="fs-sold-fill" style="width:<?= $_pct ?>%"></div></div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <button class="hs-arr hs-n" onclick="scr('fs-row',1)" aria-label="Tiếp">&#8250;</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── PER-CATEGORY PRODUCT SECTIONS ── -->
<?php
$_secDefs = array(
  array('slug'=>'may-tinh-pc', 'title'=>'Máy tính PC bán chạy',   'icon'=>'fa-desktop',       'bg'=>'bg-w'),
  array('slug'=>'laptop',      'title'=>'Laptop nổi bật',          'icon'=>'fa-laptop',        'bg'=>'bg-g'),
  array('slug'=>'man-hinh',    'title'=>'Màn hình gaming',         'icon'=>'fa-tv',            'bg'=>'bg-w'),
  array('slug'=>'chuot',       'title'=>'Chuột gaming',            'icon'=>'fa-computer-mouse','bg'=>'bg-g'),
  array('slug'=>'ban-phim',    'title'=>'Bàn phím cơ',             'icon'=>'fa-keyboard',      'bg'=>'bg-w'),
);
foreach($_secDefs as $_sd):
  $_prods = $catProducts[$_sd['slug']] ?? array();
  if(empty($_prods)) continue;
  // Collect unique brands
  $_brands = array(); foreach($_prods as $_bp){ if(!empty($_bp['brand_name']) && !in_array($_bp['brand_name'],$_brands)) $_brands[]=$_bp['brand_name']; }
  $_hsId = 'hs-'.$_sd['slug'];
?>
<div class="<?= $_sd['bg'] ?> reveal">
  <div class="sec-wrap">
    <div class="sec-hd" style="align-items:center">
      <div class="sec-bar"></div>
      <h2 class="sec-ttl"><i class="fa-solid <?= $_sd['icon'] ?>" style="color:var(--red);margin-right:.3rem;font-size:.9rem"></i><?= $_sd['title'] ?></h2>
      <?php if(count($_brands)>1): ?>
      <div class="brand-tabs" id="bt-<?= $_sd['slug'] ?>">
        <button class="brand-tab active" onclick="brandFilter('<?= $_hsId ?>','all',this)">Tất cả</button>
        <?php foreach($_brands as $_b): ?>
        <button class="brand-tab" onclick="brandFilter('<?= $_hsId ?>','<?= htmlspecialchars($_b) ?>',this)"><?= htmlspecialchars($_b) ?></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <a href="<?= APP_URL ?>/products/<?= $_sd['slug'] ?>" class="sec-more" style="margin-left:<?= count($_brands)>1?'0':'auto' ?>">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="hs-cont">
      <button class="hs-arr hs-p" onclick="scr('<?= $_hsId ?>',-1)" aria-label="Trước">&#8249;</button>
      <div class="hs-row" id="<?= $_hsId ?>">
        <?php foreach($_prods as $p): ?>
        <div class="hs-item" data-brand="<?= htmlspecialchars($p['brand_name']??'') ?>">
          <?php include __DIR__.'/../products/product_card.php'; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="hs-arr hs-n" onclick="scr('<?= $_hsId ?>',1)" aria-label="Tiếp">&#8250;</button>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- ── PROMOTIONAL 2×2 GRID ── -->
<div class="bg-w reveal">
  <div class="sec-wrap">
    <div class="sec-hd">
      <div class="sec-bar"></div>
      <h2 class="sec-ttl">Ưu đãi đặc biệt</h2>
    </div>
    <div class="promo-grid">
      <a href="<?= APP_URL ?>/products/cpu" class="promo-item" style="background:linear-gradient(135deg,#0a0a1a,#111530)">
        <div class="pi-icon"><i class="fa-solid fa-microchip"></i></div>
        <div style="font-size:.65rem;font-weight:700;color:#6366f1;letter-spacing:2px;text-transform:uppercase;margin-bottom:.3rem">CPU &amp; MAINBOARD</div>
        <div style="font-weight:900;font-size:1rem;color:#fff;margin-bottom:.3rem">Intel Gen 14 / AMD Zen 4</div>
        <div style="font-size:.78rem;color:#555;margin-bottom:.75rem">Hiệu năng đỉnh — Tiết kiệm điện</div>
        <span style="display:inline-flex;align-items:center;gap:.3rem;color:#6366f1;font-size:.78rem;font-weight:700">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.65rem"></i></span>
      </a>
      <a href="<?= APP_URL ?>/products/card-do-hoa" class="promo-item" style="background:linear-gradient(135deg,#0d0a00,#1c1400)">
        <div class="pi-icon"><i class="fa-solid fa-gamepad"></i></div>
        <div style="font-size:.65rem;font-weight:700;color:#f59e0b;letter-spacing:2px;text-transform:uppercase;margin-bottom:.3rem">CARD ĐỒ HỌA</div>
        <div style="font-weight:900;font-size:1rem;color:#fff;margin-bottom:.3rem">NVIDIA RTX 40 Series</div>
        <div style="font-size:.78rem;color:#555;margin-bottom:.75rem">4K Gaming — AI Rendering</div>
        <span style="display:inline-flex;align-items:center;gap:.3rem;color:#f59e0b;font-size:.78rem;font-weight:700">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.65rem"></i></span>
      </a>
      <a href="<?= APP_URL ?>/products/ram" class="promo-item" style="background:linear-gradient(135deg,#00100a,#01200f)">
        <div class="pi-icon"><i class="fa-solid fa-memory"></i></div>
        <div style="font-size:.65rem;font-weight:700;color:#22c55e;letter-spacing:2px;text-transform:uppercase;margin-bottom:.3rem">RAM &amp; SSD</div>
        <div style="font-weight:900;font-size:1rem;color:#fff;margin-bottom:.3rem">DDR5 — NVMe Gen 4</div>
        <div style="font-size:.78rem;color:#555;margin-bottom:.75rem">Tốc độ siêu cao — Bền bỉ</div>
        <span style="display:inline-flex;align-items:center;gap:.3rem;color:#22c55e;font-size:.78rem;font-weight:700">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.65rem"></i></span>
      </a>
      <a href="<?= APP_URL ?>/products/phu-kien" class="promo-item" style="background:linear-gradient(135deg,#0d0005,#1c000e)">
        <div class="pi-icon"><i class="fa-solid fa-headphones"></i></div>
        <div style="font-size:.65rem;font-weight:700;color:#ec4899;letter-spacing:2px;text-transform:uppercase;margin-bottom:.3rem">PHỤ KIỆN</div>
        <div style="font-weight:900;font-size:1rem;color:#fff;margin-bottom:.3rem">Headset, Mousepad &amp; Hơn nữa</div>
        <div style="font-size:.78rem;color:#555;margin-bottom:.75rem">Setup gaming hoàn chỉnh</div>
        <span style="display:inline-flex;align-items:center;gap:.3rem;color:#ec4899;font-size:.78rem;font-weight:700">Xem ngay <i class="fa-solid fa-arrow-right" style="font-size:.65rem"></i></span>
      </a>
    </div>
  </div>
</div>

<!-- ── WHY US ── -->
<div class="bg-g reveal">
  <div class="sec-wrap">
    <div class="sec-hd" style="justify-content:center;text-align:center">
      <div class="sec-bar"></div>
      <h2 class="sec-ttl">Tại sao chọn Tuấn Huy Computer?</h2>
    </div>
    <div class="why-grid">
      <?php foreach(array(
        array('fa-certificate',    'Hàng chính hãng',  'Tem niêm phong, hóa đơn VAT đầy đủ'),
        array('fa-truck-fast',     'Giao hàng 24h',    'Ship toàn quốc, nhận hàng nhanh chóng'),
        array('fa-screwdriver-wrench','BH tận nơi',   'Hỗ trợ kỹ thuật miễn phí tại nhà'),
        array('fa-tag',            'Giá tốt nhất',     'Cam kết cạnh tranh, hoàn tiền nếu thấp hơn'),
        array('fa-rotate-left',    'Đổi trả 7 ngày',  'Không cần lý do, miễn phí đổi'),
        array('fa-headset',        'Hỗ trợ 24/7',     'Tư vấn kỹ thuật mọi lúc bạn cần'),
      ) as $w): ?>
      <div class="why-item">
        <div class="why-ico"><i class="fa-solid <?= $w[0] ?>"></i></div>
        <div class="why-ttl"><?= $w[1] ?></div>
        <div class="why-dsc"><?= $w[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── LOGIN CTA ── -->
<?php if(!isLoggedIn()): ?>
<div style="background:linear-gradient(135deg,#0d0d0d,#1a0000);padding:2.5rem 1rem;text-align:center" class="reveal">
  <div style="max-width:520px;margin:0 auto">
    <div style="width:52px;height:52px;background:rgba(227,0,0,.12);border-radius:13px;display:flex;align-items:center;justify-content:center;margin:0 auto .8rem;font-size:1.2rem;color:var(--red)"><i class="fa-solid fa-user-lock"></i></div>
    <h2 style="color:#fff;font-size:1.2rem;font-weight:900;margin-bottom:.45rem">Đăng nhập để nhận ưu đãi thành viên</h2>
    <p style="color:#555;font-size:.83rem;margin-bottom:1.1rem;line-height:1.7">Theo dõi đơn hàng, tích điểm đổi quà và nhận flash sale sớm nhất.</p>
    <div style="display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap">
      <button onclick="openLM()" class="btn-red"><i class="fa-solid fa-right-to-bracket"></i>Đăng nhập</button>
      <button onclick="openLM();switchLMTab('r')" class="btn-secondary"><i class="fa-solid fa-user-plus"></i>Tạo tài khoản</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// ── Countdown to midnight ──────────────────────────────────────────
(function(){
  function tick(){
    var now=new Date(), end=new Date(); end.setHours(23,59,59,0);
    var d=Math.max(0,Math.floor((end-now)/1000));
    var h=Math.floor(d/3600); d%=3600; var m=Math.floor(d/60),s=d%60;
    var eh=document.getElementById('fcd-h'),em=document.getElementById('fcd-m'),es=document.getElementById('fcd-s');
    if(eh)eh.textContent=String(h).padStart(2,'0');
    if(em)em.textContent=String(m).padStart(2,'0');
    if(es)es.textContent=String(s).padStart(2,'0');
  }
  setInterval(tick,1000); tick();
})();

// ── Horizontal scroll ─────────────────────────────────────────────
function scr(id,dir){
  var el=document.getElementById(id);
  if(el) el.scrollBy({left:dir*220,behavior:'smooth'});
}

// ── Flash sale tab filter ─────────────────────────────────────────
function fsTab(btn,cat){
  document.querySelectorAll('.fs-tab').forEach(function(b){b.classList.remove('active');});
  btn.classList.add('active');
  document.querySelectorAll('#fs-row .fs-card').forEach(function(c){
    c.style.display=(cat==='all'||c.dataset.fcat===cat)?'':'none';
  });
}

// ── Brand filter tabs ─────────────────────────────────────────────
function brandFilter(rowId,brand,btn){
  btn.closest('.brand-tabs').querySelectorAll('.brand-tab').forEach(function(b){b.classList.remove('active');});
  btn.classList.add('active');
  document.getElementById(rowId).querySelectorAll('.hs-item').forEach(function(item){
    item.style.display=(brand==='all'||item.dataset.brand===brand)?'':'none';
  });
}

// ── Hero slider ───────────────────────────────────────────────────
(function(){
  var slides=document.querySelectorAll('#mainSlider .slide');
  var dots=document.querySelectorAll('#mainSlider .sd');
  if(!slides.length) return;
  var cur=0, timer;
  function goTo(n){
    slides[cur].classList.remove('active');
    dots[cur].classList.remove('active');
    cur=(n+slides.length)%slides.length;
    slides[cur].classList.add('active');
    dots[cur].classList.add('active');
  }
  function startTimer(){ timer=setInterval(function(){goTo(cur+1);},5000); }
  function resetTimer(){ clearInterval(timer); startTimer(); }
  window.slGo=function(d){ goTo(cur+d); resetTimer(); };
  window.slGoTo=function(n){ goTo(n); resetTimer(); };
  startTimer();
})();

// ── Scroll reveal ─────────────────────────────────────────────────
(function(){
  var obs=new IntersectionObserver(function(entries){
    entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});
  },{threshold:.08});
  document.querySelectorAll('.reveal').forEach(function(el){obs.observe(el);});
})();
</script>

<?php require_once __DIR__.'/../layouts/footer.php'; ?>
