<?php
// bulk-approve-reviews.php
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

// التحقق من وجود review_ids
if (!isset($input['review_ids']) || empty($input['review_ids'])) {
    echo json_encode(['success' => false, 'message' => 'No reviews selected']);
    exit;
}

$reviewIds = array_map('intval', $input['review_ids']);
$placeholders = implode(',', array_fill(0, count($reviewIds), '?'));

try {
    $pdo = getDB();

    // بدء المعاملة
    $pdo->beginTransaction();

    // تحديث حالة المراجعات إلى approved
    $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE review_id IN ($placeholders)");
    $stmt->execute($reviewIds);

    $updatedCount = $stmt->rowCount();

    // إنهاء المعاملة بنجاح
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'updated_count' => $updatedCount,
        'message' => "Successfully approved $updatedCount review(s)"
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