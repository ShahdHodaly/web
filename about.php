<?php
session_start();
$pageTitle = "About Us | Teddy Lap";
?>

<!DOCTYPE html>
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

    <!-- بداية قسم CSS -->
    <style>
        /* حاوية الصفحة */
        .about-container {
            padding: 50px 20px;
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* خلفية متحركة */
        .bg-shapes {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
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
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        .shape-3 { top: 50%; left: 40%; width: 250px; height: 250px; background: var(--primary); animation-delay: 10s; }

        @keyframes floatShapes {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50px, 30px) rotate(20deg); }
        }

        /* ترحيب */
        .about-hero {
            text-align: center;
            margin-bottom: 80px;
        }

        /* أنيميشن العنوان */
        .about-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--text-color);
            margin-bottom: 15px;

            /* إعدادات البداية */
            opacity: 0;
            transform: translateY(-30px);

            /* السرعة والسلاسة */
            transition: opacity 2s ease-out, transform 2s ease-out;
        }

        /* الحالة النهائية */
        .about-hero h1.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* اسم البراند المتحرك */
        .brand-name-animated {
            background: linear-gradient(90deg, #ff6b81, #c97a8d, #ff9a9e);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            animation: gradientMove 3s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% center; }
            50% { background-position: 100% center; }
            100% { background-position: 0% center; }
        }

        .about-hero p {
            font-size: 18px;
            color: var(--secondary-text);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* قسم الصورة والنص */
        .about-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 80px;
            flex-wrap: wrap;
        }

        .about-section.reverse {
            flex-direction: row-reverse;
        }

        .about-text {
            flex: 1;
            min-width: 300px;
        }

        .about-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: var(--text-color);
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .about-text h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 0;
            height: 3px;
            background-color: var(--pink);
            border-radius: 2px;
            transition: width 1s ease;
        }

        .anim-visible .about-text h2::after {
            width: 60px;
        }

        .about-text p {
            color: var(--secondary-text);
            line-height: 1.8;
            font-size: 15px;
        }

        /* صندوق الصورة */
        .about-image {
            flex: 1;
            min-width: 300px;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            background-color: transparent;
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.5s ease, filter 0.5s ease;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
        }

        .about-image:hover img {
            transform: scale(1.05) rotate(2deg);
        }

        /* بطاقات المميزات */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 80px;
        }

        .feature-card {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 20px var(--shadow);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background-color: var(--pink);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            color: #fff;
            transition: transform 0.5s ease;
        }

        .feature-card:hover .feature-icon {
            transform: rotateY(360deg);
        }

        .feature-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .feature-card p {
            font-size: 14px;
            color: var(--secondary-text);
            line-height: 1.6;
        }

        /* أنيميشن الظهور */
        .anim-hidden {
            opacity: 0;
            transition: all 1s ease-out;
        }

        .fade-up { transform: translateY(80px); }
        .fade-left { transform: translateX(-80px); }
        .fade-right { transform: translateX(80px); }
        .scale-in { transform: scale(0.8); }

        .anim-visible {
            opacity: 1;
            transform: translate(0) scale(1);
        }

        /* استجابة للجوال */
        @media (max-width: 768px) {
            .about-section, .about-section.reverse {
                flex-direction: column;
                text-align: center;
            }
            .about-text h2::after {
                left: 50%;
                transform: translateX(-50%);
            }
            .about-hero h1 { font-size: 36px; }
            .fade-left, .fade-right { transform: translateY(60px); }
        }
    </style>
</head>
<body>

<!-- أشكال الخلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
</div>

<!-- تضمين النافبار -->
<?php
if (file_exists('navbar.php')) {
    include 'navbar.php';
}
?>

<!-- محتوى صفحة About -->
<div class="about-container">

    <!-- قسم الترحيب -->
    <div class="about-hero">
        <h1>About <span class="brand-name-animated">Teddy Lap</span></h1>

        <p class="anim-hidden fade-up" style="transition-delay: 0.5s;">Where imagination comes to life. We are dedicated to bringing joy and creativity to children through our carefully curated collection of toys.</p>
    </div>

    <!-- قسم قصتنا -->
    <div class="about-section anim-hidden">
        <div class="about-text fade-left">
            <h2>Our Story</h2>
            <p>
                There was always something missing in traditional toy stores.
                Rows of identical toys sitting on shelves — cute, soft, but never truly personal.

                We asked ourselves:
                What if a child could design their own teddy?
                What if a toy could reflect personality, creativity, and imagination?

                That’s how TeddyLab was born.
                Not as a store.
                But as a creative space — a lab of imagination.
            </p>
        </div>
        <div class="about-image fade-right">
            <img src="images/about1.png" alt="Our Story">
        </div>
    </div>

    <!-- قسم مهمتنا -->
    <div class="about-section reverse anim-hidden">
        <div class="about-text fade-right">
            <h2>Our Mission</h2>
            <p>
                Our mission is simple: to inspire creativity and foster learning through play.
                We handpick every item in our store to ensure safety, quality, and developmental value.
                We are committed to helping parents find the perfect toys that encourage their children to dream big and explore the world around them.
            </p>
        </div>
        <div class="about-image fade-left">
            <img src="images/about2.png" alt="Our Mission">
        </div>
    </div>

    <!-- بطاقات المميزات -->
    <div class="features-grid">
        <!-- بطاقة 1 -->
        <div class="feature-card anim-hidden scale-in" style="transition-delay: 0.1s;">
            <div class="feature-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3>Safe & Secure</h3>
            <p>All our products are made from non-toxic, child-friendly materials and meet the highest safety standards.</p>
        </div>

        <!-- بطاقة 2 -->
        <div class="feature-card anim-hidden scale-in" style="transition-delay: 0.3s;">
            <div class="feature-icon">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <h3>Creative Design</h3>
            <p>Our toys are designed to spark imagination and help children develop essential motor and cognitive skills.</p>
        </div>

        <!-- بطاقة 3 -->
        <div class="feature-card anim-hidden scale-in" style="transition-delay: 0.5s;">
            <div class="feature-icon">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <h3>Fast Delivery</h3>
            <p>We ensure that your gifts arrive on time. Enjoy fast and reliable shipping on all orders.</p>
        </div>
    </div>

</div>

<!-- بداية قسم JavaScript -->
<script>
    // تفعيل أنيميشن العنوان
    document.addEventListener("DOMContentLoaded", function() {
        const mainTitle = document.querySelector('.about-hero h1');
        if(mainTitle) {
            // نضيف كلاس 'visible' لبدء الأنيميشن
            setTimeout(() => {
                mainTitle.classList.add('visible');
            }, 100);
        }
    });

    // مراقب ظهور العناصر
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('anim-visible');
                entry.target.classList.remove('anim-hidden', 'fade-up', 'fade-left', 'fade-right', 'scale-in');
            }
        });
    }, {
        threshold: 0.15
    });

    // تطبيق المراقب على العناصر
    document.querySelectorAll('.anim-hidden').forEach(el => {
        observer.observe(el);
    });
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>