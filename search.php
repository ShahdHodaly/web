<?php
// قسم معالجة البيانات والبحث PHP
$pageTitle = "Search | Teddy Lap";

// جلب بيانات المنتجات
include 'products.php';

// معالجة البحث
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
$categories = [];

// استخراج الأقسام من المنتجات
if (isset($products)) {
    $categories = array_unique(array_column($products, 'category'));
}

// منطق البحث
if (!empty($query) && isset($products)) {
    foreach ($products as $id => $item) {
        if (
                stripos($item['name'], $query) !== false ||
                stripos($item['description'], $query) !== false ||
                stripos($item['category'], $query) !== false
        ) {
            $results[$id] = $item;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- بداية قسم CSS -->
    <style>
        /* حاوية الصفحة */
        .search-container {
            padding: 50px 20px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* خلفية متحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }

        @keyframes floatShapes {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50px, 30px) rotate(20deg); }
        }

        /* قسم البحث العلوي */
        .search-hero {
            text-align: center;
            margin-bottom: 30px;
            opacity: 0;
            transform: translateY(-40px);
            transition: all 2s cubic-bezier(0.25, 1, 0.5, 1);
            position: relative;
            z-index: 10;
        }
        .search-hero.visible { opacity: 1; transform: translateY(0); }
        .search-hero h1 { font-family: 'Playfair Display', serif; font-size: 48px; color: var(--text-color); margin-bottom: 30px; }

        /* مربع البحث */
        .search-box {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .search-input {
            width: 100%; padding: 18px 60px 18px 30px; border-radius: 50px; border: 2px solid transparent;
            background-color: var(--card-bg); box-shadow: 0 10px 30px var(--shadow);
            font-family: 'Poppins', sans-serif; font-size: 16px; color: var(--text-color);
            transition: all 0.4s ease; box-sizing: border-box;
        }
        .search-input:focus { outline: none; border-color: var(--pink); }

        .search-btn {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            background-color: var(--pink); color: #fff; border: none;
            width: 45px; height: 45px; border-radius: 50%; cursor: pointer; font-size: 18px;
            transition: all 0.3s ease; z-index: 3;
        }
        .search-btn:hover { background-color: var(--primary); }

        /* صندوق الاقتراحات والسجل */
        .search-suggestions-dropdown {
            position: absolute;
            top: 100%; left: 0; width: 100%;
            background-color: var(--card-bg); border-radius: 25px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            margin-top: -15px; padding-top: 15px; padding-bottom: 15px; z-index: 100;
            display: none; max-height: 400px; overflow-y: auto;
        }
        .search-suggestions-dropdown.show { display: block; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* رأس قسم السجل */
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 25px;
            color: var(--secondary-text);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .clear-history-btn {
            background: none;
            border: none;
            color: var(--pink);
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            transition: color 0.2s;
        }
        .clear-history-btn:hover { color: var(--primary); text-decoration: underline; }

        .suggestion-item {
            padding: 12px 25px; cursor: pointer; transition: background 0.2s;
            display: flex; align-items: center; gap: 15px; color: var(--text-color); text-align: left;
        }
        .suggestion-item:hover { background-color: var(--shadow); }
        .suggestion-item i { color: var(--secondary-text); width: 20px; text-align: center; }
        .suggestion-item:hover i { color: var(--pink); }

        /* شريط الخيارات (Filter و Sort) */
        .filter-sort-bar {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            padding: 0 10px;
        }

        /* زر الفلتر */
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
        .filter-btn-main i {
            font-size: 12px;
        }
        .filter-btn-main:hover {
            background-color: #ff6b81;
            color: white;
            border-color: #ff6b81;
        }

        /* بوكس الفلتر المنبثق */
        .filter-wrapper {
            position: relative;
        }
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
        body.dark-mode .filter-popup {
            border-color: #444;
        }
        .filter-popup.show {
            display: block;
        }

        /* شريط السعر */
        .price-range {
            margin: 15px 0;
        }
        .price-range h4 {
            font-size: 15px;
            margin-bottom: 12px;
            color: var(--text-color);
        }
        .slider-container {
            padding: 0 5px;
        }
        .single-slider {
            width: 100%;
            margin: 12px 0;
        }
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
        .apply-btn {
            background: #ff6b81;
            color: white;
        }
        .apply-btn:hover {
            background: #ff4f6b;
        }
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
        .sort-btn-main i {
            font-size: 12px;
        }
        .sort-btn-main:hover {
            background-color: #ff6b81;
            color: white;
            border-color: #ff6b81;
        }

        /* القائمة المنسدلة للترتيب */
        .sort-wrapper {
            position: relative;
        }
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
        body.dark-mode .sort-dropdown {
            border-color: #444;
        }
        .sort-dropdown.show {
            display: block;
        }
        .sort-option {
            padding: 10px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-color);
            font-size: 13px;
        }
        .sort-option:hover {
            background-color: #ff6b81;
            color: white;
        }
        .sort-option.active {
            background-color: #ff6b81;
            color: white;
            font-weight: 500;
        }

        /* الاقتراحات (Suggestions) */
        .suggestions-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        .sugg-label { font-size: 13px; color: var(--secondary-text); font-weight: 500; }
        .sugg-chips { display: flex; gap: 10px; flex-wrap: wrap; }
        .sugg-chip {
            background-color: var(--card-bg); color: var(--text-color); padding: 8px 16px;
            border-radius: 20px; text-decoration: none; font-size: 13px;
            transition: all 0.2s ease; border: 1px solid rgba(0,0,0,0.08); cursor: pointer;
        }
        .sugg-chip:hover { background-color: var(--pink); color: #fff; border-color: var(--pink); }
        .sugg-chip.highlight { font-weight: 600; }
        .sugg-chip.highlight i { color: var(--pink); }
        .sugg-chip.highlight:hover i { color: #fff; }

        /* قسم الفلترة */
        .filter-section { display: flex; justify-content: flex-start; gap: 8px; margin-bottom: 35px; flex-wrap: wrap; padding: 0 10px; }
        .filter-btn {
            background-color: var(--card-bg); border: 1px solid rgba(0,0,0,0.08); padding: 6px 14px;
            border-radius: 20px; cursor: pointer; font-size: 12px; transition: all 0.3s ease;
            color: var(--text-color);
        }
        .filter-btn:hover, .filter-btn.active { background-color: var(--pink); color: #fff; border-color: var(--pink); }

        /* شبكة المنتجات */
        .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; }
        @media (max-width: 992px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }

        /* بطاقة المنتج */
        .product-card {
            background: var(--card-bg); border-radius: 15px; overflow: hidden;
            position: relative; transition: all 0.3s ease;
            box-shadow: 0 5px 20px var(--shadow);
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px var(--shadow); }

        .product-img-box { width: 100%; height: 250px; background-color: var(--card-bg); display: flex; align-items: center; justify-content: center; padding: 10px; box-sizing: border-box; position: relative;}
        .product-img-box a { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
        .product-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.5s ease; }
        .product-card:hover .product-img-box img { transform: scale(1.05); }

        .card-content { padding: 15px; text-align: center; }
        .product-card h3 { font-size: 16px; margin: 0 0 5px; color: var(--text-color); }
        .product-card .price { margin: 0; color: var(--pink); font-weight: bold; font-size: 18px; }
        .product-card p.desc { font-size: 12px; color: var(--secondary-text); margin: 10px 0 15px; height: 34px; overflow: hidden; }

        /* أزرار الأكشن */
        .card-actions { display: flex; align-items: center; justify-content: center; gap: 10px; opacity: 0; transition: opacity 0.4s ease; height: 50px; }
        .product-card:hover .card-actions { opacity: 1; }

        .add-cart-btn { flex: 1; padding: 10px 25px; border-radius: 30px; border: none; font-weight: bold; font-size: 13px; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 10px; background-color: var(--primary); color: #fff; }
        .add-cart-btn:hover { background-color: #333; }
        .add-cart-btn.success { background-color: #28a745 !important; }

        .fav-btn { width: 40px; height: 40px; border-radius: 50%; border: 1px solid #ddd; background: var(--card-bg); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; color: #888; }
        .fav-btn:hover, .fav-btn.active { color: var(--pink); border-color: var(--pink); }

        /* رسالة عدم وجود نتائج */
        .no-results { grid-column: 1 / -1; text-align: center; padding: 80px 20px; display: flex; flex-direction: column; align-items: center; }
        .no-results i { font-size: 70px; color: var(--pink); margin-bottom: 25px; opacity: 0.9; }
        .no-results p { font-size: 22px; color: var(--text-color); font-weight: 600; }

        /* زر العودة للأعلى */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--pink);
            color: #fff;
            border: none;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 5px 15px rgba(255, 107, 129, 0.3);
            transition: all 0.3s ease;
            z-index: 999;
        }
        .back-to-top:hover {
            background: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 107, 129, 0.4);
        }
        .back-to-top.show {
            display: flex;
        }
    </style>
</head>
<body>

<!-- أشكال الخلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- النافبار -->
<?php include 'navbar.php'; ?>

<!-- محتوى صفحة البحث -->
<div class="search-container">

    <!-- قسم العنوان والبحث -->
    <div class="search-hero">
        <h1>Find Your Perfect <span style="color: var(--pink);">Toy</span></h1>

        <form class="search-box" method="GET" action="search.php" autocomplete="off" id="searchForm">
            <input type="text" name="q" class="search-input" placeholder="Search by type, name…" value="<?php echo htmlspecialchars($query); ?>" id="searchInput">
            <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            <div class="search-suggestions-dropdown" id="searchDropdown"></div>
        </form>
    </div>

    <!-- شريط الخيارات (فلتر، سورت، اقتراحات) -->
    <div class="filter-sort-bar">
        <!-- فلتر -->
        <div class="filter-wrapper">
            <button class="filter-btn-main" onclick="toggleFilterPopup()">
                <i class="fa-solid fa-sliders"></i> Filter
            </button>
            <!-- بوكس الفلتر المنبثق -->
            <div class="filter-popup" id="filterPopup">
                <div class="price-range">
                    <h4>Max Price: $<span id="selectedPrice">100</span></h4>
                    <div class="slider-container">
                        <input type="range" id="priceSlider" class="single-slider" min="0" max="100" value="100" step="5" oninput="updateSinglePriceLabel()">
                        <div class="price-values">
                            <span>$0</span>
                            <span>100</span>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="apply-btn" onclick="applyPriceFilter()">Apply</button>
                    <button class="clear-btn" onclick="clearPriceFilter()">Clear</button>
                </div>
            </div>
        </div>

        <!-- سورت -->
        <div class="sort-wrapper">
            <button class="sort-btn-main" onclick="toggleSortDropdown()">
                <i class="fa-solid fa-arrow-up-wide-short"></i> Sort: <span id="selectedSort">Default</span>
            </button>
            <!-- القائمة المنسدلة للترتيب -->
            <div class="sort-dropdown" id="sortDropdown">
                <div class="sort-option" onclick="selectSort('default', 'Default')">Default</div>
                <div class="sort-option" onclick="selectSort('price_low', 'Price Low → High')">Price Low → High</div>
                <div class="sort-option" onclick="selectSort('price_high', 'Price High → Low')">Price High → Low</div>
                <div class="sort-option" onclick="selectSort('popularity', 'Popularity')">Popularity</div>
                <div class="sort-option" onclick="selectSort('newest', 'Newest')">Newest</div>
                <div class="sort-option" onclick="selectSort('rating', 'Rating')">Rating</div>
            </div>
        </div>

        <!-- Suggestions على اليمين -->
        <div class="suggestions-group">
            <span class="sugg-label">Suggestions:</span>
            <div class="sugg-chips">
                <a href="#" class="sugg-chip highlight"><i class="fa-solid fa-fire"></i> Trending Now</a>
                <a href="#" class="sugg-chip">New Arrivals</a>
            </div>
        </div>
    </div>

    <!-- قسم فلترة الأقسام -->
    <div class="filter-section" id="filterSection">
        <button class="filter-btn active" onclick="filterByCategory('all')"><i class="fa-solid fa-border-all"></i> All</button>
        <?php foreach ($categories as $cat): ?>
            <button class="filter-btn" onclick="filterByCategory('<?php echo $cat; ?>')">
                <i class="fa-solid fa-tag"></i> <?php echo $cat; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- قسم عرض النتائج -->
    <div class="results-section">
        <?php if (!empty($query)): ?>
            <div style="text-align: center; color: var(--secondary-text); margin-bottom: 30px;">
                Showing results for "<span style="color: var(--pink); font-weight:bold;"><?php echo htmlspecialchars($query); ?></span>"
            </div>
        <?php endif; ?>

        <!-- شبكة المنتجات -->
        <div class="product-grid" id="productGrid">
            <?php
            $display_items = !empty($query) ? $results : $products;
            if (!empty($display_items)):
                ?>
                <?php foreach ($display_items as $id => $item): ?>
                <!-- بطاقة منتج -->
                <div class="product-card" data-category="<?php echo $item['category']; ?>"
                     data-price="<?php echo $item['price']; ?>"
                     data-popularity="<?php echo $item['popularity'] ?? 0; ?>"
                     data-newest="<?php echo $item['newest'] ?? 0; ?>"
                     data-rating="<?php echo $item['rating'] ?? 0; ?>"
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
                <!-- رسالة عدم وجود نتائج -->
            <?php else: ?>
                <div class="no-results">
                    <i class="fa-solid fa-face-sad-tear"></i>
                    <p>No teddies found…</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- زر العودة للأعلى -->
<button id="backToTop" class="back-to-top" title="Back to Top">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- بداية قسم JavaScript -->
<script>
    // المتغيرات العامة وبيانات المنتجات
    const allProducts = <?php echo json_encode($products); ?>;
    const HISTORY_KEY = 'teddy_search_history';
    let searchHistory = JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];

    // متغيرات الفلتر والسورت
    let currentCategory = 'all';
    let currentSort = 'default';
    let currentSortText = 'Default';
    let maxPriceFilter = 200;

    // تحميل الصفحة
    window.addEventListener('load', () => {
        document.querySelector('.search-hero').classList.add('visible');
        initFavorites();
        updateSinglePriceLabel();
    });

    // منطق البحث والسجل
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchForm = document.getElementById('searchForm');

    // حدث الكتابة في البحث
    searchInput.addEventListener('input', function() {
        const val = this.value.trim().toLowerCase();
        if (val.length < 1) {
            renderSearchHistory();
            return;
        }
        // البحث في المنتجات
        const filtered = Object.values(allProducts).filter(p =>
            p.name.toLowerCase().includes(val) || (p.category && p.category.toLowerCase().includes(val))
        ).slice(0, 6);

        if (filtered.length > 0) {
            renderProductSuggestions(filtered, val);
            searchDropdown.classList.add('show');
        } else {
            searchDropdown.classList.remove('show');
        }
    });

    // حدث التركيز على الحقل
    searchInput.addEventListener('focus', function() {
        if(this.value.trim() === '') {
            renderSearchHistory();
        }
    });

    // حدث إرسال البحث
    searchForm.addEventListener('submit', function() {
        const val = searchInput.value.trim();
        if(val) saveSearchHistory(val);
    });

    // إخفاء القائمة
    searchInput.addEventListener('blur', () => {
        setTimeout(() => { searchDropdown.classList.remove('show'); }, 200);
    });

    // وظائف السجل والاقتراحات
    function saveSearchHistory(query) {
        searchHistory = searchHistory.filter(item => item.toLowerCase() !== query.toLowerCase());
        searchHistory.unshift(query);
        if (searchHistory.length > 5) searchHistory.pop();
        localStorage.setItem(HISTORY_KEY, JSON.stringify(searchHistory));
    }

    function renderSearchHistory() {
        if (searchHistory.length === 0) {
            searchDropdown.classList.remove('show');
            return;
        }

        let html = `
            <div class="history-header">
                <span>Recent Searches</span>
                <button type="button" class="clear-history-btn" onclick="clearHistory()">Clear All</button>
            </div>
        `;

        searchHistory.forEach(term => {
            html += `<div class="suggestion-item" onmousedown="selectHistory('${term}')">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>${term}</span>
                     </div>`;
        });
        searchDropdown.innerHTML = html;
        searchDropdown.classList.add('show');
    }

    function renderProductSuggestions(products, query) {
        let html = '';
        products.forEach(p => {
            const name = p.name.replace(new RegExp(`(${query})`, 'gi'), '<strong style="color:var(--pink)">$1</strong>');
            html += `<div class="suggestion-item" onmousedown="selectSuggestion('${p.name}')">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>${name}</span>
                     </div>`;
        });
        searchDropdown.innerHTML = html;
    }

    function selectHistory(term) {
        searchInput.value = term;
        saveSearchHistory(term);
        searchForm.submit();
    }

    function selectSuggestion(name) {
        searchInput.value = name;
        saveSearchHistory(name);
        searchForm.submit();
    }

    function clearHistory() {
        searchHistory = [];
        localStorage.removeItem(HISTORY_KEY);
        searchDropdown.classList.remove('show');
    }

    // منطق فلتر السعر
    function toggleFilterPopup() {
        document.getElementById('filterPopup').classList.toggle('show');
    }

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

    // منطق الترتيب
    function toggleSortDropdown() {
        document.getElementById('sortDropdown').classList.toggle('show');
    }

    function selectSort(type, text) {
        currentSort = type;
        currentSortText = text;
        document.getElementById('selectedSort').innerText = text;

        document.querySelectorAll('.sort-option').forEach(opt => opt.classList.remove('active'));
        event.target.classList.add('active');

        applyFiltersAndSort();
        toggleSortDropdown();
    }

    // منطق الفلترة والترتيب الرئيسي
    function filterByCategory(cat) {
        currentCategory = cat;
        const btns = document.querySelectorAll('.filter-btn');
        btns.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        applyFiltersAndSort();
    }

    function applyFiltersAndSort() {
        let products = Array.from(document.querySelectorAll('.product-card'));

        // فلتر التصنيف
        if (currentCategory !== 'all') {
            products = products.filter(p => p.dataset.category === currentCategory);
        }

        // فلتر السعر
        products = products.filter(p => {
            let price = parseFloat(p.dataset.price);
            return price <= maxPriceFilter;
        });

        // الترتيب
        if (currentSort === 'price_low') {
            products.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
        } else if (currentSort === 'price_high') {
            products.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
        } else if (currentSort === 'popularity') {
            products.sort((a, b) => (b.dataset.popularity || 0) - (a.dataset.popularity || 0));
        } else if (currentSort === 'newest') {
            products.sort((a, b) => (b.dataset.newest || 0) - (a.dataset.newest || 0));
        } else if (currentSort === 'rating') {
            products.sort((a, b) => (b.dataset.rating || 0) - (a.dataset.rating || 0));
        }

        // إعادة ترتيب العناصر
        const grid = document.getElementById('productGrid');
        document.querySelectorAll('.product-card').forEach(card => card.style.display = 'none');
        products.forEach(card => {
            card.style.display = 'block';
            grid.appendChild(card);
        });
    }

    // وظيفة الإضافة للسلة
    function addToCart(id, btn) {
        let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
        id = id.toString();
        cart[id] = (cart[id] || 0) + 1;
        localStorage.setItem('teddy_cart', JSON.stringify(cart));
        btn.classList.add('success');
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
        setTimeout(() => { btn.classList.remove('success'); btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add'; }, 1200);
    }

    // نظام المفضلة
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

    // إغلاق القوائم عند النقر خارجها
    window.addEventListener('click', function(e) {
        const filterPopup = document.getElementById('filterPopup');
        const filterBtn = document.querySelector('.filter-btn-main');
        const sortDropdown = document.getElementById('sortDropdown');
        const sortBtn = document.querySelector('.sort-btn-main');

        if (filterPopup && filterBtn && !filterPopup.contains(e.target) && !filterBtn.contains(e.target)) {
            filterPopup.classList.remove('show');
        }

        if (sortDropdown && sortBtn && !sortDropdown.contains(e.target) && !sortBtn.contains(e.target)) {
            sortDropdown.classList.remove('show');
        }
    });

    // زر العودة للأعلى
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>

<!-- الفوتر -->
<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>