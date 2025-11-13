<?php
// menu.php
include 'includes/db.php';

use App\Controllers\MenuController;

$ctrl  = new MenuController();
$items = $ctrl->listAvailable(); // mysqli_result
?>

<?php include 'header.php'; ?>
<section class="menu-section">
  <h2>Thực đơn của chúng tôi</h2>

  <!-- Thanh tìm kiếm -->
  <div class="menu-search-container">
    <div class="menu-search-box">
      <input type="text" id="menuSearch" placeholder="🔍 Tìm kiếm món ăn bạn yêu thích...">
      <button id="clearSearch" title="Xóa tìm kiếm">✕</button>
    </div>
    <p id="searchMessage" class="search-message"></p>
  </div>

  <div class="filter">
    <button class="filter-btn active" data-category="all">Tất cả</button>
    <button class="filter-btn" data-category="appetizer">Món khai vị</button>
    <button class="filter-btn" data-category="main">Món chính</button>
    <button class="filter-btn" data-category="dessert">Tráng miệng</button>
    <button class="filter-btn" data-category="drink">Đồ uống</button>
  </div>

  <div class="menu-list">
    <?php if ($items instanceof mysqli_result && $items->num_rows): ?>
<?php while ($item = $items->fetch_assoc()): ?>
  <?php
    $id       = (int)($item['id'] ?? 0);
    $name     = htmlspecialchars($item['name'] ?? 'Món ăn');
    $desc     = htmlspecialchars($item['description'] ?? '');
    $priceRaw = isset($item['price']) ? (float)$item['price'] : 0;
    $price    = number_format($priceRaw);
    $image    = htmlspecialchars($item['image_url'] ?? '');
    $category = strtolower(trim($item['category'] ?? 'other'));
    $specialClass = !empty($item['is_special']) ? ' special' : '';
  ?>
  <div class="menu-card<?= $specialClass ?>"
       data-category="<?= $category ?>"
       data-name="<?= strtolower($name) ?>"
       data-desc="<?= strtolower($desc) ?>">
    <?php if ($image): ?>
      <img src="admin/<?= $image ?>" alt="<?= $name ?>" loading="lazy">
    <?php endif; ?>
    <h3><?= $name ?></h3>
    <p><?= $desc ?></p>
    <p class="price"><?= $price ?> đ</p>

    <!-- Nút sang trang chi tiết -->
    <a href="detail.php?id=<?= $id ?>" class="detail-btn">Xem chi tiết</a>
  </div>
<?php endwhile; ?>
    <?php else: ?>
      <p class="no-items">Hiện chưa có món nào trong thực đơn.</p>
    <?php endif; ?>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('menuSearch');
  const clearBtn = document.getElementById('clearSearch');
  const cards = document.querySelectorAll('.menu-card');
  const message = document.getElementById('searchMessage');
  const filterBtns = document.querySelectorAll('.filter-btn');

  let currentCategory = 'all';

  function filterMenu() {
    const term = (searchInput.value || '').trim().toLowerCase();
    let count = 0;

    cards.forEach(card => {
      const name = (card.getAttribute('data-name') || '').toLowerCase();
      const category = card.getAttribute('data-category') || '';

      const matchCategory = currentCategory === 'all' || category === currentCategory;
      const matchSearch = term === '' || name.includes(term);

      if (matchCategory && matchSearch) {
        card.style.display = '';
        if (term && name.includes(term)) {
          card.classList.add('highlight');
          setTimeout(() => card.classList.remove('highlight'), 400);
        }
        count++;
      } else {
        card.style.display = 'none';
      }
    });

    message.textContent = term
      ? (count > 0 ? `Tìm thấy ${count} món phù hợp` : 'Không tìm thấy món nào')
      : '';
    clearBtn.style.display = term ? 'block' : 'none';
  }

  searchInput.addEventListener('input', filterMenu);
  clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    clearBtn.style.display = 'none';
    message.textContent = '';
    filterMenu();
  });

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentCategory = btn.getAttribute('data-category');
      filterMenu();
    });
  });
});
</script>

<?php include 'footer.php'; ?>
