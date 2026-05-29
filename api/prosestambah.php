<?php
/**
 * TAMBAHDATA.PHP
 * Form untuk menambah user baru
 */

require_once __DIR__ . '/config.php';
require_admin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $tanggal_lahir = sanitize_input($_POST['tanggal_lahir'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'user');

    // Validasi
    $errors = [];

    if (empty($username) || strlen($username) < 3) {
        $errors['username'] = 'Username minimal 3 karakter';
    }

    if (empty($email) || !validate_email($email)) {
        $errors['email'] = 'Email tidak valid';
    }

    if (empty($tanggal_lahir)) {
        $errors['tanggal_lahir'] = 'Tanggal lahir harus diisi';
    }

    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = 'Password minimal 8 karakter';
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Password tidak cocok';
    }

    if (!in_array($role, ['admin', 'user'])) {
        $errors['role'] = 'Role tidak valid';
    }

    // Jika ada error
    if (!empty($errors)) {
        $error = 'Ada kesalahan: ' . implode(', ', $errors);
    } else {
        // Cek email sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Insert user baru
            $hashed_password = hash_password($password);
            $insert_stmt = $conn->prepare(
                "INSERT INTO users (username, email, tanggal_lahir, password, role, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
            );
            $insert_stmt->bind_param("sssss", $username, $email, $tanggal_lahir, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $success = 'User berhasil ditambahkan!';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Gagal menambahkan user: ' . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .form-header h2 {
            font-weight: bold;
            color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-back {
            display: inline-block;
            margin-top: 15px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-back:hover {
            text-decoration: underline;
        }

        .form-hint {
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2><i class="fas fa-user-plus me-2"></i>Tambah User Baru</h2>
            <p style="color: #999;">Isi form di bawah untuk menambahkan user baru</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="dashboardadmin.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        <?php else: ?>
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="username">Username <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Masukkan username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    required
                >
                <div class="form-hint">Minimal 3 karakter, tanpa spasi</div>
            </div>

            <div class="form-group">
                <label for="email">Email <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="nama@email.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="date" 
                    id="tanggal_lahir" 
                    name="tanggal_lahir"
                    value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Minimal 8 karakter"
                    required
                >
                <div class="form-hint">Gunakan kombinasi huruf besar, kecil, angka, dan simbol</div>
            </div>

            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    placeholder="Ulangi password"
                    required
                >
            </div>

            <div class="form-group">
                <label for="role">Role <span style="color: #e74c3c;">*</span></label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo ($_POST['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>User Biasa</option>
                    <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
                <div class="form-hint">Admin memiliki akses penuh ke sistem</div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-plus-circle me-2"></i>Tambah User
            </button>

            <a href="dashboardadmin.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>