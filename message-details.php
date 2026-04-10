<?php
// message-details.php
session_start();

// تضمين مصفوفة الرسائل
require_once 'messages-array.php';

// الحصول على ID الرسالة من الرابط
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// التحقق من وجود الرسالة
if (!isset($messages[$message_id])) {
    $_SESSION['error'] = 'Message not found';
    header("Location: messages.php");
    exit;
}

$message = $messages[$message_id];
$pageTitle = "Message from " . $message['sender_name'] . " | Teddy Shop";

// تحديث حالة الرسالة إلى مقروءة (إذا كانت غير مقروءة)
if ($message['status'] === 'unread') {
    // في التطبيق الحقيقي، ستقوم بتحديث قاعدة البيانات
    // للتجربة، نعرض رسالة فقط
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

        /* Sender Info */
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

        /* Message Content */
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

        /* Message Info */
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

        /* Priority Badge */
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

        /* Reply Section */
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

        /* Action Buttons */
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

            <!-- Sender Information -->
            <div class="sender-section">
                <div class="sender-avatar">
                    <img src="<?= $message['sender_avatar'] ?>" alt="<?= $message['sender_name'] ?>">
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

            <!-- Message Info -->
            <div class="info-grid">
                <div class="info-card">
                    <i class="fa-regular fa-calendar"></i>
                    <div class="info-label">Date & Time</div>
                    <div class="info-value"><?= date('M d, Y', strtotime($message['date'])) ?><br><small><?= date('h:i A', strtotime($message['date'])) ?></small></div>
                </div>
                <div class="info-card">
                    <i class="fa-solid fa-flag"></i>
                    <div class="info-label">Priority</div>
                    <div class="info-value">
                        <span class="priority-badge priority-<?= $message['priority'] ?>">
                            <?= ucfirst($message['priority']) ?>
                        </span>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fa-regular fa-id-card"></i>
                    <div class="info-label">Message ID</div>
                    <div class="info-value">#<?= $message_id ?></div>
                </div>
            </div>

            <!-- Admin Reply Section -->
            <div class="reply-section">
                <h4><i class="fa-solid fa-reply"></i> Admin Response</h4>
                <?php if (isset($message['reply']) && !empty($message['reply'])): ?>
                    <div class="reply-box">
                        <p class="reply-text"><?= nl2br(htmlspecialchars($message['reply'])) ?></p>
                        <p class="reply-date"><i class="fa-regular fa-calendar"></i> Replied on <?= date('M d, Y', strtotime($message['reply_date'] ?? 'now')) ?></p>
                    </div>
                <?php else: ?>
                    <div class="reply-form">
                        <textarea id="replyMessage" rows="4" placeholder="Write a response to this message..."></textarea>
                        <button class="btn-action btn-reply" onclick="sendReply(<?= $message_id ?>)">
                            <i class="fa-solid fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($message['status'] === 'unread'): ?>
                    <button class="btn-action btn-mark-read" onclick="markAsRead(<?= $message_id ?>)">
                        <i class="fa-solid fa-check-double"></i> Mark as Read
                    </button>
                <?php endif; ?>
                <button class="btn-action btn-delete" onclick="deleteMessage(<?= $message_id ?>)">
                    <i class="fa-solid fa-trash"></i> Delete Message
                </button>
                <a href="messages.php" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Messages
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    function markAsRead(id) {
        // إنشاء الـ overlay
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        overlay.style.zIndex = '9998';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        // إنشاء الـ popup
        const popup = document.createElement('div');
        popup.style.backgroundColor = 'var(--card-bg, #ffffff)';
        popup.style.color = 'var(--text-color, #333)';
        popup.style.borderRadius = '28px';
        popup.style.padding = '28px 24px';
        popup.style.maxWidth = '420px';
        popup.style.width = '90%';
        popup.style.boxShadow = '0 25px 45px rgba(0,0,0,0.25)';
        popup.style.textAlign = 'center';
        popup.style.fontFamily = "'Poppins', sans-serif";
        popup.style.transform = 'scale(0.9)';
        popup.style.transition = 'transform 0.25s ease';
        popup.style.border = '1px solid var(--pink, #F8BBD0)';

        // أيقونة
        const icon = document.createElement('div');
        icon.textContent = '✉️';
        icon.style.fontSize = '40px';
        icon.style.marginBottom = '12px';

        // نص التأكيد
        const message = document.createElement('p');
        message.textContent = 'Mark this message as read?';
        message.style.fontSize = '15px';
        message.style.margin = '0 0 22px 0';
        message.style.lineHeight = '1.6';

        // حاوية الأزرار
        const btnContainer = document.createElement('div');
        btnContainer.style.display = 'flex';
        btnContainer.style.gap = '12px';
        btnContainer.style.justifyContent = 'center';

        // زر الإلغاء
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.style.flex = '1';
        cancelBtn.style.padding = '11px 20px';
        cancelBtn.style.borderRadius = '14px';
        cancelBtn.style.border = '1px solid var(--pink, #F8BBD0)';
        cancelBtn.style.backgroundColor = 'transparent';
        cancelBtn.style.color = 'var(--text-color, #333)';
        cancelBtn.style.fontFamily = "'Poppins', sans-serif";
        cancelBtn.style.fontSize = '14px';
        cancelBtn.style.fontWeight = '500';
        cancelBtn.style.cursor = 'pointer';
        cancelBtn.style.transition = 'all 0.2s ease';
        cancelBtn.addEventListener('mouseover', function() {
            cancelBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            cancelBtn.style.color = '#fff';
        });
        cancelBtn.addEventListener('mouseout', function() {
            cancelBtn.style.backgroundColor = 'transparent';
            cancelBtn.style.color = 'var(--text-color, #333)';
        });

        // زر التأكيد
        const confirmBtn = document.createElement('button');
        confirmBtn.textContent = 'Mark as Read';
        confirmBtn.style.flex = '1';
        confirmBtn.style.padding = '11px 20px';
        confirmBtn.style.borderRadius = '14px';
        confirmBtn.style.border = 'none';
        confirmBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
        confirmBtn.style.color = '#fff';
        confirmBtn.style.fontFamily = "'Poppins', sans-serif";
        confirmBtn.style.fontSize = '14px';
        confirmBtn.style.fontWeight = '600';
        confirmBtn.style.cursor = 'pointer';
        confirmBtn.style.transition = 'all 0.2s ease';
        confirmBtn.addEventListener('mouseover', function() {
            confirmBtn.style.backgroundColor = '#F48FB1';
            confirmBtn.style.transform = 'scale(1.03)';
        });
        confirmBtn.addEventListener('mouseout', function() {
            confirmBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            confirmBtn.style.transform = 'scale(1)';
        });

        // تجميع
        btnContainer.appendChild(cancelBtn);
        btnContainer.appendChild(confirmBtn);
        popup.appendChild(icon);
        popup.appendChild(message);
        popup.appendChild(btnContainer);
        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // تأثير الظهور
        requestAnimationFrame(function() {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        });

        // دالة إغلاق
        function closePopup() {
            popup.style.transform = 'scale(0.9)';
            overlay.style.opacity = '0';
            setTimeout(function() {
                overlay.remove();
            }, 300);
        }

        // دالة إظهار رسالة النجاح
        function showSuccess() {
            // تفريغ الـ popup
            popup.innerHTML = '';

            // أيقونة النجاح
            const successIcon = document.createElement('div');
            successIcon.textContent = '✅';
            successIcon.style.fontSize = '40px';
            successIcon.style.marginBottom = '12px';

            // نص النجاح
            const successMsg = document.createElement('p');
            successMsg.textContent = 'Message marked as read (Demo)';
            successMsg.style.fontSize = '15px';
            successMsg.style.margin = '0 0 22px 0';
            successMsg.style.lineHeight = '1.6';

            // زر حسناً
            const okBtn = document.createElement('button');
            okBtn.textContent = 'OK';
            okBtn.style.padding = '11px 40px';
            okBtn.style.borderRadius = '14px';
            okBtn.style.border = 'none';
            okBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            okBtn.style.color = '#fff';
            okBtn.style.fontFamily = "'Poppins', sans-serif";
            okBtn.style.fontSize = '14px';
            okBtn.style.fontWeight = '600';
            okBtn.style.cursor = 'pointer';
            okBtn.style.transition = 'all 0.2s ease';
            okBtn.addEventListener('mouseover', function() {
                okBtn.style.backgroundColor = '#F48FB1';
            });
            okBtn.addEventListener('mouseout', function() {
                okBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            });

            // تأثير تبديل المحتوى
            popup.style.transform = 'scale(0.9)';
            setTimeout(function() {
                popup.appendChild(successIcon);
                popup.appendChild(successMsg);
                popup.appendChild(okBtn);
                popup.style.transform = 'scale(1)';
            }, 250);

            okBtn.addEventListener('click', closePopup);
        }

        // أحداث الأزرار
        cancelBtn.addEventListener('click', closePopup);

        confirmBtn.addEventListener('click', function() {
            showSuccess();
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closePopup();
        });

        document.addEventListener('keydown', function handleEsc(e) {
            if (e.key === 'Escape') {
                closePopup();
                document.removeEventListener('keydown', handleEsc);
            }
        });
    }

    function showAdminConfirm(message, onConfirm) {
        // 1. إنشاء overlay الخلفية
        const overlay = document.createElement('div');
        overlay.id = 'admin-confirm-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
        overlay.style.backdropFilter = 'blur(3px)';
        overlay.style.zIndex = '9998';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        // 2. إنشاء نافذة الـ Popup
        const popup = document.createElement('div');
        popup.id = 'admin-confirm-popup';
        popup.style.backgroundColor = 'var(--card-bg, #ffffff)';
        popup.style.color = 'var(--text-color, #333)';
        popup.style.borderRadius = '28px';
        popup.style.padding = '28px 24px';
        popup.style.maxWidth = '420px';
        popup.style.width = '90%';
        popup.style.boxShadow = '0 25px 45px rgba(0,0,0,0.25)';
        popup.style.textAlign = 'center';
        popup.style.fontFamily = "'Poppins', sans-serif";
        popup.style.transform = 'scale(0.9)';
        popup.style.transition = 'transform 0.25s ease';
        popup.style.border = '1px solid var(--pink, #F8BBD0)';

        // محتوى البوب أب
        popup.innerHTML = `
        <div style="font-size: 58px; margin-bottom: 12px;">⚠️</div>
        <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 12px;">Are you sure?</h3>
        <p style="font-size: 16px; color: var(--secondary-text, #555); margin-bottom: 28px; line-height: 1.5;">${message}</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button id="confirm-cancel-btn" style="background: transparent; border: 2px solid var(--pink, #F8BBD0); padding: 10px 24px; border-radius: 40px; font-weight: 600; cursor: pointer; color: var(--text-color, #333); transition: all 0.2s;">Cancel</button>
            <button id="confirm-ok-btn" style="background: #d9534f; border: none; padding: 10px 28px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; box-shadow: 0 4px 8px rgba(217,83,79,0.3); transition: all 0.2s;">Delete</button>
        </div>
    `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // ظهور الأنيميشن
        setTimeout(() => {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        }, 10);

        // إزالة البوب أب
        function closePopup() {
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            setTimeout(() => {
                if (overlay && overlay.parentNode) overlay.remove();
            }, 250);
        }

        // دالة عرض رسالة النجاح (toast منتصف الصفحة)
        function showSuccessToast() {
            const toast = document.createElement('div');
            toast.id = 'admin-success-toast';
            toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">

                <div>
                    <strong style="font-size: 18px;">Removed from the system!</strong>

                </div>
            </div>
        `;
            toast.style.position = 'fixed';
            toast.style.top = '50%';
            toast.style.left = '50%';
            toast.style.transform = 'translate(-50%, -50%) scale(0.9)';
            toast.style.backgroundColor = 'var(--card-bg, #fff)';
            toast.style.color = 'var(--text-color, #333)';
            toast.style.padding = '18px 28px';
            toast.style.borderRadius = '60px';
            toast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
            toast.style.zIndex = '10000';
            toast.style.fontFamily = "'Poppins', sans-serif";
            toast.style.borderRight = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderLeft = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderTop = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderBottom = '4px solid var(--pink, #F8BBD0)';
            toast.style.backdropFilter = 'blur(12px)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1)';
            toast.style.fontWeight = '500';
            toast.style.textAlign = 'center';
            toast.style.minWidth = '280px';
            toast.style.boxSizing = 'border-box';

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 20);

            // إخفاء الرسالة بعد 2.5 ثانية
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                setTimeout(() => {
                    if (toast && toast.parentNode) toast.remove();
                }, 250);
            }, 2500);
        }

        // أحداث الأزرار
        const cancelBtn = popup.querySelector('#confirm-cancel-btn');
        const confirmBtn = popup.querySelector('#confirm-ok-btn');

        cancelBtn.addEventListener('click', () => {
            closePopup();
        });

        confirmBtn.addEventListener('click', () => {
            // ✅ بدون حذف فعلي – فقط استدعاء callback إذا أردت تنفيذ شيء لاحقاً (مثل تحديث واجهة)
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();  // هون بتقدر تعمل أي شيء زي تحديث UI بدون حذف حقيقي
            }
            closePopup();
            // عرض رسالة النجاح الجميلة في منتصف الصفحة
            showSuccessToast();
        });

        // إغلاق عند الضغط على overlay
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePopup();
        });
    }

    function deleteMessage(id) {
       showAdminConfirm('Are you sure you want to delete this message?', 'Delete Message', 'Delete', 'Cancel', () => {

       })
    }

    function sendReply(id) {
        const replyText = document.getElementById('replyMessage')?.value.trim();
        if(!replyText) {
            alert('Please enter a reply message');
            return;
        }
        if(confirm('Send reply to this message?')) {
            alert('Reply sent! (Demo)');
        }
    }


    // Focus effects
    const inputs = document.querySelectorAll('.form-control, textarea');
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