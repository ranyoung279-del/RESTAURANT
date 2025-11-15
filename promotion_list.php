<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/classes/db.php';

try {
    $conn = \App\Db::conn();
} catch (Throwable $e) {
    echo "<p style='color:red'>Lỗi kết nối DB: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Lấy danh sách món ăn đang có khuyến mại (có promo_id và promo_price)
try {
    $res = $conn->query("SELECT * FROM menu_items WHERE promo_id IS NOT NULL ORDER BY id DESC");
    $menuItems = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $menuItems[] = $r;
        }
    }
} catch (Throwable $e) {
    $menuItems = [];
}

// Tính toán thống kê
$totalPromo = count($menuItems);
$totalDiscount = 0;
$activePromo = 0;

foreach ($menuItems as $m) {
    $price = (float)($m['price'] ?? 0);
    $promoPrice = (float)($m['promo_price'] ?? 0);
    if ($promoPrice > 0 && $promoPrice < $price) {
        $totalDiscount += ($price - $promoPrice);
        $activePromo++;
    }
}

// Include header và sidebar
$headerFile = __DIR__ . '/header.php';
$sidebarFile = __DIR__ . '/sidebar.php';

if (file_exists($headerFile)) {
    ob_start();
    include $headerFile;
    $headerHtml = ob_get_clean();
    echo $headerHtml;
    if (file_exists($sidebarFile)) include $sidebarFile;
} else {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Quản lý Khuyến mại</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body>';
    if (file_exists($sidebarFile)) include $sidebarFile;
    echo '<main>';
}
?>

