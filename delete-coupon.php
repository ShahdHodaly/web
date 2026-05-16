<?php
// delete-coupon.php
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

// التحقق من وجود coupon_id
if (!isset($_POST['coupon_id'])) {
    echo json_encode(['success' => false, 'message' => 'Coupon ID is required']);
    exit;
}

$couponId = (int)$_POST['coupon_id'];

try {
    $pdo = getDB();

    // بدء المعاملة
    $pdo->beginTransaction();

    // حذف الكوبون
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE coupon_id = ?");
    $stmt->execute([$couponId]);
    $deletedCount = $stmt->rowCount();

    // إنهاء المعاملة بنجاح
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Coupon deleted successfully'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>