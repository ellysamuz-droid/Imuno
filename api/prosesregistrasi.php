<?php
// Set header JSON dan error reporting
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Jangan tampilkan error PHP langsung, gunakan JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // ✅ PENTING: Import config dan Database class
    require_once __DIR__ . '/config.php';

    // ✅ Validasi method request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method hanya menerima POST'
        ]);
        exit();
    }

    // ✅ Ambil data dari POST
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';
    $tanggal_lahir = isset($_POST['tanggal_lahir']) ? trim($_POST['tanggal_lahir']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    // ✅ Validasi input di server (double check)
    $errors = [];

    if (empty($username)) {
        $errors['username'] = 'Nama pengguna harus diisi';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Nama pengguna minimal 3 karakter';
    } elseif (strlen($username) > 50) {
        $errors['username'] = 'Nama pengguna maksimal 50 karakter';
    }

    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    }

    if (empty($tanggal_lahir)) {
        $errors['tanggal_lahir'] = 'Tanggal lahir harus diisi';
    }

    if (empty($password)) {
        $errors['password'] = 'Kata sandi harus diisi';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Kata sandi minimal 8 karakter';
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Kata sandi tidak cocok';
    }

    // ✅ Jika ada error validasi, kirim langsung
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $errors
        ]);
        exit();
    }

    // ✅ Cek apakah Database sudah tersambung
    if (!isset($conn) || !$conn) {
        throw new Exception('Koneksi database tidak tersedia. Pastikan config.php sudah benar.');
    }

    // ✅ Cek email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Prepare statement gagal: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execute query gagal: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode([
            'success' => false,
            'message' => 'Email sudah terdaftar. Gunakan email lain atau masuk ke akun Anda.'
        ]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // ✅ Hash password dengan bcrypt (lebih aman)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

    // ✅ Insert ke database
    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, tanggal_lahir, password, created_at, updated_at) 
         VALUES (?, ?, ?, ?, NOW(), NOW())"
    );

    if (!$stmt) {
        throw new Exception('Prepare insert statement gagal: ' . $conn->error);
    }

    $stmt->bind_param("ssss", $username, $email, $tanggal_lahir, $hashed_password);

    if (!$stmt->execute()) {
        // Cek error spesifik dari database
        $error_msg = $stmt->error;
        throw new Exception('Insert gagal: ' . $error_msg);
    }

    // ✅ Ambil ID yang baru diinsert
    $user_id = $stmt->insert_id;
    $stmt->close();

    // ✅ Success response
    http_response_code(201); // Created
    echo json_encode([
        'success' => true,
        'message' => 'Pendaftaran berhasil! Silakan login dengan akun Anda.',
        'user_id' => $user_id,
        'redirect' => 'login.php'
    ]);
    exit();

} catch (Exception $e) {
    // ✅ Error response
    error_log('Registration Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memproses pendaftaran. ' . $e->getMessage()
    ]);
    exit();
}
?>