<?php
// delete-user.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = (int)$_POST['user_id'];

// منع حذف المستخدم الحالي (نفسه)
if ($userId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
    exit;
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // حذف عناصر السلة أولاً
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id = ?)");
    $stmt->execute([$userId]);

    // حذف السلة
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);

    // حذف عناصر الطلبات
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN (SELECT order_id FROM orders WHERE user_id = ?)");
    $stmt->execute([$userId]);

    // حذف الطلبات
    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
    $stmt->execute([$userId]);

    // حذف المراجعات
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
    $stmt->execute([$userId]);

    // حذف من قائمة الرغبات
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ?");
    $stmt->execute([$userId]);

    // حذف الرسائل
    $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id = ?");
    $stmt->execute([$userId]);

    // حذف التصاميم المخصصة
    $stmt = $pdo->prepare("DELETE FROM custom_teddies WHERE user_id = ?");
    $stmt->execute([$userId]);

    // حذف المستخدم
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>