<style>
/* Layout cố định cho sidebar và content - GIỐNG ADD */
body {
    margin: 0;
    padding: 0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

header,
.admin-header,
.top-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 80px;
    background: #1a1f28;
    z-index: 1001;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.admin-sidebar,
.admin-menu,
nav.admin-menu {
    position: fixed;
    left: 0;
    top: 80px;
    bottom: 0;
    width: 220px;
    background: #1a1f28;
    color: white;
    overflow-y: auto;
    z-index: 1000;
    padding: 1rem;
}

.admin-sidebar a,
.admin-menu a,
nav.admin-menu a {
    display: block;
    padding: 20px 25px;
    color: white;
    text-decoration: none;
    transition: all 0.15s ease;
    font-size: 15px;
    font-weight: 400;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.admin-sidebar a:hover,
.admin-menu a:hover,
nav.admin-menu a:hover {
    background: rgba(255, 255, 255, 0.1);
    padding-left: 28px;
}

.admin-sidebar a.active,
.admin-menu a.active,
nav.admin-menu a.active {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

#page-content {
    margin-left: 220px;
    margin-top: 80px;
    padding: 20px;
    min-height: calc(100vh - 80px);
    background: #ecf0f1;
}

/* Header giống ADD */
.page-header-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 15px;
}

.page-title-group h2 {
    font-weight: 700;
    margin: 0 0 6px 0;
    font-size: 24px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-subtitle {
    color: #6b7280;
    font-size: 14px;
    margin: 0;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-card.secondary {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    box-shadow: 0 4px 12px rgba(240, 147, 251, 0.3);
}

.stat-card.success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
}

.stat-card-icon {
    font-size: 28px;
    margin-bottom: 8px;
}

.stat-card-label {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card-value {
    font-size: 24px;
    font-weight: 700;
}

/* Content Card - GIỐNG ADD */
.content-card {
    background: #fff;
    border-radius: 12px;
    padding: 26px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Buttons - GIỐNG ADD */
.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 28px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: #0d9488;
    color: white;
    border: 2px solid #0d9488;
}

.btn-primary:hover {
    background: #0f766e;
    border-color: #0f766e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}

.btn-sm {
    padding: 6px 14px;
    font-size: 13px;
    gap: 4px;
}

.btn-outline-secondary {
    background: white;
    color: #6c757d;
    border: 1px solid #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-1px);
}

.btn-outline-danger {
    background: white;
    color: #dc3545;
    border: 1px solid #dc3545;
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
    transform: translateY(-1px);
}

/* Table Styles */
.table-container {
    margin-top: 20px;
    overflow-x: auto;
}

.table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
}

.table thead {
    background: #f8f9fa;
}

.table th {
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 12px;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
    color: #495057;
}

.table td {
    padding: 14px 12px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background: #f8f9fa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.image-placeholder {
    width: 60px;
    height: 60px;
    background: #e9ecef;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    font-size: 24px;
}

.price-original {
    color: #6c757d;
    text-decoration: line-through;
    font-size: 13px;
    display: block;
}

.price-promo {
    color: #dc3545;
    font-weight: 600;
    font-size: 15px;
    display: block;
}

.price-saved {
    color: #28a745;
    font-weight: 600;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 20px;
    margin-bottom: 8px;
    color: #495057;
    font-weight: 600;
}

.empty-state p {
    margin-bottom: 20px;
    color: #6c757d;
}

/* Responsive - GIỐNG ADD */
@media (max-width: 900px) {
    header,
    .admin-header,
    .top-bar {
        position: relative;
        height: auto;
        padding: 15px 20px;
    }
    
    .admin-sidebar,
    .admin-menu,
    nav.admin-menu {
        position: static;
        width: 100%;
        height: auto;
        top: 0;
    }
    
    #page-content {
        margin-left: 0;
        margin-top: 0;
        padding: 15px;
    }
    
    .page-header-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .table {
        font-size: 13px;
    }
    
    .table th,
    .table td {
        padding: 10px 8px;
    }
    
    .content-card {
        padding: 20px;
    }
}
</style>

<div id="page-content">
    <!-- Header giống ADD -->
    <div class="page-header-section">
        <div class="page-title-group">
            <h2>
                <span>🎉</span>
                Quản lý khuyến mại
            </h2>
            <p class="page-subtitle">Quản lý các chương trình khuyến mại và giảm giá cho món ăn</p>
        </div>
        <div class="action-buttons">
            <a href="promotion_add.php" class="btn btn-primary">
                ➕ Tạo khuyến mại mới
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-card-icon">🍽️</div>
            <div class="stat-card-label">Món đang giảm giá</div>
            <div class="stat-card-value"><?= $totalPromo ?></div>
        </div>
        <div class="stat-card secondary">
            <div class="stat-card-icon">💰</div>
            <div class="stat-card-label">Tổng tiết kiệm</div>
            <div class="stat-card-value"><?= number_format($totalDiscount, 0, ',', '.') ?>₫</div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-icon">📊</div>
            <div class="stat-card-label">Đang hoạt động</div>
            <div class="stat-card-value"><?= $activePromo ?></div>
        </div>
    </div>

    <!-- Content Card giống ADD -->
    <div class="content-card">
        <?php if (empty($menuItems)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">🎁</div>
                <h3>Chưa có khuyến mại nào</h3>
                <p>Bắt đầu tạo chương trình khuyến mại đầu tiên của bạn để tăng doanh số!</p>
                <a href="promotion_add.php" class="btn btn-primary">
                    ➕ Tạo khuyến mại ngay
                </a>
            </div>
        <?php else: ?>
            <!-- Table hiển thị danh sách -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Tên món ăn</th>
                            <th style="width: 100px;">Hình ảnh</th>
                            <th style="width: 120px;">Giá gốc</th>
                            <th style="width: 120px;">Giá khuyến mại</th>
                            <th style="width: 100px;">Tiết kiệm</th>
                            <th style="width: 180px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach($menuItems as $m): ?>
                            <?php 
                                $price = (float)($m['price'] ?? 0);
                                $promoPrice = (float)($m['promo_price'] ?? 0);
                                $saved = $price - $promoPrice;
                                $percentSaved = $price > 0 ? round(($saved / $price) * 100) : 0;
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($m['name'] ?? '') ?></strong>
                                    <?php if ($percentSaved > 0): ?>
                                        <br><span class="badge badge-success">-<?= $percentSaved ?>%</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($m['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($m['image_url']) ?>" alt="<?= htmlspecialchars($m['name']) ?>" class="product-image">
                                    <?php else: ?>
                                        <div class="image-placeholder">📷</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="price-original"><?= number_format($price, 0, ',', '.') ?>₫</span>
                                </td>
                                <td>
                                    <?php if ($promoPrice > 0): ?>
                                        <span class="price-promo"><?= number_format($promoPrice, 0, ',', '.') ?>₫</span>
                                    <?php else: ?>
                                        <span style="color:#6c757d;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($saved > 0): ?>
                                        <span class="price-saved">-<?= number_format($saved, 0, ',', '.') ?>₫</span>
                                    <?php else: ?>
                                        <span style="color:#6c757d;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                        <a href="promotion_edit.php?id=<?= urlencode($m['id']) ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Chỉnh sửa khuyến mại">
                                            ✏️ Sửa
                                        </a>
                                        <a href="promotion_delete.php?id=<?= urlencode($m['id']) ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Bạn có chắc muốn xóa khuyến mại cho món \"<?= htmlspecialchars($m['name']) ?>\"?')"
                                           title="Xóa khuyến mại">
                                            🗑️ Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Footer -->
            <div style="margin-top:20px;padding-top:20px;border-top:2px solid #ecf0f1;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
                <div style="color:#6c757d;font-size:14px;">
                    Hiển thị <strong><?= $totalPromo ?></strong> món đang có khuyến mại
                </div>
                <div style="text-align:right;">
                    <div style="font-size:13px;color:#6c757d;margin-bottom:4px;">Tổng giá trị giảm</div>
                    <div style="font-size:20px;font-weight:700;color:#28a745;">
                        <?= number_format($totalDiscount, 0, ',', '.') ?>₫
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$footerFile = __DIR__ . '/footer.php';
if (file_exists($footerFile)) include $footerFile;
else echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script></body></html>';
?>