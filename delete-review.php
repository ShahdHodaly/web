<?php
// delete-review.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['review_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$reviewId = (int)$_POST['review_id'];

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->execute([$reviewId]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>