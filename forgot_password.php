<?php
use App\Controllers\AuthController;

require_once __DIR__ . '/includes/db.php';



$controller = new AuthController();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Tạo token reset cho khách hàng qua email (dùng bảng password_resets)
  $email = trim((string)($_POST['email'] ?? ''));
  if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $token = \App\Models\PasswordReset::create($email, 60);
    if ($token) {
      $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
      $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
      $path   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
      $link   = $scheme . '://' . $host . $path . '/reset_password.php?email=' . urlencode($email) . '&token=' . urlencode($token);
      $subject = 'Yêu cầu đặt lại mật khẩu';
      $body  = "Nếu bạn vừa yêu cầu đặt lại mật khẩu, hãy truy cập: \n$link\n\n";
      $body .= "Liên kết có hiệu lực 60 phút.";
      \App\Email::send($email, $subject, $body);
    }
  }
  $message = 'Nếu email tồn tại, hệ thống đã gửi hướng dẫn đặt lại mật khẩu.';
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
