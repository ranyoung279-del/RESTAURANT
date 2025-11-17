<?php
namespace App\Models;
use App\Db;

/** ===================== MENU ===================== */
final class Menu {
    public static function listAvailable() {
        return Db::conn()->query(
            "SELECT * FROM menu_items
             WHERE is_available=1
             ORDER BY is_special DESC, name ASC"
        );
    }

    public static function hotDeals(int $limit = 5) {
        $limit = max(1, (int)$limit);
        return Db::conn()->query(
            "SELECT * FROM menu_items
             ORDER BY is_special DESC, id DESC
             LIMIT {$limit}"
        );
    }

    public static function findById(int $id): ?array {
        $stmt = Db::conn()->prepare("SELECT * FROM menu_items WHERE id=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $r = $stmt->get_result();
        return $r? $r->fetch_assoc() : null;
    }
    /** ===================== PHẦN KHUYẾN MÃI ===================== */
    
    /**
     * Lấy thông tin món ăn kèm khuyến mãi
     */
    public static function getItemWithPromotion(int $menuItemId): ?array
    {
        $db = Db::conn();
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ? AND is_available = 1");
        if (!$stmt) return null;
        
        $stmt->bind_param("i", $menuItemId);
        $stmt->execute();
        $menuItem = $stmt->get_result()->fetch_assoc();
        
        if (!$menuItem) return null;
        
        // Tìm khuyến mãi áp dụng
        $promotion = self::getApplicablePromotion($menuItemId);
        
        return self::calculatePriceWithPromotion($menuItem, $promotion);
    }

    /**
     * Lấy tất cả món ăn kèm khuyến mãi
     */
    public static function getAllItemsWithPromotions(?string $category = null): array
    {
        $db = Db::conn();
        $sql = "SELECT * FROM menu_items WHERE is_available = 1";
        
        if ($category) {
            $sql .= " AND category = ?";
            $stmt = $db->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param("s", $category);
        } else {
            $stmt = $db->prepare($sql);
            if (!$stmt) return [];
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($item = $result->fetch_assoc()) {
            $promotion = self::getApplicablePromotion($item['id']);
            $items[] = self::calculatePriceWithPromotion($item, $promotion);
        }
        
        return $items;
    }
    
    /**
     * Tìm khuyến mãi áp dụng cho món ăn
     */
    private static function getApplicablePromotion(int $menuItemId): ?array
    {
        $db = Db::conn();
        $now = date('Y-m-d H:i:s');
        
        $sql = "SELECT * FROM promotions 
                WHERE active = 1 
                AND start_at <= ? 
                AND end_at >= ?
                AND (apply_to_all = 1 
                     OR FIND_IN_SET(?, apply_to_menu_ids))
                ORDER BY discount_value DESC
                LIMIT 1";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) return null;
        
        $stmt->bind_param("ssi", $now, $now, $menuItemId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Tính giá sau khuyến mãi
     */
    private static function calculatePriceWithPromotion(array $menuItem, ?array $promotion): array
    {
        $originalPrice = (float)$menuItem['price'];
        $finalPrice = $originalPrice;
        $discountAmount = 0;
        
        if ($promotion) {
            if ($promotion['discount_type'] === 'percent') {
                $discountAmount = ($originalPrice * $promotion['discount_value']) / 100;
            } else {
                $discountAmount = (float)$promotion['discount_value'];
            }
            $finalPrice = max(0, $originalPrice - $discountAmount);
        }
        
        return [
            'item' => $menuItem,
            'promotion' => $promotion,
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'has_promotion' => !empty($promotion)
        ];
    }
}
/** ===================== HOME (TRANG CHỦ) ===================== */
final class Home {
    public static function one(): ?array {
        $res = Db::conn()->query("SELECT * FROM home_settings LIMIT 1");
        return $res? $res->fetch_assoc() : null;
    }
}

/** ===================== SETTINGS (ĐỊA CHỈ / LIÊN HỆ) ===================== */
final class Setting {
    public static function one(): ?array {
        $res = Db::conn()->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
        return $res? $res->fetch_assoc() : null;
    }

    public static function updateById(int $id, array $data): bool {
        $c = Db::conn();
        $sql = "UPDATE settings
                   SET restaurant_name=?, address=?, phone=?, email=?, open_hours=?, social_links=?
                 WHERE id=?";
        $stmt = $c->prepare($sql);
        if (!$stmt) return false;
        $restaurant_name = $data['restaurant_name'] ?? '';
        $address         = $data['address'] ?? '';
        $phone           = $data['phone'] ?? '';
        $email           = $data['email'] ?? '';
        $open_hours      = $data['open_hours'] ?? '';
        $social_links    = $data['social_links'] ?? '{}';
        $stmt->bind_param("ssssssi",
            $restaurant_name,$address,$phone,$email,$open_hours,$social_links,$id
        );
        return $stmt->execute();
    }
}

/** ===================== RESERVATION (ĐẶT BÀN) ===================== */
final class Reservation
{
    public static function create(array $data): bool {
        $sql = "INSERT INTO reservations
                   (customer_id, full_name, phone, reservation_date, people_count, table_type, note, status, created_at)
                VALUES(?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = Db::conn()->prepare($sql);
        if (!$stmt) return false;

        $customer_id    = $data['customer_id'] ?? null;
        $full_name      = $data['full_name'] ?? '';
        $phone          = $data['phone'] ?? '';
        $reservation_dt = $data['reservation_date'] ?? '';
        $people_count   = (int)($data['people_count'] ?? 1);
        $table_type     = $data['table_type'] ?? '';
        $note           = $data['note'] ?? '';

        $stmt->bind_param(
            "isssiss",
            $customer_id, $full_name, $phone, $reservation_dt,
            $people_count, $table_type, $note
        );
        return $stmt->execute();
    }

    /** ✅ THÊM: Lịch sử đặt bàn theo khách hàng (đặt ĐÚNG bên trong class) */
    public static function listByCustomer(int $customerId) {
        $sql = "SELECT * FROM reservations
                WHERE customer_id = ?
                ORDER BY reservation_date DESC";
        $stmt = Db::conn()->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        return $stmt->get_result(); // mysqli_result|false
    }

    public static function listAll() {
        return Db::conn()->query(
            "SELECT * FROM reservations ORDER BY reservation_date DESC"
        );
    }

    public static function setStatus(int $id, string $status): bool {
        $status = in_array($status, ['pending','confirmed','cancelled'], true) ? $status : 'pending';
        $stmt = Db::conn()->prepare("UPDATE reservations SET status=? WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
}
/** ===================== CUSTOMER (KHÁCH HÀNG) ===================== */
final class Customer {
    public static function byEmail(string $email): ?array {
        $stmt = Db::conn()->prepare("SELECT * FROM customers WHERE email=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("s",$email);
        $stmt->execute(); $r=$stmt->get_result();
        return $r? $r->fetch_assoc() : null;
    }

    public static function byId(int $id): ?array {
        $stmt = Db::conn()->prepare("SELECT * FROM customers WHERE id=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i",$id);
        $stmt->execute(); $r=$stmt->get_result();
        return $r? $r->fetch_assoc() : null;
    }

    public static function setPassword(int $id, string $plain): bool {
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $stmt = Db::conn()->prepare("UPDATE customers SET password_hash=? WHERE id=?");
        if (!$stmt) return false;
        $stmt->bind_param("si",$hash,$id);
        return $stmt->execute();
    }

    // tạo tài khoản khách
    public static function create(string $fullName, string $email, string $phone, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql  = "INSERT INTO customers (full_name, email, phone, password_hash, created_at)
                 VALUES (?,?,?,?, NOW())";
        $stmt = Db::conn()->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ssss", $fullName, $email, $phone, $hash);
        return $stmt->execute(); // trùng email -> false (errno 1062)
    }
}

/** ===================== ADMIN USER (NHÂN SỰ QUẢN TRỊ) ===================== */
final class AdminUser {
    public static function byId(int $id): ?array {
        $stmt = Db::conn()->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i",$id);
        $stmt->execute(); $r=$stmt->get_result();
        return $r? $r->fetch_assoc() : null;
    }
}

/** ===================== PASSWORD RESET (QUÊN MẬT KHẨU) ===================== */
final class PasswordReset {
    public static function create(string $email, int $ttlMinutes = 60): ?string {
        $token   = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + $ttlMinutes * 60);
        $stmt = Db::conn()->prepare("INSERT INTO password_resets(email, token, expires_at) VALUES(?,?,?)");
        if (!$stmt) return null;
        $stmt->bind_param("sss",$email,$token,$expires);
        return $stmt->execute() ? $token : null;
    }

    public static function verify(string $email, string $token): bool {
        $stmt = Db::conn()->prepare(
          "SELECT 1 FROM password_resets
            WHERE email=? AND token=? AND used_at IS NULL AND expires_at > NOW()
            LIMIT 1"
        );
        if (!$stmt) return false;
        $stmt->bind_param("ss",$email,$token);
        $stmt->execute(); $r=$stmt->get_result();
        return (bool)($r && $r->num_rows);
    }

    public static function consume(string $email, string $token): void {
        $stmt = Db::conn()->prepare("UPDATE password_resets SET used_at=NOW() WHERE email=? AND token=?");
        if ($stmt) {
            $stmt->bind_param("ss",$email,$token);
            $stmt->execute();
        }
    }

    public static function purgeExpired(): void {
        Db::conn()->query("DELETE FROM password_resets WHERE expires_at <= NOW() OR used_at IS NOT NULL");
    }
}

/** ===================== PROMOTION (KHUYẾN MÃI) ===================== */
final class Promotion {
    public static function priceForItem(int $itemId, float $basePrice): float {
        $sql =
          "SELECT p.type, p.value
             FROM promotions p
             JOIN promotion_items pi ON pi.promotion_id = p.id
            WHERE p.active=1
              AND p.starts_at <= NOW() AND p.ends_at >= NOW()
              AND pi.menu_item_id = ?
            ORDER BY p.id DESC LIMIT 1";
        $stmt = Db::conn()->prepare($sql);
        if (!$stmt) return $basePrice;
        $stmt->bind_param("i",$itemId);
        if (!$stmt->execute()) return $basePrice;
        $r = $stmt->get_result();
        if (!$r || !$row = $r->fetch_assoc()) return $basePrice;

        $value = (float)$row['value'];
        if ($row['type']==='percent') {
            $price = $basePrice * (1 - max(0,min(100,$value))/100);
        } else {
            $price = max(0, $basePrice - $value);
        }
        return round($price, 2);
    }
}
