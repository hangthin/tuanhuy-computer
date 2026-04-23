<?php require_once __DIR__.'/layout_top.php'; ?>
<?php
$approved  = $approved  ?? [];
$hasBackup = $hasBackup ?? false;

$components = [
    ['key'=>'hero-banner','label'=>'Hero Banner',  'icon'=>'fa-image',          'desc'=>'Ảnh nền hero section',       'query'=>'PC gaming RGB setup dark wallpaper 4K'],
    ['key'=>'gaming-pc',  'label'=>'Gaming PC',    'icon'=>'fa-desktop',        'desc'=>'PC gaming tower RGB',         'query'=>'gaming desktop computer tower RGB lights'],
    ['key'=>'laptop',     'label'=>'Laptop',       'icon'=>'fa-laptop',         'desc'=>'Laptop gaming / văn phòng',   'query'=>'gaming laptop dark background RGB keyboard'],
    ['key'=>'monitor',    'label'=>'Màn hình',     'icon'=>'fa-tv',             'desc'=>'Màn hình gaming cong',        'query'=>'ultrawide curved gaming monitor dark setup'],
    ['key'=>'keyboard',   'label'=>'Bàn phím',     'icon'=>'fa-keyboard',       'desc'=>'Bàn phím cơ RGB',             'query'=>'mechanical RGB gaming keyboard dark background'],
    ['key'=>'mouse',      'label'=>'Chuột',        'icon'=>'fa-computer-mouse', 'desc'=>'Chuột gaming RGB',            'query'=>'gaming mouse RGB wireless dark background'],
    ['key'=>'headset',    'label'=>'Tai nghe',     'icon'=>'fa-headphones',     'desc'=>'Tai nghe gaming RGB',         'query'=>'gaming headset RGB headphones dark background'],
    ['key'=>'ram',        'label'=>'RAM',          'icon'=>'fa-memory',         'desc'=>'RAM DDR5 RGB',                'query'=>'DDR5 RGB RAM memory stick dark background'],
];
$total         = count($components);
$approvedCount = 0;
foreach ($components as $c) { if (!empty($approved[$c['key']]['url'])) $approvedCount++; }
$pct = $total > 0 ? round($approvedCount / $total * 100) : 0;
?>
<style>
/* ── Tabs (identical to ai_generator) ──────────────────────────── */
.ai-tab{padding:.44rem .78rem;background:none;border:none;color:#555;font-size:.75rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;transition:all .18s;font-family:inherit;white-space:nowrap}
.ai-tab.on{color:var(--red);border-color:var(--red)}
/* ── Drop zone (identical to ai_generator) ─────────────────────── */
.dzone{background:#0f0f0f;border:2px dashed #222;border-radius:10px;padding:1.25rem .9rem;text-align:center;cursor:pointer;position:relative;min-height:160px;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:all .2s}
.dzone:hover,.dzone.drag{border-color:var(--red);background:#1a0505}
.dzone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;width:100%;height:100%}
.dz-ic{width:40px;height:40px;background:rgba(227,0,0,.1);border-radius:9px;display:flex;align-items:center;justify-content:center;margin:0 auto .55rem;color:var(--red);font-size:1.1rem}
/* ── Card grid ──────────────────────────────────────────────────── */
.am-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1rem}
.am-card{background:#1a1a1a;border:1.5px solid #252525;border-radius:12px;overflow:hidden;transition:border-color .2s}
.am-card.done{border-color:#22c55e44}
.am-hd{padding:.65rem 1rem;border-bottom:1px solid #1e1e1e;display:flex;align-items:center;gap:.5rem}
.am-preview{height:130px;background:#111;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.am-preview img{width:100%;height:100%;object-fit:cover;display:block}
.am-preview .ph{color:#222;font-size:2.5rem}
.am-body{padding:.7rem .9rem}
.am-tabs{display:flex;gap:0;margin-bottom:.65rem;border-bottom:1px solid #1c1c1c;padding-bottom:.5rem}
/* ── Search results ─────────────────────────────────────────────── */
.sr-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:3px;margin-top:.55rem;max-height:182px;overflow-y:auto}
.sr-grid::-webkit-scrollbar{width:3px}.sr-grid::-webkit-scrollbar-thumb{background:#333}
.sr-th{aspect-ratio:1;overflow:hidden;border-radius:4px;cursor:pointer;border:2px solid transparent;transition:border-color .15s,transform .15s;position:relative}
.sr-th:hover{border-color:var(--red);transform:scale(1.06);z-index:2}
.sr-th img{width:100%;height:100%;object-fit:cover;display:block}
/* ── Approve button ─────────────────────────────────────────────── */
.app-btn{width:100%;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;padding:.52rem;border-radius:8px;font-weight:700;font-size:.8rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.4rem;margin-top:.55rem;transition:background .2s}
.app-btn:hover{background:#15803d}
/* ── Autocomplete dropdown ──────────────────────────────────────── */
.ac-drop{position:absolute;top:100%;left:0;right:0;background:#1e1e1e;border:1px solid #2a2a2a;border-top:none;border-radius:0 0 8px 8px;z-index:200;max-height:180px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.4)}
.ac-drop::-webkit-scrollbar{width:3px}.ac-drop::-webkit-scrollbar-thumb{background:#333}
.ac-item{padding:.38rem .75rem;cursor:pointer;font-size:.77rem;color:#ccc;border-bottom:1px solid #252525;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:background .12s}
.ac-item:hover{background:#2a2a2a;color:#fff}
.ac-item:last-child{border-bottom:none}
/* ── Progress ───────────────────────────────────────────────────── */
.prog-wrap{background:#1a1a1a;border:1px solid #222;border-radius:12px;padding:1rem 1.35rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1.5rem}
.prog-track{flex:1;height:7px;background:#111;border-radius:99px;overflow:hidden}
.prog-fill{height:100%;background:linear-gradient(90deg,#16a34a,#22c55e);border-radius:99px;transition:width .5s ease}
.act-bar{display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1.25rem}
/* ── Demo overlay ───────────────────────────────────────────────── */
#demoOvl{position:fixed;inset:0;background:#000;z-index:9999;display:none;flex-direction:column}
#demoOvl.show{display:flex}
.demo-x{position:absolute;top:1rem;right:1rem;z-index:10;background:rgba(255,255,255,.08);border:none;color:#fff;width:38px;height:38px;border-radius:50%;cursor:pointer;font-size:.95rem;display:flex;align-items:center;justify-content:center;transition:background .2s}
.demo-x:hover{background:rgba(255,255,255,.2)}
#demoCanvas{flex:1;position:relative;overflow:hidden}
.d-phase{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none}
.d-phase.cur{opacity:1;pointer-events:auto}
.d-bg{position:absolute;inset:0;background-size:cover;background-position:center}
.d-fog{position:absolute;inset:0;background:rgba(0,0,0,.58)}
.d-cnt{position:relative;z-index:2;text-align:center;color:#fff;padding:2rem;max-width:700px}
.d-cnt h2{font-size:clamp(1.6rem,4vw,3rem);font-weight:900;letter-spacing:-.5px;margin-bottom:.5rem}
.demo-bar{padding:.8rem 2rem;background:rgba(255,255,255,.04);border-top:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:1rem;flex-shrink:0}
.demo-bar input[type=range]{flex:1;accent-color:var(--red);cursor:pointer}
.ph-dots{display:flex;gap:.4rem;justify-content:center;margin-top:.9rem}
.ph-dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.2);cursor:pointer;transition:background .2s,transform .2s}
.ph-dot.on{background:var(--red);transform:scale(1.3)}
</style>

<!-- ── Progress ────────────────────────────────────────────────── -->
<div class="prog-wrap">
  <div>
    <div style="font-size:.68rem;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.2rem">Tiến độ duyệt ảnh</div>
    <div style="font-size:1.3rem;font-weight:900;color:#fff">
      <span id="jsCount"><?= $approvedCount ?></span>
      <span style="color:#333;font-size:.88rem"> / <?= $total ?></span>
      <span style="font-size:.75rem;color:#22c55e;margin-left:.4rem">ảnh đã duyệt</span>
    </div>
  </div>
  <div class="prog-track"><div class="prog-fill" id="jsProg" style="width:<?= $pct ?>%"></div></div>
  <div style="font-size:1.05rem;font-weight:900;color:#22c55e;min-width:42px;text-align:right" id="jsPct"><?= $pct ?>%</div>
</div>

<!-- ── Action bar ───────────────────────────────────────────────── -->
<div class="act-bar">
  <button class="btn-r" id="btnDemo"     onclick="openDemo()"    <?= $approvedCount===0?'disabled':'' ?>><i class="fa-solid fa-play"></i> Chạy Demo</button>
  <button class="btn-r" id="btnDeploy"   onclick="deployHome()"  <?= $approvedCount===0?'disabled':'' ?> style="background:#7c3aed"><i class="fa-solid fa-rocket"></i> Duyệt &amp; Áp dụng vào Trang Chủ</button>
  <button class="btn-g" id="btnRollback" onclick="rollbackHome()" style="display:<?= $hasBackup?'inline-flex':'none' ?>"><i class="fa-solid fa-rotate-left"></i> Khôi phục phiên bản cũ</button>
  <span id="depStatus" style="font-size:.76rem;color:#555"></span>
</div>

<!-- ── Asset cards ──────────────────────────────────────────────── -->
<div class="am-grid">
<?php foreach ($components as $c):
    $k      = $c['key'];
    $hasImg = !empty($approved[$k]['url']);
?>
<div class="am-card <?= $hasImg?'done':'' ?>" id="card-<?= $k ?>">
  <!-- Header -->
  <div class="am-hd">
    <div style="width:27px;height:27px;background:rgba(227,0,0,.13);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--red);font-size:.72rem;flex-shrink:0"><i class="fa-solid <?= $c['icon'] ?>"></i></div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:700;font-size:.82rem;color:#ddd"><?= $c['label'] ?></div>
      <div style="font-size:.67rem;color:#444;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $c['desc'] ?></div>
    </div>
    <div id="tick-<?= $k ?>" style="color:#22c55e;font-size:.9rem;<?= $hasImg?'':'display:none' ?>"><i class="fa-solid fa-circle-check"></i></div>
  </div>
  <!-- Current approved preview -->
  <div class="am-preview" id="prev-<?= $k ?>">
    <?php if ($hasImg): ?><img src="<?= htmlspecialchars($approved[$k]['url']) ?>" alt=""><?php else: ?><i class="fa-solid <?= $c['icon'] ?> ph"></i><?php endif; ?>
  </div>
  <!-- Background removal button (visible after approval) -->
  <div id="bgrm-wrap-<?= $k ?>" style="<?= $hasImg?'':'display:none' ?>">
    <button onclick="removeBg('<?= $k ?>')" class="btn-g" style="width:100%;border-radius:0;border-top:1px solid #1e1e1e;font-size:.73rem;padding:.4rem;display:flex;align-items:center;justify-content:center;gap:.35rem;transition:all .2s" onmouseover="this.style.background='#1a0a1a';this.style.color='#e879f9'" onmouseout="this.style.background='';this.style.color=''">
      <i class="fa-solid fa-scissors"></i> Tách nền PNG (AI)
    </button>
  </div>
  <!-- Input tabs -->
  <div class="am-body">
    <div class="am-tabs">
      <button class="ai-tab on" id="tab-<?= $k ?>-u" onclick="sTab('<?= $k ?>','u')"><i class="fa-solid fa-upload" style="margin-right:3px"></i>Upload</button>
      <button class="ai-tab"    id="tab-<?= $k ?>-l" onclick="sTab('<?= $k ?>','l')"><i class="fa-solid fa-link"   style="margin-right:3px"></i>URL</button>
      <button class="ai-tab"    id="tab-<?= $k ?>-c" onclick="sTab('<?= $k ?>','c');activeCard='<?= $k ?>'"><i class="fa-solid fa-paste"  style="margin-right:3px"></i>Clipboard</button>
      <button class="ai-tab"    id="tab-<?= $k ?>-s" onclick="sTab('<?= $k ?>','s')"><i class="fa-solid fa-magnifying-glass" style="margin-right:3px"></i>Search</button>
      <button class="ai-tab"    id="tab-<?= $k ?>-n" onclick="sTab('<?= $k ?>','n')"><i class="fa-solid fa-keyboard" style="margin-right:3px"></i>Tên SP</button>
    </div>

    <!-- ── Upload tab ─────────────────────────────────────────── -->
    <div id="at-<?= $k ?>-u">
      <div class="dzone" id="dz-<?= $k ?>"
           ondragover="event.preventDefault();this.classList.add('drag')"
           ondragleave="this.classList.remove('drag')"
           ondrop="onDropCard(event,'<?= $k ?>')">
        <input type="file" accept="image/*" id="fi-<?= $k ?>" onchange="loadFile('<?= $k ?>',this)">
        <div id="dzc-<?= $k ?>">
          <div class="dz-ic"><i class="fa-solid fa-cloud-arrow-up"></i></div>
          <div style="color:#bbb;font-weight:600;font-size:.8rem;margin-bottom:.25rem">Kéo thả hoặc click chọn ảnh</div>
          <div style="color:#333;font-size:.7rem">JPG, PNG, WEBP — tối đa 10MB</div>
        </div>
        <img id="dp-<?= $k ?>" src="" alt="" style="display:none;width:100%;max-height:150px;object-fit:contain;border-radius:7px;position:relative;z-index:1;pointer-events:none">
      </div>
      <div id="dzi-<?= $k ?>" style="display:none;margin-top:.4rem;font-size:.72rem;color:#22c55e;display:flex;align-items:center;gap:.3rem">
        <i class="fa-solid fa-circle-check"></i><span id="dfn-<?= $k ?>"></span><span style="color:#555" id="dsz-<?= $k ?>"></span>
      </div>
    </div>

    <!-- ── URL tab ────────────────────────────────────────────── -->
    <div id="at-<?= $k ?>-l" style="display:none">
      <div style="display:flex;gap:.35rem;margin-bottom:.45rem">
        <input type="url" id="ul-<?= $k ?>" class="form-inp" placeholder="https://example.com/image.jpg" style="font-size:.78rem"
               onkeydown="if(event.key==='Enter')prevUrl('<?= $k ?>')">
        <button onclick="prevUrl('<?= $k ?>')" class="btn-g" style="padding:.38rem .55rem;font-size:.77rem;flex-shrink:0"><i class="fa-solid fa-eye"></i></button>
      </div>
      <div style="background:#0f0f0f;border-radius:8px;height:130px;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #1a1a1a">
        <img id="up-<?= $k ?>" style="max-width:100%;max-height:130px;object-fit:contain;display:none" alt="">
        <div id="uph-<?= $k ?>" style="color:#2a2a2a;text-align:center"><i class="fa-solid fa-image" style="font-size:1.8rem;margin-bottom:.3rem;display:block"></i><span style="font-size:.7rem">Xem trước ảnh</span></div>
      </div>
    </div>

    <!-- ── Clipboard tab ─────────────────────────────────────── -->
    <div id="at-<?= $k ?>-c" style="display:none">
      <div style="background:#0f0f0f;border:2px dashed #1e1e1e;border-radius:10px;padding:1.25rem;text-align:center;cursor:pointer;transition:border-color .2s"
           onclick="doPaste('<?= $k ?>')"
           onmouseover="this.style.borderColor='var(--red)'"
           onmouseout="this.style.borderColor='#1e1e1e'"
           id="pz-<?= $k ?>">
        <div class="dz-ic" style="margin:0 auto .5rem"><i class="fa-solid fa-paste"></i></div>
        <div style="color:#bbb;font-weight:600;font-size:.8rem;margin-bottom:.22rem">Nhấn <kbd style="background:#222;padding:1px 5px;border-radius:4px;font-size:.72rem">Ctrl+V</kbd> để dán ảnh</div>
        <div style="color:#333;font-size:.7rem">hoặc click vào đây rồi dán</div>
      </div>
      <img id="cp-<?= $k ?>" src="" alt="" style="display:none;width:100%;max-height:130px;object-fit:contain;border-radius:7px;margin-top:.5rem">
    </div>

    <!-- ── Search tab ────────────────────────────────────────── -->
    <div id="at-<?= $k ?>-s" style="display:none">
      <div style="display:flex;gap:.4rem">
        <input class="form-inp" id="q-<?= $k ?>" value="<?= htmlspecialchars($c['query']) ?>" style="flex:1;font-size:.76rem"
               onkeydown="if(event.key==='Enter')doSearch('<?= $k ?>')">
        <button class="btn-r" onclick="doSearch('<?= $k ?>')" style="padding:.45rem .65rem;flex-shrink:0"><i class="fa-solid fa-magnifying-glass"></i></button>
      </div>
      <div id="ld-<?= $k ?>" style="display:none;text-align:center;padding:.4rem;color:#555;font-size:.72rem"><i class="fa-solid fa-spinner fa-spin"></i> Đang tìm...</div>
      <div id="res-<?= $k ?>" class="sr-grid" style="display:none"></div>
    </div>

    <!-- ── Tên SP tab ────────────────────────────────────────── -->
    <div id="at-<?= $k ?>-n" style="display:none">
      <div style="position:relative">
        <input type="text" id="ni-<?= $k ?>" class="form-inp" placeholder="VD: ASUS ROG Strix G16, Chuột Logitech G Pro…"
               style="font-size:.77rem"
               oninput="fetchSuggest('<?= $k ?>',this.value)"
               onkeydown="if(event.key==='Enter'){hideSuggest('<?= $k ?>');searchByName('<?= $k ?>');}else if(event.key==='Escape')hideSuggest('<?= $k ?>')">
        <div id="sug-<?= $k ?>" class="ac-drop" style="display:none"></div>
      </div>
      <div style="display:flex;gap:.4rem;margin-top:.45rem">
        <button class="btn-r" onclick="searchByName('<?= $k ?>')" style="flex:1;padding:.45rem .5rem;font-size:.74rem">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm ảnh theo tên
        </button>
        <button id="aisugg-<?= $k ?>" onclick="aiSuggestName('<?= $k ?>')" class="btn-g" style="padding:.45rem .6rem;font-size:.74rem;border-color:#4c1d95;color:#a78bfa;flex-shrink:0">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Gợi ý AI
        </button>
      </div>
      <div id="nist-<?= $k ?>" style="font-size:.7rem;color:#555;margin-top:.35rem;min-height:1.1em"></div>
    </div>

    <!-- ── Approve button (upload / url / clipboard) ─────────── -->
    <button class="app-btn" id="appbtn-<?= $k ?>" style="display:none" onclick="approveCard('<?= $k ?>')">
      <i class="fa-solid fa-circle-check"></i> Duyệt ảnh này
    </button>

    <!-- Filename -->
    <div id="fn-<?= $k ?>" style="margin-top:.4rem;font-size:.68rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;<?= $hasImg?'color:#22c55e':'color:#444' ?>">
      <?php if ($hasImg): ?><i class="fa-solid fa-check"></i> <?= htmlspecialchars($approved[$k]['filename'] ?? basename($approved[$k]['url'])) ?><?php else: ?>— chưa có ảnh —<?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- ── Banner trang chủ ─────────────────────────────────────────── -->
<?php
$bannerSlots = [
    ['slot'=>'main-banner-1','label'=>'Slide 1 (chính)','icon'=>'fa-panorama',    'desc'=>'Ảnh nền slide 1 — 1920×400px','group'=>'main','idx'=>0],
    ['slot'=>'main-banner-2','label'=>'Slide 2',         'icon'=>'fa-panorama',    'desc'=>'Ảnh nền slide 2 — 1920×400px','group'=>'main','idx'=>1],
    ['slot'=>'main-banner-3','label'=>'Slide 3',         'icon'=>'fa-panorama',    'desc'=>'Ảnh nền slide 3 — 1920×400px','group'=>'main','idx'=>2],
    ['slot'=>'side-banner-1','label'=>'Side banner trên','icon'=>'fa-image',       'desc'=>'Phụ kiện bên phải - trên (600×198px)','group'=>'side','idx'=>0],
    ['slot'=>'side-banner-2','label'=>'Side banner dưới','icon'=>'fa-image',       'desc'=>'Phụ kiện bên phải - dưới (600×198px)','group'=>'side','idx'=>1],
];
// Read current values from banners.json
$bjMain = !empty($banners['main']) ? $banners['main'] : [];
$bjSide = !empty($banners['side']) ? $banners['side'] : [];
?>
<?php
// Debug path info for banner sync
$_bfAbs     = realpath(__DIR__ . '/../../../storage') ?: (__DIR__ . '/../../../storage');
$_bfFile    = $_bfAbs . DIRECTORY_SEPARATOR . 'banners.json';
$_bfExists  = file_exists($_bfFile);
$_bfWrite   = is_writable($_bfAbs);
$_bfContent = $_bfExists ? file_get_contents($_bfFile) : '— file chưa tồn tại —';
?>
<div style="margin-top:1.5rem;margin-bottom:.6rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap">
  <div style="width:3px;height:1.3rem;background:var(--red);border-radius:2px"></div>
  <span style="font-weight:800;font-size:.9rem;color:#ddd">Banner trang chủ</span>
  <span style="font-size:.72rem;color:#444">— lưu vào storage/banners.json → hero section</span>
  <div style="margin-left:auto;display:flex;gap:.45rem">
    <button onclick="syncTest()" class="btn-g" style="font-size:.72rem;padding:.28rem .65rem"><i class="fa-solid fa-vials"></i> Test đồng bộ</button>
    <button onclick="var p=document.getElementById('bDbg');p.style.display=p.style.display==='none'?'block':'none'" class="btn-g" style="font-size:.72rem;padding:.28rem .65rem"><i class="fa-solid fa-bug"></i> Debug JSON</button>
  </div>
</div>
<!-- Debug panel -->
<div id="bDbg" style="display:none;background:#0a0a0a;border:1px solid #1c1c1c;border-radius:8px;padding:.75rem .9rem;margin-bottom:.7rem">
  <div style="display:flex;gap:1.5rem;font-size:.7rem;margin-bottom:.5rem;flex-wrap:wrap">
    <span>Path: <code style="color:#888"><?= htmlspecialchars($_bfFile) ?></code></span>
    <span>Exists: <b style="color:<?= $_bfExists?'#22c55e':'#ef4444' ?>"><?= $_bfExists?'✓ YES':'✗ NO' ?></b></span>
    <span>Writable: <b style="color:<?= $_bfWrite?'#22c55e':'#ef4444' ?>"><?= $_bfWrite?'✓ YES':'✗ NO' ?></b></span>
    <?php if($_bfExists): ?><span>Size: <b style="color:#888"><?= filesize($_bfFile) ?> bytes</b></span><?php endif; ?>
  </div>
  <pre id="bDbgPre" style="background:#111;border-radius:6px;padding:.65rem;overflow:auto;max-height:220px;color:#22c55e;font-size:.68rem;line-height:1.6;margin:0;white-space:pre-wrap"><?= htmlspecialchars($banners ? json_encode($banners, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : $_bfContent) ?></pre>
  <button onclick="document.getElementById('bDbgPre').textContent=''; fetch(BASE+'/admin/banner-debug').then(r=>r.text()).then(t=>{try{document.getElementById('bDbgPre').textContent=JSON.stringify(JSON.parse(t),null,2);}catch(e){document.getElementById('bDbgPre').textContent=t;}});toast('Đã reload banners.json','ok')" class="btn-g" style="margin-top:.45rem;font-size:.7rem;padding:.25rem .6rem"><i class="fa-solid fa-rotate"></i> Reload từ file</button>
</div>
<div class="am-grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
<?php foreach($bannerSlots as $bs):
    $bslot = $bs['slot'];
    $bgrp  = $bs['group']; $bidx = $bs['idx'];
    $bcur  = $bgrp==='main' ? ($bjMain[$bidx]??[]) : ($bjSide[$bidx]??[]);
    $hasB  = !empty($bcur['img']);
    $bUrl  = $hasB ? htmlspecialchars($bcur['img']) : '';
    $bDefLabel = ['HOT DEAL','BÁN CHẠY','MỚI VỀ','Màn hình','Phụ kiện'][$bs['idx'] + ($bgrp==='side'?3:0)] ?? '';
    $bDefTitle = ['PC Gaming RTX 4090<br>Sức mạnh vô giới hạn','Cấu hình gaming<br>Giá tốt nhất','Laptop mỏng nhẹ<br>Hiệu năng vượt trội','Gaming 4K<br>144Hz+','Chuột &amp; Bàn phím<br>Cơ Gaming'][$bs['idx'] + ($bgrp==='side'?3:0)] ?? '';
    $bDefUrl  = [APP_URL.'/products/may-tinh-pc',APP_URL.'/products/may-tinh-pc',APP_URL.'/products/laptop',APP_URL.'/products/man-hinh',APP_URL.'/products/chuot'][$bs['idx'] + ($bgrp==='side'?3:0)] ?? '';
?>
<div class="am-card <?= $hasB?'done':'' ?>" id="bcard-<?= $bslot ?>">
  <div class="am-hd">
    <div style="width:27px;height:27px;background:rgba(227,0,0,.13);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--red);font-size:.72rem;flex-shrink:0"><i class="fa-solid <?= $bs['icon'] ?>"></i></div>
    <div style="flex:1;min-width:0">
      <div style="font-weight:700;font-size:.82rem;color:#ddd"><?= $bs['label'] ?></div>
      <div style="font-size:.67rem;color:#444;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $bs['desc'] ?></div>
    </div>
    <div id="btick-<?= $bslot ?>" style="color:#22c55e;font-size:.9rem;<?= $hasB?'':'display:none' ?>"><i class="fa-solid fa-circle-check"></i></div>
  </div>
  <div class="am-preview" id="bprev-<?= $bslot ?>" style="height:100px">
    <?php if($hasB): ?><img src="<?= $bUrl ?>" alt="" style="width:100%;height:100%;object-fit:cover"><?php else: ?><i class="fa-solid <?= $bs['icon'] ?> ph"></i><?php endif; ?>
  </div>
  <div class="am-body">
    <div class="am-tabs">
      <button class="ai-tab on" id="btab-<?= $bslot ?>-u" onclick="sBanTab('<?= $bslot ?>','u')"><i class="fa-solid fa-upload" style="margin-right:3px"></i>Upload</button>
      <button class="ai-tab"    id="btab-<?= $bslot ?>-l" onclick="sBanTab('<?= $bslot ?>','l')"><i class="fa-solid fa-link"   style="margin-right:3px"></i>URL</button>
      <button class="ai-tab"    id="btab-<?= $bslot ?>-c" onclick="sBanTab('<?= $bslot ?>','c');activeBanCard='<?= $bslot ?>'"><i class="fa-solid fa-paste" style="margin-right:3px"></i>Clipboard</button>
    </div>

    <!-- Upload tab -->
    <div id="bat-<?= $bslot ?>-u">
      <div class="dzone" id="bdz-<?= $bslot ?>"
           ondragover="event.preventDefault();this.classList.add('drag')"
           ondragleave="this.classList.remove('drag')"
           ondrop="onBanDrop(event,'<?= $bslot ?>')">
        <input type="file" accept="image/*" id="bfi-<?= $bslot ?>" onchange="loadBanFile('<?= $bslot ?>',this)">
        <div id="bdzc-<?= $bslot ?>">
          <div class="dz-ic"><i class="fa-solid fa-cloud-arrow-up"></i></div>
          <div style="color:#bbb;font-weight:600;font-size:.8rem;margin-bottom:.22rem">Kéo thả hoặc click chọn ảnh</div>
          <div style="color:#333;font-size:.7rem">JPG, PNG, WEBP — max 10MB</div>
        </div>
        <img id="bdp-<?= $bslot ?>" src="" alt="" style="display:none;width:100%;max-height:110px;object-fit:contain;border-radius:7px;position:relative;z-index:1;pointer-events:none">
      </div>
    </div>

    <!-- URL tab -->
    <div id="bat-<?= $bslot ?>-l" style="display:none">
      <div style="display:flex;gap:.35rem;margin-bottom:.35rem">
        <input type="url" id="bul-<?= $bslot ?>" class="form-inp" placeholder="https://..." style="font-size:.78rem"
               onkeydown="if(event.key==='Enter')prevBanUrl('<?= $bslot ?>')">
        <button onclick="prevBanUrl('<?= $bslot ?>')" class="btn-g" style="padding:.38rem .55rem;font-size:.77rem;flex-shrink:0"><i class="fa-solid fa-eye"></i></button>
      </div>
      <div style="background:#0f0f0f;border-radius:8px;height:90px;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #1a1a1a">
        <img id="bup-<?= $bslot ?>" style="max-width:100%;max-height:90px;object-fit:contain;display:none" alt="">
        <div id="buph-<?= $bslot ?>" style="color:#2a2a2a;text-align:center"><i class="fa-solid fa-image" style="font-size:1.5rem;display:block"></i></div>
      </div>
    </div>

    <!-- Clipboard tab -->
    <div id="bat-<?= $bslot ?>-c" style="display:none">
      <div style="background:#0f0f0f;border:2px dashed #1e1e1e;border-radius:10px;padding:.75rem;text-align:center;cursor:pointer;transition:border-color .2s"
           onclick="doBanPaste('<?= $bslot ?>')"
           onmouseover="this.style.borderColor='var(--red)'"
           onmouseout="this.style.borderColor='#1e1e1e'">
        <div class="dz-ic" style="margin:0 auto .35rem"><i class="fa-solid fa-paste"></i></div>
        <div style="color:#bbb;font-size:.78rem">Ctrl+V để dán ảnh</div>
      </div>
      <img id="bcp-<?= $bslot ?>" src="" alt="" style="display:none;width:100%;max-height:90px;object-fit:contain;border-radius:7px;margin-top:.4rem">
    </div>

    <!-- Metadata fields -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.3rem;margin-top:.5rem">
      <input id="bmeta-label-<?= $bslot ?>" class="form-inp" placeholder="Label (VD: HOT DEAL)" value="<?= htmlspecialchars($bcur['label']??$bDefLabel) ?>" style="font-size:.73rem">
      <input id="bmeta-link-<?= $bslot ?>"  class="form-inp" placeholder="Link URL" value="<?= htmlspecialchars($bcur['url']??$bDefUrl) ?>" style="font-size:.73rem">
    </div>
    <input id="bmeta-title-<?= $bslot ?>" class="form-inp" placeholder="Tiêu đề (VD: PC Gaming RTX 4090)" value="<?= htmlspecialchars(strip_tags($bcur['title']??$bDefTitle)) ?>" style="font-size:.73rem;width:100%;margin-top:.3rem;box-sizing:border-box">

    <!-- Save / Update buttons -->
    <button class="app-btn" id="bappbtn-<?= $bslot ?>" style="display:none;margin-top:.5rem" onclick="saveBanner('<?= $bslot ?>')">
      <i class="fa-solid fa-circle-check"></i> Lưu banner này
    </button>
    <?php if($hasB): ?>
    <button class="app-btn" style="margin-top:.4rem;background:linear-gradient(135deg,#1d4ed8,#1e40af)" onclick="saveBanner('<?= $bslot ?>',true)">
      <i class="fa-solid fa-rotate"></i> Cập nhật text/link
    </button>
    <?php endif; ?>
    <button id="brmvbtn-<?= $bslot ?>" onclick="removeBanner('<?= $bslot ?>')" style="width:100%;background:none;border:1px solid #2a2a2a;color:#555;padding:.38rem;border-radius:8px;font-size:.73rem;cursor:pointer;margin-top:.3rem;display:<?= $hasB?'block':'none' ?>;font-family:inherit;transition:all .2s" onmouseover="this.style.borderColor='#ef4444';this.style.color='#ef4444'" onmouseout="this.style.borderColor='#2a2a2a';this.style.color='#555'">
      <i class="fa-solid fa-trash"></i> Xóa banner
    </button>
    <div id="bfn-<?= $bslot ?>" style="margin-top:.35rem;font-size:.68rem;<?= $hasB?'color:#22c55e':'color:#444' ?>">
      <?php if($hasB): ?><i class="fa-solid fa-check"></i> <?= htmlspecialchars(basename(parse_url($bcur['img'],PHP_URL_PATH))) ?><?php else: ?>— chưa có banner —<?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- ── Demo fullscreen overlay ─────────────────────────────────── -->
<div id="demoOvl">
  <button class="demo-x" onclick="closeDemo()"><i class="fa-solid fa-xmark"></i></button>
  <div id="demoCanvas"></div>
  <div class="demo-bar">
    <span style="color:#777;font-size:.73rem;white-space:nowrap"><i class="fa-solid fa-film"></i></span>
    <input type="range" id="demoSlider" min="0" max="<?= $total-1 ?>" step="1" value="0" oninput="gotoPhase(+this.value)">
    <span id="phLbl" style="color:#ccc;font-size:.75rem;font-weight:600;min-width:100px;text-align:center"></span>
    <button class="btn-g" id="btnAuto" onclick="toggleAuto()" style="font-size:.72rem;padding:.35rem .75rem;flex-shrink:0"><i class="fa-solid fa-circle-play"></i> Auto</button>
    <button class="btn-g" onclick="closeDemo()" style="font-size:.72rem;padding:.35rem .75rem;flex-shrink:0"><i class="fa-solid fa-xmark"></i> Đóng</button>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
const BASE   = '<?= APP_URL ?>';
const KEYS   = <?= json_encode(array_column($components,'key')) ?>;
const LABELS = <?= json_encode(array_column($components,'label')) ?>;
let approved = <?= json_encode((object)$approved) ?>;

// ── Per-card state ────────────────────────────────────────────────
const CS = {};
KEYS.forEach(function(k){ CS[k] = { b64:'', mime:'image/jpeg', url:'' }; });
let activeCard = null; // for global paste listener
let curPhase = 0, autoTimer = null;

// ── Tab switching ─────────────────────────────────────────────────
function sTab(key, tab) {
  ['u','l','c','s','n'].forEach(function(t){
    document.getElementById('at-'+key+'-'+t).style.display = t===tab ? 'block' : 'none';
    document.getElementById('tab-'+key+'-'+t).classList.toggle('on', t===tab);
  });
  if (tab === 'c') activeCard = key;
  if (tab !== 'n') hideSuggest(key);
}

// ── File upload ───────────────────────────────────────────────────
function loadFile(key, inp) {
  if (!inp.files || !inp.files[0]) return;
  var f = inp.files[0];
  if (f.size > 10*1024*1024) { toast('File quá lớn (max 10MB)', 'err'); return; }
  CS[key].mime = f.type || 'image/jpeg';
  var r = new FileReader();
  r.onload = function(e) {
    CS[key].b64 = e.target.result; CS[key].url = '';
    var p = document.getElementById('dp-'+key); p.src = e.target.result; p.style.display = 'block';
    document.getElementById('dzc-'+key).style.display = 'none';
    document.getElementById('dzi-'+key).style.display = 'flex';
    document.getElementById('dfn-'+key).textContent = f.name;
    document.getElementById('dsz-'+key).textContent = '('+Math.round(f.size/1024)+'KB)';
    showAppBtn(key);
    toast('Ảnh sẵn sàng — nhấn Duyệt!', 'ok');
  };
  r.readAsDataURL(f);
}
function onDropCard(e, key) {
  e.preventDefault();
  document.getElementById('dz-'+key).classList.remove('drag');
  var f = e.dataTransfer.files[0];
  if (f && f.type.startsWith('image/')) {
    var dt = new DataTransfer(); dt.items.add(f);
    var inp = document.getElementById('fi-'+key); inp.files = dt.files; loadFile(key, inp);
  }
}

// ── URL preview ───────────────────────────────────────────────────
function prevUrl(key) {
  var u = document.getElementById('ul-'+key).value.trim(); if (!u) return;
  CS[key].url = u; CS[key].b64 = '';
  var img = document.getElementById('up-'+key); img.src = u; img.style.display = 'block';
  document.getElementById('uph-'+key).style.display = 'none';
  showAppBtn(key);
  toast('URL sẵn sàng — nhấn Duyệt!', 'ok');
}

// ── Clipboard ─────────────────────────────────────────────────────
document.addEventListener('paste', function(e) {
  var tag = (document.activeElement||{}).tagName||'';
  if (tag==='INPUT'||tag==='TEXTAREA') return;
  if (!activeCard) return;
  var key = activeCard;
  var items = (e.clipboardData||{}).items||[];
  for (var i=0; i<items.length; i++) {
    if (items[i].type.indexOf('image') !== -1) {
      var blob = items[i].getAsFile();
      CS[key].mime = blob.type || 'image/png';
      var r = new FileReader();
      r.onload = function(ev) {
        CS[key].b64 = ev.target.result; CS[key].url = '';
        var p = document.getElementById('cp-'+key); p.src = ev.target.result; p.style.display = 'block';
        sTab(key,'c'); showAppBtn(key);
        toast('Đã dán ảnh — nhấn Duyệt!', 'ok');
      };
      r.readAsDataURL(blob); break;
    }
  }
});
function doPaste(key) {
  activeCard = key;
  if (navigator.clipboard && navigator.clipboard.read) {
    navigator.clipboard.read().then(function(items) {
      for (var idx=0; idx<items.length; idx++) {
        var item = items[idx];
        for (var ti=0; ti<item.types.length; ti++) {
          var type = item.types[ti];
          if (type.startsWith('image/')) {
            item.getType(type).then(function(blob) {
              CS[key].mime = blob.type;
              var r = new FileReader();
              r.onload = function(ev) {
                CS[key].b64 = ev.target.result; CS[key].url = '';
                var p = document.getElementById('cp-'+key); p.src = ev.target.result; p.style.display = 'block';
                showAppBtn(key); toast('Đã dán!', 'ok');
              };
              r.readAsDataURL(blob);
            }); return;
          }
        }
      }
    }).catch(function(){ toast('Dùng Ctrl+V để dán ảnh', 'ok'); });
  } else toast('Dùng Ctrl+V để dán ảnh', 'ok');
}

// ── Show/hide approve button ──────────────────────────────────────
function showAppBtn(key) {
  document.getElementById('appbtn-'+key).style.display = 'flex';
}

// ── Approve card (upload / url / clipboard) ───────────────────────
function approveCard(key) {
  var s = CS[key];
  if (!s.b64 && !s.url) { toast('Chưa có ảnh để duyệt', 'err'); return; }
  if (s.b64) {
    saveApproved(key, { component: key, image_b64: s.b64, image_mime: s.mime });
    return;
  }
  // URL tab — fetch as base64 in browser first to avoid hotlink blocks
  toast('Đang tải ảnh...', '');
  fetch(s.url)
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.blob();
    })
    .then(function(blob) {
      var reader = new FileReader();
      reader.onload = function(e) {
        saveApproved(key, { component: key, image_b64: e.target.result, image_mime: blob.type || 'image/jpeg' });
      };
      reader.readAsDataURL(blob);
    })
    .catch(function(err) {
      // Browser fetch failed (CORS) — fall back to server-side download
      saveApproved(key, { component: key, url: s.url });
    });
}

// ── Approve from search result ────────────────────────────────────
function approveAsset(key, url) {
  toast('Đang tải ảnh...', '');
  // Fetch as base64 in browser to avoid server-side hotlink blocks
  fetch(url)
    .then(function(r){ return r.blob(); })
    .then(function(blob){
      var reader = new FileReader();
      reader.onload = function(e){
        saveApproved(key, { component: key, image_b64: e.target.result, image_mime: blob.type || 'image/jpeg' });
      };
      reader.readAsDataURL(blob);
    })
    .catch(function(){
      // Fallback: let server download the URL
      saveApproved(key, { component: key, url: url });
    });
}

// ── Core save ────────────────────────────────────────────────────
async function saveApproved(key, payload) {
  toast('Đang lưu ảnh...', '');
  try {
    var r = await fetch(BASE+'/api/admin/approve-asset', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    var d = await r.json();
    if (!d.ok) { toast(d.message||'Lỗi lưu ảnh', 'err'); return; }
    approved[key] = { url: d.url, filename: d.filename };
    var isPng = d.filename && d.filename.match(/\.png$/i);
    var prevBg = isPng ? 'repeating-conic-gradient(#2a2a2a 0% 25%,#1a1a1a 0% 50%) 0 0/14px 14px' : '';
    document.getElementById('prev-'+key).style.background = prevBg;
    document.getElementById('prev-'+key).innerHTML = '<img src="'+d.url+'" alt="" style="width:100%;height:100%;object-fit:contain">';
    document.getElementById('tick-'+key).style.display = '';
    document.getElementById('card-'+key).classList.add('done');
    document.getElementById('fn-'+key).innerHTML = '<i class="fa-solid fa-check"></i> '+d.filename;
    document.getElementById('fn-'+key).style.color = '#22c55e';
    document.getElementById('appbtn-'+key).style.display = 'none';
    var bgWrap = document.getElementById('bgrm-wrap-'+key);
    if (bgWrap) bgWrap.style.display = 'block';
    // reset state
    CS[key].b64 = ''; CS[key].url = '';
    updateProg();
    toast('✓ Đã duyệt: '+LABELS[KEYS.indexOf(key)], 'ok');
  } catch(err) { toast('Lỗi: '+err.message, 'err'); }
}

// ── Search ────────────────────────────────────────────────────────
async function doSearch(key) {
  var q = document.getElementById('q-'+key).value.trim();
  if (!q) return;
  var res = document.getElementById('res-'+key);
  var ld  = document.getElementById('ld-'+key);
  res.style.display = 'none'; ld.style.display = 'block';
  try {
    var r = await fetch(BASE+'/api/admin/search-images?q='+encodeURIComponent(q)+'&component='+encodeURIComponent(key));
    var d = await r.json();
    ld.style.display = 'none';
    if (!d.ok || !d.images || !d.images.length) { toast(d.message||'Không tìm được ảnh', 'err'); return; }
    res.innerHTML = d.images.map(function(img){
      return '<div class="sr-th" onclick="approveAsset(\''+key+'\',\''+esc(img.url)+'\')" title="'+esc(img.title||'')+'"><img src="'+esc(img.thumb)+'" loading="lazy" onerror="this.src=\''+esc(img.url)+'\'"></div>';
    }).join('');
    res.style.display = 'grid';
  } catch(err) { ld.style.display='none'; toast('Lỗi kết nối', 'err'); }
}

// ── Progress ──────────────────────────────────────────────────────
function updateProg() {
  var n   = KEYS.filter(function(k){ return approved[k]&&approved[k].url; }).length;
  var pct = Math.round(n/KEYS.length*100);
  document.getElementById('jsCount').textContent = n;
  document.getElementById('jsProg').style.width  = pct+'%';
  document.getElementById('jsPct').textContent   = pct+'%';
  var ok = n > 0;
  document.getElementById('btnDemo').disabled   = !ok;
  document.getElementById('btnDeploy').disabled = !ok;
}

// ── Deploy ────────────────────────────────────────────────────────
async function deployHome() {
  if (!confirm('Áp dụng GSAP animation vào trang chủ?\nBản cũ sẽ được lưu backup tự động.')) return;
  var st = document.getElementById('depStatus');
  st.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang triển khai...'; st.style.color='#888';
  try {
    var r = await fetch(BASE+'/api/admin/deploy-homepage', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({assets: approved})
    });
    var d = await r.json();
    if (d.ok) {
      st.innerHTML = '<i class="fa-solid fa-circle-check"></i> Trang chủ đã được cập nhật!'; st.style.color='#22c55e';
      document.getElementById('btnRollback').style.display = 'inline-flex';
      toast('🚀 Trang chủ đã được cập nhật!', 'ok');
    } else {
      st.innerHTML = d.message||'Lỗi'; st.style.color='#ef4444';
      toast(d.message||'Lỗi triển khai', 'err');
    }
  } catch(err) { st.innerHTML='Lỗi kết nối'; st.style.color='#ef4444'; toast('Lỗi kết nối','err'); }
}

// ── Rollback ──────────────────────────────────────────────────────
async function rollbackHome() {
  if (!confirm('Khôi phục trang chủ về phiên bản cũ?')) return;
  try {
    var r = await fetch(BASE+'/api/admin/rollback-homepage',{method:'POST'});
    var d = await r.json();
    toast(d.ok ? '✓ Đã khôi phục trang chủ cũ' : (d.message||'Lỗi'), d.ok?'ok':'err');
    if (d.ok) document.getElementById('btnRollback').style.display='none';
  } catch(err){ toast('Lỗi kết nối','err'); }
}

// ── Demo ──────────────────────────────────────────────────────────
function openDemo() {
  buildPhases();
  document.getElementById('demoOvl').classList.add('show');
  gotoPhase(0);
}
function closeDemo() {
  document.getElementById('demoOvl').classList.remove('show');
  if (autoTimer){ clearInterval(autoTimer); autoTimer=null; resetAutoBtn(); }
}
function buildPhases() {
  var canvas = document.getElementById('demoCanvas');
  canvas.innerHTML = '';
  KEYS.forEach(function(key,i){
    var url = (approved[key]||{}).url || '';
    var div = document.createElement('div');
    div.className='d-phase'; div.id='ph-'+i;
    var dots = KEYS.map(function(_,j){ return '<div class="ph-dot'+(j===i?' on':'')+'" onclick="gotoPhase('+j+')"></div>'; }).join('');
    div.innerHTML =
      '<div class="d-bg" style="background-image:url(\''+url+'\')"></div>'+
      '<div class="d-fog"></div>'+
      '<div class="d-cnt">'+
        '<div style="font-size:.63rem;color:rgba(227,0,0,.85);letter-spacing:3px;font-weight:700;text-transform:uppercase;margin-bottom:.55rem" class="d-tag">ASSET '+(i+1)+' / '+KEYS.length+'</div>'+
        '<h2 class="d-ttl">'+LABELS[i]+'</h2>'+
        '<div class="d-sub" style="font-size:.78rem;margin-bottom:1rem;color:'+(url?'rgba(255,255,255,.45)':'rgba(255,255,255,.2)')+'">'+
          (url ? ('✓ Đã duyệt — '+(approved[key].filename||'')) : '— Chưa có ảnh —')+
        '</div>'+
        (url ? '<div style="width:min(340px,80vw);height:170px;border-radius:10px;overflow:hidden;margin:0 auto .8rem;border:1px solid rgba(255,255,255,.1)"><img src="'+url+'" style="width:100%;height:100%;object-fit:cover"></div>' : '')+
        '<div class="ph-dots">'+dots+'</div>'+
      '</div>';
    canvas.appendChild(div);
  });
}
function gotoPhase(idx) {
  document.querySelectorAll('.d-phase').forEach(function(p,i){
    var active = i===idx;
    p.classList.toggle('cur',active);
    if(active && typeof gsap!=='undefined'){
      gsap.fromTo(p,{opacity:0,scale:1.04},{opacity:1,scale:1,duration:.65,ease:'power2.out'});
      gsap.fromTo(p.querySelector('.d-bg'),{scale:1.12},{scale:1,duration:1.1,ease:'power2.out'});
      var els = p.querySelectorAll('.d-tag,.d-ttl,.d-sub,.d-img-prev,.ph-dots');
      gsap.fromTo(els,{opacity:0,y:28},{opacity:1,y:0,duration:.55,stagger:.08,ease:'power3.out',delay:.15});
    }
  });
  curPhase=idx;
  document.getElementById('demoSlider').value=idx;
  document.getElementById('phLbl').textContent=LABELS[idx]||'';
  document.querySelectorAll('.ph-dot').forEach(function(d,i){ d.classList.toggle('on', i%KEYS.length===idx); });
}
function toggleAuto(){
  if(autoTimer){ clearInterval(autoTimer); autoTimer=null; resetAutoBtn(); return; }
  document.getElementById('btnAuto').innerHTML='<i class="fa-solid fa-circle-pause"></i> Dừng';
  autoTimer=setInterval(function(){ gotoPhase((curPhase+1)%KEYS.length); }, 2800);
}
function resetAutoBtn(){ document.getElementById('btnAuto').innerHTML='<i class="fa-solid fa-circle-play"></i> Auto'; }
document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeDemo(); });

// ── Product name autocomplete ─────────────────────────────────────
var _sugTimer = {};
function fetchSuggest(key, val) {
  clearTimeout(_sugTimer[key]);
  var sug = document.getElementById('sug-'+key);
  if (!val || val.length < 2) { sug.style.display='none'; return; }
  _sugTimer[key] = setTimeout(async function() {
    try {
      var r = await fetch(BASE+'/api/admin/suggest-products?q='+encodeURIComponent(val));
      var items = await r.json();
      if (!items || !items.length) { sug.style.display='none'; return; }
      sug.innerHTML = items.map(function(name) {
        return '<div class="ac-item" onclick="pickSuggest(\''+key+'\',\''+esc(name)+'\')">'+escHtml(name)+'</div>';
      }).join('');
      sug.style.display = 'block';
    } catch(e) { sug.style.display='none'; }
  }, 250);
}
function pickSuggest(key, name) {
  document.getElementById('ni-'+key).value = name;
  hideSuggest(key);
}
function hideSuggest(key) {
  var el = document.getElementById('sug-'+key);
  if (el) el.style.display = 'none';
}

// ── Search by product name (uses layout_bottom imsOpen modal) ─────
function searchByName(key) {
  var name = document.getElementById('ni-'+key).value.trim();
  if (!name) { toast('Nhập tên sản phẩm trước', 'err'); return; }
  hideSuggest(key);
  imsOpen(function(url, thumb, title, thumbEl) {
    saveApproved(key, { component: key, url: url });
    imsClose();
  }, name);
}

// ── AI suggest product name → calls /api/ai/generate-from-name ────
async function aiSuggestName(key) {
  var name = document.getElementById('ni-'+key).value.trim();
  if (!name) { toast('Nhập tên sản phẩm cần gợi ý trước', 'err'); return; }
  var btn = document.getElementById('aisugg-'+key);
  var st  = document.getElementById('nist-'+key);
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
  st.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i> AI đang tìm…';
  try {
    var r = await fetch(BASE+'/api/ai/generate-from-name', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ product_name: name })
    });
    var d = await r.json();
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Gợi ý AI';
    if (d.success && d.data && d.data.name) {
      var suggested = d.data.name;
      document.getElementById('ni-'+key).value = suggested;
      st.innerHTML = '<span style="color:#22c55e"><i class="fa-solid fa-check"></i> '+escHtml(suggested)+'</span>';
      toast('AI gợi ý: '+suggested, 'ok');
    } else {
      st.innerHTML = '<span style="color:#ef4444">'+(d.message||'Không tìm được tên')+'</span>';
    }
  } catch(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Gợi ý AI';
    st.innerHTML  = '<span style="color:#ef4444">Lỗi kết nối</span>';
  }
}

// Close all autocomplete dropdowns when clicking outside
document.addEventListener('click', function(e) {
  if (!e.target.closest('.ac-drop') && !e.target.closest('input')) {
    KEYS.forEach(function(k){ hideSuggest(k); });
  }
});

// ── Banner management ─────────────────────────────────────────────
var BS = { 'main-banner-1':{b64:'',mime:'image/jpeg',url:'',file:null}, 'main-banner-2':{b64:'',mime:'image/jpeg',url:'',file:null}, 'main-banner-3':{b64:'',mime:'image/jpeg',url:'',file:null}, 'side-banner-1':{b64:'',mime:'image/jpeg',url:'',file:null}, 'side-banner-2':{b64:'',mime:'image/jpeg',url:'',file:null} };
var activeBanCard = null;

function sBanTab(slot, tab) {
  ['u','l','c'].forEach(function(t){
    document.getElementById('bat-'+slot+'-'+t).style.display = t===tab ? 'block' : 'none';
    document.getElementById('btab-'+slot+'-'+t).classList.toggle('on', t===tab);
  });
  if (tab === 'c') activeBanCard = slot;
}

function loadBanFile(slot, inp) {
  if (!inp.files || !inp.files[0]) return;
  var f = inp.files[0];
  if (f.size > 10*1024*1024) { toast('File quá lớn (max 10MB)', 'err'); return; }
  BS[slot].file = f; BS[slot].mime = f.type || 'image/jpeg'; BS[slot].b64 = ''; BS[slot].url = '';
  var r = new FileReader();
  r.onload = function(e) {
    var p = document.getElementById('bdp-'+slot); p.src = e.target.result; p.style.display = 'block';
    document.getElementById('bdzc-'+slot).style.display = 'none';
    showBanBtn(slot);
    toast('Ảnh sẵn sàng — nhấn Lưu!', 'ok');
  };
  r.readAsDataURL(f);
}

function onBanDrop(e, slot) {
  e.preventDefault();
  document.getElementById('bdz-'+slot).classList.remove('drag');
  var f = e.dataTransfer.files[0];
  if (f && f.type.startsWith('image/')) {
    var dt = new DataTransfer(); dt.items.add(f);
    var inp = document.getElementById('bfi-'+slot); inp.files = dt.files; loadBanFile(slot, inp);
  }
}

function prevBanUrl(slot) {
  var u = document.getElementById('bul-'+slot).value.trim(); if (!u) return;
  BS[slot].url = u; BS[slot].b64 = ''; BS[slot].file = null;
  var img = document.getElementById('bup-'+slot); img.src = u; img.style.display = 'block';
  document.getElementById('buph-'+slot).style.display = 'none';
  showBanBtn(slot);
  toast('URL sẵn sàng — nhấn Lưu!', 'ok');
}

function doBanPaste(slot) {
  activeBanCard = slot;
  if (navigator.clipboard && navigator.clipboard.read) {
    navigator.clipboard.read().then(function(items) {
      for (var idx=0; idx<items.length; idx++) {
        var item = items[idx];
        for (var ti=0; ti<item.types.length; ti++) {
          var type = item.types[ti];
          if (type.startsWith('image/')) {
            item.getType(type).then(function(blob) {
              BS[slot].mime = blob.type; BS[slot].file = null; BS[slot].url = '';
              var r = new FileReader();
              r.onload = function(ev) {
                BS[slot].b64 = ev.target.result;
                var p = document.getElementById('bcp-'+slot); p.src = ev.target.result; p.style.display = 'block';
                showBanBtn(slot); toast('Đã dán ảnh — nhấn Lưu!', 'ok');
              };
              r.readAsDataURL(blob);
            }); return;
          }
        }
      }
    }).catch(function(){ toast('Dùng Ctrl+V để dán ảnh', 'ok'); });
  } else toast('Dùng Ctrl+V để dán ảnh', 'ok');
}

// Listen for Ctrl+V paste when clipboard tab is active
document.addEventListener('paste', function(e) {
  if (!activeBanCard) return;
  var tag = (document.activeElement||{}).tagName||'';
  if (tag==='INPUT'||tag==='TEXTAREA') return;
  var slot = activeBanCard;
  // Only handle if banner tab-c is active for this slot
  var tabEl = document.getElementById('btab-'+slot+'-c');
  if (!tabEl || !tabEl.classList.contains('on')) return;
  var items = (e.clipboardData||{}).items||[];
  for (var i=0; i<items.length; i++) {
    if (items[i].type.indexOf('image') !== -1) {
      var blob = items[i].getAsFile();
      BS[slot].mime = blob.type || 'image/png'; BS[slot].file = null; BS[slot].url = '';
      var rdr = new FileReader();
      rdr.onload = function(ev) {
        BS[slot].b64 = ev.target.result;
        var p = document.getElementById('bcp-'+slot); p.src = ev.target.result; p.style.display = 'block';
        showBanBtn(slot); toast('Đã dán — nhấn Lưu!', 'ok');
      };
      rdr.readAsDataURL(blob); break;
    }
  }
});

function showBanBtn(slot) {
  document.getElementById('bappbtn-'+slot).style.display = 'flex';
}

// ── Sync test modal ──────────────────────────────────────────────
var _BANNERS = <?= json_encode($banners ?: new stdClass(), JSON_UNESCAPED_UNICODE) ?>;

function syncTest() {
  fetch(BASE+'/admin/banner-debug')
    .then(function(r){ return r.text(); })
    .then(function(t){
      var bj;
      try { bj = JSON.parse(t); } catch(e) { toast('banners.json không đọc được: '+t.slice(0,80),'err'); return; }
      var main = (bj.main||[]).filter(function(m){ return m && m.img; });
      var side = (bj.side||[]).filter(function(s){ return s && s.img; });
      var html = '<div style="position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem" onclick="if(event.target===this)this.remove()">';
      html += '<div style="background:#111;border-radius:12px;padding:1.25rem;max-width:700px;width:100%;max-height:90vh;overflow-y:auto">';
      html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem"><b style="color:#fff;font-size:.9rem"><i class="fa-solid fa-vials" style="color:var(--red);margin-right:.4rem"></i>Test đồng bộ — homepage sẽ hiển thị</b><button onclick="this.closest(\'[style*=fixed]\').remove()" style="background:none;border:1px solid #333;color:#888;border-radius:6px;padding:.2rem .55rem;cursor:pointer;font-size:.78rem">✕</button></div>';
      if (!main.length && !side.length) {
        html += '<p style="color:#555;font-size:.82rem;text-align:center;padding:1.5rem">banners.json trống — homepage dùng ảnh mặc định (approved.json / local files)</p>';
      } else {
        if (main.length) {
          html += '<div style="font-size:.73rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Main slides ('+main.length+')</div>';
          html += '<div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.8rem">';
          main.forEach(function(m,i){
            html += '<div style="width:180px;background:#0a0a0a;border-radius:7px;overflow:hidden;border:1px solid #1e1e1e">';
            html += '<div style="height:80px;background:url(\''+esc(m.img)+'\') center/cover no-repeat;position:relative">';
            html += '<span style="position:absolute;bottom:3px;left:5px;background:var(--red);color:#fff;font-size:.55rem;font-weight:800;padding:1px 5px;border-radius:3px">'+escHtml(m.label||'')+'</span></div>';
            html += '<div style="padding:.3rem .45rem;font-size:.65rem;color:#888;line-height:1.4"><div style="color:#ccc;font-weight:600">Slide '+(i+1)+'</div><div>'+escHtml(m.title||'')+'</div><div style="color:#555;margin-top:1px">→ '+escHtml(m.url||'(no link)')+'</div></div></div>';
          });
          html += '</div>';
        }
        if (side.length) {
          html += '<div style="font-size:.73rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Side banners ('+side.length+')</div>';
          html += '<div style="display:flex;gap:.4rem;flex-wrap:wrap">';
          side.forEach(function(s,i){
            html += '<div style="width:180px;background:#0a0a0a;border-radius:7px;overflow:hidden;border:1px solid #1e1e1e">';
            html += '<div style="height:60px;background:url(\''+esc(s.img)+'\') center/cover no-repeat;position:relative">';
            html += '<span style="position:absolute;bottom:3px;left:5px;color:var(--red);font-size:.55rem;font-weight:800">'+escHtml(s.label||'')+'</span></div>';
            html += '<div style="padding:.3rem .45rem;font-size:.65rem;color:#888"><div style="color:#ccc;font-weight:600">Side '+(i+1)+'</div><div>'+escHtml(s.title||'')+'</div></div></div>';
          });
          html += '</div>';
        }
      }
      html += '<div style="margin-top:.8rem;padding-top:.7rem;border-top:1px solid #1c1c1c;font-size:.67rem;color:#333">banners.json path: <?= addslashes($_bfFile) ?></div>';
      html += '</div></div>';
      document.body.insertAdjacentHTML('beforeend', html);
    })
    .catch(function(e){ toast('Lỗi kết nối: '+e.message,'err'); });
}

async function saveBanner(slot, metaOnly) {
  var s = BS[slot];
  var label = (document.getElementById('bmeta-label-'+slot)||{}).value || '';
  var title = (document.getElementById('bmeta-title-'+slot)||{}).value || '';
  var link  = (document.getElementById('bmeta-link-'+slot)||{}).value  || '';
  console.log('[bannerSave] slot='+slot, {metaOnly:!!metaOnly, hasFile:!!s.file, hasB64:!!s.b64, hasUrl:!!s.url, label:label, title:title, link:link});
  if (!metaOnly && !s.file && !s.b64 && !s.url) { toast('Chưa có ảnh để lưu', 'err'); return; }
  toast('Đang lưu banner...', '');
  try {
    var fd = new FormData();
    fd.append('slot', slot);
    fd.append('label', label);
    fd.append('title', title);
    fd.append('link',  link);
    if (metaOnly) {
      fd.append('meta_only', '1'); // server reuses existing img, only updates metadata
    } else {
      if (s.file) { fd.append('file', s.file); }
      else if (s.b64) { fd.append('image_b64', s.b64); fd.append('image_mime', s.mime); }
      else if (s.url) { fd.append('url', s.url); }
    }
    var r = await fetch(BASE+'/admin/banner-save', { method:'POST', body: fd });
    var rawText = await r.text();
    console.log('[bannerSave] server response:', rawText.slice(0, 500));
    var d;
    try { d = JSON.parse(rawText); }
    catch(e) { toast('Response không phải JSON: '+rawText.slice(0,120), 'err'); console.error('[bannerSave] raw:', rawText); return; }
    console.log('[bannerSave] parsed:', d);
    if (!d.ok) { toast(d.message||'Lỗi lưu banner', 'err'); return; }
    // Confirm write: show path + bytes
    var fnEl = document.getElementById('bfn-'+slot);
    if (!metaOnly) {
      // Cache-bust the preview
      var previewUrl = d.url + (d.url.includes('?') ? '&' : '?') + 'cb=' + Date.now();
      document.getElementById('bprev-'+slot).innerHTML = '<img src="'+previewUrl+'" alt="" style="width:100%;height:100%;object-fit:cover" onerror="this.style.border=\'2px solid #ef4444\'">';
      document.getElementById('btick-'+slot).style.display = '';
      document.getElementById('bcard-'+slot).classList.add('done');
      document.getElementById('bappbtn-'+slot).style.display = 'none';
      document.getElementById('brmvbtn-'+slot).style.display = 'block';
    }
    // Show written path for confirmation
    if (fnEl) {
      var pathInfo = d.path ? d.path.replace(/\\/g,'/').split('/storage/').pop() : '';
      var sizeInfo = d.bytes ? ' ('+d.bytes+' bytes)' : '';
      fnEl.innerHTML = '<i class="fa-solid fa-check" style="color:#22c55e"></i> storage/'+escHtml(pathInfo)+sizeInfo;
      fnEl.style.color = '#22c55e';
    }
    BS[slot].b64 = ''; BS[slot].url = ''; BS[slot].file = null;
    toast(metaOnly ? '✓ Text/link đã cập nhật!' : '✓ Đã lưu — '+d.bytes+' bytes', 'ok');
  } catch(err) { toast('Lỗi: '+err.message, 'err'); console.error(err); }
}

async function removeBanner(slot) {
  if (!confirm('Xóa banner này?')) return;
  try {
    var fd = new FormData(); fd.append('slot', slot);
    var r = await fetch(BASE+'/admin/banner-remove', { method:'POST', body: fd });
    var d = await r.json();
    if (!d.ok) { toast('Lỗi xóa banner', 'err'); return; }
    document.getElementById('bprev-'+slot).innerHTML = '<i class="fa-solid fa-image ph"></i>';
    document.getElementById('btick-'+slot).style.display = 'none';
    document.getElementById('bcard-'+slot).classList.remove('done');
    document.getElementById('bfn-'+slot).textContent = '— chưa có banner —';
    document.getElementById('bfn-'+slot).style.color = '#444';
    document.getElementById('brmvbtn-'+slot).style.display = 'none';
    toast('Đã xóa banner', 'ok');
  } catch(err) { toast('Lỗi: '+err.message, 'err'); }
}

// ── Helpers ───────────────────────────────────────────────────────
function esc(s){ return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
function escHtml(s){ var d=document.createElement('div');d.textContent=s;return d.innerHTML; }
function toast(msg,type){
  var c=document.getElementById('toast-c');
  var t=document.createElement('div'); t.className='toast '+(type||''); t.textContent=msg;
  c.appendChild(t); setTimeout(function(){t.remove();},3500);
}

// ── Background removal (@imgly/background-removal — runs in browser, no API key) ──
var _bgKey = null, _bgBlob = null;

function removeBg(key) {
  _bgKey = key; _bgBlob = null;
  var url = (approved[key]||{}).url;
  if (!url) { toast('Chưa có ảnh đã duyệt', 'err'); return; }
  var modal = document.getElementById('bgModal');
  document.getElementById('bgBefore').src = url;
  document.getElementById('bgAfterWrap').style.background = 'repeating-conic-gradient(#2a2a2a 0% 25%,#1a1a1a 0% 50%) 0 0 / 16px 16px';
  var ctx2 = document.getElementById('bgAfterCanvas').getContext('2d');
  ctx2.clearRect(0,0,9999,9999);
  document.getElementById('bgStatus').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tải thư viện AI... (lần đầu ~5s)';
  document.getElementById('bgSaveBtn').style.display = 'none';
  modal.style.display = 'flex';

  if (window._imglyRemoveBg) {
    _runRemoveBg(url);
  } else {
    // Dynamic ESM import — works in modern browsers without type="module"
    import('https://esm.sh/@imgly/background-removal@1.4.5').then(function(mod) {
      window._imglyRemoveBg = mod.removeBackground || mod.default;
      _runRemoveBg(url);
    }).catch(function(err) {
      document.getElementById('bgStatus').innerHTML = '<span style="color:#ef4444">Lỗi tải thư viện: '+escHtml(err.message)+'</span>';
    });
  }
}

async function _runRemoveBg(url) {
  var st = document.getElementById('bgStatus');
  st.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> AI đang xử lý... (10–30 giây lần đầu, model tải về ~100MB)';
  try {
    // Fetch image as blob to avoid CORS issues
    var resp = await fetch(url);
    var srcBlob = await resp.blob();
    var objUrl = URL.createObjectURL(srcBlob);

    _bgBlob = await window._imglyRemoveBg(objUrl, {
      output: { format: 'image/png', quality: 0.9 },
      debug: false,
      progress: function(k, current, total) {
        if (total > 0) st.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Xử lý... ' + Math.round(current/total*100) + '%';
      }
    });
    URL.revokeObjectURL(objUrl);

    // Draw result on canvas
    var cv = document.getElementById('bgAfterCanvas');
    var ctx2 = cv.getContext('2d');
    var img2 = new Image();
    img2.onload = function() {
      cv.width = img2.naturalWidth; cv.height = img2.naturalHeight;
      ctx2.clearRect(0,0,cv.width,cv.height);
      ctx2.drawImage(img2, 0, 0);
    };
    img2.src = URL.createObjectURL(_bgBlob);

    st.innerHTML = '<span style="color:#22c55e"><i class="fa-solid fa-circle-check"></i> Hoàn tất! Kiểm tra kết quả bên phải rồi lưu.</span>';
    document.getElementById('bgSaveBtn').style.display = 'flex';
  } catch(err) {
    st.innerHTML = '<span style="color:#ef4444"><i class="fa-solid fa-triangle-exclamation"></i> Lỗi: '+escHtml(err.message)+'</span>';
    toast('Lỗi tách nền: '+err.message, 'err');
  }
}

function closeBgModal() {
  document.getElementById('bgModal').style.display = 'none';
  _bgBlob = null; _bgKey = null;
}

async function saveBgResult() {
  if (!_bgBlob || !_bgKey) return;
  var btn = document.getElementById('bgSaveBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
  var reader = new FileReader();
  reader.onload = async function(e) {
    try {
      await saveApproved(_bgKey, { component: _bgKey, image_b64: e.target.result, image_mime: 'image/png' });
      closeBgModal();
    } catch(err) { toast('Lỗi lưu: '+err.message, 'err'); }
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Lưu PNG trong suốt';
  };
  reader.readAsDataURL(_bgBlob);
}
</script>

<!-- ── Background removal modal ────────────────────────────────── -->
<div id="bgModal" style="position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:10000;display:none;align-items:center;justify-content:center;padding:1rem">
  <div style="background:#1a1a1a;border:1px solid #2a2a2a;border-radius:16px;padding:1.5rem;max-width:680px;width:100%;position:relative;box-shadow:0 24px 64px rgba(0,0,0,.6)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem">
      <div>
        <div style="font-size:.75rem;color:var(--red);font-weight:700;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:.15rem"><i class="fa-solid fa-scissors"></i> Tách nền PNG</div>
        <div style="font-size:.72rem;color:#444">AI xử lý ngay trên trình duyệt — không gửi ảnh lên server bên ngoài</div>
      </div>
      <button onclick="closeBgModal()" style="background:rgba(255,255,255,.06);border:none;color:#888;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:.85rem;display:flex;align-items:center;justify-content:center"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:.9rem">
      <div>
        <div style="font-size:.68rem;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Ảnh gốc</div>
        <div style="background:#111;border-radius:10px;overflow:hidden;aspect-ratio:1;display:flex;align-items:center;justify-content:center">
          <img id="bgBefore" style="max-width:100%;max-height:240px;object-fit:contain" alt="">
        </div>
      </div>
      <div>
        <div style="font-size:.68rem;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Sau khi tách nền</div>
        <div id="bgAfterWrap" style="border-radius:10px;overflow:hidden;aspect-ratio:1;display:flex;align-items:center;justify-content:center">
          <canvas id="bgAfterCanvas" style="max-width:100%;max-height:240px;object-fit:contain"></canvas>
        </div>
      </div>
    </div>
    <div id="bgStatus" style="text-align:center;padding:.6rem;color:#888;font-size:.79rem;min-height:2.2em"></div>
    <div style="display:flex;gap:.5rem;margin-top:.65rem">
      <button id="bgSaveBtn" onclick="saveBgResult()" class="btn-r" style="flex:1;display:none;align-items:center;justify-content:center;gap:.4rem"><i class="fa-solid fa-floppy-disk"></i> Lưu PNG trong suốt</button>
      <button onclick="closeBgModal()" class="btn-g" style="flex:1"><i class="fa-solid fa-xmark"></i> Đóng</button>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
