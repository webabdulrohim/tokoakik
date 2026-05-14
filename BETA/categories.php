<?php
session_start();
require 'config/database.php';

// Ambil semua kategori
$stmt = $pdo->prepare("SELECT c.*, COUNT(p.id) as product_count 
                        FROM categories c 
                        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                        GROUP BY c.id 
                        ORDER BY c.name ASC");
$stmt->execute();
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori - Core Stone Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .categories-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: #1f2937;
        }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .category-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .category-card:hover {
            border-color: #10b981;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
            transform: translateY(-2px);
        }
        .category-info {
            flex: 1;
        }
        .category-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        .category-count {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="categories-container">
        <h1 class="page-title">Kategori Produk</h1>
        
        <div class="categories-grid">
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $category): ?>
                    <a href="category.php?id=<?= $category['id'] ?>" class="category-card">
                        <div class="category-info">
                            <div class="category-name"><?= htmlspecialchars($category['name']) ?></div>
                            <div class="category-count"><?= $category['product_count'] ?> Produk</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color: #9ca3af; font-size: 0.9rem;"></i>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: #6b7280;">
                    <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>Belum ada kategori.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
