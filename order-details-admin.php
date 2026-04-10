<?php
// order-details.php
session_start();

// تضمين مصفوفة الطلبات
require_once 'orders-array.php';

// الحصول على ID الطلب من الرابط
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// التحقق من وجود الطلب
if (!isset($orders[$order_id])) {
    $_SESSION['error'] = 'Order not found';
    header("Location: orders.php");
    exit;
}

$order = $orders[$order_id];
$pageTitle = "Order " . $order['order_number'] . " | Teddy Shop";
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
        .admin-wrapper { display: flex; align-items: flex-start; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); box-sizing: border-box; }

        .order-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 1000px;
            margin: 0 auto;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .order-title h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        .order-title p {
            color: var(--secondary-text);
        }
        .order-status {
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-completed { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-processing { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .status-pending { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .status-shipped { background: rgba(156, 39, 176, 0.2); color: #9C27B0; }
        .status-delivered { background: rgba(0, 150, 136, 0.2); color: #009688; }
        .status-cancelled { background: rgba(244, 67, 54, 0.2); color: #F44336; }

        /* Gift Section */
        .gift-section {
            background: linear-gradient(135deg, rgba(255, 107, 129, 0.1), rgba(255, 154, 158, 0.1));
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 107, 129, 0.3);
        }
        .gift-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        .gift-header i {
            font-size: 28px;
            color: #ff6b81;
        }
        .gift-header h3 {
            margin: 0;
            font-size: 18px;
            color: #ff6b81;
        }
        .gift-box-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .gift-message-box {
            background: var(--card-bg);
            padding: 15px;
            border-radius: 15px;
            margin-top: 10px;
            border-left: 4px solid #ff6b81;
        }
        .gift-message-box p {
            margin: 0;
            font-style: italic;
            color: var(--text-color);
        }
        .gift-message-box i {
            color: #ff6b81;
            margin-right: 8px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
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
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        /* Products Table */
        .products-table-container {
            margin: 25px 0;
            overflow-x: auto;
        }
        .order-products-table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-products-table th {
            text-align: left;
            padding: 12px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .order-products-table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
        }
        .product-img {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
        }

        /* Order Summary */
        .order-summary {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-top: 20px;
            text-align: right;
        }
        .summary-row {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            padding: 8px 0;
        }
        .summary-total {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            border-top: 2px solid var(--pink);
            margin-top: 10px;
            padding-top: 15px;
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
        .btn-print {
            background: #4CAF50;
            color: white;
        }
        .btn-print:hover {
            background: #45a049;
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

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="order-container">
            <!-- Order Header -->
            <div class="order-header">
                <div class="order-title">
                    <h1>Order #<?= htmlspecialchars($order['order_number']) ?></h1>
                    <p><i class="fa-regular fa-calendar"></i> Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['date'])) ?></p>
                </div>
                <div class="order-status status-<?= $order['status'] ?>">
                    <i class="fa-solid fa-<?= $order['status'] == 'completed' ? 'check-circle' : ($order['status'] == 'processing' ? 'spinner' : ($order['status'] == 'pending' ? 'clock' : ($order['status'] == 'shipped' ? 'truck' : 'times-circle'))) ?>"></i>
                    <?= ucfirst($order['status']) ?>
                </div>
            </div>

            <!-- Gift Section (إذا كان الطلب هدية) -->
            <?php if (isset($order['is_gift']) && $order['is_gift'] === true): ?>
                <div class="gift-section">
                    <div class="gift-header">
                        <i class="fa-solid fa-gift"></i>
                        <h3> This is a Gift Order </h3>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center;">
                        <div style="text-align: center;">
                            <img src="images/<?= $order['gift_box'] ?>" class="gift-box-img" alt="Gift Box">
                            <div><small style="color: var(--secondary-text);">Gift Box Style</small></div>
                        </div>
                        <div style="flex: 1;">
                            <div class="gift-message-box">
                                <p><i class="fa-regular fa-heart"></i> <strong>Gift Message:</strong></p>
                                <p style="margin-top: 8px;">"<?= htmlspecialchars($order['gift_message']) ?>"</p>
                            </div>
                            <div style="margin-top: 10px;">
                            <span class="badge" style="background: var(--pink); color: #000; padding: 5px 12px; border-radius: 30px;">
                                <i class="fa-solid fa-gift"></i> Gift Wrapping: +$<?= number_format($order['gift_wrap_price'], 2) ?>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Customer & Payment Info -->
            <div class="info-grid">
                <div class="info-card">
                    <h4><i class="fa-solid fa-user"></i> Customer Information</h4>
                    <p><?= htmlspecialchars($order['customer']) ?></p>
                    <small><?= htmlspecialchars($order['customer_email']) ?></small>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-credit-card"></i> Payment Method</h4>
                    <p><?= htmlspecialchars($order['payment_method']) ?></p>
                    <small>Status: <?= $order['status'] == 'cancelled' ? 'Failed' : 'Completed' ?></small>
                </div>
                <?php if (!empty($order['notes'])): ?>
                    <div class="info-card">
                        <h4><i class="fa-solid fa-note-sticky"></i> Order Notes</h4>
                        <p style="font-weight: normal;"><?= htmlspecialchars($order['notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Products Table -->
            <h4 style="margin: 20px 0 10px 0;"><i class="fa-solid fa-box"></i> Order Items</h4>
            <div class="products-table-container">
                <table class="order-products-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </thead>
                    <tbody>
                    <?php foreach($order['products'] as $product): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <img src="images/<?= $product['image'] ?>" class="product-img" alt="<?= $product['name'] ?>">
                                <div>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                </div>
                            </div>
                        <td class="product-price">$<?= number_format($product['price'], 2) ?>
                        <td style="text-align: center;"><?= $product['quantity'] ?>
                        <td class="product-price">$<?= number_format($product['price'] * $product['quantity'], 2) ?>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($order['total'] - ($order['gift_wrap_price'] ?? 0), 2) ?></span>
                </div>
                <?php if (isset($order['is_gift']) && $order['is_gift'] === true): ?>
                    <div class="summary-row">
                        <span>Gift Wrapping:</span>
                        <span>$<?= number_format($order['gift_wrap_price'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span>$<?= number_format($order['total'], 2) ?></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="edit-order.php?id=<?= $order_id ?>" class="btn-action btn-edit">
                    <i class="fa-solid fa-pen"></i> Edit Order
                </a>

                <a href="orders.php" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Orders
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    (function() {
        const themeSwitchMain = document.getElementById('themeSwitchSidebar');
        function applyTheme(isDark) {
            if (isDark) { document.body.classList.add('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = true; }
            else { document.body.classList.remove('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = false; }
        }
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true); else applyTheme(false);
        if (themeSwitchMain) themeSwitchMain.addEventListener('change', function(e) { applyTheme(this.checked); localStorage.setItem('theme', this.checked ? 'dark' : 'light'); });
    })();
</script>
</body>
</html>