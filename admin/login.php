<?php
declare(strict_types=1);
include_once __DIR__ . '/../includes/db.php';
use App\Auth;
use App\Controllers\AuthController;
Auth::start();
// Xử lý login qua AuthController
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new AuthController();
    $error = $authController->handleAdminLogin();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Đăng nhập quản trị</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../assets/css/admin2.css?v=<?= time(); ?>">
    <style>
        body{display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f5f5f5;}
        .login-container{width:360px;padding:24px;border-radius:8px;background:#fff;box-shadow:0 6px 18px rgba(0,0,0,.08)}
        .login-container h2{text-align:center;margin:0 0 16px}
        .error{color:#b00020;text-align:center;margin-bottom:12px}

        .forgot-link{margin-top:10px;text-align:right;font-size:14px;}
        .forgot-link a{color:#1a73e8;text-decoration:none;}
        .forgot-link a:hover{text-decoration:underline;}
    </style>
</head>
<body>
  <div class="login-container">
    <h2>Đăng nhập quản trị</h2>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="text" name="username" placeholder="Tên đăng nhập hoặc email" required style="width:100%;padding:10px;margin:8px 0;">
      <div class="password-field" style="margin:8px 0;">
        <input type="password" id="password" name="password" placeholder="Mật khẩu" style="width:100%;">
        <button type="button" id="togglePassword" class="password-toggle">👁</button>
      </div>

      <button type="submit" class="btn primary" style="width:100%;margin-top:8px;">
        Đăng nhập
      </button>
    </form>

        <div class="forgot-link">
          <a href="forgot_password.php">Quên mật khẩu?</a>
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
