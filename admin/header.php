<?php
require_once __DIR__ . '/../includes/db.php';
use App\Components\AdminHeader;

$header = new AdminHeader();
$header->render();
?>
