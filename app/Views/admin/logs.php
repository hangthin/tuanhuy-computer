<?php require_once __DIR__.'/layout_top.php'; ?>
<?php
// ── Nhãn tiếng Việt cho từng trường ────────────────────────────
$fieldLabels = [
    'name'           => 'Tên',
    'price'          => 'Giá',
    'sale_price'     => 'Giá KM',
    'stock'          => 'Tồn kho',
    'stock_quantity' => 'Tồn kho',
    'min_stock'      => 'Tồn tối thiểu',
    'is_active'      => 'Trạng thái',
    'is_deleted'     => 'Đã xóa',
    'is_featured'    => 'Nổi bật',
    'status'         => 'Trạng thái',
    'payment_status' => 'Thanh toán',
    'role'           => 'Vai trò',
    'fullname'       => 'Họ tên',
    'email'          => 'Email',
    'phone'          => 'SĐT',
    'category_id'    => 'Danh mục',
    'brand_id'       => 'Thương hiệu',
    'warranty'       => 'Bảo hành (tháng)',
    'short_desc'     => 'Mô tả ngắn',
    'sku'            => 'SKU',
    'slug'           => 'Slug',
    'note'           => 'Ghi chú',
    'notes'          => 'Ghi chú',
];
$roleLabels  = [1=>'Admin',2=>'Quản lý',3=>'Nhân viên',0=>'Khách'];
$statusLabels = [
    'pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận',
    'processing'=>'Đang xử lý','shipping'=>'Đang giao',
    'delivered'=>'Đã giao','cancelled'=>'Đã hủy',
    'paid'=>'Đã thanh toán','failed'=>'Thất bại','refunded'=>'Hoàn tiền',
];

function fmtVal($key, $val) {
    if ($val === null || $val === '') return '<span style="color:#444">—</span>';
    $priceFields = ['price','sale_price','total','subtotal','revenue'];
    if (in_array($key, $priceFields) && is_numeric($val))
        return '<b>' . number_format((float)$val,0,',','.') . 'đ</b>';
    if ($key === 'is_active')  return $val ? '<span style="color:#4ade80">Hiện</span>' : '<span style="color:#f87171">Ẩn</span>';
    if ($key === 'is_deleted') return $val ? '<span style="color:#f87171">Đã xóa</span>' : '<span style="color:#4ade80">Bình thường</span>';
    if ($key === 'is_featured')return $val ? '<span style="color:#fbbf24">Nổi bật</span>' : 'Thường';
    if ($key === 'role') { global $roleLabels; return $roleLabels[(int)$val] ?? $val; }
    if (in_array($key, ['status','payment_status'])) { global $statusLabels; return $statusLabels[$val] ?? $val; }
    if ($key === 'warranty') return $val . ' tháng';
    if ($key === 'stock' || $key === 'stock_quantity') return $val . ' cái';
    return htmlspecialchars(mb_substr((string)$val, 0, 80, 'UTF-8'));
}

