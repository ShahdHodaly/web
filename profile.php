<?php
$pageTitle = "My Profile | Teddy Lap";
include 'products.php';

// بيانات وهمية للمستخدم
$userName = "Jane Doe";
$joinDate = "2026";
$email = "jane.doe@example.com";
$phone = "+972 59XXXXXXX";
$address = "Palestine, Nablus";

// صورة افتراضية للبروفايل (SVG Base64)
$defaultAvatar = "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjU1IiByPSI0MCIgZmlsbD0iI2YzYmViZSIvPjxjaXJjbGUgY3g9IjE4IiBjeT0iMjUiIHI9IjE1IiBmaWxsPSIjZjNiZWJlIi8+PGNpcmNsZSBjeD0iODIiIGN5PSIyNSIgcj0iMTUiIGZpbGw9IiNmM2JlYmUiLz48Y2lyY2xlIGN4PSIxOCIgY3k9IjI1IiByPSI4IiBmaWxsPSIjZmRmMGY2Ii8+PGNpcmNsZSBjeD0iODIiIGN5PSIyNSIgcj0iOCIgZmlsbD0iI2ZkZjBmNiIvPjxlbGxpcHNlIGN4PSI1MCIgY3k9IjY1IiByeD0iMTgiIHJ5PSIxMiIgZmlsbD0iI2ZkZjBmNiIvPjxlbGxpcHNlIGN4PSI1MCIgY3k9IjYyIiByeD0iNiIgcnk9IjQiIGZpbGw9IiNkNDNhNWEiLz48Y2lyY2xlIGN4PSIzNSIgY3k9IjQ1IiByPSI1IiBmaWxsPSIjMzMzIi8+PGNpcmNsZSBjeD0iNjUiIGN5PSI0NSIgcj0iNSIgZmlsbD0iIzMzMyIvPjxwYXRoIGQ9Ik00MCA3NSBRNTAgODAgNjAgNzUiIHN0cm9rZT0iI2Q0M2E1YSIgc3Ryb2tlLXdpZHRoPSIzIiBmaWxsPSJub25lIi8+PC9zdmc+";
?>

