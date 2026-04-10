<?php
// print-order.php
session_start();
require_once 'orders-array.php';

// Get order ID from query string
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate order exists
if (!isset($orders[$orderId])) {
    die('<h2>Order not found</h2><a href="orders.php">Back to Orders</a>');
}

$order = $orders[$orderId];

// Calculate subtotal (sum of products without gift wrap)
$subtotal = 0;
foreach ($order['products'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$giftWrap = isset($order['gift_wrap_price']) ? (float)$order['gift_wrap_price'] : 0;
$grandTotal = $subtotal + $giftWrap;

// Format date
$date = new DateTime($order['date']);
$formattedDate = $date->format('F j, Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Order #<?php echo htmlspecialchars($order['order_number']); ?> - Teddy Customizer</title>
    <!-- Google Fonts + Bootstrap Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ========== LIGHT COLORS (matching orders page) ========== */
        :root {
            --cream: #FFF5E1;
            --pink: #F8BBD0;
            --lavender: #E6E6FA;
            --primary: #F8BBD0;
            --bg-color: #FFF5E1;
            --text-color: #333;
            --secondary-text: #777;
            --card-bg: #ffffff;
            --nav-bg: #ffffff;
            --shadow: rgba(0,0,0,0.1);
            --success-green: #2e7d64;
            --border-color: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            padding: 30px 20px;
        }

        /* Print wrapper container */
        .print-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* Action buttons (visible only on screen) */
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .btn-print, .btn-close {
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .btn-print {
            background: #2c3e50;
            color: white;
        }

        .btn-print:hover {
            background: #1e2b38;
            transform: translateY(-2px);
        }

        .btn-close {
            background: #e9ecef;
            color: #4a4e69;
        }

        .btn-close:hover {
            background: #dee2e6;
            transform: translateY(-2px);
        }

        /* ========== Order Invoice Document (light theme) ========== */
        .order-invoice {
            background: var(--card-bg);
            border-radius: 32px;
            box-shadow: 0 4px 15px var(--shadow);
            overflow: hidden;
            padding: 30px 35px 40px;
            transition: all 0.2s;
            border: 1px solid rgba(248, 187, 208, 0.3);
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px dashed var(--pink);
            padding-bottom: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .brand h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #2c3e50;
            letter-spacing: -0.3px;
        }

        .brand p {
            color: var(--secondary-text);
            font-size: 13px;
        }

        .order-badge {
            background: var(--primary);
            padding: 8px 20px;
            border-radius: 60px;
            font-weight: 700;
            font-size: 20px;
            color: #2d2f36;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Order Information Grid */
        .order-info-grid {
            display: flex;
            justify-content: space-between;
            background: #f8f9fc;
            padding: 18px 22px;
            border-radius: 24px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-card {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--secondary-text);
        }

        .info-value {
            font-weight: 700;
            font-size: 16px;
            color: #1e2a3e;
        }

        /* Products Section */
        .products-section {
            margin-bottom: 35px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            border-left: 5px solid var(--pink);
            padding-left: 15px;
            margin-bottom: 20px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .order-table th {
            background: #f1f3f5;
            padding: 14px 12px;
            text-align: center;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid var(--border-color);
        }

        .order-table td {
            padding: 14px 12px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            color: #3a3f4b;
        }

        .order-table tr:last-child td {
            border-bottom: none;
        }

        /* Gift message box */
        .gift-section {
            background: #fefaf5;
            border-radius: 20px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #ffe6d5;
        }

        .gift-section h4 {
            color: #d48a5c;
            margin-bottom: 10px;
        }

        /* Payment Summary */
        .summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }

        .summary-card {
            width: 320px;
            background: #fefaf5;
            border-radius: 24px;
            padding: 20px 25px;
            border: 1px solid #ffe6d5;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .summary-row.total {
            margin-top: 15px;
            padding-top: 12px;
            border-top: 2px solid var(--pink);
            font-weight: 800;
            font-size: 20px;
        }

        /* Footer Thanks */
        .footer-thanks {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
            color: #5f6c80;
            font-size: 13px;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-completed { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-processing { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .status-pending { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .status-shipped { background: rgba(156, 39, 176, 0.2); color: #9C27B0; }
        .status-cancelled { background: rgba(244, 67, 54, 0.2); color: #F44336; }
        .status-delivered { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }

        /* ========== PRINT STYLES ========== */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .action-buttons, .btn-print, .btn-close {
                display: none !important;
            }
            .order-invoice {
                box-shadow: none;
                padding: 15px;
                border-radius: 0;
                background: white;
                color: black;
            }
            .order-info-grid {
                background: #f3f3f3;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-card, .gift-section {
                background: #fafafa;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .brand h2, .order-badge {
                color: black;
            }
            .order-table th {
                background: #eaeef2;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .footer-thanks {
                color: #555;
            }
        }
    </style>
</head>
<body>
<div class="print-wrapper">
    <!-- Top action buttons -->
    <div class="action-buttons">
        <button class="btn-print" onclick="window.print();">
            <i class="bi bi-printer-fill"></i> Print Order
        </button>
        <button class="btn-close" onclick="window.close();">
            <i class="bi bi-x-lg"></i> Close
        </button>
    </div>

    <!-- Order Invoice -->
    <div class="order-invoice">
        <div class="invoice-header">
            <div class="brand">
                <h2>🐻 Teddy Customizer</h2>
                <p>Custom Teddy Store | Personalized Plush Toys</p>
            </div>
            <div class="order-badge">
                <i class="bi bi-receipt"></i> Order #<?php echo htmlspecialchars($order['order_number']); ?>
            </div>
        </div>

        <div class="order-info-grid">
            <div class="info-card">
                <span class="info-label"><i class="bi bi-calendar3"></i> Order Date</span>
                <span class="info-value"><?php echo $formattedDate; ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><i class="bi bi-person-circle"></i> Customer Name</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer']); ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><i class="bi bi-envelope"></i> Email Address</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><i class="bi bi-credit-card"></i> Payment Method</span>
                <span class="info-value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
            </div>
            <div class="info-card">
                <span class="info-label"><i class="bi bi-truck"></i> Order Status</span>
                <span class="info-value">
                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Products Table -->
        <div class="products-section">
            <div class="section-title">🧸 Order Items</div>
            <table class="order-table">
                <thead>
                <tr>
                    <th>Product / Design</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($order['products'] as $item): ?>
                    <tr>
                        <td><i class="bi bi-gift-fill"></i> <?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Gift Information (if any) -->
        <?php if (!empty($order['is_gift']) && $order['is_gift'] === true): ?>
            <div class="gift-section">
                <h4><i class="bi bi-gift-fill"></i> Gift Options</h4>
                <?php if (!empty($order['gift_message'])): ?>
                    <p><strong>Gift Message:</strong><br> “<?php echo nl2br(htmlspecialchars($order['gift_message'])); ?>”</p>
                <?php endif; ?>
                <?php if (!empty($order['gift_box'])): ?>
                    <p><strong>Gift Box:</strong> <?php echo htmlspecialchars($order['gift_box']); ?></p>
                <?php endif; ?>
                <?php if ($giftWrap > 0): ?>
                    <p><strong>Gift Wrap Fee:</strong> $<?php echo number_format($giftWrap, 2); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Order Notes -->
        <?php if (!empty($order['notes'])): ?>
            <div class="gift-section" style="background: #f8f9fc;">
                <h4><i class="bi bi-chat-text"></i> Order Notes</h4>
                <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Payment Summary -->
        <div class="summary">
            <div class="summary-card">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <?php if ($giftWrap > 0): ?>
                    <div class="summary-row">
                        <span>Gift Wrap</span>
                        <span>$<?php echo number_format($giftWrap, 2); ?></span>
                    </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Grand Total</span>
                    <span>$<?php echo number_format($grandTotal, 2); ?></span>
                </div>
            </div>
        </div>

        <div class="footer-thanks">
            <i class="bi bi-heart-fill" style="color: #F8BBD0;"></i> Thank you for shopping with us! • Contact support for order inquiries
        </div>
    </div>
</div>

<script>
    // Optional close handling
    window.onafterprint = function() {
        // nothing needed
    };
</script>
</body>
</html>