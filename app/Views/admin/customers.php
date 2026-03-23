<?php require_once __DIR__.'/layout_top.php'; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem;margin-bottom:1.1rem">
<?php
$stats = [
    ['label'=>'Tổng khách hàng', 'val'=>$custStats['total'],  'icon'=>'fa-users',       'color'=>'#3b82f6'],
    ['label'=>'Đang hoạt động',  'val'=>$custStats['active'],  'icon'=>'fa-circle-check','color'=>'#4ade80'],
    ['label'=>'Bị khóa',         'val'=>$custStats['locked'],  'icon'=>'fa-lock',        'color'=>'#f87171'],
    ['label'=>'Mới hôm nay',     'val'=>$custStats['today'],   'icon'=>'fa-user-plus',   'color'=>'#fbbf24'],
];
foreach($stats as $s): ?>
<div class="card" style="padding:.85rem 1rem;display:flex;align-items:center;gap:.75rem">
  <div style="width:36px;height:36px;border-radius:8px;background:<?= $s['color'] ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0">
    <i class="fa-solid <?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:.9rem"></i>
  </div>
  <div>
    <div style="color:#fff;font-size:1.15rem;font-weight:800;line-height:1"><?= number_format($s['val']) ?></div>
    <div style="color:#555;font-size:.7rem;margin-top:2px"><?= $s['label'] ?></div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- Filter & Search -->
