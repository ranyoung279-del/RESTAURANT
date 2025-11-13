<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Db;
final class UserController
{
        public function handleManage(): array
    {
        Auth::guardAdmin();
        
        $message = '';
        $error = '';
        $edit_data = null;
        // XOÁ
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
            [$ok, $msg] = $this->delete(
                (int)($_POST['id'] ?? 0), 
                (int)($_SESSION['user_id'] ?? 0), 
                $_POST['csrf'] ?? null
            );
            if ($ok) {
                $message = $msg;
            } else {
                $error = $msg;
            }
        }
        // LƯU
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
            [$ok, $msg] = $this->save($_POST, $_POST['csrf'] ?? null);
            if ($ok) {
                header('Location: users_manage.php');
                exit;
            }
            $error = $msg;
        }
        // LẤY DỮ LIỆU SỬA
        if (isset($_GET['id'])) {
            $editId = (int)$_GET['id'];
            if ($editId > 0) {
                $edit_data = $this->find($editId);
            }
        }
        return [
            'message' => $message,
            'error' => $error,
            'edit_data' => $edit_data
        ];
    }
    /** Chỉ admin/staff mới vào các API này */
    public function __construct()
    {
        Auth::guardAdmin();
    }
    /** Lấy 1 user theo id (đổ form) */
    public function find(int $id): ?array
    {
        $stmt = Db::conn()->prepare("SELECT id, username, role, created_at FROM users WHERE id=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    /** Danh sách user (đổ bảng) */
    public function listAll()
    {
        return Db::conn()->query("SELECT id, username, role, created_at FROM users ORDER BY id DESC");
    }

    /** Thêm/Cập nhật user (giữ nguyên form hiện tại) */
    public function save(array $data, ?string $csrf): array
    {
        if (!Csrf::check($csrf)) {
            return [false, 'Phiên không hợp lệ (CSRF).'];
        }
        $db       = Db::conn();
        $id       = (int)($data['id'] ?? 0);
        $username = trim((string)($data['username'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $role     = (string)($data['role'] ?? 'staff');
        if (!in_array($role, ['admin', 'staff'], true)) $role = 'staff';

        if ($username === '') {
            return [false, 'Tên đăng nhập không được để trống.'];
        }

        // check trùng username
        $stmt = $db->prepare("SELECT id FROM users WHERE username=? AND id<>?");
        if ($stmt) {
            $stmt->bind_param("si", $username, $id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                return [false, "Tên đăng nhập <b>" . htmlspecialchars($username) . "</b> đã tồn tại."];
            }
        }

        if ($id > 0) {
            // update
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET username=?, password_hash=?, role=? WHERE id=?");
                if (!$stmt) return [false, 'Lỗi hệ thống (prepare cập nhật có mật khẩu).'];
                $stmt->bind_param("sssi", $username, $hash, $role, $id);
            } else {
                $stmt = $db->prepare("UPDATE users SET username=?, role=? WHERE id=?");
                if (!$stmt) return [false, 'Lỗi hệ thống (prepare cập nhật).'];
                $stmt->bind_param("ssi", $username, $role, $id);
            }
            if ($stmt->execute()) return [true, ''];
            return [false, 'Cập nhật thất bại: ' . $stmt->error];
        }

        // insert
        if ($password === '') {
            return [false, 'Bạn phải nhập mật khẩu khi thêm người dùng mới.'];
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, role, created_at) VALUES (?,?,?, NOW())");
        if (!$stmt) return [false, 'Lỗi hệ thống (prepare thêm mới).'];
        $stmt->bind_param("sss", $username, $hash, $role);
        if ($stmt->execute()) return [true, ''];
        return [false, 'Thêm mới thất bại: ' . $stmt->error];
    }

    /** Xoá user (không cho xoá chính mình) */
    public function delete(int $id, int $selfId, ?string $csrf): array
    {
        if (!Csrf::check($csrf)) {
            return [false, 'Phiên không hợp lệ (CSRF).'];
        }
        if ($id <= 0) return [false, 'Thiếu id.'];
        if ($id === $selfId) return [false, 'Không thể xoá chính tài khoản đang đăng nhập.'];

        $stmt = Db::conn()->prepare("DELETE FROM users WHERE id=?");
        if (!$stmt) return [false, 'Lỗi hệ thống (prepare xoá).'];
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) return [true, 'Đã xoá người dùng.'];
        return [false, 'Không thể xoá: ' . $stmt->error];
    }
}
