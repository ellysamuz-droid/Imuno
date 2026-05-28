<?php
ob_start();
require __DIR__ . '/koneksi.php';

// Cek cookie auth
if (!isset($_COOKIE['auth_token'])) {
    header("Location: /api/loginForm.php");
    exit();
}

$tokenData = explode('|', base64_decode($_COOKIE['auth_token']));
$userRole  = $tokenData[1] ?? null;

if ($userRole != 'admin') {
    header("Location: /api/loginForm.php");
    exit();
}

$id            = $_POST['id']            ?? '';
$username      = $_POST['username']      ?? '';
$email         = $_POST['email']         ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$role          = $_POST['role']          ?? '';

if (empty($id) || empty($username) || empty($email) || empty($tanggal_lahir) || empty($role)) {
    echo "Semua field wajib diisi!";
    exit();
}

try {
    $db = Database::getInstance();

    $db->execute(
        "UPDATE pengguna SET username = ?, email = ?, tanggal_lahir = ?, role = ? WHERE id = ?",
        'ssssi',
        [$username, $email, $tanggal_lahir, $role, $id]
    );

    header("Location: /api/dashboardadmin.php");
    exit();

} catch (RuntimeException $e) {
    echo "Update Gagal: " . $e->getMessage();
}
?>