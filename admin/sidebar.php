<?php
require_once __DIR__ . '/../includes/db.php';
use App\Components\AdminSidebar;

$sb = new AdminSidebar(); // tự đọc role từ session
$sb->render();
