<?php
session_start();
require_once 'db.php';

$pdo = getDB();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── جيبي المنتج من DB ─────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: shop.php");
    exit;
}

$pageTitle = htmlspecialchars($product['name']) . " | Teddy Lap";

// ── السابق والتالي ────────────────────────────────────────────
$prevStmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id < ? ORDER BY product_id DESC LIMIT 1");
$prevStmt->execute([$id]);
$prevId = $prevStmt->fetchColumn();

$nextStmt = $pdo->prepare("SELECT product_id FROM products WHERE product_id > ? ORDER BY product_id ASC LIMIT 1");
$nextStmt->execute([$id]);
$nextId = $nextStmt->fetchColumn();

// ── منتجات ذات صلة ────────────────────────────────────────────
$relStmt = $pdo->prepare("
    SELECT p.product_id AS id, p.name, p.price, p.image
    FROM products p
    WHERE p.category_id = ? AND p.product_id != ?
    ORDER BY RANDOM() LIMIT 4
");
$relStmt->execute([$product['category_id'], $id]);
$relatedProducts = $relStmt->fetchAll();

// ── التقييمات من DB ───────────────────────────────────────────
$revStmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at,
           u.name AS reviewer_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.product_id = ? AND r.status = 'approved'
    ORDER BY r.created_at DESC
");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();

$avgRating   = 0;
$reviewCount = count($reviews);
if ($reviewCount > 0) {
    $avgRating = array_sum(array_column($reviews, 'rating')) / $reviewCount;
}

// ── Wishlist اليوزر ───────────────────────────────────────────
$inWishlist = false;
if (!empty($_SESSION['logged_in'])) {
    $wStmt = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
    $wStmt->execute([$_SESSION['user_id'], $id]);
    $inWishlist = (bool)$wStmt->fetchColumn();
}

// ── معالجة AJAX ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // إضافة للكارت
    if ($_POST['action'] === 'add_to_cart') {
        if (empty($_SESSION['logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'login_required']);
            exit;
        }
        $qty    = max(1, (int)($_POST['quantity'] ?? 1));
        $userId = $_SESSION['user_id'];

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
            VALUES (?, ?, ?)
            ON CONFLICT (cart_id, product_id)
            DO UPDATE SET quantity = cart_items.quantity + EXCLUDED.quantity
        ")->execute([$cartId, $id, $qty]);

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        echo json_encode(['success' => true, 'cart_count' => (int)$stmt->fetchColumn()]);
        exit;
    }

    // wishlist
    if ($_POST['action'] === 'toggle_wishlist') {
        if (empty($_SESSION['logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'login_required']);
            exit;
        }
        $userId = $_SESSION['user_id'];
        $check  = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$userId, $id]);

        if ($check->fetch()) {
            $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$userId, $id]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$userId, $id]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0,0) rotate(0deg); } 100% { transform: translate(50px,30px) rotate(20deg); } }
        .details-container { padding: 120px 20px 50px; max-width: 1100px; margin: 0 auto; }
        .product-navigation { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .nav-arrows { display: flex; gap: 10px; }
        .nav-arrow { width: 45px; height: 45px; border-radius: 50%; background: var(--card-bg); border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; color: var(--text-color); text-decoration: none; }
        .nav-arrow:hover:not(.disabled) { background: var(--pink); color: white; border-color: var(--pink); transform: translateX(-2px); }
        .nav-arrow.right:hover:not(.disabled) { transform: translateX(2px); }
        .nav-arrow.disabled { opacity: 0.3; cursor: not-allowed; pointer-events: none; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--secondary-text); text-decoration: none; font-weight: 500; transition: color 0.3s; cursor: pointer; background: none; border: none; font-size: inherit; font-family: inherit; }
        .back-link:hover { color: var(--pink); }
        .product-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: var(--card-bg); border-radius: 25px; padding: 40px; box-shadow: 0 15px 40px var(--shadow); opacity: 0; animation: slideUp 0.6s forwards; }
        @media (max-width: 900px) { .product-layout { grid-template-columns: 1fr; padding: 20px; gap: 30px; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .product-image-box { background: #fdfbf9; border-radius: 20px; height: 450px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        body.dark-mode .product-image-box { background: #222; }
        .product-image-box img { max-width: 90%; max-height: 90%; object-fit: contain; transition: transform 0.5s ease; }
        .product-image-box:hover img { transform: scale(1.05); }
        .category-badge { position: absolute; top: 20px; left: 20px; background: var(--pink); color: #fff; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .product-info { display: flex; flex-direction: column; justify-content: center; }
        .product-title { font-family: 'Playfair Display', serif; font-size: 42px; color: var(--text-color); margin-bottom: 15px; line-height: 1.2; }
        .product-price { font-size: 28px; font-weight: bold; color: var(--pink); margin-bottom: 25px; }
        .product-description { color: var(--secondary-text); line-height: 1.8; margin-bottom: 30px; font-size: 15px; border-bottom: 1px solid #eee; padding-bottom: 25px; }
        body.dark-mode .product-description { border-bottom-color: #333; }
        .action-section { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .quantity-selector { display: flex; align-items: center; background: #f5f5f5; border-radius: 25px; padding: 5px; }
        body.dark-mode .quantity-selector { background: #333; }
        .qty-btn-detail { width: 40px; height: 40px; border-radius: 50%; border: none; background: #fff; color: var(--text-color); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 16px; }
        body.dark-mode .qty-btn-detail { background: #444; }
        .qty-btn-detail:hover { background: var(--pink); color: #fff; }
        .qty-input-detail { width: 50px; text-align: center; border: none; background: transparent; font-size: 18px; font-weight: 600; color: var(--text-color); pointer-events: none; }
        .add-to-cart-btn { flex: 1; padding: 15px 30px; background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff; border: none; border-radius: 50px; font-weight: bold; font-size: 16px; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 20px rgba(255,154,158,0.4); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .add-to-cart-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 25px rgba(255,154,158,0.5); }
        .add-to-cart-btn.success { background: #28a745 !important; }
        .wishlist-btn { width: 50px; height: 50px; border-radius: 50%; border: 1px solid #ddd; background: var(--card-bg); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color: #888; }
        .wishlist-btn:hover, .wishlist-btn.active { color: #ff6b81; border-color: #ff6b81; }
        .product-rating { margin: 10px 0; display: flex; align-items: center; gap: 10px; }
        .stars-avg i { font-size: 18px; margin-right: 2px; }
        .reviews-section { margin-top: 60px; opacity: 0; animation: slideUp 0.6s forwards; animation-delay: 0.3s; }
        .review-item { border-bottom: 1px solid #eee; padding: 20px 0; }
        body.dark-mode .review-item { border-bottom-color: #333; }
        .related-section { margin-top: 60px; opacity: 0; animation: slideUp 0.6s forwards; animation-delay: 0.2s; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 32px; text-align: center; color: var(--text-color); margin-bottom: 40px; }
        .section-title span { color: var(--pink); }
        .related-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 25px; }
        @media (max-width: 992px) { .related-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 600px)  { .related-grid { grid-template-columns: 1fr; } }
        .product-card { background: var(--card-bg); border-radius: 15px; overflow: hidden; position: relative; transition: all 0.3s ease; box-shadow: 0 5px 20px var(--shadow); border: none; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px var(--shadow); }
        .product-img-box-small { width: 100%; height: 250px; background-color: #fdfbf9; display: flex; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box; overflow: hidden; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .product-img-box-small { background-color: #222; border-bottom-color: #333; }
        .product-img-box-small img { width: 100%; height: 100%; object-fit: contain; transition: transform 0.5s ease; }
        .product-card:hover .product-img-box-small img { transform: scale(1.05); }
        .card-content { padding: 15px; text-align: center; }
        .product-card h3 { font-size: 16px; margin: 0 0 5px; color: var(--text-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-card .price { margin: 0; color: var(--pink); font-weight: bold; font-size: 16px; }
        .card-actions { display: flex; align-items: center; justify-content: center; gap: 10px; opacity: 0; transition: opacity 0.4s ease; height: 40px; margin-top: 10px; }
        .product-card:hover .card-actions { opacity: 1; }
        .add-cart-btn-sm { flex: 1; padding: 8px 15px; border-radius: 20px; border: none; font-weight: bold; font-size: 12px; cursor: pointer; background-color: var(--primary); color: #fff; transition: 0.2s; }
        .add-cart-btn-sm:hover { background-color: #333; }
        .add-cart-btn-sm.success { background-color: #28a745 !important; }
        .fav-btn-sm { width: 36px; height: 36px; border-radius: 50%; border: 1px solid #ddd; background: var(--card-bg); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; color: #888; }
        .fav-btn-sm:hover, .fav-btn-sm.active { color: var(--pink); border-color: var(--pink); }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="details-container">

    <div class="product-navigation">
        <button onclick="goBack()" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
        <div class="nav-arrows">
            <?php if ($prevId): ?>
                <a href="product_details.php?id=<?= $prevId ?>" class="nav-arrow left" title="Previous">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            <?php else: ?>
                <span class="nav-arrow left disabled"><i class="fa-solid fa-chevron-left"></i></span>
            <?php endif; ?>

            <?php if ($nextId): ?>
                <a href="product_details.php?id=<?= $nextId ?>" class="nav-arrow right" title="Next">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="nav-arrow right disabled"><i class="fa-solid fa-chevron-right"></i></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-layout">
        <div class="product-image-box">
            <span class="category-badge"><?= htmlspecialchars($product['category']) ?></span>
            <img src="<?= htmlspecialchars($product['image']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 onerror="this.src='https://ui-avatars.com/api/?name=Toy&background=ff9a9e&color=fff'">
        </div>

        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

            <!-- التقييم من DB -->
            <div class="product-rating">
                <div class="stars-avg">
                    <?php
                    $full  = floor($avgRating);
                    $half  = ($avgRating - $full) >= 0.5;
                    $empty = 5 - $full - ($half ? 1 : 0);
                    for ($i = 0; $i < $full;  $i++) echo '<i class="fa-solid fa-star" style="color:#ffc107;"></i>';
                    if ($half)                       echo '<i class="fa-solid fa-star-half-alt" style="color:#ffc107;"></i>';
                    for ($i = 0; $i < $empty; $i++) echo '<i class="fa-regular fa-star" style="color:#ffc107;"></i>';
                    ?>
                </div>
                <span>(<?= $reviewCount ?> review<?= $reviewCount != 1 ? 's' : '' ?>)</span>
            </div>

            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>

            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>

            <div class="action-section">
                <div class="quantity-selector">
                    <button class="qty-btn-detail" onclick="updateQuantity(-1)"><i class="fa-solid fa-minus"></i></button>
                    <input type="text" class="qty-input-detail" id="quantity" value="1" readonly>
                    <button class="qty-btn-detail" onclick="updateQuantity(1)"><i class="fa-solid fa-plus"></i></button>
                </div>

                <button class="add-to-cart-btn" id="addBtn" onclick="addToCartFromDetails()">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>

                <button class="wishlist-btn <?= $inWishlist ? 'active' : '' ?>"
                        data-id="<?= $id ?>"
                        onclick="toggleFavorite(this)">
                    <i class="fa-<?= $inWishlist ? 'solid' : 'regular' ?> fa-heart"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- منتجات ذات صلة -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="related-section">
            <h2 class="section-title">You May <span>Also Love</span></h2>
            <div class="related-grid">
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="product-card">
                        <div class="product-img-box-small">
                            <a href="product_details.php?id=<?= $rel['id'] ?>">
                                <img src="<?= htmlspecialchars($rel['image']) ?>"
                                     alt="<?= htmlspecialchars($rel['name']) ?>"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Toy&background=ff9a9e&color=fff'">
                            </a>
                        </div>
                        <div class="card-content">
                            <h3><?= htmlspecialchars($rel['name']) ?></h3>
                            <p class="price">$<?= number_format($rel['price'], 2) ?></p>
                            <div class="card-actions">
                                <button class="add-cart-btn-sm" onclick="addToCart(<?= $rel['id'] ?>, this)">
                                    <i class="fa-solid fa-cart-plus"></i> Add
                                </button>
                                <button class="fav-btn-sm" data-id="<?= $rel['id'] ?>" onclick="toggleFavorite(this)">
                                    <i class="fa-regular fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- التقييمات من DB -->
    <div class="reviews-section">
        <h2 class="section-title">Customer <span>Reviews</span></h2>
        <div style="max-width:800px; margin:0 auto;">
            <?php if (empty($reviews)): ?>
                <div style="text-align:center; color:var(--secondary-text); padding:30px;">
                    <i class="fa-regular fa-star" style="font-size:40px;"></i>
                    <p>No reviews yet. Be the first to review this product!</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-item">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <strong style="color:var(--text-color);"><?= htmlspecialchars($rev['reviewer_name']) ?></strong>
                            <span style="font-size:12px; color:var(--secondary-text);">
                                <?= date('Y-m-d', strtotime($rev['created_at'])) ?>
                            </span>
                        </div>
                        <div style="margin-bottom:8px;">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fa-<?= $s <= $rev['rating'] ? 'solid' : 'regular' ?> fa-star"
                                   style="color:#ffc107; font-size:14px;"></i>
                            <?php endfor; ?>
                        </div>
                        <p style="color:var(--secondary-text); margin:0;">
                            <?= htmlspecialchars($rev['comment'] ?: 'No comment provided.') ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    const productId  = <?= $id ?>;
    const isLoggedIn = <?= !empty($_SESSION['logged_in']) ? 'true' : 'false' ?>;
    const qtyInput   = document.getElementById('quantity');
    const addBtn     = document.getElementById('addBtn');

    function goBack() {
        if (window.history.length > 1) window.history.back();
        else window.location.href = 'shop.php';
    }

    function updateQuantity(change) {
        let qty = parseInt(qtyInput.value) + change;
        if (qty >= 1 && qty <= 10) qtyInput.value = qty;
    }

    // ── Add to Cart (الزر الرئيسي) ────────────────────────────
    function addToCartFromDetails() {
        const qty      = parseInt(qtyInput.value);
        const original = addBtn.innerHTML;

        if (!isLoggedIn) {
            let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
            cart[productId] = (cart[productId] || 0) + qty;
            localStorage.setItem('teddy_cart', JSON.stringify(cart));
            addBtn.classList.add('success');
            addBtn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
            setTimeout(() => { addBtn.classList.remove('success'); addBtn.innerHTML = original; }, 1500);
            return;
        }

        fetch('product_details.php?id=' + productId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_to_cart&quantity=' + qty
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    addBtn.classList.add('success');
                    addBtn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
                    const badge = document.getElementById('cartCount');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.classList.toggle('hide', data.cart_count === 0);
                    }
                    setTimeout(() => { addBtn.classList.remove('success'); addBtn.innerHTML = original; }, 1500);
                }
            });
    }

    // ── Add to Cart (بطاقات ذات صلة) ─────────────────────────
    function addToCart(pid, btn) {
        const original = btn.innerHTML;

        if (!isLoggedIn) {
            let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
            cart[pid] = (cart[pid] || 0) + 1;
            localStorage.setItem('teddy_cart', JSON.stringify(cart));
            btn.classList.add('success');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
            setTimeout(() => { btn.classList.remove('success'); btn.innerHTML = original; }, 1200);
            return;
        }

        fetch('product_details.php?id=' + pid, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_to_cart&quantity=1'
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

    // ── Wishlist ──────────────────────────────────────────────
    function toggleFavorite(btn) {
        const pid  = btn.getAttribute('data-id');
        const icon = btn.querySelector('i');

        if (!isLoggedIn) {
            let favs = JSON.parse(localStorage.getItem('teddy_wishlist')) || [];
            const idx = favs.indexOf(String(pid));
            if (idx === -1) {
                favs.push(String(pid));
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

        fetch('product_details.php?id=' + productId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle_wishlist'
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

    // keyboard nav
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            const a = document.querySelector('.nav-arrow.left:not(.disabled)');
            if (a) window.location.href = a.href;
        } else if (e.key === 'ArrowRight') {
            const a = document.querySelector('.nav-arrow.right:not(.disabled)');
            if (a) window.location.href = a.href;
        }
    });
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>