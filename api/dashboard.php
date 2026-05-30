<?php
/**
 * DASHBOARD.PHP (UPDATED)
 * Menampilkan list anak user dengan opsi add, detail, jadwal, riwayat
 */

require_once __DIR__ . '/config.php';
require_login();

$user_id = get_user_id();
$user_role = get_user_role();
$is_admin = ($user_role === 'admin');

$user_data = get_user_data($user_id);
if (!$user_data) {
    logout();
}

$username = htmlspecialchars($user_data['username']);
$email = htmlspecialchars($user_data['email']);

// Get all children untuk user ini
try {
    $stmt = $conn->prepare("SELECT * FROM children WHERE user_id = ? ORDER BY date_of_birth DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $children = [];
    while ($row = $result->fetch_assoc()) {
        $children[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Get Children Error: ' . $e->getMessage());
    $children = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Imuno</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .admin-link {
            background: white;
            color: #E85D6F;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .admin-link:hover {
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

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #E85D6F;
        }

        .btn-add-child {
            background: linear-gradient(135deg, #E85D6F, #D94560);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add-child:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(232, 93, 111, 0.3);
        }

        .children-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .child-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-left: 5px solid #E85D6F;
        }

        .child-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .child-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #E85D6F, #D94560);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .child-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .child-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        .child-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .child-info-item i {
            width: 16px;
            color: #E85D6F;
            font-size: 0.85rem;
        }

        .child-actions {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            color: white;
        }

        .btn-detail {
            background: #3498db;
        }

        .btn-detail:hover {
            background: #2980b9;
        }

        .btn-jadwal {
            background: #f39c12;
        }

        .btn-jadwal:hover {
            background: #d68910;
        }

        .btn-riwayat {
            background: #27ae60;
        }

        .btn-riwayat:hover {
            background: #229954;
        }

        .empty-state {
            background: white;
            border-radius: 15px;
            padding: 60px 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }

        .status-info {
            background: linear-gradient(135deg, #E8F5E9 0%, #D4EDD7 100%);
            padding: 15px 20px;
            border-radius: 8px;
            color: #2d5a3d;
            font-size: 0.9rem;
            margin-bottom: 20px;
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

            .children-grid {
                grid-template-columns: 1fr;
            }

            .child-actions {
                grid-template-columns: 1fr;
            }

            .welcome-section {
                padding: 25px;
            }

            .welcome-section h2 {
                font-size: 24px;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>💉 Imuno</h1>
        <div class="user-info">
            <?php if ($is_admin): ?>
                <a href="dashboardadmin.php" class="admin-link">
                    <i class="fas fa-shield-alt"></i> Admin Panel
                </a>
            <?php endif; ?>

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
            ✅ Anda login sebagai <strong>User Biasa</strong>
        </div>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2>Selamat Datang, <?php echo $username; ?>! 👋</h2>
            <p>Kelola data anak dan jadwal imunisasi mereka dengan mudah dan aman di sistem Imuno.</p>
        </div>

        <!-- Children Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-children"></i> Anak-Anak Saya
            </h2>
            <a href="tambah_anak.php" class="btn-add-child">
                <i class="fas fa-plus-circle"></i> Tambah Anak
            </a>
        </div>

        <!-- Children List -->
        <?php if (!empty($children)): ?>
            <div class="children-grid">
                <?php foreach ($children as $child): 
                    $birthdate = new DateTime($child['date_of_birth']);
                    $today = new DateTime();
                    $age = $today->diff($birthdate)->y;
                    $months = $today->diff($birthdate)->m;
                ?>
                    <div class="child-card">
                        <div class="child-avatar">
                            <?php echo $child['gender'] === 'L' ? '👦' : '👧'; ?>
                        </div>

                        <div class="child-name"><?php echo htmlspecialchars($child['name']); ?></div>

                        <div class="child-info">
                            <div class="child-info-item">
                                <i class="fas fa-birthday-cake"></i>
                                <span><?php echo date('d M Y', strtotime($child['date_of_birth'])); ?></span>
                            </div>
                            <div class="child-info-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span><?php echo $age . ' tahun ' . $months . ' bulan'; ?></span>
                            </div>
                            <div class="child-info-item">
                                <i class="fas fa-mars-and-venus"></i>
                                <span><?php echo $child['gender'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                            </div>
                            <?php if (!empty($child['blood_type'])): ?>
                                <div class="child-info-item">
                                    <i class="fas fa-droplet"></i>
                                    <span><?php echo htmlspecialchars($child['blood_type']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="child-actions">
                            <a href="detail_anak.php?id=<?php echo $child['id']; ?>" class="btn-action btn-detail" title="Detail">
                                <i class="fas fa-info-circle"></i> Detail
                            </a>
                            <a href="jadwal_imunisasi.php?id=<?php echo $child['id']; ?>" class="btn-action btn-jadwal" title="Jadwal">
                                <i class="fas fa-calendar"></i> Jadwal
                            </a>
                            <a href="riwayat_imunisasi.php?id=<?php echo $child['id']; ?>" class="btn-action btn-riwayat" title="Riwayat">
                                <i class="fas fa-history"></i> Riwayat
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Belum Ada Data Anak</h3>
                <p>Anda belum menambahkan data anak. Mulai dengan menambahkan profil anak pertama Anda.</p>
                <a href="tambah_anak.php" class="btn-add-child">
                    <i class="fas fa-plus-circle"></i> Tambah Anak Pertama
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>