<?php
/*
namespace App;

final class Auth {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function guardAdmin(): void {
        self::start();
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, ['admin','staff'], true)) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function guardCustomer(): void {
        self::start();
        if (!isset($_SESSION['customer_id'])) {
            header('Location: /login_cus.php');
            exit;
        }
    }

    // Helper: trả thông tin người dùng đang đăng nhập (admin/staff)
    public static function currentAdmin(): ?array {
        self::start();
        if (!isset($_SESSION['user_id'])) return null;
        return [
            'id' => (int)($_SESSION['user_id']),
            'username' => $_SESSION['username'] ?? '',
            'role' => $_SESSION['user_role'] ?? ''
        ];
    }

    // Helper: khách hàng hiện tại
    public static function currentCustomer(): ?array {
        self::start();
        if (!isset($_SESSION['customer_id'])) return null;
        return [
            'id' => (int)($_SESSION['customer_id']),
            'name' => $_SESSION['customer_name'] ?? ''
        ];
    }
}

final class Auth {
    public static function start(): void {
        if (session_status() === \PHP_SESSION_NONE) session_start();
    }

    public static function guardAdmin(): void {
        self::start();
        if (empty($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'admin')) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    // ➕ thêm hàm này
    public static function logout(?string $redirect = null): void {
        self::start();
        session_unset();
        session_destroy();
        if ($redirect) {
            header('Location: ' . $redirect);
            exit;
        }
    }
}
*/
namespace App;

final class Auth {
    /** Bắt đầu session an toàn */
    public static function start(): void {
        if (session_status() === \PHP_SESSION_NONE) session_start();
    }

    /** Chỉ cho admin/staff vào khu vực admin */
    public static function guardAdmin(): void {
        self::start();
        // Nếu bạn CHỈ muốn admin vào, đổi điều kiện thành === 'admin'
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, ['admin', 'staff'], true)) {
            // Dùng đường dẫn tương đối để chạy được cả trên máy local và subdir
            header('Location: login.php');
            exit;
        }
    }

    /** Chỉ cho khách đã đăng nhập vào khu vực cần khách */
    public static function guardCustomer(): void {
        self::start();
        if (empty($_SESSION['customer_id'])) {
            header('Location: /login_cus.php');
            exit;
        }
    }

    /** Thông tin admin/staff hiện tại (nếu có) */
    public static function currentAdmin(): ?array {
        self::start();
        if (empty($_SESSION['user_id'])) return null;
        return [
            'id'       => (int)$_SESSION['user_id'],
            'username' => (string)($_SESSION['username'] ?? ''),
            'role'     => (string)($_SESSION['user_role'] ?? ''),
        ];
    }

    /** Thông tin khách hàng hiện tại (nếu có) */
    public static function currentCustomer(): ?array {
        self::start();
        if (empty($_SESSION['customer_id'])) return null;
        return [
            'id'   => (int)$_SESSION['customer_id'],
            'name' => (string)($_SESSION['customer_name'] ?? ''),
        ];
    }

    /** Đăng xuất + điều hướng (tuỳ chọn) */
    public static function logout(?string $redirect = null): void {
        self::start();
        // Xoá toàn bộ session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        if ($redirect) {
            header('Location: ' . $redirect);
            exit;
        }
    }
}
