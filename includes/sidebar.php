<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);

// جلب معلومات المستخدم الحالي من الجلسة أو قاعدة البيانات
$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? 'Admin';
$user_avatar = $_SESSION['user_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user_name) . '&background=F8BBD0&color=000&size=40';

// إذا كانت الجلسة تحتوي على user_id، يمكن جلب الصورة من قاعدة البيانات
if (isset($_SESSION['user_id']) && empty($_SESSION['user_avatar'])) {
    require_once __DIR__ . '/../db.php';
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user && !empty($user['avatar'])) {
            $user_avatar = $user['avatar'];
            $_SESSION['user_avatar'] = $user_avatar;
        }
    } catch (Exception $e) {
        // تجاهل الأخطاء، استخدم الصورة الافتراضية
    }
}
?>
<!-- ========== LEFT SIDEBAR (static, same on all admin pages) ========== -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2><i class="fa-solid fa-crown" style="font-size: 24px; margin-right: 6px;"></i> Admin</h2>
        <p>Dashboard · Teddy control</p>
    </div>

    <!-- Profile Section with Avatar -->
    <div class="profile-section-sidebar">
        <div class="profile-avatar-small">
            <img src="<?= htmlspecialchars($user_avatar) ?>" alt="<?= htmlspecialchars($user_name) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=F8BBD0&color=000&size=40'">
            <div class="profile-info-small">
                <div class="profile-name-small"><?= htmlspecialchars($user_name) ?></div>
                <div class="profile-role-small"><?= htmlspecialchars($user_role) ?></div>
            </div>
        </div>
        <a href="profile-admin.php" class="profile-link-btn <?= $current_page == 'profile-admin.php' ? 'active-link' : '' ?>">
            <i class="fa-solid fa-user-circle"></i> My Profile
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
        <!-- Logout link with popup -->
        <div class="logout-item">
            <a href="javascript:void(0)" onclick="showLogoutConfirm()">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
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
    /* Profile Section in Sidebar */
    .profile-section-sidebar {
        padding: 20px 24px;
        border-bottom: 1px solid rgba(128,128,128,0.15);
        margin-bottom: 10px;
    }

    .profile-avatar-small {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
    }

    .profile-avatar-small img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--pink);
    }

    .profile-info-small {
        flex: 1;
    }

    .profile-name-small {
        font-weight: 600;
        color: var(--text-color);
        font-size: 14px;
    }

    .profile-role-small {
        font-size: 11px;
        color: var(--secondary-text);
    }

    .profile-link-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        border-radius: 40px;
        color: var(--nav-text);
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
        background: var(--card-bg);
        margin-top: 5px;
        text-decoration: none;
    }

    .profile-link-btn i {
        width: 20px;
        font-size: 1rem;
        color: var(--primary);
    }

    .profile-link-btn:hover {
        background: var(--pink);
        color: #000;
        transform: translateX(5px);
    }

    .profile-link-btn:hover i {
        color: #000;
    }

    .profile-link-btn.active-link {
        background: var(--primary);
        color: #fff;
    }

    .profile-link-btn.active-link i {
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
        padding: 10px 16px 20px 16px;
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
        font-size: 15px;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
        text-decoration: none;
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
        text-decoration: none;
        cursor: pointer;
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
        transition: all 0.2s ease;
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
        display: none;
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

    /* Popup Styles */
    .admin-confirm-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(3px);
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .admin-confirm-popup {
        background-color: var(--card-bg);
        color: var(--text-color);
        border-radius: 28px;
        padding: 28px 24px;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 25px 45px rgba(0, 0, 0, 0.25);
        text-align: center;
        font-family: 'Poppins', sans-serif;
        transform: scale(0.9);
        transition: transform 0.25s ease;
        border: 1px solid var(--pink);
    }

    .admin-confirm-popup .popup-icon {
        font-size: 58px;
        margin-bottom: 12px;
    }

    .admin-confirm-popup h3 {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .admin-confirm-popup p {
        font-size: 16px;
        color: var(--secondary-text);
        margin-bottom: 28px;
        line-height: 1.5;
    }

    .admin-confirm-popup .popup-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .admin-confirm-popup .btn-cancel {
        background: transparent;
        border: 2px solid var(--pink);
        padding: 10px 24px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        color: var(--text-color);
        transition: all 0.2s;
    }

    .admin-confirm-popup .btn-cancel:hover {
        background: var(--pink);
        color: #000;
        transform: translateY(-2px);
    }

    .admin-confirm-popup .btn-confirm {
        background: #d9534f;
        border: none;
        padding: 10px 28px;
        border-radius: 40px;
        font-weight: 600;
        color: white;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(217, 83, 79, 0.3);
        transition: all 0.2s;
    }

    .admin-confirm-popup .btn-confirm:hover {
        background: #c9302c;
        transform: translateY(-2px);
    }

    /* Toast for success message */
    .admin-success-toast {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.9);
        background-color: var(--card-bg);
        color: var(--text-color);
        padding: 18px 28px;
        border-radius: 60px;
        box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        font-family: 'Poppins', sans-serif;
        border: 2px solid #28a745;
        backdrop-filter: blur(12px);
        opacity: 0;
        transition: all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1);
        font-weight: 500;
        text-align: center;
        min-width: 280px;
        display: flex;
        align-items: center;
        gap: 12px;
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

        .profile-section-sidebar {
            padding: 15px 20px;
        }
    }
