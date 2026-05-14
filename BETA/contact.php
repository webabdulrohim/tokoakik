<?php
/**
 * Core Stone Indonesia - Contact Page
 * Halaman kontak
 */
require_once 'config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Mohon lengkapi semua field yang wajib diisi';
    } else {
        // Save contact message to database or send email
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message, status, created_at) 
                                   VALUES (?, ?, ?, ?, ?, 'unread', NOW())");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = 'Terima kasih! Pesan Anda telah terkirim. Kami akan segera menghubungi Anda.';
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan saat mengirim pesan';
        }
    }
}

$pageTitle = 'Kontak Kami';
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
                    <li><a href="contact.php" class="active">Kontak</a></li>
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
            <p>Hubungi kami untuk pertanyaan atau bantuan</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="contact-layout">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h3>Informasi Kontak</h3>
                    <p>Silakan hubungi kami melalui saluran berikut:</p>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h4>Telepon / WhatsApp</h4>
                            <p>0812-1493-2916</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div>
                            <h4>Email</h4>
                            <p>info@corestone.id</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <h4>Alamat</h4>
                            <p>Jakarta, Indonesia</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">⏰</div>
                        <div>
                            <h4>Jam Operasional</h4>
                            <p>Senin - Sabtu: 08:00 - 20:00 WIB</p>
                            <p>Minggu: 10:00 - 18:00 WIB</p>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form">
                    <h3>Kirim Pesan</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="name" required placeholder="Masukkan nama Anda">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" required placeholder="contoh@email.com">
                            </div>
                            <div class="form-group">
                                <label>Nomor WhatsApp</label>
                                <input type="tel" name="phone" placeholder="0812-xxxx-xxxx">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Subjek</label>
                            <input type="text" name="subject" placeholder="Perihal pesan Anda">
                        </div>
                        
                        <div class="form-group">
                            <label>Pesan *</label>
                            <textarea name="message" rows="5" required placeholder="Tulis pesan Anda di sini..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            📤 Kirim Pesan
                        </button>
                    </form>
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
    <a href="https://wa.me/6281214932916?text=Halo%20saya%20ingin%20bertanya" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer">
        💬
    </a>

    <script src="assets/js/main.js"></script>
</body>
</html>
