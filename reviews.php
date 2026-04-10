<?php
// reviews.php
session_start();

// تضمين مصفوفة المراجعات
require_once 'reviews-array.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$totalReviews = count($reviews);
$totalPages = ceil($totalReviews / $perPage);
$offset = ($page - 1) * $perPage;
$paginatedReviews = array_slice($reviews, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews · Teddy Shop</title>
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
        /* تنسيقاتك الموجودة */
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        /* Stats Cards */
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
            border: 1px solid transparent;
        }
        .stat-mini-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--shadow);
            border-color: var(--pink);
        }

        .stat-mini-card:nth-child(1){
            border-left:4px solid #ff9aa2;
        }
        .stat-mini-card:nth-child(2){
            border-left:4px solid #a0c4ff;
        }
        .stat-mini-card:nth-child(3){
            border-left:4px solid #bdb2ff;
        }
        .stat-mini-card:nth-child(4){
            border-left:4px solid #ffd6a5;
        }

        .stat-mini-info h4 { font-size: 14px; color: var(--secondary-text); margin-bottom: 5px; }
        .stat-mini-info .value { font-size: 28px; font-weight: 700; color: var(--text-color); }
        .stat-mini-icon { font-size: 40px; color: var(--primary); opacity: 0.7; }

        .stat-mini-card:hover .stat-mini-icon {
            transform: scale(1.2) rotate(5deg);
            color: var(--pink);
        }

        /* Filters Section */
        .filters-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px var(--shadow);
            margin: 25px 0;
            animation: fadeInUp 0.6s ease;
        }
        .filters-grid {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-item { flex: 1; min-width: 150px; }
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

        /* Table */
        .table-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin: 25px 0;
            overflow-x: auto;
            animation: fadeInUp 0.8s ease;
        }
        .reviews-table { width: 100%; border-collapse: collapse; }
        .reviews-table th {
            text-align: left;
            padding: 15px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .reviews-table td {
            padding: 15px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
            transition: background-color 0.3s ease;
        }
        .reviews-table tbody tr:hover td {
            background-color: rgba(248, 187, 208, 0.1);
        }

        /* Product Info */
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
            transition: transform 0.3s ease;
        }
        .product-info:hover .product-img {
            transform: scale(1.1) rotate(5deg);
        }

        /* Customer Info */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--lavender);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .customer-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        tr:hover .customer-avatar {
            transform: scale(1.1) rotate(5deg);
            background: var(--pink);
        }

        /* Rating Stars */
        .rating-stars {
            display: flex;
            gap: 3px;
        }
        .rating-stars i {
            font-size: 14px;
        }

        /* Status Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            text-transform: capitalize;
        }
        .status-approved { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-pending { background: rgba(255, 152, 0, 0.2); color: #FF9800; }

        /* Comment Preview */
        .comment-preview {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--secondary-text);
            font-size: 13px;
        }

        /* Action Buttons */
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
        .action-btn.approve:hover { background: #4CAF50; color: white; }
        .action-btn.reject:hover { background: #ff6b6b; color: white; }
        .action-btn.reply:hover { background: var(--primary); color: white; }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }

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
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* تأثيرات للسيرش بار */
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

        /* تأثيرات للفلاتر */
        .filters-section h3 i {
            transition: transform 0.3s ease;
        }
        .filters-section:hover h3 i {
            transform: rotate(90deg);
        }
        .clear-filters-btn {
            transition: all 0.3s ease !important;
        }
        .clear-filters-btn:hover {
            background: var(--lavender) !important;
            transform: scale(1.05);
            box-shadow: 0 5px 15px var(--shadow);
        }

        /* Helpful Count */
        .helpful-count {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--secondary-text);
        }
        .helpful-count i {
            color: var(--pink);
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
    <!-- sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Header مع الأزرار -->
        <div class="main-header">
            <div>
                <h1 style="margin-bottom: 5px;">Reviews Management</h1>
                <p style="color: var(--secondary-text);">Manage customer reviews and ratings</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" style="background: var(--lavender); color: #000; transition: all 0.3s ease;" onclick="exportReviews()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-download"></i> Export
                </button>
                <button class="btn-primary" style="background: var(--pink); color: #000; transition: all 0.3s ease;" onclick="settingsReviews()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-gear"></i> Settings
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap;">
            <!-- Search Bar -->
            <div class="search-container">
                <form action="search-reviews.php" method="GET" style="width: 100%;">
                    <div style="position: relative; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text"
                               name="q"
                               class="search-input"
                               placeholder="Search by product, customer, comment..."
                               id="searchInput">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <i class="fa-solid fa-arrow-right"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <?php
        // حساب الإحصائيات
        $totalReviews = count($reviews);
        $approvedReviews = count(array_filter($reviews, fn($r) => $r['status'] === 'approved'));
        $pendingReviews = count(array_filter($reviews, fn($r) => $r['status'] === 'pending'));
        $avgRating = $totalReviews > 0 ? round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1) : 0;
        ?>
        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Total Reviews</h4>
                    <div class="value"><?= $totalReviews ?></div>
                </div>
                <i class="fa-solid fa-star stat-mini-icon"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Approved</h4>
                    <div class="value"><?= $approvedReviews ?></div>
                </div>
                <i class="fa-solid fa-check-circle stat-mini-icon" style="color: #4CAF50;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Pending</h4>
                    <div class="value"><?= $pendingReviews ?></div>
                </div>
                <i class="fa-solid fa-clock stat-mini-icon" style="color: #FF9800;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Avg Rating</h4>
                    <div class="value"><?= $avgRating ?> ★</div>
                </div>
                <i class="fa-solid fa-chart-line stat-mini-icon" style="color: var(--primary);"></i>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 18px; margin: 0;">
                    <i class="fa-solid fa-filter" style="margin-right: 8px;"></i>
                    Filters
                </h3>
                <button class="action-btn clear-filters-btn" style="width: auto; padding: 0 20px; border-radius: 40px;" onclick="clearFilters()">
                    <i class="fa-solid fa-undo" style="margin-right: 5px;"></i> Clear all
                </button>
            </div>

            <div class="filters-grid">
                <div class="filter-item">
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="ratingFilter">
                        <option value="">All Ratings</option>
                        <option value="5">★★★★★ (5)</option>
                        <option value="4">★★★★☆ (4+)</option>
                        <option value="3">★★★☆☆ (3+)</option>
                        <option value="2">★★☆☆☆ (2+)</option>
                        <option value="1">★☆☆☆☆ (1)</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="sortFilter">
                        <option value="">Sort by</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="rating-high">Highest Rating</option>
                        <option value="rating-low">Lowest Rating</option>
                        <option value="helpful">Most Helpful</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Reviews Table -->
        <div class="table-container">
            <table class="reviews-table">
                <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" style="transform: scale(1.2); cursor: pointer;" id="selectAll">
                    </th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </thead>
                <tbody id="reviewsTableBody">
                <!-- المراجعات رح تتحط هنا عن طريق JavaScript -->
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-section">
                <div class="pagination-info" id="paginationInfo">
                    Showing <strong>0</strong> of <strong><?= $totalReviews ?></strong> reviews
                </div>
                <div class="pagination" id="paginationControls">
                    <!-- Pagination رح يتولد عن طريق JavaScript -->
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
            <button class="btn-primary" style="background: var(--lavender); color: #000; transition: all 0.3s ease;" onclick="bulkApprove()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-check-circle"></i> Bulk Approve
            </button>
            <button class="btn-primary" style="background: #ff6b6b; transition: all 0.3s ease;" onclick="bulkDelete()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-trash-can"></i> Bulk Delete
            </button>
        </div>
    </main>
