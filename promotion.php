<?php
// promotion.php — hiển thị món ăn theo khuyến mại

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

// include auth nếu có
$authCandidates = [
    __DIR__ . '/includes/classes/Auth.php',
    __DIR__ . '/includes/classes/auth.php',
];
foreach ($authCandidates as $p) if (file_exists($p)) require_once $p;
if (!class_exists('\App\Auth')) {
    eval(<<<'PHP'
namespace App;
class Auth {
    public static function start(): void { if(session_status()===PHP_SESSION_NONE) session_start(); }
}
PHP
);
}
\App\Auth::start();

// include header
include __DIR__ . '/header.php';

require_once __DIR__ . '/includes/classes/db.php';
$conn = \App\Db::conn();

// lấy khuyến mại active kèm menu_item liên quan
$promos = [];
$sql = "
    SELECT p.*, m.id AS menu_id, m.name AS menu_name, m.price AS menu_price, m.image_url AS menu_image
    FROM promotions p
    INNER JOIN menu_items m ON m.promo_id = p.id
    WHERE p.active = 1
      AND (p.start_at IS NULL OR p.start_at <= NOW())
      AND (p.end_at IS NULL OR p.end_at >= NOW())
    ORDER BY p.start_at DESC, p.created_at DESC
";
$res = $conn->query($sql);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $promos[] = $r;
    }
}

// tính giá sau giảm
function calculateDiscountedPrice($originalPrice, $discountType, $discountValue) {
    if ($discountType === 'percent') return $originalPrice - ($originalPrice * $discountValue / 100);
    return $originalPrice - $discountValue;
}
?>

<style>
.promo-grid { 
  display: grid; 
  grid-template-columns: repeat(3, 1fr); /* 3 cột bằng nhau */
  gap: 16px; /* Khoảng cách giữa các khung nhỏ lại */
}

.promo-card { 
  background: #fff; 
  border-radius: 8px; /* Bo góc nhỏ lại */
  box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
  overflow: hidden; 
  display: flex; 
  flex-direction: column; 
  transition: 0.2s; 
}

.promo-card:hover { 
  transform: translateY(-4px); 
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); 
}

.promo-img { 
  width: 100%; 
  height: 200px; /* Tăng chiều cao ảnh lên */
  object-fit: cover; 
}

.promo-content { 
  padding: 16px; /* Tăng padding */
  flex: 1; 
  display: flex; 
  flex-direction: column; 
  min-height: 280px; /* Đảm bảo chiều cao tối thiểu */
}

.promo-title { 
  font-size: 16px; /* Giảm size chữ tiêu đề */
  font-weight: 700; 
  margin: 0 0 6px 0; 
  color: #1a202c; 
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap; /* Cắt text dài */
}

.promo-desc { 
  font-size: 13px; /* Giảm size mô tả */
  color: #6b7280; 
  margin-bottom: 12px; 
  flex: 1;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3; /* Tăng lên 3 dòng */
  -webkit-box-orient: vertical;
  line-height: 1.5;
}

.price-display { 
  display: flex; 
  flex-direction: column; 
  gap: 4px; 
  margin-bottom: 10px; 
}

.original-price { 
  font-size: 13px; /* Giảm size giá gốc */
  color: #9ca3af; 
  text-decoration: line-through; 
}

.discounted-price { 
  font-size: 18px; /* Giảm size giá giảm */
  font-weight: 700; 
  color: #dc2626; 
  display: flex; 
  align-items: center; 
  gap: 6px; 
}

