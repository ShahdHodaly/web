<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: auth.php');
    exit;
}
$pageTitle = "Order Success | Teddy Lap";
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
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0,0) rotate(0deg); } 100% { transform: translate(50px,30px) rotate(20deg); } }
        .success-container { height: 80vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 20px; position: relative; z-index: 1; }
        .success-box { background: var(--card-bg); padding: 50px; border-radius: 25px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); max-width: 500px; width: 100%; opacity: 0; animation: slideUp 0.6s forwards; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .success-icon-circle { width: 100px; height: 100px; background: linear-gradient(45deg, #ff9a9e, #fad0c4); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; box-shadow: 0 10px 20px rgba(255,154,158,0.4); }
        .success-icon-circle i { font-size: 50px; color: #fff; }
        .success-title { font-family: 'Playfair Display', serif; font-size: 32px; color: var(--text-color); margin-bottom: 15px; }
        .success-text { color: var(--secondary-text); font-size: 15px; line-height: 1.6; margin-bottom: 10px; }
        .order-number { font-weight: bold; color: var(--pink); font-size: 18px; display: block; margin: 10px 0 20px; }
        .order-total { color: var(--text-color); font-weight: 600; font-size: 16px; margin-bottom: 25px; }
        .btn-group { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .home-btn { display: inline-flex; align-items: center; gap: 10px; background: var(--primary); color: #fff; padding: 15px 35px; border-radius: 50px; text-decoration: none; font-weight: bold; transition: all 0.3s; }
        .home-btn:hover { background: var(--pink); transform: translateY(-2px); }
        .orders-btn { display: inline-flex; align-items: center; gap: 10px; background: transparent; color: var(--pink); border: 2px solid var(--pink); padding: 13px 25px; border-radius: 50px; text-decoration: none; font-weight: bold; transition: all 0.3s; }
        .orders-btn:hover { background: var(--pink); color: #fff; transform: translateY(-2px); }
        .teddy-footer { font-size: 40px; margin-top: 30px; animation: bounce 2s infinite; }
        @keyframes bounce { 0%,20%,50%,80%,100% { transform: translateY(0); } 40% { transform: translateY(-20px); } 60% { transform: translateY(-10px); } }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="success-container">
    <div class="success-box">
        <div class="success-icon-circle">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1 class="success-title">Order Confirmed! 🎉</h1>

        <p class="success-text">
            Thank you for your purchase! Your order has been placed successfully.
        </p>

        <span class="order-number" id="orderNumberDisplay">Loading...</span>
        <p class="order-total" id="orderTotalDisplay"></p>

        <div class="btn-group">
            <a href="shop.php" class="home-btn">
                <i class="fa-solid fa-bag-shopping"></i> Continue Shopping
            </a>
            <a href="profile.php?tab=orders" class="orders-btn">
                <i class="fa-solid fa-box"></i> My Orders
            </a>
        </div>

        <div class="teddy-footer">🧸</div>
    </div>
</div>

<script>
    // جيبي رقم الطلب والمجموع من sessionStorage
    const orderNumber = sessionStorage.getItem('last_order_number');
    const orderTotal  = sessionStorage.getItem('last_order_total');

    const numEl   = document.getElementById('orderNumberDisplay');
    const totalEl = document.getElementById('orderTotalDisplay');

    if (orderNumber) {
        numEl.textContent = 'Order #' + orderNumber;
        sessionStorage.removeItem('last_order_number');
    } else {
        numEl.textContent = 'Order placed successfully!';
    }

    if (orderTotal) {
        totalEl.textContent = 'Total: $' + orderTotal;
        sessionStorage.removeItem('last_order_total');
    }
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>