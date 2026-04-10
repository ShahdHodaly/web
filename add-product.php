<?php
// add-product.php
session_start();

// تضمين مصفوفة المنتجات (لجلب آخر ID)
require_once 'products.php';

// متغيرات للتخزين المؤقت
$product_name = '';
$product_price = '';
$product_category = '';
$product_description = '';
$product_image = '';
$product_stock = '';
$errors = [];
$success = false;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // جلب البيانات وتنظيفها
    $product_name = trim($_POST['product_name'] ?? '');
    $product_price = trim($_POST['product_price'] ?? '');
    $product_category = trim($_POST['product_category'] ?? '');
    $product_description = trim($_POST['product_description'] ?? '');
    $product_stock = trim($_POST['product_stock'] ?? '');

    // التحقق من صحة البيانات
    if (empty($product_name)) {
        $errors[] = 'Product name is required';
    }

    if (empty($product_price)) {
        $errors[] = 'Product price is required';
    } elseif (!is_numeric($product_price) || $product_price <= 0) {
        $errors[] = 'Product price must be a positive number';
    }

    if (empty($product_category)) {
        $errors[] = 'Product category is required';
    }

    if (!empty($product_stock) && (!is_numeric($product_stock) || $product_stock < 0)) {
        $errors[] = 'Stock must be a positive number';
    }

    // معالجة رفع الصورة
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Image size must be less than 2MB';
        } else {
            // إنشاء اسم فريد للصورة
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = strtolower(str_replace(' ', '_', $product_name)) . '_' . time() . '.' . $extension;
            $upload_path = 'uploads/products/' . $new_filename;

            // التأكد من وجود المجلد
            if (!is_dir('uploads/products')) {
                mkdir('uploads/products', 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $product_image = $upload_path;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    } else {
        // إذا لم يتم رفع صورة، استخدم صورة افتراضية
        $product_image = 'images/default-product.png';
    }

    // إذا لم يكن هناك أخطاء، احفظ المنتج
    if (empty($errors)) {
        // حساب آخر ID
        $new_id = count($products) + 1;

        // إنشاء المنتج الجديد
        $new_product = [
            'name' => $product_name,
            'price' => (float)$product_price,
            'category' => $product_category,
            'description' => $product_description,
            'image' => $product_image,
            'sales_count' => 0,
            'avg_rating' => 0,
            'stock' => $product_stock ? (int)$product_stock : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];


        $success = true;

        // إعادة تعيين النموذج
        $product_name = $product_price = $product_category = $product_description = $product_stock = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product · Teddy Shop</title>
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

        /* Form Container */
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

        /* Form Elements */
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
        .form-control:hover {
            border-color: var(--pink);
            transform: translateY(-2px);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Image Upload */
        .image-upload-area {
            border: 2px dashed var(--secondary-text);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--bg-color);
        }
        .image-upload-area:hover {
            border-color: var(--pink);
            background: rgba(248, 187, 208, 0.05);
        }
        .image-upload-area i {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 15px;
        }
        .image-upload-area p {
            color: var(--secondary-text);
            margin: 0;
        }
        .image-upload-area .small-text {
            font-size: 12px;
            margin-top: 8px;
        }
        .image-preview {
            margin-top: 15px;
            display: flex;
            justify-content: center;
        }
        .image-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 16px;
            object-fit: cover;
            border: 2px solid var(--pink);
        }

        /* Buttons */
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
            font-size: 16px;
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
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
            transform: translateY(-2px);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.3s ease;
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
        .alert i {
            font-size: 20px;
        }

        /* Category Select */
        .category-select {
            cursor: pointer;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .form-container { padding: 25px; margin: 0 15px; }
            .form-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="form-container">
            <div class="form-header">
                <h1>
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i>
                    Add New Product
                </h1>
                <p>Create a new product for your store</p>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Product added successfully! <a href="product-admin.php" style="color: #4CAF50; text-decoration: underline;">View all products</a></span>
                </div>
            <?php endif; ?>

            <!-- Error Messages -->
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

            <!-- Product Form -->
            <form action="add-product.php" method="POST" enctype="multipart/form-data" id="productForm">
                <!-- Product Name -->
                <div class="form-group">
                    <label><i class="fa-solid fa-tag"></i> Product Name <span class="required">*</span></label>
                    <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product_name) ?>" placeholder="Enter product name" required>
                </div>

                <!-- Product Price -->
                <div class="form-group">
                    <label><i class="fa-solid fa-dollar-sign"></i> Price <span class="required">*</span></label>
                    <input type="number" step="0.01" name="product_price" class="form-control" value="<?= htmlspecialchars($product_price) ?>" placeholder="0.00" required>
                </div>

                <!-- Product Category -->
                <div class="form-group">
                    <label><i class="fa-solid fa-list"></i> Category <span class="required">*</span></label>
                    <select name="product_category" class="form-control category-select" required>
                        <option value="" disabled <?= empty($product_category) ? 'selected' : '' ?>>Select a category</option>
                        <option value="Teddy Bear" <?= $product_category == 'Teddy Bear' ? 'selected' : '' ?>>Teddy Bear</option>
                        <option value="Dolls & Barbie" <?= $product_category == 'Dolls & Barbie' ? 'selected' : '' ?>>Dolls & Barbie</option>
                        <option value="Building Toys" <?= $product_category == 'Building Toys' ? 'selected' : '' ?>>Building Toys</option>
                        <option value="Cars & Vehicles" <?= $product_category == 'Cars & Vehicles' ? 'selected' : '' ?>>Cars & Vehicles</option>
                        <option value="Group Games" <?= $product_category == 'Group Games' ? 'selected' : '' ?>>Group Games</option>
                        <option value="Educational Toys" <?= $product_category == 'Educational Toys' ? 'selected' : '' ?>>Educational Toys</option>
                        <option value="Puzzles" <?= $product_category == 'Puzzles' ? 'selected' : '' ?>>Puzzles</option>
                    </select>
                </div>

                <!-- Product Stock -->
                <div class="form-group">
                    <label><i class="fa-solid fa-boxes"></i> Stock Quantity</label>
                    <input type="number" name="product_stock" class="form-control" value="<?= htmlspecialchars($product_stock) ?>" placeholder="0">
                    <small style="color: var(--secondary-text);">Leave empty if unlimited</small>
                </div>

                <!-- Product Description -->
                <div class="form-group">
                    <label><i class="fa-solid fa-align-left"></i> Description</label>
                    <textarea name="product_description" class="form-control" placeholder="Enter product description"><?= htmlspecialchars($product_description) ?></textarea>
                </div>

                <!-- Product Image -->
                <div class="form-group">
                    <label><i class="fa-solid fa-image"></i> Product Image</label>
                    <div class="image-upload-area" id="imageUploadArea">
                        <i class="fa-solid fa-cloud-upload-alt"></i>
                        <p>Click to upload product image</p>
                        <p class="small-text">PNG, JPG, WEBP (Max 2MB)</p>
                        <input type="file" name="product_image" id="productImage" accept="image/*" style="display: none;">
                    </div>
                    <div class="image-preview" id="imagePreview"></div>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Save Product
                    </button>
                    <a href="product-admin.php" class="btn-cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Image Upload Preview
    const uploadArea = document.getElementById('imageUploadArea');
    const imageInput = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');

    uploadArea.addEventListener('click', function() {
        imageInput.click();
    });

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
                uploadArea.style.borderColor = 'var(--pink)';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.innerHTML = '';
            uploadArea.style.borderColor = '';
        }
    });

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--primary)';
        uploadArea.style.background = 'rgba(248, 187, 208, 0.1)';
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = '';
        uploadArea.style.background = '';
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = '';
        uploadArea.style.background = '';

        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            imageInput.files = e.dataTransfer.files;
            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(file);
        }
    });

    // Form validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="product_name"]').value.trim();
        const price = document.querySelector('input[name="product_price"]').value;
        const category = document.querySelector('select[name="product_category"]').value;

        if (!name) {
            e.preventDefault();
            alert('Please enter product name');
            return false;
        }
        if (!price || price <= 0) {
            e.preventDefault();
            alert('Please enter a valid price');
            return false;
        }
        if (!category) {
            e.preventDefault();
            alert('Please select a category');
            return false;
        }
    });

    // Search bar effect
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        searchInput.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
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