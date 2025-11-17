<?php
// admin/forgot_password.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Controllers\AuthController;

$message = null;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctl = new AuthController();
    [$message, $error] = $ctl->handleAdminForgotPassword();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu quản trị</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?= time(); ?>">
    <style>
        body{
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            margin:0;
            background:#f5f5f5;
            font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
        }
        .login-container{
            width:360px;
            padding:24px;
            border-radius:8px;
            background:#fff;
            box-shadow:0 6px 18px rgba(0,0,0,.08);
        }
        .login-container h2{
            text-align:center;
            margin:0 0 16px;
        }
        .notice{
            padding:8px 10px;
            border-radius:6px;
            margin-bottom:10px;
            font-size:14px;
        }
        .notice.success{
            background:#e8f5e9;
            color:#256029;
        }
        .notice.error{
            background:#ffebee;
            color:#b71c1c;
        }
        .back-link{
            margin-top:12px;
            text-align:left;
            font-size:14px;
        }
        .back-link a{color:#1a73e8;text-decoration:none;}
        .back-link a:hover{text-decoration:underline;}
        .btn-primary{
            width:100%;
            padding:10px 0;
            border:none;
            border-radius:6px;
            background:#1a73e8;
            color:#fff;
            font-weight:600;
            cursor:pointer;
        }
        .btn-primary:hover{
            background:#1557b0;
        }
        input[type="text"],input[type="email"]{
            width:100%;
            padding:10px;
            margin:8px 0;
            border-radius:6px;
            border:1px solid #ddd;
            box-sizing:border-box;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Quên mật khẩu</h2>

    <?php if ($message): ?>
        <div class="notice success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="notice error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="text"
               name="identifier"
               placeholder="Nhập email hoặc tên đăng nhập"
               required>
        <button type="submit" class="btn-primary">
            Gửi link đặt lại mật khẩu
        </button>
    </form>

    <div class="back-link">
        <a href="login.php">← Quay lại đăng nhập</a>
    </div>
</div>
</body>
</html>
