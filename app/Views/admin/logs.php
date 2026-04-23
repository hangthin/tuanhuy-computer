<?php
// ── CSV Export — must run before layout_top outputs HTML ────────
if (!empty($_GET['export']) && $_GET['export'] === 'csv') {
    $db      = Database::getInstance();
    $csvLogs = $db->fetchAll("SELECT * FROM action_logs ORDER BY created_at DESC LIMIT 5000");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="logs_'.date('Ymd_His').'.csv"');
    $out = fopen('php://output','w');
    fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
    fputcsv($out, ['ID','Thời gian','Người dùng','Vai trò','Hành động','Bảng','Target ID','IP']);
    $rMap = [1=>'Admin',2=>'Quản lý',3=>'Nhân viên',0=>'Khách'];
    foreach ($csvLogs as $r) {
        fputcsv($out, [$r['id'],$r['created_at'],$r['user_name'],
            $rMap[(int)$r['user_role']]??$r['user_role'],
            $r['action'],$r['table_name'],$r['target_id'],$r['ip_address']]);
    }
    fclose($out); exit;
}

require_once __DIR__.'/layout_top.php';

// ── Labels & helpers ─────────────────────────────────────────────
$fieldLabels = [
    'name'=>'Tên','price'=>'Giá','sale_price'=>'Giá KM','stock'=>'Tồn kho',
    'stock_quantity'=>'Tồn kho','min_stock'=>'Tồn tối thiểu','is_active'=>'Trạng thái',
    'is_deleted'=>'Đã xóa','is_featured'=>'Nổi bật','is_new'=>'Hàng mới',
    'status'=>'Trạng thái','payment_status'=>'Thanh toán','role'=>'Vai trò',
    'fullname'=>'Họ tên','email'=>'Email','phone'=>'SĐT','category_id'=>'Danh mục',
    'brand_id'=>'Thương hiệu','warranty'=>'Bảo hành (tháng)','short_desc'=>'Mô tả ngắn',
    'sku'=>'SKU','note'=>'Ghi chú','sort_order'=>'Thứ tự','address'=>'Địa chỉ','city'=>'Thành phố',
];
$roleLabels   = [1=>'Admin',2=>'Quản lý',3=>'Nhân viên',0=>'Khách'];
$statusLabels = [
    'pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý',
    'shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã hủy',
    'paid'=>'Đã thanh toán','failed'=>'Thất bại','refunded'=>'Hoàn tiền',
];

function fmtVal($key, $val) {
    if ($val === null || $val === '') return '<span style="color:#333">—</span>';
    $priceFields = ['price','sale_price','total','subtotal','revenue','shipping_fee','discount'];
    if (in_array($key, $priceFields) && is_numeric($val))
        return '<span style="color:#fbbf24">'.number_format((float)$val,0,',','.').'đ</span>';
    if ($key === 'is_active')
        return $val ? '<span style="color:#4ade80">● Hiện</span>' : '<span style="color:#f87171">● Ẩn</span>';
    if ($key === 'is_deleted')
        return $val ? '<span style="color:#f87171">Đã xóa</span>' : '<span style="color:#4ade80">OK</span>';
    if ($key === 'is_featured')
        return $val ? '<span style="color:#fbbf24">★ Nổi bật</span>' : 'Thường';
    if ($key === 'is_new')
        return $val ? '<span style="color:#60a5fa">Mới</span>' : 'Thường';
    if ($key === 'role') {
        $map = [1=>'<span style="color:#e30000">Admin</span>',2=>'<span style="color:#f59e0b">Quản lý</span>',
                3=>'<span style="color:#3b82f6">Nhân viên</span>',0=>'Khách'];
        return $map[(int)$val] ?? htmlspecialchars((string)$val);
    }
    if (in_array($key, ['status','payment_status'])) {
        global $statusLabels;
        return htmlspecialchars($statusLabels[$val] ?? $val);
    }
    if ($key === 'warranty') return $val.' tháng';
    if (in_array($key, ['stock','stock_quantity','min_stock'])) return number_format((int)$val).' cái';
    return htmlspecialchars(mb_substr((string)$val, 0, 60, 'UTF-8'));
}

