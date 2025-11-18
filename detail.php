<?php
// detail.php
declare(strict_types=1);

include 'includes/db.php';

use App\Controllers\MenuController;

$ctrl  = new MenuController();
$data  = $ctrl->detailWithPromotion(); // ← ĐỔI TÊN METHOD

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
      // $item là mảng trả về từ Menu::getItemWithPromotion()
      $name     = htmlspecialchars($item['item']['name'] ?? 'Món ăn');
      $desc     = nl2br(htmlspecialchars($item['item']['description'] ?? ''));
      $image    = htmlspecialchars($item['item']['image_url'] ?? '');
      $category = htmlspecialchars($item['item']['category'] ?? '');
      
      $hasPromotion = !empty($item['has_promotion']);
      $finalPrice = number_format($item['final_price'] ?? 0);
      $originalPriceFormatted = number_format($item['original_price'] ?? 0);
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
            <?php if ($hasPromotion): ?>
              <span>Giá gốc: <span class="original-price"><?= $originalPriceFormatted ?> đ</span></span>
              <strong class="final-price"><?= $finalPrice ?> đ <span class="badge-sale-detail">Sale</span></strong>            <?php else: ?>
              <span>Giá</span>
              <strong><?= $finalPrice ?> đ</strong>
            <?php endif; ?>
          </div>
          <a href="menu.php" class="btn-back">Quay lại</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>