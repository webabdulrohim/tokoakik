<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle update profil
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    
    // Upload foto profil
    $photo_path = $user['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_name;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada
                if ($user['photo'] && file_exists($user['photo'])) {
                    unlink($user['photo']);
                }
                $photo_path = $upload_path;
            }
        }
    }
    
    // Update password jika diisi
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password = '$password'";
    }
    
    $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, city = ?, postal_code = ?, photo = ? $password_sql WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    
    if (!empty($_POST['password'])) {
        $stmt->bind_param("sssssssi", $name, $email, $phone, $address, $city, $postal_code, $photo_path, $user_id);
    } else {
        $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $city, $postal_code, $photo_path, $user_id);
    }
    
    if ($stmt->execute()) {
        $success = true;
        $message = 'Profil berhasil diperbarui!';
        // Refresh data user
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $message = 'Gagal memperbarui profil. Silakan coba lagi.';
    }
}

// Ambil riwayat pesanan
$orders_query = "SELECT o.*, 
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
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders = $orders_stmt->get_result();

$page_title = 'Akun Saya - Core Stone Indonesia';
include 'includes/header.php';
?>

<style>
.account-container {
    max-width: 1200px;
    margin: 80px auto 40px;
    padding: 0 20px;
}

.account-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
}

@media (max-width: 768px) {
    .account-grid {
        grid-template-columns: 1fr;
    }
}

.sidebar {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    text-align: center;
    height: fit-content;
}

.profile-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #e5f3eb;
    margin-bottom: 20px;
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #059669;
    margin-bottom: 5px;
}

.profile-email {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 20px;
}

.menu-item {
    display: block;
    padding: 12px 20px;
    margin: 8px 0;
    border-radius: 10px;
    color: #374151;
    text-decoration: none;
    transition: all 0.3s;
    text-align: left;
}

.menu-item:hover, .menu-item.active {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    transform: translateX(5px);
}

.main-content {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5f3eb;
}

.form-group {
    margin-bottom: 25px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="tel"],
textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

input:focus,
textarea:focus {
    outline: none;
    border-color: #10b981;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

.btn-update {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 12px 40px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-update:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.orders-table th,
.orders-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.orders-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.no-orders {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.order-link {
    color: #10b981;
    text-decoration: none;
    font-weight: 600;
}

.order-link:hover {
    text-decoration: underline;
}
</style>

<div class="account-container">
    <div class="account-grid">
        <!-- Sidebar -->
        <div class="sidebar">
            <img src="<?php echo $user['photo'] ? htmlspecialchars($user['photo']) : 'assets/images/default-avatar.png'; ?>" 
                 alt="Profile" class="profile-photo"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=10b981&color=fff&size=150'">
            
            <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
            <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            
            <nav>
                <a href="my-account.php" class="menu-item active">👤 Profil Saya</a>
                <a href="my-orders.php" class="menu-item">📦 Pesanan Saya</a>
                <a href="logout.php" class="menu-item" style="color: #ef4444;">🚪 Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="section-title">Edit Profil</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="08xx-xxxx-xxxx">
                    </div>
                    
                    <div class="form-group">
                        <label for="photo">Foto Profil</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" placeholder="Jalan, Nomor Rumah, RT/RW"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">Kota/Kabupaten</label>
                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="postal_code">Kode Pos</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Ganti Password (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" id="password" name="password" placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn-update">💾 Simpan Perubahan</button>
            </form>
            
            <h2 class="section-title" style="margin-top: 50px;">Riwayat Pesanan Terakhir</h2>
            
            <?php if ($orders->num_rows > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>No. Order</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                <td>Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="status-badge" style="background: <?php echo htmlspecialchars($order['color'] ?? '#e5e7eb'); ?>; color: <?php echo $order['color'] == '#fef3c7' ? '#92400e' : '#1f2937'; ?>">
                                        <?php echo htmlspecialchars($order['status_name'] ?? 'Pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="order-link">Lihat Detail →</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-orders">
                    <p>🎉 Anda belum memiliki pesanan.</p>
                    <a href="products.php" style="color: #10b981; text-decoration: none; font-weight: 600;">Mulai Belanja Sekarang →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
