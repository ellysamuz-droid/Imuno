<?php
/**
 * EDITDATA.PHP
 * Form untuk edit data user
 */

require_once __DIR__ . '/config.php';
require_admin();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    header("Location: dashboardadmin.php");
    exit();
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: dashboardadmin.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $tanggal_lahir = sanitize_input($_POST['tanggal_lahir'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'user');

    // Validasi
    $errors = [];

    if (empty($username) || strlen($username) < 3) {
        $errors['username'] = 'Username minimal 3 karakter';
    }

    if (empty($email) || !validate_email($email)) {
        $errors['email'] = 'Email tidak valid';
    }

    // Cek email sudah dipakai user lain
    if ($email !== $user['email']) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors['email'] = 'Email sudah dipakai user lain';
        }
        $check_stmt->close();
    }

    if (empty($tanggal_lahir)) {
        $errors['tanggal_lahir'] = 'Tanggal lahir harus diisi';
    }

    if (!in_array($role, ['admin', 'user'])) {
        $errors['role'] = 'Role tidak valid';
    }

    // Jika password diisi, validasi
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors['password'] = 'Password minimal 8 karakter';
        }
    }

    // Jika ada error
    if (!empty($errors)) {
        $error = 'Ada kesalahan: ' . implode(', ', $errors);
    } else {
        // Update user
        if (!empty($password)) {
            $hashed_password = hash_password($password);
            $update_stmt = $conn->prepare(
                "UPDATE users SET username = ?, email = ?, tanggal_lahir = ?, password = ?, role = ?, updated_at = NOW() WHERE id = ?"
            );
            $update_stmt->bind_param("sssssi", $username, $email, $tanggal_lahir, $hashed_password, $role, $user_id);
        } else {
            $update_stmt = $conn->prepare(
                "UPDATE users SET username = ?, email = ?, tanggal_lahir = ?, role = ?, updated_at = NOW() WHERE id = ?"
            );
            $update_stmt->bind_param("ssssi", $username, $email, $tanggal_lahir, $role, $user_id);
        }

        if ($update_stmt->execute()) {
            $update_stmt->close();
            header('Location: dashboardadmin.php?success=User+berhasil+diupdate');
            exit();
        } else {
            $error = 'Gagal mengupdate user: ' . $update_stmt->error;
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
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

        .user-id {
            background: rgba(102, 126, 234, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 20px;
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

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-submit {
            flex: 1;
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
            flex: 1;
            padding: 12px;
            background: #999;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .btn-back:hover {
            background: #777;
            color: white;
        }

        .form-hint {
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
        }

        .password-note {
            background: rgba(52, 152, 219, 0.1);
            padding: 12px;
            border-radius: 8px;
            color: #3498db;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2><i class="fas fa-user-edit me-2"></i>Edit User</h2>
            <p style="color: #999;">Update data user yang sudah terdaftar</p>
        </div>

        <div class="user-id">
            <i class="fas fa-user-circle me-2"></i>User ID: <?php echo $user['id']; ?> - <?php echo htmlspecialchars($user['username']); ?>
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
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="username">Username <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Masukkan username"
                    value="<?php echo htmlspecialchars($user['username']); ?>"
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
                    value="<?php echo htmlspecialchars($user['email']); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir <span style="color: #e74c3c;">*</span></label>
                <input 
                    type="date" 
                    id="tanggal_lahir" 
                    name="tanggal_lahir"
                    value="<?php echo htmlspecialchars($user['tanggal_lahir']); ?>"
                    required
                >
            </div>

            <div class="password-note">
                <i class="fas fa-info-circle me-2"></i>Kosongkan field password jika tidak ingin mengubahnya
            </div>

            <div class="form-group">
                <label for="password">Password Baru (Opsional)</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Masukkan password baru atau kosongkan"
                >
                <div class="form-hint">Minimal 8 karakter jika diisi</div>
            </div>

            <div class="form-group">
                <label for="role">Role <span style="color: #e74c3c;">*</span></label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User Biasa</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
                <div class="form-hint">Admin memiliki akses penuh ke sistem</div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
                <a href="dashboardadmin.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>