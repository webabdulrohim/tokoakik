<?php
/**
 * Admin Categories - Core Stone Indonesia
 */
require_once '../config/config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

$success_message = '';
$error_message = '';

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        if ($stmt->execute([$name, $description])) {
            $success_message = 'Kategori berhasil ditambahkan!';
        } else {
            $error_message = 'Gagal menambahkan kategori.';
        }
    } else {
        $error_message = 'Nama kategori tidak boleh kosong.';
    }
}

// Handle delete category
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $category_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$category_id])) {
        header("Location: categories.php?deleted=1");
        exit();
    }
}

// Get all categories
$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id 
                           GROUP BY c.id 
                           ORDER BY c.name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Core Stone Indonesia</title>
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
            margin-bottom: 25px;
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
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .form-card h3 {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
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
        .form-group input,
        .form-group textarea {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
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
                <li><a href="categories.php" class="active">📁 Kategori</a></li>
                <li><a href="orders.php">🛒 Pesanan</a></li>
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
                    <h1>Kelola Kategori</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Kelola kategori produk</p>
                </div>
            </div>

            <?php if ($success_message || isset($_GET['deleted'])): ?>
            <div class="alert alert-success"><?= $success_message ?: 'Kategori berhasil dihapus!' ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <!-- Add Category Form -->
            <div class="form-card">
                <h3>Tambah Kategori Baru</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" name="name" required placeholder="Masukkan nama kategori">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" placeholder="Masukkan deskripsi kategori (opsional)"></textarea>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">Tambah Kategori</button>
                </form>
            </div>

            <!-- Categories Table -->
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= htmlspecialchars($category['description'] ?? '-') ?></td>
                                <td><?= $category['product_count'] ?> produk</td>
                                <td>
                                    <a href="products.php?category=<?= $category['id'] ?>" class="btn btn-outline btn-sm">Lihat Produk</a>
                                    <a href="?delete=1&id=<?= $category['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px;">Belum ada kategori</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
