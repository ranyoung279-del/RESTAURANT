<?php
// header.php – header chung cho admin

// Đảm bảo có session để lấy thông tin user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy thông tin người dùng từ session
$username = $_SESSION['username'] ?? 'Quản trị';
$role     = $_SESSION['user_role'] ?? 'admin';

// Hiển thị nhãn vai trò đẹp hơn
$roleLabel = ($role === 'admin') ? 'Quản trị viên' : 'Nhân viên';

// Lấy chữ cái đầu làm avatar (hỗ trợ tiếng Việt)
$initial = function (string $name): string {
    $name = trim($name);
    if ($name === '') return 'U'; // Unknown
    // Dùng mb_* để hỗ trợ Unicode
    return mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
};
$avatarLetter = $initial($username);
?>

<header class="admin-header">
    <h1>WENZHU Admin</h1>

    <div class="user-dropdown">
        <button class="user-dropdown-trigger" type="button">
            <div class="user-avatar">
                <?= htmlspecialchars($avatarLetter, ENT_QUOTES, 'UTF-8') ?>
            </div>

            <div class="user-info">
                <span class="user-name">
                    <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="user-role">
                    <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <span class="user-caret">▾</span>
        </button>

        <div class="dropdown-content">
            <a href="logout.php">Đăng xuất</a>
        </div>
    </div>
</header>
