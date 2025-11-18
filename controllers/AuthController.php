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
            // Chưa xác thực email -> từ chối đăng nhập
            if (empty($user['email_verified_at'])) {
                $_SESSION['error'] = 'Tài khoản chưa xác thực email. Vui lòng kiểm tra hộp thư của bạn.';
                return [false, 'Tài khoản chưa xác thực email.'];
            }
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
    /**
 * Xử lý form Quên mật khẩu của khách hàng (OOP)
 * Không gửi email, không tạo token — chỉ trả về thông báo chung.
 */
public function handleCustomerForgotPassword(): array
{
    $email = trim((string)($_POST['email'] ?? ''));

    if ($email === '') {
        return [null, 'Vui lòng nhập email của bạn.'];
    }

    // Tìm user bằng model Customer (nếu bạn muốn xác thực email tồn tại)
    $user = \App\Models\Customer::byEmail($email);

    // KHÔNG gửi mail – KHÔNG tạo token – tránh leak thông tin
    $message = 'Nếu email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.';
    return [$message, null];
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

        /**
     * Xử lý gửi email "Quên mật khẩu" cho tài khoản admin/staff
     * Trả về [message, error]
     */
    public function handleAdminForgotPassword(): array
    {
        $identifier = trim((string)($_POST['identifier'] ?? ''));

        if ($identifier === '') {
            return [null, 'Vui lòng nhập email hoặc tên đăng nhập.'];
        }

        $db = $this->db();

        // Xác định tìm theo email hay username
        $useEmail = (strpos($identifier, '@') !== false);

        $sql = $useEmail
            ? "SELECT id, email, username FROM users WHERE email = ? AND role IN ('admin','staff') LIMIT 1"
            : "SELECT id, email, username FROM users WHERE username = ? AND role IN ('admin','staff') LIMIT 1";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [null, 'Lỗi hệ thống (prepare user).'];
        }

        $stmt->bind_param('s', $identifier);
        $stmt->execute();
        $res  = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if ($user && !empty($user['email'])) {
            $userId = (int)$user['id'];
            $email  = (string)$user['email'];

            // Tạo token mới (60 phút)
            $token      = bin2hex(random_bytes(32));
            $expiresAt  = (new \DateTimeImmutable('+60 minutes'))->format('Y-m-d H:i:s');

            $ins = $db->prepare(
                "INSERT INTO user_activation_tokens (user_id, token, expires_at) VALUES (?,?,?)"
            );
            if ($ins) {
                $ins->bind_param('iss', $userId, $token, $expiresAt);
                $ins->execute();
                $ins->close();

                // Tạo link tới trang đặt lại mật khẩu (tái sử dụng activate_staff.php)
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $path   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
                $link   = $scheme . '://' . $host . $path . '/activate_staff.php?token=' . urlencode($token);

                $subject = 'Đặt lại mật khẩu tài khoản quản trị';
                $body  = "Xin chào " . ($user['username'] ?? '') . ",\n\n";
                $body .= "Bạn (hoặc ai đó) vừa yêu cầu đặt lại mật khẩu cho tài khoản quản trị tại website nhà hàng.\n";
                $body .= "Nhấn vào liên kết sau để đặt lại mật khẩu (hiệu lực 60 phút):\n";
                $body .= $link . "\n\n";
                $body .= "Nếu bạn không yêu cầu, vui lòng bỏ qua email này.";
                $headers = "Content-Type: text/plain; charset=UTF-8\r\n";

                // Gửi email (nếu mail() lỗi, mình vẫn trả về thông báo chung để tránh lộ dữ liệu)
                @mail($email, $subject, $body, $headers);
            }
        }

        // Luôn trả về thông điệp chung, kể cả khi không tìm thấy user
        $msg = 'Nếu email hoặc tên đăng nhập hợp lệ, hệ thống đã gửi hướng dẫn đặt lại mật khẩu. Vui lòng kiểm tra hộp thư của bạn.';
        return [$msg, null];
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