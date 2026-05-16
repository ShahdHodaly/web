<?php
session_start();
require_once 'db.php';

$pageTitle  = "My Cart | Teddy Lap";
$pdo        = getDB();
$isLoggedIn = !empty($_SESSION['logged_in']);
$userId     = $_SESSION['user_id'] ?? null;

// ── معالجة AJAX ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'login_required']);
        exit;
    }

    // جيبي cart_id
    $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cartRow = $stmt->fetch();
    $cartId  = $cartRow ? $cartRow['cart_id'] : null;

    // ── تطبيق الكوبون ─────────────────────────────────────────
    if ($_POST['action'] === 'apply_coupon') {
        $couponCode = strtoupper(trim($_POST['code'] ?? ''));

        if (empty($couponCode)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
            exit;
        }

        // 1. البحث عن الكوبون في قاعدة البيانات
        $stmt = $pdo->prepare("SELECT * FROM Coupons WHERE code = ? AND status = 'active'");
        $stmt->execute([$couponCode]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            echo json_encode(['success' => false, 'message' => 'Invalid or inactive coupon code']);
            exit;
        }

        // 2. التحقق من تاريخ الصلاحية وحد الاستخدام
        $today = date('Y-m-d');
        if ($today < $coupon['start_date'] || $today > $coupon['expiry_date']) {
            echo json_encode(['success' => false, 'message' => 'Coupon is expired or not yet active']);
            exit;
        }
        if ($coupon['used_count'] >= $coupon['usage_limit']) {
            echo json_encode(['success' => false, 'message' => 'Coupon usage limit reached']);
            exit;
        }
        // 3. حساب الإجمالي الحالي للسلة (المنتجات العادية + الدبدوبات المخصصة)
        if (!$cartId) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }

        // حساب إجمالي المنتجات العادية
        $stmt = $pdo->prepare("SELECT SUM(p.price * ci.quantity) as subtotal 
                               FROM cart_items ci 
                               JOIN products p ON ci.product_id = p.product_id 
                               WHERE ci.cart_id = ?");
        $stmt->execute([$cartId]);
        $cartData = $stmt->fetch(PDO::FETCH_ASSOC);
        $subtotal = floatval($cartData['subtotal'] ?? 0);

        // إضافة إجمالي الدبدوبات المخصصة (Custom Teddies)
        $stmtCustom = $pdo->prepare("SELECT SUM(total_price * COALESCE(quantity, 1)) as custom_subtotal 
                                     FROM custom_teddies 
                                     WHERE user_id = ? AND is_saved = FALSE");
        $stmtCustom->execute([$userId]);
        $customData = $stmtCustom->fetch(PDO::FETCH_ASSOC);
        $subtotal += floatval($customData['custom_subtotal'] ?? 0);

        // 4. التحقق من الحد الأدنى للطلب (min_order)
        if ($coupon['min_order'] > 0 && $subtotal < $coupon['min_order']) {
            echo json_encode(['success' => false, 'message' => 'Minimum order amount is $' . $coupon['min_order']]);
            exit;
        }

        // 5. حساب قيمة الخصم
        $discountAmount = 0;
        if ($coupon['discount_type'] === 'percentage') {
            $discountAmount = ($subtotal * $coupon['discount_value']) / 100;
            // تطبيق الحد الأقصى للخصم (max_discount) إذا موجود
            if ($coupon['max_discount'] > 0 && $discountAmount > $coupon['max_discount']) {
                $discountAmount = $coupon['max_discount'];
            }
        } elseif ($coupon['discount_type'] === 'fixed') {
            $discountAmount = $coupon['discount_value'];
        } elseif ($coupon['discount_type'] === 'shipping') {
            // خصم الشحن سيتم حسابه لاحقاً في الشيك آوت
            $discountAmount = 0;
        }

        // التأكد أن الخصم لا يتجاوز الإجمالي
        if ($discountAmount > $subtotal) $discountAmount = $subtotal;
        $newTotal = $subtotal - $discountAmount;

        // 6. حفظ الكوبون في سلة اليوزر (جدول Cart)
        $stmt = $pdo->prepare("UPDATE Cart SET coupon_id = ? WHERE cart_id = ?");
        $stmt->execute([$coupon['coupon_id'], $cartId]);

        echo json_encode([
                'success' => true,
                'message' => 'Coupon applied successfully!',
                'discount_amount' => round($discountAmount, 2),
                'new_total' => round($newTotal, 2),
                'coupon_type' => $coupon['discount_type'],
                'coupon_code' => $couponCode
        ]);
        exit;
    }

    // ── إزالة الكوبون ─────────────────────────────────────────
    if ($_POST['action'] === 'remove_coupon') {
        if ($cartId) {
            $stmt = $pdo->prepare("UPDATE Cart SET coupon_id = NULL WHERE cart_id = ?");
            $stmt->execute([$cartId]);
        }
        echo json_encode(['success' => true, 'message' => 'Coupon removed']);
        exit;
    }

    // ── تحديث الكمية ─────────────────────────────────────────
    if ($_POST['action'] === 'update_qty') {
        $productId = (int)$_POST['product_id'];
        $change    = (int)$_POST['change'];

        if (!$cartId) { echo json_encode(['success' => false]); exit; }

        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $item = $stmt->fetch();

        if (!$item) { echo json_encode(['success' => false]); exit; }

        $newQty = $item['quantity'] + $change;
        if ($newQty <= 0) {
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")
                    ->execute([$cartId, $productId]);
        } else {
            $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?")
                    ->execute([$newQty, $cartId, $productId]);
        }

        echo json_encode(['success' => true, 'new_qty' => max(0, $newQty)]);
        exit;
    }

    // ── حذف منتج ─────────────────────────────────────────────
    if ($_POST['action'] === 'remove_item') {
        $productId = (int)$_POST['product_id'];
        if ($cartId) {
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")
                    ->execute([$cartId, $productId]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // ── حذف متعدد ────────────────────────────────────────────
    if ($_POST['action'] === 'remove_multiple') {
        $ids = json_decode($_POST['product_ids'] ?? '[]', true);
        if ($cartId && !empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([$cartId], array_map('intval', $ids));
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id IN ($placeholders)")
                    ->execute($params);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // ── تحديث كمية custom teddy ──────────────────────────────
    if ($_POST['action'] === 'update_custom_qty') {
        $customId = (int)($_POST['custom_id'] ?? 0);
        $qty      = max(1, (int)($_POST['qty'] ?? 1));
        $pdo->prepare("UPDATE custom_teddies SET quantity = ? WHERE custom_id = ? AND user_id = ?")
                ->execute([$qty, $customId, $userId]);
        echo json_encode(['success' => true, 'qty' => $qty]);
        exit;
    }

    // ── حفظ custom teddy في My Teddies (is_saved = TRUE) ──────
    if ($_POST['action'] === 'save_custom_to_teddies') {
        $customId = (int)($_POST['custom_id'] ?? 0);
        $pdo->prepare("UPDATE custom_teddies SET is_saved = TRUE WHERE custom_id = ? AND user_id = ?")
                ->execute([$customId, $userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ── حفظ custom teddy في My Teddies (إزالة من view الكارت بس) ──
    if ($_POST['action'] === 'save_custom_teddy') {
        echo json_encode(['success' => true]);
        exit;
    }

    // ── حذف custom teddy ─────────────────────────────────────
    if ($_POST['action'] === 'remove_custom') {
        $customId = (int)($_POST['custom_id'] ?? 0);
        $pdo->prepare("DELETE FROM custom_teddies WHERE custom_id = ? AND user_id = ?")
                ->execute([$customId, $userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ── نقل للـ wishlist ──────────────────────────────────────
    if ($_POST['action'] === 'move_to_wishlist') {
        $ids = json_decode($_POST['product_ids'] ?? '[]', true);
        if ($cartId && !empty($ids)) {
            foreach ($ids as $pid) {
                $pid = (int)$pid;
                $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?,?) ON CONFLICT DO NOTHING")
                        ->execute([$userId, $pid]);
                $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")
                        ->execute([$cartId, $pid]);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}

// ── جيبي محتوى الكارت من DB ──────────────────────────────────
$cartItems       = [];
$customCartItems = [];
$cartId          = null;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cartRow = $stmt->fetch();

    if ($cartRow) {
        $cartId = $cartRow['cart_id'];
        $stmt   = $pdo->prepare("
            SELECT ci.product_id AS id, ci.quantity,
                   p.name, p.price, p.image, p.description,
                   c.name AS category
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            JOIN categories cat ON p.category_id = cat.category_id
            JOIN categories c ON p.category_id = c.category_id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cartId]);
        $cartItems = $stmt->fetchAll();
    }

    // ── جيبي الدببة المخصصة من custom_teddies (فقط غير المحفوظة في My Teddies) ──
    $stmt = $pdo->prepare("
        SELECT custom_id, custom_name AS name, total_price AS price, config_json,
               COALESCE(quantity, 1) AS quantity
        FROM custom_teddies
        WHERE user_id = ? AND is_saved = FALSE
        ORDER BY custom_id DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $customCartItems = $stmt->fetchAll();
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
        .cart-container { padding: 120px 20px 50px; max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr; gap: 30px; transition: all 0.3s ease; }
        @media (max-width: 900px) { .cart-container { grid-template-columns: 1fr; padding-top: 100px; } }
        .page-header { grid-column: 1 / -1; text-align: center; margin-bottom: 20px; opacity: 0; animation: fadeDown 0.8s forwards; }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 42px; background: linear-gradient(45deg, #ff6b81, #ff9a9e, #fbc2eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px; }
        .page-header .back-link { color: var(--pink); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; margin-top: 10px; }
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .cart-items-section { display: flex; flex-direction: column; gap: 20px; }
        .cart-list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 0 5px; }
        .cart-list-header h3 { font-family: 'Poppins', sans-serif; color: var(--text-color); font-size: 20px; margin: 0; }
        .manage-toggle-btn { background: none; border: 2px solid var(--pink); color: var(--pink); padding: 6px 18px; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; }
        .manage-toggle-btn:hover, .manage-toggle-btn.active { background: var(--pink); color: #fff; }
        .manage-action-bar { display: none; justify-content: space-between; align-items: center; background: rgba(255,107,129,0.1); padding: 12px 20px; border-radius: 15px; margin-bottom: 15px; animation: fadeIn 0.3s ease; }
        .manage-action-bar.visible { display: flex; }
        .select-all-wrapper { display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: var(--text-color); user-select: none; }
        .action-buttons-group { display: flex; gap: 10px; }
        .action-btn { border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .action-btn.delete-btn { background: #ff4d4d; color: #fff; }
        .action-btn.delete-btn:hover { background: #e60000; transform: scale(1.05); }
        .action-btn.fav-btn { background: var(--lavender); color: #fff; }
        .action-btn.fav-btn:hover { background: #d896ff; transform: scale(1.05); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .cart-card { background: var(--card-bg); border-radius: 20px; padding: 20px; display: flex; gap: 15px; align-items: stretch; box-shadow: 0 10px 30px var(--shadow); position: relative; overflow: hidden; transition: all 0.4s cubic-bezier(0.25,1,0.5,1); opacity: 0; animation: slideIn 0.6s forwards; }
        .cart-card.removing { transform: translateX(100px); opacity: 0; max-height: 0; padding: 0; margin: 0; border: none; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        .select-circle { width: 22px; height: 22px; border-radius: 50%; border: 2px solid #ddd; align-self: center; flex-shrink: 0; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; color: transparent; z-index: 2; flex-shrink: 0; }
        .select-circle:hover { border-color: var(--pink); }
        .select-circle.selected { background-color: var(--pink); border-color: var(--pink); color: #fff; }
        .cart-content-link { flex: 1; display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit; transition: opacity 0.2s; }
        .cart-content-link:hover { opacity: 0.8; }
        .cart-img-box { width: 80px; height: 80px; background: #f8f8f8; border-radius: 15px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        body.dark-mode .cart-img-box { background: #333; }
        .cart-img-box img { max-width: 80%; max-height: 80%; object-fit: contain; }
        .cart-details { flex: 1; }
        .cart-details h3 { margin: 0 0 5px; color: var(--text-color); font-size: 18px; }
        .cart-details .category { color: var(--secondary-text); font-size: 12px; margin-bottom: 5px; display: block; }
        .cart-details .price { color: var(--pink); font-weight: bold; font-size: 18px; }
        .cart-actions-right { display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end; padding-bottom: 5px; z-index: 2; }
        .delete-btn-single { background: none; border: none; color: #ddd; cursor: pointer; font-size: 14px; transition: all 0.2s; margin-bottom: auto; margin-right: -5px; }
        .delete-btn-single:hover { color: #ff4d4d; transform: scale(1.1); }
        .quantity-controls { display: flex; align-items: center; gap: 8px; background: #f5f5f5; padding: 5px; border-radius: 20px; }
        body.dark-mode .quantity-controls { background: #333; }
        .qty-btn { width: 28px; height: 28px; border-radius: 50%; border: none; background: #fff; color: var(--text-color); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        body.dark-mode .qty-btn { background: #444; }
        .qty-btn:hover { background: var(--pink); color: #fff; }
        .qty-val { font-weight: 600; min-width: 20px; text-align: center; font-size: 14px; }
        .cart-summary { background: var(--card-bg); border-radius: 20px; padding: 30px; height: fit-content; box-shadow: 0 10px 30px var(--shadow); opacity: 0; animation: slideIn 0.8s 0.2s forwards; }
        .gift-btn { width: 100%; padding: 12px; margin-bottom: 20px; background: linear-gradient(45deg, #ff9a9e, #fbc2eb); color: #fff; border: none; border-radius: 50px; font-weight: bold; font-size: 16px; cursor: pointer; transition: all 0.3s; box-shadow: 0 5px 15px rgba(255,154,158,0.3); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .gift-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255,154,158,0.4); }
        .gift-mode-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; grid-column: 1 / -1; }
        .gift-mode-header .back-link { color: var(--pink); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; background: none; border: none; font-size: 16px; }
        .gift-details { background: rgba(255,107,129,0.05); border-radius: 20px; padding: 25px; animation: fadeIn 0.4s ease; }
        .gift-details h3 { font-family: 'Playfair Display', serif; color: var(--pink); margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px; }
        .gift-message-box { margin-bottom: 25px; }
        .gift-message-box label { display: block; margin-bottom: 8px; color: var(--text-color); font-weight: 500; }
        .gift-message-box textarea { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ddd; background: var(--bg-color); color: var(--text-color); font-family: 'Poppins', sans-serif; resize: vertical; min-height: 80px; }
        .gift-message-box textarea:focus { outline: none; border-color: var(--pink); }
        .wrap-options { display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between; }
        .wrap-option { flex: 1; min-width: 150px; background: var(--card-bg); border: 2px solid #eee; border-radius: 15px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.3s; opacity: 0.7; }
        body.dark-mode .wrap-option { border-color: #444; }
        .wrap-option:hover { transform: translateY(-3px); border-color: var(--pink); }
        .wrap-option.selected { border-color: var(--pink); opacity: 1; background: rgba(255,107,129,0.05); }
        .wrap-option img { width: 60px; height: 60px; object-fit: contain; margin-bottom: 10px; }
        .wrap-option span { display: block; font-weight: 600; color: var(--text-color); }
        .wrap-option small { color: var(--secondary-text); font-size: 12px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: var(--secondary-text); }
        .summary-row.total { border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; font-weight: bold; font-size: 20px; color: var(--text-color); }
        body.dark-mode .summary-row.total { border-top-color: #444; }
        .checkout-btn { width: 100%; padding: 15px; border-radius: 50px; background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff; border: none; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 20px; transition: all 0.3s; box-shadow: 0 10px 20px rgba(255,154,158,0.4); }
        .checkout-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(255,154,158,0.5); }
        .empty-cart { grid-column: 1 / -1; text-align: center; padding: 80px 20px; display: flex; flex-direction: column; align-items: center; animation: fadeDown 0.8s forwards; }
        .empty-cart i { font-size: 100px; color: var(--pink); margin-bottom: 20px; opacity: 0.8; }
        .empty-cart h2 { color: var(--text-color); margin-bottom: 10px; font-family: 'Playfair Display', serif; }
        .empty-cart p { color: var(--secondary-text); margin-bottom: 30px; font-size: 18px; }
        .shop-btn { background: var(--primary); color: #fff; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: background 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .shop-btn:hover { background: var(--pink); transform: translateY(-2px); }
        .login-prompt { grid-column: 1/-1; text-align: center; padding: 60px 20px; }
        .login-prompt i { font-size: 60px; color: var(--pink); margin-bottom: 20px; opacity: 0.8; }
        .login-prompt h2 { font-family: 'Playfair Display', serif; color: var(--text-color); margin-bottom: 10px; }
        .login-prompt p { color: var(--secondary-text); margin-bottom: 25px; }
        .login-btn { background: var(--pink); color: #fff; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; }
        .toast-container { position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 10000; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .toast-message { background: linear-gradient(45deg, #ff9a9e, #ff6b81); color: #fff; padding: 15px 25px; border-radius: 50px; box-shadow: 0 10px 25px rgba(255,107,129,0.4); font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 10px; opacity: 0; transform: translateY(-30px); animation: toastIn 0.5s forwards; }
        .toast-message.toast-out { animation: toastOut 0.5s forwards; }
        @keyframes toastIn { to { opacity: 1; transform: translateY(0); } }
        @keyframes toastOut { to { opacity: 0; transform: translateY(-30px); } }

        /* Coupon Styles */
        .coupon-box { margin-bottom: 20px; background: var(--bg-color); padding: 15px; border-radius: 15px; }
        .coupon-input-group { display: flex; gap: 10px; }
        .coupon-input-group input { flex: 1; padding: 10px 15px; border-radius: 10px; border: 1px solid #ddd; background: var(--card-bg); color: var(--text-color); outline: none; font-family: 'Poppins', sans-serif; }
        .coupon-input-group input:focus { border-color: var(--pink); }
        .coupon-input-group button { padding: 10px 20px; background: var(--pink); color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .coupon-input-group button:hover { background: var(--primary); }
        .coupon-msg { margin-top: 8px; font-size: 13px; display: none; }
        .coupon-applied { display: none; margin-top: 10px; padding: 10px; background: rgba(76, 175, 80, 0.1); border-radius: 10px; justify-content: space-between; align-items: center; }
        .coupon-applied span { color: #4CAF50; font-weight: 600; font-size: 14px; }
        .coupon-applied button { background: none; border: none; color: red; cursor: pointer; font-weight: bold; font-size: 12px; }
        .summary-row.discount { color: #4CAF50; font-weight: 600; }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="cart-container" id="cartContainer">

    <?php if (!$isLoggedIn): ?>
        <div class="login-prompt">
            <i class="fa-solid fa-cart-shopping"></i>
            <h2>Your Cart Awaits!</h2>
            <p>Please login to view your cart and continue shopping.</p>
            <a href="auth.php" class="login-btn"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
        </div>

    <?php elseif (empty($cartItems) && empty($customCartItems)): ?>
        <div class="empty-cart">
            <i class="fa-solid fa-face-sad-tear"></i>
            <h2>Your Cart is Empty</h2>
            <p>Looks like you haven't added any teddies yet.</p>
            <a href="shop.php" class="shop-btn">Start Shopping</a>
        </div>

    <?php else: ?>
        <div class="page-header">
            <h1>Shopping Cart</h1>
            <a href="shop.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Continue Shopping
            </a>
        </div>

        <div class="cart-items-section">
            <div class="cart-list-header">
                <h3>Your Items (<?= count($cartItems) + count($customCartItems) ?>)</h3>
                <button class="manage-toggle-btn" id="manageBtn" onclick="toggleManageMode()">Manage</button>
            </div>

            <div class="manage-action-bar" id="actionBar">
                <div class="select-all-wrapper" onclick="toggleSelectAll()">
                    <div class="select-circle" id="selectAllCircle" style="width:20px;height:20px;"></div>
                    <span>Select All</span>
                </div>
                <div class="action-buttons-group">
                    <button class="action-btn delete-btn" onclick="deleteSelected()">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                    <button class="action-btn fav-btn" onclick="moveToWishlist()">
                        <i class="fa-regular fa-heart"></i> Move to Fav
                    </button>
                </div>
            </div>

            <?php foreach ($cartItems as $item): ?>
                <div class="cart-card" id="card-<?= $item['id'] ?>" data-price="<?= $item['price'] ?>">
                    <div class="select-circle" id="circle-<?= $item['id'] ?>"
                         onclick="toggleSelect(<?= $item['id'] ?>)"></div>

                    <a href="product_details.php?id=<?= $item['id'] ?>" class="cart-content-link">
                        <div class="cart-img-box">
                            <img src="<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='https://ui-avatars.com/api/?name=Toy&background=ff9a9e&color=fff'">
                        </div>
                        <div class="cart-details">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <span class="category"><?= htmlspecialchars($item['category']) ?></span>
                            <div class="price">$<?= number_format($item['price'], 2) ?></div>
                        </div>
                    </a>

                    <div class="cart-actions-right">
                        <button class="delete-btn-single" onclick="removeItem(<?= $item['id'] ?>)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <span class="qty-val" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                            <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, 1)">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach ($customCartItems as $cItem):
                $cfg = $cItem['config_json'] ? json_decode($cItem['config_json'], true) : null;
                $baseImg = $cfg ? ($cfg['color']['img'] ?? 'images/brown.png') : 'images/brown.png';
                ?>
                <div class="cart-card" id="card-custom-<?= $cItem['custom_id'] ?>" data-price="<?= $cItem['price'] ?>" data-type="custom">
                    <div class="select-circle" id="circle-custom-<?= $cItem['custom_id'] ?>"
                         onclick="toggleSelectCustom(<?= $cItem['custom_id'] ?>)">
                    </div>

                    <a href="custom_details.php?id=<?= $cItem['custom_id'] ?>" class="cart-content-link">
                        <div class="cart-img-box" style="position:relative; overflow:hidden;">
                            <?php if ($cfg): ?>
                                <img src="<?= htmlspecialchars($cfg['color']['img'] ?? 'images/brown.png') ?>"
                                     style="position:absolute;width:100%;height:100%;object-fit:contain;z-index:1;"
                                     alt="base">
                                <?php if (!empty($cfg['outfit'])): $f=basename($cfg['outfit']['img'],'.png'); ?>
                                    <img src="<?= htmlspecialchars($cfg['outfit']['img']) ?>"
                                         style="position:absolute;width:50%;top:55%;left:50%;transform:translate(-50%,-50%);z-index:2;object-fit:contain;"
                                         alt="outfit">
                                <?php endif; ?>
                                <?php if (!empty($cfg['shoes'])): ?>
                                    <img src="<?= htmlspecialchars($cfg['shoes']['img']) ?>"
                                         style="position:absolute;width:40%;top:85%;left:48%;transform:translate(-50%,-50%);z-index:3;object-fit:contain;"
                                         alt="shoes">
                                <?php endif; ?>
                                <?php if (!empty($cfg['acc'])): ?>
                                    <img src="<?= htmlspecialchars($cfg['acc']['img']) ?>"
                                         style="position:absolute;width:30%;top:18%;left:15%;transform:translate(-50%,-50%);z-index:4;object-fit:contain;"
                                         alt="acc">
                                <?php endif; ?>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($baseImg) ?>"
                                     style="max-width:80%;max-height:80%;object-fit:contain;"
                                     alt="teddy">
                            <?php endif; ?>
                        </div>
                        <div class="cart-details">
                            <h3><?= htmlspecialchars($cItem['name'] ?: 'Custom Teddy') ?> <span style="background:var(--lavender);color:#fff;padding:2px 8px;border-radius:10px;font-size:10px;">Custom</span></h3>
                            <span class="category">Customized Teddy Bear</span>
                            <div class="price">$<?= number_format($cItem['price'], 2) ?></div>
                        </div>
                    </a>

                    <div class="cart-actions-right">
                        <button class="delete-btn-single" onclick="removeCustomItem(<?= $cItem['custom_id'] ?>)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateCustomQty(<?= $cItem['custom_id'] ?>, -1)"><i class="fa-solid fa-minus"></i></button>
                            <span class="qty-val" id="customqty-<?= $cItem['custom_id'] ?>"><?= $cItem['quantity'] ?></span>
                            <button class="qty-btn" onclick="updateCustomQty(<?= $cItem['custom_id'] ?>, 1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary" id="summaryBox">
            <button class="gift-btn" onclick="toggleGiftMode()">
                <i class="fa-solid fa-gift"></i> Send as Gift
            </button>

            <h3 style="margin-bottom:20px; color:var(--text-color);">Order Summary</h3>

            <!-- Coupon Section -->
            <div class="coupon-box">
                <div class="coupon-input-group" id="couponInputBox">
                    <input type="text" id="couponInput" placeholder="Enter coupon code">
                    <button onclick="applyCoupon()">Apply</button>
                </div>
                <p class="coupon-msg" id="couponMessage"></p>
                <div class="coupon-applied" id="couponAppliedBox">
                    <span id="appliedCouponText"></span>
                    <button onclick="removeCoupon()">Remove</button>
                </div>
            </div>

            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotalVal">$0.00</span>
            </div>

            <!-- Discount Row -->
            <div class="summary-row discount" id="discountRow" style="display: none;">
                <span>Discount</span>
                <span id="discountValue">-$0.00</span>
            </div>

            <div class="summary-row" id="giftWrapRow" style="display:none;">
                <span id="giftWrapLabel">Gift Wrap</span>
                <span id="giftWrapPrice">+$0.00</span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span style="color:#28a745;">Free</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span id="totalVal" style="color:var(--pink);">$0.00</span>
            </div>

            <button class="checkout-btn" id="checkoutBtn" onclick="checkout()">
                Proceed to Checkout
            </button>
        </div>

        <div id="giftDetails" style="display:none;">
            <div class="gift-details">
                <h3><i class="fa-solid fa-gift"></i> Gift Details</h3>
                <div class="gift-message-box">
                    <label>Message (optional)</label>
                    <textarea id="giftMessage" placeholder="Write your gift message here..."
                              oninput="giftData.message = this.value"></textarea>
                </div>
                <div class="wrap-options">
                    <div class="wrap-option" data-wrap="box" data-price="5" onclick="selectWrap(this)">
                        <img src="images/box.png" alt="Classic Box">
                        <span>Classic Box</span>
                        <small>+$5.00</small>
                    </div>
                    <div class="wrap-option" data-wrap="teddywrap" data-price="7" onclick="selectWrap(this)">
                        <img src="images/teddywrap.png" alt="Teddy Wrap">
                        <span>Teddy Wrap</span>
                        <small>+$7.00</small>
                    </div>
                    <div class="wrap-option" data-wrap="heartsbag" data-price="6" onclick="selectWrap(this)">
                        <img src="images/heartsbag.png" alt="Hearts Bag">
                        <span>Hearts Bag</span>
                        <small>+$6.00</small>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<script>
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    const cartItems       = <?= json_encode($cartItems) ?>;
    const customCartItems = <?= json_encode($customCartItems) ?>;

    let selectedItems       = new Set(cartItems.map(i => String(i.id)));
    let selectedCustomItems = new Set(customCartItems.map(i => String(i.custom_id)));
    let isManaging   = false;
    let isGiftMode   = false;
    let giftData     = { message: '', wrap: 'none', wrapPrice: 0 };

    // متغير لحفظ قيمة الخصم الحالي
    let currentDiscountAmount = 0;

    function updateSummary() {
        let subtotal = 0;
        cartItems.forEach(item => {
            if (selectedItems.has(String(item.id))) {
                const qty = parseInt(document.getElementById('qty-' + item.id)?.textContent || item.quantity);
                subtotal += parseFloat(item.price) * qty;
            }
        });
        customCartItems.forEach(item => {
            if (selectedCustomItems.has(String(item.custom_id))) {
                const qty = item._qty || item.quantity || 1;
                subtotal += parseFloat(item.price) * qty;
            }
        });

        // حساب الإجمالي بعد خصم الكوبون وهداية التغليف
        let total = subtotal - currentDiscountAmount + giftData.wrapPrice;
        if (total < 0) total = 0; // لضمان عدم وجود قيمة سالبة

        const subtotalEl = document.getElementById('subtotalVal');
        const totalEl    = document.getElementById('totalVal');
        if (subtotalEl) subtotalEl.textContent = '$' + subtotal.toFixed(2);
        if (totalEl)    totalEl.textContent    = '$' + total.toFixed(2);

        // تحديث عرض خصم الكوبون
        const discountRow = document.getElementById('discountRow');
        const discountValEl = document.getElementById('discountValue');
        if (currentDiscountAmount > 0) {
            if(discountRow) discountRow.style.display = 'flex';
            if(discountValEl) discountValEl.textContent = '-$' + currentDiscountAmount.toFixed(2);
        } else {
            if(discountRow) discountRow.style.display = 'none';
        }
    }

    function toggleManageMode() {
        isManaging = !isManaging;
        const btn = document.getElementById('manageBtn');
        const bar = document.getElementById('actionBar');
        if (btn) { btn.textContent = isManaging ? 'Done' : 'Manage'; btn.classList.toggle('active', isManaging); }
        if (bar) bar.classList.toggle('visible', isManaging);
    }

    function toggleSelect(id) {
        id = String(id);
        const circle = document.getElementById('circle-' + id);
        if (selectedItems.has(id)) {
            selectedItems.delete(id);
            if (circle) { circle.classList.remove('selected'); circle.innerHTML = ''; }
        } else {
            selectedItems.add(id);
            if (circle) { circle.classList.add('selected'); circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;color:#fff;"></i>'; }
        }
        updateSummary();
        updateSelectAllUI();
    }

    function toggleSelectAll() {
        const allIds = cartItems.map(i => String(i.id));
        const allCustomIds = customCartItems.map(i => String(i.custom_id));
        const allSel = allIds.every(id => selectedItems.has(id)) &&
            allCustomIds.every(id => selectedCustomItems.has(id));

        allIds.forEach(id => {
            const circle = document.getElementById('circle-' + id);
            if (allSel) {
                selectedItems.delete(id);
                if (circle) { circle.classList.remove('selected'); circle.innerHTML = ''; }
            } else {
                selectedItems.add(id);
                if (circle) { circle.classList.add('selected'); circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;color:#fff;"></i>'; }
            }
        });

        allCustomIds.forEach(id => {
            const circle = document.getElementById('circle-custom-' + id);
            if (allSel) {
                selectedCustomItems.delete(id);
                if (circle) { circle.classList.remove('selected'); circle.innerHTML = ''; }
            } else {
                selectedCustomItems.add(id);
                if (circle) { circle.classList.add('selected'); circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;color:#fff;"></i>'; }
            }
        });

        updateSummary();
        updateSelectAllUI();
    }

    function updateSelectAllUI() {
        const allIds = cartItems.map(i => String(i.id));
        const allCustomIds = customCartItems.map(i => String(i.custom_id));
        const allItems = allIds.length + allCustomIds.length;
        const allSel = allItems > 0 &&
            allIds.every(id => selectedItems.has(id)) &&
            allCustomIds.every(id => selectedCustomItems.has(id));
        const circle = document.getElementById('selectAllCircle');
        if (!circle) return;
        circle.classList.toggle('selected', allSel);
        circle.innerHTML = allSel ? '<i class="fa-solid fa-check" style="font-size:10px;color:#fff;"></i>' : '';
    }

    function updateQty(productId, change) {
        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update_qty&product_id=${productId}&change=${change}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const qtyEl = document.getElementById('qty-' + productId);
                    if (data.new_qty <= 0) {
                        const card = document.getElementById('card-' + productId);
                        if (card) { card.classList.add('removing'); setTimeout(() => { card.remove(); updateSummary(); }, 400); }
                        const idx = cartItems.findIndex(i => String(i.id) === String(productId));
                        if (idx !== -1) cartItems.splice(idx, 1);
                        selectedItems.delete(String(productId));
                    } else {
                        if (qtyEl) qtyEl.textContent = data.new_qty;
                        const item = cartItems.find(i => String(i.id) === String(productId));
                        if (item) item.quantity = data.new_qty;
                    }
                    updateSummary();
                }
            });
    }

    function removeItem(productId) {
        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=remove_item&product_id=${productId}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById('card-' + productId);
                    if (card) { card.classList.add('removing'); setTimeout(() => { card.remove(); updateSummary(); }, 400); }
                    const idx = cartItems.findIndex(i => String(i.id) === String(productId));
                    if (idx !== -1) cartItems.splice(idx, 1);
                    selectedItems.delete(String(productId));
                    if (cartItems.length === 0 && customCartItems.length === 0) location.reload();
                }
            });
    }

    function deleteSelected() {
        if (selectedItems.size === 0 && selectedCustomItems.size === 0) {
            showToast("Please select items to delete!"); return;
        }
        const promises = [];
        if (selectedItems.size > 0) {
            promises.push(fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove_multiple&product_ids=${JSON.stringify(Array.from(selectedItems))}`
            }));
        }
        selectedCustomItems.forEach(customId => {
            promises.push(fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=remove_custom&custom_id=' + customId
            }));
        });
        Promise.all(promises).then(() => {
            selectedItems.forEach(id => {
                document.getElementById('card-' + id)?.remove();
                const idx = cartItems.findIndex(i => String(i.id) === id);
                if (idx !== -1) cartItems.splice(idx, 1);
            });
            selectedItems.clear();
            selectedCustomItems.forEach(id => {
                document.getElementById('card-custom-' + id)?.remove();
                const idx = customCartItems.findIndex(i => String(i.custom_id) === id);
                if (idx !== -1) customCartItems.splice(idx, 1);
            });
            selectedCustomItems.clear();
            updateSummary();
            if (cartItems.length === 0 && customCartItems.length === 0) location.reload();
        });
    }

    function moveToWishlist() {
        if (selectedItems.size === 0 && selectedCustomItems.size === 0) {
            showToast("Please select items to move!"); return;
        }
        const promises = [];
        if (selectedItems.size > 0) {
            promises.push(fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=move_to_wishlist&product_ids=${JSON.stringify(Array.from(selectedItems))}`
            }).then(r => r.json()));
        }
        selectedCustomItems.forEach(customId => {
            promises.push(fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=save_custom_to_teddies&custom_id=${customId}`
            }).then(r => r.json()));
        });
        Promise.all(promises).then(() => {
            selectedItems.forEach(id => {
                document.getElementById('card-' + id)?.remove();
                const idx = cartItems.findIndex(i => String(i.id) === id);
                if (idx !== -1) cartItems.splice(idx, 1);
            });
            selectedItems.clear();
            selectedCustomItems.forEach(id => {
                document.getElementById('card-custom-' + id)?.remove();
                const idx = customCartItems.findIndex(i => String(i.custom_id) === id);
                if (idx !== -1) customCartItems.splice(idx, 1);
            });
            selectedCustomItems.clear();
            updateSummary();
            showToast('Done! Items moved successfully ✅');
            if (cartItems.length === 0 && customCartItems.length === 0) setTimeout(() => location.reload(), 1000);
        });
    }

    function toggleGiftMode() {
        isGiftMode = !isGiftMode;
        const giftDetails = document.getElementById('giftDetails');
        const giftBtn     = document.querySelector('.gift-btn');
        if (giftDetails) giftDetails.style.display = isGiftMode ? 'block' : 'none';
        if (giftBtn) giftBtn.innerHTML = isGiftMode
            ? '<i class="fa-solid fa-arrow-left"></i> Back to Cart'
            : '<i class="fa-solid fa-gift"></i> Send as Gift';
        if (!isGiftMode) {
            giftData = { message: '', wrap: 'none', wrapPrice: 0 };
            const row = document.getElementById('giftWrapRow');
            if (row) row.style.display = 'none';
            document.querySelectorAll('.wrap-option').forEach(o => o.classList.remove('selected'));
            updateSummary();
        }
    }

    function selectWrap(el) {
        document.querySelectorAll('.wrap-option').forEach(o => o.classList.remove('selected'));
        el.classList.add('selected');
        giftData.wrap      = el.dataset.wrap;
        giftData.wrapPrice = parseFloat(el.dataset.price);
        giftData.isGift    = true;
        const row   = document.getElementById('giftWrapRow');
        const label = document.getElementById('giftWrapLabel');
        const price = document.getElementById('giftWrapPrice');
        if (row)   row.style.display = 'flex';
        if (label) label.textContent = 'Gift Wrap (' + el.querySelector('span').textContent + ')';
        if (price) price.textContent = '+$' + giftData.wrapPrice.toFixed(2);
        updateSummary();
    }

    function checkout() {
        if (selectedItems.size === 0 && selectedCustomItems.size === 0) {
            showToast("Please select items to checkout!"); return;
        }
        if (isGiftMode && giftData.wrap === 'none') {
            showToast("Please select a gift wrap option! 🎁"); return;
        }
        sessionStorage.setItem('teddy_selected_items', JSON.stringify(Array.from(selectedItems)));
        sessionStorage.setItem('teddy_selected_custom', JSON.stringify(Array.from(selectedCustomItems)));
        if (isGiftMode) {
            giftData.isGift  = true;
            giftData.message = document.getElementById('giftMessage')?.value || '';
            sessionStorage.setItem('teddy_gift_data', JSON.stringify(giftData));
        } else {
            giftData.isGift = false;
            sessionStorage.removeItem('teddy_gift_data');
        }
        window.location.href = 'checkout.php';
    }

    function removeCustomItem(customId) {
        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=remove_custom&custom_id=' + customId
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById('card-custom-' + customId);
                    if (card) { card.classList.add('removing'); setTimeout(() => { card.remove(); updateSummary(); }, 400); }
                    const idx = customCartItems.findIndex(i => String(i.custom_id) === String(customId));
                    if (idx !== -1) customCartItems.splice(idx, 1);
                    selectedCustomItems.delete(String(customId));
                    if (cartItems.length === 0 && customCartItems.length === 0) location.reload();
                }
            });
    }

    function toggleSelectCustom(customId) {
        customId = String(customId);
        const circle = document.getElementById('circle-custom-' + customId);
        if (selectedCustomItems.has(customId)) {
            selectedCustomItems.delete(customId);
            if (circle) { circle.classList.remove('selected'); circle.innerHTML = ''; }
        } else {
            selectedCustomItems.add(customId);
            if (circle) { circle.classList.add('selected'); circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;color:#fff;"></i>'; }
        }
        updateSummary();
    }

    // ── Update Custom Teddy Quantity ─────────────────────────
    function updateCustomQty(customId, change) {
        customId = String(customId);
        const qtyEl = document.getElementById('customqty-' + customId);
        if (!qtyEl) return;

        let qty = parseInt(qtyEl.textContent) + change;
        if (qty <= 0) {
            removeCustomItem(parseInt(customId));
            return;
        }

        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update_custom_qty&custom_id=${customId}&qty=${qty}`
        }).then(r => r.json()).then(data => {
            if (data.success) {
                qtyEl.textContent = qty;
                const item = customCartItems.find(i => String(i.custom_id) === customId);
                if (item) item._qty = qty;

                // حدّث عداد الـ navbar
                const badge = document.getElementById('cartCount');
                if (badge) {
                    let total = cartItems.reduce((s, i) => s + i.quantity, 0);
                    customCartItems.forEach(ci => total += ci._qty || ci.quantity || 1);
                    badge.textContent = total;
                    badge.classList.toggle('hide', total === 0);
                }
                updateSummary();
            }
        });
    }

    // ── Coupon Functions ─────────────────────────────────────
    function applyCoupon() {
        const code = document.getElementById('couponInput').value;
        const msgElement = document.getElementById('couponMessage');

        if (!code) {
            msgElement.style.display = 'block';
            msgElement.style.color = 'red';
            msgElement.innerText = 'Please enter a code';
            return;
        }

        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=apply_coupon&code=' + encodeURIComponent(code)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('couponInputBox').style.display = 'none';
                    document.getElementById('couponAppliedBox').style.display = 'flex';
                    document.getElementById('appliedCouponText').innerText = '✅ ' + data.coupon_code + ' Applied!';

                    currentDiscountAmount = data.discount_amount;
                    updateSummary();
                    showToast(data.message);
                } else {
                    msgElement.style.display = 'block';
                    msgElement.style.color = 'red';
                    msgElement.innerText = data.message;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function removeCoupon() {
        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=remove_coupon'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('couponInputBox').style.display = 'flex';
                    document.getElementById('couponAppliedBox').style.display = 'none';
                    document.getElementById('couponMessage').style.display = 'none';
                    document.getElementById('couponInput').value = '';

                    currentDiscountAmount = 0;
                    updateSummary();
                    showToast('Coupon removed successfully');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function showToast(message) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.add('toast-out'); setTimeout(() => toast.remove(), 500); }, 3000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        selectedItems = new Set(cartItems.map(i => String(i.id)));
        cartItems.forEach(item => {
            const circle = document.getElementById('circle-' + item.id);
            if (circle) {
                circle.classList.add('selected');
                circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;color:#fff;"></i>';
            }
        });

        selectedCustomItems = new Set(customCartItems.map(i => String(i.custom_id)));
        customCartItems.forEach(item => {
            item._qty = item.quantity || 1;
            const circle = document.getElementById('circle-custom-' + item.custom_id);
            if (circle) {
                circle.classList.add('selected');
                circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;color:#fff;"></i>';
            }
        });

        // تحديث عداد الـ navbar
        const badge = document.getElementById('cartCount');
        if (badge) {
            let total = cartItems.reduce((s, i) => s + i.quantity, 0);
            customCartItems.forEach(ci => total += ci._qty || ci.quantity || 1);
            badge.textContent = total;
            badge.classList.toggle('hide', total === 0);
        }

        updateSummary();
    });
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>