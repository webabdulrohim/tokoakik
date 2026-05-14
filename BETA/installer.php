<?php
/**
 * Installer Script untuk Toko Online Maroon Gradient
 * File ini akan:
 * 1. Membuat tabel database yang diperlukan
 * 2. Mengisi data awal (admin, kategori, produk contoh)
 * 3. Memeriksa konfigurasi database
 */

// Matikan error display sementara untuk keamanan, tapi log error
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Cek apakah sudah diinstall
$install_lock_file = 'installed.lock';
if (file_exists($install_lock_file)) {
    die("<h1>Installer Terkunci</h1><p>Aplikasi sudah terinstall. Jika ingin menginstall ulang, hapus file <code>$install_lock_file</code> terlebih dahulu.</p>");
}

// Konfigurasi Database (Sesuaikan dengan hosting Anda)
$db_host = 'localhost';
$db_name = 'ulkswssv_akiku'; // Ganti dengan nama database Anda
$db_user = 'ulkswssv_akik';   // Ganti dengan user database Anda
$db_pass = '';                // GANTI DENGAN PASSWORD DATABASE ANDA

// Fungsi redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fungsi untuk membuat pesan
function message($type, $text) {
    $color = $type == 'error' ? 'red' : 'green';
    return "<p style='color: $color; font-weight: bold;'>$text</p>";
}

$errors = [];
$success = [];

// Langkah 1: Cek Koneksi Database
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $success[] = "Koneksi database berhasil!";
} catch (PDOException $e) {
    $errors[] = "Koneksi database gagal: " . $e->getMessage();
    $pdo = null;
}

// Jika koneksi berhasil, lanjutkan instalasi
if ($pdo && empty($errors)) {
    
    // Langkah 2: Buat Tabel Users
    try {
        $sql_users = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            role ENUM('admin', 'customer') DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql_users);
        $success[] = "Tabel 'users' berhasil dibuat.";
        
        // Cek apakah admin sudah ada, jika belum buat admin default
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $admin_email = 'admin@corestone.com';
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT); // Password default: admin123
            $admin_name = 'Administrator';
            
            $insert_admin = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            $insert_admin->execute([$admin_name, $admin_email, $admin_password]);
            $success[] = "User Admin default dibuat. Email: <b>$admin_email</b>, Password: <b>admin123</b>";
        }
    } catch (PDOException $e) {
        $errors[] = "Gagal membuat tabel users: " . $e->getMessage();
    }

    // Langkah 3: Buat Tabel Categories
    try {
        $sql_categories = "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql_categories);
        $success[] = "Tabel 'categories' berhasil dibuat.";
        
        // Isi data kategori contoh jika kosong
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        if ($stmt->fetchColumn() == 0) {
            $default_categories = [
                ['Batu Akik Kalimantan', 'batu-akik-kalimantan', 'Koleksi batu akik asli dari Kalimantan'],
                ['Batu Akik Jawa', 'batu-akik-jawa', 'Koleksi batu akik asli dari Jawa'],
                ['Batu Akik Sumatera', 'batu-akik-sumatera', 'Koleksi batu akik asli dari Sumatera'],
                ['Cincin & Perhiasan', 'cincin-perhiasan', 'Cincin dan perhiasan batu akik']
            ];
            
            $insert_cat = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            foreach ($default_categories as $cat) {
                $insert_cat->execute($cat);
            }
            $success[] = "Kategori default berhasil ditambahkan.";
        }
    } catch (PDOException $e) {
        $errors[] = "Gagal membuat tabel categories: " . $e->getMessage();
    }

    // Langkah 4: Buat Tabel Products
    try {
        $sql_products = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            stock INT DEFAULT 0,
            image VARCHAR(255),
            is_featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql_products);
        $success[] = "Tabel 'products' berhasil dibuat.";
        
        // Isi data produk contoh jika kosong
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        if ($stmt->fetchColumn() == 0) {
            $default_products = [
                [1, 'Akik Kalimaya Super', 'akik-kalimaya-super', 'Batu akik kalimaya super dengan warna cerah dan motif unik.', 1500000, 5, 'kalimaya.jpg', true],
                [1, 'Akik Badar Besi', 'akik-badar-besi', 'Batu akik badar besi asli dengan kekuatan energi tinggi.', 750000, 10, 'badar-besi.jpg', false],
                [2, 'Akik Giok Jawa', 'akik-giok-jawa', 'Giok jawa asli dengan tekstur halus dan warna hijau menawan.', 2000000, 3, 'giok-jawa.jpg', true],
                [3, 'Akik Sumatra Blue', 'akik-sumatra-blue', 'Batu akik langka dari Sumatera dengan warna biru kehijauan.', 1200000, 7, 'sumatra-blue.jpg', false],
                [4, 'Cincin Akik Silver', 'cincin-akik-silver', 'Cincin perak dengan镶嵌 batu akik pilihan.', 850000, 15, 'cincin-silver.jpg', true]
            ];
            
            $insert_prod = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, image, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($default_products as $prod) {
                $insert_prod->execute($prod);
            }
            $success[] = "Produk contoh berhasil ditambahkan.";
        }
    } catch (PDOException $e) {
        $errors[] = "Gagal membuat tabel products: " . $e->getMessage();
    }

    // Langkah 5: Buat Tabel Orders
    try {
        $sql_orders = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            total_amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
            customer_name VARCHAR(100) NOT NULL,
            customer_email VARCHAR(100) NOT NULL,
            customer_phone VARCHAR(20),
            shipping_address TEXT NOT NULL,
            payment_method VARCHAR(50),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql_orders);
        $success[] = "Tabel 'orders' berhasil dibuat.";
    } catch (PDOException $e) {
        $errors[] = "Gagal membuat tabel orders: " . $e->getMessage();
    }

    // Langkah 6: Buat Tabel Order Items
    try {
        $sql_order_items = "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT,
            product_name VARCHAR(200) NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            subtotal DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql_order_items);
        $success[] = "Tabel 'order_items' berhasil dibuat.";
    } catch (PDOException $e) {
        $errors[] = "Gagal membuat tabel order_items: " . $e->getMessage();
    }

    // Langkah 7: Buat Folder Uploads jika belum ada
    $upload_dir = 'uploads/products';
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            $success[] = "Folder upload '$upload_dir' berhasil dibuat.";
        } else {
            $errors[] = "Gagal membuat folder upload '$upload_dir'. Pastikan permission folder benar.";
        }
    } else {
        $success[] = "Folder upload sudah ada.";
    }

    // Jika semua berhasil, buat file lock
    if (empty($errors)) {
        if (file_put_contents($install_lock_file, 'Installed on ' . date('Y-m-d H:i:s'))) {
            $success[] = "<strong>INSTALASI SELESAI!</strong> File <code>$install_lock_file</code> telah dibuat.";
            $success[] = "Silakan login ke halaman admin dengan kredensial yang telah dibuat.";
        } else {
            $errors[] = "Gagal membuat file lock. Harap buat file <code>$install_lock_file</code> secara manual setelah ini.";
        }
    }
}

