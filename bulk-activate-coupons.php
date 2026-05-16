<?php
// bulk-activate-coupons.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// التحقق من أن المستخدم Admin
$isAdmin = false;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $userRole = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
    if ($userRole === 'Admin') {
        $isAdmin = true;
    }
}

if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// قراءة البيانات من JSON body
$input = json_decode(file_get_contents('php://input'), true);

// التحقق من وجود coupon_ids
if (!isset($input['coupon_ids']) || empty($input['coupon_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No coupons selected']);
    exit;
}

$couponIds = array_map('intval', $input['coupon_ids']);
$placeholders = implode(',', array_fill(0, count($couponIds), '?'));

try {
    $pdo = getDB();

    // بدء المعاملة
    $pdo->beginTransaction();

    // التحقق من التواريخ قبل التفعيل
    // فقط الكوبونات التي لم تنتهي صلاحيتها يمكن تفعيلها
    $stmt = $pdo->prepare("
        UPDATE coupons 
        SET status = 'active' 
        WHERE coupon_id IN ($placeholders) 
        AND expiry_date >= CURRENT_DATE
        AND start_date <= CURRENT_DATE
    ");
    $stmt->execute($couponIds);
    $activatedCount = $stmt->rowCount();

    // إنهاء المعاملة بنجاح
    $pdo->commit();

    $message = $activatedCount > 0
        ? "Successfully activated $activatedCount coupon(s)"
        : "No coupons were activated. Make sure selected coupons have valid dates.";

    echo json_encode([
        'success' => true,
        'activated_count' => $activatedCount,
        'message' => $message
    ]);

} catch (PDOException $e) {
    // التراجع عن المعاملة في حالة وجود خطأ
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>