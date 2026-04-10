<?php
// add-user.php
session_start();

// تضمين مصفوفة المستخدمين
require_once 'users-array.php';

// متغيرات النموذج
$name = '';
$email = '';
$role = '';
$status = 'active';
$errors = [];
$success = false;

// حساب آخر ID للمستخدم الجديد
$new_user_id = count($users) + 1;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // التحقق من صحة البيانات
    if (empty($name)) {
        $errors[] = 'User name is required';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (empty($role)) {
        $errors[] = 'User role is required';
    }

    // التحقق من عدم تكرار البريد الإلكتروني
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            $errors[] = 'Email address already exists';
            break;
        }
    }

    // إذا لم يكن هناك أخطاء، احفظ المستخدم
    if (empty($errors)) {
        // إنشاء صورة avatar باستخدام UI Avatars
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=F8BBD0&color=000&size=40';

        // إنشاء المستخدم الجديد
        $new_user = [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'status' => $status,
            'joined' => date('Y-m-d'),
            'orders' => 0,
            'spent' => 0,
            'avatar' => $avatar,
            'last_login' => date('Y-m-d H:i:s')
        ];

        // في التطبيق الحقيقي، ستضيفه إلى قاعدة البيانات
        // للتجربة، نعرض رسالة نجاح فقط

        $success = true;

        // إعادة تعيين النموذج
        $name = '';
        $email = '';
        $role = '';
        $status = 'active';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User · Teddy Shop</title>
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

        .status-toggle {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .status-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
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
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--pink);
            object-fit: cover;
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
                    <i class="fa-solid fa-user-plus" style="color: var(--primary);"></i>
                    Add New User
                </h1>
                <p>Create a new user account</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>User added successfully! <a href="users.php" style="color: #4CAF50;">View all users</a></span>
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

            <div class="avatar-preview" id="avatarPreview">
                <img src="https://ui-avatars.com/api/?name=User&background=F8BBD0&color=000&size=80" alt="Avatar Preview">
            </div>

            <form action="add-user.php" method="POST" id="userForm">
                <!-- User Name -->
                <div class="form-group">
                    <label><i class="fa-solid fa-user"></i> Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" placeholder="Enter full name" id="userName" required>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label><i class="fa-solid fa-envelope"></i> Email Address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" placeholder="user@example.com" required>
                </div>

                <!-- Role -->
                <div class="form-group">
                    <label><i class="fa-solid fa-briefcase"></i> User Role <span class="required">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="" disabled <?= empty($role) ? 'selected' : '' ?>>Select a role</option>
                        <option value="Customer" <?= $role == 'Customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="Moderator" <?= $role == 'Moderator' ? 'selected' : '' ?>>Moderator</option>
                        <option value="Admin" <?= $role == 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label><i class="fa-solid fa-circle-info"></i> Account Status</label>
                    <div class="status-toggle">
                        <label class="status-option">
                            <input type="radio" name="status" value="active" <?= $status == 'active' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-circle" style="color: #4CAF50; font-size: 12px;"></i> Active
                        </label>
                        <label class="status-option">
                            <input type="radio" name="status" value="inactive" <?= $status == 'inactive' ? 'checked' : '' ?>>
                            <i class="fa-solid fa-circle" style="color: #F44336; font-size: 12px;"></i> Inactive
                        </label>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Create User
                    </button>
                    <a href="users.php" class="btn-cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // تحديث معاينة الصورة عند تغيير الاسم
    const userNameInput = document.getElementById('userName');
    const avatarPreview = document.getElementById('avatarPreview').querySelector('img');

    userNameInput.addEventListener('input', function() {
        const name = this.value.trim();
        if (name) {
            const encodedName = encodeURIComponent(name);
            avatarPreview.src = `https://ui-avatars.com/api/?name=${encodedName}&background=F8BBD0&color=000&size=80`;
        } else {
            avatarPreview.src = `https://ui-avatars.com/api/?name=User&background=F8BBD0&color=000&size=80`;
        }
    });

    // Form validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="name"]').value.trim();
        const email = document.querySelector('input[name="email"]').value.trim();
        const role = document.querySelector('select[name="role"]').value;

        if (!name) {
            e.preventDefault();
            alert('Please enter user name');
            return false;
        }
        if (!email) {
            e.preventDefault();
            alert('Please enter email address');
            return false;
        }
        if (!role) {
            e.preventDefault();
            alert('Please select a role');
            return false;
        }
    });

    // Search input effect
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
                applyTheme(this.checked);
                localStorage.setItem('theme', this.checked ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>