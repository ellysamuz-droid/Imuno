<?php
/**
 * EDIT_ANAK.PHP
 * Form untuk edit data anak
 */

require_once __DIR__ . '/config.php';
require_login();

$user_id = get_user_id();
$child_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($child_id <= 0) {
    header("Location: data_anak.php");
    exit();
}

// Get child data
try {
    $stmt = $conn->prepare("SELECT * FROM children WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $child_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $child = $result->fetch_assoc();
    $stmt->close();

    if (!$child) {
        header("Location: data_anak.php");
        exit();
    }
} catch (Exception $e) {
    error_log('Edit Child Error: ' . $e->getMessage());
    header("Location: data_anak.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $date_of_birth = sanitize_input($_POST['date_of_birth'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $blood_type = sanitize_input($_POST['blood_type'] ?? '');

    // Validasi
    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors['name'] = 'Nama anak minimal 2 karakter';
    }

    if (empty($date_of_birth)) {
        $errors['date_of_birth'] = 'Tanggal lahir harus diisi';
    }

    if (empty($gender) || !in_array($gender, ['L', 'P'])) {
        $errors['gender'] = 'Jenis kelamin harus dipilih';
    }

    if (!empty($blood_type) && !in_array($blood_type, ['A', 'B', 'AB', 'O'])) {
        $errors['blood_type'] = 'Golongan darah tidak valid';
    }

    if (!empty($errors)) {
        $error = 'Ada kesalahan: ' . implode(', ', $errors);
    } else {
        try {
            $stmt = $conn->prepare(
                "UPDATE children SET name = ?, date_of_birth = ?, gender = ?, blood_type = ?, updated_at = NOW()
                 WHERE id = ? AND user_id = ?"
            );
            $stmt->bind_param("sssiii", $name, $date_of_birth, $gender, $blood_type, $child_id, $user_id);

            if ($stmt->execute()) {
                $success = 'Data anak berhasil diperbarui!';
                log_activity($user_id, 'UPDATE_CHILD', 'Update anak: ' . $name);
                // Refresh child data
                $stmt = $conn->prepare("SELECT * FROM children WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $child_id, $user_id);
                $stmt->execute();
                $child = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } else {
                $error = 'Gagal memperbarui data anak: ' . $stmt->error;
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
    <title>Edit Data Anak - Reminder Imunisasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #FFE5E9 0%, #E8F5F7 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        }

        .form-header h2 {
            color: #E85D6F;
            font-weight: bold;
            font-size: 1.8rem;
        }

        .form-header p {
            color: #999;
            margin-top: 10px;
        }

        .child-id {
            background: rgba(232, 93, 111, 0.1);
            padding: 12px 15px;
            border-radius: 8px;
            text-align: center;
            color: #E85D6F;
            font-size: 0.9rem;
            margin-bottom: 20px;
            border-left: 4px solid #E85D6F;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #E85D6F;
            box-shadow: 0 0 0 3px rgba(232, 93, 111, 0.1);
        }

        .form-hint {
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .gender-options,
        .blood-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .gender-options label,
        .blood-options label {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 0;
            font-weight: normal;
        }

        .gender-options input,
        .blood-options input {
            width: auto;
            margin-right: 8px;
            cursor: pointer;
        }

        .gender-options label:hover,
        .blood-options label:hover {
            border-color: #E85D6F;
            background: rgba(232, 93, 111, 0.05);
        }

        .gender-options input:checked + label,
        .blood-options input:checked + label {
            border-color: #E85D6F;
            background: rgba(232, 93, 111, 0.1);
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
            color: white;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h2><i class="fas fa-edit me-2"></i>Edit Data Anak</h2>
            <p>Perbarui informasi tentang anak Anda</p>
        </div>

        <div class="child-id">
            <i class="fas fa-id-card me-2"></i>
            ID: <?php echo $child['id']; ?> - <?php echo htmlspecialchars($child['name']); ?>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="name">Nama Anak <span style="color: #E85D6F;">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Masukkan nama anak"
                    value="<?php echo htmlspecialchars($child['name']); ?>"
                    required
                >
                <div class="form-hint">Nama lengkap anak Anda</div>
            </div>

            <div class="form-group">
                <label for="date_of_birth">Tanggal Lahir <span style="color: #E85D6F;">*</span></label>
                <input 
                    type="date" 
                    id="date_of_birth" 
                    name="date_of_birth"
                    value="<?php echo htmlspecialchars($child['date_of_birth']); ?>"
                    required
                >
                <div class="form-hint">Pilih tanggal lahir anak</div>
            </div>

            <div class="form-group">
                <label>Jenis Kelamin <span style="color: #E85D6F;">*</span></label>
                <div class="gender-options">
                    <div>
                        <input 
                            type="radio" 
                            id="gender_l" 
                            name="gender" 
                            value="L"
                            <?php echo $child['gender'] === 'L' ? 'checked' : ''; ?>
                            required
                        >
                        <label for="gender_l">👦 Laki-laki</label>
                    </div>
                    <div>
                        <input 
                            type="radio" 
                            id="gender_p" 
                            name="gender" 
                            value="P"
                            <?php echo $child['gender'] === 'P' ? 'checked' : ''; ?>
                            required
                        >
                        <label for="gender_p">👧 Perempuan</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Golongan Darah (Opsional)</label>
                <select name="blood_type">
                    <option value="">-- Pilih Golongan Darah --</option>
                    <option value="A" <?php echo $child['blood_type'] === 'A' ? 'selected' : ''; ?>>A</option>
                    <option value="B" <?php echo $child['blood_type'] === 'B' ? 'selected' : ''; ?>>B</option>
                    <option value="AB" <?php echo $child['blood_type'] === 'AB' ? 'selected' : ''; ?>>AB</option>
                    <option value="O" <?php echo $child['blood_type'] === 'O' ? 'selected' : ''; ?>>O</option>
                </select>
                <div class="form-hint">Informasi golongan darah membantu dalam keperluan medis</div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
                <a href="data_anak.php" class="btn-cancel">
                    <i class="fas fa-times me-2"></i>Batal
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