<?php require_once __DIR__.'/layout_top.php'; ?>
<?php
$roleMap   = array(1=>'Admin', 2=>'Manager', 3=>'Staff');
$roleColor = array(1=>'var(--red)', 2=>'#a78bfa', 3=>'#60a5fa');
$roleBg    = array(1=>'rgba(227,0,0,.12)', 2=>'rgba(167,139,250,.12)', 3=>'rgba(96,165,250,.12)');
$search    = $search ?? '';
?>

<!-- ── Add / Edit Modal ── -->
<div id="sm-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9990;align-items:center;justify-content:center;backdrop-filter:blur(3px)" onclick="if(event.target===this)smClose()">
  <div style="background:#141414;border:1px solid #2a2a2a;border-radius:14px;width:min(480px,94vw);animation:fadeIn .22s ease;box-shadow:0 24px 64px rgba(0,0,0,.5)">
    <div style="padding:.85rem 1.1rem;border-bottom:1px solid #1e1e1e;display:flex;align-items:center;justify-content:space-between">
      <span id="sm-title" style="color:#fff;font-weight:700;font-size:.9rem"></span>
      <button onclick="smClose()" style="background:none;border:none;color:#555;cursor:pointer;font-size:.9rem"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="sm-form" method="POST" style="padding:1.1rem">
      <input type="hidden" name="id" id="sm-id" value="0">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem">
        <div style="grid-column:1/-1">
          <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Họ và tên <span style="color:var(--red)">*</span></label>
          <input type="text" name="fullname" id="sm-name" class="form-inp" required placeholder="Nguyễn Văn A">
        </div>
        <div id="sm-email-wrap">
          <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Email <span style="color:var(--red)">*</span></label>
          <input type="email" name="email" id="sm-email" class="form-inp" placeholder="email@example.com">
        </div>
        <div>
          <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Số điện thoại</label>
          <input type="tel" name="phone" id="sm-phone" class="form-inp" placeholder="0909 xxx xxx">
        </div>
        <div>
          <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Vai trò <span style="color:var(--red)">*</span></label>
          <select name="role" id="sm-role" class="form-inp">
            <option value="3">Staff</option>
            <option value="2">Manager</option>
            <option value="1">Admin</option>
          </select>
        </div>
        <div id="sm-pw-wrap">
          <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Mật khẩu <span style="color:var(--red)">*</span></label>
          <input type="password" name="password" id="sm-pw" class="form-inp" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password">
        </div>
      </div>
      <div style="display:flex;gap:.5rem;margin-top:.9rem;justify-content:flex-end">
        <button type="button" onclick="smClose()" class="btn-g">Hủy</button>
        <button type="submit" class="btn-r"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Reset Password Modal ── -->
<div id="rp-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9991;align-items:center;justify-content:center;backdrop-filter:blur(3px)" onclick="if(event.target===this)rpClose()">
  <div style="background:#141414;border:1px solid #2a2a2a;border-radius:14px;width:min(360px,94vw);animation:fadeIn .22s ease;box-shadow:0 24px 64px rgba(0,0,0,.5)">
    <div style="padding:.85rem 1.1rem;border-bottom:1px solid #1e1e1e;display:flex;align-items:center;justify-content:space-between">
      <span style="color:#fff;font-weight:700;font-size:.9rem"><i class="fa-solid fa-key" style="color:#fbbf24;margin-right:.35rem"></i>Đặt lại mật khẩu</span>
      <button onclick="rpClose()" style="background:none;border:none;color:#555;cursor:pointer;font-size:.9rem"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/staff/reset" style="padding:1.1rem">
      <input type="hidden" name="id" id="rp-id" value="">
      <div style="font-size:.78rem;color:#888;margin-bottom:.75rem">Đặt mật khẩu mới cho: <span id="rp-name" style="color:#ddd;font-weight:600"></span></div>
      <label style="font-size:.72rem;color:#666;display:block;margin-bottom:.25rem">Mật khẩu mới <span style="color:var(--red)">*</span></label>
      <input type="password" name="password" class="form-inp" placeholder="Tối thiểu 6 ký tự" autocomplete="new-password" required style="margin-bottom:.75rem">
      <div style="display:flex;gap:.5rem;justify-content:flex-end">
        <button type="button" onclick="rpClose()" class="btn-g">Hủy</button>
        <button type="submit" style="background:#d97706;color:#fff;border:none;padding:.45rem 1rem;border-radius:7px;font-weight:600;font-size:.82rem;cursor:pointer;font-family:inherit"><i class="fa-solid fa-key"></i> Đặt lại</button>
      </div>
    </form>
  </div>
