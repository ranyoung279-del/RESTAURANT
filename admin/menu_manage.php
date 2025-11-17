<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Auth;
use App\Csrf;
use App\Components\AdminLayout;
use App\Controllers\MenuController;

Auth::guardAdmin();
$ctrl = new MenuController();
$result = $ctrl->handleManage();

$message = $result['message'];
$error = $result['error'];
$edit_data = $result['edit_data'];

// Lấy danh sách và CSRF token
$list = $ctrl->listAll();
$csrf = Csrf::token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý thực đơn</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>

<?php include 'header.php'; ?>

<div class="admin-dashboard">
  <?php AdminLayout::sidebar(); ?>

  <main class="admin-overview">
    <div>
      <h2>Quản lý thực đơn</h2>
    </div>
    <div class="admin-page-header">
    <a href="dashboard.php" class="admin-back-btn">
        <span class="admin-back-btn-icon">←</span>
        Quay lại
    </a>
</div>

    <?php if ($message): ?>
      <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Form thêm/sửa -->
    <div class="form-container">
      <h3><?= $edit_data ? 'Chỉnh sửa món ăn' : 'Thêm món mới' ?></h3>
      
      <?php if (isset($_GET['id']) && !$edit_data): ?>
        <div class="alert error">Không tìm thấy món ăn với ID: <?= (int)$_GET['id'] ?></div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)($edit_data['id'] ?? '')) ?>">

        <label>Tên món:</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>">

        <label>Mô tả:</label>
        <textarea name="description" rows="3"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>

        <label>Giá:</label>
        <input type="number" name="price" step="0.01" min="0" required value="<?= htmlspecialchars((string)($edit_data['price'] ?? '')) ?>">

        <label>Loại món:</label>
        <?php $cat = $edit_data['category'] ?? ''; ?>
        <select name="category" required>
          <option value="">-- Chọn loại --</option>
          <option value="appetizer" <?= $cat==='appetizer' ? 'selected' : '' ?>>Món khai vị</option>
          <option value="main"      <?= $cat==='main' ? 'selected' : '' ?>>Món chính</option>
          <option value="dessert"   <?= $cat==='dessert' ? 'selected' : '' ?>>Tráng miệng</option>
          <option value="drink"     <?= $cat==='drink' ? 'selected' : '' ?>>Đồ uống</option>
        </select>

        <label>Ảnh món ăn:</label>
        <input type="file" name="image_file" accept="image/*">
        <?php if (!empty($edit_data['image_url'])): ?>
          <p><img src="<?= htmlspecialchars($edit_data['image_url']) ?>" width="110"></p>
        <?php endif; ?>

        <label><input type="checkbox" name="is_special" <?= !empty($edit_data['is_special']) ? 'checked' : '' ?>> Món đặc biệt</label><br>
        <label><input type="checkbox" name="is_available" <?= !isset($edit_data['is_available']) || (int)$edit_data['is_available'] ? 'checked' : '' ?>> Hiển thị</label>

        <div>
          <button type="submit" class="btn primary"><?= $edit_data ? 'Cập nhật' : 'Thêm mới' ?></button>
          <?php if ($edit_data): ?>
            <a href="menu_manage.php" class="btn">Huỷ sửa</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Danh sách -->
    <div class="form-container">
      <h3>Danh sách món ăn</h3>
      <table>
        <thead>
          <tr>
            <th>Tên món</th>
            <th>Giá</th>
            <th>Loại</th>
            <th>Trạng thái</th>
            <th>Hình ảnh</th>
            <th>Đặc biệt</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
        <?php
          if ($list && $list->num_rows):
            while ($item = $list->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format((float)$item['price'], 0, ',', '.') ?> đ</td>
            <td><?= htmlspecialchars($item['category']) ?></td>
            <td><?= (int)$item['is_available'] ? 'Hiển thị' : 'Ẩn' ?></td>
            <td>
              <?php if (!empty($item['image_url'])): ?>
                <img src="<?= htmlspecialchars($item['image_url']) ?>" width="60">
              <?php else: ?> — <?php endif; ?>
            </td>
            <td><?= (int)$item['is_special'] ? '✅' : '—' ?></td>
            <td class="action-links">
              <a href="?id=<?= (int)$item['id'] ?>" class="edit">Sửa</a>
              <a href="?delete=<?= (int)$item['id'] ?>" class="delete"
                 onclick="return confirm('Bạn có chắc chắn muốn xoá món này?')">Xoá</a>
            </td>
          </tr>
        <?php
            endwhile;
          else:
            echo '<tr><td colspan="7">Chưa có dữ liệu.</td></tr>';
          endif;
        ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php AdminLayout::footer(); ?>
</body>
</html>