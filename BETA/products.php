<?php
/**
 * Core Stone Indonesia - Products Page
 * Daftar semua produk batu akik
 */
require_once 'config/config.php';

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Get products with filtering
$where = ["p.status = 'active'"];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where[] = "p.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = '%' . $_GET['search'] . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where[] = "p.price >= ?";
    $params[] = $_GET['min_price'];
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where[] = "p.price <= ?";
    $params[] = $_GET['max_price'];
}

$whereClause = implode(' AND ', $where);
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $whereClause 
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Semua Produk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Core Stone Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <span>📍 Jakarta, Indonesia</span>
                <span>📞 0812-1493-2916 | ✉️ info@corestone.id</span>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <a href="index.php" class="logo">
                    <div class="logo-icon">💎</div>
                    <span>Core Stone Indonesia</span>
                </a>
                <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="products.php" class="active">Produk</a></li>
                    <li><a href="categories.php">Kategori</a></li>
                    <li><a href="about.php">Tentang</a></li>
                    <li><a href="contact.php">Kontak</a></li>
                </ul>
                <div class="header-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="cart.php" class="btn btn-outline">🛒 Keranjang (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
                        <a href="account.php" class="btn btn-primary">Akun Saya</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1><?= $pageTitle ?></h1>
            <p>Temukan batu akik impian Anda dari koleksi lengkap kami</p>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <div class="products-layout">
                <!-- Sidebar Filter -->
                <aside class="sidebar">
                    <div class="filter-group">
                        <h3>Filter Produk</h3>
                        <form method="GET" action="products.php">
                            <div class="form-group">
                                <label>Pencarian</label>
                                <input type="text" name="search" placeholder="Cari produk..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="category">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Harga Minimum</label>
                                <input type="number" name="min_price" placeholder="Min harga" value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Harga Maksimum</label>
                                <input type="number" name="max_price" placeholder="Max harga" value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            <a href="products.php" class="btn btn-outline btn-block">Reset</a>
                        </form>
                    </div>
                </aside>

                <!-- Products Grid -->
                <main class="products-main">
                    <div class="products-header">
                        <p>Menampilkan <?= count($products) ?> produk</p>
                    </div>
                    
                    <?php if (count($products) > 0): ?>
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image"
                                 onerror="this.src='https://via.placeholder.com/300x250/10b981/ffffff?text=<?= urlencode($product['name']) ?>'">
                            <div class="product-info">
                                <div class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'Umum') ?></div>
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <div class="product-price"><?= formatRupiah($product['price']) ?></div>
                                <div class="product-stock">Stok: <?= $product['stock'] ?> pcs</div>
                                <div class="product-actions">
                                    <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
                                    <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-primary btn-sm">🛒 Beli</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🔍</div>
                        <h3>Tidak ada produk ditemukan</h3>
                        <p>Coba ubah filter pencarian Anda</p>
                    </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">
                        <div class="logo-icon">💎</div>
                        <span>Core Stone Indonesia</span>
                    </div>
                    <p>Marketplace batu akik terpercaya di Indonesia.</p>
                </div>
                <div class="footer-links">
                    <h4>Navigasi</h4>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="products.php">Produk</a></li>
                        <li><a href="categories.php">Kategori</a></li>
                        <li><a href="about.php">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Akun</h4>
                    <ul>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Daftar</a></li>
                        <li><a href="cart.php">Keranjang</a></li>
                        <li><a href="orders.php">Pesanan Saya</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Kontak</h4>
                    <ul>
                        <li>📞 0812-1493-2916</li>
                        <li>✉️ info@corestone.id</li>
                        <li>📍 Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Core Stone Indonesia. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/6281214932916?text=Halo%20Core%20Stone%20Indonesia,%20saya%20tertarik%20dengan%20produk%20Anda" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer">
        💬
    </a>

    <script src="assets/js/main.js"></script>
    <script>
    function addToCart(productId) {
        fetch('cart.php?action=add&product_id=' + productId, {
            method: 'POST',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Produk berhasil ditambahkan ke keranjang!');
                location.reload();
            } else {
                alert(data.message || 'Gagal menambahkan ke keranjang');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
    </script>
</body>
</html>
