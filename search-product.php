<?php
// search-product.php
session_start();
require_once 'db.php';

$pdo = getDB();
$pageTitle = "Search Results | Teddy Lap Admin";

// معالجة البحث
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
$totalResults = 0;

// جلب قائمة التصنيفات من قاعدة البيانات للـ Quick Filters
$stmt = $pdo->query("SELECT DISTINCT c.name FROM categories c JOIN products p ON c.category_id = p.category_id ORDER BY c.name LIMIT 5");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// منطق البحث في قاعدة البيانات
if (!empty($query)) {
    // استخدام البحث المتقدم مع ILIKE (غير حساس لحالة الأحرف)
    $searchTerm = '%' . $query . '%';

    $stmt = $pdo->prepare("
        SELECT 
            p.product_id,
            p.name,
            p.description,
            p.price,
            p.image,
            p.stock,
            p.sales_count,
            c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE 
            p.name ILIKE ? OR 
            c.name ILIKE ? OR 
            p.description ILIKE ?
        ORDER BY 
            CASE 
                WHEN p.name ILIKE ? THEN 1
                WHEN c.name ILIKE ? THEN 2
                ELSE 3
            END,
            p.name
    ");

    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $resultsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // تنسيق النتائج
    foreach ($resultsData as $item) {
        $results[$item['product_id']] = [
                'name' => $item['name'],
                'description' => $item['description'],
                'price' => (float)$item['price'],
                'category' => $item['category_name'],
                'image' => $item['image'] ?: 'images/placeholder.png',
                'stock' => $item['stock'],
                'sales_count' => $item['sales_count']
        ];
    }

    $totalResults = count($results);
}

// Pagination للنتائج
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$totalPages = ceil($totalResults / $perPage);
$offset = ($page - 1) * $perPage;
$paginatedResults = array_slice($results, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ملفات CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* تنسيقات أساسية */
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        /* Search Header */
        .search-header {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease;
        }
        .search-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        .search-query {
            color: var(--pink);
            font-weight: 600;
        }
        .search-stats {
            color: var(--secondary-text);
            font-size: 16px;
        }
        .search-stats strong {
            color: var(--primary);
            font-size: 24px;
            margin: 0 5px;
        }

        /* Search Bar كبير */
        .search-container-large {
            margin: 25px 0;
        }
        .search-box {
            position: relative;
            width: 100%;
        }
        .search-box input {
            width: 100%;
            padding: 20px 60px 20px 60px;
            background: var(--card-bg);
            border: 2px solid transparent;
            border-radius: 60px;
            color: var(--text-color);
            font-size: 18px;
            box-shadow: 0 4px 20px var(--shadow);
            transition: all 0.3s ease;
            outline: none;
        }
        .search-box input:focus {
            border-color: var(--pink);
            box-shadow: 0 8px 30px var(--shadow);
            transform: translateY(-2px);
        }
        .search-box i {
            position: absolute;
            left: 25px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
            font-size: 20px;
            transition: all 0.3s ease;
        }
        .search-box input:focus + i {
            color: var(--pink);
            transform: translateY(-50%) scale(1.1);
        }
        .search-box button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            border: none;
            color: white;
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        .search-box button:hover {
            background: var(--pink);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        /* Quick Filters */
        .quick-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        .filter-chip {
            padding: 8px 20px;
            background: var(--card-bg);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 50px;
            color: var(--text-color);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .filter-chip:hover {
            background: var(--pink);
            color: #000;
            transform: translateY(-2px);
            border-color: transparent;
        }
        .filter-chip.active {
            background: var(--pink);
            border-color: var(--pink);
            color: #000;
        }
        .filter-chip i {
            font-size: 12px;
        }

        /* Search Suggestions */
        .suggestions-container {
            position: relative;
            width: 100%;
            z-index: 1000;
        }
        .suggestions-box {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow);
            margin-top: 10px;
            padding: 15px 0;
            display: none;
            border: 1px solid rgba(128,128,128,0.1);
            animation: fadeIn 0.3s ease;
        }
        .suggestions-box.show {
            display: block;
        }
        .suggestion-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-color);
            text-decoration: none;
        }
        .suggestion-item:hover {
            background: rgba(248, 187, 208, 0.2);
        }
        .suggestion-item i {
            width: 20px;
            color: var(--pink);
        }
        .suggestion-category {
            font-size: 12px;
            color: var(--secondary-text);
            margin-left: auto;
        }
        .suggestion-divider {
            height: 1px;
            background: rgba(128,128,128,0.1);
            margin: 8px 0;
        }

        /* نتائج البحث */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .results-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
        }
        .results-actions {
            display: flex;
            gap: 10px;
        }

        /* Table (نفس تصميم products.php) */
        .table-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin: 25px 0;
            overflow-x: auto;
            animation: fadeInUp 0.8s ease;
        }
        .products-table { width: 100%; border-collapse: collapse; }
        .products-table th {
            text-align: left;
            padding: 15px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .products-table td {
            padding: 15px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .product-img {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
            transition: transform 0.2s;
        }
        .product-info:hover .product-img { transform: scale(1.1); }

        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-color);
            color: var(--secondary-text);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .action-btn:hover {
            background: var(--pink);
            color: #000;
            transform: translateY(-3px) scale(1.1);
        }
        .action-btn.view:hover { background: var(--lavender); }
        .action-btn.edit:hover { background: var(--primary); }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
        }
        .no-results i {
            font-size: 80px;
            color: var(--secondary-text);
            margin-bottom: 20px;
            opacity: 0.3;
        }
        .no-results h3 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        .no-results p {
            color: var(--secondary-text);
            margin-bottom: 25px;
        }

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .pagination-info { color: var(--secondary-text); font-size: 14px; }
        .pagination { display: flex; gap: 8px; align-items: center; }
        .page-item {
            min-width: 40px;
            height: 40px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card-bg);
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(128,128,128,0.1);
            text-decoration: none;
        }
        .page-item:hover {
            background: var(--pink);
            color: white;
            transform: translateY(-2px);
        }
        .page-item.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: scale(1.1);
        }
        .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Search Header -->
        <div class="search-header">
            <div class="search-title">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--pink); margin-right: 10px;"></i>
                Search Results
            </div>
            <?php if (!empty($query)): ?>
                <div class="search-stats">
                    Found <strong><?= $totalResults ?></strong> result<?= $totalResults != 1 ? 's' : '' ?> for
                    <span class="search-query">"<?= htmlspecialchars($query) ?>"</span>
                </div>
            <?php else: ?>
                <div class="search-stats">
                    Enter a search term to find products
                </div>
            <?php endif; ?>
        </div>

        <!-- Search Box with Suggestions -->
        <div class="search-container-large">
            <div class="suggestions-container">
                <div class="search-box">
                    <form action="search-product.php" method="GET" id="searchForm">
                        <i class="fa-solid fa-search"></i>
                        <input type="text"
                               name="q"
                               id="searchInput"
                               value="<?= htmlspecialchars($query) ?>"
                               placeholder="Search products by name, category, description..."
                               autocomplete="off">
                        <button type="submit">Search</button>
                    </form>
                </div>

                <!-- Suggestions Box -->
                <div class="suggestions-box" id="suggestionsBox">
                    <div class="suggestion-item" onclick="searchSuggestion('barbie')">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Search for "barbie"</span>
                        <span class="suggestion-category">Products</span>
                    </div>
                    <div class="suggestion-divider"></div>
                    <div class="suggestion-item" onclick="searchSuggestion('teddy')">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Search for "teddy"</span>
                        <span class="suggestion-category">Products</span>
                    </div>
                    <div class="suggestion-item" onclick="searchSuggestion('building')">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Search for "building"</span>
                        <span class="suggestion-category">Products</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="quick-filters">
            <a href="product-admin.php" class="filter-chip">
                <i class="fa-solid fa-arrow-left"></i> Back to Products
            </a>

            <?php foreach ($categories as $category): ?>
                <span class="filter-chip" onclick="searchSuggestion('<?= htmlspecialchars($category) ?>')">
                    <?= htmlspecialchars($category) ?>
                </span>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($query) && $totalResults > 0): ?>
            <!-- Results Header -->
            <div class="results-header">
                <div class="results-title">
                    <i class="fa-solid fa-cube" style="color: var(--pink); margin-right: 10px;"></i>
                    Products Found
                </div>
            </div>

            <!-- Results Table -->
            <div class="table-container">
                <table class="products-table">
                    <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $counter = $offset + 1; ?>
                    <?php foreach($paginatedResults as $id => $product): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td>
                                <div class="product-info">
                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img" onerror="this.src='images/placeholder.png'">
                                    <div>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <div style="font-size: 12px; color: var(--secondary-text);">ID: #<?= $id ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="background: var(--lavender); padding: 5px 15px; border-radius: 30px; font-size: 12px;">
                                    <?= htmlspecialchars($product['category']) ?>
                                </span>
                            </td>
                            <td><strong>$<?= number_format($product['price'], 2) ?></strong></td>
                            <td>
                                <span style="color: var(--secondary-text); font-size: 13px;">
                                    <?= htmlspecialchars(substr($product['description'] ?? 'No description', 0, 50)) ?>...
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn view" onclick="viewProduct(<?= $id ?>)" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="action-btn edit" onclick="editProduct(<?= $id ?>)" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="deleteProduct(<?= $id ?>)" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-section">
                        <div class="pagination-info">
                            Showing <strong><?= $offset + 1 ?>-<?= min($offset + $perPage, $totalResults) ?></strong> of <strong><?= $totalResults ?></strong> results
                        </div>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" class="page-item">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            if ($startPage > 1): ?>
                                <a href="?q=<?= urlencode($query) ?>&page=1" class="page-item">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="page-item disabled">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>"
                                   class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="page-item disabled">...</span>
                                <?php endif; ?>
                                <a href="?q=<?= urlencode($query) ?>&page=<?= $totalPages ?>" class="page-item"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" class="page-item">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif (!empty($query) && $totalResults == 0): ?>
            <!-- No Results -->
            <div class="no-results">
                <i class="fa-solid fa-box-open"></i>
                <h3>No products found</h3>
                <p>We couldn't find any products matching "<?= htmlspecialchars($query) ?>"</p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <span class="filter-chip" onclick="searchSuggestion('barbie')">Try "barbie"</span>
                    <span class="filter-chip" onclick="searchSuggestion('teddy')">Try "teddy"</span>
                    <span class="filter-chip" onclick="searchSuggestion('building')">Try "building"</span>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- JavaScript -->
