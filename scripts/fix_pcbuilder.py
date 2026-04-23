import sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

def rep(path, old, new, name):
    with open(path, 'r', encoding='utf-8') as f: c = f.read()
    if old in c:
        c = c.replace(old, new, 1)
        with open(path, 'w', encoding='utf-8') as f: f.write(c)
        print(f'OK: {name}')
    else:
        print(f'FAIL: {name}')

ctrl = 'C:/AppServ/www/tuanhuy_computer/app/Controllers/ProductController.php'
view = 'C:/AppServ/www/tuanhuy_computer/app/Views/products/pc_builder.php'

# ── 1. ProductController: replace entire catMap + slots + loop ─────────────────
rep(ctrl,
    """        // Mapped from DB: SELECT id, name, slug FROM categories;
        // case/cooler/psu share 'phu-kien' (no dedicated DB category yet)
        $catMap = array(
            'case'      => 'phu-kien',
            'cpu'       => 'cpu',
            'mainboard' => 'mainboard',
            'ram'       => 'ram',
            'gpu'       => 'card-do-hoa',
            'ssd'       => 'ssd-o-cung',
            'cooler'    => 'phu-kien',
            'psu'       => 'phu-kien',
        );
        $slots = array(
            array('key'=>'case',      'label'=>'V\u1ecf Case',     'icon'=>'fa-box-open'),
            array('key'=>'cpu',       'label'=>'CPU',          'icon'=>'fa-microchip'),
            array('key'=>'mainboard', 'label'=>'Mainboard',   'icon'=>'fa-server'),
            array('key'=>'ram',       'label'=>'RAM',          'icon'=>'fa-memory'),
            array('key'=>'gpu',       'label'=>'Card \u0111\u1ed3 h\u1ecda', 'icon'=>'fa-display'),
            array('key'=>'ssd',       'label'=>'\u1ed4 c\u1ee9ng SSD',  'icon'=>'fa-hard-drive'),
            array('key'=>'cooler',    'label'=>'T\u1ea3n nhi\u1ec7t',   'icon'=>'fa-fan'),
            array('key'=>'psu',       'label'=>'Ngu\u1ed3n (PSU)', 'icon'=>'fa-plug'),
        );
        $slotProducts = array();
        foreach ($slots as $s) {
            $raw = $this->model->getByCategory($catMap[$s['key']], '', 'newest', '', '', 40);
            $slotProducts[$s['key']] = array();
            foreach ($raw as $p) {
                $specs = array();
                if (!empty($p['specs'])) { $dec = json_decode($p['specs'], true); if (is_array($dec)) $specs = $dec; }
                $slotProducts[$s['key']][] = array(
                    'id'        => (int)$p['id'],
                    'name'      => $p['name'],
                    'slug'      => $p['slug'],
                    'image'     => !empty($p['image']) ? UPLOAD_URL.'/'.$p['image'] : '',
                    'price'     => (float)($p['final_price'] ?? $p['price']),
                    'short_desc'=> $p['short_desc'] ?? '',
                    'specs'     => $specs,
                    'brand'     => $p['brand_name'] ?? '',
                    'sold'      => (int)($p['sold'] ?? 0),
                );
            }
        }
        $categories = $this->catModel->getAll();
        $pageTitle = 'Build PC Gaming';
        include __DIR__.'/../Views/products/pc_builder.php';""",
    """        // Slugs from: SELECT id, name, slug FROM categories;
        // case/cooler/psu have no dedicated category \u2014 use 'phu-kien' + keyword fallback
        $catMap = array(
            'monitor'   => 'man-hinh',
            'case'      => 'phu-kien',
            'cpu'       => 'cpu',
            'mainboard' => 'mainboard',
            'ram'       => 'ram',
            'gpu'       => 'card-do-hoa',
            'ssd'       => 'ssd-o-cung',
            'cooler'    => 'phu-kien',
            'psu'       => 'phu-kien',
        );
        // Keyword filters to narrow phu-kien results per slot
        $kwMap = array('case'=>'case', 'cooler'=>'t\u1ea3n nhi\u1ec7t', 'psu'=>'ngu\u1ed3n');

        $slots = array(
            array('key'=>'monitor',  'label'=>'M\u00e0n h\u00ecnh',   'icon'=>'fa-desktop'),
            array('key'=>'case',     'label'=>'V\u1ecf Case',    'icon'=>'fa-box-open'),
            array('key'=>'cpu',      'label'=>'CPU',         'icon'=>'fa-microchip'),
            array('key'=>'mainboard','label'=>'Mainboard',  'icon'=>'fa-server'),
            array('key'=>'ram',      'label'=>'RAM',         'icon'=>'fa-memory'),
            array('key'=>'gpu',      'label'=>'Card \u0111\u1ed3 h\u1ecda','icon'=>'fa-display'),
            array('key'=>'ssd',      'label'=>'\u1ed4 c\u1ee9ng SSD', 'icon'=>'fa-hard-drive'),
            array('key'=>'cooler',   'label'=>'T\u1ea3n nhi\u1ec7t',  'icon'=>'fa-fan'),
            array('key'=>'psu',      'label'=>'Ngu\u1ed3n (PSU)','icon'=>'fa-plug'),
        );
        $noCatSlots = array('case', 'cooler', 'psu');
        $slotMeta    = array();
        $slotProducts = array();
        foreach ($slots as $s) {
            $slug = $catMap[$s['key']];
            $kw   = $kwMap[$s['key']] ?? '';
            $slotMeta[$s['key']] = array('has_cat' => !in_array($s['key'], $noCatSlots));
            $raw = $this->model->getByCategory($slug, $kw, 'newest', '', '', 40);
            if (!$raw && $kw) {   // keyword found nothing \u2192 fall back to full category
                $raw = $this->model->getByCategory($slug, '', 'newest', '', '', 40);
            }
            $slotProducts[$s['key']] = array();
            foreach ($raw as $p) {
                $specs = array();
                if (!empty($p['specs'])) { $dec = json_decode($p['specs'], true); if (is_array($dec)) $specs = $dec; }
                $slotProducts[$s['key']][] = array(
                    'id'        => (int)$p['id'],
                    'name'      => $p['name'],
                    'slug'      => $p['slug'],
                    'image'     => !empty($p['image']) ? UPLOAD_URL.'/'.$p['image'] : '',
                    'price'     => (float)($p['final_price'] ?? $p['price']),
                    'short_desc'=> $p['short_desc'] ?? '',
                    'specs'     => $specs,
                    'brand'     => $p['brand_name'] ?? '',
                    'sold'      => (int)($p['sold'] ?? 0),
                );
            }
        }
        $categories = $this->catModel->getAll();
        $pageTitle = 'Build PC Gaming';
        include __DIR__.'/../Views/products/pc_builder.php';""",
    'Controller catMap+slots+loop',
)

