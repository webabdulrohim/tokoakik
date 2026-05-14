<?php

session_start();

// TAMBAHKAN BARIS INI (Sesuaikan dengan nama file config Anda)
require_once '../config/config.php'; 

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Sekarang $pdo sudah bisa digunakan
// ... kode selanjutnya ...
$success = false;
$error_msg = '';

// Proses simpan pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $site_name = trim($_POST['site_name'] ?? '');
        $site_description = trim($_POST['site_description'] ?? '');
        $tripay_mode = trim($_POST['tripay_mode'] ?? 'sandbox');
        $tripay_merchant_code = trim($_POST['tripay_merchant_code'] ?? '');
        $tripay_private_key = trim($_POST['tripay_private_key'] ?? '');
        $tripay_public_key = trim($_POST['tripay_public_key'] ?? '');

        // Mulai transaksi
        $pdo->beginTransaction();

        // Fungsi helper untuk save setting
        $saveSetting = function($key, $value) use ($pdo) {
            // Cek apakah key ada
            $check = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
            $check->execute([$key]);
            
            if ($check->rowCount() > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmt->execute([$key, $value]);
            }
        };

        $saveSetting('site_name', $site_name);
        $saveSetting('site_description', $site_description);
        $saveSetting('tripay_mode', $tripay_mode);
        $saveSetting('tripay_merchant_code', $tripay_merchant_code);
        $saveSetting('tripay_private_key', $tripay_private_key);
        $saveSetting('tripay_public_key', $tripay_public_key);

        $pdo->commit();
        $success = true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_msg = 'Error: ' . $e->getMessage();
    }
}

// Ambil pengaturan saat ini
$current_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $current_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    // Tabel mungkin belum ada
    $current_settings = [];
}

function get_setting($key, $default = '') {
    global $current_settings;
    return isset($current_settings[$key]) ? $current_settings[$key] : $default;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php include 'sidebar.php'; ?>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Pengaturan Sistem</h1>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <?php if ($success): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <strong>Berhasil!</strong> Pengaturan telah disimpan.
                        </div>
                    <?php endif; ?>

                    <?php if ($error_msg): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <strong>Gagal!</strong> <?php echo htmlspecialchars($error_msg); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                        <h3 class="text-lg font-semibold mb-4 text-blue-600">Payment Gateway (Tripay)</h3>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Mode</label>
                            <select name="tripay_mode" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                <option value="sandbox" <?= get_setting('tripay_mode') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing)</option>
                                <option value="production" <?= get_setting('tripay_mode') === 'production' ? 'selected' : '' ?>>Production (Live)</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Merchant Code</label>
                            <input type="text" name="tripay_merchant_code" value="<?= htmlspecialchars(get_setting('tripay_merchant_code')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-mono">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Private Key</label>
                            <input type="password" name="tripay_private_key" value="<?= htmlspecialchars(get_setting('tripay_private_key')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-mono">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Public Key</label>
                            <input type="text" name="tripay_public_key" value="<?= htmlspecialchars(get_setting('tripay_public_key')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-mono">
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
