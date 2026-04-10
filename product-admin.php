<?php
// products.php
session_start();

// تضمين مصفوفة المنتجات
require_once 'products.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$totalGames = count($products);
$totalPages = ceil($totalGames / $perPage);
$offset = ($page - 1) * $perPage;
$paginatedGames = array_slice($products, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products · Teddy Shop</title>
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
        .filter-item { flex: 1;  min-width: 150px; }
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
            transition: background-color 0.3s ease;
        }
        .products-table tbody tr:hover td {
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
        .action-btn.edit:hover { background: var(--lavender); }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }
        .action-btn.view:hover { background: var(--primary); color: white; }

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

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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

        /* تأثيرات لزر Add Product */
        .add-product-btn {
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
        .add-product-btn:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 8px 25px var(--shadow);
        }
        .add-product-btn:active {
            transform: translateY(-1px);
        }
        .add-product-btn div:first-child {
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
        .add-product-btn:hover div:first-child {
            background: var(--lavender);

        }
        .add-product-btn div:first-child i {
            transition: transform 0.3s ease;
        }
        .add-product-btn:hover div:first-child i {
            transform: rotate(180deg);
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
                <h1 style="margin-bottom: 5px;">Products Management</h1>
                <p style="color: var(--secondary-text);">Manage your game inventory</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" style="background: var(--lavender); color: #000; transition: all 0.3s ease;" onclick="importProducts()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-download"></i> Import
                </button>
                <button class="btn-primary" style="background: var(--pink); color: #000; transition: all 0.3s ease;" onclick="exportProducts()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-upload"></i> Export
                </button>
            </div>
        </div>

        <!-- Search Bar + Add Product -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap;">
            <!-- Search Bar -->
            <div class="search-container">
                <form action="search-product.php" method="GET" style="width: 100%;">
                    <div style="position: relative; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text"
                               name="q"
                               class="search-input"
                               placeholder="Search for any products"
                               id="searchInput">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <i class="fa-solid fa-arrow-right"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Add Product Button -->
            <a href="add-product.php" class="add-product-btn" id="addProductBtn">
                <div>
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <div style="font-weight: 600; color: var(--text-color);">Add Product</div>
                    <div style="font-size: 12px; color: var(--secondary-text);">New item</div>
                </div>
            </a>
        </div>

        <!-- Stats Cards -->
        <?php
        // حساب الإحصائيات
        $totalProducts = count($products);
        $categories = [];
        foreach($products as $product) {
            $categories[$product['category']] = true;
        }
        $totalCategories = count($categories);
        ?>
        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Total Products</h4>
                    <div class="value"><?= $totalProducts ?></div>
                </div>
                <i class="fa-solid fa-box stat-mini-icon"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Categories</h4>
                    <div class="value"><?= $totalCategories ?></div>
                </div>
                <i class="fa-solid fa-tags stat-mini-icon" style="color: #4CAF50;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>With Reviews</h4>
                    <div class="value">0</div>
                </div>
                <i class="fa-solid fa-star stat-mini-icon" style="color: #FFD700;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>New This Month</h4>
                    <div class="value">0</div>
                </div>
                <i class="fa-solid fa-calendar-plus stat-mini-icon" style="color: var(--primary);"></i>
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
                    <select class="filter-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="teddy bear">Teddy Bears</option>
                        <option value="dolls & barbie">Dolls & Barbie</option>
                        <option value="building toys">Building Toys</option>
                        <option value="cars & vehicles">Cars & Vehicles</option>
                        <option value="group games">Group Games</option>
                        <option value="educational toys">Educational Toys</option>
                        <option value="puzzles">Puzzles</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="priceFilter">
                        <option value="">Price Range</option>
                        <option value="0-25">$0 - $25</option>
                        <option value="25-50">$25 - $50</option>
                        <option value="50-100">$50 - $100</option>
                        <option value="100+">$100+</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="sortFilter">
                        <option value="">Sort by</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name">Name</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-container">
            <table class="products-table">
                <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" style="transform: scale(1.2); cursor: pointer;" id="selectAll">
                    </th>
                    <th>Game</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="productsTableBody">
                <!-- المنتجات رح تتحط هنا عن طريق JavaScript -->
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-section">
                <div class="pagination-info" id="paginationInfo">
                    Showing <strong>0</strong> of <strong><?= $totalGames ?></strong> products
                </div>
                <div class="pagination" id="paginationControls">
                    <!-- Pagination رح يتولد عن طريق JavaScript -->
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
            <button class="btn-primary" style="background: #ff6b6b; transition: all 0.3s ease;" onclick="bulkDelete()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-trash-can"></i> Bulk Delete
            </button>
        </div>
    </main>
</div>

<script>
    // ===== بيانات المنتجات من PHP إلى JavaScript =====
    const allProducts = <?php echo json_encode($products); ?>;
    const productsArray = Object.entries(allProducts).map(([id, product]) => {
        return {
            id: id,
            ...product,
            price: parseFloat(product.price),
            category: product.category.toLowerCase()
        };
    });

    let currentPage = <?= $page ?>;
    let perPage = <?= $perPage ?>;
    let filteredProducts = [...productsArray];
    let totalFilteredProducts = filteredProducts.length;

    // ===== دوال عرض المنتجات =====
    function displayProducts() {
        const tbody = document.getElementById('productsTableBody');
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const paginatedProducts = filteredProducts.slice(start, end);

        let html = '';
        paginatedProducts.forEach(product => {
            // توليد النجوم
            let stars = '';
            const rating = product.avg_rating || 0;
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fa-solid fa-star" style="color: #FFD700; font-size: 14px;"></i>';
                } else {
                    stars += '<i class="fa-regular fa-star" style="color: #FFD700; font-size: 14px;"></i>';
                }
            }

            html += `
                <tr data-id="${product.id}">
                    <td><input type="checkbox" class="product-checkbox" style="transform: scale(1.2); cursor: pointer;" value="${product.id}"></td>
                    <td>
                        <div class="product-info">
                            <img src="${product.image}" alt="${product.name}" class="product-img">
                            <div>
                                <strong>${product.name}</strong>
                                <div style="font-size: 12px; color: var(--secondary-text);">ID: #${product.id}</div>
                            </div>
                        </div>
                    </td>
                    <td><strong>$${product.price.toFixed(2)}</strong></td>
                    <td>
                        <span style="background: var(--lavender); padding: 5px 15px; border-radius: 30px; font-size: 12px; font-weight: 600;">
                            ${product.category}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            ${stars}
                            <span style="color: var(--secondary-text); font-size: 12px; margin-left: 5px;">(${product.sales_count || 0})</span>
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewProduct(${product.id})" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="action-btn edit" onclick="editProduct(${product.id})" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="action-btn delete" onclick="deleteProduct(${product.id})" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        updatePaginationInfo();
        updatePaginationControls();
    }

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

    function updatePaginationInfo() {
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(start + perPage - 1, filteredProducts.length);
        const infoElement = document.querySelector('.pagination-info');
        infoElement.innerHTML = `Showing <strong>${filteredProducts.length > 0 ? start + '-' + end : '0'}</strong> of <strong>${filteredProducts.length}</strong> products`;
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredProducts.length / perPage);
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
        displayProducts();
    }

    // ===== دوال الفلترة =====
    function filterProducts() {
        const category = document.getElementById('categoryFilter').value;
        const priceRange = document.getElementById('priceFilter').value;
        const sortBy = document.getElementById('sortFilter').value;

        // تطبيق الفلترة على كل المنتجات
        filteredProducts = productsArray.filter(product => {
            let showProduct = true;

            // فلترة حسب التصنيف
            if (category && product.category !== category) {
                showProduct = false;
            }

            // فلترة حسب السعر
            if (priceRange && showProduct) {
                const price = product.price;
                if (priceRange === '0-25' && price > 25) showProduct = false;
                else if (priceRange === '25-50' && (price <= 25 || price > 50)) showProduct = false;
                else if (priceRange === '50-100' && (price <= 50 || price > 100)) showProduct = false;
                else if (priceRange === '100+' && price <= 100) showProduct = false;
            }

            return showProduct;
        });

        // تطبيق الترتيب
        if (sortBy) {
            filteredProducts.sort((a, b) => {
                if (sortBy === 'price-low') return a.price - b.price;
                if (sortBy === 'price-high') return b.price - a.price;
                if (sortBy === 'name') return a.name.localeCompare(b.name);
                return 0;
            });
        }

        // إعادة تعيين الصفحة الحالية
        currentPage = 1;

        // عرض المنتجات المفلترة
        displayProducts();
    }

    // ===== تأثيرات السيرش بار =====
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const addBtn = document.getElementById('addProductBtn');

        if (searchInput) {
            // تأثيرات السيرش بار
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

        if (addBtn) {
            // تأثيرات زر Add Product
            addBtn.addEventListener('mouseenter', function() {
                this.querySelector('div:first-child').style.transform = 'rotate(90deg)';
            });

            addBtn.addEventListener('mouseleave', function() {
                this.querySelector('div:first-child').style.transform = 'rotate(0)';
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
        filterProducts();
    });

    // ===== ربط الفلاتر =====
    document.getElementById('categoryFilter')?.addEventListener('change', filterProducts);
    document.getElementById('priceFilter')?.addEventListener('change', filterProducts);
    document.getElementById('sortFilter')?.addEventListener('change', filterProducts);

    // ===== دوال مساعدة =====
    function clearFilters() {
        document.querySelectorAll('.filter-select').forEach(select => select.value = '');
        filterProducts();

        // تأثير بسيط
        const filterSection = document.querySelector('.filters-section');
        filterSection.style.transform = 'scale(1.02)';
        setTimeout(() => {
            filterSection.style.transform = 'scale(1)';
        }, 200);
    }

    // وظائف الأزرار
    function viewProduct(id) {
        window.location.href = 'product_details-admin.php?id=' + id;
    }

    function editProduct(id) {
        window.location.href = 'edit-product.php?id=' + id;
    }

    function deleteProduct(id) {
        showAdminConfirm('Are you sure you want to delete this product?', () => {

        })

    }

    // تحديد كل المنتجات
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.product-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    function importProducts() {
        alert('Import products feature (Demo)');
    }

    function exportProducts() {
        alert('Export products feature (Demo)');
    }



    function bulkDelete() {
        let selected = [];
        document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if(selected.length === 0) {
            alert('Please select at least one product');
            return;
        }

        showAdminConfirm('Are you sure you want to delete ' + selected.length + ' products?' , () =>{

        } );
    }
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