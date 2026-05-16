<?php
// search-users.php
session_start();
require_once 'db.php';

$pdo = getDB();

// معالجة البحث
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
$totalResults = 0;

// منطق البحث - البحث بالاسم فقط
if (!empty($query)) {
    $searchTerm = '%' . $query . '%';

    $stmt = $pdo->prepare("
        SELECT 
            u.user_id as id,
            u.name,
            u.email,
            u.role::text as role,
            u.status::text as status,
            u.avatar,
            u.created_at as joined,
            COUNT(o.order_id) as orders_count,
            COALESCE(SUM(o.total), 0) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id AND o.status != 'cancelled'
        WHERE u.name ILIKE ?
        GROUP BY u.user_id, u.name, u.email, u.role, u.status, u.avatar, u.created_at
        ORDER BY u.name ASC
    ");
    $stmt->execute([$searchTerm]);
    $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // تنسيق النتائج
    foreach ($usersData as $user) {
        $results[$user['id']] = [
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'status' => $user['status'],
                'avatar' => $user['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=F8BBD0&color=000&size=40',
                'joined' => $user['joined'],
                'orders' => (int)$user['orders_count'],
                'spent' => (float)$user['total_spent']
        ];
    }

    $totalResults = count($results);
}

// Pagination للنتائج
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$totalPages = $totalResults > 0 ? ceil($totalResults / $perPage) : 1;
$offset = ($page - 1) * $perPage;
$paginatedResults = array_slice($results, $offset, $perPage, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users · Teddy Shop</title>
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

        /* Search Section */
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
            flex: 1;
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

        /* Results Header */
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

        /* Table */
        .table-container {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 30px;
            box-shadow: 0 4px 15px var(--shadow);
            overflow-x: auto;
            animation: fadeInUp 0.8s ease;
        }
        .users-table { width: 100%; border-collapse: collapse; }
        .users-table th {
            text-align: left;
            padding: 15px 10px;
            color: var(--secondary-text);
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid rgba(128,128,128,0.1);
        }
        .users-table td {
            padding: 15px 10px;
            border-bottom: 1px solid rgba(128,128,128,0.1);
            color: var(--text-color);
            vertical-align: middle;
        }
        .users-table tbody tr:hover td {
            background-color: rgba(248, 187, 208, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--lavender);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .role-badge {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .role-admin { background: var(--lavender); color: #000; }
        .role-customer { background: var(--pink); color: #000; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .status-active { background: rgba(76, 175, 80, 0.2); color: #4CAF50; }
        .status-inactive { background: rgba(244, 67, 54, 0.2); color: #F44336; }

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
        }

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

        /* No Results */
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
                Search Users
            </div>

            <form action="search-users.php" method="GET" id="searchForm">
                <div class="search-row">
                    <div class="search-input-group">
                        <label><i class="fa-solid fa-search"></i> Search by Name</label>
                        <input type="text"
                               name="q"
                               id="searchInput"
                               value="<?= htmlspecialchars($query) ?>"
                               placeholder="Enter user name to search...">
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                    <a href="search-users.php" class="search-btn reset-btn">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <?php if (!empty($query)): ?>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-count">
                    Found <strong><?= $totalResults ?></strong> user<?= $totalResults != 1 ? 's' : '' ?>
                    with name containing "<strong><?= htmlspecialchars($query) ?></strong>"
                </div>
                <a href="users.php" class="filter-chip" style="padding: 8px 20px; background: var(--lavender); border-radius: 50px; color: #000; text-decoration: none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Users
                </a>
            </div>

            <?php if ($totalResults > 0): ?>
                <!-- Results Table -->
                <div class="table-container">
                    <table class="users-table">
                        <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Orders</th>
                            <th>Spent</th>
                            <th>Actions</th>
                        </thead>
                        <tbody>
                        <?php foreach($paginatedResults as $id => $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" onerror="this.src='images/teddy4.png'">
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($user['name']) ?></div>
                                            <div style="font-size: 11px; color: var(--secondary-text);"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                <span class="role-badge role-<?= strtolower($user['role']) ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                                </td>
                                <td>
                                <span class="status-badge status-<?= $user['status'] ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['joined'])) ?></td>
                                <td style="text-align: center;"><?= $user['orders'] ?></td>
                                <td class="product-price">$<?= number_format($user['spent'], 2) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn view" onclick="viewUser(<?= $id ?>)" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="action-btn edit" onclick="editUser(<?= $id ?>)" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="action-btn delete" onclick="deleteUser(<?= $id ?>)" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-section">
                            <div class="pagination-info">
                                Showing <strong><?= $offset + 1 ?>-<?= min($offset + $perPage, $totalResults) ?></strong> of <strong><?= $totalResults ?></strong> results
                            </div>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" class="page-item"><i class="fa-solid fa-chevron-left"></i></a>
                                <?php else: ?>
                                    <span class="page-item disabled"><i class="fa-solid fa-chevron-left"></i></span>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);

                                if ($startPage > 1): ?>
                                    <a href="?q=<?= urlencode($query) ?>&page=1" class="page-item">1</a>
                                    <?php if ($startPage > 2): ?>
                                        <span class="page-item disabled">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>"
                                       class="page-item <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                                <?php endfor; ?>

                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <span class="page-item disabled">...</span>
                                    <?php endif; ?>
                                    <a href="?q=<?= urlencode($query) ?>&page=<?= $totalPages ?>" class="page-item"><?= $totalPages ?></a>
                                <?php endif; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" class="page-item"><i class="fa-solid fa-chevron-right"></i></a>
                                <?php else: ?>
                                    <span class="page-item disabled"><i class="fa-solid fa-chevron-right"></i></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- No Results -->
                <div class="no-results">
                    <i class="fa-solid fa-users-slash"></i>
                    <h3>No users found</h3>
                    <p style="color: var(--secondary-text); margin-top: 10px;">
                        No users found with name containing "<strong><?= htmlspecialchars($query) ?></strong>"
                    </p>
                    <div class="suggestions">
                        <a href="users.php" class="suggestion-chip">
                            <i class="fa-solid fa-eye"></i> View All Users
                        </a>
                        <a href="search-users.php" class="suggestion-chip">
                            <i class="fa-solid fa-arrow-left"></i> Try Another Search
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No search criteria entered -->
            <div class="no-results">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>Enter a name to search</h3>
                <p style="color: var(--secondary-text); margin-top: 10px;">
                    Please enter a user name to search for.
                </p>
                <div class="suggestions">
                    <a href="users.php" class="suggestion-chip">
                        <i class="fa-solid fa-eye"></i> View All Users
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
    function viewUser(id) {
        window.location.href = 'user-details.php?id=' + id;
    }

    function editUser(id) {
        window.location.href = 'edit-user.php?id=' + id;
    }

    function deleteUser(id) {
        if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            window.location.href = 'delete-user.php?id=' + id;
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

    // Prevent empty search
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const query = document.getElementById('searchInput').value.trim();
        if (!query) {
            e.preventDefault();
            alert('Please enter a name to search');
            return false;
        }
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
                applyTheme(this.checked);
                localStorage.setItem('theme', this.checked ? 'dark' : 'light');
            });
        }
    })();
</script>
</body>
</html>