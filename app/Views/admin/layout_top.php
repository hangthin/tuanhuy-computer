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
/* ── Notification bell ── */
#notif-wrap{position:relative}
#notif-btn{background:none;border:1px solid #252525;border-radius:8px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#666;transition:.15s;position:relative}
#notif-btn:hover{border-color:#444;color:#ccc;background:#1a1a1a}
#notif-btn.has-new{border-color:#ef444466;color:#ef4444;animation:bellRing .5s ease}
@keyframes bellRing{0%,100%{transform:rotate(0)}20%{transform:rotate(-15deg)}40%{transform:rotate(15deg)}60%{transform:rotate(-10deg)}80%{transform:rotate(10deg)}}
#notif-badge{position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;font-size:.55rem;font-weight:800;min-width:16px;height:16px;border-radius:99px;display:none;align-items:center;justify-content:center;border:1.5px solid #0f0f0f;padding:0 3px;line-height:1}
#notif-drop{position:absolute;top:calc(100% + 8px);right:0;width:320px;background:#141414;border:1px solid #252525;border-radius:12px;box-shadow:0 16px 48px rgba(0,0,0,.7);z-index:9990;overflow:hidden;animation:fadeIn .18s ease}
#notif-drop-hd{padding:.6rem .85rem;border-bottom:1px solid #1e1e1e;display:flex;align-items:center;justify-content:space-between}
#notif-list a:last-child{border-bottom:none!important}
#notif-footer{padding:.45rem .85rem;border-top:1px solid #1e1e1e;text-align:center}
@keyframes spin{to{transform:rotate(360deg)}}
.notif-item{display:block;padding:.62rem .85rem;text-decoration:none;border-bottom:1px solid #1e1e1e;transition:background .15s;animation:fadeIn .28s ease}
.notif-item:hover{background:#1a1a1a}
.notif-item.is-new{animation:fadeIn .4s ease;background:rgba(239,68,68,.06);border-left:2px solid #ef444466}
</style>
<style media="print">
  .sidebar,.top-nav,#pg-loader,#toast-c,.btn-r,.btn-g,.btn-export-group,
  .ord-tabs,.ord-filter,.ord-stats,.prd-toolbar,.prd-vtabs,.prd-cats,
  .prd-pages,.pagination,.filter-bar,.notif-wrap,#notif-wrap,
  [onclick],[href*="create"],[href*="edit"],[href*="delete"]{display:none!important}
  .main-wrap{margin-left:0!important;padding:.5rem!important}
  body{background:#fff!important;color:#111!important}
  .card,.stat-card,.prd-wrap{background:#fff!important;border:1px solid #ccc!important;box-shadow:none!important}
  .adm-table th{background:#111!important;color:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .adm-table td{color:#111!important;border-bottom:1px solid #ddd!important}
  .adm-table tr:nth-child(even) td{background:#f9f9f9!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  table{page-break-inside:auto}
  tr{page-break-inside:avoid;page-break-after:auto}
  thead{display:table-header-group}
  @page{size:A4 landscape;margin:1.2cm}
  h1,h2,h3{color:#111!important}
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
      array('/admin/ai/generator','fa-solid fa-wand-magic-sparkles','AI Generator'),
    );
    // Thêm AI Assistant + Telegram Bot (Admin only) — AI Report chạy ngầm qua cron/Telegram
    if(isAdmin()) $navs[] = array('/admin/ai-assistant','fa-solid fa-robot','AI Assistant');
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
    <a href="<?= APP_URL ?>/admin/assets" class="nav-item <?= strpos($curUri,'/admin/assets')!==false?'active':'' ?>"><i class="fa-solid fa-photo-film" style="width:15px;text-align:center"></i>Asset Manager</a>
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
    <div style="display:flex;align-items:center;gap:.55rem">
      <!-- Notification bell -->
      <div id="notif-wrap">
        <button id="notif-btn" title="Đơn hàng mới">
          <i class="fas fa-bell" style="font-size:.82rem"></i>
          <span id="notif-badge"></span>
        </button>
        <div id="notif-drop" style="display:none">
          <div id="notif-drop-hd">
            <span style="font-size:.78rem;font-weight:700;color:#ddd"><i class="fas fa-bell" style="color:var(--red);margin-right:.35rem"></i>Đơn hàng mới</span>
            <a href="<?= APP_URL ?>/admin/orders" style="font-size:.68rem;color:var(--red);text-decoration:none">Xem tất cả</a>
          </div>
          <div id="notif-list"></div>
          <div id="notif-footer">
            <button id="notif-clear" style="background:none;border:none;color:#444;font-size:.68rem;cursor:pointer;font-family:inherit">Đánh dấu đã đọc</button>
          </div>
        </div>
      </div>
      <a href="<?= APP_URL ?>/admin/products/create" class="btn-r" style="text-decoration:none;font-size:.78rem;padding:.4rem .8rem"><i class="fas fa-plus mr-1"></i>Thêm SP</a>
    </div>
  </div>

<script>
(function(){
  var APP_URL   = '<?= APP_URL ?>';
  var UPLOAD_URL = '<?= UPLOAD_URL ?>';
  var LS_KEY    = 'th_notif_since';
  var INTERVAL  = 30000;
  var btn       = document.getElementById('notif-btn');
  var badge     = document.getElementById('notif-badge');
  var drop      = document.getElementById('notif-drop');
  var list      = document.getElementById('notif-list');
  var clearBtn  = document.getElementById('notif-clear');
  var prevIds    = [];
  var firstPoll  = true;

  function getSince(){ var v=localStorage.getItem(LS_KEY); return v ? parseInt(v) : Math.floor(Date.now()/1000)-3600; }
  function setSince(ts){ localStorage.setItem(LS_KEY, ts); }
  function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
  function fmtVND(n){ return parseInt(n).toLocaleString('vi-VN')+'đ'; }
  function timeAgo(dt){
    var d=Math.floor((Date.now()-new Date(dt).getTime())/1000);
    if(d<60) return d+' giây trước';
    if(d<3600) return Math.floor(d/60)+' phút trước';
    return Math.floor(d/3600)+' giờ trước';
  }

  function renderList(orders,newIds){
    list.innerHTML='';
    if(!orders||!orders.length){
      list.innerHTML='<div style="padding:.85rem 1rem;color:#444;font-size:.75rem;text-align:center">Không có đơn hàng mới</div>';
      return;
    }
    orders.forEach(function(o){
      var a=document.createElement('a');
      a.href=APP_URL+'/admin/orders/detail?id='+o.id;
      a.className='notif-item'+(newIds&&newIds.indexOf(o.id)!==-1?' is-new':'');
      var img=o.product_image&&o.product_image!=='default.jpg'
        ? '<img src="'+UPLOAD_URL+esc(o.product_image)+'" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #222;flex-shrink:0" onerror="this.style.display=\'none\'">'
        : '<div style="width:40px;height:40px;background:#1a1a1a;border-radius:6px;border:1px solid #222;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-microchip" style="color:#333;font-size:.75rem"></i></div>';
      a.innerHTML=
        '<div style="display:flex;gap:.6rem;align-items:center">'+img+
        '<div style="flex:1;min-width:0">'+
        '<div style="display:flex;justify-content:space-between;align-items:baseline;gap:.2rem">'+
        '<span style="color:#ddd;font-weight:600;font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:138px">'+esc(o.product_name||'—')+'</span>'+
        '<span style="color:#444;font-size:.62rem;flex-shrink:0">'+timeAgo(o.created_at)+'</span></div>'+
        '<div style="color:#777;font-size:.71rem;margin-top:.1rem">'+esc(o.fullname)+'</div>'+
        '<div style="display:flex;justify-content:space-between;margin-top:.15rem">'+
        '<span style="color:#555;font-size:.68rem">x'+(o.quantity||1)+' · '+fmtVND(o.price||0)+'</span>'+
        '<span style="color:#4ade80;font-size:.72rem;font-weight:700">'+fmtVND(o.total)+'</span>'+
        '</div></div></div>';
      list.appendChild(a);
    });
  }

  function showBadge(n){
    badge.textContent=n>9?'9+':n;
    badge.style.display='flex';
    btn.classList.add('has-new');
  }
  function hideBadge(){
    badge.style.display='none';
    btn.classList.remove('has-new');
  }

  function browserNotify(o){
    if(!('Notification' in window)) return;
    if(Notification.permission==='granted'){
      new Notification('🛍️ Đơn hàng mới',{
        body: (o.product_name||o.order_code||'')+'  —  '+o.fullname+' ('+fmtVND(o.total)+')',
        icon: APP_URL+'/assets/images/hero-banner.jpg'
      });
    }
  }

  function poll(){
    var since=getSince();
    fetch(APP_URL+'/admin/api/new-orders-count?since='+since)
      .then(function(r){return r.json();})
      .then(function(d){
        if(!d.success) return;
        var orders=d.orders||[];
        var ids=orders.map(function(o){return o.id;});
        var newIds=firstPoll?[]:ids.filter(function(id){return prevIds.indexOf(id)===-1;});
        firstPoll=false;
        if(d.count>0) showBadge(d.count); else hideBadge();
        renderList(orders,newIds);
        if(newIds.length>0){
          var notifOrder=orders.filter(function(o){return o.id===newIds[0];})[0]||orders[0];
          if(notifOrder) browserNotify(notifOrder);
        }
        prevIds=ids;
      })
      .catch(function(e){ console.error('[notif poll]',e); });
  }

  btn.addEventListener('click',function(e){
    e.stopPropagation();
    var open=drop.style.display!=='none';
    drop.style.display=open?'none':'block';
  });
  document.addEventListener('click',function(){ drop.style.display='none'; });
  drop.addEventListener('click',function(e){ e.stopPropagation(); });

  clearBtn.addEventListener('click',function(){
    setSince(Math.floor(Date.now()/1000));
    prevIds=[];
    hideBadge();
    renderList([]);
    drop.style.display='none';
  });

  if('Notification' in window && Notification.permission==='default'){
    Notification.requestPermission();
  }

  renderList([]);
  poll();
  setInterval(poll,INTERVAL);
})();
</script>
