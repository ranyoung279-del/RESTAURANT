<?php
// customer_auth.php (ĐĂNG KÝ – MVC/OOP)
require_once __DIR__ . '/includes/db.php';

use App\Auth;
use App\Controllers\CustomerController;

Auth::start();

if (($_POST['action'] ?? null) === 'register') {
  $ctrl = new CustomerController();
  if ($ctrl->register($_POST)) {
    header('Location: login_cus.php'); // có $_SESSION['success']
    exit;
  } else {
    header('Location: registration.php'); // có $_SESSION['error']
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng ký tài khoản</title>
  <link rel="stylesheet" href="assets/css/log.css">
</head>
<body class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-box">
      <h2>Đăng ký tài khoản</h2>

      <?php if (!empty($_SESSION['error'])): ?>
        <p class="auth-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
      <?php endif; ?>

      <?php if (!empty($_SESSION['success'])): ?>
        <p class="auth-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
      <?php endif; ?>

      <form method="post" action="registration.php" autocomplete="off">
        <input type="hidden" name="action" value="register">

        <label>Họ tên:</label>
        <input name="full_name" placeholder="Nhập họ tên..." required>

        <label>Email:</label>
        <input type="email" name="email" placeholder="Nhập email..." required>

        <label>Số điện thoại:</label>
        <input name="phone" placeholder="Nhập số điện thoại..." required>

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

        <button type="submit" style="margin-top:15px;">Đăng ký</button>
      </form>

      <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField  = document.getElementById('password');
        if (togglePassword && passwordField) {
          togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁' : '🙈';
          });
        }
      </script>

      <p>Đã có tài khoản? <a href="login_cus.php">Đăng nhập ngay</a></p>
    </div>
  </div>
</body>
</html>
