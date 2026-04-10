<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- ========== LEFT SIDEBAR (static, same on all admin pages) ========== -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2><i class="fa-solid fa-crown" style="font-size: 24px; margin-right: 6px;"></i> Admin</h2>
        <p>Dashboard · Teddy control</p>
    </div>
    <!-- Profile Link -->
    <div class="profile-link-item" style="margin-bottom: 15px; padding: 0 24px;">
        <a href="profile-admin.php" class="<?= $current_page == 'profile.php' ? 'active-link' : '' ?>" style="display: flex; align-items: center; gap: 14px; padding: 12px 18px; border-radius: 40px; color: var(--nav-text); font-weight: 500;">
            <i class="fa-solid fa-user-circle" style="width: 24px; font-size: 1.2rem;"></i>
            My Profile
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="product-admin.php" class="<?= $current_page == 'product-admin.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-box"></i> Products
                </a>
            </li>
            <li>
                <a href="orders.php" class="<?= $current_page == 'orders.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-truck"></i> Orders
                </a>
            </li>
            <li>
                <a href="users.php" class="<?= $current_page == 'users.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-users"></i> Users
                </a>
            </li>
            <li>
                <a href="custom-teddies.php" class="<?= $current_page == 'custom-teddies.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-paint-brush"></i> Custom Teddies
                </a>
            </li>
            <li>
                <a href="reviews.php" class="<?= $current_page == 'reviews.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-star"></i> Reviews
                </a>
            </li>
            <li>
                <a href="gallery.php" class="<?= $current_page == 'gallery.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-image"></i> Gallery
                </a>
            </li>
            <li>
                <a href="messages.php" class="<?= $current_page == 'messages.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-envelope"></i> Messages
                </a>
            </li>
            <li>
                <a href="coupons.php" class="<?= $current_page == 'coupons.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-ticket"></i> Coupons
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?= $current_page == 'settings.php' ? 'active-link' : '' ?>">
                    <i class="fa-solid fa-sliders"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <!-- Logout link -->
        <div class="logout-item" style="margin-bottom: 18px;">
            <a href="auth.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Dark mode toggle -->
        <div class="theme-toggle-sidebar">
            <span><i class="fa-regular fa-moon"></i> Dark mode</span>
            <label class="theme-toggle" style="transform: scale(0.9);">
                <input type="checkbox" id="themeSwitchSidebar">
                <div class="toggle-track">
                    <div class="toggle-circle"></div>
                </div>
            </label>
        </div>
    </div>
</aside>

