<?php
require_once __DIR__ . '/includes/db.php';

use App\Db;
use App\Models\EmailVerification;

$token = trim((string)($_GET['token'] ?? ''));
$message = '';
$error = '';

if ($token === '') {
    $error = 'Thiếu token.';
} else {
    $customerId = EmailVerification::verify($token);
    if (!$customerId) {
        $error = 'Token không hợp lệ hoặc đã hết hạn.';
    } else {
        // cập nhật xác thực email
        $stmt = Db::conn()->prepare('UPDATE customers SET email_verified_at=NOW() WHERE id=?');
        if ($stmt) {
            $stmt->bind_param('i', $customerId);
            if ($stmt->execute()) {
                EmailVerification::consume($token);
                $message = 'Xác thực email thành công! Bạn có thể đăng nhập.';
            } else {
                $error = 'Không thể xác thực tài khoản.';
            }
        } else {
            $error = 'Lỗi hệ thống.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Xác thực email</title>
  <link rel="stylesheet" href="assets/css/log.css">
</head>
<body class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-box">
      <h2>Xác thực email</h2>
      <?php if ($message): ?><p class="auth-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="auth-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <p><a href="login_cus.php">← Về trang đăng nhập</a></p>
    </div>
  </div>
</body>
</html>
