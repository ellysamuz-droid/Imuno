<?php
/**
 * Login Process
 * File ini menangani proses login user
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
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; // Jangan sanitize password

// Validasi input
$errors = [];

if (empty($email)) {
    $errors['email'] = 'Email harus diisi';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format email tidak valid';
}

if (empty($password)) {
    $errors['password'] = 'Password harus diisi';
}

// Jika ada error, return error
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Query user berdasarkan email
$query = $conn->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ?");
$query->bind_param("s", $email);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    // Email tidak ditemukan
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Email atau password salah']);
    exit();
}

// Ambil data user
$user = $result->fetch_assoc();

// Verify password
if (!verify_password($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Email atau password salah']);
    exit();
}

// Password benar, set cookie
set_auth_cookie($user['id'], $user['email'], $user['role']);

// Return success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Login berhasil! Anda akan diarahkan ke dashboard...',
    'user' => [
        'id' => $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);

$query->close();
$conn->close();
?>
