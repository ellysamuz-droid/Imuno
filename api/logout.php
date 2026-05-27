<?php
/**
 * Logout Process
 * File ini menangani proses logout user
 */

// Include config
require_once 'config.php';

// Call logout function
logout();

// Jika logout() tidak redirect, redirect manual
header("Location: /");
exit();
?>
