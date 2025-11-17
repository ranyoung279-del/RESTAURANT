<?php
namespace App\Components;
class AdminHeader {
    private string $username;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->username = $_SESSION['username'] ?? 'Khách';
    }

    public function render(): void {
        $u = htmlspecialchars($this->username);
        echo <<<HTML
        <header class="admin-header">
          <div class="header-left">
            <h1>WENZHU Admin</h1>
          </div>
          <div class="header-right">
            <div class="user-dropdown">
              <span>{$u} ⬇</span>
              <div class="dropdown-content">
                <a href="logout.php">Đăng xuất</a>
              </div>
            </div>
          </div>
        </header>
        HTML;
    }
}
class AdminFooter {
    private string $year; private string $brand;
    public function __construct(string $brand = 'WENZHU') {
        $this->year = date('Y'); $this->brand = htmlspecialchars($brand);
    }
    public function render(): void {
        echo <<<HTML
        <link rel="stylesheet" href="../assets/css/admin.css">
        <footer class="admin-footer">
            <p>&copy; {$this->year} {$this->brand}. Bản quyền thuộc về chúng tôi.</p>
        </footer>
        HTML;
    }
}
class AdminSidebar {
    private string $role;
    private array $items;

    public function __construct(?string $role = null) {
        if (session_status() === \PHP_SESSION_NONE) session_start();
        $this->role = ($role ?? ($_SESSION['user_role'] ?? 'guest'));

        // Giữ nguyên cấu trúc menu cũ của bạn
        $this->items = [
            [
                'label' => 'Quản lý thực đơn',
                'href'  => 'menu_manage.php',
                'roles' => ['admin', 'staff']
            ],
            [
                'label' => 'Quản lý đặt bàn',
                'href'  => 'reservation_manage.php',
                'roles' => ['admin', 'staff']
            ],
            [
            'label' => 'Quản lý khuyến mãi',
                'href'  => 'promotion_manage.php',
                'roles' => ['admin', 'staff']
            ],
            [
                'label' => 'Quản lý thông tin',
                'href'  => 'info.php',
                'roles' => ['admin'] // chỉ admin
            ],
            [
                'label' => 'Quản lý người dùng',
                'href'  => 'users_manage.php',
                'roles' => ['admin'] // chỉ admin
            ],
        ];
    }

    private function isAllowed(array $roles): bool {
        return in_array($this->role, $roles, true);
    }

    private function currentFile(): string {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        return basename($path) ?: '';
    }

    public function render(): void {
        if (!in_array($this->role, ['admin','staff'], true)) return;

        $current = $this->currentFile();

        echo '<nav class="admin-menu"><ul>';

        foreach ($this->items as $item) {
            if (!$this->isAllowed($item['roles'])) continue;

            $active = ($current === $item['href']) ? ' class="active"' : '';
            echo "<li><a href=\"{$item['href']}\"{$active}>"
                 . htmlspecialchars($item['label']) . "</a></li>";
        }

        echo '</ul></nav>';
    }
}
final class Auth {
    public static function logout(string $redirect = 'login.php'): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Xoá dữ liệu session
        $_SESSION = [];

        // Xoá cookie PHPSESSID
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Hủy session trên server
        session_destroy();

        // Chuyển hướng
        header("Location: {$redirect}");
        exit;
    }

    public static function guardAdmin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
            header("Location: login.php");
            exit;
        }
    }

    /**
     * Yêu cầu quyền khách hàng
     */
    public static function guardCustomer(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['customer_id'])) {
            header("Location: login_cus.php");
            exit;
        }
    }
}

class AdminLayout {
    public static function header(): void { (new AdminHeader())->render(); }
    public static function sidebar(): void { (new AdminSidebar())->render(); }
    public static function footer(string $brand='WENZHU'): void { (new AdminFooter($brand))->render(); }
}
