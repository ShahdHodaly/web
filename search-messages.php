<?php
// search-messages.php
session_start();
require_once 'db.php';

$pdo = getDB();

// معالجة البحث
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$results = [];
$totalResults = 0;

// بناء استعلام البحث
$sql = "
    SELECT 
        m.message_id as id,
        m.sender_name,
        m.sender_email,
        m.subject,
        m.message,
        m.priority,
        m.status,
        m.created_at as date
    FROM messages m
    WHERE 1=1
";

$params = [];
$paramIndex = 1;

// التحقق من وجود معايير بحث
$hasCriteria = !empty($query) || !empty($date_from) || !empty($date_to);

if ($hasCriteria) {

    // إضافة شرط البحث النصي
    if (!empty($query)) {
        $sql .= " AND (
            m.sender_name ILIKE $" . $paramIndex . " OR 
            m.sender_email ILIKE $" . $paramIndex . " OR 
            m.subject ILIKE $" . $paramIndex . " OR 
            m.message ILIKE $" . $paramIndex . "
        )";
        $params[] = '%' . $query . '%';
        $paramIndex++;
    }

    // إضافة شرط التاريخ من
    if (!empty($date_from)) {
        $sql .= " AND DATE(m.created_at) >= $" . $paramIndex;
        $params[] = $date_from;
        $paramIndex++;
    }

    // إضافة شرط التاريخ إلى
    if (!empty($date_to)) {
        $sql .= " AND DATE(m.created_at) <= $" . $paramIndex;
        $params[] = $date_to;
        $paramIndex++;
    }

    // ترتيب النتائج من الأحدث إلى الأقدم
    $sql .= " ORDER BY m.created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $messagesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // تنسيق النتائج
        foreach ($messagesData as $message) {
            $results[$message['id']] = [
                    'sender_name' => $message['sender_name'],
                    'sender_email' => $message['sender_email'],
                    'sender_avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($message['sender_name']) . '&background=F8BBD0&color=000&size=45',
                    'subject' => $message['subject'],
                    'message' => $message['message'],
                    'priority' => $message['priority'],
                    'date' => $message['date'],
                    'status' => $message['status']
            ];
        }

        $totalResults = count($results);
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        $totalResults = 0;
    }
}