<style>
    /* Profile Link in Sidebar */
    .profile-link-item a {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 18px;
        border-radius: 40px;
        color: var(--nav-text);
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .profile-link-item a:hover {
        background: var(--pink);
        color: #000;
        transform: translateX(6px);
    }

    .profile-link-item a.active-link {
        background: var(--primary);
        color: #fff;
    }

    .profile-link-item a i {
        width: 24px;
        font-size: 1.2rem;
        color: var(--primary);
    }

    .profile-link-item a:hover i,
    .profile-link-item a.active-link i {
        color: #fff;
    }

    /* ---------- SIDEBAR (fixed left) ---------- */
    .admin-sidebar {
        width: 280px;
        background-color: var(--nav-bg);
        backdrop-filter: blur(2px);
        box-shadow: 4px 0 15px var(--shadow);
        padding: 30px 0 20px 0;
        display: flex;
        flex-direction: column;
        transition: background-color 0.4s ease;
        border-radius: 0 30px 30px 0;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }

    .sidebar-header {
        padding: 0 24px 25px 24px;
        border-bottom: 1px solid rgba(128,128,128,0.2);
    }

    .sidebar-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        margin: 0 0 5px 0;
        background: linear-gradient(135deg, var(--primary), var(--lavender));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .sidebar-header p {
        color: var(--secondary-text);
        margin: 0;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .sidebar-nav {
        flex: 1;
        padding: 25px 16px 20px 16px;
    }

    .sidebar-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li {
        margin-bottom: 6px;
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 18px;
        border-radius: 40px;
        color: var(--nav-text);
        font-weight: 500;
        font-size: 16px;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .sidebar-nav a i {
        width: 24px;
        font-size: 1.2rem;
        color: var(--primary);
        transition: transform 0.25s ease;
    }

    .sidebar-nav a:hover {
        background: var(--pink);
        color: #000;
        transform: translateX(6px);
    }

    .sidebar-nav a:hover i {
        transform: scale(1.1);
        color: #000;
    }

    body.dark-mode .sidebar-nav a:hover {
        color: #111;
    }

    /* =========================================
       Active Page Indicator
    ========================================= */
    .sidebar-nav .active-link {
        background: var(--primary);
        color: #fff;
        font-weight: 600;
        transform: translateX(6px);
    }

    .sidebar-nav .active-link i {
        color: #fff;
    }

    /* Left indicator bar */
    .sidebar-nav .active-link::before {
        content: "";
        position: absolute;
        left: -16px;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 60%;
        background: var(--primary);
        border-radius: 10px;
        box-shadow: 0 2px 8px var(--primary);
    }

    /* =========================================
       Smooth hover background animation
    ========================================= */
    .sidebar-nav a::after {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 0%;
        height: 100%;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 40px;
        transition: width 0.3s ease;
        z-index: -1;
    }

    .sidebar-nav a:hover::after {
        width: 100%;
    }

    /* Active link animation */
    .sidebar-nav .active-link::after {
        display: none;
    }

    /* =========================================
       Sidebar Footer
    ========================================= */
    .sidebar-footer {
        padding: 20px 24px 15px 24px;
        border-top: 1px solid rgba(128,128,128,0.15);
    }

    .logout-item a {
        color: #d36b6b;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 18px;
        border-radius: 40px;
        transition: all 0.25s ease;
    }

    .logout-item a:hover {
        background: #ff6b6b;
        color: #fff !important;
        transform: translateX(6px);
    }

    .logout-item a:hover i {
        color: #fff;
        transform: scale(1.1);
    }

    /* للـ body */
    body {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* للسايدبار */
    .admin-sidebar {
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* لجميع العناصر اللي بتتغير ألوانها */
    .sidebar-nav a,
    .stat-card,
    .chart-card,
    .activity-card,
    .table-container,
    .filters-section,
    .search-input,
    .add-product-btn,
    .quick-action-btn,
    .page-item,
    .pagination,
    button {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }

    /* للعناصر اللي hover عندها حركة مختلفة */
    .sidebar-nav a:hover,
    button:hover,
    .page-item:hover {
        transition: all 0.2s ease; /* hover أسرع شوي */
    }

    /* =========================================
       Dark Mode Toggle
    ========================================= */
    .theme-toggle-sidebar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 12px;
        padding: 8px 12px;
        background: var(--card-bg);
        border-radius: 40px;
        box-shadow: 0 2px 8px var(--shadow);
        transition: all 0.3s ease;
    }

    .theme-toggle-sidebar:hover {
        box-shadow: 0 4px 12px var(--shadow);
        transform: translateY(-2px);
    }

    .theme-toggle-sidebar span {
        font-size: 14px;
        color: var(--text-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .theme-toggle-sidebar span i {
        color: var(--primary);
        transition: all 0.3s ease;
    }

    .theme-toggle-sidebar:hover span i {
        transform: rotate(360deg);
    }

    /* =========================================
       Toggle Switch Styles
    ========================================= */
    .theme-toggle {
        cursor: pointer;
        display: inline-block;
    }

    .theme-toggle input {
        display: none; /* إخفاء الشيك بوكس الأصلي */
    }

    .toggle-track {
        width: 55px;
        height: 28px;
        background: linear-gradient(135deg, #87CEEB, #fceabb);
        border-radius: 50px;
        position: relative;
        transition: all 0.5s ease;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .toggle-circle {
        width: 22px;
        height: 22px;
        background: #fff;
        border-radius: 50%;
        position: absolute;
        top: 3px;
        left: 3px;
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* 🌙 حالة الدارك مود (عند تفعيل الشيك بوكس) */
    .theme-toggle input:checked + .toggle-track {
        background: linear-gradient(135deg, #2c3e50, #4a69bd);
    }

    .theme-toggle input:checked + .toggle-track .toggle-circle {
        transform: translateX(27px);
        background: #dddddd;
        box-shadow: 0 0 10px rgba(128, 128, 128, 0.6);
    }

    .theme-toggle input:checked + .toggle-track::before {
        content: "✦";
        position: absolute;
        color: #fff;
        font-size: 10px;
        left: 10px;
        top: 9px;
        opacity: 0.9;
        animation: twinkle 1s infinite alternate;
    }

    @keyframes twinkle {
        from { opacity: 0.5; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
    }

    /* =========================================
       Scrollbar Styling
    ========================================= */
    .admin-sidebar::-webkit-scrollbar {
        width: 5px;
    }

    .admin-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .admin-sidebar::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
    }

    .admin-sidebar::-webkit-scrollbar-thumb:hover {
        background: var(--pink);
    }

    /* =========================================
       Dark Mode Adjustments
    ========================================= */
    body.dark-mode .sidebar-nav .active-link {
        background: var(--primary);
        color: #fff;
    }

    body.dark-mode .sidebar-nav .active-link::before {
        background: var(--primary);
        box-shadow: 0 2px 10px var(--primary);
    }

    body.dark-mode .logout-item a:hover {
        background: #ff6b6b;
        color: #fff !important;
    }

    /* =========================================
       Responsive
    ========================================= */
    @media (max-width: 800px) {
        .admin-sidebar {
            width: 100%;
            height: auto;
            border-radius: 0;
            position: relative;
        }

        .sidebar-nav .active-link::before {
            left: -8px;
        }
    }
</style>