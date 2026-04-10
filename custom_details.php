<?php
$pageTitle = "Custom Teddy Details | Teddy Lap";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- الخطوط والأيقونات -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- بداية قسم CSS -->
    <style>
        /* خلفية متحركة */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }

        /* الحاوية الرئيسية */
        .details-container { padding: 120px 20px 50px; max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 50px; align-items: center; opacity: 0; animation: fadeIn 0.8s forwards; }
        @media (max-width: 900px) { .details-container { grid-template-columns: 1fr; padding-top: 100px; gap: 30px; } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* قسم الصورة */
        .product-image-section { position: relative; background: var(--card-bg); border-radius: 30px; padding: 40px; box-shadow: 0 15px 40px var(--shadow); display: flex; justify-content: center; align-items: center; min-height: 450px; }

        /* حاوية الطبقات (الصورة المركبة) */
        .image-layer-container {
            position: relative;
            width: 100%;
            max-width: 350px;
            height: 400px;
        }
        .layer-img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15));
        }

        /* أنماط الصور المركبة */
        .customized-preview {
            position: relative;
            width: 100%;
            height: 100%;
            background: #f8f8f8;
            border-radius: 10px;
            overflow: hidden;
        }
        .customized-preview img {
            position: absolute;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: all 0.3s;
        }
        .customized-preview .preview-base {
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 1;
        }
        .customized-preview .preview-outfit {
            width: 70%;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
        }
        .customized-preview .preview-shoes {
            width: 60%;
            top: 85%;
            left: 48%;
            transform: translate(-50%, -50%);
            z-index: 3;
        }
        .customized-preview .preview-acc {
            width: 26%;
            top: 18%;
            left: 15%;
            transform: translate(-50%, -50%);
            z-index: 4;
        }

        /* تعديلات خاصة بكل قطعة لبس */
        .customized-preview .preview-outfit.img-reddress { width: 60%; top: 55%; }
        .customized-preview .preview-outfit.img-pinkoutfit { width: 55%; top: 52%; }
        .customized-preview .preview-outfit.img-greenoutfit { width: 50%; top: 52%; }
        .customized-preview .preview-outfit.img-jeansoutfit { width: 50%; top: 55%; }

        .customized-preview .preview-shoes.img-redshoes { width: 40%; top: 95%; left: 49%; }
        .customized-preview .preview-shoes.img-darkshoes { width: 45%; top: 91%; left: 49%; }
        .customized-preview .preview-shoes.img-pinkshoes { width: 45%; top: 95%; left: 49%; }
        .customized-preview .preview-shoes.img-conversshoes { width: 43%; top: 92%; left: 49%; }

        .custom-badge-detail { position: absolute; top: 20px; left: 20px; background: linear-gradient(45deg, #a29bfe, #6c5ce7); color: #fff; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 14px; z-index: 10; box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3); }

        /* القسم الأيمن (المعلومات) */
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--secondary-text); text-decoration: none; font-weight: 500; margin-bottom: 20px; transition: color 0.2s; }
        .back-link:hover { color: var(--pink); }
        .product-name { font-family: 'Playfair Display', serif; font-size: 36px; color: var(--text-color); margin: 0 0 10px; line-height: 1.2; }
        .product-price { font-size: 28px; color: var(--pink); font-weight: 700; margin-bottom: 25px; }
        .section-divider { height: 1px; background: #eee; margin: 20px 0; }
        body.dark-mode .section-divider { background: #444; }
        .specs-title { font-size: 18px; color: var(--text-color); margin-bottom: 15px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .specs-title i { color: var(--pink); }

        .specs-list { list-style: none; padding: 0; margin: 0 0 30px 0; }
        .spec-item { display: flex; align-items: center; padding: 12px 0; border-bottom: 1px dashed #f0f0f0; }
        body.dark-mode .spec-item { border-bottom-color: #333; }
        .spec-icon { width: 40px; height: 40px; background: var(--bg-color); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--pink); margin-right: 15px; font-size: 16px; }
        .spec-info { flex: 1; }
        .spec-label { font-size: 12px; color: var(--secondary-text); margin-bottom: 2px; }
        .spec-value { font-weight: 600; color: var(--text-color); font-size: 15px; }

        .error-state { grid-column: 1 / -1; text-align: center; padding: 80px 20px; }
        .error-state i { font-size: 60px; color: var(--pink); margin-bottom: 20px; }

        /* زر الصوت */
        .voice-player-container { display: flex; align-items: center; gap: 12px; }
        .play-voice-btn { background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 3px 8px rgba(255, 154, 158, 0.4); transition: transform 0.2s; }
        .play-voice-btn:hover { transform: scale(1.1); }
        .play-voice-btn.playing { background: linear-gradient(45deg, #a29bfe, #6c5ce7); animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(162, 155, 254, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(162, 155, 254, 0); } 100% { box-shadow: 0 0 0 0 rgba(162, 155, 254, 0); } }
    </style>
</head>
<body>

<!-- أشكال الخلفية -->
<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<!-- النافبار -->
<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<!-- حاوية التفاصيل -->
<div class="details-container" id="detailsContainer"></div>
<audio id="audioPlayer" style="display:none;"></audio>

<!-- بداية قسم JavaScript -->
<script>
    // دالة مساعدة للحصول على اسم الكلاس من مسار الصورة
    function getImgClass(imgSrc) {
        if (!imgSrc) return '';
        const fileName = imgSrc.split('/').pop().split('.').shift();
        return 'img-' + fileName;
    }

    // تعريف أنماط مخصصة لكل قطعة
    const itemStyles = {
        // أكسسوارات
        'redacc.png': { width: '90px', top: '18%', left: '10%' },
        'ball.png': { width: '80px', top: '20%', left: '12%' },
        'sunglasses.png': { width: '70px', top: '22%', left: '12%' },
        'camera.png': { width: '85px', top: '19%', left: '15%' },

        // ألبسة
        'reddress.png': { width: '72%', top: '60%', left: '50%' },
        'pinkoutfit.png': { width: '65%', top: '55%', left: '50%' },
        'greenoutfit.png': { width: '65%', top: '57%', left: '50%' },
        'jeansoutfit.png': { width: '62%', top: '60%', left: '50%' },

        // أحذية
        'redshoes.png': { width: '50%', top: '95%', left: '48%' },
        'darkshoes.png': { width: '57%', top: '92%', left: '47%' },
        'pinkshoes.png': { width: '57%', top: '92%', left: '49%' },
        'conversshoes.png': { width: '55%', top: '92%', left: '48%' }
    };

    // دالة لاستخراج الأنماط من itemStyles
    function getStyleForImage(imgSrc) {
        if (!imgSrc) return null;
        const fileName = imgSrc.split('/').pop();
        return itemStyles[fileName] || null;
    }

    const container = document.getElementById('detailsContainer');
    const audioPlayer = document.getElementById('audioPlayer');
    const urlParams = new URLSearchParams(window.location.search);
    const teddyId = urlParams.get('id');

    // دالة لجلب بيانات الدبدوب من التخزين المحلي
    function getTeddyData(id) {
        // البحث في العناصر المؤقتة
        let customItemsRaw = localStorage.getItem('teddy_custom_items');
        if (customItemsRaw) {
            let items = JSON.parse(customItemsRaw);
            if (Array.isArray(items) && items.find(i => i.id === id)) return items.find(i => i.id === id);
            if (typeof items === 'object' && items[id]) return items[id];
        }

        // البحث في المحفوظات
        let savedRaw = localStorage.getItem('teddy_saved_designs');
        if (savedRaw) {
            let items = JSON.parse(savedRaw);
            let found = items.find(i => i.id === id);
            if (found) return found;
        }

        // البحث في الطلبات السابقة
        let ordersRaw = localStorage.getItem('teddy_orders');
        if (ordersRaw) {
            let orders = JSON.parse(ordersRaw);
            for (let order of orders) {
                if (order.items) {
                    let foundItem = order.items.find(i => i.id === id);
                    if (foundItem) return foundItem;
                }
            }
        }
        return null;
    }

    let teddyData = getTeddyData(teddyId);

    // التحقق من وجود البيانات وعرضها
    if (!teddyId || !teddyData) {
        container.innerHTML = `<div class="error-state"><i class="fa-solid fa-box-open"></i><h2>Teddy Not Found</h2><p style="color:var(--secondary-text);">This custom design might have been deleted.</p><a href="shop.php" class="btn-main btn-cart" style="width:auto; display:inline-flex;">Back to Shop</a></div>`;
    } else {
        // بناء الصورة المركبة
        let imageHtml = '';
        if (teddyData.config) {
            // دالة مساعدة لبناء HTML للطبقة
            function buildLayerHtml(src, baseClass) {
                if (!src) return '';
                const styles = getStyleForImage(src);
                const styleAttr = styles ? `style="width: ${styles.width}; top: ${styles.top}; left: ${styles.left};"` : '';
                const imgClass = getImgClass(src);
                return `<img src="${src}" class="${baseClass} ${imgClass}" ${styleAttr} alt="${baseClass}">`;
            }

            imageHtml = `
                <div class="customized-preview">
                    <img src="${teddyData.config.color.img}" class="preview-base" alt="Base">
                    ${teddyData.config.outfit ? buildLayerHtml(teddyData.config.outfit.img, 'preview-outfit') : ''}
                    ${teddyData.config.shoes ? buildLayerHtml(teddyData.config.shoes.img, 'preview-shoes') : ''}
                    ${teddyData.config.acc ? buildLayerHtml(teddyData.config.acc.img, 'preview-acc') : ''}
                </div>
            `;
        } else {
            // منتج عادي
            imageHtml = `<img src="${teddyData.image}" class="layer-img" alt="${teddyData.name}">`;
        }

        // بناء قائمة المواصفات
        let specsHtml = '';
        const icons = {
            'Color': 'fa-palette', 'Outfit': 'fa-shirt', 'Shoes': 'fa-shoe-prints',
            'Accessory': 'fa-wand-magic-sparkles', 'Name': 'fa-signature', 'Sound': 'fa-music'
        };

        if (teddyData.config) {
            // بناء من config
            if (teddyData.config.color) {
                specsHtml += `<li class="spec-item"><div class="spec-icon"><i class="fa-solid fa-palette"></i></div><div class="spec-info"><span class="spec-label">Color</span><span class="spec-value">${teddyData.config.color.name}</span></div></li>`;
            }
            if (teddyData.config.outfit) {
                specsHtml += `<li class="spec-item"><div class="spec-icon"><i class="fa-solid fa-shirt"></i></div><div class="spec-info"><span class="spec-label">Outfit</span><span class="spec-value">${teddyData.config.outfit.name}</span></div></li>`;
            }
            if (teddyData.config.shoes) {
                specsHtml += `<li class="spec-item"><div class="spec-icon"><i class="fa-solid fa-shoe-prints"></i></div><div class="spec-info"><span class="spec-label">Shoes</span><span class="spec-value">${teddyData.config.shoes.name}</span></div></li>`;
            }
            if (teddyData.config.acc) {
                specsHtml += `<li class="spec-item"><div class="spec-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div><div class="spec-info"><span class="spec-label">Accessory</span><span class="spec-value">${teddyData.config.acc.name}</span></div></li>`;
            }
            if (teddyData.config.name) {
                specsHtml += `<li class="spec-item"><div class="spec-icon"><i class="fa-solid fa-signature"></i></div><div class="spec-info"><span class="spec-label">Name</span><span class="spec-value">${teddyData.config.name}</span></div></li>`;
            }
        } else if (teddyData.description) {
            // استخدام description كاحتياط
            const descParts = {};
            teddyData.description.split(',').forEach(part => {
                const [key, value] = part.split(':');
                if(key && value) descParts[key.trim()] = value.trim();
            });
            Object.keys(descParts).forEach(key => {
                if(key !== 'Voice'){
                    specsHtml += `<li class="spec-item"><div class="spec-icon"><i class="fa-solid ${icons[key] || 'fa-circle'}"></i></div><div class="spec-info"><span class="spec-label">${key}</span><span class="spec-value">${descParts[key]}</span></div></li>`;
                }
            });
        }

        // إضافة عنصر الصوت
        if (teddyData.voice || teddyData.config?.sound) {
            const voiceSrc = teddyData.voice || teddyData.config?.sound;
            if (voiceSrc) {
                // تخزين الصوت في window للوصول إليه لاحقاً
                const voiceId = 'voice_' + teddyId;
                window[voiceId] = voiceSrc;

                specsHtml += `
                    <li class="spec-item">
                        <div class="spec-icon"><i class="fa-solid fa-microphone-lines"></i></div>
                        <div class="spec-info">
                            <span class="spec-label">Voice Message</span>
                            <div class="voice-player-container">
                                <span class="spec-value">Recorded Audio</span>
                                <button class="play-voice-btn" data-voice-id="${voiceId}" onclick="toggleVoice(this)" title="Play Voice">
                                    <i class="fa-solid fa-headphones"></i>
                                </button>
                            </div>
                        </div>
                    </li>
                `;
            }
        }

        // بناء الصفحة كاملة
        container.innerHTML = `
            <div class="product-image-section">
                <div class="custom-badge-detail"><i class="fa-solid fa-magic"></i> Custom Made</div>
                <div class="image-layer-container">
                    ${imageHtml}
                </div>
            </div>
            <div class="product-info-section">
                <a href="javascript:history.back()" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <h1 class="product-name">${teddyData.name}</h1>
                <div class="product-price">$${teddyData.price}</div>
                <div class="section-divider"></div>
                <div class="specs-title"><i class="fa-solid fa-list-check"></i> Customization Details</div>
                <ul class="specs-list">${specsHtml}</ul>
            </div>
        `;
    }

    // دالة تشغيل وإيقاف الصوت
    function toggleVoice(button) {
        const voiceId = button.dataset.voiceId;
        const src = window[voiceId];

        if (!src) return;

        if (audioPlayer.src === src && !audioPlayer.paused) {
            audioPlayer.pause();
            button.classList.remove('playing');
            button.innerHTML = '<i class="fa-solid fa-headphones"></i>';
        } else {
            audioPlayer.src = src;
            audioPlayer.play();
            button.classList.add('playing');
            button.innerHTML = '<i class="fa-solid fa-pause"></i>';
        }
    }

    // حدث انتهاء الصوت
    audioPlayer.onended = function() {
        const btn = document.querySelector('.play-voice-btn.playing');
        if(btn) { btn.classList.remove('playing'); btn.innerHTML = '<i class="fa-solid fa-headphones"></i>'; }
    };
</script>

<!-- الفوتر -->
<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>