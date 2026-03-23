<?php require_once __DIR__.'/layout_top.php'; ?>

<!-- Fullscreen overlay -->
<div id="ai-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.78);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(3px)">
  <div id="ai-ov-box" style="background:#1a1a1a;border-radius:20px;padding:2.5rem 2rem;text-align:center;min-width:250px;max-width:320px;box-shadow:0 32px 80px rgba(0,0,0,.6);animation:aiOvIn .32s cubic-bezier(.34,1.56,.64,1)">
    <div id="ai-ov-icon" style="width:76px;height:76px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem"></div>
    <div id="ai-ov-title" style="color:#fff;font-size:1rem;font-weight:800;margin-bottom:.3rem"></div>
    <div id="ai-ov-sub"   style="color:#555;font-size:.82rem;line-height:1.5"></div>
    <button id="ai-ov-close" onclick="aiCloseOv()" style="display:none;margin-top:1.1rem;background:#2a2a2a;border:none;color:#aaa;padding:.45rem 1.4rem;border-radius:7px;cursor:pointer;font-family:inherit">Đóng</button>
  </div>
</div>

<style>
@keyframes aiOvIn{from{opacity:0;transform:scale(.82)}to{opacity:1;transform:none}}
@keyframes aiSpin{to{transform:rotate(360deg)}}
@keyframes aiCircle{to{stroke-dashoffset:0}}
/* Live Preview */
@keyframes lpPulse{0%{transform:scale(1);opacity:1}70%{transform:scale(2.4);opacity:0}100%{transform:scale(2.4);opacity:0}}
@keyframes lpBlink{0%,100%{opacity:1}50%{opacity:0}}
@keyframes lpShim{0%{background-position:-400px 0}100%{background-position:400px 0}}
.lp-field{background:#111;border:1px solid #1e1e1e;border-radius:8px;padding:.5rem .7rem;min-height:36px;position:relative;transition:border-color .25s}
.lp-field::before{content:attr(data-label);position:absolute;top:-9px;left:8px;background:#141414;padding:0 5px;font-size:.6rem;color:#444;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
.lp-field.lp-on{border-color:rgba(227,0,0,.5);box-shadow:0 0 0 1px rgba(227,0,0,.1)}
.lp-tall{min-height:54px}
.lp-txt{color:#ddd;font-size:.81rem;line-height:1.5;word-break:break-word}
.lp-cursor::after{content:"▌";color:var(--red);animation:lpBlink .55s infinite;font-size:.88em}
.lp-num{color:#4ade80;font-weight:700;font-size:.9rem}
.lp-spec-row{display:flex;gap:.5rem;font-size:.73rem;padding:.14rem 0;border-bottom:1px solid #1a1a1a}
.lp-spec-k{color:#777;min-width:100px;flex-shrink:0}.lp-spec-v{color:#4ade80}
.lp-shim{background:linear-gradient(90deg,#1a1a1a 25%,#252525 50%,#1a1a1a 75%);background-size:400px 100%;animation:lpShim 1.2s infinite;border-radius:4px;height:13px;margin:.1rem 0}
</style>

<style>
.aig{display:grid;grid-template-columns:360px 1fr;gap:1.1rem;align-items:start}
@media(max-width:900px){.aig{grid-template-columns:1fr}}
/* When form is shown, right panel gets full width on scroll */
.aig-box{min-width:0}
.aig-box{background:#141414;border:1px solid #1e1e1e;border-radius:12px}
.ai-tab{padding:.48rem .85rem;background:none;border:none;color:#555;font-size:.79rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;transition:all .18s;font-family:inherit}
.ai-tab.on{color:var(--red);border-color:var(--red)}
.dzone{background:#0f0f0f;border:2px dashed #222;border-radius:10px;padding:1.6rem;text-align:center;cursor:pointer;position:relative;min-height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:all .2s}
.dzone:hover,.dzone.drag{border-color:var(--red);background:#1a0505}
.dzone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;width:100%;height:100%}
.dz-ic{width:48px;height:48px;background:rgba(227,0,0,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto .7rem;color:var(--red);font-size:1.25rem}
.af{margin-bottom:.65rem}
.af label{display:block;font-size:.68rem;color:#444;font-weight:700;text-transform:uppercase;letter-spacing:.35px;margin-bottom:.24rem}
.af .form-inp{background:#0f0f0f;border-color:#1e1e1e;font-size:.81rem}
.af .form-inp:focus{border-color:var(--red)}
.spec-r{display:grid;grid-template-columns:1fr 1fr 26px;gap:.35rem;margin-bottom:.28rem;align-items:center}
/* Full-width result layout */
.aig-result{width:100%}
.aig-result .aig-box{max-width:1000px;margin:0 auto}
.ps{font-size:.76rem;color:#444;display:flex;align-items:center;gap:.4rem;padding:.22rem 0}
.ps.done{color:#22c55e}.ps.act{color:var(--red)}

/* ── Glow effect on AI-filled inputs ── */
@keyframes aiGlowPulse{
  0%,100%{box-shadow:0 0 0 2px rgba(227,0,0,.55),0 0 14px rgba(227,0,0,.35);border-color:rgba(227,0,0,.85)!important}
  50%{box-shadow:0 0 0 3px rgba(255,80,80,.9),0 0 28px rgba(227,0,0,.55);border-color:#ff3333!important}
}
@keyframes aiGlowDone{
  0%{box-shadow:0 0 0 2px #22c55e,0 0 22px rgba(34,197,94,.65);border-color:#22c55e!important}
  100%{box-shadow:0 0 0 1px rgba(34,197,94,.35),0 0 6px rgba(34,197,94,.15);border-color:#22c55e!important}
}
.ai-filling{animation:aiGlowPulse .65s ease-in-out infinite!important;border-color:rgba(227,0,0,.85)!important}
.ai-filled{border-color:#22c55e!important;animation:aiGlowDone .7s ease forwards!important;box-shadow:0 0 0 1px rgba(34,197,94,.35),0 0 8px rgba(34,197,94,.18)!important;transition:border-color .3s}

/* ── Scan line effect trên ảnh ── */
@keyframes scanLine{0%{top:-8%}100%{top:108%}}
@keyframes scanPulse{0%,100%{opacity:.7}50%{opacity:1}}
#scan-wrap{position:absolute;inset:0;border-radius:7px;overflow:hidden;pointer-events:none;z-index:10}
#scan-line{position:absolute;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,rgba(227,0,0,.9),rgba(255,100,100,1),rgba(227,0,0,.9),transparent);box-shadow:0 0 12px 3px rgba(227,0,0,.6),0 0 24px 6px rgba(227,0,0,.3);animation:scanLine 1.4s linear infinite,scanPulse 1.4s ease-in-out infinite}
#scan-corners span{position:absolute;width:14px;height:14px;border-color:var(--red);border-style:solid;opacity:.9}
#scan-corners span:nth-child(1){top:4px;left:4px;border-width:2px 0 0 2px}
#scan-corners span:nth-child(2){top:4px;right:4px;border-width:2px 2px 0 0}
#scan-corners span:nth-child(3){bottom:4px;left:4px;border-width:0 0 2px 2px}
#scan-corners span:nth-child(4){bottom:4px;right:4px;border-width:0 2px 2px 0}
#scan-overlay{position:absolute;inset:0;border:1.5px solid rgba(227,0,0,.35);border-radius:7px;animation:scanPulse 1.4s ease-in-out infinite}
#scan-label{position:absolute;bottom:7px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.75);color:var(--red);font-size:.62rem;font-weight:700;letter-spacing:.8px;padding:2px 8px;border-radius:4px;white-space:nowrap;animation:scanPulse .8s ease-in-out infinite}
</style>

<!-- HEADER ROW -->
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:.5rem">
    <span style="display:inline-flex;align-items:center;gap:.35rem;background:rgba(227,0,0,.1);color:var(--red);padding:.22rem .65rem;border-radius:99px;font-size:.7rem;font-weight:700"><i class="fa-solid fa-wand-magic-sparkles"></i>AI GENERATOR</span>
    <span style="color:#444;font-size:.78rem">Upload ảnh → OpenRouter AI nhận diện & tự động điền thông tin sản phẩm</span>
  </div>
  <div style="margin-left:auto;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:8px;padding:.32rem .75rem;display:flex;align-items:center;gap:.4rem">
    <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:.72rem"></i>
    <span style="color:#4ade80;font-size:.73rem;font-weight:600">OpenRouter AI đã kết nối</span>
  </div>
</div>

<!-- GUIDE BANNER -->
<div id="aig-guide" style="background:#0a1a0a;border:1px solid #1a3a1a;border-radius:9px;padding:.75rem 1rem;margin-bottom:.9rem;display:flex;align-items:flex-start;gap:.65rem">
  <i class="fa-solid fa-circle-info" style="color:#4ade80;flex-shrink:0;margin-top:1px"></i>
  <div style="flex:1;font-size:.76rem;line-height:1.65;color:#666">
    <span style="color:#4ade80;font-weight:700">Cách dùng: </span>
    <strong style="color:#aaa">Upload ảnh</strong> sản phẩm (kéo thả, chọn file, dán Ctrl+V hoặc nhập URL)
    → Nhấn <strong style="color:var(--red)">Phân tích với AI</strong>
    → AI tự động nhận diện tên, giá, thông số, mô tả
    → Kiểm tra & nhấn <strong style="color:#22c55e">Lưu sản phẩm</strong> — xong!
  </div>
  <button onclick="document.getElementById('aig-guide').style.display='none'" style="background:none;border:none;color:#333;cursor:pointer;font-size:.78rem"><i class="fa-solid fa-xmark"></i></button>
</div>

<div class="aig">
  <!-- LEFT -->
  <div>
    <div class="aig-box" style="padding:.9rem;margin-bottom:.8rem">
      <!-- Tabs -->
      <div style="display:flex;gap:2px;margin-bottom:.8rem;border-bottom:1px solid #1c1c1c;padding-bottom:.6rem">
        <button class="ai-tab on" id="ait-u" onclick="aiT('u')"><i class="fa-solid fa-upload" style="margin-right:4px"></i>Upload file</button>
        <button class="ai-tab" id="ait-l" onclick="aiT('l')"><i class="fa-solid fa-link" style="margin-right:4px"></i>URL ảnh</button>
        <button class="ai-tab" id="ait-c" onclick="aiT('c')"><i class="fa-solid fa-paste" style="margin-right:4px"></i>Clipboard</button>
        <button class="ai-tab" id="ait-n" onclick="aiT('n')"><i class="fa-solid fa-keyboard" style="margin-right:4px"></i>Tên SP</button>
      </div>

      <!-- Upload tab -->
      <div id="at-u">
        <div class="dzone" id="dz" ondragover="event.preventDefault();this.classList.add('drag')" ondragleave="this.classList.remove('drag')" ondrop="onDrop(event)">
          <input type="file" accept="image/*" id="fi" onchange="loadF(this)" style="z-index:3">
          <div id="dz-c">
            <div class="dz-ic"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <div style="color:#bbb;font-weight:600;font-size:.84rem;margin-bottom:.3rem">Kéo thả hoặc click chọn ảnh</div>
            <div style="color:#333;font-size:.73rem">JPG, PNG, WEBP — tối đa 10MB</div>
          </div>
          <img id="up-p" src="" alt="" style="display:none;width:100%;max-height:190px;object-fit:contain;border-radius:7px;position:relative;z-index:1;pointer-events:none">
        </div>
        <div id="up-info" style="display:none;margin-top:.45rem;font-size:.73rem;color:#22c55e;display:flex;align-items:center;gap:.35rem">
          <i class="fa-solid fa-circle-check"></i><span id="up-fn"></span>
          <span style="color:#555" id="up-sz"></span>
        </div>
      </div>

      <!-- URL tab -->
      <div id="at-l" style="display:none">
        <div class="af">
          <label>URL hình ảnh sản phẩm</label>
          <div style="display:flex;gap:.35rem">
            <input type="url" id="ul" class="form-inp" placeholder="https://example.com/product.jpg" oninput="aiImgUrl=this.value">
            <button onclick="prevURL()" class="btn-g" style="padding:.38rem .55rem;font-size:.77rem;white-space:nowrap"><i class="fa-solid fa-eye"></i></button>
          </div>
        </div>
        <div style="background:#0f0f0f;border-radius:8px;height:170px;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #1a1a1a">
          <img id="ul-p" style="max-width:100%;max-height:170px;object-fit:contain;display:none" alt="">
          <div id="ul-ph" style="color:#2a2a2a;text-align:center"><i class="fa-solid fa-image" style="font-size:2rem;margin-bottom:.35rem;display:block"></i><span style="font-size:.73rem">Xem trước ảnh</span></div>
        </div>
      </div>

      <!-- Clipboard tab -->
      <div id="at-c" style="display:none">
        <div style="background:#0f0f0f;border:2px dashed #1e1e1e;border-radius:10px;padding:1.6rem;text-align:center;cursor:pointer;transition:.2s" onclick="doPaste()" id="pz" onmouseover="this.style.borderColor='var(--red)'" onmouseout="this.style.borderColor='#1e1e1e'">
          <div class="dz-ic" style="margin:0 auto .6rem"><i class="fa-solid fa-paste"></i></div>
          <div style="color:#bbb;font-weight:600;font-size:.83rem;margin-bottom:.28rem">Nhấn <kbd style="background:#222;padding:1px 5px;border-radius:4px;font-size:.75rem">Ctrl+V</kbd> để dán ảnh</div>
          <div style="color:#333;font-size:.72rem">hoặc click vào đây rồi dán</div>
        </div>
        <img id="cp-p" src="" alt="" style="display:none;width:100%;max-height:170px;object-fit:contain;border-radius:7px;margin-top:.55rem">
      </div>
    </div>

    <!-- Tab: Search by name -->
    <div id="at-n" style="display:none">
      <div style="background:#0f0f0f;border-radius:10px;padding:1rem;border:1px solid #1a1a1a">
        <div style="font-size:.73rem;color:#888;margin-bottom:.55rem;display:flex;align-items:center;gap:.35rem">
          <i class="fa-solid fa-lightbulb" style="color:#fbbf24"></i>
          Nhập tên sản phẩm để AI tìm thông tin (không cần ảnh)
        </div>
        <textarea id="ai-name-inp" rows="3" class="form-inp" style="background:#141414;border-color:#222;font-size:.82rem;resize:vertical" placeholder="VD: ASUS ROG Strix G16 i9-14900HX RTX 4070&#10;Chuột Logitech G Pro X Superlight 2..."></textarea>
        <button onclick="genFromName()" style="width:100%;margin-top:.5rem;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;border:none;padding:.58rem;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.4rem">
          <i class="fa-solid fa-magnifying-glass-chart"></i> AI tìm kiếm từ tên sản phẩm
        </button>
      </div>
    </div>

    <!-- Progress (hidden) -->
    <div id="ai-pg" class="aig-box" style="display:none;padding:.85rem;margin-bottom:.8rem">
      <div style="color:#ccc;font-size:.79rem;font-weight:600;margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem">
        <i class="fa-solid fa-spinner fa-spin" style="color:var(--red)"></i>AI đang phân tích hình ảnh...
      </div>
      <div id="pg-steps"></div>
      <div style="background:#1a1a1a;border-radius:99px;height:3px;overflow:hidden;margin-top:.55rem">
        <div id="pg-bar" style="height:100%;width:0;background:linear-gradient(90deg,var(--red),#ff4444);border-radius:99px;transition:width .4s ease"></div>
      </div>
    </div>

    <!-- Generate button -->
    <button onclick="doGen()" id="gen-btn" style="width:100%;background:linear-gradient(135deg,var(--red) 0%,#ff2020 100%);color:#fff;border:none;padding:.72rem;border-radius:10px;font-weight:700;font-size:.88rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.5rem;box-shadow:0 4px 16px rgba(227,0,0,.3);transition:all .2s">
      <i class="fa-solid fa-wand-magic-sparkles"></i>Phân tích ảnh & Tự động điền thông tin
    </button>
    
  </div>

  <!-- RIGHT: Result form -->
  <div class="aig-result" style="display:none"><div class="aig-box" style="padding:1.1rem">

    <!-- Placeholder -->
    <div id="ai-ph" style="text-align:center;padding:3rem 1rem;color:#2a2a2a">
      <i class="fa-solid fa-wand-magic-sparkles" style="font-size:2.5rem;margin-bottom:.8rem;display:block;opacity:.15"></i>
      <div style="font-weight:600;color:#333;margin-bottom:.28rem">Chờ AI phân tích</div>
      <div style="font-size:.75rem">Upload ảnh hoặc nhập tên sản phẩm</div>
    </div>

    <!-- LIVE PREVIEW PANEL -->
    <div id="lp-panel" style="display:none">
      <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.85rem;padding-bottom:.65rem;border-bottom:1px solid #1c1c1c">
        <div style="position:relative;width:10px;height:10px;flex-shrink:0">
          <span style="position:absolute;inset:0;border-radius:50%;background:var(--red);animation:lpPulse 1s infinite;opacity:.6"></span>
          <span style="position:absolute;inset:0;border-radius:50%;background:var(--red)"></span>
        </div>
        <span style="color:#fff;font-weight:700;font-size:.875rem">AI đang phân tích...</span>
        <span id="lp-step" style="color:#555;font-size:.7rem;margin-left:.15rem"></span>
      </div>
      <div style="display:grid;gap:.5rem">
        <div class="lp-field" id="lp-name" data-label="Tên sản phẩm"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
          <div class="lp-field" id="lp-cat"   data-label="Danh mục"></div>
          <div class="lp-field" id="lp-brand" data-label="Thương hiệu"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem">
          <div class="lp-field" id="lp-price" data-label="Giá"></div>
          <div class="lp-field" id="lp-stock" data-label="Kho"></div>
          <div class="lp-field" id="lp-warr"  data-label="BH"></div>
        </div>
        <div class="lp-field lp-tall" id="lp-desc" data-label="Mô tả ngắn"></div>
        <div class="lp-field" id="lp-specs-wrap" data-label="Thông số kỹ thuật">
          <div id="lp-specs" style="display:flex;flex-direction:column;gap:.22rem"></div>
        </div>
      </div>
    </div>
      <div style="font-size:.76rem">Upload ảnh sản phẩm → nhấn Phân tích → AI nhận diện tự động</div>
      <div style="font-size:.72rem;color:#1e1e1e;margin-top:.35rem">AI sẽ tự nhận diện và điền đầy đủ thông tin</div>
    </div>

    <!-- Result form -->
    <div id="ai-form" style="display:none;width:100%">
      <input type="hidden" id="ai-img-file" value="">

      <!-- Result header -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;padding-bottom:.75rem;border-bottom:1px solid #1c1c1c">
        <div style="display:flex;align-items:center;gap:.45rem">
          <i class="fa-solid fa-circle-check" style="color:#22c55e;font-size:1rem"></i>
          <span style="color:#fff;font-weight:700;font-size:.875rem">AI đã phân tích xong</span>
          <span id="ai-demo-badge" style="display:none"></span>
        </div>
        <div style="display:flex;gap:.3rem">
          <button type="button" onclick="resetAI()" class="btn-g" style="font-size:.7rem;padding:.22rem .5rem"><i class="fa-solid fa-rotate"></i> Làm lại</button>
          <a href="<?= APP_URL ?>/admin/products/create" class="btn-g" style="font-size:.7rem;padding:.22rem .5rem;text-decoration:none"><i class="fa-solid fa-arrow-up-right-from-square"></i> Form đầy đủ</a>
        </div>
      </div>

      <!-- Image preview in form -->
      <div id="aif-img" style="display:none;text-align:center;margin-bottom:.8rem">
        <img id="aif-img-src" src="" alt="" style="max-height:130px;max-width:100%;border-radius:8px;border:1px solid #1e1e1e;object-fit:contain">
        <div id="aif-saved-badge" style="display:none;margin-top:.35rem;font-size:.71rem;color:#22c55e;display:none;align-items:center;justify-content:center;gap:.3rem">
          <i class="fa-solid fa-circle-check"></i><span id="aif-saved-name"></span>
        </div>
        <!-- Image processing tools -->
        <div style="display:flex;gap:.35rem;margin-top:.45rem;justify-content:center;flex-wrap:wrap">
          <button type="button" onclick="aigProcessImage('remove-bg')" id="aig-rmbg-btn"
                  style="font-size:.68rem;background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.3);color:#a78bfa;border-radius:6px;padding:.25rem .65rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:.3rem;font-weight:600">
            <i class="fa-solid fa-eraser"></i> Tách nền
          </button>
          <button type="button" onclick="aigProcessImage('add-watermark')" id="aig-wm-btn"
                  style="font-size:.68rem;background:rgba(227,0,0,.1);border:1px solid rgba(227,0,0,.3);color:#f87171;border-radius:6px;padding:.25rem .65rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:.3rem;font-weight:600">
            <span style="background:#e30000;color:#fff;font-size:.6rem;font-weight:900;padding:1px 5px;border-radius:3px">TH</span> Gắn logo
          </button>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.65rem;min-width:0">
        <div class="af" style="grid-column:1/-1">
          <label style="display:flex;align-items:center;justify-content:space-between">
            Tên sản phẩm *
            <button type="button" onclick="aigImgSearch()" title="Tìm ảnh Google"
                    style="font-size:.65rem;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.3);color:#60a5fa;border-radius:5px;padding:2px 7px;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:.3rem">
              <i class="fa-brands fa-google"></i> Tìm ảnh
            </button>
          </label>
          <input type="text" name="name" id="ai-n" required class="form-inp">
        </div>
        <div class="af">
          <label>Danh mục *</label>
          <select name="category_id" id="ai-ct" required class="form-inp">
            <option value="">-- Chọn --</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" data-slug="<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="af">
          <label style="display:flex;align-items:center;justify-content:space-between">
            Thương hiệu
            <span id="brand-hint" style="font-size:.65rem;color:#22c55e;display:none">
              <i class="fa-solid fa-wand-magic-sparkles"></i> Tự động nhận dạng
            </span>
          </label>
          <select name="brand_id" id="ai-br" class="form-inp">
            <option value="">-- Chọn --</option>
            <?php foreach($brands as $b): ?>
            <option value="<?= $b['id'] ?>" data-nm="<?= strtolower(htmlspecialchars($b['name'])) ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="af">
          <label>Giá gốc (đ) *</label>
          <input type="number" name="price" id="ai-pr" required class="form-inp" min="100000" max="100000000" step="1000">
        </div>
        <div class="af">
          <label>Giá KM (đ)</label>
          <input type="number" name="sale_price" id="ai-sp" class="form-inp" min="0" max="100000000" step="1000" placeholder="0 nếu không KM">
        </div>
        <div class="af">
          <label>Tồn kho</label>
          <input type="number" name="stock" id="ai-st" class="form-inp" value="10" min="0">
        </div>
        <div class="af">
          <label>SKU / Mã SP</label>
          <input type="text" name="sku" id="ai-sk" class="form-inp">
        </div>
        <div class="af">
          <label>Bảo hành (tháng)</label>
          <input type="number" name="warranty" id="ai-wa" class="form-inp" value="12" min="0">
        </div>
      </div>

      <div class="af" style="grid-column:1/-1">
        <label>Mô tả ngắn (SEO)</label>
        <textarea name="short_desc" id="ai-sd" rows="2" class="form-inp" style="resize:vertical"></textarea>
      </div>
      <div class="af" style="grid-column:1/-1">
        <label>Mô tả chi tiết</label>
        <textarea name="description" id="ai-dd" rows="3" class="form-inp" style="resize:vertical"></textarea>
      </div>

      <div class="af" style="grid-column:1/-1">
        <label style="display:flex;align-items:center;justify-content:space-between">
          Thông số kỹ thuật
          <button type="button" onclick="addSpec()" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:.73rem;font-family:inherit"><i class="fa-solid fa-plus"></i> Thêm dòng</button>
        </label>
        <div id="spec-box"></div>
      </div>

      <!-- Extra images -->
      <div class="af" style="margin-bottom:.65rem;grid-column:1/-1">
        <label style="display:flex;align-items:center;justify-content:space-between">
          Ảnh mô tả thêm
          <span style="font-size:.65rem;color:#555">Tùy chọn — <i class="fa-solid fa-up-down-left-right" style="font-size:.55rem"></i> Kéo thả để sắp xếp thứ tự</span>
        </label>
        <label style="display:block;border:1.5px dashed #2a2a2a;border-radius:8px;padding:.75rem;text-align:center;cursor:pointer;transition:border-color .2s" onmouseover="this.style.borderColor='var(--red)'" onmouseout="this.style.borderColor='#2a2a2a'">
          <input type="file" id="extra-imgs-input" accept="image/*" multiple style="display:none" onchange="previewAIExtras(this)">
          <i class="fa-solid fa-images" style="font-size:1.3rem;color:#444;margin-bottom:.2rem;display:block"></i>
          <div style="font-size:.72rem;color:#555">Thêm nhiều ảnh mô tả sản phẩm</div>
        </label>
        <div id="ai-extra-preview" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.45rem"></div>
      </div>

      <div style="display:flex;gap:1rem;margin-bottom:.8rem;grid-column:1/-1">
        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.79rem;color:#777">
          <input type="checkbox" name="is_featured" value="1" style="accent-color:var(--red)"> Nổi bật
        </label>
        <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.79rem;color:#777">
          <input type="checkbox" name="is_new" value="1" checked style="accent-color:var(--red)"> Sản phẩm mới
        </label>
      </div>

      <div style="grid-column:1/-1"><button type="button" onclick="saveProd()" id="save-btn" style="width:100%;background:#22c55e;color:#fff;border:none;padding:.7rem;border-radius:9px;font-weight:700;font-size:.88rem;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:background .18s;box-shadow:0 4px 14px rgba(34,197,94,.2)" onmouseover="this.style.background='#16a34a'" onmouseout="this.style.background='#22c55e'">
        <i class="fa-solid fa-cloud-arrow-up"></i>Lưu sản phẩm vào Database
      </button>
      <div style="text-align:center;margin-top:.38rem;font-size:.71rem;color:#2a2a2a">
        Sản phẩm sẽ hiển thị ngay trên website sau khi lưu
      </div></div>
    </div>
  </div>
</div>

<script>

// ══ OVERLAY ═══════════════════════════════════════════════
function aiOv(type,title,sub,autoClose){
  var ic=document.getElementById('ai-ov-icon'),
      ti=document.getElementById('ai-ov-title'),
      sb=document.getElementById('ai-ov-sub'),
      cl=document.getElementById('ai-ov-close'),
      ov=document.getElementById('ai-overlay');
  if(type==='loading'){
    ic.style.background='rgba(96,165,250,.1)';
    ic.innerHTML='<div style="width:42px;height:42px;border:3px solid #1e3a5f;border-top-color:#60a5fa;border-radius:50%;animation:aiSpin .8s linear infinite"></div>';
    cl.style.display='none';
  } else if(type==='success'){
    ic.style.background='rgba(34,197,94,.1)';
    ic.innerHTML='<svg width="46" height="46" viewBox="0 0 46 46"><circle cx="23" cy="23" r="21" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-dasharray="132" stroke-dashoffset="132" style="animation:aiCircle .55s ease forwards"/><polyline points="14,23 21,30 32,16" fill="none" stroke="#22c55e" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="29" stroke-dashoffset="29" style="animation:aiCircle .38s .55s ease forwards"/></svg>';
    cl.style.display='none';
  } else if(type==='error'){
    ic.style.background='rgba(239,68,68,.1)';
    ic.innerHTML='<i class="fa-solid fa-circle-xmark" style="color:#f87171;font-size:2.4rem"></i>';
    cl.style.display='block';
  } else if(type==='warn'||type==='dup'){
    ic.style.background='rgba(251,191,36,.1)';
    ic.innerHTML='<i class="fa-solid '+(type==='dup'?'fa-copy':'fa-triangle-exclamation')+'" style="color:#fbbf24;font-size:2.4rem"></i>';
    cl.style.display='block';
  }
  ti.textContent=title||''; sb.textContent=sub||'';
  ov.style.display='flex';
  if(autoClose) setTimeout(aiCloseOv,autoClose);
}
function aiCloseOv(){ document.getElementById('ai-overlay').style.display='none'; }
document.getElementById('ai-overlay').addEventListener('click',function(e){if(e.target===this)aiCloseOv();});

// ══ DUPLICATE CHECK ════════════════════════════════════════
async function aiCheckDup(name,sku){
  try{
    var r=await fetch(APP_URL+'/api/ai/check-duplicate',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:name,sku:sku||''})});
    var txt=await r.text(); return JSON.parse(txt);
  }catch(e){return{duplicate:false};}
}

// ══ LIVE PREVIEW ENGINE ════════════════════════════════════
var lpTimers=[];
function lpClear(){
  lpTimers.forEach(clearTimeout); lpTimers=[];
  ['lp-name','lp-cat','lp-brand','lp-price','lp-stock','lp-warr','lp-desc'].forEach(function(id){
    var el=document.getElementById(id);
    if(el) el.innerHTML='<div class="lp-shim"></div><div class="lp-shim" style="width:60%"></div>';
  });
  var ls=document.getElementById('lp-specs'); if(ls) ls.innerHTML='';
  document.getElementById('lp-panel').style.display='block';
  document.getElementById('ai-ph').style.display='none';
}
function lpSetStep(txt){ var el=document.getElementById('lp-step'); if(el) el.textContent=txt; }
function lpHide(){ document.getElementById('lp-panel').style.display='none'; }

function lpType(elId, text, delay, cb){
  var el=document.getElementById(elId); if(!el||!text)return;
  el.classList.add('lp-on');
  el.innerHTML='<span class="lp-txt lp-cursor"></span>';
  var sp=el.querySelector('span'), i=0, txt=String(text);
  function next(){
    if(i<txt.length){ sp.textContent+=txt[i++]; lpTimers.push(setTimeout(next,18+Math.random()*35)); }
    else{ sp.classList.remove('lp-cursor'); el.classList.remove('lp-on'); if(cb)cb(); }
  }
  lpTimers.push(setTimeout(next,delay||0));
}

function lpCount(elId, target, delay, suffix, cb){
  var el=document.getElementById(elId); if(!el)return;
  el.classList.add('lp-on');
  el.innerHTML='<span class="lp-num">0'+(suffix||'')+'</span>';
  var sp=el.querySelector('span'), v=0, tgt=Number(target)||0;
  if(tgt===0){sp.textContent='0'+(suffix||'');el.classList.remove('lp-on');if(cb)cb();return;}
  var steps=28, dur=800;
  function tick(){
    v=Math.min(v+Math.ceil(tgt/steps),tgt);
    sp.textContent=v.toLocaleString('vi-VN')+(suffix||'');
    if(v<tgt){ lpTimers.push(setTimeout(tick,dur/steps)); }
    else{ el.classList.remove('lp-on'); if(cb)cb(); }
  }
  lpTimers.push(setTimeout(tick,delay||0));
}

function lpSpecs(specs, delay){
  if(!specs||typeof specs!=='object')return;
  var box=document.getElementById('lp-specs'); if(!box)return;
  box.innerHTML='';
  Object.entries(specs).forEach(function(kv,i){
    lpTimers.push(setTimeout(function(){
      var row=document.createElement('div'); row.className='lp-spec-row';
      row.innerHTML='<span class="lp-spec-k">'+kv[0]+'</span><span class="lp-spec-v">— </span>';
      box.appendChild(row);
      var vEl=row.querySelector('.lp-spec-v'), vi=0, vtxt=String(kv[1]);
      function typeV(){ if(vi<vtxt.length){vEl.textContent+=vtxt[vi++];lpTimers.push(setTimeout(typeV,16+Math.random()*26));} }
      setTimeout(typeV,60);
    }, delay+i*210));
  });
}

function lpAnimate(data){
  lpClear();
  var d=0, gap=320;
  var nlen=(data.name||'').length;

  lpSetStep('Nhận diện sản phẩm...');
  if(data.name){ (function(dl){lpTimers.push(setTimeout(function(){lpSetStep('Đặt tên...');lpType('lp-name',data.name,0);},dl));})(d); d+=Math.max(nlen*28+150,700); }

  // Category
  (function(dl){lpTimers.push(setTimeout(function(){
    lpSetStep('Phân loại danh mục...');
    var el=document.getElementById('lp-cat');
    if(el){
      var txt=''; var cs=document.getElementById('ai-ct');
      if(data.category_id){for(var i=0;i<cs.options.length;i++){if(parseInt(cs.options[i].value)===parseInt(data.category_id)){txt=cs.options[i].text;break;}}}
      if(!txt) txt=data.category_slug||'';
      el.innerHTML='<span class="lp-txt lp-cursor">'+txt+'</span>';
      setTimeout(function(){var s=el.querySelector('span');if(s)s.classList.remove('lp-cursor');},350);
    }
  },dl));})(d); d+=gap;

  // Brand
  (function(dl){lpTimers.push(setTimeout(function(){
    lpSetStep('Nhận dạng thương hiệu...');
    var el=document.getElementById('lp-brand');
    if(el){ el.innerHTML='<span class="lp-txt lp-cursor">'+(data.brand||'')+'</span>'; setTimeout(function(){var s=el.querySelector('span');if(s)s.classList.remove('lp-cursor');},350); }
  },dl));})(d); d+=gap;

  // Price count-up
  (function(dl){lpTimers.push(setTimeout(function(){lpSetStep('Ước tính giá...');lpCount('lp-price',data.price,0,'đ');},dl));})(d); d+=700;

  // Stock + warranty
  (function(dl){lpTimers.push(setTimeout(function(){lpSetStep('Tồn kho & bảo hành...');lpCount('lp-stock',data.stock||10,0,' SP');lpCount('lp-warr',data.warranty||24,100,' tháng');},dl));})(d); d+=550;

  // Description
  (function(dl){lpTimers.push(setTimeout(function(){lpSetStep('Viết mô tả...');lpType('lp-desc',data.short_desc||'',0);},dl));})(d); d+=(data.short_desc||'').length*26+300;

  // Specs
  (function(dl){lpTimers.push(setTimeout(function(){lpSetStep('Trích xuất thông số...');lpSpecs(data.specs,0);},dl));})(d); d+=Object.keys(data.specs||{}).length*230+400;

  // Done
  (function(dl){lpTimers.push(setTimeout(function(){lpSetStep('✓ Hoàn tất phân tích');},dl));})(d);

  return d; // total duration
}

// ══ GENERATE FROM NAME ════════════════════════════════════
async function genFromName(){
  var name=document.getElementById('ai-name-inp').value.trim();
  if(!name){aiOv('error','Chưa nhập tên','Vui lòng nhập tên sản phẩm cần tìm',2000);return;}
  var btn=event.target.closest('button');
  if(btn){btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang tìm...';}
  aiOv('loading','AI đang tìm kiếm...','');
  try{
    var r=await fetch(APP_URL+'/api/ai/generate-from-name',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_name:name})});
    var txt=await r.text(); var data;
    try{data=JSON.parse(txt);}catch(e){aiCloseOv();aiOv('error','Lỗi server',txt.replace(/<[^>]+>/g,'').substring(0,120));if(btn){btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-magnifying-glass-chart"></i> AI tìm kiếm từ tên sản phẩm';}return;}
    if(btn){btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-magnifying-glass-chart"></i> AI tìm kiếm từ tên sản phẩm';}
    if(data.success){
      aiCloseOv();
      var dur=lpAnimate(data.data);
      setTimeout(function(){lpHide();fillF(data.data);aiOv('success','Tìm thấy thông tin!','Kiểm tra và lưu sản phẩm',2000);}, Math.max(dur+200,4000));
    } else { aiCloseOv();aiOv('error','Không tìm được',data.message||'Thử nhập tên đầy đủ hơn'); }
  }catch(err){aiCloseOv();aiOv('error','Lỗi kết nối',err.message);if(btn){btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-magnifying-glass-chart"></i> AI tìm kiếm từ tên sản phẩm';}}
}


var aiD='', aiU='', aiM='image/jpeg', savedImgFile='';
var APP_URL='<?= APP_URL ?>';
var _aiRunning=false, _aiSaving=false;
var aiExtraFiles=[]; // [{file:File, url:string}] — reorderable
var _dragSrcIdx=-1;

// ── TABS ────────────────────────────────────────────────
function aiT(t){
  ['u','l','c','n'].forEach(function(x){
    var el=document.getElementById('at-'+x), tab=document.getElementById('ait-'+x);
    if(el) el.style.display=x===t?'block':'none';
    if(tab) tab.classList.toggle('on',x===t);
  });
}

// ── FILE LOAD ────────────────────────────────────────────
function loadF(inp){
  if(!inp.files||!inp.files[0])return;
  var f=inp.files[0];
  if(f.size>10*1024*1024){showToast('File quá lớn (max 10MB)','err');return;}
  aiM=f.type||'image/jpeg';
  var r=new FileReader();
  r.onload=function(e){
    aiD=e.target.result; aiU=''; savedImgFile='';
    var p=document.getElementById('up-p'); p.src=aiD; p.style.display='block';
    document.getElementById('dz-c').style.display='none';
    document.getElementById('up-info').style.display='flex';
    document.getElementById('up-fn').textContent=f.name;
    document.getElementById('up-sz').textContent='('+Math.round(f.size/1024)+'KB)';
    showToast('Ảnh sẵn sàng — nhấn Phân tích!','ok');
  };
  r.readAsDataURL(f);
}

function onDrop(e){
  e.preventDefault(); document.getElementById('dz').classList.remove('drag');
  var f=e.dataTransfer.files[0];
  if(f&&f.type.startsWith('image/')){
    var dt=new DataTransfer(); dt.items.add(f);
    document.getElementById('fi').files=dt.files; loadF(document.getElementById('fi'));
  }
}

function prevURL(){
  var u=document.getElementById('ul').value.trim(); if(!u)return;
  aiU=u; aiD=''; savedImgFile=''; aiM='image/jpeg';
  var img=document.getElementById('ul-p'); img.src=u; img.style.display='block';
  document.getElementById('ul-ph').style.display='none';
  showToast('URL sẵn sàng — nhấn Phân tích!','ok');
}

// ── CLIPBOARD ───────────────────────────────────────────
document.addEventListener('paste',function(e){
  // Only capture image paste if not typing in a text field
  var tag=(document.activeElement||{}).tagName||'';
  if(tag==='INPUT'||tag==='TEXTAREA') return;
  var items=e.clipboardData.items;
  for(var i=0;i<items.length;i++){
    if(items[i].type.indexOf('image')!==-1){
      var blob=items[i].getAsFile(); aiM=blob.type||'image/png';
      var r=new FileReader();
      r.onload=function(ev){
        aiD=ev.target.result; aiU=''; savedImgFile='';
        var p=document.getElementById('cp-p'); p.src=aiD; p.style.display='block';
        aiT('c'); showToast('Đã dán ảnh từ clipboard — nhấn Phân tích!','ok');
      };
      r.readAsDataURL(blob); break;
    }
  }
});
function doPaste(){
  if(navigator.clipboard&&navigator.clipboard.read){
    navigator.clipboard.read().then(function(items){
      for(var item of items){
        for(var type of item.types){
          if(type.startsWith('image/')){
            item.getType(type).then(function(blob){
              aiM=blob.type;
              var r=new FileReader();
              r.onload=function(e){ aiD=e.target.result; aiU=''; savedImgFile='';
                var p=document.getElementById('cp-p'); p.src=aiD; p.style.display='block';
                showToast('Đã dán!','ok');
              };
              r.readAsDataURL(blob);
            }); return;
          }
        }
      }
    }).catch(function(){ showToast('Dùng Ctrl+V để dán ảnh','ok'); });
  } else showToast('Dùng Ctrl+V để dán ảnh','ok');
}

// ── SAVE IMAGE TO SERVER ─────────────────────────────────
async function saveImgToServer(){
  var payload={};
  if(aiD){ payload.image_b64=aiD; payload.image_mime=aiM; }
  else if(aiU){ payload.image_url=aiU; }
  else return null;
  try{
    var r=await fetch(APP_URL+'/api/ai/save-image',{
      method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)
    });
    var rawText=await r.text();
    var d;
    try{ d=JSON.parse(rawText); }
    catch(e){ console.error('save-image parse error, raw:',rawText.substring(0,200)); return null; }
    if(d.success){
      savedImgFile=d.filename;
      document.getElementById('ai-img-file').value=d.filename;
      var badge=document.getElementById('aif-saved-badge');
      var bname=document.getElementById('aif-saved-name');
      if(badge&&bname){ bname.textContent=d.filename; badge.style.display='flex'; }
      return d.filename;
    }
  }catch(e){ console.error('save-image error:',e); }
  return null;
}

// ── MAIN GENERATE ────────────────────────────────────────
var _genController=null; // AbortController cho request hiện tại

function _genReset(btn){
  _aiRunning=false;
  stopScanEffect();
  if(btn){ btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-wand-magic-sparkles"></i>Phân tích ảnh & Tự động điền thông tin'; }
  document.getElementById('ai-pg').style.display='none';
}

async function doGen(){
  if(_aiRunning){
    showToast('Đang xử lý, vui lòng chờ...','ok');
    return;
  }
  if(!aiD&&!aiU){ showToast('Vui lòng upload hoặc nhập URL ảnh trước!','err'); return; }

  _aiRunning=true;
  var snapD=aiD, snapU=aiU, snapM=aiM;
  var btn=document.getElementById('gen-btn');
  btn.disabled=true;
  btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> AI đang phân tích... <span id="gen-elapsed" style="font-size:.7rem;opacity:.7"></span>';

  // Hiện thị thời gian chờ
  var elapsed=0;
  var elTimer=setInterval(function(){
    elapsed++;
    var el=document.getElementById('gen-elapsed');
    if(el) el.textContent='('+elapsed+'s)';
  },1000);

  // Lưu ảnh preview
  var _prev=document.getElementById('aif-img-src');
  var _wrap=document.getElementById('aif-img');
  if(_prev&&_wrap&&(snapD||snapU)){ _prev.src=snapD||snapU; _wrap.style.display='block'; }

  startScanEffect();
  document.getElementById('ai-pg').style.display='block';
  lpClear(); lpSetStep('Chuẩn bị...');

  var steps=['Lưu hình ảnh...','Gửi đến Groq AI...','Nhận diện sản phẩm...','Trích xuất thông số...','Tạo mô tả...','Hoàn tất...'];
  var si=0, stBox=document.getElementById('pg-steps');
  stBox.innerHTML='';
  steps.forEach(function(s,i){
    var d=document.createElement('div'); d.className='ps'; d.id='ps'+i;
    d.innerHTML='<i class="fa-regular fa-circle-dot" style="font-size:.6rem"></i>'+s;
    stBox.appendChild(d);
  });
  var iv=setInterval(function(){
    if(si>0){var p=document.getElementById('ps'+(si-1));if(p){p.className='ps done';p.innerHTML='<i class="fa-solid fa-check" style="font-size:.6rem"></i>'+steps[si-1];}}
    if(si<steps.length){var cc=document.getElementById('ps'+si);if(cc){cc.className='ps act';cc.innerHTML='<i class="fa-solid fa-spinner fa-spin" style="font-size:.6rem"></i>'+steps[si];}}
    document.getElementById('pg-bar').style.width=Math.min((si+1)/steps.length*75,75)+'%';
    si++; if(si>=steps.length)clearInterval(iv);
  },700);

  // AbortController để có thể hủy nếu cần
  _genController=new AbortController();

  try{
    // Bước 1: Lưu ảnh
    if(!savedImgFile){
      var savePayload={};
      if(snapD){ savePayload.image_b64=snapD; savePayload.image_mime=snapM; }
      else if(snapU){ savePayload.image_url=snapU; }
      try{
        var sr=await fetch(APP_URL+'/api/ai/save-image',{
          method:'POST',headers:{'Content-Type':'application/json'},
          body:JSON.stringify(savePayload), signal:_genController.signal
        });
        var sd=JSON.parse(await sr.text());
        if(sd&&sd.success){ savedImgFile=sd.filename; document.getElementById('ai-img-file').value=sd.filename; }
      }catch(e){ if(e.name==='AbortError') throw e; console.warn('save-image:',e); }
    }

    // Bước 2: Gọi AI
    var b64=snapD?snapD.replace(/^data:[^;]+;base64,/i,''):'';
    var resp=await fetch(APP_URL+'/api/ai/generate',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({image_b64:b64,image_mime:snapM,url:snapU}),
      signal:_genController.signal
    });
    var rawText=await resp.text();
    var data;
    try{ data=JSON.parse(rawText); }
    catch(e){
      clearInterval(iv); clearInterval(elTimer);
      _genReset(btn);
      var hint=rawText.indexOf('AI_API_KEY')>=0?'Kiểm tra AI_API_KEY':
               (rawText.indexOf('Fatal')>=0||rawText.indexOf('Warning')>=0)?'Lỗi PHP: '+rawText.replace(/<[^>]+>/g,'').substring(0,150):
               'Lỗi server (HTTP '+resp.status+')';
      aiOv('error','Lỗi server',hint); return;
    }

    clearInterval(iv); clearInterval(elTimer); stopScanEffect();
    steps.forEach(function(s,i){var el=document.getElementById('ps'+i);if(el){el.className='ps done';el.innerHTML='<i class="fa-solid fa-check" style="font-size:.6rem"></i>'+s;}});
    document.getElementById('pg-bar').style.width='100%';

    if(data.success){
      document.getElementById('ai-pg').style.display='none';
      var dur=lpAnimate(data.data);
      // Lock giữ nguyên cho đến khi animation xong & form hiển thị
      setTimeout(function(){
        _genReset(btn);
        lpHide(); fillF(data.data);
        aiOv('success','AI nhận diện xong!','Kiểm tra thông tin bên phải',2200);
      }, Math.max(dur+200,3500));
    } else {
      _genReset(btn);
      aiOv('error','AI lỗi',data.message||'Không rõ nguyên nhân');
    }

  }catch(err){
    clearInterval(iv); clearInterval(elTimer);
    _genReset(btn);
    if(err.name!=='AbortError') aiOv('error','Lỗi kết nối',err.message);
  }
}

// ── SCAN EFFECT trên ảnh ─────────────────────────────────
function startScanEffect(){
  var dz=document.getElementById('dz');
  var cp=document.getElementById('cp-p');
  var ul=document.getElementById('ul-p');
  // Tìm container ảnh đang hiển thị
  var target = (aiD && document.getElementById('up-p').style.display!=='none') ? document.getElementById('dz')
             : (aiD && cp && cp.style.display!=='none') ? cp.parentElement
             : (aiU && ul && ul.style.display!=='none') ? ul.parentElement : null;
  if(!target) target=dz;
  if(!target) return;
  stopScanEffect();
  target.style.position='relative';
  var wrap=document.createElement('div'); wrap.id='scan-wrap';
  wrap.innerHTML='<div id="scan-line"></div>'
    +'<div id="scan-corners"><span></span><span></span><span></span><span></span></div>'
    +'<div id="scan-overlay"></div>'
    +'<div id="scan-label">AI SCANNING...</div>';
  target.appendChild(wrap);
}
function stopScanEffect(){
  var w=document.getElementById('scan-wrap');
  if(w) w.remove();
}

// ── GLOW HELPER ──────────────────────────────────────────
function aiGlow(id, delay){
  setTimeout(function(){
    var el=document.getElementById(id); if(!el) return;
    el.classList.remove('ai-filled');
    el.classList.add('ai-filling');
    setTimeout(function(){ el.classList.remove('ai-filling'); el.classList.add('ai-filled'); }, 750);
  }, delay);
}

// ── EXPLODED VIEW (SVG lines từ ảnh đến các field) ───────
function showExplodedView(){
  var imgEl=document.getElementById('aif-img-src');
  if(!imgEl||!imgEl.src||imgEl.style.display==='none') return;
  var imgRect=imgEl.getBoundingClientRect();
  if(!imgRect.width) return;
  var cx=imgRect.left+imgRect.width/2, cy=imgRect.top+imgRect.height/2;

  var old=document.getElementById('expl-svg'); if(old) old.remove();
  var svg=document.createElementNS('http://www.w3.org/2000/svg','svg');
  svg.id='expl-svg';
  svg.style.cssText='position:fixed;inset:0;width:100vw;height:100vh;pointer-events:none;z-index:500;overflow:visible;';
  svg.innerHTML='<defs>'
    +'<filter id="exgl"><feGaussianBlur stdDeviation="2.5" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>'
    +'<marker id="exarr" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><polygon points="0 0,7 3.5,0 7" fill="#60a5fa" opacity=".8"/></marker>'
    +'</defs>';
  document.body.appendChild(svg);

  var targets=[
    {id:'ai-n', label:'Tên sản phẩm', color:'#ff6b6b'},
    {id:'ai-ct',label:'Danh mục',      color:'#60a5fa'},
    {id:'ai-br',label:'Thương hiệu',   color:'#f472b6'},
    {id:'ai-pr',label:'Giá bán',       color:'#4ade80'},
    {id:'ai-sk',label:'SKU',           color:'#fbbf24'},
    {id:'ai-sd',label:'Mô tả',         color:'#c084fc'},
  ];

  targets.forEach(function(t,i){
    var el=document.getElementById(t.id); if(!el) return;
    var r=el.getBoundingClientRect();
    var tx=r.left+8, ty=r.top+r.height/2;
    var cpx=(cx+tx)/2+(i%2===0?-70:70), cpy=(cy+ty)/2-50;

    var path=document.createElementNS('http://www.w3.org/2000/svg','path');
    var d='M'+cx+','+cy+' Q'+cpx+','+cpy+' '+tx+','+ty;
    var len=1200;
    path.setAttribute('d',d); path.setAttribute('stroke',t.color);
    path.setAttribute('stroke-width','1.5'); path.setAttribute('fill','none');
    path.setAttribute('stroke-dasharray',len); path.setAttribute('stroke-dashoffset',len);
    path.setAttribute('filter','url(#exgl)'); path.setAttribute('opacity','.85');
    path.style.transition='stroke-dashoffset .65s cubic-bezier(.4,0,.2,1) '+(i*.18)+'s';

    var dot=document.createElementNS('http://www.w3.org/2000/svg','circle');
    dot.setAttribute('cx',tx); dot.setAttribute('cy',ty); dot.setAttribute('r','4');
    dot.setAttribute('fill',t.color); dot.setAttribute('opacity','0');
    dot.setAttribute('filter','url(#exgl)');
    dot.style.transition='opacity .3s '+(i*.18+.5)+'s';

    var lbl=document.createElementNS('http://www.w3.org/2000/svg','text');
    lbl.setAttribute('x',tx-8); lbl.setAttribute('y',ty-9);
    lbl.setAttribute('fill',t.color); lbl.setAttribute('font-size','9.5');
    lbl.setAttribute('font-weight','700'); lbl.setAttribute('text-anchor','end');
    lbl.setAttribute('font-family','inherit'); lbl.setAttribute('opacity','0');
    lbl.textContent=t.label;
    lbl.style.transition='opacity .3s '+(i*.18+.6)+'s';

    svg.appendChild(path); svg.appendChild(dot); svg.appendChild(lbl);

    requestAnimationFrame(function(){
      setTimeout(function(){
        path.style.strokeDashoffset='0';
        dot.setAttribute('opacity','0.9');
        lbl.setAttribute('opacity','1');
      },30);
    });
  });

  // Pulse ở tâm ảnh
  var pulse=document.createElementNS('http://www.w3.org/2000/svg','circle');
  pulse.setAttribute('cx',cx); pulse.setAttribute('cy',cy); pulse.setAttribute('r','8');
  pulse.setAttribute('fill','none'); pulse.setAttribute('stroke','#ff6b6b');
  pulse.setAttribute('stroke-width','2'); pulse.setAttribute('opacity','.7');
  pulse.style.cssText='animation:exPulse 1s ease-out infinite';
  var style=document.createElement('style');
  style.textContent='@keyframes exPulse{0%{r:8;opacity:.7}100%{r:28;opacity:0}}';
  document.head.appendChild(style);
  svg.appendChild(pulse);

  setTimeout(function(){
    svg.style.transition='opacity .8s'; svg.style.opacity='0';
    setTimeout(function(){svg.remove();},800);
  },3600);
}

function fillF(data){
  document.getElementById('ai-ph').style.display='none';
  document.getElementById('ai-form').style.display='block';
  // After scan: hide left panel, show full-width result
  var aig=document.querySelector('.aig');
  var leftCol=document.querySelector('.aig>div:first-child');
  if(aig){aig.style.display='block';}
  if(leftCol){leftCol.style.display='none';}
  var res=document.querySelector('.aig-result');
  if(res){res.style.display='block';res.scrollIntoView({behavior:'smooth',block:'start'});}
  if(document.getElementById('ai-demo-badge')) document.getElementById('ai-demo-badge').style.display='none';
  if(aiD||aiU){ document.getElementById('aif-img-src').src=aiD||aiU; document.getElementById('aif-img').style.display='block'; }

  // Exploded view lines
  setTimeout(showExplodedView, 150);

  // Glow hiệu ứng từng field
  var glowFields=['ai-n','ai-ct','ai-br','ai-pr','ai-sp','ai-st','ai-sk','ai-wa','ai-sd','ai-dd'];
  glowFields.forEach(function(id,i){ aiGlow(id, i*120); });

  function sv(id,v){var el=document.getElementById(id);if(el&&v!=null&&v!==''&&v!==0)el.value=v;}
  sv('ai-n',data.name); sv('ai-pr',data.price);
  sv('ai-sp',data.sale_price>0?data.sale_price:'');
  sv('ai-st',data.stock||10); sv('ai-sk',data.sku);
  sv('ai-wa',data.warranty||24);
  sv('ai-sd',data.short_desc); sv('ai-dd',data.description);

  // ── Chọn danh mục: ưu tiên category_id từ AI (ID chính xác từ DB) ──
  var cs=document.getElementById('ai-ct');
  var catMatched=false;
  if(data.category_id&&parseInt(data.category_id)>0){
    for(var i=0;i<cs.options.length;i++){
      if(parseInt(cs.options[i].value)===parseInt(data.category_id)){
        cs.selectedIndex=i; catMatched=true; break;
      }
    }
  }
  // Fallback: match theo slug nếu không có category_id
  if(!catMatched&&data.category_slug){
    var sl=data.category_slug.toLowerCase();
    for(var i=0;i<cs.options.length;i++){
      var o=cs.options[i],os=(o.dataset.slug||'').toLowerCase();
      if(os===sl||sl.includes(os.replace(/-/g,''))||os.includes(sl.replace(/-/g,''))){cs.selectedIndex=i;catMatched=true;break;}
    }
  }

  // ── Chọn thương hiệu: ưu tiên brand_id từ AI (ID chính xác từ DB) ──
  var bs=document.getElementById('ai-br');
  var brandMatched=false;
  if(data.brand_id&&parseInt(data.brand_id)>0){
    for(var i=0;i<bs.options.length;i++){
      if(parseInt(bs.options[i].value)===parseInt(data.brand_id)){
        bs.selectedIndex=i; brandMatched=true; break;
      }
    }
  }
  // Fallback: fuzzy match theo tên brand nếu không có brand_id
  if(!brandMatched&&data.brand){
    var bn=data.brand.toLowerCase().trim();
    var best=-1,bestScore=0;
    for(var i=0;i<bs.options.length;i++){
      var nm=bs.options[i].dataset.nm||''; if(!nm)continue;
      var score=0;
      if(nm===bn)score=100; else if(nm.includes(bn)||bn.includes(nm))score=80;
      else{bn.split(/\s+/).forEach(function(p){if(p.length>1&&nm.includes(p))score+=25;});}
      if(score>bestScore){bestScore=score;best=i;}
    }
    if(best>=0&&bestScore>=25)bs.selectedIndex=best;
  }

  document.getElementById('spec-box').innerHTML='';
  if(data.specs&&typeof data.specs==='object'){
    Object.entries(data.specs).forEach(function(kv){addSpec(kv[0],kv[1]);});
  }
  if(!document.getElementById('spec-box').children.length) addSpec();

}

function addSpec(k,v){
  var c=document.getElementById('spec-box'),r=document.createElement('div');
  r.className='spec-r';
  r.innerHTML='<input type="text" name="spec_key[]" value="'+(k||'')+'" placeholder="Tên thông số" class="form-inp" style="font-size:.75rem;padding:.36rem .5rem">'
    +'<input type="text" name="spec_val[]" value="'+(v||'')+'" placeholder="Giá trị" class="form-inp" style="font-size:.75rem;padding:.36rem .5rem">'
    +'<button type="button" onclick="this.parentElement.remove()" style="width:24px;height:24px;background:rgba(239,68,68,.1);border:none;border-radius:5px;color:#f87171;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.68rem"><i class="fa-solid fa-xmark"></i></button>';
  c.appendChild(r);
}

// ── SAVE PRODUCT ─────────────────────────────────────────
async function saveProd(){
  if(_aiSaving) return;
  var name=document.getElementById('ai-n').value.trim();
  if(!name){document.getElementById('ai-n').focus();return;}
  _aiSaving=true;
  var priceVal=parseFloat(document.getElementById('ai-pr').value)||0;
  if(priceVal<100000||priceVal>100000000){
    _aiSaving=false;
    aiOv('error','Giá không hợp lệ','Giá phải từ 100.000đ đến 100.000.000đ');
    document.getElementById('ai-pr').focus(); return;
  }
  // Check duplicate first
  aiOv('loading','Kiểm tra trùng lặp...','');
  var sku=document.getElementById('ai-sk').value.trim();
  var dup=await aiCheckDup(name,sku);
  if(dup.duplicate){ aiOv('dup','Sản phẩm đã tồn tại',dup.message); return; }
  aiCloseOv();
  if(!savedImgFile&&(aiD||aiU)){ showToast('Đang lưu ảnh...','ok'); await saveImgToServer(); }

  var specs={};
  document.querySelectorAll('.spec-r').forEach(function(row){
    var k=row.querySelector('[name="spec_key[]"]')?.value.trim();
    var v=row.querySelector('[name="spec_val[]"]')?.value.trim();
    if(k&&v)specs[k]=v;
  });
  var payload={
    name:name,
    category_id:document.getElementById('ai-ct').value,
    brand_id:document.getElementById('ai-br').value||null,
    price:parseFloat(document.getElementById('ai-pr').value)||0,
    sale_price:parseFloat(document.getElementById('ai-sp').value)||0,
    stock:parseInt(document.getElementById('ai-st').value)||10,
    sku:document.getElementById('ai-sk').value.trim()||('AI-'+Date.now().toString(36).toUpperCase()),
    warranty:parseInt(document.getElementById('ai-wa').value)||24,
    short_desc:document.getElementById('ai-sd').value.trim(),
    description:document.getElementById('ai-dd').value.trim(),
    specs:specs,
    image_filename:savedImgFile||document.getElementById('ai-img-file').value||'',
    is_featured:0,
  };
  var btn=document.getElementById('save-btn');
  btn.disabled=true; btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
  try{
    var resp=await fetch(APP_URL+'/api/ai/save-product',{
      method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)
    });
    var rawText=await resp.text();
    var data;
    try{ data=JSON.parse(rawText); }
    catch(e){
      showToast('❌ Lỗi server: '+rawText.replace(/<[^>]+>/g,'').substring(0,150),'err');
      btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-cloud-arrow-up"></i>Lưu sản phẩm vào Database';
      return;
    }
    if(data.success){
      // Upload extra images if any (in reordered order)
      if(aiExtraFiles.length>0&&data.product_id){
        btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang tải ảnh phụ...';
        await uploadExtraImages(data.product_id);
      }
      aiOv('success','Lưu thành công!','Sản phẩm đã được đăng lên website');
      setTimeout(function(){location.reload();},1800);
    } else {
      _aiSaving=false;
      aiOv('error','Lỗi lưu sản phẩm',data.message||'Không xác định');
      btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-cloud-arrow-up"></i>Lưu sản phẩm vào Database';
    }
  }catch(err){
    _aiSaving=false;
    aiOv('error','Lỗi kết nối',err.message);
    btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-cloud-arrow-up"></i>Lưu sản phẩm vào Database';
  }
}

// Upload extra images via FormData (multipart) — uses aiExtraFiles in order
async function uploadExtraImages(productId){
  if(!aiExtraFiles.length) return;
  var fd=new FormData();
  fd.append('product_id', productId);
  aiExtraFiles.forEach(function(item){ fd.append('extra_images[]', item.file); });
  try{
    var r=await fetch(APP_URL+'/api/ai/upload-extra-images',{method:'POST',body:fd});
    var txt=await r.text();
    try{ var d=JSON.parse(txt); if(!d.success) console.warn('Extra images:',d.message); }
    catch(e){ console.warn('Extra images upload response:', txt.substring(0,100)); }
  }catch(e){ console.warn('Extra images upload error:',e); }
}

// Add files to aiExtraFiles array and render
function previewAIExtras(inp){
  var files=Array.from(inp.files);
  inp.value=''; // allow re-selecting same files
  if(!files.length) return;
  var pending=files.length;
  files.forEach(function(f){
    var r=new FileReader();
    r.onload=function(e){
      aiExtraFiles.push({file:f, url:e.target.result});
      pending--;
      if(pending===0) renderExtraPreviews();
    };
    r.readAsDataURL(f);
  });
}

// Render sortable thumbnails
function renderExtraPreviews(){
  var box=document.getElementById('ai-extra-preview');
  box.innerHTML='';
  aiExtraFiles.forEach(function(item,i){
    var d=document.createElement('div');
    d.className='ai-extra-item';
    d.draggable=true;
    d.dataset.idx=String(i);
    d.style.cssText='position:relative;width:72px;height:72px;flex-shrink:0;cursor:grab;border:2px solid transparent;border-radius:9px;transition:border-color .15s,opacity .15s;user-select:none';
    d.innerHTML='<img src="'+item.url+'" style="width:100%;height:100%;object-fit:cover;border-radius:7px;pointer-events:none">'
      +'<button type="button" onclick="removeExtraImg('+i+')" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;background:#ef4444;border:none;border-radius:50%;color:#fff;font-size:.6rem;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:2;padding:0"><i class="fa-solid fa-xmark"></i></button>'
      +'<span style="position:absolute;bottom:3px;left:3px;background:rgba(0,0,0,.75);color:#fff;font-size:.52rem;padding:1px 5px;border-radius:3px;pointer-events:none;font-weight:700">'+(i+1)+'</span>'
      +'<div style="position:absolute;top:3px;left:50%;transform:translateX(-50%);pointer-events:none"><i class="fa-solid fa-grip-dots" style="color:rgba(255,255,255,.55);font-size:.6rem"></i></div>';
    d.addEventListener('dragstart', onExtraDragStart);
    d.addEventListener('dragover',  onExtraDragOver);
    d.addEventListener('dragleave', onExtraDragLeave);
    d.addEventListener('drop',      onExtraDrop);
    d.addEventListener('dragend',   onExtraDragEnd);
    box.appendChild(d);
  });
}

function removeExtraImg(idx){
  aiExtraFiles.splice(idx,1);
  renderExtraPreviews();
}

function onExtraDragStart(e){
  _dragSrcIdx=parseInt(this.dataset.idx);
  this.style.opacity='0.4';
  e.dataTransfer.effectAllowed='move';
}
function onExtraDragOver(e){
  e.preventDefault();
  e.dataTransfer.dropEffect='move';
  this.style.borderColor='var(--red)';
  this.style.background='rgba(227,0,0,.08)';
}
function onExtraDragLeave(){
  this.style.borderColor='transparent';
  this.style.background='';
}
function onExtraDrop(e){
  e.preventDefault();
  var targetIdx=parseInt(this.dataset.idx);
  if(_dragSrcIdx===targetIdx){ this.style.borderColor='transparent'; this.style.background=''; return; }
  var moved=aiExtraFiles.splice(_dragSrcIdx,1)[0];
  aiExtraFiles.splice(targetIdx,0,moved);
  renderExtraPreviews();
}
function onExtraDragEnd(){
  renderExtraPreviews(); // restore opacity/border on all items
}

// ── Đảm bảo ảnh đã lưu → trả về filename ────────────────
async function aigEnsureSaved(){
  if(savedImgFile) return savedImgFile;
  if(!aiD&&!aiU){ showToast('Chưa có ảnh để xử lý','err'); return null; }
  var payload={};
  if(aiD){ payload.image_b64=aiD; payload.image_mime=aiM; }
  else     payload.image_url=aiU;
  try{
    var r=await fetch(APP_URL+'/api/ai/save-image',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    });
    var d=await r.json();
    if(d.success){ savedImgFile=d.filename; document.getElementById('ai-img-file').value=d.filename; return d.filename; }
    showToast('Lỗi lưu ảnh: '+(d.message||''),'err');
  }catch(e){ showToast('Lỗi: '+e.message,'err'); }
  return null;
}

// ── Tách nền / Gắn logo ───────────────────────────────────
async function aigProcessImage(action){
  var filename=await aigEnsureSaved();
  if(!filename) return;
  var btn=document.getElementById(action==='remove-bg'?'aig-rmbg-btn':'aig-wm-btn');
  var origHtml=btn.innerHTML;
  btn.disabled=true;
  btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';
  try{
    var r=await fetch(APP_URL+'/api/ai/'+action,{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({filename:filename})
    });
    var d=await r.json();
    btn.disabled=false; btn.innerHTML=origHtml;
    if(d.success){
      savedImgFile=d.filename;
      document.getElementById('ai-img-file').value=d.filename;
      var prev=document.getElementById('aif-img-src');
      if(prev){ prev.src=d.url+'?t='+Date.now(); }
      aiD=''; aiU=d.url; aiM='image/jpeg';
      showToast(action==='remove-bg'?'Đã tách nền xong!':'Đã gắn logo TH!','ok');
    } else {
      showToast((action==='remove-bg'?'Tách nền':'Gắn logo')+' lỗi: '+(d.message||''),'err');
    }
  }catch(e){ btn.disabled=false; btn.innerHTML=origHtml; showToast('Lỗi: '+e.message,'err'); }
}

function resetAI(){
  aiD=''; aiU=''; savedImgFile='';
  lpHide(); lpTimers.forEach(clearTimeout); lpTimers=[];
  document.getElementById('ai-ph').style.display='block';
  // Restore split layout
  var aig=document.querySelector('.aig');
  var leftCol=document.querySelector('.aig>div:first-child');
  var result=document.querySelector('.aig-result');
  if(aig){aig.style.display='grid';}
  if(leftCol){leftCol.style.display='block';}
  if(result){result.style.display='none';}
  document.getElementById('ai-form').style.display='none';
  document.getElementById('up-p').style.display='none'; document.getElementById('up-p').src='';
  document.getElementById('dz-c').style.display='flex';
  document.getElementById('up-info').style.display='none';
  document.getElementById('fi').value='';
  document.getElementById('cp-p').style.display='none';
  document.getElementById('ul').value='';
  document.getElementById('ul-p').style.display='none';
  document.getElementById('ul-ph').style.display='block';
  document.getElementById('ai-img-file').value='';
  document.getElementById('spec-box').innerHTML='';
  aiExtraFiles=[];
  var ep=document.getElementById('ai-extra-preview');
  if(ep) ep.innerHTML='';
  var ei=document.getElementById('extra-imgs-input');
  if(ei) ei.value='';
}

// ── TỰ ĐỘNG NHẬN DẠNG THƯƠNG HIỆU KHI GÕ TÊN SẢN PHẨM ──────
function autoDetectBrand(name){
  if(!name) return;
  var bs=document.getElementById('ai-br');
  if(!bs) return;
  var nameLower=name.toLowerCase();
  var best=-1, bestScore=0;
  for(var i=1;i<bs.options.length;i++){  // skip option 0 = "-- Chọn --"
    var nm=bs.options[i].dataset.nm||'';
    if(!nm) continue;
    var score=0;
    // Kiểm tra tên sản phẩm bắt đầu bằng tên thương hiệu
    if(nameLower.startsWith(nm)) score=100;
    // Tên thương hiệu xuất hiện trong tên sản phẩm (cách nhau bởi space)
    else if(new RegExp('(^|\s)'+nm.replace(/[-\/\^$*+?.()|[\]{}]/g,'\$&')+'(\s|$|-|,|\.)','i').test(nameLower)) score=90;
    // Tên sản phẩm chứa tên thương hiệu
    else if(nameLower.includes(nm)) score=70;
    // Các từ trong tên thương hiệu xuất hiện trong tên sản phẩm
    else{
      nm.split(/\s+/).forEach(function(p){
        if(p.length>1 && nameLower.includes(p)) score+=30;
      });
    }
    if(score>bestScore){ bestScore=score; best=i; }
  }
  if(best>=0 && bestScore>=70){
    bs.selectedIndex=best;
    var hint=document.getElementById('brand-hint');
    if(hint) hint.style.display='inline';
    setTimeout(function(){ if(hint) hint.style.display='none'; },3000);
  }
}

// Lắng nghe khi admin gõ tên sản phẩm
(function(){
  var nameInp=document.getElementById('ai-n');
  if(!nameInp) return;
  var t;
  nameInp.addEventListener('input',function(){
    clearTimeout(t);
    t=setTimeout(function(){ autoDetectBrand(nameInp.value.trim()); },600);
  });
})();

// ── Google Image Search ───────────────────────────────────
function aigImgSearch(){
  var q=document.getElementById('ai-n').value.trim()||document.getElementById('ai-name-inp').value.trim();
  imsOpen(function(url, thumb, title, thumbEl, extra){
    // extra={b64,mime} for Pixabay (client-side fetch), url for Bing/Google
    var APP_URL=window.APP_URL||'';
    var payload=extra&&extra.b64 ? {image_b64:extra.b64, image_mime:extra.mime} : {image_url:url};
    fetch(APP_URL+'/api/ai/save-image',{
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    }).then(function(r){return r.json();}).then(function(d){
      if(thumbEl) thumbEl.classList.remove('ims-loading');
      if(d.success){
        savedImgFile=d.filename;
        document.getElementById('ai-img-file').value=d.filename;
        // Set preview
        var prev=document.getElementById('aif-img-src');
        var wrap=document.getElementById('aif-img');
        if(prev&&wrap){ prev.src=d.url||thumb; wrap.style.display='block'; }
        // Switch URL tab preview too (fallback)
        aiU=url; aiD=''; aiM='image/jpeg';
        var ulp=document.getElementById('ul-p');
        if(ulp){ ulp.src=d.url||url; ulp.style.display='block'; }
        imsClose();
        showToast('Đã chọn ảnh — nhấn Phân tích để tiếp tục','ok');
        // Switch to URL tab so it's visible
        aiT('l');
        var ul=document.getElementById('ul'); if(ul) ul.value=url;
        var ulph=document.getElementById('ul-ph'); if(ulph) ulph.style.display='none';
      } else {
        if(thumbEl) thumbEl.classList.remove('ims-loading','ims-selected');
        showToast('Không tải được ảnh: '+(d.message||'lỗi'),'err');
      }
    }).catch(function(e){
      if(thumbEl) thumbEl.classList.remove('ims-loading','ims-selected');
      showToast('Lỗi: '+e.message,'err');
    });
  }, q);
}
</script>


<?php require_once __DIR__.'/layout_bottom.php'; ?>
