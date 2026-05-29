<?php
/**
 * DASHBOARDADMIN.PHP (ENHANCED)
 * Dashboard Admin untuk manage pengguna + BPS data
 * Updated: Session-based auth, error handling, validation
 */



// Include config dengan helper functions
require_once __DIR__ . '/config.php';

// Require admin access
require_admin();

// Get user info
$user_id = get_user_id();
$user_email = get_user_email();

// Get current user data
$user_data = get_user_data($user_id);
if (!$user_data) {
    logout();
}

// Get all users dari database
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Query dengan search (optional)
    if (!empty($search)) {
        $search = '%' . $search . '%';
        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("ssii", $search, $search, $limit, $offset);
    } else {
        $stmt = $conn->prepare(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();

    // Count total users (untuk pagination)
    if (!empty($search)) {
        $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE username LIKE ? OR email LIKE ?");
        $count_stmt->bind_param("ss", $search, $search);
    } else {
        $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    }
    
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_users = $count_row['total'];
    $total_pages = ceil($total_users / $limit);
    $count_stmt->close();

    // Count admin dan user
    $admin_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $admin_stmt->execute();
    $admin_count = $admin_stmt->get_result()->fetch_assoc()['count'];
    $admin_stmt->close();

} catch (Exception $e) {
    error_log('Dashboard Error: ' . $e->getMessage());
    $users = [];
    $total_users = 0;
    $total_pages = 1;
    $admin_count = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Imuno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .sidebar {
            height: 100vh;
            width: 280px;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: bold;
            letter-spacing: 1px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }

        .sidebar-header i {
            color: #667eea;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu a {
            padding: 15px 25px;
            display: block;
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border-left-color: #667eea;
            padding-left: 35px;
        }

        .sidebar-menu a.active {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            border-left-color: #667eea;
            font-weight: 600;
        }

        .sidebar-menu hr {
            margin: 15px 0;
            opacity: 0.2;
        }

        .sidebar-menu a.logout {
            color: #e74c3c;
        }

        .sidebar-menu a.logout:hover {
            background: rgba(231, 76, 60, 0.1);
            border-left-color: #e74c3c;
            color: #e74c3c;
        }

        .main-content {
            margin-left: 280px;
            padding: 40px;
            min-height: 100vh;
        }

        .navbar-top {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-left: 5px solid;
            transition: transform 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-box.total { border-left-color: #667eea; }
        .stat-box.admin { border-left-color: #e74c3c; }
        .stat-box.user { border-left-color: #f39c12; }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }

        .stat-box.admin .stat-number { color: #e74c3c; }
        .stat-box.user .stat-number { color: #f39c12; }

        .stat-label {
            color: #999;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-custom:active {
            transform: translateY(0);
        }

        .btn-sm-custom {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .btn-edit {
            background: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
        }

        .btn-edit:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.85rem;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: rgba(102, 126, 234, 0.1);
            border-bottom: 2px solid #667eea;
        }

        .table thead th {
            color: #667eea;
            font-weight: 600;
            padding: 15px;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        .table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge-admin {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }

        .badge-user {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }

        .pagination {
            margin-top: 20px;
            justify-content: center;
        }

        .pagination .page-link {
            color: #667eea;
            border-color: #e0e0e0;
            margin: 0 3px;
            border-radius: 5px;
        }

        .pagination .page-link:hover {
            color: white;
            background-color: #667eea;
            border-color: #667eea;
        }

        .pagination .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .bps-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            margin-top: 30px;
        }

        .bps-table {
            max-height: 400px;
            overflow-y: auto;
        }

        .loading {
            text-align: center;
            padding: 30px;
            color: #667eea;
        }

        .loading i {
            font-size: 2rem;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                margin-left: 250px;
                padding: 20px;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box input {
                width: 100%;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-shield-alt"></i> ADMIN
        </div>
        <div class="sidebar-menu">
            <a href="dashboardadmin.php" class="active">
                <i class="fas fa-users me-2"></i> Kelola Pengguna
            </a>
            <a href="dashboard.php">
                <i class="fas fa-home me-2"></i> Dashboard User
            </a>
            <hr>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOP NAVBAR -->
        <div class="navbar-top">
            <div>
                <h3 style="margin: 0; color: #333;">
                    <i class="fas fa-database me-2" style="color: #667eea;"></i>Dashboard Admin
                </h3>
            </div>
            <div class="user-info">
                <div>
                    <small style="color: #999;">Logged in as</small><br>
                    <strong style="color: #333;"><?php echo htmlspecialchars($user_data['username']); ?></strong>
                </div>
                <div class="user-avatar"><?php echo strtoupper(substr($user_data['username'], 0, 1)); ?></div>
            </div>
        </div>

        <!-- NOTIFIKASI -->
        <?php if (!empty($_GET['success'])): ?>
        <div style="background: rgba(46,204,113,0.15); color: #27ae60; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #27ae60; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($_GET['deleted'])): ?>
        <div style="background: rgba(231,76,60,0.1); color: #e74c3c; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #e74c3c; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-trash-alt"></i>
            User berhasil dihapus.
        </div>
        <?php endif; ?>

        <!-- STATISTICS -->
        <div class="stats-container">
            <div class="stat-box total">
                <div class="stat-label">
                    <i class="fas fa-users me-2"></i>Total Pengguna
                </div>
                <div class="stat-number"><?php echo $total_users; ?></div>
            </div>

            <div class="stat-box admin">
                <div class="stat-label">
                    <i class="fas fa-user-shield me-2"></i>Admin
                </div>
                <div class="stat-number"><?php echo $admin_count; ?></div>
            </div>

            <div class="stat-box user">
                <div class="stat-label">
                    <i class="fas fa-user me-2"></i>User Biasa
                </div>
                <div class="stat-number"><?php echo $total_users - $admin_count; ?></div>
            </div>
        </div>

        <!-- MANAGE USERS SECTION -->
        <div class="glass-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list me-2" style="color: #667eea;"></i>Manajemen Pengguna
                </div>
                <div class="search-box">
                    <form method="GET" style="display: flex; gap: 10px; flex: 1;">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Cari username atau email..." 
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                        <button type="submit" class="btn btn-custom btn-sm-custom">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="dashboardadmin.php" class="btn btn-custom btn-sm-custom" style="background: #999;">
                                <i class="fas fa-times me-2"></i>Reset
                            </a>
                        <?php endif; ?>
                    </form>
                    <a href="prosestambah.php" class="btn btn-custom btn-sm-custom">
                        <i class="fas fa-plus-circle me-2"></i>Tambah User
                    </a>
                </div>
            </div>

            <?php if (!empty($users)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tanggal Lahir</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = ($page - 1) * $limit + 1;
                        foreach ($users as $user): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <i class="far fa-calendar-alt me-2" style="opacity: 0.6;"></i>
                                <?php echo htmlspecialchars($user['tanggal_lahir']); ?>
                            </td>
                            <td class="text-center">
                                <?php if ($user['role'] == 'admin'): ?>
                                    <span class="badge badge-admin">
                                        <i class="fas fa-user-shield me-1"></i>Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-user">
                                        <i class="fas fa-user me-1"></i>User
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="prosesedit.php?id=<?php echo $user['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <a href="proseshapus.php?id=<?php echo $user['id']; ?>" class="btn-delete" 
                                   onclick="return confirm('Yakin ingin menghapus pengguna ini?');">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Pagination">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">First</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Last</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h4>Tidak ada data pengguna</h4>
                <p>Belum ada pengguna yang terdaftar. <a href="prosestambah.php">Tambah pengguna baru</a></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- BPS DATA SECTION -->
        <div class="glass-card">
            <div class="bps-title">
                <i class="fas fa-chart-bar me-2" style="color: #667eea;"></i>
                Data BPS: Persentase Balita Pernah Mendapat Imunisasi Campak
            </div>
            <div class="bps-table">
                <div class="loading">
                    <i class="fas fa-spinner"></i>
                    <p>Memuat data BPS...</p>
                </div>
                <table class="table table-hover" id="bpsTable" style="display: none;">
                    <thead>
                        <tr style="background: rgba(102, 126, 234, 0.1);">
                            <th style="color: #667eea; font-weight: 600;">Provinsi</th>
                            <th style="color: #667eea; font-weight: 600; text-align: right;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody id="dataBPS"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load BPS data
        document.addEventListener('DOMContentLoaded', function() {
            fetch('bps_imunisasi.php')
                .then(res => {
                    if (!res.ok) throw new Error('Network response error');
                    return res.json();
                })
                .then(data => {
                    let tbody = document.getElementById("dataBPS");
                    let table = document.getElementById("bpsTable");
                    tbody.innerHTML = "";

                    let provinsi = data.vervar || [];
                    let isiData = data.datacontent || {};

                    if (provinsi.length > 0) {
                        provinsi.forEach(prov => {
                            let kode = prov.val;
                            let nilai = null;

                            for (let key in isiData) {
                                if (key.startsWith(kode)) {
                                    nilai = isiData[key];
                                    break;
                                }
                            }

                            tbody.innerHTML += `<tr>
                                <td><strong>${htmlEscape(prov.label)}</strong></td>
                                <td style="text-align: right;">
                                    ${nilai !== null ? `<span style="color: #667eea; font-weight: 600;">${nilai}%</span>` : '-'}
                                </td>
                            </tr>`;
                        });

                        // Show table, hide loading
                        document.querySelector('.loading').style.display = 'none';
                        table.style.display = 'table';
                    } else {
                        tbody.innerHTML = `<tr><td colspan="2" class="text-center" style="color: #999;">Data tidak ditemukan</td></tr>`;
                        document.querySelector('.loading').style.display = 'none';
                        table.style.display = 'table';
                    }
                })
                .catch(err => {
                    console.error('Error loading BPS data:', err);
                    document.querySelector('.loading').innerHTML = `
                        <i class="fas fa-exclamation-circle" style="color: #e74c3c;"></i>
                        <p style="color: #e74c3c;">Gagal memuat data BPS. Silakan refresh halaman.</p>
                    `;
                });
        });

        // Helper function to escape HTML
        function htmlEscape(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>