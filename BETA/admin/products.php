<?php
/**
 * Admin Products Management - Core Stone Indonesia
 */
require_once '../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add' || $action === 'edit') {
        $name = sanitizeInput($_POST['name']);
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $description = sanitizeInput($_POST['description']);
        $status = $_POST['status'];
        
        // Generate slug
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $image = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $image);
            }
        }
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, category_id, price, stock, description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $category_id, $price, $stock, $description, $image, $status]);
                $success = 'Produk berhasil ditambahkan!';
            } else {
                $id = $_POST['id'];
                if ($image) {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, category_id=?, price=?, stock=?, description=?, image=?, status=? WHERE id=?");
                    $stmt->execute([$name, $slug, $category_id, $price, $stock, $description, $image, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, category_id=?, price=?, stock=?, description=?, status=? WHERE id=?");
                    $stmt->execute([$name, $slug, $category_id, $price, $stock, $description, $status, $id]);
                }
                $success = 'Produk berhasil diupdate!';
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan produk: ' . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Produk berhasil dihapus!';
        } catch (PDOException $e) {
            $error = 'Gagal menghapus produk';
        }
    }
}

// Get all products
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.created_at DESC")->fetchAll();

// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Core Stone Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1f2937; color: white; padding: 20px; }
        .admin-nav { list-style: none; }
        .admin-nav li { margin-bottom: 8px; }
        .admin-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #9ca3af; text-decoration: none; border-radius: 10px; }
        .admin-nav a:hover, .admin-nav a.active { background: #374151; color: white; }
        .admin-content { background: #f9fafb; padding: 30px; }
        .data-table { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 25px; text-align: left; }
        th { background: #f9fafb; font-weight: 600; color: #6b7280; font-size: 13px; text-transform: uppercase; }
        tr:not(:last-child) { border-bottom: 1px solid #e5e7eb; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a href="dashboard.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; margin-bottom: 40px;">
                <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">💎</div>
                <span style="font-size: 20px; font-weight: 700;">Core Stone Admin</span>
            </a>
            <ul class="admin-nav">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php" class="active">📦 Produk</a></li>
                <li><a href="orders.php">🛒 Pesanan</a></li>
                <li><a href="../index.php" target="_blank">🌐 Lihat Website</a></li>
                <li><a href="logout.php" style="color: #ef4444;">🚪 Logout</a></li>
            </ul>
        </aside>
        <main class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 style="font-size: 28px;">Kelola Produk</h1>
                <button onclick="openModal()" class="btn btn-primary">+ Tambah Produk</button>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'"></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                            <td><?= formatRupiah($product['price']) ?></td>
                            <td><?= $product['stock'] ?></td>
                            <td><span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; background: <?= $product['status'] === 'active' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $product['status'] === 'active' ? '#065f46' : '#991b1b' ?>;"><?= ucfirst($product['status']) ?></span></td>
                            <td>
                                <a href="?edit=<?= $product['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Hapus produk ini?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-sm" style="background: #ef4444; color: white;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal Form -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px;"><?= $edit_product ? 'Edit' : 'Tambah' ?> Produk</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $edit_product ? 'edit' : 'add' ?>">
                <?php if ($edit_product): ?><input type="hidden" name="id" value="<?= $edit_product['id'] ?>"><?php endif; ?>
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category_id">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($edit_product['category_id']) && $edit_product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="price" required value="<?= $edit_product['price'] ?? '' ?>">
                </div>
                
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stock" required value="<?= $edit_product['stock'] ?? '0' ?>">
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Gambar Produk</label>
                    <input type="file" name="image" accept="image/*" <?= !$edit_product ? 'required' : '' ?>>
                    <?php if ($edit_product && $edit_product['image']): ?>
                        <img src="../assets/images/<?= $edit_product['image'] ?>" alt="" style="width: 100px; margin-top: 10px; border-radius: 8px;">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?= (isset($edit_product['status']) && $edit_product['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (isset($edit_product['status']) && $edit_product['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() { document.getElementById('productModal').classList.add('active'); }
        function closeModal() { document.getElementById('productModal').classList.remove('active'); }
        <?php if ($edit_product): ?>openModal();<?php endif; ?>
    </script>
</body>
</html>
