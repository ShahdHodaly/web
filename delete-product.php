<?php
// delete-product.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// التحقق من أن المستخدم Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$productId = (int)$_POST['product_id'];

try {
    $pdo = getDB();

    // بدء المعاملة
    $pdo->beginTransaction();

    // حذف المنتج من wishlist أولاً (إذا وجد)
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE product_id = ?");
    $stmt->execute([$productId]);

    // حذف المنتج من cart_items (إذا وجد)
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE product_id = ?");
    $stmt->execute([$productId]);

    // حذف المراجعات المرتبطة بالمنتج
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE product_id = ?");
    $stmt->execute([$productId]);

    // حذف المنتج نفسه
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>