<?php
// admin/settings.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Auth;
use App\Csrf;
use App\Components\AdminLayout;
use App\Controllers\SettingController;

Auth::guardAdmin();

$ctrl = new SettingController();
$result = $ctrl->handleManage();

$message   = $result['message'];
$error     = $result['error'];
$edit_data = $result['edit_data'];

// Lấy danh sách settings
$list = $ctrl->listAll();
$csrf = Csrf::token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cài đặt nhà hàng</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>

<?php include 'header.php'; ?>

<div class="admin-dashboard">

  <?php AdminLayout::sidebar(); ?>

  <main class="admin-overview">

    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h2>Cài đặt thông tin nhà hàng</h2>
      <div class="admin-page-header">
    <a href="dashboard.php" class="admin-back-btn">
        <span class="admin-back-btn-icon">←</span>
        Quay lại
    </a>
</div>
    </div>

    <?php if ($message): ?>
      <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FORM THÊM/SỬA -->
    <div class="form-container">
      <h3><?= $edit_data ? "Cập nhật ({$edit_data['id']})" : "Thêm mới" ?></h3>

      <?php if (isset($_GET['id']) && !$edit_data): ?>
        <div class="alert error">Không tìm thấy bản ghi ID: <?= (int)$_GET['id'] ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)($edit_data['id'] ?? '')) ?>">
        <input type="hidden" name="do" value="save">

        <label>Tên nhà hàng:</label>
        <input type="text" name="restaurant_name" required
               value="<?= htmlspecialchars($edit_data['restaurant_name'] ?? '') ?>">

        <label>Địa chỉ:</label>
        <input type="text" name="address" required
               value="<?= htmlspecialchars($edit_data['address'] ?? '') ?>">

        <label>Số điện thoại:</label>
        <input type="text" name="phone"
               value="<?= htmlspecialchars($edit_data['phone'] ?? '') ?>">

        <label>Email:</label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>">

        <label>Giờ mở cửa:</label>
        <input type="text" name="open_hours"
               value="<?= htmlspecialchars($edit_data['open_hours'] ?? '') ?>">

        <?php
          $social = json_decode($edit_data['social_links'] ?? '{}', true) ?? [];
        ?>

        <label>Facebook:</label>
        <input type="text" name="facebook" value="<?= htmlspecialchars($social['facebook'] ?? '') ?>">

        <label>Instagram:</label>
        <input type="text" name="instagram" value="<?= htmlspecialchars($social['instagram'] ?? '') ?>">

        <label>TikTok:</label>
        <input type="text" name="tiktok" value="<?= htmlspecialchars($social['tiktok'] ?? '') ?>">

        <div>
          <button type="submit" class="btn primary"><?= $edit_data ? 'Cập nhật' : 'Thêm mới' ?></button>

          <?php if ($edit_data): ?>
            <a href="settings.php" class="btn">Huỷ</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- DANH SÁCH -->
    <div class="form-container">
      <h3>📄 Danh sách cấu hình</h3>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Tên nhà hàng</th>
            <th>Địa chỉ</th>
            <th>Điện thoại</th>
            <th>Email</th>
            <th>Giờ mở cửa</th>
            <th>Mạng xã hội</th>
            <th>Hành động</th>
          </tr>
        </thead>

        <tbody>
        <?php if ($list && $list->num_rows): ?>
          <?php while ($row = $list->fetch_assoc()): ?>
            <?php $s = json_decode($row['social_links'], true) ?: []; ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['restaurant_name']) ?></td>
              <td><?= htmlspecialchars($row['address']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['open_hours']) ?></td>
              <td>
                FB: <?= htmlspecialchars($s['facebook'] ?? '') ?><br>
                IG: <?= htmlspecialchars($s['instagram'] ?? '') ?><br>
                TikTok: <?= htmlspecialchars($s['tiktok'] ?? '') ?>
              </td>
              <td class="action-links">
                <a href="?id=<?= $row['id'] ?>" class="edit">Sửa</a>

                <form method="POST" style="display:inline;">
                  <input type="hidden" name="do" value="delete">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="csrf" value="<?= $csrf ?>">
                  <button type="submit" class="delete"
                          onclick="return confirm('Bạn chắc chắn xoá cấu hình này?')">Xoá</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>

        <?php else: ?>
          <tr><td colspan="8">Chưa có dữ liệu.</td></tr>
        <?php endif; ?>
        </tbody>

      </table>
    </div>

  </main>
</div>

<?php AdminLayout::footer(); ?>

</body>
</html>
