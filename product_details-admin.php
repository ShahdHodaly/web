<?php
// product_details.php
session_start();

// تضمين مصفوفة المنتجات
require_once 'products.php';

// الحصول على ID المنتج من الرابط
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// التحقق من وجود المنتج
if (!isset($products[$product_id])) {
    header("Location: product-admin.php");
    exit;
}

$product = $products[$product_id];
$pageTitle = $product['name'] . " | Teddy Shop";

// جلب منتجات ذات صلة (من نفس التصنيف)
$related_products = [];
foreach ($products as $id => $item) {
    if ($id != $product_id && $item['category'] == $product['category']) {
        $related_products[$id] = $item;
    }
}
// أخذ أول 4 منتجات فقط
$related_products = array_slice($related_products, 0, 4, true);
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
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
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
                        $rating = $product['avg_rating'] ?? 0;
                        for($i = 1; $i <= 5; $i++):
                            ?>
                            <i class="fa-<?= $i <= $rating ? 'solid' : 'regular' ?> fa-star" style="color: #FFD700;"></i>
                        <?php endfor; ?>
                        <span class="rating-value"><?= number_format($rating, 1) ?></span>
                        <span style="color: var(--secondary-text);">(<?= $product['sales_count'] ?? 0 ?> reviews)</span>
                    </div>

                    <!-- Price -->
                    <div class="product-price">
                        $<?= number_format($product['price'], 2) ?>
                    </div>

                    <!-- Description -->
                    <div class="product-description">
                        <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available for this product.')) ?>
                    </div>

                    <!-- Stats -->
                    <div class="product-stats">
                        <div class="stat-item">
                            <i class="fa-solid fa-chart-line"></i>
                            <div class="stat-value"><?= number_format($product['sales_count'] ?? 0) ?></div>
                            <div class="stat-label">Sales</div>
                        </div>
                        <div class="stat-item">
                            <i class="fa-solid fa-star"></i>
                            <div class="stat-value"><?= number_format($product['avg_rating'] ?? 0, 1) ?></div>
                            <div class="stat-label">Rating</div>
                        </div>
                        <div class="stat-item">
                            <i class="fa-solid fa-calendar"></i>
                            <div class="stat-value"><?= date('M d, Y', strtotime($product['created_at'] ?? 'now')) ?></div>
                            <div class="stat-label">Added</div>
                        </div>
                    </div>

                    <!-- Stock Status -->
                    <?php
                    $stock = $product['stock'] ?? 0;
                    $stockClass = $stock > 10 ? 'stock-in' : ($stock > 0 ? 'stock-low' : 'stock-out');
                    $stockText = $stock > 10 ? 'In Stock' : ($stock > 0 ? 'Low Stock (' . $stock . ' left)' : 'Out of Stock');
                    ?>
                    <div class="stock-badge <?= $stockClass ?>">
                        <i class="fa-solid fa-<?= $stock > 10 ? 'check-circle' : ($stock > 0 ? 'exclamation-triangle' : 'times-circle') ?>"></i>
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
                       Related Product
                    </div>
                    <div class="related-grid">
                        <?php foreach($related_products as $id => $item): ?>
                            <a href="product_details-admin.php?id=<?= $id ?>" class="related-card">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="related-img">
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
    // Delete product function
    function deleteProduct(id) {
        showAdminConfirm('Are you sure you want to delete this product?', () => {
        })
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