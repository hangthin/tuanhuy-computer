<?php require_once __DIR__.'/layout_top.php'; ?>

<!-- Full-screen success/error overlay -->
<div id="pf-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(3px)">
  <div id="pf-ov-box" style="background:#1a1a1a;border-radius:20px;padding:2.5rem 2rem;text-align:center;min-width:240px;box-shadow:0 32px 80px rgba(0,0,0,.5)">
    <div id="pf-ov-icon" style="width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem"></div>
    <div id="pf-ov-title" style="color:#fff;font-size:1rem;font-weight:800;margin-bottom:.3rem"></div>
    <div id="pf-ov-sub"   style="color:#666;font-size:.82rem"></div>
    <button id="pf-ov-close" onclick="pfCloseOv()" style="display:none;margin-top:1.1rem;background:#2a2a2a;border:none;color:#aaa;padding:.45rem 1.4rem;border-radius:7px;cursor:pointer;font-family:inherit">Đóng</button>
  </div>
</div>

<style>
@keyframes pfSpin{to{transform:rotate(360deg)}}
@keyframes pfBoxIn{from{opacity:0;transform:scale(.82)}to{opacity:1;transform:none}}
@keyframes pfCircle{to{stroke-dashoffset:0}}
#pf-ov-box{animation:pfBoxIn .3s cubic-bezier(.34,1.56,.64,1)}
.img-dz{border:2px dashed #2a2a2a;border-radius:9px;padding:.9rem;text-align:center;cursor:pointer;transition:all .2s;position:relative;background:#0f0f0f}
.img-dz:hover,.img-dz.drag{border-color:var(--red);background:#1a0505}
.img-dz input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;z-index:2}
.xthumb{position:relative;width:60px;height:60px;flex-shrink:0;cursor:grab;user-select:none;border:2px solid transparent;border-radius:9px;transition:border-color .15s,opacity .15s}
.xthumb img{width:60px;height:60px;object-fit:cover;border-radius:7px;border:1.5px solid #2a2a2a;pointer-events:none}
.xthumb .xdel{position:absolute;top:-5px;right:-5px;width:17px;height:17px;background:#ef4444;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.5rem;cursor:pointer;border:1.5px solid #141414;z-index:3}
.xthumb .xgrip{position:absolute;top:3px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.5);font-size:.55rem;pointer-events:none}
.xthumb.xdragging{opacity:.35}
.xthumb.xdrag-over{border-color:var(--red);background:rgba(227,0,0,.08)}
.xt-tools{position:absolute;bottom:0;left:0;right:0;display:flex;opacity:0;transition:opacity .15s;background:rgba(0,0,0,.62);border-radius:0 0 7px 7px}
.xthumb:hover .xt-tools{opacity:1}
.xt-tb{flex:1;background:none;border:none;color:#fff;cursor:pointer;font-size:.52rem;padding:3px 1px;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:2px;transition:background .12s}
.xt-tb:hover{background:rgba(255,255,255,.18)}
.xt-tb.xt-loading{opacity:.5;pointer-events:none}
.dup-warn{display:none;margin-top:.35rem;padding:.4rem .65rem;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:7px;font-size:.75rem;color:#f87171}
/* ── AI panel ── */
@keyframes pfAiIn{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:none}}
@keyframes pfAiGlow{0%{box-shadow:0 0 0 2px #22c55e,0 0 12px rgba(34,197,94,.45);border-color:#22c55e!important}100%{box-shadow:none;border-color:inherit}}
.pf-ai-panel{background:#080808;border:1px solid #1e1e1e;border-radius:9px;padding:.6rem;margin-top:.55rem;animation:pfAiIn .22s ease}
.pf-ai-row{display:grid;grid-template-columns:1fr auto;gap:.3rem .45rem;align-items:center;padding:.28rem 0;border-bottom:1px solid #111}
.pf-ai-row:last-child{border-bottom:none}
.pf-ai-lbl{font-size:.59rem;color:#444;font-weight:700;text-transform:uppercase;letter-spacing:.3px;grid-column:1}
.pf-ai-val{font-size:.71rem;color:#777;line-height:1.4;grid-column:1;word-break:break-word;max-height:42px;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.pf-ai-apb{font-size:.6rem;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#4ade80;border-radius:5px;padding:2px 7px;cursor:pointer;font-family:inherit;white-space:nowrap;grid-column:2;grid-row:1/3;align-self:center;transition:background .15s}
.pf-ai-apb:hover{background:rgba(34,197,94,.2)}
.pf-ai-glow{animation:pfAiGlow 1s ease forwards!important}
</style>

<?php
// Staff countdown: only for edit mode with a created_at timestamp
$staffTimer = null;
if(isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 3
   && !empty($product['created_at'])) {
    $secsLeft = max(0, 900 - (time() - strtotime($product['created_at'])));
    $staffTimer = $secsLeft;
}
?>
<?php if($staffTimer !== null): ?>
<div id="staff-timer-bar" style="display:flex;align-items:center;gap:.65rem;padding:.55rem .85rem;border-radius:9px;margin-bottom:.9rem;font-size:.8rem;border:1px solid;<?= $staffTimer>120?'background:rgba(74,222,128,.06);border-color:rgba(74,222,128,.2);color:#4ade80':'background:rgba(239,68,68,.07);border-color:rgba(239,68,68,.25);color:#f87171' ?>">
  <i class="fas fa-clock"></i>
  <span>Staff: thời gian chỉnh sửa còn</span>
  <strong id="staff-timer-val" style="font-size:.9rem;font-variant-numeric:tabular-nums"></strong>
  <span style="color:#555">— hết giờ sẽ không thể lưu.</span>
</div>
<script>
(function(){
  var secs = <?= (int)$staffTimer ?>;
  var el   = document.getElementById('staff-timer-val');
  var bar  = document.getElementById('staff-timer-bar');
  function fmt(s){ var m=Math.floor(s/60),ss=s%60; return m+':'+(ss<10?'0':'')+ss; }
  function tick(){
    if(secs<=0){
      el.textContent='00:00';
      bar.style.background='rgba(239,68,68,.12)'; bar.style.borderColor='rgba(239,68,68,.4)'; bar.style.color='#f87171';
      document.getElementById('pf-form').querySelectorAll('button[type=submit]').forEach(function(b){b.disabled=true;b.title='Hết thời gian chỉnh sửa';});
      return;
    }
    el.textContent = fmt(secs);
    if(secs<=120){ bar.style.background='rgba(239,68,68,.07)'; bar.style.borderColor='rgba(239,68,68,.25)'; bar.style.color='#f87171'; }
    secs--; setTimeout(tick,1000);
  }
  tick();
})();
</script>
<?php endif; ?>

<div style="max-width:860px">
<form method="POST" enctype="multipart/form-data" id="pf-form" onsubmit="return pfSubmit(event)">
  <!-- hidden for pasted image / presaved search result -->
  <input type="hidden" name="image_base64"      id="pf-b64">
  <input type="hidden" name="image_base64_mime" id="pf-b64-mime">
  <input type="hidden" name="image_presaved"    id="pf-presaved">

  <div style="display:grid;grid-template-columns:1fr 310px;gap:1rem">

    <!-- LEFT -->
    <div>
      <div class="card" style="padding:1.1rem;margin-bottom:.9rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.9rem">Thông tin cơ bản</h3>

        <div style="margin-bottom:.65rem">
          <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Tên sản phẩm *</label>
          <div style="display:flex;gap:.35rem">
            <input type="text" name="name" id="pf-name"
                   value="<?= htmlspecialchars(isset($product['name'])?$product['name']:'') ?>"
                   required class="form-inp" placeholder="Tên sản phẩm đầy đủ"
                   oninput="pfNameInput(this.value)" style="flex:1">
            <button type="button" onclick="pfAiFromName()" title="AI đề xuất từ tên sản phẩm"
                    style="padding:.38rem .6rem;background:linear-gradient(135deg,#6d28d9,#4c1d95);border:none;border-radius:7px;color:#fff;cursor:pointer;font-size:.75rem;flex-shrink:0">
              <i class="fa-solid fa-wand-magic-sparkles"></i>
            </button>
          </div>
          <div class="dup-warn" id="dup-warn"><i class="fa-solid fa-triangle-exclamation"></i> <span id="dup-txt"></span></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.65rem">
          <div>
            <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">SKU</label>
            <input type="text" name="sku" id="pf-sku" value="<?= htmlspecialchars(isset($product['sku'])?$product['sku']:'') ?>" class="form-inp" placeholder="VD: PC-ASUS-001">
          </div>
          <div>
            <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Bảo hành (tháng)</label>
            <input type="number" name="warranty" value="<?= isset($product['warranty'])?$product['warranty']:12 ?>" class="form-inp" min="0">
          </div>
        </div>

        <div style="margin-bottom:.65rem">
          <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Mô tả ngắn</label>
          <textarea name="short_desc" id="pf-short-desc" rows="3" class="form-inp" style="resize:vertical"><?= htmlspecialchars(isset($product['short_desc'])?$product['short_desc']:'') ?></textarea>
        </div>
        <div>
          <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Mô tả chi tiết</label>
          <textarea name="description" id="pf-desc" rows="4" class="form-inp" style="resize:vertical"><?= htmlspecialchars(isset($product['description'])?$product['description']:'') ?></textarea>
        </div>
      </div>

      <div class="card" style="padding:1.1rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.9rem">Giá & Tồn kho</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.6rem">
          <div>
            <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Giá gốc (đ) *</label>
            <input type="number" name="price" value="<?= isset($product['price'])?$product['price']:'' ?>" required class="form-inp" min="0" step="1000">
          </div>
          <div>
            <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Giá KM (đ)</label>
            <input type="number" name="sale_price" value="<?= isset($product['sale_price'])?$product['sale_price']:'' ?>" class="form-inp" min="0" step="1000" placeholder="Để trống nếu không">
          </div>
          <div>
            <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Tồn kho</label>
            <input type="number" name="stock" value="<?= isset($product['stock'])?$product['stock']:0 ?>" class="form-inp" min="0">
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div>
      <div class="card" style="padding:1.1rem;margin-bottom:.9rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.9rem">Phân loại</h3>
        <div style="margin-bottom:.65rem">
          <label style="font-size:.75rem;color:#777;display:block;margin-bottom:.25rem;font-weight:600">Danh mục *</label>
          <select name="category_id" required class="form-inp">
            <option value="">-- Chọn --</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= (isset($product['category_id'])&&$product['category_id']==$cat['id'])?'selected':'' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="font-size:.75rem;color:#777;display:flex;align-items:center;justify-content:space-between;margin-bottom:.25rem;font-weight:600">
            Thương hiệu
            <span id="brand-hint" style="display:none;font-size:.65rem;color:#22c55e;font-weight:400">
              <i class="fa-solid fa-wand-magic-sparkles"></i> Tự nhận dạng
            </span>
          </label>
          <select name="brand_id" id="pf-brand" class="form-inp">
            <option value="">-- Chọn --</option>
            <?php foreach($brands as $b): ?>
            <option value="<?= $b['id'] ?>"
                    data-nm="<?= strtolower(htmlspecialchars($b['name'])) ?>"
                    <?= (isset($product['brand_id'])&&$product['brand_id']==$b['id'])?'selected':'' ?>>
              <?= htmlspecialchars($b['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- IMAGE CARD -->
      <div class="card" style="padding:1.1rem;margin-bottom:.9rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.8rem;display:flex;align-items:center;justify-content:space-between">
          <span>Hình ảnh <span style="font-size:.68rem;color:#555;font-weight:400;margin-left:.4rem">Kéo thả · Click · Ctrl+V</span></span>
          <button type="button" onclick="pfImgSearch()" title="Tìm ảnh từ Google"
                  style="font-size:.65rem;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.3);color:#60a5fa;border-radius:6px;padding:.22rem .6rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:.3rem;font-weight:600">
            <i class="fa-brands fa-google"></i> Tìm ảnh Google
          </button>
        </h3>

        <!-- Main image drop zone -->
        <div style="font-size:.71rem;color:#666;margin-bottom:.3rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Ảnh chính</div>
        <div class="img-dz" id="main-dz"
             ondragover="event.preventDefault();this.classList.add('drag')"
             ondragleave="this.classList.remove('drag')"
             ondrop="pfDropMain(event)">
          <input type="file" name="image" accept="image/*" onchange="pfPreviewMain(this.files[0])">
          <div id="main-prev" style="min-height:60px;display:flex;align-items:center;justify-content:center;margin-bottom:.3rem">
            <?php if(!empty($product['image'])&&$product['image']!=='default.jpg'): ?>
              <img src="<?= UPLOAD_URL.htmlspecialchars($product['image']) ?>" style="max-height:90px;max-width:100%;border-radius:6px;object-fit:contain">
            <?php else: ?>
              <i class="fa-solid fa-image" style="font-size:2rem;color:#333"></i>
            <?php endif; ?>
          </div>
          <div style="font-size:.71rem;color:#555">Click / Kéo thả / <kbd style="background:#1e1e1e;padding:1px 5px;border-radius:3px;font-size:.68rem;color:#888;border:1px solid #333">Ctrl+V</kbd></div>
          <div style="font-size:.67rem;color:#444;margin-top:.2rem">JPG · PNG · WEBP ≤ 5MB</div>
        </div>

        <!-- Image processing tools -->
        <div style="display:flex;gap:.35rem;margin-top:.4rem;flex-wrap:wrap">
          <button type="button" onclick="pfProcessImage('remove-bg')" id="pf-rmbg-btn"
                  style="font-size:.68rem;background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.3);color:#a78bfa;border-radius:6px;padding:.25rem .65rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:.3rem;font-weight:600">
            <i class="fa-solid fa-eraser"></i> Tách nền
          </button>
          <button type="button" onclick="pfProcessImage('add-watermark')" id="pf-wm-btn"
                  style="font-size:.68rem;background:rgba(227,0,0,.1);border:1px solid rgba(227,0,0,.3);color:#f87171;border-radius:6px;padding:.25rem .65rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:.3rem;font-weight:600">
            <span style="background:#e30000;color:#fff;font-size:.6rem;font-weight:900;padding:1px 5px;border-radius:3px">TH</span> Gắn logo
          </button>
        </div>
        <!-- AI Analysis Panel -->
        <div id="pf-ai-area" style="margin-top:.5rem">
          <div id="pf-ai-loading" style="display:none;background:#0a0a0a;border:1px solid #1e1e1e;border-radius:8px;padding:.55rem .7rem;display:none;align-items:center;gap:.45rem">
            <div style="width:14px;height:14px;border:2px solid #2a2a2a;border-top-color:#7c3aed;border-radius:50%;animation:pfSpin .7s linear infinite;flex-shrink:0"></div>
            <span style="font-size:.73rem;color:#555">AI đang phân tích...</span>
            <button type="button" onclick="pfAiCancel()" style="margin-left:auto;background:none;border:none;color:#444;cursor:pointer;font-size:.7rem">Hủy</button>
          </div>
          <div id="pf-ai-panel" style="display:none" class="pf-ai-panel">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.45rem;padding-bottom:.4rem;border-bottom:1px solid #1a1a1a">
              <span style="font-size:.72rem;color:#ccc;font-weight:700;display:flex;align-items:center;gap:.3rem">
                <i class="fa-solid fa-wand-magic-sparkles" style="color:#7c3aed;font-size:.65rem"></i> AI Đề xuất
              </span>
              <div style="display:flex;gap:.3rem">
                <button type="button" onclick="pfAiApplyAll()" style="font-size:.62rem;background:rgba(124,58,237,.15);border:1px solid rgba(124,58,237,.35);color:#a78bfa;border-radius:5px;padding:2px 8px;cursor:pointer;font-family:inherit">
                  <i class="fa-solid fa-check-double"></i> Áp dụng tất cả
                </button>
                <button type="button" onclick="pfAiRescan()" style="font-size:.62rem;background:#111;border:1px solid #222;color:#555;border-radius:5px;padding:2px 7px;cursor:pointer;font-family:inherit">
                  <i class="fa-solid fa-rotate"></i>
                </button>
              </div>
            </div>
            <div id="pf-ai-rows"></div>
          </div>
        </div>

        <!-- Extra images -->
        <div style="font-size:.71rem;color:#666;margin:.8rem 0 .3rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;justify-content:space-between">
          <span>Ảnh phụ</span>
          <div style="display:flex;align-items:center;gap:.4rem">
            <span style="font-size:.67rem;color:#444;font-weight:400;text-transform:none"><i class="fa-solid fa-up-down-left-right" style="font-size:.55rem"></i> Kéo thả</span>
            <button type="button" onclick="pfExtraImgSearch()" title="Tìm ảnh phụ từ Google"
                    style="font-size:.62rem;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.3);color:#60a5fa;border-radius:5px;padding:.18rem .5rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:.25rem;font-weight:600;text-transform:none">
              <i class="fa-brands fa-google"></i> Tìm ảnh
            </button>
          </div>
        </div>
        <div class="img-dz" id="extra-dz"
             ondragover="event.preventDefault();this.classList.add('drag')"
             ondragleave="this.classList.remove('drag')"
             ondrop="pfDropExtra(event)">
          <input type="file" name="extra_images[]" id="extra-inp" accept="image/*" multiple onchange="pfPreviewExtras(this.files)">
          <i class="fa-solid fa-images" style="font-size:1.5rem;color:#333;display:block;margin-bottom:.3rem"></i>
          <div style="font-size:.71rem;color:#555">Click hoặc kéo thả nhiều ảnh</div>
        </div>

        <!-- Saved extra thumbs -->
        <div id="extra-thumbs" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.5rem">
          <?php if(!empty($productImages)): foreach($productImages as $pi): ?>
          <div class="xthumb" draggable="true" data-img-id="<?= $pi['id'] ?>" data-img="<?= htmlspecialchars($pi['image']) ?>">
            <img src="<?= UPLOAD_URL.htmlspecialchars($pi['image']) ?>">
            <span class="xdel" onclick="pfDeleteExtraImg(this,<?= $pi['id'] ?>,<?= $product['id']??0 ?>)">
              <i class="fa-solid fa-xmark"></i>
            </span>
            <span class="xgrip"><i class="fa-solid fa-grip-dots"></i></span>
            <div class="xt-tools">
              <button type="button" class="xt-tb" onclick="pfProcessThumb(this.closest('.xthumb'),'remove-bg')" title="Tách nền"><i class="fa-solid fa-eraser"></i></button>
              <button type="button" class="xt-tb" onclick="pfProcessThumb(this.closest('.xthumb'),'add-watermark')" title="Gắn logo"><span style="background:#e30000;font-size:.48rem;font-weight:900;padding:0 3px;border-radius:2px">TH</span></button>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
        <!-- New extra thumbs preview -->
        <div id="new-extra-thumbs" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.3rem"></div>
        <!-- Search-added extra images (presaved) -->
        <div id="search-extra-thumbs" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.3rem"></div>
        <div id="search-extra-inputs"></div>
      </div>

      <!-- Options -->
      <div class="card" style="padding:1.1rem">
        <h3 style="color:#fff;font-size:.875rem;font-weight:700;margin-bottom:.75rem">Tùy chọn</h3>
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;margin-bottom:.45rem;font-size:.82rem;color:#aaa">
          <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured'])?'checked':'' ?> style="accent-color:var(--red)"> Nổi bật (HOT)
        </label>
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.82rem;color:#aaa">
          <input type="checkbox" name="is_new" value="1" <?= !empty($product['is_new'])?'checked':'' ?> style="accent-color:var(--red)"> Sản phẩm mới
        </label>
      </div>
    </div>
  </div>

  <!-- SPECS -->
  <div class="card" style="padding:1.1rem;margin-top:.9rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.8rem">
      <h3 style="color:#fff;font-size:.875rem;font-weight:700">Thông số kỹ thuật
        <span style="font-size:.68rem;color:#555;font-weight:400;margin-left:.5rem">Hiển thị trên trang sản phẩm</span>
      </h3>
      <button type="button" onclick="specAddRow('','')"
              style="font-size:.72rem;background:rgba(96,165,250,.08);border:1px solid rgba(96,165,250,.25);color:#60a5fa;border-radius:6px;padding:.25rem .65rem;cursor:pointer;font-family:inherit">
        <i class="fa-solid fa-plus"></i> Thêm dòng
      </button>
    </div>
    <div id="specs-rows" style="display:flex;flex-direction:column;gap:.3rem">
      <!-- rows injected by JS -->
    </div>
    <div id="specs-empty" style="font-size:.75rem;color:#333;padding:.5rem 0;display:none">Chưa có thông số — nhấn "+ Thêm dòng" hoặc dùng AI để điền tự động.</div>
  </div>

  <div style="display:flex;gap:.65rem;margin-top:.9rem">
    <button type="submit" id="pf-submit" class="btn-r" style="padding:.55rem 1.25rem;font-size:.9rem">
      <i class="fas fa-save" style="margin-right:.35rem"></i>Lưu sản phẩm
    </button>
    <a href="<?= APP_URL ?>/admin/products" class="btn-g" style="text-decoration:none;padding:.55rem 1.1rem">Hủy</a>
  </div>
</form>
</div>

<script>
var APP_URL='<?= APP_URL ?>';
var PROD_ID=<?= !empty($product['id'])?(int)$product['id']:'0' ?>;
var PROD_IMG='<?= !empty($product['image']) ? htmlspecialchars($product['image']) : '' ?>';
var newExtraFiles=[], dupBlocked=false, dupTimer=null;
var _xDragSrc=null, _xDragType='';

// ── Specs ─────────────────────────────────────────────────
function specAddRow(key, val){
  var box=document.getElementById('specs-rows');
  var row=document.createElement('div');
  row.className='spec-row';
  row.style.cssText='display:grid;grid-template-columns:200px 1fr auto;gap:.35rem;align-items:center';
  var safeKey=(key||'').replace(/"/g,'&quot;');
  var safeVal=(val||'').replace(/"/g,'&quot;');
  row.innerHTML=
    '<input type="text" name="spec_key[]" class="form-inp" placeholder="VD: CPU, RAM, Màn hình..." value="'+safeKey+'" style="font-size:.78rem">'
    +'<input type="text" name="spec_val[]" class="form-inp" placeholder="Giá trị thông số" value="'+safeVal+'" style="font-size:.78rem">'
    +'<button type="button" onclick="this.closest(\'.spec-row\').remove();specCheckEmpty()" style="width:28px;height:28px;background:#1a1a1a;border:1px solid #333;border-radius:6px;color:#555;cursor:pointer;font-size:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-solid fa-xmark"></i></button>';
  box.appendChild(row);
  document.getElementById('specs-empty').style.display='none';
}
function specCheckEmpty(){
  var box=document.getElementById('specs-rows');
  document.getElementById('specs-empty').style.display=box.children.length===0?'block':'none';
}
function pfAiApplySpecs(specs){
  var box=document.getElementById('specs-rows');
  box.innerHTML='';
  if(!specs||typeof specs!=='object'){specCheckEmpty();return;}
  var entries=Object.keys(specs);
  if(!entries.length){specCheckEmpty();return;}
  entries.forEach(function(k){ specAddRow(k,String(specs[k])); });
  document.getElementById('specs-empty').style.display='none';
  // glow
  box.style.transition='background .3s';
  box.style.background='rgba(34,197,94,.04)';
  setTimeout(function(){box.style.background='';},900);
}

// ── Spec templates per category ───────────────────────────
var _specTemplates = {
  1: ['CPU','RAM','Card đồ họa','Ổ cứng','Mainboard','Nguồn','Case/Vỏ','Hệ điều hành','Cổng kết nối','Kích thước'],
  2: ['CPU','RAM','Màn hình','Độ phân giải','Card đồ họa','Ổ cứng','Pin','Cổng kết nối','Hệ điều hành','Kích thước','Trọng lượng'],
  3: ['Kích thước','Độ phân giải','Tần số quét','Thời gian phản hồi','Tấm nền','Độ sáng','Tỷ lệ tương phản','Cổng kết nối','HDR','Điều chỉnh chiều cao','VESA'],
  4: ['Kết nối','Cảm biến','DPI tối đa','Số nút','Đèn LED','Trọng lượng','Độ dài dây/Pin','Hệ thống hỗ trợ'],
  5: ['Kết nối','Switch','Layout','Đèn nền','Keycap','Cổng sạc','Polling Rate','Trọng lượng','Hệ thống hỗ trợ'],
  6: ['Dung lượng','Loại RAM','Tốc độ','Điện áp','Số module','CAS Latency','Hỗ trợ XMP/EXPO','Form Factor'],
  7: ['Socket','Số nhân / Luồng','Xung cơ bản','Xung tăng tốc','Cache L3','TDP','iGPU','Hỗ trợ RAM','Tiến trình'],
  8: ['GPU','VRAM','Loại VRAM','Bus bộ nhớ','Xung nhân','Xung bộ nhớ','Cổng xuất hình','TDP','Nguồn yêu cầu','Chiều dài card'],
  9: ['Dung lượng','Giao tiếp','Chuẩn form','Tốc độ đọc','Tốc độ ghi','Loại NAND','TBW','Kích thước','Mã hóa'],
  10:['Socket','Chipset','Form Factor','Số khe RAM','RAM tối đa','Khe M.2','Khe PCIe x16','Cổng SATA','Cổng USB sau','Cổng mạng','Audio'],
  11:['Loại phụ kiện','Kết nối','Tương thích','Màu sắc','Chất liệu']
};

// Merge template with existing saved values, render all rows
function specInitWithTemplate(catId, savedSpecs){
  var box = document.getElementById('specs-rows');
  box.innerHTML = '';
  var template = _specTemplates[catId] || [];
  var rendered = {};
  // 1. Template fields first (with saved value if any)
  template.forEach(function(key){
    var val = (savedSpecs && savedSpecs[key] !== undefined) ? String(savedSpecs[key]) : '';
    specAddRow(key, val);
    rendered[key] = true;
  });
  // 2. Extra saved fields not in template
  if(savedSpecs){
    Object.keys(savedSpecs).forEach(function(key){
      if(!rendered[key]) specAddRow(key, String(savedSpecs[key]));
    });
  }
  var empty = document.getElementById('specs-empty');
  if(empty) empty.style.display = box.children.length === 0 ? 'block' : 'none';
}

// Init specs from existing product data
(function(){
  <?php
  $specsArr = array();
  if (!empty($product['specs'])) {
    $dec = json_decode($product['specs'], true);
    if (is_array($dec)) $specsArr = $dec;
  }
  $initCatId = isset($product['category_id']) ? (int)$product['category_id'] : 0;
  ?>
  var initSpecs = <?= json_encode($specsArr, JSON_UNESCAPED_UNICODE) ?>;
  var initCat   = <?= $initCatId ?>;
  specInitWithTemplate(initCat, initSpecs);
})();

// Re-render spec template when category changes, preserving filled values
(function(){
  var catSel = document.querySelector('select[name="category_id"]');
  if(!catSel) return;
  catSel.addEventListener('change', function(){
    var catId = parseInt(this.value) || 0;
    // Collect current values before clearing
    var saved = {};
    document.querySelectorAll('#specs-rows .spec-row').forEach(function(row){
      var k = row.querySelector('input[name="spec_key[]"]');
      var v = row.querySelector('input[name="spec_val[]"]');
      if(k && k.value.trim()) saved[k.value.trim()] = v ? v.value : '';
    });
    specInitWithTemplate(catId, saved);
  });
})();

// ── Overlay ───────────────────────────────────────────────
function pfShowOv(type,title,sub,autoClose){
  var ic=document.getElementById('pf-ov-icon'),
      ti=document.getElementById('pf-ov-title'),
      sb=document.getElementById('pf-ov-sub'),
      cl=document.getElementById('pf-ov-close'),
      ov=document.getElementById('pf-overlay');
  if(type==='loading'){
    ic.style.background='rgba(96,165,250,.12)';
    ic.innerHTML='<div style="width:40px;height:40px;border:3px solid #1e3a5f;border-top-color:#60a5fa;border-radius:50%;animation:pfSpin .8s linear infinite"></div>';
    cl.style.display='none';
  } else if(type==='success'){
    ic.style.background='rgba(34,197,94,.1)';
    ic.innerHTML='<svg width="44" height="44" viewBox="0 0 44 44"><circle cx="22" cy="22" r="20" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-dasharray="126" stroke-dashoffset="126" style="animation:pfCircle .55s ease forwards"/><polyline points="13,22 20,29 31,15" fill="none" stroke="#22c55e" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="28" stroke-dashoffset="28" style="animation:pfCircle .4s .5s ease forwards"/></svg>';
    cl.style.display='none';
  } else if(type==='error'||type==='warn'){
    ic.style.background=type==='error'?'rgba(239,68,68,.1)':'rgba(251,191,36,.1)';
    ic.innerHTML='<i class="fa-solid '+(type==='error'?'fa-circle-xmark" style="color:#f87171':'fa-triangle-exclamation" style="color:#fbbf24')+';font-size:2.4rem"></i>';
    cl.style.display='block';
  }
  ti.textContent=title||''; sb.textContent=sub||'';
  ov.style.display='flex';
  if(autoClose) setTimeout(pfCloseOv,autoClose);
}
function pfCloseOv(){ document.getElementById('pf-overlay').style.display='none'; }
document.getElementById('pf-overlay').addEventListener('click',function(e){if(e.target===this)pfCloseOv();});

// ── Duplicate check ───────────────────────────────────────
function pfNameInput(val){
  clearTimeout(dupTimer); dupBlocked=false;
  document.getElementById('dup-warn').style.display='none';
  pfAutoDetectBrand(val);
  if(val.length<5)return;
  dupTimer=setTimeout(function(){ pfCheckDup(val); },800);
}
async function pfCheckDup(name){
  var sku=document.getElementById('pf-sku').value.trim();
  try{
    var r=await fetch(APP_URL+'/api/ai/check-duplicate',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({name:name,sku:sku,exclude_id:PROD_ID})
    });
    var d=await r.json();
    if(d.duplicate){
      document.getElementById('dup-txt').textContent=d.message;
      document.getElementById('dup-warn').style.display='block';
      dupBlocked=true;
    }
  }catch(e){}
}

// ── Auto-detect brand ─────────────────────────────────────
function pfAutoDetectBrand(name){
  var bs=document.getElementById('pf-brand'); if(!bs||!name)return;
  var nl=name.toLowerCase(), best=-1, bestScore=0;
  for(var i=1;i<bs.options.length;i++){
    var nm=bs.options[i].dataset.nm||''; if(!nm)continue;
    var score=0;
    if(nl.startsWith(nm)) score=100;
    else if(nl.includes(' '+nm+' ')||nl.includes(' '+nm)||nl.startsWith(nm+' ')) score=90;
    else if(nl.includes(nm)) score=70;
    else nm.split(/\s+/).forEach(function(p){if(p.length>1&&nl.includes(p))score+=25;});
    if(score>bestScore){bestScore=score;best=i;}
  }
  if(best>=0&&bestScore>=70){
    bs.selectedIndex=best;
    var h=document.getElementById('brand-hint');
    if(h){h.style.display='inline';setTimeout(function(){h.style.display='none';},2500);}
  }
}

// ── Image: main ───────────────────────────────────────────
var pfAiImgB64='', pfAiImgMime='image/jpeg', pfAiScanTimer=null, pfAiAbort=null, pfAiData=null;

function pfPreviewMain(file){
  if(!file||!file.type.startsWith('image/'))return;
  var r=new FileReader();
  r.onload=function(e){
    document.getElementById('main-prev').innerHTML='<img src="'+e.target.result+'" style="max-height:90px;max-width:100%;border-radius:6px;object-fit:contain">';
    document.getElementById('pf-b64').value=e.target.result;
    document.getElementById('pf-b64-mime').value=file.type;
    // Auto-trigger AI scan
    pfAiImgB64=e.target.result; pfAiImgMime=file.type;
    clearTimeout(pfAiScanTimer);
    pfAiScanTimer=setTimeout(pfAiScan, 600);
  };
  r.readAsDataURL(file);
}
function pfDropMain(e){
  e.preventDefault(); document.getElementById('main-dz').classList.remove('drag');
  var f=e.dataTransfer.files[0]; if(f&&f.type.startsWith('image/'))pfPreviewMain(f);
}
// Ctrl+V paste anywhere on page
document.addEventListener('paste',function(e){
  var it=e.clipboardData.items;
  for(var i=0;i<it.length;i++){
    if(it[i].type.startsWith('image/')){pfPreviewMain(it[i].getAsFile());break;}
  }
});

// ── Image: extra (new files) ───────────────────────────────
function pfPreviewExtras(files){
  var list=Array.from(files).filter(function(f){return f.type.startsWith('image/');});
  if(!list.length) return;
  var pending=list.length;
  list.forEach(function(f){
    var r=new FileReader();
    r.onload=function(e){ newExtraFiles.push({file:f,url:e.target.result}); pending--; if(pending===0) renderNewExtras(); };
    r.readAsDataURL(f);
  });
}
function renderNewExtras(){
  var box=document.getElementById('new-extra-thumbs'); box.innerHTML='';
  newExtraFiles.forEach(function(item,i){
    var d=document.createElement('div'); d.className='xthumb'; d.draggable=true; d.dataset.idx=String(i);
    d.innerHTML='<img src="'+item.url+'" style="border:1.5px solid rgba(227,0,0,.3)">'
      +'<span class="xdel" onclick="pfRemNew('+i+')"><i class="fa-solid fa-xmark"></i></span>'
      +'<span class="xgrip"><i class="fa-solid fa-grip-dots"></i></span>'
      +'<span style="position:absolute;top:2px;left:2px;background:rgba(0,0,0,.7);color:#fff;font-size:.48rem;padding:1px 4px;border-radius:3px;font-weight:700;pointer-events:none">'+(i+1)+'</span>'
      +'<div class="xt-tools"><button type="button" class="xt-tb" onclick="pfProcessThumb(this.closest(\'.xthumb\'),\'remove-bg\')" title="Tách nền"><i class="fa-solid fa-eraser"></i></button><button type="button" class="xt-tb" onclick="pfProcessThumb(this.closest(\'.xthumb\'),\'add-watermark\')" title="Gắn logo"><span style="background:#e30000;font-size:.48rem;font-weight:900;padding:0 3px;border-radius:2px">TH</span></button></div>';
    d.addEventListener('dragstart', function(e){ _xDragSrc=this; _xDragType='new'; this.classList.add('xdragging'); e.dataTransfer.effectAllowed='move'; });
    d.addEventListener('dragover',  function(e){ e.preventDefault(); if(_xDragType==='new') this.classList.add('xdrag-over'); });
    d.addEventListener('dragleave', function(){ this.classList.remove('xdrag-over'); });
    d.addEventListener('drop', function(e){
      e.preventDefault(); this.classList.remove('xdrag-over');
      if(_xDragType!=='new'||!_xDragSrc||_xDragSrc===this) return;
      var from=parseInt(_xDragSrc.dataset.idx), to=parseInt(this.dataset.idx);
      var moved=newExtraFiles.splice(from,1)[0]; newExtraFiles.splice(to,0,moved);
      renderNewExtras();
    });
    d.addEventListener('dragend', function(){ this.classList.remove('xdragging'); renderNewExtras(); });
    box.appendChild(d);
  });
}
function pfRemNew(idx){ newExtraFiles.splice(idx,1); renderNewExtras(); }
function pfDropExtra(e){
  e.preventDefault(); document.getElementById('extra-dz').classList.remove('drag');
  pfPreviewExtras(e.dataTransfer.files);
}

// ── Saved thumbs drag-and-drop (AJAX reorder) ─────────────
function initSavedDrag(){
  var thumbs=document.getElementById('extra-thumbs');
  if(!thumbs) return;
  Array.from(thumbs.querySelectorAll('.xthumb[data-img-id]')).forEach(function(el){
    el.addEventListener('dragstart', function(e){ _xDragSrc=this; _xDragType='saved'; this.classList.add('xdragging'); e.dataTransfer.effectAllowed='move'; });
    el.addEventListener('dragover',  function(e){ e.preventDefault(); if(_xDragType==='saved') this.classList.add('xdrag-over'); });
    el.addEventListener('dragleave', function(){ this.classList.remove('xdrag-over'); });
    el.addEventListener('drop', function(e){
      e.preventDefault(); this.classList.remove('xdrag-over');
      if(_xDragType!=='saved'||!_xDragSrc||_xDragSrc===this) return;
      // Swap in DOM
      var box=this.parentElement;
      var nodes=Array.from(box.querySelectorAll('.xthumb[data-img-id]'));
      var fi=nodes.indexOf(_xDragSrc), ti=nodes.indexOf(this);
      if(fi<ti) box.insertBefore(_xDragSrc, this.nextSibling);
      else       box.insertBefore(_xDragSrc, this);
      // Update sort_order via AJAX
      var ids=Array.from(box.querySelectorAll('.xthumb[data-img-id]')).map(function(n){return parseInt(n.dataset.imgId);});
      fetch(APP_URL+'/api/ai/reorder-images',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id:PROD_ID,ids:ids})})
        .catch(function(err){console.warn('reorder:',err);});
    });
    el.addEventListener('dragend', function(){ this.classList.remove('xdragging'); });
  });
}
document.addEventListener('DOMContentLoaded', initSavedDrag);

// ── AI: scan from image ───────────────────────────────────
async function pfAiScan(){
  if(!pfAiImgB64) return;
  pfAiShowLoading(true);
  pfAiAbort=new AbortController();
  try{
    var b64=pfAiImgB64.replace(/^data:[^;]+;base64,/i,'');
    var resp=await fetch(APP_URL+'/api/ai/generate',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({image_b64:b64,image_mime:pfAiImgMime,url:''}),
      signal:pfAiAbort.signal
    });
    var data=await resp.json();
    pfAiShowLoading(false);
    if(data.success){ pfAiData=data.data; pfAiRender(data.data); }
    else{ pfAiHide(); showToast('AI: '+(data.message||'Lỗi phân tích'),'err'); }
  }catch(e){
    pfAiShowLoading(false);
    if(e.name!=='AbortError') showToast('Lỗi kết nối AI','err');
  }
}

// ── AI: scan from product name ────────────────────────────
async function pfAiFromName(){
  var name=document.getElementById('pf-name').value.trim();
  if(!name){ showToast('Nhập tên sản phẩm trước','err'); return; }
  pfAiShowLoading(true);
  pfAiAbort=new AbortController();
  try{
    var resp=await fetch(APP_URL+'/api/ai/generate-from-name',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({product_name:name}),
      signal:pfAiAbort.signal
    });
    var data=await resp.json();
    pfAiShowLoading(false);
    if(data.success){ pfAiData=data.data; pfAiRender(data.data); }
    else{ pfAiHide(); showToast('AI: '+(data.message||'Không tìm được'),'err'); }
  }catch(e){
    pfAiShowLoading(false);
    if(e.name!=='AbortError') showToast('Lỗi kết nối AI','err');
  }
}

function pfAiCancel(){ if(pfAiAbort)pfAiAbort.abort(); pfAiShowLoading(false); }
function pfAiRescan(){ if(pfAiImgB64) pfAiScan(); else pfAiFromName(); }

function pfAiShowLoading(show){
  var l=document.getElementById('pf-ai-loading'),p=document.getElementById('pf-ai-panel');
  if(show){ l.style.display='flex'; if(p) p.style.display='none'; }
  else { l.style.display='none'; }
}
function pfAiHide(){
  document.getElementById('pf-ai-loading').style.display='none';
  document.getElementById('pf-ai-panel').style.display='none';
}

// ── AI: render suggestion rows ────────────────────────────
function pfAiRender(d){
  var panel=document.getElementById('pf-ai-panel');
  var rows=document.getElementById('pf-ai-rows');
  rows.innerHTML='';

  // Build category name for display
  var catName='';
  var cs=document.querySelector('select[name="category_id"]');
  if(cs&&d.category_id){for(var i=0;i<cs.options.length;i++){if(parseInt(cs.options[i].value)===parseInt(d.category_id)){catName=cs.options[i].text;break;}}}
  if(!catName&&d.category_slug) catName=d.category_slug;

  // Build brand name
  var brName=d.brand||'';

  // specs summary
  var specsCount=0, specsPreview='';
  if(d.specs&&typeof d.specs==='object'){
    var sk=Object.keys(d.specs); specsCount=sk.length;
    specsPreview=sk.slice(0,3).map(function(k){return k+': '+d.specs[k];}).join(' · ')+(sk.length>3?'…':'');
  }

  var fields=[
    {lbl:'Tên sản phẩm', val:d.name,       fn:function(){pfAiApply('name',d.name);}},
    {lbl:'SKU',          val:d.sku,         fn:function(){pfAiApply('sku',d.sku);}},
    {lbl:'Mô tả ngắn',  val:d.short_desc,  fn:function(){pfAiApply('short_desc',d.short_desc);}},
    {lbl:'Mô tả',        val:d.description, fn:function(){pfAiApply('description',d.description);}},
    {lbl:'Danh mục',     val:catName,       fn:function(){pfAiApplyCat(d.category_id,d.category_slug);}},
    {lbl:'Thương hiệu',  val:brName,        fn:function(){pfAiApplyBrand(d.brand_id,d.brand);}},
    {lbl:'Giá gốc',      val:d.price?(d.price).toLocaleString('vi-VN')+'đ':'',      fn:function(){pfAiApply('price',d.price);}},
    {lbl:'Giá KM',       val:(d.sale_price&&d.sale_price>0)?(d.sale_price).toLocaleString('vi-VN')+'đ':'', fn:function(){pfAiApply('sale_price',d.sale_price);}},
    {lbl:'Tồn kho',      val:(d.stock!==undefined&&d.stock!==null)?String(d.stock):'', fn:function(){pfAiApply('stock',d.stock);}},
    {lbl:'Bảo hành',     val:d.warranty?(d.warranty+' tháng'):'',  fn:function(){pfAiApply('warranty',d.warranty);}},
    {lbl:'Thông số ('+specsCount+')', val:specsPreview, fn:function(){pfAiApplySpecs(d.specs);}},
  ];

  fields.forEach(function(f){
    if(!f.val&&f.val!==0) return;
    var row=document.createElement('div'); row.className='pf-ai-row';
    var lbl=document.createElement('div'); lbl.className='pf-ai-lbl'; lbl.textContent=f.lbl;
    var val=document.createElement('div'); val.className='pf-ai-val'; val.textContent=f.val;
    var btn=document.createElement('button'); btn.type='button'; btn.className='pf-ai-apb';
    btn.innerHTML='↓ Áp dụng'; btn.onclick=f.fn;
    row.appendChild(lbl); row.appendChild(val); row.appendChild(btn);
    rows.appendChild(row);
  });

  panel.style.display='block';
}

// ── AI: apply individual fields ───────────────────────────
function pfAiApply(field,val){
  if(val===undefined||val===null||val==='') return;
  var el;
  if     (field==='name')        el=document.getElementById('pf-name');
  else if(field==='short_desc')  el=document.getElementById('pf-short-desc');
  else if(field==='description') el=document.getElementById('pf-desc');
  else if(field==='price')       el=document.querySelector('input[name="price"]');
  else if(field==='sale_price')  el=document.querySelector('input[name="sale_price"]');
  else if(field==='stock')       el=document.querySelector('input[name="stock"]');
  else if(field==='warranty')    el=document.querySelector('input[name="warranty"]');
  else if(field==='sku')         el=document.getElementById('pf-sku');
  if(!el) return;
  el.value=val;
  el.classList.remove('pf-ai-glow'); void el.offsetWidth;
  el.classList.add('pf-ai-glow');
  setTimeout(function(){el.classList.remove('pf-ai-glow');},1200);
}

function pfAiApplyCat(catId,slug){
  var cs=document.querySelector('select[name="category_id"]'); if(!cs) return;
  var matched=false;
  if(catId){for(var i=0;i<cs.options.length;i++){if(parseInt(cs.options[i].value)===parseInt(catId)){cs.selectedIndex=i;matched=true;break;}}}
  if(!matched&&slug){var sl=slug.toLowerCase();for(var i=0;i<cs.options.length;i++){if((cs.options[i].dataset.slug||'').toLowerCase()===sl){cs.selectedIndex=i;break;}}}
  cs.classList.remove('pf-ai-glow'); void cs.offsetWidth; cs.classList.add('pf-ai-glow');
  setTimeout(function(){cs.classList.remove('pf-ai-glow');},1200);
}

function pfAiApplyBrand(brandId,brandName){
  var bs=document.getElementById('pf-brand'); if(!bs) return;
  var matched=false;
  if(brandId){for(var i=0;i<bs.options.length;i++){if(parseInt(bs.options[i].value)===parseInt(brandId)){bs.selectedIndex=i;matched=true;break;}}}
  if(!matched&&brandName){
    var bn=brandName.toLowerCase().trim(), best=-1, bestScore=0;
    for(var i=0;i<bs.options.length;i++){var nm=bs.options[i].dataset.nm||'';var score=nm===bn?100:(nm.includes(bn)||bn.includes(nm)?80:0);if(score>bestScore){bestScore=score;best=i;}}
    if(best>=0&&bestScore>=80) bs.selectedIndex=best;
  }
  bs.classList.remove('pf-ai-glow'); void bs.offsetWidth; bs.classList.add('pf-ai-glow');
  setTimeout(function(){bs.classList.remove('pf-ai-glow');},1200);
}

function pfAiApplyAll(){
  if(!pfAiData) return;
  var d=pfAiData;
  if(d.name)                        pfAiApply('name',d.name);
  if(d.sku)                         pfAiApply('sku',d.sku);
  if(d.short_desc)                  pfAiApply('short_desc',d.short_desc);
  if(d.description)                 pfAiApply('description',d.description);
  if(d.price)                       pfAiApply('price',d.price);
  if(d.sale_price&&d.sale_price>0)  pfAiApply('sale_price',d.sale_price);
  if(d.stock!==undefined&&d.stock!==null) pfAiApply('stock',d.stock);
  if(d.warranty)                    pfAiApply('warranty',d.warranty);
  if(d.category_id||d.category_slug) pfAiApplyCat(d.category_id,d.category_slug);
  if(d.brand||d.brand_id)           pfAiApplyBrand(d.brand_id,d.brand);
  if(d.specs&&typeof d.specs==='object'&&Object.keys(d.specs).length)
    pfAiApplySpecs(d.specs);
  showToast('Đã áp dụng tất cả đề xuất AI','ok');
}

// ── Đảm bảo ảnh chính đã được lưu → trả về filename ─────
function pfEnsureSaved(cb){
  var presaved=document.getElementById('pf-presaved').value;
  if(presaved){ cb(presaved); return; }
  var b64=document.getElementById('pf-b64').value;
  if(b64){
    var mime=document.getElementById('pf-b64-mime').value||'image/jpeg';
    fetch(APP_URL+'/api/ai/save-image',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({image_b64:b64,image_mime:mime})
    }).then(function(r){return r.json();}).then(function(d){
      if(d.success){
        document.getElementById('pf-presaved').value=d.filename;
        document.getElementById('pf-b64').value='';
        document.getElementById('pf-b64-mime').value='';
        cb(d.filename);
      } else { showToast('Lỗi lưu ảnh: '+(d.message||''),'err'); }
    }).catch(function(e){ showToast('Lỗi: '+e.message,'err'); });
    return;
  }
  if(PROD_IMG){ cb(PROD_IMG); return; }
  showToast('Chưa có ảnh để xử lý','err');
}

// ── Browser-side background removal (no API key needed) ──
function _pfLoadImgly(cb){
  if(window._imglyRemoveBg){ cb(); return; }
  import('https://esm.sh/@imgly/background-removal@1.4.5').then(function(mod){
    window._imglyRemoveBg = mod.removeBackground || mod.default;
    cb();
  }).catch(function(e){ showToast('Không tải được thư viện AI: '+e.message,'err'); });
}
function _pfBrowserRemoveBg(imgUrl, onDone, onErr){
  _pfLoadImgly(function(){
    fetch(imgUrl).then(function(r){ return r.blob(); }).then(function(blob){
      var obj = URL.createObjectURL(blob);
      window._imglyRemoveBg(obj, {output:{format:'image/png'},debug:false})
        .then(function(resultBlob){
          URL.revokeObjectURL(obj);
          // Draw on canvas with explicit transparent background to guarantee alpha channel
          var blobUrl = URL.createObjectURL(resultBlob);
          var img2 = new Image();
          img2.onload = function(){
            var cv = document.createElement('canvas');
            cv.width = img2.naturalWidth; cv.height = img2.naturalHeight;
            var ctx = cv.getContext('2d');
            ctx.clearRect(0, 0, cv.width, cv.height); // transparent fill
            ctx.drawImage(img2, 0, 0);
            URL.revokeObjectURL(blobUrl);
            cv.toBlob(function(pngBlob){
              var reader = new FileReader();
              reader.onload = function(e){ onDone(e.target.result); };
              reader.readAsDataURL(pngBlob);
            }, 'image/png');
          };
          img2.onerror = function(){ URL.revokeObjectURL(blobUrl); onErr(new Error('Không đọc được ảnh kết quả')); };
          img2.src = blobUrl;
        }).catch(function(e){ URL.revokeObjectURL(obj); onErr(e); });
    }).catch(onErr);
  });
}

// ── Tách nền / Gắn logo ───────────────────────────────────
function pfProcessImage(action){
  if(action === 'remove-bg'){
    var btn=document.getElementById('pf-rmbg-btn');
    var origHtml=btn.innerHTML;
    btn.disabled=true;
    btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> AI đang xử lý...';
    // Get current image URL
    var imgEl=document.getElementById('main-prev').querySelector('img');
    var imgUrl = imgEl ? imgEl.src : '';
    if(!imgUrl){ btn.disabled=false; btn.innerHTML=origHtml; showToast('Chưa có ảnh để tách nền','err'); return; }
    _pfBrowserRemoveBg(imgUrl, function(b64){
      fetch(APP_URL+'/api/ai/save-image',{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({image_b64:b64, image_mime:'image/png'})
      }).then(function(r){return r.json();}).then(function(d){
        btn.disabled=false; btn.innerHTML=origHtml;
        if(d.success){
          document.getElementById('main-prev').innerHTML=
            '<div style="background:repeating-conic-gradient(#2a2a2a 0% 25%,#1a1a1a 0% 50%) 0 0/16px 16px;border-radius:6px;display:inline-block;padding:4px">'
            +'<img src="'+d.url+'?t='+Date.now()+'" style="max-height:82px;max-width:100%;border-radius:4px;object-fit:contain;display:block"></div>';
          document.getElementById('pf-presaved').value=d.filename;
          document.getElementById('pf-b64').value='';
          document.getElementById('pf-b64-mime').value='';
          PROD_IMG=d.filename;
          showToast('Đã tách nền — nền trong suốt (PNG)','ok');
        } else { showToast('Lỗi lưu ảnh: '+(d.message||''),'err'); }
      }).catch(function(e){ btn.disabled=false; btn.innerHTML=origHtml; showToast('Lỗi: '+e.message,'err'); });
    }, function(e){ btn.disabled=false; btn.innerHTML=origHtml; showToast('Tách nền lỗi: '+e.message,'err'); });
    return;
  }
  // Gắn logo — vẫn dùng server
  pfEnsureSaved(function(filename){
    var btn=document.getElementById('pf-wm-btn');
    var origHtml=btn.innerHTML;
    btn.disabled=true;
    btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
    fetch(APP_URL+'/api/ai/add-watermark',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({filename:filename})
    }).then(function(r){return r.json();}).then(function(d){
      btn.disabled=false; btn.innerHTML=origHtml;
      if(d.success){
        document.getElementById('main-prev').innerHTML=
          '<img src="'+d.url+'?t='+Date.now()+'" style="max-height:90px;max-width:100%;border-radius:6px;object-fit:contain">';
        document.getElementById('pf-presaved').value=d.filename;
        document.getElementById('pf-b64').value='';
        document.getElementById('pf-b64-mime').value='';
        PROD_IMG=d.filename;
        showToast('Đã gắn logo TH!','ok');
      } else { showToast('Gắn logo lỗi: '+(d.message||''),'err'); }
    }).catch(function(e){ btn.disabled=false; btn.innerHTML=origHtml; showToast('Lỗi: '+e.message,'err'); });
  });
}

// ── Google Image Search ───────────────────────────────────
function pfImgSearch(){
  var q=document.getElementById('pf-name').value.trim();
  imsOpen(function(url, thumb, title, thumbEl, extra){
    var payload=extra&&extra.b64 ? {image_b64:extra.b64, image_mime:extra.mime} : {image_url:url};
    fetch(APP_URL+'/api/ai/save-image',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    }).then(function(r){return r.json();}).then(function(d){
      if(thumbEl) thumbEl.classList.remove('ims-loading');
      if(d.success){
        // Show preview
        document.getElementById('main-prev').innerHTML=
          '<img src="'+d.url+'" style="max-height:90px;max-width:100%;border-radius:6px;object-fit:contain">';
        // Clear b64 fields, set presaved filename
        document.getElementById('pf-b64').value='';
        document.getElementById('pf-b64-mime').value='';
        document.getElementById('pf-presaved').value=d.filename;
        // Clear AI image state (presaved image can still trigger AI scan from URL)
        pfAiImgB64=''; pfAiImgMime='image/jpeg';
        imsClose();
        showToast('Đã chọn ảnh từ Google','ok');
      } else {
        if(thumbEl) thumbEl.classList.remove('ims-loading','ims-selected');
        showToast('Không lấy được ảnh: '+(d.message||'lỗi'),'err');
      }
    }).catch(function(e){
      if(thumbEl) thumbEl.classList.remove('ims-loading','ims-selected');
      showToast('Lỗi kết nối: '+e.message,'err');
    });
  }, q);
}

// ── Tách nền / Gắn logo cho ảnh phụ ─────────────────────
function pfProcessThumb(thumbEl, action){
  var filename = thumbEl.dataset.img || '';
  if(!filename){
    // Thử lấy từ URL ảnh
    var imgEl = thumbEl.querySelector('img');
    if(imgEl && imgEl.src){
      var parts = imgEl.src.split('/');
      filename = parts[parts.length-1].split('?')[0];
    }
  }
  if(!filename){ showToast('Không xác định được file ảnh','err'); return; }

  var btns = thumbEl.querySelectorAll('.xt-tb');
  btns.forEach(function(b){ b.classList.add('xt-loading'); });

  function _applyThumbResult(d, oldFname){
    btns.forEach(function(b){ b.classList.remove('xt-loading'); });
    if(!d.success){ showToast((action==='remove-bg'?'Tách nền':'Gắn logo')+' lỗi: '+(d.message||''),'err'); return; }
    var imgEl = thumbEl.querySelector('img');
    if(imgEl){
      imgEl.src = d.url+'?t='+Date.now();
      if(action==='remove-bg') thumbEl.style.background='repeating-conic-gradient(#2a2a2a 0% 25%,#1a1a1a 0% 50%) 0 0/10px 10px';
    }
    thumbEl.dataset.img = d.filename;
    var imgId = thumbEl.dataset.imgId;
    if(imgId){
      fetch(APP_URL+'/api/ai/update-extra-image',{
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({img_id:parseInt(imgId), filename:d.filename})
      }).catch(function(e){ console.warn('update-extra-image:',e); });
    }
    var inp = document.querySelector('#search-extra-inputs input[data-fname="'+oldFname+'"]');
    if(inp){ inp.value=d.filename; inp.dataset.fname=d.filename; }
    showToast(action==='remove-bg'?'Đã tách nền — PNG trong suốt':'Đã gắn logo!','ok');
  }

  if(action === 'remove-bg'){
    var imgEl2 = thumbEl.querySelector('img');
    var imgUrl2 = imgEl2 ? imgEl2.src.split('?')[0] : '';
    if(!imgUrl2){ btns.forEach(function(b){b.classList.remove('xt-loading');}); showToast('Không tìm được URL ảnh','err'); return; }
    _pfBrowserRemoveBg(imgUrl2, function(b64){
      fetch(APP_URL+'/api/ai/save-image',{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({image_b64:b64, image_mime:'image/png'})
      }).then(function(r){return r.json();}).then(function(d){ _applyThumbResult(d, filename); })
        .catch(function(e){ btns.forEach(function(b){b.classList.remove('xt-loading');}); showToast('Lỗi: '+e.message,'err'); });
    }, function(e){ btns.forEach(function(b){b.classList.remove('xt-loading');}); showToast('Tách nền lỗi: '+e.message,'err'); });
    return;
  }

  fetch(APP_URL+'/api/ai/'+action,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body:JSON.stringify({filename:filename})
  }).then(function(r){return r.json();}).then(function(d){ _applyThumbResult(d, filename); })
    .catch(function(e){ btns.forEach(function(b){ b.classList.remove('xt-loading'); }); showToast('Lỗi: '+e.message,'err'); });
}

// ── Google Image Search: ảnh phụ ─────────────────────────
function pfExtraImgSearch(){
  var q=document.getElementById('pf-name').value.trim();
  imsOpen(function(url, thumb, title, thumbEl, extra){
    var payload=extra&&extra.b64 ? {image_b64:extra.b64, image_mime:extra.mime} : {image_url:url};
    fetch(APP_URL+'/api/ai/save-image',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    }).then(function(r){return r.json();}).then(function(d){
      if(thumbEl) thumbEl.classList.remove('ims-loading');
      if(d.success){
        // Add hidden input
        var inp=document.createElement('input');
        inp.type='hidden'; inp.name='extra_presaved[]'; inp.value=d.filename;
        inp.dataset.fname=d.filename;
        document.getElementById('search-extra-inputs').appendChild(inp);
        // Show thumbnail
        var box=document.getElementById('search-extra-thumbs');
        var wrap=document.createElement('div');
        wrap.className='xthumb'; wrap.dataset.fname=d.filename; wrap.dataset.img=d.filename;
        wrap.innerHTML='<img src="'+d.url+'" style="border:1.5px solid rgba(96,165,250,.4)">'
          +'<span class="xdel" onclick="pfRemSearchExtra(this,\''+d.filename+'\')" style="background:#3b82f6"><i class="fa-solid fa-xmark"></i></span>'
          +'<span style="position:absolute;top:2px;left:2px;background:rgba(0,0,0,.7);color:#60a5fa;font-size:.42rem;padding:1px 3px;border-radius:3px;pointer-events:none"><i class="fa-brands fa-google"></i></span>'
          +'<div class="xt-tools"><button type="button" class="xt-tb" onclick="pfProcessThumb(this.closest(\'.xthumb\'),\'remove-bg\')" title="Tách nền"><i class="fa-solid fa-eraser"></i></button><button type="button" class="xt-tb" onclick="pfProcessThumb(this.closest(\'.xthumb\'),\'add-watermark\')" title="Gắn logo"><span style="background:#e30000;font-size:.48rem;font-weight:900;padding:0 3px;border-radius:2px">TH</span></button></div>';
        box.appendChild(wrap);
        showToast('Đã thêm ảnh phụ','ok');
      } else {
        if(thumbEl) thumbEl.classList.remove('ims-loading','ims-selected');
        showToast('Không lấy được ảnh: '+(d.message||'lỗi'),'err');
      }
    }).catch(function(e){
      if(thumbEl) thumbEl.classList.remove('ims-loading','ims-selected');
      showToast('Lỗi kết nối: '+e.message,'err');
    });
  }, q);
}
function pfRemSearchExtra(btn, fname){
  var wrap=btn.closest('.xthumb'); if(wrap) wrap.remove();
  var inp=document.querySelector('#search-extra-inputs input[data-fname="'+fname+'"]');
  if(inp) inp.remove();
}

// ── Xóa ảnh phụ đã lưu (AJAX, không reload trang) ────────
function pfDeleteExtraImg(btn, imgId, productId){
  if(!confirm('Xóa ảnh này?')) return;
  var thumb = btn.closest('.xthumb');
  btn.style.pointerEvents='none'; btn.style.opacity='.4';
  fetch(APP_URL+'/admin/products/delete-image?img_id='+imgId+'&product_id='+productId+'&json=1')
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(d.success){ thumb.remove(); }
      else { btn.style.pointerEvents=''; btn.style.opacity=''; showToast(d.message||'Xóa thất bại','err'); }
    })
    .catch(function(){ btn.style.pointerEvents=''; btn.style.opacity=''; showToast('Lỗi kết nối','err'); });
}

// ── Submit ────────────────────────────────────────────────
function pfSubmit(e){
  e.preventDefault();
  if(dupBlocked){
    pfShowOv('warn','Sản phẩm đã tồn tại',document.getElementById('dup-txt').textContent);
    return false;
  }
  // Attach new extra files (in reordered order) to file input via DataTransfer
  if(newExtraFiles.length){
    try{
      var dt=new DataTransfer();
      newExtraFiles.forEach(function(item){dt.items.add(item.file);});
      document.getElementById('extra-inp').files=dt.files;
    }catch(e2){ console.warn('DataTransfer not supported:',e2); }
  }
  pfShowOv('loading','Đang lưu sản phẩm...','');
  setTimeout(function(){ document.getElementById('pf-form').submit(); }, 200);
  return false;
}
</script>
<?php require_once __DIR__.'./layout_bottom.php'; ?>
