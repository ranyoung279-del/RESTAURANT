<?php
// controllers/MenuController.php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Db;
use App\Models\Menu; // <-- cần dòng này để gọi model Menu

final class MenuController
{
    /* ===================== ADMIN ===================== */
    public function handleManage(): array
    {
        Auth::guardAdmin();
        $message = '';
        $error = '';
        $edit_data = null;

        // Lấy dữ liệu sửa
        $editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($editId > 0) {
            $edit_data = $this->find($editId);
        }

        // Xoá
        if (isset($_GET['delete'])) {
            $delId = (int)$_GET['delete'];
            if ($delId > 0) {
                if ($this->deleteQuick($delId)) {
                    header("Location: menu_manage.php");
                    exit;
                } else {
                    $error = "Không xoá được (ID không hợp lệ hoặc lỗi hệ thống).";
                }
            }
        }

        // Thêm / Sửa
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->save($_POST, $_FILES, $_POST['csrf'] ?? null);
            if ($ok) {
                header("Location: menu_manage.php");
                exit;
            } else {
                $error = 'Lưu thất bại (kiểm tra CSRF/ảnh/đầu vào).';
            }
        }

        return [
            'message' => $message,
            'error' => $error,
            'edit_data' => $edit_data
        ];
    }

    /* ===================== FRONT ===================== */
    public function listAvailable()
    {
        return Menu::listAvailable(); // trả về mysqli_result để view while->fetch_assoc()
    }

    public function hotDeals(int $limit = 5)
    {
        return Menu::hotDeals($limit); // mysqli_result
    }

    /** Front: chi tiết món ăn */
    public function handleDetail(): array
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $item = null;
        $error = '';

        if ($id <= 0) {
            $error = 'Món ăn không hợp lệ.';
        } else {
            $item = $this->findOne($id);
            if (!$item) {
                $error = 'Không tìm thấy món ăn.';
            }
        }

        return [
            'item' => $item,
            'error' => $error
        ];
    }

    /** Front: danh sách món kèm khuyến mãi */
    public function indexWithPromotions(): array
    {
        $category = $_GET['category'] ?? null;
        return Menu::getAllItemsWithPromotions($category);
    }

    /** Front: chi tiết món kèm khuyến mãi */
    public function detailWithPromotion(): array
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            return [
                'item' => null,
                'error' => 'Món ăn không hợp lệ.'
            ];
        }

        $menuData = Menu::getItemWithPromotion($id);

        if (!$menuData) {
            return [
                'item' => null,
                'error' => 'Không tìm thấy món ăn.'
            ];
        }

        return [
            'item' => $menuData,
            'error' => ''
        ];
    }

    /** API: Lấy giá món ăn (AJAX/cart) */
    public function getPrice(): void
    {
        header('Content-Type: application/json');

        $menuItemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $menuData = Menu::getItemWithPromotion($menuItemId);

        if ($menuData) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'item_id' => $menuData['item']['id'],
                    'name' => $menuData['item']['name'],
                    'original_price' => $menuData['original_price'],
                    'final_price' => $menuData['final_price'],
                    'discount_amount' => $menuData['discount_amount'],
                    'has_promotion' => $menuData['has_promotion'],
                    'promotion' => $menuData['promotion']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy món ăn'
            ]);
        }
        exit;
    }

    /* ===================== ADMIN ===================== */
    public function listAll()
    {
        Auth::guardAdmin();
        return Db::conn()->query("SELECT * FROM menu_items ORDER BY id DESC");
    }

    public function find(int $id): ?array
    {
        Auth::guardAdmin();
        $stmt = Db::conn()->prepare("SELECT * FROM menu_items WHERE id=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function findOne(int $id): ?array
    {
        if ($id <= 0) return null;
        $db = Db::conn();
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ? AND is_available = 1 LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function save(array $data, array $files, ?string $csrf): bool
    {
        Auth::guardAdmin();
        if (!Csrf::check($csrf)) return false;

        $db          = Db::conn();
        $id          = (int)($data['id'] ?? 0);
        $name        = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $price       = (float)($data['price'] ?? 0);
        $category    = trim($data['category'] ?? '');
        $is_special  = !empty($data['is_special']) ? 1 : 0;
        $is_available= !empty($data['is_available']) ? 1 : 0;

        // Upload ảnh
        $uploadRel = 'uploads/';
        $uploadDir = __DIR__ . '/../admin/uploads/';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        $image_url = '';
        if (!empty($files['image_file']['name']) && (int)$files['image_file']['error'] === 0) {
            $safe = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', basename($files['image_file']['name']));
            $file = time() . '_' . $safe;
            $dstDisk = $uploadDir . $file;
            if (is_uploaded_file($files['image_file']['tmp_name'])) {
                if (move_uploaded_file($files['image_file']['tmp_name'], $dstDisk)) {
                    $image_url = $uploadRel . $file;
                }
            }
        }

        if ($id > 0) {
            if ($image_url === '') {
                $old = $this->find($id);
                if ($old && !empty($old['image_url'])) $image_url = $old['image_url'];
            }
            $sql  = "UPDATE menu_items
                        SET name=?, description=?, price=?, category=?, image_url=?, is_special=?, is_available=?
                      WHERE id=?";
            $stmt = $db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param("ssdssiii", $name,$description,$price,$category,$image_url,$is_special,$is_available,$id);
            return $stmt->execute();
        } else {
            $sql  = "INSERT INTO menu_items
                        (name, description, price, category, image_url, is_special, is_available)
                     VALUES (?,?,?,?,?,?,?)";
            $stmt = $db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param("ssdssii", $name,$description,$price,$category,$image_url,$is_special,$is_available);
            return $stmt->execute();
        }
    }

    public function deleteQuick(int $id): bool
    {
        Auth::guardAdmin();
        if ($id <= 0) return false;
        $stmt = Db::conn()->prepare("DELETE FROM menu_items WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
