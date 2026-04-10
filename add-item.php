<?php
// add-item.php
session_start();

// تضمين المصفوفات الحالية
require_once 'items-array.php';

// متغيرات النموذج
$item_name = '';
$item_category = '';
$item_type = '';
$item_price = '';
$item_image = '';
$errors = [];
$success = false;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name'] ?? '');
    $item_category = trim($_POST['item_category'] ?? '');
    $item_type = trim($_POST['item_type'] ?? '');
    $item_price = trim($_POST['item_price'] ?? '');

    // التحقق من صحة البيانات
    if (empty($item_name)) {
        $errors[] = 'Item name is required';
    }

    if (empty($item_category)) {
        $errors[] = 'Category is required';
    }

    if (empty($item_type)) {
        $errors[] = 'Item type is required';
    }

    if (empty($item_price) && $item_type !== 'color') {
        $errors[] = 'Price is required';
    } elseif (!empty($item_price) && (!is_numeric($item_price) || $item_price < 0)) {
        $errors[] = 'Price must be a positive number';
    }

    // معالجة رفع الصورة
    $image_path = '';
    if ($item_type !== 'color') {
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['item_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $max_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = 'Only JPG, PNG, and WEBP images are allowed';
            } elseif ($file['size'] > $max_size) {
                $errors[] = 'Image size must be less than 2MB';
            } else {
                // إنشاء اسم فريد للصورة
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = strtolower(str_replace(' ', '_', $item_name)) . '_' . time() . '.' . $extension;
                $upload_path = 'images/' . $new_filename;

                // التأكد من وجود المجلد
                if (!is_dir('images')) {
                    mkdir('images', 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $image_path = $upload_path;
                } else {
                    $errors[] = 'Failed to upload image';
                }
            }
        } else {
            $errors[] = 'Image is required for this item type';
        }
    }

    // إذا لم يكن هناك أخطاء
    if (empty($errors)) {
        // هنا يتم حفظ العنصر في قاعدة البيانات أو ملف JSON
        // للتجربة، نعرض رسالة نجاح

        $success = true;

        // إعادة تعيين النموذج بعد النجاح
        $item_name = '';
        $item_category = '';
        $item_type = '';
        $item_price = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item · Teddy Shop</title>
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
            max-width: 700px;
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
        select.form-control {
            cursor: pointer;
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
            max-width: 120px;
            max-height: 120px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--pink);
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
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i>
                    Add New Item
                </h1>
                <p>Add a new outfit, shoes, accessory, or color for the custom teddy</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Item added successfully! <a href="custom-teddies.php" style="color: #4CAF50;">Back to Custom Teddy</a></span>
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

            <form action="add-item.php" method="POST" enctype="multipart/form-data" id="itemForm">
                <!-- Item Type -->
                <div class="form-group">
                    <label><i class="fa-solid fa-tag"></i> Item Type <span class="required">*</span></label>
                    <select name="item_type" id="itemType" class="form-control" required>
                        <option value="">Select item type</option>
                        <option value="outfit" <?= $item_type == 'outfit' ? 'selected' : '' ?>>Outfit (Clothing)</option>
                        <option value="shoes" <?= $item_type == 'shoes' ? 'selected' : '' ?>>Shoes</option>
                        <option value="accessory" <?= $item_type == 'accessory' ? 'selected' : '' ?>>Accessory</option>
                        <option value="color" <?= $item_type == 'color' ? 'selected' : '' ?>>Color</option>
                    </select>
                </div>

                <!-- Category (for outfits) -->
                <div class="form-group" id="categoryGroup" style="display: none;">
                    <label><i class="fa-solid fa-list"></i> Category <span class="required">*</span></label>
                    <select name="item_category" id="itemCategory" class="form-control">
                        <option value="">Select category</option>
                        <option value="outfit" <?= $item_category == 'outfit' ? 'selected' : '' ?>>Outfit</option>
                        <option value="shoes" <?= $item_category == 'shoes' ? 'selected' : '' ?>>Shoes</option>
                        <option value="accessory" <?= $item_category == 'accessory' ? 'selected' : '' ?>>Accessory</option>
                    </select>
                </div>

                <!-- Item Name -->
                <div class="form-group">
                    <label><i class="fa-solid fa-font"></i> Item Name <span class="required">*</span></label>
                    <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($item_name) ?>" placeholder="e.g., Blue Dress, Winter Boots..." required>
                </div>

                <!-- Price -->
                <div class="form-group" id="priceGroup">
                    <label><i class="fa-solid fa-dollar-sign"></i> Price <span class="required" id="priceRequired">*</span></label>
                    <input type="number" step="0.01" name="item_price" class="form-control" value="<?= htmlspecialchars($item_price) ?>" placeholder="0.00">
                </div>

                <!-- Image Upload -->
                <div class="form-group" id="imageGroup">
                    <label><i class="fa-solid fa-image"></i> Item Image <span class="required" id="imageRequired">*</span></label>
                    <div class="image-upload-area" id="imageUploadArea">
                        <i class="fa-solid fa-cloud-upload-alt"></i>
                        <p>Click to upload image</p>
                        <p class="small-text">PNG, JPG, WEBP (Max 2MB)</p>
                        <input type="file" name="item_image" id="itemImage" accept="image/*" style="display: none;">
                    </div>
                    <div class="image-preview" id="imagePreview"></div>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Add Item
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
    // Dynamic form based on item type
    const itemTypeSelect = document.getElementById('itemType');
    const categoryGroup = document.getElementById('categoryGroup');
    const priceGroup = document.getElementById('priceGroup');
    const imageGroup = document.getElementById('imageGroup');
    const priceRequired = document.getElementById('priceRequired');
    const imageRequired = document.getElementById('imageRequired');
    const categorySelect = document.getElementById('itemCategory');

    function updateFormFields() {
        const selectedType = itemTypeSelect.value;

        if (selectedType === 'color') {
            // For colors: hide price and image, show category
            priceGroup.style.display = 'none';
            imageGroup.style.display = 'none';
            categoryGroup.style.display = 'block';
            priceRequired.style.display = 'none';
            imageRequired.style.display = 'none';

            // Update category options for colors
            categorySelect.innerHTML = '<option value="">Select category</option><option value="color">Color</option>';
        } else {
            // For outfit, shoes, accessory: show price and image, hide category
            priceGroup.style.display = 'block';
            imageGroup.style.display = 'block';
            categoryGroup.style.display = 'none';
            priceRequired.style.display = 'inline';
            imageRequired.style.display = 'inline';

            // Update category based on type
            if (selectedType === 'outfit') {
                categorySelect.innerHTML = '<option value="outfit">Outfit</option>';
            } else if (selectedType === 'shoes') {
                categorySelect.innerHTML = '<option value="shoes">Shoes</option>';
            } else if (selectedType === 'accessory') {
                categorySelect.innerHTML = '<option value="accessory">Accessory</option>';
            }
        }
    }

    itemTypeSelect.addEventListener('change', updateFormFields);
    updateFormFields();

    // Image Upload Preview
    const uploadArea = document.getElementById('imageUploadArea');
    const imageInput = document.getElementById('itemImage');
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
    document.getElementById('itemForm').addEventListener('submit', function(e) {
        const type = document.getElementById('itemType').value;
        const name = document.querySelector('input[name="item_name"]').value.trim();
        const price = document.querySelector('input[name="item_price"]').value;
        const image = document.getElementById('itemImage').files[0];

        if (!type) {
            e.preventDefault();
            alert('Please select item type');
            return false;
        }
        if (!name) {
            e.preventDefault();
            alert('Please enter item name');
            return false;
        }
        if (type !== 'color') {
            if (!price || price <= 0) {
                e.preventDefault();
                alert('Please enter a valid price');
                return false;
            }
            if (!image) {
                e.preventDefault();
                alert('Please upload an image');
                return false;
            }
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