function makeDescription($log, $oldJ, $newJ) {
    global $roleLabels, $statusLabels, $fieldLabels;
    $rawAction = $log['action'];
    $action    = strtoupper($rawAction);
    $table     = $log['table_name'];
    $id        = $log['target_id'];
    if ($action === 'TG_OFFSET') return null;

    static $namedActs = ['Cập nhật ảnh chính','Tách nền ảnh','Gắn logo',
                         'Thêm ảnh phụ','Xóa ảnh','Cập nhật giá','Cập nhật thông tin'];
    if (in_array($rawAction, $namedActs)) {
        $pn = '';
        if (is_array($newJ) && !empty($newJ['name']))
            $pn = ' <b>'.htmlspecialchars(mb_substr($newJ['name'],0,50,'UTF-8')).'</b>';
        elseif (is_array($oldJ) && !empty($oldJ['name']))
            $pn = ' <b>'.htmlspecialchars(mb_substr($oldJ['name'],0,50,'UTF-8')).'</b>';
        elseif ($id) $pn = " #{$id}";
        if ($rawAction === 'Cập nhật giá') {
            $pCu  = (is_array($oldJ) && isset($oldJ['price']) && $oldJ['price'] !== null)
                    ? number_format((float)$oldJ['price'],0,',','.').'đ' : null;
            $pMoi = (is_array($newJ) && isset($newJ['price']) && $newJ['price'] !== null)
                    ? number_format((float)$newJ['price'],0,',','.').'đ' : null;
            if ($pCu && $pMoi && $pCu !== $pMoi)
                return "Cập nhật giá{$pn}: <span style='color:#f87171'>{$pCu}</span> → <b style='color:#4ade80'>{$pMoi}</b>";
        }
        return htmlspecialchars($rawAction).$pn;
    }

    $extractName = function($j, $keys=['name','fullname','product_name','email']) {
        if (!is_array($j)) return '';
        foreach ($keys as $k) { if (!empty($j[$k])) return $j[$k]; }
        return '';
    };
    $name    = $extractName($newJ) ?: $extractName($oldJ);
    $nameStr = $name ? ' <b>'.htmlspecialchars(mb_substr($name,0,50,'UTF-8')).'</b>' : ($id ? " #{$id}" : '');

    if ($table === 'products') {
        if ($action === 'CREATE') {
            $price = isset($newJ['price']) ? ' — '.number_format((float)$newJ['price'],0,',','.').'đ' : '';
            return "Thêm sản phẩm{$nameStr}{$price}";
        }
        if ($action === 'UPDATE') {
            $skipCtx = ['name','product_name','order_code','fullname','note','slug','id'];
            $changed = [];
            if (is_array($newJ) && is_array($oldJ)) {
                foreach ($newJ as $k => $v) {
                    if (in_array($k,$skipCtx) || !array_key_exists($k,$oldJ)) continue;
                    if ((string)$oldJ[$k] !== (string)$v) $changed[] = $k;
                }
            }
            $nameChanged = is_array($newJ) && is_array($oldJ)
                && isset($newJ['name'],$oldJ['name']) && $newJ['name'] !== $oldJ['name'];
            if (in_array('is_deleted',$changed) && isset($newJ['is_deleted']) && (string)$newJ['is_deleted']==='0')
                return "♻️ Khôi phục sản phẩm{$nameStr}";
            if (in_array('is_active',$changed))
                return ($newJ['is_active']?'👁 Hiển thị':'🙈 Ẩn')." sản phẩm{$nameStr}";
            if (in_array('is_featured',$changed))
                return ($newJ['is_featured']?'⭐ Đặt nổi bật':'☆ Bỏ nổi bật')." sản phẩm{$nameStr}";
            if (in_array('is_new',$changed))
                return ($newJ['is_new']?'🆕 Hàng mới':'↩ Bỏ hàng mới')."{$nameStr}";
            if ($nameChanged && empty($changed))
                return "Đổi tên: <span style='color:#f87171'>".htmlspecialchars(mb_substr($oldJ['name'],0,35,'UTF-8'))."</span> → <b>".htmlspecialchars(mb_substr($newJ['name'],0,35,'UTF-8'))."</b>";
            if (in_array('price',$changed)) {
                $oldP = isset($oldJ['price']) ? number_format((float)$oldJ['price'],0,',','.').'đ' : '?';
                $newP = number_format((float)$newJ['price'],0,',','.').'đ';
                return "Cập nhật giá{$nameStr}: <span style='color:#f87171'>{$oldP}</span> → <b style='color:#4ade80'>{$newP}</b>";
            }
            if (in_array('stock',$changed)) {
                $d2 = is_numeric($newJ['stock']) && is_numeric($oldJ['stock']) ? (int)$newJ['stock']-(int)$oldJ['stock'] : null;
                $ds = $d2!==null?(' ('.($d2>0?"<span style='color:#4ade80'>+{$d2}</span>":"<span style='color:#f87171'>{$d2}</span>").')'):'';
                return "Tồn kho{$nameStr}: <span style='color:#f87171'>{$oldJ['stock']}</span> → <b>{$newJ['stock']}</b>{$ds}";
            }
            if (in_array('sale_price',$changed)) {
                $oSP = !empty($oldJ['sale_price'])?number_format((float)$oldJ['sale_price'],0,',','.').'đ':'—';
                $nSP = !empty($newJ['sale_price'])?number_format((float)$newJ['sale_price'],0,',','.').'đ':'—';
                return "Giá KM{$nameStr}: <span style='color:#f87171'>{$oSP}</span> → <b style='color:#4ade80'>{$nSP}</b>";
            }
            $silentKeys = ['description','short_desc','specs','views','rating','review_count',
                           'sold','image','updated_at','created_at','session_id','remember_token','deleted_at','deleted_by'];
            $visChanged = array_values(array_filter($changed, function($k) use ($silentKeys){ return !in_array($k,$silentKeys); }));
            if (!empty($visChanged)) {
                $labels = array_map(function($k) use ($fieldLabels){ return $fieldLabels[$k]??$k; }, array_slice($visChanged,0,3));
                return "Cập nhật sản phẩm{$nameStr} (".implode(', ',$labels).")";
            }
            return "Cập nhật thông tin sản phẩm{$nameStr}";
        }
        if ($action === 'DELETE') return "🗑 Xóa sản phẩm{$nameStr}";
    }

    if ($table === 'orders') {
        $oCode = (is_array($newJ)&&!empty($newJ['order_code']))?$newJ['order_code']:((is_array($oldJ)&&!empty($oldJ['order_code']))?$oldJ['order_code']:null);
        $cName = (is_array($newJ)&&!empty($newJ['fullname']))?$newJ['fullname']:((is_array($oldJ)&&!empty($oldJ['fullname']))?$oldJ['fullname']:null);
        $oRef  = $oCode?" <b>#{$oCode}</b>":" #{$id}";
        $cStr  = $cName?' — '.htmlspecialchars(mb_substr($cName,0,25,'UTF-8')):'';
        if ($action==='UPDATE' && is_array($newJ) && isset($newJ['status'])) {
            $oS = is_array($oldJ)&&isset($oldJ['status'])?($statusLabels[$oldJ['status']]??$oldJ['status']):null;
            $nS = $statusLabels[$newJ['status']]??$newJ['status'];
            return "Đơn{$oRef}{$cStr}: ".($oS?"<span style='color:#6b7280'>{$oS}</span> → ":'')."<b>{$nS}</b>";
        }
        if ($action==='CREATE') return "Tạo đơn{$oRef}{$cStr}";
        if ($action==='DELETE') return "🗑 Xóa đơn{$oRef}";
        return "Cập nhật đơn{$oRef}";
    }

    if ($table === 'users') {
        if ($action==='LOGIN')  return "🔑 Đăng nhập".($name?' — <b>'.htmlspecialchars($name).'</b>':'');
        if ($action==='LOGOUT') return "🚪 Đăng xuất".($name?' — <b>'.htmlspecialchars($name).'</b>':'');
        if ($action==='CREATE') return "➕ Tạo tài khoản{$nameStr}";
        if ($action==='UPDATE') {
            if (is_array($newJ)&&isset($newJ['is_active']))
                return ($newJ['is_active']?'🔓 Mở khóa':'🔒 Khóa')." tài khoản{$nameStr}";
            if (is_array($newJ)&&isset($newJ['role']))
                return "Đổi vai trò{$nameStr} → <b>".($roleLabels[(int)$newJ['role']]??$newJ['role'])."</b>";
            if (is_array($newJ)&&isset($newJ['note'])&&strpos((string)$newJ['note'],'password_reset')!==false)
                return "🔑 Đặt lại mật khẩu{$nameStr}";
            return "Cập nhật tài khoản{$nameStr}";
        }
        if ($action==='DELETE') return "🗑 Xóa tài khoản{$nameStr}";
    }

    if ($table === 'inventory') {
        $pN  = (is_array($newJ)&&!empty($newJ['product_name']))?$newJ['product_name']:((is_array($oldJ)&&!empty($oldJ['product_name']))?$oldJ['product_name']:null);
        $pS  = $pN?' <b>'.htmlspecialchars(mb_substr($pN,0,45,'UTF-8')).'</b>':" #{$id}";
        $oQ  = is_array($oldJ)&&isset($oldJ['stock_quantity'])?(int)$oldJ['stock_quantity']:null;
        $nQ  = is_array($newJ)&&isset($newJ['stock_quantity'])?(int)$newJ['stock_quantity']:null;
        if ($oQ!==null&&$nQ!==null) {
            $d2 = $nQ-$oQ;
            $ds = $d2>0?"<span style='color:#4ade80'>+{$d2}</span>":"<span style='color:#f87171'>{$d2}</span>";
            return "📦 Kho{$pS}: {$oQ} → <b>{$nQ}</b> ({$ds})";
        }
        return "📦 Cập nhật kho{$pS}";
    }

    if ($table === 'categories') {
        if ($action==='CREATE') return "Thêm danh mục{$nameStr}";
        if ($action==='DELETE') return "🗑 Xóa danh mục{$nameStr}";
        if ($action==='UPDATE') {
            if (is_array($newJ)&&isset($newJ['is_active']))
                return ($newJ['is_active']?'👁 Hiện':'🙈 Ẩn')." danh mục{$nameStr}";
            if (is_array($newJ)&&is_array($oldJ)&&isset($newJ['name'],$oldJ['name'])&&$newJ['name']!==$oldJ['name'])
                return "Đổi tên DM: <span style='color:#f87171'>".htmlspecialchars(mb_substr($oldJ['name'],0,30,'UTF-8'))."</span> → <b>".htmlspecialchars(mb_substr($newJ['name'],0,30,'UTF-8'))."</b>";
            return "Cập nhật danh mục{$nameStr}";
        }
    }

    $aVi = ['CREATE'=>'Thêm','UPDATE'=>'Cập nhật','DELETE'=>'Xóa','LOGIN'=>'Đăng nhập','LOGOUT'=>'Đăng xuất'];
    return ($aVi[$action]??$action)." {$table}{$nameStr}";
}