// Tampilkan Hasil Instalasi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Toko Online - Corestone Indonesia</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #800020, #a91b3a);
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #800020;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #a91b3a;
            padding-bottom: 15px;
        }
        .status-box {
            background: #f8f9fa;
            border-left: 5px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
            color: #155724;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
            color: #721c24;
        }
        .info {
            background: #e2e3e5;
            border-left-color: #6c757d;
            padding: 20px;
            margin-top: 30px;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #800020, #a91b3a);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128, 0, 32, 0.4);
        }
        .credentials {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        code {
            background: #eee;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #d63384;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ Instalasi Toko Online Corestone</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="status-box error">
                <h3>⚠️ Terjadi Kesalahan:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Silakan perbaiki kesalahan di atas dan refresh halaman ini.</p>
                <p><strong>Catatan Penting:</strong> Pastikan Anda sudah mengedit file <code>installer.php</code> dan mengisi <code>$db_pass</code> dengan password database yang benar.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="status-box success">
                <h3>✅ Berhasil:</h3>
                <ul>
                    <?php foreach ($success as $msg): ?>
                        <li><?php echo $msg; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <?php if (empty($errors)): ?>
                <div class="credentials">
                    <h3>🔐 Kredensial Admin Default:</h3>
                    <p><strong>Email:</strong> <code>admin@corestone.com</code></p>
                    <p><strong>Password:</strong> <code>admin123</code></p>
                    <p><em>Segera ubah password ini setelah login pertama kali!</em></p>
                </div>

                <div style="text-align: center;">
                    <a href="login.php" class="btn">Login ke Admin</a>
                    <a href="index.php" class="btn" style="background: linear-gradient(135deg, #6c757d, #495057); margin-left: 10px;">Lihat Website</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (empty($success) && empty($errors)): ?>
            <div class="info">
                <h3>ℹ️ Informasi Instalasi</h3>
                <p>Script ini akan melakukan instalasi otomatis untuk toko online Corestone Indonesia.</p>
                <ol>
                    <li>Pastikan Anda sudah mengedit bagian konfigurasi database di file ini (<code>$db_host</code>, <code>$db_name</code>, <code>$db_user</code>, <code>$db_pass</code>).</li>
                    <li>Pastikan database sudah dibuat di hosting Anda.</li>
                    <li>Pastikan user database memiliki hak akses penuh (CREATE, INSERT, SELECT, dll).</li>
                </ol>
                <p>Jika konfigurasi sudah benar, refresh halaman ini untuk memulai instalasi.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
