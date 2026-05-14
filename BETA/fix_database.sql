-- ================================================
-- SCRIPT LENGKAP UNTUK MEMBUAT ULANG TABEL ORDERS
-- Core Stone Indonesia - Database Fix
-- ================================================

-- Hapus tabel order_items terlebih dahulu (jika ada foreign key)
DROP TABLE IF EXISTS order_items;

-- Hapus tabel orders lama
DROP TABLE IF EXISTS orders;

-- Buat tabel orders BARU dengan SEMUA kolom yang diperlukan
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Kode Pesanan & User
    order_code VARCHAR(50) NOT NULL UNIQUE,
    user_id INT DEFAULT NULL,
    
    -- Informasi Pelanggan (SUPPORT name DAN full_name)
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
    
    -- Keuangan (SUPPORT subtotal, total, grand_total)
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
    
    -- Catatan & Waktu
    customer_notes TEXT DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_order_code (order_code),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Buat tabel order_items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- SELESAI - Tabel sudah dibuat dengan semua kolom
-- ================================================