// ── Stats for header cards ────────────────────────────────────────
$dbS       = Database::getInstance();
$todayCnt  = (int)$dbS->query("SELECT COUNT(*) FROM action_logs WHERE DATE(created_at)=CURDATE()",[])
                       ->fetchColumn();
$deleteCnt = (int)$dbS->query("SELECT COUNT(*) FROM action_logs WHERE action='DELETE' AND DATE(created_at)=CURDATE()",[])
                       ->fetchColumn();
$topUser   = $dbS->fetch("SELECT user_name,COUNT(*) c FROM action_logs WHERE DATE(created_at)=CURDATE() GROUP BY user_name ORDER BY c DESC LIMIT 1");

// ── Config ───────────────────────────────────────────────────────
$namedActivities = ['Cập nhật ảnh chính','Tách nền ảnh','Gắn logo',
                    'Thêm ảnh phụ','Xóa ảnh','Cập nhật giá','Cập nhật thông tin'];

$badgeCfg = [
    'CREATE' => ['bg'=>'#052e16','fg'=>'#4ade80','icon'=>'fa-plus'],
    'UPDATE' => ['bg'=>'#0c1a3b','fg'=>'#60a5fa','icon'=>'fa-pen'],
    'DELETE' => ['bg'=>'#2d0a0a','fg'=>'#f87171','icon'=>'fa-trash'],
    'LOGIN'  => ['bg'=>'#1e0a3b','fg'=>'#c084fc','icon'=>'fa-right-to-bracket'],
    'LOGOUT' => ['bg'=>'#111',   'fg'=>'#6b7280','icon'=>'fa-right-from-bracket'],
    'NAMED'  => ['bg'=>'#0d1f14','fg'=>'#86efac','icon'=>'fa-image'],
];
$tableIcons = [
    'products'   => ['fa-box',      '#f59e0b'],
    'orders'     => ['fa-receipt',  '#3b82f6'],
    'users'      => ['fa-user',     '#a78bfa'],
    'inventory'  => ['fa-warehouse','#34d399'],
    'categories' => ['fa-tag',      '#fb923c'],
];
// Avatar colours keyed by first char
$avatarPalette = ['A'=>'#e30000','B'=>'#3b82f6','C'=>'#f59e0b','D'=>'#8b5cf6','E'=>'#10b981',
                  'F'=>'#ec4899','G'=>'#06b6d4','H'=>'#84cc16','I'=>'#f97316','J'=>'#6366f1',
                  'K'=>'#14b8a6','L'=>'#e30000','M'=>'#3b82f6','N'=>'#f59e0b','O'=>'#8b5cf6',
                  'P'=>'#10b981','Q'=>'#ec4899','R'=>'#06b6d4','S'=>'#84cc16','T'=>'#f97316',
                  'U'=>'#6366f1','V'=>'#14b8a6','W'=>'#e30000','X'=>'#3b82f6','Y'=>'#f59e0b','Z'=>'#8b5cf6'];
