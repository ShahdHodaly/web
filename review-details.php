<?php
// review-details.php
session_start();
require_once 'db.php';

$pdo = getDB();

// الحصول على ID المراجعة من الرابط
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب المراجعة من قاعدة البيانات مع معلومات المستخدم والمنتج
$stmt = $pdo->prepare("
    SELECT 
        r.review_id,
        r.rating,
        r.comment,
        r.status::text as status,
        r.helpful_count,
        r.created_at,
        u.user_id,
        u.name as customer_name,
        u.email as customer_email,
        u.avatar as customer_avatar,
        p.product_id,
        p.name as product_name,
        p.image as product_image
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN products p ON r.product_id = p.product_id
    WHERE r.review_id = ?
");
$stmt->execute([$review_id]);
$reviewData = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود المراجعة
if (!$reviewData) {
    $_SESSION['error'] = 'Review not found';
    header("Location: reviews.php");
    exit;
}

// تنسيق بيانات المراجعة
$review = [
        'id' => $reviewData['review_id'],
        'product_name' => $reviewData['product_name'],
        'product_image' => $reviewData['product_image'] ?: 'placeholder.png',
        'customer_name' => $reviewData['customer_name'],
        'customer_avatar' => $reviewData['customer_avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($reviewData['customer_name']) . '&background=F8BBD0&color=000&size=70',
        'rating' => (int)$reviewData['rating'],
        'comment' => $reviewData['comment'],
        'date' => $reviewData['created_at'],
        'status' => $reviewData['status'],
        'helpful_count' => (int)$reviewData['helpful_count']
];

$pageTitle = "Review by " . $review['customer_name'] . " | Teddy Shop";

// معالجة تغيير حالة المراجعة (Approve/Reject)
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'approve') {
        try {
            $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved'::review_status WHERE review_id = ?");
            $stmt->execute([$review_id]);
            $review['status'] = 'approved';
            $success_message = 'Review approved successfully!';
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'reject') {
        try {
            $stmt = $pdo->prepare("UPDATE reviews SET status = 'rejected'::review_status WHERE review_id = ?");
            $stmt->execute([$review_id]);
            $review['status'] = 'rejected';
            $success_message = 'Review rejected successfully!';
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ملفات CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        .review-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 800px;
            margin: 0 auto;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .review-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        .review-title p {
            color: var(--secondary-text);
        }
        .review-status {
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-approved { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-pending { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .status-rejected { background: rgba(244, 67, 54, 0.2); color: #F44336; }

        /* Product Info */
        .product-section {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: cover;
        }
        .product-details h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        .product-details p {
            color: var(--secondary-text);
            margin: 0;
        }

        /* Customer Info */
        .customer-section {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .customer-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--lavender);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .customer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .customer-details h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        .customer-details p {
            color: var(--secondary-text);
            margin: 0;
        }

        /* Rating & Review */
        .rating-section {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .rating-stars {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }
        .rating-stars i {
            font-size: 28px;
        }
        .review-text {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-color);
            background: var(--card-bg);
            padding: 20px;
            border-radius: 20px;
            margin-top: 15px;
            border-left: 4px solid var(--pink);
        }

        /* Alert Messages */
        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }
        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
            border: 1px solid #F44336;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-approve {
            background: #4CAF50;
            color: white;
        }
        .btn-approve:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        .btn-reject {
            background: #ff6b6b;
            color: white;
        }
        .btn-reject:hover {
            background: #ff4757;
            transform: translateY(-2px);
        }
        .btn-back {
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 1px solid rgba(128,128,128,0.2);
        }
        .btn-back:hover {
            border-color: var(--pink);
            transform: translateY(-2px);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="review-container">
            <!-- Review Header -->
            <div class="review-header">
                <div class="review-title">
                    <h1>Review Details</h1>
                    <p><i class="fa-regular fa-calendar"></i> <?= date('F d, Y \a\t h:i A', strtotime($review['date'])) ?></p>
                </div>
                <div class="review-status status-<?= $review['status'] ?>">
                    <i class="fa-solid fa-<?= $review['status'] == 'approved' ? 'check-circle' : ($review['status'] == 'pending' ? 'clock' : 'times-circle') ?>"></i>
                    <?= ucfirst($review['status']) ?>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span><?= htmlspecialchars($success_message) ?> <a href="reviews.php" style="color: #4CAF50;">Back to Reviews</a></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <!-- Product Information -->
            <div class="product-section">
                <img src="<?= htmlspecialchars($review['product_image']) ?>" class="product-image" alt="<?= htmlspecialchars($review['product_name']) ?>" onerror="this.src='images/placeholder.png'">
                <div class="product-details">
                    <h3><?= htmlspecialchars($review['product_name']) ?></h3>
                    <p><i class="fa-regular fa-tag"></i> Product ID: #<?= $review_id ?></p>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="customer-section">
                <div class="customer-avatar">
                    <img src="<?= htmlspecialchars($review['customer_avatar']) ?>" alt="<?= htmlspecialchars($review['customer_name']) ?>" onerror="this.src='images/teddy4.png'">
                </div>
                <div class="customer-details">
                    <h3><?= htmlspecialchars($review['customer_name']) ?></h3>
                    <p><i class="fa-regular fa-envelope"></i> Customer review</p>
                </div>
            </div>

            <!-- Rating & Review -->
            <div class="rating-section">
                <div class="rating-stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-<?= $i <= $review['rating'] ? 'solid' : 'regular' ?> fa-star" style="color: #FFD700;"></i>
                    <?php endfor; ?>
                    <span style="margin-left: 10px; font-weight: 600;"><?= $review['rating'] ?> out of 5</span>
                </div>
                <div class="review-text">
                    <i class="fa-solid fa-quote-left" style="color: var(--primary); opacity: 0.5; margin-right: 8px;"></i>
                    <?= nl2br(htmlspecialchars($review['comment'])) ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($review['status'] === 'pending'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn-action btn-approve">
                            <i class="fa-solid fa-check-circle"></i> Approve Review
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn-action btn-reject">
                            <i class="fa-solid fa-times-circle"></i> Reject Review
                        </button>
                    </form>
                <?php endif; ?>
                <a href="reviews.php" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Reviews
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    (function() {
        const themeSwitchMain = document.getElementById('themeSwitchSidebar');
        function applyTheme(isDark) {
            if (isDark) { document.body.classList.add('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = true; }
            else { document.body.classList.remove('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = false; }
        }
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true); else applyTheme(false);
        if (themeSwitchMain) themeSwitchMain.addEventListener('change', function(e) { applyTheme(this.checked); localStorage.setItem('theme', this.checked ? 'dark' : 'light'); });
    })();
</script>
</body>
</html>