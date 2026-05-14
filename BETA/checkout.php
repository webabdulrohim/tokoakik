<?php
/**
 * Core Stone Indonesia - Checkout Page
 * Proses checkout dan pembayaran dengan Tripay
 */
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$cartItems = $_SESSION['cart'];
$cartTotal = 0;

foreach ($cartItems as &$item) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $cartTotal += $item['subtotal'];
}

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $fullName = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $province = sanitizeInput($_POST['province']);
    $postalCode = sanitizeInput($_POST['postal_code']);
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    $shippingMethod = sanitizeInput($_POST['shipping_method']);
    
    // Calculate shipping cost (simplified)
    $shippingCost = 15000; // Default shipping cost
    
    $grandTotal = $cartTotal + $shippingCost;
    
    // Create order
    $orderCode = 'CS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    try {
        $pdo->beginTransaction();
        
        // Insert order dengan semua kolom yang diperlukan
        $orderStmt = $pdo->prepare("INSERT INTO orders (
            order_code, user_id, name, full_name, email, phone, 
            address, city, province, subdistrict, postal_code, 
            courier, shipping_method, service, weight, shipping_cost,
            subtotal, total, grand_total, 
            payment_method, status, customer_notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        
        // Set default values for missing data
        $subdistrict = ''; // Kecamatan
        $courier = ''; // Kurir
        $service = ''; // Layanan
        $weight = 0; // Berat total
        $customerNotes = $_POST['notes'] ?? '';
        
        $orderStmt->execute([
            $orderCode, $userId, $fullName, $fullName, $email, $phone, 
            $address, $city, $province, $subdistrict, $postalCode,
            $courier, $shippingMethod, $service, $weight, $shippingCost,
            $cartTotal, $grandTotal, $grandTotal,
            $paymentMethod, $customerNotes
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insert order items
        foreach ($cartItems as $item) {
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, name, price, quantity, subtotal) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
            $itemStmt->execute([
                $orderId, $item['product_id'], $item['name'], $item['price'], $item['quantity'], $item['subtotal']
            ]);
            
            // Update product stock
            $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stockStmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        $pdo->commit();
        
        // Redirect to payment or success page
        $_SESSION['order_id'] = $orderId;
        $_SESSION['order_code'] = $orderCode;
        
        // Process Tripay payment
        header('Location: payment.php?order=' . $orderCode);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
    }
}

$pageTitle = 'Checkout';
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
            </div>
        </div>
    </header>

    <!-- Checkout Section -->
    <section class="checkout-section">
        <div class="container">
            <h1><?= $pageTitle ?></h1>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="checkout-layout">
                    <!-- Checkout Form -->
                    <div class="checkout-form">
                        <div class="form-section">
                            <h3>📦 Informasi Pengiriman</h3>
                            
                            <div class="form-group">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nomor WhatsApp *</label>
                                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Alamat Lengkap *</label>
                                <textarea name="address" rows="3" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Kota/Kabupaten *</label>
                                    <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Provinsi *</label>
                                    <input type="text" name="province" value="<?= htmlspecialchars($user['province'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Kode Pos *</label>
                                <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>🚚 Metode Pengiriman</h3>
                            <div class="form-group">
                                <select name="shipping_method" required>
                                    <option value="regular">Regular (Rp 15.000)</option>
                                    <option value="express">Express (Rp 25.000)</option>
                                    <option value="cargo">Cargo (Rp 35.000)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>💳 Metode Pembayaran</h3>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="bank_transfer" checked>
                                    <div class="payment-card">
                                        <strong>Transfer Bank</strong>
                                        <small>BCA, Mandiri, BRI, BNI</small>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="ewallet">
                                    <div class="payment-card">
                                        <strong>E-Wallet</strong>
                                        <small>Gopay, OVO, DANA, ShopeePay</small>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="qris">
                                    <div class="payment-card">
                                        <strong>QRIS</strong>
                                        <small>Scan QR Code</small>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="alfamart">
                                    <div class="payment-card">
                                        <strong>Alfamart</strong>
                                        <small>Bayar di toko Alfamart</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h3>Ringkasan Pesanan</h3>
                        
                        <div class="order-items">
                            <?php foreach ($cartItems as $item): ?>
                            <div class="order-item">
                                <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     onerror="this.src='https://via.placeholder.com/60x60/10b981/ffffff?text=Product'">
                                <div>
                                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                                    <p><?= $item['quantity'] ?> x <?= formatRupiah($item['price']) ?></p>
                                </div>
                                <span><?= formatRupiah($item['subtotal']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?= formatRupiah($cartTotal) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Ongkos Kirim</span>
                            <span>Rp 15.000</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?= formatRupiah($cartTotal + 15000) ?></span>
                        </div>
                        
                        <button type="submit" name="checkout" class="btn btn-success btn-block btn-lg">
                            🔒 Buat Pesanan & Bayar
                        </button>
                        
                        <p class="secure-note">
                            🔒 Pembayaran diproses dengan aman melalui Tripay
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>