<div class="card" style="padding:1rem 1.1rem">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;margin-bottom:.9rem">
    <h2 style="color:#fff;font-size:.95rem;font-weight:700;margin:0;display:flex;align-items:center;gap:.5rem">
      <i class="fa-solid fa-users" style="color:var(--red)"></i> Danh sách khách hàng
      <span style="background:#1a1a1a;color:#666;padding:2px 8px;border-radius:99px;font-size:.7rem"><?= number_format($totalCustomers) ?></span>
    </h2>
    <form method="GET" style="display:flex;gap:.4rem;flex-wrap:wrap;align-items:center">
      <input type="text" name="s" value="<?= htmlspecialchars($search??'') ?>"
             placeholder="Tên, email, SĐT..." class="form-inp" style="min-width:200px">
      <select name="status" class="form-inp" style="font-size:.78rem;padding:.35rem .6rem">
        <option value="">Tất cả trạng thái</option>
        <option value="active" <?= ($status??'')==='active'?'selected':'' ?>>✅ Hoạt động</option>
        <option value="locked" <?= ($status??'')==='locked'?'selected':'' ?>>🔒 Bị khóa</option>
      </select>
      <button type="submit" class="btn-r" style="padding:.38rem .8rem">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
      <?php if(($search??'')||($status??'')): ?>
      <a href="<?= APP_URL ?>/admin/customers" class="btn-g" style="padding:.38rem .7rem;text-decoration:none">
        <i class="fa-solid fa-xmark"></i>
      </a>
      <?php endif; ?>
    </form>
  </div>

  <div style="overflow-x:auto">
    <table class="adm-table">
      <thead>
        <tr>
          <th style="width:36px">#</th>
          <th>Khách hàng</th>
          <th>Liên hệ</th>
          <th style="text-align:center">Đơn hàng</th>
          <th style="text-align:right">Tổng chi tiêu</th>
          <th>Đơn gần nhất</th>
          <th>Ngày đăng ký</th>
          <th style="text-align:center">Trạng thái</th>
          <th style="text-align:center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
      <?php if(empty($customers)): ?>
        <tr><td colspan="9" style="text-align:center;padding:2.5rem;color:#444">
          <i class="fa-solid fa-users" style="font-size:1.5rem;display:block;margin-bottom:.4rem;opacity:.2"></i>
          Không tìm thấy khách hàng nào
        </td></tr>
      <?php else: ?>
      <?php foreach($customers as $c):
        $initials = mb_strtoupper(mb_substr($c['fullname'],0,1,'UTF-8'),'UTF-8');
        $colors   = ['#e30000','#3b82f6','#8b5cf6','#f59e0b','#10b981','#ec4899'];
        $color    = $colors[$c['id'] % count($colors)];
        $isNew    = (strtotime($c['created_at']) > strtotime('-7 days'));
      ?>
      <tr>
        <td style="color:#444;font-size:.72rem"><?= $c['id'] ?></td>

        <td>
          <div style="display:flex;align-items:center;gap:.6rem">
            <div style="width:32px;height:32px;border-radius:50%;background:<?= $color ?>;
                        display:flex;align-items:center;justify-content:center;
                        color:#fff;font-size:.78rem;font-weight:700;flex-shrink:0">
              <?= $initials ?>
            </div>
            <div>
              <div style="color:#e0e0e0;font-weight:600;font-size:.82rem">
                <?= htmlspecialchars($c['fullname']) ?>
                <?php if($isNew): ?>
                <span style="background:#fbbf2422;color:#fbbf24;font-size:.62rem;padding:1px 5px;border-radius:3px;margin-left:3px">MỚI</span>
                <?php endif; ?>
              </div>
              <div style="color:#444;font-size:.7rem"><?= htmlspecialchars($c['email']) ?></div>
            </div>
          </div>
        </td>

        <td>
          <div style="color:#777;font-size:.78rem"><?= htmlspecialchars($c['phone']??'—') ?></div>
          <?php if($c['city']): ?>
          <div style="color:#444;font-size:.7rem">
            <i class="fa-solid fa-location-dot" style="font-size:.6rem"></i>
            <?= htmlspecialchars($c['city']) ?>
          </div>
          <?php endif; ?>
        </td>

        <td style="text-align:center">
          <?php if($c['order_count'] > 0): ?>
          <span style="background:#1a1a2e;color:#60a5fa;padding:3px 10px;border-radius:99px;font-size:.75rem;font-weight:600">
            <?= $c['order_count'] ?> đơn
          </span>
          <?php else: ?>
          <span style="color:#333;font-size:.75rem">Chưa mua</span>
          <?php endif; ?>
        </td>

        <td style="text-align:right">
          <?php if($c['total_spent'] > 0): ?>
          <span style="color:#4ade80;font-weight:700;font-size:.82rem">
            <?= number_format((float)$c['total_spent'],0,',','.') ?>đ
          </span>
          <?php else: ?>
          <span style="color:#333;font-size:.75rem">—</span>
          <?php endif; ?>
        </td>

        <td style="color:#555;font-size:.75rem">
          <?php if($c['last_order_at']): ?>
          <div><?= date('d/m/Y', strtotime($c['last_order_at'])) ?></div>
          <div style="color:#3b3b3b;font-size:.68rem"><?= date('H:i', strtotime($c['last_order_at'])) ?></div>
          <?php else: ?>
          <span style="color:#333">—</span>
          <?php endif; ?>
        </td>

        <td style="color:#555;font-size:.75rem">
          <div><?= date('d/m/Y', strtotime($c['created_at'])) ?></div>
          <div style="color:#3b3b3b;font-size:.68rem"><?= date('H:i', strtotime($c['created_at'])) ?></div>
        </td>

        <td style="text-align:center">
          <?php if($c['is_active']): ?>
          <span style="background:#0a2a0a;color:#4ade80;padding:3px 8px;border-radius:5px;font-size:.7rem;font-weight:600">● Hoạt động</span>
          <?php else: ?>
          <span style="background:#2a0a0a;color:#f87171;padding:3px 8px;border-radius:5px;font-size:.7rem;font-weight:600">● Bị khóa</span>
          <?php endif; ?>
        </td>

        <td style="text-align:center">
          <div style="display:flex;gap:.35rem;justify-content:center;align-items:center">
            <!-- Xem đơn hàng -->
            <a href="<?= APP_URL ?>/admin/orders?s=<?= urlencode($c['email']) ?>"
               class="btn-g" style="padding:.28rem .55rem;font-size:.7rem"
               title="Xem đơn hàng">
              <i class="fa-solid fa-receipt"></i>
            </a>
            <!-- Khóa / Mở khóa -->
            <?php if($c['is_active']): ?>
            <a href="<?= APP_URL ?>/admin/customers/toggle?id=<?= $c['id'] ?>"
               class="btn-g" style="padding:.28rem .55rem;font-size:.7rem;border-color:rgba(239,68,68,.3);color:#f87171"
               onclick="return confirm('Khóa tài khoản <?= htmlspecialchars(addslashes($c['fullname'])) ?>?')"
               title="Khóa tài khoản">
              <i class="fa-solid fa-lock"></i>
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/admin/customers/toggle?id=<?= $c['id'] ?>"
               class="btn-g" style="padding:.28rem .55rem;font-size:.7rem;border-color:rgba(74,222,128,.3);color:#4ade80"
               onclick="return confirm('Mở khóa tài khoản <?= htmlspecialchars(addslashes($c['fullname'])) ?>?')"
               title="Mở khóa tài khoản">
              <i class="fa-solid fa-lock-open"></i>
            </a>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if(($totalPagesAdmin??1) > 1): ?>
  <div style="display:flex;gap:.3rem;margin-top:.9rem;justify-content:center;flex-wrap:wrap">
    <?php
    $q = $_GET;
    for($i=1;$i<=($totalPagesAdmin??1);$i++):
      $q['page'] = $i;
    ?>
    <a href="?<?= http_build_query($q) ?>"
       style="padding:.3rem .65rem;border-radius:5px;text-decoration:none;font-size:.78rem;
              <?= $i==($page??1)?'background:var(--red);color:#fff':'background:#1a1a1a;color:#888;border:1px solid #222' ?>">
      <?= $i ?>
    </a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
