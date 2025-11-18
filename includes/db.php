<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
// ---- PSR-4 autoload đơn giản cho project ----
spl_autoload_register(function ($class) {
    // Map prefix -> thư mục thật
    $prefixes = [
        'App\\Controllers\\' => __DIR__ . '/../controllers/', // controllers/
        'App\\Models\\'      => __DIR__ . '/classes/',        // models nằm chung 1 file
        'App\\Components\\'  => __DIR__ . '/classes/',        // components.php
        'App\\'              => __DIR__ . '/classes/',        // auth.php, csrf.php, db.php ...
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relative = substr($class, $len);                    // ví dụ: HomeController
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        // Trường hợp class nằm file riêng (Controllers, Components, …)
        if (is_file($file)) {
            require $file;
            return;
        }

        // 🔁 Fallback đặc biệt cho Models: nhiều class chung file models.php
        if ($prefix === 'App\\Models\\') {
            $fallback = __DIR__ . '/classes/models.php';
            if (is_file($fallback)) {
                require_once $fallback;
                // sau khi require models.php, class Models sẽ tồn tại
                if (class_exists($class, false)) return;
            }
        }
    }
});
// Nạp các lớp cần dùng (đúng tên file bạn đang có: db.php, auth.php, csrf.php, models.php)
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/auth.php';
// Khởi động session sớm nếu có thể (an toàn nếu header đã gửi)
\App\Auth::start();
// Nếu đã gửi output (do file khác) thì bật output buffering để tránh warning session.
if (!headers_sent()) {
    // không làm gì, an toàn
} else {
    // Bật buffering tiếp tục (chỉ khi đã gửi) để giảm cảnh báo kế tiếp
    if (function_exists('ob_start')) ob_start();
}
if (file_exists(__DIR__ . '/classes/csrf.php')) {
    require_once __DIR__ . '/classes/csrf.php';
}
require_once __DIR__ . '/classes/models.php';
require_once __DIR__ . '/classes/components.php';
if (file_exists(__DIR__ . '/classes/email.php')) {
    require_once __DIR__ . '/classes/email.php';
}
// Composer autoload (PHPMailer, etc.)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Tạo kết nối dùng chung cho code cũ
$conn = App\Db::conn();
