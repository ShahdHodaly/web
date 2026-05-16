<?php
session_start();
require_once 'db.php';

$pdo = getDB();

// ── جيبي المنتجات والتصنيفات من DB ───────────────────────────
$stmt = $pdo->query("
    SELECT p.product_id AS id, p.name, p.price, p.image,
           p.description, p.stock, p.sales_count,
           c.name AS category,
           ROUND(COALESCE(AVG(r.rating), 0), 1) AS avg_rating
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN reviews r ON r.product_id = p.product_id AND r.status = 'approved'
    WHERE p.stock > 0
    GROUP BY p.product_id, c.name
    ORDER BY p.product_id ASC
");
$products   = $stmt->fetchAll();
$categories = array_unique(array_column($products, 'category'));

// ── معالجة AJAX ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // إضافة للكارت
    if ($_POST['action'] === 'add_to_cart') {
        if (empty($_SESSION['logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'login_required']);
            exit;
        }
        $productId = (int)($_POST['product_id'] ?? 0);
        $userId    = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();

        if (!$cart) {
            $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)")->execute([$userId]);
            $cartId = $pdo->lastInsertId();
        } else {
            $cartId = $cart['cart_id'];
        }

        $pdo->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity)
            VALUES (?, ?, 1)
            ON CONFLICT (cart_id, product_id)
            DO UPDATE SET quantity = cart_items.quantity + 1
        ")->execute([$cartId, $productId]);

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        echo json_encode(['success' => true, 'cart_count' => (int)$stmt->fetchColumn()]);
        exit;
    }

    // إضافة/حذف من الـ wishlist
    if ($_POST['action'] === 'toggle_wishlist') {
        if (empty($_SESSION['logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'login_required']);
            exit;
        }
        $productId = (int)($_POST['product_id'] ?? 0);
        $userId    = $_SESSION['user_id'];

        $check = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$userId, $productId]);

        if ($check->fetch()) {
            $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                    ->execute([$userId, $productId]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")
                    ->execute([$userId, $productId]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}

// ── جيبي الـ wishlist الحالية للمستخدم ───────────────────────
$userWishlist = [];
if (!empty($_SESSION['logged_in'])) {
    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userWishlist = array_column($stmt->fetchAll(), 'product_id');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Teddy Lap</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .shop-container { padding: 50px 20px; max-width: 1200px; margin: 0 auto; }
        .shop-header { text-align: center; margin-bottom: 40px; }
        .shop-header h1 { font-family: 'Playfair Display', serif; font-size: 42px; color: var(--text-color); margin-bottom: 10px; }
        .shop-header p { color: var(--secondary-text); }
        .filter-sort-bar { display: flex; justify-content: flex-start; align-items: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .filter-btn-main { background-color: var(--card-bg); border: 1px solid #ddd; padding: 8px 20px; border-radius: 20px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease; }
        .filter-btn-main i { font-size: 12px; }
        .filter-btn-main:hover { background-color: #ff6b81; color: white; border-color: #ff6b81; }
        .filter-wrapper { position: relative; }
        .filter-popup { position: absolute; top: 45px; left: 0; width: 260px; background: var(--card-bg); border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); padding: 18px; z-index: 1000; display: none; border: 1px solid rgba(0,0,0,0.05); }
        body.dark-mode .filter-popup { border-color: #444; }
        .filter-popup.show { display: block; }
        .price-range { margin: 15px 0; }
        .price-range h4 { font-size: 15px; margin-bottom: 12px; color: var(--text-color); }
        .slider-container { padding: 0 5px; }
        .single-slider { width: 100%; margin: 12px 0; }
        .price-values { display: flex; justify-content: space-between; font-size: 13px; color: var(--secondary-text); margin-top: 8px; }
        .filter-actions { display: flex; gap: 10px; margin-top: 20px; }
        .filter-actions button { flex: 1; padding: 8px 12px; border: none; border-radius: 20px; cursor: pointer; font-weight: 500; font-size: 13px; transition: all 0.2s ease; }
        .apply-btn { background: #ff6b81; color: white; }
        .apply-btn:hover { background: #ff4f6b; }
        .clear-btn { background: transparent; border: 1px solid #ddd; color: var(--text-color); }
        .clear-btn:hover { border-color: #ff6b81; color: #ff6b81; }
        .sort-btn-main { background-color: var(--card-bg); border: 1px solid #ddd; padding: 8px 20px; border-radius: 20px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease; }
        .sort-btn-main i { font-size: 12px; }
        .sort-btn-main:hover { background-color: #ff6b81; color: white; border-color: #ff6b81; }
        .sort-wrapper { position: relative; }
        .sort-dropdown { position: absolute; top: 45px; left: 0; width: 200px; background: var(--card-bg); border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); padding: 8px 0; z-index: 1000; display: none; border: 1px solid rgba(0,0,0,0.05); }
        body.dark-mode .sort-dropdown { border-color: #444; }
        .sort-dropdown.show { display: block; }
        .sort-option { padding: 10px 16px; cursor: pointer; transition: all 0.2s ease; color: var(--text-color); font-size: 13px; }
        .sort-option:hover { background-color: #ff6b81; color: white; }
        .sort-option.active { background-color: #ff6b81; color: white; font-weight: 500; }
        .category-filter { display: flex; justify-content: flex-start; gap: 8px; margin-bottom: 40px; flex-wrap: wrap; }
        .filter-btn { background-color: var(--card-bg); border: 1px solid #ddd; padding: 8px 20px; border-radius: 20px; cursor: pointer; font-size: 14px; transition: all 0.3s ease; color: var(--text-color); }
        body.dark-mode .filter-btn { border-color: #444; }
        .filter-btn:hover, .filter-btn.active { background-color: #ff6b81; color: #fff; border-color: #ff6b81; }
        .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; }
        @media (max-width: 992px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
        .product-card { background: var(--card-bg); border-radius: 15px; overflow: hidden; position: relative; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border: none; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .product-img-box { width: 100%; height: 250px; background-color: var(--card-bg); display: flex; align-items: center; justify-content: center; padding: 10px; box-sizing: border-box; }
        .product-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.5s ease; }
        .product-card:hover .product-img-box img { transform: scale(1.05); }
        .card-content { padding: 15px; text-align: center; }
        .product-card h3 { font-size: 16px; margin: 0 0 5px; color: var(--text-color); }
        .product-card .price { margin: 0; color: #d63384; font-weight: bold; font-size: 18px; }
        .product-card p.desc { font-size: 12px; color: var(--secondary-text); margin: 10px 0 15px; height: 34px; overflow: hidden; }
        .card-actions { display: flex; align-items: center; justify-content: center; gap: 10px; opacity: 0; transition: opacity 0.4s ease; height: 50px; }
        .product-card:hover .card-actions { opacity: 1; }
        .add-cart-btn { flex: 1; padding: 10px; background: #333; color: #fff; border: none; border-radius: 25px; cursor: pointer; font-weight: bold; font-size: 13px; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 5px; }
        .add-cart-btn:hover { background: #555; }
        .add-cart-btn.success { background-color: #28a745 !important; }
        .fav-btn { width: 40px; height: 40px; border-radius: 50%; border: 1px solid #ddd; background: var(--card-bg); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; color: #888; }
        body.dark-mode .fav-btn { border-color: #444; color: #aaa; }
        .fav-btn:hover, .fav-btn.active { color: #ff6b81; border-color: #ff6b81; }
        .animated-title { font-family: 'Playfair Display', serif; font-size: 72px; font-weight: 700; background: linear-gradient(45deg, #ff6b81, #ff9a9e, #fbc2eb); background-size: 300% 300%; -webkit-background-clip: text; -webkit-text-fill-color: transparent; animation: fadeSlide 1.2s ease forwards, gradientMove 5s ease infinite; letter-spacing: 4px; }
        @media (max-width: 768px) { .animated-title { font-size: 48px; } }
        @keyframes gradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes fadeSlide { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        .back-to-top { position: fixed; bottom: 30px; right: 30px; width: 50px; height: 50px; background: var(--pink); color: #fff; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 20px; box-shadow: 0 5px 20px rgba(255, 107, 129, 0.4); opacity: 0; visibility: hidden; transition: all 0.3s ease; z-index: 999; }
        .back-to-top.show { opacity: 1; visibility: visible; }
        .back-to-top:hover { background: #ff4f6b; transform: translateY(-3px); }
    </style>
</head>
<body>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="shop-container">

    <div class="shop-header">
        <h1 class="animated-title">Teddy Lap</h1>
        <p>Find the perfect toy for your little one</p>
    </div>

    <div class="filter-sort-bar">
        <div class="filter-wrapper">
            <button class="filter-btn-main" onclick="toggleFilterPopup()">
                <i class="fa-solid fa-sliders"></i> Filter
            </button>
            <div class="filter-popup" id="filterPopup">
                <div class="price-range">
                    <h4>Max Price: $<span id="selectedPrice">100</span></h4>
                    <div class="slider-container">
                        <input type="range" id="priceSlider" class="single-slider" min="0" max="100" value="100" step="1" oninput="updateSinglePriceLabel()">
                        <div class="price-values"><span>$0</span><span>$100</span></div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="apply-btn" onclick="applyPriceFilter()">Apply</button>
                    <button class="clear-btn" onclick="clearPriceFilter()">Clear</button>
                </div>
            </div>
        </div>

        <div class="sort-wrapper">
            <button class="sort-btn-main" onclick="toggleSortDropdown()">
                <i class="fa-solid fa-arrow-up-wide-short"></i> Sort: <span id="selectedSort">Default</span>
            </button>
            <div class="sort-dropdown" id="sortDropdown">
                <div class="sort-option" onclick="selectSort('default', 'Default')">Default</div>
                <div class="sort-option" onclick="selectSort('price_low', 'Price Low → High')">Price Low → High</div>
                <div class="sort-option" onclick="selectSort('price_high', 'Price High → Low')">Price High → Low</div>
            </div>
        </div>
    </div>

    <div class="category-filter">
        <button class="filter-btn active" onclick="filterProducts('all', event)">All Toys</button>
        <?php foreach ($categories as $cat): ?>
            <button class="filter-btn" onclick="filterProducts('<?= htmlspecialchars($cat) ?>', event)">
                <?= htmlspecialchars($cat) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="product-grid" id="productGrid">
        <?php foreach ($products as $item): ?>
            <div class="product-card"
                 data-category="<?= htmlspecialchars($item['category']) ?>"
                 data-price="<?= $item['price'] ?>"
                 data-id="<?= $item['id'] ?>">

                <div class="product-img-box">
                    <a href="product_details.php?id=<?= $item['id'] ?>">
                        <img src="<?= htmlspecialchars($item['image']) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>"
                             onerror="this.src='https://ui-avatars.com/api/?name=Toy&background=ff9a9e&color=fff'">
                    </a>
                </div>

                <div class="card-content">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="price">$<?= number_format($item['price'], 2) ?></p>
                    <p class="desc"><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</p>

                    <div class="card-actions">
                        <button class="add-cart-btn" onclick="addToCart(<?= $item['id'] ?>, this)">
                            <i class="fa-solid fa-cart-plus"></i> Add
                        </button>
                        <button class="fav-btn <?= in_array($item['id'], $userWishlist) ? 'active' : '' ?>"
                                data-id="<?= $item['id'] ?>"
                                onclick="toggleFavorite(this)">
                            <i class="fa-<?= in_array($item['id'], $userWishlist) ? 'solid' : 'regular' ?> fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<button id="backToTop" class="back-to-top" onclick="scrollToTop()">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script>
    const isLoggedIn = <?= !empty($_SESSION['logged_in']) ? 'true' : 'false' ?>;

    let currentCategory = 'all';
    let currentSort     = 'default';
    let maxPriceFilter  = 100;

    function filterProducts(category, event) {
        currentCategory = category;
        document.querySelectorAll('.category-filter .filter-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        applyFiltersAndSort();
    }

    function toggleFilterPopup() { document.getElementById('filterPopup').classList.toggle('show'); }
    function updateSinglePriceLabel() {
        const price = parseInt(document.getElementById('priceSlider').value);
        document.getElementById('selectedPrice').innerText = price;
        maxPriceFilter = price;
    }
    function applyPriceFilter() { updateSinglePriceLabel(); applyFiltersAndSort(); toggleFilterPopup(); }
    function clearPriceFilter() {
        maxPriceFilter = 100;
        document.getElementById('priceSlider').value = 100;
        updateSinglePriceLabel();
        applyFiltersAndSort();
        toggleFilterPopup();
    }

    function toggleSortDropdown() { document.getElementById('sortDropdown').classList.toggle('show'); }
    function selectSort(type, text) {
        currentSort = type;
        document.getElementById('selectedSort').innerText = text;
        document.querySelectorAll('.sort-option').forEach(opt => opt.classList.remove('active'));
        event.target.classList.add('active');
        applyFiltersAndSort();
        toggleSortDropdown();
    }

    function applyFiltersAndSort() {
        let cards = Array.from(document.querySelectorAll('.product-card'));

        if (currentCategory !== 'all')
            cards = cards.filter(p => p.dataset.category === currentCategory);

        cards = cards.filter(p => parseFloat(p.dataset.price) <= maxPriceFilter);

        if (currentSort === 'price_low')
            cards.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        else if (currentSort === 'price_high')
            cards.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));

        const grid = document.getElementById('productGrid');
        document.querySelectorAll('.product-card').forEach(c => c.style.display = 'none');
        cards.forEach(c => { c.style.display = 'block'; grid.appendChild(c); });
    }

    // ── Add to Cart — DB إذا مسجّل، localStorage إذا لا ────────
    function addToCart(productId, btn) {
        const original = btn.innerHTML;

        if (!isLoggedIn) {
            let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
            cart[productId] = (cart[productId] || 0) + 1;
            localStorage.setItem('teddy_cart', JSON.stringify(cart));
            btn.classList.add('success');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
            setTimeout(() => { btn.classList.remove('success'); btn.innerHTML = original; }, 1200);
            return;
        }

        fetch('shop.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_to_cart&product_id=' + productId
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.classList.add('success');
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
                    const badge = document.getElementById('cartCount');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.classList.toggle('hide', data.cart_count === 0);
                    }
                    setTimeout(() => { btn.classList.remove('success'); btn.innerHTML = original; }, 1200);
                }
            });
    }

    // ── Wishlist — DB إذا مسجّل، localStorage إذا لا ──────────
    function toggleFavorite(btn) {
        const productId = btn.getAttribute('data-id');
        const icon      = btn.querySelector('i');

        if (!isLoggedIn) {
            let favs = JSON.parse(localStorage.getItem('teddy_wishlist')) || [];
            const idx = favs.indexOf(productId);
            if (idx === -1) {
                favs.push(productId);
                btn.classList.add('active');
                icon.classList.replace('fa-regular', 'fa-solid');
            } else {
                favs.splice(idx, 1);
                btn.classList.remove('active');
                icon.classList.replace('fa-solid', 'fa-regular');
            }
            localStorage.setItem('teddy_wishlist', JSON.stringify(favs));
            return;
        }

        fetch('shop.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle_wishlist&product_id=' + productId
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const added = data.action === 'added';
                    btn.classList.toggle('active', added);
                    icon.classList.toggle('fa-solid',  added);
                    icon.classList.toggle('fa-regular', !added);
                }
            });
    }

    function toggleBackToTopButton() {
        document.getElementById('backToTop').classList.toggle('show', window.scrollY > 300);
    }
    function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }

    window.addEventListener('scroll', toggleBackToTopButton);
    window.addEventListener('load', () => { updateSinglePriceLabel(); toggleBackToTopButton(); });

    window.addEventListener('click', function(e) {
        const fp = document.getElementById('filterPopup');
        const fb = document.querySelector('.filter-btn-main');
        const sd = document.getElementById('sortDropdown');
        const sb = document.querySelector('.sort-btn-main');
        if (fp && fb && !fp.contains(e.target) && !fb.contains(e.target)) fp.classList.remove('show');
        if (sd && sb && !sd.contains(e.target) && !sb.contains(e.target)) sd.classList.remove('show');
    });
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>