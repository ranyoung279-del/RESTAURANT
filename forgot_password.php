<?php
use App\Controllers\AuthController;

require_once __DIR__ . '/includes/db.php';



$controller = new AuthController();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$message, $error] = $controller->handleCustomerForgotPassword();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quên mật khẩu</title>
  <link rel="stylesheet" href="assets/css/log.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
  <div class="auth-box">
    <h2>Quên mật khẩu</h2>

    <?php if ($message): ?>
      <p class="auth-info"><?= htmlspecialchars($message) ?></p>
    <?php elseif ($error): ?>
      <p class="auth-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
      <label>Email của bạn:</label>
      <input type="email" name="email" placeholder="Nhập email" required>
      <button type="submit">Gửi yêu cầu</button>
    </form>

    <p><a href="login_cus.php">← Quay lại đăng nhập</a></p>
  </div>
</div>

</body>
</html>
