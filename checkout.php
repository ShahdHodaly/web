<?php
$pageTitle = "Checkout | Teddy Lap";
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

        /* تخطيط حاوية الدفع الرئيسية */
        .checkout-container {
            padding: 120px 20px 50px;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 40px;
        }
        /* تعديل التخطيط للشاشات الصغيرة */
        @media (max-width: 900px) {
            .checkout-container { grid-template-columns: 1fr; padding-top: 100px; }
        }

        /* ترويسة الصفحة والعنوان */
        .page-header {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 30px;
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
        .page-header p { color: var(--secondary-text); }
        /* حركة ظهور العناصر */
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* --- قسم النموذج --- */
        .checkout-form-section {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow);
            height: fit-content;
            opacity: 0;
            animation: slideIn 0.6s forwards;
        }

        .section-title {
            font-size: 20px;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i { color: var(--pink); }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        .form-group { margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1 / -1; }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid #eee;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        body.dark-mode .form-input { border-color: #444; }
        .form-input:focus { outline: none; border-color: var(--pink); box-shadow: 0 0 0 3px rgba(255, 107, 129, 0.1); }

        /* --- ستايل معلومات الهدية --- */
        .gift-info-summary {
            background: linear-gradient(45deg, rgba(255, 154, 158, 0.1), rgba(255, 107, 129, 0.1));
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid var(--pink);
        }

        .gift-info-summary h4 {
            color: var(--pink);
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .gift-info-summary p {
            margin: 5px 0;
            color: var(--text-color);
            font-size: 14px;
        }

        .gift-info-summary .gift-message {
            background: var(--card-bg);
            padding: 10px;
            border-radius: 8px;
            margin-top: 8px;
            font-style: italic;
        }

        /* --- ستايل طرق الدفع --- */
        .payment-method-selector {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background-color: var(--bg-color);
        }
        body.dark-mode .payment-option { border-color: #444; background-color: var(--card-bg); }
        .payment-option:hover { border-color: var(--pink); }
        .payment-option.active { border-color: var(--pink); background-color: rgba(255, 107, 129, 0.05); }
        .payment-option input[type="radio"] { display: none; }

        .custom-radio {
            width: 20px; height: 20px; border: 2px solid #ccc; border-radius: 50%;
            margin-right: 15px; display: flex; align-items: center; justify-content: center; transition: 0.2s;
        }
        .payment-option.active .custom-radio { border-color: var(--pink); }
        .custom-radio::after {
            content: ''; width: 10px; height: 10px; background-color: var(--pink);
            border-radius: 50%; opacity: 0; transform: scale(0); transition: 0.2s;
        }
        .payment-option.active .custom-radio::after { opacity: 1; transform: scale(1); }

        .method-info { display: flex; align-items: center; gap: 10px; flex: 1; }
        .method-info i { font-size: 20px; color: var(--secondary-text); }
        .method-info span { font-weight: 500; color: var(--text-color); }

        /* تفاصيل طرق الدفع */
        .payment-details {
            display: none;
            padding: 20px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            border: 1px dashed #eee;
            margin-top: 10px;
            animation: fadeIn 0.3s ease;
        }
        body.dark-mode .payment-details { background: rgba(0,0,0,0.1); border-color: #444; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* --- قسم ملخص الطلب --- */
        .order-summary {
            background: var(--card-bg); padding: 30px; border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow); position: sticky; top: 100px; height: fit-content;
            opacity: 0; animation: slideIn 0.6s 0.2s forwards;
        }

        .order-items { max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding-right: 10px; }
        .order-item { display: flex; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        body.dark-mode .order-item { border-bottom-color: #333; }

        /* --- أنماط الصور المركبة للمنتجات المخصصة --- */
        .order-item-img.customized-preview {
            position: relative;
            width: 60px;
            height: 60px;
            background: #f8f8f8;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .order-item-img.customized-preview img {
            position: absolute;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: all 0.3s;
        }
        .order-item-img.customized-preview .preview-base {
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 1;
        }
        .order-item-img.customized-preview .preview-outfit {
            width: 70%;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }
        .order-item-img.customized-preview .preview-shoes {
            width: 60%;
            top: 85%;
            left: 48%;
            transform: translate(-50%, -50%);
            z-index: 3;
        }
        .order-item-img.customized-preview .preview-acc {
            width: 26%;
            top: 18%;
            left: 15%;
            transform: translate(-50%, -50%);
            z-index: 4;
        }

        /* تعديلات خاصة بكل قطعة لبس حسب الصورة (لصورة 60×60) */
        .order-item-img.customized-preview .preview-outfit.img-reddress { width: 60%; top: 55%; }
        .order-item-img.customized-preview .preview-outfit.img-pinkoutfit { width: 55%; top: 52%; }
        .order-item-img.customized-preview .preview-outfit.img-greenoutfit { width: 50%; top: 52%; }
        .order-item-img.customized-preview .preview-outfit.img-jeansoutfit { width: 50%; top: 55%; }

        .order-item-img.customized-preview .preview-shoes.img-redshoes { width: 40%; top: 95%; left: 49%; }
        .order-item-img.customized-preview .preview-shoes.img-darkshoes { width: 45%; top: 91%; left: 49%; }
        .order-item-img.customized-preview .preview-shoes.img-pinkshoes { width: 45%; top: 95%; left: 49%; }
        .order-item-img.customized-preview .preview-shoes.img-conversshoes { width: 43%; top: 92%; left: 49%; }

        /* الصندوق العادي للصورة */
        .order-item-img {
            width: 60px; height: 60px; background: #f8f8f8; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        body.dark-mode .order-item-img { background: #333; }
        .order-item-img img { max-width: 80%; max-height: 80%; object-fit: contain; }

        .order-item-details { flex: 1; }
        .order-item-details h4 { margin: 0 0 5px; font-size: 14px; color: var(--text-color); }
        .order-item-details p { margin: 0; font-size: 12px; color: var(--secondary-text); }
        .order-item-price { font-weight: bold; color: var(--text-color); font-size: 14px; }

        /* شارة المنتج المخصص في ملخص الطلب */
        .custom-badge-small {
            background: var(--lavender);
            color: #fff;
            padding: 1px 6px;
            border-radius: 6px;
            font-size: 9px;
            margin-left: 5px;
            vertical-align: middle;
        }

        /* حسابات السعر */
        .summary-calc { border-top: 1px solid #eee; padding-top: 20px; margin-top: 10px; }
        body.dark-mode .summary-calc { border-top-color: #333; }

        .calc-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--secondary-text); font-size: 14px; }
        .calc-row.total { font-size: 18px; font-weight: bold; color: var(--text-color); margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ddd; }

        .place-order-btn {
            width: 100%; padding: 15px; border-radius: 50px;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff;
            border: none; font-weight: bold; font-size: 16px; cursor: pointer;
            margin-top: 25px; transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(255, 154, 158, 0.4);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .place-order-btn:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(255, 154, 158, 0.5); }
        .place-order-btn:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }

        @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body>

<!-- خلفية متحركة -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- النافبار -->
<?php
// تضمين شريط التنقل
if (file_exists('navbar.php')) include 'navbar.php';
?>

<!-- محتوى الدفع -->
<form id="checkoutForm" onsubmit="return placeOrder(event);">
    <div class="checkout-container" id="checkoutContainer">

        <div class="page-header">
            <h1>Checkout</h1>
            <p>Complete your order securely</p>
        </div>

        <!-- قسم البيانات -->
        <div class="checkout-form-section">
            <!-- معلومات الشحن -->
            <div class="section-title">
                <i class="fa-solid fa-truck"></i> Shipping Details
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstname" class="form-input" required placeholder="Enter first name">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" class="form-input" required placeholder="Enter last name">
                </div>
                <div class="form-group full-width">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-input" required placeholder="Enter your email">
                </div>
                <div class="form-group full-width">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-input" required placeholder="Enter phone number">
                </div>
                <div class="form-group full-width">
                    <label>Address</label>
                    <input type="text" name="address" class="form-input" required placeholder="Street address">
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-input" required placeholder="City">
                </div>
                <div class="form-group">
                    <label>Postal Code</label>
                    <input type="text" name="postal" class="form-input" required placeholder="Postal code">
                </div>
            </div>

            <br><hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <!-- معلومات الدفع -->
            <div class="section-title">
                <i class="fa-solid fa-wallet"></i> Payment Method
            </div>

            <div class="payment-method-selector">
                <!-- خيار 1: بطاقة ائتمان -->
                <label class="payment-option active" onclick="selectPayment(this, 'credit-card')">
                    <input type="radio" name="payment_method" value="credit_card" checked>
                    <div class="custom-radio"></div>
                    <div class="method-info">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Credit Card</span>
                    </div>
                </label>

                <!-- خيار 2: الدفع عند الاستلام -->
                <label class="payment-option" onclick="selectPayment(this, 'cod')">
                    <input type="radio" name="payment_method" value="cod">
                    <div class="custom-radio"></div>
                    <div class="method-info">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Cash on Delivery</span>
                    </div>
                </label>
            </div>

            <!-- تفاصيل البطاقة -->
            <div class="payment-details" id="credit-card-details" style="display: block;">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Card Number</label>
                        <input type="text" class="form-input" placeholder="4242 4242 4242 4242" maxlength="19">
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="password" class="form-input" placeholder="•••" maxlength="3">
                    </div>
                </div>
            </div>

            <!-- تفاصيل الدفع عند الاستلام -->
            <div class="payment-details" id="cod-details">
                <p style="color: var(--secondary-text); text-align:center; margin:0;">
                    <i class="fa-solid fa-truck-fast" style="font-size: 24px; margin-bottom: 10px; display:block;"></i>
                    Pay with cash when your order is delivered.
                </p>
            </div>
        </div>

        <!-- قسم ملخص الطلب -->
        <div class="order-summary">
            <h3 style="margin-bottom:20px; color:var(--text-color);">Your Order</h3>

            <div class="order-items" id="orderItems"></div>

            <!-- معلومات الهدية (تظهر إذا كانت مفعلة) -->
            <div id="giftInfoCheckout" style="display: none;" class="gift-info-summary"></div>

            <div class="summary-calc">
                <div class="calc-row">
                    <span>Subtotal</span>
                    <span id="checkoutSubtotal">$0.00</span>
                </div>
                <div class="calc-row" id="giftWrapRow" style="display: none;">
                    <span>Gift Wrap</span>
                    <span id="giftWrapPrice">+$0.00</span>
                </div>
                <div class="calc-row">
                    <span>Shipping</span>
                    <span style="color: #28a745;">Free</span>
                </div>
                <div class="calc-row total">
                    <span>Total</span>
                    <span id="checkoutTotal" style="color:var(--pink);">$0.00</span>
                </div>
            </div>

            <button type="submit" class="place-order-btn" id="placeOrderBtn">
                <i class="fa-solid fa-lock"></i> Place Order
            </button>
        </div>
    </div>
</form>

<!-- --- بداية قسم JavaScript --- -->
<script>
    // دالة مساعدة للحصول على اسم الكلاس من مسار الصورة
    function getImgClass(imgSrc) {
        if (!imgSrc) return '';
        const fileName = imgSrc.split('/').pop().split('.').shift();
        return 'img-' + fileName;
    }

    // 1. دمج المنتجات من PHP والتخزين المحلي
    const phpProducts = <?php echo json_encode($products ?? []); ?>;
    const customProducts = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};
    const savedDesigns = JSON.parse(localStorage.getItem('teddy_saved_designs')) || [];

    const savedDesignsMap = {};
    savedDesigns.forEach(item => savedDesignsMap[item.id] = item);

    const allProducts = { ...phpProducts, ...savedDesignsMap, ...customProducts };

    const checkoutItemsDiv = document.getElementById('orderItems');
    const subtotalSpan = document.getElementById('checkoutSubtotal');
    const totalSpan = document.getElementById('checkoutTotal');
    const giftWrapRow = document.getElementById('giftWrapRow');
    const giftWrapPrice = document.getElementById('giftWrapPrice');
    const giftInfoCheckout = document.getElementById('giftInfoCheckout');

    window.addEventListener('load', () => {
        renderCheckout();
    });

    function renderCheckout() {
        let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
        let selectedItems = JSON.parse(localStorage.getItem('teddy_selected_items')) || Object.keys(cart);

        // جلب بيانات الهدية
        let giftData = JSON.parse(localStorage.getItem('teddy_gift_data')) || {
            isGift: false,
            message: '',
            wrap: 'none',
            wrapPrice: 0
        };

        if (Object.keys(cart).length === 0) {
            window.location.href = "cart.php";
            return;
        }

        let itemsHtml = '';
        let subtotal = 0;

        selectedItems.forEach(id => {
            const product = allProducts[id];
            if (!product || !cart[id]) return;

            const qty = cart[id];
            const itemTotal = parseFloat(product.price) * qty;
            subtotal += itemTotal;

            const isCustom = id.startsWith('CUSTOM_');
            const badge = isCustom ? '<span class="custom-badge-small">Custom</span>' : '';
            const info = product.description || product.category || '';

            // بناء صورة المنتج (عادية أو مركبة)
            let imgHtml = '';
            if (product.config) {
                // منتج مخصص: نعرض طبقات متعددة
                imgHtml = `
                    <div class="order-item-img customized-preview">
                        <img src="${product.config.color.img}" class="preview-base" alt="Base">
                        ${product.config.outfit ? `<img src="${product.config.outfit.img}" class="preview-outfit ${getImgClass(product.config.outfit.img)}" alt="Outfit">` : ''}
                        ${product.config.shoes ? `<img src="${product.config.shoes.img}" class="preview-shoes ${getImgClass(product.config.shoes.img)}" alt="Shoes">` : ''}
                        ${product.config.acc ? `<img src="${product.config.acc.img}" class="preview-acc ${getImgClass(product.config.acc.img)}" alt="Accessory">` : ''}
                    </div>
                `;
            } else {
                // منتج عادي
                imgHtml = `
                    <div class="order-item-img">
                        <img src="${product.image}" alt="${product.name}">
                    </div>
                `;
            }

            itemsHtml += `
                <div class="order-item">
                    ${imgHtml}
                    <div class="order-item-details">
                        <h4>${product.name} ${badge}</h4>
                        <p>Qty: ${qty}</p>
                    </div>
                    <div class="order-item-price">$${itemTotal.toFixed(2)}</div>
                </div>
            `;
        });

        checkoutItemsDiv.innerHTML = itemsHtml;

        // عرض معلومات الهدية
        if (giftData.isGift) {
            let giftHtml = `
                <h4><i class="fa-solid fa-gift"></i> Gift Order</h4>
            `;

            if (giftData.wrap !== 'none') {
                const wrapNames = {
                    'box': 'Classic Box',
                    'teddywrap': 'Teddy Wrap',
                    'heartsbag': 'Hearts Bag'
                };
                giftHtml += `<p><strong>Wrap:</strong> ${wrapNames[giftData.wrap] || giftData.wrap} (+$${giftData.wrapPrice.toFixed(2)})</p>`;
            }

            if (giftData.message) {
                giftHtml += `<div class="gift-message"><i class="fa-solid fa-quote-left"></i> ${giftData.message}</div>`;
            }

            giftInfoCheckout.innerHTML = giftHtml;
            giftInfoCheckout.style.display = 'block';

            // إظهار سعر التغليف
            if (giftData.wrap !== 'none') {
                giftWrapRow.style.display = 'flex';
                giftWrapPrice.innerText = `+$${giftData.wrapPrice.toFixed(2)}`;
            } else {
                giftWrapRow.style.display = 'none';
            }
        } else {
            giftInfoCheckout.style.display = 'none';
            giftWrapRow.style.display = 'none';
        }

        // حساب المجموع النهائي
        const giftTotal = giftData.isGift && giftData.wrap !== 'none' ? giftData.wrapPrice : 0;
        const total = subtotal + giftTotal;

        subtotalSpan.innerText = `$${subtotal.toFixed(2)}`;
        totalSpan.innerText = `$${total.toFixed(2)}`;
    }

    function selectPayment(element, method) {
        document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        document.querySelectorAll('.payment-details').forEach(el => el.style.display = 'none');
        const detailsDiv = document.getElementById(method + '-details');
        if (detailsDiv) detailsDiv.style.display = 'block';
    }

    function placeOrder(e) {
        e.preventDefault();

        const btn = document.getElementById('placeOrderBtn');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        setTimeout(() => {
            // 1. جمع بيانات النموذج
            const form = document.getElementById('checkoutForm');
            const formData = new FormData(form);

            const firstName = formData.get('firstname');
            const lastName = formData.get('lastname');
            const address = formData.get('address') + ', ' + formData.get('city') + ', ' + formData.get('postal');
            const phone = formData.get('phone');

            // 2. تجهيز بيانات الطلب
            let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
            let selectedIds = JSON.parse(localStorage.getItem('teddy_selected_items')) || Object.keys(cart);
            let giftData = JSON.parse(localStorage.getItem('teddy_gift_data')) || {
                isGift: false,
                message: '',
                wrap: 'none',
                wrapPrice: 0
            };

            let orderItems = [];
            let subtotal = 0;

            selectedIds.forEach(id => {
                const product = allProducts[id];
                if (product && cart[id]) {
                    const qty = cart[id];
                    const itemTotal = parseFloat(product.price) * qty;
                    subtotal += itemTotal;

                    orderItems.push({
                        id: id,
                        name: product.name,
                        price: product.price,
                        qty: qty,
                        image: product.image,
                        description: product.description,
                        voice: product.voice,
                        config: product.config // حفظ الإعدادات المخصصة للعرض لاحقاً
                    });
                }
            });

            const giftTotal = giftData.isGift && giftData.wrap !== 'none' ? giftData.wrapPrice : 0;
            const total = subtotal + giftTotal;

            // 3. إنشاء كائن الطلب مع بيانات الهدية
            const orderId = 'ORD-' + Date.now();
            const today = new Date();
            const dateStr = today.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

            const newOrder = {
                id: orderId,
                date: dateStr,
                status: 'Processing',
                subtotal: subtotal.toFixed(2),
                total: total.toFixed(2),
                shipping: 0,
                address: address,
                phone: phone,
                items: orderItems,
                gift: giftData // إضافة بيانات الهدية للطلب
            };

            // 4. الحفظ في سجل الطلبات
            let ordersHistory = JSON.parse(localStorage.getItem('teddy_orders')) || [];
            ordersHistory.push(newOrder);
            localStorage.setItem('teddy_orders', JSON.stringify(ordersHistory));

            // 5. تحديث السلة (حذف العناصر المشتراة)
            selectedIds.forEach(id => delete cart[id]);
            localStorage.setItem('teddy_cart', JSON.stringify(cart));

            // 6. تنظيف البيانات المؤقتة
            localStorage.removeItem('teddy_selected_items');
            localStorage.removeItem('teddy_gift_data');

            // 7. التوجيه لصفحة النجاح
            window.location.href = 'success.php';

        }, 2000);

        return false;
    }
</script>

<?php
// تضمين ذيل الصفحة
if (file_exists('footer.php')) include 'footer.php';
?>
</body>
</html>