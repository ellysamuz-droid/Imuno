<?php
/**
 * DASHBOARD.PHP
 * Halaman dashboard user (role: user)
 * Hanya bisa diakses oleh user yang sudah login
 */

require_once __DIR__ . '/config.php';

// Require user harus login
require_login();

// Get current user info
$user_id = get_user_id();
$user_role = get_user_role();

// Jika user adalah admin, redirect ke dashboardadmin.php
if ($user_role === 'admin') {
    header('Location: dashboardadmin.php');
    exit();
}

// Get user data lengkap dari database
$user_data = get_user_data($user_id);

if (!$user_data) {
    // User tidak ditemukan di database, logout
    logout();
}

$username = htmlspecialchars($user_data['username']);
$email = htmlspecialchars($user_data['email']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Reminder Imunisasi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #FFE5E9 0%, #E8F5F7 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #E85D6F 0%, #D94560 100%);
            padding: 20px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .user-data {
            text-align: right;
        }

        .user-data p {
            margin: 0;
            font-size: 14px;
        }

        .user-data strong {
            font-size: 15px;
        }

        .logout-btn {
            background: white;
            color: #E85D6F;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .welcome-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-section h2 {
            color: #E85D6F;
            margin-bottom: 15px;
            font-size: 32px;
        }

        .welcome-section p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            cursor: pointer;
            animation: slideUp 0.6s ease-out;
            border-left: 4px solid #E85D6F;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .card p {
            color: #999;
            font-size: 14px;
            line-height: 1.5;
        }

        .card:nth-child(1) { animation-delay: 0.1s; border-left-color: #E85D6F; }
        .card:nth-child(2) { animation-delay: 0.2s; border-left-color: #78B7B7; }
        .card:nth-child(3) { animation-delay: 0.3s; border-left-color: #FFD89B; }

        .status-info {
            background: linear-gradient(135deg, #E8F5E9 0%, #D4EDD7 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #2d5a3d;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }

            .user-info {
                width: 100%;
                justify-content: space-between;
            }

            .container {
                padding: 20px;
            }

            .welcome-section {
                padding: 25px;
            }

            .welcome-section h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>💉 Reminder Imunisasi</h1>
        <div class="user-info">
            <div class="user-data">
                <p><strong><?php echo $username; ?></strong></p>
                <p><?php echo $email; ?></p>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Status Info -->
        <div class="status-info">
            ✅ Anda login sebagai <strong>User Biasa</strong> | Role: <strong><?php echo htmlspecialchars($user_role); ?></strong>
        </div>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Selamat Datang, <?php echo $username; ?>! 👋</h2>
            <p>Anda berhasil login ke sistem Reminder Imunisasi. Di sini Anda dapat mengelola jadwal imunisasi buah hati dengan mudah dan aman.</p>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <div class="card" onclick="alert('Fitur sedang dalam pengembangan')">
                <div class="card-icon">👶</div>
                <h3>Data Anak</h3>
                <p>Tambah dan kelola data anak Anda dalam sistem</p>
            </div>

            <div class="card" onclick="alert('Fitur sedang dalam pengembangan')">
                <div class="card-icon">📅</div>
                <h3>Jadwal Imunisasi</h3>
                <p>Lihat dan pantau jadwal imunisasi yang akan datang</p>
            </div>

            <div class="card" onclick="alert('Fitur sedang dalam pengembangan')">
                <div class="card-icon">📋</div>
                <h3>Riwayat Imunisasi</h3>
                <p>Cek riwayat lengkap imunisasi yang sudah dilakukan</p>
            </div>
        </div>
    </div>

    <script>
        // Log activity (optional - bisa diubah menjadi fetch ke server)
        console.log('Dashboard loaded for user: <?php echo $username; ?>');
    </script>
</body>
</html>

<?php
$conn->close();
?>