// Sinh câu mô tả hành động
function makeDescription($log, $oldJ, $newJ) {
    global $roleLabels, $statusLabels;
    $action = strtoupper($log['action']);
    $table  = $log['table_name'];
    $id     = $log['target_id'];

    // Bỏ qua bản ghi nội bộ của bot
    if ($action === 'TG_OFFSET') return null;

    $name = '';
    if (is_array($newJ) && !empty($newJ['name']))     $name = $newJ['name'];
    if (!$name && is_array($oldJ) && !empty($oldJ['name'])) $name = $oldJ['name'];
    if (!$name && is_array($newJ) && !empty($newJ['fullname'])) $name = $newJ['fullname'];
    if (!$name && is_array($oldJ) && !empty($oldJ['fullname'])) $name = $oldJ['fullname'];
    if (!$name && is_array($newJ) && !empty($newJ['email']))    $name = $newJ['email'];

    $nameStr = $name ? ' <b>' . htmlspecialchars(mb_substr($name,0,50,'UTF-8')) . '</b>' : ($id ? " #$id" : '');

    // products
    if ($table === 'products') {
        if ($action === 'CREATE') {
            $price = is_array($newJ) && isset($newJ['price']) ? ' — ' . number_format((float)$newJ['price'],0,',','.') . 'đ' : '';
            return "Thêm sản phẩm{$nameStr}{$price}";
        }
        if ($action === 'UPDATE') {
            if (is_array($newJ) && array_keys($newJ) === ['is_deleted'] && $newJ['is_deleted'] == 0)
                return "Khôi phục sản phẩm{$nameStr}";
            if (is_array($newJ) && isset($newJ['status']))
                return "Đổi trạng thái SP{$nameStr}: <b>{$newJ['status']}</b>";
            if (is_array($newJ) && isset($newJ['is_active']))
                return ($newJ['is_active'] ? 'Hiện' : 'Ẩn') . " sản phẩm{$nameStr}";
            if (is_array($newJ) && isset($newJ['price']))
                return "Cập nhật giá sản phẩm{$nameStr}: <b>" . number_format((float)$newJ['price'],0,',','.') . "đ</b>";
            return "Cập nhật sản phẩm{$nameStr}";
        }
        if ($action === 'DELETE') return "Xóa sản phẩm{$nameStr}";
    }

    // orders
    if ($table === 'orders') {
        if ($action === 'UPDATE') {
            if (is_array($newJ) && isset($newJ['status'])) {
                $oldS = (is_array($oldJ) && isset($oldJ['status'])) ? ($statusLabels[$oldJ['status']] ?? $oldJ['status']) : '';
                $newS = $statusLabels[$newJ['status']] ?? $newJ['status'];
                $arrow = $oldS ? " <span style='color:#555'>$oldS</span> → <b>$newS</b>" : " → <b>$newS</b>";
                return "Cập nhật đơn hàng #$id{$arrow}";
            }
            return "Cập nhật đơn hàng #$id";
        }
        if ($action === 'CREATE') return "Tạo đơn hàng #$id{$nameStr}";
        if ($action === 'DELETE') return "Xóa đơn hàng #$id";
    }

    // users
    if ($table === 'users') {
        if ($action === 'LOGIN')  return "Đăng nhập" . ($name ? " — <b>{$name}</b>" : '');
        if ($action === 'LOGOUT') return "Đăng xuất" . ($name ? " — <b>{$name}</b>" : '');
        if ($action === 'CREATE') return "Tạo tài khoản{$nameStr}";
        if ($action === 'UPDATE') {
            if (is_array($newJ) && isset($newJ['is_active']))
                return ($newJ['is_active'] ? '🔓 Mở khóa' : '🔒 Khóa') . " tài khoản{$nameStr}";
            if (is_array($newJ) && isset($newJ['role'])) {
                $roleStr = $roleLabels[(int)$newJ['role']] ?? $newJ['role'];
                return "Đổi vai trò{$nameStr} → <b>$roleStr</b>";
            }
            if (is_array($newJ) && isset($newJ['note']) && $newJ['note'] === 'password_reset')
                return "Đặt lại mật khẩu{$nameStr}";
            return "Cập nhật tài khoản{$nameStr}";
        }
        if ($action === 'DELETE') return "Xóa tài khoản{$nameStr}";
    }

    // inventory
    if ($table === 'inventory') {
        if ($action === 'UPDATE') {
            $oldQ = is_array($oldJ) && isset($oldJ['stock_quantity']) ? (int)$oldJ['stock_quantity'] : null;
            $newQ = is_array($newJ) && isset($newJ['stock_quantity']) ? (int)$newJ['stock_quantity'] : null;
            if ($oldQ !== null && $newQ !== null) {
                $diff = $newQ - $oldQ;
                $arrow = $diff > 0 ? "<span style='color:#4ade80'>+$diff</span>" : "<span style='color:#f87171'>$diff</span>";
                return "Cập nhật tồn kho SP #$id: {$oldQ} → <b>{$newQ}</b> ({$arrow})";
            }
            return "Cập nhật tồn kho SP #$id";
        }
    }

    // categories
    if ($table === 'categories') {
        if ($action === 'CREATE') return "Thêm danh mục{$nameStr}";
        if ($action === 'UPDATE') return "Cập nhật danh mục{$nameStr}";
        if ($action === 'DELETE') return "Xóa danh mục{$nameStr}";
    }

    // Generic fallback
    $actionVi = ['CREATE'=>'Thêm','UPDATE'=>'Cập nhật','DELETE'=>'Xóa','LOGIN'=>'Đăng nhập','LOGOUT'=>'Đăng xuất'];
    return ($actionVi[$action] ?? $action) . " {$table}{$nameStr}";
}

