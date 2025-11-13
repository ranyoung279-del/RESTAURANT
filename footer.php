<?php
// footer.php (dùng chung SettingController)
require_once __DIR__ . '/includes/db.php';

use App\Controllers\SettingController;

$controller = new SettingController();
$settings   = $controller->getSetting(); // trả về mảng settings

// Parse social links JSON
$socialLinks = json_decode($settings['social_links'] ?? '{}', true) ?: [];

// Map tên hiển thị
$platformNames = [
  'facebook'  => 'Facebook',
  'instagram' => 'Instagram',
  'tiktok'    => 'TikTok',
];
?>
<footer>
  <div class="footer-container">
    <div class="footer-section contact">
      <h3>Liên hệ</h3>
      <p>Điện thoại:
        <a href="tel:<?= htmlspecialchars($settings['phone'] ?? '') ?>">
          <?= htmlspecialchars($settings['phone'] ?? 'Chưa cập nhật') ?>
        </a>
      </p>
      <p>Email:
        <a href="mailto:<?= htmlspecialchars($settings['email'] ?? '') ?>">
          <?= htmlspecialchars($settings['email'] ?? 'Chưa cập nhật') ?>
        </a>
      </p>
      <p>Giờ mở cửa: <?= htmlspecialchars($settings['open_hours'] ?? 'Chưa cập nhật') ?></p>
    </div>

    <div class="footer-section social-links">
      <h3>Mạng xã hội</h3>
      <?php if (!empty($socialLinks)): ?>
        <?php foreach ($socialLinks as $platform => $url): ?>
          <?php
            if (!is_string($url) || $url === '') continue;
            $displayName = $platformNames[$platform] ?? ucfirst($platform);
            $displayUrl  = preg_replace('#^https?://#', '', rtrim($url, '/'));
          ?>
          <p>
            <?= htmlspecialchars($displayName) ?>:
            <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer">
              <?= htmlspecialchars($displayUrl) ?>
            </a>
          </p>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Chưa cập nhật</p>
      <?php endif; ?>
    </div>
  </div>

  <p class="copy">&copy; 2025 WENZHU. Bản quyền thuộc về chúng tôi.</p>
</footer>
