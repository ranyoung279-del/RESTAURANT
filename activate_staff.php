<?php
require_once __DIR__ . '/includes/db.php';

use App\Db;

$token = trim((string)($_GET['token'] ?? ''));
$message = '';
$error = '';
$showForm = false;

if ($token !== '') {
    $db = Db::conn();
    // Tìm trong admin_invite_tokens trước
    $stmt = $db->prepare("SELECT t.id, t.user_id, u.email, u.username, t.expires_at, t.used_at
                            FROM admin_invite_tokens t
                            JOIN users u ON u.id = t.user_id
                           WHERE t.token=? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        $invite = $res? $res->fetch_assoc() : null;
        if ($invite) {
            if (!empty($invite['used_at'])) {
                $error = 'Token đã được sử dụng.';
            } elseif (strtotime($invite['expires_at']) < time()) {
                $error = 'Token đã hết hạn.';
            } else {
                $showForm = true; // hợp lệ
                $context = 'invite';
                $userId = (int)$invite['user_id'];
            }
        } else {
            // Thử trong user_activation_tokens (quên mật khẩu admin)
            $stmt2 = $db->prepare("SELECT t.id, t.user_id, u.email, u.username, t.expires_at, t.used_at
                                      FROM user_activation_tokens t
                                      JOIN users u ON u.id = t.user_id
                                     WHERE t.token=? LIMIT 1");
            if ($stmt2) {
                $stmt2->bind_param('s', $token);
                $stmt2->execute();
                $r2 = $stmt2->get_result();
                $reset = $r2? $r2->fetch_assoc() : null;
                if ($reset) {
                    if (!empty($reset['used_at'])) {
                        $error = 'Token đã được sử dụng.';
                    } elseif (strtotime($reset['expires_at']) < time()) {
                        $error = 'Token đã hết hạn.';
                    } else {
                        $showForm = true;
                        $context = 'reset';
                        $userId = (int)$reset['user_id'];
                    }
                } else {
                    $error = 'Token không hợp lệ.';
                }
            }
        }
    }
} else {
    $error = 'Thiếu token.';
}

if ($showForm && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = (string)($_POST['password'] ?? '');
    if (strlen($pass) < 6) {
        $error = 'Mật khẩu phải >= 6 ký tự.';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $uStmt = Db::conn()->prepare("UPDATE users SET password_hash=? WHERE id=?");
        if ($uStmt) {
            $uStmt->bind_param('si', $hash, $userId);
            if ($uStmt->execute()) {
                if ($context === 'invite') {
                    $mStmt = Db::conn()->prepare("UPDATE admin_invite_tokens SET used_at=NOW() WHERE token=?");
                } else {
                    $mStmt = Db::conn()->prepare("UPDATE user_activation_tokens SET used_at=NOW() WHERE token=?");
                }
                if ($mStmt) { $mStmt->bind_param('s', $token); $mStmt->execute(); }
                $message = 'Đặt mật khẩu thành công. Bạn có thể đăng nhập.';
                $showForm = false;
            } else {
                $error = 'Không thể cập nhật mật khẩu.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đặt mật khẩu tài khoản</title>
  <link rel="stylesheet" href="assets/css/log.css">
</head>
<body class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-box">
      <h2>Thiết lập / đặt lại mật khẩu</h2>
      <?php if ($message): ?><p class="auth-success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="auth-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <?php if ($showForm): ?>
        <form method="post">
          <label>Mật khẩu mới (>=6 ký tự)</label>
          <input type="password" name="password" required minlength="6">
          <button type="submit">Cập nhật mật khẩu</button>
        </form>
      <?php endif; ?>
      <p><a href="admin/login.php">← Về trang đăng nhập</a></p>
    </div>
  </div>
</body>
</html>
