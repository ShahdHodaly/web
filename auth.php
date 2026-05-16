<?php
// auth.php
session_start();

// معالجة تسجيل الخروج
if (isset($_GET['logout']) || isset($_POST['logout'])) {
    // تدمير جميع متغيرات الجلسة
    $_SESSION = array();

    // تدمير الجلسة
    session_destroy();

    // حذف كوكي الجلسة
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // التوجيه إلى صفحة تسجيل الدخول
    header("Location: auth.php");
    exit;
}

require_once 'db.php';
require_once 'mailer.php';

// إذا اليوزر مسجل دخول مسبقاً، وجّهه مباشرة
if (!empty($_SESSION['logged_in'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'Admin' ? 'dashboard.php' : 'home.php'));
    exit;
}


// ══════════════════════════════════════════════════════════════════
// معالجة AJAX — Login
// ══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    // ── Login ────────────────────────────────────────────────────
    if ($action === 'login') {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
            exit;
        }

        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Email not found. Please check your email or sign up.']);
            exit;
        }

        // التحقق من الباسورد بـ bcrypt
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Incorrect password. Please try again.']);
            exit;
        }

        // تحديث last_login
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")
                ->execute([$user['user_id']]);
        sendLoginNotificationEmail($user['email'], $user['name']);

        // تخزين الـ session
        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['logged_in']  = true;

        $redirect = $user['role'] === 'Admin' ? 'dashboard.php' : 'home.php';
        echo json_encode(['success' => true, 'redirect' => $redirect]);
        exit;
    }

    // ── Signup ───────────────────────────────────────────────────
    if ($action === 'signup') {
        $name     = trim($_POST['name']            ?? '');
        $email    = trim($_POST['email']           ?? '');
        $password = trim($_POST['password']        ?? '');
        $confirm  = trim($_POST['confirm_password'] ?? '');

        // Validation
        if (!$name || !$email || !$password || !$confirm) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit;
        }
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            exit;
        }
        if ($password !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }

        $pdo = getDB();

        // التحقق إن الإيميل مش مسجل مسبقاً
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists. Please login instead.']);
            exit;
        }

        // تشفير الباسورد بـ bcrypt
        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // إضافة اليوزر لقاعدة البيانات
        $insert = $pdo->prepare("
            INSERT INTO users (name, email, password, role, status, created_at)
            VALUES (?, ?, ?, 'Customer', 'active', NOW())
            RETURNING user_id
        ");
        $insert->execute([$name, $email, $hashed]);
        $newUserId = $insert->fetchColumn();

        // إرسال إيميل الترحيب (بدون ما يتأثر التسجيل إذا فشل)
        if (function_exists('sendWelcomeEmail')) {
            @sendWelcomeEmail($email, $name);
        }

        echo json_encode([
                'success' => true,
                'message' => 'Account created successfully! Please login.',
                'email'   => $email
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Signup · Teddy Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #fff5e8 0%, #ffe6f0 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: #ff9a9e; }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: #fad0c4; animation-delay: 5s; }
        .shape-3 { top: 50%; left: 50%; width: 250px; height: 250px; background: #fbc2eb; transform: translate(-50%, -50%); animation-delay: 2s; }
        @keyframes floatShapes {
            0%   { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50px, 30px) rotate(20deg); }
        }

        .auth-container { position: relative; z-index: 10; width: 100%; max-width: 450px; margin: 20px; }

        .auth-tabs {
            position: relative; display: flex;
            background: rgba(255,255,255,0.9); backdrop-filter: blur(10px);
            border-radius: 60px; padding: 5px; margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .auth-tab {
            flex: 1; text-align: center; padding: 12px 20px;
            border-radius: 50px; font-weight: 600; cursor: pointer;
            transition: all 0.3s ease; color: #888;
            border: none; background: transparent; font-size: 16px;
            position: relative; z-index: 2;
        }
        .auth-tab.active { color: white; }
        .tab-slider {
            position: absolute; top: 5px; left: 5px;
            width: calc(50% - 5px); height: calc(100% - 10px);
            background: linear-gradient(135deg, #ff6b81, #ff9a9e);
            border-radius: 50px; transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1;
        }
        .tab-slider.signup { transform: translateX(100%); }

        .auth-card {
            background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
            border-radius: 30px; padding: 35px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.5);
        }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h2 { font-family: 'Playfair Display', serif; font-size: 32px; color: #333; margin-bottom: 10px; }
        .auth-header p { color: #888; font-size: 14px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 8px; }
        .form-group label i { color: #ff6b81; margin-right: 6px; }
        .input-wrapper { position: relative; }
        .input-wrapper input {
            width: 100%; padding: 14px 18px;
            background: #f8f9fa; border: 2px solid transparent;
            border-radius: 16px; font-size: 14px;
            transition: all 0.3s ease; outline: none; color: #333;
        }
        .input-wrapper input:focus {
            border-color: #ff6b81; background: white;
            box-shadow: 0 0 0 3px rgba(255,107,129,0.1);
        }
        .input-wrapper input::placeholder { color: #bbb; }
        .toggle-password {
            position: absolute; right: 15px; top: 50%;
            transform: translateY(-50%); cursor: pointer; color: #aaa; transition: color 0.3s;
        }
        .toggle-password:hover { color: #ff6b81; }

        .submit-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #ff6b81, #ff9a9e);
            border: none; border-radius: 50px; color: white;
            font-weight: 600; font-size: 16px; cursor: pointer;
            transition: all 0.3s ease; margin-top: 10px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,107,129,0.4); }
        .submit-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .alert-message {
            padding: 12px 18px; border-radius: 16px; margin-bottom: 20px;
            display: none; align-items: center; gap: 10px;
            animation: slideDown 0.3s ease;
        }
        .alert-message.show { display: flex; }
        .alert-message.success { background: rgba(76,175,80,0.1); color: #4CAF50; border: 1px solid #4CAF50; }
        .alert-message.error   { background: rgba(244,67,54,0.1);  color: #F44336; border: 1px solid #F44336; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .tab-content.hide { display: none; }
        .tab-content.show { display: block; animation: fadeInContent 0.4s ease; }
        @keyframes fadeInContent {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .divider { text-align: center; margin: 25px 0; position: relative; }
        .divider::before, .divider::after {
            content: ''; position: absolute; top: 50%;
            width: calc(50% - 60px); height: 1px; background: #e0e0e0;
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }
        .divider span { background: white; padding: 0 15px; color: #aaa; font-size: 13px; }

        .social-buttons { display: flex; gap: 15px; }
        .social-btn {
            flex: 1; padding: 12px; border-radius: 50px;
            border: 1px solid #e0e0e0; background: white; cursor: pointer;
            transition: all 0.3s ease; display: flex; align-items: center;
            justify-content: center; gap: 8px; color: #555; font-weight: 500;
        }
        .social-btn:hover { border-color: #ff6b81; background: #fff5f5; transform: translateY(-2px); }
        .social-btn.google i   { color: #DB4437; }
        .social-btn.facebook i { color: #4267B2; }

        .auth-footer { text-align: center; margin-top: 20px; font-size: 13px; color: #888; }
        .auth-footer a { color: #ff6b81; text-decoration: none; font-weight: 600; cursor: pointer; }
        .auth-footer a:hover { text-decoration: underline; }

        @media (max-width: 500px) {
            .auth-card { padding: 25px; }
            .social-buttons { flex-direction: column; }
            .auth-header h2 { font-size: 26px; }
        }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
</div>

<div class="auth-container">

    <div class="auth-tabs">
        <div class="tab-slider" id="tabSlider"></div>
        <button class="auth-tab active" id="loginTab" onclick="switchTab('login')">
            <i class="fa-solid fa-sign-in-alt"></i> Login
        </button>
        <button class="auth-tab" id="signupTab" onclick="switchTab('signup')">
            <i class="fa-solid fa-user-plus"></i> Sign Up
        </button>
    </div>

    <!-- ── Login Form ─────────────────────────────────────── -->
    <div id="loginForm" class="tab-content show">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back! 🧸</h2>
                <p>Login to your Teddy Shop account</p>
            </div>

            <div id="loginAlert" class="alert-message"></div>

            <div class="form-group">
                <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="loginEmail" placeholder="Enter your email">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-lock"></i> Password</label>
                <div class="input-wrapper">
                    <input type="password" id="loginPassword" placeholder="Enter your password">
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePassword('loginPassword')"></i>
                </div>
            </div>

            <button type="button" class="submit-btn" id="loginBtn" onclick="handleLogin()">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
            </button>

            <div class="divider"><span>Or continue with</span></div>

            <div class="social-buttons">
                <button class="social-btn google"   onclick="alert('Google login coming soon!')"><i class="fab fa-google"></i> Google</button>
                <button class="social-btn facebook" onclick="alert('Facebook login coming soon!')"><i class="fab fa-facebook-f"></i> Facebook</button>
            </div>

            <div class="auth-footer">
                Don't have an account? <a onclick="switchTab('signup')">Sign up</a>
            </div>
        </div>
    </div>

    <!-- ── Signup Form ────────────────────────────────────── -->
    <div id="signupForm" class="tab-content hide">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Create Account 🎀</h2>
                <p>Join Teddy Shop today</p>
            </div>

            <div id="signupAlert" class="alert-message"></div>

            <div class="form-group">
                <label><i class="fa-solid fa-user"></i> Full Name</label>
                <div class="input-wrapper">
                    <input type="text" id="signupName" placeholder="Enter your full name">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                <div class="input-wrapper">
                    <input type="email" id="signupEmail" placeholder="Enter your email">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-lock"></i> Password</label>
                <div class="input-wrapper">
                    <input type="password" id="signupPassword" placeholder="Create a password (min 6 characters)">
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePassword('signupPassword')"></i>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fa-solid fa-lock"></i> Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" id="confirmPassword" placeholder="Confirm your password">
                    <i class="fa-regular fa-eye toggle-password" onclick="togglePassword('confirmPassword')"></i>
                </div>
            </div>

            <button type="button" class="submit-btn" id="signupBtn" onclick="handleSignup()">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>

            <div class="divider"><span>Or sign up with</span></div>

            <div class="social-buttons">
                <button class="social-btn google"   onclick="alert('Google login coming soon!')"><i class="fab fa-google"></i> Google</button>
                <button class="social-btn facebook" onclick="alert('Facebook login coming soon!')"><i class="fab fa-facebook-f"></i> Facebook</button>
            </div>

            <div class="auth-footer">
                Already have an account? <a onclick="switchTab('login')">Login</a>
            </div>
        </div>
    </div>

</div><!-- /auth-container -->

<script>
    // ── Tab Switching ────────────────────────────────────────────────
    function switchTab(tab) {
        const isLogin = tab === 'login';
        document.getElementById('tabSlider').classList.toggle('signup', !isLogin);
        document.getElementById('loginTab').classList.toggle('active',  isLogin);
        document.getElementById('signupTab').classList.toggle('active', !isLogin);
        document.getElementById('loginForm').className  = 'tab-content ' + (isLogin  ? 'show' : 'hide');
        document.getElementById('signupForm').className = 'tab-content ' + (!isLogin ? 'show' : 'hide');
        clearAlerts();
    }

    // ── Alerts ───────────────────────────────────────────────────────
    function clearAlerts() {
        ['loginAlert','signupAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('show');
        });
    }

    function showAlert(form, message, type) {
        const el = document.getElementById(form + 'Alert');
        el.innerHTML = '<i class="fa-solid fa-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '"></i> ' + message;
        el.className = 'alert-message show ' + type;
        setTimeout(() => el.classList.remove('show'), 6000);
    }

    // ── Loading State ────────────────────────────────────────────────
    function setLoading(btnId, loading, defaultHTML) {
        const btn = document.getElementById(btnId);
        btn.disabled = loading;
        btn.innerHTML = loading
            ? '<i class="fa-solid fa-spinner fa-spin"></i> Please wait...'
            : defaultHTML;
    }

    // ── Login ────────────────────────────────────────────────────────
    function handleLogin() {
        const email    = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;
        clearAlerts();

        if (!email)    { showAlert('login', 'Please enter your email', 'error'); return; }
        if (!password) { showAlert('login', 'Please enter your password', 'error'); return; }

        setLoading('loginBtn', true);

        fetch('auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=login&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('login', 'Login successful! Redirecting...', 'success');
                    setTimeout(() => window.location.href = data.redirect, 1200);
                } else {
                    showAlert('login', data.message, 'error');
                    setLoading('loginBtn', false, '<i class="fa-solid fa-arrow-right-to-bracket"></i> Login');
                }
            })
            .catch(() => {
                showAlert('login', 'Something went wrong. Please try again.', 'error');
                setLoading('loginBtn', false, '<i class="fa-solid fa-arrow-right-to-bracket"></i> Login');
            });
    }

    // ── Signup ───────────────────────────────────────────────────────
    function handleSignup() {
        const name     = document.getElementById('signupName').value.trim();
        const email    = document.getElementById('signupEmail').value.trim();
        const password = document.getElementById('signupPassword').value;
        const confirm  = document.getElementById('confirmPassword').value;
        clearAlerts();

        if (!name)     { showAlert('signup', 'Please enter your full name', 'error'); return; }
        if (!email)    { showAlert('signup', 'Please enter your email', 'error'); return; }
        if (!password) { showAlert('signup', 'Please create a password', 'error'); return; }
        if (password.length < 6) { showAlert('signup', 'Password must be at least 6 characters', 'error'); return; }
        if (password !== confirm) { showAlert('signup', 'Passwords do not match', 'error'); return; }

        setLoading('signupBtn', true);

        fetch('auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=signup'
                + '&name='             + encodeURIComponent(name)
                + '&email='            + encodeURIComponent(email)
                + '&password='         + encodeURIComponent(password)
                + '&confirm_password=' + encodeURIComponent(confirm)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('signup', '🎉 Account created! Check your email for a welcome message.', 'success');
                    // reset form
                    ['signupName','signupEmail','signupPassword','confirmPassword']
                        .forEach(id => document.getElementById(id).value = '');

                    setTimeout(() => {
                        switchTab('login');
                        document.getElementById('loginEmail').value = data.email ?? '';
                        showAlert('login', 'Account created! Please login with your credentials.', 'success');
                        setLoading('signupBtn', false, '<i class="fa-solid fa-user-plus"></i> Create Account');
                    }, 2000);
                } else {
                    showAlert('signup', data.message, 'error');
                    setLoading('signupBtn', false, '<i class="fa-solid fa-user-plus"></i> Create Account');
                }
            })
            .catch(() => {
                showAlert('signup', 'Something went wrong. Please try again.', 'error');
                setLoading('signupBtn', false, '<i class="fa-solid fa-user-plus"></i> Create Account');
            });
    }

    // ── Helpers ──────────────────────────────────────────────────────
    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon  = input.nextElementSibling;
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        icon.classList.toggle('fa-eye',       !show);
        icon.classList.toggle('fa-eye-slash',  show);
    }

    // Enter key support
    ['loginEmail','loginPassword'].forEach(id =>
        document.getElementById(id)?.addEventListener('keypress', e => e.key === 'Enter' && handleLogin())
    );
    ['signupName','signupEmail','signupPassword','confirmPassword'].forEach(id =>
        document.getElementById(id)?.addEventListener('keypress', e => e.key === 'Enter' && handleSignup())
    );

    // Focus effects
    document.querySelectorAll('.input-wrapper input').forEach(input => {
        input.addEventListener('focus', () => input.parentElement.style.transform = 'translateY(-2px)');
        input.addEventListener('blur',  () => input.parentElement.style.transform = 'translateY(0)');
    });
</script>

</body>
</html>