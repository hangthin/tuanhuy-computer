import re

path = "C:/AppServ/www/tuanhuy_computer/app/Views/pages/showcase.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

results = []

def rep(name, old, new, n=1):
    global content
    ok = old in content
    results.append((name, ok))
    if ok:
        content = content.replace(old, new, n)
    else:
        print(f"NOT FOUND: {name}")
        # show context
        idx = 0
        for line in old.splitlines()[:2]:
            i = content.find(line)
            if i >= 0:
                print(f"  partial match '{line[:60]}' at {i}")

# ── 1. PHP: add $_narrations ─────────────────────────────────────
rep('$_narrations',
    '$_total = count($_allKeys);',
    """$_narrations = array(
    'monitor'  => array('counter'=>'01 \u2014 M\u00c0N H\u00ccNH',     'sub'=>'Kh\u00f4ng gian hi\u1ec3n th\u1ecb ho\u00e0n h\u1ea3o',       'desc'=>'M\u00e0u s\u1eafc ch\u00e2n th\u1ef1c, chuy\u1ec3n \u0111\u1ed9ng m\u01b0\u1ee3t m\u00e0 \u0111\u1ebfn t\u1eebng khung h\u00ecnh'),
    'case'     => array('counter'=>'02 \u2014 TH\u00d9NG M\u00c1Y',    'sub'=>'Ki\u1ebfn tr\u00fac l\u00e0m m\u00e1t th\u1ebf h\u1ec7 m\u1edbi',       'desc'=>'Lu\u1ed3ng kh\u00ed t\u1ed1i \u01b0u, RGB \u0111\u1ed3ng b\u1ed9, k\u00ednh c\u01b0\u1eddng l\u1ef1c to\u00e0n th\u00e2n'),
    'keyboard' => array('counter'=>'03 \u2014 B\u00c0N PH\u00cdM',     'sub'=>'Ph\u1ea3n h\u1ed3i c\u01a1 h\u1ecdc ch\u00ednh x\u00e1c',          'desc'=>'T\u1eebng c\u00fa g\u00f5 l\u00e0 m\u1ed9t tuy\u00ean ng\u00f4n \u2014 nhanh, ch\u1eafc, \u0111\u1eb3ng c\u1ea5p'),
    'mouse'    => array('counter'=>'04 \u2014 CHU\u1ed8T',        'sub'=>'Ki\u1ec3m so\u00e1t tuy\u1ec7t \u0111\u1ed1i',                'desc'=>'Theo d\u00f5i ch\u00ednh x\u00e1c \u0111\u1ebfn t\u1eebng pixel, c\u1ea7m n\u1eafm ergonomic ho\u00e0n h\u1ea3o'),
    'cpu'      => array('counter'=>'05 \u2014 B\u1ed8 X\u1eac L\u00dd',    'sub'=>'Tr\u00e1i tim c\u1ee7a h\u1ec7 th\u1ed1ng',              'desc'=>'X\u1eed l\u00fd \u0111a nhi\u1ec7m kh\u00f4ng gi\u1edbi h\u1ea1n, hi\u1ec7u n\u0103ng v\u01b0\u1ee3t m\u1ecdi th\u1eed th\u00e1ch'),
    'ram'      => array('counter'=>'06 \u2014 B\u1ed8 NH\u1ecc',      'sub'=>'T\u1ed1c \u0111\u1ed9 kh\u00f4ng t\u01b0\u1edfng',                 'desc'=>'D\u1eef li\u1ec7u lu\u00e2n chuy\u1ec3n li\u00ean t\u1ee5c, kh\u00f4ng lag, kh\u00f4ng ch\u1edd \u0111\u1ee3i'),
    'gpu'      => array('counter'=>'07 \u2014 CARD \u0110\u1ed2 H\u1ecaA', 'sub'=>'S\u1ee9c m\u1ea1nh h\u00ecnh \u1ea3nh \u0111\u1ec9nh cao',         'desc'=>'Ray tracing th\u1eddi gian th\u1ef1c, DLSS 3.0 \u2014 gaming nh\u01b0 \u0111i\u1ec7n \u1ea3nh'),
    'cooler'   => array('counter'=>'08 \u2014 T\u1ea2N NHI\u1ec6T',   'sub'=>'L\u00e0m m\u00e1t hi\u1ec7u qu\u1ea3 tuy\u1ec7t \u0111\u1ed1i',        'desc'=>'D\u00f2ng ch\u1ea3y nhi\u1ec7t \u1ed5n \u0111\u1ecbnh, h\u1ec7 th\u1ed1ng v\u1eadn h\u00e0nh b\u1ec1n b\u1ec9 24/7'),
    'ssd'      => array('counter'=>'09 \u2014 \u1ed4 C\u1ee8NG',      'sub'=>'T\u1ea3i game trong t\u00edch t\u1eafc',            'desc'=>'7000MB/s \u2014 m\u1ecdi th\u1ee9 s\u1eb5n s\u00e0ng tr\u01b0\u1edbc khi b\u1ea1n k\u1ecbp ch\u1edbp m\u1eaft'),
);
$_total = count($_allKeys);""",
)

