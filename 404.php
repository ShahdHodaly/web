<?php
http_response_code(404); // لإعلام المتصفحات ومحركات البحث أن الصفحة غير موجودة
$pageTitle = "Page Not Found | Teddy Lap";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- HTML: الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* خلفية متحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* تنسيق حاوية المحتوى */
        .error-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /*  تنسيق رقم 404 */
        .error-code {
            font-family: 'Playfair Display', serif;
            font-size: 180px;
            font-weight: 700;
            background: linear-gradient(45deg, #ff6b81, #ff9a9e, #a18cd1);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            line-height: 1;
            animation: gradientMove 5s ease infinite;
            text-shadow: 0 10px 30px rgba(255, 107, 129, 0.2);
        }

        @media (max-width: 768px) {
            .error-code { font-size: 100px; }
        }

        /* تنسيق النصوص */
        .error-title {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            color: var(--text-color);
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .error-text {
            font-size: 16px;
            color: var(--secondary-text);
            max-width: 400px;
            margin-bottom: 40px;
        }

        /*  أنيميشن الأيقونة */
        .teddy-icon {
            font-size: 60px;
            color: var(--pink);
            margin-bottom: 20px;
            animation: floatTeddy 3s ease-in-out infinite;
            display: inline-block;
        }

        @keyframes floatTeddy {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /*  زر العودة للرئيسية */
        .home-btn {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(255, 154, 158, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .home-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 154, 158, 0.5);
        }
    </style>
</head>
<body>

<!-- HTML: أشكال الخلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- PHP: شريط التنقل -->
<?php
if (file_exists('navbar.php')) {
    include 'navbar.php';
}
?>

<!-- HTML: محتوى صفحة الخطأ -->
<div class="error-container">
    <!-- أيقونة الدب -->
    <div class="teddy-icon">
        <i class="fa-solid fa-paw"></i>
    </div>

    <!-- رقم 404 -->
    <h1 class="error-code">404</h1>

    <!-- النصوص التوضيحية -->
    <h2 class="error-title">Oops! Lost in the Teddy World?</h2>
    <p class="error-text">The page you are looking for seems to have wandered off. Don't worry, we can help you find your way back!</p>

    <!-- زر العودة للرئيسية -->
    <a href="home.php" class="home-btn">
        <i class="fa-solid fa-house"></i> Back to Home
    </a>
</div>

</body>
</html>