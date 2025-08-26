<?php
/**
 * Delete User
 * 
 * This page handles the deletion of users from the system.
 * Only administrators can delete users.
 */

require_once 'config/init.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'You must be an administrator to access this page.');
    redirect('login.php');
    exit;
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user data for logging
$user = db_get_row("SELECT first_name, last_name FROM users WHERE id = ?", [$user_id]);

// Prevent admin from deleting themselves
if ($user_id === $_SESSION['user_id']) {
    set_flash_message('error', 'You cannot delete your own account.');
    redirect('users.php');
    exit;
}

// Delete user
if ($user && db_delete('users', ['id' => $user_id])) {
    set_flash_message('success', 'User deleted successfully!');
    log_activity($_SESSION['user_id'], 'user', 'Deleted user: ' . $user['first_name'] . ' ' . $user['last_name']);
} else {
    set_flash_message('error', 'Failed to delete user.');
}

redirect('users.php');