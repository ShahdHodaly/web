<?php
// compose-message.php
session_start();

// تضمين مصفوفة الرسائل (لجلب بيانات الرد إن وجد)
require_once 'messages-array.php';

// متغيرات النموذج
$reply_to = isset($_GET['reply']) ? (int)$_GET['reply'] : 0;
$recipient_name = '';
$recipient_email = '';
$subject = '';
$message = '';
$errors = [];
$success = false;

// إذا كان الرد على رسالة موجودة
if ($reply_to > 0 && isset($messages[$reply_to])) {
    $original = $messages[$reply_to];
    $recipient_name = $original['sender_name'];
    $recipient_email = $original['sender_email'];
    $subject = 'Re: ' . $original['subject'];
    $message = "\n\n--- Original Message ---\nFrom: " . $original['sender_name'] . "\nDate: " . date('M d, Y h:i A', strtotime($original['date'])) . "\n\n" . $original['message'];
}

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = trim($_POST['recipient_name'] ?? '');
    $recipient_email = trim($_POST['recipient_email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = trim($_POST['priority'] ?? 'medium');

    // التحقق من صحة البيانات
    if (empty($recipient_name)) {
        $errors[] = 'Recipient name is required';
    }

    if (empty($recipient_email)) {
        $errors[] = 'Recipient email is required';
    } elseif (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }

    if (empty($message)) {
        $errors[] = 'Message is required';
    }

    // إذا لم يكن هناك أخطاء
    if (empty($errors)) {
        // هنا يتم حفظ الرسالة في قاعدة البيانات
        // للتجربة، نعرض رسالة نجاح

        $success = true;

        // إعادة تعيين النموذج بعد النجاح
        if (!$reply_to) {
            $recipient_name = '';
            $recipient_email = '';
            $subject = '';
            $message = '';
            $priority = 'medium';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message · Teddy Shop</title>
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

        .form-container {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 4px 15px var(--shadow);
            animation: fadeInUp 0.6s ease;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .form-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .form-header p {
            color: var(--secondary-text);
        }

        .reply-badge {
            background: var(--lavender);
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        .form-group label i {
            color: var(--primary);
            margin-right: 8px;
        }
        .required {
            color: #ff6b6b;
            margin-left: 4px;
        }
        .form-control {
            width: 100%;
            padding: 14px 18px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 16px;
            color: var(--text-color);
            font-size: 15px;
            transition: all 0.3s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }
        select.form-control {
            cursor: pointer;
        }

        /* Priority Select */
        .priority-option {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .priority-high { color: #F44336; }
        .priority-medium { color: #FF9800; }
        .priority-low { color: #4CAF50; }

        .alert {
            padding: 12px 18px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
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

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-submit {
            flex: 1;
            padding: 14px 25px;
            background: linear-gradient(135deg, var(--primary), var(--pink));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(248, 187, 208, 0.5);
        }
        .btn-cancel {
            flex: 1;
            padding: 14px 25px;
            background: var(--card-bg);
            color: var(--secondary-text);
            border: 2px solid rgba(128,128,128,0.2);
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
        }
        .btn-cancel:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .form-container { margin: 0 15px; }
            .form-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="form-container">
            <div class="form-header">
                <h1>
                    <i class="fa-solid fa-envelope" style="color: var(--primary);"></i>
                    <?= $reply_to > 0 ? 'Reply to Message' : 'Compose New Message' ?>
                </h1>
                <p><?= $reply_to > 0 ? 'Send a reply to this customer' : 'Send a new message to a customer' ?></p>
            </div>

            <?php if ($reply_to > 0): ?>
                <div class="reply-badge">
                    <i class="fa-solid fa-reply"></i> Replying to: <?= htmlspecialchars($original['sender_name']) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Message sent successfully! <a href="messages.php" style="color: #4CAF50;">Back to messages</a></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="compose-message.php<?= $reply_to > 0 ? '?reply=' . $reply_to : '' ?>" method="POST" id="messageForm">
                <!-- Recipient Name -->
                <div class="form-group">
                    <label><i class="fa-solid fa-user"></i> Recipient Name <span class="required">*</span></label>
                    <input type="text" name="recipient_name" class="form-control" value="<?= htmlspecialchars($recipient_name) ?>" placeholder="Enter recipient name" required>
                </div>

                <!-- Recipient Email -->
                <div class="form-group">
                    <label><i class="fa-solid fa-envelope"></i> Recipient Email <span class="required">*</span></label>
                    <input type="email" name="recipient_email" class="form-control" value="<?= htmlspecialchars($recipient_email) ?>" placeholder="recipient@email.com" required>
                </div>

                <!-- Subject -->
                <div class="form-group">
                    <label><i class="fa-solid fa-heading"></i> Subject <span class="required">*</span></label>
                    <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($subject) ?>" placeholder="Enter message subject" required>
                </div>

                <!-- Priority -->
                <div class="form-group">
                    <label><i class="fa-solid fa-flag"></i> Priority</label>
                    <select name="priority" class="form-control">
                        <option value="" disabled <?= empty($priority) ? 'selected' : '' ?>>Select priority</option>
                        <option value="low" <?= isset($priority) && strtolower($priority) == 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= isset($priority) && strtolower($priority) == 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= isset($priority) && strtolower($priority) == 'high' ? 'selected' : '' ?>>High</option>
                    </select>
                </div>

                <!-- Message -->
                <div class="form-group">
                    <label><i class="fa-solid fa-message"></i> Message <span class="required">*</span></label>
                    <textarea name="message" class="form-control" placeholder="Type your message here..." required><?= htmlspecialchars($message) ?></textarea>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> Send Message
                    </button>
                    <a href="messages.php" class="btn-cancel">
                        <i class="fa-solid fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Character counter for message
    const messageTextarea = document.querySelector('textarea[name="message"]');
    if (messageTextarea) {
        // Create counter element
        const counterDiv = document.createElement('div');
        counterDiv.style.cssText = 'text-align: right; font-size: 12px; color: var(--secondary-text); margin-top: 5px;';
        counterDiv.innerHTML = '<span id="charCount">0</span> characters';
        messageTextarea.parentNode.appendChild(counterDiv);

        const charCountSpan = document.getElementById('charCount');

        function updateCharCount() {
            const count = messageTextarea.value.length;
            charCountSpan.textContent = count;
            if (count > 1000) {
                charCountSpan.style.color = '#ff6b6b';
            } else {
                charCountSpan.style.color = 'var(--secondary-text)';
            }
        }

        messageTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    // Form validation
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="recipient_name"]').value.trim();
        const email = document.querySelector('input[name="recipient_email"]').value.trim();
        const subject = document.querySelector('input[name="subject"]').value.trim();
        const message = document.querySelector('textarea[name="message"]').value.trim();

        if (!name) {
            e.preventDefault();
            alert('Please enter recipient name');
            return false;
        }
        if (!email) {
            e.preventDefault();
            alert('Please enter recipient email');
            return false;
        }
        if (!subject) {
            e.preventDefault();
            alert('Please enter a subject');
            return false;
        }
        if (!message) {
            e.preventDefault();
            alert('Please enter a message');
            return false;
        }
    });

    // Focus effects
    const inputs = document.querySelectorAll('.form-control');
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