<?php require_once __DIR__.'/layout_top.php'; ?>
<?php
$avatarPalette = ['#e30000','#3b82f6','#8b5cf6','#f59e0b','#10b981','#ec4899'];
function custAvatarColor($id) {
    $c = ['#e30000','#3b82f6','#8b5cf6','#f59e0b','#10b981','#ec4899'];
    return $c[(int)$id % 6];
}
function custSegmentBadge($c) {
    $spent   = (float)$c['total_spent'];
    $orders  = (int)$c['order_count'];
    $created = $c['created_at'];
    $last    = $c['last_order_at'];
    if ($spent >= 10000000) {
        return '<span style="background:rgba(120,53,15,.35);color:#fbbf24;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700"><i class="fas fa-crown" style="font-size:.58rem;margin-right:2px"></i>VIP</span>';
    }
    if ($orders >= 5) {
        return '<span style="background:rgba(59,130,246,.18);color:#60a5fa;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700"><i class="fas fa-heart" style="font-size:.58rem;margin-right:2px"></i>Thân thiết</span>';
    }
    if (!empty($created) && strtotime($created) >= strtotime('-30 days')) {
        return '<span style="background:rgba(139,92,246,.18);color:#c084fc;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700"><i class="fas fa-star" style="font-size:.58rem;margin-right:2px"></i>Mới</span>';
    }
    if ($orders === 0 && !empty($created) && strtotime($created) < strtotime('-30 days')) {
        return '<span style="background:rgba(80,80,80,.18);color:#555;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700">Không HĐ</span>';
    }
    if (!empty($last) && strtotime($last) < strtotime('-90 days')) {
        return '<span style="background:rgba(80,80,80,.18);color:#555;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700">Không HĐ</span>';
    }
    return '';
}
function fmtVND($n) {
    return number_format((float)$n, 0, ',', '.') . 'đ';
}
$trendDiff  = (int)($stats['new_this_month'] ?? 0) - (int)($stats['new_last_month'] ?? 0);
$trendIcon  = $trendDiff >= 0 ? '▲' : '▼';
$trendColor = $trendDiff >= 0 ? '#4ade80' : '#f87171';
$segLabels  = ['all' => 'Tất cả', 'vip' => 'VIP', 'loyal' => 'Thân thiết', 'new' => 'Mới', 'inactive' => 'Không HĐ', 'locked' => 'Bị khóa'];
function pgLink($page, $segment, $search, $sort, $perPage) {
    return APP_URL . '/admin/customers?page=' . $page
        . '&segment=' . urlencode($segment)
        . '&s=' . urlencode($search)
        . '&sort=' . urlencode($sort)
        . '&per=' . $perPage;
}
?>
<style>
.seg-tab{display:inline-flex;align-items:center;gap:.35rem;padding:.48rem .85rem;font-size:.78rem;font-weight:600;color:#555;cursor:pointer;border-bottom:2px solid transparent;text-decoration:none;transition:color .15s,border-color .15s;white-space:nowrap}
.seg-tab:hover{color:#bbb}
.seg-tab.active{color:#fff;border-bottom-color:#e30000}
.seg-badge{background:#1a1a1a;color:#444;padding:1px 7px;border-radius:99px;font-size:.62rem;font-weight:700;line-height:1.5;transition:all .15s}
.seg-tab.active .seg-badge{background:rgba(227,0,0,.18);color:#e30000}
.cust-avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.82rem;color:#fff;flex-shrink:0;letter-spacing:0}
.sort-sel{background:#141414;border:1.5px solid #1e1e1e;color:#aaa;padding:.42rem .7rem;border-radius:7px;font-size:.78rem;outline:none;cursor:pointer;font-family:inherit;transition:border-color .15s}
.sort-sel:focus{border-color:#e30000}
.sort-sel option{background:#141414;color:#aaa}
.act-btn{width:29px;height:29px;border-radius:6px;border:1px solid #1e1e1e;background:transparent;color:#555;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;font-size:.72rem;text-decoration:none;flex-shrink:0}
.act-btn:hover{background:#1e1e1e;color:#ccc;border-color:#333}
.act-btn.act-danger:hover{background:rgba(239,68,68,.1);color:#f87171;border-color:rgba(239,68,68,.25)}
.act-btn.act-success:hover{background:rgba(74,222,128,.08);color:#4ade80;border-color:rgba(74,222,128,.25)}
.bulk-bar{position:fixed;bottom:0;left:220px;right:0;background:#141414;border-top:1px solid #1e1e1e;padding:.7rem 1.35rem;display:flex;align-items:center;gap:.65rem;z-index:200;transform:translateY(100%);transition:transform .22s cubic-bezier(.4,0,.2,1);box-shadow:0 -4px 24px rgba(0,0,0,.5)}
.bulk-bar.visible{transform:translateY(0)}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.78);z-index:500;display:none;align-items:center;justify-content:center;padding:1rem;backdrop-filter:blur(3px)}
.modal-overlay.open{display:flex;animation:fadeIn .18s ease}
.modal-box{background:#141414;border:1px solid #1e1e1e;border-radius:14px;width:100%;max-width:700px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 70px rgba(0,0,0,.75)}
.modal-hdr{padding:1.1rem 1.25rem;border-bottom:1px solid #1e1e1e;display:flex;align-items:center;gap:.85rem;flex-shrink:0}
.modal-bdy{flex:1;overflow-y:auto;padding:1.1rem 1.25rem;min-height:200px}
.modal-ftr{padding:.8rem 1.25rem;border-top:1px solid #1e1e1e;display:flex;gap:.45rem;align-items:center;flex-shrink:0}
.modal-tab{padding:.45rem .85rem;font-size:.78rem;font-weight:600;color:#555;cursor:pointer;border-bottom:2px solid transparent;transition:all .15s;user-select:none}
.modal-tab:hover{color:#bbb}
.modal-tab.active{color:#fff;border-bottom-color:#e30000}
.modal-tab-content{display:none}
.modal-tab-content.active{display:block;animation:fadeIn .18s ease}
.dstat{background:#0f0f0f;border:1px solid #1e1e1e;border-radius:9px;padding:.75rem 1rem;flex:1;min-width:130px}
.dstat-val{font-size:1.05rem;font-weight:800;color:#fff;line-height:1;margin-bottom:.2rem}
.dstat-lbl{font-size:.67rem;color:#444;text-transform:uppercase;letter-spacing:.4px}
.note-item{background:#0f0f0f;border:1px solid #1e1e1e;border-radius:8px;padding:.6rem .85rem;margin-bottom:.45rem}
.note-text{color:#bbb;font-size:.8rem;line-height:1.55}
.note-meta{color:#333;font-size:.67rem;margin-top:.3rem}
.scard{background:#141414;border:1px solid #1e1e1e;border-radius:12px;padding:1.05rem 1.2rem;transition:border-color .15s}
.scard:hover{border-color:#2a2a2a}
.scard-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
.scard-val{font-size:1.5rem;font-weight:900;color:#fff;line-height:1}
.scard-lbl{font-size:.71rem;color:#444;margin-top:.2rem}
.pg-btn{padding:.3rem .62rem;border-radius:5px;text-decoration:none;font-size:.77rem;border:1px solid #1e1e1e;color:#666;background:#141414;cursor:pointer;transition:all .15s;font-family:inherit;display:inline-flex;align-items:center;justify-content:center}
.pg-btn:hover:not(.active-pg){background:#1e1e1e;color:#ccc;border-color:#333}
.pg-btn.active-pg{background:#e30000;color:#fff;border-color:#e30000;font-weight:700}
input[type=checkbox]{accent-color:#e30000;width:14px;height:14px;cursor:pointer}
</style>

<!-- ── STATS GRID ──────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1.1rem">

  <div class="scard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
      <div class="scard-icon" style="background:rgba(59,130,246,.1)"><i class="fas fa-users" style="color:#60a5fa"></i></div>
      <span style="font-size:.62rem;color:#444;background:#0f0f0f;padding:2px 8px;border-radius:99px;border:1px solid #1e1e1e">Tổng</span>
    </div>
    <div class="scard-val"><?= number_format((int)$stats['total']) ?></div>
    <div class="scard-lbl">Tổng khách hàng</div>
  </div>

  <div class="scard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
      <div class="scard-icon" style="background:rgba(34,197,94,.08)"><i class="fas fa-user-plus" style="color:#4ade80"></i></div>
      <span style="font-size:.62rem;color:<?= $trendColor ?>;background:#0f0f0f;padding:2px 8px;border-radius:99px;border:1px solid #1e1e1e"><?= $trendIcon ?> <?= abs($trendDiff) ?></span>
    </div>
    <div class="scard-val"><?= number_format((int)$stats['new_this_month']) ?></div>
    <div class="scard-lbl">Mới tháng này</div>
  </div>

  <div class="scard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
      <div class="scard-icon" style="background:rgba(251,191,36,.08)"><i class="fas fa-crown" style="color:#fbbf24"></i></div>
      <span style="font-size:.62rem;color:#fbbf24;background:#0f0f0f;padding:2px 8px;border-radius:99px;border:1px solid #1e1e1e">≥ 10tr</span>
    </div>
    <div class="scard-val"><?= number_format((int)$stats['vip']) ?></div>
    <div class="scard-lbl">Khách hàng VIP</div>
  </div>

  <div class="scard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem">
      <div class="scard-icon" style="background:rgba(239,68,68,.08)"><i class="fas fa-user-clock" style="color:#f87171"></i></div>
      <span style="font-size:.62rem;color:#f87171;background:#0f0f0f;padding:2px 8px;border-radius:99px;border:1px solid #1e1e1e">&gt; 90 ngày</span>
    </div>
    <div class="scard-val"><?= number_format((int)$stats['inactive']) ?></div>
    <div class="scard-lbl">Không hoạt động</div>
  </div>

</div>

<!-- ── MAIN CARD ──────────────────────────────────────────── -->
<div class="card" style="padding:0;overflow:hidden">

  <!-- Segment Tabs -->
  <div style="border-bottom:1px solid #1e1e1e;padding:0 1.1rem;display:flex;gap:0;overflow-x:auto">
    <?php foreach ($segLabels as $sk => $sl):
      $cnt      = isset($segCounts[$sk]) ? (int)$segCounts[$sk] : 0;
      $isAct    = ($segment === $sk || ($sk === 'all' && $segment === ''));
      $href     = APP_URL . '/admin/customers?segment=' . $sk
                . ($search ? '&s=' . urlencode($search) : '')
                . ($sort !== 'newest' ? '&sort=' . urlencode($sort) : '')
                . ($perPage != 20 ? '&per=' . $perPage : '');
    ?>
    <a href="<?= $href ?>" class="seg-tab <?= $isAct ? 'active' : '' ?>">
      <?= $sl ?> <span class="seg-badge"><?= $cnt ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Filter Bar -->
  <div style="padding:.8rem 1.1rem;display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;border-bottom:1px solid #1e1e1e">
    <form method="GET" id="filter-form" style="display:contents">
      <input type="hidden" name="segment" value="<?= htmlspecialchars($segment) ?>">
      <input type="hidden" name="sort"    id="sort-hidden" value="<?= htmlspecialchars($sort) ?>">
      <input type="hidden" name="per"     id="per-hidden"  value="<?= $perPage ?>">
      <div style="position:relative;flex:1;min-width:190px;max-width:300px">
        <i class="fas fa-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:#333;font-size:.72rem;pointer-events:none"></i>
        <input type="text" name="s" id="search-inp" value="<?= htmlspecialchars($search) ?>"
               placeholder="Tên, email, SĐT..."
               class="form-inp" style="padding-left:2rem">
      </div>
      <select class="sort-sel" id="sort-sel" onchange="changeSortFilter(this.value)">
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
        <option value="spent"  <?= $sort === 'spent'  ? 'selected' : '' ?>>Chi tiêu cao</option>
        <option value="orders" <?= $sort === 'orders' ? 'selected' : '' ?>>Nhiều đơn</option>
      </select>
    </form>
    <div class="btn-export-group" style="display:flex;gap:.3rem">
      <button onclick="doExport()" class="btn-g" style="display:inline-flex;align-items:center;gap:.35rem;white-space:nowrap;font-size:.75rem;padding:.4rem .7rem">
        <i class="fas fa-file-csv" style="color:#22c55e"></i> CSV
      </button>
      <button onclick="window.print()" class="btn-g" style="font-size:.75rem;display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .7rem">
        <i class="fa-solid fa-print"></i> In
      </button>
      <?php $pdfQ = http_build_query(array_filter(['type'=>'customers','s'=>$search??'','segment'=>$segment??''])); ?>
      <a href="<?= APP_URL ?>/admin/export-pdf?<?= $pdfQ ?>" target="_blank" class="btn-g" style="font-size:.75rem;display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .7rem;text-decoration:none">
        <i class="fa-solid fa-file-pdf" style="color:#ef4444"></i> PDF
      </a>
    </div>
  </div>

  <!-- Table -->
  <div style="overflow-x:auto">
    <table class="adm-table">
      <thead>
        <tr>
          <th style="width:36px"><input type="checkbox" id="chk-all" title="Chọn tất cả"></th>
          <th style="width:38px">#</th>
          <th>Khách hàng</th>
          <th>SĐT</th>
          <th style="min-width:90px">Phân loại</th>
          <th style="text-align:center;min-width:52px">Đơn</th>
          <th style="min-width:110px">Chi tiêu</th>
          <th style="min-width:90px">Lần mua cuối</th>
          <th style="min-width:80px">Đăng ký</th>
          <th style="text-align:center;min-width:100px">Trạng thái</th>
          <th style="text-align:center;min-width:72px">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($customers)): ?>
        <tr><td colspan="11" style="text-align:center;padding:2.5rem;color:#333">
          <i class="fas fa-users" style="font-size:1.5rem;margin-bottom:.5rem;display:block;opacity:.2"></i>
          Không tìm thấy khách hàng nào.
        </td></tr>
        <?php else: ?>
        <?php foreach ($customers as $i => $c):
          $avCol    = custAvatarColor((int)$c['id']);
          $initial  = mb_strtoupper(mb_substr($c['fullname'], 0, 1, 'UTF-8'), 'UTF-8');
          $segBadge = custSegmentBadge($c);
          $rowNum   = ($page - 1) * $perPage + $i + 1;
          $isLocked = !(int)$c['is_active'];
          $lastOrd  = !empty($c['last_order_at']) ? date('d/m/y', strtotime($c['last_order_at'])) : '—';
          $regDate  = date('d/m/y', strtotime($c['created_at']));
          $toggleUrl = APP_URL . '/admin/customers/toggle?id=' . $c['id'];
        ?>
        <tr data-id="<?= (int)$c['id'] ?>" style="<?= $isLocked ? 'opacity:.55' : '' ?>">
          <td><input type="checkbox" class="row-chk" value="<?= (int)$c['id'] ?>"></td>
          <td style="color:#333;font-size:.72rem"><?= $rowNum ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:.6rem">
              <div class="cust-avatar" style="background:<?= $avCol ?>"><?= htmlspecialchars($initial) ?></div>
              <div style="min-width:0">
                <div style="color:#e0e0e0;font-weight:600;font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px"><?= htmlspecialchars($c['fullname']) ?></div>
                <div style="color:#3a3a3a;font-size:.69rem;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px"><?= htmlspecialchars($c['email']) ?></div>
                <?php if (!empty($c['city'])): ?>
                <div style="color:#2d2d2d;font-size:.65rem;margin-top:.06rem"><i class="fas fa-map-marker-alt" style="font-size:.56rem"></i> <?= htmlspecialchars($c['city']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td style="color:#666;font-size:.78rem"><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
          <td><?= $segBadge ?: '<span style="color:#2a2a2a;font-size:.7rem">—</span>' ?></td>
          <td style="text-align:center">
            <span style="color:<?= (int)$c['order_count'] > 0 ? '#60a5fa' : '#2a2a2a' ?>;font-weight:700;font-size:.85rem"><?= (int)$c['order_count'] ?></span>
          </td>
          <td style="color:<?= (float)$c['total_spent'] >= 10000000 ? '#fbbf24' : ((float)$c['total_spent'] > 0 ? '#4ade80' : '#2a2a2a') ?>;font-weight:700;font-size:.79rem">
            <?= (float)$c['total_spent'] > 0 ? fmtVND($c['total_spent']) : '—' ?>
          </td>
          <td style="color:#444;font-size:.75rem"><?= $lastOrd ?></td>
          <td style="color:#383838;font-size:.75rem"><?= $regDate ?></td>
          <td style="text-align:center">
            <?php if ($isLocked): ?>
            <span style="background:rgba(239,68,68,.1);color:#f87171;padding:2px 9px;border-radius:99px;font-size:.67rem;font-weight:700"><i class="fas fa-lock" style="font-size:.58rem;margin-right:2px"></i>Bị khóa</span>
            <?php else: ?>
            <span style="background:rgba(34,197,94,.08);color:#4ade80;padding:2px 9px;border-radius:99px;font-size:.67rem;font-weight:700"><i class="fas fa-check" style="font-size:.58rem;margin-right:2px"></i>Hoạt động</span>
            <?php endif; ?>
          </td>
          <td style="text-align:center">
            <div style="display:flex;gap:.3rem;justify-content:center">
              <button class="act-btn" onclick="openDetail(<?= (int)$c['id'] ?>)" title="Xem chi tiết">
                <i class="fas fa-eye"></i>
              </button>
              <a href="<?= htmlspecialchars($toggleUrl) ?>"
                 class="act-btn <?= $isLocked ? 'act-success' : 'act-danger' ?>"
                 title="<?= $isLocked ? 'Mở khóa' : 'Khóa' ?>"
                 onclick="return confirm('<?= $isLocked ? 'Mở khóa khách hàng này?' : 'Khóa khách hàng này?' ?>')">
                <i class="fas fa-<?= $isLocked ? 'lock-open' : 'lock' ?>"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div style="padding:.8rem 1.1rem;display:flex;align-items:center;justify-content:space-between;gap:.7rem;flex-wrap:wrap;border-top:1px solid #1e1e1e">
    <div style="color:#444;font-size:.77rem">
      <?php
      $from = $totalCustomers > 0 ? ($page - 1) * $perPage + 1 : 0;
      $to   = min($page * $perPage, $totalCustomers);
      ?>
      Hiển thị <span style="color:#aaa;font-weight:600"><?= $from ?>–<?= $to ?></span>
      trong <span style="color:#aaa;font-weight:600"><?= number_format($totalCustomers) ?></span> khách hàng
    </div>
    <div style="display:flex;align-items:center;gap:.45rem">
      <span style="color:#333;font-size:.72rem">Hiển thị:</span>
      <select class="sort-sel" onchange="changePerPage(this.value)" style="padding:.28rem .55rem;font-size:.73rem">
        <?php foreach ([10, 20, 50] as $pp): ?>
        <option value="<?= $pp ?>" <?= $pp == $perPage ? 'selected' : '' ?>><?= $pp ?>/trang</option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if ($totalPagesAdmin > 1): ?>
    <div style="display:flex;gap:.3rem;align-items:center;flex-wrap:wrap">
      <?php if ($page > 1): ?>
      <a href="<?= pgLink($page - 1, $segment, $search, $sort, $perPage) ?>" class="pg-btn"><i class="fas fa-chevron-left" style="font-size:.6rem"></i></a>
      <?php endif; ?>
      <?php
      $pgStart = max(1, $page - 2);
      $pgEnd   = min($totalPagesAdmin, $page + 2);
      if ($pgStart > 1): ?>
        <a href="<?= pgLink(1, $segment, $search, $sort, $perPage) ?>" class="pg-btn">1</a>
        <?php if ($pgStart > 2): ?><span style="color:#2a2a2a;font-size:.75rem;padding:0 .2rem">…</span><?php endif; ?>
      <?php endif; ?>
      <?php for ($pg = $pgStart; $pg <= $pgEnd; $pg++): ?>
      <a href="<?= pgLink($pg, $segment, $search, $sort, $perPage) ?>"
         class="pg-btn <?= $pg === $page ? 'active-pg' : '' ?>"><?= $pg ?></a>
      <?php endfor; ?>
      <?php if ($pgEnd < $totalPagesAdmin): ?>
        <?php if ($pgEnd < $totalPagesAdmin - 1): ?><span style="color:#2a2a2a;font-size:.75rem;padding:0 .2rem">…</span><?php endif; ?>
        <a href="<?= pgLink($totalPagesAdmin, $segment, $search, $sort, $perPage) ?>" class="pg-btn"><?= $totalPagesAdmin ?></a>
      <?php endif; ?>
      <?php if ($page < $totalPagesAdmin): ?>
      <a href="<?= pgLink($page + 1, $segment, $search, $sort, $perPage) ?>" class="pg-btn"><i class="fas fa-chevron-right" style="font-size:.6rem"></i></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /card -->

<!-- ── BULK ACTIONS BAR ──────────────────────────────────────── -->
<div class="bulk-bar" id="bulk-bar">
  <i class="fas fa-check-square" style="color:#e30000;font-size:.9rem"></i>
  <span style="color:#777;font-size:.82rem">Đã chọn <strong id="bulk-count" style="color:#fff">0</strong> khách hàng</span>
  <div style="flex:1"></div>
  <button onclick="doBulkAction('lock')" style="background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.25);padding:.38rem .85rem;border-radius:7px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s;display:inline-flex;align-items:center;gap:.35rem">
    <i class="fas fa-lock"></i> Khóa hàng loạt
  </button>
  <button onclick="doBulkAction('unlock')" style="background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.25);padding:.38rem .85rem;border-radius:7px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s;display:inline-flex;align-items:center;gap:.35rem">
    <i class="fas fa-lock-open"></i> Mở khóa hàng loạt
  </button>
  <button onclick="clearBulk()" class="btn-g" style="font-size:.78rem;display:inline-flex;align-items:center;gap:.3rem">
    <i class="fas fa-times"></i> Bỏ chọn
  </button>
</div>

<!-- ── CUSTOMER DETAIL MODAL ─────────────────────────────────── -->
<div class="modal-overlay" id="detail-modal" onclick="modalOutsideClick(event)">
  <div class="modal-box">

    <!-- Header -->
    <div class="modal-hdr">
      <div id="modal-avatar" class="cust-avatar" style="width:46px;height:46px;font-size:1.05rem;flex-shrink:0"></div>
      <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap">
          <span id="modal-name"       style="font-size:.98rem;font-weight:800;color:#fff"></span>
          <span id="modal-seg-badge"  style="display:inline-flex"></span>
          <span id="modal-act-badge"  style="display:inline-flex"></span>
        </div>
        <div style="color:#444;font-size:.73rem;margin-top:.15rem;display:flex;gap:.5rem;flex-wrap:wrap">
          <span id="modal-email"></span>
          <span style="color:#1e1e1e">|</span>
          <span id="modal-phone"></span>
        </div>
      </div>
      <button onclick="closeDetail()" style="background:transparent;border:1px solid #1e1e1e;color:#555;cursor:pointer;font-size:.9rem;width:30px;height:30px;border-radius:7px;transition:all .15s;display:flex;align-items:center;justify-content:center;flex-shrink:0"
              onmouseover="this.style.background='#1e1e1e';this.style.color='#ccc'"
              onmouseout="this.style.background='transparent';this.style.color='#555'">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Modal Tabs -->
    <div style="display:flex;border-bottom:1px solid #1e1e1e;padding:0 1.25rem;flex-shrink:0;overflow-x:auto">
      <div class="modal-tab active" onclick="switchTab('overview')" id="mtab-overview">Tổng quan</div>
      <div class="modal-tab"        onclick="switchTab('orders')"   id="mtab-orders">Lịch sử đơn</div>
      <div class="modal-tab"        onclick="switchTab('notes')"    id="mtab-notes">Ghi chú admin</div>
    </div>

    <!-- Modal Body -->
    <div class="modal-bdy" id="modal-body">

      <!-- Loader -->
      <div id="modal-loading" style="text-align:center;padding:2.5rem 1rem;color:#444">
        <i class="fas fa-circle-notch fa-spin" style="font-size:1.3rem;color:#e30000;display:block;margin-bottom:.6rem"></i>
        Đang tải dữ liệu...
      </div>

      <!-- Tab: Tổng quan -->
      <div class="modal-tab-content" id="mtc-overview">
        <div style="display:flex;gap:.55rem;flex-wrap:wrap;margin-bottom:.85rem">
          <div class="dstat">
            <div class="dstat-val" id="ds-orders">0</div>
            <div class="dstat-lbl">Số đơn hàng</div>
          </div>
          <div class="dstat">
            <div class="dstat-val" id="ds-spent" style="color:#4ade80">0đ</div>
            <div class="dstat-lbl">Tổng chi tiêu</div>
          </div>
          <div class="dstat">
            <div class="dstat-val" id="ds-lastord" style="font-size:.82rem">—</div>
            <div class="dstat-lbl">Lần mua cuối</div>
          </div>
          <div class="dstat">
            <div class="dstat-val" id="ds-reg" style="font-size:.82rem">—</div>
            <div class="dstat-lbl">Ngày đăng ký</div>
          </div>
        </div>
        <div style="background:#0f0f0f;border:1px solid #1e1e1e;border-radius:9px;padding:.85rem 1rem">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem .9rem">
            <div>
              <div style="color:#333;font-size:.67rem;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.2rem">Thành phố</div>
              <div id="ds-city" style="color:#bbb;font-size:.81rem">—</div>
            </div>
            <div>
              <div style="color:#333;font-size:.67rem;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.2rem">Địa chỉ</div>
              <div id="ds-addr" style="color:#bbb;font-size:.81rem">—</div>
            </div>
            <div>
              <div style="color:#333;font-size:.67rem;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.2rem">Đăng nhập cuối</div>
              <div id="ds-lastlogin" style="color:#bbb;font-size:.81rem">—</div>
            </div>
            <div>
              <div style="color:#333;font-size:.67rem;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.2rem">Trạng thái TK</div>
              <div id="ds-actstatus" style="font-size:.81rem">—</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab: Lịch sử đơn -->
      <div class="modal-tab-content" id="mtc-orders">
        <div id="ord-table-wrap">
          <table class="adm-table" style="font-size:.77rem">
            <thead>
              <tr>
                <th>Mã đơn</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th style="text-align:center">SP</th>
                <th>Ngày đặt</th>
              </tr>
            </thead>
            <tbody id="ord-tbody"></tbody>
          </table>
        </div>
        <div id="ord-empty" style="display:none;text-align:center;padding:2rem;color:#333;font-size:.82rem">
          <i class="fas fa-shopping-bag" style="font-size:1.2rem;display:block;margin-bottom:.4rem;opacity:.25"></i>
          Chưa có đơn hàng nào.
        </div>
      </div>

      <!-- Tab: Ghi chú admin -->
      <div class="modal-tab-content" id="mtc-notes">
        <div style="margin-bottom:.9rem">
          <div style="color:#444;font-size:.68rem;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.4rem">Thêm ghi chú mới</div>
          <textarea id="note-ta" class="form-inp" rows="3"
                    placeholder="Nhập ghi chú về khách hàng này..."
                    style="resize:vertical;min-height:68px;font-size:.8rem"></textarea>
          <button onclick="saveNote()" class="btn-r" style="margin-top:.45rem;font-size:.77rem;padding:.38rem .85rem;display:inline-flex;align-items:center;gap:.3rem">
            <i class="fas fa-save"></i> Lưu ghi chú
          </button>
        </div>
        <div id="notes-list">
          <div id="notes-empty" style="color:#333;font-size:.8rem;text-align:center;padding:.85rem">Chưa có ghi chú nào.</div>
        </div>
      </div>

    </div><!-- /modal-body -->

    <!-- Footer -->
    <div class="modal-ftr">
      <a id="modal-reset-pw" href="#" class="btn-g" style="font-size:.77rem;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem">
        <i class="fas fa-key"></i> Reset mật khẩu
      </a>
      <div style="flex:1"></div>
      <button id="modal-toggle-btn" onclick="modalToggle()" class="btn-g" style="font-size:.77rem;display:inline-flex;align-items:center;gap:.35rem">
        <i class="fas fa-lock" id="modal-toggle-ico"></i>
        <span id="modal-toggle-lbl">Khóa</span>
      </button>
      <button onclick="closeDetail()" class="btn-g" style="font-size:.77rem">Đóng</button>
    </div>

  </div><!-- /modal-box -->
</div><!-- /modal-overlay -->

<script>
(function() {
  'use strict';
  var APP = '<?= APP_URL ?>';
  var _detailId   = null;
  var _detailData = null;

  /* ── LIVE SEARCH ───────────────────────────────────────── */
  function initLiveSearch() {
    var inp = document.getElementById('search-inp');
    if (!inp) return;
    var t = null;
    inp.addEventListener('input', function() {
      clearTimeout(t);
      t = setTimeout(function() {
        document.getElementById('filter-form').submit();
      }, 400);
    });
  }

  /* ── SORT / PER PAGE ───────────────────────────────────── */
  window.changeSortFilter = function(val) {
    document.getElementById('sort-hidden').value = val;
    document.getElementById('filter-form').submit();
  };
  window.changePerPage = function(val) {
    document.getElementById('per-hidden').value = val;
    document.getElementById('filter-form').submit();
  };

  /* ── EXPORT ────────────────────────────────────────────── */
  window.doExport = function() {
    var url = APP + '/admin/customers/export'
            + '?segment=<?= urlencode($segment) ?>'
            + '&s=<?= urlencode($search) ?>'
            + '&sort=<?= urlencode($sort) ?>';
    window.location.href = url;
  };

  /* ── CHECKBOXES ────────────────────────────────────────── */
  function initCheckboxes() {
    var all  = document.getElementById('chk-all');
    var rows = document.querySelectorAll('.row-chk');
    if (!all) return;
    all.addEventListener('change', function() {
      for (var i = 0; i < rows.length; i++) rows[i].checked = all.checked;
      updateBulkBar();
    });
    for (var i = 0; i < rows.length; i++) {
      rows[i].addEventListener('change', function() {
        var chk = document.querySelectorAll('.row-chk:checked').length;
        all.indeterminate = chk > 0 && chk < rows.length;
        all.checked = chk === rows.length && rows.length > 0;
        updateBulkBar();
      });
    }
  }

  function updateBulkBar() {
    var chk = document.querySelectorAll('.row-chk:checked').length;
    document.getElementById('bulk-count').textContent = chk;
    var bar = document.getElementById('bulk-bar');
    if (chk > 0) { bar.classList.add('visible'); }
    else         { bar.classList.remove('visible'); }
  }

  window.clearBulk = function() {
    var all  = document.getElementById('chk-all');
    var rows = document.querySelectorAll('.row-chk');
    for (var i = 0; i < rows.length; i++) rows[i].checked = false;
    if (all) { all.checked = false; all.indeterminate = false; }
    updateBulkBar();
  };

  /* ── BULK ACTIONS ──────────────────────────────────────── */
  window.doBulkAction = function(act) {
    var checked = document.querySelectorAll('.row-chk:checked');
    if (!checked.length) return;
    var lbl = act === 'lock' ? 'khóa' : 'mở khóa';
    if (!confirm('Bạn có chắc muốn ' + lbl + ' ' + checked.length + ' khách hàng?')) return;
    var ids = [];
    for (var i = 0; i < checked.length; i++) ids.push(checked[i].value);
    var fd = new FormData();
    fd.append('act', act === 'lock' ? 'lock' : 'unlock');
    for (var j = 0; j < ids.length; j++) fd.append('ids[]', ids[j]);
    fetch(APP + '/admin/customers/bulk-toggle', { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (d.ok) {
          showToast('Đã ' + lbl + ' ' + d.count + ' khách hàng', 'ok');
          setTimeout(function() { location.reload(); }, 900);
        } else {
          showToast(d.message || 'Lỗi xảy ra', 'err');
        }
      })
      .catch(function() { showToast('Lỗi kết nối', 'err'); });
  };

  /* ── HELPERS ───────────────────────────────────────────── */
  var _avColors = ['#e30000','#3b82f6','#8b5cf6','#f59e0b','#10b981','#ec4899'];
  function avColor(id) { return _avColors[parseInt(id) % 6]; }

  function segBadgeHtml(c) {
    var spent  = parseFloat(c.total_spent)  || 0;
    var orders = parseInt(c.order_count)    || 0;
    var now    = Date.now();
    var cre    = new Date(c.created_at).getTime();
    var last   = c.last_order_at ? new Date(c.last_order_at).getTime() : null;
    var d30 = 30 * 86400000;
    var d90 = 90 * 86400000;
    if (spent >= 10000000)
      return '<span style="background:rgba(120,53,15,.35);color:#fbbf24;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700"><i class="fas fa-crown" style="font-size:.58rem;margin-right:2px"></i>VIP</span>';
    if (orders >= 5)
      return '<span style="background:rgba(59,130,246,.18);color:#60a5fa;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700"><i class="fas fa-heart" style="font-size:.58rem;margin-right:2px"></i>Thân thiết</span>';
    if ((now - cre) < d30)
      return '<span style="background:rgba(139,92,246,.18);color:#c084fc;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700"><i class="fas fa-star" style="font-size:.58rem;margin-right:2px"></i>Mới</span>';
    if (orders === 0 || (last !== null && (now - last) > d90))
      return '<span style="background:rgba(80,80,80,.18);color:#555;padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700">Không HĐ</span>';
    return '';
  }

  function fmtMoney(n) {
    return parseInt(n || 0).toLocaleString('vi-VN') + 'đ';
  }

  function fmtDate(str) {
    if (!str) return '—';
    try {
      var d = new Date(str.replace(' ', 'T'));
      if (isNaN(d)) return str;
      var pad = function(x) { return String(x).padStart(2, '0'); };
      return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear()
           + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    } catch(e) { return str; }
  }

  function escHtml(s) {
    if (!s) return '';
    return String(s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  var _ordStatusMap = {
    pending:    { label:'Chờ XN',    cls:'bdg-pending' },
    confirmed:  { label:'Đã XN',     cls:'bdg-confirmed' },
    processing: { label:'Xử lý',     cls:'bdg-processing' },
    shipping:   { label:'Đang giao', cls:'bdg-shipping' },
    delivered:  { label:'Đã giao',   cls:'bdg-delivered' },
    cancelled:  { label:'Đã hủy',    cls:'bdg-cancelled' }
  };

  /* ── OPEN DETAIL ───────────────────────────────────────── */
  window.openDetail = function(id) {
    _detailId = id;
    var ov = document.getElementById('detail-modal');
    ov.classList.add('open');
    document.body.style.overflow = 'hidden';

    // Reset state
    document.getElementById('modal-loading').style.display = 'block';
    document.querySelectorAll('.modal-tab-content').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.modal-tab').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('mtab-overview').classList.add('active');
    document.getElementById('modal-name').textContent = '';
    document.getElementById('modal-email').textContent = '';
    document.getElementById('modal-phone').textContent = '';
    document.getElementById('modal-seg-badge').innerHTML = '';
    document.getElementById('modal-act-badge').innerHTML = '';

    fetch(APP + '/admin/customers/detail?id=' + id)
      .then(function(r) { return r.json(); })
      .then(function(d) {
        document.getElementById('modal-loading').style.display = 'none';
        if (!d.ok) { showToast('Không tải được dữ liệu', 'err'); return; }
        _detailData = d;
        populateDetail(d);
      })
      .catch(function() {
        document.getElementById('modal-loading').style.display = 'none';
        showToast('Lỗi kết nối', 'err');
      });
  };

  function populateDetail(d) {
    var c = d.customer;
    var isActive = parseInt(c.is_active) === 1;

    // Avatar + header
    var av = document.getElementById('modal-avatar');
    av.style.background = avColor(c.id);
    av.textContent = c.fullname ? c.fullname.charAt(0).toUpperCase() : '?';
    document.getElementById('modal-name').textContent  = c.fullname;
    document.getElementById('modal-email').textContent = c.email  || '—';
    document.getElementById('modal-phone').textContent = c.phone  || '—';
    document.getElementById('modal-seg-badge').innerHTML = segBadgeHtml(c);
    document.getElementById('modal-act-badge').innerHTML = isActive
      ? '<span style="background:rgba(34,197,94,.08);color:#4ade80;padding:2px 8px;border-radius:99px;font-size:.67rem;font-weight:700">Hoạt động</span>'
      : '<span style="background:rgba(239,68,68,.1);color:#f87171;padding:2px 8px;border-radius:99px;font-size:.67rem;font-weight:700">Bị khóa</span>';

    // Reset password link
    document.getElementById('modal-reset-pw').href = APP + '/admin/customers/reset-pw?id=' + c.id;

    // Toggle button
    var tBtn = document.getElementById('modal-toggle-btn');
    var tIco = document.getElementById('modal-toggle-ico');
    var tLbl = document.getElementById('modal-toggle-lbl');
    if (isActive) {
      tBtn.style.borderColor = 'rgba(239,68,68,.3)';
      tBtn.style.color = '#f87171';
      tIco.className = 'fas fa-lock';
      tLbl.textContent = 'Khóa tài khoản';
    } else {
      tBtn.style.borderColor = 'rgba(34,197,94,.3)';
      tBtn.style.color = '#4ade80';
      tIco.className = 'fas fa-lock-open';
      tLbl.textContent = 'Mở khóa';
    }

    // Overview stats
    document.getElementById('ds-orders').textContent   = c.order_count || 0;
    document.getElementById('ds-spent').textContent    = fmtMoney(c.total_spent);
    document.getElementById('ds-lastord').textContent  = c.last_order_at ? fmtDate(c.last_order_at) : '—';
    document.getElementById('ds-reg').textContent      = fmtDate(c.created_at);
    document.getElementById('ds-city').textContent     = c.city    || '—';
    document.getElementById('ds-addr').textContent     = c.address || '—';
    document.getElementById('ds-lastlogin').textContent = c.last_login ? fmtDate(c.last_login) : 'Chưa đăng nhập';
    document.getElementById('ds-actstatus').innerHTML  = isActive
      ? '<span style="color:#4ade80">Hoạt động</span>'
      : '<span style="color:#f87171">Bị khóa</span>';

    // Orders
    var tbody = document.getElementById('ord-tbody');
    tbody.innerHTML = '';
    if (d.orders && d.orders.length) {
      document.getElementById('ord-empty').style.display = 'none';
      document.getElementById('ord-table-wrap').style.display = 'block';
      d.orders.forEach(function(o) {
        var sm = _ordStatusMap[o.status] || { label: o.status, cls: 'bdg-pending' };
        var tr = document.createElement('tr');
        tr.innerHTML =
          '<td><a href="' + APP + '/admin/orders/detail?id=' + o.id + '" style="color:#e30000;font-weight:700;text-decoration:none" target="_blank">' + escHtml(o.order_code) + '</a></td>' +
          '<td style="color:#4ade80;font-weight:700">' + fmtMoney(o.total) + '</td>' +
          '<td><span class="badge ' + sm.cls + '">' + sm.label + '</span></td>' +
          '<td style="text-align:center;color:#555">' + (parseInt(o.items) || 0) + '</td>' +
          '<td style="color:#444;font-size:.73rem">' + fmtDate(o.created_at) + '</td>';
        tbody.appendChild(tr);
      });
    } else {
      document.getElementById('ord-empty').style.display = 'block';
      document.getElementById('ord-table-wrap').style.display = 'none';
    }

    // Notes
    renderNotes(d.notes || []);

    // Show overview tab content
    document.getElementById('mtc-overview').classList.add('active');
  }

  /* ── RENDER NOTES ──────────────────────────────────────── */
  function renderNotes(notes) {
    var list  = document.getElementById('notes-list');
    var empty = document.getElementById('notes-empty');
    var old   = list.querySelectorAll('.note-item');
    for (var i = 0; i < old.length; i++) old[i].parentNode.removeChild(old[i]);
    if (notes && notes.length) {
      empty.style.display = 'none';
      notes.forEach(function(n) {
        var div = document.createElement('div');
        div.className = 'note-item';
        var nd = n.new_data;
        var txt = '', by = '';
        try {
          var p = typeof nd === 'string' ? JSON.parse(nd) : (nd || {});
          txt = p.note || (typeof nd === 'string' ? nd : '');
          by  = p.by   || n.user_name || '';
        } catch(e) {
          txt = typeof nd === 'string' ? nd : '';
          by  = n.user_name || '';
        }
        div.innerHTML =
          '<div class="note-text">' + escHtml(txt) + '</div>' +
          '<div class="note-meta"><i class="fas fa-user" style="font-size:.58rem;margin-right:.22rem"></i>' + escHtml(by) +
          '<span style="margin:0 .3rem;color:#1e1e1e">·</span>' + fmtDate(n.created_at) + '</div>';
        list.appendChild(div);
      });
    } else {
      empty.style.display = 'block';
    }
  }

  /* ── TAB SWITCH ────────────────────────────────────────── */
  window.switchTab = function(tab) {
    document.querySelectorAll('.modal-tab').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.modal-tab-content').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('mtab-' + tab).classList.add('active');
    document.getElementById('mtc-'  + tab).classList.add('active');
  };

  /* ── CLOSE MODAL ───────────────────────────────────────── */
  window.closeDetail = function() {
    document.getElementById('detail-modal').classList.remove('open');
    document.body.style.overflow = '';
    _detailId = null;
    _detailData = null;
  };
  window.modalOutsideClick = function(e) {
    if (e.target === document.getElementById('detail-modal')) closeDetail();
  };

  /* ── MODAL TOGGLE ──────────────────────────────────────── */
  window.modalToggle = function() {
    if (!_detailId) return;
    if (!confirm('Thay đổi trạng thái tài khoản khách hàng này?')) return;
    window.location.href = APP + '/admin/customers/toggle?id=' + _detailId;
  };

  /* ── SAVE NOTE ─────────────────────────────────────────── */
  window.saveNote = function() {
    if (!_detailId) return;
    var ta   = document.getElementById('note-ta');
    var note = ta.value.trim();
    if (!note) { showToast('Vui lòng nhập nội dung ghi chú', 'err'); return; }
    var fd = new FormData();
    fd.append('customer_id', _detailId);
    fd.append('note', note);
    fetch(APP + '/admin/customers/note', { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (d.ok) {
          showToast('Đã lưu ghi chú', 'ok');
          ta.value = '';
          var newN = {
            new_data  : JSON.stringify({ note: note, by: '<?= addslashes(htmlspecialchars($_SESSION['user_name'] ?? 'Admin')) ?>' }),
            user_name : '<?= addslashes(htmlspecialchars($_SESSION['user_name'] ?? 'Admin')) ?>',
            created_at: new Date().toISOString().replace('T',' ').substring(0,19)
          };
          if (_detailData) {
            if (!_detailData.notes) _detailData.notes = [];
            _detailData.notes.unshift(newN);
            renderNotes(_detailData.notes);
          }
        } else {
          showToast('Lỗi khi lưu ghi chú', 'err');
        }
      })
      .catch(function() { showToast('Lỗi kết nối', 'err'); });
  };

  /* ── TOAST ─────────────────────────────────────────────── */
  window.showToast = function(msg, type) {
    var c = document.getElementById('toast-c');
    if (!c) return;
    var el = document.createElement('div');
    el.className = 'toast ' + (type || '');
    el.innerHTML = '<i class="fas ' + (type === 'ok' ? 'fa-check-circle' : 'fa-exclamation-circle') + '" style="margin-right:.4rem"></i>' + escHtml(msg);
    c.appendChild(el);
    setTimeout(function() {
      el.style.transition = 'opacity .3s';
      el.style.opacity = '0';
      setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 350);
    }, 3000);
  };

  /* ── INIT ──────────────────────────────────────────────── */
  document.addEventListener('DOMContentLoaded', function() {
    initLiveSearch();
    initCheckboxes();
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeDetail();
    });
    var loader = document.getElementById('pg-loader');
    if (loader) setTimeout(function() { loader.classList.add('hidden'); }, 350);
  });

})();
</script>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