</style>

<script>
    // ========== Logout Popup Function ==========
    function showLogoutConfirm() {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'admin-confirm-overlay';

        // Create popup
        const popup = document.createElement('div');
        popup.className = 'admin-confirm-popup';

        popup.innerHTML = `
            <div class="popup-icon">🚪</div>
            <h3>Logout Confirmation</h3>
            <p>Are you sure you want to logout? You will need to login again to access the dashboard.</p>
            <div class="popup-buttons">
                <button class="btn-cancel" id="logoutCancelBtn">Cancel</button>
                <button class="btn-confirm" id="logoutConfirmBtn">Logout</button>
            </div>
        `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // Show animation
        setTimeout(() => {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        }, 10);

        // Close popup function
        function closePopup() {
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            setTimeout(() => {
                if (overlay && overlay.parentNode) overlay.remove();
            }, 250);
        }

        // Show success toast
        function showSuccessToast() {
            const toast = document.createElement('div');
            toast.className = 'admin-success-toast';
            toast.innerHTML = `
                <i class="fa-solid fa-check-circle" style="font-size: 28px; color: #28a745;"></i>
                <div>
                    <strong style="font-size: 18px;">Logged out successfully!</strong>
                    <div style="font-size: 13px;">Redirecting to login page...</div>
                </div>
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 20);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                setTimeout(() => {
                    if (toast && toast.parentNode) toast.remove();
                }, 250);
            }, 2000);
        }

        // Handle cancel button
        const cancelBtn = popup.querySelector('#logoutCancelBtn');
        cancelBtn.addEventListener('click', closePopup);

        // Handle confirm button
        const confirmBtn = popup.querySelector('#logoutConfirmBtn');
        confirmBtn.addEventListener('click', function() {
            closePopup();
            showSuccessToast();

            // Redirect to logout after toast
            setTimeout(() => {
                window.location.href = 'auth.php?logout=1';
            }, 1500);
        });

        // Close when clicking overlay
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closePopup();
        });
    }

    // ========== Dark Mode Toggle ==========
    (function() {
        const themeSwitch = document.getElementById('themeSwitchSidebar');
        if (!themeSwitch) return;

        function applyTheme(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                themeSwitch.checked = true;
            } else {
                document.body.classList.remove('dark-mode');
                themeSwitch.checked = false;
            }
        }

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            applyTheme(true);
        } else if (savedTheme === 'light') {
            applyTheme(false);
        } else {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(prefersDark);
        }

        themeSwitch.addEventListener('change', function(e) {
            const isDark = this.checked;
            applyTheme(isDark);
            localStorage.setItem('theme', isDark ? 'dark' : 'light');

            document.body.style.transition = 'background-color 0.3s ease, color 0.2s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        });
    })();
</script>