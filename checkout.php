<?php
session_start();
require_once 'db.php';
require_once 'mailer.php';

$pageTitle  = "Checkout | Teddy Lap";
$pdo        = getDB();
$isLoggedIn = !empty($_SESSION['logged_in']);
$userId     = $_SESSION['user_id'] ?? null;

// إذا مش مسجّل دخول
if (!$isLoggedIn) {
    header('Location: auth.php');
    exit;
}

// ── جيبي محتوى الكارت من DB ──────────────────────────────────
$stmt = $pdo->prepare("SELECT cart_id, coupon_id FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);
$cartRow = $stmt->fetch();

$cartItems       = [];
$customCartItems = [];
$cartId          = null;
$appliedCoupon   = null;

if ($cartRow) {
    $cartId = $cartRow['cart_id'];

    // ── جلب بيانات الكوبون إذا كان موجوداً ──
    if (!empty($cartRow['coupon_id'])) {
        $stmtC = $pdo->prepare("SELECT * FROM Coupons WHERE coupon_id = ? AND status = 'active'");
        $stmtC->execute([$cartRow['coupon_id']]);
        $appliedCoupon = $stmtC->fetch(PDO::FETCH_ASSOC);
    }

    $stmt = $pdo->prepare("
        SELECT ci.product_id AS id, ci.quantity,
               p.name, p.price, p.image,
               c.name AS category
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        JOIN categories c ON p.category_id = c.category_id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cartId]);
    $cartItems = $stmt->fetchAll();
}