</div>

<script>

    function showAdminConfirm(message, onConfirm) {
        // 1. إنشاء overlay الخلفية
        const overlay = document.createElement('div');
        overlay.id = 'admin-confirm-overlay';
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

        // 2. إنشاء نافذة الـ Popup
        const popup = document.createElement('div');
        popup.id = 'admin-confirm-popup';
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

        // محتوى البوب أب
        popup.innerHTML = `
        <div style="font-size: 58px; margin-bottom: 12px;">⚠️</div>
        <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 12px;">Are you sure?</h3>
        <p style="font-size: 16px; color: var(--secondary-text, #555); margin-bottom: 28px; line-height: 1.5;">${message}</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button id="confirm-cancel-btn" style="background: transparent; border: 2px solid var(--pink, #F8BBD0); padding: 10px 24px; border-radius: 40px; font-weight: 600; cursor: pointer; color: var(--text-color, #333); transition: all 0.2s;">Cancel</button>
            <button id="confirm-ok-btn" style="background: #d9534f; border: none; padding: 10px 28px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; box-shadow: 0 4px 8px rgba(217,83,79,0.3); transition: all 0.2s;">Delete</button>
        </div>
    `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // ظهور الأنيميشن
        setTimeout(() => {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        }, 10);

        // إزالة البوب أب
        function closePopup() {
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            setTimeout(() => {
                if (overlay && overlay.parentNode) overlay.remove();
            }, 250);
        }

        // دالة عرض رسالة النجاح (toast منتصف الصفحة)
        function showSuccessToast() {
            const toast = document.createElement('div');
            toast.id = 'admin-success-toast';
            toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">

                <div>
                    <strong style="font-size: 18px;">Removed from the system!</strong>

                </div>
            </div>
        `;
            toast.style.position = 'fixed';
            toast.style.top = '50%';
            toast.style.left = '50%';
            toast.style.transform = 'translate(-50%, -50%) scale(0.9)';
            toast.style.backgroundColor = 'var(--card-bg, #fff)';
            toast.style.color = 'var(--text-color, #333)';
            toast.style.padding = '18px 28px';
            toast.style.borderRadius = '60px';
            toast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
            toast.style.zIndex = '10000';
            toast.style.fontFamily = "'Poppins', sans-serif";
            toast.style.borderRight = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderLeft = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderTop = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderBottom = '4px solid var(--pink, #F8BBD0)';
            toast.style.backdropFilter = 'blur(12px)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1)';
            toast.style.fontWeight = '500';
            toast.style.textAlign = 'center';
            toast.style.minWidth = '280px';
            toast.style.boxSizing = 'border-box';

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 20);

            // إخفاء الرسالة بعد 2.5 ثانية
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                setTimeout(() => {
                    if (toast && toast.parentNode) toast.remove();
                }, 250);
            }, 2500);
        }

        // أحداث الأزرار
        const cancelBtn = popup.querySelector('#confirm-cancel-btn');
        const confirmBtn = popup.querySelector('#confirm-ok-btn');

        cancelBtn.addEventListener('click', () => {
            closePopup();
        });

        confirmBtn.addEventListener('click', () => {
            // ✅ بدون حذف فعلي – فقط استدعاء callback إذا أردت تنفيذ شيء لاحقاً (مثل تحديث واجهة)
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();  // هون بتقدر تعمل أي شيء زي تحديث UI بدون حذف حقيقي
            }
            closePopup();
            // عرض رسالة النجاح الجميلة في منتصف الصفحة
            showSuccessToast();
        });

        // إغلاق عند الضغط على overlay
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePopup();
        });
    }

    // ===== بيانات المراجعات من PHP إلى JavaScript =====
    const allReviews = <?php echo json_encode($reviews); ?>;
    const reviewsArray = Object.entries(allReviews).map(([id, review]) => {
        return {
            id: id,
            product_name: review.product_name,
            product_image: review.product_image,
            customer_name: review.customer_name,
            customer_avatar: review.customer_avatar,
            rating: parseInt(review.rating),
            comment: review.comment,
            date: review.date,
            status: review.status,
            helpful_count: parseInt(review.helpful_count)
        };
    });

    let currentPage = <?= $page ?>;
    let perPage = <?= $perPage ?>;
    let filteredReviews = [...reviewsArray];
    let totalFilteredReviews = filteredReviews.length;

    // ===== دوال عرض المراجعات =====
    function displayReviews() {
        const tbody = document.getElementById('reviewsTableBody');
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const paginatedReviews = filteredReviews.slice(start, end);

        let html = '';
        paginatedReviews.forEach(review => {
            // توليد النجوم
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= review.rating) {
                    stars += '<i class="fa-solid fa-star" style="color: #FFD700; font-size: 14px;"></i>';
                } else {
                    stars += '<i class="fa-regular fa-star" style="color: #FFD700; font-size: 14px;"></i>';
                }
            }

            // تنسيق التاريخ
            const date = new Date(review.date);
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            html += `
                <tr data-id="${review.id}" data-status="${review.status}" data-rating="${review.rating}">
                    <td><input type="checkbox" class="review-checkbox" style="transform: scale(1.2); cursor: pointer;" value="${review.id}">
                    <td>
                        <div class="product-info">
                            <img src="images/${review.product_image}" alt="${review.product_name}" class="product-img">
                            <div>
                                <strong>${review.product_name}</strong>
                                <div style="font-size: 12px; color: var(--secondary-text);">ID: #${review.id}</div>
                            </div>
                        </div>

                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <img src="${review.customer_avatar}" alt="${review.customer_name}">
                            </div>
                            <div>
                                <div style="font-weight: 600;">${review.customer_name}</div>
                                <div class="helpful-count">
                                    <i class="fa-regular fa-thumbs-up"></i> ${review.helpful_count} found helpful
                                </div>
                            </div>
                        </div>

                    <td style="text-align: center;">
                        <div class="rating-stars" style="justify-content: center;">
                            ${stars}
                        </div>

                    <td style="max-width: 250px;">
                        <div class="comment-preview" title="${review.comment.replace(/"/g, '&quot;')}">
                            ${review.comment.substring(0, 80)}${review.comment.length > 80 ? '...' : ''}
                        </div>

                    <td style="white-space: nowrap;">
                        <div>${formattedDate}</div>
                        <div style="font-size: 11px; color: var(--secondary-text);">${formattedTime}</div>

                    <td>
                        <span class="status-badge status-${review.status}">${review.status.charAt(0).toUpperCase() + review.status.slice(1)}</span>

                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewReview(${review.id})" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            ${review.status === 'pending' ? `
                                <button class="action-btn approve" onclick="approveReview(${review.id})" title="Approve">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button class="action-btn reject" onclick="rejectReview(${review.id})" title="Reject">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            ` : ''}

                            <button class="action-btn delete" onclick="deleteReview(${review.id})" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>

                </tr>
            `;
        });

        tbody.innerHTML = html;
        updatePaginationInfo();
        updatePaginationControls();
    }

    function updatePaginationInfo() {
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(start + perPage - 1, filteredReviews.length);
        const infoElement = document.getElementById('paginationInfo');
        infoElement.innerHTML = `Showing <strong>${filteredReviews.length > 0 ? start + '-' + end : '0'}</strong> of <strong>${filteredReviews.length}</strong> reviews`;
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredReviews.length / perPage);
        const paginationDiv = document.getElementById('paginationControls');
        let html = '';

        // Previous button
        if (currentPage > 1) {
            html += `<a href="#" onclick="changePage(${currentPage - 1}); return false;" class="page-item"><i class="fa-solid fa-chevron-left"></i></a>`;
        } else {
            html += `<span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>`;
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                html += `<span class="page-item active">${i}</span>`;
            } else {
                html += `<a href="#" onclick="changePage(${i}); return false;" class="page-item">${i}</a>`;
            }
        }

        // Next button
        if (currentPage < totalPages) {
            html += `<a href="#" onclick="changePage(${currentPage + 1}); return false;" class="page-item"><i class="fa-solid fa-chevron-right"></i></a>`;
        } else {
            html += `<span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>`;
        }

        paginationDiv.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        displayReviews();
    }

    // ===== دوال الفلترة =====
    function filterReviews() {
        const status = document.getElementById('statusFilter').value;
        const rating = document.getElementById('ratingFilter').value;
        const sortBy = document.getElementById('sortFilter').value;

        // تطبيق الفلترة
        filteredReviews = reviewsArray.filter(review => {
            let showReview = true;

            // فلترة حسب الحالة
            if (status && review.status !== status) {
                showReview = false;
            }

            // فلترة حسب التقييم
            if (rating && showReview) {
                if (review.rating < parseInt(rating)) {
                    showReview = false;
                }
            }

            return showReview;
        });

        // تطبيق الترتيب
        if (sortBy) {
            filteredReviews.sort((a, b) => {
                if (sortBy === 'newest') {
                    return new Date(b.date) - new Date(a.date);
                } else if (sortBy === 'oldest') {
                    return new Date(a.date) - new Date(b.date);
                } else if (sortBy === 'rating-high') {
                    return b.rating - a.rating;
                } else if (sortBy === 'rating-low') {
                    return a.rating - b.rating;
                } else if (sortBy === 'helpful') {
                    return b.helpful_count - a.helpful_count;
                }
                return 0;
            });
        }

        // إعادة تعيين الصفحة الحالية
        currentPage = 1;

        // عرض المراجعات المفلترة
        displayReviews();
    }

    // ===== تأثيرات السيرش بار =====
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');

        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.style.transform = 'translateY(-2px)';
            });

            searchInput.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
            });

            searchInput.addEventListener('input', function() {
                if (this.value.length > 0) {
                    searchBtn.style.background = 'var(--lavender)';
                    searchBtn.querySelector('i').style.transform = 'translateX(8px)';
                } else {
                    searchBtn.style.background = 'var(--primary)';
                    searchBtn.querySelector('i').style.transform = 'translateX(0)';
                }
            });
        }

        // إضافة تأثيرات للفلاتر
        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.style.backgroundColor = 'var(--pink)';
                this.style.color = '#000';
                setTimeout(() => {
                    this.style.backgroundColor = 'var(--bg-color)';
                    this.style.color = 'var(--text-color)';
                }, 200);
            });
        });

        // تشغيل الفلترة عند تحميل الصفحة
        filterReviews();
    });

    // ===== ربط الفلاتر =====
    document.getElementById('statusFilter')?.addEventListener('change', filterReviews);
    document.getElementById('ratingFilter')?.addEventListener('change', filterReviews);
    document.getElementById('sortFilter')?.addEventListener('change', filterReviews);

    // ===== دوال مساعدة =====
    function clearFilters() {
        document.querySelectorAll('.filter-select').forEach(select => select.value = '');
        filterReviews();

        // تأثير بسيط
        const filterSection = document.querySelector('.filters-section');
        filterSection.style.transform = 'scale(1.02)';
        setTimeout(() => {
            filterSection.style.transform = 'scale(1)';
        }, 200);
    }

    // وظائف الأزرار
    function viewReview(id) {
        window.location.href = 'review-details.php?id=' + id;
    }

    // ================== دالة عرض تأكيد مخصصة (مثل showAdminConfirm) ==================
    function showConfirmPopup(message, onConfirm, actionName = 'item') {
        // إنشاء overlay
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

        // نافذة popup
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

        <p style="font-size: 16px; color: var(--secondary-text, #555); margin-bottom: 28px; line-height: 1.5;">${message}</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button id="popup-cancel-btn" style="background: transparent; border: 2px solid var(--pink, #F8BBD0); padding: 10px 24px; border-radius: 40px; font-weight: 600; cursor: pointer; color: var(--text-color, #333);">Cancel</button>
            <button id="popup-confirm-btn" style="background: #d9534f; border: none; padding: 10px 28px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; box-shadow: 0 4px 8px rgba(217,83,79,0.3);">Yes</button>
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

        // دالة عرض رسالة نجاح (toast)
        function showSuccessToast(successMessage) {
            const toast = document.createElement('div');
            toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">

                <div>
                    <strong style="font-size: 18px;">${successMessage}</strong>
                    <div style="font-size: 13px; opacity: 0.9;">${actionName}</div>
                </div>
            </div>
        `;
            toast.style.position = 'fixed';
            toast.style.top = '50%';
            toast.style.left = '50%';
            toast.style.transform = 'translate(-50%, -50%) scale(0.9)';
            toast.style.backgroundColor = 'var(--card-bg, #fff)';
            toast.style.color = 'var(--text-color, #333)';
            toast.style.padding = '18px 28px';
            toast.style.borderRadius = '60px';
            toast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
            toast.style.zIndex = '10000';
            toast.style.fontFamily = "'Poppins', sans-serif";
            toast.style.borderRight = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderLeft = '4px solid var(--pink, #F8BBD0)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1)';
            toast.style.minWidth = '280px';
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 20);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                setTimeout(() => toast.remove(), 250);
            }, 2500);
        }

        document.getElementById('popup-cancel-btn').onclick = () => closePopup();
        document.getElementById('popup-confirm-btn').onclick = () => {
            if (onConfirm && typeof onConfirm === 'function') onConfirm();
            closePopup();
            showSuccessToast('Action completed successfully!');
        };

        overlay.onclick = (e) => { if (e.target === overlay) closePopup(); };
    }

    // ================== الدالتان المطلوبتان ==================
    function approveReview(id) {
        showConfirmPopup('Are you sure you want to APPROVE this review?', () => {
            // هنا مكان الكود اللي بدك إياه بعد التأكيد (بدون حذف فعلي)
            console.log(`Review ${id} approved (demo)`);
            // يمكنك تحديث واجهة المستخدم هنا، مثلاً تغيير لون الصف أو تعطيل الأزرار
            const row = document.querySelector(`tr[data-review-id="${id}"]`);
            if (row) {
                row.style.backgroundColor = '#e8f5e9';
                row.querySelector('.status-badge').innerText = 'Approved';
            }
        }, 'Review approved');
    }

    function rejectReview(id) {
        showConfirmPopup('Are you sure you want to REJECT this review?', () => {
            console.log(`Review ${id} rejected (demo)`);
            const row = document.querySelector(`tr[data-review-id="${id}"]`);
            if (row) {
                row.style.backgroundColor = '#ffebee';
                row.querySelector('.status-badge').innerText = 'Rejected';
            }
        }, 'Review rejected');
    }



    function deleteReview(id) {
        showAdminConfirm('Are you sure you want to delete this review?', () => {})
    }

    function exportReviews() {
        alert('Export reviews feature (Demo)');
    }

    function settingsReviews() {
        alert('Review settings (Demo)');
    }

    function bulkApprove() {
        let selected = [];
        document.querySelectorAll('.review-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if(selected.length === 0) {
            alert('Please select at least one review');
            return;
        }

        alert('Approved ' + selected.length + ' reviews (Demo)');
    }

    function bulkDelete() {
        let selected = [];
        document.querySelectorAll('.review-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if(selected.length === 0) {
            alert('Please select at least one review');
            return;
        }

        showAdminConfirm('Are you sure you want to delete these ' + selected.length + ' reviews?', () => {})
    }

    // تحديد كل المراجعات
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.review-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });
</script>

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