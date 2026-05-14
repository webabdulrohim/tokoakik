<?php
/**
 * Core Stone Indonesia - About Page
 * Tentang kami
 */
require_once 'config/config.php';
$pageTitle = 'Tentang Kami';
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
                    <li><a href="about.php" class="active">Tentang</a></li>
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
            <p>Mengenal lebih dekat Core Stone Indonesia</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <h2>Tentang Core Stone Indonesia</h2>
                <p>Core Stone Indonesia adalah marketplace terpercaya yang mengkhususkan diri dalam penjualan batu akik dan batu mulia premium di Indonesia. Berdiri sejak tahun 2020, kami berkomitmen untuk menyediakan koleksi batu akik berkualitas tinggi dengan harga yang terjangkau.</p>
                
                <h3>Visi Kami</h3>
                <p>Menjadi marketplace batu akik nomor satu di Indonesia yang dipercaya oleh para pecinta batu mulia di seluruh nusantara.</p>
                
                <h3>Misi Kami</h3>
                <ul>
                    <li>Menyediakan batu akik 100% asli dengan sertifikat keaslian</li>
                    <li>Memberikan pengalaman belanja online yang mudah dan aman</li>
                    <li>Menawarkan harga kompetitif dengan kualitas terbaik</li>
                    <li>Memberikan layanan pelanggan yang responsif dan profesional</li>
                    <li>Mengirimkan pesanan dengan cepat dan aman ke seluruh Indonesia</li>
                </ul>
                
                <h3>Kenapa Memilih Kami?</h3>
                <div class="features-grid">
                    <div class="feature-box">
                        <div class="feature-icon">✅</div>
                        <h4>100% Batu Asli</h4>
                        <p>Semua produk kami dijamin keasliannya dengan sertifikat resmi</p>
                    </div>
                    <div class="feature-box">
                        <div class="feature-icon">💎</div>
                        <h4>Kualitas Premium</h4>
                        <p>Hanya memilih batu dengan kualitas terbaik untuk Anda</p>
                    </div>
                    <div class="feature-box">
                        <div class="feature-icon">🚚</div>
                        <h4>Pengiriman Cepat</h4>
                        <p>Pengiriman ke seluruh Indonesia dalam 1-3 hari kerja</p>
                    </div>
                    <div class="feature-box">
                        <div class="feature-icon">💳</div>
                        <h4>Pembayaran Aman</h4>
                        <p>Berbagai metode pembayaran yang aman melalui Tripay</p>
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
    <a href="https://wa.me/6281214932916?text=Halo%20saya%20ingin%20tahu%20lebih%20lanjut%20tentang%20Core%20Stone" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer">
        💬
    </a>

    <script src="assets/js/main.js"></script>
</body>
</html>
