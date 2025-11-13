<?php
// admin/dashboard.php
include_once __DIR__ . '/../includes/db.php';
\App\Auth::guardAdmin();
use App\Auth;

Auth::start();

// ✅ Chỉ cho admin đã đăng nhập truy cập
if (empty($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Quản trị';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Trang quản trị - WENZHU</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>
  <?php include __DIR__ . '/header.php'; ?>

  <div class="admin-dashboard">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="admin-overviewdash">
      <h2>👋 Xin chào, <?= htmlspecialchars($username) ?></h2>
      <p>Chào mừng bạn đến với trang quản trị hệ thống WENZHU.</p>
    </main>
  </div>

  <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
