<?php
/**
 * HAPUS_ANAK.PHP
 * Handle delete anak dengan validasi
 */

require_once __DIR__ . '/config.php';
require_login();

$user_id = get_user_id();
$child_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    if ($child_id <= 0) {
        throw new Exception('ID anak tidak valid');
    }

    // Verify child belongs to current user
    $check_stmt = $conn->prepare("SELECT id, name FROM children WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $child_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Data anak tidak ditemukan');
    }

    $child = $result->fetch_assoc();
    $child_name = $child['name'];
    $check_stmt->close();

    // Delete child
    $delete_stmt = $conn->prepare("DELETE FROM children WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $child_id, $user_id);

    if ($delete_stmt->execute()) {
        log_activity($user_id, 'DELETE_CHILD', 'Hapus anak: ' . $child_name);
        
        // Redirect dengan success message
        header('Location: data_anak.php?deleted=1');
        exit();
    } else {
        throw new Exception('Gagal menghapus data anak: ' . $delete_stmt->error);
    }

    $delete_stmt->close();

} catch (Exception $e) {
    error_log('Delete Child Error: ' . $e->getMessage());
    header('Location: data_anak.php?error=' . urlencode($e->getMessage()));
    exit();
}

$conn->close();
?>