<?php
// reservation.php (Front, gọi Controller)
require_once __DIR__ . '/includes/db.php';

use App\Auth;
use App\Controllers\ReservationController;

Auth::start();

// Nếu chưa đăng nhập → hiện đúng UI cũ
if (empty($_SESSION['customer_id'])) {
    include 'header.php';
    echo '
    <section class="reservation-section">
        <h2> Đặt bàn </h2>
        <p style="color:#000; text-align:center; font-size:1.1rem; margin-top:20px;">
            Bạn cần <a href="login_cus.php" style="color:#970000; text-decoration:none; font-weight:bold;">đăng nhập</a> để đặt bàn.
        </p>
    </section>';
    include 'footer.php';
    exit;
}

include 'header.php';

$message = '';
$error   = '';

$customer_name  = $_SESSION['customer_name']  ?? '';
$customer_phone = $_SESSION['customer_phone'] ?? '';

$ctrl = new ReservationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $ctrl->createForCustomer($_POST);
    if ($ok) {
        $message = ' Đặt bàn thành công! Chúng tôi sẽ liên hệ lại với bạn sớm nhất.';
    } else {
        $error   = ' Đã xảy ra lỗi khi đặt bàn, vui lòng kiểm tra lại thông tin và thử lại.';
    }
}
?>

<section class="reservation-section">
  <h2>📅 Đặt bàn</h2>

  <?php if ($message): ?>
    <p class="success-message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="error-message"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="reservation.php" class="reservation-form">
    <label>Họ tên:</label>
    <input type="text" name="full_name" required value="<?= htmlspecialchars($customer_name) ?>">

    <label>Số điện thoại:</label>
    <input type="tel" name="phone" required pattern="[0-9+]{7,15}" value="<?= htmlspecialchars($customer_phone) ?>">

    <label>Ngày giờ đặt bàn:</label>
    <input type="datetime-local" name="reservation_date" required>

    <label>Loại bàn:</label>
    <select name="table_type" required>
      <option value="Bàn thường">Bàn thường</option>
      <option value="Bàn VIP">Bàn VIP</option>
    </select>

    <label>Số lượng người:</label>
    <input type="number" name="people_count" min="1" value="1" required>

    <label>Ghi chú (nếu có):</label>
    <textarea name="note" rows="3"></textarea>

    <button type="submit">Đặt bàn</button>
  </form>
</section>

<?php include 'footer.php'; ?>
