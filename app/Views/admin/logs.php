<?php require_once __DIR__.'/layout_top.php'; ?>
<?php
$actionColors = [
    'CREATE' => ['#dcfce7','#166534'],
    'UPDATE' => ['#dbeafe','#1e40af'],
    'DELETE' => ['#fee2e2','#991b1b'],
    'LOGIN'  => ['#f3e8ff','#6b21a8'],
    'LOGOUT' => ['#f1f5f9','#475569'],
];
$tableIcons = [
    'products'  => 'fa-box',
    'orders'    => 'fa-receipt',
    'users'     => 'fa-user',
    'inventory' => 'fa-warehouse',
    'categories'=> 'fa-tag',
];
?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;flex-wrap:wrap;gap:.5rem">
  <h2 style="color:#fff;font-size:1rem;font-weight:800;margin:0;display:flex;align-items:center;gap:.5rem">
    <i class="fa-solid fa-clock-rotate-left" style="color:var(--red)"></i> Nhật ký hoạt động
    <span style="background:#1a1a1a;color:#aaa;padding:2px 8px;border-radius:99px;font-size:.7rem;font-weight:600"><?= number_format($totalLogs) ?> bản ghi</span>
  </h2>
  <!-- Filters -->
  <form method="GET" style="display:flex;gap:.4rem;flex-wrap:wrap">
    <select name="action" class="form-inp" style="font-size:.78rem;padding:.3rem .6rem">
      <option value="">Tất cả hành động</option>
      <?php foreach(['CREATE','UPDATE','DELETE','LOGIN','LOGOUT'] as $a): ?>
      <option value="<?= $a ?>" <?= ($_GET['action']??'')===$a?'selected':'' ?>><?= $a ?></option>
      <?php endforeach; ?>
    </select>
    <select name="table" class="form-inp" style="font-size:.78rem;padding:.3rem .6rem">
      <option value="">Tất cả bảng</option>
      <?php foreach(['products','orders','users','inventory','categories'] as $t): ?>
      <option value="<?= $t ?>" <?= ($_GET['table']??'')===$t?'selected':'' ?>><?= $t ?></option>
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
      <tr style="background:#111">
        <th style="padding:.6rem .85rem;text-align:left;color:#888;font-weight:600;white-space:nowrap">Thời gian</th>
        <th style="padding:.6rem .85rem;text-align:left;color:#888;font-weight:600">Người dùng</th>
        <th style="padding:.6rem .85rem;text-align:left;color:#888;font-weight:600">Hành động</th>
        <th style="padding:.6rem .85rem;text-align:left;color:#888;font-weight:600">Bảng / ID</th>
        <th style="padding:.6rem .85rem;text-align:left;color:#888;font-weight:600">Thay đổi</th>
        <th style="padding:.6rem .85rem;text-align:left;color:#888;font-weight:600">IP</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($logs as $log): ?>
    <?php
      $ac   = strtoupper($log['action']);
      $clr  = $actionColors[$ac] ?? ['#1c1c1c','#aaa'];
      $icon = $tableIcons[$log['table_name']] ?? 'fa-database';
      $oldJ = $log['old_data'] ? json_decode($log['old_data'], true) : null;
      $newJ = $log['new_data'] ? json_decode($log['new_data'], true) : null;
    ?>
    <tr style="border-bottom:1px solid #1a1a1a" onmouseover="this.style.background='#0f0f0f'" onmouseout="this.style.background=''">
      <td style="padding:.55rem .85rem;color:#666;white-space:nowrap">
        <?= date('d/m H:i', strtotime($log['created_at'])) ?>
        <div style="font-size:.68rem;color:#444"><?= date('Y', strtotime($log['created_at'])) ?></div>
      </td>
      <td style="padding:.55rem .85rem">
        <div style="color:#ddd;font-weight:600"><?= htmlspecialchars($log['user_name']) ?></div>
        <div style="font-size:.68rem;color:#555">
          <?php
          $rl = [1=>'Admin',2=>'Manager',3=>'Staff',0=>'Customer'];
          $rc = [1=>'#e30000',2=>'#f59e0b',3=>'#3b82f6'];
          $r = (int)$log['user_role'];
          ?>
          <span style="color:<?= $rc[$r]??'#888' ?>"><?= $rl[$r]??'?' ?></span>
        </div>
      </td>
      <td style="padding:.55rem .85rem">
        <span style="background:<?= $clr[0] ?>;color:<?= $clr[1] ?>;padding:2px 8px;border-radius:5px;font-size:.7rem;font-weight:700">
          <?= $ac ?>
        </span>
      </td>
      <td style="padding:.55rem .85rem;color:#aaa">
        <i class="fa-solid <?= $icon ?>" style="font-size:.7rem;margin-right:3px"></i>
        <?= htmlspecialchars($log['table_name']) ?>
        <?php if($log['target_id']): ?>
        <span style="color:#555"> #<?= $log['target_id'] ?></span>
        <?php endif; ?>
      </td>
      <td style="padding:.55rem .85rem;max-width:280px">
        <?php if($oldJ || $newJ): ?>
        <div style="font-size:.7rem;line-height:1.6">
          <?php if(is_array($oldJ)): foreach($oldJ as $k=>$v): ?>
          <div><span style="color:#ef4444">− <?= htmlspecialchars($k) ?>:</span> <span style="color:#777"><?= htmlspecialchars(substr((string)$v,0,60)) ?></span></div>
          <?php endforeach; endif; ?>
          <?php if(is_array($newJ)): foreach($newJ as $k=>$v): ?>
          <div><span style="color:#4ade80">+ <?= htmlspecialchars($k) ?>:</span> <span style="color:#aaa"><?= htmlspecialchars(substr((string)$v,0,60)) ?></span></div>
          <?php endforeach; endif; ?>
        </div>
        <?php else: ?>
        <span style="color:#444">—</span>
        <?php endif; ?>
      </td>
      <td style="padding:.55rem .85rem;color:#444;font-size:.7rem"><?= htmlspecialchars($log['ip_address']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php if($totalPagesAdmin > 1): ?>
  <div style="padding:.75rem .85rem;border-top:1px solid #1a1a1a;display:flex;gap:.35rem;flex-wrap:wrap">
    <?php
    $q = $_GET;
    for($i=1;$i<=$totalPagesAdmin;$i++):
      $q['page']=$i; $qs=http_build_query($q);
      $cur=($page==$i);
    ?>
    <a href="?<?= $qs ?>" style="padding:3px 9px;border-radius:5px;font-size:.75rem;text-decoration:none;
      background:<?= $cur?'var(--red)':'#1a1a1a' ?>;color:<?= $cur?'#fff':'#888' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
