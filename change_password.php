<?php
$pageTitle = "Change Password | Teddy Lap";
// متغير لرسالة التنبيه
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // جلب البيانات المرسلة
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // كلمة المرور الحالية الوهمية (للتجربة)
    $dummy_current_pass = "123456";

    // التحقق من الحقول الفارغة
    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $message = "<div class='alert error'>Please fill in all fields.</div>";
        // التحقق من صحة كلمة المرور الحالية
    } elseif ($current_pass !== $dummy_current_pass) {
        $message = "<div class='alert error'>Current password is incorrect.</div>";
        // التحقق من طول كلمة المرور الجديدة
    } elseif (strlen($new_pass) < 6) {
        $message = "<div class='alert error'>New password must be at least 6 characters.</div>";
        // التحقق من تطابق كلمة المرور الجديدة
    } elseif ($new_pass !== $confirm_pass) {
        $message = "<div class='alert error'>New passwords do not match.</div>";
    } else {
        //   تحديث كلمة المرور في قاعدة البيانات
        $message = "<div class='alert success'>Password updated successfully! ✅</div>";
        // مسح القيم بعد النجاح
        $_POST = array();
    }
}
?>

<!DOCTYPE html>
<!-- --- بداية قسم HTML --- -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- ملف الستايل العام -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- --- بداية قسم CSS --- -->
    <style>
        /* ستايلات الصفحة */
        /* خلفية متحركة */
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        /* حركة الأشكال */
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* حاوية تغيير كلمة المرور */
        .change-pass-container {
            padding: 120px 20px 50px;
            max-width: 500px;
            margin: 0 auto;
        }

        /* بطاقة النموذج */
        .form-card {
            background: var(--card-bg);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 40px var(--shadow);
            position: relative;
            overflow: hidden;
        }

        /* رابط العودة */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary-text);
            text-decoration: none;
            margin-bottom: 25px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .back-link:hover { color: var(--pink); }

        /* عنوان النموذج */
        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--text-color);
            text-align: center;
            margin-bottom: 10px;
        }
        .form-subtitle {
            text-align: center;
            color: var(--secondary-text);
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* حقول الإدخال */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; color: var(--text-color); font-weight: 500; }

        .input-wrapper {
            position: relative;
        }
        /* أيقونات الحقول */
        .input-wrapper i {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: #ccc;
        }
        .form-input {
            width: 100%; padding: 12px 15px 12px 45px;
            border: 2px solid #eee; border-radius: 15px;
            font-family: 'Poppins', sans-serif; font-size: 15px;
            color: var(--text-color); background: var(--bg-color);
            transition: border-color 0.3s; box-sizing: border-box;
        }
        .form-input:focus { outline: none; border-color: var(--pink); }

        /* زر الإرسال */
        .submit-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff; border: none; border-radius: 50px;
            font-weight: bold; font-size: 16px; cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 10px;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(255, 154, 158, 0.4); }

        /* التنبيهات */
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .error { background-color: #ffe6e6; color: #d8000c; border: 1px solid #d8000c; }
        .success { background-color: #dff0d8; color: #4f8a10; border: 1px solid #4f8a10; }
        /* تنسيقات الوضع الداكن للتنبيهات */
        body.dark-mode .error { background-color: #3a2f2f; color: #ff6b6b; border-color: #ff6b6b; }
        body.dark-mode .success { background-color: #2f3a2f; color: #81c784; border-color: #81c784; }

    </style>
</head>
<body>

<!-- خلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- شريط التنقل -->
<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<!-- محتوى الصفحة -->
<div class="change-pass-container">
    <div class="form-card">
        <a href="profile.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>

        <h2 class="form-title">Change Password</h2>
        <p class="form-subtitle">Ensure your account is using a strong password.</p>

        <!-- عرض رسالة التنبيه -->
        <?php echo $message; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Current Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="current_pass" class="form-input" placeholder="Enter current password" required>
                </div>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-key"></i>
                    <input type="password" name="new_pass" class="form-input" placeholder="Enter new password" required>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-check-double"></i>
                    <input type="password" name="confirm_pass" class="form-input" placeholder="Confirm new password" required>
                </div>
            </div>

            <!-- زر التحديث -->
            <button type="submit" class="submit-btn">Update Password</button>
        </form>
    </div>
</div>
<!-- تضمين ذيل الصفحة -->
<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>