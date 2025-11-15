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

// Lấy ID khuyến mại từ URL
$promoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($promoId <= 0) {
    die("<div style='padding:20px;text-align:center;'><h3>❌ ID khuyến mại không hợp lệ</h3><a href='promotion_list.php'>← Quay lại danh sách</a></div>");
}

// Fetch khuyến mại từ bảng promotions
$stmt = $conn->prepare("SELECT * FROM promotions WHERE id = ?");
$stmt->bind_param("i", $promoId);
$stmt->execute();
$res = $stmt->get_result();
$promo = $res->fetch_assoc();
$stmt->close();

if (!$promo) {
    die("<div style='padding:20px;text-align:center;'><h3>❌ Không tìm thấy khuyến mại này</h3><a href='promotion_list.php'>← Quay lại danh sách</a></div>");
}

$errors = [];
$success = null;
$old = [
    'title' => $promo['title'] ?? '',
    'description' => $promo['description'] ?? '',
    'discount_type' => $promo['discount_type'] ?? 'percent',
    'discount_value' => $promo['discount_value'] ?? '',
    'coupon_code' => $promo['coupon_code'] ?? '',
    'start_at' => $promo['start_at'] ?? '',
    'end_at' => $promo['end_at'] ?? '',
    'active' => $promo['active'] ?? 1,
    'image_url' => $promo['image_url'] ?? '',
    'menu_items' => []
];

// Lấy danh sách món ăn hiện có
$menuItems = [];
$res = $conn->query("SELECT id, name FROM menu_items ORDER BY name ASC");
if ($res) while ($r = $res->fetch_assoc()) $menuItems[] = $r;

// Lấy món đang áp dụng promo này
$selectedItems = [];
$res = $conn->query("SELECT id FROM menu_items WHERE promo_id = {$promoId}");
if ($res) while ($r = $res->fetch_assoc()) $selectedItems[] = $r['id'];
$old['menu_items'] = $selectedItems;

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

    if ($old['title'] === '') $errors[] = "Tiêu đề không được để trống.";
    if (!is_numeric($old['discount_value'])) $errors[] = "Giá trị giảm giá phải là số.";

    // Xử lý upload hình ảnh
    $uploadedImagePath = $old['image_url'];
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
                // Xóa ảnh cũ
                if (!empty($old['image_url']) && file_exists(__DIR__ . '/../' . $old['image_url'])) {
                    @unlink(__DIR__ . '/../' . $old['image_url']);
                }
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
            UPDATE promotions
            SET title=?, description=?, discount_type=?, discount_value=?, coupon_code=?, start_at=?, end_at=?, active=?, image_url=?
            WHERE id=?
        ");
        
        if (!$stmt) {
            $errors[] = "Lỗi prepare SQL: " . $conn->error;
        } else {
            $stmt->bind_param(
                "ssdssssssi",
                $old['title'],
                $old['description'],
                $old['discount_type'],
                $old['discount_value'],
                $old['coupon_code'],
                $old['start_at'],
                $old['end_at'],
                $old['active'],
                $uploadedImagePath,
                $promoId
            );
            
            if ($stmt->execute()) {
                // Xóa promo_id cũ
                $conn->query("UPDATE menu_items SET promo_id = NULL, promo_price = 0 WHERE promo_id = {$promoId}");
                
                // Cập nhật món mới
                if (!empty($old['menu_items'])) {
                    foreach ($old['menu_items'] as $menuId) {
                        $menuId = (int)$menuId;
                        $priceRes = $conn->query("SELECT price FROM menu_items WHERE id = {$menuId}");
                        if ($priceRes && $row = $priceRes->fetch_assoc()) {
                            $originalPrice = (float)$row['price'];
                            $discountValue = (float)$old['discount_value'];
                            
                            if ($old['discount_type'] === 'percent') {
                                $promoPrice = $originalPrice - ($originalPrice * $discountValue / 100);
                            } else {
                                $promoPrice = $originalPrice - $discountValue;
                            }
                            $promoPrice = max(0, $promoPrice);
                            
                            $conn->query("UPDATE menu_items SET promo_id = {$promoId}, promo_price = {$promoPrice} WHERE id = {$menuId}");
                        }
                    }
                }
                
                $success = "Cập nhật khuyến mại thành công!";
                $promo = array_merge($promo, $old);
            } else {
                $errors[] = "Lỗi khi lưu: " . $stmt->error;
            }
            $stmt->close();
        }
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
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Sửa Khuyến mại</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body>';
    if (file_exists($sidebarFile)) include $sidebarFile;
    echo '<main>';
}
?>

<style>
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

.mb-3 {
    margin-bottom: 18px;
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

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px;
    background: #f0fdfa;
    border: 2px solid #0d9488;
    border-radius: 8px;
    margin-bottom: 20px;
}

.form-check-input {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #0d9488;
}

