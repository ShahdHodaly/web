<?php
$pageTitle = "Order Success | Teddy Lap";
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
        /* أنماط خلفية الصفحة المتحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        /* حركة الأشكال العائمة */
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* حاوية المحتوى الرئيسية */
        .success-container {
            height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* صندوق رسالة النجاح */
        .success-box {
            background: var(--card-bg);
            padding: 50px;
            border-radius: 25px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            opacity: 0;
            animation: slideUp 0.6s forwards;
        }

        /* حركة الظهور للأعلى */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* دائرة أيقونة النجاح */
        .success-icon-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 20px rgba(255, 154, 158, 0.4);
        }

        /* أيقونة الصح داخل الدائرة */
        .success-icon-circle i {
            font-size: 50px;
            color: #fff;
        }

        /* عنوان النجاح */
        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        /* نص رسالة النجاح */
        .success-text {
            color: var(--secondary-text);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* تنسيق رقم الطلب */
        .order-number {
            font-weight: bold;
            color: var(--pink);
        }

        /* زر العودة للمتجر */
        .home-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary);
            color: #fff;
            padding: 15px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        /* تأثير التمرير على الزر */
        .home-btn:hover {
            background: var(--pink);
            transform: translateY(-2px);
        }

        /* أنيميشن الدب السفلي */
        .teddy-footer {
            font-size: 40px;
            margin-top: 30px;
            animation: bounce 2s infinite;
        }
        /* حركة القفز */
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
    </style>
</head>
<body>

<!-- خلفية متحركة -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- النافبار -->
<?php
// تضمين شريط التنقل
if (file_exists('navbar.php')) include 'navbar.php';
?>

<!-- المحتوى -->
<div class="success-container">
    <div class="success-box">
        <div class="success-icon-circle">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1 class="success-title">Order Confirmed!</h1>

        <p class="success-text">
            Thank you for your purchase. Your order has been placed successfully and is being processed.<br>
            <!-- عرض رقم طلب عشوائي -->
            Order Number: <span class="order-number">#<?php echo strtoupper(uniqid()); ?></span>
        </p>

        <a href="shop.php" class="home-btn">
            <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
        </a>

        <!-- أيقونة الدب السفلي -->
        <div class="teddy-footer">🧸</div>
    </div>
</div>

</body>
</html>