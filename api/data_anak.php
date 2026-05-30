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
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
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
            gap: 10px;
        }

        .search-box input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            width: 250px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #E85D6F;
            box-shadow: 0 0 0 3px rgba(232, 93, 111, 0.1);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }

        .children-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .child-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-left: 5px solid #E85D6F;
            position: relative;
            overflow: hidden;
        }

        .child-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
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
            gap: 10px;
        }

        .child-info-item i {
            width: 20px;
            color: #E85D6F;
        }

        .child-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
            color: white;
        }

        .btn-edit {
            background: #3498db;
        }

        .btn-edit:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #e74c3c;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }

        .btn-empty-add {
            background: linear-gradient(135deg, #E85D6F, #D94560);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-empty-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(232, 93, 111, 0.3);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(46, 204, 113, 0.2);
            color: #27ae60;
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

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-box {
                width: 100%;
            }

            .search-box input {
                width: 100%;
            }

            .children-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
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

    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- BACK LINK -->
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-child me-2" style="color: #E85D6F;"></i>
                Data Anak Saya
            </h1>
            <div style="display: flex; gap: 10px;">
                <form method="GET" style="display: flex; gap: 10px;">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Cari nama anak..." 
                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                    >
                    <button type="submit" class="btn-add" style="padding: 10px 16px;">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="data_anak.php" class="btn-add" style="padding: 10px 16px; background: #999;">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
                <a href="tambah_anak.php" class="btn-add">
                    <i class="fas fa-plus-circle"></i> Tambah Anak
                </a>
            </div>
        </div>

        <!-- CHILDREN LIST -->
        <?php if (!empty($children)): ?>
            <div class="children-grid">
                <?php foreach ($children as $child): ?>
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
                                <i class="fas fa-mars-and-venus"></i>
                                <span><?php echo $child['gender'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                            </div>
                            <?php if (!empty($child['blood_type'])): ?>
                                <div class="child-info-item">
                                    <i class="fas fa-droplet"></i>
                                    <span>Golongan Darah: <?php echo htmlspecialchars($child['blood_type']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="child-info-item">
                                <i class="fas fa-calendar"></i>
                                <span>
                                    <?php 
                                    $birthdate = new DateTime($child['date_of_birth']);
                                    $today = new DateTime();
                                    $age = $today->diff($birthdate)->y;
                                    echo $age . ' tahun';
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="child-actions">
                            <a href="edit_anak.php?id=<?php echo $child['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="hapus_anak.php?id=<?php echo $child['id']; ?>" class="btn-delete"
                               onclick="return confirm('Yakin ingin menghapus data anak ini?');">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="glass-card">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Belum ada data anak</h3>
                    <p>Anda belum menambahkan data anak. Mulai dengan menambahkan profil anak Anda.</p>
                    <a href="tambah_anak.php" class="btn-empty-add">
                        <i class="fas fa-plus-circle me-2"></i> Tambah Anak Pertama
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