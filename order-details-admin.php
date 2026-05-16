<?php
// order-details-admin.php
session_start();
require_once 'db.php';

$pdo = getDB();

// الحصول على ID الطلب من الرابط
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب الطلب من قاعدة البيانات مع معلومات المستخدم
$stmt = $pdo->prepare("
    SELECT 
        o.order_id,
        o.order_number,
        o.user_id,
        o.status,
        o.payment_method,
        o.subtotal,
        o.discount_amount,
        o.gift_wrap_price,
        o.total,
        o.is_gift,
        o.gift_message,
        o.gift_box,
        o.notes,
        o.created_at,
        u.name as customer_name,
        u.email as customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$stmt->execute([$order_id]);
$orderData = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود الطلب
if (!$orderData) {
    $_SESSION['error'] = 'Order not found';
    header("Location: orders.php");
    exit;
}

// جلب عناصر الطلب مع معلومات المنتجات
$stmt = $pdo->prepare("
    SELECT 
        oi.item_id,
        oi.product_id,
        oi.quantity,
        oi.unit_price,
        oi.subtotal,
        p.name as product_name,
        p.image as product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تنسيق بيانات الطلب للعرض
$order = [
        'order_number' => $orderData['order_number'],
        'customer' => $orderData['customer_name'],
        'customer_email' => $orderData['customer_email'],
        'date' => $orderData['created_at'],
        'status' => $orderData['status'],
        'payment_method' => $orderData['payment_method'],
        'subtotal' => (float)$orderData['subtotal'],
        'total' => (float)$orderData['total'],
        'gift_wrap_price' => (float)$orderData['gift_wrap_price'],
        'is_gift' => filter_var($orderData['is_gift'], FILTER_VALIDATE_BOOLEAN),
        'gift_message' => $orderData['gift_message'],
        'gift_box' => $orderData['gift_box'],
        'notes' => $orderData['notes'],
        'products' => []
];

// تنسيق عناصر الطلب
foreach ($orderItems as $item) {
    $order['products'][] = [
            'name' => $item['product_name'],
            'price' => (float)$item['unit_price'],
            'quantity' => (int)$item['quantity'],
            'image' => $item['product_image'] ?: 'placeholder.png',
            'subtotal' => (float)$item['subtotal']
    ];
}

// ── استخراج أرقام الكوستوم من notes ──
$customIds = [];
$noteParts = explode(' | ', $orderData['notes'] ?? '');
foreach ($noteParts as $part) {
    if (strpos($part, 'custom:') === 0) {
        $ids = explode(',', substr($part, 7));
        $customIds = array_map('intval', array_filter($ids));
    }
}

// جلب بيانات الدببة المخصصة
$customItems = [];
if (!empty($customIds)) {
    $cPlaceholders = implode(',', array_fill(0, count($customIds), '?'));
    $stmtC = $pdo->prepare("
        SELECT custom_id, custom_name AS name, total_price, config_json, quantity
        FROM custom_teddies
        WHERE custom_id IN ($cPlaceholders)
    ");
    $stmtC->execute($customIds);
    $customItems = $stmtC->fetchAll(PDO::FETCH_ASSOC);
}

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

            <!-- Gift Section (إذا كان الطلب هدية فعلاً) -->
            <?php if ($order['is_gift'] && ((!empty($order['gift_box']) && $order['gift_box'] !== 'none') || !empty($order['gift_message']))): ?>
                <div class="gift-section">
                    <div class="gift-header">
                        <i class="fa-solid fa-gift"></i>
                        <h3>✨ This is a Gift Order ✨</h3>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;">

                        <?php
                        // عرض التغليف إذا كان موجود ومش "none"
                        if (!empty($order['gift_box']) && $order['gift_box'] !== 'none'):
                            $wrapNames = [
                                    'box'       => 'Classic Box',
                                    'teddywrap' => 'Teddy Wrap',
                                    'heartsbag' => 'Hearts Bag'
                            ];
                            $wrapName = $wrapNames[$order['gift_box']] ?? ucfirst($order['gift_box']);
                            ?>
                            <?php
                            // ── البحث التلقائي عن صورة التغليف ──
                            $boxVal = $order['gift_box'];
                            $possiblePaths = [
                                    "images/{$boxVal}.png",
                                    "images/gift-{$boxVal}.png",
                                    "images/" . str_replace('wrap', '-wrap', $boxVal) . ".png",
                                    "images/" . str_replace('bag', '-bag', $boxVal) . ".png",
                            ];
                            $wrapImg = '';
                            foreach ($possiblePaths as $path) {
                                if (file_exists($path)) {
                                    $wrapImg = $path;
                                    break;
                                }
                            }
                            ?>
                            <div style="text-align: center; background: var(--card-bg); padding: 15px 20px; border-radius: 15px; min-width: 120px;">
                                <?php if (!empty($wrapImg)): ?>
                                    <img src="<?= htmlspecialchars($wrapImg) ?>"
                                         style="width:80px; height:80px; object-fit:contain; margin-bottom:8px;"
                                         alt="<?= htmlspecialchars($wrapName) ?>">
                                <?php else: ?>
                                    <i class="fa-solid fa-box-open" style="font-size: 40px; color: #ff6b81; margin-bottom: 8px; display: block;"></i>
                                <?php endif; ?>

                                <div><strong style="color: var(--text-color);"><?= htmlspecialchars($wrapName) ?></strong></div>
                                <small style="color: var(--secondary-text); margin-top: 5px; display: block;">
                                    +$<?= number_format($order['gift_wrap_price'], 2) ?>
                                </small>
                            </div>
                        <?php endif; ?>

                        <div style="flex: 1;">
                            <?php if (!empty($order['gift_message'])): ?>
                                <div class="gift-message-box">
                                    <p style="margin:0 0 5px;"><i class="fa-regular fa-heart"></i> <strong>Gift Message:</strong></p>
                                    <p style="margin:0;">"<?= htmlspecialchars($order['gift_message']) ?>"</p>
                                </div>
                            <?php endif; ?>

                            <?php if (empty($order['gift_message']) && (empty($order['gift_box']) || $order['gift_box'] === 'none')): ?>
                                <p style="color: var(--secondary-text); margin: 0; font-style: italic;">No gift wrapping or message selected.</p>
                            <?php endif; ?>
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
                        <h4><i class="fa-solid fa-note-sticky"></i> Shipping / Notes</h4>
                        <?php
                        $displayNotes = $order['notes'];
                        // إخفاء جزء الكوستوم من العرض
                        $displayNotes = preg_replace('/\s*\|\s*custom:\d+(,\d+)*/', '', $displayNotes);
                        $noteDisplayParts = explode(' | ', $displayNotes);
                        ?>
                        <?php if (isset($noteDisplayParts[0])): ?>
                            <p><i class="fa-solid fa-user" style="color:var(--pink); width:16px;"></i> <?= htmlspecialchars($noteDisplayParts[0]) ?></p>
                        <?php endif; ?>
                        <?php if (isset($noteDisplayParts[1])): ?>
                            <p><i class="fa-solid fa-location-dot" style="color:var(--pink); width:16px;"></i> <?= htmlspecialchars($noteDisplayParts[1]) ?></p>
                        <?php endif; ?>
                        <?php if (isset($noteDisplayParts[2])): ?>
                            <p><i class="fa-solid fa-phone" style="color:var(--pink); width:16px;"></i> <?= htmlspecialchars($noteDisplayParts[2]) ?></p>
                        <?php endif; ?>
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
                                    <img src="<?= htmlspecialchars($product['image']) ?>" class="product-img" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='images/placeholder.png'">
                                    <div>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td class="product-price">$<?= number_format($product['price'], 2) ?></td>
                            <td style="text-align: center;"><?= $product['quantity'] ?></td>
                            <td class="product-price">$<?= number_format($product['price'] * $product['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php foreach ($customItems as $cItem):
                        $cfg = $cItem['config_json'] ? json_decode($cItem['config_json'], true) : null;
                        $cQty = (int)($cItem['quantity'] ?? 1);
                        $cPrice = (float)$cItem['total_price'];
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="position:relative; width:50px; height:50px; flex-shrink:0;">
                                        <?php if ($cfg): ?>
                                            <img src="<?= htmlspecialchars($cfg['color']['img'] ?? 'images/brown.png') ?>"
                                                 style="position:absolute; width:100%; height:100%; object-fit:contain; z-index:1;" alt="base">
                                            <?php if (!empty($cfg['outfit'])): ?>
                                                <img src="<?= htmlspecialchars($cfg['outfit']['img']) ?>"
                                                     style="position:absolute; width:50%; top:46%; left:40%; transform:translate(-50%,-50%); object-fit:contain; z-index:2;" alt="outfit">
                                            <?php endif; ?>
                                            <?php if (!empty($cfg['shoes'])): ?>
                                                <img src="<?= htmlspecialchars($cfg['shoes']['img']) ?>"
                                                     style="position:absolute; width:40%; top:75%; left:40%; transform:translate(-50%,-50%); object-fit:contain; z-index:3;" alt="shoes">
                                            <?php endif; ?>
                                            <?php if (!empty($cfg['acc'])): ?>
                                                <img src="<?= htmlspecialchars($cfg['acc']['img']) ?>"
                                                     style="position:absolute; width:26%; top:16%; left:10%; transform:translate(-50%,-50%); object-fit:contain; z-index:4;" alt="acc">
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <img src="images/brown.png" style="width:100%; height:100%; object-fit:contain;" alt="teddy">
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($cItem['name'] ?: 'Custom Teddy') ?></strong>
                                        <span style="background:var(--lavender); color:#fff; padding:2px 8px; border-radius:10px; font-size:10px; margin-left:6px;">Custom</span>
                                    </div>
                                </div>
                            </td>
                            <td class="product-price">$<?= number_format($cPrice, 2) ?></td>
                            <td style="text-align: center;"><?= $cQty ?></td>
                            <td class="product-price">$<?= number_format($cPrice * $cQty, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($order['subtotal'], 2) ?></span>
                </div>
                <?php if ($order['is_gift'] === true && $order['gift_wrap_price'] > 0): ?>
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
                <button onclick="window.print()" class="btn-action btn-print">
                    <i class="fa-solid fa-print"></i> Print Order
                </button>
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