<?php
// settings.php
session_start();
require_once 'db.php';

$pdo = getDB();

// جلب جميع الإعدادات من قاعدة البيانات (بدون setting_group)
$stmt = $pdo->query("SELECT setting_key, setting_value, setting_type FROM settings ORDER BY setting_key");
$settingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تنظيم الإعدادات في مصفوفة general
$generalSettings = [];
foreach ($settingsData as $setting) {
    $generalSettings[$setting['setting_key']] = $setting['setting_value'];
}

// معالجة حفظ الإعدادات
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        $pdo->beginTransaction();

        foreach ($_POST as $key => $value) {
            if ($key !== 'save_settings' && !empty($key)) {
                // تنظيف القيمة
                $value = trim($value);

                // التحقق من وجود الإعداد وتحديثه
                $stmt = $pdo->prepare("
                    UPDATE settings 
                    SET setting_value = ?, updated_at = NOW() 
                    WHERE setting_key = ?
                ");
                $stmt->execute([$value, $key]);

                // إذا لم يتم تحديث أي صف، قم بإدراج إعداد جديد
                if ($stmt->rowCount() === 0) {
                    // تحديد نوع الإعداد بناءً على المفتاح
                    $setting_type = 'text';
                    if (strpos($key, 'description') !== false || strpos($key, 'address') !== false) {
                        $setting_type = 'textarea';
                    } elseif (strpos($key, 'email') !== false) {
                        $setting_type = 'email';
                    } elseif ($key === 'currency' || $key === 'timezone') {
                        $setting_type = 'select';
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO settings (setting_key, setting_value, setting_type, created_at, updated_at)
                        VALUES (?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->execute([$key, $value, $setting_type]);
                }
            }
        }

        $pdo->commit();
        $success_message = 'Settings saved successfully!';

        // إعادة تحميل الإعدادات
        $stmt = $pdo->query("SELECT setting_key, setting_value, setting_type FROM settings");
        $settingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $generalSettings = [];
        foreach ($settingsData as $setting) {
            $generalSettings[$setting['setting_key']] = $setting['setting_value'];
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = 'Database error: ' . $e->getMessage();
    }
}
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
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        .settings-container {
            background: var(--card-bg);
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            padding: 35px;
            animation: fadeInUp 0.8s ease;
            max-width: 900px;
            margin: 0 auto;
        }

        .settings-header {
            margin-bottom: 30px;
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .settings-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .settings-header p {
            color: var(--secondary-text);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        @media (max-width: 992px) {
            .settings-grid { grid-template-columns: 1fr; }
        }

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
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
        }
        .setting-card h4 i {
            color: var(--primary);
        }

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
            border-radius: 12px;
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
        textarea.form-control {
            border-radius: 16px;
            resize: vertical;
        }
        select.form-control {
            cursor: pointer;
        }

        .save-btn {
            background: linear-gradient(45deg, var(--primary), var(--pink));
            color: white;
            border: none;
            padding: 14px 35px;
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
        .save-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .save-btn i {
            transition: transform 0.3s ease;
        }
        .save-btn:hover i {
            transform: rotate(90deg);
        }

        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="settings-container">
            <div class="settings-header">
                <h1>
                    <i class="fa-solid fa-sliders" style="color: var(--pink);"></i>
                    General Settings
                </h1>
                <p>Configure your store general settings</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span><?= $success_message ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <span><?= $error_message ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" id="settingsForm">
                <input type="hidden" name="save_settings" value="1">

                <div class="settings-grid">
                    <!-- Site Information Card -->
                    <div class="setting-card">
                        <h4><i class="fa-solid fa-info-circle"></i> Site Information</h4>
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($generalSettings['site_name'] ?? 'Teddy Shop') ?>">
                        </div>
                        <div class="form-group">
                            <label>Site Description</label>
                            <textarea name="site_description" class="form-control" rows="3"><?= htmlspecialchars($generalSettings['site_description'] ?? 'Your favorite teddy bear store') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Site Email</label>
                            <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($generalSettings['site_email'] ?? 'info@teddyshop.com') ?>">
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="setting-card">
                        <h4><i class="fa-solid fa-address-card"></i> Contact Information</h4>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars($generalSettings['site_phone'] ?? '+1 234 567 890') ?>">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="site_address" class="form-control" rows="3"><?= htmlspecialchars($generalSettings['site_address'] ?? '123 Teddy Street, Toy City') ?></textarea>
                        </div>
                    </div>

                    <!-- Localization Card -->
                    <div class="setting-card">
                        <h4><i class="fa-solid fa-location-dot"></i> Localization</h4>
                        <div class="form-group">
                            <label>Currency</label>
                            <select name="currency" class="form-control">
                                <option value="USD" <?= ($generalSettings['currency'] ?? 'USD') == 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                <option value="EUR" <?= ($generalSettings['currency'] ?? 'USD') == 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                <option value="GBP" <?= ($generalSettings['currency'] ?? 'USD') == 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                                <option value="JPY" <?= ($generalSettings['currency'] ?? 'USD') == 'JPY' ? 'selected' : '' ?>>JPY (¥)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Timezone</label>
                            <select name="timezone" class="form-control">
                                <option value="America/New_York" <?= ($generalSettings['timezone'] ?? 'America/New_York') == 'America/New_York' ? 'selected' : '' ?>>New York (EST)</option>
                                <option value="Europe/London" <?= ($generalSettings['timezone'] ?? 'America/New_York') == 'Europe/London' ? 'selected' : '' ?>>London (GMT)</option>
                                <option value="Asia/Dubai" <?= ($generalSettings['timezone'] ?? 'America/New_York') == 'Asia/Dubai' ? 'selected' : '' ?>>Dubai (GST)</option>
                                <option value="Asia/Tokyo" <?= ($generalSettings['timezone'] ?? 'America/New_York') == 'Asia/Tokyo' ? 'selected' : '' ?>>Tokyo (JST)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="save-btn" id="saveBtn">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // ✅ إصلاح مشكلة زر الـ Save
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        const saveBtn = document.getElementById('saveBtn');
        const originalHTML = saveBtn.innerHTML;

        // تغيير النص وتعطيل الزر
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;

        // السماح بإرسال النموذج (سيتم إعادة تحميل الصفحة)
        // لا نمنع الـ submit
    });

    // Form validation before submit
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        const siteEmail = document.querySelector('input[name="site_email"]').value;
        if (siteEmail && !/^[^\s@]+@([^\s@]+\.)+[^\s@]+$/.test(siteEmail)) {
            e.preventDefault();
            alert('Please enter a valid email address for Site Email');
            // إعادة تفعيل الزر إذا تم منع الإرسال
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
            saveBtn.disabled = false;
            return false;
        }
    });

    // Focus effects
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        control.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
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