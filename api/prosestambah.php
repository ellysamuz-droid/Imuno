<?php
require 'koneksi.php';

$username      = $_POST['username']      ?? '';
$email         = $_POST['email']         ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$password      = $_POST['password']      ?? '';
$role          = $_POST['role']          ?? '';

if (empty($username) || empty($email) || empty($tanggal_lahir) || empty($password)) {
    echo "Semua field wajib diisi!";
    exit();
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $db = Database::getInstance();

    $db->execute(
        "INSERT INTO pengguna (username, email, tanggal_lahir, password, role) VALUES (?, ?, ?, ?, ?)",
        'sssss',
        [$username, $email, $tanggal_lahir, $password_hash, $role]
    );

    header("Location: dashboardadmin.php");
    exit();

} catch (RuntimeException $e) {
    echo "Gagal menambah user: " . $e->getMessage();
}
?>