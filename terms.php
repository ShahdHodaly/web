<?php
session_start();
$pageTitle = "Terms of Service | Teddy Lap";
include 'products.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!--  HTML  -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /*  CSS*/

        /* ستايل خلفية الصفحة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }

        /* أشكال الخلفية المتحركة */
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }

        /* حركة طفو الأشكال */
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* حاوية المحتوى الرئيسية */
        .content-container { padding: 120px 20px 50px; max-width: 900px; margin: 0 auto; }

        /* ستايل رأس الصفحة والعنوان */
        .page-header { text-align: center; margin-bottom: 50px; opacity: 0; animation: fadeDown 0.8s forwards; }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: var(--pink);
            margin-bottom: 10px;
        }
        .page-header p { color: var(--secondary-text); font-size: 16px; }

        /* حركة ظهور العنوان */
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* بطاقة نص الشروط */
        .text-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px var(--shadow);
            line-height: 1.8;
            color: var(--secondary-text);
        }

        /* عناوين داخل البطاقة */
        .text-card h2 { color: var(--text-color); font-size: 22px; margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid var(--lavender); padding-bottom: 5px; display: inline-block; }

        /* الفقرات والقوائم */
        .text-card p { margin-bottom: 15px; text-align: justify; }
        .text-card ul { padding-left: 20px; margin-bottom: 15px; }
        .text-card ul li { margin-bottom: 8px; }
    </style>
</head>
<body>

<!-- HTML: أشكال الخلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<!-- HTML: حاوية المحتوى -->
<div class="content-container">

    <!-- رأس الصفحة مع العنوان -->
    <div class="page-header">
        <h1>Terms of Service</h1>
        <p>Please read these terms carefully before using our website.</p>
    </div>

    <!-- محتوى شروط الاستخدام -->
    <div class="text-card">
        <p>Welcome to Teddy Lap. By accessing or using our website, you agree to be bound by these Terms of Service.</p>

        <h2>1. Use of Our Services</h2>
        <p>You must be at least 18 years old to make a purchase. You agree to use our services only for lawful purposes and in accordance with these Terms.</p>

        <h2>2. Products and Pricing</h2>
        <p>We make every effort to display our products and pricing as accurately as possible. However, we do not guarantee that your computer's display of any color will be accurate. Prices are subject to change without notice.</p>

        <h2>3. Orders and Payment</h2>
        <p>We reserve the right to refuse any order you place with us. Payment must be received prior to acceptance of an order. We accept major credit cards and other payment methods listed at checkout.</p>

        <h2>4. Intellectual Property</h2>
        <p>All content included on this site, such as text, graphics, logos, images, and software, is the property of Teddy Lap and protected by international copyright laws.</p>
    </div>
</div>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>

</body>
</html>