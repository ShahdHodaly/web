<?php
// edit-item.php
session_start();
require_once 'db.php';

$pdo = getDB();

// الحصول على ID العنصر من URL
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$itemType = isset($_GET['type']) ? $_GET['type'] : '';

// إذا لم يتم تحديد النوع، نحاول اكتشافه من قاعدة البيانات
if (empty($itemType)) {
    // البحث في جدول clothing_items أولاً
    $stmt = $pdo->prepare("SELECT 'clothing' as source, type FROM clothing_items WHERE item_id = ?");
    $stmt->execute([$itemId]);
    $result = $stmt->fetch();

    if ($result) {
        $itemType = $result['type'];
        $source = 'clothing';
    } else {
        // البحث في جدول teddy_colors
        $stmt = $pdo->prepare("SELECT 'color' as source FROM teddy_colors WHERE color_id = ?");
        $stmt->execute([$itemId]);
        $result = $stmt->fetch();
        if ($result) {
            $itemType = 'color';
            $source = 'color';
        }
    }
}

// جلب العنصر من قاعدة البيانات
$item = null;
$source = null;

if ($itemType === 'color') {
    $stmt = $pdo->prepare("SELECT color_id as id, name, 'color' as type, NULL as price, image FROM teddy_colors WHERE color_id = ?");
    $stmt->execute([$itemId]);
    $itemData = $stmt->fetch();
    if ($itemData) {
        $item = [
                'id' => $itemData['id'],
                'name' => $itemData['name'],
                'type' => 'color',
                'category' => 'color',
                'price' => 0,
                'image' => $itemData['image']
        ];
        $source = 'color';
    }
} else {
    $stmt = $pdo->prepare("SELECT item_id as id, name, type, price, image FROM clothing_items WHERE item_id = ?");
    $stmt->execute([$itemId]);
    $itemData = $stmt->fetch();
    if ($itemData) {
        // تحويل نوع الـ type إلى القيمة الصحيحة (outfit, shoes, accessory)
        $typeValue = $itemData['type'];
        if (!in_array($typeValue, ['outfit', 'shoes', 'accessory'])) {
            $typeValue = 'outfit';
        }

        $item = [
                'id' => $itemData['id'],
                'name' => $itemData['name'],
                'type' => $typeValue,
                'category' => $typeValue,
                'price' => (float)$itemData['price'],
                'image' => $itemData['image']
        ];
        $source = 'clothing';
    }
}

// إذا لم يتم العثور على العنصر، الرجوع إلى الصفحة السابقة
if (!$item) {
    header('Location: custom-teddies.php');
    exit;
}

// متغيرات النموذج مع بيانات العنصر الحالي
$item_name = $item['name'];
$item_type = $item['type'];
$item_price = $item['price'];
$errors = [];
$success = false;

// معالجة إرسال النموذج (تحديث السعر فقط)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_price = trim($_POST['item_price'] ?? '');

    // التحقق من صحة السعر
    if (empty($item_price) && $item_type !== 'color') {
        $errors[] = 'Price is required';
    } elseif (!empty($item_price) && (!is_numeric($item_price) || $item_price < 0)) {
        $errors[] = 'Price must be a positive number';
    }

    // إذا لم يكن هناك أخطاء، قم بتحديث السعر في قاعدة البيانات
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if ($item_type === 'color') {
                // الألوان ليس لها سعر، يتم تخطي التحديث أو إظهار رسالة
                $success = true;
            } else {
                // تحديث السعر فقط في جدول clothing_items
                $stmt = $pdo->prepare("
                    UPDATE clothing_items 
                    SET price = ? 
                    WHERE item_id = ?
                ");
                $stmt->execute([$item_price, $itemId]);
                $pdo->commit();
                $success = true;

                // تحديث المتغير للعرض
                $item_price = $item_price;
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
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
    <title>Edit Item · Teddy Shop</title>
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

        .form-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 600px;
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

        .info-card {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .info-card .item-name {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        .info-card .item-type {
            display: inline-block;
            background: var(--pink);
            padding: 5px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            color: #000;
        }
        .info-card .item-id {
            margin-top: 15px;
            font-size: 13px;
            color: var(--secondary-text);
        }

        .form-group {
            margin-bottom: 25px;
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
            padding: 14px 18px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 16px;
            color: var(--text-color);
            font-size: 15px;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }

        .form-control:disabled {
            background: var(--card-bg);
            color: var(--secondary-text);
            opacity: 0.7;
            cursor: not-allowed;
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .form-container { margin: 0 15px; }
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
                    <i class="fa-solid fa-dollar-sign" style="color: var(--primary);"></i>
                    Edit Item Price
                </h1>
                <p>Update the price of the selected item</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Price updated successfully! <a href="custom-teddies.php" style="color: #4CAF50;">Back to Custom Teddies</a></span>
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

            <form action="edit-item.php?id=<?= $itemId ?>&type=<?= $item_type ?>" method="POST" id="itemForm">

                <!-- Item Information Card (Read Only) -->
                <div class="info-card">
                    <div class="item-name"><?= htmlspecialchars($item_name) ?></div>
                    <div class="item-type">
                        <i class="fa-solid fa-<?= $item_type == 'outfit' ? 'shirt' : ($item_type == 'shoes' ? 'shoe-prints' : ($item_type == 'accessory' ? 'gem' : 'palette')) ?>"></i>
                        <?= ucfirst($item_type) ?>
                    </div>
                    <div class="item-id">
                        <i class="fa-regular fa-id-card"></i> Item ID: #<?= $itemId ?>
                    </div>
                </div>

                <!-- Price (Editable) -->
                <div class="form-group" id="priceGroup">
                    <label><i class="fa-solid fa-dollar-sign"></i> Price <span class="required">*</span></label>
                    <input type="number" step="0.01" name="item_price" class="form-control" value="<?= htmlspecialchars($item_price) ?>" placeholder="0.00" required>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Update Price
                    </button>
                    <a href="custom-teddies.php" class="btn-cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Form validation
    const itemForm = document.getElementById('itemForm');
    if (itemForm) {
        itemForm.addEventListener('submit', function(e) {
            const price = document.querySelector('input[name="item_price"]')?.value;

            if (!price || price <= 0) {
                e.preventDefault();
                alert('Please enter a valid price');
                return false;
            }
        });
    }

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