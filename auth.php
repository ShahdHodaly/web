<?php
// auth.php
session_start();

// بيانات تجريبية للمستخدمين
$users = [
        1 => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@teddy.com',
                'password' => 'admin123',
                'role' => 'Admin',
                'avatar' => 'https://ui-avatars.com/api/?name=Admin&background=F8BBD0&color=000&size=40'
        ],
        2 => [
                'id' => 2,
                'name' => 'Customer User',
                'email' => 'customer@teddy.com',
                'password' => 'customer123',
                'role' => 'Customer',
                'avatar' => 'https://ui-avatars.com/api/?name=Customer&background=E6E6FA&color=000&size=40'
        ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Signup · Teddy Shop</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        /* Background Shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: floatShapes 15s infinite alternate;
        }

        .shape-1 {
            top: 10%;
            left: 10%;
            width: 300px;
            height: 300px;
            background: #ff9a9e;
        }

        .shape-2 {
            bottom: 20%;
            right: 10%;
            width: 400px;
            height: 400px;
            background: #fad0c4;
            animation-delay: 5s;
        }

        .shape-3 {
            top: 50%;
            left: 50%;
            width: 250px;
            height: 250px;
            background: #fbc2eb;
            transform: translate(-50%, -50%);
            animation-delay: 2s;
        }

        @keyframes floatShapes {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50px, 30px) rotate(20deg); }
        }

        /* Auth Container */
        .auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            margin: 20px;
        }

        /* Tabs with Smooth Slider */
        .auth-tabs {
            position: relative;
            display: flex;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 60px;
            padding: 5px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 12px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #888;
            border: none;
            background: transparent;
            font-size: 16px;
            position: relative;
            z-index: 2;
        }

        .auth-tab.active {
            color: white;
        }

        /* Slider Indicator */
        .tab-slider {
            position: absolute;
            top: 5px;
            left: 5px;
            width: calc(50% - 5px);
            height: calc(100% - 10px);
            background: linear-gradient(135deg, #ff6b81, #ff9a9e);
            border-radius: 50px;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }

        .tab-slider.signup {
            transform: translateX(100%);
        }

        /* Card */
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .auth-header p {
            color: #888;
            font-size: 14px;
        }

        .teddy-icon {
            font-size: 48px;
            color: #ff6b81;
            margin-bottom: 15px;
            display: inline-block;
            animation: wave 2s infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        /* Demo Credentials */
        .demo-credentials {
            background: rgba(255, 107, 129, 0.1);
            border-radius: 16px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        .demo-credentials p {
            font-size: 12px;
            color: #ff6b81;
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .demo-credentials .cred-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .demo-credentials .cred-item {
            font-size: 11px;
            background: white;
            padding: 4px 10px;
            border-radius: 30px;
            color: #555;
        }

        .demo-credentials .cred-item i {
            color: #ff6b81;
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
            color: #555;
            margin-bottom: 8px;
        }

        .form-group label i {
            color: #ff6b81;
            margin-right: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 18px;
            background: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
            color: #333;
        }

        .input-wrapper input:focus {
            border-color: #ff6b81;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 107, 129, 0.1);
        }

        .input-wrapper input::placeholder {
            color: #bbb;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: #ff6b81;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ff6b81, #ff9a9e);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 129, 0.4);
        }

        .submit-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .submit-btn.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Alert Message */
        .alert-message {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .alert-message.show {
            display: flex;
        }

        .alert-message.success {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }

        .alert-message.error {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: 1px solid #F44336;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tab Content Transition */
        .tab-content {
            transition: opacity 0.3s ease;
        }

        .tab-content.hide {
            display: none;
        }

        .tab-content.show {
            display: block;
            animation: fadeInContent 0.4s ease;
        }

        @keyframes fadeInContent {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Divider */
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: calc(50% - 60px);
            height: 1px;
            background: #e0e0e0;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #aaa;
            font-size: 13px;
        }

        /* Social Buttons */
        .social-buttons {
            display: flex;
            gap: 15px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border-radius: 50px;
            border: 1px solid #e0e0e0;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #555;
            font-weight: 500;
        }

        .social-btn:hover {
            border-color: #ff6b81;
            background: #fff5f5;
            transform: translateY(-2px);
        }

        .social-btn.google i { color: #DB4437; }
        .social-btn.facebook i { color: #4267B2; }

        /* Footer Links */
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #888;
        }

        .auth-footer a {
            color: #ff6b81;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 500px) {
            .auth-card {
                padding: 25px;
            }

            .social-buttons {
                flex-direction: column;
            }

            .auth-header h2 {
                font-size: 26px;
            }

            .demo-credentials .cred-row {
                flex-direction: column;
                gap: 8px;
            }
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
    <!-- Tabs with Smooth Slider -->
    <div class="auth-tabs">
        <div class="tab-slider" id="tabSlider"></div>
        <button class="auth-tab active" data-tab="login" id="loginTab" onclick="switchTab('login')">
            <i class="fa-solid fa-sign-in-alt"></i> Login
        </button>
        <button class="auth-tab" data-tab="signup" id="signupTab" onclick="switchTab('signup')">
            <i class="fa-solid fa-user-plus"></i> Sign Up
        </button>
    </div>

    <!-- Login Form -->
    <div id="loginForm" class="tab-content show">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back!</h2>
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

            <div class="divider">
                <span>Or continue with</span>
            </div>

            <div class="social-buttons">
                <button class="social-btn google" onclick="socialLogin('google')">
                    <i class="fab fa-google"></i> Google
                </button>
                <button class="social-btn facebook" onclick="socialLogin('facebook')">
                    <i class="fab fa-facebook-f"></i> Facebook
                </button>
            </div>

            <div class="auth-footer">
                Don't have an account? <a onclick="switchTab('signup')">Sign up</a>
            </div>
        </div>
    </div>

    <!-- Signup Form -->
    <div id="signupForm" class="tab-content hide">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Create Account</h2>
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

            <div class="divider">
                <span>Or sign up with</span>
            </div>

            <div class="social-buttons">
                <button class="social-btn google" onclick="socialLogin('google')">
                    <i class="fab fa-google"></i> Google
                </button>
                <button class="social-btn facebook" onclick="socialLogin('facebook')">
                    <i class="fab fa-facebook-f"></i> Facebook
                </button>
            </div>

            <div class="auth-footer">
                Already have an account? <a onclick="switchTab('login')">Login</a>
            </div>
        </div>
    </div>
</div>

<script>
    const usersData = [
        {
            id: 1,
            name: 'Admin User',
            email: 'admin@teddy.com',
            password: 'admin123',
            role: 'Admin'
        },
        {
            id: 2,
            name: 'Customer User',
            email: 'customer@teddy.com',
            password: 'customer123',
            role: 'Customer'
        }
    ];

    // ========== Smooth Tab Slider ==========
    const loginTab = document.getElementById('loginTab');
    const signupTab = document.getElementById('signupTab');
    const tabSlider = document.getElementById('tabSlider');
    const loginFormDiv = document.getElementById('loginForm');
    const signupFormDiv = document.getElementById('signupForm');

    function switchTab(tab) {
        if (tab === 'login') {
            // Move slider to left
            tabSlider.classList.remove('signup');

            // Update active classes
            loginTab.classList.add('active');
            signupTab.classList.remove('active');

            // Switch forms
            loginFormDiv.classList.remove('hide');
            loginFormDiv.classList.add('show');
            signupFormDiv.classList.remove('show');
            signupFormDiv.classList.add('hide');

            // Clear alerts
            clearAlerts();
        } else {
            // Move slider to right
            tabSlider.classList.add('signup');

            // Update active classes
            signupTab.classList.add('active');
            loginTab.classList.remove('active');

            // Switch forms
            signupFormDiv.classList.remove('hide');
            signupFormDiv.classList.add('show');
            loginFormDiv.classList.remove('show');
            loginFormDiv.classList.add('hide');

            // Clear alerts
            clearAlerts();
        }
    }

    // Initialize slider
    document.addEventListener('DOMContentLoaded', function() {
        tabSlider.classList.remove('signup');
    });

    // Clear alerts
    function clearAlerts() {
        const loginAlert = document.getElementById('loginAlert');
        const signupAlert = document.getElementById('signupAlert');
        if (loginAlert) loginAlert.classList.remove('show');
        if (signupAlert) signupAlert.classList.remove('show');
    }

    // Show alert message
    function showAlert(formType, message, type) {
        const alertDiv = document.getElementById(formType + 'Alert');
        alertDiv.innerHTML = '<i class="fa-solid fa-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '"></i> ' + message;
        alertDiv.className = 'alert-message show ' + type;

        // Auto hide after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
        }, 5000);
    }

    // Loading state
    function setLoading(btn, isLoading) {
        if (isLoading) {
            btn.classList.add('loading');
            btn.disabled = true;
        } else {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    }

    // ========== Login Function ==========
    function handleLogin() {
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;
        const btn = document.getElementById('loginBtn');

        // Clear previous alerts
        clearAlerts();

        // Validation
        if (!email) {
            showAlert('login', 'Please enter your email', 'error');
            return;
        }
        if (!password) {
            showAlert('login', 'Please enter your password', 'error');
            return;
        }

        // Show loading
        setLoading(btn, true);
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Logging in...';

        // Simulate slight delay for better UX
        setTimeout(() => {
            // Find user in database
            const user = usersData.find(u => u.email === email);

            if (!user) {
                showAlert('login', 'Email not found. Please check your email or sign up.', 'error');
                setLoading(btn, false);
                btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> Login';
                return;
            }

            // Check password
            if (user.password !== password) {
                showAlert('login', 'Incorrect password. Please try again.', 'error');
                setLoading(btn, false);
                btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> Login';
                return;
            }

            // Login successful
            showAlert('login', 'Login successful!', 'success');

            // Store user in session via AJAX
            fetch('auth-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=login&user_id=' + user.id + '&user_name=' + encodeURIComponent(user.name) + '&user_email=' + encodeURIComponent(user.email) + '&user_role=' + user.role
            }).then(() => {
                // Redirect based on role
                setTimeout(() => {
                    if (user.role === 'Admin') {
                        window.location.href = 'dashboard.php';
                    } else {
                        window.location.href = 'home.php';
                    }
                }, 1500);
            }).catch(error => {
                console.error('Session error:', error);
                // Even if session fails, redirect anyway
                setTimeout(() => {
                    if (user.role === 'Admin') {
                        window.location.href = 'dashboard.php';
                    } else {
                        window.location.href = 'index.php';
                    }
                }, 1500);
            });
        }, 500);
    }

    // ========== Signup Function ==========
    function handleSignup() {
        const name = document.getElementById('signupName').value.trim();
        const email = document.getElementById('signupEmail').value.trim();
        const password = document.getElementById('signupPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const btn = document.getElementById('signupBtn');

        // Clear previous alerts
        clearAlerts();

        // Validation
        if (!name) {
            showAlert('signup', 'Please enter your full name', 'error');
            return;
        }
        if (!email) {
            showAlert('signup', 'Please enter your email address', 'error');
            return;
        }
        if (!email.includes('@') || !email.includes('.')) {
            showAlert('signup', 'Please enter a valid email address', 'error');
            return;
        }
        if (!password) {
            showAlert('signup', 'Please create a password', 'error');
            return;
        }
        if (password.length < 6) {
            showAlert('signup', 'Password must be at least 6 characters', 'error');
            return;
        }
        if (password !== confirmPassword) {
            showAlert('signup', 'Passwords do not match', 'error');
            return;
        }

        // Check if email already exists
        const existingUser = usersData.find(u => u.email === email);
        if (existingUser) {
            showAlert('signup', 'Email already exists. Please use a different email or login.', 'error');
            return;
        }

        // Show loading
        setLoading(btn, true);
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating account...';

        // Simulate slight delay
        setTimeout(() => {
            // Create new user ID
            const newId = usersData.length + 1;

            // Create new user object
            const newUser = {
                id: newId,
                name: name,
                email: email,
                password: password,
                role: 'Customer'
            };

            // Add to users array (in memory)
            usersData.push(newUser);

            showAlert('signup', 'Account created successfully! Redirecting to login...', 'success');

            // Reset form
            document.getElementById('signupName').value = '';
            document.getElementById('signupEmail').value = '';
            document.getElementById('signupPassword').value = '';
            document.getElementById('confirmPassword').value = '';

            // Switch to login after 2 seconds
            setTimeout(() => {
                switchTab('login');
                setLoading(btn, false);
                btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Create Account';

                // Auto-fill email for convenience
                document.getElementById('loginEmail').value = email;

                // Show success message on login form
                showAlert('login', 'Account created! Please login with your credentials.', 'success');
            }, 2000);
        }, 500);
    }

    // ========== Helper Functions ==========
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function socialLogin(provider) {
        alert(provider.charAt(0).toUpperCase() + provider.slice(1) + ' login coming soon!');
    }

    // Add focus effects
    document.querySelectorAll('.input-wrapper input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });

    // Enter key support
    document.getElementById('loginEmail')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleLogin();
    });
    document.getElementById('loginPassword')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleLogin();
    });
    document.getElementById('signupName')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleSignup();
    });
    document.getElementById('signupEmail')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleSignup();
    });
    document.getElementById('signupPassword')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleSignup();
    });
    document.getElementById('confirmPassword')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleSignup();
    });

    // Make functions global
    window.switchTab = switchTab;
    window.handleLogin = handleLogin;
    window.handleSignup = handleSignup;
    window.togglePassword = togglePassword;
    window.socialLogin = socialLogin;
</script>

</body>
</html>