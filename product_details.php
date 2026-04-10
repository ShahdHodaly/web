<?php
$pageTitle = "Product Details | Teddy Lap";
include 'products.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
$product = null;

if ($id && isset($products[$id])) {
    $product = $products[$id];
    $pageTitle = $product['name'] . " | Teddy Lap";
} else {
    header("Location: shop.php");
    exit;
}

// تجهيز روابط التنقل (السابق والتالي)
$productIds = array_keys($products);
$currentIndex = array_search($id, $productIds);

$prevId = ($currentIndex > 0) ? $productIds[$currentIndex - 1] : null;
$nextId = ($currentIndex < count($productIds) - 1) ? $productIds[$currentIndex + 1] : null;

// جلب المنتجات ذات الصلة من نفس القسم
$relatedProducts = [];
$currentCategory = $product['category'];
foreach ($products as $pId => $pItem) {
    if ($pId != $id && $pItem['category'] == $currentCategory) {
        $relatedProducts[$pId] = $pItem;
    }
}

// خلط واختيار 4 منتجات ذات صلة فقط
$keys = array_keys($relatedProducts);
shuffle($keys);
$keys = array_slice($keys, 0, 4);

$finalRelated = [];
foreach ($keys as $key) {
    $finalRelated[$key] = $relatedProducts[$key];
}
$relatedProducts = $finalRelated;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- ربط الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- ربط ملف الستايل العام -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- بداية قسم CSS الداخلي -->
    <style>
        /* ستايل خلفية الصفحة المتحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* ستايل الحاوية الرئيسية للصفحة */
        .details-container {
            padding: 120px 20px 50px;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* ستايل شريط التنقل بين المنتجات */
        .product-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .nav-arrows {
            display: flex;
            gap: 10px;
        }

        .nav-arrow {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-color);
            text-decoration: none;
        }

        .nav-arrow:hover:not(.disabled) {
            background: var(--pink);
            color: white;
            border-color: var(--pink);
            transform: translateX(-2px);
        }

        .nav-arrow.right:hover:not(.disabled) {
            transform: translateX(2px);
        }

        .nav-arrow.disabled {
            opacity: 0.3;
            cursor: not-allowed;
            pointer-events: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary-text);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            cursor: pointer;
            background: none;
            border: none;
            font-size: inherit;
            font-family: inherit;
        }
        .back-link:hover { color: var(--pink); }

        /* ستايل تخطيط عرض المنتج */
        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: var(--card-bg);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 40px var(--shadow);
            opacity: 0;
            animation: slideUp 0.6s forwards;
        }

        @media (max-width: 900px) {
            .product-layout { grid-template-columns: 1fr; padding: 20px; gap: 30px; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ستايل قسم صورة المنتج */
        .product-image-box {
            background: #fdfbf9;
            border-radius: 20px;
            height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        body.dark-mode .product-image-box { background: #222; }

        .product-image-box img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }
        .product-image-box:hover img { transform: scale(1.05); }

        .category-badge {
            position: absolute; top: 20px; left: 20px;
            background: var(--pink); color: #fff;
            padding: 5px 15px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px;
        }

        /* ستايل قسم معلومات المنتج */
        .product-info { display: flex; flex-direction: column; justify-content: center; }

        .product-title {
            font-family: 'Playfair Display', serif; font-size: 42px;
            color: var(--text-color); margin-bottom: 15px; line-height: 1.2;
        }

        .product-price {
            font-size: 28px; font-weight: bold;
            color: var(--pink); margin-bottom: 25px;
        }

        .product-description {
            color: var(--secondary-text); line-height: 1.8;
            margin-bottom: 30px; font-size: 15px;
            border-bottom: 1px solid #eee; padding-bottom: 25px;
        }
        body.dark-mode .product-description { border-bottom-color: #333; }

        /* ستايل قسم الكمية وأزرار الشراء */
        .action-section { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }

        .quantity-selector {
            display: flex; align-items: center;
            background: #f5f5f5; border-radius: 25px; padding: 5px;
        }
        body.dark-mode .quantity-selector { background: #333; }

        .qty-btn-detail {
            width: 40px; height: 40px; border-radius: 50%; border: none;
            background: #fff; color: var(--text-color); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; font-size: 16px;
        }
        body.dark-mode .qty-btn-detail { background: #444; }
        .qty-btn-detail:hover { background: var(--pink); color: #fff; }

        .qty-input-detail {
            width: 50px; text-align: center; border: none;
            background: transparent; font-size: 18px; font-weight: 600;
            color: var(--text-color); pointer-events: none;
        }

        .add-to-cart-btn {
            flex: 1; padding: 15px 30px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff; border: none; border-radius: 50px;
            font-weight: bold; font-size: 16px; cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(255, 154, 158, 0.4);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .add-to-cart-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 25px rgba(255, 154, 158, 0.5); }
        .add-to-cart-btn.success { background-color: #28a745 !important; }

        .wishlist-btn {
            width: 50px; height: 50px; border-radius: 50%;
            border: 1px solid #ddd; background: var(--card-bg);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; color: #888;
        }
        .wishlist-btn:hover, .wishlist-btn.active { color: #ff6b81; border-color: #ff6b81; }

        /* ستايل قسم التقييمات */
        .product-rating {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .stars-avg i {
            font-size: 18px;
            margin-right: 2px;
        }

        .reviews-section {
            margin-top: 60px;
            opacity: 0;
            animation: slideUp 0.6s forwards;
            animation-delay: 0.3s;
        }

        .review-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }
        body.dark-mode .review-item { border-bottom-color: #333; }

        /* ستايل قسم منتجات ذات صلة */
        .related-section {
            margin-top: 60px;
            opacity: 0;
            animation: slideUp 0.6s forwards;
            animation-delay: 0.2s;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            text-align: center;
            color: var(--text-color);
            margin-bottom: 40px;
        }
        .section-title span { color: var(--pink); }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        @media (max-width: 992px) {
            .related-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .related-grid { grid-template-columns: 1fr; }
        }

        /* ستايل بطاقة المنتج المصغرة */
        .product-card {
            background: var(--card-bg); border-radius: 15px; overflow: hidden;
            position: relative; transition: all 0.3s ease;
            box-shadow: 0 5px 20px var(--shadow); border: none;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px var(--shadow); }

        .product-img-box-small {
            width: 100%; height: 250px; background-color: #fdfbf9;
            display: flex; align-items: center; justify-content: center;
            padding: 20px; box-sizing: border-box; overflow: hidden; border-bottom: 1px solid #f0f0f0;
        }
        body.dark-mode .product-img-box-small { background-color: #222; border-bottom-color: #333; }

        .product-img-box-small img {
            width: 100%; height: 100%; object-fit: contain;
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-img-box-small img { transform: scale(1.05); }

        .card-content { padding: 15px; text-align: center; }
        .product-card h3 { font-size: 16px; margin: 0 0 5px; color: var(--text-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-card .price { margin: 0; color: var(--pink); font-weight: bold; font-size: 16px; }

        .card-actions { display: flex; align-items: center; justify-content: center; gap: 10px; opacity: 0; transition: opacity 0.4s ease; height: 40px; margin-top: 10px; }
        .product-card:hover .card-actions { opacity: 1; }

        .add-cart-btn-sm {
            flex: 1; padding: 8px 15px; border-radius: 20px; border: none;
            font-weight: bold; font-size: 12px; cursor: pointer;
            background-color: var(--primary); color: #fff; transition: 0.2s;
        }
        .add-cart-btn-sm:hover { background-color: #333; }
        .add-cart-btn-sm.success { background-color: #28a745 !important; }

        .fav-btn-sm {
            width: 36px; height: 36px; border-radius: 50%;
            border: 1px solid #ddd; background: var(--card-bg);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; color: #888;
        }
        .fav-btn-sm:hover, .fav-btn-sm.active { color: var(--pink); border-color: var(--pink); }
    </style>
</head>
<body>

<!-- قسم عناصر الخلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- تضمين شريط التنقل -->
<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<!-- حاوية محتوى تفاصيل المنتج -->
<div class="details-container">
    <!-- شريط التنقل العلوي (رجوع/سابق/تالي) -->
    <div class="product-navigation">
        <button onclick="goBack()" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>

        <div class="nav-arrows">
            <?php if ($prevId): ?>
                <a href="product_details.php?id=<?php echo $prevId; ?>" class="nav-arrow left" title="Previous Product">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            <?php else: ?>
                <span class="nav-arrow left disabled">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            <?php endif; ?>

            <?php if ($nextId): ?>
                <a href="product_details.php?id=<?php echo $nextId; ?>" class="nav-arrow right" title="Next Product">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="nav-arrow right disabled">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- تخطيط عرض تفاصيل المنتج -->
    <div class="product-layout">
        <!-- قسم صورة المنتج -->
        <div class="product-image-box">
            <span class="category-badge"><?php echo $product['category']; ?></span>
            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        </div>

        <!-- قسم معلومات المنتج -->
        <div class="product-info">
            <h1 class="product-title"><?php echo $product['name']; ?></h1>
            <!-- عرض متوسط التقييمات -->
            <div class="product-rating" id="averageRating">
                <div class="stars-avg" id="averageStars"></div>
                <span id="reviewCount">(0 reviews)</span>
            </div>
            <div class="product-price">$<?php echo $product['price']; ?></div>

            <p class="product-description">
                <?php echo $product['description']; ?>
            </p>

            <!-- قسم إجراءات الشراء (الكمية والسلة والمفضلة) -->
            <div class="action-section">
                <div class="quantity-selector">
                    <button class="qty-btn-detail" onclick="updateQuantity(-1)"><i class="fa-solid fa-minus"></i></button>
                    <input type="text" class="qty-input-detail" id="quantity" value="1" readonly>
                    <button class="qty-btn-detail" onclick="updateQuantity(1)"><i class="fa-solid fa-plus"></i></button>
                </div>

                <button class="add-to-cart-btn" id="addBtn" onclick="addToCartFromDetails()">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>

                <!-- زر إضافة للمفضلة -->
                <button class="wishlist-btn" data-id="<?php echo $id; ?>" onclick="toggleFavorite(this)">
                    <i class="fa-regular fa-heart"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- قسم منتجات ذات صلة -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="related-section">
            <h2 class="section-title">You May <span>Also Love</span></h2>
            <div class="related-grid">
                <?php foreach ($relatedProducts as $pId => $pItem): ?>
                    <div class="product-card">
                        <div class="product-img-box-small">
                            <a href="product_details.php?id=<?php echo $pId; ?>">
                                <img src="<?php echo $pItem['image']; ?>" alt="<?php echo $pItem['name']; ?>">
                            </a>
                        </div>
                        <div class="card-content">
                            <h3><?php echo $pItem['name']; ?></h3>
                            <p class="price">$<?php echo $pItem['price']; ?></p>
                            <div class="card-actions">
                                <button class="add-cart-btn-sm" onclick="addToCart('<?php echo $pId; ?>', this)">
                                    <i class="fa-solid fa-cart-plus"></i> Add
                                </button>
                                <!-- زر مفضلة مصغر -->
                                <button class="fav-btn-sm" data-id="<?php echo $pId; ?>" onclick="toggleFavorite(this)">
                                    <i class="fa-regular fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- قسم التقييمات والآراء -->
    <div class="reviews-section">
        <h2 class="section-title">Customer <span>Reviews</span></h2>
        <div id="reviews-list" style="max-width: 800px; margin: 0 auto;"></div>
        <div id="no-reviews" style="text-align: center; color: var(--secondary-text); padding: 30px; display: none;">
            <i class="fa-regular fa-star" style="font-size: 40px;"></i>
            <p>No reviews yet. Be the first to review this product!</p>
        </div>
    </div>

</div>

<!-- بداية قسم JavaScript -->
<script>
    // تعريف المتغيرات الأساسية
    const productId = '<?php echo $id; ?>';
    const qtyInput = document.getElementById('quantity');
    const addBtn = document.getElementById('addBtn');
    const REVIEWS_KEY = 'teddy_reviews';

    // دالة الرجوع للخلف
    function goBack() {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = 'shop.php';
        }
    }

    // دالة تحديث الكمية
    function updateQuantity(change) {
        let currentQty = parseInt(qtyInput.value);
        let newQty = currentQty + change;
        if (newQty >= 1 && newQty <= 10) {
            qtyInput.value = newQty;
        }
    }

    // دالة إضافة المنتج للسلة من التفاصيل
    function addToCartFromDetails() {
        let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
        const qty = parseInt(qtyInput.value);

        if (cart[productId]) {
            cart[productId] += qty;
        } else {
            cart[productId] = qty;
        }

        localStorage.setItem('teddy_cart', JSON.stringify(cart));

        addBtn.classList.add('success');
        addBtn.innerHTML = '<i class="fa-solid fa-check"></i> Added';

        setTimeout(() => {
            addBtn.classList.remove('success');
            addBtn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to Cart';
        }, 1500);
    }

    // دالة إضافة منتج للسلة (من البطاقات المصغرة)
    function addToCart(id, btn) {
        let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
        id = id.toString();

        if (cart[id]) {
            cart[id]++;
        } else {
            cart[id] = 1;
        }

        localStorage.setItem('teddy_cart', JSON.stringify(cart));

        btn.classList.add('success');
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';

        setTimeout(() => {
            btn.classList.remove('success');
            btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add';
        }, 1200);
    }

    // دوال نظام المفضلة
    function toggleFavorite(btn) {
        const id = btn.getAttribute('data-id');
        let favorites = JSON.parse(localStorage.getItem('teddy_wishlist')) || [];
        const icon = btn.querySelector('i');

        const index = favorites.indexOf(id);

        if (index === -1) {
            favorites.push(id);
            btn.classList.add('active');
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid');
        } else {
            favorites.splice(index, 1);
            btn.classList.remove('active');
            icon.classList.remove('fa-solid');
            icon.classList.add('fa-regular');
        }

        localStorage.setItem('teddy_wishlist', JSON.stringify(favorites));
    }

    // تهيئة حالة المفضلة عند التحميل
    function initFavorites() {
        let favorites = JSON.parse(localStorage.getItem('teddy_wishlist')) || [];
        document.querySelectorAll('.wishlist-btn, .fav-btn-sm').forEach(btn => {
            const id = btn.getAttribute('data-id');
            const icon = btn.querySelector('i');
            if (favorites.includes(id)) {
                btn.classList.add('active');
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
            }
        });
    }

    // نظام عرض التقييمات
    function renderProductReviews() {
        const allReviews = JSON.parse(localStorage.getItem(REVIEWS_KEY)) || {};
        const productReviews = allReviews[productId] || [];
        const reviewListDiv = document.getElementById('reviews-list');
        const noReviewsDiv = document.getElementById('no-reviews');
        const averageStarsDiv = document.getElementById('averageStars');
        const reviewCountSpan = document.getElementById('reviewCount');

        if (productReviews.length === 0) {
            reviewListDiv.innerHTML = '';
            noReviewsDiv.style.display = 'block';
            averageStarsDiv.innerHTML = '';
            reviewCountSpan.innerText = '(0 reviews)';
            return;
        }

        noReviewsDiv.style.display = 'none';

        // حساب متوسط التقييم
        const totalRating = productReviews.reduce((sum, r) => sum + r.rating, 0);
        const avgRating = totalRating / productReviews.length;
        const fullStars = Math.floor(avgRating);
        const halfStar = (avgRating - fullStars) >= 0.5;
        const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

        let starsHtml = '';
        for (let i = 0; i < fullStars; i++) starsHtml += '<i class="fa-solid fa-star" style="color: #ffc107;"></i>';
        if (halfStar) starsHtml += '<i class="fa-solid fa-star-half-alt" style="color: #ffc107;"></i>';
        for (let i = 0; i < emptyStars; i++) starsHtml += '<i class="fa-regular fa-star" style="color: #ffc107;"></i>';
        averageStarsDiv.innerHTML = starsHtml;
        reviewCountSpan.innerText = `(${productReviews.length} review${productReviews.length > 1 ? 's' : ''})`;

        // بناء قائمة التقييمات
        let reviewsHtml = '';
        productReviews.sort((a, b) => new Date(b.date) - new Date(a.date)).forEach(review => {
            let starHtml = '';
            for (let i = 1; i <= 5; i++) {
                starHtml += `<i class="fa-${i <= review.rating ? 'solid' : 'regular'} fa-star" style="color: #ffc107; font-size: 14px;"></i>`;
            }
            reviewsHtml += `
                <div class="review-item">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: var(--text-color);">${review.userName || 'Anonymous'}</strong>
                        <span style="font-size: 12px; color: var(--secondary-text);">${review.date}</span>
                    </div>
                    <div style="margin-bottom: 8px;">${starHtml}</div>
                    <p style="color: var(--secondary-text); margin: 0;">${review.comment || 'No comment provided.'}</p>
                </div>
            `;
        });
        reviewListDiv.innerHTML = reviewsHtml;
    }

    // التنقل بين المنتجات بالأسهم
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            const prevLink = document.querySelector('.nav-arrow.left:not(.disabled)');
            if (prevLink) {
                window.location.href = prevLink.href;
            }
        } else if (e.key === 'ArrowRight') {
            const nextLink = document.querySelector('.nav-arrow.right:not(.disabled)');
            if (nextLink) {
                window.location.href = nextLink.href;
            }
        }
    });

    // تشغيل الدوال عند تحميل الصفحة
    window.addEventListener('load', () => {
        initFavorites();
        renderProductReviews();
    });
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>