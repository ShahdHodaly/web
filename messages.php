<?php
// messages.php
session_start();

// تضمين مصفوفة الرسائل
require_once 'messages-array.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6;
$totalMessages = count($messages);
$totalPages = ceil($totalMessages / $perPage);
$offset = ($page - 1) * $perPage;
$paginatedMessages = array_slice($messages, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages · Teddy Shop</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ملفات CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* تنسيقاتك الموجودة */
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        /* Stats Cards */
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 25px 0;
        }
        .stat-mini-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
            border: 1px solid transparent;
        }
        .stat-mini-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--shadow);
            border-color: var(--pink);
        }

        .stat-mini-card:nth-child(1){
            border-left:4px solid #ff9aa2;
        }
        .stat-mini-card:nth-child(2){
            border-left:4px solid #a0c4ff;
        }
        .stat-mini-card:nth-child(3){
            border-left:4px solid #bdb2ff;
        }
        .stat-mini-card:nth-child(4){
            border-left:4px solid #ffd6a5;
        }

        .stat-mini-info h4 { font-size: 14px; color: var(--secondary-text); margin-bottom: 5px; }
        .stat-mini-info .value { font-size: 28px; font-weight: 700; color: var(--text-color); }
        .stat-mini-icon { font-size: 40px; color: var(--primary); opacity: 0.7; }

        .stat-mini-card:hover .stat-mini-icon {
            transform: scale(1.2) rotate(5deg);
            color: var(--pink);
        }

        /* Filters Section */
        .filters-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px var(--shadow);
            margin: 25px 0;
            animation: fadeInUp 0.6s ease;
        }
        .filters-grid {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-item { flex: 1; min-width: 150px; }
        .filter-select {
            width: 100%;
            padding: 12px 20px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 40px;
            color: var(--text-color);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
        }
        .filter-select:hover {
            border-color: var(--pink);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(248, 187, 208, 0.3);
        }

        /* Table */
        .table-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin: 25px 0;
            overflow-x: auto;
            animation: fadeInUp 0.8s ease;
        }
        .messages-table { width: 100%; border-collapse: collapse; }
        .messages-table th {
            text-align: left;
            padding: 15px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .messages-table td {
            padding: 15px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
            transition: background-color 0.3s ease;
        }
        .messages-table tbody tr:hover td {
            background-color: rgba(248, 187, 208, 0.1);
        }

        /* Sender Info */
        .sender-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sender-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--lavender);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        .sender-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        tr:hover .sender-avatar {
            transform: scale(1.1) rotate(5deg);
            background: var(--pink);
        }

        /* Message Preview */
        .message-preview {
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--secondary-text);
            font-size: 13px;
        }
        .message-subject {
            font-weight: 600;
            margin-bottom: 3px;
            color: var(--text-color);
        }

        /* Status Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            text-transform: capitalize;
        }
        .status-read { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-unread { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .status-replied { background: rgba(33, 150, 243, 0.2); color: #2196F3; }

        /* Priority Badges */
        .priority-badge {
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 10px;
            font-weight: 600;
            display: inline-block;
        }
        .priority-high { background: rgba(244, 67, 54, 0.2); color: #F44336; }
        .priority-medium { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .priority-low { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
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
            position: relative;
            overflow: hidden;
        }
        .action-btn:hover {
            background: var(--pink);
            color: #000;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .action-btn:active {
            transform: translateY(-1px) scale(1.05);
        }
        .action-btn.view:hover { background: var(--lavender); }
        .action-btn.reply:hover { background: var(--primary); color: white; }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }
        .action-btn.mark-read:hover { background: #4CAF50; color: white; }

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .pagination-info { color: var(--secondary-text); font-size: 14px; }
        .pagination { display: flex; gap: 8px; align-items: center; }
        .page-item {
            min-width: 40px;
            height: 40px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card-bg);
            color: var(--text-color);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(128,128,128,0.1);
            text-decoration: none;
        }
        .page-item:hover {
            background: var(--pink);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .page-item.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: scale(1.1);
        }
        .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* تأثيرات للسيرش بار */
        .search-container {
            flex: 1;
            min-width: 300px;
            position: relative;
            animation: slideInRight 0.5s ease;
        }
        .search-input {
            width: 100%;
            padding: 16px 25px 16px 55px;
            background: var(--card-bg);
            border: 2px solid transparent;
            border-radius: 60px;
            color: var(--text-color);
            font-size: 15px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
            outline: none;
        }
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 8px 25px var(--shadow);
            transform: translateY(-2px);
        }
        .search-input::placeholder {
            color: var(--secondary-text);
            transition: opacity 0.3s ease;
        }
        .search-input:focus::placeholder {
            opacity: 0.5;
        }
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
            z-index: 10;
            transition: all 0.3s ease;
        }
        .search-input:focus + .search-icon {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 60px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            overflow: hidden;
        }
        .search-btn:hover {
            background: var(--lavender);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .search-btn:active {
            transform: translateY(-50%) scale(0.95);
        }
        .search-btn i {
            transition: transform 0.3s ease;
        }
        .search-btn:hover i {
            transform: translateX(8px);
        }

        /* تأثيرات للفلاتر */
        .filters-section h3 i {
            transition: transform 0.3s ease;
        }
        .filters-section:hover h3 i {
            transform: rotate(90deg);
        }
        .clear-filters-btn {
            transition: all 0.3s ease !important;
        }
        .clear-filters-btn:hover {
            background: var(--lavender) !important;
            transform: scale(1.05);
            box-shadow: 0 5px 15px var(--shadow);
        }

        /* Compose Button */
        .compose-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card-bg);
            padding: 12px 24px;
            border-radius: 60px;
            box-shadow: 0 4px 15px var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            animation: slideInRight 0.5s ease;
            position: relative;
            overflow: hidden;
        }
        .compose-btn:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: 0 8px 25px var(--shadow);
        }
        .compose-btn:active {
            transform: translateY(-1px);
        }
        .compose-btn div:first-child {
            background: var(--primary);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }
        .compose-btn:hover div:first-child {
            background: var(--lavender);
        }
        .compose-btn div:first-child i {
            transition: transform 0.3s ease;
        }
        .compose-btn:hover div:first-child i {
            transform: rotate(180deg);
        }

        @media (max-width: 1100px) {
            .stats-mini { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 800px) {
            .admin-wrapper { flex-direction: column; }
            .admin-sidebar { width: 100%; height: auto; border-radius: 0; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Header مع الأزرار -->
        <div class="main-header">
            <div>
                <h1 style="margin-bottom: 5px;">Messages</h1>
                <p style="color: var(--secondary-text);">Manage customer inquiries and messages</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" style="background: var(--lavender); color: #000; transition: all 0.3s ease;" onclick="exportMessages()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-download"></i> Export
                </button>
                <button class="btn-primary" style="background: var(--pink); color: #000; transition: all 0.3s ease;" onclick="markAllRead()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-check-double"></i> Mark All Read
                </button>
            </div>
        </div>

        <!-- Search Bar + Compose -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap;">
            <!-- Search Bar -->
            <div class="search-container">
                <form action="search-messages.php" method="GET" style="width: 100%;">
                    <div style="position: relative; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text"
                               name="q"
                               class="search-input"
                               placeholder="Search by sender, subject, message..."
                               id="searchInput">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <i class="fa-solid fa-arrow-right"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Compose Button -->
            <a href="compose-message.php" class="compose-btn" id="composeBtn">
                <div>
                    <i class="fa-solid fa-pen"></i>
                </div>
                <div>
                    <div style="font-weight: 600; color: var(--text-color);">Compose</div>
                    <div style="font-size: 12px; color: var(--secondary-text);">New message</div>
                </div>
            </a>
        </div>

        <!-- Stats Cards -->
        <?php
        $totalMessages = count($messages);
        $unreadMessages = count(array_filter($messages, fn($m) => $m['status'] === 'unread'));
        $repliedMessages = count(array_filter($messages, fn($m) => $m['status'] === 'replied'));
        $highPriority = count(array_filter($messages, fn($m) => $m['priority'] === 'high'));
        ?>
        <div class="stats-mini">
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Total Messages</h4>
                    <div class="value"><?= $totalMessages ?></div>
                </div>
                <i class="fa-solid fa-envelope stat-mini-icon"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Unread</h4>
                    <div class="value"><?= $unreadMessages ?></div>
                </div>
                <i class="fa-solid fa-envelope-open-text stat-mini-icon" style="color: #FF9800;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>Replied</h4>
                    <div class="value"><?= $repliedMessages ?></div>
                </div>
                <i class="fa-solid fa-reply-all stat-mini-icon" style="color: #4CAF50;"></i>
            </div>
            <div class="stat-mini-card">
                <div class="stat-mini-info">
                    <h4>High Priority</h4>
                    <div class="value"><?= $highPriority ?></div>
                </div>
                <i class="fa-solid fa-exclamation-triangle stat-mini-icon" style="color: #F44336;"></i>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 18px; margin: 0;">
                    <i class="fa-solid fa-filter" style="margin-right: 8px;"></i>
                    Filters
                </h3>
                <button class="action-btn clear-filters-btn" style="width: auto; padding: 0 20px; border-radius: 40px;" onclick="clearFilters()">
                    <i class="fa-solid fa-undo" style="margin-right: 5px;"></i> Clear all
                </button>
            </div>

            <div class="filters-grid">
                <div class="filter-item">
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="read">Read</option>
                        <option value="unread">Unread</option>
                        <option value="replied">Replied</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="priorityFilter">
                        <option value="">All Priority</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="filter-item">
                    <select class="filter-select" id="sortFilter">
                        <option value="">Sort by</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="priority-high">Priority: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="table-container">
            <table class="messages-table">
                <thead>
                <tr>
                    <th style="width: 50px;">
                        <input type="checkbox" style="transform: scale(1.2); cursor: pointer;" id="selectAll">
                    </th>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </thead>
                <tbody id="messagesTableBody">
                <!-- الرسائل رح تتحط هنا عن طريق JavaScript -->
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-section">
                <div class="pagination-info" id="paginationInfo">
                    Showing <strong>0</strong> of <strong><?= $totalMessages ?></strong> messages
                </div>
                <div class="pagination" id="paginationControls">
                    <!-- Pagination رح يتولد عن طريق JavaScript -->
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
            <button class="btn-primary" style="background: var(--lavender); color: #000; transition: all 0.3s ease;" onclick="bulkMarkRead()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-check-double"></i> Mark as Read
            </button>
            <button class="btn-primary" style="background: #ff6b6b; transition: all 0.3s ease;" onclick="bulkDelete()" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-trash-can"></i> Bulk Delete
            </button>
        </div>
    </main>
</div>

<script>
    // ===== بيانات الرسائل من PHP إلى JavaScript =====
    const allMessages = <?php echo json_encode($messages); ?>;
    const messagesArray = Object.entries(allMessages).map(([id, message]) => {
        return {
            id: id,
            sender_name: message.sender_name,
            sender_email: message.sender_email,
            sender_avatar: message.sender_avatar,
            subject: message.subject,
            message: message.message,
            priority: message.priority,
            date: message.date,
            status: message.status
        };
    });

    let currentPage = <?= $page ?>;
    let perPage = <?= $perPage ?>;
    let filteredMessages = [...messagesArray];
    let totalFilteredMessages = filteredMessages.length;

    // ===== دوال عرض الرسائل =====
    function displayMessages() {
        const tbody = document.getElementById('messagesTableBody');
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const paginatedMessages = filteredMessages.slice(start, end);

        let html = '';
        paginatedMessages.forEach(message => {
            // تنسيق التاريخ
            const date = new Date(message.date);
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

            html += `
                <tr data-id="${message.id}" data-status="${message.status}" data-priority="${message.priority}">
                    <td>
    <div style="display:flex; align-items:center; gap:10px;">
        <input type="checkbox" class="message-checkbox" ...>
        <div class="sender-info">
            <div class="sender-avatar">
                <img src="${message.sender_avatar}" alt="${message.sender_name}">
            </div>
            <div>
                <div style="font-weight: 600;">${message.sender_name}</div>
                <div style="font-size: 11px; color: var(--secondary-text);">${message.sender_email}</div>
            </div>
        </div>
    </div>
</td>

                    <td style="max-width: 180px;">
                        <div class="message-subject">${message.subject}</div>

                    <td style="max-width: 280px;">
                        <div class="message-preview" title="${message.message.replace(/"/g, '&quot;')}">
                            ${message.message.substring(0, 70)}${message.message.length > 70 ? '...' : ''}
                        </div>

                    <td style="text-align: center;">
                        <span class="priority-badge priority-${message.priority}">
                            ${message.priority.charAt(0).toUpperCase() + message.priority.slice(1)}
                        </span>

                    <td style="white-space: nowrap;">
                        <div>${formattedDate}</div>
                        <div style="font-size: 11px; color: var(--secondary-text);">${formattedTime}</div>

                    <td>
                        <span class="status-badge status-${message.status}">
                            ${message.status.charAt(0).toUpperCase() + message.status.slice(1)}
                        </span>

                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewMessage(${message.id})" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="action-btn reply" onclick="replyMessage(${message.id})" title="Reply">
                                <i class="fa-solid fa-reply"></i>
                            </button>
                            ${message.status === 'unread' ? `
                                <button class="action-btn mark-read" onclick="markAsRead(${message.id})" title="Mark as Read">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            ` : ''}
                            <button class="action-btn delete" onclick="deleteMessage(${message.id})" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>

            `;
        });

        tbody.innerHTML = html;
        updatePaginationInfo();
        updatePaginationControls();
    }

    function updatePaginationInfo() {
        const start = (currentPage - 1) * perPage + 1;
        const end = Math.min(start + perPage - 1, filteredMessages.length);
        const infoElement = document.getElementById('paginationInfo');
        infoElement.innerHTML = `Showing <strong>${filteredMessages.length > 0 ? start + '-' + end : '0'}</strong> of <strong>${filteredMessages.length}</strong> messages`;
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredMessages.length / perPage);
        const paginationDiv = document.getElementById('paginationControls');
        let html = '';

        if (currentPage > 1) {
            html += `<a href="#" onclick="changePage(${currentPage - 1}); return false;" class="page-item"><i class="fa-solid fa-chevron-left"></i></a>`;
        } else {
            html += `<span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>`;
        }

        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                html += `<span class="page-item active">${i}</span>`;
            } else {
                html += `<a href="#" onclick="changePage(${i}); return false;" class="page-item">${i}</a>`;
            }
        }

        if (currentPage < totalPages) {
            html += `<a href="#" onclick="changePage(${currentPage + 1}); return false;" class="page-item"><i class="fa-solid fa-chevron-right"></i></a>`;
        } else {
            html += `<span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>`;
        }

        paginationDiv.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        displayMessages();
    }

    // ===== دوال الفلترة =====
    function filterMessages() {
        const status = document.getElementById('statusFilter').value;
        const priority = document.getElementById('priorityFilter').value;
        const sortBy = document.getElementById('sortFilter').value;

        filteredMessages = messagesArray.filter(message => {
            let showMessage = true;

            if (status && message.status !== status) {
                showMessage = false;
            }

            if (priority && message.priority !== priority) {
                showMessage = false;
            }

            return showMessage;
        });

        if (sortBy) {
            filteredMessages.sort((a, b) => {
                if (sortBy === 'newest') {
                    return new Date(b.date) - new Date(a.date);
                } else if (sortBy === 'oldest') {
                    return new Date(a.date) - new Date(b.date);
                } else if (sortBy === 'priority-high') {
                    const priorityOrder = { high: 3, medium: 2, low: 1 };
                    return priorityOrder[b.priority] - priorityOrder[a.priority];
                }
                return 0;
            });
        }

        currentPage = 1;
        displayMessages();
    }

    // ===== تأثيرات السيرش بار =====
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const composeBtn = document.getElementById('composeBtn');

        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.style.transform = 'translateY(-2px)';
            });

            searchInput.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
            });

            searchInput.addEventListener('input', function() {
                if (this.value.length > 0) {
                    searchBtn.style.background = 'var(--lavender)';
                    searchBtn.querySelector('i').style.transform = 'translateX(8px)';
                } else {
                    searchBtn.style.background = 'var(--primary)';
                    searchBtn.querySelector('i').style.transform = 'translateX(0)';
                }
            });
        }

        if (composeBtn) {
            composeBtn.addEventListener('mouseenter', function() {
                this.querySelector('div:first-child').style.transform = 'rotate(90deg)';
            });

            composeBtn.addEventListener('mouseleave', function() {
                this.querySelector('div:first-child').style.transform = 'rotate(0)';
            });
        }

        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.style.backgroundColor = 'var(--pink)';
                this.style.color = '#000';
                setTimeout(() => {
                    this.style.backgroundColor = 'var(--bg-color)';
                    this.style.color = 'var(--text-color)';
                }, 200);
            });
        });

        filterMessages();
    });

    // ===== ربط الفلاتر =====
    document.getElementById('statusFilter')?.addEventListener('change', filterMessages);
    document.getElementById('priorityFilter')?.addEventListener('change', filterMessages);
    document.getElementById('sortFilter')?.addEventListener('change', filterMessages);

    // ===== دوال مساعدة =====
    function clearFilters() {
        document.querySelectorAll('.filter-select').forEach(select => select.value = '');
        filterMessages();

        const filterSection = document.querySelector('.filters-section');
        filterSection.style.transform = 'scale(1.02)';
        setTimeout(() => {
            filterSection.style.transform = 'scale(1)';
        }, 200);
    }

    // وظائف الأزرار
    function viewMessage(id) {
        window.location.href = 'message-details.php?id=' + id;
    }

    function replyMessage(id) {
        window.location.href = 'compose-message.php?reply=' + id;
    }

    function markAsRead(id) {
        // إنشاء الـ overlay
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        overlay.style.zIndex = '9998';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        // إنشاء الـ popup
        const popup = document.createElement('div');
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

        // أيقونة
        const icon = document.createElement('div');
        icon.textContent = '✉️';
        icon.style.fontSize = '40px';
        icon.style.marginBottom = '12px';

        // نص التأكيد
        const message = document.createElement('p');
        message.textContent = 'Mark this message as read?';
        message.style.fontSize = '15px';
        message.style.margin = '0 0 22px 0';
        message.style.lineHeight = '1.6';

        // حاوية الأزرار
        const btnContainer = document.createElement('div');
        btnContainer.style.display = 'flex';
        btnContainer.style.gap = '12px';
        btnContainer.style.justifyContent = 'center';

        // زر الإلغاء
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.style.flex = '1';
        cancelBtn.style.padding = '11px 20px';
        cancelBtn.style.borderRadius = '14px';
        cancelBtn.style.border = '1px solid var(--pink, #F8BBD0)';
        cancelBtn.style.backgroundColor = 'transparent';
        cancelBtn.style.color = 'var(--text-color, #333)';
        cancelBtn.style.fontFamily = "'Poppins', sans-serif";
        cancelBtn.style.fontSize = '14px';
        cancelBtn.style.fontWeight = '500';
        cancelBtn.style.cursor = 'pointer';
        cancelBtn.style.transition = 'all 0.2s ease';
        cancelBtn.addEventListener('mouseover', function() {
            cancelBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            cancelBtn.style.color = '#fff';
        });
        cancelBtn.addEventListener('mouseout', function() {
            cancelBtn.style.backgroundColor = 'transparent';
            cancelBtn.style.color = 'var(--text-color, #333)';
        });

        // زر التأكيد
        const confirmBtn = document.createElement('button');
        confirmBtn.textContent = 'Mark as Read';
        confirmBtn.style.flex = '1';
        confirmBtn.style.padding = '11px 20px';
        confirmBtn.style.borderRadius = '14px';
        confirmBtn.style.border = 'none';
        confirmBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
        confirmBtn.style.color = '#fff';
        confirmBtn.style.fontFamily = "'Poppins', sans-serif";
        confirmBtn.style.fontSize = '14px';
        confirmBtn.style.fontWeight = '600';
        confirmBtn.style.cursor = 'pointer';
        confirmBtn.style.transition = 'all 0.2s ease';
        confirmBtn.addEventListener('mouseover', function() {
            confirmBtn.style.backgroundColor = '#F48FB1';
            confirmBtn.style.transform = 'scale(1.03)';
        });
        confirmBtn.addEventListener('mouseout', function() {
            confirmBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            confirmBtn.style.transform = 'scale(1)';
        });

        // تجميع
        btnContainer.appendChild(cancelBtn);
        btnContainer.appendChild(confirmBtn);
        popup.appendChild(icon);
        popup.appendChild(message);
        popup.appendChild(btnContainer);
        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // تأثير الظهور
        requestAnimationFrame(function() {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        });

        // دالة إغلاق
        function closePopup() {
            popup.style.transform = 'scale(0.9)';
            overlay.style.opacity = '0';
            setTimeout(function() {
                overlay.remove();
            }, 300);
        }

        // دالة إظهار رسالة النجاح
        function showSuccess() {
            // تفريغ الـ popup
            popup.innerHTML = '';

            // أيقونة النجاح
            const successIcon = document.createElement('div');
            successIcon.textContent = '✅';
            successIcon.style.fontSize = '40px';
            successIcon.style.marginBottom = '12px';

            // نص النجاح
            const successMsg = document.createElement('p');
            successMsg.textContent = 'Message marked as read ';
            successMsg.style.fontSize = '15px';
            successMsg.style.margin = '0 0 22px 0';
            successMsg.style.lineHeight = '1.6';

            // زر حسناً
            const okBtn = document.createElement('button');
            okBtn.textContent = 'OK';
            okBtn.style.padding = '11px 40px';
            okBtn.style.borderRadius = '14px';
            okBtn.style.border = 'none';
            okBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            okBtn.style.color = '#fff';
            okBtn.style.fontFamily = "'Poppins', sans-serif";
            okBtn.style.fontSize = '14px';
            okBtn.style.fontWeight = '600';
            okBtn.style.cursor = 'pointer';
            okBtn.style.transition = 'all 0.2s ease';
            okBtn.addEventListener('mouseover', function() {
                okBtn.style.backgroundColor = '#F48FB1';
            });
            okBtn.addEventListener('mouseout', function() {
                okBtn.style.backgroundColor = 'var(--pink, #F8BBD0)';
            });

            // تأثير تبديل المحتوى
            popup.style.transform = 'scale(0.9)';
            setTimeout(function() {
                popup.appendChild(successIcon);
                popup.appendChild(successMsg);
                popup.appendChild(okBtn);
                popup.style.transform = 'scale(1)';
            }, 250);

            okBtn.addEventListener('click', closePopup);
        }

        // أحداث الأزرار
        cancelBtn.addEventListener('click', closePopup);

        confirmBtn.addEventListener('click', function() {
            showSuccess();
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closePopup();
        });

        document.addEventListener('keydown', function handleEsc(e) {
            if (e.key === 'Escape') {
                closePopup();
                document.removeEventListener('keydown', handleEsc);
            }
        });
    }

    function showAdminConfirm(message, onConfirm) {
        // 1. إنشاء overlay الخلفية
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

        // 2. إنشاء نافذة الـ Popup
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

        // محتوى البوب أب
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

        // ظهور الأنيميشن
        setTimeout(() => {
            overlay.style.opacity = '1';
            popup.style.transform = 'scale(1)';
        }, 10);

        // إزالة البوب أب
        function closePopup() {
            overlay.style.opacity = '0';
            popup.style.transform = 'scale(0.9)';
            setTimeout(() => {
                if (overlay && overlay.parentNode) overlay.remove();
            }, 250);
        }

        // دالة عرض رسالة النجاح (toast منتصف الصفحة)
        function showSuccessToast() {
            const toast = document.createElement('div');
            toast.id = 'admin-success-toast';
            toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">

                <div>
                    <strong style="font-size: 18px;">Removed from the system!</strong>

                </div>
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
            toast.style.borderRight = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderLeft = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderTop = '4px solid var(--pink, #F8BBD0)';
            toast.style.borderBottom = '4px solid var(--pink, #F8BBD0)';
            toast.style.backdropFilter = 'blur(12px)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1)';
            toast.style.fontWeight = '500';
            toast.style.textAlign = 'center';
            toast.style.minWidth = '280px';
            toast.style.boxSizing = 'border-box';

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 20);

            // إخفاء الرسالة بعد 2.5 ثانية
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -50%) scale(0.95)';
                setTimeout(() => {
                    if (toast && toast.parentNode) toast.remove();
                }, 250);
            }, 2500);
        }

        // أحداث الأزرار
        const cancelBtn = popup.querySelector('#confirm-cancel-btn');
        const confirmBtn = popup.querySelector('#confirm-ok-btn');

        cancelBtn.addEventListener('click', () => {
            closePopup();
        });

        confirmBtn.addEventListener('click', () => {
            // ✅ بدون حذف فعلي – فقط استدعاء callback إذا أردت تنفيذ شيء لاحقاً (مثل تحديث واجهة)
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();  // هون بتقدر تعمل أي شيء زي تحديث UI بدون حذف حقيقي
            }
            closePopup();
            // عرض رسالة النجاح الجميلة في منتصف الصفحة
            showSuccessToast();
        });

        // إغلاق عند الضغط على overlay
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closePopup();
        });
    }

    function deleteMessage(id) {
       showAdminConfirm('Are you sure you want to delete this message?', () => {})
    }

    function exportMessages() {
        alert('Export messages feature (Demo)');
    }

    function markAllRead() {
        if(confirm('Mark all messages as read?')) {
            alert('All messages marked as read (Demo)');
        }
    }

    function bulkMarkRead() {
        let selected = [];
        document.querySelectorAll('.message-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if(selected.length === 0) {
            alert('Please select at least one message');
            return;
        }

        alert('Marked ' + selected.length + ' messages as read (Demo)');
    }

    function bulkDelete() {
        let selected = [];
        document.querySelectorAll('.message-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if(selected.length === 0) {
            alert('Please select at least one message');
            return;
        }

        showAdminConfirm('Are you sure you want to delete these '+selected.length +' messages?', () => {})
    }

    // تحديد الكل
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.message-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });
</script>

<script>
    (function() {
        const themeSwitchMain = document.getElementById('themeSwitchSidebar');

        function applyTheme(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                if (themeSwitchMain) themeSwitchMain.checked = true;
            } else {
                document.body.classList.remove('dark-mode');
                if (themeSwitchMain) themeSwitchMain.checked = false;
            }
        }

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true);
        else applyTheme(false);

        if (themeSwitchMain) {
            themeSwitchMain.addEventListener('change', function(e) {
                const isDark = this.checked;
                applyTheme(isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>