<?php
/* Backend - PHP: منطق الشات والردود */
session_start();

$pageTitle = "Chat Assistant | Teddy Lap";

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// معالجة مسح المحادثة
if (isset($_GET['clear'])) {
    $_SESSION['chat_history'] = [];
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['ok' => true]);
    } else {
        header('Location: chatbot.php');
    }
    exit;
}

// معالجة AJAX — بدون reload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $userMessage = trim($_POST['message'] ?? '');
    $reply = '';
    if (!empty($userMessage)) {
        $reply = getBotReply($userMessage);
        $_SESSION['chat_history'][] = ['type' => 'user', 'text' => $userMessage];
        $_SESSION['chat_history'][] = ['type' => 'bot',  'text' => $reply];
    }
    header('Content-Type: application/json');
    echo json_encode(['reply' => $reply]);
    exit;
}

function getBotReply($message) {

    $clean = strtolower(trim($message));
    $clean = preg_replace("/[^a-z0-9 ]/", " ", $clean);
    $clean = preg_replace('/\s+/', ' ', $clean);

    $responses = [

        // ── عبارات مركبة أولاً (الأكثر تحديداً) ─────────────────────────
            "how_long_shipping" => [
                    "keywords" => [
                            "how long is shipping","how long does shipping take",
                            "how long does delivery take","how many days",
                            "when will my order arrive","when will it arrive",
                            "shipping time","delivery time","how long to deliver",
                            "how long to ship"
                    ],
                    "replies" => [
                            "Shipping usually takes 3-5 business days 📦 You'll receive a tracking link via email.",
                            "Standard delivery takes 3-5 business days. Express options are available at checkout!",
                            "We deliver worldwide! Expect your teddy in 3-5 business days 🌍📦"
                    ]
            ],

            "how_to_customize" => [
                    "keywords" => [
                            "how can i customize","how do i customize","how to customize",
                            "how can i design","how do i design","how to design",
                            "how can i create a teddy","how do i create a teddy",
                            "how can i make a teddy","how do i make a teddy",
                            "how can i build a teddy","how do i build a teddy",
                            "how can i personalize","how do i personalize"
                    ],
                    "replies" => [
                            "It's super easy! 🎨 Go to the <a href='customize.php'>Customize page</a>, pick your bear's color, outfit, shoes, and accessories, then add a custom name!",
                            "Visit our <a href='customize.php'>Customize page</a> to design your perfect teddy! Choose colors, outfits, shoes, and accessories 🧸✨",
                            "Head to <a href='customize.php'>Customize</a> and let your creativity flow! Pick everything from color to accessories 🎀"
                    ]
            ],

            "gift_wrap" => [
                    "keywords" => [
                            "gift wrap","gift wrapping","do you offer gift","gift box",
                            "gift packaging","can i gift wrap","wrap it","wrapping service",
                            "gift option","gift message","add a gift"
                    ],
                    "replies" => [
                            "Yes! 🎁 We offer gift wrapping and a personal message option at checkout. Your teddy will arrive beautifully wrapped!",
                            "Absolutely! Add a gift message and choose gift wrapping during checkout 🎀 Perfect for any occasion!",
                            "We love helping you surprise someone special! Gift wrap + personal card available at checkout 🧸🎁"
                    ]
            ],

            "how_to_return" => [
                    "keywords" => [
                            "how do i return","how can i return","how to return",
                            "how do i get a refund","how can i get a refund",
                            "return policy","refund policy","return process"
                    ],
                    "replies" => [
                            "Returns are accepted within 14 days if the item is unused 📦 Visit our <a href='contact.php'>Contact page</a> to start the process.",
                            "To return an item, contact us via the <a href='contact.php'>Contact page</a> within 14 days of receiving your order.",
                            "No worries! Reach out through our <a href='contact.php'>Contact page</a> and we'll guide you 😊"
                    ]
            ],

            "how_to_contact" => [
                    "keywords" => [
                            "how can i contact","how do i contact","how to contact",
                            "how can i reach","how do i reach support","how to reach support",
                            "how can i get help","how do i get help","contact support"
                    ],
                    "replies" => [
                            "You can reach us through the <a href='contact.php'>Contact page</a> 💬 We reply within 24 hours!",
                            "Visit our <a href='contact.php'>Contact page</a> or email us at support@teddylap.com 😊",
                            "Head to the <a href='contact.php'>Contact page</a> — we'll get back to you ASAP 🧸"
                    ]
            ],

        // ── تحيات ────────────────────────────────────────────────────────
            "greeting" => [
                    "keywords" => [
                            "hi","hello","hey","good morning","good evening","good afternoon",
                            "howdy","hiya","greetings","yo","helo","hii","hiii"
                    ],
                    "replies" => [
                            "Hello! 👋 I'm Teddy Assistant. How can I help you today?",
                            "Hey there! 🧸 Welcome to Teddy Lap! What can I do for you?",
                            "Hi! Ready to find the perfect teddy bear? Ask me anything! 😊",
                            "Hey! Great to see you here. What are you looking for today? 🧸"
                    ]
            ],

            "positive" => [
                    "keywords" => [
                            "ok","okay","sure","fine","got it","understood","alright","great",
                            "cool","nice","perfect","awesome","wow","noted","yep","yup",
                            "yes","yeah","ya","yas","sounds good","makes sense","i see",
                            "no problem","thank you","thanks","thx","ty","appreciate",
                            "helpful","love it","amazing","excellent","superb","wonderful"
                    ],
                    "replies" => [
                            "Glad I could help! 😊 Is there anything else you'd like to know?",
                            "Great! Let me know if you have any other questions 🧸",
                            "Awesome! Feel free to ask me anything else about our teddies 🎀",
                            "Happy to help! What else can I do for you? ✨",
                            "Of course! Don't hesitate to ask if you need anything else 😊"
                    ]
            ],

            "negative" => [
                    "keywords" => [
                            "nope","nah","not really","never mind","nevermind",
                            "forget it","not now","maybe later","not interested",
                            "no thanks","no thank you"
                    ],
                    "replies" => [
                            "No worries! I'm here whenever you need me 🧸",
                            "That's okay! Feel free to come back anytime 😊",
                            "Sure thing! Just let me know if you change your mind 🎀"
                    ]
            ],

            "how_are_you" => [
                    "keywords" => [
                            "how are you","how r u","how do you do","how are u",
                            "are you okay","how's it going","hows it going",
                            "what's new","whats new","how have you been"
                    ],
                    "replies" => [
                            "I'm doing great, thanks for asking! 🧸 How can I help you today?",
                            "Feeling fluffy and fantastic! 😄 What can I do for you?",
                            "Always happy when there's a customer to help! What do you need? 🎀"
                    ]
            ],

            "products" => [
                    "keywords" => [
                            "product","teddy","bear","shop","items","buy","catalog","collection",
                            "browse","what do you have","what do you sell","what can i buy",
                            "available","toys","dolls","plush","stuffed","barbie","puzzle",
                            "building blocks","latest","featured"
                    ],
                    "replies" => [
                            "We have a wonderful collection of teddies! 🧸 Check out the <a href='shop.php'>Shop page</a>.",
                            "Looking for a new friend? Explore our full catalog in the <a href='shop.php'>Shop section</a>!",
                            "Our collection includes teddy bears, dolls, cars, puzzles and more! Visit the <a href='shop.php'>Shop</a> 🎀"
                    ]
            ],

            "price" => [
                    "keywords" => [
                            "price","cost","how much","expensive","cheap","pricing",
                            "afford","budget","value","worth","dollar","fee",
                            "what is the price","whats the price","price range"
                    ],
                    "replies" => [
                            "Our teddy prices start from \$20! 💰 Customization adds a bit extra depending on the outfit.",
                            "Prices range from \$20 to \$50 based on size and accessories. Check <a href='shop.php'>product pages</a> for exact prices!",
                            "Great value for amazing quality! Products start at \$20. Visit the <a href='shop.php'>Shop</a> 🧸"
                    ]
            ],

            "discount" => [
                    "keywords" => [
                            "discount","sale","offer","promotion","coupon code","promo code",
                            "deal","savings","voucher","percent off","special offer"
                    ],
                    "replies" => [
                            "We have special discounts on selected teddies! 🎉 Check the <a href='shop.php'>Shop</a> for sale badges.",
                            "Use coupon codes at checkout for extra savings! Check our <a href='home.php'>homepage</a> for promotions.",
                            "Look out for our seasonal sales! Apply coupon codes during checkout 🎀"
                    ]
            ],

            "shipping" => [
                    "keywords" => [
                            "shipping","delivery","track my order","package","receive",
                            "dispatch","order status","where is my order","transit",
                            "courier","express delivery","tracking link"
                    ],
                    "replies" => [
                            "Shipping usually takes 3-5 business days 📦 You'll receive a tracking link via email.",
                            "We deliver worldwide! Standard delivery: 3-5 days. Express options available at checkout.",
                            "Need to track your order? Check <a href='profile.php'>My Orders</a> in your profile 📦"
                    ]
            ],

            "custom" => [
                    "keywords" => [
                            "custom","customize","personalize","customization",
                            "own teddy","my teddy","unique teddy","special teddy",
                            "design a teddy","create a teddy","build a teddy","make a teddy"
                    ],
                    "replies" => [
                            "You can design your own unique teddy in the <a href='customize.php'>Customize page</a>! 🎨",
                            "Want a personalized bear? Visit our <a href='customize.php'>Customize section</a>! ✨",
                            "Let your creativity flow! Go to <a href='customize.php'>Customize</a> and make your dream teddy 🧸"
                    ]
            ],

            "gift" => [
                    "keywords" => [
                            "gift","present","birthday","anniversary","surprise",
                            "someone special","for a friend","celebration",
                            "valentine","christmas","eid","holiday","gift for"
                    ],
                    "replies" => [
                            "Our teddies make the perfect gift! 🎁 We offer gift wrapping and a personal message at checkout.",
                            "Yes! Add a gift message and we'll wrap your teddy beautifully 🎀 Choose gift options at checkout.",
                            "Surprise someone special with a custom teddy! Gift boxes + messages available at checkout 🧸🎁"
                    ]
            ],

            "payment" => [
                    "keywords" => [
                            "payment","pay","card","visa","mastercard","paypal",
                            "credit card","debit card","cash","secure payment",
                            "payment method","how to pay","payment options","accepted cards"
                    ],
                    "replies" => [
                            "We accept Credit Card, Debit Card, and PayPal 💳 All payments are secure and encrypted.",
                            "Paying is safe and easy! We support Visa, Mastercard, and PayPal at checkout.",
                            "Your payment is 100% secure 🔒 We accept card and PayPal payments."
                    ]
            ],

            "returns" => [
                    "keywords" => [
                            "return","refund","exchange","money back","broken","damaged",
                            "wrong item","defective","cancel order","cancellation","replacement"
                    ],
                    "replies" => [
                            "We accept returns within 14 days if the teddy is unused 📦 Contact support for help!",
                            "Issue with your order? Visit the <a href='contact.php'>Contact page</a> and we'll sort it out.",
                            "We're sorry to hear that! Please reach out via the <a href='contact.php'>Contact page</a> 🧸"
                    ]
            ],

            "account" => [
                    "keywords" => [
                            "account","profile","login","sign in","register","sign up",
                            "my account","forgot password","reset password",
                            "create account","new account","membership"
                    ],
                    "replies" => [
                            "You can manage your account from the <a href='profile.php'>Profile page</a> 👤",
                            "To sign in or create an account, visit the <a href='auth.php'>Login page</a>!",
                            "Forgot your password? Use the <a href='change_password.php'>Reset Password</a> option 🔑"
                    ]
            ],

            "orders" => [
                    "keywords" => [
                            "my orders","order history","past orders","placed order",
                            "order number","invoice","order confirmation","purchased","bought"
                    ],
                    "replies" => [
                            "View all your orders in <a href='profile.php'>Profile → My Orders</a> 📦",
                            "Your order history is in your <a href='profile.php'>Profile</a>. Check there for updates!",
                            "Need order details? Go to <a href='profile.php'>Profile → My Orders</a> 🧾"
                    ]
            ],

            "materials" => [
                    "keywords" => [
                            "material","made of","plush","hypoallergenic","allergy",
                            "kids safe","baby safe","stuffing","filling","fabric","cotton"
                    ],
                    "replies" => [
                            "Our teddies are made of premium hypoallergenic plush — super soft and safe for all ages! 🧸",
                            "We use high-quality soft cotton and plush. Completely safe for babies and kids!",
                            "All materials are child-safe and hypoallergenic 💝 Perfect for all ages!"
                    ]
            ],

            "clean" => [
                    "keywords" => [
                            "wash","machine wash","washing instructions","stain",
                            "how to clean","care instructions","keep clean","dirty teddy"
                    ],
                    "replies" => [
                            "Hand wash with mild soap and air dry 🧼 Avoid machine washing to keep your teddy fluffy!",
                            "We recommend gentle hand washing with cold water. Air dry only — no tumble drying!",
                            "For best results: hand wash with mild soap, then air dry 🌟"
                    ]
            ],

            "support" => [
                    "keywords" => [
                            "support","customer service","talk to agent","speak to agent",
                            "get in touch","complaint","feedback","email support"
                    ],
                    "replies" => [
                            "Reach our support team from the <a href='contact.php'>Contact page</a> 💬 We reply within 24 hours!",
                            "Need assistance? Visit the <a href='contact.php'>Contact page</a> or email support@teddylap.com.",
                            "We're here to help! Head to the <a href='contact.php'>Contact page</a> and we'll get back to you ASAP 😊"
                    ]
            ],

            "bye" => [
                    "keywords" => [
                            "bye","goodbye","see you","take care","good night","goodnight",
                            "gotta go","gtg","cya","farewell","im done","i'm done",
                            "that's all","no more questions","all good now"
                    ],
                    "replies" => [
                            "Goodbye! Have a lovely day 🧸 Come back anytime!",
                            "See you later! Hope to see you again soon 🎀",
                            "Bye! Take care and happy shopping! 😊",
                            "Until next time! 🧸✨"
                    ]
            ],

    ];

    // ── مطابقة العبارات الكاملة أولاً (multi-word) ───────────────────────
    foreach ($responses as $intent) {
        foreach ($intent["keywords"] as $word) {
            if (strpos($word, ' ') !== false && strpos($clean, $word) !== false) {
                $replies = $intent["replies"];
                return $replies[array_rand($replies)];
            }
        }
    }

    // ── ثم مطابقة كلمات مفردة بحدود كلمة كاملة (word boundary) ──────────
    foreach ($responses as $intent) {
        foreach ($intent["keywords"] as $word) {
            if (strpos($word, ' ') === false) {
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $clean)) {
                    $replies = $intent["replies"];
                    return $replies[array_rand($replies)];
                }
            }
        }
    }

    $fallbacks = [
            "Hmm, I'm not sure about that 🤔 Try asking about products, prices, shipping, or customization!",
            "I didn't quite catch that! Ask me about teddies, delivery, payments, or returns 🧸",
            "Not sure I understood! Try: 'How much do teddies cost?' or 'How long is shipping?' 😊",
            "I'm still learning! For complex questions, visit our <a href='contact.php'>Contact page</a> 💬"
    ];

    return $fallbacks[array_rand($fallbacks)];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .chat-wrapper {
            padding: 120px 20px 50px;
            max-width: 900px;
            margin: auto;
        }
        .chat-box {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 70vh;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .chat-header {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff;
            padding: 20px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: var(--bg-color);
        }
        .message {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
        }
        .user-message {
            align-self: flex-end;
            background: var(--pink);
            color: #fff;
            box-shadow: 0 3px 10px rgba(255, 107, 129, 0.3);
        }
        .bot-message {
            align-self: flex-start;
            background: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--lavender);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .bot-message a { color: var(--pink); text-decoration: underline; }
        body.dark-mode .bot-message { border-color: #444; }
        .chat-input {
            display: flex;
            padding: 20px;
            border-top: 1px solid #eee;
            background: var(--card-bg);
            gap: 10px;
        }
        body.dark-mode .chat-input { border-top-color: #333; }
        .chat-input input {
            flex: 1;
            padding: 12px 18px;
            border-radius: 30px;
            border: 1px solid var(--lavender);
            outline: none;
            background: var(--bg-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
        }
        .chat-input input::placeholder { color: var(--secondary-text); }
        .chat-input button {
            width: 45px; height: 45px;
            border: none; border-radius: 50%;
            background: var(--pink); color: #fff;
            cursor: pointer; flex-shrink: 0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .chat-input button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 107, 129, 0.4);
        }
        .clear-btn {
            display: block; margin: 12px auto 0;
            background: none; border: 1px solid #ddd;
            color: var(--secondary-text); font-size: 12px;
            padding: 5px 14px; border-radius: 20px;
            cursor: pointer; transition: all 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .clear-btn:hover { border-color: var(--pink); color: var(--pink); }
        .page-header { text-align: center; margin-bottom: 25px; }
        .page-header h1 {
            font-family: 'Playfair Display', serif; font-size: 38px;
            background: linear-gradient(45deg, #ff6b81, #ff9a9e, #fbc2eb);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .suggestions {
            display: flex; flex-wrap: wrap; gap: 8px;
            padding: 10px 20px; background: var(--card-bg);
            border-top: 1px solid #eee;
        }
        body.dark-mode .suggestions { border-top-color: #333; }
        .suggestion-btn {
            background: none; border: 1px solid var(--lavender);
            color: var(--text-color); font-size: 12px;
            padding: 5px 12px; border-radius: 20px;
            cursor: pointer; transition: all 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .suggestion-btn:hover { background: var(--pink); border-color: var(--pink); color: #fff; }
    </style>
</head>
<body>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="chat-wrapper">
    <div class="page-header">
        <h1>Teddy Assistant</h1>
        <p style="color:var(--secondary-text); font-size:14px;">Ask me anything about our teddies! 🧸</p>
    </div>

    <div class="chat-box">
        <div class="chat-header">
            <i class="fa-solid fa-robot"></i> Chat with Teddy Assistant
        </div>

        <div class="chat-messages" id="chatMessages">
            <?php if (empty($_SESSION['chat_history'])): ?>
                <div class="message bot-message">
                    Hello! 👋 I'm Teddy Assistant 🧸<br>
                    I can help you with <strong>products, prices, customization, shipping,</strong> and more. What's on your mind?
                </div>
            <?php else: ?>
                <?php foreach ($_SESSION['chat_history'] as $msg): ?>
                    <?php if ($msg['type'] === 'user'): ?>
                        <div class="message user-message">
                            <?= htmlspecialchars($msg['text']) ?>
                        </div>
                    <?php else: ?>
                        <div class="message bot-message">
                            <i class="fa-solid fa-robot" style="margin-right:5px; color:var(--pink);"></i>
                            <?= $msg['text'] ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="suggestions">
            <button class="suggestion-btn" onclick="fillInput('How much do teddies cost?')">💰 Prices</button>
            <button class="suggestion-btn" onclick="fillInput('How long is shipping?')">📦 Shipping</button>
            <button class="suggestion-btn" onclick="fillInput('How can I customize a teddy?')">🎨 Customize</button>
            <button class="suggestion-btn" onclick="fillInput('Do you offer gift wrapping?')">🎁 Gifts</button>
            <button class="suggestion-btn" onclick="fillInput('How do I return an item?')">↩️ Returns</button>
            <button class="suggestion-btn" onclick="fillInput('How can I contact support?')">💬 Support</button>
        </div>

        <div class="chat-input">
            <input type="text" id="messageInput"
                   placeholder="Type your message..." autocomplete="off">
            <button onclick="sendMessage()">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <button class="clear-btn" onclick="clearChat()">
        <i class="fa-solid fa-trash-can"></i> Clear conversation
    </button>
</div>

<script>
    const chatMessages = document.getElementById("chatMessages");

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    scrollToBottom();

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const text = input.value.trim();
        if (!text) return;

        appendMessage('user', text);
        input.value = '';
        scrollToBottom();

        fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(text) + '&ajax=1'
        })
            .then(res => res.json())
            .then(data => {
                if (data.reply) {
                    appendMessage('bot', data.reply);
                    scrollToBottom();
                }
            })
            .catch(() => {
                appendMessage('bot', "Oops! Something went wrong. Please try again 🧸");
                scrollToBottom();
            });
    }

    function appendMessage(type, text) {
        const div = document.createElement('div');
        div.className = 'message ' + (type === 'user' ? 'user-message' : 'bot-message');
        if (type === 'bot') {
            div.innerHTML = '<i class="fa-solid fa-robot" style="margin-right:5px; color:var(--pink);"></i>' + text;
        } else {
            div.textContent = text;
        }
        chatMessages.appendChild(div);
    }

    function fillInput(text) {
        document.getElementById('messageInput').value = text;
        document.getElementById('messageInput').focus();
    }

    function clearChat() {
        fetch('chatbot.php?clear=1', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(() => {
            chatMessages.innerHTML =
                '<div class="message bot-message">' +
                '<i class="fa-solid fa-robot" style="margin-right:5px; color:var(--pink);"></i>' +
                "Hello! 👋 I\'m Teddy Assistant 🧸 What\'s on your mind?" +
                '</div>';
        });
    }

    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>

</body>
</html>