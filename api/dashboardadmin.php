<?php
ob_start();

// Cek cookie auth
if (!isset($_COOKIE['auth_token'])) {
    header("Location: /api/loginForm.php"); exit();
}

$tokenData = explode('|', base64_decode($_COOKIE['auth_token']));
$userId    = $tokenData[0] ?? null;
$userRole  = $tokenData[1] ?? null;
$userEmail = $tokenData[2] ?? null;

if ($userRole != 'admin') {
    header("Location: /api/loginForm.php"); exit();
}

require __DIR__ . '/koneksi.php';

try {
    $db   = Database::getInstance();
    $rows = $db->query("SELECT * FROM pengguna");
} catch (RuntimeException $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">

    <style>
        body{
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../bgimunisasi.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Segoe UI', sans-serif;
            color: white;
        }

        .sidebar {
            height: 100vh;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu a {
            padding: 15px 25px;
            display: block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(240, 128, 128, 0.6);
            color: white;
            padding-left: 35px;
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
        }

        .stats-box {
            background: rgba(240, 128, 128, 0.4);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .table {
            color: white !important;
        }

        .table thead {
            background: rgba(0, 0, 0, 0.2);
        }

        .btn-custom {
            background-color: indianred;
            color: white;
            border: none;
        }

        .btn-custom:hover {
            background-color: #c94c4c;
            color: white;
        }

        .badge-admin { background-color: #dc3545; }
        .badge-user { background-color: #0dcaf0; color: #000; }
    </style>
</head>

<script>
    fetch('bps_imunisasi.php')
    .then(res => res.json())
    .then(data => {
        let tbody = document.getElementById("dataBPS");
        tbody.innerHTML = "";

        let provinsi = data.vervar;
        let isiData  = data.datacontent;

        if (provinsi && isiData) {
            provinsi.forEach(prov => {
                let kode  = prov.val;
                let nilai = null;

                for (let key in isiData) {
                    if (key.startsWith(kode)) {
                        nilai = isiData[key];
                        break;
                    }
                }

                tbody.innerHTML += `<tr>
                    <td>${prov.label}</td>
                    <td>${nilai !== null ? nilai + " %" : "-"}</td>
                </tr>`;
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='2'>Data tidak ditemukan</td></tr>";
        }
    })
    .catch(err => console.log(err));
</script>

<body>
    <div class="sidebar shadow">
        <div class="sidebar-header">
            <i class="fas fa-shield-alt me-2"></i> ADMIN PANEL
        </div>
        <div class="sidebar-menu">
            <a href="dashboardadmin.php" class="active"><i class="fas fa-users me-2"></i> Kelola Pengguna</a>
            <a href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard User</a>
            <hr class="mx-3 opacity-25">
            <a href="logout.php" class="text-warning"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-5">
                <h2 class="fw-bold"><i class="fas fa-database me-3"></i>Manajemen Pengguna</h2>
                <a href="tambahdata.php" class="btn btn-custom px-4 py-2 shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Pengguna
                </a>
            </div>

            <div class="glass-card p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="py-3">Username</th>
                                <th class="py-3">Email</th>
                                <th class="py-3">Tanggal Lahir</th>
                                <th class="py-3 text-center">Akun</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($row['username']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><i class="far fa-calendar-alt me-2 opacity-50"></i><?= htmlspecialchars($row['tanggal_lahir']); ?></td>
                                <td class="text-center">
                                    <?php if ($row['role'] == 'admin') : ?>
                                        <span class="badge bg-danger p-2 px-3"><i class="fas fa-user-shield me-1"></i> Admin</span>
                                    <?php else : ?>
                                        <span class="badge bg-info text-dark p-2 px-3"><i class="fas fa-user me-1"></i> User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="/api/editdata.php?id=<?= $row['id']; ?>" class="btn-action btn-edit me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="/api/proseshapus.php?id=<?= $row['id']; ?>"
                                       class="btn-action btn-delete"
                                       onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-4 mt-4">
                <h4>Persentase Balita yang Pernah Mendapat Imunisasi Campak Menurut Provinsi</h4>
                <table class="table table-bordered text-white">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="dataBPS"></tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>