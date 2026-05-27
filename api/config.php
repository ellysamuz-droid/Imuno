<?php
/**
 * Database Configuration
 * File ini menangani koneksi ke database MySQL
 */

// Database Configuration
define('DB_HOST', 'localhost');      // Host database
define('DB_USER', 'root');           // Username database
define('DB_PASS', '');               // Password database
define('DB_NAME', 'imuno');          // Nama database

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset ke UTF-8
$conn->set_charset("utf8");

// Cek jika table users sudah ada, jika tidak buat
$sql_check = "SHOW TABLES LIKE 'users'";
$result = $conn->query($sql_check);

if ($result->num_rows == 0) {
    // Create users table
    $sql_create = "CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        tanggal_lahir DATE,
        role VARCHAR(20) DEFAULT 'orang_tua',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql_create) === TRUE) {
        // echo "Table users berhasil dibuat";
    } else {
        // echo "Error creating table: " . $conn->error;
    }
}

// Fungsi untuk sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Fungsi untuk verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Fungsi untuk set cookie
function set_auth_cookie($user_id, $user_email, $user_role) {
    $token = bin2hex(random_bytes(32)); // Generate random token
    $auth_data = $user_id . '|' . $user_email . '|' . $user_role;
    $encoded = base64_encode($auth_data);
    
    setcookie('auth_token', $encoded, time() + (86400 * 30), "/"); // 30 hari
    setcookie('user_id', $user_id, time() + (86400 * 30), "/");
    setcookie('user_email', $user_email, time() + (86400 * 30), "/");
    setcookie('user_role', $user_role, time() + (86400 * 30), "/");
}

// Fungsi untuk check apakah user sudah login
function is_logged_in() {
    return isset($_COOKIE['auth_token']) && isset($_COOKIE['user_id']);
}

// Fungsi untuk get current user ID
function get_user_id() {
    return isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : null;
}

// Fungsi untuk get current user email
function get_user_email() {
    return isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : null;
}

// Fungsi untuk get current user role
function get_user_role() {
    return isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : null;
}

// Fungsi untuk logout
function logout() {
    setcookie('auth_token', '', time() - 3600, "/");
    setcookie('user_id', '', time() - 3600, "/");
    setcookie('user_email', '', time() - 3600, "/");
    setcookie('user_role', '', time() - 3600, "/");
    session_destroy();
    header("Location: /");
    exit();
}

// Fungsi untuk redirect jika belum login
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.html");
        exit();
    }
}

// Fungsi untuk redirect jika sudah login
function require_logout() {
    if (is_logged_in()) {
        header("Location: dashboard.html");
        exit();
    }
}

?>
