<?php
// admin/users_manage.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Auth;
use App\Csrf;
use App\Components\AdminLayout;
use App\Controllers\UserController;

Auth::guardAdmin();
$ctl = new UserController();
$result = $ctl->handleManage();

$message = $result['message'];
$error = $result['error'];
$edit_data = $result['edit_data'];

// Lấy danh sách và CSRF token
$list = $ctl->listAll();
$csrf = Csrf::token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý người dùng</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>

<?php AdminLayout::header(); ?>

<div class="admin-dashboard">
  <?php AdminLayout::sidebar(); ?>

  <main class="admin-overview">

    <!-- Thanh tiêu đề + nút quay lại thống nhất như settings.php -->
    <div class="page-topbar">
      <h2>Quản lý người dùng</h2>
      <a href="dashboard.php" class="btn ghost">← Quay lại</a>
    </div>

    <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert error"><?= htmlspecialchars($error)   ?></div><?php endif; ?>

    <div class="form-container">
      <form method="POST" autocomplete="off">
        <h3><?= $edit_data ? 'Sửa người dùng' : 'Thêm người dùng' ?></h3>

        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <input type="hidden" name="do"   value="save">
        <input type="hidden" name="id"   value="<?= htmlspecialchars((string)($edit_data['id'] ?? '')) ?>">

        <label>Tên đăng nhập:</label>
        <input type="text" name="username" required
               value="<?= htmlspecialchars($edit_data['username'] ?? '') ?>">

        <label>Mật khẩu: <?= $edit_data ? '(để trống nếu không đổi)' : '' ?></label>
        <div style="position:relative;">
          <input type="password" id="password" name="password" <?= $edit_data ? '' : 'required' ?>>
          <button type="button" id="togglePassword"
                  class="btn ghost sm"
                  style="position:absolute;right:6px;top:50%;transform:translateY(-50%);">👁</button>
        </div>

        <label>Vai trò:</label>
        <?php $roleVal = $edit_data['role'] ?? 'staff'; ?>
        <select name="role">
          <option value="staff" <?= $roleVal==='staff' ? 'selected' : '' ?>>Staff</option>
          <option value="admin" <?= $roleVal==='admin' ? 'selected' : '' ?>>Admin</option>
        </select>

        <div class="form-actions">
          <button type="submit" class="btn primary"><?= $edit_data ? 'Cập nhật' : 'Thêm' ?></button>
          <?php if ($edit_data): ?>
            <a href="users_manage.php" class="btn">Huỷ sửa</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="form-container">
      <h3>Danh sách người dùng</h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Tên đăng nhập</th>
              <th>Vai trò</th>
              <th>Ngày tạo</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($list && $list->num_rows): ?>
            <?php while ($u = $list->fetch_assoc()): ?>
              <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
                <td class="action-links" style="white-space:nowrap; display:flex; gap:10px; align-items:center;">
                  <a href="?id=<?= (int)$u['id'] ?>" class="edit">Sửa</a>

                  <?php if ((int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                    <form method="post" onsubmit="return confirm('Xoá người dùng này?');" style="display:inline;">
                      <input type="hidden" name="csrf" value="<?= $csrf ?>">
                      <input type="hidden" name="do"   value="delete">
                      <input type="hidden" name="id"   value="<?= (int)$u['id'] ?>">
                      <!-- Dùng nút kiểu link cho đồng nhất -->
                      <button type="submit" class="delete-link">Xoá</button>
                    </form>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">Chưa có dữ liệu.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php AdminLayout::footer(); ?>

<script>
const togglePassword = document.getElementById('togglePassword');
const passwordField  = document.getElementById('password');
if (togglePassword && passwordField) {
  togglePassword.addEventListener('click', function(){
    const t = passwordField.type === 'password' ? 'text' : 'password';
    passwordField.type = t;
    this.textContent = (t === 'password') ? '👁' : '🙈';
  });
}
</script>
</body>
</html>
