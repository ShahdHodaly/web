<?php
session_start();
require_once 'db.php';

// لوغ اوت
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: auth.php');
    exit;
}

// إذا مش مسجّل دخول، روّحه لصفحة اللوغ إن
if (empty($_SESSION['logged_in'])) {
    header('Location: auth.php');
    exit;
}

$pageTitle = "My Profile | Teddy Lap";
include 'products.php';

$pdo    = getDB();
$userId = $_SESSION['user_id'];

// ── جيبي بيانات اليوزر من DB ─────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$userName  = htmlspecialchars($user['name']   ?? $_SESSION['user_name']);
$email     = htmlspecialchars($user['email']  ?? $_SESSION['user_email']);
$phone     = htmlspecialchars($user['phone']  ?? '');
$joinDate  = $user['created_at'] ? date('Y', strtotime($user['created_at'])) : '2026';

// ── جيبي التقييمات من DB ─────────────────────────────────
$reviewsMap = [];
$stmtUserReviews = $pdo->prepare("
    SELECT r.product_id, r.order_id, r.rating, r.comment, r.status::text as status
    FROM reviews r
    WHERE r.user_id = ?
");
$stmtUserReviews->execute([$userId]);
$userReviewsList = $stmtUserReviews->fetchAll();

foreach ($userReviewsList as $rev) {
    $key = $rev['order_id'] . '_' . $rev['product_id'];
    $reviewsMap[$key] = [
            'rating' => (int)$rev['rating'],
            'comment' => $rev['comment'],
            'status' => $rev['status']
    ];
}

// ── جيبي طلبات اليوزر من DB ──────────────────────────────────
$stmtOrders = $pdo->prepare("
    SELECT o.*
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmtOrders->execute([$userId]);
$dbOrders = $stmtOrders->fetchAll();

// جيبي items لكل طلب
$ordersData = [];
foreach ($dbOrders as $order) {
    $stmtItems = $pdo->prepare("
        SELECT oi.*, p.name AS product_name, p.image AS product_image, p.product_id
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$order['order_id']]);
    $items = $stmtItems->fetchAll();
    $ordersData[] = [
            'id'     => $order['order_number'],
            'orderId' => $order['order_id'],
            'date'   => date('Y-m-d', strtotime($order['created_at'])),
            'status' => ucfirst($order['status']),
            'total'  => $order['total'],
            'items'  => array_map(fn($i) => [
                    'productId' => $i['product_id'],
                    'name'      => $i['product_name'],
                    'image'     => $i['product_image'],
                    'price'     => $i['unit_price'],
                    'review'    => $reviewsMap[$order['order_id'] . '_' . $i['product_id']] ?? null,
            ], $items)
    ];
}

// ── جيبي Wishlist من DB ───────────────────────────────────────
$stmtWish = $pdo->prepare("
    SELECT p.product_id, p.name, p.price, p.image
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = ?
");
$stmtWish->execute([$userId]);
$wishlistItems = $stmtWish->fetchAll();


// ── جيبي My Teddies من DB ────────────────────────────────────
$stmtTeddies = $pdo->prepare("
    SELECT custom_id, custom_name AS name, total_price AS price, config_json, created_at
    FROM custom_teddies
    WHERE user_id = ? AND is_saved = TRUE
    ORDER BY custom_id DESC
");
$stmtTeddies->execute([$userId]);
$myTeddies = $stmtTeddies->fetchAll();

// ── صورة الأفاتار ────────────────────────────────────────────
$defaultAvatar = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjU1IiByPSI0MCIgZmlsbD0iI2YzYmViZSIvPjxjaXJjbGUgY3g9IjE4IiBjeT0iMjUiIHI9IjE1IiBmaWxsPSIjZjNiZWJlIi8+PGNpcmNsZSBjeD0iODIiIGN5PSIyNSIgcj0iMTUiIGZpbGw9IiNmM2JlYmUiLz48Y2lyY2xlIGN4PSIxOCIgY3k9IjI1IiByPSI4IiBmaWxsPSIjZmRmMGY2Ii8+PGNpcmNsZSBjeD0iODIiIGN5PSIyNSIgcj0iOCIgZmlsbD0iI2ZkZjBmNiIvPjxlbGxpcHNlIGN4PSI1MCIgY3k9IjY1IiByeD0iMTgiIHJ5PSIxMiIgZmlsbD0iI2ZkZjBmNiIvPjxlbGxpcHNlIGN4PSI1MCIgY3k9IjYyIiByeD0iNiIgcnk9IjQiIGZpbGw9IiNkNDNhNWEiLz48Y2lyY2xlIGN4PSIzNSIgY3k9IjQ1IiByPSI1IiBmaWxsPSIjMzMzIi8+PGNpcmNsZSBjeD0iNjUiIGN5PSI0NSIgcj0iNSIgZmlsbD0iIzMzMyIvPjxwYXRoIGQ9Ik00MCA3NSBRNTAgODAgNjAgNzUiIHN0cm9rZT0iI2Q0M2E1YSIgc3Ryb2tlLXdpZHRoPSIzIiBmaWxsPSJub25lIi8+PC9zdmc+";
$userAvatar = !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : $defaultAvatar;

// ── معالجة تعديل بيانات اليوزر (AJAX) ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // رفع صورة الأفاتار
    if ($_POST['action'] === 'upload_avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== 0) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            exit;
        }
        $file      = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '.' . $ext;
        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $pdo->prepare("UPDATE users SET avatar = ? WHERE user_id = ?")
                    ->execute([$dest, $userId]);
            echo json_encode(['success' => true, 'avatar' => $dest]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Upload failed']);
        }
        exit;
    }

    if ($_POST['action'] === 'update_profile') {
        $newName  = trim($_POST['name']  ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        if (!$newName) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            exit;
        }

        $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE user_id = ?")
                ->execute([$newName, $newPhone, $userId]);

        $_SESSION['user_name'] = $newName;
        echo json_encode(['success' => true, 'message' => 'Profile updated!']);
        exit;
    }

    // حذف Custom Teddy
    if ($_POST['action'] === 'delete_teddy') {
        $customId = (int)($_POST['custom_id'] ?? 0);
        $pdo->prepare("DELETE FROM custom_teddies WHERE custom_id = ? AND user_id = ?")
                ->execute([$customId, $userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    // إضافة كوستوم تيدي للسلة (تحويل is_saved إلى FALSE)
    if ($_POST['action'] === 'add_custom_to_cart') {
        $customId = (int)($_POST['custom_id'] ?? 0);
        $pdo->prepare("UPDATE custom_teddies SET is_saved = FALSE WHERE custom_id = ? AND user_id = ?")
                ->execute([$customId, $userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    // إضافة للكارت من Favorites
    if ($_POST['action'] === 'add_to_cart') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cartRow2 = $stmt->fetch();
        if (!$cartRow2) {
            $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)")->execute([$userId]);
            $cartId2 = $pdo->lastInsertId();
        } else { $cartId2 = $cartRow2['cart_id']; }
        $pdo->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?,?,1)
            ON CONFLICT (cart_id, product_id) DO UPDATE SET quantity = cart_items.quantity + 1
        ")->execute([$cartId2, $productId]);
        echo json_encode(['success' => true]);
        exit;
    }

    // إضافة/حذف Wishlist
    if ($_POST['action'] === 'toggle_wishlist') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $check = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check->execute([$userId, $productId]);

        if ($check->fetch()) {
            $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")
                    ->execute([$userId, $productId]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")
                    ->execute([$userId, $productId]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
        exit;
    }

    // ── [إضافة 1] حفظ التقييم في DB ─────────────────────────
    if ($_POST['action'] === 'submit_review') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $orderNum  = trim($_POST['order_number'] ?? '');
        $rating    = (int)($_POST['rating'] ?? 0);
        $comment   = trim($_POST['comment'] ?? '');

        if (!$productId || !$rating) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            exit;
        }

        $stmtO = $pdo->prepare("SELECT order_id FROM orders WHERE order_number = ? AND user_id = ?");
        $stmtO->execute([$orderNum, $userId]);
        $orderRow = $stmtO->fetch();
        $orderId = $orderRow ? $orderRow['order_id'] : null;

        $pdo->prepare("
            INSERT INTO reviews (user_id, product_id, order_id, rating, comment, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON CONFLICT (user_id, product_id) DO UPDATE
            SET rating = EXCLUDED.rating, comment = EXCLUDED.comment, created_at = NOW()
        ")->execute([$userId, $productId, $orderId, $rating, $comment]);

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}
?>

<!DOCTYPE html>
<!-- --- بداية قسم HTML --- -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- --- بداية قسم CSS --- -->
    <style>
        /* خلفية متحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        #toast-container {
            position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
            z-index: 9999; display: flex; flex-direction: column; gap: 10px;
            pointer-events: none;
        }
        .toast-message {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff; padding: 15px 30px; border-radius: 50px;
            box-shadow: 0 10px 25px rgba(255, 107, 129, 0.4);
            font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 600;
            display: flex; align-items: center; gap: 10px;
            opacity: 0; transform: translateY(-30px);
            animation: toastIn 0.5s forwards;
        }
        .toast-message.toast-out { animation: toastOut 0.5s forwards; }
        .toast-message i { font-size: 18px; }
        @keyframes toastIn { to { opacity: 1; transform: translateY(0); } }
        @keyframes toastOut { to { opacity: 0; transform: translateY(-30px); } }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4); backdrop-filter: blur(5px);
            z-index: 10000; display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.3s;
        }
        .modal-overlay.visible { opacity: 1; pointer-events: auto; }
        .modal-box {
            background: var(--card-bg); padding: 30px;
            border-radius: 25px; width: 90%; max-width: 400px; text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            transform: scale(0.8); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.visible .modal-box { transform: scale(1); }
        .modal-box i { font-size: 50px; color: var(--pink); margin-bottom: 15px; }
        .modal-box h3 { margin: 0 0 10px; color: var(--text-color); font-family: 'Playfair Display', serif; font-size: 24px; }
        .modal-box p { color: var(--secondary-text); margin-bottom: 25px; font-size: 15px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 15px; justify-content: center; }
        .modal-btn { padding: 10px 30px; border-radius: 30px; border: none; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 15px; }
        .modal-btn.cancel { background: #eee; color: #555; }
        body.dark-mode .modal-btn.cancel { background: #444; color: #ddd; }
        .modal-btn.cancel:hover { background: #ddd; }
        .modal-btn.confirm { background: #ff4757; color: #fff; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3); }
        .modal-btn.confirm:hover { background: #ff6b81; transform: translateY(-2px); }

        .profile-container { padding: 50px 20px; max-width: 1100px; margin: 0 auto; position: relative; z-index: 1; }
        .profile-header-new { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 30px; background-color: var(--card-bg); padding: 30px 40px; border-radius: 25px; box-shadow: 0 10px 30px var(--shadow); margin-bottom: 40px; opacity: 0; transform: translateY(-30px); transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); }
        .profile-header-new.visible { opacity: 1; transform: translateY(0); }
        .user-side { display: flex; align-items: center; gap: 25px; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--pink); padding: 3px; background-color: #fff; box-shadow: 0 5px 15px var(--shadow); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .profile-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; background-color: #fff3e0; }
        .edit-avatar-btn { position: absolute; bottom: 5px; right: 0; background-color: var(--pink); color: #fff; width: 30px; height: 30px; border-radius: 50%; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; z-index: 2; }
        .user-info-text h2 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--text-color); margin: 0 0 5px; }
        .membership-badge { display: inline-block; color: var(--pink); font-weight: 600; font-size: 14px; }
        .nav-side { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .nav-tab-btn { background: transparent; border: 1px solid transparent; padding: 10px 20px; border-radius: 30px; color: var(--secondary-text); font-weight: 500; font-size: 14px; cursor: pointer; transition: all 0.4s ease; }
        .nav-tab-btn:hover { color: var(--text-color); background-color: rgba(248, 187, 208, 0.1); }
        .nav-tab-btn.active { background-color: var(--pink); color: #fff; border-color: var(--pink); box-shadow: 0 5px 15px rgba(248, 187, 208, 0.4); }
        .profile-content-area { background-color: var(--card-bg); border-radius: 25px; padding: 40px; box-shadow: 0 10px 30px var(--shadow); opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); }
        .profile-content-area.visible { opacity: 1; transform: translateY(0); }
        .tab-content { display: none; animation: fadeIn 0.5s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .section-header { border-bottom-color: #333; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--text-color); margin: 0; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; }
        .info-box label { display: block; font-size: 12px; color: var(--secondary-text); margin-bottom: 8px; }
        .info-value { margin: 0; color: var(--text-color); font-weight: 600; font-size: 16px; padding: 10px 0; }
        .info-input { width: 100%; padding: 10px; border: 1px solid var(--lavender); border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 15px; color: var(--text-color); background-color: var(--bg-color); box-sizing: border-box; display: none; }
        .editing-mode .info-value { display: none; }
        .editing-mode .info-input { display: block; }
        .editing-mode .edit-btn { display: none; }
        .editing-mode .save-btn { display: inline-block; }
        .editing-mode .cancel-btn { display: flex; }
        .edit-btn { font-size: 14px; color: var(--pink); cursor: pointer; font-weight: 600; }
        .save-btn, .cancel-btn { display: none; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; border: none; }
        .save-btn { background-color: #28a745; color: #fff; }
        .cancel-btn { background-color: #dc3545; color: #fff; width: 40px; height: 40px; padding: 0; border-radius: 50%; align-items: center; justify-content: center; }
        .order-card { border: 1px solid #f0f0f0; border-radius: 15px; padding: 20px; margin-bottom: 15px; transition: transform 0.2s, box-shadow 0.2s; }
        body.dark-mode .order-card { border-color: #333; }
        .order-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px var(--shadow); }
        .order-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .order-id { font-weight: bold; color: var(--text-color); }
        .order-status { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-delivered, .status-completed { background: #d4edda; color: #155724; }
        .status-processing { background: #fff3cd; color: #856404; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-pending { background: #f8d7da; color: #721c24; }
        .status-cancelled { background: #e2e3e5; color: #383d41; }
        .order-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; border-top: 1px dashed #eee; padding-top: 10px; }
        body.dark-mode .order-footer { border-top-color: #333; }
        .order-total { font-weight: bold; color: var(--pink); }
        .details-link { background: var(--lavender); color: #fff; padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 14px; transition: background 0.3s; }
        .details-link:hover { background: var(--pink); }
        .setting-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .setting-item { border-bottom-color: #333; }
        .setting-info h4 { margin: 0 0 5px; color: var(--text-color); }
        .setting-info p { margin: 0; font-size: 13px; color: var(--secondary-text); }
        .settings-action-btn { background: #333; color: #fff; border: none; padding: 8px 20px; border-radius: 20px; cursor: pointer; text-decoration: none; font-size: 14px; transition: background 0.3s; }
        .settings-action-btn:hover { background: #555; }
        .logout-btn { background: #ff4757; }
        .logout-btn:hover { background: #ff6b81; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--pink); }
        input:checked + .slider:before { transform: translateX(24px); }
        .fav-header-actions { display: flex; gap: 10px; align-items: center; }
        .manage-toggle-btn { background: none; border: 2px solid var(--pink); color: var(--pink); padding: 6px 18px; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; }
        .manage-toggle-btn:hover, .manage-toggle-btn.active { background: var(--pink); color: #fff; }
        .manage-action-bar { display: none; justify-content: space-between; align-items: center; background: rgba(255, 107, 129, 0.1); padding: 12px 20px; border-radius: 15px; margin-bottom: 20px; animation: fadeIn 0.3s ease; }
        .manage-action-bar.visible { display: flex; }
        .select-all-wrapper { display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: var(--text-color); }
        .action-buttons-group { display: flex; gap: 10px; }
        .action-btn { border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .action-btn.delete-btn { background: #ff4d4d; color: #fff; }
        .action-btn.delete-btn:hover { background: #e60000; }
        .favorites-grid, .teddies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .fav-card, .teddy-card { background: #fff; border-radius: 15px; padding: 15px; text-align: center; box-shadow: 0 5px 15px var(--shadow); transition: transform 0.3s; position: relative; }
        body.dark-mode .fav-card, body.dark-mode .teddy-card { background: #222; }
        .fav-card:hover, .teddy-card:hover { transform: translateY(-5px); }
        .select-circle { width: 22px; height: 22px; border-radius: 50%; border: 2px solid #ddd; position: absolute; top: 10px; left: 10px; z-index: 5; display: none; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; background-color: #fff; }
        .fav-card.managing .select-circle, .teddy-card.managing .select-circle { display: flex; }
        .select-circle:hover { border-color: var(--pink); }
        .select-circle.selected { background-color: var(--pink); border-color: var(--pink); color: #fff; }
        .fav-card .fav-img-link, .teddy-card .teddy-img-link { display: block; line-height: 0; }
        .fav-img, .teddy-img { width: 100px; height: 100px; background: #f8f8f8; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin: 0 auto 10px auto; transition: transform 0.3s; }
        body.dark-mode .fav-img, body.dark-mode .teddy-img { background: #333; }
        .fav-img img, .teddy-img img { max-width: 80%; max-height: 80%; object-fit: contain; }
        .fav-name, .teddy-name { font-weight: 600; color: var(--text-color); margin: 0 0 5px; font-size: 16px; }
        .fav-name a, .teddy-name a { text-decoration: none; color: inherit; }
        .fav-name a:hover, .teddy-name a:hover { color: var(--pink); }
        .fav-price, .teddy-price { color: var(--pink); margin-bottom: 15px; font-weight: bold; }
        .add-cart-btn { background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff; border: none; padding: 5px 15px; border-radius: 20px; cursor: pointer; transition: all 0.3s; font-size: 13px; display: inline-block; box-shadow: 0 4px 10px rgba(255,154,158,0.3); }
        .add-cart-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(255,154,158,0.4); }
        .add-cart-btn.added { background: #28a745; }
        .teddy-info, .fav-info { display: flex; flex-direction: column; align-items: center; }
        .remove-fav-btn { background: #ff4757; color: #fff; border: none; padding: 5px 15px; border-radius: 20px; cursor: pointer; transition: background 0.3s; font-size: 13px; }
        .remove-fav-btn:hover { background: #ff6b81; }
        .empty-state { text-align: center; padding: 40px 20px; color: var(--secondary-text); }
        .empty-state i { font-size: 60px; color: var(--lavender); margin-bottom: 20px; opacity: 0.8; }
        .empty-state h4 { font-size: 20px; color: var(--text-color); margin-bottom: 10px; }
        .empty-state p { font-size: 15px; margin-bottom: 20px; }
        .shop-btn { display: inline-block; background: var(--pink); color: #fff; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; transition: all 0.3s; box-shadow: 0 5px 15px rgba(248, 187, 208, 0.3); }
        .shop-btn:hover { background: var(--primary); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(248, 187, 208, 0.4); }
        .review-area { margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        .review-items-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .review-item-card { background: #f9f9f9; border-radius: 10px; padding: 15px; }
        body.dark-mode .review-item-card { background: #2d2d2d; }
        .star-rating i { font-size: 20px; cursor: pointer; margin-right: 2px; }
        .review-comment { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; }
        body.dark-mode .review-comment { background: #333; color: #fff; border-color: #444; }
        @media (max-width: 768px) {
            .profile-header-new { flex-direction: column; text-align: center; padding: 20px; }
            .user-side { flex-direction: column; gap: 15px; }
            .favorites-grid, .teddies-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<div id="toast-container"></div>

<div id="customModal" class="modal-overlay">
    <div class="modal-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <h3>Are you sure?</h3>
        <p id="modalMessage">Do you really want to delete this item?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
            <button class="modal-btn confirm" id="confirmModalBtn">Delete</button>
        </div>
    </div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="profile-container">
    <div class="profile-header-new">
        <div class="user-side">
            <div class="profile-avatar">
                <img src="<?php echo $userAvatar; ?>" alt="User Avatar" id="avatarImg">
                <div class="edit-avatar-btn" onclick="document.getElementById('avatarInput').click()"><i class="fa-solid fa-camera"></i></div>
                <input type="file" id="avatarInput" accept="image/*" style="display:none;" onchange="uploadAvatar(this)">
            </div>
            <div class="user-info-text">
                <h2><?php echo $userName; ?></h2>
                <span class="membership-badge">Member since <?php echo $joinDate; ?></span>
            </div>
        </div>

        <div class="nav-side">
            <button class="nav-tab-btn active" data-tab="account"><i class="fa-solid fa-user"></i> My Account</button>
            <button class="nav-tab-btn" data-tab="orders"><i class="fa-solid fa-box"></i> My Orders</button>
            <button class="nav-tab-btn" data-tab="teddies"><i class="fa-solid fa-wand-magic-sparkles"></i> My Teddies</button>
            <button class="nav-tab-btn" data-tab="favorites"><i class="fa-solid fa-heart"></i> Favorites</button>
            <button class="nav-tab-btn" data-tab="settings"><i class="fa-solid fa-gear"></i> Settings</button>
        </div>
    </div>

    <div class="profile-content-area" id="contentArea">

        <!-- 1. Account -->
        <div id="tab-account" class="tab-content active">
            <div class="section-header">
                <h3 class="section-title">Personal Information</h3>
                <div class="action-buttons">
                    <span class="edit-btn" onclick="toggleEdit(true)"><i class="fa-solid fa-pen"></i> Edit</span>
                    <button class="save-btn" onclick="saveChanges()"><i class="fa-solid fa-check"></i> Save</button>
                    <button class="cancel-btn" onclick="toggleEdit(false)"><i class="fa-solid fa-times"></i></button>
                </div>
            </div>
            <form id="profileForm">
                <div class="info-grid">
                    <div class="info-box">
                        <label>Full Name</label>
                        <p class="info-value"><?php echo $userName; ?></p>
                        <input type="text" class="info-input" name="name" value="<?php echo $userName; ?>">
                    </div>
                    <div class="info-box">
                        <label>Email Address</label>
                        <p class="info-value"><?php echo $email; ?></p>
                        <input type="email" class="info-input" value="<?php echo $email; ?>" disabled>
                    </div>
                    <div class="info-box">
                        <label>Phone Number</label>
                        <p class="info-value"><?php echo $phone ?: 'Not set'; ?></p>
                        <input type="text" class="info-input" name="phone" value="<?php echo $phone; ?>" placeholder="+972 5X XXX XXXX">
                    </div>
                </div>
            </form>
        </div>

        <!-- 2. Orders -->
        <div id="tab-orders" class="tab-content">
            <div class="section-header"><h3 class="section-title">My Orders</h3></div>
            <div id="orders-list-container">
                <?php if (empty($ordersData)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-box-open"></i>
                        <h4>No Orders Yet</h4>
                        <p>Looks like you haven't placed any orders yet.</p>
                        <a href="shop.php" class="shop-btn"><i class="fa-solid fa-bag-shopping"></i> Start Shopping</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($ordersData as $order): ?>
                        <div class="order-card" data-order-id="<?= htmlspecialchars($order['id']) ?>">
                            <div class="order-header">
                                <span class="order-id">#<?= htmlspecialchars($order['id']) ?></span>
                                <span class="order-status status-<?= strtolower($order['status']) ?>"><?= $order['status'] ?></span>
                            </div>
                            <p style="color:var(--secondary-text); font-size:14px;">
                                <i class="fa-regular fa-calendar"></i> Date: <?= $order['date'] ?>
                            </p>
                            <div class="order-footer">
                                <span class="order-total">$<?= number_format($order['total'], 2) ?></span>
                                <div>
                                    <a href="order_details.php?id=<?= htmlspecialchars($order['id']) ?>" class="details-link">
                                        View Details <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                    <?php
                                    $unreviewedCount = count(array_filter($order['items'], fn($i) => $i['review'] === null));
                                    $allReviewed = $unreviewedCount === 0;
                                    ?>
                                    <?php if (in_array($order['status'], ['Completed', 'Delivered'])): ?>
                                        <?php if ($allReviewed): ?>
                                            <button class="details-link"
                                                    onclick="toggleReviewArea('<?= htmlspecialchars($order['id']) ?>')"
                                                    style="margin-left:10px; background:var(--lavender); border:none; cursor:pointer;">
                                                My Reviews <i class="fa-solid fa-star"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="details-link rate-order-btn"
                                                    onclick="toggleReviewArea('<?= htmlspecialchars($order['id']) ?>')"
                                                    style="margin-left:10px; background:var(--pink); border:none; cursor:pointer;">
                                                Rate Order <i class="fa-solid fa-star"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div id="review-area-<?= htmlspecialchars($order['id']) ?>" class="review-area" style="display:none;">
                                <h4 style="margin-bottom:15px;">
                                    <?= $allReviewed ? 'My Reviews' : 'Rate Products' ?> — Order #<?= htmlspecialchars($order['id']) ?>
                                </h4>
                                <div class="review-items-grid">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="review-item-card"
                                             data-product-id="<?= $item['productId'] ?>"
                                             data-order-id="<?= htmlspecialchars($order['id']) ?>">
                                            <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                                     style="width:50px; height:50px; object-fit:contain; border-radius:5px;">
                                                <div>
                                                    <strong style="color:var(--text-color);"><?= htmlspecialchars($item['name']) ?></strong>
                                                    <p style="margin:5px 0 0; font-size:13px; color:var(--secondary-text);">$<?= number_format($item['price'], 2) ?></p>
                                                </div>
                                            </div>

                                            <?php if ($item['review'] !== null): ?>
                                                <!-- ── ريفيو موجود ── -->
                                                <div class="existing-review">
                                                    <div style="margin-bottom:8px;">
                                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                                            <i class="fa-<?= $s <= $item['review']['rating'] ? 'solid' : 'regular' ?> fa-star"
                                                               style="color:<?= $s <= $item['review']['rating'] ? '#ffc107' : '#ddd' ?>; font-size:18px;"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <?php if (!empty($item['review']['comment'])): ?>
                                                        <p style="color:var(--secondary-text); font-size:13px; font-style:italic; margin:0;">
                                                            "<?= htmlspecialchars($item['review']['comment']) ?>"
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($item['review']['status'] === 'pending'): ?>
                                                        <small style="color:#FF9800; display:block; margin-top:5px;">
                                                            <i class="fa-solid fa-clock"></i> Pending approval
                                                        </small>
                                                    <?php elseif ($item['review']['status'] === 'approved'): ?>
                                                        <small style="color:#4CAF50; display:block; margin-top:5px;">
                                                            <i class="fa-solid fa-check-circle"></i> Approved
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <!-- ─ـ فورم ريفيو ── -->
                                                <div class="star-rating" style="margin-bottom:10px;">
                                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                                        <i class="fa-regular fa-star"
                                                           style="color:#ddd; font-size:20px; cursor:pointer; margin-right:2px;"
                                                           onclick="setRating('<?= htmlspecialchars($order['id']) ?>', '<?= $item['productId'] ?>', <?= $s ?>)"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <textarea class="review-comment"
                                                          placeholder="Write your review (optional)..."
                                                          onchange="setComment('<?= htmlspecialchars($order['id']) ?>', '<?= $item['productId'] ?>', this.value)"></textarea>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (!$allReviewed): ?>
                                    <div style="text-align:right; margin-top:20px;">
                                        <button class="shop-btn"
                                                onclick="submitAllReviews('<?= htmlspecialchars($order['id']) ?>')"
                                                style="padding:8px 25px;">
                                            Submit All Reviews
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. Teddies -->
        <div id="tab-teddies" class="tab-content">
            <div class="section-header">
                <h3 class="section-title">My Customized Teddies</h3>
                <div class="fav-header-actions">
                    <button id="teddyManageBtn" class="manage-toggle-btn" onclick="toggleTeddyManageMode()" style="display:none;">Manage</button>
                </div>
            </div>
            <div id="teddyActionBar" class="manage-action-bar">
                <div class="select-all-wrapper" onclick="toggleTeddySelectAll()">
                    <div id="teddySelectAllCircle" class="select-circle" style="position:relative; top:0; left:0; display:flex;"></div>
                    <span>Select All</span>
                </div>
                <div class="action-buttons-group">
                    <button class="action-btn delete-btn" onclick="deleteSelectedTeddies()">
                        <i class="fa-solid fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
            <div id="teddies-empty" class="empty-state" style="display:none;">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <h4>No Custom Teddies Yet</h4>
                <p>Start designing your own unique teddy bear!</p>
                <a href="customize.php" class="shop-btn">Create Now</a>
            </div>
            <div id="teddies-grid" class="teddies-grid"></div>
        </div>

        <!-- 4. Favorites -->
        <div id="tab-favorites" class="tab-content">
            <div class="section-header">
                <h3 class="section-title">My Favorites</h3>
                <div class="fav-header-actions">
                    <button id="favManageBtn" class="manage-toggle-btn" onclick="toggleFavManageMode()">Manage</button>
                </div>
            </div>

            <div id="favActionBar" class="manage-action-bar">
                <div class="select-all-wrapper" onclick="toggleFavSelectAll()">
                    <div id="favSelectAllCircle" class="select-circle" style="position:relative; top:0; left:0; display:flex;"></div>
                    <span>Select All</span>
                </div>
                <div class="action-buttons-group">
                    <button class="action-btn delete-btn" onclick="deleteSelectedFavorites()">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </div>
            </div>

            <?php if (empty($wishlistItems)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-heart-crack"></i>
                    <h4>No Favorites Yet</h4>
                    <p>You haven't added any teddies to your favorites yet.</p>
                    <a href="shop.php" class="shop-btn">Start Shopping</a>
                </div>
            <?php else: ?>
                <div id="favorites-empty" class="empty-state" style="display:none;">
                    <i class="fa-solid fa-heart-crack"></i>
                    <h4>No Favorites Yet</h4>
                    <p>You haven't added any teddies to your favorites yet.</p>
                    <a href="shop.php" class="shop-btn">Start Shopping</a>
                </div>

                <div id="favorites-grid" class="favorites-grid">
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="fav-card" data-product-id="<?= $item['product_id'] ?>">
                            <div class="select-circle" onclick="toggleFavSelect('<?= $item['product_id'] ?>')"></div>
                            <a href="product_details.php?id=<?= $item['product_id'] ?>" class="fav-img-link">
                                <div class="fav-img">
                                    <img src="<?= htmlspecialchars($item['image']) ?>"
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         onerror="this.src='https://ui-avatars.com/api/?name=Teddy&background=ff9a9e&color=fff'">
                                </div>
                            </a>
                            <div class="fav-info">
                                <h4 class="fav-name">
                                    <a href="product_details.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['name']) ?></a>
                                </h4>
                                <p class="fav-price">$<?= number_format($item['price'], 2) ?></p>
                                <button class="add-cart-btn" onclick="addFavToCart(<?= $item['product_id'] ?>, this)">
                                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 5. Settings -->
        <div id="tab-settings" class="tab-content">
            <div class="section-header"><h3 class="section-title">Settings</h3></div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Change Password</h4>
                    <p>Update your password regularly for security.</p>
                </div>
                <a href="change_password.php" class="settings-action-btn">
                    <i class="fa-solid fa-key"></i> Change
                </a>
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Email Notifications</h4>
                    <p>Receive emails about new products and offers.</p>
                </div>
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Logout</h4>
                    <p>Sign out of your account on this device.</p>
                </div>
                <a href="?logout=1" class="settings-action-btn logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

    </div>
</div>

<!-- --- بداية قسم JavaScript --- -->
<script>
    // Upload Avatar
    function uploadAvatar(input) {
        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('action', 'upload_avatar');
        formData.append('avatar', file);

        fetch('profile.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('avatarImg').src = data.avatar + '?t=' + Date.now();
                    showToast('Profile photo updated! 📸');
                } else {
                    showToast(data.message || 'Upload failed');
                }
            });
    }

    // Toast
    function showToast(message) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.add('toast-out'); setTimeout(() => toast.remove(), 500); }, 3000);
    }

    // Modal
    let confirmCallback = null;
    function showCustomConfirm(message, callback) {
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('customModal').classList.add('visible');
        confirmCallback = callback;
    }
    function closeModal() {
        document.getElementById('customModal').classList.remove('visible');
        confirmCallback = null;
    }
    document.getElementById('confirmModalBtn').addEventListener('click', () => {
        if (confirmCallback) confirmCallback();
        closeModal();
    });
    document.getElementById('customModal').addEventListener('click', (e) => {
        if (e.target.id === 'customModal') closeModal();
    });

    // Tabs
    const tabButtons = document.querySelectorAll('.nav-tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            tabContents.forEach(content => content.classList.remove('active'));
            document.getElementById('tab-' + button.getAttribute('data-tab')).classList.add('active');
        });
    });

    window.addEventListener('load', () => {
        setTimeout(() => document.querySelector('.profile-header-new').classList.add('visible'), 200);
        setTimeout(() => document.querySelector('.profile-content-area').classList.add('visible'), 600);

        renderMyTeddies();

        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab) {
            const targetBtn = document.querySelector(`.nav-tab-btn[data-tab="${activeTab}"]`);
            if (targetBtn) targetBtn.click();
        }
    });

    // Edit Profile
    function toggleEdit(isEditing) {
        const accountTab = document.getElementById('tab-account');
        if (isEditing) accountTab.classList.add('editing-mode');
        else accountTab.classList.remove('editing-mode');
    }

    function saveChanges() {
        const name  = document.querySelector('#profileForm input[name="name"]').value.trim();
        const phone = document.querySelector('#profileForm input[name="phone"]').value.trim();

        fetch('profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=update_profile&name=' + encodeURIComponent(name) + '&phone=' + encodeURIComponent(phone)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Changes Saved Successfully!');
                    document.querySelectorAll('#tab-account .info-value')[0].textContent = name;
                    if (phone) document.querySelectorAll('#tab-account .info-value')[2].textContent = phone;
                    toggleEdit(false);
                } else {
                    showToast(data.message);
                }
            });
    }

    // Favorites Manage
    let favIsManaging = false;
    let selectedFavIds = new Set();

    function toggleFavManageMode() {
        favIsManaging = !favIsManaging;
        const manageBtn = document.getElementById('favManageBtn');
        const actionBar = document.getElementById('favActionBar');
        manageBtn.classList.toggle('active', favIsManaging);
        manageBtn.innerText = favIsManaging ? 'Done' : 'Manage';
        actionBar.classList.toggle('visible', favIsManaging);
        if (!favIsManaging) selectedFavIds.clear();
        document.querySelectorAll('.fav-card').forEach(card => {
            card.classList.toggle('managing', favIsManaging);
        });
    }

    function toggleFavSelect(id) {
        if (!favIsManaging) return;
        const card = document.querySelector(`.fav-card[data-product-id="${id}"]`);
        const circle = card?.querySelector('.select-circle');
        if (selectedFavIds.has(String(id))) {
            selectedFavIds.delete(String(id));
            circle?.classList.remove('selected');
            circle.innerHTML = '';
        } else {
            selectedFavIds.add(String(id));
            circle?.classList.add('selected');
            circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;"></i>';
        }
    }

    function toggleFavSelectAll() {
        const cards = document.querySelectorAll('.fav-card');
        const allSelected = cards.length > 0 && [...cards].every(c => selectedFavIds.has(c.dataset.productId));
        cards.forEach(card => {
            const id = card.dataset.productId;
            const circle = card.querySelector('.select-circle');
            if (allSelected) {
                selectedFavIds.delete(id);
                circle?.classList.remove('selected');
                if (circle) circle.innerHTML = '';
            } else {
                selectedFavIds.add(id);
                circle?.classList.add('selected');
                if (circle) circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;"></i>';
            }
        });
    }

    function addFavToCart(productId, btn) {
        fetch('profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_to_cart&product_id=' + productId
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
                    btn.classList.add('added');
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to Cart';
                        btn.classList.remove('added');
                    }, 1500);
                    updateNavbarCartCount();
                    showToast('Added to cart! 🛒');
                }
            });
    }

    function updateNavbarCartCount() {
        const badge = document.getElementById('cartCount');
        if (!badge) return;
        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_cart_count'
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    badge.textContent = data.count;
                    badge.classList.toggle('hide', data.count === 0);
                }
            });
    }

    function deleteSelectedFavorites() {
        if (selectedFavIds.size === 0) { showToast("Please select items to delete."); return; }
        showCustomConfirm(`Delete ${selectedFavIds.size} selected item(s)?`, () => {
            const promises = [...selectedFavIds].map(id =>
                fetch('profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=toggle_wishlist&product_id=' + id
                })
            );
            Promise.all(promises).then(() => {
                selectedFavIds.forEach(id => {
                    document.querySelector(`.fav-card[data-product-id="${id}"]`)?.remove();
                });
                selectedFavIds.clear();
                showToast("Deleted successfully!");
                const grid = document.getElementById('favorites-grid');
                if (grid && grid.children.length === 0) {
                    document.getElementById('favorites-empty').style.display = 'block';
                    document.getElementById('favManageBtn').style.display = 'none';
                }
            });
        });
    }

    // My Teddies
    const myTeddiesFromDB = <?= json_encode($myTeddies) ?>;
    let teddyIsManaging = false;
    let selectedTeddyIds = new Set();

    function renderMyTeddies() {
        const grid     = document.getElementById('teddies-grid');
        const emptyMsg = document.getElementById('teddies-empty');
        const manageBtn = document.getElementById('teddyManageBtn');

        if (myTeddiesFromDB.length === 0) {
            grid.style.display = 'none';
            emptyMsg.style.display = 'block';
            manageBtn.style.display = 'none';
        } else {
            grid.style.display = 'grid';
            emptyMsg.style.display = 'none';
            manageBtn.style.display = 'block';

            let html = '';
            myTeddiesFromDB.forEach(item => {
                let cfg = null;
                try { cfg = item.config_json ? JSON.parse(item.config_json) : null; } catch(e) {}

                const baseImg = cfg?.color?.img || 'images/brown.png';
                const outfitImg = cfg?.outfit?.img || '';
                const shoesImg  = cfg?.shoes?.img  || '';
                const accImg    = cfg?.acc?.img    || '';
                const teddyName = item.name || 'Custom Teddy';

                html += `
                    <div class="teddy-card" data-teddy-id="${item.custom_id}">
                        <div class="select-circle" onclick="toggleTeddySelect('${item.custom_id}')"></div>
                        <a href="custom_details.php?id=${item.custom_id}" class="teddy-img-link">
                            <div class="teddy-img" style="position:relative; overflow:visible;">
                                <div style="position:relative; width:90px; height:110px; margin:0 auto;">
                                    <img src="${baseImg}" style="position:absolute;width:100%;height:100%;object-fit:contain;z-index:1;top:0;left:0;" alt="base">
                                    ${outfitImg ? `<img src="${outfitImg}" style="position:absolute;width:60%;height:auto;top:50%;left:40%;transform:translate(-50%,-50%);z-index:2;object-fit:contain;" alt="outfit">` : ''}
                                    ${shoesImg  ? `<img src="${shoesImg}"  style="position:absolute;width:50%;height:auto;top:80%;left:40%;transform:translate(-50%,-50%);z-index:3;object-fit:contain;" alt="shoes">` : ''}
                                    ${accImg    ? `<img src="${accImg}"    style="position:absolute;width:26%;height:auto;top:16%;left:5%;transform:translate(-50%,-50%);z-index:4;object-fit:contain;" alt="acc">` : ''}
                                </div>
                            </div>
                        </a>
                        <div class="teddy-info">
                            <h4 class="teddy-name">${teddyName}</h4>
                            <p class="teddy-price">$${parseFloat(item.price).toFixed(2)}</p>
                            <button class="add-cart-btn" onclick="addCustomToCartDB(${item.custom_id}, this)">
                                <i class="fa-solid fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                `;
            });
            grid.innerHTML = html;
        }
    }

    function addCustomToCartDB(customId, btn) {
        fetch('profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_custom_to_cart&custom_id=' + customId
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
                    btn.classList.add('added');
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to Cart';
                        btn.classList.remove('added');
                    }, 1500);
                    updateNavbarCartCount();
                    showToast('Teddy added to cart! 🧸 <a href="cart.php" style="color:#fff;text-decoration:underline;">View Cart</a>');
                }
            });
    }

    function toggleTeddyManageMode() {
        teddyIsManaging = !teddyIsManaging;
        const manageBtn = document.getElementById('teddyManageBtn');
        const actionBar = document.getElementById('teddyActionBar');
        manageBtn.classList.toggle('active', teddyIsManaging);
        manageBtn.innerText = teddyIsManaging ? 'Done' : 'Manage';
        actionBar.classList.toggle('visible', teddyIsManaging);
        if (!teddyIsManaging) selectedTeddyIds.clear();
        document.querySelectorAll('.teddy-card').forEach(card => card.classList.toggle('managing', teddyIsManaging));
    }

    function toggleTeddySelect(id) {
        if (!teddyIsManaging) return;
        const card = document.querySelector(`.teddy-card[data-teddy-id="${id}"]`);
        const circle = card?.querySelector('.select-circle');
        if (selectedTeddyIds.has(id)) {
            selectedTeddyIds.delete(id);
            circle?.classList.remove('selected');
            if (circle) circle.innerHTML = '';
        } else {
            selectedTeddyIds.add(id);
            circle?.classList.add('selected');
            if (circle) circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;"></i>';
        }
    }

    function toggleTeddySelectAll() {
        const cards = document.querySelectorAll('.teddy-card');
        const allSelected = [...cards].every(c => selectedTeddyIds.has(c.dataset.teddyId));
        cards.forEach(card => {
            const id = card.dataset.teddyId;
            const circle = card.querySelector('.select-circle');
            if (allSelected) {
                selectedTeddyIds.delete(id);
                circle?.classList.remove('selected');
                if (circle) circle.innerHTML = '';
            } else {
                selectedTeddyIds.add(id);
                circle?.classList.add('selected');
                if (circle) circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;"></i>';
            }
        });
    }

    function deleteSelectedTeddies() {
        if (selectedTeddyIds.size === 0) { showToast("Please select items to delete."); return; }
        showCustomConfirm(`Delete ${selectedTeddyIds.size} teddy(s)?`, () => {
            const promises = [...selectedTeddyIds].map(id =>
                fetch('profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=delete_teddy&custom_id=' + id
                })
            );
            Promise.all(promises).then(() => {
                selectedTeddyIds.forEach(id => {
                    document.querySelector(`.teddy-card[data-teddy-id="${id}"]`)?.remove();
                    const idx = myTeddiesFromDB.findIndex(i => String(i.custom_id) === id);
                    if (idx !== -1) myTeddiesFromDB.splice(idx, 1);
                });
                selectedTeddyIds.clear();
                if (myTeddiesFromDB.length === 0) {
                    document.getElementById('teddies-grid').style.display = 'none';
                    document.getElementById('teddies-empty').style.display = 'block';
                    document.getElementById('teddyManageBtn').style.display = 'none';
                }
                showToast("Deleted successfully!");
            });
        });
    }

    // Reviews
    function toggleReviewArea(orderId) {
        const area = document.getElementById(`review-area-${orderId}`);
        if (area) area.style.display = area.style.display === 'none' ? 'block' : 'none';
    }

    function setRating(orderId, productId, rating) {
        let temp = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
        if (!temp[orderId]) temp[orderId] = {};
        if (!temp[orderId][productId]) temp[orderId][productId] = {};
        temp[orderId][productId].rating = rating;
        sessionStorage.setItem('temp_reviews', JSON.stringify(temp));

        const stars = document.querySelectorAll(
            `.review-item-card[data-product-id="${productId}"][data-order-id="${orderId}"] .star-rating i`
        );
        stars.forEach((star, i) => {
            star.classList.toggle('fa-solid', i < rating);
            star.classList.toggle('fa-regular', i >= rating);
            star.style.color = i < rating ? '#ffc107' : '#ddd';
        });
    }

    function setComment(orderId, productId, comment) {
        let temp = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
        if (!temp[orderId]) temp[orderId] = {};
        if (!temp[orderId][productId]) temp[orderId][productId] = {};
        temp[orderId][productId].comment = comment;
        sessionStorage.setItem('temp_reviews', JSON.stringify(temp));
    }

    // ── [إضافة 2] submitAllReviews تحفظ في DB ────────────────
    function submitAllReviews(orderId) {
        const temp = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
        const orderReviews = temp[orderId];
        if (!orderReviews) { showToast('No reviews to submit.'); return; }

        const promises = [];
        const productIds = [];
        for (const [productId, data] of Object.entries(orderReviews)) {
            if (!data.rating) continue;
            productIds.push({ id: productId, rating: data.rating, comment: data.comment || '' });
            promises.push(
                fetch('profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=submit_review&product_id=${productId}&order_number=${orderId}&rating=${data.rating}&comment=${encodeURIComponent(data.comment || '')}`
                }).then(r => r.json())
            );
        }

        if (promises.length === 0) { showToast('Please add at least one rating.'); return; }

        Promise.all(promises).then(results => {
            const allOk = results.every(r => r.success);
            if (allOk) {
                delete temp[orderId];
                sessionStorage.setItem('temp_reviews', JSON.stringify(temp));
                showToast('Reviews submitted! ⭐');

                // تحديث كل كارت منتج: استبدالي الفورم بالريفيو
                productIds.forEach(p => {
                    const card = document.querySelector(`.review-item-card[data-product-id="${p.id}"][data-order-id="${orderId}"]`);
                    if (!card) return;

                    // بناء نجوم التقييم
                    let starsHtml = '<div style="margin-bottom:8px;">';
                    for (let s = 1; s <= 5; s++) {
                        starsHtml += `<i class="fa-${s <= p.rating ? 'solid' : 'regular'} fa-star" style="color:${s <= p.rating ? '#ffc107' : '#ddd'}; font-size:18px;"></i>`;
                    }
                    starsHtml += '</div>';

                    // بناء التعليق
                    let commentHtml = '';
                    if (p.comment) {
                        const div = document.createElement('div');
                        div.textContent = p.comment;
                        const escaped = div.innerHTML;
                        commentHtml = `<p style="color:var(--secondary-text); font-size:13px; font-style:italic; margin:0;">"${escaped}"</p>`;
                    }

                    // بناء حالة الرفيو
                    const statusHtml = '<small style="color:#FF9800; display:block; margin-top:5px;"><i class="fa-solid fa-clock"></i> Pending approval</small>';

                    // استبدال الفورم بالريفيو
                    const starRating = card.querySelector('.star-rating');
                    const textarea = card.querySelector('.review-comment');
                    if (starRating) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'existing-review';
                        wrapper.innerHTML = starsHtml + commentHtml + statusHtml;
                        starRating.replaceWith(wrapper);
                    }
                    if (textarea) textarea.remove();
                });

                // إخفاء زر Submit
                const reviewArea = document.getElementById(`review-area-${orderId}`);
                const submitBtn = reviewArea?.querySelector('.shop-btn');
                if (submitBtn) submitBtn.style.display = 'none';

                // تغيير زر Rate Order إلى My Reviews
                const orderCard = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
                const rateBtn = orderCard?.querySelector('.rate-order-btn');
                if (rateBtn) {
                    rateBtn.innerHTML = 'My Reviews <i class="fa-solid fa-star"></i>';
                    rateBtn.style.background = 'var(--lavender)';
                    rateBtn.classList.remove('rate-order-btn');
                }
            } else {
                showToast('Some reviews failed to save.');
            }
        });
    }
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>