.discount-badge { 
  background: linear-gradient(135deg, #dc2626, #b91c1c); 
  color: #fff; 
  padding: 2px 6px; /* Giảm padding badge */
  border-radius: 4px; 
  font-size: 11px; /* Giảm size badge */
  font-weight: 700; 
}

.promo-footer { 
  border-top: 1px solid #e5e7eb; 
  padding-top: 10px; 
  margin-top: auto; 
  font-size: 12px; /* Giảm size footer */
  color: #64748b; 
  display: flex; 
  flex-direction: column; 
  gap: 6px; 
}

.coupon-code { 
  background: linear-gradient(135deg, #0d9488, #0f766e); 
  color: #fff; 
  padding: 4px 8px; /* Giảm padding mã */
  border-radius: 4px; 
  font-weight: 700; 
  text-transform: uppercase; 
  font-size: 11px; /* Giảm size mã */
  display: inline-block; 
}

.btn-actions { 
  display: flex; 
  gap: 6px; 
  margin-top: 8px; 
}

.btn-actions .btn {
  font-size: 11px; /* Giảm size button */
  padding: 4px 8px; /* Giảm padding button */
}

/* Responsive cho màn hình nhỏ */
@media (max-width: 1024px) {
  .promo-grid {
    grid-template-columns: repeat(2, 1fr); /* 2 cột trên tablet */
  }
}

@media (max-width: 640px) {
  .promo-grid {
    grid-template-columns: 1fr; /* 1 cột trên mobile */
  }
}
</style>

<div id="page-content" style="padding:24px;">
  <div style="max-width:1200px;margin:0 auto;">
    <h1 style="font-size:32px;font-weight:700;margin-bottom:12px;">🎉 Khuyến mại món ăn</h1>
    <div style="color:#6b7280;margin-bottom:24px;">Danh sách khuyến mại đang diễn ra</div>

    <?php if (empty($promos)): ?>
      <div style="background:#fff;padding:48px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);text-align:center;">
        <div style="font-size:48px;margin-bottom:16px;">📭</div>
        <h3 style="color:#6b7280;font-weight:600;">Hiện chưa có khuyến mại nào</h3>
      </div>
    <?php else: ?>
      <div class="promo-grid">
        <?php 
        foreach ($promos as $p):
          $row = is_object($p)?(array)$p:$p;
          
          $type = $row['discount_type'] ?? 'percent';
          $value = floatval($row['discount_value'] ?? 0);
          $coupon = $row['coupon_code'] ?? '';
          $title = $row['title'] ?? $row['menu_name'] ?? 'Không có tiêu đề';
          $desc = $row['description'] ?? '';
          $start = !empty($row['start_at'])?date('d/m/Y',strtotime($row['start_at'])):null;
          $end = !empty($row['end_at'])?date('d/m/Y',strtotime($row['end_at'])):null;

          $originalPrice = isset($row['menu_price'])?floatval($row['menu_price']):0;
          $discountedPrice = calculateDiscountedPrice($originalPrice,$type,$value);
          
          // Lấy hình ảnh từ menu_items
          $menuImage = $row['menu_image'] ?? '';
          $img = 'https://via.placeholder.com/400x200?text=No+Image';
          
          if (!empty($menuImage)) {
              // Nếu là URL đầy đủ (http/https)
              if (preg_match('/^https?:\/\//i', $menuImage)) {
                  $img = $menuImage;
              } 
              // Nếu là đường dẫn tương đối
              else {
                  // Thử đọc file trực tiếp và convert sang base64
                  $cleanPath = ltrim($menuImage, '/');
                  
                  // Thử nhiều đường dẫn có thể
                  $possiblePaths = [
                      __DIR__ . '/admin/' . $cleanPath,           // C:\xampp\htdocs\RESTAURANTC/admin/uploads/...
                      __DIR__ . '/' . $cleanPath,                 // C:\xampp\htdocs\RESTAURANTC/uploads/...
                      __DIR__ . '/admin/uploads/' . basename($cleanPath),
                  ];
                  
                  $fileFound = false;
                  foreach ($possiblePaths as $testPath) {
                      if (file_exists($testPath) && is_file($testPath)) {
                          // Đọc file và convert sang base64
                          $imageData = file_get_contents($testPath);
                          $finfo = finfo_open(FILEINFO_MIME_TYPE);
                          $mimeType = finfo_file($finfo, $testPath);
                          finfo_close($finfo);
                          
                          $base64 = base64_encode($imageData);
                          $img = "data:{$mimeType};base64,{$base64}";
                          $fileFound = true;
                          break;
                      }
                  }
                  
                  // Nếu không tìm thấy file, dùng đường dẫn thông thường
                  if (!$fileFound) {
                      if (strpos($cleanPath, 'uploads/') === 0) {
                          $img = '/admin/' . $cleanPath;
                      } else {
                          $img = '/' . $cleanPath;
                      }
                  }
              }
          }
        ?>
        <article class="promo-card">
          <!-- DEBUG: Hiển thị URL trong HTML comment -->
          <!-- Image URL: <?= htmlspecialchars($img) ?> -->
          <img src="<?= htmlspecialchars($img) ?>" 
               class="promo-img" 
               alt="<?= htmlspecialchars($row['menu_name'] ?? $title) ?>"
               onload="console.log('✓ Ảnh load thành công:', this.src)"
               onerror="console.error('❌ Ảnh load THẤT BẠI:', this.src); console.log('Trying placeholder...'); this.onerror=null; this.src='https://via.placeholder.com/400x200?text=<?= urlencode($row['menu_name'] ?? 'Error') ?>'">
          <div class="promo-content">
            <h3 class="promo-title"><?= htmlspecialchars($title) ?></h3>
            <div class="promo-desc"><?= nl2br(htmlspecialchars($desc)) ?></div>
            <div class="price-display">
              <div class="original-price"><?= number_format($originalPrice,0,',','.') ?> ₫</div>
              <div class="discounted-price">
                <?= number_format($discountedPrice,0,',','.') ?> ₫
                <span class="discount-badge">-<?= $type==='percent'?rtrim(rtrim(number_format($value,2),'0'),'.').'%':number_format($value,0,',','.').' ₫' ?></span>
              </div>
            </div>
            <div class="promo-footer">
              <?php if ($coupon): ?>
                <div>Mã giảm giá: <span id="coupon-<?= htmlspecialchars($row['menu_id']) ?>" class="coupon-code"><?= htmlspecialchars($coupon) ?></span></div>
              <?php else: ?>
                <div>✓ Áp dụng tự động khi thanh toán</div>
              <?php endif; ?>
              <?php if ($start || $end): ?>
                <div>⏰ <?= $start?'Từ <strong>'.$start.'</strong>':'' ?> <?= $end?'đến <strong>'.$end.'</strong>':'' ?></div>
              <?php endif; ?>
              <div class="btn-actions">
                <?php if ($coupon): ?>
                  <button class="btn btn-sm btn-outline-secondary btn-copy" data-target="coupon-<?= htmlspecialchars($row['menu_id']) ?>">📋 Sao chép mã</button>
                <?php endif; ?>
                <a href="viewer.php" class="btn btn-sm btn-outline-primary">🛍️ Xem cửa hàng</a>
              </div>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// copy coupon
document.addEventListener('click',function(e){
  var btn=e.target.closest('.btn-copy'); if(!btn)return;
  var id=btn.getAttribute('data-target'); var el=document.getElementById(id); if(!el)return;
  var txt=(el.textContent||el.innerText||'').trim(); if(!txt)return alert('Không có mã để sao chép.');
  if(!navigator.clipboard){
    var ta=document.createElement('textarea'); ta.value=txt; document.body.appendChild(ta); ta.select();
    try{document.execCommand('copy'); btn.innerHTML='✓ Đã sao chép'; setTimeout(()=>btn.innerHTML='📋 Sao chép mã',1500);}catch(e){alert('Không thể sao chép');}
    document.body.removeChild(ta); return;
  }
  navigator.clipboard.writeText(txt).then(function(){btn.innerHTML='✓ Đã sao chép'; setTimeout(()=>btn.innerHTML='📋 Sao chép mã',1500);},function(){alert('Không thể sao chép');});
});
</script>

<?php include __DIR__ . '/footer.php'; ?>