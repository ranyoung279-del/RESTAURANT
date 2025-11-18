<?php
// admin/users_manage.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Auth;
use App\Csrf;
use App\Components\AdminLayout;
use App\Controllers\UserController;

Auth::guardAdmin();
$ctl    = new UserController();
$result = $ctl->handleManage();

$message   = $result['message']   ?? '';
$error     = $result['error']     ?? '';
$tab       = $result['tab']       ?? ($_GET['tab'] ?? '');
$customers = $result['customers'] ?? [];
$staffs    = $result['staffs']    ?? [];
$csrf      = $result['csrf']      ?? Csrf::token();

if (!in_array($tab, ['customers', 'staff'], true)) {
    $tab = '';
}

// dữ liệu danh sách
$customers = $result['customers'] ?? [];
$staffs    = $result['staffs']    ?? [];

// token CSRF (ưu tiên token do controller trả về)
$csrf = $result['csrf'] ?? Csrf::token();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý người dùng</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
    <style>
      /* Card chọn nhóm tài khoản */
      .user-type-switch {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
        margin-top: 12px;
      }
      .user-type-switch .stat-box {
        cursor: pointer;
        border-radius: 16px;
        padding: 18px 20px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        transition: transform .15s ease, box-shadow .15s ease,
                    border-color .15s ease, background-color .15s ease;
      }
      .user-type-switch .stat-box h3 {
        margin: 0 0 4px;
        font-size: 18px;
      }
      .user-type-switch .stat-box p {
        margin: 0;
        color: #4b5563;
        font-size: 14px;
      }
      .user-type-switch .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15,23,42,.08);
        border-color: #6366f1;
        background: #eef2ff;
      }
      .user-type-switch .stat-box.active {
        border-color: #4f46e5;
        background: #eef2ff;
        box-shadow: 0 8px 20px rgba(79,70,229,.18);
      }

      /* Thẻ card nội dung */
      .card {
        background: #fff;
        border-radius: 18px;
        padding: 20px 22px;
        box-shadow: 0 12px 30px rgba(15,23,42,.06);
        margin-top: 24px;
      }
      .card h3 {
        margin-top: 0;
        margin-bottom: 12px;
        font-size: 20px;
      }

      /* Cột hành động trong bảng */
      .actions-cell {
        white-space: nowrap;
      }
      .actions-cell .btn-inline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 6px;
        padding-inline: 10px;
      }

      /* Form ngang tạo nhân viên */
      .form-horizontal {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px 24px;
        align-items: end;
      }
      .form-horizontal .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
      }
      .form-horizontal .form-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-start;
      }

      @media (max-width: 768px) {
        .user-type-switch {
          grid-template-columns: 1fr;
        }
        .card {
          padding: 16px 14px;
        }
      }
    </style>

</head>
<body>
<?php include 'header.php'; ?>

