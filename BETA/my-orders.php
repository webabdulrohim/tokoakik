<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];

// Ambil semua pesanan user
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
          WHERE o.user_id = ? 
          ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Pesanan Saya - Core Stone Indonesia';
include 'includes/header.php';
?>

<style>
.orders-container {
    max-width: 1200px;
    margin: 80px auto 40px;
    padding: 0 20px;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 30px;
    text-align: center;
}

.orders-grid {
    display: grid;
    gap: 20px;
}

.order-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s;
}

.order-card:hover {
    transform: translateY(-3px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5f3eb;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.order-number {
    font-size: 1.3rem;
    font-weight: 700;
    color: #059669;
}

.order-date {
    color: #6b7280;
    font-size: 0.9rem;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
}

.order-body {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: center;
}

@media (max-width: 768px) {
    .order-body {
        grid-template-columns: 1fr;
    }
}

.order-items {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.item-preview {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
    border: 2px solid #e5f3eb;
}

.items-count {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 10px;
    background: #f3f4f6;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.9rem;
}

.order-total {
    text-align: right;
}

.total-label {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.total-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #059669;
}

.order-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s;
    display: inline-block;
    cursor: pointer;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.no-orders h3 {
    font-size: 1.5rem;
    color: #6b7280;
    margin-bottom: 15px;
}

.no-orders p {
    color: #9ca3af;
    margin-bottom: 25px;
}

.btn-shop {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 12px 30px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: transform 0.3s;
}

.btn-shop:hover {
    transform: translateY(-2px);
}
</style>

<div class="orders-container">
    <h1 class="page-title">📦 Pesanan Saya</h1>
    
    <?php if (count($orders) > 0): ?>
        <div class="orders-grid">
            <?php foreach($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="order-date"><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                        <span class="status-badge" style="background: <?php echo htmlspecialchars($order['color'] ?? '#e5e7eb'); ?>; color: <?php echo $order['color'] == '#fef3c7' ? '#92400e' : '#1f2937'; ?>">
                            <?php echo htmlspecialchars($order['status_name'] ?? 'Pending'); ?>
                        </span>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-items">
                            <?php
                            // Ambil item pesanan
                            $items_query = "SELECT oi.*, p.image FROM order_items oi 
                                           LEFT JOIN products p ON oi.product_id = p.id 
                                           WHERE oi.order_id = ? LIMIT 3";
                            $items_stmt = $pdo->prepare($items_query);
                            $items_stmt->execute([$order['id']]);
                            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach($items as $item):
                            ?>
                                <img src="<?php echo htmlspecialchars($item['image'] ?? 'assets/images/default-product.png'); ?>" 
                                     alt="Product" class="item-preview">
                            <?php endforeach; ?>
                            
                            <?php
                            // Hitung sisa item
                            $total_items_query = "SELECT COUNT(*) as count FROM order_items WHERE order_id = ?";
                            $total_stmt = $pdo->prepare($total_items_query);
                            $total_stmt->execute([$order['id']]);
                            $total_items = $total_stmt->fetchColumn();
                            
                            if ($total_items > 3):
                            ?>
                                <div class="items-count">+<?php echo $total_items - 3; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="order-total">
                            <div class="total-label">Total Pembayaran</div>
                            <div class="total-amount">Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">Lihat Detail</a>
                        <?php if ($order['status'] == 1): // Pending ?>
                            <a href="cancel-order.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary" onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">Batalkan Pesanan</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-orders">
            <h3>🎉 Belum Ada Pesanan</h3>
            <p>Anda belum memiliki riwayat pesanan. Yuk mulai belanja batu akik berkualitas!</p>
            <a href="products.php" class="btn-shop">Mulai Belanja →</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