// جيبي الدببة المخصصة
$stmt = $pdo->prepare("
    SELECT custom_id, custom_name AS name, total_price AS price, config_json, quantity
    FROM custom_teddies WHERE user_id = ? AND is_saved = FALSE
    ORDER BY custom_id DESC LIMIT 20
");
$stmt->execute([$userId]);
$customCartItems = $stmt->fetchAll();

if (empty($cartItems) && empty($customCartItems)) {
    header('Location: cart.php');
    exit;
}

// ── بيانات اليوزر للـ prefill ────────────────────────────────
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

$nameParts = explode(' ', $userData['name'] ?? '', 2);
$firstName = $nameParts[0] ?? '';
$lastName  = $nameParts[1] ?? '';

// ── معالجة تأكيد الطلب (AJAX) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    header('Content-Type: application/json');

    $firstname     = trim($_POST['firstname']      ?? '');
    $lastname      = trim($_POST['lastname']       ?? '');
    $email         = trim($_POST['email']          ?? '');
    $phone         = trim($_POST['phone']          ?? '');
    $address       = trim($_POST['address']        ?? '');
    $city          = trim($_POST['city']           ?? '');
    $postal        = trim($_POST['postal']         ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'card');
    $selectedIds   = json_decode($_POST['selected_ids'] ?? '[]', true);
    $selectedCustomIds = json_decode($_POST['selected_custom_ids'] ?? '[]', true);
    $isGift        = (bool)($_POST['is_gift']      ?? false);
    $giftMessage   = trim($_POST['gift_message']   ?? '');
    $giftBox       = trim($_POST['gift_box']       ?? '');
    $giftWrapPrice = (float)($_POST['gift_wrap_price'] ?? 0);

    if (empty($selectedIds) && empty($selectedCustomIds)) {
        echo json_encode(['success' => false, 'message' => 'No items selected']);
        exit;
    }

    // ── احسب مجموع العادي من DB ─────────────────────────────
    $subtotal = 0;
    $selectedItems = [];

    if (!empty($selectedIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $params       = array_merge([$cartId], array_map('intval', $selectedIds));
        $stmt         = $pdo->prepare("
            SELECT ci.product_id, ci.quantity, p.price
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ? AND ci.product_id IN ($placeholders)
        ");
        $stmt->execute($params);
        $selectedItems = $stmt->fetchAll();

        foreach ($selectedItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
    }

    // ── احسب مجموع الكوستوم من DB ───────────────────────────
    $customItemsData = [];
    if (!empty($selectedCustomIds)) {
        $cPlaceholders = implode(',', array_fill(0, count($selectedCustomIds), '?'));
        $cParams       = array_map('intval', $selectedCustomIds);
        $cParams[]     = $userId;

        $stmtC = $pdo->prepare("
            SELECT custom_id, total_price, quantity 
            FROM custom_teddies 
            WHERE custom_id IN ($cPlaceholders) AND user_id = ? AND is_saved = FALSE
        ");
        $stmtC->execute($cParams);
        $customItemsData = $stmtC->fetchAll();

        foreach ($customItemsData as $cItem) {
            $cQty = (int)$cItem['quantity'] > 0 ? (int)$cItem['quantity'] : 1;
            $subtotal += $cItem['total_price'] * $cQty;
        }
    }

    // ── حساب الخصم (الكوبون) ────────────────────────────────
    $discountAmount = 0;
    $couponIdToSave = null;
    if ($appliedCoupon) {
        $couponIdToSave = $appliedCoupon['coupon_id'];
        if ($appliedCoupon['discount_type'] === 'percentage') {
            $discountAmount = ($subtotal * $appliedCoupon['discount_value']) / 100;
            if ($appliedCoupon['max_discount'] > 0 && $discountAmount > $appliedCoupon['max_discount']) {
                $discountAmount = $appliedCoupon['max_discount'];
            }
        } elseif ($appliedCoupon['discount_type'] === 'fixed') {
            $discountAmount = $appliedCoupon['discount_value'];
        }
        if ($discountAmount > $subtotal) $discountAmount = $subtotal;
    }

    $total = $subtotal - $discountAmount + $giftWrapPrice;
    if ($total < 0) $total = 0;

    // map payment method
    $pmMap = ['credit_card' => 'card', 'cod' => 'cash', 'paypal' => 'paypal'];
    $pm    = $pmMap[$paymentMethod] ?? 'card';

    // أنشئ الطلب
    $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), -6));
    // ── بناء نص notes مع أرقام الكوستوم ──
    $notesStr = "$firstname $lastname | $address, $city $postal | $phone";
    if (!empty($selectedCustomIds)) {
        $notesStr .= " | custom:" . implode(',', array_map('intval', $selectedCustomIds));
    }

    $pdo->prepare("
        INSERT INTO orders (order_number, user_id, coupon_id, status, payment_method,
                            subtotal, discount_amount, gift_wrap_price, total,
                            is_gift, gift_message, gift_box, notes, created_at)
        VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ")->execute([
            $orderNumber, $userId, $couponIdToSave, $pm,
            $subtotal, $discountAmount, $giftWrapPrice, $total,
            $isGift ? 1 : 0, $giftMessage, $giftBox,
            $notesStr
    ]);

    $orderId = $pdo->lastInsertId();

    // تحديث عدد استخدام الكوبون
    if ($couponIdToSave) {
        $pdo->prepare("UPDATE Coupons SET used_count = used_count + 1 WHERE coupon_id = ?")->execute([$couponIdToSave]);
        // إزالة الكوبون من السلة
        if ($cartId) {
            $pdo->prepare("UPDATE Cart SET coupon_id = NULL WHERE cart_id = ?")->execute([$cartId]);
        }
    }

    // أضف Order Items العادي
    if (!empty($selectedItems)) {
        $insertItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($selectedItems as $item) {
            $insertItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            $pdo->prepare("
                UPDATE products SET stock = stock - ?, sales_count = sales_count + ?
                WHERE product_id = ? AND stock >= ?
            ")->execute([$item['quantity'], $item['quantity'], $item['product_id'], $item['quantity']]);
        }

        // احذف العناصر العادية المشتراة من الكارت
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $params       = array_merge([$cartId], array_map('intval', $selectedIds));
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id IN ($placeholders)")->execute($params);
    }

    // ── انقل الدببة المخصصة من السلة (is_saved = TRUE) ─────
    if (!empty($selectedCustomIds)) {
        $cPlaceholders = implode(',', array_fill(0, count($selectedCustomIds), '?'));
        $cParams       = array_merge(array_map('intval', $selectedCustomIds), [$userId]);
        $pdo->prepare("UPDATE custom_teddies SET is_saved = TRUE WHERE custom_id IN ($cPlaceholders) AND user_id = ?")
                ->execute($cParams);
    }

    // إذا الكارت فضي احذفه
    if ($cartId) {
        $checkEmpty = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = ?");
        $checkEmpty->execute([$cartId]);
        if ($checkEmpty->fetchColumn() == 0) {
            $pdo->prepare("DELETE FROM cart WHERE cart_id = ?")->execute([$cartId]);
        }
    }

    echo json_encode([
            'success'      => true,
            'order_number' => $orderNumber,
            'total'        => number_format($total, 2)
    ]);

    // إيميل لليوزر
    sendOrderConfirmationEmail($email, "$firstname $lastname", $orderNumber, $total);
    // إيميل للأدمن
    sendNewOrderAdminEmail($orderNumber, "$firstname $lastname", $email, $total);
    exit;
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
        .checkout-container { padding: 120px 20px 50px; max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; }
        @media (max-width: 900px) { .checkout-container { grid-template-columns: 1fr; padding-top: 100px; } }
        .page-header { grid-column: 1/-1; text-align: center; margin-bottom: 30px; opacity: 0; animation: fadeDown 0.8s forwards; }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 42px; background: linear-gradient(45deg, #ff6b81, #ff9a9e, #fbc2eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px; }
        .page-header p { color: var(--secondary-text); }
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .checkout-form-section { background: var(--card-bg); padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px var(--shadow); height: fit-content; opacity: 0; animation: slideIn 0.6s forwards; }
        .section-title { font-size: 20px; color: var(--text-color); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: var(--pink); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1/-1; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--text-color); font-size: 14px; font-weight: 500; }
        .form-input { width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid #eee; background-color: var(--bg-color); color: var(--text-color); font-family: 'Poppins', sans-serif; font-size: 14px; transition: all 0.3s; box-sizing: border-box; }
        body.dark-mode .form-input { border-color: #444; }
        .form-input:focus { outline: none; border-color: var(--pink); box-shadow: 0 0 0 3px rgba(255,107,129,0.1); }
        .gift-info-summary { background: linear-gradient(45deg, rgba(255,154,158,0.1), rgba(255,107,129,0.1)); border-radius: 12px; padding: 15px; margin-bottom: 25px; border-left: 4px solid var(--pink); }
        .gift-info-summary h4 { color: var(--pink); margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px; }
        .gift-info-summary p { margin: 5px 0; color: var(--text-color); font-size: 14px; }
        .gift-info-summary .gift-message { background: var(--card-bg); padding: 10px; border-radius: 8px; margin-top: 8px; font-style: italic; }
        .payment-method-selector { display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; }
        .payment-option { display: flex; align-items: center; padding: 15px; border: 1px solid #eee; border-radius: 12px; cursor: pointer; transition: all 0.2s; background-color: var(--bg-color); }
        body.dark-mode .payment-option { border-color: #444; background-color: var(--card-bg); }
        .payment-option:hover { border-color: var(--pink); }
        .payment-option.active { border-color: var(--pink); background-color: rgba(255,107,129,0.05); }
        .payment-option input[type="radio"] { display: none; }
        .custom-radio { width: 20px; height: 20px; border: 2px solid #ccc; border-radius: 50%; margin-right: 15px; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .payment-option.active .custom-radio { border-color: var(--pink); }
        .custom-radio::after { content: ''; width: 10px; height: 10px; background-color: var(--pink); border-radius: 50%; opacity: 0; transform: scale(0); transition: 0.2s; }
        .payment-option.active .custom-radio::after { opacity: 1; transform: scale(1); }
        .method-info { display: flex; align-items: center; gap: 10px; flex: 1; }
        .method-info i { font-size: 20px; color: var(--secondary-text); }
        .method-info span { font-weight: 500; color: var(--text-color); }
        .payment-details { display: none; padding: 20px; background: rgba(255,255,255,0.5); border-radius: 12px; border: 1px dashed #eee; margin-top: 10px; animation: fadeIn 0.3s ease; }
        body.dark-mode .payment-details { background: rgba(0,0,0,0.1); border-color: #444; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .order-summary { background: var(--card-bg); padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px var(--shadow); position: sticky; top: 100px; height: fit-content; opacity: 0; animation: slideIn 0.6s 0.2s forwards; }
        .order-items { max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding-right: 10px; }
        .order-item { display: flex; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .order-item { border-bottom-color: #333; }
        .order-item-img { width: 60px; height: 60px; background: #f8f8f8; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        body.dark-mode .order-item-img { background: #333; }
        .order-item-img img { max-width: 80%; max-height: 80%; object-fit: contain; }
        .order-item-details { flex: 1; }
        .order-item-details h4 { margin: 0 0 5px; font-size: 14px; color: var(--text-color); }
        .order-item-details p { margin: 0; font-size: 12px; color: var(--secondary-text); }
        .order-item-price { font-weight: bold; color: var(--text-color); font-size: 14px; }
        .summary-calc { border-top: 1px solid #eee; padding-top: 20px; margin-top: 10px; }
        body.dark-mode .summary-calc { border-top-color: #333; }
        .calc-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--secondary-text); font-size: 14px; }
        .calc-row.total { font-size: 18px; font-weight: bold; color: var(--text-color); margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ddd; }
        .calc-row.discount { color: #4CAF50; font-weight: 600; }
        .place-order-btn { width: 100%; padding: 15px; border-radius: 50px; background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff; border: none; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 25px; transition: all 0.3s; box-shadow: 0 10px 20px rgba(255,154,158,0.4); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .place-order-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(255,154,158,0.5); }
        .place-order-btn:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        .order-item-img.custom-preview { position: relative; overflow: visible !important; background: transparent; box-shadow: none; }
        .teddy-mini-wrap { position: relative; width: 60px; height: 60px; flex-shrink: 0; }
        .teddy-mini-wrap img { position: absolute; object-fit: contain; }
        .teddy-mini-wrap .p-base { width: 100%; height: 100%; top: 0; left: 0; z-index: 1; transform: none; }
        .teddy-mini-wrap .p-outfit { width: 50%; height: auto; top: 46%; left: 40%; transform: translate(-50%, -50%); z-index: 2; }
        .teddy-mini-wrap .p-shoes { width: 40%; height: auto; top: 75%; left: 40%; transform: translate(-50%, -50%); z-index: 3; }
        .teddy-mini-wrap .p-acc { width: 26%; height: auto; top: 16%; left: 10%; transform: translate(-50%, -50%); z-index: 4; }
        .custom-badge-sm { background:var(--lavender); color:#fff; padding:1px 6px; border-radius:8px; font-size:9px; margin-left:4px; }
        .gift-info-summary h4 { display: none; }

        /* تنبيه منتصف الشاشة */
        .center-modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .center-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .center-modal-box {
            background: var(--card-bg, #fff);
            padding: 40px 30px;
            border-radius: 24px;
            text-align: center;
            max-width: 380px;
            width: 90%;
            box-shadow: 0 20px 50px rgba(255,107,129,0.3);
            transform: scale(0.8) translateY(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .center-modal-overlay.active .center-modal-box {
            transform: scale(1) translateY(0);
        }
        .center-modal-icon {
            width: 70px; height: 70px;
            background: linear-gradient(45deg, #ff9a9e, #fbc2eb);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: #fff;
            box-shadow: 0 10px 20px rgba(255,107,129,0.4);
        }
        .center-modal-text {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: var(--text-color, #333);
            margin-bottom: 25px;
            line-height: 1.5;
            font-weight: 500;
        }
        .center-modal-btn {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(255,154,158,0.4);
        }
        .center-modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,154,158,0.5);
        }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- نافذة التنبيه المخصصة -->
<div class="center-modal-overlay" id="centerModal">
    <div class="center-modal-box">
        <div class="center-modal-icon">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="center-modal-text" id="centerModalText">Message here</div>
        <button class="center-modal-btn" onclick="closeCenterModal()">OK</button>
    </div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="checkout-container">

    <div class="page-header">
        <h1>Checkout</h1>
        <p>Complete your order securely</p>
        <a href="cart.php" style="display:inline-flex; align-items:center; gap:6px; color:var(--pink); text-decoration:none; font-weight:600; margin-top:10px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Cart
        </a>
    </div>

    <!-- ── Shipping & Payment Form ─────────────────────────── -->
    <div class="checkout-form-section">
        <div class="section-title">
            <i class="fa-solid fa-truck"></i> Shipping Details
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" id="firstname" class="form-input" required
                       placeholder="First name" value="<?= htmlspecialchars($firstName) ?>">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="lastname" class="form-input" required
                       placeholder="Last name" value="<?= htmlspecialchars($lastName) ?>">
            </div>
            <div class="form-group full-width">
                <label>Email Address</label>
                <input type="email" id="email" class="form-input" required
                       placeholder="Email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>">
            </div>
            <div class="form-group full-width">
                <label>Phone Number</label>
                <input type="text" id="phone" class="form-input" required
                       placeholder="Phone" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
            </div>
            <div class="form-group full-width">
                <label>Address</label>
                <input type="text" id="address" class="form-input" required placeholder="Street address">
            </div>
            <div class="form-group">
                <label>City</label>
                <input type="text" id="city" class="form-input" required placeholder="City">
            </div>
            <div class="form-group">
                <label>Postal Code</label>
                <input type="text" id="postal" class="form-input" required placeholder="Postal code">
            </div>
        </div>

        <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">

        <div class="section-title">
            <i class="fa-solid fa-wallet"></i> Payment Method
        </div>

        <div class="payment-method-selector">
            <label class="payment-option active" onclick="selectPayment(this,'credit-card')">
                <input type="radio" name="payment_method" value="credit_card" checked>
                <div class="custom-radio"></div>
                <div class="method-info">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>Credit Card</span>
                </div>
            </label>
            <label class="payment-option" onclick="selectPayment(this,'cod')">
                <input type="radio" name="payment_method" value="cod">
                <div class="custom-radio"></div>
                <div class="method-info">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>Cash on Delivery</span>
                </div>
            </label>
        </div>

        <div class="payment-details" id="credit-card-details" style="display:block;">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Card Number</label>
                    <input type="text" class="form-input" placeholder="4242 4242 4242 4242" maxlength="19">
                </div>
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="password" class="form-input" placeholder="•••" maxlength="3">
                </div>
            </div>
        </div>

        <div class="payment-details" id="cod-details">
            <p style="color:var(--secondary-text); text-align:center; margin:0;">
                <i class="fa-solid fa-truck-fast" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                Pay with cash when your order is delivered.
            </p>
        </div>
    </div>

    <!-- ── Order Summary ───────────────────────────────────── -->
    <div class="order-summary">
        <h3 style="margin-bottom:20px; color:var(--text-color);">Your Order</h3>

        <div class="order-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="order-item" data-id="<?= $item['id'] ?>"
                     data-price="<?= $item['price'] ?>"
                     data-qty="<?= $item['quantity'] ?>">
                    <div class="order-item-img">
                        <img src="<?= htmlspecialchars($item['image']) ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>"
                             onerror="this.src='https://ui-avatars.com/api/?name=Toy&background=ff9a9e&color=fff'">
                    </div>
                    <div class="order-item-details">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p>Qty: <?= $item['quantity'] ?></p>
                    </div>
                    <div class="order-item-price">
                        $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach ($customCartItems as $cItem):
                $cfg = $cItem['config_json'] ? json_decode($cItem['config_json'], true) : null;
                $cQty = (int)($cItem['quantity'] ?? 1);
                ?>
                <div class="order-item" data-id="custom_<?= $cItem['custom_id'] ?>"
                     data-price="<?= $cItem['price'] ?>" data-qty="<?= $cQty ?>">
                    <div class="order-item-img custom-preview" style="background:transparent; box-shadow:none;">
                        <div class="teddy-mini-wrap">
                            <?php if ($cfg): ?>
                                <img src="<?= htmlspecialchars($cfg['color']['img'] ?? 'images/brown.png') ?>" class="p-base" alt="base">
                                <?php if (!empty($cfg['outfit'])): ?>
                                    <img src="<?= htmlspecialchars($cfg['outfit']['img']) ?>" class="p-outfit" alt="outfit">
                                <?php endif; ?>
                                <?php if (!empty($cfg['shoes'])): ?>
                                    <img src="<?= htmlspecialchars($cfg['shoes']['img']) ?>" class="p-shoes" alt="shoes">
                                <?php endif; ?>
                                <?php if (!empty($cfg['acc'])): ?>
                                    <img src="<?= htmlspecialchars($cfg['acc']['img']) ?>" class="p-acc" alt="acc">
                                <?php endif; ?>
                            <?php else: ?>
                                <img src="images/brown.png" class="p-base" alt="teddy">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="order-item-details">
                        <h4><?= htmlspecialchars($cItem['name'] ?: 'Custom Teddy') ?> <span class="custom-badge-sm">Custom</span></h4>
                        <p>Qty: <?= $cQty ?></p>
                    </div>
                    <div class="order-item-price">
                        $<?= number_format($cItem['price'] * $cQty, 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="giftInfoCheckout" style="display:none;" class="gift-info-summary"></div>

        <div class="summary-calc">
            <div class="calc-row">
                <span>Subtotal</span>
                <span id="checkoutSubtotal">$0.00</span>
            </div>

            <!-- Discount Row -->
            <?php if ($appliedCoupon): ?>
                <div class="calc-row discount" id="checkoutDiscountRow">
                    <span>Discount (<?= htmlspecialchars($appliedCoupon['code']) ?>)</span>
                    <span id="checkoutDiscount">-$0.00</span>
                </div>
            <?php endif; ?>

            <div class="calc-row" id="giftWrapRow" style="display:none;">
                <span>Gift Wrap</span>
                <span id="giftWrapPrice">+$0.00</span>
            </div>
            <div class="calc-row">
                <span>Shipping</span>
                <span style="color:#28a745;">Free</span>
            </div>
            <div class="calc-row total">
                <span>Total</span>
                <span id="checkoutTotal" style="color:var(--pink);">$0.00</span>
            </div>
        </div>

        <button class="place-order-btn" id="placeOrderBtn" onclick="placeOrder()">
            <i class="fa-solid fa-lock"></i> Place Order
        </button>
    </div>

</div>

<script>
    // ── نافذة التنبيه المخصصة ──────────────────────────
    function showCenterModal(message) {
        const modal = document.getElementById('centerModal');
        const text  = document.getElementById('centerModalText');
        if (text)  text.textContent  = message;
        if (modal) modal.classList.add('active');
    }

    function closeCenterModal() {
        const modal = document.getElementById('centerModal');
        if (modal) modal.classList.remove('active');
    }

    // ── بيانات من PHP ─────────────────────────────────────────
    const cartItemsFromDB = <?= json_encode($cartItems) ?>;
    const customCartItemsDB = <?= json_encode($customCartItems) ?>;

    // Coupon Data
    const couponData = <?= json_encode($appliedCoupon) ?>; // يحتوي على بيانات الكوبون أو null

    const selectedIdsRaw = sessionStorage.getItem('teddy_selected_items');
    const selectedIds    = selectedIdsRaw ? JSON.parse(selectedIdsRaw) : cartItemsFromDB.map(i => String(i.id));

    const selectedCustomIds = JSON.parse(sessionStorage.getItem('teddy_selected_custom') || '[]');

    const giftDataRaw = sessionStorage.getItem('teddy_gift_data') || localStorage.getItem('teddy_gift_data');
    const giftData    = giftDataRaw ? JSON.parse(giftDataRaw) : { isGift: false, message: '', wrap: 'none', wrapPrice: 0 };

    // ── حساب المجموع ─────────────────────────────────────────
    let subtotal = 0;
    cartItemsFromDB.forEach(item => {
        if (selectedIds.includes(String(item.id))) {
            subtotal += parseFloat(item.price) * item.quantity;
        }
    });

    customCartItemsDB.forEach(item => {
        if (selectedCustomIds.includes(String(item.custom_id))) {
            const qty = parseInt(item.quantity) || 1;
            subtotal += parseFloat(item.price) * qty;
        }
    });

    // حساب الخصم في الجافاسكريبت عشان يتحدث مباشرة
    let discountAmount = 0;
    if (couponData) {
        if (couponData.discount_type === 'percentage') {
            discountAmount = (subtotal * parseFloat(couponData.discount_value)) / 100;
            if (parseFloat(couponData.max_discount) > 0 && discountAmount > parseFloat(couponData.max_discount)) {
                discountAmount = parseFloat(couponData.max_discount);
            }
        } else if (couponData.discount_type === 'fixed') {
            discountAmount = parseFloat(couponData.discount_value);
        }
        if (discountAmount > subtotal) discountAmount = subtotal;
    }

    const giftWrapCost = (giftData.isGift && giftData.wrap !== 'none') ? parseFloat(giftData.wrapPrice) : 0;
    let total = subtotal - discountAmount + giftWrapCost;
    if (total < 0) total = 0;

    // تحديث الواجهة
    document.getElementById('checkoutSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('checkoutTotal').textContent    = '$' + total.toFixed(2);

    if (discountAmount > 0) {
        const discountRow = document.getElementById('checkoutDiscountRow');
        const discountEl = document.getElementById('checkoutDiscount');
        if (discountEl) discountEl.textContent = '-$' + discountAmount.toFixed(2);
    }

    if (giftWrapCost > 0) {
        document.getElementById('giftWrapRow').style.display  = 'flex';
        document.getElementById('giftWrapPrice').textContent  = '+$' + giftWrapCost.toFixed(2);
    }

    if (giftData.isGift) {
        const wrapNames = { box: 'Classic Box', teddywrap: 'Teddy Wrap', heartsbag: 'Hearts Bag' };
        let html = '';
        if (giftData.wrap !== 'none') html += `<p><i class="fa-solid fa-gift" style="color:var(--pink);"></i> <strong>Wrap:</strong> ${wrapNames[giftData.wrap] || giftData.wrap} (+$${giftWrapCost.toFixed(2)})</p>`;
        if (giftData.message)         html += `<div class="gift-message"><i class="fa-solid fa-quote-left"></i> ${giftData.message}</div>`;
        if (html) {
            document.getElementById('giftInfoCheckout').innerHTML    = html;
            document.getElementById('giftInfoCheckout').style.display = 'block';
        }
    }

    // ── إخفاء العناصر غير المختارة ──────────────────────────
    document.querySelectorAll('.order-item').forEach(el => {
        const itemId = String(el.dataset.id);
        if (itemId.startsWith('custom_')) {
            const customId = itemId.replace('custom_', '');
            if (!selectedCustomIds.includes(customId)) {
                el.style.display = 'none';
            }
        } else {
            if (!selectedIds.includes(itemId)) {
                el.style.display = 'none';
            }
        }
    });

    // ── Payment Method ────────────────────────────────────────
    function selectPayment(element, method) {
        document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        document.querySelectorAll('.payment-details').forEach(el => el.style.display = 'none');
        const d = document.getElementById(method + '-details');
        if (d) d.style.display = 'block';
    }

    // ── Place Order ───────────────────────────────────────────
    function placeOrder() {
        const firstname = document.getElementById('firstname').value.trim();
        const lastname  = document.getElementById('lastname').value.trim();
        const email     = document.getElementById('email').value.trim();
        const phone     = document.getElementById('phone').value.trim();
        const address   = document.getElementById('address').value.trim();
        const city      = document.getElementById('city').value.trim();
        const postal    = document.getElementById('postal').value.trim();

        if (!firstname || !lastname || !email || !phone || !address || !city || !postal) {
            showCenterModal('Please fill in all required fields.');
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'credit_card';

        const btn = document.getElementById('placeOrderBtn');
        btn.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        btn.disabled   = true;

        const body = new URLSearchParams({
            action:          'place_order',
            firstname, lastname, email, phone, address, city, postal,
            payment_method:  paymentMethod,
            selected_ids:    JSON.stringify(selectedIds),
            selected_custom_ids: JSON.stringify(selectedCustomIds),
            is_gift:         giftData.isGift ? '1' : '0',
            gift_message:    giftData.message || '',
            gift_box:        giftData.wrap    || '',
            gift_wrap_price: giftWrapCost.toString()
        });

        fetch('checkout.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString()
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('last_order_number', data.order_number);
                    sessionStorage.setItem('last_order_total',  data.total);
                    sessionStorage.removeItem('teddy_selected_items');
                    sessionStorage.removeItem('teddy_selected_custom');
                    sessionStorage.removeItem('teddy_gift_data');
                    window.location.href = 'success.php';
                } else {
                    showCenterModal(data.message || 'Something went wrong. Please try again.');
                    btn.innerHTML = '<i class="fa-solid fa-lock"></i> Place Order';
                    btn.disabled  = false;
                }
            })
            .catch(() => {
                showCenterModal('Network error. Please try again.');
                btn.innerHTML = '<i class="fa-solid fa-lock"></i> Place Order';
                btn.disabled  = false;
            });
    }
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>