<?php
// edit-user.php
session_start();
require_once 'db.php';

$pdo = getDB();

// الحصول على ID المستخدم من الرابط
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
        u.last_login,
        u.created_at
    FROM users u
    WHERE u.user_id = ?
");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود المستخدم
if (!$userData) {
    $_SESSION['error'] = 'User not found';
    header("Location: users.php");
    exit;
}

// تنسيق بيانات المستخدم
$user = [
        'id' => $userData['user_id'],
        'name' => $userData['name'],
        'email' => $userData['email'],
        'role' => $userData['role'],
        'status' => $userData['status'],
        'phone' => $userData['phone'],
        'avatar' => $userData['avatar'] ?: 'images/teddy4.png',
        'last_login' => $userData['last_login'],
        'joined' => $userData['created_at']
];

$pageTitle = "Edit " . $user['name'] . " | Teddy Shop";

// متغيرات النموذج
$name = $user['name'];
$email = $user['email'];
$role = $user['role'];
$status = $user['status'];
$errors = [];
$success = false;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // الاسم والبريد الإلكتروني لا يتم أخذهما من POST (لأنهما disabled)
    // فقط الدور والحالة يتم تحديثهما
    $role = trim($_POST['role'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // التحقق من صحة البيانات
    if (empty($role)) {
        $errors[] = 'User role is required';
    }

    // التحقق من أن الدور مسموح به
    $allowed_roles = ['Customer', 'Admin'];
    if (!in_array($role, $allowed_roles)) {
        $errors[] = 'Invalid user role';
    }

    // التحقق من أن الحالة مسموح بها
    $allowed_status = ['active', 'inactive'];
    if (!in_array($status, $allowed_status)) {
        $errors[] = 'Invalid account status';
    }

    // إذا لم يكن هناك أخطاء، قم بتحديث المستخدم في قاعدة البيانات
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // تحديث بيانات المستخدم (الاسم والبريد الإلكتروني لا يتم تحديثهما)
            $stmt = $pdo->prepare("
                UPDATE users 
                SET role = ?::user_role, 
                    status = ?::user_status
                WHERE user_id = ?
            ");

            $stmt->execute([
                    $role,
                    $status,
                    $user_id
            ]);

            $pdo->commit();
            $success = true;

            // تحديث Session إذا كان المستخدم يعدل نفسه
            if ($user_id == ($_SESSION['user_id'] ?? 0)) {
                $_SESSION['role'] = $role;
            }

            // تحديث المتغيرات المعروضة
            $user['role'] = $role;
            $user['status'] = $status;

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

        .user-badge {
            background: var(--lavender);
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .info-note {
            background: rgba(248, 187, 208, 0.15);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 13px;
            color: var(--secondary-text);
            border-left: 4px solid var(--pink);
        }
        .info-note i {
            color: var(--primary);
            font-size: 16px;
            margin-right: 8px;
        }
        .info-note strong {
            color: var(--text-color);
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

        .form-control:disabled {
            background: var(--card-bg);
            color: var(--secondary-text);
            opacity: 0.7;
            cursor: not-allowed;
            border-color: rgba(128,128,128,0.1);
        }

        .form-control:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }
        select.form-control {
            cursor: pointer;
        }
        select.form-control:disabled {
            cursor: not-allowed;
        }

        .status-toggle {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .status-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 40px;
            background: var(--bg-color);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        .status-option:hover {
            background: rgba(248, 187, 208, 0.2);
            border-color: var(--pink);
        }
        .status-option input {
            width: 18px;
            height: 18px;
            cursor: pointer;
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

        .avatar-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        .avatar-preview img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid var(--pink);
            object-fit: cover;
        }

        .field-note {
            font-size: 12px;
            color: var(--secondary-text);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .field-note i {
            font-size: 11px;
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
            .status-toggle { flex-direction: column; align-items: stretch; }
            .status-option { justify-content: center; }
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
                    Edit User
                </h1>
                <p>Update user role and account status</p>
                <div class="user-badge">
                    <i class="fa-regular fa-id-card"></i> User ID: #<?= $user_id ?>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>User updated successfully! <a href="user-details.php?id=<?= $user_id ?>" style="color: #4CAF50;">View user</a></span>
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

            <div class="info-note">
                <i class="fa-solid fa-shield-haltered"></i>
                <strong>Account Information</strong><br>
                User name and email address cannot be changed after account creation. Only role and status can be modified.
            </div>

            <div class="avatar-preview">
                <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar Preview" onerror="this.src='images/teddy4.png'">
                <div class="field-note" style="justify-content: center; margin-top: 8px;">
                    <i class="fa-solid fa-camera"></i> Profile picture
                </div>
            </div>

            <form action="edit-user.php?id=<?= $user_id ?>" method="POST" id="userForm">
                <!-- User Name (Disabled - Cannot be changed) -->
                <div class="form-group">
                    <label><i class="fa-solid fa-user"></i> Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
                    <div class="field-note">
                        <i class="fa-solid fa-lock"></i> Name cannot be changed
                    </div>
                </div>

                <!-- Email (Disabled - Cannot be changed) -->
                <div class="form-group">
                    <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled>
                    <div class="field-note">
                        <i class="fa-solid fa-lock"></i> Email cannot be changed
                    </div>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label><i class="fa-solid fa-briefcase"></i> User Role <span class="required">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="Customer" <?= $role == 'Customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="Admin" <?= $role == 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <div class="field-note">
                        <i class="fa-solid fa-shield"></i> Admins have full access to the dashboard
                    </div>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label><i class="fa-solid fa-circle-info"></i> Account Status <span class="required">*</span></label>
                    <div class="status-toggle">
                        <label class="status-option">
                            <input type="radio" name="status" value="active" <?= $status == 'active' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-circle" style="color: #4CAF50; font-size: 12px;"></i>
                            <span>Active</span>
                            <span style="font-size: 11px; color: var(--secondary-text);">(User can log in)</span>
                        </label>
                        <label class="status-option">
                            <input type="radio" name="status" value="inactive" <?= $status == 'inactive' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-circle" style="color: #F44336; font-size: 12px;"></i>
                            <span>Inactive</span>
                            <span style="font-size: 11px; color: var(--secondary-text);">(User cannot log in)</span>
                        </label>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Update User
                    </button>
                    <a href="user-details.php?id=<?= $user_id ?>" class="btn-cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Form validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const role = document.querySelector('select[name="role"]').value;
        const statusChecked = document.querySelector('input[name="status"]:checked');

        if (!role) {
            e.preventDefault();
            alert('Please select a user role');
            return false;
        }

        if (!statusChecked) {
            e.preventDefault();
            alert('Please select account status');
            return false;
        }
    });

    // Focus effects for select
    const selectInput = document.querySelector('select[name="role"]');
    if (selectInput) {
        selectInput.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        selectInput.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    }

    // Status option hover effect
    document.querySelectorAll('.status-option').forEach(option => {
        option.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        option.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
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
                applyTheme(this.checked);
                localStorage.setItem('theme', this.checked ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>