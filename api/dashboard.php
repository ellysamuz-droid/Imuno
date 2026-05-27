<?php
/**
 * Dashboard
 * Halaman utama setelah login
 * Hanya dapat diakses oleh user yang sudah login
 */

// Include config
require_once 'config.php';

// Check if user is logged in
require_login();

// Get current user data dari cookie
$user_id = get_user_id();
$user_email = get_user_email();
$user_role = get_user_role();

// Get user full data dari database
$query = $conn->prepare("SELECT id, nama, email, role FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // User tidak ditemukan, logout
    logout();
}

$user_name = $user['nama'];
$user_email = $user['email'];
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
            background: linear-gradient(135deg, #FF9BA5 0%, #FFB3BA 100%);
            padding: 20px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 24px;
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

        .logout-btn {
            background: white;
            color: #FF9BA5;
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
            color: #FF9BA5;
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

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }

            .user-info {
                width: 100%;
                justify-content: space-between;
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
                <p><strong><?php echo htmlspecialchars($user_name); ?></strong></p>
                <p><?php echo htmlspecialchars($user_email); ?></p>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Selamat Datang, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
            <p>Anda berhasil login ke sistem Reminder Imunisasi. Di sini Anda dapat mengelola jadwal imunisasi buah hati dengan mudah dan aman.</p>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-icon">👶</div>
                <h3>Data Anak</h3>
                <p>Tambah dan kelola data anak Anda dalam sistem</p>
            </div>

            <div class="card">
                <div class="card-icon">📅</div>
                <h3>Jadwal Imunisasi</h3>
                <p>Lihat dan pantau jadwal imunisasi yang akan datang</p>
            </div>

            <div class="card">
                <div class="card-icon">📋</div>
                <h3>Riwayat Imunisasi</h3>
                <p>Cek riwayat lengkap imunisasi yang sudah dilakukan</p>
            </div>
        </div>
    </div>

    <script>
        // Simple dashboard functionality
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('h3').textContent;
                alert('Fitur "' + title + '" sedang dalam pengembangan');
            });
        });

        // Check session periodically (optional)
        // setInterval(() => {
        //     // Check if user still logged in
        //     fetch('check_session.php')
        //         .then(r => r.json())
        //         .then(data => {
        //             if (!data.logged_in) {
        //                 window.location.href = 'login.html';
        //             }
        //         });
        // }, 5 * 60 * 1000); // Check every 5 minutes
    </script>
</body>
</html>

<?php
$conn->close();
?>
