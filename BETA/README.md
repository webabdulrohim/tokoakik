# Core Stone Indonesia - Marketplace Batu Akik

Aplikasi web marketplace untuk penjualan batu akik dengan desain fresh, modern, dan responsif.

## Fitur Utama

### Frontend (User)
- ✅ Tampilan modern dengan warna hijau gradient yang fresh
- ✅ Responsive design untuk semua device (desktop, tablet, mobile)
- ✅ Katalog produk batu akik dengan kategori
- ✅ User registration dan login
- ✅ Keranjang belanja
- ✅ Checkout dengan integrasi Tripay payment gateway
- ✅ WhatsApp chat button di pojok bawah (0812-1493-2916)
- ✅ Halaman detail produk
- ✅ Pencarian produk

### Backend (Admin Panel)
- ✅ Dashboard dengan statistik penjualan
- ✅ Manajemen produk (CRUD)
- ✅ Manajemen kategori
- ✅ Manajemen pesanan
- ✅ Manajemen pengguna
- ✅ Laporan penjualan
- ✅ Pengaturan sistem

### Payment Gateway
- ✅ Integrasi Tripay API
- ✅ Multiple payment methods (Bank Transfer, E-Wallet, dll)
- ✅ Callback otomatis untuk update status pembayaran
- ✅ Sandbox mode untuk testing

## Teknologi

- **Backend**: PHP Native (PHP 7.4+)
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Custom dengan CSS Grid & Flexbox
- **Payment**: Tripay API

## Instalasi

### Langkah 1: Persiapan
1. Pastikan server web (Apache/Nginx) sudah terinstall
2. Pastikan PHP 7.4+ dan MySQL/MariaDB sudah terinstall
3. Clone atau download project ini ke folder web server Anda

### Langkah 2: Database Setup
1. Buka browser dan akses `http://localhost/core-stone/installer.php`
2. Isi konfigurasi database:
   - Database Host: localhost
   - Database Username: root
   - Database Password: (kosongkan jika tidak ada)
   - Database Name: core_stone_db
3. Buat akun admin:
   - Admin Name: Nama Anda
   - Admin Email: email@anda.com
   - Admin Password: password aman
4. Klik "Install Sekarang"

### Langkah 3: Konfigurasi Tripay
1. Daftar di [Tripay](https://tripay.co.id) untuk mendapatkan API credentials
2. Login ke admin panel: `http://localhost/core-stone/admin/`
3. Masuk ke menu Pengaturan
4. Masukkan Tripay credentials:
   - Merchant Code
   - Private Key
   - Public Key
5. Set mode ke "sandbox" untuk testing

### Langkah 4: Keamanan
**PENTING**: Hapus file installer.php setelah instalasi selesai!
```bash
rm installer.php
```

## Struktur Folder

```
core-stone/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   └── main.js            # JavaScript functions
│   └── images/                # Product images
├── config/
│   └── config.php             # Database & app configuration
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── products.php           # Product management
│   ├── orders.php             # Order management
│   ├── users.php              # User management
│   ├── settings.php           # Settings
│   └── logout.php             # Logout handler
├── api/
│   ├── tripay.php             # Tripay payment integration
│   └── cart.php               # Cart API
├── includes/                  # Reusable components
├── index.php                  # Homepage
├── login.php                  # Login page
├── register.php               # Registration page
├── products.php               # Products listing
├── product-detail.php         # Product detail page
├── cart.php                   # Shopping cart
├── checkout.php               # Checkout page
├── installer.php              # Installation script
└── README.md                  # This file
```

## Default Credentials (Setelah Install)

**Admin Login:**
- URL: `http://localhost/core-stone/admin/`
- Email: (email yang Anda masukkan saat install)
- Password: (password yang Anda masukkan saat install)

## WhatsApp Integration

Tombol WhatsApp otomatis terhubung ke nomor: **0812-1493-2916**
- Lokasi: Pojok kanan bawah setiap halaman
- Format: https://wa.me/6281214932916

## Payment Methods (Tripay)

- Bank Transfer (BCA, Mandiri, BNI, BRI, dll)
- E-Wallet (GoPay, OVO, Dana, ShopeePay)
- QRIS
- Alfamart/Indomaret
- Dan banyak lagi...

## Customization

### Mengubah Warna Theme
Edit file `assets/css/style.css`:
```css
:root {
    --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --primary-color: #10b981;
}
```

### Mengubah Nomor WhatsApp
Edit file `config/config.php` atau melalui admin panel.

## Troubleshooting

### Database Connection Failed
- Pastikan MySQL service berjalan
- Periksa kredensial database di `config/config.php`

### Permission Denied (Upload Gambar)
```bash
chmod 755 assets/images/
chown www-data:www-data assets/images/
```

### Tripay Callback Tidak Berfungsi
- Pastikan URL callback dapat diakses dari internet
- Gunakan ngrok untuk development lokal
- Periksa signature verification

## Support

Untuk bantuan teknis dan pertanyaan:
- Email: admin@corestone.id
- WhatsApp: 0812-1493-2916

## License

© 2024 Core Stone Indonesia. All rights reserved.

---

**Dibuat dengan ❤️ untuk pecinta batu akik Indonesia**
