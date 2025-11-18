<?php
namespace App\Controllers;

use App\Models\Home;
use App\Models\Menu;
use App\Db;
use App\Csrf;
use App\Auth;

final class HomeController
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
            $ok = $this->save($_POST, $_FILES, $_POST['csrf'] ?? null);
            if ($ok) {
                header('Location: home_manage.php');
                exit;
            } else {
                $error = 'Lưu thất bại (kiểm tra CSRF/ảnh).';
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

    /**
     * FRONT: Lấy dữ liệu cho trang viewer.php
     * - home: 1 bản ghi home_settings
     * - deals: danh sách hot deals (menu_items)
     */
    public function index(): array
    {
        return [
            'home'  => Home::one(),
            'deals' => Menu::hotDeals(5),
        ];
    }

    /** ADMIN: Lấy danh sách tất cả bản ghi home_settings */
    public function listAll()
    {
        Auth::guardAdmin();
        return Db::conn()->query("SELECT * FROM home_settings ORDER BY id DESC");
    }

    /** ADMIN: Lấy 1 bản ghi theo id */
    public function find(int $id): ?array
    {
        Auth::guardAdmin();
        $stmt = Db::conn()->prepare("SELECT * FROM home_settings WHERE id=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * ADMIN: Thêm/Cập nhật
     * - $data: $_POST
     * - $files: $_FILES
     * - $csrf: token
     */
    public function save(array $data, array $files, ?string $csrf): bool
    {
        Auth::guardAdmin();
        if (!Csrf::check($csrf)) return false;

        $db        = Db::conn();
        $uploadRel = 'uploads/home/';                           // lưu vào DB
        $uploadDir = __DIR__ . '/../admin/uploads/home/';       // nơi move_uploaded_file
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        $id    = (int)($data['id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $desc  = trim($data['description'] ?? '');

        // Upload nhiều ảnh intro
        $intro = [];
        if (!empty($files['intro_images']['name'][0])) {
            foreach ($files['intro_images']['name'] as $i => $name) {
                if ((int)($files['intro_images']['error'][$i] ?? 0) === 0) {
                    $safe = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $name);
                    $file = time() . '_' . $safe;
                    $dstDisk = $uploadDir . $file;
                    if (is_uploaded_file($files['intro_images']['tmp_name'][$i])) {
                        if (move_uploaded_file($files['intro_images']['tmp_name'][$i], $dstDisk)) {
                            $intro[] = $uploadRel . $file;
                        }
                    }
                }
            }
        }
        $introStr = implode("\n", $intro);

        // Upload banner
        $bannerRel = '';
        if (!empty($files['banner_image']['name'])) {
            if ((int)($files['banner_image']['error'] ?? 0) === 0) {
                $safe = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $files['banner_image']['name']);
                $file = time() . '_' . $safe;
                $dstDisk = $uploadDir . $file;
                if (is_uploaded_file($files['banner_image']['tmp_name'])) {
                    if (move_uploaded_file($files['banner_image']['tmp_name'], $dstDisk)) {
                        $bannerRel = $uploadRel . $file;
                    }
                }
            }
        }

        if ($id > 0) {
            $stmt = $db->prepare(
                "UPDATE home_settings SET title=?, description=?, intro_images=?, banner_image=? WHERE id=?"
            );
            if (!$stmt) return false;
            $stmt->bind_param("ssssi", $title, $desc, $introStr, $bannerRel, $id);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO home_settings (title, description, intro_images, banner_image) VALUES (?,?,?,?)"
            );
            if (!$stmt) return false;
            $stmt->bind_param("ssss", $title, $desc, $introStr, $bannerRel);
        }
        return $stmt->execute();
    }

    /** ADMIN: Xoá 1 bản ghi */
    public function delete(int $id, ?string $csrf): bool
    {
        Auth::guardAdmin();
        if (!Csrf::check($csrf)) return false;
        $stmt = Db::conn()->prepare("DELETE FROM home_settings WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
