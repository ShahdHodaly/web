<?php
$pageTitle = "Contact Us | Teddy Lap";
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
        .contact-container {
            padding: 50px 20px;
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        /* أشكال خلفية متحركة */
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            z-index: -1;
            animation: float 10s infinite ease-in-out alternate;
        }
        .shape-1 { top: -50px; left: -50px; width: 300px; height: 300px; background: var(--pink); animation-delay: 0s; }
        .shape-2 { bottom: -50px; right: -50px; width: 400px; height: 400px; background: var(--lavender); animation-delay: 2s; }
        .shape-3 { top: 40%; left: 50%; width: 200px; height: 200px; background: var(--primary); animation-delay: 4s; }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); }
            50% { transform: translate(30px, 50px) rotate(10deg) scale(1.1); }
            100% { transform: translate(-20px, 20px) rotate(-5deg) scale(0.9); }
        }

        /* العنوان */
        .page-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--text-color);
            margin-bottom: 15px;
            /* أنيميشن دخول للعنوان */
            opacity: 0;
            animation: fadeSlideDown 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .page-header p {
            color: var(--secondary-text);
            font-size: 16px;
            opacity: 0;
            animation: fadeSlideDown 1s 0.2s forwards;
        }

        /* تقسيم الصفحة */
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            align-items: stretch; /* لجعل البطاقتين بنفس الطول */
        }

        /* بطاقة المعلومات */
        .info-card {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow);
            backdrop-filter: blur(10px); /* تأثير زجاجي */
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px var(--shadow);
        }

        .info-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background-color: var(--pink);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(248, 187, 208, 0.4);
            transition: transform 0.3s ease;
        }

        .info-item:hover .info-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .info-text h4 {
            margin: 0 0 5px;
            font-size: 16px;
            color: var(--text-color);
        }

        .info-text p {
            margin: 0;
            font-size: 14px;
            color: var(--secondary-text);
            line-height: 1.6;
        }

        /* السوشيال ميديا */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid var(--shadow);
        }

        .social-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--pink);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 10px rgba(248, 187, 208, 0.4);
        }

        .social-btn:hover {
            transform: translateY(-5px) scale(1.2);
            background-color: var(--primary);
        }

        /* بطاقة الفورم */
        .form-card {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.3s ease;
        }

        .form-card:hover {
            transform: translateY(-5px);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
            font-size: 14px;
        }

        /* تعديل حقول الإدخال */
        .form-control {
            width: 100%;
            padding: 12px 20px;
            border-radius: 30px;
            border: 2px solid transparent; /* تعديل الحد */
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            box-shadow: inset 0 0 0 1px var(--lavender); /* حد داخلي ناعم */
        }

        .form-control:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3), inset 0 0 0 1px var(--primary); /* توهج خارجي */
            border-color: var(--primary);
            background-color: #fff;
        }

        textarea.form-control {
            border-radius: 20px;
            min-height: 120px;
            resize: vertical;
        }

        .submit-btn {
            background-color: var(--primary);
            color: #fff;
            padding: 12px 30px;
            border-radius: 30px;
            border: none;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(248, 187, 208, 0.4);
        }

        .submit-btn:hover {
            background-color: #333;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        /* تأثير الموجة عند الضغط */
        .submit-btn .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: scale(0);
            animation: rippleEffect 0.6s linear;
            pointer-events: none;
        }

        @keyframes rippleEffect {
            to { transform: scale(4); opacity: 0; }
        }

        /* أنيميشن العنوان */
        .brand-name-animated {
            background: linear-gradient(90deg, #ff6b81, #c97a8d, #ff9a9e);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            animation: gradientMove 3s ease infinite;
        }

        /* أنيميشن الظهور */
        @keyframes gradientMove {
            0% { background-position: 0% center; }
            50% { background-position: 100% center; }
            100% { background-position: 0% center; }
        }

        @keyframes fadeSlideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes popUp {
            0% { opacity: 0; transform: translateY(40px) scale(0.9); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .animate-on-scroll {
            opacity: 0;
        }
        .animate-on-scroll.visible {
            animation: popUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        /* استجابة للجوال */
        @media (max-width: 768px) {
            .contact-wrapper {
                grid-template-columns: 1fr;
            }
            .page-header h1 { font-size: 36px; }
            /* إخفاء الأشكال في الجوال لتقليل الاستهلاك */
            .bg-shape { display: none; }
        }
    </style>
</head>
<body>

<!-- تضمين شريط التنقل -->
<?php
if (file_exists('navbar.php')) {
    include 'navbar.php';
}
?>

<!-- محتوى صفحة التواصل -->
<div class="contact-container">
    <!-- أشكال الخلفية -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>

    <!-- عنوان الصفحة -->
    <div class="page-header">
        <h1>Get in <span class="brand-name-animated">Touch</span></h1>
        <p>Have a question or need help? Feel free to reach out to us!</p>
    </div>

    <!-- قسم المعلومات والفورم -->
    <div class="contact-wrapper">

        <!-- بطاقة معلومات التواصل -->
        <div class="info-card animate-on-scroll">
            <h3>Contact Information</h3>

            <div class="info-item">
                <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div class="info-text">
                    <h4>Our Store</h4>
                    <p>Palestine, Nablus</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                <div class="info-text">
                    <h4>Phone Number</h4>
                    <p>+972 59XXXXXXX</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                <div class="info-text">
                    <h4>Email Address</h4>
                    <p>teddylap2026@gmail.com</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="info-text">
                    <h4>Working Hours</h4>
                    <p>Sun - Thu: 9:00 AM - 5:00 PM</p>
                </div>
            </div>

            <!-- روابط السوشيال ميديا -->
            <div class="social-links">
                <a href="#" class="social-btn"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="social-btn"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="social-btn"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="social-btn"><i class="fa-brands fa-tiktok"></i></a>
            </div>
        </div>

        <!-- بطاقة نموذج الإرسال -->
        <div class="form-card animate-on-scroll">
            <form id="contactForm" onsubmit="sendMail(event)">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" class="form-control" placeholder="Enter your name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" class="form-control" placeholder="What is this about?">
                </div>

                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" class="form-control" placeholder="Write your message here..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>

    </div>
</div>

<!-- بداية قسم JavaScript -->
<script>
    // دالة إرسال النموذج مع التأثيرات
    function sendMail(e) {
        e.preventDefault();

        const btn = document.querySelector('.submit-btn');
        const originalText = btn.innerHTML;

        // تأثير الموجة (Ripple)
        const ripple = document.createElement('span');
        ripple.classList.add('ripple');
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = `${size}px`;
        ripple.style.left = `${e.clientX - rect.left - size/2}px`;
        ripple.style.top = `${e.clientY - rect.top - size/2}px`;
        btn.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);

        // عملية الإرسال
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
        btn.disabled = true;

        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Sent Successfully!';
            btn.style.backgroundColor = '#28a745';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.backgroundColor = '';
                btn.disabled = false;
                document.getElementById('contactForm').reset();
            }, 2000);
        }, 1500);
    }

    // تفعيل أنيميشن الظهور عند التمرير
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // لإيقاف المراقبة بعد الظهور
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
</script>

<!-- تضمين الفوتر -->
<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>