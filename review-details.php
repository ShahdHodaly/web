<?php
// review-details.php
session_start();

// تضمين مصفوفة المراجعات
require_once 'reviews-array.php';

// الحصول على ID المراجعة من الرابط
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// التحقق من وجود المراجعة
if (!isset($reviews[$review_id])) {
    $_SESSION['error'] = 'Review not found';
    header("Location: reviews.php");
    exit;
}

$review = $reviews[$review_id];
$pageTitle = "Review by " . $review['customer_name'] . " | Teddy Shop";
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
            max-width: 900px;
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

        /* Review Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
        }
        .stat-card i {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .stat-card .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
        }
        .stat-card .stat-label {
            font-size: 13px;
            color: var(--secondary-text);
        }

        /* Admin Reply */
        .reply-section {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .reply-section h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
        }
        .reply-box {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 15px;
            border-left: 3px solid var(--primary);
        }
        .reply-text {
            margin: 0 0 10px 0;
            color: var(--text-color);
        }
        .reply-date {
            font-size: 11px;
            color: var(--secondary-text);
        }
        .reply-form textarea {
            width: 100%;
            padding: 12px;
            background: var(--card-bg);
            border: 2px solid transparent;
            border-radius: 16px;
            color: var(--text-color);
            font-size: 14px;
            resize: vertical;
            margin-bottom: 10px;
            outline: none;
        }
        .reply-form textarea:focus {
            border-color: var(--pink);
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
        .btn-reply {
            background: var(--primary);
            color: white;
        }
        .btn-reply:hover {
            background: var(--pink);
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

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
            .stats-grid { grid-template-columns: 1fr; }
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
                    <i class="fa-solid fa-<?= $review['status'] == 'approved' ? 'check-circle' : 'clock' ?>"></i>
                    <?= ucfirst($review['status']) ?>
                </div>
            </div>

            <!-- Product Information -->
            <div class="product-section">
                <img src="images/<?= $review['product_image'] ?>" class="product-image" alt="<?= $review['product_name'] ?>">
                <div class="product-details">
                    <h3><?= htmlspecialchars($review['product_name']) ?></h3>
                    <p><i class="fa-regular fa-tag"></i> Product ID: #<?= $review_id ?></p>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="customer-section">
                <div class="customer-avatar">
                    <img src="<?= $review['customer_avatar'] ?>" alt="<?= $review['customer_name'] ?>">
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

            <!-- Review Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fa-regular fa-thumbs-up"></i>
                    <div class="stat-value"><?= $review['helpful_count'] ?></div>
                    <div class="stat-label">Found Helpful</div>
                </div>
                <div class="stat-card">
                    <i class="fa-regular fa-clock"></i>
                    <div class="stat-value"><?= date('M d, Y', strtotime($review['date'])) ?></div>
                    <div class="stat-label">Review Date</div>
                </div>
                <div class="stat-card">
                    <i class="fa-regular fa-star"></i>
                    <div class="stat-value"><?= $review['rating'] ?></div>
                    <div class="stat-label">Rating</div>
                </div>
            </div>

            <!-- Admin Reply Section -->
            <div class="reply-section">
                <h4><i class="fa-solid fa-reply"></i> Admin Response</h4>
                <?php if (isset($review['reply']) && !empty($review['reply'])): ?>
                    <div class="reply-box">
                        <p class="reply-text"><?= nl2br(htmlspecialchars($review['reply'])) ?></p>
                        <p class="reply-date"><i class="fa-regular fa-calendar"></i> Replied on <?= date('M d, Y', strtotime($review['reply_date'] ?? 'now')) ?></p>
                    </div>
                <?php else: ?>
                    <div class="reply-form">
                        <textarea rows="3" placeholder="Write a response to this review..."></textarea>
                        <button class="btn-action btn-reply" onclick="submitReply(<?= $review_id ?>)">
                            <i class="fa-solid fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($review['status'] === 'pending'): ?>
                    <button class="btn-action btn-approve" onclick="approveReview(<?= $review_id ?>)">
                        <i class="fa-solid fa-check-circle"></i> Approve Review
                    </button>
                    <button class="btn-action btn-reject" onclick="rejectReview(<?= $review_id ?>)">
                        <i class="fa-solid fa-times-circle"></i> Reject Review
                    </button>
                <?php endif; ?>
                <a href="reviews.php" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Reviews
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    function approveReview(id) {
        if(confirm('Approve this review? It will be visible to customers.')) {
            alert('Review approved (Demo)');
        }
    }

    function rejectReview(id) {
        if(confirm('Reject this review? It will be removed from the site.')) {
            alert('Review rejected (Demo)');
        }
    }

    function submitReply(id) {
        const replyText = document.querySelector('.reply-form textarea').value;
        if(!replyText.trim()) {
            alert('Please enter a reply message');
            return;
        }
        if(confirm('Send reply to this review?')) {
            alert('Reply sent! (Demo)');
        }
    }
</script>

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