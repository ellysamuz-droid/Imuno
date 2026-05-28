<?php
/**
 * config.php
 * Configuration file untuk database dan helper functions
 * 
 * PENTING: File ini HARUS ada dan di-include di setiap file PHP
 */

// ===== DATABASE CONFIGURATION =====
// TODO: Ubah sesuai database Anda

define('DB_HOST', 'localhost');      // Host database (localhost untuk lokal)
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL (kosong untuk lokal)
define('DB_NAME', 'imunisasi_db');   // Nama database

// ===== CONNECT TO DATABASE =====
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    ]));
}

// Set charset
$conn->set_charset("utf8");

// ===== HELPER FUNCTIONS =====

/**
 * Sanitize input untuk mencegah SQL injection
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Hash password dengan password_hash
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password
 */
function verify_password($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

/**
 * Set authentication cookie
 */
function set_auth_cookie($user_id, $email, $role) {
    // Generate token
    $token = hash('sha256', $user_id . $email . time());
    
    // Set cookies (berlaku 7 hari)
    setcookie('user_id', $user_id, time() + (86400 * 7), '/', '', false, true);
    setcookie('user_email', $email, time() + (86400 * 7), '/', '', false, true);
    setcookie('user_role', $role, time() + (86400 * 7), '/', '', false, true);
    setcookie('user_token', $token, time() + (86400 * 7), '/', '', false, true);
}

/**
 * Clear authentication cookie (untuk logout)
 */
function clear_auth_cookie() {
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('user_email', '', time() - 3600, '/');
    setcookie('user_role', '', time() - 3600, '/');
    setcookie('user_token', '', time() - 3600, '/');
}

/**
 * Check apakah user sudah login
 */
function is_logged_in() {
    return isset($_COOKIE['user_id']) && isset($_COOKIE['user_email']);
}

/**
 * Get user ID dari cookie
 */
function get_user_id() {
    return $_COOKIE['user_id'] ?? null;
}

/**
 * Get user email dari cookie
 */
function get_user_email() {
    return $_COOKIE['user_email'] ?? null;
}

/**
 * Get user role dari cookie
 */
function get_user_role() {
    return $_COOKIE['user_role'] ?? null;
}

/**
 * Require login - redirect ke login page kalau belum login
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Logout - clear cookies dan redirect
 */
function logout() {
    clear_auth_cookie();
    header('Location: login.php');
    exit();
}

/**
 * Check apakah user adalah admin
 */
function is_admin() {
    return get_user_role() === 'admin';
}

/**
 * Check apakah user adalah dokter
 */
function is_doctor() {
    return get_user_role() === 'dokter';
}

/**
 * Check apakah user adalah orang tua
 */
function is_parent() {
    return get_user_role() === 'orang_tua';
}

/**
 * Send JSON response
 */
function send_json($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}

/**
 * Get last insert ID
 */
function get_last_insert_id() {
    global $conn;
    return $conn->insert_id;
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function is_strong_password($password) {
    // Minimal 8 karakter
    if (strlen($password) < 8) {
        return false;
    }
    // Harus punya huruf besar dan kecil
    if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password)) {
        return false;
    }
    // Harus punya angka
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

/**
 * Generate random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// ===== COMMON QUERIES =====

/**
 * Get user by ID
 */
function get_user($user_id) {
    global $conn;
    $query = $conn->prepare("SELECT id, username, email, role, tanggal_lahir FROM users WHERE id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

/**
 * Get user by email
 */
function get_user_by_email($email) {
    global $conn;
    $query = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

/**
 * Check apakah email sudah terdaftar
 */
function email_exists($email) {
    global $conn;
    $query = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    return $query->get_result()->num_rows > 0;
}

/**
 * Check apakah username sudah terdaftar
 */
function username_exists($username) {
    global $conn;
    $query = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    return $query->get_result()->num_rows > 0;
}

// ===== CLOSE CONNECTION SECARA OTOMATIS =====
register_shutdown_function(function() {
    global $conn;
    if ($conn && $conn->ping()) {
        $conn->close();
    }
});

?>