    <!-- WhatsApp Float -->
    <a href="https://wa.me/6281214932916?text=Halo%20Core%20Stone%20Indonesia,%20saya%20tertarik%20dengan%20produk%20Anda" 
       class="whatsapp-float" 
       target="_blank" 
       rel="noopener noreferrer">
        💬
    </a>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>💎 Core Stone Indonesia</h3>
                    <p>Platform marketplace batu akik terpercaya di Indonesia. Menyediakan berbagai jenis batu akik berkualitas dengan harga terbaik.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Tautan Cepat</h3>
                    <a href="index.php">Beranda</a>
                    <a href="products.php">Produk</a>
                    <a href="categories.php">Kategori</a>
                    <a href="about.php">Tentang Kami</a>
                    <a href="contact.php">Kontak</a>
                </div>
                
                <div class="footer-section">
                    <h3>Akun</h3>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="my-account.php">Akun Saya</a>
                        <a href="my-orders.php">Pesanan Saya</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Daftar</a>
                    <?php endif; ?>
                </div>
                
                <div class="footer-section">
                    <h3>Hubungi Kami</h3>
                    <p>📞 0812-1493-2916</p>
                    <p>📧 info@corestone.id</p>
                    <p>📍 Indonesia</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Core Stone Indonesia. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
