<?php
/**
 * PROSESHAPUS.PHP
 * Handle delete user dengan validasi
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_admin();

try {
    // Get user ID dari GET
    $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($user_id <= 0) {
        throw new Exception('User ID tidak valid');
    }

    // Validasi user exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User tidak ditemukan');
    }
    $check_stmt->close();

    // Prevent deleting own account
    if ($user_id === get_user_id()) {
        throw new Exception('Anda tidak bisa menghapus akun Anda sendiri!');
    }

    // Delete user
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $user_id);

    if ($delete_stmt->execute()) {
        // Log activity
        log_activity(get_user_id(), 'DELETE_USER', 'Delete user ID: ' . $user_id);
        
        // If AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } else {
            // Regular form submit
            header('Location: dashboardadmin.php?deleted=1');
        }
    } else {
        throw new Exception('Gagal menghapus user: ' . $delete_stmt->error);
    }
    $delete_stmt->close();

} catch (Exception $e) {
    error_log('Delete User Error: ' . $e->getMessage());
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        header('Location: dashboardadmin.php?error=' . urlencode($e->getMessage()));
    }
}

$conn->close();
?>