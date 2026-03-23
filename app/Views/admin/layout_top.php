<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= isset($pageTitle)?$pageTitle:'Admin' ?> | TH Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root{--red:#E30000}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Be Vietnam Pro',sans-serif;background:#0f0f0f;color:#ddd}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
@keyframes loadBar{to{width:100%}}
#pg-loader{position:fixed;inset:0;background:#0f0f0f;display:flex;align-items:center;justify-content:center;z-index:9999;transition:opacity .5s}
#pg-loader.hidden{opacity:0;pointer-events:none}
.sidebar{width:220px;background:#111;height:100vh;position:fixed;left:0;top:0;overflow-y:auto;border-right:1px solid #1e1e1e;z-index:100}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:#333}
.main-wrap{margin-left:220px;min-height:100vh;padding:1.35rem}
.nav-item{display:flex;align-items:center;gap:.55rem;padding:.55rem .9rem;color:#777;text-decoration:none;font-size:.82rem;font-weight:500;border-radius:7px;margin:1px .45rem;transition:all .2s}
.nav-item:hover,.nav-item.active{background:rgba(227,0,0,.15);color:var(--red)}
.stat-card{background:#1a1a1a;border:1px solid #222;border-radius:12px;padding:1.1rem;animation:fadeIn .4s}
.stat-card:hover{border-color:#333}
.adm-table{width:100%;border-collapse:collapse;font-size:.82rem}
.adm-table th{background:#1a1a1a;color:#666;font-weight:600;padding:.6rem .9rem;text-align:left;font-size:.75rem;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid #222}
.adm-table td{padding:.6rem .9rem;border-bottom:1px solid #1e1e1e;color:#bbb;vertical-align:middle}
.adm-table tr:hover td{background:rgba(255,255,255,.015)}
.form-inp{width:100%;padding:.5rem .8rem;background:#1a1a1a;border:1.5px solid #2a2a2a;border-radius:7px;color:#ddd;font-size:.82rem;outline:none;transition:border-color .2s;font-family:inherit}
.form-inp:focus{border-color:var(--red)}
.form-inp option{background:#1a1a1a}
.btn-r{background:var(--red);color:#fff;border:none;padding:.48rem 1.1rem;border-radius:7px;font-weight:600;cursor:pointer;font-size:.82rem;transition:background .2s;font-family:inherit}
.btn-r:hover{background:#b00}
.btn-g{background:transparent;color:#888;border:1px solid #333;padding:.45rem .9rem;border-radius:7px;font-size:.8rem;cursor:pointer;font-family:inherit;transition:all .2s}
.btn-g:hover{background:#1a1a1a;color:#ccc}
.card{background:#1a1a1a;border:1px solid #222;border-radius:12px;animation:fadeIn .4s}
.badge{padding:2px 9px;border-radius:99px;font-size:.7rem;font-weight:600}
.bdg-pending{background:rgba(234,179,8,.15);color:#fbbf24}
.bdg-confirmed{background:rgba(59,130,246,.15);color:#60a5fa}
.bdg-processing{background:rgba(249,115,22,.15);color:#fb923c}
.bdg-shipping{background:rgba(14,165,233,.15);color:#38bdf8}
.bdg-delivered{background:rgba(34,197,94,.15);color:#4ade80}
.bdg-cancelled{background:rgba(239,68,68,.15);color:#f87171}
#toast-c{position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.45rem}
.toast{background:#1e1e1e;color:#fff;padding:.65rem 1.1rem;border-radius:8px;border-left:4px solid var(--red);min-width:220px;font-size:.82rem;animation:fadeIn .3s}
.toast.ok{border-color:#22c55e}.toast.err{border-color:#ef4444}
::-webkit-scrollbar{width:4px;height:4px}::-webkit-scrollbar-track{background:#111}::-webkit-scrollbar-thumb{background:#333;border-radius:2px}
</style>
</head>
<body>
<div id="pg-loader"><div style="text-align:center"><div style="color:#fff;font-size:1.1rem;font-weight:900">TH <span style="color:var(--red)">ADMIN</span></div><div style="width:100px;height:2px;background:#222;border-radius:99px;margin:.6rem auto;overflow:hidden"><div style="width:0;height:100%;background:var(--red);animation:loadBar .7s ease forwards"></div></div></div></div>
<div id="toast-c"></div>
<!-- SIDEBAR -->
<aside class="sidebar">
  <div style="padding:1.1rem .9rem;border-bottom:1px solid #1e1e1e">
    <div style="display:flex;align-items:center;gap:.55rem">
      <div style="width:34px;height:34px;background:var(--red);border-radius:7px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:.85rem;flex-shrink:0">TH</div>
      <div><div style="color:#fff;font-weight:800;font-size:.85rem">TUẤN HUY</div><div style="color:var(--red);font-size:.58rem;letter-spacing:2px">ADMIN PANEL</div></div>
    </div>
  </div>
  <div style="padding:.6rem .9rem;border-bottom:1px solid #1e1e1e;display:flex;align-items:center;gap:.55rem">
    <div style="width:30px;height:30px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.78rem;flex-shrink:0"><?= strtoupper(mb_substr($_SESSION['user_name']??'A',0,1)) ?></div>
    <?php
    $roleNum   = (int)($_SESSION['user_role'] ?? 1);
    $roleLabel = $roleNum===1 ? 'Admin' : ($roleNum===2 ? 'Manager' : ($roleNum===3 ? 'Staff' : 'User'));
    $roleColor = $roleNum===1 ? 'var(--red)' : ($roleNum===2 ? '#a78bfa' : '#60a5fa');
    ?>
    <div><div style="font-size:.78rem;font-weight:600;color:#ccc;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px"><?= htmlspecialchars($_SESSION['user_name']??'Admin') ?></div><div style="font-size:.65rem;color:<?= $roleColor ?>"><?= $roleLabel ?></div></div>
  </div>
  <nav style="padding:.4rem 0">
    <div style="padding:.4rem .9rem .15rem;font-size:.62rem;color:#444;text-transform:uppercase;letter-spacing:1px;font-weight:700">Tổng quan</div>
    <?php
    $curUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $navs = array(
      array('/admin','fas fa-tachometer-alt','Dashboard'),
      array('/admin/stats','fas fa-chart-line','Thống kê'),
      array('/admin/ai/generator','fa-solid fa-wand-magic-sparkles','AI Generator ✨'),
    );
    // Thêm AI Report + Telegram Bot (Admin only)
    if(isAdmin()) $navs[] = array('/admin/ai-report','fa-solid fa-brain','AI Report 🧠');
    if(isAdmin()) $navs[] = array('/admin/telegram-bot','fa-brands fa-telegram','Telegram Bot');
    foreach($navs as $n): $np=$n[0];$ni=$n[1];$nl=$n[2];
      $active = (rtrim(str_replace(APP_URL,'',$curUri),'/')===$np)?'active':'';
    ?>
    <a href="<?= APP_URL.$np ?>" class="nav-item <?= $active ?>"><i class="<?= $ni ?>" style="width:15px;text-align:center"></i><?= $nl ?></a>
    <?php endforeach; ?>
    <div style="padding:.5rem .9rem .15rem;font-size:.62rem;color:#444;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-top:.4rem">Quản lý</div>
    <?php
    $navs2 = array(
      array('/admin/products','fas fa-box','Sản phẩm'),
      array('/admin/categories','fas fa-tag','Danh mục'),
      array('/admin/customers','fas fa-users','Khách hàng'),
      array('/admin/orders','fas fa-shopping-bag','Đơn hàng'),
      array('/admin/inventory','fas fa-warehouse','Kho hàng'),
    );
    foreach($navs2 as $n): $np=$n[0];$ni=$n[1];$nl=$n[2];
      $active = (strpos($curUri,$np)!==false)?'active':'';
    ?>
    <a href="<?= APP_URL.$np ?>" class="nav-item <?= $active ?>"><i class="<?= $ni ?>" style="width:15px;text-align:center"></i><?= $nl ?></a>
    <?php endforeach; ?>
    <?php if(isAdmin()): ?>
    <a href="<?= APP_URL ?>/admin/staff" class="nav-item <?= strpos($curUri,'/admin/staff')!==false?'active':'' ?>"><i class="fas fa-user-tie" style="width:15px;text-align:center"></i>Nhân sự</a>
    <?php endif; ?>
    <div style="padding:.5rem .9rem .15rem;font-size:.62rem;color:#444;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-top:.4rem">Hệ thống</div>
    <?php if(isAdmin()): ?>
    <a href="<?= APP_URL ?>/admin/logs" class="nav-item <?= strpos($curUri,'/admin/logs')!==false?'active':'' ?>"><i class="fa-solid fa-clock-rotate-left" style="width:15px;text-align:center"></i>Nhật ký</a>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/" target="_blank" class="nav-item"><i class="fas fa-external-link-alt" style="width:15px;text-align:center"></i>Xem website</a>
    <a href="<?= APP_URL ?>/auth/logout" class="nav-item" style="color:#ef4444" onclick="return confirm('Đăng xuất?')"><i class="fas fa-sign-out-alt" style="width:15px;text-align:center"></i>Đăng xuất</a>
  </nav>
</aside>
<!-- MAIN -->
<div class="main-wrap">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.65rem">
    <div>
      <h1 style="font-size:1.15rem;font-weight:800;color:#fff"><?= isset($pageTitle)?$pageTitle:'Admin' ?></h1>
      <p style="color:#444;font-size:.75rem;margin-top:.1rem"><?= date('l, d/m/Y H:i') ?></p>
    </div>
    <a href="<?= APP_URL ?>/admin/products/create" class="btn-r" style="text-decoration:none;font-size:.78rem;padding:.4rem .8rem"><i class="fas fa-plus mr-1"></i>Thêm SP</a>
  </div>
