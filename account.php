<?php
// account.php (OOP chuẩn với Controller)
require_once __DIR__ . '/includes/db.php';

use App\Auth;
use App\Models\Customer;
use App\Controllers\ReservationController;

Auth::start();

$customerId = $_SESSION['customer_id'] ?? null;
$customer   = null;
$history    = null;

if ($customerId) {
  // Lấy thông tin khách hàng
  $customer = Customer::byId((int)$customerId);

  // Gọi controller để lấy lịch sử đặt bàn
  $ctrl = new ReservationController();
  $history = $ctrl->listByCustomer((int)$customerId);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tài khoản khách hàng</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="account-page">
<?php include 'header.php'; ?>

<div class="account-container">
  <?php if (!$customerId || !$customer): ?>
    <!-- 🔹 Giao diện khi chưa đăng nhập -->
    <div class="auth-choice">
      <h2>Chào mừng bạn đến với Wenzhu Restaurant 🍽️</h2>
      <p>Hãy đăng nhập hoặc đăng ký để theo dõi lịch sử đặt bàn của bạn.</p>

      <div class="auth-buttons">
        <a href="login_cus.php" class="btn-auth login">Đăng nhập</a>
        <a href="registration.php" class="btn-auth register">Đăng ký</a>
      </div>
    </div>
  <?php else: ?>
    <!-- 🔹 Khi đã đăng nhập -->
    <h1> <span class="highlight"><?= htmlspecialchars($customer['full_name'] ?? '') ?>!</span></h1>

    <section class="profile-section">
      <h3>Thông tin cá nhân</h3>
      <table class="info-table">
        <tr><th>Họ tên:</th><td><?= htmlspecialchars($customer['full_name'] ?? '') ?></td></tr>
        <tr><th>Email:</th><td><?= htmlspecialchars($customer['email'] ?? '') ?></td></tr>
        <tr><th>Số điện thoại:</th><td><?= htmlspecialchars($customer['phone'] ?? '') ?></td></tr>
        <tr><th>Ngày tham gia:</th>
          <td>
            <?php
              $joined = $customer['created_at'] ?? null;
              echo $joined ? date('d/m/Y H:i', strtotime($joined)) : '—';
            ?>
          </td>
        </tr>
      </table>
    </section>

    <section class="history-section">
      <h3>Lịch sử đặt bàn</h3>
      <input type="text" id="searchInput" placeholder="Tìm kiếm..." onkeyup="filterTable()">
      <table id="historyTable">
        <thead>
          <tr>
            <th>Ngày đặt</th>
            <th>Số người</th>
            <th>Loại bàn</th>
            <th>Ghi chú</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($history && $history->num_rows): ?>
            <?php while ($r = $history->fetch_assoc()): ?>
              <tr>
                <td><?= date('d/m/Y H:i', strtotime($r['reservation_date'])) ?></td>
                <td><?= (int)$r['people_count'] ?></td>
                <td><?= htmlspecialchars($r['table_type'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['note'] ?? '') ?></td>
                <td class="status <?= htmlspecialchars($r['status'] ?? '') ?>">
                  <?= htmlspecialchars(ucfirst($r['status'] ?? '')) ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">Chưa có lịch sử đặt bàn.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  <?php endif; ?>
</div>

<script>
function filterTable() {
  const input  = document.getElementById("searchInput");
  const filter = (input.value || "").toLowerCase();
  const rows   = document.querySelectorAll("#historyTable tbody tr");
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(filter) ? "" : "none";
  });
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>
