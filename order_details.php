<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: auth.php');
    exit;
}

$pageTitle = "Order Details | Teddy Lap";
$pdo       = getDB();
$userId    = $_SESSION['user_id'];

// جيبي رقم الطلب من الـ URL
$orderNumber = isset($_GET['id']) ? trim($_GET['id']) : '';

// جيبي الطلب من DB
$stmt = $pdo->prepare("
    SELECT * FROM orders
    WHERE order_number = ? AND user_id = ?
");
$stmt->execute([$orderNumber, $userId]);
$order = $stmt->fetch();

$orderItems = [];
if ($order) {
    $stmt = $pdo->prepare("
        SELECT oi.quantity, oi.unit_price,
               p.name, p.image, p.product_id
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['order_id']]);
    $orderItems = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0,0) rotate(0deg); } 100% { transform: translate(50px,30px) rotate(20deg); } }
        .order-details-container { padding: 120px 20px 50px; max-width: 900px; margin: 0 auto; }
        .order-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; opacity: 0; animation: fadeDown 0.8s forwards; }
        .order-title h1 { font-family: 'Playfair Display', serif; font-size: 32px; color: var(--text-color); margin: 0 0 5px; }
        .order-title span { color: var(--secondary-text); font-size: 14px; }
        .back-btn { background: var(--lavender); color: #fff; padding: 10px 25px; border-radius: 25px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; }
        .back-btn:hover { background: var(--pink); transform: translateY(-2px); }
        .tracking-card { background: var(--card-bg); border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px var(--shadow); margin-bottom: 30px; opacity: 0; animation: slideIn 0.8s 0.2s forwards; }
        .status-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .status-header h3 { margin: 0; color: var(--text-color); font-size: 18px; }
        .status-badge { padding: 6px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .status-pending    { background: #f8d7da; color: #721c24; }
        .status-processing { background: #fff3cd; color: #856404; }
        .status-shipped    { background: #cce5ff; color: #004085; }
        .status-completed  { background: #d4edda; color: #155724; }
        .status-cancelled  { background: #e2e3e5; color: #383d41; }
        .tracking-steps { display: flex; justify-content: space-between; position: relative; margin-top: 40px; }
        .tracking-steps::before { content: ''; position: absolute; top: 15px; left: 0; width: 100%; height: 4px; background: #e0e0e0; z-index: 0; }
        .step { position: relative; z-index: 1; text-align: center; flex: 1; }
        .step-icon { width: 35px; height: 35px; background: #e0e0e0; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; }
        .step-text { font-size: 12px; color: var(--secondary-text); font-weight: 500; }
        .step.completed .step-icon { background: var(--pink); box-shadow: 0 0 0 3px rgba(248,187,208,0.3); }
        .step.completed .step-text { color: var(--pink); }
        .step.active .step-icon { background: var(--lavender); box-shadow: 0 0 0 3px rgba(200,162,200,0.3); }
        .step.active .step-text { color: var(--lavender); font-weight: 600; }
        .order-content { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; opacity: 0; animation: slideIn 0.8s 0.4s forwards; }
        @media (max-width: 768px) { .order-content { grid-template-columns: 1fr; } }
        .items-card { background: var(--card-bg); border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px var(--shadow); }
        .items-card h3 { margin-top: 0; margin-bottom: 20px; color: var(--text-color); }
        .item-row { display: flex; gap: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .item-row { border-bottom-color: #333; }
        .item-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .item-img { width: 70px; height: 70px; background: #f8f8f8; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        body.dark-mode .item-img { background: #333; }
        .item-img img { max-width: 90%; max-height: 90%; object-fit: contain; }
        .item-info { flex: 1; }
        .item-info h4 { margin: 0 0 5px; color: var(--text-color); font-size: 16px; }
        .item-info p { margin: 0; color: var(--secondary-text); font-size: 13px; }
        .item-price { text-align: right; }
        .item-price span { display: block; color: var(--pink); font-weight: bold; }
        .item-price small { color: var(--secondary-text); font-size: 12px; }
        .summary-side { display: flex; flex-direction: column; gap: 20px; }
        .info-card { background: var(--card-bg); border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px var(--shadow); }
        .info-card h4 { margin-top: 0; margin-bottom: 15px; color: var(--text-color); font-size: 16px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; color: var(--secondary-text); }
        .info-row strong { color: var(--text-color); }
        .total-row { border-top: 1px solid #eee; padding-top: 15px; margin-top: 10px; font-size: 18px; font-weight: bold; color: var(--text-color); }
        body.dark-mode .total-row { border-top-color: #333; }
        .gift-card-display { background: linear-gradient(135deg, #fff0f5 0%, #fff 100%); border: 1px solid #ffcee6; }
        body.dark-mode .gift-card-display { background: linear-gradient(135deg, #2a2025 0%, #222 100%); border-color: #555; }
        .gift-message-box { background: rgba(255,255,255,0.6); border-left: 3px solid var(--pink); padding: 10px 15px; border-radius: 0 8px 8px 0; font-style: italic; font-size: 13px; color: var(--text-color); margin-top: 10px; }
        body.dark-mode .gift-message-box { background: rgba(0,0,0,0.2); }
        .error-state { text-align: center; padding: 50px; }
        .error-state i { font-size: 50px; color: var(--pink); margin-bottom: 15px; }
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="order-details-container">

    <?php if (!$order): ?>
        <!-- طلب مش موجود -->
        <div class="error-state">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h3>Order Not Found</h3>
            <p>We couldn't find the order you are looking for.</p>
            <a href="profile.php?tab=orders" class="back-btn">Back to Orders</a>
        </div>

    <?php else:
        // استخراج بيانات الشحن من الـ notes
        $notes   = $order['notes'] ?? '';
        $parts   = explode(' | ', $notes);
        $name    = $parts[0] ?? '';
        $address = $parts[1] ?? '';
        $phone   = $parts[2] ?? '';

        // ── استخراج أرقام الكوستوم من notes ──
        $customIds = [];
        foreach ($parts as $part) {
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
            $customItems = $stmtC->fetchAll();
        }

        // status → index للـ tracking
        $statusMap = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'completed' => 3, 'cancelled' => -1];
        $statusIdx = $statusMap[strtolower($order['status'])] ?? 0;
        ?>
        <!-- ── Header ─────────────────────────────────────────────── -->
        <div class="order-header">
            <div class="order-title">
                <h1>Order #<?= htmlspecialchars($order['order_number']) ?></h1>
                <span>
                <i class="fa-regular fa-calendar"></i>
                Placed on <?= date('M d, Y', strtotime($order['created_at'])) ?>
            </span>
            </div>
            <a href="profile.php?tab=orders" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- ── Tracking ────────────────────────────────────────────── -->
        <div class="tracking-card">
            <div class="status-header">
                <h3>Order Status</h3>
                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                <?= ucfirst($order['status']) ?>
            </span>
            </div>

            <?php if ($order['status'] !== 'cancelled'): ?>
                <div class="tracking-steps">
                    <?php
                    $steps = [
                            ['id' => 'placed',     'icon' => 'fa-box',   'label' => 'Placed'],
                            ['id' => 'processing', 'icon' => 'fa-cog',   'label' => 'Processing'],
                            ['id' => 'shipped',    'icon' => 'fa-truck', 'label' => 'Shipped'],
                            ['id' => 'completed',  'icon' => 'fa-house', 'label' => 'Delivered'],
                    ];
                    foreach ($steps as $i => $step):
                        $cls = '';
                        if ($i < $statusIdx)      $cls = 'completed';
                        elseif ($i === $statusIdx) $cls = 'active';
                        $iconClass = ($i < $statusIdx) ? 'fa-solid fa-check' : 'fa-solid ' . $step['icon'];
                        ?>
                        <div class="step <?= $cls ?>">
                            <div class="step-icon"><i class="<?= $iconClass ?>"></i></div>
                            <div class="step-text"><?= $step['label'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center; color:#dc3545; margin:20px 0 0;">
                    <i class="fa-solid fa-ban"></i> This order has been cancelled.
                </p>
            <?php endif; ?>
        </div>

        <!-- ── Content ────────────────────────────────────────────── -->
        <div class="order-content">

            <!-- Items -->
            <div class="items-card">
                <h3>Items Ordered</h3>
                <?php foreach ($orderItems as $item): ?>
                    <div class="item-row">
                        <div class="item-img">
                            <img src="<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='https://ui-avatars.com/api/?name=Toy&background=ff9a9e&color=fff'">
                        </div>
                        <div class="item-info">
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <p>Qty: <?= $item['quantity'] ?> &nbsp;|&nbsp; $<?= number_format($item['unit_price'], 2) ?> each</p>
                        </div>
                        <div class="item-price">
                            <span>$<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($customItems as $cItem):
                    $cfg = $cItem['config_json'] ? json_decode($cItem['config_json'], true) : null;
                    $cQty = (int)($cItem['quantity'] ?? 1);
                    ?>
                    <div class="item-row">
                        <div class="item-img" style="background:transparent; box-shadow:none; position:relative;">
                            <div style="position:relative; width:65px; height:65px;">
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
                        </div>
                        <div class="item-info">
                            <h4><?= htmlspecialchars($cItem['name'] ?: 'Custom Teddy') ?>
                                <span style="background:var(--lavender); color:#fff; padding:1px 6px; border-radius:8px; font-size:9px; margin-left:4px;">Custom</span>
                            </h4>
                            <p>Qty: <?= $cQty ?> &nbsp;|&nbsp; $<?= number_format($cItem['total_price'], 2) ?> each</p>
                        </div>
                        <div class="item-price">
                            <span>$<?= number_format($cItem['total_price'] * $cQty, 2) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary Side -->
            <div class="summary-side">

                <!-- Shipping Info -->
                <div class="info-card">
                    <h4><i class="fa-solid fa-truck" style="color:var(--pink); margin-right:5px;"></i> Shipping Info</h4>
                    <div class="info-row">
                        <span>Name</span>
                        <strong><?= htmlspecialchars($name) ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Address</span>
                        <strong style="text-align:right; max-width:150px;"><?= htmlspecialchars($address) ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Phone</span>
                        <strong><?= htmlspecialchars($phone) ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Payment</span>
                        <strong><?= ucfirst($order['payment_method']) ?></strong>
                    </div>
                </div>

                <!-- Gift Details (إذا كان هدية) -->
                <?php if ($order['is_gift']): ?>
                    <div class="info-card gift-card-display">
                        <h4><i class="fa-solid fa-gift" style="color:var(--pink); margin-right:5px;"></i> Gift Details</h4>
                        <?php if ($order['gift_box']): ?>
                            <p style="font-size:13px; color:var(--secondary-text);">
                                Wrap: <?= htmlspecialchars($order['gift_box']) ?>
                                (+$<?= number_format($order['gift_wrap_price'], 2) ?>)
                            </p>
                        <?php endif; ?>
                        <?php if ($order['gift_message']): ?>
                            <div class="gift-message-box">
                                <i class="fa-solid fa-quote-left"></i>
                                <?= htmlspecialchars($order['gift_message']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Total Summary -->
                <div class="info-card">
                    <h4><i class="fa-solid fa-receipt" style="color:var(--lavender); margin-right:5px;"></i> Total Summary</h4>
                    <div class="info-row">
                        <span>Subtotal</span>
                        <span>$<?= number_format($order['subtotal'], 2) ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="info-row">
                            <span>Discount</span>
                            <span style="color:#28a745;">-$<?= number_format($order['discount_amount'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($order['gift_wrap_price'] > 0): ?>
                        <div class="info-row">
                            <span>Gift Wrap</span>
                            <span>+$<?= number_format($order['gift_wrap_price'], 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span>Shipping</span>
                        <span style="color:#28a745;">Free</span>
                    </div>
                    <div class="info-row total-row">
                        <span>Total</span>
                        <span style="color:var(--pink);">$<?= number_format($order['total'], 2) ?></span>
                    </div>
                </div>

            </div>
        </div>

    <?php endif; ?>
</div>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>