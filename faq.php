<?php
$pageTitle = "FAQ | Teddy Lap";
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
        /* CSS*/

        /* خلفية متحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }

        /* حركة الطفو */
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* حاوية المحتوى */
        .content-container { padding: 120px 20px 50px; max-width: 900px; margin: 0 auto; }

        /* رأس الصفحة والعنوان */
        .page-header { text-align: center; margin-bottom: 50px; opacity: 0; animation: fadeDown 0.8s forwards; }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 42px; color: var(--pink); margin-bottom: 10px; }
        .page-header p { color: var(--secondary-text); }

        /* حركة الظهور */
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* بطاقة السؤال (الأكورديون) */
        .faq-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px var(--shadow);
            transition: transform 0.3s;
            cursor: pointer;
        }
        .faq-card:hover { transform: translateY(-3px); }

        /* عنوان السؤال داخل البطاقة */
        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-color);
            font-size: 17px;
        }
        .faq-question i { color: var(--pink); transition: transform 0.3s; }

        /* تدوير السهم عند الفتح */
        .faq-card.active .faq-question i { transform: rotate(180deg); }

        /* منطقة الإجابة */
        .faq-answer {
            margin-top: 0;
            max-height: 0;
            overflow: hidden;
            color: var(--secondary-text);
            font-size: 15px;
            line-height: 1.6;
            transition: all 0.4s ease;
        }

        /* إظهار الإجابة */
        .faq-card.active .faq-answer { margin-top: 15px; max-height: 200px; padding-top: 15px; border-top: 1px dashed #eee; }
        body.dark-mode .faq-card.active .faq-answer { border-top-color: #333; }

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

    <!-- عنوان القسم -->
    <div class="page-header">
        <h1>Frequently Asked Questions</h1>
        <p>Got questions? We've got answers.</p>
    </div>

    <!-- قائمة الأسئلة -->
    <div class="faq-list">

        <!-- سؤال 1 -->
        <div class="faq-card" onclick="toggleFaq(this)">
            <div class="faq-question">
                <span>How long does shipping take?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Standard shipping typically takes 5-7 business days. Express shipping options are available at checkout for faster delivery (2-3 business days). Customized teddies may require an additional 1-2 days for preparation.
            </div>
        </div>

        <!-- سؤال 2 -->
        <div class="faq-card" onclick="toggleFaq(this)">
            <div class="faq-question">
                <span>Can I return a customized teddy?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Due to the personalized nature of customized teddies, we cannot accept returns unless the item arrives damaged or defective. Please contact our support team within 48 hours of delivery if there are any issues.
            </div>
        </div>

        <!-- سؤال 3 -->
        <div class="faq-card" onclick="toggleFaq(this)">
            <div class="faq-question">
                <span>How do I care for my teddy bear?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                We recommend surface washing with a damp cloth and mild soap. Avoid machine washing or drying to preserve the fabric softness and the integrity of any accessories or voice modules.
            </div>
        </div>

        <!-- سؤال 4 -->
        <div class="faq-card" onclick="toggleFaq(this)">
            <div class="faq-question">
                <span>What payment methods do you accept?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and Cash on Delivery (COD) in selected regions.
            </div>
        </div>
    </div>
</div>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>

<!-- JavaScript: تفاعل الأكورديون -->
<script>
    // دالة فتح وإغلاق الإجابات
    function toggleFaq(card) {
        // إغلاق أي بطاقة مفتوحة أخرى
        document.querySelectorAll('.faq-card').forEach(c => {
            if (c !== card) c.classList.remove('active');
        });
        // تبديل حالة البطاقة الحالية
        card.classList.toggle('active');
    }
</script>

</body>
</html>