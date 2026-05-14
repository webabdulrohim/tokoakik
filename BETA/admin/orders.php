<?php
/**
 * Admin Orders - Core Stone Indonesia
 */
require_once '../config/config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitizeInput($_POST['new_status']);
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    header("Location: orders.php?updated=1");
    exit();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$where_clauses = [];
$params = [];

if ($status_filter) {
    $where_clauses[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_clauses[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders o 
                             JOIN users u ON o.user_id = u.id 
                             $where_sql");
$count_stmt->execute($params);
$total_orders = $count_stmt->fetch()['count'];
$total_pages = ceil($total_orders / $per_page);

// Get orders - using integer parameters directly in SQL for LIMIT and OFFSET
// Note: We don't add $per_page and $offset to $params since we're embedding them directly in SQL

// Prepare statement without binding LIMIT/OFFSET as strings
$sql = "SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        $where_sql
        ORDER BY o.created_at DESC";

// Add limit and offset directly to SQL (safe since they are integers)
$sql .= " LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Core Stone Indonesia</title>
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
        .filter-bar select,
        .filter-bar input {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
        }
        .filter-bar input[type="text"] {
            flex: 1;
            min-width: 200px;
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
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
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
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
        }
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
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
                <li><a href="orders.php" class="active">🛒 Pesanan</a></li>
                <li><a href="users.php">👥 Pengguna</a></li>
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
                    <h1>Kelola Pesanan</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Kelola semua pesanan pelanggan</p>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <form method="GET" style="display: flex; gap: 15px; flex: 1; flex-wrap: wrap;">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="paid" <?= $status_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <input type="text" name="search" placeholder="Cari no. order / pelanggan..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="orders.php" class="btn btn-outline">Reset</a>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>No. Order</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_number']) ?></td>
                                <td>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($order['user_name']) ?></div>
                                    <div style="font-size: 12px; color: #6b7280;"><?= htmlspecialchars($order['user_email']) ?></div>
                                </td>
                                <td><?= formatRupiah($order['grand_total']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
                                    <button onclick="openStatusModal(<?= $order['id'] ?>, '<?= $order['status'] ?>')" class="btn btn-primary btn-sm">Update Status</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">Tidak ada pesanan ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>" 
                       class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px;">Update Status Pesanan</h3>
            <form method="POST">
                <input type="hidden" name="order_id" id="modal_order_id">
                <div class="form-group">
                    <label>Status Baru</label>
                    <select name="new_status" id="modal_new_status" required>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeStatusModal()" class="btn btn-outline">Batal</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openStatusModal(orderId, currentStatus) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('modal_new_status').value = currentStatus;
            document.getElementById('statusModal').style.display = 'flex';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('statusModal');
            if (event.target === modal) {
                closeStatusModal();
            }
        }
    </script>
</body>
</html>
