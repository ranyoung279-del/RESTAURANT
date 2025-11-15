<?php
// includes/classes/PromotionRepository.php
require_once __DIR__ . '/Promotion.php';

class PromotionRepository {
    private \mysqli $conn;

    public function __construct($conn = null) {
        if ($conn instanceof \mysqli) {
            $this->conn = $conn;
            return;
        }

        if (class_exists('\App\Db')) {
            $this->conn = \App\Db::conn();
            if (!$this->conn instanceof \mysqli) {
                throw new Exception("PromotionRepository: \App\Db::conn() did not return mysqli instance.");
            }
        } else {
            throw new Exception("PromotionRepository: No mysqli connection provided and App\\Db not found.");
        }
    }

    public function create(array $data): int {
        $sql = "INSERT INTO promotions (title, description, coupon_code, discount_type, discount_value, start_at, end_at, active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->conn->error);

        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $coupon = $data['coupon_code'] ?? null;
        $dtype = $data['discount_type'] ?? 'percent';
        $dval = isset($data['discount_value']) ? floatval($data['discount_value']) : 0.0;
        $start = !empty($data['start_at']) ? $data['start_at'] : null;
        $end = !empty($data['end_at']) ? $data['end_at'] : null;
        $active = isset($data['active']) ? intval($data['active']) : 0;

        $stmt->bind_param('ssssdssi', $title, $description, $coupon, $dtype, $dval, $start, $end, $active);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Insert failed: " . $err);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return (int)$id;
    }

    // ⭐ THÊM METHOD UPDATE (BẠN THIẾU CÁI NÀY)
    public function update(int $id, array $data): bool {
        $sql = "UPDATE promotions 
                SET title = ?, 
                    description = ?, 
                    coupon_code = ?, 
                    discount_type = ?, 
                    discount_value = ?, 
                    start_at = ?, 
                    end_at = ?, 
                    active = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->conn->error);

        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $coupon = $data['coupon_code'] ?? null;
        $dtype = $data['discount_type'] ?? 'percent';
        $dval = isset($data['discount_value']) ? floatval($data['discount_value']) : 0.0;
        $start = !empty($data['start_at']) ? $data['start_at'] : null;
        $end = !empty($data['end_at']) ? $data['end_at'] : null;
        $active = isset($data['active']) ? intval($data['active']) : 0;

        $stmt->bind_param('ssssdssii', $title, $description, $coupon, $dtype, $dval, $start, $end, $active, $id);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Update failed: " . $err);
        }
        
        $affected = $stmt->affected_rows > 0;
        $stmt->close();
        return $affected;
    }

    // ⭐ THÊM METHOD DELETE (BẠN THIẾU CÁI NÀY)
    public function delete(int $id): bool {
        $sql = "DELETE FROM promotions WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->conn->error);

        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception("Delete failed: " . $err);
        }

        $affected = $stmt->affected_rows > 0;
        $stmt->close();
        return $affected;
    }

    // ⭐ THÊM METHOD FINDBYID (BẠN THIẾU CÁI NÀY)
    public function findById(int $id): ?array {
        $sql = "SELECT * FROM promotions WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: " . $this->conn->error);
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        
        return $row ?: null;
    }

    public function getActive(): array {
        $sql = "SELECT * FROM promotions WHERE active=1 AND (start_at IS NULL OR start_at <= NOW()) AND (end_at IS NULL OR end_at >= NOW()) ORDER BY start_at DESC";
        $res = $this->conn->query($sql);
        if (!$res) return [];
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = Promotion::fromRow($r);
        }
        return $rows;
    }

    public function find(int $id): ?Promotion {
        $sql = "SELECT * FROM promotions WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ? Promotion::fromRow($row) : null;
    }

    // ⭐ THÊM METHOD ALL (LẤY TẤT CẢ)
    public function all(): array {
        $sql = "SELECT * FROM promotions ORDER BY created_at DESC";
        $res = $this->conn->query($sql);
        if (!$res) return [];
        
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r; // Trả về array thay vì object Promotion
        }
        return $rows;
    }

    public function logSend($promotionId, $email, $phone, $channel, $status, $response = null) {
        $sql = "INSERT INTO promotion_sends (promotion_id, customer_email, customer_phone, channel, status, response, sent_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isssss', $promotionId, $email, $phone, $channel, $status, $response);
        $stmt->execute();
        $stmt->close();
    }
}