# ── 2. PHP: update $scPhases to include narration ────────────────
rep('$scPhases narr',
    """    $scPhases[] = array(
        'counter' => sprintf('%02d/%02d', $_i+1, $_total),
        'label'   => $_def['label'],
        'icon'    => $_def['icon'],
        'type'    => $_def['type'],
        'model'   => $_name ?: $_def['type'],
        'specs'   => array_slice($_specsArr, 0, 3),
        'price'   => $_price,
        'hasData' => ($_name !== ''),
    );""",
    """    $_narr = $_narrations[$_pk] ?? array('counter'=>sprintf('%02d', $_i+1),'sub'=>$_def['type'],'desc'=>'');
    $scPhases[] = array(
        'counter'   => $_narr['counter'],
        'narr_sub'  => $_narr['sub'],
        'narr_desc' => $_narr['desc'],
        'label'     => $_def['label'],
        'icon'      => $_def['icon'],
        'type'      => $_def['type'],
        'model'     => $_name ?: $_def['type'],
        'specs'     => array_slice($_specsArr, 0, 3),
        'price'     => $_price,
        'hasData'   => ($_name !== ''),
    );""",
)

# ── 3. CSS: #sc-stage add flex centering ─────────────────────────
rep('#sc-stage flex',
    '  position:sticky;top:0;left:0;\n  width:100vw;height:100vh;overflow:hidden;background:#000;\n}',
    '  position:sticky;top:0;left:0;\n  width:100vw;height:100vh;overflow:hidden;background:#000;\n  display:flex;align-items:center;justify-content:center;\n}',
)

# ── 4. CSS: #sc-group → position:relative + #pc-group grid ───────
rep('#sc-group+#pc-group CSS',
    """/* \u2500\u2500 Assembled desk group \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
#sc-group{
  position:absolute;
  /* centred via GSAP xPercent/yPercent */
  display:grid;
  grid-template-columns:auto auto;
  grid-template-rows:auto auto;
  column-gap:clamp(12px,1.6vw,28px);
  row-gap:clamp(4px,.5vh,8px);
  z-index:10;opacity:0;
}
.acomp{overflow:visible}
.acomp img{
  width:100%;height:auto;
  object-fit:contain;display:block;pointer-events:none;
}
/* Desk grid placement */
#ac-monitor {
  grid-column:1;grid-row:1;
  width:44vw;max-width:600px;
  align-self:end;
}
#ac-case {
  grid-column:2;grid-row:1;
  width:20vw;max-width:280px;
  align-self:center;
  filter:drop-shadow(0 0 22px rgba(229,57,53,.42)) drop-shadow(0 0 8px rgba(229,57,53,.25));
}
#ac-keyboard {
  grid-column:1;grid-row:2;
  width:38vw;max-width:520px;
  align-self:start;
}
#ac-mouse {
  grid-column:2;grid-row:2;
  width:8vw;max-width:110px;
  align-self:start;
}
/* Internal (inside case) \u2013 visible only when spotlighted */
#ac-cpu,#ac-ram,#ac-gpu,#ac-cooler,#ac-ssd{
  position:absolute;
  width:17%;height:24%;right:6%;top:22%;opacity:0;
}""",
    """/* \u2500\u2500 Phase 1 subtitle \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
#sc-subtitle{
  position:absolute;top:calc(6vh + 30px);left:50%;
  transform:translateX(-50%);
  z-index:30;pointer-events:none;
  font-size:clamp(11px,1vw,13px);
  font-weight:400;letter-spacing:2px;
  color:#777;white-space:nowrap;opacity:0;
  text-align:center;
}

/* \u2500\u2500 Phase 4 reassemble text \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
#sc-reassemble{
  position:absolute;bottom:10vh;left:50%;
  transform:translateX(-50%);
  z-index:30;pointer-events:none;
  font-size:clamp(12px,1.2vw,15px);
  font-weight:700;letter-spacing:4px;
  color:#e53935;white-space:nowrap;opacity:0;
  text-align:center;text-transform:uppercase;
}

/* \u2500\u2500 Assembled desk group \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
#sc-group{
  position:relative;
  max-width:90vw;
  z-index:10;opacity:0;
}
#pc-group{
  display:grid;
  grid-template-columns:auto auto;
  grid-template-rows:auto auto;
  column-gap:clamp(12px,1.6vw,28px);
  row-gap:clamp(4px,.5vh,8px);
  width:fit-content;
  position:relative;
  margin:auto;
}
.acomp{overflow:visible}
.acomp img{
  width:100%;height:auto;
  object-fit:contain;display:block;pointer-events:none;
}
/* Desk grid placement */
#ac-monitor {
  grid-column:1;grid-row:1;
  width:min(44vw,480px);
  align-self:end;
}
#ac-case {
  grid-column:2;grid-row:1;
  width:min(18vw,200px);
  align-self:center;
  filter:drop-shadow(0 0 22px rgba(229,57,53,.42)) drop-shadow(0 0 8px rgba(229,57,53,.25));
}
#ac-keyboard {
  grid-column:1;grid-row:2;
  width:min(36vw,420px);
  align-self:start;
}
#ac-mouse {
  grid-column:2;grid-row:2;
  width:min(7vw,90px);
  align-self:start;
}
/* Internal (inside case) \u2013 visible only when spotlighted */
#ac-cpu,#ac-ram,#ac-gpu,#ac-cooler,#ac-ssd{
  position:absolute;
  width:17%;height:24%;right:6%;top:22%;opacity:0;
}""",
)

