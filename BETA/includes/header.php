<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Core Stone Indonesia'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9fafb;
            color: #1f2937;
            line-height: 1.6;
        }
        
        .navbar {
            background: linear-gradient(135deg, #800020, #a91b3a);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
            font-size: 0.95rem;
        }
        
        .nav-menu a:hover {
            opacity: 0.8;
        }
        
        .nav-icons {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .nav-icon {
            color: white;
            text-decoration: none;
            font-size: 1.3rem;
            position: relative;
            transition: transform 0.3s;
        }
        
        .nav-icon:hover {
            transform: scale(1.1);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 50%;
            min-width: 18px;
            text-align: center;
        }
        
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: linear-gradient(135deg, #800020, #a91b3a);
                flex-direction: column;
                padding: 20px;
                gap: 15px;
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .mobile-toggle {
                display: block;
            }
        }
        
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #800020, #a91b3a);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 4px 15px rgba(128, 0, 32, 0.4);
            z-index: 999;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
        }
        
        .whatsapp-float:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(128, 0, 32, 0.6);
        }
        
        .footer {
            background: #1f2937;
            color: white;
            padding: 50px 0 20px;
            margin-top: 80px;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #a91b3a;
        }
        
        .footer-section p,
        .footer-section a {
            color: #9ca3af;
            line-height: 2;
            text-decoration: none;
            display: block;
        }
        
        .footer-section a:hover {
            color: #a91b3a;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #374151;
            color: #6b7280;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="logo">💎 Core Stone</a>
            
            <button class="mobile-toggle" onclick="toggleMenu()">☰</button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="products.php">Produk</a></li>
                <li><a href="categories.php">Kategori</a></li>
                <li><a href="about.php">Tentang</a></li>
                <li><a href="contact.php">Kontak</a></li>
            </ul>
            
            <div class="nav-icons">
                <?php if (isset($_SESSION['user_id'])): 
                    // Ambil data user untuk menampilkan nama atau foto
                    $user_id = $_SESSION['user_id'];
                    $stmt = $pdo->prepare("SELECT name, email, profile_photo FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $avatar_url = '';
                    if (!empty($user_data['profile_photo']) && file_exists('assets/images/profiles/' . $user_data['profile_photo'])) {
                        $avatar_url = 'assets/images/profiles/' . $user_data['profile_photo'];
                    } else {
                        $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($user_data['name'] ?? 'User') . '&background=800020&color=fff&size=40';
                    }
                ?>
                    <a href="my-account.php" class="nav-icon" title="Akun Saya" style="display: flex; align-items: center; gap: 8px;">
                        <img src="<?php echo $avatar_url; ?>" alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid white;">
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-icon" title="Login">🔐</a>
                    <a href="register.php" class="nav-icon" title="Daftar" style="font-size: 0.9rem; padding: 6px 12px; background: rgba(255,255,255,0.2); border-radius: 20px;">Daftar</a>
                <?php endif; ?>
                
                <a href="cart.php" class="nav-icon" title="Keranjang">
                    🛒
                    <?php
                    $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                    if ($cart_count > 0):
                    ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>
    
    <script>
        function toggleMenu() {
            document.getElementById('navMenu').classList.toggle('active');
        }
    </script>
