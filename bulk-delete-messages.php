<?php
// bulk-delete-messages.php
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
if (!isset($input['message_ids']) || empty($input['message_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No messages selected']);
    exit;
}

$messageIds = array_map('intval', $input['message_ids']);
$placeholders = implode(',', array_fill(0, count($messageIds), '?'));

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM messages WHERE message_id IN ($placeholders)");
    $stmt->execute($messageIds);
    echo json_encode(['success' => true, 'deleted_count' => $stmt->rowCount()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>