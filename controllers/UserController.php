<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Db;

final class UserController
{
    /**
     * Xử lý toàn bộ màn hình Quản lý người dùng (admin/users_manage.php)
     */
    public function handleManage(): array
    {
        Auth::guardAdmin();

        // ===== Biến mặc định =====
        $message   = '';
        $error     = '';
        $tab       = $_POST['tab'] ?? $_GET['tab'] ?? '';
        $customers = [];
        $staffs    = [];

        // ===== Xử lý form POST =====
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $csrf   = $_POST['csrf']   ?? '';

            if (!Csrf::check($csrf)) {
                $error = 'Phiên không hợp lệ (CSRF). Vui lòng thử lại.';
            } else {
                switch ($action) {
                    case 'update_customer':
                        [$ok, $msg] = $this->updateCustomer($_POST);
                        if ($ok) $message = $msg; else $error = $msg;
                        $tab = 'customers';
                        break;

                    case 'delete_customer':
                        [$ok, $msg] = $this->deleteCustomer($_POST);
                        if ($ok) $message = $msg; else $error = $msg;
                        $tab = 'customers';
                        break;

                    case 'create_staff':
                        [$ok, $msg] = $this->createStaff($_POST);
                        if ($ok) $message = $msg; else $error = $msg;
                        $tab = 'staff';
                        break;

                    case 'delete_staff':
                        [$ok, $msg] = $this->deleteStaff($_POST);
                        if ($ok) $message = $msg; else $error = $msg;
                        $tab = 'staff';
                        break;
                }
            }
        }

        // ===== Load danh sách sau khi xử lý =====
        if ($tab === '' || $tab === 'customers') {
            $customers = $this->getCustomerList();
        }
        if ($tab === '' || $tab === 'staff') {
            $staffs = $this->getStaffList();
        }

        // Chuẩn hoá tab
        if (!in_array($tab, ['customers', 'staff'], true)) {
            $tab = '';
        }

