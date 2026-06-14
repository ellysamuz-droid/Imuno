<?php
/**
 * DATA_ANAK.PHP
 * Halaman untuk manage data anak (children)
 * Hanya user yang login bisa akses
 */

require_once __DIR__ . '/config.php';
require_login();

$user_id = get_user_id();
$user_data = get_user_data($user_id);

if (!$user_data) {
    logout();
}

// Get all children untuk user ini
try {
    $search = $_GET['search'] ?? '';
    
    if (!empty($search)) {
        $search = '%' . $search . '%';
        $stmt = $conn->prepare(
            "SELECT * FROM children WHERE user_id = ? AND name LIKE ? ORDER BY date_of_birth DESC"
        );
        $stmt->bind_param("is", $user_id, $search);
    } else {
        $stmt = $conn->prepare(
            "SELECT * FROM children WHERE user_id = ? ORDER BY date_of_birth DESC"
        );
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $children = [];
    while ($row = $result->fetch_assoc()) {
        $children[] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    error_log('Data Anak Error: ' . $e->getMessage());
    $children = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Anak - Reminder Imunisasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #FFE5E9 0%, #E8F5F7 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #E85D6F 0%, #D94560 100%);
            padding: 20px 30px;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar-title {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background: white;
            color: #E85D6F;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-add {
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

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(232, 93, 111, 0.3);
            color: white;
        }

        .search-box {
            display: flex;
            gap: 8px;
        }

        .search-box input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            width: 250px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .search-box input:focus {
            outline: none;
            border-color: #E85D6F;
            box-shadow: 0 0 0 3px rgba(232, 93, 111, 0.1);
        }

        .search-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .search-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .reset-btn {
            background: #999;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }

        .reset-btn:hover {
            background: #777;
            transform: translateY(-2px);
        }

        .children-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .child-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-left: 5px solid #E85D6F;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .child-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        /* Avatar */
        .avatar-section {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .child-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #E85D6F, #D94560);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 4px 15px rgba(232, 93, 111, 0.2);
        }

        .child-name {
            font-size: 1.4rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            text-transform: capitalize;
        }

        /* Info Section */
        .child-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 25px;
            flex-grow: 1;
        }

        .child-info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 12px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .child-info-item i {
            width: 20px;
            color: #E85D6F;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .info-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-label {
            font-size: 0.8rem;
            color: #999;
            font-weight: 500;
        }

        .info-value {
            font-size: 0.95rem;
            color: #333;
            font-weight: 600;
        }

        /* === BUTTONS SECTION === */
        .child-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .action-buttons-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .btn-action {
            padding: 10px 12px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            color: white;
            min-height: 42px;
        }

        .btn-edit {
            background: linear-gradient(135deg, #3498db, #2980b9);
            grid-column: 1;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(52, 152, 219, 0.3);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            grid-column: 2;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(231, 76, 60, 0.3);
            color: white;
        }

        .btn-jadwal {
            background: linear-gradient(135deg, #E85D6F, #D94560);
            grid-column: 1 / -1;
            font-size: 0.95rem;
            min-height: 44px;
        }

        .btn-jadwal:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(232, 93, 111, 0.3);
            color: white;
        }

        /* Empty State */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 60px 30px;
        }

        .empty-state {
            text-align: center;
            color: #999;
        }

        .empty-state-icon {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 30px;
            font-size: 1rem;
        }

        .btn-empty-add {
            background: linear-gradient(135deg, #E85D6F, #D94560);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-empty-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(232, 93, 111, 0.3);
            color: white;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #E85D6F;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .back-link:hover {
            gap: 12px;
            color: #D94560;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
                flex: 1;
            }

            .search-btn,
            .reset-btn {
                flex: 1;
            }

            .children-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div class="navbar-title">
                <i class="fas fa-baby"></i> Data Anak
            </div>
            <a href="dashboard.php" class="logout-btn">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-child me-2" style="color: #E85D6F;"></i>
                Data Anak Saya
            </h1>
            <div class="header-actions">
                <form method="GET" style="display: flex; gap: 8px; width: 100%;">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Cari nama anak..." 
                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                        class="search-box" style="margin: 0; width: auto; flex: 1;"
                    >
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="data_anak.php" class="reset-btn">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
                <a href="tambah_anak.php" class="btn-add">
                    <i class="fas fa-plus-circle"></i> Tambah Anak
                </a>
            </div>
        </div>

        <?php if (!empty($children)): ?>
            <div class="children-grid">
                <?php foreach ($children as $child): ?>
                    <div class="child-card">
                        <!-- Avatar -->
                        <div class="avatar-section">
                            <div class="child-avatar">
                                <?php echo $child['gender'] === 'L' ? '👦' : '👧'; ?>
                            </div>
                        </div>

                        <!-- Nama -->
                        <div class="child-name">
                            <?php echo htmlspecialchars($child['name']); ?>
                        </div>

                        <!-- Info -->
                        <div class="child-info">
                            <!-- Tanggal Lahir -->
                            <div class="child-info-item">
                                <i class="fas fa-birthday-cake"></i>
                                <div class="info-text">
                                    <span class="info-label">Tanggal Lahir</span>
                                    <span class="info-value"><?php echo date('d M Y', strtotime($child['date_of_birth'])); ?></span>
                                </div>
                            </div>

                            <!-- Umur -->
                            <div class="child-info-item">
                                <i class="fas fa-hourglass-end"></i>
                                <div class="info-text">
                                    <span class="info-label">Umur</span>
                                    <span class="info-value">
                                        <?php 
                                        $birthdate = new DateTime($child['date_of_birth']);
                                        $today = new DateTime();
                                        $age = $today->diff($birthdate);
                                        echo $age->y . ' tahun ' . $age->m . ' bulan';
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="child-info-item">
                                <i class="fas fa-mars-and-venus"></i>
                                <div class="info-text">
                                    <span class="info-label">Jenis Kelamin</span>
                                    <span class="info-value"><?php echo $child['gender'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                                </div>
                            </div>

                            <!-- Golongan Darah -->
                            <?php if (!empty($child['blood_type'])): ?>
                                <div class="child-info-item">
                                    <i class="fas fa-droplet" style="color: #e74c3c;"></i>
                                    <div class="info-text">
                                        <span class="info-label">Golongan Darah</span>
                                        <span class="info-value"><?php echo htmlspecialchars($child['blood_type']); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="child-actions">
                            <div class="action-buttons-row">
                                <a href="edit_anak.php?id=<?php echo $child['id']; ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="hapus_anak.php?id=<?php echo $child['id']; ?>" class="btn-action btn-delete"
                                   onclick="return confirm('Yakin ingin menghapus data anak ini?');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                            <a href="jadwal_imunisasi.php?id=<?php echo $child['id']; ?>" class="btn-action btn-jadwal">
                                <i class="fas fa-syringe"></i> Jadwal Imunisasi
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="glass-card">
                <div class="empty-state">
                    <div class="empty-state-icon">👶</div>
                    <h3>Belum ada data anak</h3>
                    <p>Anda belum menambahkan data anak. Mulai dengan menambahkan profil anak Anda.</p>
                    <a href="tambah_anak.php" class="btn-empty-add">
                        <i class="fas fa-plus-circle"></i> Tambah Anak Pertama
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>