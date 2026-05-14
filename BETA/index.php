<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Stone Indonesia - Marketplace Batu Akik Terpercaya</title>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php" class="btn btn-outline">🛒 Keranjang</a>
                        <a href="account.php" class="btn btn-primary">Akun Saya</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Login</a>
                        <a href="register.php" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Batu Akik Premium Kualitas Terbaik</h1>
                <p>Temukan koleksi batu akik dan batu mulia pilihan dengan kualitas terbaik dan harga terjangkau. Garansi keaslian 100%.</p>
                <div class="hero-buttons">
                    <a href="products.php" class="btn btn-white">Belanja Sekarang</a>
                    <a href="categories.php" class="btn btn-transparent">Lihat Kategori</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/hero-batu-akik.png" alt="Batu Akik Premium" onerror="this.src='https://via.placeholder.com/500x400/10b981/ffffff?text=Batu+Akik+Premium'">
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <div class="section-header">
                <h2>Kategori Populer</h2>
                <p>Pilih kategori batu akik sesuai dengan preferensi Anda</p>
            </div>
            <div class="category-grid">
                <?php
                require_once 'config/config.php';
                $categories = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC LIMIT 6")->fetchAll();
                foreach ($categories as $category):
                ?>
                <a href="category.php?slug=<?= $category['slug'] ?>" class="category-card">
                    <div class="category-icon">💎</div>
                    <h3><?= htmlspecialchars($category['name']) ?></h3>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="products">
        <div class="container">
            <div class="section-header">
                <h2>Produk Unggulan</h2>
                <p>Koleksi batu akik pilihan dengan kualitas terbaik untuk Anda</p>
            </div>
            <div class="product-grid">
                <?php
                $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                                        LEFT JOIN categories c ON p.category_id = c.id 
                                        WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT 8")
                                ->fetchAll();
                foreach ($products as $product):
                ?>
                <div class="product-card">
                    <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-image"
                         onerror="this.src='https://via.placeholder.com/300x250/10b981/ffffff?text=<?= urlencode($product['name']) ?>'">
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price"><?= formatRupiah($product['price']) ?></div>
                        <div class="product-stock">Stok: <?= $product['stock'] ?> pcs</div>
                        <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn btn-primary">
                            Lihat Detail
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <a href="products.php" class="btn btn-outline">Lihat Semua Produk</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Kenapa Memilih Kami?</h2>
                <p>Kami berkomitmen memberikan pengalaman belanja terbaik untuk Anda</p>
            </div>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">✅</div>
                    <div class="feature-content">
                        <h3>100% Asli</h3>
                        <p>Semua batu akik kami dijamin keasliannya dengan sertifikat resmi</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚚</div>
                    <div class="feature-content">
                        <h3>Pengiriman Cepat</h3>
                        <p>Pengiriman ke seluruh Indonesia dengan layanan ekspres</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <div class="feature-content">
                        <h3>Pembayaran Aman</h3>
                        <p>Berbagai metode pembayaran yang aman dan terpercaya</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎧</div>
                    <div class="feature-content">
                        <h3>Layanan 24/7</h3>
                        <p>Tim customer service siap membantu Anda kapan saja</p>
                    </div>
                </div>
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
                    <p>Marketplace batu akik terpercaya di Indonesia. Menyediakan berbagai jenis batu akik dan batu mulia berkualitas premium dengan harga terjangkau.</p>
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
</body>
</html>
