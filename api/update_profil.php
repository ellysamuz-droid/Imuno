<?php
/**
 * UPDATE_PROFIL.PHP
 * Halaman untuk update data profil user termasuk nomor WhatsApp
 */

require_once __DIR__ . '/config.php';
require_login();

$user_id = get_user_id();
$user_data = get_user_data($user_id);

if (!$user_data) {
    logout();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $whatsapp_number = sanitize_input($_POST['whatsapp_number'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validasi
    $errors = [];
    
    if (empty($full_name) || strlen($full_name) < 3) {
        $errors[] = 'Nama minimal 3 karakter';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }
    
    if (!empty($whatsapp_number)) {
        // Validasi nomor WhatsApp (harus dimulai dengan + dan kode negara)
        if (!preg_match('/^\+\d{10,15}$/', $whatsapp_number)) {
            $errors[] = 'Nomor WhatsApp harus format internasional (contoh: +62812345678)';
        }
    }
    
    if (!empty($errors)) {
        $error = 'Ada kesalahan: ' . implode(', ', $errors);
    } else {
        try {
            // Update nama dan email
            $updateQuery = "UPDATE users SET username = ?, email = ?, whatsapp_number = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssi", $full_name, $email, $whatsapp_number, $user_id);
            
            if ($stmt->execute()) {
                // Jika ada password baru, update juga
                if (!empty($password) && strlen($password) >= 6) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $pwdQuery = "UPDATE users SET password = ? WHERE id = ?";
                    $pwdStmt = $conn->prepare($pwdQuery);
                    $pwdStmt->bind_param("si", $hashedPassword, $user_id);
                    $pwdStmt->execute();
                    $pwdStmt->close();
                }
                
                $success = '✅ Profil berhasil diperbarui!';
                
                // Refresh user data
                $user_data = get_user_data($user_id);
            } else {
                $error = 'Gagal memperbarui profil: ' . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profil - Imuno</title>
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
            padding: 20px;
        }

        .navbar {
            background: linear-gradient(135deg, #E85D6F 0%, #D94560 100%);
            padding: 20px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
            border-radius: 10px;
        }

        .navbar h1 {
            font-size: 22px;
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
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #E85D6F;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #999;
            font-size: 14px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #E85D6F;
            box-shadow: 0 0 0 3px rgba(232, 93, 111, 0.1);
        }

        .form-hint {
            font-size: 12px;
            color: #999;
            margin-top: 6px;
        }

        .whatsapp-info {
            background: rgba(37, 211, 102, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #25d366;
        }

        .whatsapp-info h4 {
            color: #25d366;
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .whatsapp-info p {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-submit {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #E85D6F, #D94560);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(232, 93, 111, 0.3);
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            background: #999;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #777;
        }

        .password-note {
            background: #f0f0f0;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            color: #666;
            margin-top: 8px;
        }

        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px 20px;
            }

            .form-card {
                padding: 20px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>
            <i class="fas fa-user-circle"></i> Update Profil
        </h1>
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h2>Perbarui Data Profil</h2>
                <p>Ubah informasi akun Anda termasuk nomor WhatsApp untuk menerima reminder</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success show">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger show">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="whatsapp-info">
                <h4>
                    <i class="fab fa-whatsapp"></i> Aktifkan WhatsApp Reminder
                </h4>
                <p>
                    Masukkan nomor WhatsApp Anda untuk menerima pengingat otomatis jadwal imunisasi anak 5 hari dan 1 hari sebelum jadwal.
                </p>
            </div>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name"
                        placeholder="Masukkan nama lengkap"
                        value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        placeholder="Masukkan email"
                        value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="whatsapp_number">Nomor WhatsApp</label>
                    <input 
                        type="text" 
                        id="whatsapp_number" 
                        name="whatsapp_number"
                        placeholder="+62812345678"
                        value="<?php echo htmlspecialchars($user_data['whatsapp_number'] ?? ''); ?>"
                    >
                    <div class="form-hint">
                        Format: +62812345678 (dengan kode negara +62 untuk Indonesia)<br>
                        Kosongkan jika tidak ingin menerima reminder WhatsApp
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password Baru (Opsional)</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        placeholder="Biarkan kosong jika tidak ingin mengubah"
                    >
                    <div class="password-note">
                        Jika ingin mengubah password, masukkan password baru (minimal 6 karakter)
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="dashboard.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>