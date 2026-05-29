<?php
/**
 * PROSESLOGIN.PHP
 * File ini menangani proses login user
 * Includes role-based redirect: admin -> dashboardadmin.php, user -> dashboard.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Jangan tampilkan error PHP langsung
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Include config
    require_once __DIR__ . '/config.php';

    // ✅ Validasi request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method hanya menerima POST'
        ]);
        exit();
    }

    // ✅ Ambil data dari POST
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;

    // ✅ Validasi input
    $errors = [];

    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    } elseif (!validate_email($email)) {
        $errors['email'] = 'Format email tidak valid';
    }

    if (empty($password)) {
        $errors['password'] = 'Password harus diisi';
    }

    // Jika ada error validasi
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $errors
        ]);
        exit();
    }

    // ✅ Cari user berdasarkan email (case-insensitive)
    $email_lower = strtolower($email);
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE LOWER(email) = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare statement gagal: ' . $conn->error);
    }

    $stmt->bind_param("s", $email_lower);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute query gagal: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    // ✅ Cek apakah email ditemukan
    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Email atau password salah'
        ]);
        $stmt->close();
        exit();
    }

    // ✅ Ambil data user
    $user = $result->fetch_assoc();
    $stmt->close();

    // ✅ Verifikasi password
    if (!verify_password($password, $user['password'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Email atau password salah'
        ]);
        exit();
    }

    // ✅ Password benar! Set auth session/cookie
    $user_role = $user['role'] ?? 'user';
    set_auth_cookie($user['id'], $user['email'], $user_role);

    // ✅ Log activity
    log_activity($user['id'], 'LOGIN', 'Berhasil login');

    // ✅ Tentukan redirect URL berdasarkan role
    $redirect_url = ($user_role === 'admin') ? 'dashboardadmin.php' : 'dashboard.php';

    // ✅ Return success response dengan redirect
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil! Anda akan diarahkan...',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user_role
        ],
        'redirect' => $redirect_url
    ]);

    exit();

} catch (Exception $e) {
    error_log('Login Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat login. ' . $e->getMessage()
    ]);
    exit();
}
?>