<div class="admin-dashboard">
  <?php App\Components\AdminLayout::sidebar(); ?>

  <main class="admin-overview">
    <div class="page-topbar">
      <h2>👤 Quản lý người dùng</h2>
      <a href="dashboard.php" class="btn ghost">← Về Dashboard</a>
    </div>

    <?php if ($message): ?>
      <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

        <?php if ($tab === ''): ?>
          <p>Chọn nhóm tài khoản bạn muốn quản lý:</p>

          <div class="stats user-type-switch">
            <div class="stat-box"
                onclick="location.href='users_manage.php?tab=customers'">
              <h3>Tài khoản khách hàng</h3>
              <p>Xem, cập nhật email / SĐT và xóa tài khoản khách hàng.</p>
            </div>

            <div class="stat-box"
                onclick="location.href='users_manage.php?tab=staff'">
              <h3>Tài khoản nhân viên</h3>
              <p>Tạo tài khoản admin/staff, gửi link đặt mật khẩu và xóa nhân viên.</p>
            </div>
          </div>
        <?php endif; ?>


    <?php if ($tab === 'customers'): ?>
      <!-- KHÁCH HÀNG -->
      <section class="card">
        <h3>Danh sách tài khoản khách hàng</h3>
        <?php if (!$customers): ?>
          <p>Chưa có khách hàng nào.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $c): ?>
              <tr>
                <form method="POST">
                  <!-- CSRF & tab -->
                  <input type="hidden" name="csrf" value="<?= $csrf ?>">
                  <input type="hidden" name="tab"  value="customers">
                  <input type="hidden" name="id"   value="<?= (int)$c['id'] ?>">

                  <td><?= (int)$c['id'] ?></td>
                  <td><?= htmlspecialchars($c['full_name']) ?></td>
                  <td>
                    <input type="email"
                           name="email"
                           value="<?= htmlspecialchars($c['email']) ?>"
                           required>
                  </td>
                  <td>
                    <input
                      type="tel"
                      name="phone"
                      value="<?= htmlspecialchars((string)$c['phone']) ?>"
                      inputmode="numeric"
                      pattern="^[0-9]{10,11}$"
                      minlength="10"
                      maxlength="11"
                      placeholder="10–11 chữ số"
                      title="Số điện thoại phải có 10–11 chữ số"
                    >
                  </td>
                  <td><?= htmlspecialchars($c['created_at']) ?></td>
                  <td class="actions-cell">
                    <button type="submit"
                            name="action"
                            value="update_customer"
                            class="btn small btn-inline">
                      💾 Lưu
                    </button>

                    <button type="submit"
                            name="action"
                            value="delete_customer"
                            class="btn small danger btn-inline"
                            onclick="return confirm('Xóa tài khoản khách hàng này?');">
                      🗑 Xóa
                    </button>
                  </td>
                </form>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>

     <?php elseif ($tab === 'staff'): ?>
      <!-- NHÂN VIÊN -->
      <section class="card">
        <h3>Tạo tài khoản nhân viên</h3>
        <form method="POST" class="form-horizontal" autocomplete="off">
          <input type="hidden" name="csrf"  value="<?= $csrf ?>">
          <input type="hidden" name="tab"   value="staff">
          <input type="hidden" name="action" value="create_staff">
          <div class="form-group">
            <label for="staff_email">Email</label>
            <input type="email" id="staff_email" name="email" required>
          </div>
          <div class="form-group">
            <label for="staff_username">Tên người dùng</label>
            <input type="text" id="staff_username" name="username" required>
          </div>
          <div class="form-group">
            <label for="staff_role">Phân quyền</label>
            <select id="staff_role" name="role">
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
            </select>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary">Tạo tài khoản nhân viên</button>
          </div>
        </form>
        <p style="margin-top:8px;font-size:0.9rem;">
          Sau khi tạo, hệ thống sẽ gửi email chứa link để nhân viên tự đặt mật khẩu đăng nhập.
        </p>
      </section>

      <section class="card">
        <h3>Danh sách tài khoản nhân viên</h3>
        <?php if (!$staffs): ?>
          <p>Chưa có tài khoản nhân viên nào.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tên người dùng</th>
                <th>Email</th>
                <th>Quyền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($staffs as $s): ?>
              <tr>
                <td><?= (int)$s['id'] ?></td>
                <td><?= htmlspecialchars($s['username']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['role']) ?></td>
                <td>
                  <?php if (!empty($s['password_hash'])): ?>
<<<<<<< HEAD
                    ✅ Đã đặt mật khẩu
                  <?php else: ?>
                    ⏳ Chưa đặt mật khẩu
=======
                    ------------------
                  <?php else: ?>
                    ------------------
>>>>>>> 8d71618b4a15096e4cfb9fce32de9e4852252747
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($s['created_at']) ?></td>
                <td class="actions-cell">
                  <form method="POST"
                        onsubmit="return confirm('Xóa tài khoản nhân viên này?');"
                        style="margin:0;">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="tab"  value="staff">
                    <input type="hidden" name="id"   value="<?= (int)$s['id'] ?>">

                    <button type="submit"
                            name="action"
                            value="delete_staff"
                            class="btn small danger btn-inline">
                      🗑 Xóa
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>

      <?php else: ?>
        <p style="margin-top:16px;color:#666;">
          Vui lòng chọn <strong>Tài khoản khách hàng</strong> hoặc
          <strong>Tài khoản nhân viên</strong> ở phía trên để xem chi tiết.
        </p>
      <?php endif; ?>

  </main>
</div>

<?php App\Components\AdminLayout::footer(); ?>
</body>
</html>
