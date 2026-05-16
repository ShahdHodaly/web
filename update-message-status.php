<?php
// update-message-status.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['message_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$messageId = (int)$_POST['message_id'];
$status = $_POST['status'];
$allowedStatus = ['read', 'unread', 'replied'];

if (!in_array($status, $allowedStatus)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE messages SET status = ? WHERE message_id = ?");
    $stmt->execute([$status, $messageId]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>