<?php
// settings.php
session_start();

// بيانات تجريبية للإعدادات
$settings = [
    'general' => [
        'site_name' => 'Teddy Shop',
        'site_description' => 'Your favorite teddy bear store',
        'site_email' => 'info@teddyshop.com',
        'site_phone' => '+1 234 567 890',
        'site_address' => '123 Teddy Street, Toy City',
        'currency' => 'USD',
        'timezone' => 'America/New_York'
    ],
    'appearance' => [
        'theme' => 'light',
        'primary_color' => '#F8BBD0',
        'secondary_color' => '#E6E6FA',
        'font' => 'Poppins',
        'logo' => 'logo.png',
        'favicon' => 'favicon.ico'
    ],
    'payment' => [
        'methods' => ['credit_card', 'paypal', 'bank_transfer'],
        'tax_rate' => 10,
        'shipping_fee' => 5.99,
        'free_shipping_threshold' => 50
    ],
    'email' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'smtp_username' => 'noreply@teddyshop.com',
        'smtp_password' => '********'
    ],
    'security' => [
        'two_factor_auth' => false,
        'session_timeout' => 30,
        'max_login_attempts' => 5,
        'recaptcha_enabled' => true,
        'recaptcha_site_key' => '6Lc...',
        'recaptcha_secret_key' => '6Ld...'
    ]
];

