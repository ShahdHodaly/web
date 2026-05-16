<?php
// custom-teddies.php
session_start();
require_once 'db.php';

$pdo = getDB();

// جلب العناصر من قاعدة البيانات (ألوان + ملابس + إكسسوارات)
// 1. جلب ألوان الدب من جدول teddy_colors
$stmt = $pdo->query("SELECT color_id as id, name, image, 'color' as type, 0 as price FROM teddy_colors ORDER BY color_id");
$colors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. جلب الملابس والإكسسوارات والأحذية من جدول clothing_items
$stmt = $pdo->query("
    SELECT 
        item_id as id, 
        name, 
        type, 
        image, 
        price,
        CASE 
            WHEN type = 'outfit' THEN 'outfit'
            WHEN type = 'shoes' THEN 'shoes'
            WHEN type = 'accessory' THEN 'accessory'
        END as category
    FROM clothing_items 
    ORDER BY item_id
");
$clothes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دمج جميع العناصر في مصفوفة واحدة
$allItems = array_merge($colors, $clothes);

// تحويل إلى نفس تنسيق JavaScript المتوقع
$clothesItems = [];
$teddyColors = [];

foreach ($allItems as $item) {
    if ($item['type'] === 'color') {
        $teddyColors[$item['id']] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'category' => 'color',
                'image' => $item['image'],
                'price' => (float)$item['price'],
                'type' => 'color'
        ];
    } else {
        $clothesItems[$item['id']] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'category' => $item['category'],
                'image' => $item['image'],
                'price' => (float)$item['price'],
                'type' => $item['type']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Teddy · Teddy Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* جميع التنسيقات كما هي في الكود الأصلي */
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; align-items: flex-start; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); box-sizing: border-box; }

        .filter-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        .filter-btn {
            padding: 10px 24px;
            background: var(--card-bg);
            border: 2px solid transparent;
            border-radius: 40px;
            color: var(--text-color);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .filter-btn i { color: var(--primary); }
        .filter-btn:hover { background: var(--pink); color: #000; transform: translateY(-2px); }
        .filter-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .filter-btn.active i { color: #fff; }

        .table-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 30px;
            overflow-x: auto;
            animation: fadeInUp 0.6s ease;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            text-align: left;
            padding: 15px 12px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .items-table tbody tr:hover td { background-color: rgba(248, 187, 208, 0.1); }
        .items-table tbody tr.selected { background-color: rgba(248, 187, 208, 0.3); border-left: 3px solid var(--pink); }
        .item-img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        tr:hover .item-img { transform: scale(1.1); }
        .category-badge {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-outfit { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .badge-shoes { background: rgba(33, 150, 243, 0.2); color: #2196F3; }
        .badge-accessory { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .badge-color { background: rgba(156, 39, 176, 0.2); color: #9C27B0; }
        .price-value { font-weight: 600; color: var(--primary); }

        .action-buttons { display: flex; gap: 8px; }
        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-color);
            color: var(--secondary-text);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .action-btn:hover {
            background: var(--pink);
            color: #000;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .action-btn.edit:hover { background: var(--primary); }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }

        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .pagination-btn {
            padding: 8px 16px;
            background: var(--card-bg);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 8px;
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .pagination-btn:hover:not(:disabled) { background: var(--pink); color: #000; transform: translateY(-2px); }
        .pagination-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .page-numbers { display: flex; gap: 5px; flex-wrap: wrap; }
        .page-number {
            padding: 8px 12px;
            background: var(--card-bg);
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 50%;
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 40px;
            text-align: center;
        }
        .page-number:hover { background: var(--pink); color: #000; }
        .page-number.active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .items-info { text-align: center; margin-top: 15px; color: var(--secondary-text); font-size: 14px; }

        .preview-area {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-top: 30px;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            scroll-margin-top: 100px;
        }
        .preview-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .preview-title i { color: var(--pink); font-size: 24px; }

        .teddy-stage {
            position: relative;
            width: 350px;
            height: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .teddy-container {
            position: relative;
            width: 100%;
            height: 100%;
            animation: floatBear 3s ease-in-out infinite;
        }
        @keyframes floatBear {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .layer-base, .layer-item {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 10;
            transition: all 0.4s ease;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15));
        }
        .layer-base { width: 75%; height: 100%; object-fit: contain; object-position: center; }
        .layer-item { opacity: 0; pointer-events: none; }
        .layer-item.active { opacity: 1; }
        #previewAcc {
            width: 20%;
            height: auto;
            left: 35PX;
            top: 18%;
            transform: translate(-50%, -50%);
            z-index: 5;
        }
        #previewOutfit {
            width: 80%;
            height: auto;
            top: 250PX;
            left: 175PX;
            transform: translate(-50%, -50%);
            z-index: 15;
        }
        #previewShoes {
            width: 55%;
            height: auto;
            top: 95%;
            left: 48%;
            transform: translate(-50%, -50%);
            z-index: 16;
        }
        .teddy-platform {
            margin-top: 20px;
            text-align: center;
            padding: 10px 25px;
            background: linear-gradient(135deg, #f0f0f0, #e8e8e8);
            border-radius: 40px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .teddy-platform span { font-weight: 600; color: var(--primary); }
        .preview-info {
            margin-top: 20px;
            text-align: center;
            padding: 12px 20px;
            background: rgba(248, 187, 208, 0.15);
            border-radius: 30px;
        }
        .action-buttons-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .custom-btn {
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-reset { background: #ff6b6b; color: white; }
        .btn-reset:hover { background: #ff4757; transform: translateY(-2px); }
        .btn-screenshot { background: #4CAF50; color: white; }
        .btn-screenshot:hover { background: #45a049; transform: translateY(-2px); }
        .btn-add { background: var(--lavender); color: #000; }
        .btn-add:hover { background: var(--primary); color: white; transform: translateY(-2px); }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes highlightPulse {
            0% { box-shadow: 0 0 0 0 var(--pink); }
            70% { box-shadow: 0 0 0 15px rgba(248, 187, 208, 0); }
            100% { box-shadow: 0 0 0 0 rgba(248, 187, 208, 0); }
        }
        .preview-highlight { animation: highlightPulse 0.8s ease-out; }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .action-buttons-row { flex-direction: column; align-items: center; }
            .custom-btn { width: 100%; justify-content: center; }
            .teddy-stage { width: 280px; height: 380px; }
            #previewOutfit { width: 78%; top: 46%; }
            #previewShoes { width: 50%; top: 78%; }
            #previewAcc { width: 60px; left: 5%; top: 18%; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-main">
        <div class="main-header" style="animation: fadeInDown 0.6s ease;">
            <div><h1 style="margin-bottom: 5px;">Custom Teddy</h1><p style="color: var(--secondary-text);">Design your own teddy bear</p></div>
        </div>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">
                <i class="fa-solid fa-grid-2"></i> All
            </button>
            <button class="filter-btn" data-filter="outfit">
                <i class="fa-solid fa-shirt"></i> Outfits
            </button>
            <button class="filter-btn" data-filter="shoes">
                <i class="fa-solid fa-shoe-prints"></i> Shoes
            </button>
            <button class="filter-btn" data-filter="accessory">
                <i class="fa-solid fa-gem"></i> Accessories
            </button>
            <button class="filter-btn" data-filter="color">
                <i class="fa-solid fa-palette"></i> Colors
            </button>
        </div>

        <!-- Items Table with Pagination -->
        <div class="table-container">
            <table class="items-table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="itemsTableBody">
                <!-- العناصر رح تتحط هنا عن طريق JavaScript -->
                </tbody>
            </table>

            <!-- Pagination Controls -->
            <div class="pagination-container" id="paginationContainer">
                <button class="pagination-btn" id="prevPageBtn" disabled>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="page-numbers" id="pageNumbers"></div>
                <button class="pagination-btn" id="nextPageBtn">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="items-info" id="itemsInfo"></div>
        </div>

        <!-- Teddy Preview Area -->
        <div class="preview-area" id="previewArea">
            <div class="preview-title"><i class="fa-solid fa-bear"></i> Teddy Preview</div>
            <div class="teddy-stage" id="teddyStage">
                <div class="teddy-container" id="teddyContainer">
                    <img src="images/brown.png" class="layer-base" id="previewBase" alt="Teddy Bear">
                    <img src="" class="layer-item" id="previewOutfit" alt="Outfit">
                    <img src="" class="layer-item" id="previewShoes" alt="Shoes">
                    <img src="" class="layer-item" id="previewAcc" alt="Accessory">
                </div>
                <div class="teddy-platform"><span>✨ Click any item to customize ✨</span></div>
            </div>
            <div class="preview-info" id="previewInfo">
                <p>Select an item from the table to customize your teddy</p>
            </div>
            <div class="action-buttons-row">
                <button class="custom-btn btn-reset" id="resetBtn">
                    <i class="fa-solid fa-undo-alt"></i> Reset All
                </button>
                <button class="custom-btn btn-screenshot" id="screenshotBtn">
                    <i class="fa-solid fa-camera"></i> Save as Image
                </button>
                <a href="add-item.php" class="custom-btn btn-add">
                    <i class="fa-solid fa-plus"></i> Add New Item
                </a>
            </div>
        </div>
    </main>
</div>

<script>
    // ========== بيانات العناصر من قاعدة البيانات ==========
    const clothesItems = <?php echo json_encode($clothesItems); ?>;
    const teddyColors = <?php echo json_encode($teddyColors); ?>;

    let allItems = [...Object.values(clothesItems), ...Object.values(teddyColors)];
    let currentFilter = 'all';
    let currentSelections = {
        color: 'brown.png',
        outfit: null,
        accessory: null,
        shoes: null
    };

    // Pagination variables
    let currentPage = 1;
    let itemsPerPage = 8;
    let filteredItems = [];

    // ========== عرض الجدول مع Pagination ==========
    function displayTable() {
        // تصفية العناصر
        if (currentFilter !== 'all') {
            filteredItems = allItems.filter(item => item.type === currentFilter);
        } else {
            filteredItems = [...allItems];
        }

        // حساب pagination
        const totalItems = filteredItems.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const currentItems = filteredItems.slice(startIndex, endIndex);

        // عرض العناصر
        const tbody = document.getElementById('itemsTableBody');
        let html = '';

        currentItems.forEach(item => {
            const isSelected = checkIfSelected(item);
            const selectedClass = isSelected ? 'selected' : '';
            const badgeClass = item.type === 'outfit' ? 'badge-outfit' : (item.type === 'shoes' ? 'badge-shoes' : (item.type === 'accessory' ? 'badge-accessory' : 'badge-color'));

            // ✅ تحديد محتوى عمود السعر (Price) - للألوان تظهر "Free"
            let priceHtml = '';
            if (item.type === 'color') {
                priceHtml = '<span style="color: #4CAF50; font-weight: 500;"><i class="fa-regular fa-gem"></i> Free</span>';
            } else {
                priceHtml = `<span class="price-value">$${item.price.toFixed(2)}</span>`;
            }

            // ✅ تحديد محتوى عمود الإجراءات (Actions)
            let actionsHtml = '';
            if (item.type === 'color') {
                // للألوان: عرض زر حذف فقط (بدون تعديل)
                actionsHtml = `
                    <button class="action-btn delete" onclick="event.stopPropagation(); deleteItem(${item.id})" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
            } else {
                // للملابس والأحذية والإكسسوارات: عرض زر تعديل وحذف
                actionsHtml = `
                    <button class="action-btn edit" onclick="event.stopPropagation(); editItem(${item.id})" title="Edit Price">
                        <i class="fa-solid fa-dollar-sign"></i>
                    </button>
                    <button class="action-btn delete" onclick="event.stopPropagation(); deleteItem(${item.id})" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
            }

            html += `
                <tr data-id="${item.id}" data-name="${item.name}" data-image="${item.image}" data-type="${item.type}" data-price="${item.price}" class="${selectedClass}" onclick="applyItem(this)">
                    <td><strong>${escapeHtml(item.name)}</strong></td>
                    <td><span class="category-badge ${badgeClass}">${item.category.charAt(0).toUpperCase() + item.category.slice(1)}</span></td>
                    <td><img src="images/${item.image}" class="item-img" onerror="this.src='images/placeholder.png'"></td>
                    <td class="price-value">${priceHtml}</td>
                    <td class="action-buttons">
                        ${actionsHtml}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // تحديث معلومات pagination
        updatePaginationControls(totalItems, totalPages);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function updatePaginationControls(totalItems, totalPages) {
        const startNum = (currentPage - 1) * itemsPerPage + 1;
        const endNum = Math.min(currentPage * itemsPerPage, totalItems);

        document.getElementById('itemsInfo').innerHTML = `Showing ${startNum} - ${endNum} of ${totalItems} items`;

        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;

        const pageNumbersDiv = document.getElementById('pageNumbers');
        let pageNumbersHtml = '';

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            pageNumbersHtml += `<div class="page-number" data-page="1">1</div>`;
            if (startPage > 2) {
                pageNumbersHtml += `<div class="page-number disabled">...</div>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            pageNumbersHtml += `<div class="page-number ${activeClass}" data-page="${i}">${i}</div>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pageNumbersHtml += `<div class="page-number disabled">...</div>`;
            }
            pageNumbersHtml += `<div class="page-number" data-page="${totalPages}">${totalPages}</div>`;
        }

        pageNumbersDiv.innerHTML = pageNumbersHtml;

        document.querySelectorAll('.page-number').forEach(el => {
            if (!el.classList.contains('disabled')) {
                el.addEventListener('click', function() {
                    const page = parseInt(this.getAttribute('data-page'));
                    if (page && page !== currentPage) {
                        currentPage = page;
                        displayTable();
                        reapplyHighlights();
                    }
                });
            }
        });
    }

    function reapplyHighlights() {
        document.querySelectorAll('#itemsTableBody tr').forEach(row => {
            const type = row.getAttribute('data-type');
            const image = row.getAttribute('data-image');

            let isSelected = false;
            if (type === 'color') {
                isSelected = currentSelections.color === image;
            } else if (type === 'outfit') {
                isSelected = currentSelections.outfit === image;
            } else if (type === 'shoes') {
                isSelected = currentSelections.shoes === image;
            } else if (type === 'accessory') {
                isSelected = currentSelections.accessory === image;
            }

            if (isSelected) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        });
    }

    function checkIfSelected(item) {
        if (item.type === 'color') {
            return currentSelections.color === item.image;
        } else if (item.type === 'outfit') {
            return currentSelections.outfit === item.image;
        } else if (item.type === 'shoes') {
            return currentSelections.shoes === item.image;
        } else if (item.type === 'accessory') {
            return currentSelections.accessory === item.image;
        }
        return false;
    }

    // ========== تطبيق العنصر على الدب ==========
    function applyItem(row) {
        const type = row.getAttribute('data-type');
        const image = row.getAttribute('data-image');
        const name = row.getAttribute('data-name');

        if (type === 'color') {
            document.getElementById('previewBase').src = 'images/' + image;
            currentSelections.color = image;
        } else if (type === 'outfit') {
            const layer = document.getElementById('previewOutfit');
            layer.src = 'images/' + image;
            layer.classList.add('active');
            currentSelections.outfit = image;
        } else if (type === 'shoes') {
            const layer = document.getElementById('previewShoes');
            layer.src = 'images/' + image;
            layer.classList.add('active');
            currentSelections.shoes = image;
        } else if (type === 'accessory') {
            const layer = document.getElementById('previewAcc');
            layer.src = 'images/' + image;
            layer.classList.add('active');
            currentSelections.accessory = image;
        }

        document.querySelectorAll('#itemsTableBody tr').forEach(tr => {
            tr.classList.remove('selected');
        });
        row.classList.add('selected');

        updatePreviewInfo(name, type);
        scrollToPreview();
    }

    function updatePreviewInfo(itemName, type) {
        let infoText = `<p><strong style="color: var(--primary);">${escapeHtml(itemName)}</strong> applied to ${type}</p>`;
        infoText += `<p style="font-size: 13px; margin-top: 5px;">`;
        if (currentSelections.outfit) infoText += `👕 Outfit selected<br>`;
        if (currentSelections.shoes) infoText += `👟 Shoes selected<br>`;
        if (currentSelections.accessory) infoText += `💎 Accessory selected`;
        if (!currentSelections.outfit && !currentSelections.shoes && !currentSelections.accessory && currentSelections.color === 'brown.png') {
            infoText += `No items selected yet. Click on items to customize!`;
        }
        infoText += `</p>`;
        document.getElementById('previewInfo').innerHTML = infoText;
    }

    function scrollToPreview() {
        const previewArea = document.getElementById('previewArea');
        if (previewArea) {
            const offset = 100;
            const elementPosition = previewArea.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - offset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            previewArea.classList.add('preview-highlight');
            setTimeout(() => {
                previewArea.classList.remove('preview-highlight');
            }, 800);
        }
    }

    // ========== إعادة تعيين الدب ==========
    function resetTeddy() {
        document.getElementById('previewBase').src = 'images/brown.png';
        currentSelections.color = 'brown.png';

        const outfitLayer = document.getElementById('previewOutfit');
        outfitLayer.src = '';
        outfitLayer.classList.remove('active');
        currentSelections.outfit = null;

        const shoesLayer = document.getElementById('previewShoes');
        shoesLayer.src = '';
        shoesLayer.classList.remove('active');
        currentSelections.shoes = null;

        const accLayer = document.getElementById('previewAcc');
        accLayer.src = '';
        accLayer.classList.remove('active');
        currentSelections.accessory = null;

        document.querySelectorAll('#itemsTableBody tr').forEach(row => {
            row.classList.remove('selected');
        });

        document.getElementById('previewInfo').innerHTML = `<p>All items cleared. Select new items to customize!</p>`;

        const btn = document.getElementById('resetBtn');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Reset!';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
        }, 1000);

        scrollToPreview();
    }

    // ========== دوال التعديل والحذف ==========
    function editItem(itemId) {
        window.location.href = `edit-item.php?id=${itemId}`;
    }

    async function deleteItemFromDB(itemId, itemType) {
        try {
            const response = await fetch('delete-item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&item_type=${itemType}`
            });
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    function showAdminConfirm(message, onConfirm) {
        const overlay = document.createElement('div');
        overlay.id = 'admin-confirm-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
        overlay.style.backdropFilter = 'blur(3px)';
        overlay.style.zIndex = '9998';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        const popup = document.createElement('div');
        popup.id = 'admin-confirm-popup';
        popup.style.backgroundColor = 'var(--card-bg, #ffffff)';
        popup.style.color = 'var(--text-color, #333)';
        popup.style.borderRadius = '28px';
        popup.style.padding = '28px 24px';
        popup.style.maxWidth = '420px';
        popup.style.width = '90%';
        popup.style.boxShadow = '0 25px 45px rgba(0,0,0,0.25)';
        popup.style.textAlign = 'center';
        popup.style.fontFamily = "'Poppins', sans-serif";
        popup.style.transform = 'scale(0.9)';
        popup.style.transition = 'transform 0.25s ease';
        popup.style.border = '1px solid var(--pink, #F8BBD0)';

        popup.innerHTML = `
            <div style="font-size: 58px; margin-bottom: 12px;">⚠️</div>
            <h3 style="font-size: 24px; font-weight: 600; margin-bottom: 12px;">Are you sure?</h3>
            <p style="font-size: 16px; color: var(--secondary-text, #555); margin-bottom: 28px; line-height: 1.5;">${message}</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button id="confirm-cancel-btn" style="background: transparent; border: 2px solid var(--pink, #F8BBD0); padding: 10px 24px; border-radius: 40px; font-weight: 600; cursor: pointer; color: var(--text-color, #333); transition: all 0.2s;">Cancel</button>
                <button id="confirm-ok-btn" style="background: #d9534f; border: none; padding: 10px 28px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; box-shadow: 0 4px 8px rgba(217,83,79,0.3); transition: all 0.2s;">Delete</button>
            </div>
        `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        setTimeout(() => {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        }, 10);

        function closePopup() {
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            setTimeout(() => {
                if (overlay && overlay.parentNode) overlay.remove();
            }, 250);
        }

        function showSuccessToast() {
            const toast = document.createElement('div');
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-check-circle" style="font-size: 28px; color: #28a745;"></i>
                    <div><strong style="font-size: 18px;">Item removed successfully!</strong></div>
                </div>
            `;
            toast.style.position = 'fixed';
            toast.style.top = '50%';
            toast.style.left = '50%';
            toast.style.transform = 'translate(-50%, -50%) scale(0.9)';
            toast.style.backgroundColor = 'var(--card-bg, #fff)';
            toast.style.color = 'var(--text-color, #333)';
            toast.style.padding = '18px 28px';
            toast.style.borderRadius = '60px';
            toast.style.boxShadow = '0 20px 35px rgba(0,0,0,0.2)';
            toast.style.zIndex = '10000';
            toast.style.fontFamily = "'Poppins', sans-serif";
            toast.style.border = '2px solid #28a745';
            toast.style.backdropFilter = 'blur(12px)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1)';
            toast.style.fontWeight = '500';
            toast.style.textAlign = 'center';
            toast.style.minWidth = '280px';

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 20);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                setTimeout(() => {
                    if (toast && toast.parentNode) toast.remove();
                }, 250);
            }, 2500);
        }

        const cancelBtn = popup.querySelector('#confirm-cancel-btn');
        const confirmBtn = popup.querySelector('#confirm-ok-btn');

        cancelBtn.addEventListener('click', () => {
            closePopup();
        });

        confirmBtn.addEventListener('click', async () => {
            if (onConfirm && typeof onConfirm === 'function') {
                await onConfirm();
            }
            closePopup();
            showSuccessToast();
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePopup();
        });
    }

    function deleteItem(itemId) {
        // البحث عن نوع العنصر
        const item = allItems.find(i => i.id == itemId);
        if (!item) return;

        showAdminConfirm('Are you sure you want to delete this item?', async () => {
            const success = await deleteItemFromDB(itemId, item.type);
            if (success) {
                // إزالة العنصر من المصفوفة المحلية
                const index = allItems.findIndex(i => i.id == itemId);
                if (index !== -1) allItems.splice(index, 1);

                // إزالة من clothesItems أو teddyColors حسب النوع
                if (item.type === 'color') {
                    delete teddyColors[itemId];
                } else {
                    delete clothesItems[itemId];
                }

                // إعادة عرض الجدول
                displayTable();
                reapplyHighlights();
            }
        });
    }

    // ========== حفظ كصورة ==========
    async function captureScreenshot() {
        const container = document.getElementById('teddyContainer');
        const btn = document.getElementById('screenshotBtn');
        const originalHTML = btn.innerHTML;

        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Capturing...';
        btn.disabled = true;
        container.style.animation = "none";

        try {
            await new Promise(r => setTimeout(r, 100));
            const canvas = await html2canvas(container, {
                scale: 3,
                backgroundColor: '#FFF0F5',
                useCORS: true
            });

            const finalCanvas = document.createElement('canvas');
            const ctx = finalCanvas.getContext('2d');
            const padding = 170;
            finalCanvas.width = canvas.width + padding * 2;
            finalCanvas.height = canvas.height + padding * 2;

            ctx.fillStyle = '#FFF0F5';
            ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
            ctx.drawImage(canvas, padding, padding);

            const link = document.createElement('a');
            link.download = `teddy-design-${Date.now()}.png`;
            link.href = finalCanvas.toDataURL('image/png');
            link.click();
        } catch(e) {
            console.error(e);
        }

        container.style.animation = "floatBear 3s ease-in-out infinite";
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }

    // ========== الفلترة ==========
    function filterItems(type) {
        currentFilter = type;
        currentPage = 1;

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-filter') === type) {
                btn.classList.add('active');
            }
        });

        displayTable();
    }

    // ========== تهيئة الصفحة ==========
    document.addEventListener('DOMContentLoaded', function() {
        displayTable();

        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                filterItems(this.getAttribute('data-filter'));
            });
        });

        document.getElementById('prevPageBtn').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                displayTable();
                reapplyHighlights();
            }
        });

        document.getElementById('nextPageBtn').addEventListener('click', function() {
            const totalPages = Math.ceil(filteredItems.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                displayTable();
                reapplyHighlights();
            }
        });

        document.getElementById('resetBtn').addEventListener('click', resetTeddy);
        document.getElementById('screenshotBtn').addEventListener('click', captureScreenshot);
    });

    window.applyItem = applyItem;
    window.resetTeddy = resetTeddy;
    window.captureScreenshot = captureScreenshot;
    window.editItem = editItem;
    window.deleteItem = deleteItem;
</script>

<script>
    (function() {
        const themeSwitchMain = document.getElementById('themeSwitchSidebar');
        function applyTheme(isDark) {
            if (isDark) { document.body.classList.add('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = true; }
            else { document.body.classList.remove('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = false; }
        }
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true); else applyTheme(false);
        if (themeSwitchMain) themeSwitchMain.addEventListener('change', function(e) { applyTheme(this.checked); localStorage.setItem('theme', this.checked ? 'dark' : 'light'); });
    })();
</script>
</body>
</html>