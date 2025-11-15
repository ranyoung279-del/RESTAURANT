<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/classes/db.php';

try {
    $conn = \App\Db::conn();
} catch (Throwable $e) {
    die("<p style='color:red'>Lỗi kết nối DB: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$errors = [];
$success = null;
$old = [
    'title' => '',
    'description' => '',
    'discount_type' => 'percent',
    'discount_value' => '',
    'coupon_code' => '',
    'start_at' => '',
    'end_at' => '',
    'active' => 1,
    'menu_items' => [],
    'image_url' => ''
];

// ✅ Lấy danh sách TẤT CẢ món ăn (không điều kiện gì)
$menuItems = [];
$res = $conn->query("SELECT id, name, price FROM menu_items ORDER BY name ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $menuItems[] = $r;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['title'] = trim($_POST['title'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['discount_type'] = $_POST['discount_type'] ?? 'percent';
    $old['discount_value'] = $_POST['discount_value'] ?? '';
    $old['coupon_code'] = trim($_POST['coupon_code'] ?? '');
    $old['start_at'] = $_POST['start_at'] ?? '';
    $old['end_at'] = $_POST['end_at'] ?? '';
    $old['active'] = isset($_POST['active']) ? 1 : 0;
    $old['menu_items'] = $_POST['menu_items'] ?? [];

    if ($old['title'] === '') $errors[] = "Tiêu đề khuyến mại không được để trống.";
    if (!is_numeric($old['discount_value'])) $errors[] = "Giá trị giảm giá phải là số.";

    // Xử lý upload hình ảnh
    $uploadedImagePath = '';
    if (isset($_FILES['promo_image']) && $_FILES['promo_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/promotions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['promo_image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $ext = pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION);
            $filename = 'promo_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $targetPath)) {
                $uploadedImagePath = 'uploads/promotions/' . $filename;
            } else {
                $errors[] = "Không thể lưu hình ảnh.";
            }
        } else {
            $errors[] = "Chỉ chấp nhận file ảnh JPG, PNG, GIF, WEBP.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO promotions
            (title, description, discount_type, discount_value, coupon_code, start_at, end_at, active, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssdsssss",
            $old['title'],
            $old['description'],
            $old['discount_type'],
            $old['discount_value'],
            $old['coupon_code'],
            $old['start_at'],
            $old['end_at'],
            $old['active'],
            $uploadedImagePath
        );
        if ($stmt->execute()) {
            $promoId = $stmt->insert_id;
            if (!empty($old['menu_items'])) {
                // Cập nhật promo_id và tính promo_price cho từng món
                foreach ($old['menu_items'] as $menuId) {
                    $menuId = (int)$menuId;
                    // Lấy giá gốc
                    $priceRes = $conn->query("SELECT price FROM menu_items WHERE id = {$menuId}");
                    if ($priceRes && $row = $priceRes->fetch_assoc()) {
                        $originalPrice = (float)$row['price'];
                        $discountValue = (float)$old['discount_value'];
                        
                        // Tính giá sau giảm
                        if ($old['discount_type'] === 'percent') {
                            $promoPrice = $originalPrice - ($originalPrice * $discountValue / 100);
                        } else {
                            $promoPrice = $originalPrice - $discountValue;
                        }
                        $promoPrice = max(0, $promoPrice); // Không âm
                        
                        // Cập nhật vào database
                        $conn->query("UPDATE menu_items SET promo_id = {$promoId}, promo_price = {$promoPrice} WHERE id = {$menuId}");
                    }
                }
            }
            $success = "Khuyến mại đã được tạo thành công (ID = {$promoId})";
            $old = ['title'=>'','description'=>'','discount_type'=>'percent','discount_value'=>'','coupon_code'=>'','start_at'=>'','end_at'=>'','active'=>1,'menu_items'=>[],'image_url'=>''];
        } else {
            $errors[] = "Lỗi khi lưu khuyến mại: " . $stmt->error;
        }
        $stmt->close();
    }
}

// include header + sidebar
$headerFile = __DIR__ . '/header.php';
$sidebarFile = __DIR__ . '/sidebar.php';

if (file_exists($headerFile)) {
    ob_start();
    include $headerFile;
    $headerHtml = ob_get_clean();
    echo $headerHtml;
    if (file_exists($sidebarFile)) include $sidebarFile;
} else {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Thêm Khuyến mại</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body>';
    if (file_exists($sidebarFile)) include $sidebarFile;
    echo '<main>';
}
?>

<style>
/* ... giữ nguyên toàn bộ CSS ... */
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

#page-content {
    margin-left: 220px;
    margin-top: 80px;
    padding: 20px;
    min-height: calc(100vh - 80px);
    background: #ecf0f1;
}

