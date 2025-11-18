<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
use App\Auth;
use App\Csrf;
use App\Components\AdminLayout;
use App\Controllers\PromotionController;

Auth::guardAdmin();
$ctrl = new PromotionController();
$result = $ctrl->handleManage();

$message = $result['message'];
$error = $result['error'];
$edit_data = $result['edit_data'];
// Lấy danh sách và CSRF token
$list = $ctrl->listAll();
$csrf = Csrf::token();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý khuyến mãi</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=<?= time(); ?>">
</head>
<body>

<?php include 'header.php'; ?>
<div class="admin-dashboard">
  <?php AdminLayout::sidebar(); ?>    
    <!-- Content area -->
    <div class="admin-overview">
        <h2>Quản Lý Khuyến Mãi</h2>
        
        <?php if ($message): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Form thêm promotion - 2 cột -->
        <div class="form-container" style="max-width: 100%;">
            <h3>Thêm Khuyến Mãi Mới</h3>
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Tiêu đề:</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div>
                        <label>Mã khuyến mãi:</label>
                        <input type="text" name="coupon_code" required>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label>Mô tả:</label>
                        <textarea name="description" rows="2"></textarea>
                    </div>
                    
                    <div>
                        <label>Loại giảm giá:</label>
                        <select name="discount_type" required>
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định</option>
                        </select>
                    </div>
                    
                    <div>
                        <label>Giá trị giảm:</label>
                        <input type="number" name="discount_value" step="0.01" required>
                    </div>
                    
                    <div>
                        <label>Ngày bắt đầu:</label>
                        <input type="datetime-local" name="start_at">
                    </div>
                    
                    <div>
                        <label>Ngày kết thúc:</label>
                        <input type="datetime-local" name="end_at">
                    </div>
                    <!-- Áp dụng cho tất cả món -->
<div>
    <label>
        <input type="checkbox" name="apply_to_all" value="1" <?= !empty($edit_data['apply_to_all']) ? 'checked' : '' ?>>
        Áp dụng cho tất cả món
    </label>
</div>
<!-- Danh sách ID món áp dụng (nếu không áp dụng tất cả) -->
<div>
    <label>Danh sách ID món áp dụng:</label>
    <input type="text" name="apply_to_menu_ids" 
           value="<?= htmlspecialchars($edit_data['apply_to_menu_ids'] ?? '') ?>" 
           placeholder="Ví dụ: 2,3,4,9">
</div>
                    <div style="grid-column: 1 / -1;">
                        <label>
                            <input type="checkbox" name="active" checked> Kích hoạt
                        </label>
                    </div>
                </div>
                
                <button type="submit" name="add_promotion" style="margin-top: 15px;">➕ Thêm Khuyến Mãi</button>
            </form>
        </div>
        
        <!-- Danh sách promotions -->
        <h3>Danh Sách Khuyến Mãi</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Mã KM</th>
                    <th>Loại</th>
                    <th>Giá trị</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($list->num_rows > 0): ?>
                     <?php while ($row = $list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['coupon_code']); ?></strong></td>
                            <td><?php echo $row['discount_type'] === 'percent' ? 'Phần trăm' : 'Cố định'; ?></td>
                            <td><?php echo $row['discount_value']; ?><?php echo $row['discount_type'] === 'percent' ? '%' : 'đ'; ?></td>
                            <td>
                                <?php echo $row['start_at'] ? date('d/m/Y', strtotime($row['start_at'])) : '—'; ?><br>
                                <small><?php echo $row['end_at'] ? date('d/m/Y', strtotime($row['end_at'])) : '—'; ?></small>
                            </td>
                            <td><?php echo $row['active'] ? '✅ Hoạt động' : '❌ Tắt'; ?></td>
                            <td>
                                <div class="action-links">
                                    <a href="#" onclick="editPromotion(<?php echo htmlspecialchars(json_encode($row)); ?>); return false;" class="edit">✏️ Sửa</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Bạn có chắc muốn xóa?')">🗑️ Xóa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Chưa có khuyến mãi nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- End admin-dashboard -->

<!-- Modal sửa promotion -->
<div id="editModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); overflow-y: auto;">
    <div style="background-color:#fff; margin:3% auto; padding:25px; width:85%; max-width:750px; border-radius:12px;">
        <span onclick="closeModal()" style="float:right; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
        <h3 style="margin-bottom: 20px;">Sửa Khuyến Mãi</h3>
        <form method="POST" action="">
            <input type="hidden" name="id" id="edit_id">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Tiêu đề:</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                
                <div>
                    <label>Mã khuyến mãi:</label>
                    <input type="text" name="coupon_code" id="edit_coupon_code" required>
                </div>
                
                <div style="grid-column: 1 / -1;">
                    <label>Mô tả:</label>
                    <textarea name="description" id="edit_description" rows="2"></textarea>
                </div>
                
                <div>
                    <label>Loại giảm giá:</label>
                    <select name="discount_type" id="edit_discount_type" required>
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định</option>
                    </select>
                </div>
                
                <div>
                    <label>Giá trị giảm:</label>
                    <input type="number" name="discount_value" id="edit_discount_value" step="0.01" required>
                </div>
                
                <div>
                    <label>Ngày bắt đầu:</label>
                    <input type="datetime-local" name="start_at" id="edit_start_at">
                </div>
                
                <div>
                    <label>Ngày kết thúc:</label>
                    <input type="datetime-local" name="end_at" id="edit_end_at">
                </div>
                
                <div style="grid-column: 1 / -1;">
                    <label>
                        <input type="checkbox" name="active" id="edit_active"> Kích hoạt
                    </label>
                </div>
                <!-- Áp dụng cho tất cả món -->
                <div style="grid-column: 1 / -1;">
                    <label>
                        <input type="checkbox" name="apply_to_all" id="edit_apply_to_all" value="1"> Áp dụng cho tất cả món
                    </label>
                </div>

                <!-- Danh sách ID món áp dụng (nếu không áp dụng tất cả) -->
                <div style="grid-column: 1 / -1;">
                    <label>Danh sách ID món áp dụng:</label>
                    <input type="text" name="apply_to_menu_ids" id="edit_apply_to_menu_ids" placeholder="Ví dụ: 2,3,4,9">
                </div>
            </div>
            <div style="margin-top: 15px;">
                <button type="submit" name="update_promotion">💾 Cập Nhật</button>
                <button type="button" onclick="closeModal()" class="cancel-btn">❌ Hủy</button>
            </div>
        </form>
    </div>
</div>
<script>
    function editPromotion(promotion) {
        document.getElementById('edit_id').value = promotion.id;
        document.getElementById('edit_title').value = promotion.title;
        document.getElementById('edit_coupon_code').value = promotion.coupon_code;
        document.getElementById('edit_description').value = promotion.description || '';
        document.getElementById('edit_discount_type').value = promotion.discount_type;
        document.getElementById('edit_discount_value').value = promotion.discount_value;
        
        // Format datetime
        if (promotion.start_at) {
            document.getElementById('edit_start_at').value = promotion.start_at.replace(' ', 'T').substring(0, 16);
        }
        if (promotion.end_at) {
            document.getElementById('edit_end_at').value = promotion.end_at.replace(' ', 'T').substring(0, 16);
        }
        
        document.getElementById('edit_active').checked = promotion.active == 1;
        
        document.getElementById('editModal').style.display = 'block';
    }
    
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById('editModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>
<?php AdminLayout::footer(); ?>
</body>
</html>