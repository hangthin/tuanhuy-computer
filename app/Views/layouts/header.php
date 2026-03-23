<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= isset($pageTitle)?sanitize($pageTitle):'Trang chủ' ?> | Tuấn Huy Computer</title>
<!-- Font: dùng system font stack - không cần Google Fonts, không bị lỗi offline -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ── RESET & VARIABLES ── */
:root{
  --red:#E30000;--red-dk:#B00000;
  --bg:#f3f4f6;--white:#fff;
  --dark:#0d0d0d;--dark2:#141414;--dark3:#1c1c1c;
  --border:#e5e7eb;--border-dk:#252525;
  --text:#111;--text2:#6b7280;--text3:#9ca3af;
  --font:'Segoe UI',system-ui,-apple-system,sans-serif;
  --font-head:'Segoe UI Black','Arial Black',system-ui,sans-serif;
  --r:10px;--r-lg:16px;
  --shadow:0 2px 16px rgba(0,0,0,.08);
  --shadow-md:0 4px 24px rgba(0,0,0,.12);
  --t:.18s ease;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--font);background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;line-height:1.5}
img{max-width:100%}
a{text-decoration:none}
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:#f0f0f0}
::-webkit-scrollbar-thumb{background:var(--red);border-radius:3px}

/* ── LOADER ── */
#pg-ld{position:fixed;inset:0;background:var(--dark);z-index:9999;display:flex;align-items:center;justify-content:center;transition:opacity .5s,visibility .5s}
#pg-ld.out{opacity:0;visibility:hidden}
.ld-txt{font-size:1.3rem;font-weight:900;color:#fff;letter-spacing:-0.5px;margin-bottom:14px}
.ld-txt em{color:var(--red);font-style:normal}
.ld-bar{width:160px;height:3px;background:#222;border-radius:99px;overflow:hidden}
.ld-fill{height:100%;width:0;background:var(--red);border-radius:99px;animation:fill .7s .1s ease forwards}
@keyframes fill{to{width:100%}}

/* ── TOAST ── */
#toast-wrap{position:fixed;bottom:20px;right:20px;z-index:8999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{background:var(--dark2);color:#fff;padding:11px 16px;border-radius:10px;min-width:240px;font-size:.82rem;font-weight:500;pointer-events:auto;box-shadow:0 8px 32px rgba(0,0,0,.35);display:flex;align-items:center;gap:10px;animation:tIn .28s ease;transition:opacity .3s,transform .3s}
.toast.out{opacity:0;transform:translateX(16px)}
.t-ico{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;flex-shrink:0}
.toast.success .t-ico{background:rgba(34,197,94,.15);color:#22c55e}
.toast.error   .t-ico{background:rgba(239,68,68,.15);color:#ef4444}
.toast.success{border-left:3px solid #22c55e}
.toast.error  {border-left:3px solid #ef4444}
@keyframes tIn{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:none}}

/* ── TOPBAR ── */
.topbar{background:var(--dark);padding:5px 0;font-size:.72rem;color:#555}
.topbar a{color:#666;transition:color var(--t)}.topbar a:hover{color:var(--red)}
.tb-inner{max-width:1280px;margin:0 auto;padding:0 1rem;display:flex;justify-content:space-between;align-items:center;gap:.75rem}
.tb-badge{background:rgba(227,0,0,.18);color:var(--red);padding:1px 7px;border-radius:99px;font-size:.65rem;font-weight:700;margin-left:5px}

/* ── NAVBAR ── */
.navbar{background:var(--dark2);position:sticky;top:0;z-index:500;border-bottom:1px solid var(--border-dk);box-shadow:0 2px 12px rgba(0,0,0,.2)}
.nav-inner{max-width:1280px;margin:0 auto;padding:0 1rem;height:62px;display:flex;align-items:center;gap:1rem}

/* Logo */
.logo{display:flex;align-items:center;gap:9px;flex-shrink:0}
.logo-icon{width:38px;height:38px;background:var(--red);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.85rem;color:#fff}
.logo-name{font-weight:900;font-size:.88rem;color:#fff;letter-spacing:-.3px;line-height:1.15;font-family:var(--font-head)}
.logo-sub{font-size:.55rem;color:var(--red);letter-spacing:2.5px;font-weight:700}

/* Search */
.search-box{flex:1;max-width:440px;position:relative}
.s-inp{width:100%;height:38px;background:#1a1a1a;border:1.5px solid #282828;border-radius:8px;padding:0 38px 0 14px;color:#e0e0e0;font-size:.82rem;font-family:var(--font);outline:none;transition:border-color var(--t)}
.s-inp:focus{border-color:var(--red)}.s-inp::placeholder{color:#444}
.s-btn{position:absolute;right:0;top:0;height:100%;width:38px;background:none;border:none;color:#555;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:color var(--t)}.s-btn:hover{color:var(--red)}

/* Nav links */
.nav-links{display:flex;align-items:center;gap:2px}
.nl{display:flex;align-items:center;gap:.3rem;padding:.35rem .6rem;border-radius:7px;color:#aaa;font-size:.82rem;font-weight:500;transition:all var(--t)}
.nl:hover,.nl.on{color:#fff;background:rgba(255,255,255,.07)}
.nl.on{color:var(--red)}

/* Dropdown */
.dd{position:relative}
.dd:hover .dd-panel{opacity:1;visibility:visible;transform:translateY(0)}
.dd-panel{position:absolute;top:calc(100% + 10px);left:0;min-width:230px;background:var(--dark2);border:1px solid #222;border-top:2px solid var(--red);border-radius:0 0 12px 12px;opacity:0;visibility:hidden;transform:translateY(-6px);transition:all .18s;z-index:300;padding:.4rem 0;box-shadow:0 12px 40px rgba(0,0,0,.3)}
.dd-item{display:flex;align-items:center;gap:9px;padding:.5rem .9rem;color:#999;font-size:.79rem;transition:all var(--t)}
.dd-item:hover{color:#fff;background:rgba(255,255,255,.04);padding-left:1.1rem}
.dd-item i{width:15px;color:var(--red);opacity:.7;font-size:.75rem}
.dd-sep{height:1px;background:#1e1e1e;margin:.3rem 0}

/* Right actions */
.nav-right{display:flex;align-items:center;gap:.35rem;margin-left:auto}
.ico-btn{width:37px;height:37px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#aaa;transition:all var(--t);position:relative;border:none;background:none;cursor:pointer;font-size:.9rem}
.ico-btn:hover{color:#fff;background:rgba(255,255,255,.07)}
.cart-dot{position:absolute;top:-2px;right:-2px;background:var(--red);color:#fff;font-size:.58rem;font-weight:700;min-width:16px;height:16px;border-radius:99px;display:flex;align-items:center;justify-content:center;border:2px solid var(--dark2)}

/* User menu */
.u-btn{display:flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.06);border:1px solid #252525;border-radius:8px;padding:.28rem .6rem .28rem .3rem;color:#aaa;cursor:pointer;font-size:.78rem;font-family:var(--font);transition:all var(--t)}
.u-btn:hover{background:rgba(255,255,255,.1);color:#fff}
.u-av{width:28px;height:28px;background:var(--red);border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;color:#fff;flex-shrink:0}
.u-dd{position:relative}
.u-dd:hover .u-panel,.u-dd:focus-within .u-panel{opacity:1;visibility:visible;transform:translateY(0)}
.u-panel{position:absolute;top:calc(100% + 6px);right:0;width:190px;background:var(--dark2);border:1px solid #222;border-radius:12px;opacity:0;visibility:hidden;transform:translateY(-6px);transition:all .18s;z-index:300;overflow:hidden;box-shadow:0 12px 32px rgba(0,0,0,.3)}
.u-item{display:flex;align-items:center;gap:9px;padding:.55rem .9rem;color:#999;font-size:.79rem;transition:all var(--t)}
.u-item:hover{color:#fff;background:rgba(255,255,255,.04)}
.u-item i{width:14px;text-align:center;color:var(--red);opacity:.8}
.u-item.red{color:#f87171}.u-item.red i{color:#f87171}

.btn-login{display:flex;align-items:center;gap:.4rem;background:var(--red);color:#fff;padding:.38rem .85rem;border-radius:8px;font-size:.78rem;font-weight:700;transition:all var(--t)}
.btn-login:hover{background:var(--red-dk)}

.hamburger{display:none;background:none;border:1px solid #2a2a2a;border-radius:7px;width:35px;height:35px;align-items:center;justify-content:center;color:#aaa;cursor:pointer;font-size:.88rem}
.hamburger:hover{color:#fff;border-color:#444}

/* Mobile menu */
.mob-menu{display:none;background:var(--dark2);border-top:1px solid #1c1c1c;padding:.6rem 1rem 1rem}
.mob-menu.open{display:block;animation:slD .2s ease}
.mob-link{display:flex;align-items:center;gap:.55rem;padding:.5rem .4rem;color:#aaa;font-size:.84rem;border-radius:7px;transition:all var(--t)}
.mob-link:hover{color:#fff;background:rgba(255,255,255,.05)}
.mob-link i{width:15px;text-align:center;color:var(--red);opacity:.7}
@keyframes slD{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}

/* Search bar expand */
.s-bar{display:none;padding:.55rem 1rem;border-top:1px solid #1c1c1c}

/* Flash */
.flash-wrap{max-width:1280px;margin:.65rem auto;padding:0 1rem}
.flash-inner{display:flex;align-items:center;gap:.6rem;padding:.65rem .9rem;border-radius:9px;font-size:.84rem;animation:slD .3s}
.flash-inner.success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.flash-inner.error  {background:#fef2f2;border:1px solid #fecaca;color:#991b1b}

/* ── GLOBAL COMPONENTS ── */
.btn-red,.btn-primary{display:inline-flex;align-items:center;gap:.4rem;background:var(--red);color:#fff;border:none;padding:.55rem 1.2rem;border-radius:var(--r);font-weight:700;font-size:.84rem;cursor:pointer;font-family:var(--font);transition:all var(--t);letter-spacing:.01em}
.btn-red:hover,.btn-primary:hover{background:var(--red-dk);box-shadow:0 4px 16px rgba(227,0,0,.3);transform:translateY(-1px)}
.btn-dark{display:inline-flex;align-items:center;gap:.4rem;background:#111;color:#fff;border:none;padding:.55rem 1.2rem;border-radius:var(--r);font-weight:700;font-size:.84rem;cursor:pointer;font-family:var(--font);transition:all var(--t)}
.btn-dark:hover{background:#2a2a2a}
.btn-outline,.btn-ghost{display:inline-flex;align-items:center;gap:.4rem;background:transparent;color:var(--red);border:1.5px solid var(--red);padding:.53rem 1.2rem;border-radius:var(--r);font-weight:700;font-size:.84rem;cursor:pointer;font-family:var(--font);transition:all var(--t)}
.btn-outline:hover,.btn-ghost:hover{background:var(--red);color:#fff}
.btn-secondary{display:inline-flex;align-items:center;gap:.4rem;background:transparent;color:#ddd;border:1.5px solid #333;padding:.53rem 1.2rem;border-radius:var(--r);font-weight:600;font-size:.84rem;cursor:pointer;font-family:var(--font);transition:all var(--t)}
.btn-secondary:hover{border-color:#555;background:rgba(255,255,255,.06)}

.form-input{width:100%;padding:.55rem .85rem;border:1.5px solid var(--border);border-radius:var(--r);outline:none;font-family:var(--font);font-size:.875rem;background:#fff;color:var(--text);transition:border-color var(--t)}
.form-input:focus{border-color:var(--red)}
.price-final{color:var(--red);font-weight:700}
.price-old{text-decoration:line-through;color:var(--text3);font-size:.83rem}
.stars{color:#f59e0b}
.badge-sale{background:var(--red);color:#fff;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:4px}
.badge-new{background:#111;color:#fff;font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:4px}

/* Product card */
.product-card{background:var(--white);border-radius:var(--r-lg);overflow:hidden;transition:transform .22s,box-shadow .22s;border:1px solid var(--border);position:relative}
.product-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.1)}
.card-actions{position:absolute;bottom:0;left:0;right:0;padding:.5rem .6rem;background:linear-gradient(0deg,rgba(0,0,0,.75),transparent);opacity:0;transform:translateY(4px);transition:all .2s;display:flex;gap:.4rem;justify-content:center}
.product-card:hover .card-actions{opacity:1;transform:translateY(0)}

/* Status badges */
.status-badge{padding:3px 9px;border-radius:99px;font-size:.7rem;font-weight:600}
.status-pending   {background:#fef9c3;color:#854d0e}
.status-confirmed {background:#dbeafe;color:#1e40af}
.status-processing{background:#fde8d8;color:#9a3412}
.status-shipping  {background:#e0f2fe;color:#075985}
.status-delivered {background:#dcfce7;color:#166534}
.status-cancelled {background:#fee2e2;color:#991b1b}

/* Reveal */
.reveal{opacity:0;transform:translateY(18px);transition:opacity .5s,transform .5s}
.reveal.visible{opacity:1;transform:none}

@media(max-width:768px){
  .nav-links,.search-box{display:none!important}
  .hamburger{display:flex}
  .u-btn .u-name{display:none}
}
@keyframes pulseRed{0%,100%{box-shadow:0 0 0 0 rgba(227,0,0,.4)}50%{box-shadow:0 0 0 8px rgba(227,0,0,0)}}
</style>
</head>
<body>

<!-- LOADER -->
<div id="pg-ld">
  <div style="text-align:center">
    <div class="ld-txt">TUẤN HUY <em>COMPUTER</em></div>
    <div class="ld-bar"><div class="ld-fill"></div></div>
  </div>
</div>

<div id="toast-wrap"></div>

<!-- TOPBAR -->
<div class="topbar">
  <div class="tb-inner">
    <div style="display:flex;align-items:center;gap:1rem">
      <span><i class="fa-solid fa-phone" style="color:var(--red);margin-right:4px"></i><a href="tel:0909999888">0909 999 888</a></span>
      <span class="hd-sm"><i class="fa-solid fa-envelope" style="color:var(--red);margin-right:4px"></i><a href="mailto:info@tuanhuycmp.vn">info@tuanhuycmp.vn</a></span>
      <span class="hd-sm"><i class="fa-solid fa-clock" style="color:var(--red);margin-right:4px"></i>8:00 – 21:00</span>
    </div>
    <div style="display:flex;align-items:center;gap:1rem">
      <span><i class="fa-solid fa-truck-fast" style="color:var(--red);margin-right:4px"></i>Free ship &ge;500K<span class="tb-badge">HOT</span></span>
      <span class="hd-sm"><i class="fa-solid fa-shield-halved" style="color:var(--red);margin-right:4px"></i>BH Chính hãng</span>
    </div>
  </div>
</div>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="<?= APP_URL ?>/" class="logo">
      <div class="logo-icon"><i class="fa-solid fa-microchip"></i></div>
      <div><div class="logo-name">TUẤN HUY</div><div class="logo-sub">COMPUTER</div></div>
    </a>

    <form action="<?= APP_URL ?>/search" method="GET" class="search-box" id="desk-search">
      <input class="s-inp" type="text" name="q" value="<?= htmlspecialchars(isset($_GET['q'])?$_GET['q']:'') ?>" placeholder="Tìm PC, Laptop, CPU, RAM...">
      <button type="submit" class="s-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>

    <div class="nav-links" id="nav-links">
      <?php
      $cp = parse_url(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'/', PHP_URL_PATH);
      $isHome = in_array($cp,['/tuanhuy_computer/','/tuanhuy_computer']);
      if(!isset($allCategories)) $allCategories = (new CategoryModel())->getAll();
      ?>
      <a href="<?= APP_URL ?>/" class="nl <?= $isHome?'on':'' ?>"><i class="fa-solid fa-house"></i> Trang chủ</a>
      <div class="dd">
        <a href="<?= APP_URL ?>/products" class="nl"><i class="fa-solid fa-layer-group"></i> Sản phẩm <i class="fa-solid fa-chevron-down" style="font-size:.58rem;opacity:.5"></i></a>
        <div class="dd-panel">
          <a href="<?= APP_URL ?>/products" class="dd-item"><i class="fa-solid fa-border-all"></i>Tất cả sản phẩm</a>
          <div class="dd-sep"></div>
          <?php foreach($allCategories as $cat): ?>
          <a href="<?= APP_URL ?>/products/<?= $cat['slug'] ?>" class="dd-item">
            <i class="fa-solid fa-angle-right"></i><?= htmlspecialchars($cat['name']) ?>
            <span style="margin-left:auto;font-size:.68rem;color:#333"><?= $cat['product_count'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="<?= APP_URL ?>/products/laptop" class="nl"><i class="fa-solid fa-laptop"></i> Laptop</a>
      <a href="<?= APP_URL ?>/products/may-tinh-pc" class="nl"><i class="fa-solid fa-desktop"></i> PC Gaming</a>
    </div>

    <div class="nav-right">
      <button class="ico-btn" onclick="toggleSBar()" title="Tìm kiếm"><i class="fa-solid fa-magnifying-glass"></i></button>
      <a href="<?= APP_URL ?>/cart" class="ico-btn" title="Giỏ hàng">
        <i class="fa-solid fa-cart-shopping"></i>
        <?php $cc=getCartCount(); ?>
        <span class="cart-dot" id="cart-badge" style="<?= $cc<1?'display:none':'' ?>"><?= $cc ?></span>
      </a>
      <?php if(isLoggedIn()): ?>
      <div class="u-dd">
        <button class="u-btn">
          <div class="u-av"><?= strtoupper(mb_substr($_SESSION['user_name']??'U',0,1)) ?></div>
          <span class="u-name" style="max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars(mb_substr($_SESSION['user_name']??'',0,12)) ?></span>
          <i class="fa-solid fa-chevron-down" style="font-size:.58rem;opacity:.5"></i>
        </button>
        <div class="u-panel">
          <?php if(isStaff()): ?>
          <a href="<?= APP_URL ?>/admin" class="u-item"><i class="fa-solid fa-gauge-high"></i>Admin Panel</a>
          <div class="dd-sep"></div>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/account" class="u-item"><i class="fa-solid fa-circle-user"></i>Tài khoản</a>
          <a href="<?= APP_URL ?>/account/orders" class="u-item"><i class="fa-solid fa-box-open"></i>Đơn hàng</a>
          <div class="dd-sep"></div>
          <a href="<?= APP_URL ?>/auth/logout" class="u-item red"><i class="fa-solid fa-arrow-right-from-bracket"></i>Đăng xuất</a>
        </div>
      </div>
      <?php else: ?>
      <a href="<?= APP_URL ?>/auth/login" class="btn-login"><i class="fa-solid fa-right-to-bracket"></i>Đăng nhập</a>
      <?php endif; ?>
      <button class="hamburger" onclick="toggleMob()" id="ham"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>

  <!-- Search expand -->
  <div class="s-bar" id="s-bar">
    <form action="<?= APP_URL ?>/search" method="GET" style="max-width:600px;margin:0 auto;position:relative">
      <input class="s-inp" type="text" name="q" value="<?= htmlspecialchars(isset($_GET['q'])?$_GET['q']:'') ?>" placeholder="Tìm kiếm sản phẩm..." autofocus>
      <button type="submit" class="s-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
  </div>

  <!-- Mobile menu -->
  <div class="mob-menu" id="mob-menu">
    <form action="<?= APP_URL ?>/search" method="GET" style="margin-bottom:.7rem;position:relative">
      <input class="s-inp" type="text" name="q" placeholder="Tìm kiếm...">
      <button type="submit" class="s-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <a href="<?= APP_URL ?>/" class="mob-link"><i class="fa-solid fa-house"></i>Trang chủ</a>
    <a href="<?= APP_URL ?>/products" class="mob-link"><i class="fa-solid fa-layer-group"></i>Sản phẩm</a>
    <?php foreach(array_slice($allCategories,0,6) as $c): ?>
    <a href="<?= APP_URL ?>/products/<?= $c['slug'] ?>" class="mob-link" style="padding-left:1.25rem"><i class="fa-solid fa-angle-right"></i><?= htmlspecialchars($c['name']) ?></a>
    <?php endforeach; ?>
    <a href="<?= APP_URL ?>/cart" class="mob-link"><i class="fa-solid fa-cart-shopping"></i>Giỏ hàng</a>
    <?php if(!isLoggedIn()): ?>
    <a href="<?= APP_URL ?>/auth/login" class="mob-link" style="color:var(--red)"><i class="fa-solid fa-right-to-bracket"></i>Đăng nhập</a>
    <?php endif; ?>
  </div>
</nav>

<?php $flash=getFlash(); if($flash): ?>
<div class="flash-wrap">
  <div class="flash-inner <?= $flash['type'] ?>">
    <i class="fa-solid <?= $flash['type']==='success'?'fa-circle-check':'fa-circle-xmark' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
  </div>
</div>
<?php endif; ?>

<!-- ── LOGIN MODAL ── -->
<div id="lm" style="display:none;position:fixed;inset:0;z-index:8000;align-items:center;justify-content:center">
  <div onclick="closeLM()" style="position:absolute;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px)"></div>
  <div style="position:relative;width:100%;max-width:400px;margin:1rem;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.25);animation:lmIn .25s ease">
    <div style="background:linear-gradient(135deg,#0d0d0d,#1a0000);padding:1.5rem 1.5rem 1rem">
      <button onclick="closeLM()" style="position:absolute;top:.9rem;right:.9rem;width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,.08);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem;transition:.2s" onmouseover="this.style.background='rgba(255,255,255,.16)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div style="display:flex;align-items:center;gap:9px">
        <div style="width:36px;height:36px;background:var(--red);border-radius:8px;display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-microchip" style="color:#fff;font-size:.85rem"></i></div>
        <div><div style="color:#fff;font-weight:800;font-size:.88rem">TUẤN HUY COMPUTER</div><div style="color:#555;font-size:.6rem;letter-spacing:1.5px">ĐĂNG NHẬP ĐỂ MUA SẮM</div></div>
      </div>
    </div>
    <div style="display:flex;border-bottom:1px solid #f0f0f0">
      <button id="lmt-l" onclick="switchLMTab('l')" style="flex:1;padding:.65rem;background:none;border:none;font-weight:700;font-size:.83rem;color:var(--red);border-bottom:2px solid var(--red);cursor:pointer;font-family:var(--font)"><i class="fa-solid fa-right-to-bracket" style="margin-right:5px"></i>Đăng nhập</button>
      <button id="lmt-r" onclick="switchLMTab('r')" style="flex:1;padding:.65rem;background:none;border:none;font-weight:600;font-size:.83rem;color:#999;border-bottom:2px solid transparent;cursor:pointer;font-family:var(--font)"><i class="fa-solid fa-user-plus" style="margin-right:5px"></i>Đăng ký</button>
    </div>
    <!-- Login form -->
    <div id="lm-l" style="padding:1.35rem">
      <div id="lm-err" style="display:none;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:.55rem .8rem;border-radius:8px;font-size:.8rem;margin-bottom:.85rem;align-items:center;gap:.4rem">
        <i class="fa-solid fa-circle-xmark"></i><span id="lm-err-txt"></span>
      </div>
      <div style="margin-bottom:.75rem">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.28rem">Email</label>
        <div style="position:relative">
          <i class="fa-solid fa-envelope" style="position:absolute;left:.72rem;top:50%;transform:translateY(-50%);color:#ccc;font-size:.75rem"></i>
          <input id="lm-email" type="email" placeholder="your@email.com" class="form-input" style="padding-left:2.1rem" onkeydown="if(event.key==='Enter')doLMLogin()">
        </div>
      </div>
      <div style="margin-bottom:1rem">
        <label style="display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.28rem">Mật khẩu</label>
        <div style="position:relative">
          <i class="fa-solid fa-lock" style="position:absolute;left:.72rem;top:50%;transform:translateY(-50%);color:#ccc;font-size:.75rem"></i>
          <input id="lm-pass" type="password" placeholder="••••••••" class="form-input" style="padding-left:2.1rem;padding-right:2.5rem" onkeydown="if(event.key==='Enter')doLMLogin()">
          <button type="button" onclick="var i=document.getElementById('lm-pass');i.type=i.type==='password'?'text':'password'" style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#ccc;cursor:pointer;font-size:.8rem"><i class="fa-regular fa-eye"></i></button>
        </div>
      </div>
      <button onclick="doLMLogin()" id="lm-btn" class="btn-red" style="width:100%;justify-content:center;padding:.62rem"><i class="fa-solid fa-right-to-bracket"></i>Đăng nhập</button>
      <div style="text-align:center;margin-top:.75rem;font-size:.76rem;color:#999">Hoặc <a href="<?= APP_URL ?>/auth/login" style="color:var(--red);font-weight:600">đăng nhập trang đầy đủ</a></div>
    </div>
    <!-- Register form -->
    <div id="lm-r" style="padding:1.35rem;display:none">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-bottom:.6rem">
        <div><label style="display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.28rem">Họ và tên *</label><input id="rg-name" type="text" placeholder="Nguyễn A" class="form-input" style="font-size:.82rem"></div>
        <div><label style="display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.28rem">SĐT</label><input id="rg-phone" type="tel" placeholder="0909..." class="form-input" style="font-size:.82rem"></div>
      </div>
      <div style="margin-bottom:.6rem"><label style="display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.28rem">Email *</label><input id="rg-email" type="email" placeholder="your@email.com" class="form-input" style="font-size:.82rem"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-bottom:.9rem">
        <div><label style="display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.28rem">Mật khẩu *</label><input id="rg-pass" type="password" placeholder="≥6 ký tự" class="form-input" style="font-size:.82rem"></div>
        <div><label style="display:block;font-size:.75rem;font-weight:600;color:#444;margin-bottom:.28rem">Xác nhận *</label><input id="rg-cfm" type="password" placeholder="Nhập lại" class="form-input" style="font-size:.82rem"></div>
      </div>
      <div id="rg-err" style="display:none;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:.55rem .8rem;border-radius:8px;font-size:.8rem;margin-bottom:.7rem"></div>
      <button onclick="doLMRegister()" id="rg-btn" class="btn-red" style="width:100%;justify-content:center;padding:.62rem"><i class="fa-solid fa-user-plus"></i>Tạo tài khoản</button>
    </div>
  </div>
</div>
<style>@keyframes lmIn{from{opacity:0;transform:scale(.96) translateY(8px)}to{opacity:1;transform:none}}</style>

<script>
// ── Loader
window.addEventListener('load',function(){
  setTimeout(function(){var l=document.getElementById('pg-ld');if(l){l.classList.add('out');setTimeout(function(){l.remove()},500);}},350);
});

// ── UI toggles
function toggleSBar(){var b=document.getElementById('s-bar');var on=b.style.display==='block';b.style.display=on?'none':'block';if(!on)b.querySelector('input').focus();}
function toggleMob(){
  var m=document.getElementById('mob-menu');m.classList.toggle('open');
  var h=document.getElementById('ham');h.querySelector('i').className=m.classList.contains('open')?'fa-solid fa-xmark':'fa-solid fa-bars';
}

// ── Toast
function showToast(msg,type){
  type=type||'success';
  var w=document.getElementById('toast-wrap');
  var t=document.createElement('div');t.className='toast '+type;
  t.innerHTML='<div class="t-ico"><i class="fa-solid '+(type==='success'?'fa-circle-check':'fa-circle-xmark')+'"></i></div><span>'+msg+'</span>';
  w.appendChild(t);
  setTimeout(function(){t.classList.add('out');setTimeout(function(){t.remove()},300)},3500);
}

// ── Cart badge
function updateCartBadge(n){var b=document.getElementById('cart-badge');if(!b)return;if(n>0){b.style.display='flex';b.textContent=n>99?'99+':n;}else b.style.display='none';}
function fmtPrice(n){return new Intl.NumberFormat('vi-VN').format(n)+'đ';}

// ── addToCart AJAX
function addToCart(id,qty,btn){
  qty=qty||1;
  if(btn){btn.disabled=true;var orig=btn.innerHTML;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i>';}
  fetch('<?= APP_URL ?>/api/cart/add',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id:id,quantity:qty})})
  .then(function(r){return r.json();}).then(function(d){
    if(d.success){showToast('Đã thêm vào giỏ hàng!','success');updateCartBadge(d.cart_count);}
    else showToast(d.message||'Có lỗi xảy ra!','error');
  }).catch(function(){showToast('Lỗi kết nối!','error');})
  .finally(function(){if(btn){btn.disabled=false;btn.innerHTML=orig||'<i class="fa-solid fa-cart-plus"></i> Thêm giỏ';}});
}

// ── Login Modal
var _pcId=null,_pcQty=1;
function openLM(id,qty){_pcId=id||null;_pcQty=qty||1;var m=document.getElementById('lm');m.style.display='flex';document.body.style.overflow='hidden';setTimeout(function(){document.getElementById('lm-email').focus();},250);}
function closeLM(){document.getElementById('lm').style.display='none';document.body.style.overflow='';_pcId=null;}
function switchLMTab(t){
  var isL=t==='l';
  document.getElementById('lm-l').style.display=isL?'block':'none';
  document.getElementById('lm-r').style.display=isL?'none':'block';
  ['l','r'].forEach(function(x){
    var b=document.getElementById('lmt-'+x);var on=x===t;
    b.style.color=on?'var(--red)':'#999';b.style.borderBottomColor=on?'var(--red)':'transparent';b.style.fontWeight=on?'700':'600';
  });
}
function showLMErr(msg){var el=document.getElementById('lm-err');document.getElementById('lm-err-txt').textContent=msg;el.style.display='flex';}
function doLMLogin(){
  var em=document.getElementById('lm-email').value.trim(),pw=document.getElementById('lm-pass').value;
  if(!em||!pw){showLMErr('Vui lòng nhập đầy đủ');return;}
  var btn=document.getElementById('lm-btn');btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
  document.getElementById('lm-err').style.display='none';
  fetch('<?= APP_URL ?>/api/auth/login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:em,password:pw})})
  .then(function(r){return r.json();}).then(function(d){
    if(d.success){showToast('Xin chào '+d.name+'!','success');closeLM();setTimeout(function(){window.location.reload();},700);if(_pcId){setTimeout(function(){addToCart(_pcId,_pcQty);},1100);}}
    else{showLMErr(d.message||'Email hoặc mật khẩu không đúng');btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-right-to-bracket"></i>Đăng nhập';}
  }).catch(function(){showLMErr('Lỗi kết nối');btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-right-to-bracket"></i>Đăng nhập';});
}
function doLMRegister(){
  var n=document.getElementById('rg-name').value.trim(),ph=document.getElementById('rg-phone').value.trim(),em=document.getElementById('rg-email').value.trim(),pw=document.getElementById('rg-pass').value,cf=document.getElementById('rg-cfm').value;
  var er=document.getElementById('rg-err');er.style.display='none';
  if(!n||!em||!pw){er.style.display='block';er.textContent='Vui lòng điền đầy đủ';return;}
  if(pw.length<6){er.style.display='block';er.textContent='Mật khẩu ít nhất 6 ký tự';return;}
  if(pw!==cf){er.style.display='block';er.textContent='Mật khẩu không khớp';return;}
  var btn=document.getElementById('rg-btn');btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
  fetch('<?= APP_URL ?>/api/auth/register',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({fullname:n,phone:ph,email:em,password:pw})})
  .then(function(r){return r.json();}).then(function(d){
    if(d.success){showToast('Đăng ký thành công! Chào '+n,'success');closeLM();setTimeout(function(){window.location.reload();},900);}
    else{er.style.display='block';er.textContent=d.message||'Có lỗi xảy ra';btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-user-plus"></i>Tạo tài khoản';}
  }).catch(function(){er.style.display='block';er.textContent='Lỗi kết nối';btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-user-plus"></i>Tạo tài khoản';});
}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeLM();});

// ── Guard functions
function guardedAddToCart(id,qty,btn){
  <?php if(isLoggedIn()): ?>addToCart(id,qty,btn);<?php else: ?>openLM(id,qty);<?php endif; ?>
}
function guardedBuyNow(id){
  <?php if(isLoggedIn()): ?>window.location.href='<?= APP_URL ?>/checkout/buynow/'+id;<?php else: ?>openLM(id,1);<?php endif; ?>
}

// ── Scroll reveal
(function(){var els=document.querySelectorAll('.reveal');if(!els.length)return;var obs=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting)e.target.classList.add('visible');});},{threshold:.08});els.forEach(function(el){obs.observe(el);});})();
</script>
