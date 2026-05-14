<?php
/**
 * Core Stone Indonesia - Payment Page
 * Proses pembayaran dengan Tripay API
 */
require_once 'config/config.php';

if (!isset($_GET['order']) || empty($_GET['order'])) {
    redirect('orders.php');
}

$orderCode = sanitizeInput($_GET['order']);

// Get order details
$stmt = $pdo->prepare("SELECT o.*, u.email as user_email FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       WHERE o.order_code = ?");
$stmt->execute([$orderCode]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

// Only process if order is pending
if ($order['status'] !== 'pending') {
    redirect('order-success.php?order=' . $orderCode);
}

$paymentUrl = '';
$errorMessage = '';

// Process Tripay payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' || !isset($_SESSION['tripay_' . $orderCode])) {
    $merchantCode = TRIPAY_MERCHANT_CODE;
    $privateKey = TRIPAY_PRIVATE_KEY;
    
    if (empty($merchantCode) || empty($privateKey)) {
        // Demo mode - show manual payment instructions
        $demoMode = true;
    } else {
        // Real Tripay API call
        $tripayData = [
            'method'         => 'POST',
            'url'            => 'https://tripay.co.id/api-sandbox/transaction/create', // Use production URL for live mode
            'data'           => [
                'merchant_code'   => $merchantCode,
                'amount'          => $order['total'],
                'transaction_time'=> date('Y-m-d H:i:s'),
                'customer_name'   => $order['full_name'],
                'customer_email'  => $order['email'],
                'customer_phone'  => $order['phone'],
                'order_items'     => [],
                'return_url'      => APP_URL . '/payment-success.php?order=' . $orderCode,
                'signature'       => hash_hmac('sha256', $merchantCode.$order['total'].date('Y-m-d H:i:s'), $privateKey),
                'channel_code'    => '', // Will be selected by customer
            ]
        ];
        
        // Get order items
        $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll();
        
        foreach ($items as $item) {
            $tripayData['data']['order_items'][] = [
                'sku_code'    => 'PROD-' . $item['product_id'],
                'price'       => $item['price'],
                'quantity'    => $item['quantity'],
                'name'        => $item['name'],
            ];
        }
        
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $tripayData['url']);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($tripayData['data']));
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $privateKey]);
        
        $response = curl_exec($curlHandle);
        curl_close($curlHandle);
        
        $result = json_decode($response, true);
        
        if ($result && isset($result['data']) && isset($result['data']['checkout_url'])) {
            $paymentUrl = $result['data']['checkout_url'];
            $_SESSION['tripay_' . $orderCode] = $paymentUrl;
        } else {
            $errorMessage = 'Gagal membuat transaksi pembayaran';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - <?= $orderCode ?> - Core Stone Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="header-main">
            <div class="container">
                <a href="index.php" class="logo">
                    <div class="logo-icon">💎</div>
                    <span>Core Stone Indonesia</span>
                </a>
            </div>
        </div>
    </header>

    <section class="payment-section">
        <div class="container">
            <div class="payment-box">
                <h1>🔒 Pembayaran Pesanan</h1>
                <p class="order-code">Kode Pesanan: <strong><?= $orderCode ?></strong></p>
                
                <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= $errorMessage ?></div>
                <?php endif; ?>
                
                <div class="order-summary-payment">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="summary-row">
                        <span>Total Pembayaran</span>
                        <span class="total"><?= formatRupiah($order['total']) ?></span>
                    </div>
                </div>
                
                <?php if ($paymentUrl): ?>
                <div class="payment-instruction">
                    <p>Silakan klik tombol di bawah untuk melanjutkan ke halaman pembayaran Tripay:</p>
                    <a href="<?= $paymentUrl ?>" target="_blank" class="btn btn-success btn-lg btn-block">
                        🔗 Lanjut ke Pembayaran
                    </a>
                </div>
                <?php elseif (isset($demoMode)): ?>
                <div class="demo-payment">
                    <h3>📋 Mode Demo - Instruksi Pembayaran</h3>
                    <p>Karena API Tripay belum dikonfigurasi, silakan transfer ke rekening berikut:</p>
                    <div class="bank-info">
                        <div class="bank-item">
                            <strong>Bank BCA</strong><br>
                            1234567890<br>
                            a.n Core Stone Indonesia
                        </div>
                        <div class="bank-item">
                            <strong>Bank Mandiri</strong><br>
                            9876543210<br>
                            a.n Core Stone Indonesia
                        </div>
                    </div>
                    <p class="note">Konfirmasi: 0812-1493-2916 (WhatsApp)</p>
                    <a href="order-success.php?order=<?= $orderCode ?>" class="btn btn-primary btn-block">
                        Saya Sudah Transfer
                    </a>
                </div>
                <?php else: ?>
                <div class="payment-loading">
                    <p>Memproses pembayaran...</p>
                    <form method="POST" action="">
                        <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
                    </form>
                </div>
                <?php endif; ?>
                
                <div class="payment-note">
                    <p>⏰ Silakan selesaikan pembayaran dalam waktu 24 jam</p>
                    <p>📞 Butuh bantuan? Hubungi 0812-1493-2916</p>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>