</div>

<div class="card" style="padding:1.1rem">

  <!-- Toolbar -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;margin-bottom:.9rem">
    <form method="GET" style="display:flex;gap:.4rem">
      <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên, email, SĐT..." class="form-inp" style="max-width:260px">
      <button type="submit" class="btn-r" style="padding:.45rem .8rem"><i class="fas fa-search"></i></button>
      <?php if($search): ?>
      <a href="<?= APP_URL ?>/admin/staff" class="btn-g" style="padding:.45rem .65rem" title="Xóa tìm kiếm"><i class="fas fa-xmark"></i></a>
      <?php endif; ?>
    </form>
    <button onclick="smOpen()" class="btn-r" style="display:flex;align-items:center;gap:.35rem">
      <i class="fas fa-user-plus"></i> Thêm nhân sự
    </button>
  </div>

  <!-- Stats row -->
  <?php
  $db = Database::getInstance();
  $sc = $db->fetch("SELECT
    COUNT(*) AS total,
    SUM(role=1) AS admins,
    SUM(role=2) AS managers,
    SUM(role=3) AS staff_cnt,
    SUM(is_active=1) AS active_cnt
    FROM users WHERE role IN (1,2,3)");
  ?>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.55rem;margin-bottom:.9rem">
    <?php foreach(array(
      array($sc['total'],'Tổng nhân sự','#aaa','fa-users'),
      array($sc['admins'],'Admin','var(--red)','fa-user-shield'),
      array($sc['managers'],'Manager','#a78bfa','fa-user-tie'),
      array($sc['staff_cnt'],'Staff','#60a5fa','fa-user'),
    ) as $s): ?>
    <div style="background:#111;border:1px solid #1e1e1e;border-radius:9px;padding:.65rem .8rem;display:flex;align-items:center;gap:.55rem">
      <i class="fa-solid <?= $s[3] ?>" style="color:<?= $s[2] ?>;font-size:.9rem;width:18px;text-align:center"></i>
      <div><div style="font-size:1.1rem;font-weight:800;color:#fff;line-height:1"><?= (int)$s[0] ?></div><div style="font-size:.65rem;color:#555;margin-top:2px"><?= $s[1] ?></div></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Table -->
  <div style="overflow-x:auto">
    <table class="adm-table">
      <thead><tr>
        <th style="width:40px"></th>
        <th>Nhân viên</th>
        <th>Liên hệ</th>
        <th>Vai trò</th>
        <th>Trạng thái</th>
        <th>Đăng nhập cuối</th>
        <th>Ngày tạo</th>
        <th style="width:130px">Thao tác</th>
      </tr></thead>
      <tbody>
      <?php foreach($staffList as $u): ?>
      <?php
        $rn  = (int)$u['role'];
        $rc  = $roleColor[$rn]  ?? '#aaa';
        $rbg = $roleBg[$rn]     ?? '#1a1a1a';
        $rl  = $roleMap[$rn]    ?? '?';
        $isSelf = ((int)$u['id'] === (int)$_SESSION['user_id']);
      ?>
      <tr>
        <td>
          <div style="width:34px;height:34px;background:<?= $rbg ?>;border:1.5px solid <?= $rc ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:<?= $rc ?>;font-size:.82rem">
            <?= strtoupper(mb_substr($u['fullname']??'?',0,1,'UTF-8')) ?>
          </div>
        </td>
        <td>
          <div style="font-weight:600;color:#ddd"><?= htmlspecialchars($u['fullname']) ?>
            <?php if($isSelf): ?><span style="font-size:.62rem;background:#1e1e1e;color:#555;padding:1px 5px;border-radius:4px;margin-left:4px">bạn</span><?php endif; ?>
          </div>
          <div style="font-size:.72rem;color:#555"><?= htmlspecialchars($u['email']) ?></div>
        </td>
        <td style="color:#777;font-size:.8rem"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
        <td>
          <span style="background:<?= $rbg ?>;color:<?= $rc ?>;padding:2px 9px;border-radius:5px;font-size:.7rem;font-weight:700">
            <?= $rl ?>
          </span>
        </td>
        <td>
          <?php if($u['is_active']): ?>
          <span class="badge bdg-delivered">Hoạt động</span>
          <?php else: ?>
          <span class="badge bdg-cancelled">Đã khóa</span>
          <?php endif; ?>
        </td>
        <td style="color:#555;font-size:.75rem">
          <?= !empty($u['last_login']) ? date('d/m/Y H:i', strtotime($u['last_login'])) : '—' ?>
        </td>
        <td style="color:#555;font-size:.75rem">
          <?= date('d/m/Y', strtotime($u['created_at'])) ?>
        </td>
        <td>
          <div style="display:flex;gap:.3rem">
            <!-- Edit -->
            <button onclick="smEdit(<?= htmlspecialchars(json_encode(['id'=>$u['id'],'fullname'=>$u['fullname'],'phone'=>$u['phone']??'','role'=>$u['role']]),ENT_QUOTES) ?>)"
                    class="btn-g" style="padding:.28rem .52rem;font-size:.7rem;border-color:rgba(96,165,250,.35);color:#60a5fa" title="Sửa">
              <i class="fas fa-edit"></i>
            </button>
            <!-- Reset PW -->
            <button onclick="rpOpen(<?= $u['id'] ?>,'<?= htmlspecialchars(addslashes($u['fullname'])) ?>')"
                    class="btn-g" style="padding:.28rem .52rem;font-size:.7rem;border-color:rgba(251,191,36,.35);color:#fbbf24" title="Đặt lại mật khẩu">
              <i class="fas fa-key"></i>
            </button>
            <!-- Lock/Unlock -->
            <?php if(!$isSelf): ?>
            <a href="<?= APP_URL ?>/admin/staff/toggle?id=<?= $u['id'] ?>"
               onclick="return confirm('<?= $u['is_active'] ? 'Khóa' : 'Mở khóa' ?> tài khoản <?= htmlspecialchars(addslashes($u['fullname'])) ?>?')"
               class="btn-g" style="padding:.28rem .52rem;font-size:.7rem;<?= $u['is_active'] ? 'border-color:rgba(239,68,68,.35);color:#f87171' : 'border-color:rgba(34,197,94,.35);color:#4ade80' ?>"
               title="<?= $u['is_active'] ? 'Khóa' : 'Mở khóa' ?>">
              <i class="fas <?= $u['is_active'] ? 'fa-lock' : 'fa-lock-open' ?>"></i>
            </a>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; if(empty($staffList)): ?>
      <tr><td colspan="8" style="text-align:center;padding:2.5rem;color:#555">Chưa có nhân sự nào.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($totalPagesAdmin > 1): ?>
  <div style="display:flex;gap:.3rem;flex-wrap:wrap;margin-top:.85rem">
    <?php for($i=1;$i<=$totalPagesAdmin;$i++): $cur=($page==$i); ?>
    <a href="?page=<?= $i ?><?= $search?'&s='.urlencode($search):'' ?>"
       style="padding:3px 9px;border-radius:5px;font-size:.75rem;text-decoration:none;background:<?= $cur?'var(--red)':'#1a1a1a' ?>;color:<?= $cur?'#fff':'#888' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

</div>

<script>
function smOpen(){
  document.getElementById('sm-title').textContent='Thêm nhân sự mới';
  document.getElementById('sm-form').action='<?= APP_URL ?>/admin/staff/create';
  document.getElementById('sm-id').value='0';
  document.getElementById('sm-name').value='';
  document.getElementById('sm-email').value='';
  document.getElementById('sm-phone').value='';
  document.getElementById('sm-role').value='3';
  document.getElementById('sm-pw').value='';
  document.getElementById('sm-email-wrap').style.display='';
  document.getElementById('sm-pw-wrap').style.display='';
  document.getElementById('sm-email').required=true;
  document.getElementById('sm-pw').required=true;
  document.getElementById('sm-modal').style.display='flex';
  document.getElementById('sm-name').focus();
}
function smEdit(u){
  document.getElementById('sm-title').textContent='Sửa: '+u.fullname;
  document.getElementById('sm-form').action='<?= APP_URL ?>/admin/staff/edit';
  document.getElementById('sm-id').value=u.id;
  document.getElementById('sm-name').value=u.fullname;
  document.getElementById('sm-phone').value=u.phone||'';
  document.getElementById('sm-role').value=u.role;
  document.getElementById('sm-email-wrap').style.display='none';
  document.getElementById('sm-pw-wrap').style.display='none';
  document.getElementById('sm-email').required=false;
  document.getElementById('sm-pw').required=false;
  document.getElementById('sm-modal').style.display='flex';
  document.getElementById('sm-name').focus();
}
function smClose(){
  document.getElementById('sm-modal').style.display='none';
}
function rpOpen(id,name){
  document.getElementById('rp-id').value=id;
  document.getElementById('rp-name').textContent=name;
  document.getElementById('rp-modal').style.display='flex';
}
function rpClose(){
  document.getElementById('rp-modal').style.display='none';
}
</script>

<?php require_once __DIR__.'/layout_bottom.php'; ?>