$actionColors = [
    'CREATE' => ['#052e16','#4ade80'],
    'UPDATE' => ['#0c1a3b','#60a5fa'],
    'DELETE' => ['#2d0a0a','#f87171'],
    'LOGIN'  => ['#1e0a3b','#c084fc'],
    'LOGOUT' => ['#111','#6b7280'],
];
$tableIcons = [
    'products'   => ['fa-box','#f59e0b'],
    'orders'     => ['fa-receipt','#3b82f6'],
    'users'      => ['fa-user','#a78bfa'],
    'inventory'  => ['fa-warehouse','#34d399'],
    'categories' => ['fa-tag','#fb923c'],
];
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;flex-wrap:wrap;gap:.5rem">
  <h2 style="color:#fff;font-size:1rem;font-weight:800;margin:0;display:flex;align-items:center;gap:.5rem">
    <i class="fa-solid fa-clock-rotate-left" style="color:var(--red)"></i> Nhật ký hoạt động
    <span style="background:#1a1a1a;color:#aaa;padding:2px 8px;border-radius:99px;font-size:.7rem;font-weight:600"><?= number_format($totalLogs) ?> bản ghi</span>
  </h2>
  <form method="GET" style="display:flex;gap:.4rem;flex-wrap:wrap">
    <select name="action" class="form-inp" style="font-size:.78rem;padding:.3rem .6rem">
      <option value="">Tất cả hành động</option>
      <?php foreach(['CREATE','UPDATE','DELETE','LOGIN','LOGOUT'] as $a): ?>
      <option value="<?= $a ?>" <?= ($_GET['action']??'')===$a?'selected':'' ?>><?= $a ?></option>
      <?php endforeach; ?>
    </select>
    <select name="table" class="form-inp" style="font-size:.78rem;padding:.3rem .6rem">
      <option value="">Tất cả</option>
      <?php foreach(['products','orders','users','inventory','categories'] as $tb): ?>
      <option value="<?= $tb ?>" <?= ($_GET['table']??'')===$tb?'selected':'' ?>><?= $tb ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-g" style="font-size:.78rem;padding:.3rem .75rem"><i class="fa-solid fa-filter"></i> Lọc</button>
    <a href="<?= APP_URL ?>/admin/logs" class="btn-g" style="font-size:.78rem;padding:.3rem .75rem;text-decoration:none"><i class="fa-solid fa-xmark"></i></a>
  </form>
</div>

<div class="card" style="padding:0;overflow:hidden">
<?php if(empty($logs)): ?>
  <div style="text-align:center;padding:3rem;color:#555">
    <i class="fa-solid fa-clock-rotate-left" style="font-size:2rem;margin-bottom:.5rem;display:block;opacity:.3"></i>
    Chưa có nhật ký nào
  </div>
