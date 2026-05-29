<?php
/**
 * LOGOUT.PHP
 * Menghapus session dan redirect ke login page
 */

require_once __DIR__ . '/config.php';

// Log activity sebelum logout
if (is_logged_in()) {
    log_activity(get_user_id(), 'LOGOUT', 'User logout');
}

// Logout user
logout();

// Fungsi logout() sudah handle redirect ke login.php
// Jadi kode di bawah tidak akan dieksekusi
?>