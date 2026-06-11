<?php
/**
 * JADWAL_IMUNISASI.PHP
 * Tampilkan jadwal imunisasi per anak
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

    // 1. Cek apakah anak ini sudah memiliki jadwal di database
    $check_stmt = $conn->prepare("SELECT COUNT(*) as total FROM immunization_schedules WHERE child_id = ?");
    $check_stmt->bind_param("i", $child_id);
    $check_stmt->execute();
    $has_schedule = $check_stmt->get_result()->fetch_assoc()['total'];
    $check_stmt->close();

    // 2. JIKA BELUM ADA JADWAL, GENERATE OTOMATIS DI SINI
    if ($has_schedule == 0) {
        // Ambil semua tipe imunisasi master
        $types_res = $conn->query("SELECT id, recommended_age_range FROM immunization_types ORDER BY id ASC");
        
        if ($types_res && $types_res->num_rows > 0) {
            $insert_stmt = $conn->prepare(
                "INSERT INTO immunization_schedules (child_id, immunization_type_id, scheduled_date, age_in_months, status) 
                 VALUES (?, ?, ?, ?, 'pending')"
            );

            while ($type = $types_res->fetch_assoc()) {
                $months_to_add = (int)$type['recommended_age_range'];
                
                // Hitung tanggal jadwal: Tanggal Lahir + X Bulan
                $birth_date_obj = new DateTime($child['date_of_birth']);
                if ($months_to_add > 0) {
                    $birth_date_obj->modify("+" . $months_to_add . " month");
                }
                $scheduled_date = $birth_date_obj->format('Y-m-d');

                // Bind dan eksekusi insert
                $insert_stmt->bind_param("iisi", $child_id, $type['id'], $scheduled_date, $months_to_add);
                $insert_stmt->execute();
            }
            $insert_stmt->close();
        }
    }

    // 3. Ambil ulang data immunization schedule (sekarang dipastikan sudah terisi)
    $schedule_stmt = $conn->prepare(
        "SELECT ims.*, immu.name, immu.description, immu.recommended_age_range 
         FROM immunization_schedules ims
         LEFT JOIN immunization_types immu ON ims.immunization_type_id = immu.id
         WHERE ims.child_id = ?
         ORDER BY ims.age_in_months ASC"
    );
    $schedule_stmt->bind_param("i", $child_id);
    $schedule_stmt->execute();
    $schedules = $schedule_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $schedule_stmt->close();

} catch (Exception $e) {
    error_log('Jadwal Imunisasi Error: ' . $e->getMessage());
    $schedules = [];
}

// Calculate child age
$birthdate = new DateTime($child['date_of_birth']);
$today = new DateTime();
$age_years = $today->diff($birthdate)->y;
$age_months = $today->diff($birthdate)->m;
$age_days = $today->diff($birthdate)->d;

// Get status count
$pending = count(array_filter($schedules, fn($s) => $s['status'] === 'pending'));
$completed = count(array_filter($schedules, fn($s) => $s['status'] === 'completed'));
$upcoming = count(array_filter($schedules, fn($s) => $s['scheduled_date'] && $s['scheduled_date'] > $today->format('Y-m-d')));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Imunisasi - Imuno</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .stat-box {
            background: rgba(232, 93, 111, 0.1);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #E85D6F;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .schedule-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-header {
            background: rgba(232, 93, 111, 0.1);
            padding: 20px;
        }

        .table-header h3 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header i {
            color: #E85D6F;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(232, 93, 111, 0.1);
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #E85D6F;
            border-bottom: 2px solid #E85D6F;
        }

        td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: rgba(232, 93, 111, 0.05);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-upcoming {
            background: #cfe2ff;
            color: #084298;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-message i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
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

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>
            <i class="fas fa-calendar"></i> Jadwal Imunisasi
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

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number"><?php echo count($schedules); ?></div>
                    <div class="stat-label">Total Jadwal</div>
                </div>
                <div class="stat-box" style="background: rgba(46, 204, 113, 0.1); color: #27ae60;">
                    <div class="stat-number" style="color: #27ae60;"><?php echo $completed; ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
                <div class="stat-box" style="background: rgba(243, 156, 18, 0.1); color: #d68910;">
                    <div class="stat-number" style="color: #d68910;"><?php echo $upcoming; ?></div>
                    <div class="stat-label">Akan Datang</div>
                </div>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="schedule-table">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list"></i>
                    Daftar Jadwal Imunisasi
                </h3>
            </div>

            <?php if (!empty($schedules)): ?>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Imunisasi</th>
                        <th>Jadwal</th>
                        <th>Umur</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($schedules as $schedule): 
                        $status = $schedule['status'] ?? 'pending';
                        $scheduled_date = $schedule['scheduled_date'] ? new DateTime($schedule['scheduled_date']) : null;
                        
                        // Logika penentuan badge status
                        if ($status === 'completed') {
                            $display_status = 'Selesai';
                            $status_class = 'status-completed';
                        } elseif ($scheduled_date && $scheduled_date < $today) {
                            $display_status = 'Lewat Jadwal';
                            $status_class = 'status-pending'; // Menggunakan style kuning/merah terlewat
                        } else {
                            $display_status = 'Akan Datang';
                            $status_class = 'status-upcoming';
                        }
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php 
                                if($schedule['age_in_months'] == 0) {
                                    echo "0-24 jam";
                                } else {
                                    echo $schedule['age_in_months'] . " Bulan";
                                }
                            ?>
                        </td>
                        <td>
                            <strong><?php echo $schedule['scheduled_date'] ? date('d/m/Y', strtotime($schedule['scheduled_date'])) : '-'; ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($schedule['name'] ?? 'N/A'); ?></strong>
                            <br><small style="color: #999;"><?php echo htmlspecialchars($schedule['description'] ?? ''); ?></small>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $display_status; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($status !== 'completed'): ?>
                            <a href="catat_imunisasi.php?schedule_id=<?php echo $schedule['id']; ?>&child_id=<?php echo $child_id; ?>" 
                            style="color: #3498db; text-decoration: none; font-weight: 600;">
                                Catat ✓
                            </a>
                            <?php else: ?>
                            <span style="color: #27ae60; font-weight: 600;"><i class="fas fa-check-circle"></i> Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php else: ?>
            <div class="empty-message">
                <i class="fas fa-inbox"></i>
                <h3>Belum Ada Jadwal Imunisasi</h3>
                <p>Belum ada jadwal imunisasi yang terbuat untuk anak ini.</p>
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