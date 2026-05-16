<?php
// reviews.php
session_start();
require_once 'db.php';

$pdo = getDB();

// جلب جميع المراجعات من قاعدة البيانات مع معلومات المستخدم والمنتج
$stmt = $pdo->query("
    SELECT 
        r.review_id,
        r.rating,
        r.comment,
        r.status::text as status,
        r.helpful_count,
        r.created_at,
        u.name as customer_name,
        u.email as customer_email,
        u.avatar as customer_avatar,
        p.name as product_name,
        p.image as product_image
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN products p ON r.product_id = p.product_id
    ORDER BY r.created_at DESC
");
$reviewsFromDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تحويل المراجعات إلى نفس تنسيق المصفوفة القديمة للتوافق مع JavaScript
$reviews = [];
foreach ($reviewsFromDB as $review) {
    $reviews[$review['review_id']] = [
            'id' => $review['review_id'],
            'product_name' => $review['product_name'],
            'product_image' => $review['product_image'] ?: 'placeholder.png',
            'customer_name' => $review['customer_name'],
            'customer_avatar' => $review['customer_avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($review['customer_name']) . '&background=F8BBD0&color=000&size=40',
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'],
            'date' => $review['created_at'],
            'status' => $review['status'],
            'helpful_count' => (int)$review['helpful_count']
    ];
}

// حساب الإحصائيات من قاعدة البيانات
$totalReviews = count($reviews);
$approvedReviews = count(array_filter($reviews, fn($r) => $r['status'] === 'approved'));
$pendingReviews = count(array_filter($reviews, fn($r) => $r['status'] === 'pending'));
$avgRating = $totalReviews > 0 ? round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1) : 0;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
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
        /* جميع التنسيقات كما هي في الكود الأصلي */
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
            border: 1px solid transparent;
        }
        .stat-mini-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--shadow);
            border-color: var(--pink);
        }
        .stat-mini-card:nth-child(1) { border-left:4px solid #ff9aa2; }
        .stat-mini-card:nth-child(2) { border-left:4px solid #a0c4ff; }
        .stat-mini-card:nth-child(3) { border-left:4px solid #bdb2ff; }
        .stat-mini-card:nth-child(4) { border-left:4px solid #ffd6a5; }
        .stat-mini-info h4 { font-size: 14px; color: var(--secondary-text); margin-bottom: 5px; }
        .stat-mini-info .value { font-size: 28px; font-weight: 700; color: var(--text-color); }
        .stat-mini-icon { font-size: 40px; color: var(--primary); opacity: 0.7; }
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

        .rating-stars {
            display: flex;
            gap: 3px;
        }
        .rating-stars i {
            font-size: 14px;
        }

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

        .comment-preview {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--secondary-text);
            font-size: 13px;
        }

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
            box-shadow: 0 5px 15px var(--shadow);
        }
        .action-btn.view:hover { background: var(--lavender); }
        .action-btn.approve:hover { background: #4CAF50; color: white; }
        .action-btn.reject:hover { background: #ff6b6b; color: white; }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }

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
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
            z-index: 10;
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
        }
        .search-btn:hover {
            background: var(--lavender);
            transform: translateY(-50%) scale(1.05);
        }

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
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Header مع الأزرار -->
        <div class="main-header">
            <div>
                <h1 style="margin-bottom: 5px;">Reviews Management</h1>
                <p style="color: var(--secondary-text);">Manage customer reviews and ratings</p>
            </div>
        </div>

        <!-- Stats Cards -->
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
                    <th style="width: 50px;"><input type="checkbox" style="transform: scale(1.2); cursor: pointer;" id="selectAll"></th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
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
            <button class="btn-primary" style="background: var(--lavender); color: #000;" onclick="bulkApprove()">
                <i class="fa-solid fa-check-circle"></i> Bulk Approve
            </button>
        </div>
    </main>
</div>

<script>
    // ===== بيانات المراجعات من PHP إلى JavaScript =====
    const allReviews = <?php echo json_encode($reviews); ?>;
    const reviewsArray = Object.values(allReviews).map(review => {
        return {
            id: review.id,
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

    function displayReviews() {
        const tbody = document.getElementById('reviewsTableBody');
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const paginatedReviews = filteredReviews.slice(start, end);

        if (paginatedReviews.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 50px;">No reviews found</td></tr>`;
            updatePaginationInfo();
            updatePaginationControls();
            return;
        }

        let html = '';
        paginatedReviews.forEach(review => {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= review.rating) {
                    stars += '<i class="fa-solid fa-star" style="color: #FFD700; font-size: 14px;"></i>';
                } else {
                    stars += '<i class="fa-regular fa-star" style="color: #FFD700; font-size: 14px;"></i>';
                }
            }

            const date = new Date(review.date);
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            html += `
                <tr data-id="${review.id}" data-status="${review.status}" data-rating="${review.rating}">
                    <td><input type="checkbox" class="review-checkbox" style="transform: scale(1.2); cursor: pointer;" value="${review.id}"></td>
                    <td>
                        <div class="product-info">
                            <img src="${review.product_image}" alt="${review.product_name}" class="product-img" onerror="this.src='images/placeholder.png'">
                            <div>
                                <strong>${escapeHtml(review.product_name)}</strong>
                                <div style="font-size: 12px; color: var(--secondary-text);">ID: #${review.id}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <img src="${review.customer_avatar}" alt="${review.customer_name}" onerror="this.src='images/teddy4.png'">
                            </div>
                            <div>
                                <div style="font-weight: 600;">${escapeHtml(review.customer_name)}</div>
                                <div class="helpful-count">
                                    <i class="fa-regular fa-thumbs-up"></i> ${review.helpful_count} found helpful
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center;"><div class="rating-stars" style="justify-content: center;">${stars}</div></td>
                    <td style="max-width: 250px;"><div class="comment-preview" title="${escapeHtml(review.comment)}">${escapeHtml(review.comment.substring(0, 80))}${review.comment.length > 80 ? '...' : ''}</div></td>
                    <td style="white-space: nowrap;"><div>${formattedDate}</div><div style="font-size: 11px; color: var(--secondary-text);">${formattedTime}</div></td>
                    <td><span class="status-badge status-${review.status}">${review.status.charAt(0).toUpperCase() + review.status.slice(1)}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewReview(${review.id})" title="View"><i class="fa-solid fa-eye"></i></button>
                            ${review.status === 'pending' ? `
                                <button class="action-btn approve" onclick="approveReview(${review.id})" title="Approve"><i class="fa-solid fa-check"></i></button>
                                <button class="action-btn reject" onclick="rejectReview(${review.id})" title="Reject"><i class="fa-solid fa-times"></i></button>
                            ` : ''}
                            <button class="action-btn delete" onclick="deleteReview(${review.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
        const end = Math.min(start + perPage - 1, filteredReviews.length);
        const infoElement = document.getElementById('paginationInfo');
        infoElement.innerHTML = `Showing <strong>${filteredReviews.length > 0 ? start + '-' + end : '0'}</strong> of <strong>${filteredReviews.length}</strong> reviews`;
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredReviews.length / perPage);
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
        displayReviews();
    }

    function filterReviews() {
        const status = document.getElementById('statusFilter').value;
        const rating = document.getElementById('ratingFilter').value;
        const sortBy = document.getElementById('sortFilter').value;

        filteredReviews = reviewsArray.filter(review => {
            let showReview = true;
            if (status && review.status !== status) showReview = false;
            if (rating && showReview && review.rating < parseInt(rating)) showReview = false;
            return showReview;
        });

        if (sortBy) {
            filteredReviews.sort((a, b) => {
                if (sortBy === 'newest') return new Date(b.date) - new Date(a.date);
                if (sortBy === 'oldest') return new Date(a.date) - new Date(b.date);
                if (sortBy === 'rating-high') return b.rating - a.rating;
                if (sortBy === 'rating-low') return a.rating - b.rating;
                if (sortBy === 'helpful') return b.helpful_count - a.helpful_count;
                return 0;
            });
        }

        currentPage = 1;
        displayReviews();
    }

    document.addEventListener('DOMContentLoaded', function() {
        displayReviews();

        document.getElementById('statusFilter')?.addEventListener('change', filterReviews);
        document.getElementById('ratingFilter')?.addEventListener('change', filterReviews);
        document.getElementById('sortFilter')?.addEventListener('change', filterReviews);
    });

    function clearFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('ratingFilter').value = '';
        document.getElementById('sortFilter').value = '';
        filterReviews();
    }

    function viewReview(id) {
        window.location.href = 'review-details.php?id=' + id;
    }

    async function updateReviewStatus(reviewId, newStatus) {
        try {
            const response = await fetch('update-review-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `review_id=${reviewId}&status=${newStatus}`
            });
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    async function deleteReviewFromDB(reviewId) {
        try {
            const response = await fetch('delete-review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `review_id=${reviewId}`
            });
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    // ===== دوال الحذف الجماعي والموافقة الجماعية =====
    async function bulkApproveFromDB(reviewIds) {
        try {
            const response = await fetch('bulk-approve-reviews.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ review_ids: reviewIds })
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error:', error);
            return { success: false, message: 'Network error' };
        }
    }



    // ===== دالة showConfirmPopup =====
    function showConfirmPopup(message, onConfirm, successMessage) {
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

        function showToast(msg, isSuccess = true) {
            const toast = document.createElement('div');
            toast.innerHTML = `<div style="display: flex; align-items: center; gap: 12px;"><i class="fa-solid fa-${isSuccess ? 'check-circle' : 'exclamation-triangle'}" style="font-size: 24px; color: ${isSuccess ? '#4CAF50' : '#f44336'}"></i><div><strong>${msg}</strong></div></div>`;
            toast.style.position = 'fixed';
            toast.style.top = '50%';
            toast.style.left = '50%';
            toast.style.transform = 'translate(-50%, -50%)';
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

        document.getElementById('popup-cancel-btn').onclick = closePopup;
        document.getElementById('popup-confirm-btn').onclick = async () => {
            if (onConfirm) await onConfirm();
            closePopup();
            showToast(successMessage, true);
        };
        overlay.onclick = (e) => { if (e.target === overlay) closePopup(); };
    }

    // ===== دالة showToast العامة =====
    function showToast(message, isSuccess = true) {
        const toast = document.createElement('div');
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-${isSuccess ? 'check-circle' : 'exclamation-triangle'}" style="font-size: 24px; color: ${isSuccess ? '#4CAF50' : '#f44336'}"></i>
                <div>
                    <strong style="font-size: 16px;">${message}</strong>
                </div>
            </div>
        `;
        toast.style.position = 'fixed';
        toast.style.top = '50%';
        toast.style.left = '50%';
        toast.style.transform = 'translate(-50%, -50%) scale(0.9)';
        toast.style.backgroundColor = 'var(--card-bg, #fff)';
        toast.style.color = 'var(--text-color, #333)';
        toast.style.padding = '15px 25px';
        toast.style.borderRadius = '50px';
        toast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
        toast.style.zIndex = '10000';
        toast.style.fontFamily = "'Poppins', sans-serif";
        toast.style.border = `2px solid ${isSuccess ? '#4CAF50' : '#f44336'}`;
        toast.style.opacity = '0';
        toast.style.transition = 'all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1)';
        toast.style.fontWeight = '500';
        toast.style.textAlign = 'center';
        toast.style.minWidth = '280px';

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translate(-50%, -50%) scale(1)';
        }, 20);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, -50%) scale(0.95)';
            setTimeout(() => {
                if (toast && toast.parentNode) toast.remove();
            }, 250);
        }, 3000);
    }

    // ===== دالة الموافقة على مراجعة واحدة =====
    function approveReview(id) {
        showConfirmPopup('Approve this review? It will be visible to customers.', async () => {
            const success = await updateReviewStatus(id, 'approved');
            if (success) {
                const index = reviewsArray.findIndex(r => r.id == id);
                if (index !== -1) reviewsArray[index].status = 'approved';
                filterReviews();
            } else {
                showToast('Failed to approve review in database', false);
            }
        }, 'Review approved successfully!');
    }

    // ===== دالة رفض مراجعة واحدة =====
    function rejectReview(id) {
        showConfirmPopup('Reject this review? It will not be shown.', async () => {
            const success = await updateReviewStatus(id, 'rejected');
            if (success) {
                const index = reviewsArray.findIndex(r => r.id == id);
                if (index !== -1) reviewsArray[index].status = 'rejected';
                filterReviews();
            } else {
                showToast('Failed to reject review in database', false);
            }
        }, 'Review rejected successfully!');
    }

    // ===== دالة حذف مراجعة واحدة =====
    function deleteReview(id) {
        showConfirmPopup('Permanently delete this review? This cannot be undone.', async () => {
            const success = await deleteReviewFromDB(id);
            if (success) {
                const index = reviewsArray.findIndex(r => r.id == id);
                if (index !== -1) reviewsArray.splice(index, 1);
                filterReviews();
            }
        }, 'Review deleted successfully!');
    }

    // ===== دالة الموافقة الجماعية (Bulk Approve) =====
    async function bulkApprove() {
        const selected = [];
        document.querySelectorAll('.review-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if (selected.length === 0) {
            alert('Please select at least one review');
            return;
        }

        showConfirmPopup(
            `Are you sure you want to approve ${selected.length} review(s)?`,
            async () => {
                const result = await bulkApproveFromDB(selected);
                if (result.success) {
                    for (const id of selected) {
                        const index = reviewsArray.findIndex(r => r.id == id);
                        if (index !== -1) {
                            reviewsArray[index].status = 'approved';
                        }
                    }
                    filterReviews();
                    showToast(result.message, true);
                } else {
                    showToast(result.message || 'Failed to approve reviews', false);
                }
            },
            'Approving reviews...'
        );
    }

    // ===== دالة الحذف الجماعي (Bulk Delete) =====
    async function bulkDelete() {
        const selected = [];
        document.querySelectorAll('.review-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if (selected.length === 0) {
            alert('Please select at least one review');
            return;
        }

        showConfirmPopup(
            `⚠️ WARNING: You are about to permanently delete ${selected.length} review(s). This action cannot be undone!`,
            async () => {
                const result = await bulkDeleteFromDB(selected);
                if (result.success) {
                    for (const id of selected) {
                        const index = reviewsArray.findIndex(r => r.id == id);
                        if (index !== -1) {
                            reviewsArray.splice(index, 1);
                        }
                    }
                    filterReviews();
                    showToast(result.message, true);

                    const selectAll = document.getElementById('selectAll');
                    if (selectAll) selectAll.checked = false;
                } else {
                    showToast(result.message || 'Failed to delete reviews', false);
                }
            },
            'Deleting reviews...'
        );
    }

    // ===== تحديد كل المراجعات =====
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = this.checked);
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