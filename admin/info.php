<?php
// admin/info.php
include_once __DIR__ . '/../includes/db.php';

use App\Auth;

// Chặn truy cập nếu không phải admin/staff
Auth::guardAdmin();  // sẽ redirect về login.php nếu không đủ quyền
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Quản lý thông tin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>" />
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<div class="admin-dashboard">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <main class="admin-overview">
    <h2>Quản lý thông tin nhà hàng</h2>
    <p>Chọn phần bạn muốn chỉnh sửa:</p>

    <div class="stats">
      <div class="stat-box" onclick="location.href='home_manage.php'">
        <h3>Trang chủ</h3>
        <p>Quản lý nội dung giới thiệu, banner, mô tả ngắn...</p>
      </div>

      <div class="stat-box" onclick="location.href='settings.php'">
        <h3>Liên hệ</h3>
        <p>Cập nhật địa chỉ, giờ mở cửa, số điện thoại, mạng xã hội.</p>
      </div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
