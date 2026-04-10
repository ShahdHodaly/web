<?php
$pageTitle = "My Cart | Teddy Lap";
include 'products.php';
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

    <!-- ملف الستايل العام -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- --- بداية قسم CSS --- -->
    <style>
        /* أنماط خلفية الصفحة المتحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        /* حركة الأشكال العائمة */
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* تخطيط حاوية السلة الرئيسية */
        .cart-container {
            padding: 120px 20px 50px;
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            transition: all 0.3s ease;
        }
        /* تعديل التخطيط للشاشات الصغيرة */
        @media (max-width: 900px) {
            .cart-container { grid-template-columns: 1fr; padding-top: 100px; }
        }

        /* ترويسة الصفحة والعنوان */
        .page-header {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 20px;
            opacity: 0;
            animation: fadeDown 0.8s forwards;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            background: linear-gradient(45deg, #ff6b81, #ff9a9e, #fbc2eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        .page-header .back-link { color: var(--pink); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; margin-top: 10px; }
        /* حركة ظهور العناصر */
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* قسم قائمة المنتجات */
        .cart-items-section { display: flex; flex-direction: column; gap: 20px; }

        /* رأس القائمة وزر الإدارة */
        .cart-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 0 5px;
        }
        .cart-list-header h3 {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            font-size: 20px;
            margin: 0;
        }
        .manage-toggle-btn {
            background: none;
            border: 2px solid var(--pink);
            color: var(--pink);
            padding: 6px 18px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        .manage-toggle-btn:hover, .manage-toggle-btn.active {
            background: var(--pink);
            color: #fff;
        }

        /* شريط الإجراءات العلوي */
        .manage-action-bar {
            display: none;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 107, 129, 0.1);
            padding: 12px 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            animation: fadeIn 0.3s ease;
        }
        .manage-action-bar.visible { display: flex; }

        .select-all-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-color);
            user-select: none;
        }

        .action-buttons-group { display: flex; gap: 10px; }
        .action-btn {
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .action-btn.delete-btn { background: #ff4d4d; color: #fff; }
        .action-btn.delete-btn:hover { background: #e60000; transform: scale(1.05); }
        .action-btn.fav-btn { background: var(--lavender); color: #fff; }
        .action-btn.fav-btn:hover { background: #d896ff; transform: scale(1.05); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* بطاقة المنتج */
        .cart-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 20px;
            display: flex;
            gap: 15px;
            align-items: stretch;
            box-shadow: 0 10px 30px var(--shadow);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            opacity: 0;
            animation: slideIn 0.6s forwards;
        }
        /* تأثير الحذف للبطاقة */
        .cart-card.removing { transform: translateX(100px); opacity: 0; max-height: 0; padding: 0; margin: 0; border: none; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }

        /* دائرة الاختيار */
        .select-circle {
            width: 22px; height: 22px; border-radius: 50%; border: 2px solid #ddd;
            align-self: center; flex-shrink: 0; cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center; color: transparent;
            z-index: 2;
        }
        .select-circle:hover { border-color: var(--pink); }
        .select-circle.selected { background-color: var(--pink); border-color: var(--pink); color: #fff; }

        /* رابط المحتوى */
        .cart-content-link {
            flex: 1;
            display: flex;
            gap: 15px;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: opacity 0.2s;
        }
        .cart-content-link:hover { opacity: 0.8; }

        /* ---- الأنماط الجديدة للصور المركبة ---- */
        .cart-img-box.customized-preview {
            position: relative;
            width: 80px;
            height: 80px;
            background: #f8f8f8;
            border-radius: 15px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .cart-img-box.customized-preview img {
            position: absolute;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: all 0.3s;
        }
        .cart-img-box.customized-preview .preview-base {
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 1;
        }
        .cart-img-box.customized-preview .preview-outfit {
            width: 70%;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }
        .cart-img-box.customized-preview .preview-shoes {
            width: 60%;
            top: 85%;
            left: 48%;
            transform: translate(-50%, -50%);
            z-index: 3;
        }
        .cart-img-box.customized-preview .preview-acc {
            width: 26%;
            top: 18%;
            left: 15%;
            transform: translate(-50%, -50%);
            z-index: 4;
        }

        /* تعديلات خاصة بكل قطعة لبس حسب الصورة */
        .cart-img-box.customized-preview .preview-outfit.img-reddress { width: 60%; top: 55%; }
        .cart-img-box.customized-preview .preview-outfit.img-pinkoutfit { width: 55%; top: 52%; }
        .cart-img-box.customized-preview .preview-outfit.img-greenoutfit { width: 50%; top: 52%; }
        .cart-img-box.customized-preview .preview-outfit.img-jeansoutfit { width: 50%; top: 55%; }

        /* تعديلات خاصة بكل حذاء */
        .cart-img-box.customized-preview .preview-shoes.img-redshoes { width: 40%; top: 95%; left: 49%; }
        .cart-img-box.customized-preview .preview-shoes.img-darkshoes { width: 45%; top: 91%; left: 49%; }
        .cart-img-box.customized-preview .preview-shoes.img-pinkshoes { width: 45%; top: 95%; left: 49%; }
        .cart-img-box.customized-preview .preview-shoes.img-conversshoes { width: 43%; top: 92%; left: 49%; }

        /* الصندوق العادي للصورة */
        .cart-img-box {
            width: 80px; height: 80px; background: #f8f8f8; border-radius: 15px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        body.dark-mode .cart-img-box { background: #333; }
        .cart-img-box img { max-width: 80%; max-height: 80%; object-fit: contain; }

        .cart-details { flex: 1; }
        .cart-details h3 { margin: 0 0 5px; color: var(--text-color); font-size: 18px; }

        .cart-details .category {
            color: var(--secondary-text);
            font-size: 12px;
            margin-bottom: 5px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 250px;
        }

        .custom-badge {
            background: linear-gradient(45deg, #a29bfe, #6c5ce7);
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            margin-left: 8px;
            vertical-align: middle;
        }

        .cart-details .price { color: var(--pink); font-weight: bold; font-size: 18px; }

        .cart-actions-right { display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end; padding-bottom: 5px; z-index: 2; }
        .delete-btn-single { background: none; border: none; color: #ddd; cursor: pointer; font-size: 14px; transition: all 0.2s; margin-bottom: auto; margin-right: -5px; }
        .delete-btn-single:hover { color: #ff4d4d; transform: scale(1.1); }

        .quantity-controls { display: flex; align-items: center; gap: 8px; background: #f5f5f5; padding: 5px; border-radius: 20px; }
        body.dark-mode .quantity-controls { background: #333; }
        .qty-btn { width: 28px; height: 28px; border-radius: 50%; border: none; background: #fff; color: var(--text-color); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        body.dark-mode .qty-btn { background: #444; }
        .qty-btn:hover { background: var(--pink); color: #fff; }
        .qty-val { font-weight: 600; min-width: 20px; text-align: center; font-size: 14px; }

        .cart-summary {
            background: var(--card-bg); border-radius: 20px; padding: 30px;
            height: fit-content; box-shadow: 0 10px 30px var(--shadow);
            opacity: 0; animation: slideIn 0.8s 0.2s forwards;
        }

        .gift-btn {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ff9a9e, #fbc2eb);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(255, 154, 158, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .gift-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 154, 158, 0.4); }
        .gift-btn i { font-size: 18px; }

        .gift-mode-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            grid-column: 1 / -1;
        }
        .gift-mode-header .back-link {
            color: var(--pink);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 16px;
        }
        .gift-mode-header .back-link:hover { color: #ff6b81; }

        .gift-details {
            background: rgba(255, 107, 129, 0.05);
            border-radius: 20px;
            padding: 25px;
            animation: fadeIn 0.4s ease;
        }
        .gift-details h3 {
            font-family: 'Playfair Display', serif;
            color: var(--pink);
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .gift-details h3 i { font-size: 24px; }

        .gift-message-box {
            margin-bottom: 25px;
        }
        .gift-message-box label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }
        .gift-message-box textarea {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #ddd;
            background: var(--bg-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            resize: vertical;
            min-height: 80px;
        }
        .gift-message-box textarea:focus { outline: none; border-color: var(--pink); }

        .wrap-options {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
        }
        .wrap-option {
            flex: 1;
            min-width: 150px;
            background: var(--card-bg);
            border: 2px solid #eee;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            opacity: 0.7;
        }
        body.dark-mode .wrap-option { border-color: #444; }
        .wrap-option:hover { transform: translateY(-3px); border-color: var(--pink); }
        .wrap-option.selected { border-color: var(--pink); opacity: 1; background: rgba(255, 107, 129, 0.05); }
        .wrap-option img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .wrap-option span {
            display: block;
            font-weight: 600;
            color: var(--text-color);
        }
        .wrap-option small {
            color: var(--secondary-text);
            font-size: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: var(--secondary-text);
        }
        .summary-row.total {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 20px;
            color: var(--text-color);
        }
        body.dark-mode .summary-row.total { border-top-color: #444; }

        .checkout-btn {
            width: 100%; padding: 15px; border-radius: 50px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff;
            border: none; font-weight: bold; font-size: 16px; cursor: pointer;
            margin-top: 20px; transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(255, 154, 158, 0.4);
        }
        .checkout-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(255, 154, 158, 0.5); }

        .empty-cart {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeDown 0.8s forwards;
        }
        .empty-cart i { font-size: 100px; color: var(--pink); margin-bottom: 20px; opacity: 0.8; }
        .empty-cart h2 { color: var(--text-color); margin-bottom: 10px; font-family: 'Playfair Display', serif; }
        .empty-cart p { color: var(--secondary-text); margin-bottom: 30px; font-size: 18px; }
        .shop-btn { background: var(--primary); color: #fff; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: background 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .shop-btn:hover { background: var(--pink); transform: translateY(-2px); }

        /* --- أنماط رسالة التنبيه المخصصة (Toast) --- */
        .toast-container {
            position: fixed; top: 100px; left: 50%; transform: translateX(-50%);
            z-index: 10000; display: flex; flex-direction: column; gap: 10px;
            pointer-events: none;
        }
        .toast-message {
            background: linear-gradient(45deg, #ff9a9e, #ff6b81);
            color: #fff; padding: 15px 25px; border-radius: 50px;
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
    </style>
</head>
<body>

<!-- خلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php
// تضمين شريط التنقل
if (file_exists('navbar.php')) {
    include 'navbar.php';
}
?>

<!-- حاوية السلة -->
<div class="cart-container" id="cartContainer">
</div>

<!-- --- بداية قسم JavaScript --- -->
<script>
    // 1. جلب المنتجات العادية من PHP
    const phpProducts = <?php echo json_encode($products ?? []); ?>;

    // 2. جلب المنتجات المخصصة المؤقتة (من الكاستمايز)
    let customItemsRaw = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};
    let customProducts = {};
    if (Array.isArray(customItemsRaw)) {
        customItemsRaw.forEach(item => { customProducts[item.id] = item; });
    } else {
        customProducts = customItemsRaw;
    }

    // 3. جلب التصاميم المحفوظة (My Teddies)
    const savedDesignsArray = JSON.parse(localStorage.getItem('teddy_saved_designs')) || [];
    const savedDesignsMap = {};
    savedDesignsArray.forEach(item => {
        savedDesignsMap[item.id] = item;
    });

    // 4. دمج الكل: المنتجات العادية + الدببة المحفوظة + الدببة المؤقتة
    const allProducts = { ...phpProducts, ...savedDesignsMap, ...customProducts };

    // 5. إدارة السلة
    const cartContainer = document.getElementById('cartContainer');
    let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
    let wishlist = JSON.parse(localStorage.getItem('teddy_wishlist')) || [];
    let selectedItems = new Set();
    let isManaging = false;
    let isGiftMode = false; // حالة وضع الهدية

    // بيانات الهدية
    let giftData = {
        isGift: false,
        message: '',
        wrap: 'none',
        wrapPrice: 0
    };

    // تعريف أسعار التغليف
    const wrapPrices = {
        'box': 5.00,
        'teddywrap': 7.00,
        'heartsbag': 6.00
    };

    window.addEventListener('load', () => {
        Object.keys(cart).forEach(id => selectedItems.add(id.toString()));
        renderCart();
    });

    // دالة مساعدة للحصول على اسم الكلاس من مسار الصورة
    function getImgClass(imgSrc) {
        if (!imgSrc) return '';
        const fileName = imgSrc.split('/').pop().split('.').shift();
        return 'img-' + fileName;
    }

    // --- دالة عرض التنبيهات الجديدة ---
    function showToast(message) {
        // إنشاء حاوية التنبيهات إذا لم تكن موجودة
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        // إنشاء عنصر التنبيه
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        // استخدام أيقونة لطيفة
        toast.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${message}`;

        container.appendChild(toast);

        // إزالة التنبيه بعد 3 ثواني
        setTimeout(() => {
            toast.classList.add('toast-out');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    function toggleManageMode() {
        isManaging = !isManaging;
        renderCart();
    }

    // تفعيل/إلغاء وضع الهدية
    function toggleGiftMode() {
        isGiftMode = !isGiftMode;
        if (!isGiftMode) {
            // إعادة تعيين بيانات الهدية
            giftData = { isGift: false, message: '', wrap: 'none', wrapPrice: 0 };
        }
        renderCart();
    }

    // اختيار نوع التغليف
    function selectWrap(wrapType) {
        giftData.wrap = wrapType;
        giftData.wrapPrice = wrapPrices[wrapType] || 0;
        renderCart();
    }

    // تحديث رسالة الهدية
    function updateGiftMessage(message) {
        giftData.message = message;
    }

    function renderCart() {
        const productIds = Object.keys(cart);
        let leftColumnHtml = '';  // العمود الأيسر
        let rightColumnHtml = ''; // العمود الأيمن
        let subtotal = 0;

        // حساب المجموع الفرعي من العناصر المختارة
        productIds.forEach(id => {
            if (selectedItems.has(id.toString())) {
                const product = allProducts[id];
                if (product) {
                    subtotal += parseFloat(product.price) * cart[id];
                }
            }
        });

        const giftTotal = (isGiftMode && giftData.wrap !== 'none') ? giftData.wrapPrice : 0;
        const total = subtotal + giftTotal;

        // بناء قائمة المنتجات (تظهر في اليسار دائماً)
        let productsHtml = `<div class="cart-items-section">`;

        // زر العودة في وضع الهدية (يظهر في اليسار)
        if (isGiftMode && productIds.length > 0) {
            productsHtml += `
                <div class="gift-mode-header">
                    <button class="back-link" onclick="toggleGiftMode()">
                        <i class="fa-solid fa-arrow-left"></i> Back to Cart
                    </button>
                </div>
            `;
        }

        if (productIds.length > 0) {
            productsHtml += `
                <div class="cart-list-header">
                    <h3>Your Items</h3>
                    <button class="manage-toggle-btn ${isManaging ? 'active' : ''}" onclick="toggleManageMode()">
                        ${isManaging ? 'Done' : 'Manage'}
                    </button>
                </div>
            `;
        }

        if (isManaging && productIds.length > 0) {
            const allSelected = productIds.every(id => selectedItems.has(id.toString()));
            productsHtml += `
                <div class="manage-action-bar visible">
                    <div class="select-all-wrapper" onclick="toggleSelectAll()">
                        <div class="select-circle ${allSelected ? 'selected' : ''}" style="width:20px; height:20px;">
                            ${allSelected ? '<i class="fa-solid fa-check" style="font-size:10px;"></i>' : ''}
                        </div>
                        <span>Select All</span>
                    </div>
                    <div class="action-buttons-group">
                        <button class="action-btn delete-btn" onclick="deleteSelected()">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                        <button class="action-btn fav-btn" onclick="moveToWishlist()">
                            <i class="fa-regular fa-heart"></i> Move to Fav
                        </button>
                    </div>
                </div>
            `;
        }

        productIds.forEach(id => {
            const qty = cart[id];
            const product = allProducts[id];
            if (!product) return;

            const isSelected = selectedItems.has(id.toString()) ? 'selected' : '';
            const checkIcon = selectedItems.has(id.toString()) ? '<i class="fa-solid fa-check"></i>' : '';

            const isCustom = customProducts[id] || savedDesignsMap[id] || id.startsWith('CUSTOM_');
            const detailLink = isCustom ? `custom_details.php?id=${id}` : `product_details.php?id=${id}`;

            const infoText = product.description || product.category || 'Teddy Bear';
            const badge = isCustom ? '<span class="custom-badge">Custom</span>' : '';

            // بناء صورة المنتج (عادية أو مركبة)
            let imgHtml = '';
            if (product.config) {
                // منتج مخصص: نعرض طبقات متعددة
                imgHtml = `
                    <div class="cart-img-box customized-preview">
                        <img src="${product.config.color.img}" class="preview-base" alt="Base">
                        ${product.config.outfit ? `<img src="${product.config.outfit.img}" class="preview-outfit ${getImgClass(product.config.outfit.img)}" alt="Outfit">` : ''}
                        ${product.config.shoes ? `<img src="${product.config.shoes.img}" class="preview-shoes ${getImgClass(product.config.shoes.img)}" alt="Shoes">` : ''}
                        ${product.config.acc ? `<img src="${product.config.acc.img}" class="preview-acc ${getImgClass(product.config.acc.img)}" alt="Accessory">` : ''}
                    </div>
                `;
            } else {
                // منتج عادي
                imgHtml = `
                    <div class="cart-img-box">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                `;
            }

            productsHtml += `
                <div class="cart-card" id="card-${id}">
                    <div class="select-circle ${isSelected}" onclick="toggleSelect('${id}')">
                        ${checkIcon}
                    </div>

                    <a href="${detailLink}" class="cart-content-link">
                        ${imgHtml}
                        <div class="cart-details">
                            <h3>${product.name} ${badge}</h3>
                            <span class="category">${infoText}</span>
                            <div class="price">$${product.price}</div>
                        </div>
                    </a>

                    <div class="cart-actions-right">
                        <button class="delete-btn-single" onclick="removeItem('${id}')"><i class="fa-solid fa-trash"></i></button>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateQty('${id}', -1)"><i class="fa-solid fa-minus"></i></button>
                            <span class="qty-val">${qty}</span>
                            <button class="qty-btn" onclick="updateQty('${id}', 1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            `;
        });

        productsHtml += `</div>`;

        // بناء ملخص الطلب (Order Summary)-
        let summaryHtml = `
            <div class="cart-summary">
                ${!isGiftMode ? `
                    <button class="gift-btn" onclick="toggleGiftMode()">
                        <i class="fa-solid fa-gift"></i> Send as Gift
                    </button>
                ` : ''}
                <h3 style="margin-bottom:20px; color:var(--text-color);">Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$${subtotal.toFixed(2)}</span>
                </div>
        `;

        if (isGiftMode && giftData.wrap !== 'none') {
            let wrapName = '';
            if (giftData.wrap === 'box') wrapName = 'Classic Box';
            else if (giftData.wrap === 'teddywrap') wrapName = 'Teddy Wrap';
            else if (giftData.wrap === 'heartsbag') wrapName = 'Hearts Bag';

            summaryHtml += `
                <div class="summary-row">
                    <span>Gift Wrap (${wrapName})</span>
                    <span>+$${giftData.wrapPrice.toFixed(2)}</span>
                </div>
            `;
        }

        summaryHtml += `
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color: #28a745;">Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span style="color:var(--pink);">$${total.toFixed(2)}</span>
                </div>
                <button class="checkout-btn"
                    onclick="checkout()"
                    ${selectedItems.size === 0 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''}>
                    Proceed to Checkout
                </button>
            </div>
        `;

        // بناء تفاصيل الهدية (Gift Details)
        let giftDetailsHtml = '';
        if (isGiftMode && productIds.length > 0) {
            giftDetailsHtml = `
                <div class="gift-details">
                    <h3><i class="fa-solid fa-gift"></i> Gift Details</h3>
                    <div class="gift-message-box">
                        <label for="giftMessage">Message (optional)</label>
                        <textarea id="giftMessage" placeholder="Write your gift message here..." oninput="updateGiftMessage(this.value)">${giftData.message}</textarea>
                    </div>
                    <div class="wrap-options">
                        <div class="wrap-option ${giftData.wrap === 'box' ? 'selected' : ''}" data-wrap="box" onclick="selectWrap('box')">
                            <img src="images/box.png" alt="Classic Box">
                            <span>Classic Box</span>
                            <small>+$5.00</small>
                        </div>
                        <div class="wrap-option ${giftData.wrap === 'teddywrap' ? 'selected' : ''}" data-wrap="teddywrap" onclick="selectWrap('teddywrap')">
                            <img src="images/teddywrap.png" alt="Teddy Wrap">
                            <span>Teddy Wrap</span>
                            <small>+$7.00</small>
                        </div>
                        <div class="wrap-option ${giftData.wrap === 'heartsbag' ? 'selected' : ''}" data-wrap="heartsbag" onclick="selectWrap('heartsbag')">
                            <img src="images/heartsbag.png" alt="Hearts Bag">
                            <span>Hearts Bag</span>
                            <small>+$6.00</small>
                        </div>
                    </div>
                </div>
            `;
        }

        // توزيع المحتوى على الأعمدة حسب وضع الهدية
        if (isGiftMode) {
            leftColumnHtml = productsHtml + summaryHtml;
            rightColumnHtml = giftDetailsHtml;
        } else {
            leftColumnHtml = productsHtml;
            rightColumnHtml = summaryHtml;
        }

        // إذا كانت السلة فارغة
        if (productIds.length === 0) {
            cartContainer.innerHTML = `
                <div class="empty-cart">
                    <i class="fa-solid fa-face-sad-tear"></i>
                    <h2>Your Cart is Empty</h2>
                    <p>Looks like you haven't added any teddies yet.</p>
                    <a href="shop.php" class="shop-btn">Start Shopping</a>
                </div>
            `;
            return;
        }

        // تجميع الصفحة
        let finalHtml = `
            <div class="page-header">
                <h1>Shopping Cart</h1>
                <a href="shop.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Continue Shopping</a>
            </div>
            ${leftColumnHtml}
            ${rightColumnHtml}
        `;

        cartContainer.innerHTML = finalHtml;
    }

    // دوال التعديل على السلة
    function toggleSelect(id) {
        id = id.toString();
        if (selectedItems.has(id)) selectedItems.delete(id);
        else selectedItems.add(id);
        renderCart();
    }

    function toggleSelectAll() {
        const productIds = Object.keys(cart);
        const allSelected = productIds.every(id => selectedItems.has(id.toString()));
        if (allSelected) selectedItems.clear();
        else productIds.forEach(id => selectedItems.add(id.toString()));
        renderCart();
    }

    function deleteSelected() {
        if (selectedItems.size === 0) {
            showToast("Please select items to delete!");
            return;
        }
        Array.from(selectedItems).forEach(id => {
            delete cart[id];
            let customItems = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};
            if (customItems[id]) {
                delete customItems[id];
                localStorage.setItem('teddy_custom_items', JSON.stringify(customItems));
            }
        });
        selectedItems.clear();
        saveCart();
        renderCart();
    }

    function moveToWishlist() {
        if (selectedItems.size === 0) {
            showToast("Please select items to move!");
            return;
        }

        const idsToMove = Array.from(selectedItems);
        let wishlist = JSON.parse(localStorage.getItem('teddy_wishlist')) || [];
        let savedDesigns = JSON.parse(localStorage.getItem('teddy_saved_designs')) || [];
        let customItems = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};

        idsToMove.forEach(id => {
            const isCustom = customProducts[id] || savedDesignsMap[id] || id.startsWith('CUSTOM_');

            if (isCustom) {
                const exists = savedDesigns.some(item => item.id === id);
                if (!exists && allProducts[id]) {
                    savedDesigns.push({
                        id: id,
                        name: allProducts[id].name,
                        price: allProducts[id].price,
                        image: allProducts[id].image,
                        description: allProducts[id].description,
                        voice: allProducts[id].voice,
                        config: allProducts[id].config
                    });
                }
                if (customItems[id]) {
                    delete customItems[id];
                    localStorage.setItem('teddy_custom_items', JSON.stringify(customItems));
                }
                localStorage.setItem('teddy_saved_designs', JSON.stringify(savedDesigns));
            } else {
                if (!wishlist.includes(id)) {
                    wishlist.push(id);
                }
            }
            delete cart[id];
        });

        localStorage.setItem('teddy_wishlist', JSON.stringify(wishlist));
        selectedItems.clear();
        saveCart();
        renderCart();

        if(typeof updateWishlistCount === 'function') updateWishlistCount();
    }

    function updateQty(id, change) {
        id = id.toString();
        if (!cart[id]) return;
        cart[id] += change;
        if (cart[id] <= 0) removeItem(id);
        else { saveCart(); renderCart(); }
    }

    function removeItem(id) {
        id = id.toString();
        const card = document.getElementById(`card-${id}`);
        if (card) {
            card.classList.add('removing');
            setTimeout(() => {
                delete cart[id];
                selectedItems.delete(id);
                let customItems = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};
                if (customItems[id]) {
                    delete customItems[id];
                    localStorage.setItem('teddy_custom_items', JSON.stringify(customItems));
                }
                saveCart();
                renderCart();
            }, 400);
        }
    }

    function saveCart() {
        localStorage.setItem('teddy_cart', JSON.stringify(cart));
    }

    function checkout() {
        if (selectedItems.size === 0) {
            showToast("Please select items to checkout!");
            return;
        }

        localStorage.setItem(
            'teddy_selected_items',
            JSON.stringify(Array.from(selectedItems))
        );

        if (isGiftMode) {
            giftData.isGift = true;
            localStorage.setItem('teddy_gift_data', JSON.stringify(giftData));
        } else {
            localStorage.removeItem('teddy_gift_data');
        }

        window.location.href = 'checkout.php';
    }
</script>
<?php
// تضمين ذيل الصفحة
if (file_exists('footer.php')) include 'footer.php';
?>
</body>
</html>