# ── 2. View: add SLOT_META constant after PRODUCTS ─────────────────────────────
rep(view,
    'const PRODUCTS   = <?= json_encode($slotProducts, JSON_UNESCAPED_UNICODE) ?>;',
    'const PRODUCTS   = <?= json_encode($slotProducts, JSON_UNESCAPED_UNICODE) ?>;\nconst SLOT_META  = <?= json_encode($slotMeta,    JSON_UNESCAPED_UNICODE) ?>;',
    'View SLOT_META constant',
)

# ── 3. View: update SLOTS_DEF (add monitor, keep rest) ─────────────────────────
rep(view,
    """const SLOTS_DEF  = [
  {key:'case',      label:'V\u1ecf Case',     icon:'fa-box-open'},
  {key:'cpu',       label:'CPU',          icon:'fa-microchip'},
  {key:'mainboard', label:'Mainboard',   icon:'fa-server'},
  {key:'ram',       label:'RAM',          icon:'fa-memory'},
  {key:'gpu',       label:'Card \u0111\u1ed3 h\u1ecda', icon:'fa-display'},
  {key:'ssd',       label:'\u1ed4 c\u1ee9ng SSD',  icon:'fa-hard-drive'},
  {key:'cooler',    label:'T\u1ea3n nhi\u1ec7t',   icon:'fa-fan'},
  {key:'psu',       label:'Ngu\u1ed3n (PSU)', icon:'fa-plug'},
];""",
    """const SLOTS_DEF  = [
  {key:'monitor',  label:'M\u00e0n h\u00ecnh',   icon:'fa-desktop'},
  {key:'case',     label:'V\u1ecf Case',    icon:'fa-box-open'},
  {key:'cpu',      label:'CPU',         icon:'fa-microchip'},
  {key:'mainboard',label:'Mainboard',  icon:'fa-server'},
  {key:'ram',      label:'RAM',         icon:'fa-memory'},
  {key:'gpu',      label:'Card \u0111\u1ed3 h\u1ecda',icon:'fa-display'},
  {key:'ssd',      label:'\u1ed4 c\u1ee9ng SSD', icon:'fa-hard-drive'},
  {key:'cooler',   label:'T\u1ea3n nhi\u1ec7t',  icon:'fa-fan'},
  {key:'psu',      label:'Ngu\u1ed3n (PSU)',icon:'fa-plug'},
];""",
    'View SLOTS_DEF',
)

