<?php
// gallery.php
session_start();

// دالة لقراءة الصور من مجلد images/gallery/
function getGalleryImages() {
    $galleryDir = 'images/gallery/';
    $images = [];

    // التأكد من وجود المجلد
    if (!is_dir($galleryDir)) {
        mkdir($galleryDir, 0777, true);
        return [];
    }

    // قراءة جميع الملفات من المجلد
    $files = scandir($galleryDir);

    foreach ($files as $file) {
        // استبعاد المجلدات الحالية والأصلية
        if ($file != '.' && $file != '..') {
            // التحقق من امتداد الصورة
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = [
                        'filename' => $file,
                        'path' => $galleryDir . $file,
                        'name' => pathinfo($file, PATHINFO_FILENAME),
                        'size' => filesize($galleryDir . $file),
                        'date' => date('Y-m-d H:i:s', filemtime($galleryDir . $file))
                ];
            }
        }
    }

    // ترتيب حسب التاريخ (الأحدث أولاً)
    usort($images, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    return $images;
}

// معالجة رفع صورة جديدة
$upload_error = '';
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $galleryDir = 'images/gallery/';

    // التأكد من وجود المجلد
    if (!is_dir($galleryDir)) {
        mkdir($galleryDir, 0777, true);
    }

    if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['gallery_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $upload_error = 'Only JPG, PNG, GIF, and WEBP images are allowed';
        } elseif ($file['size'] > $max_size) {
            $upload_error = 'Image size must be less than 5MB';
        } else {
            // إنشاء اسم فريد للصورة
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = time() . '_' . uniqid() . '.' . $extension;
            $upload_path = $galleryDir . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $upload_success = true;
            } else {
                $upload_error = 'Failed to upload image';
            }
        }
    } else {
        $upload_error = 'Please select an image to upload';
    }
}

// معالجة حذف صورة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $filename = isset($_POST['filename']) ? trim($_POST['filename']) : '';
    $filepath = 'images/gallery/' . $filename;

    if (!empty($filename) && file_exists($filepath)) {
        if (unlink($filepath)) {
            $delete_success = true;
        } else {
            $delete_error = 'Failed to delete image';
        }
    }
}

