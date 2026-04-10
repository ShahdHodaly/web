<?php
$pageTitle = "Chat Assistant | Teddy Lap";
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $pageTitle; ?></title>

    <!-- HTML-->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* CSS */

        /* حاوية الشات الرئيسية */
        .chat-wrapper{
            padding:120px 20px 50px;
            max-width:900px;
            margin:auto;
        }

        /* صندوق المحادثة */
        .chat-box{
            background:var(--card-bg);
            border-radius:20px;
            box-shadow:0 10px 30px var(--shadow);
            overflow:hidden;
            display:flex;
            flex-direction:column;
            height:70vh;
            border: 1px solid rgba(0,0,0,0.05);
        }

        /* رأس صندوق الشات */
        .chat-header{
            background:linear-gradient(45deg,#ff9a9e,#fad0c4);
            color:#fff;
            padding:20px;
            text-align:center;
            font-size:22px;
            font-weight:bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* منطقة عرض الرسائل */
        .chat-messages{
            flex:1;
            padding:20px;
            overflow-y:auto;
            display:flex;
            flex-direction:column;
            gap:12px;
            background:var(--bg-color);
        }

        /* ستايل الرسالة العامة */
        .message{
            max-width:70%;
            padding:12px 18px;
            border-radius:18px;
            font-size:14px;
            line-height: 1.5;
            position: relative;
            word-wrap: break-word;
        }

        /* رسالة المستخدم */
        .user-message{
            align-self:flex-end;
            background:var(--pink);
            color:#fff;
            box-shadow: 0 3px 10px rgba(255, 107, 129, 0.3);
        }

        /* رسالة البوت */
        .bot-message{
            align-self:flex-start;
            background:var(--card-bg);
            color:var(--text-color);
            border: 1px solid var(--lavender);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* تعديل لون الحدود في الدارك مود */
        body.dark-mode .bot-message {
            border-color: #444;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        /* منطقة إدخال النص */
        .chat-input{
            display:flex;
            padding:20px;
            border-top:1px solid #eee;
            background:var(--card-bg);
        }

        body.dark-mode .chat-input {
            border-top-color: #333;
        }

        /* حقل الإدخال */
        .chat-input input{
            flex:1;
            padding:12px 18px;
            border-radius:30px;
            border:1px solid var(--lavender);
            outline:none;
            background: var(--bg-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
        }

        .chat-input input::placeholder {
            color: var(--secondary-text);
        }

        /* زر الإرسال */
        .chat-input button{
            width:45px;
            height:45px;
            margin-left:10px;
            border:none;
            border-radius:50%;
            background:var(--pink);
            color:#fff;
            cursor:pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .chat-input button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 107, 129, 0.4);
        }

        /* عنوان الصفحة */
        .page-header{
            text-align:center;
            margin-bottom:25px;
        }

        .page-header h1{
            font-family:'Playfair Display',serif;
            font-size:38px;
            background:linear-gradient(45deg,#ff6b81,#ff9a9e,#fbc2eb);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
        }

    </style>
</head>

<body>

<!-- PHP: شريط التنقل -->
<?php
if(file_exists('navbar.php')){
    include 'navbar.php';
}
?>

<!-- HTML: حاوية الشات -->
<div class="chat-wrapper">

    <div class="page-header">
        <h1>Teddy Assistant</h1>
    </div>

    <div class="chat-box">

        <div class="chat-header">
            <i class="fa-solid fa-robot"></i> Chat with Teddy Assistant
        </div>

        <div class="chat-messages" id="chatMessages">

            <!-- PHP: عرض الرسائل -->
            <?php if(empty($_SESSION['chat_history'])): ?>

                <div class="message bot-message">
                    Hello! 👋 I'm Teddy Assistant 🧸
                    I can help you with products, prices, customization, shipping, and returns. What's on your mind?
                </div>

            <?php else: ?>

                <?php foreach($_SESSION['chat_history'] as $msg): ?>

                    <?php if($msg['type']=="user"): ?>

                        <div class="message user-message">
                            <?= htmlspecialchars($msg['text']) ?>
                        </div>

                    <?php else: ?>

                        <div class="message bot-message">
                            <i class="fa-solid fa-robot" style="margin-right:5px; color:var(--pink);"></i>
                            <?= htmlspecialchars($msg['text']) ?>
                        </div>

                    <?php endif; ?>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

        <!-- HTML: نموذج الإدخال -->
        <form method="POST" class="chat-input">

            <input type="text" name="message" placeholder="Type your message..." required autocomplete="off">

            <button type="submit">
                <i class="fa-solid fa-paper-plane"></i>
            </button>

        </form>

    </div>
</div>

<!-- JavaScript: التمرير التلقائي -->
<script>

    const chatMessages = document.getElementById("chatMessages");

    if(chatMessages){
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

</script>

<?php
if(file_exists('footer.php')){
    include 'footer.php';
}
?>

</body>
</html>