// تحديد التبويب النشط
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings · Teddy Shop</title>
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
        /* تنسيقات أساسية - نفس باقي الصفحات */
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        /* Stats Cards - مثل products.php */
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
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
        }
        .stat-mini-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-mini-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-mini-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-mini-card:nth-child(4) { animation-delay: 0.4s; }

        .stat-mini-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px var(--shadow);
        }
        .stat-mini-info h4 { font-size: 14px; color: var(--secondary-text); margin-bottom: 5px; }
        .stat-mini-info .value { font-size: 32px; font-weight: 700; color: var(--text-color); }
        .stat-mini-icon { font-size: 40px; opacity: 0.7; transition: all 0.3s ease; }
        .stat-mini-card:hover .stat-mini-icon {
            transform: scale(1.2) rotate(5deg);
            color: var(--pink);
        }

        /* Settings Container */
        .settings-container {
            background: var(--card-bg);
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            padding: 30px;
            margin: 25px 0;
            animation: fadeInUp 0.8s ease;
        }

        /* Settings Tabs - تصميم جديد */
        .settings-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .tab-btn {
            padding: 12px 25px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 50px;
            color: var(--text-color);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .tab-btn i {
            color: var(--primary);
            transition: all 0.3s ease;
        }
        .tab-btn:hover {
            background: var(--pink);
            color: #000;
            transform: translateY(-2px);
            border-color: transparent;
        }
        .tab-btn:hover i {
            color: #000;
            transform: rotate(90deg);
        }
        .tab-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .tab-btn.active i {
            color: #fff;
        }

        /* Settings Panel */
        .settings-panel {
            animation: fadeIn 0.5s ease;
        }
        .settings-panel h3 {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .settings-panel h3 i {
            color: var(--pink);
            font-size: 28px;
        }

        /* Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        @media (max-width: 992px) {
            .settings-grid { grid-template-columns: 1fr; }
        }

        /* Settings Card */
        .setting-card {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(128,128,128,0.1);
            transition: all 0.3s ease;
        }
        .setting-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--shadow);
            border-color: var(--pink);
        }
        .setting-card h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .setting-card h4 i {
            color: var(--primary);
        }

        /* Form Elements - نفس تصميم الفلاتر */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 8px;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: var(--card-bg);
            border: 2px solid transparent;
            border-radius: 50px;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:hover {
            border-color: var(--pink);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .toggle-slider {
            background-color: var(--primary);
        }
        input:focus + .toggle-slider {
            box-shadow: 0 0 1px var(--primary);
        }
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* Color Picker */
        .color-picker {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .color-input {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--card-bg);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .color-input:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .color-value {
            font-size: 14px;
            color: var(--secondary-text);
        }

        /* Save Button */
        .save-btn {
            background: linear-gradient(45deg, var(--primary), var(--pink));
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .save-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(248, 187, 208, 0.5);
        }
        .save-btn i {
            transition: transform 0.3s ease;
        }
        .save-btn:hover i {
            transform: rotate(90deg);
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
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Header -->
        <div class="main-header" style="animation: fadeInDown 0.6s ease;">
            <div>
                <h1 style="margin-bottom: 5px;">Settings</h1>
                <p style="color: var(--secondary-text);">Configure your store settings</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" style="background: var(--lavender); color: #000; transition: all 0.3s ease;" onclick="backupSettings()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-download"></i> Backup
                </button>
                <button class="btn-primary" style="background: var(--pink); color: #000; transition: all 0.3s ease;" onclick="resetSettings()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Active Settings</h4>
                    <div class="value">24</div>
                </div>
                <i class="fa-solid fa-gear stat-mini-icon" style="color: var(--primary);"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Payment Methods</h4>
                    <div class="value">3</div>
                </div>
                <i class="fa-solid fa-credit-card stat-mini-icon" style="color: #4CAF50;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Email Templates</h4>
                    <div class="value">12</div>
                </div>
                <i class="fa-solid fa-envelope stat-mini-icon" style="color: #FF9800;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Last Backup</h4>
                    <div class="value">2h ago</div>
                </div>
                <i class="fa-solid fa-clock stat-mini-icon" style="color: var(--pink);"></i>
            </div>
        </div>

        <!-- Settings Container -->
        <div class="settings-container">
            <!-- Settings Tabs -->
            <div class="settings-tabs">
                <button class="tab-btn <?= $active_tab == 'general' ? 'active' : '' ?>" onclick="switchTab('general')">
                    <i class="fa-solid fa-globe"></i> General
                </button>
                <button class="tab-btn <?= $active_tab == 'appearance' ? 'active' : '' ?>" onclick="switchTab('appearance')">
                    <i class="fa-solid fa-palette"></i> Appearance
                </button>
                <button class="tab-btn <?= $active_tab == 'payment' ? 'active' : '' ?>" onclick="switchTab('payment')">
                    <i class="fa-solid fa-credit-card"></i> Payment
                </button>
                <button class="tab-btn <?= $active_tab == 'email' ? 'active' : '' ?>" onclick="switchTab('email')">
                    <i class="fa-solid fa-envelope"></i> Email
                </button>
                <button class="tab-btn <?= $active_tab == 'security' ? 'active' : '' ?>" onclick="switchTab('security')">
                    <i class="fa-solid fa-shield-hal"></i> Security
                </button>
            </div>

            <!-- General Settings Panel -->
            <?php if ($active_tab == 'general'): ?>
                <div class="settings-panel">
                    <h3><i class="fa-solid fa-globe"></i> General Settings</h3>
                    <div class="settings-grid">
                        <!-- Site Information Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-info-circle"></i> Site Information</h4>
                            <div class="form-group">
                                <label>Site Name</label>
                                <input type="text" class="form-control" value="<?= $settings['general']['site_name'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Site Description</label>
                                <textarea class="form-control" rows="3"><?= $settings['general']['site_description'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Site Email</label>
                                <input type="email" class="form-control" value="<?= $settings['general']['site_email'] ?>">
                            </div>
                        </div>

                        <!-- Contact Information Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-address-card"></i> Contact Information</h4>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" class="form-control" value="<?= $settings['general']['site_phone'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea class="form-control" rows="3"><?= $settings['general']['site_address'] ?></textarea>
                            </div>
                        </div>

                        <!-- Localization Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-location-dot"></i> Localization</h4>
                            <div class="form-group">
                                <label>Currency</label>
                                <select class="form-control">
                                    <option value="USD" <?= $settings['general']['currency'] == 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                    <option value="JPY">JPY (¥)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Timezone</label>
                                <select class="form-control">
                                    <option value="America/New_York" <?= $settings['general']['timezone'] == 'America/New_York' ? 'selected' : '' ?>>New York (EST)</option>
                                    <option value="Europe/London">London (GMT)</option>
                                    <option value="Asia/Dubai">Dubai (GST)</option>
                                    <option value="Asia/Tokyo">Tokyo (JST)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Appearance Settings Panel -->
            <?php if ($active_tab == 'appearance'): ?>
                <div class="settings-panel">
                    <h3><i class="fa-solid fa-palette"></i> Appearance Settings</h3>
                    <div class="settings-grid">
                        <!-- Theme Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-paint-roller"></i> Theme</h4>
                            <div class="form-group">
                                <label>Default Theme</label>
                                <select class="form-control">
                                    <option value="light" <?= $settings['appearance']['theme'] == 'light' ? 'selected' : '' ?>>Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="auto">Auto (System)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Colors Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-droplet"></i> Colors</h4>
                            <div class="form-group">
                                <label>Primary Color</label>
                                <div class="color-picker">
                                    <input type="color" class="color-input" value="<?= $settings['appearance']['primary_color'] ?>">
                                    <span class="color-value"><?= $settings['appearance']['primary_color'] ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Secondary Color</label>
                                <div class="color-picker">
                                    <input type="color" class="color-input" value="<?= $settings['appearance']['secondary_color'] ?>">
                                    <span class="color-value"><?= $settings['appearance']['secondary_color'] ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Fonts Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-font"></i> Fonts</h4>
                            <div class="form-group">
                                <label>Primary Font</label>
                                <select class="form-control">
                                    <option value="Poppins" <?= $settings['appearance']['font'] == 'Poppins' ? 'selected' : '' ?>>Poppins</option>
                                    <option value="Inter">Inter</option>
                                    <option value="Roboto">Roboto</option>
                                    <option value="Open Sans">Open Sans</option>
                                </select>
                            </div>
                        </div>

                        <!-- Logo Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-image"></i> Logo & Favicon</h4>
                            <div class="form-group">
                                <label>Logo</label>
                                <input type="file" class="form-control" accept="image/*">
                                <small style="color: var(--secondary-text);">Current: <?= $settings['appearance']['logo'] ?></small>
                            </div>
                            <div class="form-group">
                                <label>Favicon</label>
                                <input type="file" class="form-control" accept="image/*">
                                <small style="color: var(--secondary-text);">Current: <?= $settings['appearance']['favicon'] ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payment Settings Panel -->
            <?php if ($active_tab == 'payment'): ?>
                <div class="settings-panel">
                    <h3><i class="fa-solid fa-credit-card"></i> Payment Settings</h3>
                    <div class="settings-grid">
                        <!-- Payment Methods Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-money-bill"></i> Payment Methods</h4>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" <?= in_array('credit_card', $settings['payment']['methods']) ? 'checked' : '' ?>> Credit Card
                                </label>
                            </div>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" <?= in_array('paypal', $settings['payment']['methods']) ? 'checked' : '' ?>> PayPal
                                </label>
                            </div>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" <?= in_array('bank_transfer', $settings['payment']['methods']) ? 'checked' : '' ?>> Bank Transfer
                                </label>
                            </div>
                        </div>

                        <!-- Pricing Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-calculator"></i> Pricing</h4>
                            <div class="form-group">
                                <label>Tax Rate (%)</label>
                                <input type="number" class="form-control" value="<?= $settings['payment']['tax_rate'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Shipping Fee ($)</label>
                                <input type="number" step="0.01" class="form-control" value="<?= $settings['payment']['shipping_fee'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Free Shipping Threshold ($)</label>
                                <input type="number" step="0.01" class="form-control" value="<?= $settings['payment']['free_shipping_threshold'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Email Settings Panel -->
            <?php if ($active_tab == 'email'): ?>
                <div class="settings-panel">
                    <h3><i class="fa-solid fa-envelope"></i> Email Settings</h3>
                    <div class="settings-grid">
                        <!-- SMTP Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-server"></i> SMTP Configuration</h4>
                            <div class="form-group">
                                <label>SMTP Host</label>
                                <input type="text" class="form-control" value="<?= $settings['email']['smtp_host'] ?>">
                            </div>
                            <div class="form-group">
                                <label>SMTP Port</label>
                                <input type="number" class="form-control" value="<?= $settings['email']['smtp_port'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Encryption</label>
                                <select class="form-control">
                                    <option value="tls" <?= $settings['email']['smtp_encryption'] == 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>

                        <!-- Authentication Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-lock"></i> Authentication</h4>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?= $settings['email']['smtp_username'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control" value="<?= $settings['email']['smtp_password'] ?>">
                            </div>
                            <div class="form-group">
                                <button class="btn-primary" style="width: 100%;">Test Connection</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Security Settings Panel -->
            <?php if ($active_tab == 'security'): ?>
                <div class="settings-panel">
                    <h3><i class="fa-solid fa-shield-hal"></i> Security Settings</h3>
                    <div class="settings-grid">
                        <!-- Authentication Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-user-lock"></i> Authentication</h4>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; justify-content: space-between;">
                                    <span>Two-Factor Authentication</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" <?= $settings['security']['two_factor_auth'] ? 'checked' : '' ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </label>
                            </div>
                            <div class="form-group">
                                <label>Session Timeout (minutes)</label>
                                <input type="number" class="form-control" value="<?= $settings['security']['session_timeout'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Max Login Attempts</label>
                                <input type="number" class="form-control" value="<?= $settings['security']['max_login_attempts'] ?>">
                            </div>
                        </div>

                        <!-- reCAPTCHA Card -->
                        <div class="setting-card">
                            <h4><i class="fa-solid fa-robot"></i> reCAPTCHA</h4>
                            <div class="form-group">
                                <label style="display: flex; align-items: center; justify-content: space-between;">
                                    <span>Enable reCAPTCHA</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" <?= $settings['security']['recaptcha_enabled'] ? 'checked' : '' ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </label>
                            </div>
                            <div class="form-group">
                                <label>Site Key</label>
                                <input type="text" class="form-control" value="<?= $settings['security']['recaptcha_site_key'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Secret Key</label>
                                <input type="password" class="form-control" value="<?= $settings['security']['recaptcha_secret_key'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Save Button -->
            <div style="text-align: right;">
                <button class="save-btn" onclick="saveSettings()">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </div>
    </main>
</div>

<script>
    // ===== تبديل التبويبات =====
    function switchTab(tab) {
        window.location.href = 'settings.php?tab=' + tab;
    }

    // ===== حفظ الإعدادات =====
    function saveSettings() {
        // تأثير مؤقت للزر
        const btn = event.currentTarget;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Saved!';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
            }, 1500);
        }, 1000);
    }

    // ===== وظائف الأزرار =====
    function backupSettings() {
        alert('Backup settings feature (Demo)');
    }

    function resetSettings() {
        if(confirm('Are you sure you want to reset all settings to default?')) {
            alert('Settings reset (Demo)');
        }
    }

    // ===== تأثيرات السيرش بار =====
    document.addEventListener('DOMContentLoaded', function() {
        // تأثيرات للفورم كونترول
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.addEventListener('focus', function() {
                this.style.transform = 'translateY(-2px)';
            });
            control.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // تأثيرات للتبويبات
        const tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(tab => {
            tab.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'translateY(-2px)';
                }
            });
            tab.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'translateY(0)';
                }
            });
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
                const isDark = this.checked;
                applyTheme(isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>