// جلب الصور
$galleryImages = getGalleryImages();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$totalImages = count($galleryImages);
$totalPages = ceil($totalImages / $perPage);
$offset = ($page - 1) * $perPage;
$paginatedImages = array_slice($galleryImages, $offset, $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery · Teddy Shop</title>
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
        .stat-mini-card:nth-child(1) { border-left: 4px solid #ff9aa2; }
        .stat-mini-card:nth-child(2) { border-left: 4px solid #a0c4ff; }
        .stat-mini-card:nth-child(3) { border-left: 4px solid #bdb2ff; }
        .stat-mini-card:nth-child(4) { border-left: 4px solid #ffd6a5; }
        .stat-mini-info h4 { font-size: 14px; color: var(--secondary-text); margin-bottom: 5px; }
        .stat-mini-info .value { font-size: 28px; font-weight: 700; color: var(--text-color); }
        .stat-mini-icon { font-size: 40px; opacity: 0.7; transition: all 0.3s ease; }
        .stat-mini-card:hover .stat-mini-icon {
            transform: scale(1.2) rotate(5deg);
            color: var(--pink);
        }

        /* Upload Section */
        .upload-section {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 25px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease;
        }
        .upload-area {
            border: 2px dashed var(--secondary-text);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--bg-color);
        }
        .upload-area:hover {
            border-color: var(--pink);
            background: rgba(248, 187, 208, 0.05);
        }
        .upload-area i {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 15px;
        }
        .upload-area p {
            color: var(--secondary-text);
            margin: 0;
        }
        .upload-area .small-text {
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
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--pink);
        }

        /* Gallery Grid */
        .gallery-container {
            margin: 25px 0;
            animation: fadeInUp 0.8s ease;
        }
        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .gallery-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .gallery-title i {
            color: var(--pink);
            font-size: 24px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        /* Gallery Card */
        .gallery-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 2px 10px var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }
        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px var(--shadow);
        }
        .card-image {
            position: relative;
            width: 100%;
            height: 180px;
            overflow: hidden;
            background: var(--bg-color);
        }
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .gallery-card:hover .card-image img {
            transform: scale(1.05);
        }
        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            gap: 15px;
        }
        .gallery-card:hover .card-overlay {
            opacity: 1;
        }
        .card-overlay button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }
        .card-overlay button:hover {
            transform: scale(1.1);
        }
        .card-overlay .view-btn:hover { background: var(--primary); color: white; }
        .card-overlay .delete-btn:hover { background: #ff6b6b; color: white; }
        .card-info {
            padding: 12px;
            text-align: center;
        }
        .card-name {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-date {
            font-size: 10px;
            color: var(--secondary-text);
            margin-top: 4px;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 20px;
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

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
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
        }
        .page-item.active {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; }
            .stats-mini { grid-template-columns: repeat(2, 1fr); }
            .gallery-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="main-header" style="animation: fadeInDown 0.6s ease;">
            <div>
                <h1 style="margin-bottom: 5px;">Gallery</h1>
                <p style="color: var(--secondary-text);">Manage your gallery images</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" style="background: var(--lavender); color: #000;" onclick="openUploadModal()">
                    <i class="fa-solid fa-upload"></i> Upload Image
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Total Images</h4>
                    <div class="value"><?= $totalImages ?></div>
                </div>
                <i class="fa-solid fa-images stat-mini-icon"></i>
            </div>

            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Last Upload</h4>
                    <div class="value"><?= !empty($galleryImages) ? date('M d', strtotime($galleryImages[0]['date'])) : '-' ?></div>
                </div>
                <i class="fa-solid fa-clock stat-mini-icon" style="color: #FF9800;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Storage Used</h4>
                    <div class="value"><?= round(array_sum(array_column($galleryImages, 'size')) / 1024 / 1024, 1) ?> MB</div>
                </div>
                <i class="fa-solid fa-hard-drive stat-mini-icon" style="color: var(--primary);"></i>
            </div>
        </div>

        <!-- Upload Modal -->
        <div id="uploadModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; align-items: center; justify-content: center;">
            <div style="background: var(--card-bg); border-radius: 30px; padding: 30px; max-width: 500px; width: 90%; animation: fadeInUp 0.3s ease;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 24px;"><i class="fa-solid fa-cloud-upload-alt"></i> Upload Image</h2>
                    <button onclick="closeUploadModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                </div>

                <form action="gallery.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="action" value="upload">
                    <div class="upload-area" id="modalUploadArea" style="margin-bottom: 20px;">
                        <i class="fa-solid fa-cloud-upload-alt"></i>
                        <p>Click to select image</p>
                        <p class="small-text">PNG, JPG, GIF, WEBP (Max 5MB)</p>
                        <input type="file" name="gallery_image" id="modalImageInput" accept="image/*" style="display: none;" required>
                    </div>
                    <div id="modalImagePreview" style="text-align: center; margin-bottom: 20px;"></div>
                    <div style="display: flex; gap: 15px;">
                        <button type="submit" class="custom-btn btn-save" style="flex: 1; justify-content: center; background: var(--primary); color: white; padding: 12px; border-radius: 50px; border: none; cursor: pointer;">Upload</button>
                        <button type="button" onclick="closeUploadModal()" class="custom-btn" style="flex: 1; background: var(--bg-color); color: var(--secondary-text); border: 1px solid rgba(128,128,128,0.2); border-radius: 50px; cursor: pointer;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($upload_success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                <span>Image uploaded successfully!</span>
            </div>
        <?php elseif (!empty($upload_error)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <span><?= $upload_error ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($delete_success)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                <span>Image deleted successfully!</span>
            </div>
        <?php elseif (isset($delete_error)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <span><?= $delete_error ?></span>
            </div>
        <?php endif; ?>

        <!-- Gallery Grid -->
        <div class="gallery-container">
            <div class="gallery-header">
                <div class="gallery-title">
                    <i class="fa-solid fa-images"></i>
                    Image Gallery
                </div>
                <div class="gallery-count"><?= $totalImages ?> images</div>
            </div>

            <?php if ($totalImages > 0): ?>
                <div class="gallery-grid">
                    <?php foreach($paginatedImages as $image): ?>
                        <div class="gallery-card" data-filename="<?= $image['filename'] ?>">
                            <div class="card-image">
                                <img src="<?= $image['path'] ?>" alt="<?= $image['name'] ?>">
                                <div class="card-overlay">
                                    <button class="view-btn" onclick="viewImage('<?= $image['path'] ?>', '<?= addslashes($image['name']) ?>')" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="delete-btn" onclick="deleteImage('<?= $image['filename'] ?>')" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-info">
                                <div class="card-name" title="<?= $image['name'] ?>"><?= htmlspecialchars(substr($image['name'], 0, 25)) ?></div>
                                <div class="card-date"><?= date('M d, Y', strtotime($image['date'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-section">
                        <div class="pagination-info">
                            Showing <strong><?= $offset + 1 ?>-<?= min($offset + $perPage, $totalImages) ?></strong> of <strong><?= $totalImages ?></strong> images
                        </div>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="page-item"><i class="fa-solid fa-chevron-left"></i></a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>
                            <?php endif; ?>

                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?= $i ?>" class="page-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>" class="page-item"><i class="fa-solid fa-chevron-right"></i></a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div style="text-align: center; padding: 60px; background: var(--card-bg); border-radius: 30px;">
                    <i class="fa-regular fa-images" style="font-size: 64px; color: var(--secondary-text); opacity: 0.5; margin-bottom: 20px; display: block;"></i>
                    <h3 style="color: var(--text-color);">No images yet</h3>
                    <p style="color: var(--secondary-text);">Click the "Upload Image" button to add your first image</p>
                    <button class="custom-btn btn-add" onclick="openUploadModal()" style="margin-top: 20px; background: var(--lavender); color: #000; padding: 12px 28px; border-radius: 50px; border: none; cursor: pointer;">
                        <i class="fa-solid fa-upload"></i> Upload Image
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
    // Upload Modal Functions
    function openUploadModal() {
        document.getElementById('uploadModal').style.display = 'flex';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
        document.getElementById('modalImagePreview').innerHTML = '';
        document.getElementById('modalImageInput').value = '';
    }

    // Image Preview in Modal
    const modalUploadArea = document.getElementById('modalUploadArea');
    const modalImageInput = document.getElementById('modalImageInput');
    const modalImagePreview = document.getElementById('modalImagePreview');

    if (modalUploadArea) {
        modalUploadArea.addEventListener('click', function() {
            modalImageInput.click();
        });

        modalImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    modalImagePreview.innerHTML = `<img src="${event.target.result}" style="max-width: 150px; max-height: 150px; border-radius: 12px; border: 2px solid var(--pink);">`;
                    modalUploadArea.style.borderColor = 'var(--pink)';
                };
                reader.readAsDataURL(file);
            } else {
                modalImagePreview.innerHTML = '';
                modalUploadArea.style.borderColor = '';
            }
        });

        // Drag and drop
        modalUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            modalUploadArea.style.borderColor = 'var(--primary)';
            modalUploadArea.style.background = 'rgba(248, 187, 208, 0.1)';
        });

        modalUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            modalUploadArea.style.borderColor = '';
            modalUploadArea.style.background = '';
        });

        modalUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            modalUploadArea.style.borderColor = '';
            modalUploadArea.style.background = '';

            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                modalImageInput.files = e.dataTransfer.files;
                const reader = new FileReader();
                reader.onload = function(event) {
                    modalImagePreview.innerHTML = `<img src="${event.target.result}" style="max-width: 150px; max-height: 150px; border-radius: 12px;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // View Image
    function viewImage(path, name) {
        const modalHTML = `
            <div id="viewModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10001; display: flex; align-items: center; justify-content: center;">
                <div style="max-width: 90%; max-height: 90%; position: relative;">
                    <img src="${path}" alt="${name}" style="max-width: 100%; max-height: 90vh; border-radius: 10px;">
                    <button onclick="closeViewModal()" style="position: absolute; top: -40px; right: 0; background: none; border: none; color: white; font-size: 30px; cursor: pointer;">&times;</button>
                    <div style="position: absolute; bottom: -40px; left: 0; right: 0; text-align: center; color: white;">${name}</div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    function closeViewModal() {
        const modal = document.getElementById('viewModal');
        if (modal) modal.remove();
    }

    // Delete Image
    function deleteImage(filename) {
        // إنشاء الـ overlay
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

        // إنشاء الـ popup
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

        // أيقونة التحذير
        const icon = document.createElement('div');
        icon.textContent = '⚠️';
        icon.style.fontSize = '40px';
        icon.style.marginBottom = '12px';

        // نص التأكيد
        const message = document.createElement('p');
        message.textContent = 'Are you sure you want to delete this image?';
        message.style.fontSize = '15px';
        message.style.margin = '0 0 22px 0';
        message.style.lineHeight = '1.6';

        // حاوية الأزرار
        const btnContainer = document.createElement('div');
        btnContainer.style.display = 'flex';
        btnContainer.style.gap = '12px';
        btnContainer.style.justifyContent = 'center';

        // زر الإلغاء
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.style.flex = '1';
        cancelBtn.style.padding = '11px 20px';
        cancelBtn.style.borderRadius = '14px';
        cancelBtn.style.border = '1px solid var(--pink, #F8BBD0)';
        cancelBtn.style.backgroundColor = 'transparent';
        cancelBtn.style.color = 'var(--text-color, #333)';
        cancelBtn.style.fontFamily = "'Poppins', sans-serif";
        cancelBtn.style.fontSize = '14px';
        cancelBtn.style.fontWeight = '500';
        cancelBtn.style.cursor = 'pointer';
        cancelBtn.style.transition = 'all 0.2s ease';
        cancelBtn.addEventListener('mouseover', function() {
            cancelBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            cancelBtn.style.color = '#fff';
        });
        cancelBtn.addEventListener('mouseout', function() {
            cancelBtn.style.backgroundColor = 'transparent';
            cancelBtn.style.color = 'var(--text-color, #333)';
        });

        // زر الحذف
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Delete';
        deleteBtn.style.flex = '1';
        deleteBtn.style.padding = '11px 20px';
        deleteBtn.style.borderRadius = '14px';
        deleteBtn.style.border = 'none';
        deleteBtn.style.backgroundColor = '#e74c3c';
        deleteBtn.style.color = '#fff';
        deleteBtn.style.fontFamily = "'Poppins', sans-serif";
        deleteBtn.style.fontSize = '14px';
        deleteBtn.style.fontWeight = '600';
        deleteBtn.style.cursor = 'pointer';
        deleteBtn.style.transition = 'all 0.2s ease';
        deleteBtn.addEventListener('mouseover', function() {
            deleteBtn.style.backgroundColor = '#c0392b';
            deleteBtn.style.transform = 'scale(1.03)';
        });
        deleteBtn.addEventListener('mouseout', function() {
            deleteBtn.style.backgroundColor = '#e74c3c';
            deleteBtn.style.transform = 'scale(1)';
        });

        // تجميع العناصر
        btnContainer.appendChild(cancelBtn);
        btnContainer.appendChild(deleteBtn);
        popup.appendChild(icon);
        popup.appendChild(message);
        popup.appendChild(btnContainer);
        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // تأثير الظهور
        requestAnimationFrame(function() {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        });

        // دالة الإغلاق مع تأثير الاختفاء
        function closePopup() {
            popup.style.transform = 'scale(0.9)';
            overlay.style.opacity = '0';
            setTimeout(function() {
                overlay.remove();
            }, 300);
        }

        // زر الإلغاء → إغلاق فقط
        cancelBtn.addEventListener('click', closePopup);

        // الضغط على الـ overlay → إغلاق
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closePopup();
        });

        // زر الحذف → إغلاق ثم إرسال الفورم
        deleteBtn.addEventListener('click', function() {
            closePopup();
            setTimeout(function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'gallery.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const filenameInput = document.createElement('input');
                filenameInput.type = 'hidden';
                filenameInput.name = 'filename';
                filenameInput.value = filename;

                form.appendChild(actionInput);
                form.appendChild(filenameInput);
                document.body.appendChild(form);
                form.submit();
            }, 300);
        });

        // إغلاق بـ Escape
        document.addEventListener('keydown', function handleEsc(e) {
            if (e.key === 'Escape') {
                closePopup();
                document.removeEventListener('keydown', handleEsc);
            }
        });
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('uploadModal');
        if (modal && event.target === modal) {
            closeUploadModal();
        }
    });

    // Auto hide alerts after 3 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        });
    }, 3000);
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