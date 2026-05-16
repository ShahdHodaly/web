<?php
// dashboard.php
session_start();
require_once 'db.php';

$pdo = getDB();

// --- حساب الإحصائيات من قاعدة البيانات ---

// 1. إجمالي المنتجات
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt->fetchColumn();

// 2. إجمالي الطلبات
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $stmt->fetchColumn();

// 3. إجمالي المستخدمين
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Customer'"); // حسب الكود حقك، في Admin و Customer
$totalUsers = $stmt->fetchColumn();

// 4. إجمالي الإيرادات (مجموع عمود total من جدول orders)
$stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'"); // استبعاد الملغاة اذا حبيت
$totalRevenue = $stmt->fetchColumn();

// --- حساب الطلبات الشهرية (آخر 6 أشهر) ---
$months = [];
$monthlyOrders = [];
$currentMonth = date('n');
$currentYear = date('Y');

// جلب عدد الطلبات لكل شهر من قاعدة البيانات دفعة واحدة
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthNum = $currentMonth - $i;
    $year = $currentYear;
    if ($monthNum <= 0) {
        $monthNum += 12;
        $year--;
    }
    $monthName = date('M', mktime(0, 0, 0, $monthNum, 1));
    $months[] = $monthName;

    // استعلام لجلب عدد الطلبات لشهر وسنة محددين
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM orders 
        WHERE EXTRACT(MONTH FROM created_at) = ? AND EXTRACT(YEAR FROM created_at) = ?
    ");
    $stmt->execute([$monthNum, $year]);
    $count = $stmt->fetchColumn();
    $monthlyOrders[] = $count;
}
$maxMonthlyOrders = max($monthlyOrders) ?: 1;

