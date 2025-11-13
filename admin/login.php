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
    <link rel="stylesheet" href="../assets/css/log.css?v=<?= time(); ?>">
</head>
<body>
  <div class="login-container">
    <h2>Đăng nhập quản trị</h2>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
      <input type="text" name="username" placeholder="Tên đăng nhập hoặc email">
      <div style="position:relative">
        <input type="password" id="password" name="password" placeholder="Mật khẩu">
        <button type="button" id="togglePassword" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer">👁</button>
      </div>
      <button type="submit" style="width:100%;padding:10px;margin-top:12px;background:#970000;color:#fff;border:none;cursor:pointer">Đăng nhập</button>
    </form>
<script>
document.getElementById('togglePassword').addEventListener('click', function(){
  const p = document.getElementById('password');
  if (p.type === 'password') { p.type = 'text'; this.textContent='🙈'; }
  else { p.type='password'; this.textContent='👁'; }
});
</script>
</body>
</html>
