<?php
/**
 * Admin Users - Core Stone Indonesia
 */
require_once '../config/config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Handle delete user
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->execute([$user_id]);
    header("Location: users.php?deleted=1");
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'user' $where_sql");
$count_stmt->execute($params);
$total_users = $count_stmt->fetch()['count'];
$total_pages = ceil($total_users / $per_page);

// Get users
$sql = "SELECT * FROM users WHERE role = 'user' $where_sql ORDER BY created_at DESC LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Core Stone Indonesia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }
        .admin-sidebar {
            background: #1f2937;
            color: white;
            padding: 20px;
        }
        .admin-sidebar .logo {
            color: white;
            margin-bottom: 40px;
            padding: 10px 0;
        }
        .admin-sidebar .logo-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .admin-nav {
            list-style: none;
        }
        .admin-nav li {
            margin-bottom: 8px;
        }
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #9ca3af;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .admin-nav a:hover,
        .admin-nav a.active {
            background: #374151;
            color: white;
        }
        .admin-nav a.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .admin-content {
            background: #f9fafb;
            padding: 30px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .admin-header h1 {
            font-size: 28px;
            color: #1f2937;
        }
        .filter-bar {
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #10b981;
            color: #10b981;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px 25px;
            text-align: left;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
            text-transform: uppercase;
        }
        tr:not(:last-child) {
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover {
            background: #f9fafb;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }
        .pagination a {
            padding: 10px 15px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            text-decoration: none;
            color: #1f2937;
            transition: all 0.3s;
        }
        .pagination a:hover,
        .pagination a.active {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        @media (max-width: 1024px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="dashboard.php" class="logo" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                <div class="logo-icon" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">💎</div>
                <span style="font-size: 20px; font-weight: 700;">Core Stone Admin</span>
            </a>
            <ul class="admin-nav">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php">📦 Produk</a></li>
                <li><a href="categories.php">📁 Kategori</a></li>
                <li><a href="orders.php">🛒 Pesanan</a></li>
                <li><a href="users.php" class="active">👥 Pengguna</a></li>
                <li><a href="customers.php">👤 Pelanggan</a></li>
                <li><a href="settings.php">⚙️ Pengaturan</a></li>
                <li><a href="../index.php" target="_blank">🌐 Lihat Website</a></li>
                <li><a href="logout.php" style="color: #ef4444;">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1>Kelola Pengguna</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Kelola semua pengguna terdaftar</p>
                </div>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Pengguna berhasil dihapus!</div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" style="display: flex; gap: 15px; flex: 1; flex-wrap: wrap;">
                    <input type="text" name="search" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <a href="users.php" class="btn btn-outline">Reset</a>
                </form>
            </div>

            <!-- Users Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <a href="order-detail.php?id=<?= $user['id'] ?>" class="btn btn-outline btn-sm">Lihat Pesanan</a>
                                    <a href="?delete=1&id=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px;">Tidak ada pengguna ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                       class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