<!DOCTYPE html>
<!-- --- بداية قسم HTML --- -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- --- بداية قسم CSS --- -->
    <style>
        /* خلفية متحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        /* حركة الأشكال */
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* --- أنماط رسالة التنبيه (Toast) --- */
        #toast-container {
            position: fixed; top: 80px; left: 50%; transform: translateX(-50%);
            z-index: 9999; display: flex; flex-direction: column; gap: 10px;
            pointer-events: none;
        }
        .toast-message {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff; padding: 15px 30px; border-radius: 50px;
            box-shadow: 0 10px 25px rgba(255, 107, 129, 0.4);
            font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 600;
            display: flex; align-items: center; gap: 10px;
            opacity: 0; transform: translateY(-30px);
            animation: toastIn 0.5s forwards;
        }
        .toast-message.toast-out { animation: toastOut 0.5s forwards; }
        .toast-message i { font-size: 18px; }
        @keyframes toastIn { to { opacity: 1; transform: translateY(0); } }
        @keyframes toastOut { to { opacity: 0; transform: translateY(-30px); } }

        /* --- أنماط نافذة التأكيد المخصصة (Modal) --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4); backdrop-filter: blur(5px);
            z-index: 10000; display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.3s;
        }
        .modal-overlay.visible { opacity: 1; pointer-events: auto; }
        .modal-box {
            background: var(--card-bg); padding: 30px;
            border-radius: 25px; width: 90%; max-width: 400px; text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            transform: scale(0.8); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.visible .modal-box { transform: scale(1); }
        .modal-box i { font-size: 50px; color: var(--pink); margin-bottom: 15px; }
        .modal-box h3 { margin: 0 0 10px; color: var(--text-color); font-family: 'Playfair Display', serif; font-size: 24px; }
        .modal-box p { color: var(--secondary-text); margin-bottom: 25px; font-size: 15px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 15px; justify-content: center; }
        .modal-btn {
            padding: 10px 30px; border-radius: 30px; border: none;
            font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 15px;
        }
        .modal-btn.cancel { background: #eee; color: #555; }
        body.dark-mode .modal-btn.cancel { background: #444; color: #ddd; }
        .modal-btn.cancel:hover { background: #ddd; }
        .modal-btn.confirm { background: #ff4757; color: #fff; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3); }
        .modal-btn.confirm:hover { background: #ff6b81; transform: translateY(-2px); }

        /* حاوية الصفحة */
        .profile-container { padding: 50px 20px; max-width: 1100px; margin: 0 auto; position: relative; z-index: 1; }

        /* الهيدر */
        .profile-header-new { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 30px; background-color: var(--card-bg); padding: 30px 40px; border-radius: 25px; box-shadow: 0 10px 30px var(--shadow); margin-bottom: 40px; opacity: 0; transform: translateY(-30px); transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); }
        .profile-header-new.visible { opacity: 1; transform: translateY(0); }
        .user-side { display: flex; align-items: center; gap: 25px; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--pink); padding: 3px; background-color: #fff; box-shadow: 0 5px 15px var(--shadow); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .profile-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; background-color: #fff3e0; }
        .edit-avatar-btn { position: absolute; bottom: 5px; right: 0; background-color: var(--pink); color: #fff; width: 30px; height: 30px; border-radius: 50%; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; z-index: 2; }
        .user-info-text h2 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--text-color); margin: 0 0 5px; }
        .membership-badge { display: inline-block; color: var(--pink); font-weight: 600; font-size: 14px; }

        /* القائمة */
        .nav-side { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
        .nav-tab-btn { background: transparent; border: 1px solid transparent; padding: 10px 20px; border-radius: 30px; color: var(--secondary-text); font-weight: 500; font-size: 14px; cursor: pointer; transition: all 0.4s ease; }
        .nav-tab-btn:hover { color: var(--text-color); background-color: rgba(248, 187, 208, 0.1); }
        .nav-tab-btn.active { background-color: var(--pink); color: #fff; border-color: var(--pink); box-shadow: 0 5px 15px rgba(248, 187, 208, 0.4); }

        /* المحتوى */
        .profile-content-area { background-color: var(--card-bg); border-radius: 25px; padding: 40px; box-shadow: 0 10px 30px var(--shadow); opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.25, 1, 0.5, 1); }
        .profile-content-area.visible { opacity: 1; transform: translateY(0); }

        .tab-content { display: none; animation: fadeIn 0.5s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .section-header { border-bottom-color: #333; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 24px; color: var(--text-color); margin: 0; }

        /* Account Info */
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; }
        .info-box label { display: block; font-size: 12px; color: var(--secondary-text); margin-bottom: 8px; }
        .info-value { margin: 0; color: var(--text-color); font-weight: 600; font-size: 16px; padding: 10px 0; }
        .info-input { width: 100%; padding: 10px; border: 1px solid var(--lavender); border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 15px; color: var(--text-color); background-color: var(--bg-color); box-sizing: border-box; display: none; }
        .editing-mode .info-value { display: none; }
        .editing-mode .info-input { display: block; }
        .editing-mode .edit-btn { display: none; }
        .editing-mode .save-btn { display: inline-block; }
        .editing-mode .cancel-btn { display: flex; }
        .edit-btn { font-size: 14px; color: var(--pink); cursor: pointer; font-weight: 600; }
        .save-btn, .cancel-btn { display: none; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; border: none; }
        .save-btn { background-color: #28a745; color: #fff; }
        .cancel-btn { background-color: #dc3545; color: #fff; width: 40px; height: 40px; padding: 0; border-radius: 50%; align-items: center; justify-content: center; }

        /* Orders */
        .order-card { border: 1px solid #f0f0f0; border-radius: 15px; padding: 20px; margin-bottom: 15px; transition: transform 0.2s, box-shadow 0.2s; }
        body.dark-mode .order-card { border-color: #333; }
        .order-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px var(--shadow); }
        .order-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .order-id { font-weight: bold; color: var(--text-color); }
        .order-status { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-processing { background: #fff3cd; color: #856404; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .order-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; border-top: 1px dashed #eee; padding-top: 10px; }
        body.dark-mode .order-footer { border-top-color: #333; }
        .order-total { font-weight: bold; color: var(--pink); }
        .details-link { background: var(--lavender); color: #fff; padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 14px; transition: background 0.3s; }
        .details-link:hover { background: var(--pink); }

        /* Settings Styles */
        .setting-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .setting-item { border-bottom-color: #333; }
        .setting-info h4 { margin: 0 0 5px; color: var(--text-color); }
        .setting-info p { margin: 0; font-size: 13px; color: var(--secondary-text); }

        .settings-action-btn { background: #333; color: #fff; border: none; padding: 8px 20px; border-radius: 20px; cursor: pointer; text-decoration: none; font-size: 14px; transition: background 0.3s; }
        .settings-action-btn:hover { background: #555; }
        .logout-btn { background: #ff4757; }
        .logout-btn:hover { background: #ff6b81; }

        /* Toggle Switch */
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: var(--pink); }
        input:checked + .slider:before { transform: translateX(24px); }

        /* === Favorites & Teddies Styles === */
        .fav-header-actions { display: flex; gap: 10px; align-items: center; }
        .manage-toggle-btn {
            background: none; border: 2px solid var(--pink); color: var(--pink);
            padding: 6px 18px; border-radius: 20px; cursor: pointer;
            font-weight: 600; font-size: 14px; transition: all 0.3s;
        }
        .manage-toggle-btn:hover, .manage-toggle-btn.active { background: var(--pink); color: #fff; }

        /* شريط الإجراءات */
        .manage-action-bar {
            display: none; justify-content: space-between; align-items: center;
            background: rgba(255, 107, 129, 0.1); padding: 12px 20px;
            border-radius: 15px; margin-bottom: 20px; animation: fadeIn 0.3s ease;
        }
        .manage-action-bar.visible { display: flex; }
        .select-all-wrapper { display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: var(--text-color); }
        .action-buttons-group { display: flex; gap: 10px; }
        .action-btn {
            border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer;
            font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 6px; transition: all 0.2s;
        }
        .action-btn.delete-btn { background: #ff4d4d; color: #fff; }
        .action-btn.delete-btn:hover { background: #e60000; }

        /* شبكة المفضلات والدببة */
        .favorites-grid, .teddies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .fav-card, .teddy-card {
            background: #fff; border-radius: 15px; padding: 15px; text-align: center;
            box-shadow: 0 5px 15px var(--shadow); transition: transform 0.3s;
            position: relative;
        }
        body.dark-mode .fav-card, body.dark-mode .teddy-card { background: #222; }
        .fav-card:hover, .teddy-card:hover { transform: translateY(-5px); }

        /* دائرة التحديد */
        .select-circle {
            width: 22px; height: 22px; border-radius: 50%; border: 2px solid #ddd;
            position: absolute; top: 10px; left: 10px; z-index: 5;
            display: none; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.2s; background-color: #fff;
        }
        .fav-card.managing .select-circle, .teddy-card.managing .select-circle { display: flex; }
        .select-circle:hover { border-color: var(--pink); }
        .select-circle.selected { background-color: var(--pink); border-color: var(--pink); color: #fff; }

        /* أنماط صندوق الصورة */
        .fav-card .fav-img-link, .teddy-card .teddy-img-link {
            display: block;
            line-height: 0;
        }
        .fav-img, .teddy-img {
            width: 100px;
            height: 100px;
            background: #f8f8f8;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            margin: 0 auto 10px auto;
            transition: transform 0.3s;
        }
        body.dark-mode .fav-img, body.dark-mode .teddy-img { background: #333; }
        .fav-img:not(.customized-preview) img, .teddy-img:not(.customized-preview) img {
            max-width: 80%; max-height: 80%; object-fit: contain;
        }

        /* أنماط الصور المركبة */
        .fav-img.customized-preview, .teddy-img.customized-preview {
            position: relative; overflow: hidden;
        }
        .fav-img.customized-preview img, .teddy-img.customized-preview img {
            position: absolute; max-width: 100%; max-height: 100%;
            object-fit: contain; transition: all 0.3s;
        }
        .fav-img.customized-preview .preview-base, .teddy-img.customized-preview .preview-base { width: 100%; height: 100%; object-fit: contain; z-index: 1; }
        .fav-img.customized-preview .preview-outfit, .teddy-img.customized-preview .preview-outfit { width: 70%; top: 45%; left: 50%; transform: translate(-50%, -50%); z-index: 2; }
        .fav-img.customized-preview .preview-shoes, .teddy-img.customized-preview .preview-shoes { width: 60%; top: 85%; left: 48%; transform: translate(-50%, -50%); z-index: 3; }
        .fav-img.customized-preview .preview-acc, .teddy-img.customized-preview .preview-acc { width: 26%; top: 18%; left: 15%; transform: translate(-50%, -50%); z-index: 4; }

        /* تعديلات خاصة بكل قطعة لبس */
        .fav-img.customized-preview .preview-outfit.img-reddress, .teddy-img.customized-preview .preview-outfit.img-reddress { width: 60%; top: 55%; }
        .fav-img.customized-preview .preview-outfit.img-pinkoutfit, .teddy-img.customized-preview .preview-outfit.img-pinkoutfit { width: 55%; top: 52%; }
        .fav-img.customized-preview .preview-outfit.img-greenoutfit, .teddy-img.customized-preview .preview-outfit.img-greenoutfit { width: 50%; top: 52%; }
        .fav-img.customized-preview .preview-outfit.img-jeansoutfit, .teddy-img.customized-preview .preview-outfit.img-jeansoutfit { width: 50%; top: 55%; }
        .fav-img.customized-preview .preview-shoes.img-redshoes, .teddy-img.customized-preview .preview-shoes.img-redshoes { width: 40%; top: 95%; left: 49%; }
        .fav-img.customized-preview .preview-shoes.img-darkshoes, .teddy-img.customized-preview .preview-shoes.img-darkshoes { width: 45%; top: 91%; left: 49%; }
        .fav-img.customized-preview .preview-shoes.img-pinkshoes, .teddy-img.customized-preview .preview-shoes.img-pinkshoes { width: 45%; top: 95%; left: 49%; }
        .fav-img.customized-preview .preview-shoes.img-conversshoes, .teddy-img.customized-preview .preview-shoes.img-conversshoes { width: 43%; top: 92%; left: 49%; }

        .fav-name, .teddy-name { font-weight: 600; color: var(--text-color); margin: 0 0 5px; font-size: 16px; }
        .fav-name a, .teddy-name a { text-decoration: none; color: inherit; }
        .fav-name a:hover, .teddy-name a:hover { color: var(--pink); }
        .fav-price, .teddy-price { color: var(--pink); margin-bottom: 15px; font-weight: bold; }

        /* أزرار الدبدوب */
        .add-cart-btn {
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: #fff; border: none; padding: 5px 15px; border-radius: 20px;
            cursor: pointer; transition: all 0.3s; font-size: 13px;
            display: inline-block; box-shadow: 0 4px 10px rgba(255,154,158,0.3);
        }
        .add-cart-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(255,154,158,0.4); }
        .add-cart-btn.added { background: #28a745; }

        /* تعديل لتوسيط محتوى بطاقة الدبدوب */
        .teddy-info, .fav-info { display: flex; flex-direction: column; align-items: center; }

        .remove-fav-btn { background: #ff4757; color: #fff; border: none; padding: 5px 15px; border-radius: 20px; cursor: pointer; transition: background 0.3s; font-size: 13px; }
        .remove-fav-btn:hover { background: #ff6b81; }

        /* Empty State (General) */
        .empty-state { text-align: center; padding: 40px 20px; color: var(--secondary-text); }
        .empty-state i { font-size: 60px; color: var(--lavender); margin-bottom: 20px; opacity: 0.8; }
        .empty-state h4 { font-size: 20px; color: var(--text-color); margin-bottom: 10px; }
        .empty-state p { font-size: 15px; margin-bottom: 20px; }

        /* Shop Button in Empty State */
        .shop-btn {
            display: inline-block; background: var(--pink); color: #fff;
            padding: 12px 30px; border-radius: 30px; text-decoration: none;
            font-weight: 600; transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(248, 187, 208, 0.3);
        }
        .shop-btn:hover { background: var(--primary); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(248, 187, 208, 0.4); }

        /* Review Area Styles */
        .review-area { margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        .review-items-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .review-item-card { background: #f9f9f9; border-radius: 10px; padding: 15px; }
        body.dark-mode .review-item-card { background: #2d2d2d; }
        .star-rating i { font-size: 20px; cursor: pointer; margin-right: 2px; }
        .review-comment { width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif; }
        body.dark-mode .review-comment { background: #333; color: #fff; border-color: #444; }

        @media (max-width: 768px) {
            .profile-header-new { flex-direction: column; text-align: center; padding: 20px; }
            .user-side { flex-direction: column; gap: 15px; }
            .favorites-grid, .teddies-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        }
    </style>
</head>
<body>

<!-- خلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- حاوية التنبيهات (Toast Container) -->
<div id="toast-container"></div>

<!-- نافذة التأكيد المخصصة (Modal) -->
<div id="customModal" class="modal-overlay">
    <div class="modal-box">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <h3>Are you sure?</h3>
        <p id="modalMessage">Do you really want to delete this item?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
            <button class="modal-btn confirm" id="confirmModalBtn">Delete</button>
        </div>
    </div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="profile-container">
    <!-- الهيدر -->
    <div class="profile-header-new">
        <div class="user-side">
            <div class="profile-avatar">
                <img src="<?php echo $defaultAvatar; ?>" alt="User Avatar">
                <div class="edit-avatar-btn"><i class="fa-solid fa-camera"></i></div>
            </div>
            <div class="user-info-text">
                <h2><?php echo $userName; ?></h2>
                <span class="membership-badge">Member since <?php echo $joinDate; ?></span>
            </div>
        </div>

        <div class="nav-side">
            <button class="nav-tab-btn active" data-tab="account"><i class="fa-solid fa-user"></i> My Account</button>
            <button class="nav-tab-btn" data-tab="orders"><i class="fa-solid fa-box"></i> My Orders</button>
            <button class="nav-tab-btn" data-tab="teddies"><i class="fa-solid fa-wand-magic-sparkles"></i> My Teddies</button>
            <button class="nav-tab-btn" data-tab="favorites"><i class="fa-solid fa-heart"></i> Favorites</button>
            <button class="nav-tab-btn" data-tab="settings"><i class="fa-solid fa-gear"></i> Settings</button>
        </div>
    </div>

    <!-- المحتوى -->
    <div class="profile-content-area" id="contentArea">

        <!-- 1. Account -->
        <div id="tab-account" class="tab-content active">
            <div class="section-header">
                <h3 class="section-title">Personal Information</h3>
                <div class="action-buttons">
                    <span class="edit-btn" onclick="toggleEdit(true)"><i class="fa-solid fa-pen"></i> Edit</span>
                    <button class="save-btn" onclick="saveChanges()"><i class="fa-solid fa-check"></i> Save</button>
                    <button class="cancel-btn" onclick="toggleEdit(false)"><i class="fa-solid fa-times"></i></button>
                </div>
            </div>
            <form id="profileForm">
                <div class="info-grid">
                    <div class="info-box"><label>Full Name</label><p class="info-value"><?php echo $userName; ?></p><input type="text" class="info-input" value="<?php echo $userName; ?>"></div>
                    <div class="info-box"><label>Email Address</label><p class="info-value"><?php echo $email; ?></p><input type="email" class="info-input" value="<?php echo $email; ?>"></div>
                    <div class="info-box"><label>Phone Number</label><p class="info-value"><?php echo $phone; ?></p><input type="text" class="info-input" value="<?php echo $phone; ?>"></div>
                    <div class="info-box"><label>Address</label><p class="info-value"><?php echo $address; ?></p><input type="text" class="info-input" value="<?php echo $address; ?>"></div>
                </div>
            </form>
        </div>

        <!-- 2. Orders -->
        <div id="tab-orders" class="tab-content">
            <div class="section-header"><h3 class="section-title">My Orders</h3></div>
            <div id="orders-list-container"></div>
            <div id="orders-empty" class="empty-state" style="display:none;">
                <i class="fa-solid fa-box-open"></i>
                <h4>No Orders Yet</h4>
                <p>Looks like you haven't placed any orders yet.<br>Start shopping to find your favorite teddies!</p>
                <a href="shop.php" class="shop-btn"><i class="fa-solid fa-bag-shopping"></i> Start Shopping</a>
            </div>
        </div>

        <!-- 3. Teddies -->
        <div id="tab-teddies" class="tab-content">
            <div class="section-header">
                <h3 class="section-title">My Customized Teddies</h3>
                <div class="fav-header-actions">
                    <button id="teddyManageBtn" class="manage-toggle-btn" onclick="toggleTeddyManageMode()" style="display:none;">Manage</button>
                </div>
            </div>

            <div id="teddyActionBar" class="manage-action-bar">
                <div class="select-all-wrapper" onclick="toggleTeddySelectAll()">
                    <div id="teddySelectAllCircle" class="select-circle" style="position:relative; top:0; left:0; display:flex;"></div>
                    <span>Select All</span>
                </div>
                <div class="action-buttons-group">
                    <button class="action-btn delete-btn" onclick="deleteSelectedTeddies()">
                        <i class="fa-solid fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>

            <div id="teddies-empty" class="empty-state" style="display:none;">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <h4>No Custom Teddies Yet</h4>
                <p>Start designing your own unique teddy bear!</p>
                <a href="customize.php" class="shop-btn">Create Now</a>
            </div>

            <div id="teddies-grid" class="teddies-grid"></div>
        </div>

        <!-- 4. Favorites -->
        <div id="tab-favorites" class="tab-content">
            <div class="section-header">
                <h3 class="section-title">My Favorites</h3>
                <div class="fav-header-actions">
                    <button id="favManageBtn" class="manage-toggle-btn" onclick="toggleFavManageMode()" style="display:none;">Manage</button>
                </div>
            </div>

            <div id="favActionBar" class="manage-action-bar">
                <div class="select-all-wrapper" onclick="toggleFavSelectAll()">
                    <div id="favSelectAllCircle" class="select-circle" style="position:relative; top:0; left:0; display:flex;"></div>
                    <span>Select All</span>
                </div>
                <div class="action-buttons-group">
                    <button class="action-btn delete-btn" onclick="deleteSelectedFavorites()">
                        <i class="fa-solid fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>

            <div id="favorites-empty" class="empty-state" style="display:none;">
                <i class="fa-solid fa-heart-crack"></i>
                <h4>No Favorites Yet</h4>
                <p>You haven't added any teddies to your favorites yet.</p>
                <a href="shop.php" class="shop-btn">Start Shopping</a>
            </div>

            <div id="favorites-grid" class="favorites-grid"></div>
        </div>

        <!-- 5. Settings -->
        <div id="tab-settings" class="tab-content">
            <div class="section-header"><h3 class="section-title">Settings</h3></div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Change Password</h4>
                    <p>Update your password regularly for security.</p>
                </div>
                <a href="change_password.php" class="settings-action-btn">
                    <i class="fa-solid fa-key"></i> Change
                </a>
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Email Notifications</h4>
                    <p>Receive emails about new products and offers.</p>
                </div>
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Logout</h4>
                    <p>Sign out of your account on this device.</p>
                </div>
                <a href="auth.php" class="settings-action-btn logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

    </div>
</div>

<!-- --- بداية قسم JavaScript --- -->
<script>
    // دالة مساعدة للحصول على اسم الكلاس من مسار الصورة
    function getImgClass(imgSrc) {
        if (!imgSrc) return '';
        const fileName = imgSrc.split('/').pop().split('.').shift();
        return 'img-' + fileName;
    }

    // --- نظام التنبيهات (Toast) ---
    function showToast(message) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('toast-out');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // --- نظام نافذة التأكيد (Modal) ---
    let confirmCallback = null;

    function showCustomConfirm(message, callback) {
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('customModal').classList.add('visible');
        confirmCallback = callback;
    }

    function closeModal() {
        document.getElementById('customModal').classList.remove('visible');
        confirmCallback = null;
    }

    document.getElementById('confirmModalBtn').addEventListener('click', () => {
        if (confirmCallback) confirmCallback();
        closeModal();
    });

    // إغلاق النافذة عند الضغط على الخلفية
    document.getElementById('customModal').addEventListener('click', (e) => {
        if (e.target.id === 'customModal') closeModal();
    });

    // جلب المنتجات العادية من PHP
    const phpProducts = <?php echo json_encode($products); ?>;

    // جلب المنتجات المخصصة المؤقتة (من الكاستمايز)
    let customItemsRaw = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};
    let customProducts = {};
    if (Array.isArray(customItemsRaw)) {
        customItemsRaw.forEach(item => { customProducts[item.id] = item; });
    } else {
        customProducts = customItemsRaw;
    }

    // جلب التصاميم المحفوظة (My Teddies)
    const savedDesignsArray = JSON.parse(localStorage.getItem('teddy_saved_designs')) || [];
    const savedDesignsMap = {};
    savedDesignsArray.forEach(item => {
        savedDesignsMap[item.id] = item;
    });

    // دمج الكل
    const allProducts = { ...phpProducts, ...savedDesignsMap, ...customProducts };

    const WISHLIST_KEY = 'teddy_wishlist';
    const SAVED_TEDDIES_KEY = 'teddy_saved_designs';
    const CART_KEY = 'teddy_cart';
    const ORDERS_KEY = 'teddy_orders';
    const REVIEWS_KEY = 'teddy_reviews';

    let favIsManaging = false;
    let selectedFavIds = new Set();
    let teddyIsManaging = false;
    let selectedTeddyIds = new Set();

    window.addEventListener('load', () => {
        setTimeout(() => { document.querySelector('.profile-header-new').classList.add('visible'); }, 200);
        setTimeout(() => { document.querySelector('.profile-content-area').classList.add('visible'); }, 600);

        renderFavorites();
        renderMyTeddies();
        renderOrders();

        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab) {
            const targetBtn = document.querySelector(`.nav-tab-btn[data-tab="${activeTab}"]`);
            if (targetBtn) {
                targetBtn.click();
            }
        }
    });

    const tabButtons = document.querySelectorAll('.nav-tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            tabContents.forEach(content => content.classList.remove('active'));
            const tabName = button.getAttribute('data-tab');
            document.getElementById('tab-' + tabName).classList.add('active');

            if (tabName !== 'account') toggleEdit(false);
            if (tabName !== 'favorites' && favIsManaging) toggleFavManageMode();
            if (tabName !== 'teddies' && teddyIsManaging) toggleTeddyManageMode();
        });
    });

    function toggleEdit(isEditing) {
        const accountTab = document.getElementById('tab-account');
        if (isEditing) accountTab.classList.add('editing-mode');
        else accountTab.classList.remove('editing-mode');
    }
    function saveChanges() {
        showToast("Changes Saved Successfully!");
        toggleEdit(false);
    }

    // ------------------ نظام الطلبات والتقييمات ------------------
    function initializeOrders() {
        let orders = JSON.parse(localStorage.getItem(ORDERS_KEY)) || [];

        // تعريف الطلبات التجريبية المطلوبة: واحدة Shipped و واحدة Delivered
        const mockOrders = [
            {
                id: 'MOCK-SHIP-001',
                date: '2025-04-05',
                status: 'Shipped',
                total: 120.00,
                items: [{ productId: '1', name: 'Giant Panda', image: 'images/teddy1.png', price: 120 }]
            },
            {
                id: 'MOCK-DEL-001',
                date: '2025-03-28',
                status: 'Delivered',
                total: 75.00,
                items: [
                    { productId: '6', name: 'Barbie Princess', image: 'images/barbie5.png', price: 40 },
                    { productId: '11', name: 'Building Blocks', image: 'images/building1.png', price: 35 }
                ]
            }
        ];

        // إضافة الطلبات التجريبية فقط إذا لم تكن موجودة (للتأكد من عدم تكرارها)
        mockOrders.forEach(mockOrder => {
            if (!orders.some(o => o.id === mockOrder.id)) {
                orders.push(mockOrder);
            }
        });

        // حفظ القائمة المحدثة (التي تحتوي على طلبات المستخدم الجديدة + الطلبات التجريبية)
        localStorage.setItem(ORDERS_KEY, JSON.stringify(orders));
        return orders;
    }

    function renderOrders() {
        let orders = initializeOrders();
        const container = document.getElementById('orders-list-container');
        const emptyMsg = document.getElementById('orders-empty');

        if (orders.length === 0) {
            container.innerHTML = '';
            emptyMsg.style.display = 'block';
        } else {
            emptyMsg.style.display = 'none';
            let html = '';
            // ترتيب الطلبات: الحقيقية أولاً ثم التجريبية (أو حسب التاريخ)
            orders.sort((a, b) => new Date(b.date) - new Date(a.date));

            orders.forEach(order => {
                const items = order.items || [];
                const reviewAreaId = `review-area-${order.id}`;

                html += `
                    <div class="order-card" data-order-id="${order.id}">
                        <div class="order-header">
                            <span class="order-id">#${order.id}</span>
                            <span class="order-status status-${order.status.toLowerCase()}">${order.status}</span>
                        </div>
                        <p style="color:var(--secondary-text); font-size:14px;"><i class="fa-regular fa-calendar"></i> Date: ${order.date}</p>
                        <div class="order-footer">
                            <span class="order-total">$${order.total}</span>
                            <div>
                                <a href="order_details.php?id=${order.id}" class="details-link">View Details <i class="fa-solid fa-arrow-right"></i></a>
                                ${order.status === 'Delivered' ? `<button class="details-link rate-order-btn" onclick="toggleReviewArea('${order.id}')" style="margin-left:10px; background:var(--pink);">Rate Order <i class="fa-solid fa-star"></i></button>` : ''}
                            </div>
                        </div>
                        <div id="${reviewAreaId}" class="review-area" style="display: none;">
                            <h4 style="margin-bottom: 15px;">Rate Products from Order #${order.id}</h4>
                            <div class="review-items-grid">
                                ${items.map(item => renderReviewItem(item, order.id)).join('')}
                            </div>
                            <div style="text-align: right; margin-top: 20px;">
                                <button class="shop-btn" onclick="submitAllReviews('${order.id}')" style="padding: 8px 25px;">Submit All Reviews</button>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
            loadExistingReviews();
        }
    }

    function renderReviewItem(item, orderId) {
        const productId = item.productId;
        const savedReview = getSavedReview(productId, orderId);
        const rating = savedReview ? savedReview.rating : 0;
        const comment = savedReview ? savedReview.comment : '';

        return `
            <div class="review-item-card" data-product-id="${productId}" data-order-id="${orderId}">
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
                    <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: contain; border-radius: 5px;">
                    <div>
                        <strong style="color: var(--text-color);">${item.name}</strong>
                        <p style="margin: 5px 0 0; font-size: 13px; color: var(--secondary-text);">Price: $${item.price}</p>
                    </div>
                </div>
                <div class="star-rating" style="margin-bottom: 10px;">
                    ${[1,2,3,4,5].map(star => `
                        <i class="fa-${star <= rating ? 'solid' : 'regular'} fa-star"
                           style="color: ${star <= rating ? '#ffc107' : '#ddd'}; font-size: 20px; cursor: pointer; margin-right: 2px;"
                           onclick="setRating('${orderId}', '${productId}', ${star})"></i>
                    `).join('')}
                </div>
                <textarea class="review-comment" placeholder="Write your review (optional)..."
                          onchange="setComment('${orderId}', '${productId}', this.value)">${comment}</textarea>
            </div>
        `;
    }

    function setRating(orderId, productId, rating) {
        let tempReviews = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
        if (!tempReviews[orderId]) tempReviews[orderId] = {};
        if (!tempReviews[orderId][productId]) tempReviews[orderId][productId] = {};
        tempReviews[orderId][productId].rating = rating;
        sessionStorage.setItem('temp_reviews', JSON.stringify(tempReviews));
        updateStarDisplay(orderId, productId);
    }

    function setComment(orderId, productId, comment) {
        let tempReviews = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
        if (!tempReviews[orderId]) tempReviews[orderId] = {};
        if (!tempReviews[orderId][productId]) tempReviews[orderId][productId] = {};
        tempReviews[orderId][productId].comment = comment;
        sessionStorage.setItem('temp_reviews', JSON.stringify(tempReviews));
    }

    function updateStarDisplay(orderId, productId) {
        const tempReviews = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
        const rating = tempReviews[orderId]?.[productId]?.rating || 0;
        const stars = document.querySelectorAll(`.review-item-card[data-product-id="${productId}"][data-order-id="${orderId}"] .star-rating i`);
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('fa-regular'); star.classList.add('fa-solid'); star.style.color = '#ffc107';
            } else {
                star.classList.remove('fa-solid'); star.classList.add('fa-regular'); star.style.color = '#ddd';
            }
        });
    }

    function getSavedReview(productId, orderId) {
        const allReviews = JSON.parse(localStorage.getItem(REVIEWS_KEY)) || {};
        if (allReviews[productId]) {
            return allReviews[productId].find(r => r.orderId === orderId) || null;
        }
        return null;
    }

    function loadExistingReviews() {
        const allReviews = JSON.parse(localStorage.getItem(REVIEWS_KEY)) || {};
        document.querySelectorAll('.review-item-card').forEach(card => {
            const productId = card.dataset.productId;
            const orderId = card.dataset.orderId;
            const saved = allReviews[productId]?.find(r => r.orderId === orderId);
            if (saved) {
                setRating(orderId, productId, saved.rating);
                setComment(orderId, productId, saved.comment);
                const textarea = card.querySelector('.review-comment');
                if (textarea) textarea.value = saved.comment;
            }
        });
    }

    function toggleReviewArea(orderId) {
        const area = document.getElementById(`review-area-${orderId}`);
        if (area) {
            if (area.style.display === 'none' || area.style.display === '') {
                area.style.display = 'block';
                loadExistingReviews();
            } else {
                area.style.display = 'none';
                const temp = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
                delete temp[orderId];
                sessionStorage.setItem('temp_reviews', JSON.stringify(temp));
            }
        }
    }

    function submitAllReviews(orderId) {
        const tempReviews = JSON.parse(sessionStorage.getItem('temp_reviews')) || {};
        const orderReviews = tempReviews[orderId];
        if (!orderReviews) {
            showToast('No reviews to submit.');
            return;
        }

        const allReviews = JSON.parse(localStorage.getItem(REVIEWS_KEY)) || {};
        const userName = '<?php echo $userName; ?>';
        const now = new Date().toISOString().split('T')[0];

        for (const [productId, data] of Object.entries(orderReviews)) {
            if (!data.rating) continue;
            if (!allReviews[productId]) allReviews[productId] = [];
            const existingIndex = allReviews[productId].findIndex(r => r.orderId === orderId);
            const review = {
                rating: data.rating,
                comment: data.comment || '',
                date: now,
                userName: userName,
                orderId: orderId
            };
            if (existingIndex !== -1) {
                allReviews[productId][existingIndex] = review;
            } else {
                allReviews[productId].push(review);
            }
        }

        localStorage.setItem(REVIEWS_KEY, JSON.stringify(allReviews));
        delete tempReviews[orderId];
        sessionStorage.setItem('temp_reviews', JSON.stringify(tempReviews));

        showToast('Reviews submitted successfully!');
        toggleReviewArea(orderId);
        const rateBtn = document.querySelector(`.order-card[data-order-id="${orderId}"] .rate-order-btn`);
        if (rateBtn) {
            rateBtn.disabled = true;
            rateBtn.style.opacity = '0.5';
            rateBtn.innerText = 'Rated';
        }
    }

    // ------------------ دوال المفضلات (Favorites) ------------------
    function renderFavorites() {
        let wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY)) || [];
        const grid = document.getElementById('favorites-grid');
        const emptyMsg = document.getElementById('favorites-empty');
        const manageBtn = document.getElementById('favManageBtn');
        const actionBar = document.getElementById('favActionBar');

        if (wishlist.length === 0) {
            grid.style.display = 'none';
            emptyMsg.style.display = 'block';
            manageBtn.style.display = 'none';
            actionBar.classList.remove('visible');
            favIsManaging = false;
        } else {
            grid.style.display = 'grid';
            emptyMsg.style.display = 'none';
            manageBtn.style.display = 'block';

            let html = '';
            wishlist.forEach(id => {
                const product = allProducts[id];
                if (product) {
                    const isSelected = selectedFavIds.has(id) ? 'selected' : '';
                    const checkIcon = selectedFavIds.has(id) ? '<i class="fa-solid fa-check"></i>' : '';
                    const managingClass = favIsManaging ? 'managing' : '';

                    let imgHtml = '';
                    if (product.config) {
                        imgHtml = `
                            <div class="fav-img customized-preview">
                                <img src="${product.config.color.img}" class="preview-base" alt="Base">
                                ${product.config.outfit ? `<img src="${product.config.outfit.img}" class="preview-outfit ${getImgClass(product.config.outfit.img)}" alt="Outfit">` : ''}
                                ${product.config.shoes ? `<img src="${product.config.shoes.img}" class="preview-shoes ${getImgClass(product.config.shoes.img)}" alt="Shoes">` : ''}
                                ${product.config.acc ? `<img src="${product.config.acc.img}" class="preview-acc ${getImgClass(product.config.acc.img)}" alt="Accessory">` : ''}
                            </div>
                        `;
                    } else {
                        imgHtml = `<img src="${product.image}" alt="${product.name}" class="fav-img" onerror="this.src='https://ui-avatars.com/api/?name=Teddy&background=ff9a9e&color=fff'">`;
                    }

                    html += `
                        <div class="fav-card ${managingClass} ${isSelected}">
                            <div class="select-circle ${isSelected}" onclick="toggleFavSelect('${id}')">${checkIcon}</div>
                            <a href="product_details.php?id=${id}" class="fav-img-link">
                                ${imgHtml}
                            </a>
                            <div class="fav-info">
                                <h4 class="fav-name"><a href="product_details.php?id=${id}">${product.name}</a></h4>
                                <p class="fav-price">$${product.price}</p>
                                <button class="remove-fav-btn" onclick="removeFavorite('${id}')">
                                    <i class="fa-solid fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    `;
                }
            });
            grid.innerHTML = html;
        }
        updateFavSelectAllUI();
    }

    function toggleFavManageMode() {
        favIsManaging = !favIsManaging;
        const manageBtn = document.getElementById('favManageBtn');
        const actionBar = document.getElementById('favActionBar');

        if (favIsManaging) {
            manageBtn.classList.add('active');
            manageBtn.innerText = 'Done';
            actionBar.classList.add('visible');
        } else {
            manageBtn.classList.remove('active');
            manageBtn.innerText = 'Manage';
            actionBar.classList.remove('visible');
            selectedFavIds.clear();
        }
        renderFavorites();
    }

    function toggleFavSelect(id) {
        if (!favIsManaging) return;
        if (selectedFavIds.has(id)) selectedFavIds.delete(id);
        else selectedFavIds.add(id);
        renderFavorites();
    }

    function toggleFavSelectAll() {
        let wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY)) || [];
        const allSelected = wishlist.every(id => selectedFavIds.has(id));
        if (allSelected) selectedFavIds.clear();
        else wishlist.forEach(id => selectedFavIds.add(id));
        renderFavorites();
    }

    function updateFavSelectAllUI() {
        let wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY)) || [];
        const circle = document.getElementById('favSelectAllCircle');
        if (!circle) return;
        const allSelected = wishlist.length > 0 && wishlist.every(id => selectedFavIds.has(id));
        if (allSelected) { circle.classList.add('selected'); circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px; color:#fff;"></i>'; }
        else { circle.classList.remove('selected'); circle.innerHTML = ''; }
    }

    function deleteSelectedFavorites() {
        if (selectedFavIds.size === 0) { showToast("Please select items to delete."); return; }

        showCustomConfirm(`Are you sure you want to delete ${selectedFavIds.size} selected item(s)?`, () => {
            let wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY)) || [];
            wishlist = wishlist.filter(id => !selectedFavIds.has(id));
            localStorage.setItem(WISHLIST_KEY, JSON.stringify(wishlist));
            selectedFavIds.clear();
            renderFavorites();
            showToast("Items deleted successfully!");
            if(typeof updateWishlistCount === 'function') updateWishlistCount();
        });
    }

    function removeFavorite(id) {
        showCustomConfirm("Are you sure you want to remove this item from favorites?", () => {
            let wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY)) || [];
            wishlist = wishlist.filter(item => item !== id);
            localStorage.setItem(WISHLIST_KEY, JSON.stringify(wishlist));
            renderFavorites();
            showToast("Item removed!");
            if(typeof updateWishlistCount === 'function') updateWishlistCount();
        });
    }

    // ------------------ دوال الدببة المخصصة (My Teddies) ------------------
    function renderMyTeddies() {
        let designs = JSON.parse(localStorage.getItem(SAVED_TEDDIES_KEY)) || [];
        const grid = document.getElementById('teddies-grid');
        const emptyMsg = document.getElementById('teddies-empty');
        const manageBtn = document.getElementById('teddyManageBtn');
        const actionBar = document.getElementById('teddyActionBar');

        if (designs.length === 0) {
            grid.style.display = 'none';
            emptyMsg.style.display = 'block';
            manageBtn.style.display = 'none';
            actionBar.classList.remove('visible');
            teddyIsManaging = false;
        } else {
            grid.style.display = 'grid';
            emptyMsg.style.display = 'none';
            manageBtn.style.display = 'block';

            let html = '';
            designs.forEach(item => {
                const isSelected = selectedTeddyIds.has(item.id) ? 'selected' : '';
                const checkIcon = selectedTeddyIds.has(item.id) ? '<i class="fa-solid fa-check"></i>' : '';
                const managingClass = teddyIsManaging ? 'managing' : '';

                let imgHtml = '';
                if (item.config) {
                    imgHtml = `
                        <div class="teddy-img customized-preview">
                            <img src="${item.config.color.img}" class="preview-base" alt="Base">
                            ${item.config.outfit ? `<img src="${item.config.outfit.img}" class="preview-outfit ${getImgClass(item.config.outfit.img)}" alt="Outfit">` : ''}
                            ${item.config.shoes ? `<img src="${item.config.shoes.img}" class="preview-shoes ${getImgClass(item.config.shoes.img)}" alt="Shoes">` : ''}
                            ${item.config.acc ? `<img src="${item.config.acc.img}" class="preview-acc ${getImgClass(item.config.acc.img)}" alt="Accessory">` : ''}
                        </div>
                    `;
                } else {
                    imgHtml = `<img src="${item.image}" alt="${item.name}" class="teddy-img" onerror="this.src='https://ui-avatars.com/api/?name=Teddy&background=ff9a9e&color=fff'">`;
                }

                html += `
                    <div class="teddy-card ${managingClass} ${isSelected}">
                        <div class="select-circle ${isSelected}" onclick="toggleTeddySelect('${item.id}')">${checkIcon}</div>
                        <a href="custom_details.php?id=${item.id}" class="teddy-img-link">
                            ${imgHtml}
                        </a>
                        <div class="teddy-info">
                            <h4 class="teddy-name"><a href="custom_details.php?id=${item.id}">${item.name}</a></h4>
                            <p class="teddy-price">$${item.price}</p>
                            <button class="add-cart-btn" onclick="addCustomToCart('${item.id}', this)">
                                <i class="fa-solid fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                `;
            });
            grid.innerHTML = html;
        }
        updateTeddySelectAllUI();
    }

    function addCustomToCart(id, btn) {
        let cart = JSON.parse(localStorage.getItem(CART_KEY)) || {};
        cart[id] = (cart[id] || 0) + 1;
        localStorage.setItem(CART_KEY, JSON.stringify(cart));

        btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
        btn.classList.add('added');

        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to Cart';
            btn.classList.remove('added');
        }, 1500);

        if(typeof updateCartCount === 'function') updateCartCount();
    }

    function toggleTeddyManageMode() {
        teddyIsManaging = !teddyIsManaging;
        const manageBtn = document.getElementById('teddyManageBtn');
        const actionBar = document.getElementById('teddyActionBar');

        if (teddyIsManaging) {
            manageBtn.classList.add('active');
            manageBtn.innerText = 'Done';
            actionBar.classList.add('visible');
        } else {
            manageBtn.classList.remove('active');
            manageBtn.innerText = 'Manage';
            actionBar.classList.remove('visible');
            selectedTeddyIds.clear();
        }
        renderMyTeddies();
    }

    function toggleTeddySelect(id) {
        if (!teddyIsManaging) return;
        if (selectedTeddyIds.has(id)) selectedTeddyIds.delete(id);
        else selectedTeddyIds.add(id);
        renderMyTeddies();
    }

    function toggleTeddySelectAll() {
        let designs = JSON.parse(localStorage.getItem(SAVED_TEDDIES_KEY)) || [];
        const allSelected = designs.every(item => selectedTeddyIds.has(item.id));
        if (allSelected) selectedTeddyIds.clear();
        else designs.forEach(item => selectedTeddyIds.add(item.id));
        renderMyTeddies();
    }

    function updateTeddySelectAllUI() {
        let designs = JSON.parse(localStorage.getItem(SAVED_TEDDIES_KEY)) || [];
        const circle = document.getElementById('teddySelectAllCircle');
        if (!circle) return;
        const allSelected = designs.length > 0 && designs.every(item => selectedTeddyIds.has(item.id));
        if (allSelected) { circle.classList.add('selected'); circle.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px; color:#fff;"></i>'; }
        else { circle.classList.remove('selected'); circle.innerHTML = ''; }
    }

    function deleteSelectedTeddies() {
        if (selectedTeddyIds.size === 0) { showToast("Please select items to delete."); return; }

        const count = selectedTeddyIds.size;
        const itemWord = count === 1 ? 'teddy' : 'teddies';

        showCustomConfirm(`Are you sure you want to delete ${count} customized ${itemWord}?`, () => {
            let designs = JSON.parse(localStorage.getItem(SAVED_TEDDIES_KEY)) || [];
            designs = designs.filter(item => !selectedTeddyIds.has(item.id));
            localStorage.setItem(SAVED_TEDDIES_KEY, JSON.stringify(designs));
            selectedTeddyIds.clear();
            renderMyTeddies();
            showToast("Deleted successfully!");
        });
    }
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>