# ── 5. CSS: .p-name size + add .p-desc ───────────────────────────
rep('.p-name+.p-desc CSS',
    '.p-name{\n  font-size:clamp(22px,3vw,40px);\n  font-weight:900;color:#fff;line-height:1.2;margin-bottom:14px;\n}',
    '.p-name{\n  font-size:clamp(24px,3vw,42px);\n  font-weight:900;color:#fff;line-height:1.2;margin-bottom:10px;\n}\n.p-desc{\n  font-size:clamp(13px,1.2vw,16px);color:#aaa;line-height:1.75;margin-top:8px;\n}',
)

# ── 6. CSS: strip old sc-group from responsive breakpoints ───────
rep('responsive',
    """/* \u2500\u2500 Responsive \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
@media(max-width:1100px){
  #sc-group{width:88vw;height:62vh}
}
@media(max-width:768px){
  #sc-group{width:96vw;height:54vh}
  #sc-dots{display:none}""",
    """/* \u2500\u2500 Responsive \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
@media(max-width:768px){
  #sc-dots{display:none}""",
)

# ── 7. HTML: add #sc-subtitle + #sc-reassemble elements ──────────
rep('#sc-subtitle HTML',
    '  <!-- Title -->\n  <div id="sc-title">TU\u1ea4N HUY COMPUTER</div>',
    '  <!-- Title -->\n  <div id="sc-title">TU\u1ea4N HUY COMPUTER</div>\n  <div id="sc-subtitle">Tr\u1ea3i nghi\u1ec7m gaming \u0111\u1ec9nh cao \u2014 \u0111\u01b0\u1ee3c ki\u1ebfn t\u1ea1o t\u1eeb nh\u1eefng linh ki\u1ec7n t\u1ed1t nh\u1ea5t</div>\n  <div id="sc-reassemble">M\u1ed9t ki\u1ec7t t\u00e1c \u0111\u01b0\u1ee3c l\u1eafp r\u00e1p \u2014 s\u1eb5n s\u00e0ng chinh ph\u1ee5c m\u1ecdi th\u1eed th\u00e1ch</div>',
)

# ── 8. HTML: add #pc-group wrapper inside #sc-group ───────────────
rep('#pc-group HTML',
    """  <!-- Assembled desk group -->
  <div id="sc-group">
    <?php foreach ($_allKeys as $_k): ?>
    <div class="acomp" id="ac-<?= $_k ?>"<?= $scVisible[$_k] ? '' : ' style="display:none"' ?>>
      <?php if ($scVisible[$_k]): ?>
      <img src="<?= htmlspecialchars($scUrl.$_k.'.png?t='.filemtime($scDir.$_k.'.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>""",
    """  <!-- Assembled desk group -->
  <div id="sc-group">
    <div id="pc-group">
      <?php foreach (array('monitor','case','keyboard','mouse') as $_k): ?>
      <div class="acomp" id="ac-<?= $_k ?>"<?= $scVisible[$_k] ? '' : ' style="display:none"' ?>>
        <?php if ($scVisible[$_k]): ?>
        <img src="<?= htmlspecialchars($scUrl.$_k.'.png?t='.filemtime($scDir.$_k.'.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php foreach (array('cpu','ram','gpu','cooler','ssd') as $_k): ?>
    <div class="acomp" id="ac-<?= $_k ?>"<?= $scVisible[$_k] ? '' : ' style="display:none"' ?>>
      <?php if ($scVisible[$_k]): ?>
      <img src="<?= htmlspecialchars($scUrl.$_k.'.png?t='.filemtime($scDir.$_k.'.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>""",
)

