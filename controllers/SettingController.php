<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Db;
use App\Models\Setting;

final class SettingController
{
    
    public function handleManage(): array
    {
        Auth::guardAdmin();
        $message = '';
        $error = '';
        $edit_data = null;

        // XOÁ
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
            $ok = $this->delete((int)($_POST['id'] ?? 0), $_POST['csrf'] ?? null);
            if ($ok) {
                $message = 'Đã xoá bản ghi.';
            } else {
                $error = 'Không thể xoá (CSRF hoặc ID không hợp lệ).';
            }
        }
        // LƯU
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
            $ok = $this->save($_POST, $_POST['csrf'] ?? null);
            if ($ok) {
                header('Location: settings.php');
                exit;
            } else {
                $error = 'Lưu thất bại (kiểm tra CSRF/đầu vào).';
            }
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
    /*FRONT: lấy cấu hình công khai (dùng cho footer, address…)*/
    public function getPublic(): array
    {
        return Setting::one() ?? [
            'restaurant_name' => 'WENZHU',
            'address'         => 'Chưa cập nhật địa chỉ',
            'open_hours'      => 'Chưa cập nhật giờ mở cửa',
            'phone'           => 'Chưa cập nhật',
            'email'           => 'Chưa cập nhật',
            'social_links'    => '{}',
        ];
    }

    /* (Alias cho front nếu bạn đang gọi getSetting ở view) */
    public function getSetting(): array
    {
        return $this->getPublic();
    }

    /**
     * ADMIN: danh sách settings
     */
    public function listAll()
    {
        Auth::guardAdmin();
        return Db::conn()->query("SELECT * FROM settings ORDER BY id DESC");
    }

    /**
     * ADMIN: lấy 1 bản ghi theo id
     */
    public function find(int $id): ?array
    {
        Auth::guardAdmin();
        $stmt = Db::conn()->prepare("SELECT * FROM settings WHERE id=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * ADMIN: thêm/cập nhật (giữ nguyên cấu trúc form hiện tại)
     * - id > 0 => UPDATE, ngược lại INSERT
     * - có kiểm tra CSRF
     */
    public function save(array $data, ?string $csrf): bool
    {
        Auth::guardAdmin();
        if (!Csrf::check($csrf)) return false;

        $db  = Db::conn();
        $id  = (int)($data['id'] ?? 0);

        $restaurant_name = trim((string)($data['restaurant_name'] ?? ''));
        $address         = trim((string)($data['address'] ?? ''));
        $phone           = trim((string)($data['phone'] ?? ''));
        $email           = trim((string)($data['email'] ?? ''));
        $open_hours      = trim((string)($data['open_hours'] ?? ''));

        $social_links = json_encode([
            'facebook'  => trim((string)($data['facebook']  ?? '')),
            'instagram' => trim((string)($data['instagram'] ?? '')),
            'tiktok'    => trim((string)($data['tiktok']    ?? '')),
        ], JSON_UNESCAPED_UNICODE);

        if ($id > 0) {
            $sql  = "UPDATE settings
                        SET restaurant_name=?, address=?, phone=?, email=?, open_hours=?, social_links=?
                      WHERE id=?";
            $stmt = $db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param(
                "ssssssi",
                $restaurant_name, $address, $phone, $email, $open_hours, $social_links, $id
            );
            return $stmt->execute();
        } else {
            $sql  = "INSERT INTO settings
                        (restaurant_name, address, phone, email, open_hours, social_links)
                     VALUES (?,?,?,?,?,?)";
            $stmt = $db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param(
                "ssssss",
                $restaurant_name, $address, $phone, $email, $open_hours, $social_links
            );
            return $stmt->execute();
        }
    }

    /* ADMIN: cập nhật cấu hình hiện tại với id=1 (nếu bạn muốn nút "Lưu" luôn ghi vào bản ghi 1)*/
    public function updateSetting(array $data): bool
    {
        Auth::guardAdmin();

        $db = Db::conn();
        $sql = "UPDATE settings
                   SET restaurant_name=?, address=?, phone=?, email=?, open_hours=?, social_links=?
                 WHERE id=1";
        $stmt = $db->prepare($sql);
        if (!$stmt) return false;

        $social = json_encode([
            'facebook'  => trim((string)($data['facebook']  ?? '')),
            'instagram' => trim((string)($data['instagram'] ?? '')),
            'tiktok'    => trim((string)($data['tiktok']    ?? '')),
        ], JSON_UNESCAPED_UNICODE);

        $stmt->bind_param(
            "ssssss",
            $data['restaurant_name'] ?? '',
            $data['address']         ?? '',
            $data['phone']           ?? '',
            $data['email']           ?? '',
            $data['open_hours']      ?? '',
            $social
        );
        return $stmt->execute();
    }

    /* ADMIN: xoá 1 bản ghi */
    public function delete(int $id, ?string $csrf): bool
    {
        Auth::guardAdmin();
        if (!Csrf::check($csrf)) return false;
        if ($id <= 0) return false;

        $stmt = Db::conn()->prepare("DELETE FROM settings WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