function avatarColor($name) {
    global $avatarPalette;
    $c = mb_strtoupper(mb_substr(trim($name),0,1,'UTF-8'),'UTF-8');
    return $avatarPalette[$c] ?? '#555';
}
function avatarInitial($name) {
    return mb_strtoupper(mb_substr(trim($name),0,1,'UTF-8'),'UTF-8');
}

// Build CSV export URL preserving current filters
$csvParams = $_GET; $csvParams['export'] = 'csv';
$csvUrl = '?' . http_build_query($csvParams);
?>
<style>
.log-stat-card{background:#1a1a1a;border:1px solid #222;border-radius:10px;padding:.85rem 1.1rem;display:flex;align-items:center;gap:.75rem;min-width:0}
.log-stat-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.85rem}
.log-stat-val{font-size:1.3rem;font-weight:800;color:#fff;line-height:1}
.log-stat-lbl{font-size:.68rem;color:#555;margin-top:2px}
.log-filters{background:#141414;border:1px solid #1e1e1e;border-radius:10px;padding:.75rem 1rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem}
.log-inp{background:#0d0d0d;border:1px solid #2a2a2a;color:#ccc;border-radius:6px;padding:.35rem .65rem;font-size:.78rem;outline:none;transition:border .15s}
.log-inp:focus{border-color:#e30000}
.log-inp::placeholder{color:#3a3a3a}
.log-table{width:100%;border-collapse:collapse;font-size:.78rem}
.log-table thead tr{background:#0d0d0d}
.log-table th{padding:.6rem .85rem;text-align:left;color:#3a3a3a;font-weight:600;font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;border-bottom:1px solid #1a1a1a}
.log-row{border-bottom:1px solid #181818;cursor:pointer;transition:background .12s}
.log-row:hover{background:#0f0f0f}
.log-row.active{background:#0a0f0a}
.log-expand{display:none;background:#0a0a0a;border-bottom:1px solid #1a1a1a}
.log-expand.open{display:table-row}
.log-avatar{width:30px;height:30px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;flex-shrink:0;color:#fff}
.log-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:5px;font-size:.67rem;font-weight:700;white-space:nowrap}
.change-pill{display:inline-flex;align-items:center;gap:3px;font-size:.68rem;padding:1px 6px;border-radius:4px;background:#111;border:1px solid #222;margin:1px}
.diff-block{font-family:monospace;font-size:.72rem;white-space:pre-wrap;word-break:break-all;background:#070707;border:1px solid #1e1e1e;border-radius:6px;padding:.65rem .85rem;color:#888;max-height:280px;overflow:auto}
.diff-block .dk{color:#4ade80}.diff-block .dv{color:#94a3b8}
.pg-btn{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:28px;padding:0 8px;border-radius:6px;font-size:.75rem;text-decoration:none;transition:background .12s,color .12s}
.pg-btn.cur{background:#e30000;color:#fff;font-weight:700}
.pg-btn:not(.cur){background:#1a1a1a;color:#666}
.pg-btn:not(.cur):hover{background:#252525;color:#aaa}
</style>

<?php
// ── Stats row ────────────────────────────────────────────────────
$stats = [
    ['icon'=>'fa-clock-rotate-left','bg'=>'#1a0d0d','ic'=>'#e30000','val'=>number_format($totalLogs),'lbl'=>'Tổng bản ghi'],
    ['icon'=>'fa-calendar-day',     'bg'=>'#0d1a0d','ic'=>'#4ade80','val'=>number_format($todayCnt), 'lbl'=>'Hôm nay'],
    ['icon'=>'fa-trash-can',        'bg'=>'#1a0d0d','ic'=>'#f87171','val'=>number_format($deleteCnt),'lbl'=>'Xóa hôm nay'],
    ['icon'=>'fa-user-clock',       'bg'=>'#0d0d1a','ic'=>'#c084fc','val'=>$topUser?htmlspecialchars($topUser['user_name']):'—','lbl'=>$topUser?'Hoạt động nhiều nhất ('.$topUser['c'].' lần)':'Chưa có hôm nay'],
];
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
  <h2 style="color:#fff;font-size:1rem;font-weight:800;margin:0;display:flex;align-items:center;gap:.5rem">
    <i class="fa-solid fa-clock-rotate-left" style="color:#e30000"></i>
    Nhật ký hoạt động
  </h2>
  <a href="<?= $csvUrl ?>" style="display:inline-flex;align-items:center;gap:.4rem;background:#052e16;color:#4ade80;border:1px solid #166534;border-radius:7px;padding:.3rem .85rem;font-size:.75rem;font-weight:700;text-decoration:none;transition:background .12s" onmouseover="this.style.background='#14532d'" onmouseout="this.style.background='#052e16'">
    <i class="fa-solid fa-file-csv"></i> Export CSV
  </a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.65rem;margin-bottom:1rem">
<?php foreach($stats as $s): ?>
  <div class="log-stat-card">
    <div class="log-stat-icon" style="background:<?= $s['bg'] ?>">
      <i class="fa-solid <?= $s['icon'] ?>" style="color:<?= $s['ic'] ?>"></i>
    </div>
    <div>
      <div class="log-stat-val"><?= $s['val'] ?></div>
      <div class="log-stat-lbl"><?= $s['lbl'] ?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<!-- Filters -->
<form method="GET" class="log-filters" id="logForm">
  <i class="fa-solid fa-magnifying-glass" style="color:#333;font-size:.8rem"></i>
  <input type="text" name="q" value="<?= htmlspecialchars($_GET['q']??'') ?>" placeholder="Tìm tên, IP, hành động…" class="log-inp" style="flex:1;min-width:160px" id="logSearch">

  <select name="action" class="log-inp">
    <option value="">Tất cả hành động</option>
    <?php foreach(['CREATE'=>'Thêm mới','UPDATE'=>'Cập nhật','DELETE'=>'Xóa','LOGIN'=>'Đăng nhập','LOGOUT'=>'Đăng xuất'] as $av=>$al): ?>
    <option value="<?= $av ?>" <?= ($_GET['action']??'')===$av?'selected':'' ?>><?= $al ?></option>
    <?php endforeach; ?>
  </select>

  <select name="table" class="log-inp">
    <option value="">Tất cả bảng</option>
    <?php foreach(['products'=>'Sản phẩm','orders'=>'Đơn hàng','users'=>'Người dùng','inventory'=>'Kho','categories'=>'Danh mục'] as $tv=>$tl): ?>
    <option value="<?= $tv ?>" <?= ($_GET['table']??'')===$tv?'selected':'' ?>><?= $tl ?></option>
    <?php endforeach; ?>
  </select>

  <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from']??'') ?>" class="log-inp" title="Từ ngày">
  <input type="date" name="date_to"   value="<?= htmlspecialchars($_GET['date_to']??'') ?>"   class="log-inp" title="Đến ngày">

  <input type="text" name="uid_name" value="<?= htmlspecialchars($_GET['uid_name']??'') ?>" placeholder="Người dùng…" class="log-inp" style="width:110px">

  <button type="submit" style="background:#e30000;color:#fff;border:none;border-radius:6px;padding:.35rem .9rem;font-size:.78rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.35rem">
    <i class="fa-solid fa-filter"></i> Lọc
  </button>
  <a href="<?= APP_URL ?>/admin/logs" style="background:#1a1a1a;color:#666;border-radius:6px;padding:.35rem .7rem;font-size:.78rem;text-decoration:none;display:flex;align-items:center;gap:.3rem" title="Xóa bộ lọc">
    <i class="fa-solid fa-xmark"></i>
  </a>
</form>

<!-- Table -->
<div class="card" style="padding:0;overflow:hidden">
<?php if(empty($logs)): ?>
  <div style="text-align:center;padding:4rem;color:#333">
    <i class="fa-solid fa-clock-rotate-left" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.2"></i>
    <div style="font-size:.85rem">Không tìm thấy bản ghi nào</div>
  </div>
<?php else: ?>
<table class="log-table" id="logTable">
  <thead>
    <tr>
      <th style="width:36px"></th>
      <th style="width:150px">Người thực hiện</th>
      <th style="width:105px">Hành động</th>
      <th>Mô tả</th>
      <th style="width:200px">Thay đổi</th>
      <th style="width:95px">IP</th>
      <th style="width:90px">Thời gian</th>
    </tr>
  </thead>
  <tbody>
<?php
$hideKeys = ['slug','updated_at','created_at','deleted_at','deleted_by',
             'description','short_desc','specs','views','rating','review_count',
             'sold','session_id','remember_token','image'];
$contextKeys = ['name','product_name','order_code','fullname','note','slug','id'];

foreach($logs as $idx => $log):
    $rawAc   = $log['action'];
    $ac      = strtoupper($rawAc);
    if ($ac === 'TG_OFFSET') continue;
    $isNamed = in_array($rawAc, $namedActivities);
    $bcfg    = $isNamed ? $badgeCfg['NAMED'] : ($badgeCfg[$ac] ?? ['bg'=>'#111','fg'=>'#666','icon'=>'fa-circle']);
    $tInfo   = $tableIcons[$log['table_name']] ?? ['fa-database','#555'];
    $oldJ    = $log['old_data'] ? json_decode($log['old_data'],true) : null;
    $newJ    = $log['new_data'] ? json_decode($log['new_data'],true) : null;
    $desc    = makeDescription($log, $oldJ, $newJ);
    if ($desc === null) continue;

    // Compute visible changes
    $changes = [];
    if (($ac==='UPDATE'||$isNamed) && is_array($oldJ) && is_array($newJ)) {
        foreach ($newJ as $k=>$v) {
            if (in_array($k,$contextKeys)) continue;
            $ov = $oldJ[$k]??null;
            if ((string)$ov !== (string)$v) $changes[] = ['k'=>$k,'o'=>$ov,'n'=>$v];
        }
    } elseif ($ac==='CREATE' && is_array($newJ)) {
        foreach (['name','fullname','email','price','stock','role','status'] as $k)
            if (isset($newJ[$k])) $changes[] = ['k'=>$k,'o'=>null,'n'=>$newJ[$k]];
    } elseif ($ac==='DELETE' && is_array($oldJ)) {
        foreach (['name','fullname','email','price','status'] as $k)
            if (isset($oldJ[$k])) $changes[] = ['k'=>$k,'o'=>$oldJ[$k],'n'=>null];
    }
    $visChanges = array_values(array_filter($changes, function($c) use ($hideKeys){ return !in_array($c['k'],$hideKeys); }));

    // Role display
    $r     = (int)$log['user_role'];
    $roleC = [1=>'#e30000',2=>'#f59e0b',3=>'#3b82f6',0=>'#555'];
    $roleN = [1=>'Admin',2=>'Quản lý',3=>'Nhân viên',0=>'Khách'];

    // Relative time
    $ts    = strtotime($log['created_at']);
    $diff  = time() - $ts;
    if ($diff < 60)       $relT = $diff.'s';
    elseif ($diff < 3600) $relT = floor($diff/60).'ph';
    elseif ($diff < 86400)$relT = floor($diff/3600).'h';
    elseif ($diff < 604800)$relT= floor($diff/86400).'d';
    else                  $relT = date('d/m/y', $ts);

    $rowId = 'lr'.$idx;
    $avCol = avatarColor($log['user_name']);
    $avLtr = avatarInitial($log['user_name']);

    // Build JSON diff string for expand panel
    $jsonOld = $log['old_data'] ? json_encode(json_decode($log['old_data']),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : null;
    $jsonNew = $log['new_data'] ? json_encode(json_decode($log['new_data']),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : null;
?>
  <tr class="log-row" onclick="toggleRow('<?= $rowId ?>')" data-search="<?= htmlspecialchars(strtolower($log['user_name'].' '.$log['ip_address'].' '.$rawAc.' '.$log['table_name'])) ?>">

    <td style="padding:.5rem .5rem .5rem .85rem;vertical-align:middle">
      <i class="fa-solid fa-chevron-right" id="arr_<?= $rowId ?>" style="color:#2a2a2a;font-size:.6rem;transition:transform .18s"></i>
    </td>

    <td style="padding:.55rem .85rem;vertical-align:top">
      <div style="display:flex;align-items:center;gap:.5rem">
        <div class="log-avatar" style="background:<?= $avCol ?>1a;border:1px solid <?= $avCol ?>55">
          <span style="color:<?= $avCol ?>"><?= $avLtr ?></span>
        </div>
        <div>
          <div style="color:#d4d4d4;font-weight:600;font-size:.78rem;white-space:nowrap"><?= htmlspecialchars($log['user_name']) ?></div>
          <div style="font-size:.65rem;color:<?= $roleC[$r]??'#555' ?>;margin-top:1px"><?= $roleN[$r]??'?' ?></div>
        </div>
      </div>
    </td>

    <td style="padding:.55rem .85rem;vertical-align:top">
      <div class="log-badge" style="background:<?= $bcfg['bg'] ?>;color:<?= $bcfg['fg'] ?>" title="<?= htmlspecialchars($rawAc) ?>">
        <i class="fa-solid <?= $bcfg['icon'] ?>" style="font-size:.6rem"></i>
        <?= $isNamed ? htmlspecialchars(mb_substr($rawAc,0,10,'UTF-8')).(mb_strlen($rawAc,'UTF-8')>10?'…':'') : $ac ?>
      </div>
      <div style="font-size:.62rem;color:#2a2a2a;margin-top:3px;display:flex;align-items:center;gap:3px">
        <i class="fa-solid <?= $tInfo[0] ?>" style="color:<?= $tInfo[1] ?>;font-size:.58rem"></i>
        <?= htmlspecialchars($log['table_name']) ?><?= $log['target_id']?' #'.$log['target_id']:'' ?>
      </div>
    </td>

    <td style="padding:.55rem .85rem;vertical-align:top;line-height:1.55">
      <div style="color:#e0e0e0;font-size:.78rem"><?= $desc ?></div>
    </td>

    <td style="padding:.55rem .85rem;vertical-align:top">
      <?php if($visChanges): ?>
        <div style="display:flex;flex-wrap:wrap;gap:2px">
        <?php foreach(array_slice($visChanges,0,4) as $ch):
          $lbl = $fieldLabels[$ch['k']]??$ch['k'];
          $isUpd = $ch['o']!==null && $ch['n']!==null;
        ?>
          <span class="change-pill">
            <span style="color:#444"><?= htmlspecialchars($lbl) ?>:</span>
            <?php if($isUpd): ?>
              <span style="color:#f87171"><?= fmtVal($ch['k'],$ch['o']) ?></span>
              <span style="color:#333">→</span>
              <span style="color:#4ade80;font-weight:600"><?= fmtVal($ch['k'],$ch['n']) ?></span>
            <?php elseif($ch['n']!==null): ?>
              <span style="color:#d1d5db"><?= fmtVal($ch['k'],$ch['n']) ?></span>
            <?php else: ?>
              <span style="color:#f87171;text-decoration:line-through"><?= fmtVal($ch['k'],$ch['o']) ?></span>
            <?php endif; ?>
          </span>
        <?php endforeach; ?>
        <?php if(count($visChanges)>4): ?>
          <span style="color:#333;font-size:.65rem;padding:1px 4px">+<?= count($visChanges)-4 ?> khác</span>
        <?php endif; ?>
        </div>
      <?php else: ?>
        <span style="color:#222">—</span>
      <?php endif; ?>
    </td>

    <td style="padding:.55rem .85rem;vertical-align:top;white-space:nowrap">
      <div style="color:#3a3a3a;font-size:.68rem;font-family:monospace"><?= htmlspecialchars($log['ip_address']?:'—') ?></div>
    </td>

    <td style="padding:.55rem .85rem;vertical-align:top;white-space:nowrap;text-align:right">
      <div style="color:#555;font-size:.72rem"><?= $relT ?></div>
      <div style="color:#2d2d2d;font-size:.62rem;margin-top:2px"><?= date('d/m H:i',$ts) ?></div>
    </td>
  </tr>

  <!-- Expand row -->
  <tr class="log-expand" id="<?= $rowId ?>">
    <td colspan="7" style="padding:.75rem 1.1rem 1rem 3rem">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;max-width:900px">
        <?php if($jsonOld): ?>
        <div>
          <div style="font-size:.65rem;color:#f87171;font-weight:700;margin-bottom:.3rem;display:flex;align-items:center;gap:.3rem">
            <i class="fa-solid fa-circle-minus" style="font-size:.6rem"></i> BEFORE
          </div>
          <div class="diff-block"><?= htmlspecialchars($jsonOld) ?></div>
        </div>
        <?php endif; ?>
        <?php if($jsonNew): ?>
        <div>
          <div style="font-size:.65rem;color:#4ade80;font-weight:700;margin-bottom:.3rem;display:flex;align-items:center;gap:.3rem">
            <i class="fa-solid fa-circle-plus" style="font-size:.6rem"></i> AFTER
          </div>
          <div class="diff-block"><?= htmlspecialchars($jsonNew) ?></div>
        </div>
        <?php endif; ?>
        <?php if(!$jsonOld && !$jsonNew): ?>
        <div style="color:#333;font-size:.75rem;grid-column:1/-1">Không có dữ liệu chi tiết</div>
        <?php endif; ?>
      </div>
      <div style="margin-top:.5rem;font-size:.65rem;color:#2a2a2a">
        <?= date('d/m/Y H:i:s', $ts) ?> &nbsp;·&nbsp; ID #<?= $log['id'] ?> &nbsp;·&nbsp; <?= htmlspecialchars($log['ip_address']?:'—') ?>
      </div>
    </td>
  </tr>

<?php endforeach; ?>
  </tbody>
</table>

<!-- Pagination -->
<?php if($totalPagesAdmin > 1 || true): // always show footer ?>
<div style="padding:.65rem 1rem;border-top:1px solid #1a1a1a;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
  <div style="font-size:.72rem;color:#444">
    <?php
    $perPage = 50;
    $from = ($page-1)*$perPage+1;
    $to   = min($page*$perPage, $totalLogs);
    ?>
    Hiển thị <span style="color:#666"><?= number_format($from) ?>–<?= number_format($to) ?></span>
    / <span style="color:#666"><?= number_format($totalLogs) ?></span> bản ghi
  </div>

  <?php if($totalPagesAdmin > 1): ?>
  <div style="display:flex;gap:.25rem;align-items:center;flex-wrap:wrap">
    <?php
    $q = $_GET;
    // Prev
    if ($page > 1) {
        $q['page']=$page-1;
        echo '<a href="?'.http_build_query($q).'" class="pg-btn"><i class="fa-solid fa-chevron-left" style="font-size:.6rem"></i></a>';
    }
    // Page numbers — show max 7 with ellipsis
    $start = max(1, $page-3);
    $end   = min($totalPagesAdmin, $page+3);
    if ($start > 1)  { $q['page']=1; echo '<a href="?'.http_build_query($q).'" class="pg-btn">1</a>'; if($start>2) echo '<span style="color:#333;padding:0 4px;font-size:.72rem">…</span>'; }
    for ($i=$start;$i<=$end;$i++) {
        $q['page']=$i;
        $cur = ($i===$page);
        echo '<a href="?'.http_build_query($q).'" class="pg-btn'.($cur?' cur':'').'">'.$i.'</a>';
    }
    if ($end < $totalPagesAdmin) { if($end<$totalPagesAdmin-1) echo '<span style="color:#333;padding:0 4px;font-size:.72rem">…</span>'; $q['page']=$totalPagesAdmin; echo '<a href="?'.http_build_query($q).'" class="pg-btn">'.$totalPagesAdmin.'</a>'; }
    // Next
    if ($page < $totalPagesAdmin) {
        $q['page']=$page+1;
        echo '<a href="?'.http_build_query($q).'" class="pg-btn"><i class="fa-solid fa-chevron-right" style="font-size:.6rem"></i></a>';
    }
    ?>
  </div>
  <?php endif; ?>

  <div style="display:flex;align-items:center;gap:.4rem;font-size:.72rem;color:#444">
    Hàng/trang:
    <select onchange="window.location='?<?= http_build_query(array_merge($_GET,['per_page'=>'__V__'])) ?>'.replace('__V__',this.value)" class="log-inp" style="padding:.2rem .4rem">
      <?php foreach([25,50,100,200] as $pp): ?>
      <option value="<?= $pp ?>" <?= ($perPage==$pp)?'selected':'' ?>><?= $pp ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>
<?php endif; ?>

<?php endif; ?>
</div>

<script>
function toggleRow(id) {
    var ex  = document.getElementById(id);
    var arr = document.getElementById('arr_' + id);
    var row = ex ? ex.previousElementSibling : null;
    if (!ex) return;
    var open = ex.classList.toggle('open');
    if (arr) arr.style.transform = open ? 'rotate(90deg)' : '';
    if (row) row.classList.toggle('active', open);
}

// Client-side search filter
var searchInp = document.getElementById('logSearch');
if (searchInp) {
    // Only apply JS search if no server-side q param was used (to avoid conflict)
    searchInp.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        var rows = document.querySelectorAll('#logTable tbody .log-row');
        rows.forEach(function(row) {
            var next = row.nextElementSibling;
            var hay  = (row.dataset.search || '') + ' ' + row.textContent.toLowerCase();
            var show = !q || hay.indexOf(q) !== -1;
            row.style.display = show ? '' : 'none';
            if (next && next.classList.contains('log-expand')) next.style.display = 'none';
        });
    });
}
</script>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
