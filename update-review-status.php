<?php
// update-review-status.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// التحقق من تسجيل الدخول
if (empty($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$new_status = trim($_POST['status'] ?? '');

if ($review_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid review ID']);
    exit;
}

if (!in_array($new_status, ['approved', 'pending', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status: ' . $new_status]);
    exit;
}

$pdo = getDB();

try {
    // تأكد إنو الريفيو موجود
    $check = $pdo->prepare("SELECT review_id FROM reviews WHERE review_id = ?");
    $check->execute([$review_id]);

    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Review not found']);
        exit;
    }

    // حدّث الحالة
    $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE review_id = ?");
    $result = $stmt->execute([$new_status, $review_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Status updated to ' . $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}