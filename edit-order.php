<?php
// edit-order.php
session_start();

// تضمين المصفوفات
require_once 'products.php';
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
$pageTitle = "Edit Order " . $order['order_number'] . " | Teddy Shop";

// متغيرات النموذج
$customer_name = $order['customer'];
$customer_email = $order['customer_email'];
$payment_method = $order['payment_method'];
$status = $order['status'];
$notes = $order['notes'] ?? '';
$selected_products = $order['products'];
$is_gift = $order['is_gift'] ?? 0;
$gift_box = $order['gift_box'] ?? '';
$gift_message = $order['gift_message'] ?? '';
$gift_wrap_price = $order['gift_wrap_price'] ?? 0;

// أسعار التغليف الثابتة
$gift_prices = [
        'box.png' => 5.00,
        'heartsbag.png' => 7.50,
        'teddywrap.png' => 10.00,
        'premium.png' => 15.00
];

$errors = [];
$success = false;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $product_ids = $_POST['product_ids'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $is_gift = isset($_POST['is_gift']) ? (int)$_POST['is_gift'] : 0;
    $gift_box = $_POST['gift_box'] ?? '';
    $gift_message = trim($_POST['gift_message'] ?? '');
    $gift_wrap_price = 0;

    // تحديد سعر تغليف الهدية حسب النوع المختار
    if ($is_gift == 1 && !empty($gift_box) && isset($gift_prices[$gift_box])) {
        $gift_wrap_price = $gift_prices[$gift_box];
    }

    // التحقق من صحة البيانات
    if (empty($customer_name)) {
        $errors[] = 'Customer name is required';
    }

    if (empty($customer_email)) {
        $errors[] = 'Customer email is required';
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (empty($payment_method)) {
        $errors[] = 'Payment method is required';
    }

    if (empty($status)) {
        $errors[] = 'Order status is required';
    }

    if (empty($product_ids)) {
        $errors[] = 'Please select at least one product';
    }

    // التحقق من اختيار صندوق الهدية إذا كان طلب هدية
    if ($is_gift == 1 && empty($gift_box)) {
        $errors[] = 'Please select a gift box style';
    }

    // حساب تفاصيل الطلب
    $items_count = 0;
    $subtotal = 0;
    $total = 0;
    $products_list = [];

    if (empty($errors)) {
        foreach ($product_ids as $index => $product_id) {
            $quantity = (int)($quantities[$index] ?? 1);
            if ($quantity > 0 && isset($products[$product_id])) {
                $product = $products[$product_id];
                $price = $product['price'];
                $item_subtotal = $price * $quantity;
                $subtotal += $item_subtotal;
                $items_count += $quantity;
                $products_list[] = [
                        'id' => $product_id,
                        'name' => $product['name'],
                        'price' => $price,
                        'quantity' => $quantity,
                        'subtotal' => $item_subtotal,
                        'image' => $product['image']
                ];
            }
        }

        if ($items_count == 0) {
            $errors[] = 'Please select valid products with quantities';
        }

        // حساب المجموع الكلي مع إضافة سعر التغليف إذا وجد
        $total = $subtotal + $gift_wrap_price;
    }

    // إذا لم يكن هناك أخطاء، قم بتحديث الطلب
    if (empty($errors)) {
        // تحديث الطلب (في التطبيق الحقيقي، ستقوم بتحديث قاعدة البيانات)
        // للتجربة، نعرض رسالة نجاح فقط

        // تحديث مصفوفة الطلب مؤقتاً
        $orders[$order_id] = [
                'order_number' => $order['order_number'],
                'customer' => $customer_name,
                'customer_email' => $customer_email,
                'date' => $order['date'],
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => $status,
                'payment_method' => $payment_method,
                'items_count' => $items_count,
                'products' => $products_list,
                'notes' => $notes,
                'is_gift' => $is_gift,
                'gift_box' => $gift_box,
                'gift_message' => $gift_message,
                'gift_wrap_price' => $gift_wrap_price
        ];

        $success = true;

        // تحديث المتغيرات المعروضة
        $order = $orders[$order_id];
        $selected_products = $products_list;
    }
}
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

        .form-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .form-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .form-header p {
            color: var(--secondary-text);
        }

        .order-number {
            background: var(--lavender);
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .form-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        .form-group label i {
            color: var(--primary);
            margin-right: 8px;
        }
        .required {
            color: #ff6b6b;
            margin-left: 4px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 16px;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        .help-text {
            font-size: 12px;
            color: var(--secondary-text);
            margin-top: 5px;
        }

        /* Gift Options */
        .gift-options-container {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(128,128,128,0.1);
        }
        .status-toggle {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .status-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 50px;
            background: var(--bg-color);
            transition: all 0.3s ease;
        }
        .status-option:hover {
            background: var(--pink);
            color: #000;
        }
        .status-option input {
            cursor: pointer;
        }

        /* Gift Box Styles */
        .gift-boxes {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .gift-box-item {
            cursor: pointer;
            text-align: center;
            padding: 10px;
            border-radius: 16px;
            transition: all 0.3s ease;
            background: var(--bg-color);
            border: 2px solid transparent;
        }
        .gift-box-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .gift-box-item.selected {
            border-color: var(--primary);
            background: rgba(248, 187, 208, 0.1);
        }
        .gift-box-img {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            margin-bottom: 8px;
        }
        .gift-box-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-color);
        }
        .gift-box-price {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            margin-top: 4px;
        }

        /* Status Select */
        .status-select {
            cursor: pointer;
        }
        .status-badge-sm {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Products Table */
        .products-table-container {
            margin: 20px 0;
            overflow-x: auto;
        }
        .products-select-table {
            width: 100%;
            border-collapse: collapse;
        }
        .products-select-table th {
            text-align: left;
            padding: 12px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .products-select-table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
        }
        .product-select {
            width: 40px;
            height: 40px;
            cursor: pointer;
        }
        .qty-input {
            width: 70px;
            padding: 8px;
            text-align: center;
            border-radius: 30px;
            border: 1px solid rgba(128,128,128,0.2);
            background: var(--bg-color);
            color: var(--text-color);
        }
        .qty-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .product-price {
            font-weight: 600;
            color: var(--primary);
        }

        .order-summary {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(128,128,128,0.1);
        }
        .summary-total {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            padding-top: 15px;
            border-top: 2px solid var(--pink);
        }
        .gift-summary {
            color: var(--secondary-text);
            font-size: 14px;
        }

        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
            border: 1px solid #F44336;
        }
        .alert-error ul {
            margin: 0;
            padding-left: 20px;
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-submit {
            flex: 1;
            padding: 14px 25px;
            background: linear-gradient(135deg, var(--primary), var(--pink));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(248, 187, 208, 0.5);
        }
        .btn-cancel {
            flex: 1;
            padding: 14px 25px;
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 2px solid rgba(128,128,128,0.2);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .form-layout { grid-template-columns: 1fr; gap: 20px; }
            .form-buttons { flex-direction: column; }
            .gift-box-img { width: 80px; height: 80px; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="form-container">
            <div class="form-header">
                <h1><i class="fa-solid fa-pen-to-square"></i> Edit Order</h1>
                <p>Update order information</p>
                <div class="order-number">Order #: <?= htmlspecialchars($order['order_number']) ?></div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Order updated successfully! <a href="order-details-admin.php?id=<?= $order_id ?>" style="color: #4CAF50;">View order</a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="edit-order.php?id=<?= $order_id ?>" method="POST" id="orderForm">
                <div class="form-layout">
                    <!-- Left Column - Customer Info -->
                    <div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-user"></i> Customer Name <span class="required">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($customer_name) ?>" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fa-solid fa-envelope"></i> Customer Email <span class="required">*</span></label>
                            <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($customer_email) ?>" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fa-solid fa-credit-card"></i> Payment Method <span class="required">*</span></label>
                            <select name="payment_method" class="form-control" required>
                                <option value="Credit Card" <?= $payment_method == 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                                <option value="PayPal" <?= $payment_method == 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                                <option value="Bank Transfer" <?= $payment_method == 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="Cash on Delivery" <?= $payment_method == 'Cash on Delivery' ? 'selected' : '' ?>>Cash on Delivery</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fa-solid fa-tag"></i> Order Status <span class="required">*</span></label>
                            <select name="status" class="form-control status-select" required>
                                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $status == 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $status == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fa-solid fa-note-sticky"></i> Order Notes</label>
                            <textarea name="notes" class="form-control" placeholder="Special instructions or notes..."><?= htmlspecialchars($notes) ?></textarea>
                        </div>

                        <!-- Gift Options -->
                        <div class="form-group">
                            <label><i class="fa-solid fa-gift"></i> Gift Options</label>
                            <div class="status-toggle">
                                <label class="status-option">
                                    <input type="radio" name="is_gift" value="0" <?= $is_gift == 0 ? 'checked' : '' ?> onchange="toggleGiftOptions(false)">
                                    <i class="fa-solid fa-box"></i> Regular Order
                                </label>
                                <label class="status-option">
                                    <input type="radio" name="is_gift" value="1" <?= $is_gift == 1 ? 'checked' : '' ?> onchange="toggleGiftOptions(true)">
                                    <i class="fa-solid fa-gift"></i> Gift Order
                                </label>
                            </div>
                        </div>

                        <!-- Gift Options Container -->
                        <div id="giftOptions" class="gift-options-container" style="display: <?= $is_gift == 1 ? 'block' : 'none' ?>;">
                            <div class="form-group">
                                <label><i class="fa-solid fa-box-open"></i> Gift Box Style <span class="required">*</span></label>
                                <div class="gift-boxes">
                                    <div class="gift-box-item <?= $gift_box == 'box.png' ? 'selected' : '' ?>" data-box="box.png" data-price="5.00" onclick="selectGiftBox(this, 'box.png', 5.00)">
                                        <img src="images/box.png" class="gift-box-img" alt="Classic Box">
                                        <div class="gift-box-name">Classic Box</div>
                                        <div class="gift-box-price">$5.00</div>
                                    </div>
                                    <div class="gift-box-item <?= $gift_box == 'heartsbag.png' ? 'selected' : '' ?>" data-box="heartsbag.png" data-price="7.50" onclick="selectGiftBox(this, 'heartsbag.png', 7.50)">
                                        <img src="images/heartsbag.png" class="gift-box-img" alt="Heart Bag">
                                        <div class="gift-box-name">Heart Bag</div>
                                        <div class="gift-box-price">$7.50</div>
                                    </div>
                                    <div class="gift-box-item <?= $gift_box == 'teddywrap.png' ? 'selected' : '' ?>" data-box="teddywrap.png" data-price="10.00" onclick="selectGiftBox(this, 'teddywrap.png', 10.00)">
                                        <img src="images/teddywrap.png" class="gift-box-img" alt="Teddy Wrap">
                                        <div class="gift-box-name">Teddy Wrap</div>
                                        <div class="gift-box-price">$10.00</div>
                                    </div>
                                </div>
                                <input type="hidden" name="gift_box" id="giftBoxInput" value="<?= htmlspecialchars($gift_box) ?>">
                                <input type="hidden" name="gift_wrap_price" id="giftWrapPriceInput" value="<?= $gift_wrap_price ?>">
                                <div class="help-text">
                                    <i class="fa-solid fa-info-circle"></i> Choose a gift box style - each style has a fixed price
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fa-regular fa-message"></i> Gift Message</label>
                                <textarea name="gift_message" class="form-control" rows="3" placeholder="Write a heartfelt message for the recipient..."><?= htmlspecialchars($gift_message) ?></textarea>
                                <div class="help-text">This message will be included with the gift</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Products -->
                    <div>
                        <label><i class="fa-solid fa-box"></i> Select Products <span class="required">*</span></label>
                        <div class="products-table-container">
                            <table class="products-select-table">
                                <thead>
                                <tr>
                                    <th style="width: 40px"></th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                                </thead>
                                <tbody id="productsList">
                                <?php foreach($products as $id => $product):
                                    $selected_qty = 0;
                                    $selected_subtotal = 0;
                                    foreach($selected_products as $selected) {
                                        if ($selected['name'] == $product['name']) {
                                            $selected_qty = $selected['quantity'];
                                            $selected_subtotal = $selected['subtotal'] ?? ($product['price'] * $selected_qty);
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr data-price="<?= $product['price'] ?>" data-id="<?= $id ?>">
                                        <td>
                                            <input type="checkbox" name="product_ids[]" value="<?= $id ?>" class="product-select" <?= $selected_qty > 0 ? 'checked' : '' ?> onchange="updateSummary()">
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <img src="<?= $product['image'] ?>" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                                <div>
                                                    <strong><?= $product['name'] ?></strong>
                                                    <div style="font-size: 11px; color: var(--secondary-text);"><?= $product['category'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="product-price">$<?= number_format($product['price'], 2) ?></td>
                                        <td>
                                            <input type="number" name="quantities[]" class="qty-input" value="<?= $selected_qty ?: 1 ?>" min="1" max="99" onchange="updateSummary()" <?= $selected_qty > 0 ? '' : 'disabled' ?>>
                                        </td>
                                        <td class="subtotal" style="font-weight: 600;">$<?= number_format($selected_subtotal ?: $product['price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Items Count:</span>
                                <span id="itemsCount"><?= $order['items_count'] ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotal">$<?= number_format($order['subtotal'] ?? $order['total'], 2) ?></span>
                            </div>
                            <div class="summary-row" id="giftWrapRow" style="display: <?= ($is_gift == 1 && $gift_wrap_price > 0) ? 'flex' : 'none' ?>;">
                                <span>Gift Wrap:</span>
                                <span id="giftWrapAmount">$<?= number_format($gift_wrap_price, 2) ?></span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total:</span>
                                <span id="total">$<?= number_format($order['total'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Update Order
                    </button>
                    <a href="order-details-admin.php?id=<?= $order_id ?>" class="btn-cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    let currentGiftPrice = <?= $gift_wrap_price ?>;

    function updateSummary() {
        let itemsCount = 0;
        let subtotal = 0;

        const rows = document.querySelectorAll('#productsList tr');
        rows.forEach(row => {
            const checkbox = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.qty-input');
            const price = parseFloat(row.getAttribute('data-price'));
            const subtotalCell = row.querySelector('.subtotal');

            if (checkbox.checked) {
                qtyInput.disabled = false;
                const qty = parseInt(qtyInput.value) || 1;
                const itemSubtotal = price * qty;
                subtotal += itemSubtotal;
                itemsCount += qty;
                subtotalCell.textContent = '$' + itemSubtotal.toFixed(2);
            } else {
                qtyInput.disabled = true;
                qtyInput.value = 1;
                subtotalCell.textContent = '$' + price.toFixed(2);
            }
        });

        let total = subtotal + currentGiftPrice;

        document.getElementById('itemsCount').textContent = itemsCount;
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
    }

    // Gift box selection
    function selectGiftBox(element, boxValue, price) {
        // Remove selected class from all boxes
        document.querySelectorAll('.gift-box-item').forEach(item => {
            item.classList.remove('selected');
        });

        // Add selected class to clicked box
        element.classList.add('selected');

        // Set hidden inputs
        document.getElementById('giftBoxInput').value = boxValue;
        document.getElementById('giftWrapPriceInput').value = price;

        // Update current gift price
        currentGiftPrice = price;

        // Show gift wrap row and update amount
        const giftWrapRow = document.getElementById('giftWrapRow');
        const giftWrapAmount = document.getElementById('giftWrapAmount');

        giftWrapRow.style.display = 'flex';
        giftWrapAmount.textContent = '$' + price.toFixed(2);

        // Update total
        updateSummary();
    }

    // Toggle gift options
    function toggleGiftOptions(isGift) {
        const giftOptions = document.getElementById('giftOptions');
        const giftWrapRow = document.getElementById('giftWrapRow');

        if (isGift) {
            giftOptions.style.display = 'block';
        } else {
            giftOptions.style.display = 'none';
            giftWrapRow.style.display = 'none';

            // Reset gift selection
            document.querySelectorAll('.gift-box-item').forEach(item => {
                item.classList.remove('selected');
            });
            document.getElementById('giftBoxInput').value = '';
            document.getElementById('giftWrapPriceInput').value = '0';
            currentGiftPrice = 0;

            // Update total
            updateSummary();
        }
    }

    // Enable/disable quantity inputs based on checkbox
    document.querySelectorAll('.product-select').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const qtyInput = row.querySelector('.qty-input');
            qtyInput.disabled = !this.checked;
            if (!this.checked) {
                qtyInput.value = 1;
            }
            updateSummary();
        });
    });

    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', updateSummary);
        input.addEventListener('input', updateSummary);
    });

    // Initial update
    updateSummary();
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