<?php
include 'includes/db.php';
use App\Controllers\HomeController;

$controller = new HomeController();
$data = $controller->index();
$home  = $data['home'];
$deals = $data['deals'];

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>WENZHU - Bake with love</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<?php include 'header.php'; ?>

<!-- Banner -->
<div class="banner">
  <?php if (!empty($home['banner_image'])): ?>
    <img src="admin/<?= htmlspecialchars($home['banner_image']) ?>" alt="Banner món ăn" loading="lazy">
  <?php endif; ?>
</div>

<section class="hot-deals">
  <h2>Hot Deals</h2>
  <div class="product-list">
    <?php if ($deals instanceof mysqli_result && $deals->num_rows): ?>
      <?php while ($row = $deals->fetch_assoc()): ?>
        <div class="product">
          <img src="admin/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
          <h3><?= htmlspecialchars($row['name']) ?></h3>
          <p>Giá: <?= number_format($row['price']) ?> đ</p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Không có sản phẩm nào để hiển thị.</p>
    <?php endif; ?>
  </div>
</section>

<section class="about">
  <h2><?= htmlspecialchars($home['title'] ?? 'Về chúng tôi') ?></h2>
  <p><?= nl2br(htmlspecialchars($home['description'] ?? 'Thông tin chưa được cập nhật.')) ?></p>
  <?php 
  $images = [];
  if (!empty($home['intro_images'])) {
      $images = preg_split('/\r\n|\r|\n/', trim($home['intro_images']));
      $images = array_filter(array_map('trim', $images));
  } elseif (!empty($home['intro_image'])) {
      $images = [ $home['intro_image'] ];
  }
  ?>
  <?php if ($images): ?>
  <div class="about-images-container">
      <?php foreach ($images as $img): ?>
          <img src="admin/<?= htmlspecialchars($img) ?>" alt="Ảnh giới thiệu" loading="lazy">
      <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
