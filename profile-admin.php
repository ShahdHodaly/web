<?php
// profile.php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: auth.php");
    exit;
}

$user = [
    'id' => 1,
    'name' => 'Sarah Admin',
    'email' => 'admin@teddyshop.com',
    'role' => 'Admin',
    'avatar' => 'https://ui-avatars.com/api/?name=Sarah+Admin&background=F8BBD0&color=000&size=150',
    'joined' => '2024-01-15',
    'last_login' => '2024-03-28 14:30:00',
    'phone' => '+1 234 567 890',
    'address' => '123 Teddy Street, Toy City, USA',
    'bio' => 'Passionate about creating the perfect teddy bears for children around the world. Teddy Shop founder since 2020.',
    'total_orders_managed' => 3,
    'products_added' => 5,
    'customers_helped' => 20,
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

        $user['name'] = $name;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['address'] = $address;
        $user['bio'] = $bio;

        // تحديث الصورة إذا تم رفع صورة جديدة
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            $max_size = 2 * 1024 * 1024;

            if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $extension;
                $upload_path = 'uploads/avatars/' . $new_filename;

                if (!is_dir('uploads/avatars')) {
                    mkdir('uploads/avatars', 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $user['avatar'] = $upload_path;
                }
            }
        }

        $success_message = 'Profile updated successfully!';

        // تحديث جلسة المستخدم
        $_SESSION['user_name'] = $user['name'];
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
                    <i class="fa-solid fa-crown"></i> Administrator
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= $success_message ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div class="avatar-container">
                        <img src="<?= $user['avatar'] ?>" alt="<?= $user['name'] ?>" id="avatarPreview">
                        <label for="avatarUpload" class="change-avatar-btn" title="Change Avatar">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                    </div>
                    <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
                    <div class="profile-role">
                        <i class="fa-solid fa-shield-hal"></i> <?= $user['role'] ?>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?= $user['total_orders_managed'] ?></div>
                            <div class="stat-label">Orders Managed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $user['products_added'] ?></div>
                            <div class="stat-label">Products Added</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $user['customers_helped'] ?></div>
                            <div class="stat-label">Customers Helped</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?= $user['response_time'] ?></div>
                            <div class="stat-label">Avg Response</div>
                        </div>
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
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-phone"></i> Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa-solid fa-calendar"></i> Member Since</label>
                                    <input type="text" class="form-control" value="<?= date('F d, Y', strtotime($user['joined'])) ?>" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3><i class="fa-solid fa-location-dot"></i> Address & Bio</h3>
                            <div class="form-group">
                                <label><i class="fa-solid fa-location-dot"></i> Address</label>
                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fa-solid fa-pen"></i> Bio / About</label>
                                <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>
                            </div>
                        </div>

                        <div class="info-section">
                            <h3><i class="fa-solid fa-clock"></i> Account Activity</h3>
                            <div class="info-grid">
                                <div class="info-field">
                                    <label>Last Login</label>
                                    <div class="value"><i class="fa-regular fa-clock"></i> <?= date('F d, Y \a\t h:i A', strtotime($user['last_login'])) ?></div>
                                </div>
                                <div class="info-field">
                                    <label>Account Status</label>
                                    <div class="value"><i class="fa-solid fa-circle" style="color: #4CAF50; font-size: 12px;"></i> Active</div>
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

    avatarUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                avatarPreview.src = event.target.result;

                // Auto submit form to save avatar
                const formData = new FormData();
                formData.append('avatar', file);
                formData.append('name', document.querySelector('input[name="name"]').value);
                formData.append('email', document.querySelector('input[name="email"]').value);
                formData.append('phone', document.querySelector('input[name="phone"]').value);
                formData.append('address', document.querySelector('input[name="address"]').value);
                formData.append('bio', document.querySelector('textarea[name="bio"]').value);

                fetch('profile.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    location.reload();
                });
            };
            reader.readAsDataURL(file);
        }
    });

    // Focus effects
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Form validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="name"]').value.trim();
        const email = document.querySelector('input[name="email"]').value.trim();

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