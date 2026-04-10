<!-- بداية قسم HTML (شريط التنقل) -->
<nav class="navbar">

    <!-- الجزء اليسار (البروفايل وزر الدارك مود) -->
    <div class="nav-left">
        <a href="profile.php">
            <i class="fa-solid fa-user profile-icon"></i>
        </a>

        <!-- زر تبديل الوضع الليلي -->
        <label class="theme-toggle">
            <input type="checkbox" id="themeSwitch">
            <div class="toggle-track">
                <div class="toggle-circle"></div>
            </div>
        </label>
    </div>

    <!-- الجزء الوسط (الروابط) -->
    <ul class="nav-center">
        <li><a href="home.php"><i class="fa-solid fa-paw"></i> Home</a></li>
        <li><a href="shop.php">Shop</a></li>
        <li><a href="customize.php">Customize</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>

        <!-- رابط البحث -->
        <li><a href="search.php"><i class="fa-solid fa-magnifying-glass"></i></a></li>

        <!-- سلة التسوق مع العداد -->
        <li class="cart-item">
            <a href="cart.php">
                <i class="fa-solid fa-cart-shopping"></i>
                <span class="cart-count" id="cartCount">0</span>
            </a>
        </li>
    </ul>

</nav>

<!-- بداية قسم CSS (ستايل السلة) -->
<style>
    /* تنسيق أيقونة السلة مع العداد */
    .cart-item {
        position: relative;
    }

    /* تنسيق دائرة العداد */
    .cart-count {
        position: absolute;
        top: -8px;
        right: -12px;
        background-color: #ff6b81;
        color: white;
        font-size: 11px;
        font-weight: bold;
        min-width: 18px;
        height: 18px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: transform 0.2s ease;
        border: 2px solid var(--card-bg);
    }

    /* تأثير المرور على العداد */
    .cart-item:hover .cart-count {
        transform: scale(1.1);
        background-color: #ff4f6b;
    }

    /* تنسيق خاص للدارك مود */
    body.dark-mode .cart-count {
        border-color: #222;
    }

    /* إخفاء العداد إذا كان صفر */
    .cart-count.hide {
        display: none;
    }
</style>

<!-- زر الدردشة العائم (HTML) -->
<a href="chatbot.php" class="chat-float-btn" title="محادثة مع البوت">
    <i class="fa-solid fa-comment-dots"></i>
</a>

<!-- بداية قسم CSS (ستايل زر الدردشة) -->
<style>
    /* تنسيق الزر العائم */
    .chat-float-btn {
        position: fixed;
        bottom: 25px;
        left: 25px;
        background-color: var(--primary-color, #ff6b81);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        z-index: 999;
        text-decoration: none;
        border: 2px solid var(--card-bg, white);
    }

    /* تأثير الهوفر */
    .chat-float-btn:hover {
        background-color: #ff4f6b;
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 8px 20px rgba(255, 75, 100, 0.4);
    }

    /* تعديل اللون في الوضع الليلي */
    body.dark-mode .chat-float-btn {
        background-color: #bb5c6e;
        border-color: #222;
        box-shadow: 0 6px 16px rgba(0,0,0,0.6);
    }

    /* هوفر الوضع الليلي */
    body.dark-mode .chat-float-btn:hover {
        background-color: #d46b7e;
    }

    /* للأجهزة الصغيرة */
    @media (max-width: 768px) {
        .chat-float-btn {
            bottom: 15px;
            left: 15px;
            width: 50px;
            height: 50px;
            font-size: 24px;
        }
    }
</style>

<!-- بداية قسم JavaScript -->
<script>
    // دالة تحديث عداد السلة
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
        const cartCount = document.getElementById('cartCount');

        // حساب مجموع الكميات
        let totalItems = 0;
        for (let id in cart) {
            totalItems += cart[id];
        }

        // تحديث العداد
        cartCount.textContent = totalItems;

        // إخفاء العداد إذا كان 0
        if (totalItems === 0) {
            cartCount.classList.add('hide');
        } else {
            cartCount.classList.remove('hide');
        }
    }

    // تحديث العداد عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', updateCartCount);

    // الاستماع لتغييرات السلة
    window.addEventListener('storage', function(e) {
        if (e.key === 'teddy_cart') {
            updateCartCount();
        }
    });

    // تحديث العداد كل ثانية
    setInterval(updateCartCount, 1000);

    // نظام الدارك مود
    const themeSwitch = document.getElementById("themeSwitch");

    // دالة تطبيق الثيم
    function applyTheme(isDark) {
        if (isDark) {
            document.body.classList.add("dark-mode");
            if(themeSwitch) themeSwitch.checked = true;
        } else {
            document.body.classList.remove("dark-mode");
            if(themeSwitch) themeSwitch.checked = false;
        }
    }

    // تطبيق الثيم المحفوظ
    document.addEventListener("DOMContentLoaded", () => {
        const savedTheme = localStorage.getItem("theme");
        if (savedTheme === "dark") {
            applyTheme(true);
        } else {
            applyTheme(false);
        }
    });

    // تغيير الثيم عند الضغط
    if (themeSwitch) {
        themeSwitch.addEventListener("change", function() {
            const isDark = this.checked;
            applyTheme(isDark);
            if (isDark) {
                localStorage.setItem("theme", "dark");
            } else {
                localStorage.setItem("theme", "light");
            }
        });
    }
</script>