<?php else: ?>
<table style="width:100%;border-collapse:collapse;font-size:.78rem">
  <thead>
    <tr style="background:#0d0d0d">
      <th style="padding:.6rem .85rem;text-align:left;color:#555;font-weight:600;white-space:nowrap;width:110px">Thời gian</th>
      <th style="padding:.6rem .85rem;text-align:left;color:#555;font-weight:600;width:130px">Người thực hiện</th>
      <th style="padding:.6rem .85rem;text-align:left;color:#555;font-weight:600;width:70px">Loại</th>
      <th style="padding:.6rem .85rem;text-align:left;color:#555;font-weight:600">Mô tả hành động</th>
      <th style="padding:.6rem .85rem;text-align:left;color:#555;font-weight:600;width:220px">Chi tiết thay đổi</th>
      <th style="padding:.6rem .85rem;text-align:left;color:#555;font-weight:600;width:100px">IP</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($logs as $log):
    $ac   = strtoupper($log['action']);
    if ($ac === 'TG_OFFSET') continue; // ẩn bản ghi nội bộ bot
    $clr  = $actionColors[$ac] ?? ['#111','#888'];
    $tInfo= $tableIcons[$log['table_name']] ?? ['fa-database','#666'];
    $oldJ = $log['old_data'] ? json_decode($log['old_data'], true) : null;
    $newJ = $log['new_data'] ? json_decode($log['new_data'], true) : null;
    $desc = makeDescription($log, $oldJ, $newJ);
    if ($desc === null) continue;

    // Tính diff để hiển thị chi tiết thay đổi
    $changes = [];
    if ($ac === 'UPDATE' && is_array($oldJ) && is_array($newJ)) {
        foreach ($newJ as $k => $v) {
            $oldV = $oldJ[$k] ?? null;
            if ((string)$oldV !== (string)$v) {
                $changes[] = ['key'=>$k,'old'=>$oldV,'new'=>$v];
            }
        }
    } elseif ($ac === 'CREATE' && is_array($newJ)) {
        $important = ['name','fullname','email','price','stock','role','status'];
        foreach ($important as $k) {
            if (isset($newJ[$k])) $changes[] = ['key'=>$k,'old'=>null,'new'=>$newJ[$k]];
        }
    } elseif ($ac === 'DELETE' && is_array($oldJ)) {
        $important = ['name','fullname','email','price','status'];
        foreach ($important as $k) {
            if (isset($oldJ[$k])) $changes[] = ['key'=>$k,'old'=>$oldJ[$k],'new'=>null];
        }
    }
    global $fieldLabels;
  ?>
  <tr style="border-bottom:1px solid #141414" onmouseover="this.style.background='#0a0a0a'" onmouseout="this.style.background=''">

    <td style="padding:.6rem .85rem;color:#555;white-space:nowrap;vertical-align:top">
      <div style="color:#777;font-size:.75rem"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
      <div style="color:#444;font-size:.68rem"><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
    </td>

    <td style="padding:.6rem .85rem;vertical-align:top">
      <div style="color:#ddd;font-weight:600;font-size:.78rem"><?= htmlspecialchars($log['user_name']) ?></div>
      <?php
        $r = (int)$log['user_role'];
        $roleC = [1=>'#e30000',2=>'#f59e0b',3=>'#3b82f6',0=>'#6b7280'];
        $roleN = [1=>'Admin',2=>'Quản lý',3=>'Nhân viên',0=>'Khách'];
      ?>
      <div style="font-size:.68rem;color:<?= $roleC[$r]??'#666' ?>;margin-top:1px"><?= $roleN[$r]??'?' ?></div>
    </td>

    <td style="padding:.6rem .85rem;vertical-align:top">
      <span style="display:inline-flex;align-items:center;gap:4px;background:<?= $clr[0] ?>;color:<?= $clr[1] ?>;padding:3px 8px;border-radius:5px;font-size:.68rem;font-weight:700;white-space:nowrap">
        <i class="fa-solid <?= $tInfo[0] ?>" style="color:<?= $tInfo[1] ?>;font-size:.65rem"></i>
        <?= $ac ?>
      </span>
    </td>

    <td style="padding:.6rem .85rem;vertical-align:top;line-height:1.5">
      <div style="color:#e0e0e0"><?= $desc ?></div>
      <?php if($log['target_id'] && !in_array($ac,['LOGIN','LOGOUT'])): ?>
      <div style="font-size:.68rem;color:#444;margin-top:2px">
        <i class="fa-solid <?= $tInfo[0] ?>" style="font-size:.6rem"></i>
        <?= htmlspecialchars($log['table_name']) ?> #<?= $log['target_id'] ?>
      </div>
      <?php endif; ?>
    </td>

    <td style="padding:.6rem .85rem;vertical-align:top;max-width:220px">
      <?php if($changes): ?>
      <div style="font-size:.7rem;line-height:1.7">
        <?php foreach(array_slice($changes,0,5) as $ch):
          $label = $fieldLabels[$ch['key']] ?? $ch['key'];
        ?>
        <?php if($ch['old'] !== null && $ch['new'] !== null): ?>
          <div style="display:flex;flex-wrap:wrap;gap:3px;align-items:center">
            <span style="color:#888"><?= htmlspecialchars($label) ?>:</span>
            <span style="color:#ef4444;text-decoration:line-through;font-size:.67rem"><?= fmtVal($ch['key'],$ch['old']) ?></span>
            <span style="color:#555">→</span>
            <span style="color:#4ade80"><?= fmtVal($ch['key'],$ch['new']) ?></span>
          </div>
        <?php elseif($ch['new'] !== null): ?>
          <div>
            <span style="color:#888"><?= htmlspecialchars($label) ?>:</span>
            <span style="color:#ccc"> <?= fmtVal($ch['key'],$ch['new']) ?></span>
          </div>
        <?php elseif($ch['old'] !== null): ?>
          <div>
            <span style="color:#888"><?= htmlspecialchars($label) ?>:</span>
            <span style="color:#ef4444;text-decoration:line-through"> <?= fmtVal($ch['key'],$ch['old']) ?></span>
          </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php if(count($changes) > 5): ?>
        <div style="color:#444;font-size:.65rem">+<?= count($changes)-5 ?> thay đổi khác</div>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <span style="color:#333">—</span>
      <?php endif; ?>
    </td>

    <td style="padding:.6rem .85rem;color:#444;font-size:.68rem;vertical-align:top;white-space:nowrap">
      <?= htmlspecialchars($log['ip_address'] ?: '—') ?>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php if($totalPagesAdmin > 1): ?>
<div style="padding:.75rem .85rem;border-top:1px solid #1a1a1a;display:flex;gap:.35rem;flex-wrap:wrap;align-items:center">
  <span style="color:#555;font-size:.72rem;margin-right:.3rem">Trang:</span>
  <?php
  $q = $_GET;
  for($i=1;$i<=$totalPagesAdmin;$i++):
    $q['page']=$i; $qs=http_build_query($q);
    $cur=($page==$i);
  ?>
  <a href="?<?= $qs ?>" style="padding:3px 10px;border-radius:5px;font-size:.75rem;text-decoration:none;
    background:<?= $cur?'var(--red)':'#1a1a1a' ?>;color:<?= $cur?'#fff':'#888' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