// --- حساب أفضل منتج مبيعاً ---
$stmt = $pdo->query("
    SELECT p.product_id, p.name, p.image, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY p.product_id, p.name, p.image
    ORDER BY total_sold DESC
    LIMIT 1
");
$topProductData = $stmt->fetch(PDO::FETCH_ASSOC);

$topProduct = $topProductData ? $topProductData['name'] : 'No products sold yet';
$topProductSales = $topProductData ? $topProductData['total_sold'] : 0;
$topProductImage = $topProductData ? $topProductData['image'] : 'barbie5.png';
if (!$topProductImage || $topProductImage == '') {
    $topProductImage = 'barbie5.png';
}

// --- آخر 3 طلبات ---
$stmt = $pdo->query("
    SELECT order_id, order_number, created_at, total, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = orders.order_id) as items_count
    FROM orders
    ORDER BY created_at DESC
    LIMIT 3
");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- آخر 3 مراجعات معتمدة ---
$stmt = $pdo->query("
    SELECT r.comment, r.rating, r.created_at, u.name as customer_name, p.name as product_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN products p ON r.product_id = p.product_id
    WHERE r.status = 'approved'
    ORDER BY r.created_at DESC
    LIMIT 3
");
$recentReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard · Teddy Lap</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* تم نقل جميع الأنماط الخاصة بالداشبورد هنا كما هي في كودك الأصلي */
        body { background-color: var(--bg-color); margin: 0; font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .admin-wrapper { display: flex; min-height: 100vh; }

        .admin-main {
            flex: 1;
            padding: 30px 35px;
            background-color: var(--bg-color);
            transition: background-color 0.4s ease;
            overflow-y: auto;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInDown 0.6s ease;
        }
        .main-header h1 {
            font-size: 32px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .main-header .date-badge {
            background: var(--card-bg);
            padding: 10px 20px;
            border-radius: 40px;
            box-shadow: 0 2px 10px var(--shadow);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .main-header .date-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow);
        }

        /* stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4,1fr);
            gap: 24px;
            margin-bottom: 40px;
        }
        .stat-card {
            background-color: var(--card-bg);
            border-radius: 28px;
            padding: 22px 20px;
            box-shadow: 0 5px 12px var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
            border: 1px solid transparent;
        }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow);
            border-color: var(--pink);
        }
        .stat-left h3 { font-size: 15px; font-weight: 500; color: var(--secondary-text); margin-bottom: 8px; }
        .stat-left .value { font-size: 38px; font-weight: 700; color: var(--text-color); line-height: 1; }
        .stat-icon { font-size: 48px; color: var(--primary); opacity: 0.7; transition: all 0.3s ease; }
        .stat-card:hover .stat-icon { transform: scale(1.1) rotate(5deg); color: var(--pink); }

        /* charts row */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 40px;
        }
        .chart-card {
            background-color: var(--card-bg);
            border-radius: 28px;
            padding: 22px 24px;
            box-shadow: 0 5px 12px var(--shadow);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
            border: 1px solid transparent;
        }
        .chart-card:nth-child(1) { animation-delay: 0.5s; }
        .chart-card:nth-child(2) { animation-delay: 0.6s; }
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow);
            border-color: var(--lavender);
        }
        .chart-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .chart-title i { transition: transform 0.3s ease; }
        .chart-card:hover .chart-title i { transform: rotate(90deg); color: var(--pink); }
        .chart-placeholder {
            background: linear-gradient(145deg, var(--card-bg), var(--bg-color));
            border-radius: 24px;
            padding: 20px;
            min-height: 180px;
            border: 1px solid rgba(128,128,128,0.1);
            transition: all 0.3s ease;
        }
        .chart-card:hover .chart-placeholder { border-color: var(--pink); }

        .bar-container {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            height: 130px;
        }
        .bar-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .bar {
            width: 35px;
            border-radius: 12px 12px 4px 4px;
            transition: all 0.3s ease;
            background: linear-gradient(180deg, var(--primary), var(--pink));
            cursor: pointer;
        }
        .bar:hover {
            transform: scaleY(1.05) translateY(-2px);
            filter: brightness(1.1);
        }
        .bar-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--card-bg);
            color: var(--text-color);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 2px 10px var(--shadow);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
            margin-bottom: 8px;
            border: 1px solid var(--pink);
        }
        .bar-wrapper:hover .bar-tooltip { opacity: 1; }
        .bar-label { text-align: center; font-size: 12px; color: var(--secondary-text); margin-top: 8px; }

        .game-item {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .game-item:hover { transform: translateX(10px); }
        .game-img {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
        }
        .progress-tag {
            background: var(--lavender);
            padding: 8px 18px;
            border-radius: 60px;
            color: #000;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .progress-tag:hover { transform: scale(1.05); }

        /* activity row */
        .activity-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .activity-card {
            background-color: var(--card-bg);
            border-radius: 28px;
            padding: 22px 24px;
            box-shadow: 0 5px 12px var(--shadow);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
            border: 1px solid transparent;
        }
        .activity-card:nth-child(1) { animation-delay: 0.7s; }
        .activity-card:nth-child(2) { animation-delay: 0.8s; }
        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px var(--shadow);
            border-color: var(--pink);
        }
        .activity-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(128,128,128,0.15);
            transition: all 0.3s ease;
        }
        .activity-item:hover {
            background: rgba(248, 187, 208, 0.1);
            padding-left: 10px;
            border-radius: 15px;
        }
        .activity-item:last-child { border: none; }
        .activity-badge {
            width: 45px; height: 45px; background: var(--pink); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: #000; font-size: 20px;
            transition: all 0.3s ease;
        }
        .activity-item:hover .activity-badge {
            transform: scale(1.1) rotate(5deg);
            background: var(--lavender);
        }
        .pill-new {
            background: var(--primary);
            color: white;
            font-size: 12px;
            padding: 3px 12px;
            border-radius: 60px;
            margin-left: 12px;
        }

        /* search bar */
        .search-container {
            flex: 1;
            min-width: 300px;
            position: relative;
            animation: slideInRight 0.6s ease;
        }
        .search-input {
            width: 100%;
            padding: 16px 25px 16px 55px;
            background: var(--card-bg);
            border: 2px solid transparent;
            border-radius: 60px;
            color: var(--text-color);
            font-size: 15px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
            outline: none;
        }
        .search-input:focus {
            border-color: var(--pink);
            box-shadow: 0 8px 25px var(--shadow);
            transform: translateY(-2px);
        }
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
        }
        .search-input:focus + .search-icon { color: var(--pink); transform: translateY(-50%) scale(1.1); }
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 60px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .search-btn:hover { background: var(--pink); transform: translateY(-50%) scale(1.05); }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card-bg);
            padding: 12px 24px;
            border-radius: 60px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: transform 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            animation: slideInRight 0.6s ease;
            animation-fill-mode: both;
        }
        .quick-action-btn:nth-child(1) { animation-delay: 0.2s; }
        .quick-action-btn:nth-child(2) { animation-delay: 0.3s; }
        .quick-action-btn:nth-child(3) { animation-delay: 0.4s; }
        .quick-action-btn:hover {
            transform: translateY(-3px);
            border-color: var(--pink);
        }
        .quick-action-btn div:first-child {
            transition: transform 0.3s ease;
        }


        .quick-actions-dropdown {
            position: relative;
            animation: slideInRight 0.6s ease;
            animation-delay: 0.5s;
            animation-fill-mode: both;
        }
        .quick-actions-dropdown button {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .quick-actions-dropdown button:hover {
            transform: rotate(90deg);
            background: var(--pink) !important;
            color: white !important;
        }
        #quickMenu {
            position: absolute;
            right: 0;
            top: 70px;
            background: var(--card-bg);
            border-radius: 25px;
            box-shadow: 0 10px 30px var(--shadow);
            width: 250px;
            padding: 15px 0;
            display: none;
            z-index: 1000;
            border: 1px solid rgba(128,128,128,0.1);
            animation: fadeIn 0.2s ease;
        }
        #quickMenu ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--text-color);
            transition: background 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }
        #quickMenu ul li a:hover {
            background-color: var(--pink);
            color: #000 !important;
            transform: translateX(5px);
        }

        .rating-stars { display: flex; gap: 2px; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .charts-row { grid-template-columns: 1fr; }
            .activity-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 800px) {
            .admin-wrapper { flex-direction: column; }
            .admin-sidebar { width: 100%; height: auto; border-radius: 0; }
            .quick-action-btn div:last-child { display: none; }
            .quick-action-btn { padding: 12px !important; }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="main-header">
            <h1 style="display: flex; align-items: center; gap: 8px;">
                Welcome back, Admin
                <iconify-icon icon="noto:teddy-bear" width="48" height="48" style="animation: wave 2s infinite;"></iconify-icon>
            </h1>
            <div class="date-badge"><i class="fa-regular fa-calendar"></i> <?= date('F d, Y') ?></div>
        </div>

        <!-- Search Bar + Quick Actions -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap;">
            <div class="search-container">
                <form action="search-admin.php" method="GET" style="width: 100%;">
                    <div style="position: relative; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="q" class="search-input" placeholder="Search orders, products, users..." id="searchInput">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <i class="fa-solid fa-arrow-right"></i> Search
                        </button>
                    </div>
                </form>
            </div>


        </div>

        <!-- statistics cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-left">
                    <h3>Total Orders</h3>
                    <div class="value"><?= number_format($totalOrders) ?></div>
                </div>
                <i class="fa-solid fa-cart-shopping stat-icon"></i>
            </div>
            <div class="stat-card">
                <div class="stat-left">
                    <h3>Total Users</h3>
                    <div class="value"><?= number_format($totalUsers) ?></div>
                </div>
                <i class="fa-solid fa-user stat-icon"></i>
            </div>
            <div class="stat-card">
                <div class="stat-left">
                    <h3>Total Products</h3>
                    <div class="value"><?= number_format($totalProducts) ?></div>
                </div>
                <i class="fa-solid fa-teddy-bear stat-icon"></i>
            </div>
            <div class="stat-card">
                <div class="stat-left">
                    <h3>Total Revenue</h3>
                    <div class="value">$<?= number_format($totalRevenue, 2) ?></div>
                </div>
                <i class="fa-solid fa-dollar-sign stat-icon"></i>
            </div>
        </div>

        <!-- charts row -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title"><i class="fa-regular fa-calendar-check"></i> Orders per month</div>
                <div class="chart-placeholder">
                    <div class="bar-container">
                        <?php for($i = 0; $i < count($months); $i++):
                            $barHeight = max(20, ($monthlyOrders[$i] / $maxMonthlyOrders) * 100);
                            ?>
                            <div class="bar-wrapper">
                                <div class="bar" style="height: <?= $barHeight ?>px"></div>
                                <div class="bar-tooltip"><?= $monthlyOrders[$i] ?> orders</div>
                                <span class="bar-label"><?= $months[$i] ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <p style="color: var(--secondary-text); font-size: 13px; margin-top: 15px;">Last 6 months performance</p>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-title"><i class="fa-solid fa-star"></i> Most Selling Product</div>
                <div class="chart-placeholder" style="justify-content: center;">
                    <div class="game-item" style="display: flex; align-items: center; padding: 12px; background: linear-gradient(90deg, rgba(255,215,0,0.1), transparent); border-radius: 15px; margin-bottom: 8px;">
                        <div style="position: relative; margin-right: 8px;">
                            <i class="fa-solid fa-trophy" style="font-size: 32px; color: #FFD700;"></i>
                            <span style="position: absolute; top: -8px; right: -8px; background: #FFD700; color: black; font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 50%;">1</span>
                        </div>
                        <img src="<?= htmlspecialchars($topProductImage) ?>" class="game-img" onerror="this.src='images/barbie5.png'">
                        <div style="flex: 1;">
                            <strong style="font-size: 18px;"><?= htmlspecialchars($topProduct) ?></strong>
                            <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                                <span style="color: var(--secondary-text);"><?= number_format($topProductSales) ?> sales</span>
                                <span style="background: #FFD700; color: black; padding: 4px 12px; border-radius: 40px; font-size: 12px;">
                                    <i class="fa-solid fa-crown"></i> Best Seller
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="progress-tag">🔥 Top performer this quarter</div>
                </div>
            </div>
        </div>

        <!-- latest activity -->
        <div class="activity-row">
            <div class="activity-card">
                <div class="chart-title"><i class="fa-regular fa-clock"></i> Latest Orders</div>
                <?php if(empty($recentOrders)): ?>
                    <div class="activity-item">No orders found.</div>
                <?php else: ?>
                    <?php foreach($recentOrders as $order): ?>
                        <div class="activity-item">
                            <div class="activity-badge"><i class="fa-solid fa-bag-shopping"></i></div>
                            <div class="activity-desc">
                                <p><strong><?= htmlspecialchars($order['order_number']) ?></strong> · <?= $order['items_count'] ?> items</p>
                                <small><?= date('M d, H:i', strtotime($order['created_at'])) ?> · $<?= number_format($order['total'], 2) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div style="margin-top: 12px;"><a href="orders.php" style="color: var(--primary);">View all orders →</a></div>
            </div>
            <div class="activity-card">
                <div class="chart-title"><i class="fa-regular fa-bell"></i> Latest Reviews</div>
                <?php if(empty($recentReviews)): ?>
                    <div class="activity-item">No reviews yet.</div>
                <?php else: ?>
                    <?php foreach($recentReviews as $review): ?>
                        <div class="activity-item">
                            <div class="activity-badge"><i class="fa-regular fa-star"></i></div>
                            <div class="activity-desc">
                                <p><strong><?= htmlspecialchars($review['customer_name']) ?></strong> reviewed <strong><?= htmlspecialchars($review['product_name']) ?></strong></p>
                                <div class="rating-stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-<?= $i <= $review['rating'] ? 'solid' : 'regular' ?> fa-star" style="color: #FFD700; font-size: 11px;"></i>
                                    <?php endfor; ?>
                                </div>
                                <small>"<?= htmlspecialchars(substr($review['comment'], 0, 50)) ?>..."</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div style="margin-top: 8px;"><a href="reviews.php" style="color: var(--primary);">View all reviews →</a></div>
            </div>
        </div>

        <!-- birthday reminder -->
        <div style="margin-top: 30px; display: flex; gap: 20px; align-items: center; background: var(--card-bg); border-radius: 60px; padding: 16px 28px; box-shadow: 0 2px 8px var(--shadow);">
            <div><i class="fa-solid fa-cake-candles" style="color: var(--primary); font-size: 28px;"></i></div>
            <div><strong>Cute Giraffe</strong> · Has birthday today <span style="background: var(--pink); padding: 5px 15px; border-radius: 60px; margin-left: 15px; color:#000;">Wish Him</span></div>
        </div>
    </main>
</div>

<script>
    function toggleQuickMenu() {
        const menu = document.getElementById('quickMenu');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'block';
        } else {
            menu.style.display = 'none';
        }
    }

    document.addEventListener('click', function(event) {
        const menu = document.getElementById('quickMenu');
        const button = event.target.closest('.quick-actions-dropdown');
        if (!button && menu && menu.style.display === 'block') {
            menu.style.display = 'none';
        }
    });

    document.getElementById('quickMenu')?.addEventListener('click', function(event) {
        event.stopPropagation();
    });

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary)';
            this.style.boxShadow = '0 8px 20px var(--shadow)';
        });
        searchInput.addEventListener('blur', function() {
            this.style.borderColor = 'transparent';
            this.style.boxShadow = '0 4px 15px var(--shadow)';
        });
    }

    document.querySelectorAll('.quick-action-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.borderColor = 'var(--pink)';
        });
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.borderColor = 'transparent';
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>

<script>
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
            themeSwitchMain.addEventListener('change', function(e) {
                const isDark = this.checked;
                applyTheme(isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>