<?php
include 'products.php';
if (!isset($products) || !is_array($products)) {
    $products = [];
}
// استخراج التصنيفات الفريدة
$categories = [];
if (!empty($products)) {
    $categories = array_unique(array_column($products, 'category'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Teddy Lap</title>

    <!-- HTML: الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* CSS */

        /* حاوية الصفحة */
        .shop-container {
            padding: 50px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* عنوان الصفحة */
        .shop-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .shop-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        .shop-header p { color: var(--secondary-text); }

        /* شريط الفلتر والسورت */
        .filter-sort-bar {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        /* زر الفلتر الرئيسي */
        .filter-btn-main {
            background-color: var(--card-bg);
            border: 1px solid #ddd;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        .filter-btn-main i { font-size: 12px; }
        .filter-btn-main:hover {
            background-color: #ff6b81;
            color: white;
            border-color: #ff6b81;
        }

        /* بوكس الفلتر المنبثق */
        .filter-wrapper { position: relative; }
        .filter-popup {
            position: absolute;
            top: 45px;
            left: 0;
            width: 260px;
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            padding: 18px;
            z-index: 1000;
            display: none;
            border: 1px solid rgba(0,0,0,0.05);
        }
        body.dark-mode .filter-popup { border-color: #444; }
        .filter-popup.show { display: block; }

        /* شريط السعر */
        .price-range { margin: 15px 0; }
        .price-range h4 {
            font-size: 15px;
            margin-bottom: 12px;
            color: var(--text-color);
        }
        .slider-container { padding: 0 5px; }
        .single-slider { width: 100%; margin: 12px 0; }
        .price-values {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--secondary-text);
            margin-top: 8px;
        }

        /* أزرار الفلتر */
        .filter-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .filter-actions button {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .apply-btn { background: #ff6b81; color: white; }
        .apply-btn:hover { background: #ff4f6b; }
        .clear-btn {
            background: transparent;
            border: 1px solid #ddd;
            color: var(--text-color);
        }
        .clear-btn:hover {
            border-color: #ff6b81;
            color: #ff6b81;
        }

        /* زر السورت */
        .sort-btn-main {
            background-color: var(--card-bg);
            border: 1px solid #ddd;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        .sort-btn-main i { font-size: 12px; }
        .sort-btn-main:hover {
            background-color: #ff6b81;
            color: white;
            border-color: #ff6b81;
        }

        /* القائمة المنسدلة للترتيب */
        .sort-wrapper { position: relative; }
        .sort-dropdown {
            position: absolute;
            top: 45px;
            left: 0;
            width: 200px;
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            padding: 8px 0;
            z-index: 1000;
            display: none;
            border: 1px solid rgba(0,0,0,0.05);
        }
        body.dark-mode .sort-dropdown { border-color: #444; }
        .sort-dropdown.show { display: block; }
        .sort-option {
            padding: 10px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-color);
            font-size: 13px;
        }
        .sort-option:hover { background-color: #ff6b81; color: white; }
        .sort-option.active { background-color: #ff6b81; color: white; font-weight: 500; }

        /* فلاتر التصنيفات */
        .category-filter {
            display: flex;
            justify-content: flex-start;
            gap: 8px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background-color: var(--card-bg);
            border: 1px solid #ddd;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            color: var(--text-color);
        }
        body.dark-mode .filter-btn { border-color: #444; }
        .filter-btn:hover, .filter-btn.active {
            background-color: #ff6b81;
            color: #fff;
            border-color: #ff6b81;
        }

        /* شبكة المنتجات */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }
        @media (max-width: 992px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }

        /* كرت المنتج */
        .product-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: none;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }

        /* صندوق الصورة */
        .product-img-box {
            width: 100%; height: 250px; background-color: var(--card-bg);
            display: flex; align-items: center; justify-content: center;
            padding: 10px; box-sizing: border-box;
        }
        .product-img-box img {
            max-width: 100%; max-height: 100%; object-fit: contain;
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-img-box img { transform: scale(1.05); }

        /* محتوى الكرت */
        .card-content { padding: 15px; text-align: center; }
        .product-card h3 { font-size: 16px; margin: 0 0 5px; color: var(--text-color); }
        .product-card .price { margin: 0; color: #d63384; font-weight: bold; font-size: 18px; }
        .product-card p.desc { font-size: 12px; color: var(--secondary-text); margin: 10px 0 15px; height: 34px; overflow: hidden; }

        /* أزرار الأكشن */
        .card-actions {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            opacity: 0; transition: opacity 0.4s ease; height: 50px;
        }
        .product-card:hover .card-actions { opacity: 1; }

        .add-cart-btn {
            flex: 1; padding: 10px; background: #333; color: #fff; border: none;
            border-radius: 25px; cursor: pointer; font-weight: bold; font-size: 13px;
            transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 5px;
        }
        .add-cart-btn:hover { background: #555; }
        .add-cart-btn.success { background-color: #28a745 !important; }

        /* زر المفضلة */
        .fav-btn {
            width: 40px; height: 40px; border-radius: 50%;
            border: 1px solid #ddd; background: var(--card-bg);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease; color: #888;
        }
        body.dark-mode .fav-btn { border-color: #444; color: #aaa; }
        .fav-btn:hover, .fav-btn.active { color: #ff6b81; border-color: #ff6b81; }

        /* أنيميشن العنوان */
        .animated-title {
            font-family: 'Playfair Display', serif; font-size: 72px; font-weight: 700;
            background: linear-gradient(45deg, #ff6b81, #ff9a9e, #fbc2eb);
            background-size: 300% 300%; -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeSlide 1.2s ease forwards, gradientMove 5s ease infinite;
            letter-spacing: 4px;
        }
        @media (max-width: 768px) { .animated-title { font-size: 48px; } }
        @keyframes gradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes fadeSlide { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }

        /* زر العودة للأعلى */
        .back-to-top {
            position: fixed; bottom: 30px; right: 30px;
            width: 50px; height: 50px; background: var(--pink);
            color: #fff; border: none; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 20px;
            box-shadow: 0 5px 20px rgba(255, 107, 129, 0.4);
            opacity: 0; visibility: hidden; transition: all 0.3s ease; z-index: 999;
        }
        .back-to-top.show { opacity: 1; visibility: visible; }
        .back-to-top:hover { background: #ff4f6b; transform: translateY(-3px); }
    </style>
</head>
<body>

<!-- HTML: شريط التنقل -->
<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<!-- HTML: محتوى المتجر -->
<div class="shop-container">

    <div class="shop-header">
        <h1 class="animated-title">Teddy Lap</h1>
        <p>Find the perfect toy for your little one</p>
    </div>

    <!-- HTML: شريط الفلترة والترتيب -->
    <div class="filter-sort-bar">
        <!-- زر الفلتر -->
        <div class="filter-wrapper">
            <button class="filter-btn-main" onclick="toggleFilterPopup()">
                <i class="fa-solid fa-sliders"></i> Filter
            </button>
            <div class="filter-popup" id="filterPopup">
                <div class="price-range">
                    <!-- تم تغيير القيم إلى 100 لتشمل جميع المنتجات -->
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

        <!-- زر السورت -->
        <div class="sort-wrapper">
            <button class="sort-btn-main" onclick="toggleSortDropdown()">
                <i class="fa-solid fa-arrow-up-wide-short"></i> Sort: <span id="selectedSort">Default</span>
            </button>
            <!-- تم حذف خيارات الترتيب غير العاملة -->
            <div class="sort-dropdown" id="sortDropdown">
                <div class="sort-option" onclick="selectSort('default', 'Default')">Default</div>
                <div class="sort-option" onclick="selectSort('price_low', 'Price Low → High')">Price Low → High</div>
                <div class="sort-option" onclick="selectSort('price_high', 'Price High → Low')">Price High → Low</div>
            </div>
        </div>
    </div>

    <!-- HTML: أزرار تصفية التصنيفات -->
    <div class="category-filter">
        <button class="filter-btn active" onclick="filterProducts('all', event)">All Toys</button>
        <?php foreach ($categories as $cat): ?>
            <button class="filter-btn" onclick="filterProducts('<?php echo htmlspecialchars($cat); ?>', event)">
                <?php echo htmlspecialchars($cat); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- HTML: شبكة المنتجات -->
    <div class="product-grid" id="productGrid">
        <?php foreach ($products as $id => $item): ?>
            <!-- تم حذف data-popularity, data-newest, data-rating -->
            <div class="product-card"
                 data-category="<?php echo $item['category']; ?>"
                 data-price="<?php echo $item['price']; ?>"
                 data-id="<?php echo $id; ?>">

                <div class="product-img-box">
                    <a href="product_details.php?id=<?php echo $id; ?>">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                    </a>
                </div>

                <div class="card-content">
                    <h3><?php echo $item['name']; ?></h3>
                    <p class="price">$<?php echo $item['price']; ?></p>
                    <p class="desc"><?php echo substr($item['description'], 0, 50); ?>...</p>

                    <div class="card-actions">
                        <button class="add-cart-btn" onclick="addToCart('<?php echo $id; ?>', this)">
                            <i class="fa-solid fa-cart-plus"></i> Add
                        </button>
                        <button class="fav-btn" data-id="<?php echo $id; ?>" onclick="toggleFavorite(this)">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- HTML: زر العودة للأعلى -->
<button id="backToTop" class="back-to-top" onclick="scrollToTop()">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script>
    /* JavaScript: منطق الفلترة والترتيب */

    let currentCategory = 'all';
    let currentSort = 'default';
    let currentSortText = 'Default';
    let maxPriceFilter = 100;

    // فلتر التصنيفات
    function filterProducts(category, event) {
        currentCategory = category;
        document.querySelectorAll('.category-filter .filter-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        applyFiltersAndSort();
    }

    // فلتر السعر
    function toggleFilterPopup() { document.getElementById('filterPopup').classList.toggle('show'); }
    function updateSinglePriceLabel() {
        let price = parseInt(document.getElementById('priceSlider').value);
        document.getElementById('selectedPrice').innerText = price;
        maxPriceFilter = price;
    }
    function applyPriceFilter() {
        updateSinglePriceLabel();
        applyFiltersAndSort();
        toggleFilterPopup();
    }
    function clearPriceFilter() {
        maxPriceFilter = 100;
        document.getElementById('priceSlider').value = 100;
        updateSinglePriceLabel();
        applyFiltersAndSort();
        toggleFilterPopup();
    }

    // قائمة الترتيب
    function toggleSortDropdown() { document.getElementById('sortDropdown').classList.toggle('show'); }
    function selectSort(type, text) {
        currentSort = type;
        currentSortText = text;
        document.getElementById('selectedSort').innerText = text;
        document.querySelectorAll('.sort-option').forEach(opt => opt.classList.remove('active'));
        event.target.classList.add('active');
        applyFiltersAndSort();
        toggleSortDropdown();
    }

    // الدالة الموحدة للفلترة والترتيب
    function applyFiltersAndSort() {
        let products = Array.from(document.querySelectorAll('.product-card'));

        // 1. فلتر التصنيف
        if (currentCategory !== 'all') {
            products = products.filter(p => p.dataset.category === currentCategory);
        }

        // 2. فلتر السعر
        products = products.filter(p => parseFloat(p.dataset.price) <= maxPriceFilter);

        // 3. الترتيب (تم حذف popularity, newest, rating)
        if (currentSort === 'price_low') {
            products.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        } else if (currentSort === 'price_high') {
            products.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
        }

        // تحديث الـ DOM
        const grid = document.getElementById('productGrid');
        document.querySelectorAll('.product-card').forEach(card => card.style.display = 'none');
        products.forEach(card => {
            card.style.display = 'block';
            grid.appendChild(card);
        });
    }

    // سلة الشراء
    function addToCart(id, btn) {
        let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
        id = id.toString();
        cart[id] = (cart[id] || 0) + 1;
        localStorage.setItem('teddy_cart', JSON.stringify(cart));

        btn.classList.add('success');
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
        setTimeout(() => { btn.classList.remove('success'); btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add'; }, 1200);
    }

    // المفضلة
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

    // تهيئة المفضلة والزر
    function initFavorites() {
        let favorites = JSON.parse(localStorage.getItem('teddy_wishlist')) || [];
        document.querySelectorAll('.fav-btn').forEach(btn => {
            const id = btn.getAttribute('data-id');
            const icon = btn.querySelector('i');
            if (favorites.includes(id)) {
                btn.classList.add('active');
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
            }
        });
    }

    function toggleBackToTopButton() {
        const btn = document.getElementById('backToTop');
        if (window.scrollY > 300) btn.classList.add('show');
        else btn.classList.remove('show');
    }

    function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }

    window.addEventListener('scroll', toggleBackToTopButton);
    window.addEventListener('load', () => {
        initFavorites();
        updateSinglePriceLabel();
        toggleBackToTopButton();
    });

    // إغلاق القوائم عند النقر خارجها
    window.addEventListener('click', function(e) {
        const filterPopup = document.getElementById('filterPopup');
        const filterBtn = document.querySelector('.filter-btn-main');
        const sortDropdown = document.getElementById('sortDropdown');
        const sortBtn = document.querySelector('.sort-btn-main');

        if (filterPopup && filterBtn && !filterPopup.contains(e.target) && !filterBtn.contains(e.target)) filterPopup.classList.remove('show');
        if (sortDropdown && sortBtn && !sortDropdown.contains(e.target) && !sortBtn.contains(e.target)) sortDropdown.classList.remove('show');
    });
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>