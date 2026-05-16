<?php
// bulk-delete-products.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// التحقق من أن المستخدم Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['product_ids']) || empty($input['product_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No products selected']);
    exit;
}

$productIds = array_map('intval', $input['product_ids']);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

try {
    $pdo = getDB();
    $pdo->beginTransaction();

    // حذف من wishlist
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE product_id IN ($placeholders)");
    $stmt->execute($productIds);

    // حذف من cart_items
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE product_id IN ($placeholders)");
    $stmt->execute($productIds);

    // حذف المراجعات
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE product_id IN ($placeholders)");
    $stmt->execute($productIds);

    // حذف المنتجات
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($productIds);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>