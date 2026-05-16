<?php
// delete-item.php
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

$itemId = (int)($_POST['item_id'] ?? 0);
$itemType = $_POST['item_type'] ?? '';

if (!$itemId || !$itemType) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $pdo = getDB();

    if ($itemType === 'color') {
        // حذف من جدول teddy_colors
        $stmt = $pdo->prepare("DELETE FROM teddy_colors WHERE color_id = ?");
        $stmt->execute([$itemId]);
    } else {
        // حذف من جدول clothing_items
        // أولاً التحقق من عدم وجود custom_teddies مرتبطة بهذا العنصر
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM custom_teddies 
            WHERE outfit_item_id = ? OR shoes_item_id = ? OR accessory_item_id = ?
        ");
        $stmt->execute([$itemId, $itemId, $itemId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete: Item is used in custom teddies']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM clothing_items WHERE item_id = ?");
        $stmt->execute([$itemId]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>