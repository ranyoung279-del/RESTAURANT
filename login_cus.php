<?php
// login_cus.php (MVC/OOP chuẩn)
require_once __DIR__ . '/includes/db.php';

use App\Controllers\AuthController;
use App\Auth;

Auth::start();
$auth = new AuthController();

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($auth->loginCustomer($email, $pass)) {
        header('Location: account.php');
        exit;
    } else {
        // redirect lại để show flash message (đã set $_SESSION['error'])
        header('Location: login_cus.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập khách hàng</title>
  <link rel="stylesheet" href="assets/css/log.css?v=<?= time(); ?>">
</head>
<body class="auth-page">
<div class="auth-wrapper">
  <div class="auth-box">
    <h2>Đăng nhập</h2>

    <?php if (!empty($_SESSION['error'])): ?>
      <p class="auth-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="post" action="login_cus.php" autocomplete="on">
      <input type="hidden" name="action" value="login">

      <label>Email:</label>
      <input type="email" name="email" placeholder="Nhập email của bạn" required
             value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">

      <label>Mật khẩu:</label>
      <div style="position: relative;">
        <input type="password" id="password" name="password" required
               style="width: 100%; box-sizing: border-box; padding-right: 40px;">
        <button type="button" id="togglePassword"
                style="position:absolute; right:10px; top:50%; transform:translateY(-50%);
                       background:none; border:none; cursor:pointer; font-size:18px; color:#333;">
          👁
        </button>
      </div>

      <button type="submit" style="margin-top:12px;padding:10px 16px;">Đăng nhập</button>
    </form>

    <p>
      <a href="forgot_password.php" style="text-decoration:none; color:#007bff;">
        Quên mật khẩu?
      </a>
    </p>

    <p>Chưa có tài khoản? <a href="registration.php">Đăng ký ngay</a></p>
  </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function(){
  const p = document.getElementById('password');
  if (p.type === 'password') { p.type = 'text'; this.textContent='🙈'; }
  else { p.type='password'; this.textContent='👁'; }
});
</script>
</body>
</html>
