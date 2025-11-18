<?php
include 'includes/db.php';

use App\Controllers\PromotionController;

$ctrl  = new PromotionController();
$items = $ctrl->listAvailable(); // mysqli_result
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>WENZHU - Bake with love</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="promotions-page">
    <div class="promotions-container">
        <div class="page-header">
            <h1>🎉 MERRY CHRISTMAS</h1>
            <p>Nhận ngay các ưu đãi đặc biệt dành cho bạn</p>
        </div>
        <?php if ($items->num_rows > 0): ?>
            <div class="promotions-grid">
                <?php while ($promo = $items->fetch_assoc()): ?>
                <div class="promotion-card">
                    <div class="promotion-content">
                        <div class="discount-badge">
                            <?php 
                            if ($promo['discount_type'] === 'percent') {
                                echo '-' . number_format($promo['discount_value'], 0) . '%';
                            } else {
                                echo '-' . number_format($promo['discount_value'], 0) . 'đ';
                            }
                            ?>
                        </div>  
                        
                        <h3 class="promotion-title"><?php echo htmlspecialchars($promo['title']); ?></h3>
                        
                        <?php if ($promo['description']): ?>
                            <p class="promotion-description"><?php echo htmlspecialchars($promo['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="coupon-section">
                            <div class="coupon-label">Mã khuyến mãi</div>
                            <div class="coupon-code">
                                <span><?php echo htmlspecialchars($promo['coupon_code']); ?></span>
                            </div>
                        </div>
                        
                        <div class="promotion-info">
                            <?php if ($promo['start_at']): ?>
                                <div class="info-item">
                                    <div class="info-label">Bắt đầu</div>
                                    <div class="info-value"><?php echo date('d/m/Y', strtotime($promo['start_at'])); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($promo['end_at']): ?>
                                <div class="info-item">
                                    <div class="info-label">Kết thúc</div>
                                    <div class="info-value"><?php echo date('d/m/Y', strtotime($promo['end_at'])); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <div class="info-label">Giảm giá</div>
                                <div class="info-value">
                                    <?php 
                                    if ($promo['discount_type'] === 'percent') {
                                        echo number_format($promo['discount_value'], 0) . '%';
                                    } else {
                                        echo number_format($promo['discount_value'], 0) . 'đ';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-promotions">
                <h2>Hiện chưa có khuyến mãi nào</h2>
                <p>Vui lòng quay lại sau để nhận những ưu đãi hấp dẫn!</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    function copyCoupon(code) {
        navigator.clipboard.writeText(code).then(function() {
            const toast = document.getElementById('toast');
            toast.style.display = 'block';
            
            setTimeout(function() {
                toast.style.display = 'none';
            }, 2000);
        }).catch(function(err) {
            alert('Mã khuyến mãi: ' + code);
        });
    }
</script>
<?php include_once __DIR__ . '/footer.php'; ?>
</body>
</html>