<?php
// bulk-delete-users.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['user_ids']) || empty($input['user_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No users selected']);
    exit;
}

$userIds = array_map('intval', $input['user_ids']);
$placeholders = implode(',', array_fill(0, count($userIds), '?'));

// منع حذف المستخدم الحالي
if (in_array($_SESSION['user_id'], $userIds)) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // حذف عناصر السلة
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id IN ($placeholders))");
    $stmt->execute($userIds);

    // حذف السلة
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id IN ($placeholders)");
    $stmt->execute($userIds);

    // حذف عناصر الطلبات
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN (SELECT order_id FROM orders WHERE user_id IN ($placeholders))");
    $stmt->execute($userIds);

    // حذف الطلبات
    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id IN ($placeholders)");
    $stmt->execute($userIds);

    // حذف المراجعات
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id IN ($placeholders)");
    $stmt->execute($userIds);

    // حذف من قائمة الرغبات
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id IN ($placeholders)");
    $stmt->execute($userIds);

    // حذف الرسائل
    $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id IN ($placeholders)");
    $stmt->execute($userIds);

    // حذف التصاميم المخصصة
    $stmt = $pdo->prepare("DELETE FROM custom_teddies WHERE user_id IN ($placeholders)");
    $stmt->execute($userIds);

    // حذف المستخدمين
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id IN ($placeholders)");
    $stmt->execute($userIds);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?><?php
