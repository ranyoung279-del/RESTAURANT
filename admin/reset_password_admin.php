<?php
// admin/reset_password_admin.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Models\PasswordReset;
use App\Db;

$email = trim((string)($_GET['email'] ?? ''));
$token = trim((string)($_GET['token'] ?? ''));
$message = '';
$error = '';
$showForm = false;

if ($email === '' || $token === '') {
    $error = 'Thiếu email hoặc token.';
} else {
    // 1. Xác minh token và email qua bảng password_resets
    if (PasswordReset::verify($email, $token)) {
        $showForm = true;
    } else {
        $error = 'Liên kết không hợp lệ hoặc đã hết hạn.';
    }
}

if ($showForm && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pwd = (string)($_POST['password'] ?? '');
    if (strlen($pwd) < 6) {
        $error = 'Mật khẩu phải tối thiểu 6 ký tự.';
    } else {
        // 2. Cập nhật mật khẩu cho ADMIN (bảng users)
        $stmt = Db::conn()->prepare('UPDATE users SET password_hash=? WHERE email=? LIMIT 1');
        if ($stmt) {
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt->bind_param('ss', $hash, $email);
            if ($stmt->execute() && $stmt->affected_rows === 1) {
                // 3. Tiêu thụ token sau khi cập nhật thành công
                PasswordReset::consume($email, $token);
                $message = 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập.';
                $showForm = false;
            } else {
                $error = 'Không thể đặt lại mật khẩu. Có thể email không tồn tại trong bảng users.';
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
  <title>Đặt lại mật khẩu Quản trị</title>
  <link rel="stylesheet" href="../assets/css/log.css"> </head>
<body class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-box">
      <h2>Đặt lại mật khẩu Quản trị</h2>
      <?php if ($message): ?><p class="auth-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="auth-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <?php if ($showForm): ?>
        <form method="post">
          <label>Mật khẩu mới (>=6 ký tự)</label>
          <input type="password" name="password" required minlength="6">
          <button type="submit">Cập nhật mật khẩu</button>
        </form>
      <?php endif; ?>
      <p><a href="login.php">← Về trang đăng nhập Quản trị</a></p>
    </div>
  </div>
</body>
</html>