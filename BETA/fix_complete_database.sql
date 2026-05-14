-- Script SQL Lengkap untuk Memperbaiki Database Core Stone
-- Jalankan di phpMyAdmin pada database core_stone_db

-- 1. Buat tabel settings jika belum ada
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Drop dan buat ulang tabel orders dengan struktur LENGKAP
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    user_id INT DEFAULT NULL,
    
    -- Informasi Pelanggan (support name & full_name)
    name VARCHAR(100) DEFAULT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    
    -- Alamat Lengkap
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    province VARCHAR(100) DEFAULT NULL,
    subdistrict VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    
    -- Pengiriman
    courier VARCHAR(50) DEFAULT NULL,
    shipping_method VARCHAR(50) DEFAULT NULL,
    service VARCHAR(100) DEFAULT NULL,
    weight INT DEFAULT 0,
    shipping_cost DECIMAL(15,2) DEFAULT 0.00,
    
    -- Keuangan (SEMUA KOLUM DICANTUMKAN)
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    total DECIMAL(15,2) DEFAULT 0.00,
    grand_total DECIMAL(15,2) DEFAULT 0.00,
    
    -- Status & Pembayaran
    status ENUM('pending', 'paid', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT NULL,
    
    -- Tripay Integration
    tripay_reference VARCHAR(100) DEFAULT NULL,
    tripay_merchant_code VARCHAR(50) DEFAULT NULL,
    tripay_signature VARCHAR(100) DEFAULT NULL,
    
    -- Lainnya
    customer_notes TEXT DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_order_code (order_code),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Pastikan tabel users memiliki role
ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin') DEFAULT 'user';

-- 4. Insert default admin jika belum ada (password: admin123)
INSERT INTO users (name, email, password, role) 
SELECT 'Administrator', 'admin@corestone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'admin');

-- 5. Insert pengaturan default Tripay
INSERT INTO settings (setting_key, setting_value) VALUES
    ('site_name', 'Core Stone Indonesia'),
    ('site_description', 'Pusat Batu Mulia Berkualitas'),
    ('tripay_mode', 'sandbox'),
    ('tripay_merchant_code', ''),
    ('tripay_private_key', ''),
    ('tripay_public_key', '')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- SELESAI - Database siap digunakan
