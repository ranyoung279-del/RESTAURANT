<?php
// menu_detail.php
declare(strict_types=1);

include 'includes/db.php';

use App\Controllers\MenuController;

$ctrl  = new MenuController();
$data  = $ctrl->handleDetail();

$item  = $data['item'] ?? null;
$error = $data['error'] ?? '';
?>
<?php include 'header.php'; ?>

<section class="menu-detail-section">
  <?php if ($error): ?>
    <div class="menu-detail-card">
      <h2><?= htmlspecialchars($error) ?></h2>
      <p>Có thể món đã bị xoá hoặc tạm ẩn khỏi thực đơn.</p>
      <a href="menu.php" class="btn-back">Quay lại</a>
    </div>
  <?php else: ?>
    <?php
      $name     = htmlspecialchars($item['name'] ?? 'Món ăn');
      $desc     = nl2br(htmlspecialchars($item['description'] ?? ''));
      $priceRaw = isset($item['price']) ? (float)$item['price'] : 0;
      $price    = number_format($priceRaw);
      $image    = htmlspecialchars($item['image_url'] ?? '');
      $category = htmlspecialchars($item['category'] ?? '');
    ?>
    <div class="menu-detail-card">
      <div class="menu-detail-image">
        <?php if ($image): ?>
          <img src="admin/<?= $image ?>" alt="<?= $name ?>">
        <?php else: ?>
          <div class="no-image">Không có hình ảnh</div>
        <?php endif; ?>
      </div>
      <div class="menu-detail-info">
        <span class="menu-detail-category"><?= $category ?: 'Món ăn' ?></span>
        <h1><?= $name ?></h1>
        <p class="menu-detail-desc"><?= $desc ?></p>

        <div class="menu-detail-bottom">
          <div class="menu-detail-price">
            <span>Giá</span>
            <strong><?= $price ?> đ</strong>
          </div>
          <a href="menu.php" class="btn-back"> Quay lại</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
