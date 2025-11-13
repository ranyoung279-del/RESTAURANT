<?php
namespace App\Controllers;

use App\Auth;
use App\Models\Customer;

class CustomerController extends BaseController
{
    /**
     * Đăng ký khách hàng mới.
     * - Validate input
     * - Check trùng email
     * - Tạo tài khoản qua Model
     * - Set session message để view hiển thị
     */
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
        // tạo tài khoản
        if (Customer::create($name, $email, $phone, $pass)) {
            $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
            return true;
        }
        // fallback lỗi hệ thống
        $_SESSION['error'] = 'Đã xảy ra lỗi hệ thống, vui lòng thử lại.';
        return false;
    }
}