# ── 4. View: update state.build (add monitor) ──────────────────────────────────
rep(view,
    "  build: {case:null,cpu:null,mainboard:null,ram:null,gpu:null,ssd:null,cooler:null,psu:null},",
    "  build: {monitor:null,case:null,cpu:null,mainboard:null,ram:null,gpu:null,ssd:null,cooler:null,psu:null},",
    'View state.build',
)

# ── 5. View: make progress bar count dynamic (was hardcoded /8) ─────────────────
rep(view,
    "  document.getElementById('prog-fill').style.width = (count/8*100)+'%';\n"
    "  document.getElementById('prog-txt').textContent  = count+'/8';\n"
    "  document.getElementById('build-label').textContent =\n"
    "    count === 8 ? '\u2713 C\u1ea5u h\u00ecnh ho\u00e0n t\u1ea5t \u2014 s\u1eb5n s\u00e0ng x\u00e2y d\u1ef1ng!' : `Ch\u1ecdn ${8-count} linh ki\u1ec7n n\u1eefa`;",
    "  const total = SLOTS_DEF.length;\n"
    "  document.getElementById('prog-fill').style.width = (count/total*100)+'%';\n"
    "  document.getElementById('prog-txt').textContent  = count+'/'+total;\n"
    "  document.getElementById('build-label').textContent =\n"
    "    count === total ? '\u2713 C\u1ea5u h\u00ecnh ho\u00e0n t\u1ea5t \u2014 s\u1eb5n s\u00e0ng x\u00e2y d\u1ef1ng!' : `Ch\u1ecdn ${total-count} linh ki\u1ec7n n\u1eefa`;",
    'View progress dynamic',
)

# ── 6. View: renderProducts empty state \u2014 show "Danh m\u1ee5c \u0111ang c\u1eadp nh\u1eadt" when no dedicated cat ──
rep(view,
    "  if (!list.length) {\n"
    "    grid.innerHTML='<div class=\"pcb-empty\"><i class=\"fa-solid fa-box-open\"></i><p>Kh\u00f4ng c\u00f3 s\u1ea3n ph\u1ea9m</p><p style=\"font-size:.74rem;color:#2a2a2a\">Th\u1eed t\u00ecm ki\u1ebfm ho\u1eb7c ki\u1ec3m tra l\u1ea1i danh m\u1ee5c</p></div>';\n"
    "    return;\n"
    "  }",
    "  if (!list.length) {\n"
    "    const hasCat = !SLOT_META[state.tab] || SLOT_META[state.tab].has_cat !== false;\n"
    "    const emsg = hasCat\n"
    "      ? '<i class=\"fa-solid fa-magnifying-glass\"></i><p>Kh\u00f4ng t\u00ecm th\u1ea5y s\u1ea3n ph\u1ea9m</p><p style=\"font-size:.74rem;color:#2a2a2a\">Th\u1eed thay \u0111\u1ed5i t\u1eeb kh\u00f3a ho\u1eb7c b\u1ed9 l\u1ecdc</p>'\n"
    "      : '<i class=\"fa-solid fa-layer-group\"></i><p style=\"color:#e30000\">Danh m\u1ee5c \u0111ang c\u1eadp nh\u1eadt</p><p style=\"font-size:.74rem;color:#555\">Danh m\u1ee5c n\u00e0y ch\u01b0a c\u00f3 s\u1ea3n ph\u1ea9m ri\u00eang.<br>Vui l\u00f2ng linh ki\u1ec7n t\u01b0\u01a1ng \u1ee9ng qua trang S\u1ea3n ph\u1ea9m.</p>';\n"
    "    grid.innerHTML='<div class=\"pcb-empty\">'+emsg+'</div>';\n"
    "    return;\n"
    "  }",
    'View renderProducts empty state',
)