        return [
            'message'   => $message,
            'error'     => $error,
            'tab'       => $tab,
            'customers' => $customers,
            'staffs'    => $staffs,
            'csrf'      => \App\Csrf::token(),
        ];
    }

    /* ============================================================
       KHÁCH HÀNG
       ============================================================ */

    /**
     * Danh sách khách hàng từ bảng customers
     */
    private function getCustomerList(): array
    {
        $sql = "SELECT id, full_name, email, phone, created_at
                FROM customers
                ORDER BY id DESC";

        $res = Db::conn()->query($sql);
        if (!$res) {
            return [];
        }
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Cập nhật email / phone khách hàng
     */
    private function updateCustomer(array $data): array
    {
        $id    = isset($data['id']) ? (int)$data['id'] : 0;
        $email = trim((string)($data['email'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));

        if ($id <= 0) {
            return [false, 'Thiếu ID khách hàng.'];
        }
        if ($email === '') {
            return [false, 'Email không được để trống.'];
        }

        // Phone có thể rỗng, nhưng nếu có thì kiểm tra 10–11 số
        if ($phone !== '' && !preg_match('/^[0-9]{10,11}$/', $phone)) {
            return [false, 'Số điện thoại phải có 10–11 chữ số.'];
        }

        $stmt = Db::conn()->prepare(
            "UPDATE customers SET email = ?, phone = ? WHERE id = ?"
        );
        if (!$stmt) {
            return [false, 'Lỗi hệ thống (prepare update customer).'];
        }

        $stmt->bind_param('ssi', $email, $phone, $id);
        if ($stmt->execute()) {
            return [true, 'Đã cập nhật thông tin khách hàng.'];
        }

        return [false, 'Không thể cập nhật khách hàng: ' . $stmt->error];
    }

    /**
     * Xoá khách hàng
     */
    private function deleteCustomer(array $data): array
    {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if ($id <= 0) {
            return [false, 'Thiếu ID khách hàng.'];
        }

        $stmt = Db::conn()->prepare("DELETE FROM customers WHERE id = ?");
        if (!$stmt) {
            return [false, 'Lỗi hệ thống (prepare delete customer).'];
        }

        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            return [true, 'Đã xoá tài khoản khách hàng.'];
        }

        return [false, 'Không thể xoá khách hàng: ' . $stmt->error];
    }

    /* ============================================================
       NHÂN VIÊN (users: admin / staff)
       ============================================================ */

    /**
     * Danh sách tài khoản nhân viên / admin từ bảng users
     */
    private function getStaffList(): array
    {
        $sql = "SELECT id, username, email, role, created_at
                FROM users
                WHERE role IN ('admin', 'staff')
                ORDER BY id DESC";

        $res = Db::conn()->query($sql);
        if (!$res) {
            return [];
        }
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Tạo staff mới (gửi email kích hoạt / đặt mật khẩu sau – nếu bạn muốn)
     */
    private function createStaff(array $data): array
    {
        $email    = trim((string)($data['email']    ?? ''));
        $username = trim((string)($data['username'] ?? ''));
        $role     = trim((string)($data['role']     ?? ''));

        if ($email === '' || $username === '' || $role === '') {
            return [false, 'Vui lòng nhập đủ Email, Tên đăng nhập và Phân quyền.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Email không hợp lệ.'];
        }
        if (!in_array($role, ['admin', 'staff'], true)) {
            return [false, 'Phân quyền không hợp lệ.'];
        }

        // Kiểm tra trùng email / username
        $stmt = Db::conn()->prepare(
            "SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1"
        );
        if (!$stmt) {
            return [false, 'Lỗi hệ thống (prepare check user).'];
        }
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->fetch_assoc()) {
            return [false, 'Email hoặc tên đăng nhập đã tồn tại.'];
        }

<<<<<<< HEAD
        // Tạo user với password tạm (có thể random); sau đó dùng activate_staff để đặt lại
        $tmpPassword = bin2hex(random_bytes(4)); // 8 ký tự hex
        $hash        = password_hash($tmpPassword, PASSWORD_BCRYPT);

=======
        // Tạo user với password rỗng để bắt buộc đặt qua email kích hoạt
        $emptyHash = '';
>>>>>>> 8d71618b4a15096e4cfb9fce32de9e4852252747
        $stmt = Db::conn()->prepare(
            "INSERT INTO users (username, email, password_hash, role, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );
        if (!$stmt) {
            return [false, 'Lỗi hệ thống (prepare create staff).'];
        }
<<<<<<< HEAD

        $stmt->bind_param('ssss', $username, $email, $hash, $role);
        if ($stmt->execute()) {
            // TODO: nếu muốn, bạn có thể gửi email chứa link activate_staff.php + token
            return [true, 'Đã tạo tài khoản nhân viên. Hãy gửi link kích hoạt / đặt mật khẩu cho họ.'];
=======
        $stmt->bind_param('ssss', $username, $email, $emptyHash, $role);
        if ($stmt->execute()) {
            $userId = Db::conn()->insert_id;
            // Tạo token kích hoạt
            $token = bin2hex(random_bytes(32));
            $expires = (new \DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s');
            $tokStmt = Db::conn()->prepare("INSERT INTO admin_invite_tokens (user_id, token, expires_at) VALUES (?,?,?)");
            if ($tokStmt) {
                $tokStmt->bind_param('iss', $userId, $token, $expires);
                $tokStmt->execute();
            }
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $path   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
            $link   = $scheme . '://' . $host . $path . '/activate_staff.php?token=' . urlencode($token);
            $subject = 'Mời kích hoạt tài khoản nhân viên';
            $body  = "Xin chào $username,\n\n";
            $body .= "Bạn được mời tạo tài khoản quản trị. Vui lòng đặt mật khẩu tại liên kết (hiệu lực 24h):\n$link\n";
            $body .= "Nếu bạn không mong đợi email này, hãy bỏ qua.";
            \App\Email::send($email, $subject, $body);
            return [true, 'Đã tạo tài khoản và gửi email kích hoạt đặt mật khẩu.'];
>>>>>>> 8d71618b4a15096e4cfb9fce32de9e4852252747
        }

        return [false, 'Không thể tạo nhân viên: ' . $stmt->error];
    }

    /**
     * Xoá staff/admin (trừ chính mình)
     */
    private function deleteStaff(array $data): array
    {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if ($id <= 0) {
            return [false, 'Thiếu ID nhân viên.'];
        }

        // Nếu project của bạn có lưu ID admin trong session,
        // bạn có thể BỔ SUNG kiểm tra ở đây, ví dụ:
        //
        // if (!empty($_SESSION['admin_id']) && $id === (int)$_SESSION['admin_id']) {
        //     return [false, 'Không thể xoá chính tài khoản đang đăng nhập.'];
        // }

        $stmt = Db::conn()->prepare("DELETE FROM users WHERE id = ?");
        if (!$stmt) {
            return [false, 'Lỗi hệ thống (prepare delete staff).'];
        }

        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            return [true, 'Đã xoá tài khoản nhân viên.'];
        }

        return [false, 'Không thể xoá nhân viên: ' . $stmt->error];
    }


    /* ============================================================
       Các hàm cũ để tương thích (nếu view khác có dùng)
       ============================================================ */

    /** Lấy 1 user theo id (nếu cần) */
    public function find(int $id): ?array
    {
        $stmt = Db::conn()->prepare(
            "SELECT id, username, email, role, created_at FROM users WHERE id = ? LIMIT 1"
        );
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /** Trả về toàn bộ user (chủ yếu để tương thích code cũ) */
    public function listAll(): array
    {
        $sql = "SELECT id, username, email, role, created_at
                FROM users
                ORDER BY id DESC";
        $res = Db::conn()->query($sql);
        if (!$res) return [];
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
