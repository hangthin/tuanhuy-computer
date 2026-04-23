<?php include __DIR__.'/../layouts/header.php'; ?>
<style>
/* ── PC Builder ────────────────────────────────────────────── */
.pcb-page{
  display:flex;flex-direction:column;
  height:calc(100vh - 92px);
  background:#0f0f0f;color:#e0e0e0;
  overflow:hidden;
}
.pcb-topbar{
  display:flex;align-items:center;gap:1rem;
  padding:.6rem 1.2rem;background:#141414;
  border-bottom:1px solid #1e1e1e;flex-shrink:0;
}
.pcb-topbar h1{font-size:1rem;font-weight:800;color:#fff;letter-spacing:-.3px}
.pcb-topbar h1 em{color:#e30000;font-style:normal}
.pcb-topbar-sub{font-size:.75rem;color:#555;margin-left:auto}
/* Mobile tabs */
.pcb-mtabs{display:none;background:#141414;border-bottom:1px solid #1e1e1e;flex-shrink:0}
.pcbt{flex:1;padding:.6rem;background:none;border:none;color:#777;font-size:.8rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;font-family:inherit;transition:.15s}
.pcbt.active{color:#e30000;border-bottom-color:#e30000}
/* Body */
.pcb-body{display:flex;flex:1;overflow:hidden;min-height:0}
/* Left panel */
.pcb-left{
  width:360px;flex-shrink:0;
  background:#141414;border-right:1px solid #1e1e1e;
  display:flex;flex-direction:column;overflow:hidden;
}
.pcb-lhead{padding:.9rem 1rem .6rem;border-bottom:1px solid #1a1a1a;flex-shrink:0}
.pcb-lhead h2{font-size:.82rem;font-weight:700;color:#888;letter-spacing:1px;text-transform:uppercase;margin-bottom:.6rem}
.pcb-prog{display:flex;align-items:center;gap:.6rem}
.pcb-prog-bar{flex:1;height:4px;background:#1e1e1e;border-radius:99px;overflow:hidden}
.pcb-prog-fill{height:100%;background:#e30000;border-radius:99px;transition:width .4s ease}
.pcb-prog-txt{font-size:.72rem;color:#555;white-space:nowrap}
/* Slot list */
.pcb-slots{flex:1;overflow-y:auto;padding:.5rem}
.pcb-slot{
  display:flex;align-items:center;gap:.65rem;
  padding:.6rem .65rem;border-radius:9px;margin-bottom:4px;
  border:1px solid #1e1e1e;background:#0f0f0f;
  cursor:pointer;transition:border-color .15s,background .15s;
  min-height:58px;
}
.pcb-slot:hover{border-color:#2a2a2a;background:#161616}
.pcb-slot.active{border-color:#e30000;background:#1a0000}
.pcb-slot.has-item{border-color:#1c1c1c}
.slot-ico{
  width:34px;height:34px;background:#1a1a1a;border-radius:7px;
  display:flex;align-items:center;justify-content:center;
  color:#e30000;font-size:.8rem;flex-shrink:0;
}
.pcb-slot.active .slot-ico{background:#2a0000}
.slot-content{flex:1;min-width:0}
.slot-label{font-size:.72rem;color:#666;font-weight:600;letter-spacing:.5px;text-transform:uppercase}
.slot-empty{font-size:.78rem;color:#333;margin-top:1px}
.slot-sel{display:flex;align-items:center;gap:.5rem;margin-top:2px}
.slot-sel img{width:32px;height:32px;object-fit:contain;background:#1a1a1a;border-radius:5px;flex-shrink:0}
.slot-sel-info{min-width:0}
.slot-sel-name{font-size:.74rem;color:#ccc;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.slot-sel-price{font-size:.7rem;color:#e30000;font-weight:700}
.slot-meta{display:flex;align-items:center;gap:.4rem;flex-shrink:0}
.compat-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.compat-dot.ok{background:#22c55e}
.compat-dot.warn{background:#f59e0b}
.compat-dot.bad{background:#ef4444;animation:pulse .8s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.btn-choose{
  background:#e30000;color:#fff;border:none;border-radius:6px;
  padding:.25rem .55rem;font-size:.7rem;font-weight:700;cursor:pointer;
  white-space:nowrap;transition:.15s;
}
.btn-choose:hover{background:#b80000}
.btn-rm{
  background:none;border:1px solid #2a2a2a;color:#555;border-radius:6px;
  width:24px;height:24px;cursor:pointer;display:flex;align-items:center;justify-content:center;
  font-size:.68rem;transition:.15s;flex-shrink:0;
}
.btn-rm:hover{border-color:#ef4444;color:#ef4444;background:rgba(239,68,68,.1)}
/* Compat warnings panel */
.pcb-compat{padding:.6rem .8rem;flex-shrink:0;border-top:1px solid #1a1a1a}
.compat-warn{
  display:flex;align-items:flex-start;gap:.5rem;
  background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);
  border-radius:7px;padding:.5rem .65rem;margin-bottom:4px;
  font-size:.72rem;color:#f87171;line-height:1.4;
}
.compat-warn i{margin-top:1px;flex-shrink:0}
/* ── Right panel ──────────────────────────────────────── */
.pcb-right{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0}
.pcb-tabs-wrap{
  background:#141414;border-bottom:1px solid #1e1e1e;
  overflow-x:auto;flex-shrink:0;
}
.pcb-tabs{display:flex;padding:.4rem .6rem 0;gap:2px;min-width:max-content}
.pcb-tab{
  display:flex;align-items:center;gap:.35rem;
  padding:.4rem .75rem;background:none;border:none;
  color:#666;font-size:.76rem;font-weight:600;cursor:pointer;
  border-bottom:2px solid transparent;border-radius:7px 7px 0 0;
  transition:.15s;white-space:nowrap;font-family:inherit;position:relative;
}
.pcb-tab:hover{color:#ccc;background:rgba(255,255,255,.04)}
.pcb-tab.active{color:#fff;border-bottom-color:#e30000;background:rgba(227,0,0,.08)}
.pcb-tab .tab-ck{
  width:14px;height:14px;background:#22c55e;border-radius:50%;
  display:flex;align-items:center;justify-content:center;font-size:.55rem;color:#fff;
  position:absolute;top:4px;right:4px;
}
/* Filters */
.pcb-filters{
  display:flex;align-items:center;gap:.5rem;
  padding:.6rem .8rem;background:#0f0f0f;
  border-bottom:1px solid #1a1a1a;flex-shrink:0;flex-wrap:wrap;
}
.pcb-filters input,.pcb-filters select{
  background:#141414;border:1px solid #222;color:#ccc;
  border-radius:7px;padding:.35rem .65rem;font-size:.78rem;
  outline:none;font-family:inherit;transition:.15s;
}
.pcb-filters input:focus,.pcb-filters select:focus{border-color:#e30000}
#pb-search{flex:1;min-width:140px}
.pb-price-wrap{display:flex;align-items:center;gap:.4rem}
.pb-price-wrap input{width:90px}
.pb-price-wrap span{font-size:.7rem;color:#444}
/* Product grid */
.pcb-scroll{flex:1;overflow-y:auto;padding:.8rem}
.pcb-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
  gap:.65rem;
}
.pcb-empty{
  grid-column:1/-1;text-align:center;padding:4rem 1rem;color:#333;
}
.pcb-empty i{font-size:2.5rem;display:block;margin-bottom:.8rem}
/* Product card */
.pcb-card{
  background:#141414;border:1.5px solid #1e1e1e;border-radius:10px;
  overflow:hidden;cursor:pointer;transition:border-color .15s,transform .15s;
  display:flex;flex-direction:column;
}
.pcb-card:hover{border-color:#333;transform:translateY(-2px)}
.pcb-card.selected{border-color:#e30000;background:#1a0000}
.pcb-card-img{
  aspect-ratio:4/3;background:#0a0a0a;
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;position:relative;
}
.pcb-card-img img{width:100%;height:100%;object-fit:contain;padding:.5rem}
.pcb-card-badge{
  position:absolute;top:6px;right:6px;
  width:22px;height:22px;background:#22c55e;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.65rem;color:#fff;
}
.pcb-card-body{padding:.5rem .65rem;flex:1}
.pcb-card-brand{font-size:.65rem;color:#444;font-weight:600;text-transform:uppercase;margin-bottom:2px}
.pcb-card-name{font-size:.78rem;color:#ccc;font-weight:600;line-height:1.35;margin-bottom:.3rem;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.pcb-card-price{color:#e30000;font-weight:800;font-size:.88rem}
.pcb-card-desc{font-size:.68rem;color:#444;margin-top:.25rem;line-height:1.4;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.pcb-card-footer{padding:.4rem .65rem .55rem}
.pcb-add-btn{
  width:100%;padding:.35rem;background:#e30000;color:#fff;border:none;
  border-radius:7px;font-size:.74rem;font-weight:700;cursor:pointer;
  font-family:inherit;transition:.15s;display:flex;align-items:center;justify-content:center;gap:.3rem;
}
.pcb-add-btn:hover{background:#b80000}
.pcb-add-btn.sel{background:#22c55e}
.pcb-add-btn.sel:hover{background:#16a34a}
/* Summary bar */
.pcb-bar{
  position:fixed;bottom:0;left:0;right:0;z-index:400;
  background:#141414;border-top:1px solid #2a2a2a;
  box-shadow:0 -4px 24px rgba(0,0,0,.5);
  display:flex;align-items:center;padding:.55rem 1rem;gap:.8rem;
  transition:transform .3s;
}
.pcb-bar.hidden{transform:translateY(100%)}
.pcb-bar-items{flex:1;display:flex;gap:.5rem;overflow-x:auto;min-width:0}
.bar-item{
  display:flex;align-items:center;gap:.35rem;flex-shrink:0;
  background:#1a1a1a;border:1px solid #222;border-radius:7px;
  padding:.3rem .45rem .3rem .35rem;
}
.bar-item img{width:30px;height:30px;object-fit:contain;background:#111;border-radius:5px}
.bar-item-name{font-size:.68rem;color:#bbb;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.bar-item-price{font-size:.68rem;color:#e30000;font-weight:700}
.bar-item-rm{background:none;border:none;color:#444;cursor:pointer;padding:0 0 0 .2rem;font-size:.7rem;transition:.15s}
.bar-item-rm:hover{color:#ef4444}
.pcb-bar-total{display:flex;align-items:center;gap:.6rem;flex-shrink:0}
.bar-total-lbl{font-size:.78rem;color:#666}
.bar-total-price{font-size:1rem;font-weight:800;color:#fff;white-space:nowrap}
.pcb-bar-btns{display:flex;gap:.4rem;flex-shrink:0}
.pcb-bar-btns button{padding:.4rem .75rem;border-radius:7px;font-size:.78rem;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;border:none}
.btn-cart-all{background:#e30000;color:#fff}
.btn-cart-all:hover{background:#b80000}
.btn-save-build,.btn-share-build{background:#1a1a1a;color:#aaa;border:1px solid #2a2a2a!important}
.btn-save-build:hover,.btn-share-build:hover{background:#222;color:#fff}
/* page bottom padding when bar visible */
body.bar-open .pcb-page{padding-bottom:0}
/* ── Auth modal ──────────────────────────────────────────────── */
.pcb-modal-ov{
  position:fixed;inset:0;z-index:900;
  background:rgba(0,0,0,.78);backdrop-filter:blur(6px);
  display:none;align-items:center;justify-content:center;
}
.pcb-modal-ov.open{display:flex}
.pcb-modal-box{
  background:#1a1a1a;border:1px solid #2a2a2a;border-radius:14px;
  padding:2rem 1.8rem;max-width:380px;width:90%;text-align:center;
  box-shadow:0 24px 60px rgba(0,0,0,.65);
}
.pcb-modal-icon{
  width:52px;height:52px;background:rgba(227,0,0,.15);border-radius:50%;
  display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;
  color:#e30000;font-size:1.3rem;
}
.pcb-modal-box h3{color:#fff;font-size:1.05rem;font-weight:800;margin-bottom:.5rem}
.pcb-modal-box p{color:#888;font-size:.83rem;line-height:1.5;margin-bottom:1.4rem}
.pcb-modal-btns{display:flex;flex-direction:column;gap:.6rem}
.btn-ml{
  display:block;padding:.65rem;border-radius:8px;
  font-weight:700;font-size:.88rem;text-decoration:none;
  transition:.15s;border:none;cursor:pointer;font-family:inherit;width:100%;
}
.btn-ml-login{background:#e30000;color:#fff}
.btn-ml-login:hover{background:#b80000}
.btn-ml-reg{background:#141414;color:#ccc;border:1px solid #333!important}
.btn-ml-reg:hover{background:#1e1e1e;color:#fff}
.btn-ml-guest{background:none;color:#555;font-size:.78rem;border:none!important;padding:.4rem}
.btn-ml-guest:hover{color:#888}
/* Responsive */
@media(max-width:900px){
  .pcb-left{width:300px}
  .pcb-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}
}
@media(max-width:680px){
  .pcb-mtabs{display:flex}
  .pcb-body{position:relative}
  .pcb-left{
    position:absolute;inset:0;z-index:2;width:100%;
    border-right:none;
  }
  .pcb-right{position:absolute;inset:0;z-index:2;width:100%}
  .pcb-left.mob-hidden,.pcb-right.mob-hidden{display:none}
  .pcb-bar{flex-wrap:wrap}
  .pcb-bar-items{width:100%;order:-1}
  .pcb-bar-btns{width:100%;justify-content:flex-end}
  .pb-price-wrap{display:none}
}
</style>

<div class="pcb-page" id="pcb-page">
  <div class="pcb-topbar">
    <div>
      <h1><em>Build</em> PC Gaming</h1>
    </div>
    <div class="pcb-topbar-sub" id="build-label">Chọn 8 linh kiện để hoàn tất cấu hình</div>
  </div>

  <!-- Mobile tabs -->
  <div class="pcb-mtabs">
    <button class="pcbt active" onclick="mobileTab('slots')"><i class="fa-solid fa-list"></i> Linh kiện</button>
    <button class="pcbt" onclick="mobileTab('browser')"><i class="fa-solid fa-magnifying-glass"></i> Chọn sản phẩm</button>
  </div>

  <div class="pcb-body">
    <!-- LEFT: Slots -->
    <div class="pcb-left" id="pcb-slots-panel">
      <div class="pcb-lhead">
        <h2>Cấu hình</h2>
        <div class="pcb-prog">
          <div class="pcb-prog-bar"><div class="pcb-prog-fill" id="prog-fill" style="width:0"></div></div>
          <span class="pcb-prog-txt" id="prog-txt">0/8</span>
        </div>
      </div>
      <div class="pcb-slots" id="slots-list"></div>
      <div class="pcb-compat" id="compat-panel"></div>
    </div>

    <!-- RIGHT: Browser -->
    <div class="pcb-right" id="pcb-browser-panel">
      <div class="pcb-tabs-wrap">
        <div class="pcb-tabs" id="pcb-tabs"></div>
      </div>
      <div class="pcb-filters">
        <input type="text" id="pb-search" placeholder="Tìm sản phẩm..." oninput="debouncedFilter()">
        <select id="pb-sort" onchange="filterProducts()">
          <option value="newest">Mới nhất</option>
          <option value="price_asc">Giá tăng dần</option>
          <option value="price_desc">Giá giảm dần</option>
          <option value="bestseller">Bán chạy</option>
        </select>
        <div class="pb-price-wrap">
          <input type="number" id="pb-min" placeholder="Từ" oninput="filterProducts()" style="width:80px">
          <span>—</span>
          <input type="number" id="pb-max" placeholder="Đến" oninput="filterProducts()" style="width:80px">
        </div>
      </div>
      <div class="pcb-scroll" id="pcb-scroll">
        <div class="pcb-grid" id="pcb-grid"></div>
      </div>
    </div>
  </div>
</div>

<!-- Summary bar -->
<div class="pcb-bar hidden" id="pcb-bar">
  <div class="pcb-bar-items" id="bar-items"></div>
  <div class="pcb-bar-total">
    <span class="bar-total-lbl">Tổng:</span>
    <span class="bar-total-price" id="bar-total">0₫</span>
  </div>
  <div class="pcb-bar-btns">
    <button class="btn-cart-all" id="btn-cart-all" onclick="addAllToCart()">
      <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
    </button>
    <button class="btn-save-build" onclick="saveBuild()"><i class="fa-solid fa-floppy-disk"></i> Lưu</button>
    <button class="btn-share-build" onclick="shareBuild()"><i class="fa-solid fa-share-nodes"></i> Chia sẻ</button>
  </div>
</div>

<!-- Auth modal -->
<div class="pcb-modal-ov" id="pcb-auth-modal">
  <div class="pcb-modal-box">
    <div class="pcb-modal-icon"><i class="fa-solid fa-cart-shopping"></i></div>
    <h3>Đăng nhập để thêm vào giỏ</h3>
    <p>Đăng nhập để lưu cấu hình PC và quản lý đơn hàng dễ hơn.</p>
    <div class="pcb-modal-btns">
      <a id="pcb-auth-login" href="#" class="btn-ml btn-ml-login"><i class="fa-solid fa-right-to-bracket"></i> Đăng nhập</a>
      <a id="pcb-auth-reg"   href="#" class="btn-ml btn-ml-reg"><i class="fa-solid fa-user-plus"></i> Đăng ký</a>
      <button class="btn-ml btn-ml-guest" onclick="closePcbModal();proceedAddToCart()">Tiếp tục không cần đăng nhập</button>
    </div>
  </div>
</div>

<script>
const APP_URL     = '<?= APP_URL ?>';
const UPLOAD_URL  = '<?= UPLOAD_URL ?>';
const IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;
const PRODUCTS    = <?= json_encode($slotProducts, JSON_UNESCAPED_UNICODE) ?>;
const SLOT_META   = <?= json_encode($slotMeta,    JSON_UNESCAPED_UNICODE) ?>;
const SLOTS_DEF  = [
  {key:'man-hinh',   label:'Màn hình',    icon:'fa-desktop'},
  {key:'vo-case',    label:'Vỏ Case',     icon:'fa-box-open'},
  {key:'cpu',        label:'CPU',          icon:'fa-microchip'},
  {key:'mainboard',  label:'Mainboard',   icon:'fa-server'},
  {key:'ram',        label:'RAM',          icon:'fa-memory'},
  {key:'card-do-hoa',label:'Card đồ họa', icon:'fa-display'},
  {key:'ssd-o-cung', label:'Ổ cứng SSD',  icon:'fa-hard-drive'},
  {key:'tan-nhiet',  label:'Tản nhiệt',   icon:'fa-fan'},
  {key:'nguon',      label:'Nguồn (PSU)', icon:'fa-plug'},
];

const state = {
  build: {'man-hinh':null,'vo-case':null,cpu:null,mainboard:null,ram:null,'card-do-hoa':null,'ssd-o-cung':null,'tan-nhiet':null,nguon:null},
  tab: 'vo-case',
  search: '',
  sort: 'newest',
  minPrice: 0,
  maxPrice: Infinity,
  compat: [],
};

// ── Init ──────────────────────────────────────────────────────
loadFromStorage();
renderAll();
// Auto-trigger cart if returning from login/register redirect
if (localStorage.getItem('pcbuild_auto_cart')==='1' && IS_LOGGED_IN) {
  localStorage.removeItem('pcbuild_auto_cart');
  setTimeout(addAllToCart, 700);
}

// ── Render ────────────────────────────────────────────────────
function renderAll() {
  checkCompatibility();
  renderSlots();
  renderTabs();
  renderProducts();
  renderBar();
}

function renderSlots() {
  const count = SLOTS_DEF.filter(s => state.build[s.key]).length;
  const total = SLOTS_DEF.length;
  document.getElementById('prog-fill').style.width = (count/total*100)+'%';
  document.getElementById('prog-txt').textContent  = count+'/'+total;
  document.getElementById('build-label').textContent =
    count === total ? '✓ Cấu hình hoàn tất — sẵn sàng xây dựng!' : `Chọn ${total-count} linh kiện nữa`;

  const list = document.getElementById('slots-list');
  list.innerHTML = SLOTS_DEF.map(slot => {
    const p      = state.build[slot.key];
    const isAct  = state.tab === slot.key;
    const cEntry = state.compat.find(c => c.slots.includes(slot.key));
    const dotCls = !p ? '' : cEntry ? 'bad' : 'ok';
    const dotTip = cEntry ? cEntry.msg : (p ? 'Tương thích' : '');
    return `<div class="pcb-slot${isAct?' active':''}${p?' has-item':''}" onclick="switchTab('${slot.key}')">
      <div class="slot-ico"><i class="fa-solid ${slot.icon}"></i></div>
      <div class="slot-content">
        <div class="slot-label">${slot.label}</div>
        ${p ? `<div class="slot-sel">
          <img src="${p.image||'data:image/gif;base64,R0lGODlhAQABAAAAACw='}" onerror="this.style.display='none'" alt="">
          <div class="slot-sel-info">
            <div class="slot-sel-name">${esc(p.name)}</div>
            <div class="slot-sel-price">${fmt(p.price)}</div>
          </div>
        </div>` : `<div class="slot-empty">Chưa chọn</div>`}
      </div>
      <div class="slot-meta">
        ${dotCls ? `<div class="compat-dot ${dotCls}" title="${esc(dotTip)}"></div>` : ''}
        ${p
          ? `<button class="btn-rm" onclick="event.stopPropagation();removeProduct('${slot.key}')" title="Xóa"><i class="fa-solid fa-xmark"></i></button>`
          : `<button class="btn-choose" onclick="event.stopPropagation();switchTab('${slot.key}')">Chọn</button>`
        }
      </div>
    </div>`;
  }).join('');

  // Compat warnings
  const panel = document.getElementById('compat-panel');
  panel.innerHTML = state.compat.map(c =>
    `<div class="compat-warn"><i class="fa-solid fa-triangle-exclamation"></i>${esc(c.msg)}</div>`
  ).join('');
}

function renderTabs() {
  document.getElementById('pcb-tabs').innerHTML = SLOTS_DEF.map(s => {
    const sel = state.build[s.key];
    return `<button class="pcb-tab${state.tab===s.key?' active':''}" onclick="switchTab('${s.key}')">
      <i class="fa-solid ${s.icon}"></i> ${s.label}
      ${sel ? '<span class="tab-ck"><i class="fa-solid fa-check"></i></span>' : ''}
    </button>`;
  }).join('');
}

function renderProducts() {
  const grid = document.getElementById('pcb-grid');
  let list = [...(PRODUCTS[state.tab] || [])];

  // Filter
  const q = state.search.trim().toLowerCase();
  if (q)                 list = list.filter(p => (p.name+p.short_desc+p.brand).toLowerCase().includes(q));
  if (state.minPrice>0)  list = list.filter(p => p.price >= state.minPrice);
  if (state.maxPrice<Infinity) list = list.filter(p => p.price <= state.maxPrice);
  // Sort
  if      (state.sort==='price_asc')  list.sort((a,b)=>a.price-b.price);
  else if (state.sort==='price_desc') list.sort((a,b)=>b.price-a.price);
  else if (state.sort==='bestseller') list.sort((a,b)=>b.sold-a.sold);

  if (!list.length) {
    const hasCat = !SLOT_META[state.tab] || SLOT_META[state.tab].has_cat !== false;
    const emsg = hasCat
      ? '<i class="fa-solid fa-magnifying-glass"></i><p>Không tìm thấy sản phẩm</p><p style="font-size:.74rem;color:#2a2a2a">Thử thay đổi từ khóa hoặc bộ lọc</p>'
      : '<i class="fa-solid fa-layer-group"></i><p style="color:#e30000">Danh mục đang cập nhật</p><p style="font-size:.74rem;color:#555">Danh mục này chưa có sản phẩm riêng.<br>Vui lòng linh kiện tương ứng qua trang Sản phẩm.</p>';
    grid.innerHTML='<div class="pcb-empty">'+emsg+'</div>';
    return;
  }

  const selId = state.build[state.tab]?.id;
  grid.innerHTML = list.map(p => {
    const isSel = selId === p.id;
    return `<div class="pcb-card${isSel?' selected':''}" onclick="selectProduct('${state.tab}',${p.id})">
      <div class="pcb-card-img">
        ${p.image ? `<img src="${p.image}" alt="${esc(p.name)}" onerror="this.parentNode.innerHTML='<i class=\\'fa-solid fa-box\\' style=\\'color:#ccc;font-size:1.6rem\\'></i>'">` : '<i class="fa-solid fa-box" style="color:#ccc;font-size:1.6rem"></i>'}
        ${isSel ? '<div class="pcb-card-badge"><i class="fa-solid fa-check"></i></div>' : ''}
      </div>
      <div class="pcb-card-body">
        ${p.brand ? `<div class="pcb-card-brand">${esc(p.brand)}</div>` : ''}
        <div class="pcb-card-name">${esc(p.name)}</div>
        <div class="pcb-card-price">${fmt(p.price)}</div>
        ${p.short_desc ? `<div class="pcb-card-desc">${esc(p.short_desc)}</div>` : ''}
      </div>
      <div class="pcb-card-footer">
        <button class="pcb-add-btn${isSel?' sel':''}" onclick="event.stopPropagation();selectProduct('${state.tab}',${p.id})">
          <i class="fa-solid ${isSel?'fa-check':'fa-plus'}"></i> ${isSel?'Đã chọn':'Thêm vào build'}
        </button>
      </div>
    </div>`;
  }).join('');
}

function renderBar() {
  const bar   = document.getElementById('pcb-bar');
  const items = SLOTS_DEF.map(s => [s.key, state.build[s.key]]).filter(([,v])=>v);
  if (!items.length) { bar.classList.add('hidden'); document.body.style.paddingBottom=''; return; }
  bar.classList.remove('hidden');
  document.body.style.paddingBottom='72px';

  let total=0;
  document.getElementById('bar-items').innerHTML = items.map(([k,p]) => {
    total += p.price;
    return `<div class="bar-item">
      ${p.image?`<img src="${p.image}" alt="" onerror="this.style.display='none'">`:''}
      <div>
        <div class="bar-item-name">${esc(p.name)}</div>
        <div class="bar-item-price">${fmt(p.price)}</div>
      </div>
      <button class="bar-item-rm" onclick="removeProduct('${k}')" title="Xóa"><i class="fa-solid fa-xmark"></i></button>
    </div>`;
  }).join('');
  document.getElementById('bar-total').textContent = fmt(total);
}

// ── Actions ───────────────────────────────────────────────────
function selectProduct(slotKey, productId) {
  const p = (PRODUCTS[slotKey]||[]).find(p=>p.id===productId);
  if (!p) return;
  state.build[slotKey] = p;
  renderAll();
  // Auto-advance to next empty slot
  const cur = SLOTS_DEF.findIndex(s=>s.key===slotKey);
  const next = SLOTS_DEF.slice(cur+1).find(s=>!state.build[s.key]);
  if (next) setTimeout(()=>switchTab(next.key), 350);
}

function removeProduct(slotKey) {
  state.build[slotKey] = null;
  renderAll();
}

function switchTab(key) {
  state.tab = key;
  state.search = '';
  state.minPrice = 0;
  state.maxPrice = Infinity;
  const si = document.getElementById('pb-search');
  const mi = document.getElementById('pb-min');
  const xi = document.getElementById('pb-max');
  if (si) si.value='';
  if (mi) mi.value='';
  if (xi) xi.value='';
  renderTabs(); renderSlots(); renderProducts();
  document.getElementById('pcb-scroll').scrollTop=0;
}

function filterProducts() {
  state.search   = document.getElementById('pb-search').value;
  state.sort     = document.getElementById('pb-sort').value;
  const mn = document.getElementById('pb-min').value;
  const mx = document.getElementById('pb-max').value;
  state.minPrice = mn ? parseFloat(mn)*1000000 : 0;  // assumes input in millions? or raw?
  // Actually interpret as raw number (VND)
  state.minPrice = mn ? parseFloat(mn) : 0;
  state.maxPrice = mx ? parseFloat(mx) : Infinity;
  renderProducts();
}

let _filterTimer;
function debouncedFilter() { clearTimeout(_filterTimer); _filterTimer=setTimeout(filterProducts,220); }

// ── Compatibility check ───────────────────────────────────────
function checkCompatibility() {
  const b = state.build;
  const warns = [];
  const up = s => (s||'').toUpperCase();

  // RAM ↔ Mainboard DDR
  if (b.ram && b.mainboard) {
    const rn=up(b.ram.name+' '+b.ram.short_desc), mn=up(b.mainboard.name+' '+b.mainboard.short_desc);
    if ((rn.includes('DDR4')&&mn.includes('DDR5'))||(rn.includes('DDR5')&&mn.includes('DDR4')))
      warns.push({slots:['ram','mainboard'], msg:'RAM và Mainboard không cùng chuẩn DDR (DDR4 ≠ DDR5)'});
  }

  // CPU ↔ Mainboard socket
  if (b.cpu && b.mainboard) {
    const cn=up(b.cpu.name), mn=up(b.mainboard.name);
    const socks=['AM4','AM5','LGA1700','LGA1200','LGA1851','TR5','STRX4'];
    let cs='',ms='';
    socks.forEach(s=>{ if(cn.includes(s))cs=s; if(mn.includes(s))ms=s; });
    if (cs && ms && cs!==ms)
      warns.push({slots:['cpu','mainboard'], msg:`CPU socket ${cs} không khớp với Mainboard socket ${ms}`});
  }

  // PSU ↔ GPU wattage
  if (b['nguon'] && b['card-do-hoa']) {
    const pw=parseInt((up(b['nguon'].name).match(/(\d{3,4})\s*W/)||[])[1]||0);
    const gn=up(b['card-do-hoa'].name);
    let rw=0;
    if(gn.match(/409/))rw=850; else if(gn.match(/408/))rw=750;
    else if(gn.match(/407[0-9]/))rw=650; else if(gn.match(/309/))rw=750;
    else if(gn.match(/308/))rw=700; else if(gn.match(/307/))rw=650;
    else if(gn.match(/RX\s*79/))rw=800; else if(gn.match(/RX\s*6[89]/))rw=700;
    if (pw && rw && pw<rw)
      warns.push({slots:['nguon','card-do-hoa'], msg:`Nguồn ${pw}W có thể không đủ cho GPU này (khuyến nghị ≥${rw}W)`});
  }

  state.compat = warns;
}

// ── Cart ──────────────────────────────────────────────────────
async function addAllToCart() {
  const items = SLOTS_DEF.map(s=>state.build[s.key]).filter(Boolean);
  if (!items.length) { toast('Chưa chọn linh kiện nào!','error'); return; }
  if (!IS_LOGGED_IN) {
    saveBuildSilent();
    const redir = encodeURIComponent(APP_URL+'/products/pc-builder');
    document.getElementById('pcb-auth-login').href = APP_URL+'/auth/login?redirect='+redir;
    document.getElementById('pcb-auth-reg').href   = APP_URL+'/auth/register?redirect='+redir;
    document.getElementById('pcb-auth-modal').classList.add('open');
    return;
  }
  await proceedAddToCart();
}

async function proceedAddToCart() {
  const items = SLOTS_DEF.map(s=>state.build[s.key]).filter(Boolean);
  if (!items.length) { toast('Chưa chọn linh kiện nào!','error'); return; }
  const btn = document.getElementById('btn-cart-all');
  btn.disabled=true; btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang thêm...';
  let ok=0;
  for (const p of items) {
    try {
      const r = await fetch(APP_URL+'/api/cart/add',{
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({product_id:p.id,quantity:1})
      });
      const d = await r.json(); if(d.success) ok++;
    } catch(e){}
  }
  btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng';
  toast(`Đã thêm ${ok}/${items.length} sản phẩm vào giỏ hàng`,'success');
  const badge=document.getElementById('cart-badge');
  if(badge&&ok>0){badge.style.display='';badge.textContent=(parseInt(badge.textContent)||0)+ok;}
}

function closePcbModal() {
  document.getElementById('pcb-auth-modal').classList.remove('open');
}

// ── Save / Load / Share ───────────────────────────────────────
function saveBuild() {
  const ids={};
  SLOTS_DEF.forEach(s=>{ if(state.build[s.key]) ids[s.key]=state.build[s.key].id; });
  localStorage.setItem('pcbuild_tuanhuy',JSON.stringify(ids));
  toast('Đã lưu cấu hình vào thiết bị!','success');
}

function saveBuildSilent() {
  const ids={};
  SLOTS_DEF.forEach(s=>{ if(state.build[s.key]) ids[s.key]=state.build[s.key].id; });
  localStorage.setItem('pcbuild_tuanhuy',JSON.stringify(ids));
  localStorage.setItem('pcbuild_auto_cart','1');
}

function loadFromStorage() {
  const params=new URLSearchParams(location.search);
  const b64=params.get('b');
  let ids={};
  try { if(b64) ids=JSON.parse(atob(b64)); }
  catch(e){ try { ids=JSON.parse(localStorage.getItem('pcbuild_tuanhuy')||'{}'); }catch(e){} }
  if (!Object.keys(ids).length) try { ids=JSON.parse(localStorage.getItem('pcbuild_tuanhuy')||'{}'); }catch(e){}
  for(const [k,id] of Object.entries(ids)){
    const p=(PRODUCTS[k]||[]).find(p=>p.id===id);
    if(p) state.build[k]=p;
  }
}

function shareBuild() {
  const ids={};
  SLOTS_DEF.forEach(s=>{ if(state.build[s.key]) ids[s.key]=state.build[s.key].id; });
  if(!Object.keys(ids).length){toast('Chưa chọn linh kiện nào!','error');return;}
  const url=location.origin+location.pathname+'?b='+btoa(JSON.stringify(ids));
  if(navigator.clipboard){
    navigator.clipboard.writeText(url).then(()=>toast('Đã copy link chia sẻ!','success'))
      .catch(()=>prompt('Copy link:',url));
  } else { prompt('Copy link:',url); }
}

// ── Mobile tabs ───────────────────────────────────────────────
function mobileTab(tab) {
  document.querySelectorAll('.pcbt').forEach((b,i)=>b.classList.toggle('active',(tab==='slots'&&i===0)||(tab==='browser'&&i===1)));
  document.getElementById('pcb-slots-panel').classList.toggle('mob-hidden',tab!=='slots');
  document.getElementById('pcb-browser-panel').classList.toggle('mob-hidden',tab!=='browser');
}

// ── Helpers ───────────────────────────────────────────────────
function fmt(n) { return new Intl.NumberFormat('vi-VN').format(n)+'₫'; }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg,type){
  const w=document.getElementById('toast-wrap');if(!w)return;
  const t=document.createElement('div');
  t.className='toast '+type;
  t.innerHTML=`<div class="t-ico"><i class="fa-solid ${type==='success'?'fa-check':'fa-xmark'}"></i></div><span>${msg}</span>`;
  w.appendChild(t);
  setTimeout(()=>{t.classList.add('out');setTimeout(()=>t.remove(),300);},3000);
}
</script>
<?php include __DIR__.'/../layouts/footer.php'; ?>
