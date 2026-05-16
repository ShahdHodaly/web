<?php
// navbar.php

// جيبي عدد الـ cart من DB مباشرة
$cartCount = 0;
if (!empty($_SESSION['logged_in']) && !empty($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/db.php';
        $pdo  = getDB();

        // عدد المنتجات العادية
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(ci.quantity), 0)
            FROM Cart c
            JOIN Cart_Items ci ON c.cart_id = ci.cart_id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $regularCount = (int) $stmt->fetchColumn();

        // عدد الدببة المخصصة (مجموع الكميات وليس عدد الصفوف)
        try {
            $stmt2 = $pdo->prepare("
                SELECT COALESCE(SUM(quantity), COUNT(*)) 
                FROM custom_teddies
                WHERE user_id = ? AND is_saved = FALSE
            ");
            $stmt2->execute([$_SESSION['user_id']]);
            $customCount = (int) $stmt2->fetchColumn();
        } catch (Exception $e) {
            // لو عمود quantity ما كان موجود بأي سبب، نحسب بعدد الصفوف
            $stmt2 = $pdo->prepare("
                SELECT COUNT(*) FROM custom_teddies
                WHERE user_id = ? AND is_saved = FALSE
            ");
            $stmt2->execute([$_SESSION['user_id']]);
            $customCount = (int) $stmt2->fetchColumn();
        }

        $cartCount = $regularCount + $customCount;
    } catch (Exception $e) {
        $cartCount = 0;
    }
}
?>


<!-- بداية قسم HTML (شريط التنقل) -->
<nav class="navbar">

    <!-- الجزء اليسار (البروفايل وزر الدارك مود) -->
    <div class="nav-left">
        <a href="<?= !empty($_SESSION['logged_in']) ? 'profile.php' : 'auth.php' ?>">
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
                <span class="cart-count <?= $cartCount === 0 ? 'hide' : '' ?>" id="cartCount">
                    <?= $cartCount ?>
                </span>
            </a>
        </li>
    </ul>

</nav>

<!-- بداية قسم CSS (ستايل السلة) -->
<style>
    .cart-item {
        position: relative;
    }

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

    .cart-item:hover .cart-count {
        transform: scale(1.1);
        background-color: #ff4f6b;
    }

    body.dark-mode .cart-count {
        border-color: #222;
    }

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

    .chat-float-btn:hover {
        background-color: #ff4f6b;
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 8px 20px rgba(255, 75, 100, 0.4);
    }

    body.dark-mode .chat-float-btn {
        background-color: #bb5c6e;
        border-color: #222;
        box-shadow: 0 6px 16px rgba(0,0,0,0.6);
    }

    body.dark-mode .chat-float-btn:hover {
        background-color: #d46b7e;
    }

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
    // نظام الدارك مود
    const themeSwitch = document.getElementById("themeSwitch");

    function applyTheme(isDark) {
        if (isDark) {
            document.body.classList.add("dark-mode");
            if(themeSwitch) themeSwitch.checked = true;
        } else {
            document.body.classList.remove("dark-mode");
            if(themeSwitch) themeSwitch.checked = false;
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const savedTheme = localStorage.getItem("theme");
        if (savedTheme === "dark") {
            applyTheme(true);
        } else {
            applyTheme(false);
        }
    });

    if (themeSwitch) {
        themeSwitch.addEventListener("change", function() {
            const isDark = this.checked;
            applyTheme(isDark);
            localStorage.setItem("theme", isDark ? "dark" : "light");
        });
    }
</script>