<?php
declare(strict_types=1);
// admin/promotion_delete.php

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/classes/db.php';

// get DB connection
try {
    $conn = \App\Db::conn();
} catch (Throwable $e) {
    echo "<p style='color:red'>Lỗi kết nối DB: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// lấy id từ query
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<p style='color:red'>ID món ăn không hợp lệ.</p>";
    exit;
}

// kiểm tra xem món ăn có tồn tại không
$stmt = $conn->prepare("SELECT name FROM menu_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$item = $res->fetch_assoc();
$stmt->close();

if (!$item) {
    echo "<p style='color:red'>Món ăn không tồn tại.</p>";
    exit;
}

// xóa món ăn
$stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    $stmt->close();
    // redirect về danh sách với thông báo success
    header("Location: promotion_list.php?msg=" . urlencode("Món ăn '{$item['name']}' đã được xóa."));
    exit;
} else {
    $stmt->close();
    echo "<p style='color:red'>Lỗi khi xóa: " . htmlspecialchars($conn->error) . "</p>";
    echo '<p><a href="promotion_list.php">← Quay lại danh sách</a></p>';
}
?>
