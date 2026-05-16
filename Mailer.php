<?php
// mailer.php — إرسال الإيميلات عبر Gmail
// يستخدم PHPMailer الموجود في vendor/
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



function sendLoginNotificationEmail(string $toEmail, string $toName): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_ADDRESS'];
        $mail->Password   = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['EMAIL_ADDRESS'], $_ENV['EMAIL_FROM_NAME']);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = '🔐 New Login to Your Teddy Shop Account';
        $mail->Body    = getLoginEmailHTML($toName);
        $mail->AltBody = "Hi $toName! A new login was detected on your Teddy Shop account.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function getLoginEmailHTML(string $name): string {
    $time = date('Y-m-d H:i:s');
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    body{font-family:Poppins,Arial,sans-serif;background:#fff5f7;margin:0;padding:0;}
    .container{max-width:560px;margin:40px auto;background:#fff;border-radius:24px;box-shadow:0 8px 30px rgba(255,107,129,0.12);overflow:hidden;}
    .header{background:linear-gradient(135deg,#ff6b81,#ff9a9e);padding:40px 30px;text-align:center;}
    .header h1{color:#fff;font-size:26px;margin:0 0 6px;}
    .header p{color:rgba(255,255,255,0.9);font-size:14px;margin:0;}
    .body{padding:36px 30px;}
    .body h2{color:#333;font-size:20px;margin:0 0 14px;}
    .body p{color:#666;font-size:14px;line-height:1.7;margin:0 0 16px;}
    .info-box{background:#fff5f7;border-radius:14px;padding:16px 20px;margin:20px 0;border-left:4px solid #ff6b81;}
    .info-box p{margin:4px 0;font-size:13px;color:#555;}
    .footer{background:#fff5f7;padding:20px 30px;text-align:center;font-size:12px;color:#aaa;}
    </style></head><body>
    <div class="container">
    <div class="header"><h1>🔐 New Login Detected</h1><p>Someone just logged into your account</p></div>
    <div class="body">
    <h2>Hi ' . htmlspecialchars($name) . '! 👋</h2>
    <p>We noticed a new login to your Teddy Shop account. If this was you, no action is needed.</p>
    <div class="info-box">
    <p><strong>🕐 Time:</strong> ' . $time . '</p>
    <p><strong>🌐 Site:</strong> Teddy Shop</p>
    </div>
    <p>If you did not login, please change your password immediately.</p>
    </div>
    <div class="footer">© 2025 Teddy Shop · This is an automated security notification.</div>
    </div></body></html>';
}

// إيميل لليوزر لما تتأكد طلبيته
function sendOrderConfirmationEmail(string $toEmail, string $toName, string $orderNumber, float $total): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_ADDRESS'];
        $mail->Password   = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['EMAIL_ADDRESS'], $_ENV['EMAIL_FROM_NAME']);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = '🧸 Order Confirmed #' . $orderNumber;
        $mail->Body    = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        body{font-family:Poppins,Arial,sans-serif;background:#fff5f7;margin:0;padding:0;}
        .container{max-width:560px;margin:40px auto;background:#fff;border-radius:24px;box-shadow:0 8px 30px rgba(255,107,129,0.12);overflow:hidden;}
        .header{background:linear-gradient(135deg,#ff6b81,#ff9a9e);padding:40px 30px;text-align:center;}
        .header h1{color:#fff;font-size:26px;margin:0 0 6px;}
        .header p{color:rgba(255,255,255,0.9);font-size:14px;margin:0;}
        .body{padding:36px 30px;}
        .body h2{color:#333;font-size:20px;margin:0 0 14px;}
        .body p{color:#666;font-size:14px;line-height:1.7;margin:0 0 16px;}
        .info-box{background:#fff5f7;border-radius:14px;padding:16px 20px;margin:20px 0;border-left:4px solid #ff6b81;}
        .info-box p{margin:6px 0;font-size:13px;color:#555;}
        .btn{display:inline-block;background:linear-gradient(135deg,#ff6b81,#ff9a9e);color:#fff!important;padding:14px 36px;border-radius:50px;text-decoration:none;font-weight:600;font-size:15px;}
        .footer{background:#fff5f7;padding:20px 30px;text-align:center;font-size:12px;color:#aaa;}
        </style></head><body>
        <div class="container">
        <div class="header"><h1>🧸 Order Confirmed!</h1><p>Thank you for shopping with us</p></div>
        <div class="body">
        <h2>Hi ' . htmlspecialchars($toName) . '! 🎉</h2>
        <p>Your order has been placed successfully! We\'re getting it ready for you.</p>
        <div class="info-box">
        <p><strong>📦 Order Number:</strong> #' . htmlspecialchars($orderNumber) . '</p>
        <p><strong>💰 Total:</strong> $' . number_format($total, 2) . '</p>
        <p><strong>🕐 Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
        <p><strong>📍 Status:</strong> Pending</p>
        </div>
        <p>We\'ll notify you when your order is on its way!</p>
        <a href="http://localhost/Store/gamesStore/profile.php?tab=orders" class="btn">View My Orders</a>
        </div>
        <div class="footer">© 2025 Teddy Shop · Questions? Email us at ' . $_ENV['EMAIL_ADDRESS'] . '</div>
        </div></body></html>';
        $mail->AltBody = "Hi $toName! Your order #$orderNumber has been confirmed. Total: $$total";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

// إيميل للأدمن لما يجي طلب جديد
function sendNewOrderAdminEmail(string $orderNumber, string $userName, string $userEmail, float $total): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_ADDRESS'];
        $mail->Password   = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['EMAIL_ADDRESS'], $_ENV['EMAIL_FROM_NAME']);
        $mail->addAddress($_ENV['EMAIL_ADDRESS'], 'Teddy Shop Admin');

        $mail->isHTML(true);
        $mail->Subject = '🛒 New Order Received #' . $orderNumber;
        $mail->Body    = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
        body{font-family:Poppins,Arial,sans-serif;background:#f5f5f5;margin:0;padding:0;}
        .container{max-width:560px;margin:40px auto;background:#fff;border-radius:24px;box-shadow:0 8px 30px rgba(0,0,0,0.1);overflow:hidden;}
        .header{background:linear-gradient(135deg,#333,#555);padding:40px 30px;text-align:center;}
        .header h1{color:#fff;font-size:26px;margin:0 0 6px;}
        .header p{color:rgba(255,255,255,0.8);font-size:14px;margin:0;}
        .body{padding:36px 30px;}
        .body h2{color:#333;font-size:20px;margin:0 0 14px;}
        .info-box{background:#f9f9f9;border-radius:14px;padding:16px 20px;margin:20px 0;border-left:4px solid #ff6b81;}
        .info-box p{margin:6px 0;font-size:13px;color:#555;}
        .btn{display:inline-block;background:linear-gradient(135deg,#333,#555);color:#fff!important;padding:14px 36px;border-radius:50px;text-decoration:none;font-weight:600;font-size:15px;}
        .footer{background:#f5f5f5;padding:20px 30px;text-align:center;font-size:12px;color:#aaa;}
        </style></head><body>
        <div class="container">
        <div class="header"><h1>🛒 New Order!</h1><p>A customer just placed an order</p></div>
        <div class="body">
        <h2>New Order Received 📦</h2>
        <div class="info-box">
        <p><strong>📦 Order Number:</strong> #' . htmlspecialchars($orderNumber) . '</p>
        <p><strong>👤 Customer:</strong> ' . htmlspecialchars($userName) . '</p>
        <p><strong>📧 Email:</strong> ' . htmlspecialchars($userEmail) . '</p>
        <p><strong>💰 Total:</strong> $' . number_format($total, 2) . '</p>
        <p><strong>🕐 Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
        </div>
        <a href="http://localhost/Store/gamesStore/admin/orders.php" class="btn">View Orders</a>
        </div>
        <div class="footer">© 2025 Teddy Shop Admin Panel</div>
        </div></body></html>';
        $mail->AltBody = "New order #$orderNumber from $userName ($userEmail). Total: $$total";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

// إيميل رد على رسالة التواصل
function sendReplyEmail(string $toEmail, string $toName, string $originalSubject, string $replyText): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_ADDRESS'];
        $mail->Password   = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['EMAIL_ADDRESS'], $_ENV['EMAIL_FROM_NAME']);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Re: ' . $originalSubject;
        $mail->Body    = getReplyEmailHTML($toName, $replyText);
        $mail->AltBody = "Hi $toName! We replied to your message: " . strip_tags($replyText);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function getReplyEmailHTML(string $name, string $reply): string {
    $escapedReply = nl2br(htmlspecialchars($reply));
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
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
    <h2>Hi ' . htmlspecialchars($name) . ' 👋</h2>
    <p>Thank you for contacting us. Here is our reply to your message:</p>
    <div class="reply-box">
    <p>' . $escapedReply . '</p>
    </div>
    <p style="color:#888;font-size:13px;">If you have any further questions, feel free to contact us again.</p>
    </div>
    <div class="footer">© 2025 Teddy Shop · We love hearing from you!</div>
    </div></body></html>';
}

// إيميل الترحيب لليوزر الجديد
function sendWelcomeEmail(string $toEmail, string $toName): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_ADDRESS'];
        $mail->Password   = $_ENV['EMAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['EMAIL_ADDRESS'], $_ENV['EMAIL_FROM_NAME']);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = '🧸 Welcome to Teddy Shop!';
        $mail->Body    = getWelcomeEmailHTML($toName);
        $mail->AltBody = "Welcome to Teddy Shop, $toName! We're so glad you joined us.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function getWelcomeEmailHTML(string $name): string {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    body{font-family:Poppins,Arial,sans-serif;background:#fff5f7;margin:0;padding:0;}
    .container{max-width:560px;margin:40px auto;background:#fff;border-radius:24px;box-shadow:0 8px 30px rgba(255,107,129,0.12);overflow:hidden;}
    .header{background:linear-gradient(135deg,#ff6b81,#ff9a9e);padding:40px 30px;text-align:center;}
    .header h1{color:#fff;font-size:26px;margin:0 0 6px;}
    .header p{color:rgba(255,255,255,0.9);font-size:14px;margin:0;}
    .body{padding:36px 30px;}
    .body h2{color:#333;font-size:20px;margin:0 0 14px;}
    .body p{color:#666;font-size:14px;line-height:1.7;margin:0 0 16px;}
    .btn{display:inline-block;background:linear-gradient(135deg,#ff6b81,#ff9a9e);color:#fff!important;padding:14px 36px;border-radius:50px;text-decoration:none;font-weight:600;font-size:15px;}
    .feature-box{background:#fff5f7;border-radius:14px;padding:16px 20px;margin:20px 0;}
    .feature-box p{margin:6px 0;font-size:13px;color:#555;}
    .footer{background:#fff5f7;padding:20px 30px;text-align:center;font-size:12px;color:#aaa;}
    </style></head><body>
    <div class="container">
    <div class="header"><h1>🧸 Welcome!</h1><p>Your Teddy Shop journey begins now</p></div>
    <div class="body">
    <h2>Hi ' . htmlspecialchars($name) . '! 👋</h2>
    <p>We\'re thrilled to have you join the Teddy Shop family! Get ready for the cutest teddy bears and amazing shopping experience.</p>
    <div class="feature-box">
    <p>🎨 <strong>Customize</strong> — Design your own unique teddy</p>
    <p>🧸 <strong>Shop</strong> — Browse our adorable collection</p>
    <p>🎁 <strong>Gift</strong> — Send love with beautiful gift wrapping</p>
    </div>
    <a href="http://localhost/Store/gamesStore/shop.php" class="btn">Start Shopping</a>
    </div>
    <div class="footer">© 2025 Teddy Shop · We love having you here!</div>
    </div></body></html>';
}