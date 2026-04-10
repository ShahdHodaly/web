<?php
$pageTitle = "Order Details | Teddy Lap";
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

        /* حاوية الصفحة */
        .order-details-container {
            padding: 120px 20px 50px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* الهيدر */
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
            opacity: 0;
            animation: fadeDown 0.8s forwards;
        }
        .order-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: var(--text-color);
            margin: 0 0 5px;
        }
        .order-title span { color: var(--secondary-text); font-size: 14px; }

        .back-btn {
            background: var(--lavender);
            color: #fff;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .back-btn:hover { background: var(--pink); transform: translateY(-2px); }

        /* تتبع الطلب */
        .tracking-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px var(--shadow);
            margin-bottom: 30px;
            opacity: 0;
            animation: slideIn 0.8s 0.2s forwards;
        }
        .status-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .status-header h3 { margin: 0; color: var(--text-color); font-size: 18px; }
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-processing { background: #fff3cd; color: #856404; }

        /* خطوات التتبع */
        .tracking-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 40px;
        }
        .tracking-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }
        .step-icon {
            width: 35px; height: 35px;
            background: #e0e0e0;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
        }
        .step-text { font-size: 12px; color: var(--secondary-text); font-weight: 500; }

        .step.completed .step-icon { background: var(--pink); box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3); }
        .step.completed .step-text { color: var(--pink); }
        .step.active .step-icon { background: var(--lavender); box-shadow: 0 0 0 3px rgba(200, 162, 200, 0.3); }
        .step.active .step-text { color: var(--lavender); font-weight: 600; }

        /* محتوى الطلب */
        .order-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            opacity: 0;
            animation: slideIn 0.8s 0.4s forwards;
        }
        @media (max-width: 768px) {
            .order-content { grid-template-columns: 1fr; }
        }

        /* قائمة المنتجات */
        .items-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px var(--shadow);
        }
        .items-card h3 { margin-top: 0; margin-bottom: 20px; color: var(--text-color); }

        .item-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        body.dark-mode .item-row { border-bottom-color: #333; }
        .item-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

        /* --- أنماط الصور المركبة للمنتجات المخصصة --- */
        .item-img.customized-preview {
            position: relative;
            width: 70px;
            height: 70px;
            background: #f8f8f8;
            border-radius: 12px;
            overflow: hidden;
        }
        .item-img.customized-preview img {
            position: absolute;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: all 0.3s;
        }
        .item-img.customized-preview .preview-base {
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 1;
        }
        .item-img.customized-preview .preview-outfit {
            width: 70%;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }
        .item-img.customized-preview .preview-shoes {
            width: 60%;
            top: 85%;
            left: 48%;
            transform: translate(-50%, -50%);
            z-index: 3;
        }
        .item-img.customized-preview .preview-acc {
            width: 26%;
            top: 18%;
            left: 15%;
            transform: translate(-50%, -50%);
            z-index: 4;
        }

        /* تعديلات خاصة بكل قطعة لبس حسب الصورة (لصورة 70×70) */
        .item-img.customized-preview .preview-outfit.img-reddress { width: 60%; top: 55%; }
        .item-img.customized-preview .preview-outfit.img-pinkoutfit { width: 55%; top: 52%; }
        .item-img.customized-preview .preview-outfit.img-greenoutfit { width: 50%; top: 52%; }
        .item-img.customized-preview .preview-outfit.img-jeansoutfit { width: 50%; top: 55%; }

        .item-img.customized-preview .preview-shoes.img-redshoes { width: 40%; top: 95%; left: 49%; }
        .item-img.customized-preview .preview-shoes.img-darkshoes { width: 45%; top: 91%; left: 49%; }
        .item-img.customized-preview .preview-shoes.img-pinkshoes { width: 45%; top: 95%; left: 49%; }
        .item-img.customized-preview .preview-shoes.img-conversshoes { width: 43%; top: 92%; left: 49%; }

        /* الصندوق العادي للصورة */
        .item-img {
            width: 70px; height: 70px;
            background: #f8f8f8;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        body.dark-mode .item-img { background: #333; }
        .item-img img { max-width: 90%; max-height: 90%; object-fit: contain; }

        .item-info { flex: 1; }
        .item-info h4 { margin: 0 0 5px; color: var(--text-color); font-size: 16px; }
        .item-info p { margin: 0; color: var(--secondary-text); font-size: 13px; }

        .item-price { text-align: right; }
        .item-price span { display: block; color: var(--pink); font-weight: bold; }
        .item-price small { color: var(--secondary-text); font-size: 12px; }

        /* ملخص الشحن والدفع */
        .summary-side {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .info-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px var(--shadow);
        }
        .info-card h4 { margin-top: 0; margin-bottom: 15px; color: var(--text-color); font-size: 16px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; color: var(--secondary-text); }
        .info-row strong { color: var(--text-color); }

        .total-row {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 10px;
            font-size: 18px;
            font-weight: bold;
            color: var(--text-color);
        }
        body.dark-mode .total-row { border-top-color: #333; }

        /* --- أنماط بطاقة الهدية --- */
        .gift-card-display {
            background: linear-gradient(135deg, #fff0f5 0%, #fff 100%);
            border: 1px solid #ffcee6;
        }
        body.dark-mode .gift-card-display {
            background: linear-gradient(135deg, #2a2025 0%, #222 100%);
            border-color: #555;
        }
        .gift-message-box {
            background: rgba(255, 255, 255, 0.6);
            border-left: 3px solid var(--pink);
            padding: 10px 15px;
            border-radius: 0 8px 8px 0;
            font-style: italic;
            font-size: 13px;
            color: var(--text-color);
            margin-top: 10px;
        }
        body.dark-mode .gift-message-box { background: rgba(0,0,0,0.2); }
        .gift-wrap-preview {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* حالة عدم وجود طلب */
        .error-state { text-align: center; padding: 50px; grid-column: 1/-1; }
        .error-state i { font-size: 50px; color: var(--pink); margin-bottom: 15px; }

        @keyframes fadeDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<!-- خلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- النافبار -->
<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="order-details-container">

    <!-- رأس الصفحة -->
    <div class="order-header">
        <div class="order-title">
            <h1>Order #<span id="displayOrderId">...</span></h1>
            <span><i class="fa-regular fa-calendar"></i> Placed on <span id="displayOrderDate">...</span></span>
        </div>
        <a href="profile.php?tab=orders" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <!-- محتوى الصفحة (سيتم تعبئته بـ JS) -->
    <div id="orderContentWrapper">
        <!-- تتبع الطلب -->
        <div class="tracking-card">
            <div class="status-header">
                <h3>Order Status</h3>
                <span id="displayStatusBadge" class="status-badge">
                    ...
                </span>
            </div>

            <div class="tracking-steps">
                <div class="step" id="step-placed">
                    <div class="step-icon"><i class="fa-solid fa-box"></i></div>
                    <div class="step-text">Placed</div>
                </div>
                <div class="step" id="step-processing">
                    <div class="step-icon"><i class="fa-solid fa-cog"></i></div>
                    <div class="step-text">Processing</div>
                </div>
                <div class="step" id="step-shipped">
                    <div class="step-icon"><i class="fa-solid fa-truck"></i></div>
                    <div class="step-text">Shipped</div>
                </div>
                <div class="step" id="step-delivered">
                    <div class="step-icon"><i class="fa-solid fa-house"></i></div>
                    <div class="step-text">Delivered</div>
                </div>
            </div>
        </div>

        <!-- تفاصيل الطلب -->
        <div class="order-content">

            <!-- المنتجات -->
            <div class="items-card">
                <h3>Items Ordered</h3>
                <div id="displayOrderItems">
                    <!-- Items will be injected here -->
                </div>
            </div>

            <!-- معلومات الشحن والدفع -->
            <div class="summary-side">
                <!-- معلومات التوصيل -->
                <div class="info-card">
                    <h4><i class="fa-solid fa-truck" style="color:var(--pink); margin-right:5px;"></i> Shipping Info</h4>
                    <div class="info-row">
                        <span>Address</span>
                        <strong id="displayAddress" style="text-align: right; max-width: 150px;">...</strong>
                    </div>
                    <div class="info-row">
                        <span>Phone</span>
                        <strong id="displayPhone">...</strong>
                    </div>
                </div>

                <!-- بطاقة الهدية (مخفية افتراضياً) -->
                <div class="info-card gift-card-display" id="giftDetailsCard" style="display: none;">
                    <h4><i class="fa-solid fa-gift" style="color:var(--pink); margin-right:5px;"></i> Gift Details</h4>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                        <div id="giftWrapImageContainer"></div>
                        <div style="font-size: 13px; color: var(--secondary-text);">Wrapped as a gift</div>
                    </div>
                    <div id="giftMessageContainer" class="gift-message-box"></div>
                </div>

                <!-- ملخص الفاتورة -->
                <div class="info-card">
                    <h4><i class="fa-solid fa-receipt" style="color:var(--lavender); margin-right:5px;"></i> Total Summary</h4>
                    <div class="info-row">
                        <span>Subtotal</span>
                        <span id="displaySubtotal">$0.00</span>
                    </div>
                    <div class="info-row">
                        <span>Shipping</span>
                        <span id="displayShipping" style="color: #28a745;">Free</span>
                    </div>
                    <div class="info-row total-row">
                        <span>Total</span>
                        <span id="displayTotal" style="color:var(--pink);">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- حالة الخطأ -->
    <div id="errorState" class="error-state" style="display:none;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <h3>Order Not Found</h3>
        <p>We couldn't find the order you are looking for.</p>
        <a href="profile.php?tab=orders" class="back-btn">Back to Orders</a>
    </div>

</div>

<!-- --- بداية قسم JavaScript --- -->
<script>
    // بيانات وهمية للمنتجات للصور
    const phpProducts = <?php echo json_encode($products ?? []); ?>;
    const customProducts = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};
    const savedDesignsArray = JSON.parse(localStorage.getItem('teddy_saved_designs')) || [];
    const savedDesignsMap = {};
    savedDesignsArray.forEach(item => savedDesignsMap[item.id] = item);

    const allProducts = { ...phpProducts, ...savedDesignsMap, ...customProducts };

    // دالة مساعدة للحصول على اسم الكلاس من مسار الصورة
    function getImgClass(imgSrc) {
        if (!imgSrc) return '';
        const fileName = imgSrc.split('/').pop().split('.').shift();
        return 'img-' + fileName;
    }

    window.addEventListener('load', () => {
        // 1. الحصول على الـ ID من الرابط
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('id');

        // 2. جلب الطلبات من التخزين
        const orders = JSON.parse(localStorage.getItem('teddy_orders')) || [];

        // 3. البحث عن الطلب
        const order = orders.find(o => o.id === orderId);

        if (order) {
            document.getElementById('displayOrderId').innerText = order.id;
            document.getElementById('displayOrderDate').innerText = order.date;

            // Status Badge
            const badge = document.getElementById('displayStatusBadge');
            badge.innerText = order.status;
            badge.className = 'status-badge status-' + order.status.toLowerCase();

            // Tracking Steps Logic
            updateTrackingSteps(order.status);

            // Items
            const itemsContainer = document.getElementById('displayOrderItems');
            let itemsHtml = '';
            let subtotal = 0;

            order.items.forEach(item => {
                const productData = allProducts[item.id] || {};
                // استخدام config من العنصر نفسه أو من البيانات العامة
                const itemConfig = item.config || productData.config;
                const itemTotal = parseFloat(item.price) * item.qty;
                subtotal += itemTotal;

                // بناء صورة المنتج (عادية أو مركبة)
                let imgHtml = '';

                // === التعديل هنا: التحقق إذا كان المنتج مخصص لوضع رابط عليه ===
                if (itemConfig) {
                    // منتج مخصص: نعرض طبقات متعددة ونحيطها برابط
                    // استخدام item.id للرابط
                    imgHtml = `
                        <a href="custom_details.php?id=${item.id}" style="display: contents; cursor: pointer;" title="View Customization">
                            <div class="item-img customized-preview">
                                <img src="${itemConfig.color.img}" class="preview-base" alt="Base">
                                ${itemConfig.outfit ? `<img src="${itemConfig.outfit.img}" class="preview-outfit ${getImgClass(itemConfig.outfit.img)}" alt="Outfit">` : ''}
                                ${itemConfig.shoes ? `<img src="${itemConfig.shoes.img}" class="preview-shoes ${getImgClass(itemConfig.shoes.img)}" alt="Shoes">` : ''}
                                ${itemConfig.acc ? `<img src="${itemConfig.acc.img}" class="preview-acc ${getImgClass(itemConfig.acc.img)}" alt="Accessory">` : ''}
                            </div>
                        </a>
                    `;
                } else {
                    // منتج عادي: لا يوجد رابط
                    const imgSrc = item.image || productData.image || 'https://via.placeholder.com/70';
                    imgHtml = `
                        <div class="item-img">
                            <img src="${imgSrc}" alt="${item.name}">
                        </div>
                    `;
                }

                itemsHtml += `
                    <div class="item-row">
                        ${imgHtml}
                        <div class="item-info">
                            <h4>${item.name}</h4>
                            <p>Qty: ${item.qty}</p>
                        </div>
                        <div class="item-price">
                            <span>$${itemTotal.toFixed(2)}</span>
                        </div>
                    </div>
                `;
            });
            itemsContainer.innerHTML = itemsHtml;

            // Summary
            document.getElementById('displaySubtotal').innerText = `$${subtotal.toFixed(2)}`;
            document.getElementById('displayShipping').innerText = order.shipping == 0 ? 'Free' : `$${order.shipping}`;
            document.getElementById('displayTotal').innerText = `$${order.total}`;

            // Shipping Info
            document.getElementById('displayAddress').innerText = order.address || 'N/A';
            document.getElementById('displayPhone').innerText = order.phone || 'N/A';

            // === Gift Details Logic ===
            if (order.gift) {
                const giftCard = document.getElementById('giftDetailsCard');
                const msgContainer = document.getElementById('giftMessageContainer');
                const imgContainer = document.getElementById('giftWrapImageContainer');

                giftCard.style.display = 'block';

                if (order.gift_message) {
                    msgContainer.innerText = order.gift_message;
                } else {
                    msgContainer.style.display = 'none';
                }

                if (order.gift_wrap) {
                    imgContainer.innerHTML = `<img src="${order.gift_wrap}" class="gift-wrap-preview" alt="Gift Wrap">`;
                } else {
                    imgContainer.style.display = 'none';
                }
            }

        } else {
            // عرض رسالة خطأ إذا لم يتم العثور على الطلب
            document.getElementById('orderContentWrapper').style.display = 'none';
            document.getElementById('errorState').style.display = 'block';
        }
    });

    function updateTrackingSteps(status) {
        const steps = ['placed', 'processing', 'shipped', 'delivered'];
        const statusIndex = steps.indexOf(status.toLowerCase());

        steps.forEach((stepName, index) => {
            const stepEl = document.getElementById('step-' + stepName);
            if (!stepEl) return;

            stepEl.classList.remove('completed', 'active');

            const icon = stepEl.querySelector('.step-icon i');

            if (index < statusIndex) {
                stepEl.classList.add('completed');
                // تغيير الأيقونة لعلامة صح
                icon.className = 'fa-solid fa-check';
            } else if (index === statusIndex) {
                stepEl.classList.add('active');
                // إعادة الأيقونة الأصلية
                if (stepName === 'placed') icon.className = 'fa-solid fa-box';
                else if (stepName === 'processing') icon.className = 'fa-solid fa-cog';
                else if (stepName === 'shipped') icon.className = 'fa-solid fa-truck';
                else if (stepName === 'delivered') icon.className = 'fa-solid fa-house';
            } else {
                // إعادة الأيقونة الأصلية للحالات المستقبلية
                if (stepName === 'placed') icon.className = 'fa-solid fa-box';
                else if (stepName === 'processing') icon.className = 'fa-solid fa-cog';
                else if (stepName === 'shipped') icon.className = 'fa-solid fa-truck';
                else if (stepName === 'delivered') icon.className = 'fa-solid fa-house';
            }
        });
    }
</script>
<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>