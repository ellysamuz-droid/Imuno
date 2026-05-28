<?php
/**
 * Register Process
 * File ini menangani proses registrasi user baru
 */

header('Content-Type: application/json');

// Include config
require_once 'config.php';

// Cek request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit();
}

// Ambil data dari request
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$password = $_POST['password'] ?? ''; // Jangan sanitize password
$password_confirm = $_POST['password_confirm'] ?? '';

// Validasi input
$errors = [];

// Validasi username
if (empty($username)) {
    $errors['username'] = 'Username harus diisi';
} elseif (strlen($username) < 3) {
    $errors['username'] = 'Username minimal 3 karakter';
} elseif (strlen($username) > 50) {
    $errors['username'] = 'Username maksimal 50 karakter';
}

// Validasi email
if (empty($email)) {
    $errors['email'] = 'Email harus diisi';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format email tidak valid';
}

// Validasi tanggal lahir
if (empty($tanggal_lahir)) {
    $errors['tanggal_lahir'] = 'Tanggal lahir harus diisi';
} else {
    $birth_date = DateTime::createFromFormat('Y-m-d', $tanggal_lahir);
    if (!$birth_date || $birth_date > new DateTime()) {
        $errors['tanggal_lahir'] = 'Tanggal lahir tidak valid';
    }
}

// Validasi password
if (empty($password)) {
    $errors['password'] = 'Password harus diisi';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password minimal 8 karakter';
}

// Validasi password confirmation
if ($password !== $password_confirm) {
    $errors['password_confirm'] = 'Password tidak cocok';
}

// Jika ada error, return error
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Cek apakah email sudah terdaftar
$check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_email->bind_param("s", $email);
$check_email->execute();
$check_email->store_result();

if ($check_email->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar. Silakan gunakan email lain.']);
    exit();
}

// Hash password
$hashed_password = hash_password($password);

// Insert user ke database
$insert_user = $conn->prepare("INSERT INTO users (username, email, password, tanggal_lahir, role) VALUES (?, ?, ?, ?, ?)");
$role = 'orang_tua'; // Role default

$insert_user->bind_param("sssss", $username, $email, $hashed_password, $tanggal_lahir, $role);

if ($insert_user->execute()) {
    $user_id = $insert_user->insert_id;
    
    // Set cookie untuk auto-login setelah register
    set_auth_cookie($user_id, $email, $role);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Registrasi berhasil! Anda akan diarahkan ke dashboard...',
        'user' => [
            'id' => $user_id,
            'username' => $username,
            'email' => $email,
            'role' => $role
        ]
    ]);
    exit();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat registrasi. Coba lagi nanti.']);
    exit();
}

$conn->close();
?>
