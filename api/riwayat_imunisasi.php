<?php
/**
 * RIWAYAT_IMUNISASI.PHP
 * Tampilkan riwayat imunisasi per anak
 */

require_once __DIR__ . '/config.php';
require_login();

$user_id = get_user_id();
$child_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($child_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Verify child ownership
try {
    $stmt = $conn->prepare("SELECT * FROM children WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $child_id, $user_id);
    $stmt->execute();
    $child = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$child) {
        header("Location: dashboard.php");
        exit();
    }

    // Get immunization records untuk anak ini
    $records_stmt = $conn->prepare(
        "SELECT imr.*, immu.name, immu.description
         FROM immunization_records imr
         LEFT JOIN immunization_types immu ON imr.immunization_type_id = immu.id
         WHERE imr.child_id = ?
         ORDER BY imr.vaccination_date DESC"
    );
    $records_stmt->bind_param("i", $child_id);
    $records_stmt->execute();
    $records = $records_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $records_stmt->close();

} catch (Exception $e) {
    error_log('Riwayat Imunisasi Error: ' . $e->getMessage());
    $records = [];
}

// Calculate child age
$birthdate = new DateTime($child['date_of_birth']);
$today = new DateTime();
$age_years = $today->diff($birthdate)->y;
$age_months = $today->diff($birthdate)->m;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Imunisasi - Imuno</title>
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
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn {
            background: white;
            color: #E85D6F;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .child-info {
            display: flex;
            align-items: center;
            gap: 20px;
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
            color: white;
            font-size: 2.5rem;
        }

        .child-details h2 {
            color: #333;
            margin-bottom: 5px;
        }

        .child-details p {
            color: #666;
            font-size: 0.9rem;
            margin: 3px 0;
        }

        .stat-box {
            background: rgba(46, 204, 113, 0.1);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #27ae60;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .records-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            background: rgba(46, 204, 113, 0.1);
            padding: 20px;
        }

        .table-header h3 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header i {
            color: #27ae60;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(46, 204, 113, 0.1);
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #27ae60;
            border-bottom: 2px solid #27ae60;
        }

        td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: rgba(46, 204, 113, 0.05);
        }

        .record-card {
            background: white;
            border-left: 4px solid #27ae60;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .record-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .record-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        .record-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .record-info-item i {
            color: #27ae60;
            width: 16px;
        }

        .empty-message {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-message i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .btn-add-record {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-add-record:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.3);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .navbar h1 {
                font-size: 16px;
            }

            .child-info {
                flex-direction: column;
                text-align: center;
            }

            .record-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>
            <i class="fas fa-history"></i> Riwayat Imunisasi
        </h1>
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="child-info">
                <div class="child-avatar">
                    <?php echo $child['gender'] === 'L' ? '👦' : '👧'; ?>
                </div>
                <div class="child-details">
                    <h2><?php echo htmlspecialchars($child['name']); ?></h2>
                    <p>
                        <i class="fas fa-birthday-cake"></i>
                        Lahir: <?php echo date('d M Y', strtotime($child['date_of_birth'])); ?>
                    </p>
                    <p>
                        <i class="fas fa-hourglass-half"></i>
                        Umur: <?php echo $age_years . ' tahun ' . $age_months . ' bulan'; ?>
                    </p>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-number"><?php echo count($records); ?></div>
                <div class="stat-label">Total Imunisasi Tercatat</div>
            </div>

            <a href="catat_imunisasi.php?child_id=<?php echo $child_id; ?>" class="btn-add-record">
                <i class="fas fa-plus-circle"></i> Catat Imunisasi Baru
            </a>
        </div>

        <!-- Records -->
        <div class="records-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-check-circle"></i>
                    Daftar Imunisasi yang Sudah Dilakukan
                </h3>
            </div>

            <?php if (!empty($records)): ?>
                <div style="padding: 20px;">
                    <?php foreach ($records as $record): ?>
                    <div class="record-card">
                        <div class="record-title">
                            ✓ <?php echo htmlspecialchars($record['name'] ?? 'N/A'); ?>
                        </div>
                        
                        <div class="record-info">
                            <div class="record-info-item">
                                <i class="fas fa-calendar"></i>
                                <span>
                                    Tanggal: 
                                    <strong><?php echo date('d M Y', strtotime($record['vaccination_date'])); ?></strong>
                                </span>
                            </div>
                            
                            <?php if (!empty($record['age_in_months'])): ?>
                            <div class="record-info-item">
                                <i class="fas fa-hourglass-half"></i>
                                <span>Umur: <strong><?php echo $record['age_in_months']; ?> bulan</strong></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($record['healthcare_provider'])): ?>
                            <div class="record-info-item">
                                <i class="fas fa-user-nurse"></i>
                                <span>Pemberi: <strong><?php echo htmlspecialchars($record['healthcare_provider']); ?></strong></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($record['location'])): ?>
                            <div class="record-info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Lokasi: <strong><?php echo htmlspecialchars($record['location']); ?></strong></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($record['batch_number'])): ?>
                            <div class="record-info-item">
                                <i class="fas fa-barcode"></i>
                                <span>Batch: <strong><?php echo htmlspecialchars($record['batch_number']); ?></strong></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($record['notes'])): ?>
                            <div class="record-info-item" style="grid-column: 1 / -1;">
                                <i class="fas fa-note-sticky"></i>
                                <span>Catatan: <strong><?php echo htmlspecialchars($record['notes']); ?></strong></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="empty-message">
                    <i class="fas fa-inbox"></i>
                    <h3>Belum Ada Riwayat Imunisasi</h3>
                    <p>Belum ada catatan imunisasi untuk anak ini.</p>
                    <a href="catat_imunisasi.php?child_id=<?php echo $child_id; ?>" class="btn-add-record">
                        <i class="fas fa-plus-circle"></i> Catat Imunisasi Pertama
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>