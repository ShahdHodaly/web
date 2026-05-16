<?php
// messages.php
session_start();
require_once 'db.php';

$pdo = getDB();

// جلب جميع الرسائل من قاعدة البيانات
$stmt = $pdo->query("
    SELECT 
        message_id,
        sender_name,
        sender_email,
        subject,
        message,
        priority,
        status,
        created_at
    FROM messages
    ORDER BY created_at DESC
");
$messagesFromDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: تأكد من وجود بيانات
if (empty($messagesFromDB)) {
    echo "<!-- No messages found in database -->";
}

// تحويل الرسائل إلى مصفوفة
$messages = [];
foreach ($messagesFromDB as $msg) {
    $messages[$msg['message_id']] = [
            'id' => $msg['message_id'],
            'sender_name' => $msg['sender_name'],
            'sender_email' => $msg['sender_email'],
            'sender_avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($msg['sender_name']) . '&background=F8BBD0&color=000&size=40',
            'subject' => $msg['subject'],
            'message' => $msg['message'],
            'priority' => $msg['priority'],
            'date' => $msg['created_at'],
            'status' => $msg['status']
    ];
}

// Debug: عرض عدد الرسائل
$debug_count = count($messages);
echo "<!-- Total messages loaded: " . $debug_count . " -->";

// حساب الإحصائيات
$totalMessages = $debug_count;
$unreadMessages = count(array_filter($messages, fn($m) => $m['status'] === 'unread'));

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6;
$totalPages = $totalMessages > 0 ? ceil($totalMessages / $perPage) : 1;
$offset = ($page - 1) * $perPage;
$paginatedMessages = array_slice($messages, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages · Teddy Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        .stats-mini {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 25px 0;
            max-width: 400px;
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
        .stat-mini-card:nth-child(1) { border-left:4px solid #ff9aa2; }
        .stat-mini-card:nth-child(2) { border-left:4px solid #a0c4ff; }
        .stat-mini-info h4 { font-size: 14px; color: var(--secondary-text); margin-bottom: 5px; }
        .stat-mini-info .value { font-size: 28px; font-weight: 700; color: var(--text-color); }
        .stat-mini-icon { font-size: 40px; color: var(--primary); opacity: 0.7; }

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
        }
        .action-btn:hover {
            background: var(--pink);
            color: #000;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px var(--shadow);
        }
        .action-btn.view:hover { background: var(--lavender); }
        .action-btn.delete:hover { background: #ff6b6b; color: white; }
        .action-btn.mark-read:hover { background: #4CAF50; color: white; }

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
        .page-item:hover { background: var(--pink); color: white; transform: translateY(-2px); }
        .page-item.active { background: var(--primary); color: white; border-color: var(--primary); transform: scale(1.1); }
        .page-item.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }

        .search-container {
            flex: 1;
            min-width: 300px;
            position: relative;
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
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-text);
            z-index: 10;
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
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .stats-mini { max-width: 100%; }
        }

        /* Custom Modal & Toast */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);
            z-index: 10000; display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.3s;
        }
        .modal-overlay.visible { opacity: 1; pointer-events: auto; }
        .modal-box {
            background: var(--card-bg); padding: 30px; border-radius: 28px;
            width: 90%; max-width: 420px; text-align: center;
            box-shadow: 0 25px 45px rgba(0,0,0,0.2);
            transform: scale(0.8); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--pink);
        }
        .modal-overlay.visible .modal-box { transform: scale(1); }
        .modal-box .modal-icon { font-size: 48px; margin-bottom: 10px; }
        .modal-box h3 { font-size: 22px; font-weight: 600; margin-bottom: 10px; color: var(--text-color); }
        .modal-box p { font-size: 15px; color: var(--secondary-text); margin-bottom: 25px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 15px; justify-content: center; }
        .modal-btn { padding: 10px 28px; border-radius: 40px; font-weight: 600; cursor: pointer; transition: all 0.3s; border: none; font-size: 15px; }
        .modal-btn.cancel { background: transparent; border: 2px solid var(--pink); color: var(--pink); }
        .modal-btn.cancel:hover { background: rgba(248,187,208,0.1); }
        .modal-btn.confirm { background: var(--pink); color: #fff; box-shadow: 0 5px 15px rgba(248,187,208,0.4); }
        .modal-btn.confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(248,187,208,0.5); }
        .modal-btn.danger { background: #ff4757; color: #fff; box-shadow: 0 5px 15px rgba(255,71,87,0.3); }
        .modal-btn.danger:hover { transform: translateY(-2px); }

        .toast-container {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            z-index: 10001; pointer-events: none;
        }
        .toast-message {
            background: var(--card-bg); padding: 20px 30px; border-radius: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            display: flex; align-items: center; gap: 12px;
            opacity: 0; transform: scale(0.8);
            animation: toastIn 0.4s forwards, toastOut 0.4s 2.5s forwards;
            border: 2px solid var(--pink); min-width: 280px; justify-content: center;
        }
        .toast-message i { font-size: 20px; }
        .toast-message span { font-weight: 600; font-size: 15px; color: var(--text-color); }
        @keyframes toastIn { to { opacity: 1; transform: scale(1); } }
        @keyframes toastOut { to { opacity: 0; transform: scale(0.8); } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="main-header">
            <div>
                <h1 style="margin-bottom: 5px;">Messages</h1>
                <p style="color: var(--secondary-text);">Manage customer inquiries and messages</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="btn-primary" style="background: var(--lavender); color: #000;" onclick="exportMessages()">
                    <i class="fa-solid fa-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; gap: 20px; flex-wrap: wrap;">
            <div class="search-container">
                <form action="search-messages.php" method="GET" style="width: 100%;">
                    <div style="position: relative; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" name="q" class="search-input" placeholder="Search by sender, subject, message..." id="searchInput">
                        <button type="submit" class="search-btn" id="searchBtn">
                            <i class="fa-solid fa-arrow-right"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
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
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="font-size: 18px; margin: 0;"><i class="fa-solid fa-filter"></i> Filters</h3>
                <button class="action-btn clear-filters-btn" style="width: auto; padding: 0 20px; border-radius: 40px;" onclick="clearFilters()">
                    <i class="fa-solid fa-undo"></i> Clear all
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
                    <select class="filter-select" id="sortFilter">
                        <option value="">Sort by</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="table-container">
            <table class="messages-table">
                <thead>
                <tr>
                    <th style="width: 50px;"><input type="checkbox" style="transform: scale(1.2); cursor: pointer;" id="selectAll"></th>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="messagesTableBody">
                <?php if ($totalMessages > 0): ?>
                    <?php foreach($paginatedMessages as $id => $message): ?>
                        <?php
                        $date = new DateTime($message['date']);
                        $formattedDate = $date->format('M d, Y');
                        $formattedTime = $date->format('h:i A');
                        ?>
                        <tr data-id="<?= $id ?>" data-status="<?= $message['status'] ?>">
                            <td><input type="checkbox" class="message-checkbox" value="<?= $id ?>"></td>
                            <td>
                                <div class="sender-info">
                                    <div class="sender-avatar">
                                        <img src="<?= htmlspecialchars($message['sender_avatar']) ?>" onerror="this.src='images/default-avatar.png'">
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($message['sender_name']) ?></div>
                                        <div style="font-size: 11px; color: var(--secondary-text);"><?= htmlspecialchars($message['sender_email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="max-width: 180px;">
                                <div class="message-subject"><?= htmlspecialchars($message['subject']) ?></div>
                            </td>
                            <td style="max-width: 280px;">
                                <div class="message-preview" title="<?= htmlspecialchars($message['message']) ?>">
                                    <?= htmlspecialchars(substr($message['message'], 0, 70)) ?><?= strlen($message['message']) > 70 ? '...' : '' ?>
                                </div>
                            </td>
                            <td style="white-space: nowrap;">
                                <div><?= $formattedDate ?></div>
                                <div style="font-size: 11px; color: var(--secondary-text);"><?= $formattedTime ?></div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $message['status'] ?>">
                                    <?= ucfirst($message['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn view" onclick="viewMessage(<?= $id ?>)" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <?php if ($message['status'] === 'unread'): ?>
                                        <button class="action-btn mark-read" onclick="markAsRead(<?= $id ?>)" title="Mark as Read">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="action-btn delete" onclick="deleteMessage(<?= $id ?>)" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 50px; color: var(--secondary-text);">
                            <i class="fa-solid fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            No messages found
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-section">
                    <div class="pagination-info">
                        Showing <strong><?= $offset + 1 ?>-<?= min($offset + $perPage, $totalMessages) ?></strong> of <strong><?= $totalMessages ?></strong> messages
                    </div>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="page-item"><i class="fa-solid fa-chevron-left"></i></a>
                        <?php else: ?>
                            <span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>
                        <?php endif; ?>

                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="page-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="page-item"><i class="fa-solid fa-chevron-right"></i></a>
                        <?php else: ?>
                            <span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
            <button class="btn-primary" style="background: var(--lavender); color: #000;" onclick="bulkMarkRead()">
                <i class="fa-solid fa-check-double"></i> Mark as Read
            </button>
            <button class="btn-primary" style="background: #ff6b6b;" onclick="bulkDelete()">
                <i class="fa-solid fa-trash-can"></i> Bulk Delete
            </button>
        </div>
    </main>
</div>

<!-- Custom Modal - لازم يكون قبل الـ script -->
<div id="customModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon" id="modalIcon">⚠️</div>
        <h3 id="modalTitle">Confirm Action</h3>
        <p id="modalMessage">Are you sure?</p>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
            <button class="modal-btn confirm" id="modalConfirmBtn">Confirm</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
    // ===== Custom Modal & Toast =====
    let confirmCallback = null;

    function showConfirmPopup(message, callback, isDanger) {
        const iconEl = document.getElementById('modalIcon');
        const titleEl = document.getElementById('modalTitle');
        const msgEl = document.getElementById('modalMessage');
        const confirmBtn = document.getElementById('modalConfirmBtn');
        const modal = document.getElementById('customModal');

        if (isDanger) {
            iconEl.textContent = '🗑️';
            titleEl.textContent = 'Delete Confirmation';
            confirmBtn.className = 'modal-btn danger';
            confirmBtn.innerText = 'Delete';
        } else {
            iconEl.textContent = '⚠️';
            titleEl.textContent = 'Confirm Action';
            confirmBtn.className = 'modal-btn confirm';
            confirmBtn.innerText = 'Confirm';
        }

        msgEl.innerText = message;
        confirmCallback = callback;
        modal.classList.add('visible');
    }

    function closeModal() {
        document.getElementById('customModal').classList.remove('visible');
        confirmCallback = null;
    }

    document.getElementById('modalConfirmBtn').addEventListener('click', function() {
        if (typeof confirmCallback === 'function') {
            confirmCallback();
        }
        closeModal();
    });

    document.getElementById('customModal').addEventListener('click', function(e) {
        if (e.target.id === 'customModal') closeModal();
    });

    function showToast(message, isSuccess) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.style.borderColor = isSuccess !== false ? 'var(--pink)' : '#ff4757';
        toast.innerHTML = '<i class="fa-solid fa-' + (isSuccess !== false ? 'check-circle' : 'exclamation-triangle') + '" style="color:' + (isSuccess !== false ? 'var(--pink)' : '#ff4757') + '"></i><span>' + message + '</span>';
        container.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3500);
    }

    // ===== Actions =====
    function viewMessage(id) {
        window.location.href = 'message-details.php?id=' + id;
    }

    function markAsRead(id) {
        showConfirmPopup('Mark this message as read?', function() {
            fetch('update-message-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message_id=' + id + '&status=read'
            }).then(function() {
                showToast('Marked as read successfully!');
                setTimeout(function() { location.reload(); }, 1500);
            });
        }, false);
    }

    function deleteMessage(id) {
        showConfirmPopup('Permanently delete this message? This cannot be undone.', function() {
            fetch('delete-message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message_id=' + id
            }).then(function() {
                showToast('Message deleted successfully!');
                setTimeout(function() { location.reload(); }, 1500);
            });
        }, true);
    }

    function exportMessages() {
        showToast('Export feature coming soon! 📥', true);
    }

    function bulkMarkRead() {
        var selected = [];
        document.querySelectorAll('.message-checkbox:checked').forEach(function(cb) { selected.push(cb.value); });
        if (selected.length === 0) {
            showToast('Please select at least one message', false);
            return;
        }
        showConfirmPopup('Mark ' + selected.length + ' message(s) as read?', function() {
            showToast('Marked ' + selected.length + ' messages as read!');
            setTimeout(function() { location.reload(); }, 1500);
        }, false);
    }

    function bulkDelete() {
        var selected = [];
        document.querySelectorAll('.message-checkbox:checked').forEach(function(cb) { selected.push(cb.value); });
        if (selected.length === 0) {
            showToast('Please select at least one message', false);
            return;
        }
        showConfirmPopup('⚠️ Permanently delete ' + selected.length + ' message(s)?', function() {
            showToast('Deleted ' + selected.length + ' messages!');
            setTimeout(function() { location.reload(); }, 1500);
        }, true);
    }

    function clearFilters() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('sortFilter').value = '';
        window.location.href = 'messages.php';
    }

    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.message-checkbox').forEach(function(cb) { cb.checked = this.checked; }.bind(this));
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        var status = this.value;
        var rows = document.querySelectorAll('#messagesTableBody tr');
        rows.forEach(function(row) {
            if (!status || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<script>
    (function() {
        var themeSwitchMain = document.getElementById('themeSwitchSidebar');
        function applyTheme(isDark) {
            if (isDark) { document.body.classList.add('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = true; }
            else { document.body.classList.remove('dark-mode'); if (themeSwitchMain) themeSwitchMain.checked = false; }
        }
        var savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') applyTheme(true); else applyTheme(false);
        if (themeSwitchMain) themeSwitchMain.addEventListener('change', function(e) { applyTheme(this.checked); localStorage.setItem('theme', this.checked ? 'dark' : 'light'); });
    })();
</script>
</body>
</html>