.content-card {
    background: #fff;
    border-radius: 12px;
    padding: 26px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 24px;
}

.form-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #ecf0f1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.mb-3 label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #0d9488;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
}

.form-control[multiple] {
    background: #fff;
    min-height: 200px;
    padding: 8px;
}

.form-control[multiple] option {
    padding: 10px 12px;
    border-radius: 4px;
    margin-bottom: 4px;
}

.form-control[multiple] option:checked {
    background: #0d9488;
    color: white;
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
}

.btn-primary:hover {
    background: #0f766e;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
}

.alert-success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.required {
    color: #e74c3c;
}

.helper-text {
    font-size: 13px;
    color: #6c757d;
    margin-top: 6px;
}
</style>

<div id="page-content">
    <h2>🎉 Tạo khuyến mại mới</h2>

    <div class="content-card">
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>❌ Lỗi:</strong>
                <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-section">
                <div class="form-section-title">📋 Thông tin cơ bản</div>
                
                <div class="mb-3">
                    <label>Tiêu đề <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($old['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($old['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label>Hình ảnh</label>
                    <input type="file" name="promo_image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">💰 Cấu hình giảm giá</div>
                
                <div class="mb-3">
                    <label>Loại giảm giá</label>
                    <select name="discount_type" class="form-control">
                        <option value="percent" <?= $old['discount_type']==='percent'?'selected':'' ?>>Phần trăm (%)</option>
                        <option value="amount" <?= $old['discount_type']==='amount'?'selected':'' ?>>Số tiền (VNĐ)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Giá trị giảm <span class="required">*</span></label>
                    <input type="number" step="0.01" name="discount_value" 
                           class="form-control" value="<?= htmlspecialchars($old['discount_value']) ?>" required>
                </div>

                <div class="mb-3">
                    <label>Mã giảm giá (Coupon)</label>
                    <input type="text" name="coupon_code" class="form-control" 
                           value="<?= htmlspecialchars($old['coupon_code']) ?>">
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">📅 Thời gian</div>
                
                <div class="mb-3">
                    <label>Ngày bắt đầu</label>
                    <input type="date" name="start_at" class="form-control" 
                           value="<?= htmlspecialchars($old['start_at']) ?>">
                </div>

                <div class="mb-3">
                    <label>Ngày kết thúc</label>
                    <input type="date" name="end_at" class="form-control" 
                           value="<?= htmlspecialchars($old['end_at']) ?>">
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">🍽️ Chọn món ăn áp dụng</div>
                
                <div class="mb-3">
                    <label>Danh sách món ăn (<?= count($menuItems) ?> món)</label>
                    <select name="menu_items[]" class="form-control" multiple size="10">
                        <?php foreach($menuItems as $m): ?>
                            <option value="<?= $m['id'] ?>" 
                                    <?= in_array($m['id'], $old['menu_items'])?'selected':'' ?>>
                                <?= htmlspecialchars($m['name']) ?> - <?= number_format($m['price']) ?>đ
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="helper-text">
                        <?php if (empty($menuItems)): ?>
                            ⚠️ Chưa có món ăn nào. <a href="menu_manage.php">Thêm món ăn</a>
                        <?php else: ?>
                            Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều món
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label>
                    <input type="checkbox" name="active" <?= $old['active']?'checked':'' ?>>
                    Kích hoạt ngay
                </label>
            </div>

            <button type="submit" class="btn btn-primary">💾 Lưu khuyến mại</button>
            <a href="promotion_list.php" class="btn">Hủy</a>
        </form>
    </div>
</div>

<?php
$footerFile = __DIR__ . '/footer.php';
if (file_exists($footerFile)) include $footerFile;
?>