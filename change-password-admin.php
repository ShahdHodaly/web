<?php
session_start(); 

// التحقق من تسجيل الدخول
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: auth.php");
    exit;
}

// مصفوفة المستخدم (بيانات وهمية)
$user = [
        'id' => 1,
        'name' => 'Sarah Admin',
        'email' => 'admin@teddyshop.com',
        'role' => 'Admin',
];

$success_message = '';
$error_message = '';

// معالجة تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // كلمة المرور الحالية (تجريبية - في الحقيقة تستعلم من قاعدة البيانات)
    $stored_hash = password_hash("Admin123", PASSWORD_DEFAULT);
    $is_current_valid = password_verify($current_password, $stored_hash);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!$is_current_valid) {
        $error_message = "Current password is incorrect.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $error_message = "New password must contain at least one uppercase letter and one number.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirmation do not match.";
    } else {
        $success_message = "Password changed successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password · Teddy Shop</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        :root {
            --bg-color: #fef9f5;
            --card-bg: #ffffff;
            --text-color: #2d2a2a;
            --secondary-text: #6c6a6a;
            --primary: #e91e63;
            --pink: #f8bbd0;
            --lavender: #e8e0f5;
            --shadow: rgba(0, 0, 0, 0.05);
        }
        body.dark-mode {
            --bg-color: #1a1a1a;
            --card-bg: #2c2c2c;
            --text-color: #f0f0f0;
            --secondary-text: #b0b0b0;
            --primary: #ff4081;
            --pink: #c2185b;
            --lavender: #4a3a6e;
            --shadow: rgba(255, 255, 255, 0.05);
        }
        body {
            background-color: var(--bg-color);
            font-family: 'Poppins', sans-serif;
            transition: background 0.3s, color 0.2s;
            margin: 0;
        }
        .admin-wrapper {
            display: flex;
            align-items: flex-start;
            min-height: 100vh;
        }
        .admin-main {
            flex: 1;
            width: calc(100% - 280px);
            padding: 30px 35px;
            background-color: var(--bg-color);
            box-sizing: border-box;
        }
        .profile-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
        }
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
        body.dark-mode .profile-badge {
            color: #fff;
            background: #5a4a7a;
        }
        .password-change-wrapper {
            max-width: 720px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 28px;
            padding: 20px 0;
        }
        .security-tip {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-left: 6px solid var(--primary);
        }
        .security-tip i {
            font-size: 38px;
            color: var(--primary);
        }
        .security-tip p {
            margin: 0;
            font-size: 14px;
            color: var(--text-color);
        }
        .form-group {
            margin-bottom: 25px;
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .form-control:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .strength-meter {
            height: 4px;
            background: #e0e0e0;
            border-radius: 4px;
            flex: 1;
            overflow: hidden;
        }
        .strength-fill {
            width: 0%;
            height: 100%;
            background: #f44336;
            transition: width 0.2s;
        }
        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn-primary-custom {
            padding: 12px 28px;
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
        .alert-info {
            background: rgba(33, 150, 243, 0.1);
            color: #2196F3;
            border: 1px solid #2196F3;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; padding: 20px; }
        }
        @media (max-width: 550px) {
            .form-buttons { flex-direction: column; }
            .btn-primary-custom, .btn-secondary { width: 100%; justify-content: center; }
        }
        hr {
            border-color: rgba(128,128,128,0.15);
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="profile-container">
            <div class="profile-header">
                <h1><i class="fa-solid fa-key"></i> Change Password</h1>
                <div class="profile-badge">
                    <i class="fa-solid fa-shield-hal"></i> Security Center
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

            <div class="password-change-wrapper">
                <div class="security-tip">
                    <i class="fa-solid fa-shield-hal"></i>
                    <div>
                        <strong style="color: var(--primary);">Password guidelines:</strong>
                        <p>Use 8+ characters, include uppercase letters, numbers, and avoid common phrases. Keep your account secure.</p>
                    </div>
                </div>

                <form action="change-password-admin.php" method="POST" id="changePasswordForm">
                    <div class="form-group">
                        <label><i class="fa-solid fa-lock"></i> Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter your current password" required autocomplete="current-password">
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-key"></i> New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Min. 8 characters, 1 uppercase & 1 number" required autocomplete="new-password">
                        <div class="password-strength">
                            <span style="font-size: 12px;">Password strength:</span>
                            <div class="strength-meter">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span id="strengthText" style="font-size: 12px; min-width: 70px;">Weak</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-check-circle"></i> Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter new password" required autocomplete="off">
                        <div id="matchIndicator" style="font-size: 12px; margin-top: 6px;"></div>
                    </div>

                    <hr style="margin: 20px 0;">

                    <div class="form-buttons">
                        <button type="submit" class="btn-primary-custom">
                            <i class="fa-solid fa-rotate-right"></i> Update Password
                        </button>
                        <a href="profile-admin.php" class="btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Back to Profile
                        </a>
                        <a href="dashboard.php" class="btn-secondary">
                            <i class="fa-solid fa-gauge-high"></i> Dashboard
                        </a>
                    </div>
                </form>

                <div class="alert alert-info" style="margin-top: 25px; background: var(--bg-color);">
                    <i class="fa-solid fa-circle-info"></i>
                    <small>After changing your password, you'll need to use it next time you log in. Keep it safe.</small>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    (function() {
        const newPassword = document.getElementById('new_password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const confirmInput = document.getElementById('confirm_password');
        const matchIndicator = document.getElementById('matchIndicator');

        function evaluateStrength(password) {
            let score = 0;
            if (!password) return { score: 0, label: 'Weak' };
            if (password.length >= 8) score += 1;
            if (password.length >= 12) score += 1;
            if (/[A-Z]/.test(password)) score += 1;
            if (/[0-9]/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 1;
            let width = 0;
            let label = 'Weak';
            if (score >= 4) { width = 100; label = 'Strong'; strengthFill.style.background = '#4caf50'; }
            else if (score >= 2.5) { width = 66; label = 'Medium'; strengthFill.style.background = '#ff9800'; }
            else { width = 33; label = 'Weak'; strengthFill.style.background = '#f44336'; }
            return { width, label };
        }

        function updateStrength() {
            const pwd = newPassword.value;
            const { width, label } = evaluateStrength(pwd);
            strengthFill.style.width = width + '%';
            strengthText.innerText = label;
        }

        function checkMatch() {
            const newVal = newPassword.value;
            const confirmVal = confirmInput.value;
            if (confirmVal === '') {
                matchIndicator.innerHTML = '';
                return;
            }
            if (newVal === confirmVal && newVal.length > 0) {
                matchIndicator.innerHTML = '<i class="fa-solid fa-check-circle"></i> Passwords match';
                matchIndicator.style.color = '#4caf50';
            } else {
                matchIndicator.innerHTML = '<i class="fa-solid fa-times-circle"></i> Passwords do not match';
                matchIndicator.style.color = '#f44336';
            }
        }

        newPassword.addEventListener('input', () => {
            updateStrength();
            checkMatch();
        });
        confirmInput.addEventListener('input', checkMatch);
        updateStrength();
    })();

    const form = document.getElementById('changePasswordForm');
    form.addEventListener('submit', function(e) {
        const current = document.getElementById('current_password').value.trim();
        const newPwd = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (!current) {
            e.preventDefault();
            alert('Please enter your current password.');
            return false;
        }
        if (newPwd.length < 8) {
            e.preventDefault();
            alert('New password must be at least 8 characters long.');
            return false;
        }
        if (!/[A-Z]/.test(newPwd) || !/[0-9]/.test(newPwd)) {
            e.preventDefault();
            alert('New password must contain at least one uppercase letter and one number.');
            return false;
        }
        if (newPwd !== confirm) {
            e.preventDefault();
            alert('New password and confirmation do not match.');
            return false;
        }
        if (current === newPwd) {
            e.preventDefault();
            alert('New password cannot be the same as current password.');
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

<script>
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() { this.style.transform = 'translateY(-2px)'; });
        input.addEventListener('blur', function() { this.style.transform = 'translateY(0)'; });
    });
</script>
</body>
</html>