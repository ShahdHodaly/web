<?php
// delete-order.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// التحقق من أن المستخدم Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// التحقق من وجود order_id
if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$orderId = (int)$_POST['order_id'];

try {
    $pdo = getDB();

    // بدء المعاملة
    $pdo->beginTransaction();

    // 1. حذف عناصر الطلب أولاً (order_items)
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);

    // 2. حذف الطلب نفسه (orders)
    $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);

    // 3. حذف المراجعات المرتبطة بهذا الطلب (إذا وجدت)
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE order_id = ?");
    $stmt->execute([$orderId]);

    // 4. حذف التصميمات المخصصة المرتبطة بهذا الطلب (إذا وجدت)
    $stmt = $pdo->prepare("DELETE FROM custom_teddies WHERE order_id = ?");
    $stmt->execute([$orderId]);

    // إنهاء المعاملة بنجاح
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order deleted successfully'
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