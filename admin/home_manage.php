<?php
// admin/home_manage.php (dùng HomeController, giữ nguyên form/layout)
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Auth;
use App\Csrf;                       // đã require trong includes/db.php
use App\Components\AdminLayout;
use App\Controllers\HomeController; // ⬅️ dùng controller

Auth::guardAdmin();
$ctrl = new HomeController();
$result = $ctrl->handleManage();

$msg = $result['message'];
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
  <title>Quản lý nội dung trang chủ</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
  <style>.thumb{width:90px;border-radius:6px;border:1px solid #e5e7eb;margin:2px}</style>
</head>
<body>

<?php AdminLayout::header(); ?>

<div class="admin-dashboard">
  <?php AdminLayout::sidebar(); ?>

  <main class="admin-overview">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h2> Nội dung trang chủ</h2>
      <a href="info.php" class="btn" style="background:#1a1f28;color:#fff;">Quay lại</a>
    </div>

    <?php if ($msg):   ?><div class="alert success"><?= htmlspecialchars($msg)   ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="form-container">
      <!--  KHÔNG đổi cấu trúc form -->
      <form method="POST" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        <input type="hidden" name="do"   value="save">
        <input type="hidden" name="id"   value="<?= htmlspecialchars((string)($edit_data['id'] ?? '')) ?>">

        <label>Tiêu đề:</label>
        <input type="text" name="title" required value="<?= htmlspecialchars($edit_data['title'] ?? '') ?>">

        <label>Mô tả:</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>

        <label>Ảnh giới thiệu:</label>
        <input type="file" name="intro_images[]" accept="image/*" multiple>
        <div style="margin-top:6px;">
          <?php
          if (!empty($edit_data['intro_images'])) {
              $imgs = preg_split('/\r\n|\r|\n/', (string)$edit_data['intro_images']);
              foreach ($imgs as $img) {
                  if ($img) echo '<img src="'.htmlspecialchars($img).'" class="thumb" loading="lazy">';
              }
          }
          ?>
        </div>

        <label>Ảnh banner món ăn:</label>
        <input type="file" name="banner_image" accept="image/*">
        <div style="margin-top:6px;">
          <?php if (!empty($edit_data['banner_image'])): ?>
            <img src="<?= htmlspecialchars($edit_data['banner_image']) ?>" class="thumb" loading="lazy">
          <?php endif; ?>
        </div>

        <div style="display:flex;gap:8px;align-items:center;margin-top:12px;">
          <button type="submit" class="btn primary"><?= $edit_data ? 'Cập nhật' : 'Thêm mới' ?></button>
          <?php if ($edit_data): ?><a href="home_manage.php" class="btn">Huỷ sửa</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="form-container">
      <h3>Danh sách nội dung</h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Tiêu đề</th>
              <th>Mô tả</th>
              <th>Ảnh giới thiệu</th>
              <th>Ảnh banner</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($list && $list->num_rows): ?>
            <?php while ($r = $list->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($r['title'] ?? '') ?></td>
                <td><?= nl2br(htmlspecialchars($r['description'] ?? '')) ?></td>
                <td>
                  <?php
                    if (!empty($r['intro_images'])) {
                        $imgs = preg_split('/\r\n|\r|\n/', (string)$r['intro_images']);
                        foreach ($imgs as $img) {
                            if ($img) echo '<img src="'.htmlspecialchars($img).'" class="thumb" loading="lazy">';
                        }
                    } else echo '—';
                  ?>
                </td>
                <td>
                  <?php if (!empty($r['banner_image'])): ?>
                    <img src="<?= htmlspecialchars($r['banner_image']) ?>" class="thumb" loading="lazy">
                  <?php else: ?> — <?php endif; ?>
                </td>
                <td class="action-links">
                  <a href="?id=<?= (int)$r['id'] ?>" class="edit">Sửa</a>

                  <form method="post" style="display:inline" onsubmit="return confirm('Xoá bản ghi này?');">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="do"   value="delete">
                    <input type="hidden" name="id"   value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="delete"
                            style="background:none;border:none;color:#b00020;cursor:pointer;">Xoá</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">Chưa có nội dung nào.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php AdminLayout::footer(); ?>
</body>
</html>
