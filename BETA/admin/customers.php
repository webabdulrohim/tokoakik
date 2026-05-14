<?php
/**
 * Admin Customers - Core Stone Indonesia
 */
require_once '../config/config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
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
    $where_clauses[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count with order statistics
$count_stmt = $pdo->prepare("SELECT COUNT(DISTINCT u.id) as count 
                             FROM users u 
                             LEFT JOIN orders o ON u.id = o.user_id 
                             WHERE u.role = 'user' $where_sql");
$count_stmt->execute($params);
$total_customers = $count_stmt->fetch()['count'];
$total_pages = ceil($total_customers / $per_page);

// Get customers with order stats
$sql = "SELECT u.*,
               COUNT(o.id) as total_orders,
               COALESCE(SUM(o.grand_total), 0) as total_spent,
               MAX(o.created_at) as last_order_date
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE u.role = 'user' $where_sql
        GROUP BY u.id
        ORDER BY total_spent DESC
        LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelanggan - Core Stone Indonesia</title>
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
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
        .stat-label {
            color: #6b7280;
            font-size: 13px;
            margin-top: 5px;
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
        .customer-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
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
                <li><a href="users.php">👥 Pengguna</a></li>
                <li><a href="customers.php" class="active">👤 Pelanggan</a></li>
                <li><a href="settings.php">⚙️ Pengaturan</a></li>
                <li><a href="../index.php" target="_blank">🌐 Lihat Website</a></li>
                <li><a href="logout.php" style="color: #ef4444;">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1>Daftar Pelanggan</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Kelola dan lihat statistik pelanggan</p>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($total_customers) ?></div>
                    <div class="stat-label">Total Pelanggan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php
                        $total_revenue = array_sum(array_column($customers, 'total_spent'));
                        echo formatRupiah($total_revenue);
                        ?>
                    </div>
                    <div class="stat-label">Total Pendapatan dari Pelanggan</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" style="display: flex; gap: 15px; flex: 1; flex-wrap: wrap;">
                    <input type="text" name="search" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <a href="customers.php" class="btn btn-outline">Reset</a>
                </form>
            </div>

            <!-- Customers Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Email</th>
                            <th>Total Pesanan</th>
                            <th>Total Belanja</th>
                            <th>Terakhir Order</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($customers) > 0): ?>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="customer-avatar"><?= strtoupper(substr($customer['name'], 0, 1)) ?></div>
                                        <div style="font-weight: 600;"><?= htmlspecialchars($customer['name']) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($customer['email']) ?></td>
                                <td><?= $customer['total_orders'] ?> pesanan</td>
                                <td><?= formatRupiah($customer['total_spent']) ?></td>
                                <td>
                                    <?php if ($customer['last_order_date']): ?>
                                        <?= date('d M Y', strtotime($customer['last_order_date'])) ?>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">Belum pernah order</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="orders.php?search=<?= urlencode($customer['email']) ?>" class="btn btn-outline btn-sm">Lihat Pesanan</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">Tidak ada pelanggan ditemukan</td>
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
