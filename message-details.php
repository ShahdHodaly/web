<?php
// message-details.php
session_start();
require_once 'db.php';
require_once 'mailer.php';
$pdo = getDB();

// الحصول على ID الرسالة من الرابط
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// جلب الرسالة من قاعدة البيانات
$stmt = $pdo->prepare("
    SELECT 
        m.message_id,
        m.user_id,
        m.sender_name,
        m.sender_email,
        m.subject,
        m.message,
        m.reply,
        m.priority,
        m.status,
        m.created_at,
        u.avatar as sender_avatar
    FROM messages m
    LEFT JOIN users u ON m.user_id = u.user_id
    WHERE m.message_id = ?
");
$stmt->execute([$message_id]);
$messageData = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود الرسالة
if (!$messageData) {
    $_SESSION['error'] = 'Message not found';
    header("Location: messages.php");
    exit;
}

// تنسيق بيانات الرسالة
$message = [
        'sender_name' => $messageData['sender_name'],
        'sender_email' => $messageData['sender_email'],
        'sender_avatar' => $messageData['sender_avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($messageData['sender_name']) . '&background=F8BBD0&color=000&size=70',
        'subject' => $messageData['subject'],
        'message' => $messageData['message'],
        'priority' => $messageData['priority'],
        'status' => $messageData['status'],
        'date' => $messageData['created_at'],
        'reply' => $messageData['reply'],
        'reply_date' => $messageData['reply_date'] ?? null
];

$pageTitle = "Message from " . $message['sender_name'] . " | Teddy Shop";

// تحديث حالة الرسالة إلى مقروءة (إذا كانت غير مقروءة)
if ($message['status'] === 'unread') {
    try {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $message['status'] = 'read';
    } catch (PDOException $e) {
        // تجاهل الخطأ
    }
}

// ── تعريف كل المتغيرات اللي بتنستخدم في HTML ──
$reply_success = false;
$reply_error = '';
$emailSent = false;
$emailStatus = '';
$toEmail = $messageData['sender_email'] ?? '';
$toName = $messageData['sender_name'] ?? '';
$subject = 'Re: ' . ($messageData['subject'] ?? '');
$emailBody = '';

// معالجة إرسال الرد
$reply_success = false;
$reply_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    $reply_text = trim($_POST['reply_text'] ?? '');

    if (empty($reply_text)) {
        $reply_error = 'Please enter a reply message';
    } else {
        try {
            // حفظ الرد في الداتابيس
            $stmt = $pdo->prepare("
                UPDATE messages 
                SET reply = ?, 
                    status = 'replied' 
                WHERE message_id = ?
            ");
            $stmt->execute([$reply_text, $message_id]);

            // ── إرسال إيميل لليوزر ──
            $emailSent = false;

            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                try {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $_ENV['EMAIL_ADDRESS'];
                    $mail->Password   = $_ENV['EMAIL_PASSWORD'];
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom($_ENV['EMAIL_ADDRESS'], 'Teddy Lap');
                    $mail->addAddress($messageData['sender_email'], $messageData['sender_name']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Re: ' . $messageData['subject'];
                    $mail->Body    = '
                    <!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                    body{font-family:Poppins,Arial,sans-serif;background:#fff5f7;margin:0;padding:0;}
                    .container{max-width:560px;margin:40px auto;background:#fff;border-radius:24px;box-shadow:0 8px 30px rgba(255,107,129,0.12);overflow:hidden;}
                    .header{background:linear-gradient(135deg,#ff6b81,#ff9a9e);padding:40px 30px;text-align:center;}
                    .header h1{color:#fff;font-size:26px;margin:0 0 6px;}
                    .header p{color:rgba(255,255,255,0.9);font-size:14px;margin:0;}
                    .body{padding:36px 30px;}
                    .body h2{color:#333;font-size:20px;margin:0 0 14px;}
                    .body p{color:#666;font-size:14px;line-height:1.7;margin:0 0 16px;}
                    .reply-box{background:#fff5f7;border-radius:14px;padding:16px 20px;margin:20px 0;border-left:4px solid #ff6b81;}
                    .reply-box p{margin:0;font-size:14px;color:#555;line-height:1.6;}
                    .footer{background:#fff5f7;padding:20px 30px;text-align:center;font-size:12px;color:#aaa;}
                    </style></head><body>
                    <div class="container">
                    <div class="header"><h1>🧸 Message Reply</h1><p>From Teddy Lap Support</p></div>
                    <div class="body">
                    <h2>Hi ' . htmlspecialchars($messageData['sender_name']) . ' 👋</h2>
                    <p>Thank you for contacting us. Here is our reply to your message:</p>
                    <div class="reply-box">
                    <p>' . nl2br(htmlspecialchars($reply_text)) . '</p>
                    </div>
                    <p style="color:#888;font-size:13px;">If you have any further questions, feel free to contact us again.</p>
                    </div>
                    <div class="footer">© 2025 Teddy Shop · We love hearing from you!</div>
                    </div></body></html>';
                    $mail->AltBody = strip_tags($reply_text);

                    $mail->send();
                    $emailSent = true;
                } catch (Exception $e) {
                    $emailSent = false;
                }
            }

            $emailStatus = $emailSent
                    ? '✅ Email sent successfully to ' . $messageData['sender_email']
                    : '❌ Email failed to send. Check error_log for details.';

            $emailStatus = $emailSent
                    ? '✅ Email sent successfully to ' . $messageData['sender_email']
                    : '❌ Email failed to send. Check error_log for details.';

            // محاولة استخدام mailer.php لو موجود
            if (file_exists('mailer.php')) {
                require_once 'mailer.php';
                if (function_exists('sendOrderConfirmationEmail')) {
                    // استخدام دالة الإيميل الموجودة عندك
                    try {
                        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'your_email@gmail.com';     // ← غيّريه
                            $mail->Password   = 'your_app_password';        // ← غيّريه
                            $mail->SMTPSecure = 'tls';
                            $mail->Port       = 587;
                            $mail->setFrom('your_email@gmail.com', 'Teddy Lap');
                            $mail->addAddress($toEmail, $toName);
                            $mail->isHTML(true);
                            $mail->Subject = $subject;
                            $mail->Body    = $emailBody;
                            $mail->send();
                            $emailSent = true;
                        }
                    } catch (Exception $e) {
                        $emailSent = false;
                    }
                }
            }

            // لو mailer مش موجود، استخدم mail()
            if (!$emailSent) {
                $headers  = "From: Teddy Lap <noreply@teddylap.com>\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                @mail($toEmail, $subject, $emailBody, $headers);
            }

            $reply_success = true;
            $message['reply'] = $reply_text;
            $message['status'] = 'replied';
        } catch (PDOException $e) {
            $reply_error = 'Database error: ' . $e->getMessage();
        }
    }
}

// معالجة حذف الرسالة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $_SESSION['success'] = 'Message deleted successfully';
        header("Location: messages.php");
        exit;
    } catch (PDOException $e) {
        $reply_error = 'Database error: ' . $e->getMessage();
    }
}

// معالجة تعليم الرسالة كمقروءة (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    try {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE message_id = ?");
        $stmt->execute([$message_id]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
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
        .admin-wrapper { display: flex; align-items: flex-start; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); box-sizing: border-box; }

        .message-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 900px;
            margin: 0 auto;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--pink);
        }
        .message-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        .message-title p {
            color: var(--secondary-text);
        }
        .message-status {
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-read { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-unread { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .status-replied { background: rgba(33, 150, 243, 0.2); color: #2196F3; }

        .sender-section {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .sender-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--lavender);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .sender-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sender-details h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-color);
        }
        .sender-details p {
            color: var(--secondary-text);
            margin: 0;
        }

        .message-content {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .message-subject {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
        }
        .message-text {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-color);
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-card {
            background: var(--bg-color);
            border-radius: 20px;
            padding: 15px;
            text-align: center;
        }
        .info-card i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 8px;
        }
        .info-card .info-label {
            font-size: 12px;
            color: var(--secondary-text);
        }
        .info-card .info-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin-top: 5px;
        }

        .priority-badge {
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .priority-high { background: rgba(244, 67, 54, 0.2); color: #F44336; }
        .priority-medium { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .priority-low { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }

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
            white-space: pre-wrap;
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

        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: 1px solid #F44336;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
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
        .btn-reply {
            background: var(--primary);
            color: white;
        }
        .btn-reply:hover {
            background: var(--pink);
            transform: translateY(-2px);
        }
        .btn-mark-read {
            background: #4CAF50;
            color: white;
        }
        .btn-mark-read:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        .btn-delete {
            background: #ff6b6b;
            color: white;
        }
        .btn-delete:hover {
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
            .admin-sidebar { width: 100%; height: auto; position: relative; top: 0; }
            .admin-main { width: 100%; }
            .info-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="message-container">
            <!-- Message Header -->
            <div class="message-header">
                <div class="message-title">
                    <h1>Message Details</h1>
                    <p><i class="fa-regular fa-envelope"></i> Received on <?= date('F d, Y \a\t h:i A', strtotime($message['date'])) ?></p>
                </div>
                <div class="message-status status-<?= $message['status'] ?>">
                    <i class="fa-solid fa-<?= $message['status'] == 'read' ? 'check-circle' : ($message['status'] == 'unread' ? 'clock' : 'reply') ?>"></i>
                    <?= ucfirst($message['status']) ?>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($reply_success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Reply sent successfully!</span>
                </div>
            <?php endif; ?>

            <?php if ($reply_error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <span><?= htmlspecialchars($reply_error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Sender Information -->
            <div class="sender-section">
                <div class="sender-avatar">
                    <img src="<?= htmlspecialchars($message['sender_avatar']) ?>" alt="<?= htmlspecialchars($message['sender_name']) ?>" onerror="this.src='images/default-avatar.png'">
                </div>
                <div class="sender-details">
                    <h3><?= htmlspecialchars($message['sender_name']) ?></h3>
                    <p><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($message['sender_email']) ?></p>
                </div>
            </div>

            <!-- Message Content -->
            <div class="message-content">
                <div class="message-subject">
                    <i class="fa-solid fa-heading"></i> <?= htmlspecialchars($message['subject']) ?>
                </div>
                <div class="message-text">
                    <?= nl2br(htmlspecialchars($message['message'])) ?>
                </div>
            </div>



            <!-- Admin Reply Section -->
            <div class="reply-section">
                <h4><i class="fa-solid fa-reply"></i> Admin Response</h4>
                <?php if (!empty($message['reply'])): ?>
                    <div class="reply-box">
                        <p class="reply-text"><?= nl2br(htmlspecialchars($message['reply'])) ?></p>
                    </div>
                <?php else: ?>
                    <form method="POST" class="reply-form" id="replyForm">
                        <input type="hidden" name="action" value="reply">
                        <textarea name="reply_text" id="replyMessage" rows="4" placeholder="Write a response to this message..."></textarea>
                        <button type="submit" class="btn-action btn-reply">
                            <i class="fa-solid fa-paper-plane"></i> Send Reply
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($message['status'] === 'unread'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="mark_read">
                        <button type="submit" class="btn-action btn-mark-read">
                            <i class="fa-solid fa-check-double"></i> Mark as Read
                        </button>
                    </form>
                <?php endif; ?>
                <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn-action btn-delete">
                        <i class="fa-solid fa-trash"></i> Delete Message
                    </button>
                </form>
                <a href="messages.php" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Messages
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete this message? This action cannot be undone.');
    }

    // Form validation for reply
    document.getElementById('replyForm')?.addEventListener('submit', function(e) {
        const textarea = this.querySelector('textarea[name="reply_text"]');
        if (!textarea.value.trim()) {
            e.preventDefault();
            alert('Please enter a reply message');
        }
    });

    // Focus effects
    const inputs = document.querySelectorAll('textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    });
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