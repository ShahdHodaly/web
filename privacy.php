<?php
$pageTitle = "Privacy Policy | Teddy Lap";
include 'products.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!--  HTML -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* CSS */

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

        /* بطاقة نص السياسة */
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

        /* الفقرات */
        .text-card p { margin-bottom: 15px; text-align: justify; }
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
        <h1>Privacy Policy</h1>
        <p>Your privacy is important to us. Learn how we handle your data.</p>
    </div>

    <!-- محتوى سياسة الخصوصية -->
    <div class="text-card">
        <p>Your privacy is important to us. This policy outlines how we collect, use, and protect your personal information.</p>

        <h2>1. Information We Collect</h2>
        <p>We collect information you provide directly to us, such as when you create an account, make a purchase, or contact us for support. This may include your name, email address, shipping address, and payment information.</p>

        <h2>2. How We Use Your Information</h2>
        <p>We use the information we collect to process transactions, send you updates about your order, respond to your comments and questions, and improve our services. We do not sell your personal data to third parties.</p>

        <h2>3. Data Security</h2>
        <p>We implement a variety of security measures to maintain the safety of your personal information. All sensitive information is transmitted via Secure Socket Layer (SSL) technology.</p>

        <h2>4. Cookies</h2>
        <p>Our website uses cookies to enhance your browsing experience. You can choose to disable cookies through your browser settings, but this may affect the functionality of the site.</p>

        <h2>5. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at support@teddylap.com.</p>
    </div>
</div>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>

</body>
</html>