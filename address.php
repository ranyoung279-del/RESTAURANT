<?php
include 'includes/db.php';

use App\Controllers\SettingController;

// Gọi controller
$controller = new SettingController();
$restaurant = $controller->getSetting();

include 'header.php';
?>

<section class="location-section">
  <h2>Địa điểm của <?= htmlspecialchars($restaurant['restaurant_name'] ?? 'WENZHU') ?></h2>

  <div class="address-info">
    <h3>Địa chỉ</h3>
    <p><?= nl2br(htmlspecialchars($restaurant['address'] ?? 'Chưa cập nhật địa chỉ')) ?></p>

    <h3>Giờ mở cửa</h3>
    <p><?= nl2br(htmlspecialchars($restaurant['open_hours'] ?? 'Chưa cập nhật giờ mở cửa')) ?></p>

    <h3>Liên hệ</h3>
    <p>Điện thoại:
      <a href="tel:<?= htmlspecialchars($restaurant['phone'] ?? '') ?>">
        <?= htmlspecialchars($restaurant['phone'] ?? 'Chưa cập nhật') ?>
      </a>
    </p>
    <p>Email:
      <a href="mailto:<?= htmlspecialchars($restaurant['email'] ?? '') ?>">
        <?= htmlspecialchars($restaurant['email'] ?? 'Chưa cập nhật') ?>
      </a>
    </p>
  </div>

  <div class="social-links">
    <h3>Mạng xã hội</h3>
    <?php
      $platformNames = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok'    => 'TikTok',
      ];
      $socialLinks = json_decode($restaurant['social_links'] ?? '{}', true);

      if (is_array($socialLinks) && !empty($socialLinks)) {
        foreach ($socialLinks as $platform => $url) {
          if (!is_string($url) || $url === '') continue;
          $displayName = $platformNames[$platform] ?? ucfirst($platform);
          $displayUrl  = preg_replace('#^https?://#', '', rtrim($url, '/'));
          echo '<p>' . htmlspecialchars($displayName) . ': '
             . '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener noreferrer">'
             . htmlspecialchars($displayUrl) . '</a></p>';
        }
      } else {
        echo '<p>Chưa cập nhật</p>';
      }
    ?>
  </div>

  <div class="map-container">
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.1234567890!2d106.700000!3d10.776123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f1234567890%3A0xabcdef1234567890!2zMTIzIMSQxrDhu51uZyBCw6FuaCBNaSwgUXXhuq1uIDE!5e0!3m2!1svi!2s!4v1234567890123"
      width="100%"
      height="400"
      style="border:0;"
      allowfullscreen=""
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade">
    </iframe>
  </div>
</section>

<?php include 'footer.php'; ?>
