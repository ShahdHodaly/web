<?php
// coupons.php
session_start();
require_once 'db.php';

$pdo = getDB();

// جلب الإحصائيات من قاعدة البيانات
$statsQuery = $pdo->query("
    SELECT 
        COUNT(*) as total_coupons,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_coupons,
        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_coupons,
        SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_coupons,
        COALESCE(SUM(used_count), 0) as total_usage
    FROM coupons
");
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

$totalCoupons = $stats['total_coupons'];
$activeCoupons = $stats['active_coupons'];
$scheduledCoupons = $stats['scheduled_coupons'];
$expiredCoupons = $stats['expired_coupons'];
$totalUsage = $stats['total_usage'];

// جلب الكوبونات من قاعدة البيانات
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY coupon_id DESC");
$dbCoupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تجهيز المصفوفة بنفس الشكل اللي يتوقعه الفرونت إند (JavaScript)
$coupons = [];
foreach ($dbCoupons as $row) {
    $coupons[$row['coupon_id']] = [
            'code' => $row['code'],
            'description' => $row['description'],
            'discount_type' => $row['discount_type'],
            'discount_value' => (float)$row['discount_value'],
            'min_order' => (float)$row['min_order'],
            'max_discount' => (float)$row['max_discount'],
            'usage_limit' => (int)$row['usage_limit'],
            'used_count' => (int)$row['used_count'],
            'start_date' => $row['start_date'],
            'expiry_date' => $row['expiry_date'],
            'status' => $row['status']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons · Teddy Shop</title>
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
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        .stats-mini {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 25px 0;
        }
        .stat-mini-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
            animation-fill-mode: both;
            border: 1px solid transparent;
        }
        .stat-mini-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-mini-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-mini-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-mini-card:nth-child(4) { animation-delay: 0.4s; }

        .stat-mini-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px var(--shadow);
            border-color: var(--pink);
        }

        .stat-mini-card:nth-child(1) { border-left:4px solid #ff9aa2; }
        .stat-mini-card:nth-child(2) { border-left:4px solid #a0c4ff; }
        .stat-mini-card:nth-child(3) { border-left:4px solid #bdb2ff; }
        .stat-mini-card:nth-child(4) { border-left:4px solid #ffd6a5; }

        .stat-mini-info h4 { font-size: 14px; color: var(--secondary-text); margin-bottom: 5px; }
        .stat-mini-info .value { font-size: 28px; font-weight: 700; color: var(--text-color); }
        .stat-mini-icon { font-size: 40px; opacity: 0.7; transition: all 0.3s ease; }
        .stat-mini-card:hover .stat-mini-icon {
            transform: scale(1.2) rotate(5deg);
            color: var(--pink);
        }

        .filters-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px var(--shadow);
            margin: 25px 0;
            animation: fadeInUp 0.6s ease;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }
        .filters-section:hover {
            border-color: var(--pink);
            box-shadow: 0 8px 25px var(--shadow);
        }
        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px dashed var(--pink);
        }
        .filters-header h3 {
            font-size: 18px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .filters-header h3 i {
            color: var(--pink);
            background: rgba(248, 187, 208, 0.2);
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        .filters-header:hover h3 i {
            transform: rotate(90deg);
            background: var(--pink);
            color: white;
        }
        .filters-grid {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-item {
            flex: 1;
            min-width: 150px;
            position: relative;
        }
        .filter-select {
            width: 100%;
            padding: 12px 20px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 40px;
            color: var(--text-color);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
        }
        .filter-select:hover {
            border-color: var(--pink);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }
        .clear-filters-btn {
            transition: all 0.3s ease !important;
        }
        .clear-filters-btn:hover {
            background: var(--lavender) !important;
            transform: scale(1.05);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .table-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin: 25px 0;
            overflow-x: auto;
            animation: fadeInUp 0.8s ease;
            position: relative;
        }
        .coupons-table { width: 100%; border-collapse: collapse; }
        .coupons-table th {
            text-align: left;
            padding: 15px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .coupons-table td {
            padding: 15px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
            transition: background-color 0.3s ease;
        }
        .coupons-table tbody tr:hover td {
            background-color: rgba(248, 187, 208, 0.1);
        }

        .coupon-code {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 16px;
            color: var(--primary);
            background: rgba(248, 187, 208, 0.2);
            padding: 5px 12px;
            border-radius: 30px;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .discount-badge {
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .discount-percentage { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .discount-fixed { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .discount-shipping { background: rgba(255, 152, 0, 0.2); color: #FF9800; }

        .status-badge {
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            text-transform: capitalize;
        }
        .status-active { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-scheduled { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .status-expired { background: rgba(244, 67, 54, 0.2); color: #F44336; }

        .usage-progress {
            width: 100%;
            height: 6px;
            background: rgba(128,128,128,0.1);
            border-radius: 10px;
            margin-top: 5px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .progress-bar.active { background: linear-gradient(90deg, var(--primary), var(--pink)); }
        .progress-bar.warning { background: linear-gradient(90deg, #FF9800, #FFB74D); }
        .progress-bar.danger { background: linear-gradient(90deg, #F44336, #FF8A80); }

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
            position: relative;
            overflow: hidden;
        }
        .action-btn:hover {
            background: var(--pink);
            color: #000;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .action-btn:active {
            transform: translateY(-1px) scale(1.05);
        }
        .action-btn.view:hover { background: var(--lavender); }
        .action-btn.edit:hover { background: var(--primary); }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }
        .action-btn.copy:hover { background: #4CAF50; color: white; }

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
            box-shadow: 0 5px 15px var(--shadow);
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
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .search-container {
            flex: 1;
            min-width: 300px;
            position: relative;
            animation: slideInRight 0.5s ease;
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
            border-color: var(--primary);
            box-shadow: 0 8px 25px var(--shadow);
            transform: translateY(-2px);
        }
        .search-input::placeholder {
            color: var(--secondary-text);
            transition: opacity 0.3s ease;
        }
        .search-input:focus::placeholder {
            opacity: 0.5;
        }
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
            z-index: 10;
            transition: all 0.3s ease;
        }
        .search-input:focus + .search-icon {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }
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
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            overflow: hidden;
        }
        .search-btn:hover {
            background: var(--lavender);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .search-btn:active {
            transform: translateY(-50%) scale(0.95);
        }
        .search-btn i {
            transition: transform 0.3s ease;
        }
        .search-btn:hover i {
            transform: translateX(8px);
        }

        .add-coupon-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card-bg);
            padding: 12px 24px;
            border-radius: 60px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            animation: slideInRight 0.5s ease;
            position: relative;
            overflow: hidden;
        }
        .add-coupon-btn:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 8px 25px var(--shadow);
        }
        .add-coupon-btn:active {
            transform: translateY(-1px);
        }
        .add-coupon-btn div:first-child {
            background: var(--primary);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }
        .add-coupon-btn:hover div:first-child {
            background: var(--lavender);
        }
        .add-coupon-btn div:first-child i {
            transition: transform 0.3s ease;
        }
        .add-coupon-btn:hover div:first-child i {
            transform: rotate(180deg);
        }

        @media (max-width: 1100px) {
            .stats-mini { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 800px) {
            .admin-wrapper { flex-direction: column; }
            .admin-sidebar { width: 100%; height: auto; border-radius: 0; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="main-header" style="animation: fadeInDown 0.6s ease;">
            <div>
                <h1 style="margin-bottom: 5px;">Coupons Management</h1>
                <p style="color: var(--secondary-text);">Create and manage discount coupons</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" style="background: var(--lavender); color: #000; transition: all 0.3s ease;" onclick="exportCoupons()">
                    <i class="fa-solid fa-download"></i> Export
                </button>
                <button class="btn-primary" style="background: var(--pink); color: #000; transition: all 0.3s ease;" onclick="importCoupons()">
                    <i class="fa-solid fa-upload"></i> Import
                </button>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap;">
            <div class="search-container">
                <form action="search-coupons.php" method="GET" style="width: 100%;">
                    <div style="position: relative; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="q" class="search-input" placeholder="Search by code" id="searchInput">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <i class="fa-solid fa-arrow-right"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <a href="add-coupon.php" class="add-coupon-btn" id="addCouponBtn">
                <div><i class="fa-solid fa-ticket"></i></div>
                <div>
                    <div style="font-weight: 600; color: var(--text-color);">Add Coupon</div>
                    <div style="font-size: 12px; color: var(--secondary-text);">New discount</div>
                </div>
            </a>
        </div>

        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Total Coupons</h4>
                    <div class="value"><?= $totalCoupons ?></div>
                </div>
                <i class="fa-solid fa-ticket stat-mini-icon"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Active</h4>
                    <div class="value"><?= $activeCoupons ?></div>
                </div>
                <i class="fa-solid fa-check-circle stat-mini-icon" style="color: #4CAF50;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Scheduled</h4>
                    <div class="value"><?= $scheduledCoupons ?></div>
                </div>
                <i class="fa-solid fa-clock stat-mini-icon" style="color: #FF9800;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Total Uses</h4>
                    <div class="value"><?= $totalUsage ?></div>
                </div>
                <i class="fa-solid fa-chart-line stat-mini-icon" style="color: var(--pink);"></i>
            </div>
        </div>

        <div class="filters-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 18px; margin: 0;">
                    <i class="fa-solid fa-filter" style="margin-right: 8px;"></i> Filters
                </h3>
                <button class="action-btn clear-filters-btn" style="width: auto; padding: 0 20px; border-radius: 40px;" onclick="clearFilters()">
                    <i class="fa-solid fa-undo" style="margin-right: 5px;"></i> Clear all
                </button>
            </div>

            <div class="filters-grid">
                <div class="filter-item">
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                        <option value="shipping">Free Shipping</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="sortFilter">
                        <option value="">Sort by</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="usage">Most Used</option>
                        <option value="value">Highest Value</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="coupons-table">
                <thead>
                <tr>
                    <th style="width: 50px;"><input type="checkbox" style="transform: scale(1.2); cursor: pointer;" id="selectAll"></th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Discount</th>
                    <th>Min Order</th>
                    <th>Usage</th>
                    <th>Valid Period</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="couponsTableBody"></tbody>
            </table>

            <div class="pagination-section">
                <div class="pagination-info" id="paginationInfo">
                    Showing <strong>0</strong> of <strong><?= $totalCoupons ?></strong> coupons
                </div>
                <div class="pagination" id="paginationControls"></div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
            <button class="btn-primary" style="background: #4CAF50; color: white; transition: all 0.3s ease;" onclick="bulkActivate()">
                <i class="fa-solid fa-check-circle"></i> Bulk Activate
            </button>
            <button class="btn-primary" style="background: #ff6b6b; transition: all 0.3s ease;" onclick="bulkDelete()">
                <i class="fa-solid fa-trash-can"></i> Bulk Delete
            </button>
        </div>
    </main>
</div>

<script>
    // ===== بيانات الكوبونات من PHP إلى JavaScript =====
    const allCoupons = <?php echo json_encode($coupons); ?>;
    const couponsArray = Object.entries(allCoupons).map(([id, coupon]) => {
        return {
            id: parseInt(id),
            code: coupon.code,
            description: coupon.description,
            discount_type: coupon.discount_type,
            discount_value: parseFloat(coupon.discount_value),
            min_order: parseFloat(coupon.min_order),
            max_discount: parseFloat(coupon.max_discount),
            usage_limit: parseInt(coupon.usage_limit),
            used_count: parseInt(coupon.used_count),
            start_date: coupon.start_date,
            expiry_date: coupon.expiry_date,
            status: coupon.status
        };
    });

    let currentPage = 1;
    let perPage = 5;
    let filteredCoupons = [...couponsArray];

    function displayCoupons() {
        const tbody = document.getElementById('couponsTableBody');
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const paginatedCoupons = filteredCoupons.slice(start, end);

        if (paginatedCoupons.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 50px;">No coupons found</td></tr>`;
            updatePaginationInfo();
            updatePaginationControls();
            return;
        }

        let html = '';
        paginatedCoupons.forEach(coupon => {
            let discountHtml = '';
            if (coupon.discount_type === 'percentage') {
                discountHtml = `<span class="discount-badge discount-percentage">${coupon.discount_value}% OFF</span>`;
                if (coupon.max_discount > 0) {
                    discountHtml += `<div><small style="color: var(--secondary-text);">Max $${coupon.max_discount}</small></div>`;
                }
            } else if (coupon.discount_type === 'fixed') {
                discountHtml = `<span class="discount-badge discount-fixed">$${coupon.discount_value} OFF</span>`;
            } else {
                discountHtml = `<span class="discount-badge discount-shipping">Free Shipping</span>`;
            }

            const usagePercentage = (coupon.used_count / coupon.usage_limit) * 100;
            let barClass = 'active';
            if (usagePercentage >= 80) barClass = 'danger';
            else if (usagePercentage >= 50) barClass = 'warning';

            const startDate = new Date(coupon.start_date);
            const expiryDate = new Date(coupon.expiry_date);
            const formattedStart = startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const formattedExpiry = expiryDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            html += `
                <tr data-id="${coupon.id}" data-status="${coupon.status}">
                    <td><input type="checkbox" class="coupon-checkbox" value="${coupon.id}"></td>
                    <td><span class="coupon-code">${escapeHtml(coupon.code)}</span></td>
                    <td><strong>${escapeHtml(coupon.description)}</strong></td>
                    <td>${discountHtml}</td>
                    <td>${coupon.min_order > 0 ? '$' + coupon.min_order : '<span style="color: var(--secondary-text);">No min</span>'}</td>
                    <td>
                        <div><strong>${coupon.used_count}</strong> / ${coupon.usage_limit}</div>
                        <div class="usage-progress"><div class="progress-bar ${barClass}" style="width: ${usagePercentage}%"></div></div>
                    </td>
                    <td>
                        <small>From</small> ${formattedStart}<br>
                        <small>To</small> ${formattedExpiry}
                    </td>
                    <td><span class="status-badge status-${coupon.status}">${coupon.status.charAt(0).toUpperCase() + coupon.status.slice(1)}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewCoupon(${coupon.id})" title="View"><i class="fa-solid fa-eye"></i></button>
                            <button class="action-btn edit" onclick="editCoupon(${coupon.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                            <button class="action-btn copy" onclick="copyCoupon('${escapeHtml(coupon.code)}')" title="Copy Code"><i class="fa-solid fa-copy"></i></button>
                            <button class="action-btn delete" onclick="deleteCoupon(${coupon.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        updatePaginationInfo();
        updatePaginationControls();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function updatePaginationInfo() {
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(start + perPage - 1, filteredCoupons.length);
        const infoElement = document.getElementById('paginationInfo');
        infoElement.innerHTML = `Showing <strong>${filteredCoupons.length > 0 ? start + '-' + end : '0'}</strong> of <strong>${filteredCoupons.length}</strong> coupons`;
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredCoupons.length / perPage);
        const paginationDiv = document.getElementById('paginationControls');
        let html = '';

        if (currentPage > 1) {
            html += `<a href="#" onclick="changePage(${currentPage - 1}); return false;" class="page-item"><i class="fa-solid fa-chevron-left"></i></a>`;
        } else {
            html += `<span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>`;
        }

        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                html += `<span class="page-item active">${i}</span>`;
            } else {
                html += `<a href="#" onclick="changePage(${i}); return false;" class="page-item">${i}</a>`;
            }
        }

        if (currentPage < totalPages) {
            html += `<a href="#" onclick="changePage(${currentPage + 1}); return false;" class="page-item"><i class="fa-solid fa-chevron-right"></i></a>`;
        } else {
            html += `<span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>`;
        }

        paginationDiv.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        displayCoupons();
    }

    function filterCoupons() {
        const status = document.getElementById('statusFilter').value;
        const type = document.getElementById('typeFilter').value;
        const sortBy = document.getElementById('sortFilter').value;

        filteredCoupons = couponsArray.filter(coupon => {
            let show = true;
            if (status && coupon.status !== status) show = false;
            if (type && coupon.discount_type !== type) show = false;
            return show;
        });

        if (sortBy) {
            filteredCoupons.sort((a, b) => {
                if (sortBy === 'newest') return new Date(b.start_date) - new Date(a.start_date);
                if (sortBy === 'oldest') return new Date(a.start_date) - new Date(b.start_date);
                if (sortBy === 'usage') return b.used_count - a.used_count;
                if (sortBy === 'value') return b.discount_value - a.discount_value;
                return 0;
            });
        }

        currentPage = 1;
        displayCoupons();
    }

    function clearFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('sortFilter').value = '';
        filteredCoupons = [...couponsArray];
        currentPage = 1;
        displayCoupons();
    }

    function viewCoupon(id) {
        window.location.href = 'coupon-details.php?id=' + id;
    }

    function editCoupon(id) {
        window.location.href = 'edit-coupon.php?id=' + id;
    }

    function copyCoupon(code) {
        navigator.clipboard.writeText(code).then(() => {
            showToast('Copied: ' + code, true);
        });
    }

    // ===== دوال الحذف والتفعيل الجماعي =====
    async function bulkDeleteFromDB(couponIds) {
        try {
            const response = await fetch('bulk-delete-coupons.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ coupon_ids: couponIds })
            });
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return { success: false, message: 'Network error' };
        }
    }

    async function bulkActivateFromDB(couponIds) {
        try {
            const response = await fetch('bulk-activate-coupons.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ coupon_ids: couponIds })
            });
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return { success: false, message: 'Network error' };
        }
    }

    async function deleteCouponFromDB(couponId) {
        try {
            const response = await fetch('delete-coupon.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'coupon_id=' + couponId
            });
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    function showConfirmPopup(message, onConfirm, loadingMessage) {
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
        overlay.style.backdropFilter = 'blur(3px)';
        overlay.style.zIndex = '9998';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        const popup = document.createElement('div');
        popup.style.backgroundColor = 'var(--card-bg, #ffffff)';
        popup.style.color = 'var(--text-color, #333)';
        popup.style.borderRadius = '28px';
        popup.style.padding = '28px 24px';
        popup.style.maxWidth = '420px';
        popup.style.width = '90%';
        popup.style.boxShadow = '0 25px 45px rgba(0,0,0,0.25)';
        popup.style.textAlign = 'center';
        popup.style.fontFamily = "'Poppins', sans-serif";
        popup.style.transform = 'scale(0.9)';
        popup.style.transition = 'transform 0.25s ease';
        popup.style.border = '1px solid var(--pink, #F8BBD0)';

        popup.innerHTML = `
            <div style="font-size: 48px; margin-bottom: 10px;">⚠️</div>
            <h3 style="font-size: 22px; font-weight: 600; margin-bottom: 10px;">Confirm Action</h3>
            <p style="font-size: 15px; color: var(--secondary-text); margin-bottom: 25px;">${message}</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button id="popup-cancel-btn" style="background: transparent; border: 2px solid var(--pink); padding: 10px 24px; border-radius: 40px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button id="popup-confirm-btn" style="background: #4CAF50; border: none; padding: 10px 28px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer;">Confirm</button>
            </div>
        `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        setTimeout(() => {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        }, 10);

        function closePopup() {
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            setTimeout(() => overlay.remove(), 250);
        }

        document.getElementById('popup-cancel-btn').onclick = closePopup;
        document.getElementById('popup-confirm-btn').onclick = async () => {
            if (onConfirm) await onConfirm();
            closePopup();
            if (loadingMessage) showToast(loadingMessage, true);
        };
        overlay.onclick = (e) => { if (e.target === overlay) closePopup(); };
    }

    function showToast(message, isSuccess = true) {
        const toast = document.createElement('div');
        toast.innerHTML = `<div style="display: flex; align-items: center; gap: 12px;"><i class="fa-solid fa-${isSuccess ? 'check-circle' : 'exclamation-triangle'}" style="font-size: 24px; color: ${isSuccess ? '#4CAF50' : '#f44336'}"></i><div><strong>${message}</strong></div></div>`;
        toast.style.position = 'fixed';
        toast.style.top = '50%';
        toast.style.left = '50%';
        toast.style.transform = 'translate(-50%, -50%) scale(0.9)';
        toast.style.backgroundColor = 'var(--card-bg)';
        toast.style.padding = '15px 25px';
        toast.style.borderRadius = '50px';
        toast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
        toast.style.zIndex = '10000';
        toast.style.fontFamily = "'Poppins', sans-serif";
        toast.style.border = `2px solid ${isSuccess ? '#4CAF50' : '#f44336'}`;
        toast.style.opacity = '0';
        toast.style.transition = 'all 0.25s ease';
        document.body.appendChild(toast);
        setTimeout(() => toast.style.opacity = '1', 20);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 250);
        }, 2500);
    }

    async function deleteCoupon(id) {
        showConfirmPopup('⚠️ Delete this coupon? This action cannot be undone!', async () => {
            const success = await deleteCouponFromDB(id);
            if (success) {
                const index = couponsArray.findIndex(c => c.id == id);
                if (index !== -1) couponsArray.splice(index, 1);
                filterCoupons();
                showToast('Coupon deleted successfully!', true);
            } else {
                showToast('Failed to delete coupon', false);
            }
        });
    }

    async function bulkDelete() {
        const selected = [...document.querySelectorAll('.coupon-checkbox:checked')].map(cb => cb.value);
        if (selected.length === 0) { alert('Please select coupons'); return; }
        showConfirmPopup(`Delete ${selected.length} coupon(s) permanently?`, async () => {
            const result = await bulkDeleteFromDB(selected);
            if (result.success) {
                for (const id of selected) {
                    const index = couponsArray.findIndex(c => c.id == id);
                    if (index !== -1) couponsArray.splice(index, 1);
                }
                filterCoupons();
                showToast(result.message, true);
                const selectAll = document.getElementById('selectAll');
                if (selectAll) selectAll.checked = false;
            } else {
                showToast(result.message || 'Failed to delete coupons', false);
            }
        });
    }

    async function bulkActivate() {
        const selected = [...document.querySelectorAll('.coupon-checkbox:checked')].map(cb => cb.value);
        if (selected.length === 0) { alert('Please select coupons'); return; }
        showConfirmPopup(`Activate ${selected.length} coupon(s)?`, async () => {
            const result = await bulkActivateFromDB(selected);
            if (result.success) {
                for (const id of selected) {
                    const index = couponsArray.findIndex(c => c.id == id);
                    if (index !== -1) couponsArray[index].status = 'active';
                }
                filterCoupons();
                showToast(result.message, true);
            } else {
                showToast(result.message || 'Failed to activate coupons', false);
            }
        });
    }

    function exportCoupons() { alert('Export feature demo'); }
    function importCoupons() { alert('Import feature demo'); }

    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.coupon-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('statusFilter')?.addEventListener('change', filterCoupons);
    document.getElementById('typeFilter')?.addEventListener('change', filterCoupons);
    document.getElementById('sortFilter')?.addEventListener('change', filterCoupons);

    document.addEventListener('DOMContentLoaded', function() {
        displayCoupons();
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