<?php
// Wrapper: redirect /admin/activate_staff.php -> /activate_staff.php to avoid 404
$token = isset($_GET['token']) ? (string)$_GET['token'] : '';
$dest  = '../activate_staff.php' . ($token !== '' ? ('?token=' . urlencode($token)) : '');
header('Location: ' . $dest);
exit;