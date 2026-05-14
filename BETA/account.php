<?php
session_start();
require 'config/database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle Update Profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    
    // Upload Foto Profil
    $photo_path = $user['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = 'assets/images/profiles/' . $new_filename;
            
            if (!file_exists('assets/images/profiles')) {
                mkdir('assets/images/profiles', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada
                if ($user['photo'] && file_exists($user['photo'])) {
                    unlink($user['photo']);
                }
                $photo_path = $upload_path;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, photo = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $address, $photo_path, $user_id);
    
    if ($stmt->execute()) {
        $success_msg = "Profil berhasil diperbarui!";
        // Refresh data user
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error_msg = "Gagal memperbarui profil.";
    }
}

// Handle Ganti Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if (password_verify($current_pass, $user['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_pass, $user_id);
            
            if ($stmt->execute()) {
                $success_msg = "Password berhasil diubah!";
            } else {
                $error_msg = "Gagal mengubah password.";
            }
        } else {
            $error_msg = "Konfirmasi password tidak cocok.";
        }
    } else {
        $error_msg = "Password saat ini salah.";
    }
}

// Ambil Riwayat Pesanan
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Core Stone Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .account-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .account-header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .account-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            background: #fff;
        }
        .account-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1rem;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 0.5rem 1rem;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
            color: #6b7280;
            font-weight: 500;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #10b981;
            border-bottom: 2px solid #10b981;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        .order-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-shipped { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        @media (max-width: 768px) {
            .account-header {
                flex-direction: column;
                text-align: center;
            }
            .order-header {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="account-container">
        <div class="account-header">
            <?php 
            $avatar_url = '';
            if (!empty($user['photo']) && file_exists($user['photo'])) {
                $avatar_url = $user['photo'];
            } else {
                $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=10b981&color=fff&size=200';
            }
            ?>
            <img src="<?= $avatar_url ?>" alt="Profile" class="account-avatar">
            <div>
                <h1 style="margin: 0; font-size: 1.5rem;">Halo, <?= htmlspecialchars($user['name']) ?>!</h1>
                <p style="margin: 0.5rem 0 0; opacity: 0.9;"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-error"><?= $error_msg ?></div>
        <?php endif; ?>

        <div class="account-tabs">
            <button class="tab-btn active" onclick="showTab('profile')">Profil Saya</button>
            <button class="tab-btn" onclick="showTab('orders')">Riwayat Pesanan</button>
            <button class="tab-btn" onclick="showTab('password')">Ganti Password</button>
        </div>

        <!-- Tab Profil -->
        <div id="profile" class="tab-content active">
            <div class="order-card">
                <h2 style="margin-bottom: 1.5rem; color: #1f2937;">Edit Profil</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Foto Profil</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <small style="color: #6b7280;">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="4"><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <!-- Tab Pesanan -->
        <div id="orders" class="tab-content">
            <h2 style="margin-bottom: 1.5rem; color: #1f2937;">Riwayat Pesanan</h2>
            <?php if ($orders_result->num_rows > 0): ?>
                <?php while($order = $orders_result->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <strong>Order #<?= $order['order_number'] ?></strong>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                                    <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 500;">Total: <span style="color: #10b981; font-size: 1.25rem;">Rp <?= number_format($order['grand_total'], 0, ',', '.') ?></span></div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Metode: <?= $order['payment_method'] ?></div>
                            </div>
                            <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Lihat Detail</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="order-card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280;">Belum ada riwayat pesanan.</p>
                    <a href="products.php" class="btn-primary" style="margin-top: 1rem; display: inline-block;">Mulai Belanja</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Password -->
        <div id="password" class="tab-content">
            <div class="order-card">
                <h2 style="margin-bottom: 1.5rem; color: #1f2937;">Ganti Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" name="change_password" class="btn-primary">Ubah Password</button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