# ── 9. HTML: update panel .p-left content ────────────────────────
rep('.p-left panel HTML',
    """      <!-- Left text -->
      <div class="p-left" id="ptxt-<?= $_k ?>">
        <div class="p-counter"><?= htmlspecialchars($_ph['counter'].' \u2014 '.$_ph['label'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="p-name"><?= htmlspecialchars($_ph['model'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="p-sep"></div>
        <div class="p-specs">
          <?php if ($_ph['hasData']): ?>
            <?php foreach ($_ph['specs'] as $_sp): ?>
            <div><?= htmlspecialchars($_sp['k'], ENT_QUOTES, 'UTF-8') ?>: <b><?= htmlspecialchars($_sp['v'], ENT_QUOTES, 'UTF-8') ?></b></div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="p-fallback">Ch\u01b0a c\u00f3 th\u00f4ng tin s\u1ea3n ph\u1ea9m.<br>V\u00e0o <b>Admin \u203a Showcase Assets</b> \u0111\u1ec3 qu\u00e9t AI.</div>
          <?php endif; ?>
        </div>
        <?php if (!empty($_ph['price'])): ?>
        <div class="p-price"><?= htmlspecialchars($_ph['price'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
      </div>""",
    """      <!-- Left text -->
      <div class="p-left" id="ptxt-<?= $_k ?>">
        <div class="p-counter"><?= htmlspecialchars($_ph['counter'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="p-name"><?= htmlspecialchars($_ph['narr_sub'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="p-sep"></div>
        <div class="p-desc"><?= htmlspecialchars($_ph['narr_desc'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php if ($_ph['hasData']): ?>
        <div class="p-specs" style="margin-top:14px">
          <?php foreach ($_ph['specs'] as $_sp): ?>
          <div><?= htmlspecialchars($_sp['k'], ENT_QUOTES, 'UTF-8') ?>: <b><?= htmlspecialchars($_sp['v'], ENT_QUOTES, 'UTF-8') ?></b></div>
          <?php endforeach; ?>
        </div>
        <?php if (!empty($_ph['price'])): ?>
        <div class="p-price"><?= htmlspecialchars($_ph['price'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php endif; ?>
      </div>""",
)

# ── 10. JS: remove GSAP centering set call ───────────────────────
rep('gsap.set remove',
    "// Centre the group using GSAP (avoids CSS transform conflict)\ngsap.set(scGroup, {xPercent:-50, yPercent:-50, left:'50%', top:'50%'});",
    "// Group centred by #sc-stage flexbox",
)

# ── 11. JS: add scSubtitle + scReassemble DOM refs ────────────────
rep('scSubtitle DOM',
    "const scGlow  = document.getElementById('sc-glow');",
    "const scGlow       = document.getElementById('sc-glow');\nconst scSubtitle   = document.getElementById('sc-subtitle');\nconst scReassemble = document.getElementById('sc-reassemble');",
)

# ── 12. JS: Phase 1 subtitle in ───────────────────────────────────
rep('Phase1 subtitle in',
    "tl.to(scTitle, {opacity:1, duration:.6, ease:'power2.out'}, 0.15);",
    "tl.to(scTitle,    {opacity:1, duration:.6, ease:'power2.out'}, 0.15);\ntl.to(scSubtitle, {opacity:1, duration:.5, ease:'power2.out'}, 0.3);",
)

# ── 13. JS: Phase 2 subtitle out ─────────────────────────────────
rep('Phase2 subtitle out',
    "tl.to(scTitle, {opacity:0, scale:.9, duration:.4, ease:'power2.in'}, P2_END-.5);",
    "tl.to(scTitle,    {opacity:0, scale:.9, duration:.4, ease:'power2.in'}, P2_END-.5);\ntl.to(scSubtitle, {opacity:0,           duration:.3, ease:'power2.in'}, P2_END-.5);",
)

# ── 14. JS: Phase 4 reassemble text in ───────────────────────────
rep('Phase4 reassemble',
    "// Group fades back in after components settle\ntl.to(scGroup, {opacity:1, duration:.55, ease:'power2.out'}, P3_END+.5);",
    "// Group fades back in after components settle\ntl.to(scGroup,      {opacity:1, duration:.55, ease:'power2.out'}, P3_END+.5);\ntl.to(scReassemble, {opacity:1, duration:.6,  ease:'power2.out'}, P3_END+.7);",
)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print('\nResults:')
for name, ok in results:
    print(f'  {"OK" if ok else "FAIL"}: {name}')
print('\nDONE')
