<?php
session_start();
require_once 'db.php';

$pageTitle = "Home | Teddy Lap";

// ── جيبي المنتجات من DB ───────────────────────────────────────
$pdo  = getDB();
$stmt = $pdo->query("
    SELECT p.product_id AS id, p.name, p.price, p.image,
           p.sales_count, c.name AS category,
           ROUND(COALESCE(AVG(r.rating), 0), 1) AS avg_rating
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN reviews r ON r.product_id = p.product_id AND r.status = 'approved'
    WHERE p.stock > 0
    GROUP BY p.product_id, c.name
    ORDER BY p.sales_count DESC, p.product_id ASC
");
$products = [];
foreach ($stmt->fetchAll() as $row) {
    $products[$row['id']] = $row;
}

// ── معالجة AJAX: إضافة للكارت ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'add_to_cart') {
        if (empty($_SESSION['logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'Please login first']);
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $userId    = $_SESSION['user_id'];

        // جيبي أو انشئي cart لليوزر
        $stmt = $pdo->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();

        if (!$cart) {
            $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)")->execute([$userId]);
            $cartId = $pdo->lastInsertId();
        } else {
            $cartId = $cart['cart_id'];
        }

        // أضيفي المنتج أو زيدي الكمية
        $pdo->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity)
            VALUES (?, ?, 1)
            ON CONFLICT (cart_id, product_id)
            DO UPDATE SET quantity = cart_items.quantity + 1
        ")->execute([$cartId, $productId]);

        // جيبي العدد الجديد
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(quantity), 0)
            FROM cart_items WHERE cart_id = ?
        ");
        $stmt->execute([$cartId]);
        $count = (int)$stmt->fetchColumn();

        echo json_encode(['success' => true, 'cart_count' => $count]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;600&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body { overflow-x: hidden; }
        .hero-section { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 50px 20px; position: relative; perspective: 1000px; overflow: hidden; }
        .hero-shapes { position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: -1; overflow: hidden; }
        .shape-blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.5; transition: transform 0.1s linear; }
        .blob-1 { width: 600px; height: 600px; background: var(--pink); top: -150px; left: -150px; }
        .blob-2 { width: 500px; height: 500px; background: var(--lavender); bottom: -100px; right: -100px; }
        .blob-3 { width: 400px; height: 400px; background: var(--primary); top: 40%; left: 60%; }
        .hero-content { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; max-width: 1200px; margin: 0 auto; align-items: center; z-index: 2; }
        .hero-text { text-align: left; }
        .hero-text h4 { color: var(--pink); font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 10px; font-size: 14px; opacity: 0; transform: translateY(20px); animation: fadeUp 0.8s ease forwards 0.2s; }
        .hero-text h1 { font-family: 'Playfair Display', serif; font-size: 64px; line-height: 1.1; color: var(--text-color); margin-bottom: 20px; opacity: 0; transform: translateY(20px); animation: fadeUp 0.8s ease forwards 0.4s; }
        .hero-text h1 span { font-family: 'Dancing Script', cursive; color: var(--pink); display: inline-block; animation: wiggle 2s infinite ease-in-out 1s; }
        @keyframes wiggle { 0%, 100% { transform: rotate(-3deg); } 50% { transform: rotate(3deg) scale(1.1); } }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .hero-text p { color: var(--secondary-text); font-size: 18px; margin-bottom: 30px; max-width: 500px; opacity: 0; animation: fadeUp 0.8s ease forwards 0.6s; }
        .hero-btns { display: flex; gap: 15px; opacity: 0; animation: fadeUp 0.8s ease forwards 0.8s; }
        .btn-explore { background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 10px 30px rgba(255, 154, 158, 0.4); display: inline-flex; align-items: center; gap: 10px; position: relative; overflow: hidden; }
        .btn-explore::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); transition: .5s; }
        .btn-explore:hover::before { left: 100%; }
        .btn-explore:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 15px 40px rgba(255, 154, 158, 0.6); }
        .btn-customize { background: transparent; color: var(--pink); border: 2px solid var(--pink); padding: 13px 38px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s; animation: pulseBorder 2s infinite; }
        @keyframes pulseBorder { 0%, 100% { box-shadow: 0 0 0 0 rgba(255, 107, 129, 0.4); } 50% { box-shadow: 0 0 0 12px rgba(255, 107, 129, 0); } }
        .btn-customize:hover { background: rgba(255, 107, 129, 0.1); transform: scale(1.05); }
        .hero-visual { position: relative; display: flex; justify-content: center; align-items: center; }
        .teddy-3d-container { position: relative; width: 450px; height: 500px; transform-style: preserve-3d; transition: transform 0.2s ease-out; animation: floatContainer 6s ease-in-out infinite; }
        @keyframes floatContainer { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        .teddy-layer { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; }
        .teddy-main-img { width: 90%; height: auto; filter: drop-shadow(0 30px 50px rgba(0,0,0,0.15)); animation: floatTeddy 4s ease-in-out infinite alternate; transform-style: preserve-3d; }
        .floating-element { position: absolute; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1)); pointer-events: none; animation: floatRandom 8s ease-in-out infinite; }
        .elem-1 { top: 10%; right: 10%; width: 60px; animation-delay: 0s; transform: translateZ(50px); }
        .elem-2 { bottom: 15%; left: 5%; width: 80px; animation-delay: 2s; transform: translateZ(80px); }
        @keyframes floatTeddy { 0% { transform: translateY(0) rotate(0deg); } 100% { transform: translateY(-15px) rotate(2deg); } }
        @keyframes floatRandom { 0%, 100% { transform: translate(0, 0) rotate(0deg); } 25% { transform: translate(15px, -25px) rotate(10deg); } 75% { transform: translate(-15px, 15px) rotate(-10deg); } }
        .products-showcase { padding: 50px 20px 100px; background: transparent; position: relative; }
        .section-title-center { text-align: center; margin-bottom: 60px; }
        .section-title-center h2 { font-family: 'Playfair Display', serif; font-size: 36px; color: var(--text-color); margin-bottom: 10px; }
        .section-title-center p { color: var(--secondary-text); }
        .slider-wrapper { position: relative; max-width: 1200px; margin: 0 auto; padding: 0 50px; }
        .products-slider { display: flex; gap: 25px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; padding: 20px 0; -ms-overflow-style: none; scrollbar-width: none; }
        .products-slider::-webkit-scrollbar { display: none; }
        .product-card-home { background: var(--card-bg); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px var(--shadow); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; flex: 0 0 280px; scroll-snap-align: start; cursor: pointer; }
        .product-card-home:hover { transform: translateY(-15px) rotate(2deg) scale(1.02); box-shadow: 0 25px 50px rgba(255, 107, 129, 0.2); }
        .product-img-box-home { height: 250px; background: #fff; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .product-img-box-home img { width: 80%; height: 80%; object-fit: contain; transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .product-card-home:hover .product-img-box-home img { transform: scale(1.15) rotate(-5deg); }
        .product-info-home { padding: 20px; text-align: center; }
        .product-info-home h3 { font-size: 18px; color: var(--text-color); margin-bottom: 5px; }
        .product-rating { color: #FFD700; margin-bottom: 8px; font-size: 14px; }
        .product-rating .empty { color: #e0e0e0; }
        .product-info-home .price { color: var(--pink); font-weight: 700; font-size: 18px; margin-bottom: 15px; }
        .add-btn-home { background: var(--lavender); color: #fff; border: none; padding: 8px 20px; border-radius: 20px; cursor: pointer; transition: all 0.3s; font-weight: 600; position: relative; z-index: 10; }
        .add-btn-home:hover { background: var(--pink); transform: scale(1.1); box-shadow: 0 5px 15px rgba(255,107,129,0.3); }
        .slider-btn { position: absolute; top: 50%; transform: translateY(-50%); background: #fff; border: 1px solid #eee; width: 45px; height: 45px; border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.1); cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; color: var(--text-color); transition: all 0.3s; }
        .slider-btn:hover { background: var(--pink); color: #fff; border-color: var(--pink); transform: translateY(-50%) scale(1.1); }
        .slider-btn.prev { left: 0; }
        .slider-btn.next { right: 0; }
        .features-interactive-section { padding: 100px 20px; background: transparent; position: relative; overflow: hidden; }
        .features-container { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 50px; position: relative; }
        .features-content { flex: 1; text-align: left; z-index: 2; }
        .features-content h2 { font-family: 'Playfair Display', serif; font-size: 48px; color: var(--text-color); margin-bottom: 20px; }
        .feature-slide-text { display: none; animation: fadeInUp 0.5s ease forwards; }
        .feature-slide-text.active { display: block; }
        .feature-slide-text h3 { font-size: 28px; color: var(--pink); margin-bottom: 15px; font-weight: 700; }
        .feature-slide-text p { font-size: 18px; color: var(--secondary-text); line-height: 1.6; margin-bottom: 30px; }
        .features-visual { flex: 1; position: relative; height: 450px; width: 100%; display: flex; justify-content: center; align-items: center; }
        .feature-img-card { position: absolute; width: 350px; height: 400px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.15); transition: all 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55); opacity: 0; transform: translateY(100%) scale(0.8) rotate(-15deg); z-index: 1; }
        .feature-img-card.active { opacity: 1; transform: translateY(0) scale(1) rotate(0); z-index: 2; }
        .feature-img-card.out { animation: slideOutDown 0.6s forwards; }
        @keyframes slideOutDown { to { transform: translateY(100%) scale(0.8) rotate(15deg); opacity: 0; } }
        .feature-img-card img { width: 100%; height: 100%; object-fit: cover; }
        .features-nav { display: flex; align-items: center; gap: 20px; margin-top: 20px; }
        .nav-dot { width: 12px; height: 12px; border-radius: 50%; background: #ddd; cursor: pointer; transition: all 0.3s; }
        .nav-dot.active { background: var(--pink); transform: scale(1.3); }
        .arrow-btn { background: #fff; border: 1px solid #eee; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-color); transition: all 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .arrow-btn:hover { background: var(--pink); color: #fff; border-color: var(--pink); transform: scale(1.1); }
        .reviews-section { padding: 100px 20px; background: transparent; position: relative; overflow: hidden; }
        .reviews-shapes { position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 0; pointer-events: none; }
        .review-shape { position: absolute; opacity: 0.1; animation: floatRandom 10s infinite linear; }
        .rs-1 { top: 10%; left: 5%; font-size: 40px; color: var(--pink); animation-delay: 0s; }
        .rs-2 { top: 50%; right: 10%; font-size: 60px; color: var(--lavender); animation-delay: 2s; }
        .rs-3 { bottom: 10%; left: 20%; font-size: 30px; color: var(--primary); animation-delay: 4s; }
        .reviews-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1100px; margin: 0 auto; position: relative; z-index: 1; }
        .review-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s; position: relative; border: 1px solid rgba(255,255,255,0.5); }
        .review-card:hover { transform: translateY(-15px) rotate(2deg); box-shadow: 0 20px 40px rgba(255, 107, 129, 0.2); }
        .review-header { display: flex; align-items: center; margin-bottom: 20px; gap: 15px; }
        .reviewer-img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--pink); transition: transform 0.3s; }
        .review-card:hover .reviewer-img { transform: scale(1.1) rotate(-5deg); }
        .reviewer-info h4 { font-size: 16px; color: var(--text-color); margin-bottom: 2px; }
        .review-stars { color: #FFD700; font-size: 14px; }
        .review-text { font-size: 15px; color: var(--secondary-text); line-height: 1.6; font-style: italic; }
        .quote-icon { position: absolute; top: 20px; right: 20px; font-size: 30px; color: rgba(255, 107, 129, 0.15); transition: transform 0.3s; }
        .review-card:hover .quote-icon { transform: scale(1.2) rotate(10deg); color: rgba(255, 107, 129, 0.3); }
        .features-section { padding: 100px 20px; background: transparent; position: relative; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; max-width: 1100px; margin: 0 auto; }
        .feature-card-3d { background: var(--card-bg); padding: 40px 20px; border-radius: 25px; text-align: center; box-shadow: 0 15px 30px var(--shadow); transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform-style: preserve-3d; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
        .feature-card-3d:hover { transform: translateY(-10px) rotateX(5deg) rotateY(5deg); box-shadow: 0 30px 60px var(--shadow); }
        .feature-icon-3d { width: 80px; height: 80px; background: linear-gradient(135deg, var(--pink), var(--lavender)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #fff; font-size: 30px; box-shadow: 0 10px 20px rgba(248, 187, 208, 0.3); transition: transform 0.5s; animation: pulse 3s infinite; }
        @keyframes pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(248, 187, 208, 0.4); } 50% { box-shadow: 0 0 0 15px rgba(248, 187, 208, 0); } }
        .feature-card-3d:hover .feature-icon-3d { transform: translateZ(30px) scale(1.1); }
        .feature-card-3d h3 { font-size: 20px; color: var(--text-color); margin-bottom: 10px; transition: transform 0.5s; }
        .feature-card-3d p { color: var(--secondary-text); font-size: 14px; line-height: 1.6; transition: transform 0.5s; }
        .feature-card-3d:hover h3, .feature-card-3d:hover p { transform: translateZ(20px); }
        @media (max-width: 900px) {
            .hero-content { grid-template-columns: 1fr; text-align: center; }
            .hero-text { text-align: center; order: 2; }
            .hero-visual { order: 1; }
            .teddy-3d-container { width: 300px; height: 350px; }
            .hero-text h1 { font-size: 48px; }
            .hero-btns { justify-content: center; }
            .slider-wrapper { padding: 0 10px; }
            .slider-btn { display: none; }
            .features-container { flex-direction: column-reverse; }
            .features-content { text-align: center; }
            .features-visual { height: 300px; }
            .feature-img-card { width: 250px; height: 300px; }
            .features-nav { justify-content: center; }
        }
        .reveal { opacity: 0; transform: translateY(60px) scale(0.9); transition: all 1s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .reveal.active { opacity: 1; transform: translateY(0) scale(1); }
    </style>
</head>
<body>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<!-- 1. Hero -->
<section class="hero-section">
    <div class="hero-shapes">
        <div class="shape-blob blob-1"></div>
        <div class="shape-blob blob-2"></div>
        <div class="shape-blob blob-3"></div>
    </div>
    <div class="hero-content">
        <div class="hero-text">
            <h4>Welcome to Teddy Lap</h4>
            <h1>Design Your Own <span>Teddy</span></h1>
            <p>Create the teddy bear of your dreams. Choose colors, outfits, accessories, and even record a voice message to make it truly yours.</p>
            <div class="hero-btns">
                <a href="customize.php" class="btn-explore">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Start Creating
                </a>
                <a href="shop.php" class="btn-customize">Shop Collection</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="teddy-3d-container" id="teddy3D">
                <div class="teddy-layer">
                    <img src="images/home1.png" alt="Teddy Bear" class="teddy-main-img"
                         onerror="this.src='https://ui-avatars.com/api/?name=Teddy&background=ff9a9e&color=fff&size=400'">
                </div>
                <img src="images/redacc.png" class="floating-element elem-1" alt="Accessory" onerror="this.style.display='none'">
                <img src="images/ball.png"   class="floating-element elem-2" alt="Accessory" onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>

<!-- 2. Best Sellers -->
<section class="products-showcase">
    <div class="section-title-center reveal">
        <h2>Our Best Sellers</h2>
        <p>Top rated picks by our customers. Swipe to explore more.</p>
    </div>
    <div class="slider-wrapper reveal">
        <button class="slider-btn prev" id="prevBtn"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="products-slider" id="productSlider"></div>
        <button class="slider-btn next" id="nextBtn"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
</section>

<!-- 3. Experience -->
<section class="features-interactive-section">
    <div class="features-container reveal">
        <div class="features-content">
            <h2>The Teddy Lap Experience</h2>
            <div class="feature-slide-text active" data-index="0">
                <h3>Full Customization</h3>
                <p>Create your teddy exactly the way you want. Choose colors, outfits, shoes and accessories.</p>
            </div>
            <div class="feature-slide-text" data-index="1">
                <h3>Unique Designs</h3>
                <p>Every teddy can be personalized with names, sounds and special details.</p>
            </div>
            <div class="feature-slide-text" data-index="2">
                <h3>Perfect Gift</h3>
                <p>A customized teddy makes the perfect gift for someone special.</p>
            </div>
            <a href="customize.php" class="btn-explore" style="margin-top:10px; display:inline-flex;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Start Customizing
            </a>
            <div class="features-nav" style="margin-top:30px;">
                <button class="arrow-btn" id="featPrev"><i class="fa-solid fa-arrow-left"></i></button>
                <div class="nav-dot active" data-index="0"></div>
                <div class="nav-dot" data-index="1"></div>
                <div class="nav-dot" data-index="2"></div>
                <button class="arrow-btn" id="featNext"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
        <div class="features-visual">
            <div class="feature-img-card active" data-index="0"><img src="images/h1.png" alt="Customization"></div>
            <div class="feature-img-card" data-index="1"><img src="images/h2.png" alt="Unique Designs"></div>
            <div class="feature-img-card" data-index="2"><img src="images/h3.png" alt="Perfect Gift"></div>
        </div>
    </div>
</section>

<!-- 4. Reviews -->
<section class="reviews-section">
    <div class="reviews-shapes">
        <i class="fa-solid fa-star review-shape rs-1"></i>
        <i class="fa-solid fa-heart review-shape rs-2"></i>
        <i class="fa-solid fa-circle review-shape rs-3"></i>
    </div>
    <div class="section-title-center reveal">
        <h2>What People Say</h2>
        <p>Real stories from our happy customers.</p>
    </div>
    <div class="reviews-grid">
        <div class="review-card reveal" style="transition-delay:0.1s;">
            <i class="fa-solid fa-quote-right quote-icon"></i>
            <div class="review-header">
                <img src="https://ui-avatars.com/api/?name=Sara+K&background=ff9a9e&color=fff&rounded=true" alt="Sara" class="reviewer-img">
                <div class="reviewer-info"><h4>Sara K.</h4><div class="review-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div></div>
            </div>
            <p class="review-text">"I designed a bear for my daughter's birthday and she absolutely loved it! The quality of the fabric is amazing, and the voice recording feature is such a special touch. Highly recommend!"</p>
        </div>
        <div class="review-card reveal" style="transition-delay:0.3s;">
            <i class="fa-solid fa-quote-right quote-icon"></i>
            <div class="review-header">
                <img src="https://ui-avatars.com/api/?name=Mike+R&background=a29bfe&color=fff&rounded=true" alt="Mike" class="reviewer-img">
                <div class="reviewer-info"><h4>Mike R.</h4><div class="review-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div></div>
            </div>
            <p class="review-text">"Best gift I've ever given. The customization process was fun and easy. My girlfriend cried when she saw the teddy wearing the outfit I picked. Thank you Teddy Lap!"</p>
        </div>
        <div class="review-card reveal" style="transition-delay:0.5s;">
            <i class="fa-solid fa-quote-right quote-icon"></i>
            <div class="review-header">
                <img src="https://ui-avatars.com/api/?name=Emily+L&background=ff9a9e&color=fff&rounded=true" alt="Emily" class="reviewer-img">
                <div class="reviewer-info"><h4>Emily L.</h4><div class="review-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></div></div>
            </div>
            <p class="review-text">"Shipping was fast, and the teddy arrived in beautiful packaging. The outfit details are very precise. It's soft and huggable. Will definitely order again for the holidays."</p>
        </div>
    </div>
</section>

<!-- 5. Why Choose Us -->
<section class="features-section">
    <div class="section-title-center reveal">
        <h2>Why Choose Us?</h2>
        <p>We make dreams come true, one stitch at a time.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card-3d reveal" style="transition-delay:0.1s;">
            <div class="feature-icon-3d"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <h3>Full Customization</h3>
            <p>Design every detail from fur color to outfits. Your imagination is the only limit.</p>
        </div>
        <div class="feature-card-3d reveal" style="transition-delay:0.3s;">
            <div class="feature-icon-3d"><i class="fa-solid fa-heart"></i></div>
            <h3>Made with Love</h3>
            <p>Each teddy is handcrafted with premium materials to ensure it's soft, safe, and huggable.</p>
        </div>
        <div class="feature-card-3d reveal" style="transition-delay:0.5s;">
            <div class="feature-icon-3d"><i class="fa-solid fa-truck-fast"></i></div>
            <h3>Fast Global Shipping</h3>
            <p>Safe and speedy delivery right to your doorstep, no matter where you are.</p>
        </div>
    </div>
</section>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>

<script>
    // ── بيانات المنتجات من PHP ────────────────────────────────
    const allProducts = <?php echo json_encode(array_values($products)); ?>;
    const isLoggedIn  = <?php echo !empty($_SESSION['logged_in']) ? 'true' : 'false'; ?>;

    function getStarsHTML(rating) {
        let stars = '';
        const full  = Math.floor(rating);
        const empty = 5 - full;
        for (let i = 0; i < full;  i++) stars += '<i class="fa-solid fa-star"></i>';
        for (let i = 0; i < empty; i++) stars += '<i class="fa-regular fa-star empty"></i>';
        return stars;
    }

    function renderHomeProducts() {
        const slider = document.getElementById('productSlider');

        // ترتيب بالمبيعات وأخذ أول 12
        const sorted = [...allProducts]
            .sort((a, b) => (b.sales_count || 0) - (a.sales_count || 0))
            .slice(0, 12);

        if (sorted.length === 0) {
            slider.innerHTML = '<p style="text-align:center;width:100%;color:var(--secondary-text);">No products found.</p>';
            return;
        }

        slider.innerHTML = sorted.map(p => `
            <div class="product-card-home" onclick="window.location.href='product_details.php?id=${p.id}'">
                <div class="product-img-box-home">
                    <img src="${p.image}" alt="${p.name}"
                         onerror="this.src='https://ui-avatars.com/api/?name=Toy&background=ff9a9e&color=fff'">
                </div>
                <div class="product-info-home">
                    <h3>${p.name}</h3>
                    <div class="product-rating">${getStarsHTML(p.avg_rating || 0)}</div>
                    <div class="price">$${p.price}</div>
                    <button class="add-btn-home" onclick="event.stopPropagation(); addToCartHome(${p.id}, this)">
                        <i class="fa-solid fa-cart-plus"></i> Add
                    </button>
                </div>
            </div>
        `).join('');
    }
    renderHomeProducts();

    // ── Slider Buttons ────────────────────────────────────────
    const sliderEl = document.getElementById('productSlider');
    document.getElementById('nextBtn').addEventListener('click', () => sliderEl.scrollLeft += 310);
    document.getElementById('prevBtn').addEventListener('click', () => sliderEl.scrollLeft -= 310);

    // ── Add to Cart — يحفظ في DB إذا مسجّل، localStorage إذا لا ──
    function addToCartHome(productId, btn) {
        const original = btn.innerHTML;

        if (!isLoggedIn) {
            // مش مسجّل — حفظ في localStorage مؤقتاً
            let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
            cart[productId] = (cart[productId] || 0) + 1;
            localStorage.setItem('teddy_cart', JSON.stringify(cart));
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
            btn.style.background = '#28a745';
            setTimeout(() => { btn.innerHTML = original; btn.style.background = ''; }, 1000);
            return;
        }

        // مسجّل — حفظ في DB
        fetch('home.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_to_cart&product_id=' + productId
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
                    btn.style.background = '#28a745';
                    // تحديث عداد الـ cart في الـ navbar
                    const badge = document.getElementById('cartCount');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        badge.classList.toggle('hide', data.cart_count === 0);
                    }
                    setTimeout(() => { btn.innerHTML = original; btn.style.background = ''; }, 1000);
                }
            })
            .catch(() => {
                btn.innerHTML = '<i class="fa-solid fa-times"></i> Error';
                setTimeout(() => { btn.innerHTML = original; btn.style.background = ''; }, 1000);
            });
    }

    // ── 3D Parallax ───────────────────────────────────────────
    const teddyContainer = document.getElementById('teddy3D');
    const heroSection    = document.querySelector('.hero-section');
    const blobs          = document.querySelectorAll('.shape-blob');

    if (teddyContainer && heroSection) {
        heroSection.addEventListener('mousemove', (e) => {
            const rect    = heroSection.getBoundingClientRect();
            const x       = e.clientX - rect.left;
            const y       = e.clientY - rect.top;
            const centerX = rect.width  / 2;
            const centerY = rect.height / 2;
            teddyContainer.style.transform = `rotateX(${(y - centerY) / 20}deg) rotateY(${(centerX - x) / 20}deg)`;
            blobs.forEach((blob, i) => {
                const s = (i + 1) * 0.5;
                blob.style.transform = `translate(${(x - centerX) / (50 / s)}px, ${(y - centerY) / (50 / s)}px)`;
            });
        });
        heroSection.addEventListener('mouseleave', () => {
            teddyContainer.style.transform = 'rotateX(0) rotateY(0)';
            blobs.forEach(b => b.style.transform = 'translate(0,0)');
        });
    }

    // ── Feature Slider ────────────────────────────────────────
    const featTexts = document.querySelectorAll('.feature-slide-text');
    const featImgs  = document.querySelectorAll('.feature-img-card');
    const featDots  = document.querySelectorAll('.nav-dot');
    let currentFeature = 0;
    const totalFeatures = 3;
    let featureInterval;

    function updateFeatureSlider(index) {
        featTexts.forEach(t => t.classList.remove('active'));
        featImgs.forEach(img => {
            if (img.dataset.index == currentFeature) { img.classList.add('out'); setTimeout(() => img.classList.remove('out'), 600); }
            img.classList.remove('active');
        });
        featDots.forEach(d => d.classList.remove('active'));
        currentFeature = index;
        featTexts[currentFeature].classList.add('active');
        featImgs[currentFeature].classList.add('active');
        featDots[currentFeature].classList.add('active');
    }

    function nextFeature() { updateFeatureSlider((currentFeature + 1) % totalFeatures); }
    function prevFeature() { updateFeatureSlider((currentFeature - 1 + totalFeatures) % totalFeatures); }

    document.getElementById('featNext').addEventListener('click', () => { nextFeature(); resetAutoSlide(); });
    document.getElementById('featPrev').addEventListener('click', () => { prevFeature(); resetAutoSlide(); });
    featDots.forEach(dot => dot.addEventListener('click', (e) => { updateFeatureSlider(parseInt(e.target.dataset.index)); resetAutoSlide(); }));

    function startAutoSlide() { featureInterval = setInterval(nextFeature, 5000); }
    function resetAutoSlide() { clearInterval(featureInterval); startAutoSlide(); }
    startAutoSlide();

    // ── Reveal on Scroll ──────────────────────────────────────
    function revealOnScroll() {
        document.querySelectorAll('.reveal').forEach(el => {
            if (el.getBoundingClientRect().top < window.innerHeight - 150) el.classList.add('active');
        });
    }
    window.addEventListener('scroll', revealOnScroll);
    setTimeout(revealOnScroll, 100);
</script>

</body>
</html>