<?php
// header.php
// Trang gọi header đã include 'includes/db.php' trước đó rồi.
// Nếu bạn muốn header tự nạp, có thể bật dòng dưới:
// include_once __DIR__ . '/includes/db.php';

use App\Auth;

Auth::start();

$customerId   = $_SESSION['customer_id']   ?? null;
$customerName = $_SESSION['customer_name'] ?? null;

$current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
function is_active($file, $current) {
  return $file === $current ? 'class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>WENZHU - Bake with love</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>" />
</head>
<body>
  <header>
    <div class="topbar">
      <h1><a href="viewer.php" style="text-decoration:none;color:inherit;">WENZHU</a></h1>
      <nav>
        <a href="viewer.php"     <?= is_active('viewer.php', $current) ?>>Trang chủ</a>
        <a href="address.php"    <?= is_active('address.php', $current) ?>>Địa điểm</a>
        <a href="menu.php"       <?= is_active('menu.php', $current) ?>>Menu</a>
        <a href="promotion.php"<?= is_active('promotion.php', $current) ?>>Khuyến mại</a>
        <a href="reservation.php"<?= is_active('reservation.php', $current) ?>>Đặt bàn</a>
        <a href="account.php"    <?= is_active('account.php', $current) ?>>Tài khoản</a>
      </nav>

      <div class="user-box">
        <?php if ($customerId): ?>
          <a class="logout" href="logout.php" title="Đăng xuất">Đăng xuất</a>
        <?php else: ?>
          <a class="login" href="login_cus.php">Đăng nhập</a>
          <a class="register" href="registration.php">Đăng ký</a>
        <?php endif; ?>
      </div>
    </div>
  </header>
