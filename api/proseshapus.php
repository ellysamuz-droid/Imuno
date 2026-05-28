<?php
ob_start();
require 'config.php';

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

// Cek apakah id tersedia
if (empty($_GET['id'])) {
    header("Location: /api/dashboardadmin.php");
    exit();
}

$id = (int) $_GET['id'];

try {
    $db = Database::getInstance();

    $db->execute(
        "DELETE FROM pengguna WHERE id = ?",
        'i',
        [$id]
    );

    header("Location: /api/dashboardadmin.php");
    exit();

} catch (RuntimeException $e) {
    echo "Gagal menghapus data: " . $e->getMessage();
}
?>