// Pagination للنتائج
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5;
$totalPages = $totalResults > 0 ? ceil($totalResults / $perPage) : 1;
$offset = ($page - 1) * $perPage;
$paginatedResults = array_slice($results, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Messages · Teddy Shop</title>
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
        body { background-color: var(--bg-color); font-family: 'Poppins', sans-serif; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; width: calc(100% - 280px); padding: 30px 35px; background-color: var(--bg-color); overflow-y: auto; box-sizing: border-box; }

        .search-section {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease;
        }

        .search-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .search-input-group {
            flex: 2;
            min-width: 250px;
        }
        .search-input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 8px;
        }
        .search-input-group input {
            width: 100%;
            padding: 14px 18px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 50px;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .search-input-group input:focus {
            border-color: var(--pink);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .date-group {
            flex: 1;
            min-width: 180px;
        }
        .date-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--secondary-text);
            margin-bottom: 8px;
        }
        .date-group input {
            width: 100%;
            padding: 14px 18px;
            background: var(--bg-color);
            border: 2px solid transparent;
            border-radius: 50px;
            color: var(--text-color);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }
        .date-group input:focus {
            border-color: var(--pink);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .search-btn {
            padding: 14px 30px;
            background: var(--primary);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-btn:hover {
            background: var(--pink);
            transform: translateY(-2px);
        }

        .reset-btn {
            background: var(--bg-color);
            border: 1px solid rgba(128,128,128,0.2);
            color: var(--secondary-text);
        }
        .reset-btn:hover {
            background: #ff6b6b;
            color: white;
            border-color: #ff6b6b;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .results-count {
            font-size: 16px;
            color: var(--secondary-text);
        }
        .results-count strong {
            color: var(--primary);
            font-size: 20px;
        }

        .table-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
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
            overflow: hidden;
        }
        .sender-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .message-preview {
            max-width: 250px;
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
        }
        .status-read { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-unread { background: rgba(255, 152, 0, 0.2); color: #FF9800; }
        .status-replied { background: rgba(33, 150, 243, 0.2); color: #2196F3; }

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

        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .action-btn {
            width: 32px;
            height: 32px;
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
            transform: translateY(-2px) scale(1.1);
        }

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
        }
        .page-item.active {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }
        .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: 30px;
        }
        .no-results i {
            font-size: 80px;
            color: var(--secondary-text);
            opacity: 0.5;
            margin-bottom: 20px;
        }
        .no-results h3 {
            font-size: 24px;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .suggestions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .suggestion-chip {
            padding: 8px 20px;
            background: var(--lavender);
            border-radius: 50px;
            color: #000;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .suggestion-chip:hover {
            background: var(--pink);
            transform: translateY(-2px);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 800px) {
            .admin-sidebar { width: 100%; height: auto; }
            .admin-main { width: 100%; }
            .search-row { flex-direction: column; }
            .search-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-main">
        <!-- Search Section -->
        <div class="search-section">
            <div class="search-title">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--pink);"></i>
                Search Messages
            </div>

            <form action="search-messages.php" method="GET" id="searchForm">
                <div class="search-row">
                    <div class="search-input-group">
                        <label><i class="fa-solid fa-search"></i> Search by</label>
                        <input type="text"
                               name="q"
                               id="searchInput"
                               value="<?= htmlspecialchars($query) ?>"
                               placeholder="Sender name, email, subject, message...">
                    </div>
                    <div class="date-group">
                        <label><i class="fa-regular fa-calendar"></i> From Date</label>
                        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    <div class="date-group">
                        <label><i class="fa-regular fa-calendar"></i> To Date</label>
                        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                    <a href="search-messages.php" class="search-btn reset-btn">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <?php if (!empty($query) || !empty($date_from) || !empty($date_to)): ?>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-count">
                    Found <strong><?= $totalResults ?></strong> message<?= $totalResults != 1 ? 's' : '' ?>
                    <?php if (!empty($query)): ?>
                        for "<strong><?= htmlspecialchars($query) ?></strong>"
                    <?php endif; ?>
                    <?php if (!empty($date_from) && !empty($date_to)): ?>
                        between <strong><?= date('M d, Y', strtotime($date_from)) ?></strong> and <strong><?= date('M d, Y', strtotime($date_to)) ?></strong>
                    <?php elseif (!empty($date_from)): ?>
                        from <strong><?= date('M d, Y', strtotime($date_from)) ?></strong>
                    <?php elseif (!empty($date_to)): ?>
                        until <strong><?= date('M d, Y', strtotime($date_to)) ?></strong>
                    <?php endif; ?>
                </div>
                <a href="messages.php" class="filter-chip" style="padding: 8px 20px; background: var(--lavender); border-radius: 50px; color: #000; text-decoration: none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Messages
                </a>
            </div>

            <?php if ($totalResults > 0): ?>
                <!-- Results Table -->
                <div class="table-container">
                    <table class="messages-table">
                        <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Priority</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                        <?php foreach($paginatedResults as $id => $message): ?>
                            <tr>
                                <td style="min-width: 200px;">
                                    <div class="sender-info">
                                        <div class="sender-avatar">
                                            <img src="<?= htmlspecialchars($message['sender_avatar']) ?>" alt="<?= htmlspecialchars($message['sender_name']) ?>" onerror="this.src='images/default-avatar.png'">
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
                                <td style="text-align: center;">
                                <span class="priority-badge priority-<?= $message['priority'] ?>">
                                    <?= ucfirst($message['priority']) ?>
                                </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <?= date('M d, Y', strtotime($message['date'])) ?>
                                    <div style="font-size: 11px; color: var(--secondary-text);"><?= date('h:i A', strtotime($message['date'])) ?></div>
                                </td>
                                <td style="text-align: center;">
                                <span class="status-badge status-<?= $message['status'] ?>">
                                    <?= ucfirst($message['status']) ?>
                                </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-buttons">
                                        <button class="action-btn view" onclick="viewMessage(<?= $id ?>)" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="action-btn delete" onclick="deleteMessage(<?= $id ?>)" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-section">
                        <div class="pagination-info">
                            Showing <strong><?= $offset + 1 ?>-<?= min($offset + $perPage, $totalResults) ?></strong> of <strong><?= $totalResults ?></strong> results
                        </div>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?q=<?= urlencode($query) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&page=<?= $page - 1 ?>" class="page-item">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            if ($startPage > 1): ?>
                                <a href="?q=<?= urlencode($query) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&page=1" class="page-item">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span class="page-item disabled">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?q=<?= urlencode($query) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&page=<?= $i ?>"
                                   class="page-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="page-item disabled">...</span>
                                <?php endif; ?>
                                <a href="?q=<?= urlencode($query) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&page=<?= $totalPages ?>" class="page-item"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?q=<?= urlencode($query) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&page=<?= $page + 1 ?>" class="page-item">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- No Results -->
                <div class="no-results">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <h3>No messages found</h3>
                    <p style="color: var(--secondary-text); margin-top: 10px;">
                        Try different search terms or clear filters
                    </p>
                    <div class="suggestions">
                        <a href="messages.php" class="suggestion-chip">
                            <i class="fa-solid fa-eye"></i> View All Messages
                        </a>
                        <a href="?q=customer" class="suggestion-chip">
                            <i class="fa-solid fa-user"></i> Search "customer"
                        </a>
                        <a href="?q=order" class="suggestion-chip">
                            <i class="fa-solid fa-shopping-cart"></i> Search "order"
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No search criteria entered -->
            <div class="no-results">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>Enter search criteria</h3>
                <p style="color: var(--secondary-text); margin-top: 10px;">
                    Please enter a search term or select a date range to search.
                </p>
                <div class="suggestions">
                    <a href="messages.php" class="suggestion-chip">
                        <i class="fa-solid fa-eye"></i> View All Messages
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
    function viewMessage(id) {
        window.location.href = 'message-details.php?id=' + id;
    }

    function deleteMessage(id) {
        if(confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
            window.location.href = 'delete-message.php?id=' + id;
        }
    }

    // Search input effect
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        searchInput.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    }

    // Date picker effects
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Prevent empty search
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const query = document.getElementById('searchInput').value.trim();
        const dateFrom = document.querySelector('input[name="date_from"]').value;
        const dateTo = document.querySelector('input[name="date_to"]').value;

        if (!query && !dateFrom && !dateTo) {
            e.preventDefault();
            alert('Please enter a search term or select a date range');
            return false;
        }
    });
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