<?php
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/plain; charset=UTF-8');

$vars = ['DB_HOST','DB_USER','DB_PASS','DB_NAME','DB_PORT'];
foreach ($vars as $v) {
    $val = getenv($v);
    if ($v === 'DB_PASS' && $val !== false) {
        $mask = str_repeat('*', max(4, strlen($val)));
        echo "$v=$mask\n";
    } else {
        echo "$v=" . ($val === false ? '(not set)' : $val) . "\n";
    }
}

try {
    $conn = App\Db::conn();
    echo "\nConnected successfully. Server info: " . $conn->host_info . "\n";
    $res = $conn->query('SELECT DATABASE() AS db');
    if ($res) { $row = $res->fetch_assoc(); echo 'Current database: ' . $row['db'] . "\n"; }
} catch (Throwable $e) {
    echo "\nConnection exception: " . $e->getMessage() . "\n";
}
