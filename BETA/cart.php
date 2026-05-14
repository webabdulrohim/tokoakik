<?php
/**
 * Core Stone Indonesia - Cart Page
 * Keranjang belanja
 */
require_once 'config/config.php';

// Handle cart actions
if (isset($_GET['action'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    switch ($_GET['action']) {
        case 'add':
            if (!isLoggedIn()) {
                echo json_encode(['success' => false, 'require_login' => true, 'message' => 'Silakan login terlebih dahulu']);
                exit;
            }
            
            $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
            $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
            
            if ($productId > 0) {
                // Check product stock
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                
                if ($product) {
                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId]['quantity'] += $quantity;
                    } else {
                        $_SESSION['cart'][$productId] = [
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'name' => $product['name'],
                            'price' => $product['price'],
                            'image' => $product['image'],
                            'stock' => $product['stock']
                        ];
                    }
                    echo json_encode(['success' => true, 'message' => 'Produk ditambahkan ke keranjang']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID produk tidak valid']);
            }
            exit;
            
        case 'remove':
            $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
            if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
            redirect('cart.php');
            
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                foreach ($_POST['quantities'] as $productId => $quantity) {
                    $productId = (int)$productId;
                    $quantity = (int)$quantity;
                    if (isset($_SESSION['cart'][$productId]) && $quantity > 0) {
                        $_SESSION['cart'][$productId]['quantity'] = $quantity;
                    } elseif ($quantity <= 0) {
                        unset($_SESSION['cart'][$productId]);
                    }
                }
            }
            redirect('cart.php');
            
        case 'clear':
            unset($_SESSION['cart']);
            redirect('cart.php');
    }
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cartItems = $_SESSION['cart'];
$cartTotal = 0;

foreach ($cartItems as &$item) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $cartTotal += $item['subtotal'];
}

$pageTitle = 'Keranjang Belanja';
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
                        <a href="cart.php" class="btn btn-outline active">🛒 Keranjang (<?= count($cartItems) ?>)</a>
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
            <p>Review pesanan Anda sebelum melanjutkan ke pembayaran</p>
        </div>
    </section>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <?php if (count($cartItems) > 0): ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    <form method="POST" action="cart.php?action=update">
                        <div class="cart-table">
                            <div class="cart-row header">
                                <div class="col-product">Produk</div>
                                <div class="col-price">Harga</div>
                                <div class="col-quantity">Jumlah</div>
                                <div class="col-subtotal">Subtotal</div>
                                <div class="col-action">Aksi</div>
                            </div>
                            
                            <?php foreach ($cartItems as $itemId => $item): ?>
                            <div class="cart-row">
                                <div class="col-product">
                                    <div class="product-info-cart">
                                        <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             onerror="this.src='https://via.placeholder.com/80x80/10b981/ffffff?text=Product'">
                                        <div>
                                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-price"><?= formatRupiah($item['price']) ?></div>
                                <div class="col-quantity">
                                    <input type="number" name="quantities[<?= $itemId ?>]" 
                                           value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" 
                                           onchange="this.form.submit()">
                                </div>
                                <div class="col-subtotal"><?= formatRupiah($item['subtotal']) ?></div>
                                <div class="col-action">
                                    <a href="cart.php?action=remove&product_id=<?= $itemId ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus produk dari keranjang?')">Hapus</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-outline">Lanjutkan Belanja</a>
                            <button type="submit" class="btn btn-primary">Update Keranjang</button>
                            <a href="cart.php?action=clear" class="btn btn-danger" onclick="return confirm('Kosongkan keranjang?')">Kosongkan Keranjang</a>
                        </div>
                    </form>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?= formatRupiah($cartTotal) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span>Dihitung saat checkout</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?= formatRupiah($cartTotal) ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-success btn-block btn-lg">
                        Lanjutkan ke Checkout
                    </a>
                    
                    <div class="trust-badges">
                        <div class="badge">🔒 Pembayaran Aman</div>
                        <div class="badge">✅ Garansi Asli</div>
                        <div class="badge">🚚 Pengiriman Cepat</div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🛒</div>
                <h3>Keranjang Anda Kosong</h3>
                <p>Belum ada produk di keranjang belanja Anda</p>
                <a href="products.php" class="btn btn-primary btn-lg">Mulai Belanja</a>
            </div>
            <?php endif; ?>
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
    <a href="https://wa.me/6281214932916?text=Halo%20saya%20butuh%20bantuan%20dengan%20keranjang%20belanja" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer">
        💬
    </a>

    <script src="assets/js/main.js"></script>
</body>
</html>
