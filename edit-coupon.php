<?php
// edit-coupon.php
session_start();

// الاتصال بقاعدة البيانات
require_once 'db.php';
$pdo = getDB();
// الحصول على ID الكوبون من الرابط
$coupon_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات الكوبون من الداتا بيز
$stmt = $pdo->prepare("SELECT * FROM Coupons WHERE coupon_id = ?");
$stmt->execute([$coupon_id]);
$coupon = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود الكوبون
if (!$coupon) {
    $_SESSION['error'] = 'Coupon not found';
    header("Location: coupons.php");
    exit;
}

$pageTitle = "Edit " . $coupon['code'] . " | Teddy Shop";

// متغيرات النموذج (نجلب البيانات الحالية من الداتا بيز)
$code = $coupon['code'];
$description = $coupon['description'];
$discount_type = $coupon['discount_type'];
$discount_value = $coupon['discount_value'];
$min_order = $coupon['min_order'];
$max_discount = $coupon['max_discount'];
$usage_limit = $coupon['usage_limit'];
$start_date = $coupon['start_date'];
$expiry_date = $coupon['expiry_date'];
$status = $coupon['status'];

// هالحقولين مو موجوين بالداتا بيز، فعطيتهم قيم افتراضية عشان الفرونت
$applicable_products = 'all';
$applicable_categories = 'all';

$errors = [];
$success = false;

