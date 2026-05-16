<?php
// user-details.php
session_start();
require_once 'db.php';

$pdo = getDB();

// الحصول على ID المستخدم من الرابط
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب بيانات المستخدم من قاعدة البيانات
$stmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        u.role::text as role,
        u.status::text as status,
        u.phone,
        u.avatar,
        u.last_login,
        u.created_at as joined
    FROM users u
    WHERE u.user_id = ?
");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود المستخدم
if (!$userData) {
    $_SESSION['error'] = 'User not found';
    header("Location: users.php");
    exit;
}

// تنسيق بيانات المستخدم
$userDetails = [
        'id' => $userData['user_id'],
        'name' => $userData['name'],
        'email' => $userData['email'],
        'role' => $userData['role'],
        'status' => $userData['status'],
        'phone' => $userData['phone'],
        'avatar' => $userData['avatar'] ?: 'images/teddy4.png',
        'last_login' => $userData['last_login'],
        'joined' => $userData['joined']
];

$pageTitle = $userDetails['name'] . " | Teddy Shop";

// جلب طلبات المستخدم من قاعدة البيانات
$stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_number,
        o.status::text as status,
        o.payment_method::text as payment_method,
        o.total,
        o.created_at as date
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$userOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تنسيق الطلبات
$user_orders = [];
foreach ($userOrders as $order) {
    $user_orders[$order['order_id']] = [
            'order_number' => $order['order_number'],
            'date' => $order['date'],
            'total' => (float)$order['total'],
            'status' => $order['status'],
            'payment_method' => $order['payment_method']
    ];
}

// حساب الإحصائيات
$total_orders = count($user_orders);
$total_spent = array_sum(array_column($user_orders, 'total'));

// حساب عدد الأيام منذ الانضمام
$joinDate = new DateTime($userDetails['joined']);
$today = new DateTime();
$daysSinceJoined = $joinDate->diff($today)->days;

