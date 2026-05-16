<?php
// profile-admin.php
session_start();
require_once 'db.php';

$pdo = getDB();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم من قاعدة البيانات
$stmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        u.role::text as role,
        u.status::text as status,
        u.phone,
        u.avatar,
        u.address,
        u.bio,
        u.created_at as joined,
        u.last_login
    FROM users u
    WHERE u.user_id = ?
");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود المستخدم
if (!$userData) {
    session_destroy();
    header("Location: auth.php");
    exit;
}

// جلب إحصائيات المستخدم (الطلبات التي أدارها، المنتجات التي أضافها، إلخ)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders");
$stmt->execute();
$totalOrders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
$stmt->execute();
$totalProducts = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'Customer'");
$stmt->execute();
$totalCustomers = $stmt->fetchColumn();

// تنسيق بيانات المستخدم
$profileUser = [
        'id' => $userData['user_id'],
        'name' => $userData['name'],
        'email' => $userData['email'],
        'role' => $userData['role'],
        'status' => $userData['status'],
        'avatar' => $userData['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($userData['name']) . '&background=F8BBD0&color=000&size=150',
        'joined' => $userData['joined'],
        'last_login' => $userData['last_login'],
        'phone' => $userData['phone'] ?? '',
        'address' => $userData['address'] ?? '',
        'bio' => $userData['bio'] ?? '',
    // إحصائيات إضافية
        'total_orders_managed' => $totalOrders,
        'products_added' => $totalProducts,
        'customers_helped' => $totalCustomers,
        'response_time' => '2.5 hours'
];

// معالجة تحديث الملف الشخصي
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if (empty($name)) {
        $error_message = 'Name is required';
    } elseif (empty($email)) {
        $error_message = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } else {
        try {
            $pdo->beginTransaction();

            // تحديث بيانات المستخدم مع إضافة address و bio
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, 
                    email = ?, 
                    phone = ?,
                    address = ?,
                    bio = ?
                WHERE user_id = ?
            ");

            $stmt->execute([
                    $name,
                    $email,
                    $phone,
                    $address,
                    $bio,
                    $user_id
            ]);

            // معالجة رفع الصورة الجديدة
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                $max_size = 2 * 1024 * 1024; // 2MB

                if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
                    $upload_path = 'uploads/avatars/' . $new_filename;

                    if (!is_dir('uploads/avatars')) {
                        mkdir('uploads/avatars', 0777, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        // حذف الصورة القديمة إذا كانت موجودة
                        if ($profileUser['avatar'] && strpos($profileUser['avatar'], 'uploads/avatars/') === 0 && file_exists($profileUser['avatar'])) {
                            unlink($profileUser['avatar']);
                        }

                        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
                        $stmt->execute([$upload_path, $user_id]);
                        $profileUser['avatar'] = $upload_path;
                    }
                }
            }

            $pdo->commit();

            // تحديث المتغيرات
            $profileUser['name'] = $name;
            $profileUser['email'] = $email;
            $profileUser['phone'] = $phone;
            $profileUser['address'] = $address;
            $profileUser['bio'] = $bio;

            // تحديث الجلسة
            $_SESSION['user_name'] = $name;

            $success_message = 'Profile updated successfully!';

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile · Teddy Shop</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; align-items: flex-start; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); box-sizing: border-box; }

        /* Profile Container */
        .profile-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
        }

        /* Profile Header */
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .profile-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .profile-header h1 i {
            color: var(--pink);
        }
        .profile-badge {
            background: var(--lavender);
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            color: #000;
        }

        /* Profile Layout */
        .profile-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 35px;
        }

        /* Profile Sidebar */
        .profile-sidebar {
            text-align: center;
        }
        .avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        .avatar-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--pink);
            box-shadow: 0 5px 20px var(--shadow);
        }
        .change-avatar-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid white;
        }
        .change-avatar-btn:hover {
            background: var(--pink);
            transform: scale(1.1);
        }
        .profile-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-color);
            margin: 15px 0 5px;
        }
        .profile-role {
            display: inline-block;
            background: var(--pink);
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            color: #000;
            margin-bottom: 15px;
        }
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(128,128,128,0.1);
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background: var(--bg-color);
            border-radius: 15px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }
        .stat-label {
            font-size: 11px;
            color: var(--secondary-text);
        }

        /* Profile Info */
        .profile-info {
            background: var(--bg-color);
            border-radius: 25px;
            padding: 25px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-section h3 i {
            color: var(--pink);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .info-field {
            margin-bottom: 5px;
        }
        .info-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 5px;
        }
        .info-field .value {
            font-size: 15px;
            color: var(--text-color);
            font-weight: 500;
            word-break: break-word;
        }
        .info-field .value i {
            color: var(--primary);
            margin-right: 5px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        .form-group label i {
            color: var(--primary);
            margin-right: 6px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--card-bg);
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

        .form-control:disabled {
            background: var(--card-bg);
            color: var(--secondary-text);
            opacity: 0.7;
            cursor: not-allowed;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Buttons */
        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        .btn-primary-custom {
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--primary), var(--pink));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(248, 187, 208, 0.4);
        }
        .btn-secondary {
            padding: 12px 25px;
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-secondary:hover {
            border-color: var(--pink);
            color: var(--pink);
        }
        .btn-change-password{
            padding: 12px 25px;
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-change-password:hover {
            border-color: red;
            color: red;
        }

        /* Alert */
        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: 1px solid #F44336;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 900px) {
            .profile-layout { grid-template-columns: 1fr; gap: 30px; }
            .info-grid { grid-template-columns: 1fr; }
            .form-buttons { flex-direction: column; }
            .btn-primary-custom, .btn-secondary { width: 100%; justify-content: center; }
        }
        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="profile-container">
            <div class="profile-header">
                <h1><i class="fa-solid fa-user-circle"></i> My Profile</h1>
                <div class="profile-badge">
                    <i class="fa-solid fa-crown"></i> <?= htmlspecialchars($profileUser['role'] ?? 'Admin') ?>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div class="avatar-container">
                        <img src="<?= htmlspecialchars($profileUser['avatar'] ?? 'https://ui-avatars.com/api/?name=User&background=F8BBD0&color=000&size=150') ?>"
                             alt="<?= htmlspecialchars($profileUser['name'] ?? 'User') ?>"
                             id="avatarPreview">
                        <label for="avatarUpload" class="change-avatar-btn" title="Change Avatar">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="avatarUpload" name="avatar" accept="image/*" style="display: none;" form="profileForm">
                    </div>
                    <div class="profile-name"><?= htmlspecialchars($profileUser['name'] ?? 'User') ?></div>
                    <div class="profile-role">
                        <i class="fa-solid fa-shield-hal"></i> <?= htmlspecialchars($profileUser['role'] ?? 'Customer') ?>
                    </div>
                </div>

                <!-- Main Info -->
                <div class="profile-info">
                    <form action="profile-admin.php" method="POST" enctype="multipart/form-data" id="profileForm">
                        <div class="info-section">
                            <h3><i class="fa-solid fa-user-pen"></i> Personal Information</h3>
                            <div class="info-grid">
                                <div class="form-group">
                                    <label><i class="fa-solid fa-user"></i> Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($profileUser['name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profileUser['email'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-phone"></i> Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($profileUser['phone'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-calendar"></i> Member Since</label>
                                    <input type="text" class="form-control" value="<?= !empty($profileUser['joined']) ? date('F d, Y', strtotime($profileUser['joined'])) : 'Just joined' ?>" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3><i class="fa-solid fa-location-dot"></i> Address & Bio</h3>
                            <div class="form-group">
                                <label><i class="fa-solid fa-location-dot"></i> Address</label>
                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($profileUser['address'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fa-solid fa-pen"></i> Bio / About</label>
                                <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($profileUser['bio'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3><i class="fa-solid fa-clock"></i> Account Activity</h3>
                            <div class="info-grid">
                                <div class="info-field">
                                    <label>Last Login</label>
                                    <div class="value">
                                        <i class="fa-regular fa-clock"></i>
                                        <?php
                                        if (!empty($profileUser['last_login'])) {
                                            echo date('F d, Y \a\t h:i A', strtotime($profileUser['last_login']));
                                        } else {
                                            echo 'Never';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="info-field">
                                    <label>Account Status</label>
                                    <div class="value">
                                        <i class="fa-solid fa-circle" style="color: #4CAF50; font-size: 12px;"></i>
                                        <?= ucfirst($profileUser['status'] ?? 'Active') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn-primary-custom">
                                <i class="fa-solid fa-save"></i> Save Changes
                            </button>
                            <a href="change-password-admin.php" class="btn-change-password">
                                Change Password
                            </a>
                            <a href="dashboard.php" class="btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Avatar Upload Preview
    const avatarUpload = document.getElementById('avatarUpload');
    const avatarPreview = document.getElementById('avatarPreview');
    const profileForm = document.getElementById('profileForm');

    if (avatarUpload) {
        avatarUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    avatarPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);

                // Auto submit form to save avatar
                profileForm.submit();
            }
        });
    }

    // Focus effects
    document.querySelectorAll('.form-control:not([disabled])').forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Form validation
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]')?.value.trim();
            const email = document.querySelector('input[name="email"]')?.value.trim();

            if (!name) {
                e.preventDefault();
                alert('Please enter your name');
                return false;
            }
            if (!email) {
                e.preventDefault();
                alert('Please enter your email');
                return false;
            }

            const emailPattern = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
            if (!emailPattern.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
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