<?php
// bulk-delete-orders.php
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

// قراءة البيانات من JSON body
$input = json_decode(file_get_contents('php://input'), true);

// التحقق من وجود order_ids
if (!isset($input['order_ids']) || empty($input['order_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No orders selected']);
    exit;
}

$orderIds = array_map('intval', $input['order_ids']);
$placeholders = implode(',', array_fill(0, count($orderIds), '?'));

try {
    $pdo = getDB();

    // بدء المعاملة
    $pdo->beginTransaction();

    // 1. حذف عناصر الطلبات أولاً (order_items)
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)");
    $stmt->execute($orderIds);
    $deletedItemsCount = $stmt->rowCount();

    // 2. حذف المراجعات المرتبطة بهذه الطلبات
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE order_id IN ($placeholders)");
    $stmt->execute($orderIds);

    // 3. حذف التصميمات المخصصة المرتبطة بهذه الطلبات
    $stmt = $pdo->prepare("DELETE FROM custom_teddies WHERE order_id IN ($placeholders)");
    $stmt->execute($orderIds);

    // 4. حذف الطلبات نفسها (orders)
    $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id IN ($placeholders)");
    $stmt->execute($orderIds);
    $deletedOrdersCount = $stmt->rowCount();

    // إنهاء المعاملة بنجاح
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'deleted_count' => $deletedOrdersCount,
        'deleted_items_count' => $deletedItemsCount,
        'message' => "Successfully deleted $deletedOrdersCount order(s) and their items"
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