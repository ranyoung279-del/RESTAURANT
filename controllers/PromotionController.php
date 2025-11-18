<?php
namespace App\Controllers;

class PromotionController
{
    private $conn;

    public function __construct($conn = null)
    {
        if ($conn === null) {
            global $conn;
        }
        $this->conn = $conn;
    }

    /* --------------------------
        VALIDATE PROMOTION
    --------------------------- */
    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) $errors[] = "Tiêu đề không được để trống";
        if (empty($data['coupon_code'])) $errors[] = "Mã khuyến mãi không được để trống";

        if (!in_array($data['discount_type'], ['percent', 'fixed'])) {
            $errors[] = "Loại giảm giá không hợp lệ";
        }

        if (!is_numeric($data['discount_value']) || $data['discount_value'] <= 0) {
            $errors[] = "Giá trị giảm giá phải lớn hơn 0";
        }

        if ($data['discount_type'] === 'percent' && $data['discount_value'] > 100) {
            $errors[] = "Giảm giá phần trăm không được vượt quá 100%";
        }

        if (!empty($data['start_at']) && !empty($data['end_at'])) {
            if (strtotime($data['start_at']) >= strtotime($data['end_at'])) {
                $errors[] = "Ngày kết thúc phải sau ngày bắt đầu";
            }
        }

        return $errors;
    }

    /* --------------------------
        KIỂM TRA COUPON TỒN TẠI
    --------------------------- */
    private function couponExists(string $coupon_code, $exclude_id = null): bool
    {
        if ($exclude_id) {
            $stmt = $this->conn->prepare("SELECT id FROM promotions WHERE coupon_code=? AND id!=?");
            $stmt->bind_param("si", $coupon_code, $exclude_id);
        } else {
            $stmt = $this->conn->prepare("SELECT id FROM promotions WHERE coupon_code=?");
            $stmt->bind_param("s", $coupon_code);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /* --------------------------
        DANH SÁCH KHUYẾN MÃI (Khách)
    --------------------------- */
    public function listAvailable()
    {
        return $this->conn->query("SELECT * FROM promotions WHERE active=1 ORDER BY created_at DESC");
    }

    /* --------------------------
        GET ALL (Admin)
    --------------------------- */
    public function getAll()
    {
        return $this->conn->query("SELECT * FROM promotions ORDER BY created_at DESC");
    }

    /* --------------------------
        FIND BY ID
    --------------------------- */
    public function find(int $id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM promotions WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* --------------------------
        CREATE
    --------------------------- */
    public function create(array $data)
    {
        $data['active'] = isset($data['active']) ? 1 : 0;
        $data['apply_to_all'] = isset($data['apply_to_all']) ? 1 : 0;
        $data['apply_to_menu_ids'] = !$data['apply_to_all'] && !empty($data['apply_to_menu_ids'])
            ? implode(',', array_map('intval', explode(',', $data['apply_to_menu_ids'])))
            : null;

        if ($this->couponExists($data['coupon_code'])) {
            return ['error' => 'Mã khuyến mãi đã tồn tại!'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO promotions 
            (title, description, coupon_code, discount_type, discount_value, apply_to_menu_ids, apply_to_all, start_at, end_at, active, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssdsiisss",
            $data['title'],
            $data['description'],
            $data['coupon_code'],
            $data['discount_type'],
            $data['discount_value'],
            $data['apply_to_menu_ids'],
            $data['apply_to_all'],
            $data['start_at'],
            $data['end_at'],
            $data['active'],
            $data['image_url']
        );

        if ($stmt->execute()) return ['success' => true];
        return ['error' => $this->conn->error];
    }

    /* --------------------------
        UPDATE
    --------------------------- */
    public function update(int $id, array $data)
    {
        $data['active'] = isset($data['active']) ? 1 : 0;
        $data['apply_to_all'] = isset($data['apply_to_all']) ? 1 : 0;
        $data['apply_to_menu_ids'] = !$data['apply_to_all'] && !empty($data['apply_to_menu_ids'])
            ? implode(',', array_map('intval', explode(',', $data['apply_to_menu_ids'])))
            : null;

        if ($this->couponExists($data['coupon_code'], $id)) {
            return ['error' => 'Mã khuyến mãi đã tồn tại!'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE promotions SET title=?, description=?, coupon_code=?, discount_type=?, discount_value=?,
             apply_to_menu_ids=?, apply_to_all=?, start_at=?, end_at=?, active=?, image_url=? WHERE id=?"
        );
        $stmt->bind_param(
            "ssssdsiisssi",
            $data['title'],
            $data['description'],
            $data['coupon_code'],
            $data['discount_type'],
            $data['discount_value'],
            $data['apply_to_menu_ids'],
            $data['apply_to_all'],
            $data['start_at'],
            $data['end_at'],
            $data['active'],
            $data['image_url'],
            $id
        );

        if ($stmt->execute()) return ['success' => true];
        return ['error' => $this->conn->error];
    }

    /* --------------------------
        DELETE
    --------------------------- */
    public function delete(int $id)
    {
        $stmt = $this->conn->prepare("DELETE FROM promotions WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) return ['success' => true];
        return ['error' => $this->conn->error];
    }

    /* --------------------------
        APPLY PROMOTION
    --------------------------- */
    public function apply(string $coupon_code, float $order_amount)
    {
        $stmt = $this->conn->prepare("SELECT * FROM promotions WHERE coupon_code=? AND active=1");
        $stmt->bind_param("s", $coupon_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Mã khuyến mãi không hợp lệ'];
        }

        $promo = $result->fetch_assoc();
        $now   = date('Y-m-d H:i:s');

        if ($promo['start_at'] && $now < $promo['start_at']) return ['success'=>false,'message'=>'Mã chưa hiệu lực'];
        if ($promo['end_at'] && $now > $promo['end_at']) return ['success'=>false,'message'=>'Mã đã hết hạn'];

        $discount = ($promo['discount_type']==='percent') ? ($order_amount * $promo['discount_value']/100) : $promo['discount_value'];
        $discount = min($discount, $order_amount);

        return ['success'=>true,'discount'=>$discount,'promotion'=>$promo];
    }

    /* --------------------------
        HANDLER: xử lý POST/GET cho admin view
    --------------------------- */
    public function handleRequest(): array
    {
        $message = '';
        $error = '';
        $list = $this->getAll();
        $edit_data = null;

        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (isset($_POST['add_promotion'])) {
                $res = $this->create($_POST);
                if (isset($res['error'])) $error = $res['error'];
                else $message = "Thêm khuyến mãi thành công!";
            }
            if (isset($_POST['update_promotion'])) {
                $id = $_POST['id'];
                $res = $this->update($id,$_POST);
                if (isset($res['error'])) $error = $res['error'];
                else $message = "Cập nhật thành công!";
            }
        }

        if (isset($_GET['delete'])) {
            $res = $this->delete($_GET['delete']);
            if (isset($res['error'])) $error = $res['error'];
            else $message = "Xóa thành công!";
        }

        // refresh danh sách
        $list = $this->getAll();

        return [
            'message'=>$message,
            'error'=>$error,
            'list'=>$list,
            'edit_data'=>$edit_data
        ];
    }

    public function handleManage(): array
    {
        return $this->handleRequest();
    }

    public function listAll()
    {
        return $this->getAll();
    }
}
