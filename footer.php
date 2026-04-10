<!-- بداية قسم CSS -->
<style>
    /* تنسيق حاوية الفوتر */
    .site-footer {
        background: transparent;
        border-top: 1px solid rgba(255, 107, 129, 0.1);
        padding: 40px 20px;
        margin-top: 50px;
    }
    /* تنسيق المحتوى الداخلي */
    .footer-content {
        max-width: 1100px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        text-align: center;
    }
    /* تنسيق شعار الفوتر */
    .footer-logo {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        color: var(--pink);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .footer-logo i {
        font-size: 20px;
    }
    /* تنسيق روابط الفوتر */
    .footer-links {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .footer-links a {
        color: var(--secondary-text);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
        position: relative;
    }
    /* تأثير المرور على الروابط */
    .footer-links a:hover {
        color: var(--pink);
    }
    /* الخط المتحرك تحت الرابط */
    .footer-links a::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--pink);
        transition: width 0.3s;
    }
    .footer-links a:hover::after {
        width: 100%;
    }
    /* تنسيق حقوق النشر */
    .copyright {
        font-size: 13px;
        color: var(--secondary-text);
        opacity: 0.8;
    }

    /* استجابة للجوال */
    @media (max-width: 600px) {
        .footer-links { flex-direction: column; gap: 15px; }
    }
</style>

<!-- بداية قسم HTML (الفوتر) -->
<footer class="site-footer">
    <div class="footer-content">
        <!-- الشعار -->
        <div class="footer-logo">
            <i class="fa-solid fa-paw"></i> Teddy Lap
        </div>

        <!-- الروابط السريعة -->
        <div class="footer-links">
            <a href="faq.php">FAQ</a>
            <a href="terms.php">Terms of Service</a>
            <a href="privacy.php">Privacy Policy</a>
        </div>

        <!-- حقوق الملكية -->
        <p class="copyright">&copy; 2026 TeddyLab. All Rights Reserved.</p>
    </div>
</footer>