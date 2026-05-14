<?php
/**
 * Core Stone Indonesia - Product Detail Page
 * Detail produk batu akik
 */
require_once 'config/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('products.php');
}

$productId = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products.php');
}

// Get related products
$relatedStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' LIMIT 4");
$relatedStmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $relatedStmt->fetchAll();

$pageTitle = htmlspecialchars($product['name']);
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
                    <li><a href="products.php">Produk</a></li>
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

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Beranda</a> / 
            <a href="products.php">Produk</a> / 
            <a href="category.php?slug=<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name'] ?? 'Produk') ?></a> / 
            <span><?= $pageTitle ?></span>
        </div>
    </div>

    <!-- Product Detail -->
    <section class="product-detail-section">
        <div class="container">
            <div class="product-detail-grid">
                <div class="product-gallery">
                    <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="main-image"
                         onerror="this.src='https://via.placeholder.com/600x500/10b981/ffffff?text=<?= urlencode($product['name']) ?>'">
                </div>
                
                <div class="product-info-detail">
                    <div class="product-category-badge"><?= htmlspecialchars($product['category_name'] ?? 'Umum') ?></div>
                    <h1><?= $pageTitle ?></h1>
                    <div class="product-price-large"><?= formatRupiah($product['price']) ?></div>
                    
                    <div class="product-meta">
                        <div class="meta-item">
                            <span class="meta-label">Stok:</span>
                            <span class="meta-value"><?= $product['stock'] ?> pcs tersedia</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">SKU:</span>
                            <span class="meta-value"><?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    
                    <div class="product-description">
                        <h3>Deskripsi Produk</h3>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                    
                    <div class="product-actions-detail">
                        <div class="quantity-selector">
                            <label>Jumlah:</label>
                            <div class="quantity-controls">
                                <button type="button" onclick="decreaseQty()">-</button>
                                <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                <button type="button" onclick="increaseQty(<?= $product['stock'] ?>)">+</button>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-primary btn-lg">
                                🛒 Tambah ke Keranjang
                            </button>
                            <button onclick="buyNow(<?= $product['id'] ?>)" class="btn btn-success btn-lg">
                                ⚡ Beli Sekarang
                            </button>
                        </div>
                        
                        <div class="share-links">
                            <span>Bagikan:</span>
                            <a href="#" onclick="shareToWA()">WhatsApp</a>
                            <a href="#" onclick="shareToFB()">Facebook</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (count($relatedProducts) > 0): ?>
    <section class="related-products">
        <div class="container">
            <div class="section-header">
                <h2>Produk Terkait</h2>
                <p>Mungkin Anda juga tertarik dengan produk ini</p>
            </div>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $related): ?>
                <div class="product-card">
                    <img src="assets/images/<?= htmlspecialchars($related['image']) ?>" 
                         alt="<?= htmlspecialchars($related['name']) ?>" 
                         class="product-image"
                         onerror="this.src='https://via.placeholder.com/300x250/10b981/ffffff?text=<?= urlencode($related['name']) ?>'">
                    <div class="product-info">
                        <h3 class="product-title"><?= htmlspecialchars($related['name']) ?></h3>
                        <div class="product-price"><?= formatRupiah($related['price']) ?></div>
                        <a href="product-detail.php?id=<?= $related['id'] ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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
    <a href="https://wa.me/6281214932916?text=Halo%20saya%20tertarik%20dengan%20<?= urlencode($product['name']) ?>" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer">
        💬
    </a>

    <script src="assets/js/main.js"></script>
    <script>
    function increaseQty(max) {
        const qtyInput = document.getElementById('quantity');
        let qty = parseInt(qtyInput.value);
        if (qty < max) {
            qtyInput.value = qty + 1;
        }
    }
    
    function decreaseQty() {
        const qtyInput = document.getElementById('quantity');
        let qty = parseInt(qtyInput.value);
        if (qty > 1) {
            qtyInput.value = qty - 1;
        }
    }
    
    function addToCart(productId) {
        const quantity = document.getElementById('quantity').value;
        fetch('cart.php?action=add&product_id=' + productId + '&quantity=' + quantity, {
            method: 'POST',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Produk berhasil ditambahkan ke keranjang!');
                location.reload();
            } else {
                if (data.require_login) {
                    if (confirm('Anda harus login terlebih dahulu untuk menambahkan ke keranjang. Login sekarang?')) {
                        window.location.href = 'login.php';
                    }
                } else {
                    alert(data.message || 'Gagal menambahkan ke keranjang');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
    
    function buyNow(productId) {
        const quantity = document.getElementById('quantity').value;
        // Direct to checkout with this product
        window.location.href = 'checkout.php?product_id=' + productId + '&quantity=' + quantity;
    }
    
    function shareToWA() {
        const text = encodeURIComponent('Lihat produk ini: <?= addslashes($product['name']) ?> - <?= formatRupiah($product['price']) ?>');
        const url = encodeURIComponent(window.location.href);
        window.open(`https://wa.me/?text=${text}%0A${url}`, '_blank');
    }
    
    function shareToFB() {
        const url = encodeURIComponent(window.location.href);
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
    }
    </script>
</body>
</html>
