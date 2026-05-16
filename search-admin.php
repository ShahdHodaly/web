<?php
// search-admin.php
session_start();
require_once 'db.php';

$pdo = getDB();

// معالجة البحث
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : 'all';
$results = [
        'products' => [],
        'orders' => [],
        'users' => [],
        'reviews' => [],
        'messages' => [],
        'coupons' => []
];
$totalResults = 0;

// منطق البحث
if (!empty($query)) {
    $searchTerm = '%' . $query . '%';

    // ========== البحث في المنتجات ==========
    if ($type == 'all' || $type == 'products') {
        $stmt = $pdo->prepare("
            SELECT 
                p.product_id as id,
                p.name,
                p.description,
                p.price,
                p.image,
                p.stock,
                c.name as category,
                'product' as _type
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE p.name ILIKE ? 
               OR c.name ILIKE ? 
               OR p.description ILIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $product['_id'] = $product['id'];
            $results['products'][] = $product;
            $totalResults++;
        }
    }

    // ========== البحث في الطلبات ==========
    if ($type == 'all' || $type == 'orders') {
        $stmt = $pdo->prepare("
            SELECT 
                o.order_id as id,
                o.order_number,
                o.total,
                o.status::text as status,
                o.created_at as date,
                u.name as customer,
                u.email as customer_email,
                'order' as _type
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            WHERE o.order_number ILIKE ? 
               OR u.name ILIKE ? 
               OR u.email ILIKE ? 
               OR o.status::text ILIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as $order) {
            $order['_id'] = $order['id'];
            $results['orders'][] = $order;
            $totalResults++;
        }
    }

    // ========== البحث في المستخدمين ==========
    if ($type == 'all' || $type == 'users') {
        $stmt = $pdo->prepare("
            SELECT 
                user_id as id,
                name,
                email,
                role::text as role,
                status::text as status,
                created_at as joined,
                'user' as _type
            FROM users
            WHERE name ILIKE ? 
               OR email ILIKE ? 
               OR role::text ILIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            $user['_id'] = $user['id'];
            $results['users'][] = $user;
            $totalResults++;
        }
    }

    // ========== البحث في المراجعات ==========
    if ($type == 'all' || $type == 'reviews') {
        $stmt = $pdo->prepare("
            SELECT 
                r.review_id as id,
                r.rating,
                r.comment,
                r.status::text as status,
                r.helpful_count,
                r.created_at as date,
                u.name as customer_name,
                p.name as product_name,
                'review' as _type
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            JOIN products p ON r.product_id = p.product_id
            WHERE p.name ILIKE ? 
               OR u.name ILIKE ? 
               OR r.comment ILIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reviews as $review) {
            $review['_id'] = $review['id'];
            $results['reviews'][] = $review;
            $totalResults++;
        }
    }

    // ========== البحث في الرسائل ==========
    if ($type == 'all' || $type == 'messages') {
        $stmt = $pdo->prepare("
            SELECT 
                message_id as id,
                sender_name,
                sender_email,
                subject,
                message,
                priority,
                status,
                created_at as date,
                'message' as _type
            FROM messages
            WHERE sender_name ILIKE ? 
               OR sender_email ILIKE ? 
               OR subject ILIKE ? 
               OR message ILIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as $message) {
            $message['_id'] = $message['id'];
            $results['messages'][] = $message;
            $totalResults++;
        }
    }

    // ========== البحث في الكوبونات ==========
    if ($type == 'all' || $type == 'coupons') {
        $stmt = $pdo->prepare("
            SELECT 
                coupon_id as id,
                code,
                description,
                discount_type,
                discount_value,
                status::text as status,
                start_date,
                expiry_date,
                'coupon' as _type
            FROM coupons
            WHERE code ILIKE ? 
               OR description ILIKE ?
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($coupons as $coupon) {
            $coupon['_id'] = $coupon['id'];
            $results['coupons'][] = $coupon;
            $totalResults++;
        }
    }
}

// دمج جميع النتائج في مصفوفة واحدة
$allResults = [];
foreach ($results as $category => $items) {
    foreach ($items as $item) {
        $allResults[] = $item;
    }
}

// Pagination للنتائج
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$totalPages = $totalResults > 0 ? ceil($totalResults / $perPage) : 1;
$offset = ($page - 1) * $perPage;
$paginatedResults = array_slice($allResults, $offset, $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Admin · Teddy Shop</title>
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
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; align-items: flex-start; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); box-sizing: border-box; }

        /* Search Section */
        .search-section {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease;
        }

        .search-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .search-input-group {
            flex: 2;
            min-width: 250px;
        }
        .search-input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 8px;
        }
        .search-input-group input {
            width: 100%;
            padding: 14px 18px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 50px;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .search-input-group input:focus {
            border-color: var(--pink);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        .filter-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 8px;
        }
        .filter-group select {
            width: 100%;
            padding: 14px 18px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 50px;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
            cursor: pointer;
        }
        .filter-group select:focus {
            border-color: var(--pink);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .search-btn {
            padding: 14px 30px;
            background: var(--primary);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-btn:hover {
            background: var(--pink);
            transform: translateY(-2px);
        }

        .reset-btn {
            background: var(--bg-color);
            border: 1px solid rgba(128,128,128,0.2);
            color: var(--secondary-text);
        }
        .reset-btn:hover {
            background: #ff6b6b;
            color: white;
            border-color: #ff6b6b;
        }

        /* Results Header */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .results-count {
            font-size: 16px;
            color: var(--secondary-text);
        }
        .results-count strong {
            color: var(--primary);
            font-size: 20px;
        }

        /* Results Cards */
        .results-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .result-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 8px var(--shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }
        .result-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }
        .card-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }
        .type-product { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .type-order { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .type-user { background: rgba(156, 39, 176, 0.2); color: #9C27B0; }
        .type-review { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .type-message { background: rgba(244, 67, 54, 0.2); color: #F44336; }
        .type-coupon { background: rgba(0, 150, 136, 0.2); color: #009688; }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }
        .card-details {
            color: var(--secondary-text);
            font-size: 13px;
            margin-top: 8px;
            line-height: 1.5;
        }
        .card-link {
            margin-top: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .card-link:hover {
            color: var(--pink);
            text-decoration: underline;
        }

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
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
            transform: scale(1.1);
        }
        .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: 30px;
        }
        .no-results i {
            font-size: 80px;
            color: var(--secondary-text);
            opacity: 0.5;
            margin-bottom: 20px;
        }
        .no-results h3 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; }
            .search-row { flex-direction: column; }
            .search-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Search Section -->
        <div class="search-section">
            <div class="search-title">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--pink);"></i>
                Admin Search
            </div>

            <form action="search-admin.php" method="GET" id="searchForm">
                <div class="search-row">
                    <div class="search-input-group">
                        <label><i class="fa-solid fa-search"></i> Search</label>
                        <input type="text"
                               name="q"
                               id="searchInput"
                               value="<?= htmlspecialchars($query) ?>"
                               placeholder="Search for products, orders, users, reviews, messages, coupons...">
                    </div>
                    <div class="filter-group">
                        <label><i class="fa-solid fa-filter"></i> Filter by</label>
                        <select name="type">
                            <option value="all" <?= $type == 'all' ? 'selected' : '' ?>>All Categories</option>
                            <option value="products" <?= $type == 'products' ? 'selected' : '' ?>>Products</option>
                            <option value="orders" <?= $type == 'orders' ? 'selected' : '' ?>>Orders</option>
                            <option value="users" <?= $type == 'users' ? 'selected' : '' ?>>Users</option>
                            <option value="reviews" <?= $type == 'reviews' ? 'selected' : '' ?>>Reviews</option>
                            <option value="messages" <?= $type == 'messages' ? 'selected' : '' ?>>Messages</option>
                            <option value="coupons" <?= $type == 'coupons' ? 'selected' : '' ?>>Coupons</option>
                        </select>
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                    <a href="search-admin.php" class="search-btn reset-btn">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <?php if (!empty($query)): ?>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-count">
                    Found <strong><?= $totalResults ?></strong> result<?= $totalResults != 1 ? 's' : '' ?>
                    for "<strong><?= htmlspecialchars($query) ?></strong>"
                </div>
                <a href="dashboard.php" class="filter-chip" style="padding: 8px 20px; background: var(--lavender); border-radius: 50px; color: #000; text-decoration: none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if ($totalResults > 0): ?>
                <!-- Results Cards -->
                <div class="results-container">
                    <?php foreach($paginatedResults as $item): ?>
                        <?php
                        $resultType = $item['_type'];
                        $resultId = $item['_id'];
                        $link = '';
                        $title = '';
                        $details = '';
                        $icon = '';

                        if ($resultType == 'product') {
                            $link = "product_details-admin.php?id=$resultId";
                            $title = $item['name'];
                            $details = "Category: " . ($item['category'] ?? 'N/A') . " | Price: $" . number_format($item['price'], 2);
                            $icon = "fa-box";
                        } elseif ($resultType == 'order') {
                            $link = "order-details-admin.php?id=$resultId";
                            $title = $item['order_number'];
                            $details = "Customer: " . ($item['customer'] ?? 'N/A') . " | Total: $" . number_format($item['total'], 2) . " | Status: " . ucfirst($item['status']);
                            $icon = "fa-truck";
                        } elseif ($resultType == 'user') {
                            $link = "user-details.php?id=$resultId";
                            $title = $item['name'];
                            $details = "Email: " . ($item['email'] ?? 'N/A') . " | Role: " . ucfirst($item['role']) . " | Status: " . ucfirst($item['status']);
                            $icon = "fa-user";
                        } elseif ($resultType == 'review') {
                            $link = "review-details.php?id=$resultId";
                            $title = $item['product_name'] ?? 'Product';
                            $details = "By: " . ($item['customer_name'] ?? 'Anonymous') . " | Rating: " . ($item['rating'] ?? 0) . "★ | Status: " . ucfirst($item['status'] ?? 'pending');
                            $icon = "fa-star";
                        } elseif ($resultType == 'message') {
                            $link = "message-details.php?id=$resultId";
                            $title = $item['subject'];
                            $details = "From: " . ($item['sender_name'] ?? 'Unknown') . " | Date: " . date('M d, Y', strtotime($item['date'] ?? 'now')) . " | Priority: " . ucfirst($item['priority'] ?? 'medium');
                            $icon = "fa-envelope";
                        } elseif ($resultType == 'coupon') {
                            $link = "coupon-details.php?id=$resultId";
                            $title = $item['code'];
                            $details = ($item['description'] ?? 'No description') . " | Status: " . ucfirst($item['status'] ?? 'active');
                            $icon = "fa-ticket";
                        }
                        ?>
                        <div class="result-card">
                            <div class="card-header">
                                <span class="card-type type-<?= $resultType ?>">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                    <?= ucfirst($resultType) ?>
                                </span>
                                <span style="font-size: 11px; color: var(--secondary-text);">ID: #<?= $resultId ?></span>
                            </div>
                            <div class="card-title"><?= htmlspecialchars($title) ?></div>
                            <div class="card-details"><?= htmlspecialchars($details) ?></div>
                            <a href="<?= $link ?>" class="card-link">
                                View Details <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-section">
                        <div class="pagination-info">
                            Showing <strong><?= $offset + 1 ?>-<?= min($offset + $perPage, $totalResults) ?></strong> of <strong><?= $totalResults ?></strong> results
                        </div>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?q=<?= urlencode($query) ?>&type=<?= $type ?>&page=<?= $page - 1 ?>" class="page-item"><i class="fa-solid fa-chevron-left"></i></a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            if ($startPage > 1): ?>
                                <a href="?q=<?= urlencode($query) ?>&type=<?= $type ?>&page=1" class="page-item">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="page-item disabled">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?q=<?= urlencode($query) ?>&type=<?= $type ?>&page=<?= $i ?>" class="page-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="page-item disabled">...</span>
                                <?php endif; ?>
                                <a href="?q=<?= urlencode($query) ?>&type=<?= $type ?>&page=<?= $totalPages ?>" class="page-item"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?q=<?= urlencode($query) ?>&type=<?= $type ?>&page=<?= $page + 1 ?>" class="page-item"><i class="fa-solid fa-chevron-right"></i></a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- No Results -->
                <div class="no-results">
                    <i class="fa-solid fa-search"></i>
                    <h3>No results found</h3>
                    <p style="color: var(--secondary-text); margin-top: 10px;">
                        Try different search terms or check your spelling
                    </p>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                        <span class="filter-chip" onclick="document.getElementById('searchInput').focus()" style="cursor: pointer;">Try again</span>
                        <a href="dashboard.php" class="filter-chip" style="text-decoration: none;">Back to Dashboard</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<script>
    // Search input effect
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        searchInput.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    }

    // Select effect
    const selectInput = document.querySelector('select');
    if (selectInput) {
        selectInput.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        selectInput.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    }

    // Form validation - prevent empty search
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const query = document.getElementById('searchInput').value.trim();
        if (!query) {
            e.preventDefault();
            alert('Please enter a search term');
            return false;
        }
    });
</script>

<script>
    (function() {
        const themeSwitchMain = document.getElementById('themeSwitchSidebar');
        function applyTheme(isDark) {
            if (isDark) { document.body.classList.add('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = true; }
            else { document.body.classList.remove('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = false; }
        }
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true); else applyTheme(false);
        if (themeSwitchMain) themeSwitchMain.addEventListener('change', function(e) { applyTheme(this.checked); localStorage.setItem('theme', this.checked ? 'dark' : 'light'); });
    })();
</script>
</body>
</html>