// معالجة إرسال النموذج (تحديث البيانات)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $description = trim($_POST['description'] ?? '');
    $discount_type = trim($_POST['discount_type'] ?? '');
    $discount_value = floatval($_POST['discount_value'] ?? 0);
    $min_order = floatval($_POST['min_order'] ?? 0);
    $max_discount = floatval($_POST['max_discount'] ?? 0);
    $usage_limit = intval($_POST['usage_limit'] ?? 0);
    $start_date = trim($_POST['start_date'] ?? '');
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $applicable_products = trim($_POST['applicable_products'] ?? 'all');
    $applicable_categories = trim($_POST['applicable_categories'] ?? 'all');

    // التحقق من صحة البيانات
    if (empty($code)) {
        $errors[] = 'Coupon code is required';
    }

    // التحقق من تكرار الكود (مع استثناء الكوبون الحالي)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Coupons WHERE code = ? AND coupon_id != ?");
    $stmt->execute([$code, $coupon_id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Coupon code already exists for another coupon';
    }

    if (empty($description)) {
        $errors[] = 'Description is required';
    }

    if (empty($discount_type)) {
        $errors[] = 'Discount type is required';
    }

    if ($discount_value <= 0 && $discount_type != 'shipping') {
        $errors[] = 'Discount value must be greater than 0';
    }

    if ($usage_limit <= 0) {
        $errors[] = 'Usage limit must be greater than 0';
    }

    if (empty($start_date)) {
        $errors[] = 'Start date is required';
    }

    if (empty($expiry_date)) {
        $errors[] = 'Expiry date is required';
    }

    if (strtotime($expiry_date) < strtotime($start_date)) {
        $errors[] = 'Expiry date cannot be before start date';
    }

    // إذا لم يكن هناك أخطاء، حدث قاعدة البيانات
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE Coupons SET 
                                   code = ?, description = ?, discount_type = ?, discount_value = ?, 
                                   min_order = ?, max_discount = ?, usage_limit = ?, start_date = ?, 
                                   expiry_date = ?, status = ? 
                                   WHERE coupon_id = ?");
            $stmt->execute([
                    $code, $description, $discount_type, $discount_value,
                    $min_order, $max_discount, $usage_limit, $start_date, $expiry_date, $status,
                    $coupon_id
            ]);

            $success = true;

            // تحديث المتغيرات المعروضة بالقيم الجديدة
            $coupon['code'] = $code;
            $coupon['description'] = $description;
            $coupon['discount_type'] = $discount_type;
            $coupon['discount_value'] = $discount_value;
            $coupon['min_order'] = $min_order;
            $coupon['max_discount'] = $max_discount;
            $coupon['usage_limit'] = $usage_limit;
            $coupon['start_date'] = $start_date;
            $coupon['expiry_date'] = $expiry_date;
            $coupon['status'] = $status;

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
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
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; align-items: flex-start; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); box-sizing: border-box; }

        .form-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .form-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .form-header p {
            color: var(--secondary-text);
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        .form-group label i {
            color: var(--primary);
            margin-right: 8px;
        }
        .required {
            color: #ff6b6b;
            margin-left: 4px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 16px;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        select.form-control {
            cursor: pointer;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
            border: 1px solid #F44336;
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-submit {
            flex: 1;
            padding: 14px 25px;
            background: linear-gradient(135deg, var(--primary), var(--pink));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(248, 187, 208, 0.5);
        }
        .btn-cancel {
            flex: 1;
            padding: 14px 25px;
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 2px solid rgba(128,128,128,0.2);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        .help-text {
            font-size: 11px;
            color: var(--secondary-text);
            margin-top: 5px;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; }
            .form-row { grid-template-columns: 1fr; gap: 15px; }
            .form-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="form-container">
            <div class="form-header">
                <h1>
                    <i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i>
                    Edit Coupon
                </h1>
                <p>Update coupon information</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Coupon updated successfully! <a href="coupon-details.php?id=<?= $coupon_id ?>" style="color: #4CAF50;">View coupon</a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="edit-coupon.php?id=<?= $coupon_id ?>" method="POST" id="couponForm">
                <!-- Coupon Code -->
                <div class="form-group">
                    <label><i class="fa-solid fa-ticket"></i> Coupon Code <span class="required">*</span></label>
                    <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($code) ?>" placeholder="e.g., SUMMER20" required>
                    <div class="help-text">Use uppercase letters and numbers only</div>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label><i class="fa-solid fa-align-left"></i> Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control" placeholder="Describe the coupon offer" required><?= htmlspecialchars($description) ?></textarea>
                </div>

                <!-- Discount Type and Value -->
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fa-solid fa-percent"></i> Discount Type <span class="required">*</span></label>
                        <select name="discount_type" id="discountType" class="form-control" required>
                            <option value="percentage" <?= $discount_type == 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                            <option value="fixed" <?= $discount_type == 'fixed' ? 'selected' : '' ?>>Fixed Amount ($)</option>
                            <option value="shipping" <?= $discount_type == 'shipping' ? 'selected' : '' ?>>Free Shipping</option>
                        </select>
                    </div>
                    <div class="form-group" id="discountValueGroup">
                        <label><i class="fa-solid fa-dollar-sign"></i> Discount Value <span class="required">*</span></label>
                        <input type="number" step="0.01" name="discount_value" id="discountValue" class="form-control" value="<?= $discount_value ?>" placeholder="0.00">
                        <div class="help-text" id="discountHelpText">Enter discount amount</div>
                    </div>
                </div>

                <!-- Max Discount (for percentage) -->
                <div class="form-group" id="maxDiscountGroup" style="display: <?= $discount_type == 'percentage' ? 'block' : 'none' ?>;">
                    <label><i class="fa-solid fa-chart-line"></i> Maximum Discount Amount</label>
                    <input type="number" step="0.01" name="max_discount" class="form-control" value="<?= $max_discount ?>" placeholder="0.00">
                    <div class="help-text">Leave 0 for no limit</div>
                </div>

                <!-- Min Order and Usage Limit -->
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fa-solid fa-cart-shopping"></i> Minimum Order Amount</label>
                        <input type="number" step="0.01" name="min_order" class="form-control" value="<?= $min_order ?>" placeholder="0.00">
                        <div class="help-text">Minimum order amount to apply coupon</div>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-chart-simple"></i> Usage Limit <span class="required">*</span></label>
                        <input type="number" name="usage_limit" class="form-control" value="<?= $usage_limit ?>" placeholder="100" required>
                        <div class="help-text">Maximum number of times coupon can be used</div>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fa-regular fa-calendar"></i> Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-regular fa-calendar"></i> Expiry Date <span class="required">*</span></label>
                        <input type="date" name="expiry_date" class="form-control" value="<?= $expiry_date ?>" required>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label><i class="fa-solid fa-circle-info"></i> Status <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="scheduled" <?= $status == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="expired" <?= $status == 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>

                <!-- Applicable Products -->
                <div class="form-group">
                    <label><i class="fa-solid fa-box"></i> Applicable Products</label>
                    <select name="applicable_products" id="applicableProducts" class="form-control">
                        <option value="all" <?= $applicable_products == 'all' ? 'selected' : '' ?>>All Products</option>
                        <option value="specific" <?= $applicable_products == 'specific' ? 'selected' : '' ?>>Specific Products</option>
                    </select>
                </div>

                <!-- Applicable Categories -->
                <div class="form-group" id="categoriesGroup" style="display: <?= $applicable_products == 'specific' ? 'block' : 'none' ?>;">
                    <label><i class="fa-solid fa-tags"></i> Applicable Categories</label>
                    <select name="applicable_categories" class="form-control">
                        <option value="all" <?= $applicable_categories == 'all' ? 'selected' : '' ?>>All Categories</option>
                    </select>
                    <div class="help-text">Select which categories this coupon applies to</div>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Update Coupon
                    </button>
                    <a href="coupon-details.php?id=<?= $coupon_id ?>" class="btn-cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Toggle discount value visibility based on type
    const discountType = document.getElementById('discountType');
    const discountValueGroup = document.getElementById('discountValueGroup');
    const maxDiscountGroup = document.getElementById('maxDiscountGroup');
    const discountValue = document.getElementById('discountValue');
    const discountHelpText = document.getElementById('discountHelpText');

    function updateDiscountFields() {
        if (discountType.value === 'shipping') {
            discountValueGroup.style.display = 'none';
            maxDiscountGroup.style.display = 'none';
            discountValue.removeAttribute('required');
        } else {
            discountValueGroup.style.display = 'block';
            discountValue.setAttribute('required', 'required');

            if (discountType.value === 'percentage') {
                maxDiscountGroup.style.display = 'block';
                discountHelpText.innerHTML = 'Enter percentage (e.g., 10 for 10% off)';
            } else {
                maxDiscountGroup.style.display = 'none';
                discountHelpText.innerHTML = 'Enter fixed amount (e.g., 20 for $20 off)';
            }
        }
    }

    discountType.addEventListener('change', updateDiscountFields);
    updateDiscountFields();

    // Toggle categories group
    const applicableProducts = document.getElementById('applicableProducts');
    const categoriesGroup = document.getElementById('categoriesGroup');

    applicableProducts.addEventListener('change', function() {
        if (this.value === 'specific') {
            categoriesGroup.style.display = 'block';
        } else {
            categoriesGroup.style.display = 'none';
        }
    });

    // Form validation
    document.getElementById('couponForm').addEventListener('submit', function(e) {
        const code = document.querySelector('input[name="code"]').value.trim();
        const startDate = document.querySelector('input[name="start_date"]').value;
        const expiryDate = document.querySelector('input[name="expiry_date"]').value;

        if (!code) {
            e.preventDefault();
            alert('Please enter coupon code');
            return false;
        }

        if (startDate && expiryDate && new Date(expiryDate) < new Date(startDate)) {
            e.preventDefault();
            alert('Expiry date cannot be before start date');
            return false;
        }
    });

    // Focus effects
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
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