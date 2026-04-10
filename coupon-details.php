<?php
// coupon-details.php
session_start();

// تضمين مصفوفة الكوبونات
require_once 'coupons-array.php';

// الحصول على ID الكوبون من الرابط
$coupon_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// التحقق من وجود الكوبون
if (!isset($coupons[$coupon_id])) {
    $_SESSION['error'] = 'Coupon not found';
    header("Location: coupons.php");
    exit;
}

$coupon = $coupons[$coupon_id];
$pageTitle = $coupon['code'] . " | Teddy Shop";
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
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; align-items: flex-start; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); box-sizing: border-box; }

        .coupon-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 900px;
            margin: 0 auto;
        }

        .coupon-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .coupon-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 5px;
            font-family: monospace;
            letter-spacing: 1px;
        }
        .coupon-title p {
            color: var(--secondary-text);
        }
        .coupon-status {
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-active { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-scheduled { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .status-expired { background: rgba(244, 67, 54, 0.2); color: #F44336; }

        /* Coupon Code Display */
        .code-display {
            background: linear-gradient(135deg, var(--bg-color), rgba(248, 187, 208, 0.1));
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
            border: 2px dashed var(--pink);
        }
        .code-display .code {
            font-size: 32px;
            font-weight: 700;
            font-family: monospace;
            letter-spacing: 3px;
            color: var(--primary);
            background: var(--card-bg);
            display: inline-block;
            padding: 10px 30px;
            border-radius: 50px;
            margin-bottom: 10px;
        }
        .copy-btn {
            background: var(--lavender);
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .copy-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-card {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
        }
        .info-card h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-card .value {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color);
        }
        .info-card .small-text {
            font-size: 12px;
            color: var(--secondary-text);
            margin-top: 5px;
        }

        /* Discount Badge */
        .discount-badge-large {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary), var(--pink));
            border-radius: 20px;
            margin-bottom: 25px;
        }
        .discount-badge-large .discount-value {
            font-size: 48px;
            font-weight: 800;
            color: white;
        }
        .discount-badge-large .discount-type {
            font-size: 16px;
            color: white;
            opacity: 0.9;
        }

        /* Usage Progress */
        .usage-section {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .usage-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .usage-progress {
            width: 100%;
            height: 12px;
            background: rgba(128,128,128,0.1);
            border-radius: 20px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 20px;
            transition: width 0.3s ease;
        }
        .progress-fill.active { background: linear-gradient(90deg, var(--primary), var(--pink)); }
        .progress-fill.warning { background: linear-gradient(90deg, #FF9800, #FFB74D); }
        .progress-fill.danger { background: linear-gradient(90deg, #F44336, #FF8A80); }

        /* Date Range */
        .date-range {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .date-box {
            flex: 1;
            background: var(--bg-color);
            border-radius: 20px;
            padding: 15px;
            text-align: center;
        }
        .date-box i {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 8px;
        }
        .date-box .date {
            font-weight: 600;
            color: var(--text-color);
        }
        .date-box .label {
            font-size: 11px;
            color: var(--secondary-text);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-edit {
            background: var(--lavender);
            color: #000;
        }
        .btn-edit:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }
        .btn-delete {
            background: #ff6b6b;
            color: white;
        }
        .btn-delete:hover {
            background: #ff4757;
            transform: translateY(-2px);
        }
        .btn-back {
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 1px solid rgba(128,128,128,0.2);
        }
        .btn-back:hover {
            border-color: var(--pink);
            transform: translateY(-2px);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; }
            .info-grid { grid-template-columns: 1fr; }
            .date-range { flex-direction: column; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="coupon-container">
            <!-- Coupon Header -->
            <div class="coupon-header">
                <div class="coupon-title">
                    <h1><?= htmlspecialchars($coupon['code']) ?></h1>
                    <p><i class="fa-regular fa-tag"></i> Coupon ID: #<?= $coupon_id ?></p>
                </div>
                <div class="coupon-status status-<?= $coupon['status'] ?>">
                    <i class="fa-solid fa-<?= $coupon['status'] == 'active' ? 'check-circle' : ($coupon['status'] == 'scheduled' ? 'clock' : 'times-circle') ?>"></i>
                    <?= ucfirst($coupon['status']) ?>
                </div>
            </div>

            <!-- Coupon Code Display -->
            <div class="code-display">
                <div class="code"><?= $coupon['code'] ?></div>
                <button class="copy-btn" onclick="copyCoupon('<?= $coupon['code'] ?>')">
                    <i class="fa-regular fa-copy"></i> Copy Code
                </button>
            </div>

            <!-- Discount Badge -->
            <div class="discount-badge-large">
                <?php if ($coupon['discount_type'] == 'percentage'): ?>
                    <div class="discount-value"><?= $coupon['discount_value'] ?>% OFF</div>
                    <div class="discount-type">Percentage Discount</div>
                    <?php if ($coupon['max_discount'] > 0): ?>
                        <div class="small-text" style="color: white; margin-top: 5px;">Max discount: $<?= $coupon['max_discount'] ?></div>
                    <?php endif; ?>
                <?php elseif ($coupon['discount_type'] == 'fixed'): ?>
                    <div class="discount-value">$<?= $coupon['discount_value'] ?> OFF</div>
                    <div class="discount-type">Fixed Amount Discount</div>
                <?php else: ?>
                    <div class="discount-value">FREE SHIPPING</div>
                    <div class="discount-type">Free Shipping on all orders</div>
                <?php endif; ?>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <h4><i class="fa-solid fa-receipt"></i> Description</h4>
                    <div class="value" style="font-size: 16px; font-weight: normal;"><?= htmlspecialchars($coupon['description']) ?></div>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-dollar-sign"></i> Minimum Order</h4>
                    <div class="value"><?= $coupon['min_order'] > 0 ? '$' . $coupon['min_order'] : 'No minimum' ?></div>
                    <div class="small-text">Required order amount to use this coupon</div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="usage-section">
                <h4 style="margin-bottom: 15px;"><i class="fa-solid fa-chart-line"></i> Usage Statistics</h4>
                <div class="usage-stats">
                    <span><strong><?= $coupon['used_count'] ?></strong> used</span>
                    <span>out of <strong><?= $coupon['usage_limit'] ?></strong> total</span>
                    <span><strong><?= round(($coupon['used_count'] / $coupon['usage_limit']) * 100) ?>%</strong> used</span>
                </div>
                <?php
                $usagePercentage = ($coupon['used_count'] / $coupon['usage_limit']) * 100;
                $barClass = $usagePercentage < 50 ? 'active' : ($usagePercentage < 80 ? 'warning' : 'danger');
                ?>
                <div class="usage-progress">
                    <div class="progress-fill <?= $barClass ?>" style="width: <?= $usagePercentage ?>%"></div>
                </div>
                <div class="small-text" style="margin-top: 10px; color: var(--secondary-text);">
                    <?= $coupon['usage_limit'] - $coupon['used_count'] ?> uses remaining
                </div>
            </div>

            <!-- Date Range -->
            <div class="date-range">
                <div class="date-box">
                    <i class="fa-regular fa-calendar-check"></i>
                    <div class="date"><?= date('F d, Y', strtotime($coupon['start_date'])) ?></div>
                    <div class="label">Start Date</div>
                </div>
                <div class="date-box">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <div class="date"><?= date('F d, Y', strtotime($coupon['expiry_date'])) ?></div>
                    <div class="label">Expiry Date</div>
                </div>
            </div>

            <!-- Applicable Products/Categories -->
            <div class="info-card" style="margin-top: 25px;">
                <h4><i class="fa-solid fa-box"></i> Applicable To</h4>
                <div class="value" style="font-size: 14px; font-weight: normal;">
                    <?php if ($coupon['applicable_products'] == 'all'): ?>
                        <span class="badge" style="background: var(--lavender); padding: 5px 12px; border-radius: 30px;">All Products</span>
                    <?php else: ?>
                        <span class="badge" style="background: var(--primary); padding: 5px 12px; border-radius: 30px;">Specific Products</span>
                    <?php endif; ?>

                    <?php if ($coupon['applicable_categories'] != 'all'): ?>
                        <span class="badge" style="background: var(--pink); padding: 5px 12px; border-radius: 30px; margin-left: 8px;">
                            Category: <?= ucfirst($coupon['applicable_categories']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="edit-coupon.php?id=<?= $coupon_id ?>" class="btn-action btn-edit">
                    <i class="fa-solid fa-pen"></i> Edit Coupon
                </a>

                <a href="coupons.php" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Coupons
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    function copyCoupon(code) {
        navigator.clipboard.writeText(code).then(() => {
            // إنشاء الـ toast
            const toast = document.createElement('div');
            toast.textContent = 'Copied!';
            toast.style.position = 'fixed';
            toast.style.bottom = '500px';
            toast.style.left = '50%';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
            toast.style.backgroundColor = 'var(--card-bg, #ffffff)';
            toast.style.color = 'var(--text-color, #333)';
            toast.style.border = '1px solid var(--pink, #F8BBD0)';
            toast.style.borderRadius = '50px';
            toast.style.padding = '10px 28px';
            toast.style.fontFamily = "'Poppins', sans-serif";
            toast.style.fontSize = '14px';
            toast.style.fontWeight = '600';
            toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.15)';
            toast.style.zIndex = '9999';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
            toast.style.whiteSpace = 'nowrap';
            toast.style.pointerEvents = 'none';

            document.body.appendChild(toast);

            // تأثير الظهور
            requestAnimationFrame(function() {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(-50%) translateY(0)';
            });

            // إخفاء بعد ثانية ونص
            setTimeout(function() {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(20px)';
                setTimeout(function() {
                    toast.remove();
                }, 350);
            }, 1500);
        });
    }


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