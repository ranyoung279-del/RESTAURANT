<?php
declare(strict_types=1);

namespace App\Controllers;
use App\Auth;
use App\Db;
use App\Models\Customer;
final class AuthController
{
    public function handleAdminLogin(): string
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            return 'Vui lòng nhập đầy đủ thông tin.';
        }
        // Gọi loginAdmin để xác thực
        [$success, $message] = $this->loginAdmin($username, $password);
        if ($success) {
            header('Location: dashboard.php');
            exit;
        }
        return $message;
    }

    /** Đăng nhập khách hàng */
    public function loginCustomer(string $email, string $password): array
    {
        Auth::start();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $_SESSION['error'] = 'Email hoặc mật khẩu không hợp lệ.';
            return [false, 'Email hoặc mật khẩu không hợp lệ.'];
        }

        $user = Customer::byEmail($email);
        if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            $_SESSION['customer_id']    = (int)$user['id'];
            $_SESSION['customer_name']  = (string)($user['full_name'] ?? '');
            $_SESSION['customer_phone'] = (string)($user['phone'] ?? '');
            return [true, ''];
        }

        $_SESSION['error'] = 'Email hoặc mật khẩu không đúng!';
        return [false, 'Email hoặc mật khẩu không đúng!'];
    }

    /** Đăng xuất khách hàng */
    public function logoutCustomer(?string $redirect = 'login_cus.php'): void
    {
        Auth::start();
        session_unset();
        session_destroy();
        if ($redirect) {
            header('Location: ' . $redirect);
            exit;
        }
    }

    /* ===========================
       2) ADMIN/STAFF AUTH
       =========================== */
    public function loginAdmin(string $username, string $password): array
    {
        Auth::start();
        try {
            $db = $this->db();
            // Detect columns
            $hasEmail    = (bool)$db->query("SHOW COLUMNS FROM `users` LIKE 'email'")->num_rows;
            $hasRole     = (bool)$db->query("SHOW COLUMNS FROM `users` LIKE 'role'")->num_rows;
            $hasPwHash   = (bool)$db->query("SHOW COLUMNS FROM `users` LIKE 'password_hash'")->num_rows;
            $hasPwLegacy = (bool)$db->query("SHOW COLUMNS FROM `users` LIKE 'password'")->num_rows;

            // Truy vấn theo cột có sẵn
            if ($hasEmail) {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
                if (!$stmt) return [false, 'Lỗi prepare: ' . ($db->error ?? '')];
                $stmt->bind_param('ss', $username, $username);
            } else {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                if (!$stmt) return [false, 'Lỗi prepare: ' . ($db->error ?? '')];
                $stmt->bind_param('s', $username);
            }

            if (!$stmt->execute()) {
                return [false, 'Lỗi execute: ' . ($stmt->error ?: ($db->error ?? ''))];
            }

            $res  = $stmt->get_result();
            $user = $res ? $res->fetch_assoc() : null;
            if (!$user) return [false, 'Tài khoản không tồn tại.'];

            // LẤY GIÁ TRỊ MẬT KHẨU TRONG DB (CHỈ NHẬN NON-EMPTY)
            $dbHash = null;
            if ($hasPwHash && isset($user['password_hash']) && $user['password_hash'] !== '') {
                $dbHash = (string)$user['password_hash'];
            } elseif ($hasPwLegacy && isset($user['password']) && $user['password'] !== '') {
                $dbHash = (string)$user['password']; // md5 hoặc plaintext
            }

            if ($dbHash === null) {
                return [false, 'Tài khoản chưa thiết lập mật khẩu.'];
            }

            $ok = false; 
            $needUpgrade = false; 
            $newHash = null;

            // Bcrypt/Argon
            if (preg_match('/^\$(2y|2a|argon2i|argon2id)\$/', $dbHash)) {
                $ok = password_verify($password, $dbHash);
            }
            // MD5 (32 hex)
            elseif (preg_match('/^[a-f0-9]{32}$/i', $dbHash)) {
                if (md5($password) === strtolower($dbHash)) {
                    $ok = true; 
                    $needUpgrade = true; 
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                }
            }
            // Plain text
            else {
                if (hash_equals($dbHash, $password)) {
                    $ok = true; 
                    $needUpgrade = true; 
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                }
            }

            if (!$ok) return [false, 'Sai tài khoản hoặc mật khẩu.'];

            // UPGRADE HASH nếu cần (ưu tiên password_hash)
            if ($needUpgrade && $newHash) {
                if ($hasPwHash) {
                    $up = $db->prepare("UPDATE users SET password_hash=? WHERE id=?");
                    if ($up) { 
                        $uid = (int)$user['id']; 
                        $up->bind_param('si', $newHash, $uid); 
                        $up->execute(); 
                    }
                } elseif ($hasPwLegacy) {
                    $up = $db->prepare("UPDATE users SET password=? WHERE id=?");
                    if ($up) { 
                        $uid = (int)$user['id']; 
                        $up->bind_param('si', $newHash, $uid); 
                        $up->execute(); 
                    }
                }
            }

            // Role an toàn
            $role = 'admin';
            if ($hasRole) {
                $dbRole = (string)($user['role'] ?? '');
                $role   = in_array($dbRole, ['admin','staff'], true) ? $dbRole : 'admin';
            }

            // Session
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['username']  = $user['username'] ?? ($user['email'] ?? 'admin');
            $_SESSION['user_role'] = $role;
            session_regenerate_id(true);

            return [true, ''];
        } catch (\Throwable $e) {
            return [false, 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    /** Đăng xuất admin/staff */
    public function logoutAdmin(?string $redirect = 'login.php'): void
    {
        Auth::start();
        session_unset();
        session_destroy();
        if ($redirect) {
            header('Location: ' . $redirect);
            exit;
        }
    }

    /* ===========================
       Helpers
       =========================== */

    private function db(): \mysqli
    {
        if (class_exists('\App\Db')) {
            return \App\Db::conn();
        }
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof \mysqli) {
            return $GLOBALS['conn'];
        }
        throw new \RuntimeException('Không tìm thấy kết nối DB (App\\Db hoặc $conn).');
    }
}