.form-check-label {
    font-weight: 600;
    color: #2c3e50;
    cursor: pointer;
    margin: 0;
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

.btn-secondary {
    background: white;
    color: #6c757d;
    border: 2px solid #dee2e6;
}

.btn-secondary:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
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

.alert ul {
    margin: 8px 0 0 0;
    padding-left: 20px;
}

.required {
    color: #e74c3c;
    margin-left: 4px;
}

.helper-text {
    font-size: 13px;
    color: #6c757d;
    margin-top: 6px;
}

.image-upload-area {
    border: 2px dashed #cbd5e0;
    border-radius: 8px;
    padding: 24px;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.image-upload-area:hover {
    border-color: #0d9488;
    background: #f0fdfa;
}

.image-upload-area.has-image {
    border-style: solid;
    border-color: #0d9488;
}

.image-preview {
    max-width: 300px;
    max-height: 200px;
    margin: 12px auto;
    border-radius: 8px;
    display: none;
}

.image-preview.show {
    display: block;
}

.upload-icon {
    font-size: 48px;
    color: #cbd5e0;
    margin-bottom: 12px;
}

.upload-text {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 8px;
}

.upload-hint {
    color: #94a3b8;
    font-size: 12px;
}

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
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .content-card {
        padding: 20px;
    }
}
</style>

<div id="page-content">
    <div class="page-header-section">
        <div class="page-title-group">
            <h2>
                <span>✏️</span>
                Chỉnh sửa khuyến mại
            </h2>
            <p class="page-subtitle">Cập nhật thông tin chương trình khuyến mại</p>
        </div>
        <div class="action-buttons">
            <a href="promotion_list.php" class="btn btn-secondary">
                ← Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="content-card">
        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($success) ?>
                <div style="margin-top:10px;">
                    <a href="promotion_list.php" style="color:#155724;text-decoration:underline;">Xem danh sách khuyến mại</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>❌ Vui lòng kiểm tra lại:</strong>
                <ul>
                    <?php foreach($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-section">
                <div class="form-section-title">
                    <span>📋</span> Thông tin cơ bản
                </div>
                
                <div class="mb-3">
                    <label>Tiêu đề khuyến mại <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" 
                           placeholder="VD: Giảm giá 20% tất cả món ăn" 
                           value="<?= htmlspecialchars($old['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label>Mô tả chi tiết</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Mô tả chi tiết về chương trình khuyến mại..."><?= htmlspecialchars($old['description']) ?></textarea>
                    <div class="helper-text">Mô tả sẽ hiển thị cho khách hàng khi xem khuyến mại</div>
                </div>

                <div class="mb-3">
                    <label>Hình ảnh khuyến mại</label>
                    <div class="image-upload-area <?= !empty($old['image_url'])?'has-image':'' ?>" id="uploadArea">
                        <input type="file" name="promo_image" id="promoImage" accept="image/*" style="display:none;">
                        <div class="upload-icon">🖼️</div>
                        <div class="upload-text"><?= !empty($old['image_url'])?'Nhấp để thay đổi hình ảnh':'Nhấp để chọn hình ảnh' ?></div>
                        <div class="upload-hint">JPG, PNG, GIF, WEBP - Tối đa 5MB</div>
                        <img id="imagePreview" class="image-preview <?= !empty($old['image_url'])?'show':'' ?>" 
                             src="<?= !empty($old['image_url'])?'/'.htmlspecialchars($old['image_url']):'' ?>" alt="Preview">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <span>💰</span> Cấu hình giảm giá
                </div>
                
                <div class="form-row">
                    <div class="mb-3">
                        <label>Loại giảm giá</label>
                        <select name="discount_type" class="form-control">
                            <option value="percent" <?= $old['discount_type']==='percent'?'selected':'' ?>>📊 Phần trăm (%)</option>
                            <option value="amount" <?= $old['discount_type']==='amount'?'selected':'' ?>>💵 Số tiền cố định (VNĐ)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Giá trị giảm <span class="required">*</span></label>
                        <input type="number" step="0.01" name="discount_value" 
                               class="form-control" placeholder="VD: 20 hoặc 50000"
                               value="<?= htmlspecialchars($old['discount_value']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Mã giảm giá (Coupon Code)</label>
                    <input type="text" name="coupon_code" class="form-control" 
                           placeholder="VD: SUMMER2024, GIAM20" 
                           value="<?= htmlspecialchars($old['coupon_code']) ?>"
                           style="text-transform:uppercase;">
                    <div class="helper-text">Khách hàng sẽ nhập mã này để áp dụng giảm giá</div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <span>📅</span> Thời gian áp dụng
                </div>
                
                <div class="form-row">
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
                <div class="helper-text">Để trống nếu muốn khuyến mại không giới hạn thời gian</div>
            </div>

            <div class="form-section">
                <div class="form-section-title">
                    <span>🍽️</span> Chọn món ăn áp dụng
                </div>
                
                <div class="mb-3">
                    <label>Danh sách món ăn</label>
                    <select name="menu_items[]" class="form-control" multiple>
                        <?php if (empty($menuItems)): ?>
                            <option disabled>Chưa có món ăn nào</option>
                        <?php else: ?>
                            <?php foreach($menuItems as $m): ?>
                                <option value="<?= $m['id'] ?>" 
                                        <?= in_array($m['id'], $old['menu_items'])?'selected':'' ?>>
                                    <?= htmlspecialchars($m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="helper-text">Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều món</div>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" name="active" class="form-check-input" 
                       id="active" <?= $old['active']?'checked':'' ?>>
                <label class="form-check-label" for="active">
                    ✅ Kích hoạt khuyến mại
                </label>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">
                    💾 Cập nhật khuyến mại
                </button>
                <a href="promotion_list.php" class="btn btn-secondary">
                    ❌ Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Xử lý upload và preview hình ảnh
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('promoImage');
const preview = document.getElementById('imagePreview');

uploadArea.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.add('show');
            uploadArea.classList.add('has-image');
        }
        reader.readAsDataURL(file);
    }
});

// Drag and drop
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = '#0d9488';
    uploadArea.style.background = '#f0fdfa';
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.style.borderColor = '#cbd5e0';
    uploadArea.style.background = '#f8fafc';
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        fileInput.files = e.dataTransfer.files;
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.add('show');
            uploadArea.classList.add('has-image');
        }
        reader.readAsDataURL(file);
    }
    uploadArea.style.borderColor = '#cbd5e0';
    uploadArea.style.background = '#f8fafc';
});
</script>

<?php
$footerFile = __DIR__ . '/footer.php';
if (file_exists($footerFile)) include $footerFile;
else echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script></body></html>';
?>