// آخر 5 طلبات فقط
$recent_orders = array_slice($user_orders, 0, 5, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ملفات CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        .user-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
        }

        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .user-title h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        .user-title p {
            color: var(--secondary-text);
        }
        .user-status {
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-active { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-inactive { background: rgba(244, 67, 54, 0.2); color: #F44336; }

        /* User Profile */
        .profile-section {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        .profile-avatar {
            text-align: center;
        }
        .profile-avatar img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid var(--pink);
            object-fit: cover;
        }
        .profile-avatar h3 {
            margin-top: 15px;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
        }
        .profile-avatar p {
            color: var(--secondary-text);
            font-size: 14px;
        }

        /* Info Grid */
        .info-grid {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-card {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(128,128,128,0.1);
        }
        .info-card h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-card p {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }
        .info-card small {
            color: var(--secondary-text);
            font-size: 12px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(128,128,128,0.1);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--pink);
        }
        .stat-card i {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
        }
        .stat-card .stat-label {
            font-size: 13px;
            color: var(--secondary-text);
        }

        /* Recent Orders */
        .recent-section {
            margin-top: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .recent-table {
            width: 100%;
            border-collapse: collapse;
        }
        .recent-table th {
            text-align: left;
            padding: 12px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .recent-table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
        }
        .recent-table tr:hover td {
            background-color: rgba(248, 187, 208, 0.1);
        }
        .status-badge-sm {
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .status-completed { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-processing { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .status-pending { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .status-shipped { background: rgba(156, 39, 176, 0.2); color: #9C27B0; }
        .status-cancelled { background: rgba(244, 67, 54, 0.2); color: #F44336; }

        .no-orders {
            text-align: center;
            padding: 40px;
            color: var(--secondary-text);
        }
        .no-orders i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            opacity: 0.5;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-edit {
            background: var(--lavender);
            color: #000;
        }
        .btn-edit:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }
        .btn-back {
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 1px solid rgba(128,128,128,0.2);
        }
        .btn-back:hover {
            border-color: var(--pink);
            transform: translateY(-2px);
        }
        .btn-delete {
            background: #ff6b6b;
            color: white;
        }
        .btn-delete:hover {
            background: #ff4757;
            transform: translateY(-2px);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .profile-section { flex-direction: column; align-items: center; text-align: center; }
            .info-grid { width: 100%; }
            .stats-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="user-container">
            <!-- User Header -->
            <div class="user-header">
                <div class="user-title">
                    <h1><?= htmlspecialchars($userDetails['name'] ?? 'User') ?></h1>
                    <p><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($userDetails['email'] ?? '') ?></p>
                </div>
                <div class="user-status status-<?= $userDetails['status'] ?? 'active' ?>">
                    <i class="fa-solid fa-<?= ($userDetails['status'] ?? 'active') == 'active' ? 'circle' : 'circle-xmark' ?>" style="font-size: 10px;"></i>
                    <?= ucfirst($userDetails['status'] ?? 'Active') ?>
                </div>
            </div>

            <!-- Profile Section -->
            <div class="profile-section">
                <div class="profile-avatar">
                    <img src="<?= htmlspecialchars($userDetails['avatar'] ?? 'images/teddy4.png') ?>"
                         alt="<?= htmlspecialchars($userDetails['name'] ?? 'User') ?>"
                         onerror="this.src='images/teddy4.png'">
                    <h3><?= htmlspecialchars($userDetails['name'] ?? 'User') ?></h3>
                    <p><?= htmlspecialchars($userDetails['role'] ?? 'Customer') ?></p>
                </div>

                <div class="info-grid">
                    <div class="info-card">
                        <h4><i class="fa-regular fa-calendar"></i> Joined</h4>
                        <p><?= !empty($userDetails['joined']) ? date('F d, Y', strtotime($userDetails['joined'])) : 'Unknown' ?></p>
                        <small><?= !empty($userDetails['joined']) ? date('l', strtotime($userDetails['joined'])) : '' ?></small>
                    </div>
                    <div class="info-card">
                        <h4><i class="fa-regular fa-clock"></i> Last Login</h4>
                        <?php if (!empty($userDetails['last_login'])): ?>
                            <p><?= date('M d, Y', strtotime($userDetails['last_login'])) ?></p>
                            <small><?= date('h:i A', strtotime($userDetails['last_login'])) ?></small>
                        <?php else: ?>
                            <p>Never</p>
                            <small>No login recorded</small>
                        <?php endif; ?>
                    </div>
                    <div class="info-card">
                        <h4><i class="fa-solid fa-envelope"></i> Email</h4>
                        <p><?= htmlspecialchars($userDetails['email'] ?? '') ?></p>
                        <small>Primary email address</small>
                    </div>
                    <?php if (!empty($userDetails['phone'])): ?>
                        <div class="info-card">
                            <h4><i class="fa-solid fa-phone"></i> Phone</h4>
                            <p><?= htmlspecialchars($userDetails['phone']) ?></p>
                            <small>Contact number</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Cards - بيانات حقيقية من الطلبات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fa-solid fa-shopping-cart"></i>
                    <div class="stat-value"><?= $total_orders ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-dollar-sign"></i>
                    <div class="stat-value">$<?= number_format($total_spent, 2) ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-calendar-week"></i>
                    <div class="stat-value"><?= $daysSinceJoined ?> days</div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>

            <!-- Recent Orders - من قاعدة البيانات -->
            <div class="recent-section">
                <div class="section-title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Recent Orders
                </div>

                <?php if ($total_orders > 0): ?>
                    <table class="recent-table">
                        <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                        <?php foreach($recent_orders as $id => $order): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($order['date'])) ?></td>
                                <td class="product-price">$<?= number_format($order['total'], 2) ?></td>
                                <td>
                                    <span class="status-badge-sm status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order-details-admin.php?id=<?= $id ?>" style="color: var(--primary);">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($total_orders > 5): ?>
                        <div style="text-align: right; margin-top: 15px;">
                            <a href="orders.php?user=<?= $user_id ?>" style="color: var(--primary);">View all orders →</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fa-regular fa-rectangle-list"></i>
                        <p>No orders yet from this user</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="edit-user.php?id=<?= $user_id ?>" class="btn-action btn-edit">
                    <i class="fa-solid fa-pen"></i> Edit User
                </a>
                <button class="btn-action btn-delete" onclick="deleteUser(<?= $user_id ?>, '<?= addslashes($userDetails['name'] ?? 'User') ?>')">
                    <i class="fa-solid fa-trash"></i> Delete User
                </button>
                <a href="users.php" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    function showAdminConfirm(message, onConfirm) {
        const overlay = document.createElement('div');
        overlay.id = 'admin-confirm-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
        overlay.style.backdropFilter = 'blur(3px)';
        overlay.style.zIndex = '9998';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        const popup = document.createElement('div');
        popup.id = 'admin-confirm-popup';
        popup.style.backgroundColor = 'var(--card-bg, #ffffff)';
        popup.style.color = 'var(--text-color, #333)';
        popup.style.borderRadius = '28px';
        popup.style.padding = '28px 24px';
        popup.style.maxWidth = '420px';
        popup.style.width = '90%';
        popup.style.boxShadow = '0 25px 45px rgba(0,0,0,0.25)';
        popup.style.textAlign = 'center';
        popup.style.fontFamily = "'Poppins', sans-serif";
        popup.style.transform = 'scale(0.9)';
        popup.style.transition = 'transform 0.25s ease';
        popup.style.border = '1px solid var(--pink, #F8BBD0)';

        popup.innerHTML = `
            <div style="font-size: 58px; margin-bottom: 12px;">⚠️</div>
            <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 12px;">Are you sure?</h3>
            <p style="font-size: 16px; color: var(--secondary-text, #555); margin-bottom: 28px; line-height: 1.5;">${message}</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button id="confirm-cancel-btn" style="background: transparent; border: 2px solid var(--pink, #F8BBD0); padding: 10px 24px; border-radius: 40px; font-weight: 600; cursor: pointer; color: var(--text-color, #333); transition: all 0.2s;">Cancel</button>
                <button id="confirm-ok-btn" style="background: #d9534f; border: none; padding: 10px 28px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; box-shadow: 0 4px 8px rgba(217,83,79,0.3); transition: all 0.2s;">Delete</button>
            </div>
        `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        setTimeout(() => {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        }, 10);

        function closePopup() {
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            setTimeout(() => {
                if (overlay && overlay.parentNode) overlay.remove();
            }, 250);
        }

        function showSuccessToast() {
            const toast = document.createElement('div');
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-check-circle" style="font-size: 28px; color: #28a745;"></i>
                    <div><strong style="font-size: 18px;">User removed successfully!</strong></div>
                </div>
            `;
            toast.style.position = 'fixed';
            toast.style.top = '50%';
            toast.style.left = '50%';
            toast.style.transform = 'translate(-50%, -50%) scale(0.9)';
            toast.style.backgroundColor = 'var(--card-bg, #fff)';
            toast.style.color = 'var(--text-color, #333)';
            toast.style.padding = '18px 28px';
            toast.style.borderRadius = '60px';
            toast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
            toast.style.zIndex = '10000';
            toast.style.fontFamily = "'Poppins', sans-serif";
            toast.style.border = '2px solid #28a745';
            toast.style.backdropFilter = 'blur(12px)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1)';
            toast.style.fontWeight = '500';
            toast.style.textAlign = 'center';
            toast.style.minWidth = '280px';

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
            }, 2500);
        }

        const cancelBtn = popup.querySelector('#confirm-cancel-btn');
        const confirmBtn = popup.querySelector('#confirm-ok-btn');

        cancelBtn.addEventListener('click', () => {
            closePopup();
        });

        confirmBtn.addEventListener('click', async () => {
            if (onConfirm && typeof onConfirm === 'function') {
                await onConfirm();
            }
            closePopup();
            showSuccessToast();
            setTimeout(() => {
                window.location.href = 'users.php';
            }, 1500);
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePopup();
        });
    }

    async function deleteUserFromDB(userId) {
        try {
            const response = await fetch('delete-user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            });
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    function deleteUser(id, name) {
        showAdminConfirm(`Are you sure you want to delete user "${name}"? This action cannot be undone.`, async () => {
            const success = await deleteUserFromDB(id);
            if (!success) {
                const errorToast = document.createElement('div');
                errorToast.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fa-solid fa-exclamation-triangle" style="font-size: 28px; color: #ff4757;"></i>
                        <div><strong style="font-size: 18px;">Failed to delete user!</strong></div>
                    </div>
                `;
                errorToast.style.position = 'fixed';
                errorToast.style.top = '50%';
                errorToast.style.left = '50%';
                errorToast.style.transform = 'translate(-50%, -50%) scale(0.9)';
                errorToast.style.backgroundColor = 'var(--card-bg, #fff)';
                errorToast.style.color = 'var(--text-color, #333)';
                errorToast.style.padding = '18px 28px';
                errorToast.style.borderRadius = '60px';
                errorToast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
                errorToast.style.zIndex = '10000';
                errorToast.style.fontFamily = "'Poppins', sans-serif";
                errorToast.style.border = '2px solid #ff4757';
                errorToast.style.backdropFilter = 'blur(12px)';
                errorToast.style.opacity = '0';
                errorToast.style.transition = 'all 0.25s ease';
                errorToast.style.fontWeight = '500';
                errorToast.style.textAlign = 'center';
                errorToast.style.minWidth = '280px';

                document.body.appendChild(errorToast);

                setTimeout(() => {
                    errorToast.style.opacity = '1';
                    errorToast.style.transform = 'translate(-50%, -50%) scale(1)';
                }, 20);

                setTimeout(() => {
                    errorToast.style.opacity = '0';
                    errorToast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                    setTimeout(() => {
                        if (errorToast && errorToast.parentNode) errorToast.remove();
                    }, 250);
                }, 2500);
            }
        });
    }
</script>

<script>
    (function() {
        const themeSwitchMain = document.getElementById('themeSwitchSidebar');
        function applyTheme(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                if (themeSwitchMain) themeSwitchMain.checked = true;
            } else {
                document.body.classList.remove('dark-mode');
                if (themeSwitchMain) themeSwitchMain.checked = false;
            }
        }
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true);
        else applyTheme(false);
        if (themeSwitchMain) {
            themeSwitchMain.addEventListener('change', function(e) {
                applyTheme(this.checked);
                localStorage.setItem('theme', this.checked ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>