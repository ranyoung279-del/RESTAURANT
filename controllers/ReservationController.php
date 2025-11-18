<?php
// controllers/ReservationController.php
namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\Db;
use App\Models\Reservation;

final class ReservationController
{
        public function handleManage(): array
    {
        Auth::guardAdmin();
        
        $message = '';
        $error = '';

        // Cập nhật trạng thái
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
            $ok = $this->updateStatus(
                (int)$_POST['id'],
                (string)$_POST['action'],
                $_POST['csrf'] ?? null
            );
            if ($ok) {
                $message = 'Cập nhật trạng thái thành công.';
            } else {
                $error = 'Phiên không hợp lệ hoặc lỗi hệ thống (CSRF/DB).';
            }
        }

        return [
            'message' => $message,
            'error' => $error
        ];
    }
    /** Front: tạo đặt bàn cho khách đang đăng nhập */
    public function createForCustomer(array $data): bool
    {
        Auth::guardCustomer();

        $customerId = (int)($_SESSION['customer_id'] ?? 0);
        if ($customerId <= 0) return false;
        $fullName  = trim($data['full_name'] ?? '');
        $phone     = trim($data['phone'] ?? '');
        $dtInput   = $data['reservation_date'] ?? '';
        $ts        = $dtInput ? strtotime($dtInput) : false;
        $resDate   = $ts ? date('Y-m-d H:i:s', $ts) : '';
        $tableType = (string)($data['table_type'] ?? 'Bàn thường');
        $people    = (int)($data['people_count'] ?? 1);
        $note      = trim($data['note'] ?? '');

        if ($fullName === '' || $phone === '' || $resDate === '' || $people < 1) {
            return false;
        }

        return Reservation::create([
            'customer_id'      => $customerId,
            'full_name'        => $fullName,
            'phone'            => $phone,
            'reservation_date' => $resDate,
            'people_count'     => $people,
            'table_type'       => $tableType,
            'note'             => $note,
        ]);
    }
    public function listByCustomer(int $customerId)
    {
        return Reservation::listByCustomer($customerId); 
    }
  public function listAll()
    {
        Auth::guardAdmin();
        return Reservation::listAll();
    }
    public function updateStatus(int $id, string $action, ?string $csrf): bool
    {
        Auth::guardAdmin();
        if (!Csrf::check($csrf)) return false;
        if ($id <= 0) return false;
        $validStatuses = ['pending', 'confirmed', 'cancelled'];
        if (!in_array($action, $validStatuses, true)) return false;
        return Reservation::setStatus($id, $action);
    }
}
