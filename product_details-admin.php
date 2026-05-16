<?php
// product_details-admin.php
session_start();
require_once 'db.php';

$pdo = getDB();

// الحصول على ID المنتج من الرابط
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب المنتج من قاعدة البيانات
$stmt = $pdo->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.description,
        p.price,
        p.category_id,
        p.image,
        p.stock,
        p.sales_count,
        p.created_at,
        p.sale_price,
        c.name as category_name,
        c.category_id as cat_id
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = ?
");
$stmt->execute([$product_id]);
$productData = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود المنتج
if (!$productData) {
    header("Location: product-admin.php");
    exit;
}

// جلب التقييمات للمنتج
$stmt = $pdo->prepare("
    SELECT 
        COALESCE(AVG(rating), 0) as avg_rating,
        COUNT(*) as review_count
    FROM reviews 
    WHERE product_id = ? AND status = 'approved'
");
$stmt->execute([$product_id]);
$ratingData = $stmt->fetch(PDO::FETCH_ASSOC);

// تنسيق المنتج للعرض
$product = [
        'id' => $productData['product_id'],
        'name' => $productData['name'],
        'description' => $productData['description'],
        'price' => (float)$productData['price'],
        'category' => $productData['category_name'],
        'image' => !empty($productData['image']) ? $productData['image'] : 'images/placeholder.png',
        'stock' => (int)$productData['stock'],
        'sales_count' => (int)$productData['sales_count'],
        'avg_rating' => round($ratingData['avg_rating'], 1),
        'review_count' => (int)$ratingData['review_count'],
        'created_at' => $productData['created_at'],
        'sale_price' => $productData['sale_price'] ? (float)$productData['sale_price'] : null
];

$pageTitle = $product['name'] . " | Teddy Shop";

// جلب منتجات ذات صلة (من نفس التصنيف)
$stmt = $pdo->prepare("
    SELECT 
        p.product_id as id,
        p.name,
        p.price,
        p.image
    FROM products p
    WHERE p.category_id = ? AND p.product_id != ?
    ORDER BY RANDOM()
    LIMIT 4
");
$stmt->execute([$productData['category_id'], $product_id]);
$relatedProductsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$related_products = [];
foreach ($relatedProductsData as $item) {
    $related_products[$item['id']] = [
            'name' => $item['name'],
            'price' => (float)$item['price'],
            'image' => !empty($item['image']) ? $item['image'] : 'images/placeholder.png'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
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

        /* Product Container */
        .product-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
        }

        /* Product Layout */
        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        /* Product Image Section */
        .product-image-section {
            background: var(--bg-color);
            border-radius: 25px;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(128,128,128,0.1);
        }
        .product-image {
            max-width: 100%;
            max-height: 350px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .product-image:hover {
            transform: scale(1.05);
        }

        /* Product Info Section */
        .product-info-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .product-name {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        .product-category {
            display: inline-block;
            background: var(--lavender);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            color: #000;
        }
        .product-price {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin: 15px 0;
        }
        .product-description {
            color: var(--secondary-text);
            line-height: 1.8;
            padding: 15px 0;
            border-top: 1px solid rgba(128,128,128,0.1);
            border-bottom: 1px solid rgba(128,128,128,0.1);
        }

        /* Product Stats */
        .product-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            padding: 15px 0;
        }
        .stat-item {
            text-align: center;
            padding: 12px;
            background: var(--bg-color);
            border-radius: 20px;
        }
        .stat-item i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 8px;
            display: block;
        }
        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color);
        }
        .stat-label {
            font-size: 12px;
            color: var(--secondary-text);
        }

        /* Stock Badge */
        .stock-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
        }
        .stock-in { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .stock-low { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .stock-out { background: rgba(244, 67, 54, 0.2); color: #F44336; }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .btn-action {
            flex: 1;
            padding: 14px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-edit {
            background: var(--lavender);
            color: #000;
        }
        .btn-edit:hover {
            background: var(--primary);
            transform: translateY(-3px);
            color: white;
        }
        .btn-delete {
            background: #ff6b6b;
            color: white;
        }
        .btn-delete:hover {
            background: #ff4757;
            transform: translateY(-3px);
        }
        .btn-back {
            background: var(--card-bg);
            color: var(--text-color);
            border: 2px solid rgba(128,128,128,0.2);
        }
        .btn-back:hover {
            border-color: var(--pink);
            transform: translateY(-3px);
        }

        /* Rating Stars */
        .rating-stars {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        .rating-stars i {
            font-size: 18px;
        }
        .rating-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-left: 10px;
        }

        /* Related Products */
        .related-section {
            margin-top: 50px;
        }
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i {
            color: var(--pink);
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .related-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 2px 8px var(--shadow);
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px var(--shadow);
        }
        .related-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: var(--bg-color);
        }
        .related-info {
            padding: 15px;
            text-align: center;
        }
        .related-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
            font-size: 14px;
        }
        .related-price {
            color: var(--primary);
            font-weight: 700;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            .product-layout { grid-template-columns: 1fr; gap: 30px; }
            .product-stats { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="product-container">
            <!-- Product Layout -->
            <div class="product-layout">
                <!-- Image Section -->
                <div class="product-image-section">
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" onerror="this.src='images/placeholder.png'">
                </div>

                <!-- Info Section -->
                <div class="product-info-section">
                    <div>
                        <span class="product-category">
                            <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($product['category']) ?>
                        </span>
                    </div>
                    <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

                    <!-- Rating -->
                    <div class="rating-stars">
                        <?php
                        $rating = $product['avg_rating'];
                        $fullStars = floor($rating);
                        $hasHalfStar = ($rating - $fullStars) >= 0.5;

                        for($i = 1; $i <= 5; $i++):
                            if ($i <= $fullStars) {
                                echo '<i class="fa-solid fa-star" style="color: #FFD700;"></i>';
                            } elseif ($hasHalfStar && $i == $fullStars + 1) {
                                echo '<i class="fa-solid fa-star-half-alt" style="color: #FFD700;"></i>';
                            } else {
                                echo '<i class="fa-regular fa-star" style="color: #FFD700;"></i>';
                            }
                        endfor;
                        ?>
                        <span class="rating-value"><?= number_format($rating, 1) ?></span>
                        <span style="color: var(--secondary-text);">(<?= $product['review_count'] ?> reviews)</span>
                    </div>

                    <!-- Price -->
                    <div class="product-price">
                        $<?= number_format($product['price'], 2) ?>
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                            <span style="font-size: 20px; color: #999; text-decoration: line-through; margin-left: 10px;">
                                $<?= number_format($product['sale_price'], 2) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="product-description">
                        <?php
                        if (!empty($product['description'])) {
                            echo nl2br(htmlspecialchars($product['description']));
                        } else {
                            echo '<em>No description available for this product.</em>';
                        }
                        ?>
                    </div>

                    <!-- Stats -->
                    <div class="product-stats">
                        <div class="stat-item">
                            <i class="fa-solid fa-chart-line"></i>
                            <div class="stat-value"><?= number_format($product['sales_count']) ?></div>
                            <div class="stat-label">Total Sales</div>
                        </div>
                        <div class="stat-item">
                            <i class="fa-solid fa-star"></i>
                            <div class="stat-value"><?= number_format($product['avg_rating'], 1) ?></div>
                            <div class="stat-label">Average Rating</div>
                        </div>
                        <div class="stat-item">
                            <i class="fa-solid fa-calendar"></i>
                            <div class="stat-value"><?= date('M d, Y', strtotime($product['created_at'])) ?></div>
                            <div class="stat-label">Date Added</div>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <?php
                    $stock = $product['stock'];
                    if ($stock > 10) {
                        $stockClass = 'stock-in';
                        $stockIcon = 'check-circle';
                        $stockText = 'In Stock (' . $stock . ' units)';
                    } elseif ($stock > 0) {
                        $stockClass = 'stock-low';
                        $stockIcon = 'exclamation-triangle';
                        $stockText = 'Low Stock (' . $stock . ' left)';
                    } else {
                        $stockClass = 'stock-out';
                        $stockIcon = 'times-circle';
                        $stockText = 'Out of Stock';
                    }
                    ?>
                    <div class="stock-badge <?= $stockClass ?>">
                        <i class="fa-solid fa-<?= $stockIcon ?>"></i>
                        <?= $stockText ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="edit-product.php?id=<?= $product_id ?>" class="btn-action btn-edit">
                            <i class="fa-solid fa-pen"></i> Edit Product
                        </a>
                        <button class="btn-action btn-delete" onclick="deleteProduct(<?= $product_id ?>)">
                            <i class="fa-solid fa-trash"></i> Delete Product
                        </button>
                        <a href="product-admin.php" class="btn-action btn-back">
                            <i class="fa-solid fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
                <div class="related-section">
                    <div class="section-title">
                        <i class="fa-solid fa-arrow-right"></i>
                        Related Products
                    </div>
                    <div class="related-grid">
                        <?php foreach($related_products as $id => $item): ?>
                            <a href="product_details-admin.php?id=<?= $id ?>" class="related-card">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="related-img" onerror="this.src='images/placeholder.png'">
                                <div class="related-info">
                                    <div class="related-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="related-price">$<?= number_format($item['price'], 2) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
    // دالة حذف المنتج مع الاتصال بقاعدة البيانات
    async function deleteProductFromDB(productId) {
        try {
            const response = await fetch('delete-product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            });
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    function showAdminConfirm(message, onConfirm) {
        // إنشاء overlay الخلفية
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

        // إنشاء نافذة الـ Popup
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

        // دالة عرض رسالة النجاح
        function showSuccessToast(message = 'Product deleted successfully!') {
            const toast = document.createElement('div');
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-check-circle" style="font-size: 28px; color: #28a745;"></i>
                    <div>
                        <strong style="font-size: 18px;">${message}</strong>
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
            toast.style.border = '2px solid #28a745';
            toast.style.backdropFilter = 'blur(12px)';
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
            }, 2500);
        }

        // أحداث الأزرار
        const cancelBtn = popup.querySelector('#confirm-cancel-btn');
        const confirmBtn = popup.querySelector('#confirm-ok-btn');

        cancelBtn.addEventListener('click', () => {
            closePopup();
        });

        confirmBtn.addEventListener('click', async () => {
            if (onConfirm && typeof onConfirm === 'function') {
                await onConfirm();
            }
            closePopup();
            showSuccessToast();

            // توجيه المستخدم إلى صفحة المنتجات بعد ثانية ونصف
            setTimeout(() => {
                window.location.href = 'product-admin.php';
            }, 1500);
        });

        // إغلاق عند الضغط على overlay
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePopup();
        });
    }

    // Delete product function مع الحذف الفعلي من قاعدة البيانات
    function deleteProduct(id) {
        showAdminConfirm('Are you sure you want to delete this product?', async () => {
            const success = await deleteProductFromDB(id);
            if (!success) {
                // عرض رسالة خطأ إذا فشل الحذف
                const errorToast = document.createElement('div');
                errorToast.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fa-solid fa-exclamation-triangle" style="font-size: 28px; color: #ff4757;"></i>
                        <div>
                            <strong style="font-size: 18px;">Failed to delete product!</strong>
                        </div>
                    </div>
                `;
                errorToast.style.position = 'fixed';
                errorToast.style.top = '50%';
                errorToast.style.left = '50%';
                errorToast.style.transform = 'translate(-50%, -50%) scale(0.9)';
                errorToast.style.backgroundColor = 'var(--card-bg, #fff)';
                errorToast.style.color = 'var(--text-color, #333)';
                errorToast.style.padding = '18px 28px';
                errorToast.style.borderRadius = '60px';
                errorToast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
                errorToast.style.zIndex = '10000';
                errorToast.style.fontFamily = "'Poppins', sans-serif";
                errorToast.style.border = '2px solid #ff4757';
                errorToast.style.backdropFilter = 'blur(12px)';
                errorToast.style.opacity = '0';
                errorToast.style.transition = 'all 0.25s ease';
                errorToast.style.fontWeight = '500';
                errorToast.style.textAlign = 'center';
                errorToast.style.minWidth = '280px';

                document.body.appendChild(errorToast);

                setTimeout(() => {
                    errorToast.style.opacity = '1';
                    errorToast.style.transform = 'translate(-50%, -50%) scale(1)';
                }, 20);

                setTimeout(() => {
                    errorToast.style.opacity = '0';
                    errorToast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                    setTimeout(() => {
                        if (errorToast && errorToast.parentNode) errorToast.remove();
                    }, 250);
                }, 2500);
            }
        });
    }

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
            themeSwitchMain.addEventListener('change', function(e) {
                applyTheme(this.checked);
                localStorage.setItem('theme', this.checked ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>