<script>
    // Search Suggestions
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestionsBox');

    searchInput.addEventListener('focus', function() {
        suggestionsBox.classList.add('show');
    });

    searchInput.addEventListener('blur', function() {
        setTimeout(() => {
            suggestionsBox.classList.remove('show');
        }, 200);
    });

    // Suggestion functions
    function searchSuggestion(term) {
        window.location.href = 'search-product.php?q=' + encodeURIComponent(term);
    }

    // Product functions
    function viewProduct(id) {
        window.location.href = 'product_details-admin.php?id=' + id;
    }

    function editProduct(id) {
        window.location.href = 'edit-product.php?id=' + id;
    }

    function deleteProduct(id) {
        if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
            window.location.href = 'delete-product.php?id=' + id;
        }
    }

    // Clear search input if empty
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const input = document.getElementById('searchInput');
        if (!input.value.trim()) {
            e.preventDefault();
            window.location.href = 'product-admin.php';
        }
    });

    // Dark mode script
    (function() {
        const themeSwitchMain = document.getElementById('themeSwitchSidebar');
        function applyTheme(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                if (themeSwitchMain) themeSwitchMain.checked = true;
            } else {
                document.body.classList.remove('dark-mode');
                if (themeSwitchMain) themeSwitchMain.checked = false;
            }
        }

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true);
        else applyTheme(false);

        if (themeSwitchMain) {
            themeSwitchMain.addEventListener('change', function() {
                applyTheme(this.checked);
                localStorage.setItem('theme', this.checked ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>