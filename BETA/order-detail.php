<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: my-orders.php');
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil detail pesanan
$query = "SELECT o.*, 
          CASE 
              WHEN o.status = 'pending' THEN 'Pending'
              WHEN o.status = 'paid' THEN 'Paid'
              WHEN o.status = 'completed' THEN 'Completed'
              WHEN o.status = 'cancelled' THEN 'Cancelled'
              ELSE 'Unknown'
          END as status_name,
          CASE 
              WHEN o.status = 'pending' THEN '#fef3c7'
              WHEN o.status = 'paid' THEN '#dbeafe'
              WHEN o.status = 'completed' THEN '#d1fae5'
              WHEN o.status = 'cancelled' THEN '#fee2e2'
              ELSE '#e5e7eb'
          END as color
          FROM orders o 
          WHERE o.id = ? AND o.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: my-orders.php');
    exit;
}

// Ambil item pesanan
$items_query = "SELECT oi.*, p.name, p.image, p.price 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$items_stmt = $pdo->prepare($items_query);
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Detail Pesanan - Core Stone Indonesia';
include 'includes/header.php';
?>

<style>
.detail-container {
    max-width: 1000px;
    margin: 80px auto 40px;
    padding: 0 20px;
}

.back-link {
    display: inline-block;
    color: #6b7280;
    text-decoration: none;
    margin-bottom: 20px;
    font-weight: 500;
    transition: color 0.3s;
}

.back-link:hover {
    color: #10b981;
}

.detail-card {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5f3eb;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

@media (max-width: 600px) {
    .detail-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

.order-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #059669;
}

.status-badge {
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 1rem;
    font-weight: 600;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.info-box {
    background: #f9fafb;
    padding: 20px;
    border-radius: 10px;
}

.info-label {
    font-size: 0.85rem;
    color: #6b7280;
    margin-bottom: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1rem;
    color: #1f2937;
    line-height: 1.6;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin: 30px 0;
}

.items-table th {
    background: #f9fafb;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

.items-table td {
    padding: 20px 15px;
    border-bottom: 1px solid #e5e7eb;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.product-image {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
    border: 2px solid #e5f3eb;
}

.product-name {
    font-weight: 600;
    color: #1f2937;
}

.summary-section {
    background: #f9fafb;
    padding: 25px;
    border-radius: 10px;
    margin-top: 30px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
}

.summary-row:last-child {
    border-bottom: none;
    padding-top: 15px;
    margin-top: 10px;
    border-top: 2px solid #d1d5db;
    font-size: 1.3rem;
    font-weight: 700;
    color: #059669;
}

.payment-info {
    background: #fef3c7;
    border: 1px solid #fbbf24;
    padding: 20px;
    border-radius: 10px;
    margin-top: 30px;
}

.payment-title {
    font-weight: 700;
    color: #92400e;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-details {
    color: #78350f;
    line-height: 1.6;
}

.btn-pay {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 15px 40px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.1rem;
    display: inline-block;
    margin-top: 20px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-pay:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4);
}

.btn-disabled {
    background: #9ca3af;
    cursor: not-allowed;
    pointer-events: none;
}
</style>

<div class="detail-container">
    <a href="my-orders.php" class="back-link">← Kembali ke Daftar Pesanan</a>
    
    <div class="detail-card">
        <div class="detail-header">
            <div>
                <h1 class="order-title">Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                <p style="color: #6b7280; margin-top: 5px;"><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></p>
            </div>
            <span class="status-badge" style="background: <?php echo htmlspecialchars($order['color'] ?? '#e5e7eb'); ?>; color: <?php echo $order['color'] == '#fef3c7' ? '#92400e' : '#1f2937'; ?>">
                <?php echo htmlspecialchars($order['status_name'] ?? 'Pending'); ?>
            </span>
        </div>
        
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">📦 Informasi Pengiriman</div>
                <div class="info-value">
                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?><br>
                    <?php if ($order['customer_phone']): ?>
                        📞 <?php echo htmlspecialchars($order['customer_phone']); ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="info-box">
                <div class="info-label">💳 Metode Pembayaran</div>
                <div class="info-value">
                    <?php echo htmlspecialchars($order['payment_method'] ?? 'Belum ditentukan'); ?>
                </div>
            </div>
            
            <?php if ($order['payment_code']): ?>
            <div class="info-box">
                <div class="info-label">🔢 Kode Pembayaran</div>
                <div class="info-value" style="font-family: monospace; font-size: 1.2rem;">
                    <?php echo htmlspecialchars($order['payment_code']); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <h3 style="margin-bottom: 20px; color: #1f2937;">🛒 Detail Produk</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach($items as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/images/default-product.png'); ?>" 
                                     alt="Product" class="product-image">
                                <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                            </div>
                        </td>
                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="summary-section">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>Rp <?php echo number_format($order['grand_total'] - ($order['shipping_cost'] ?? 0), 0, ',', '.'); ?></span>
            </div>
            <?php if ($order['shipping_cost']): ?>
            <div class="summary-row">
                <span>Ongkos Kirim</span>
                <span>Rp <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row">
                <span>Total Pembayaran</span>
                <span>Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></span>
            </div>
        </div>
        
        <?php if ($order['status'] == 1 && $order['tripay_code']): // Pending & Ada kode tripay ?>
        <div class="payment-info">
            <div class="payment-title">
                💡 Instruksi Pembayaran
            </div>
            <div class="payment-details">
                <p>Silakan lakukan pembayaran melalui kode berikut:</p>
                <p style="font-size: 1.5rem; font-weight: 700; font-family: monospace; margin: 15px 0;">
                    <?php echo htmlspecialchars($order['tripay_code']); ?>
                </p>
                <p>Atau klik tombol di bawah untuk pembayaran langsung:</p>
                <a href="payment.php?order=<?php echo $order['order_number']; ?>" class="btn-pay">Bayar Sekarang</a>
            </div>
        </div>
        <?php elseif ($order['status'] > 1): ?>
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #d1fae5; border-radius: 10px;">
            <p style="color: #065f46; font-weight: 600;">✅ Pesanan Anda sedang diproses. Terima kasih!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
