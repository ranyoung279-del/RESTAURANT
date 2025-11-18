<?php
namespace App\Controllers;

use App\Auth;
use App\Models\Customer;
use App\Db;

class CustomerController extends BaseController
{
    public function register(array $data): bool
    {
        Auth::start();

        $name  = trim($data['full_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $pass  = (string)($data['password'] ?? '');
        // validate cơ bản
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
            $_SESSION['error'] = 'Thông tin không hợp lệ hoặc mật khẩu quá ngắn (>= 6 ký tự).';
            return false;
        }
        // check trùng email
        if (Customer::byEmail($email)) {
            $_SESSION['error'] = 'Email đã được sử dụng.';
            return false;
        }
        // tạo tài khoản (chưa xác thực email)
        if (Customer::create($name, $email, $phone, $pass)) {
            // Lấy ID vừa tạo
            $cid = Db::conn()->insert_id;
            // Tạo token xác thực email
            $token = \App\Models\EmailVerification::create($cid);
            if ($token) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $path   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
                $link   = $scheme . '://' . $host . $path . '/verify_email.php?token=' . urlencode($token);
                $subject = 'Xác thực tài khoản khách hàng';
                $body  = "Xin chào $name,\n\n";
                $body .= "Vui lòng nhấn vào liên kết sau để xác thực tài khoản: \n$link\n\n";
                $body .= "Liên kết hết hạn sau 24 giờ.";
                \App\Email::send($email, $subject, $body);
            }
            $_SESSION['success'] = 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.';
            return true;
        }
        // fallback lỗi hệ thống
        $_SESSION['error'] = 'Đã xảy ra lỗi hệ thống, vui lòng thử lại.';
        return false;
    }
}
