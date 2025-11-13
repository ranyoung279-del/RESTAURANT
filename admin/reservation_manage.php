<?php
// admin/reservation_manage.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';

use App\Auth;
use App\Csrf;
use App\Components\AdminLayout;
use App\Controllers\ReservationController;

Auth::guardAdmin();
$ctrl = new ReservationController();
$result = $ctrl->handleManage();

$message = $result['message'];
$error = $result['error'];

// Lấy danh sách và CSRF token
$list = $ctrl->listAll();
$csrf = Csrf::token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý đặt bàn</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>

<?php AdminLayout::header(); ?>

<div class="admin-dashboard">
  <?php AdminLayout::sidebar(); ?>

  <main class="admin-overview">
    <h2>📅 Danh sách đặt bàn</h2>

    <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert error"><?= htmlspecialchars($error)   ?></div><?php endif; ?>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Họ tên</th>
            <th>Điện thoại</th>
            <th>Thời gian</th>
            <th>Số người</th>
            <th>Loại bàn</th>
            <th>Ghi chú</th>
            <th>Trạng thái</th>
            <th>Xác thực</th>
            <th>Huỷ</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($list && $list->num_rows): ?>
          <?php while ($r = $list->fetch_assoc()): ?>
            <?php
              $id     = (int)$r['id'];
              $status = (string)$r['status'];
              $isConfirmed = ($status === 'confirmed');
              $timeStr = $r['reservation_date'] ? date('d/m/Y H:i', strtotime($r['reservation_date'])) : '';
            ?>
            <tr>
              <td><?= htmlspecialchars($r['full_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
              <td><?= htmlspecialchars($timeStr) ?></td>
              <td><?= (int)($r['people_count'] ?? 0) ?></td>
              <td><?= htmlspecialchars($r['table_type'] ?? '') ?></td>
              <td><?= htmlspecialchars($r['note'] ?? '') ?></td>
              <td class="status-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></td>

              <!-- Toggle xác nhận -->
              <td style="text-align:center;">
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf" value="<?= $csrf ?>">
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <input type="hidden" name="action" value="<?= $isConfirmed ? 'pending' : 'confirmed' ?>">
                  <input type="checkbox" <?= $isConfirmed ? 'checked' : '' ?> onchange="this.form.submit()">
                </form>
              </td>

              <!-- Huỷ -->
              <td style="text-align:center;">
                <?php if ($status !== 'cancelled'): ?>
                  <form method="post" onsubmit="return confirm('Xác nhận huỷ đặt bàn này?')">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="action" value="cancelled">
                    <button type="submit" class="btn danger" style="padding:4px 10px;">Huỷ</button>
                  </form>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" style="text-align:center;">Chưa có đặt bàn.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?php AdminLayout::footer(); ?>
</body>
</html>
