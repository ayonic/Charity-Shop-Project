<?php
/**
 * Role Switching Script for Administrators
 */
require_once __DIR__ . '/../config/init.php';
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    set_flash_message('error', 'You must be logged in to switch roles.');
    redirect('../login.php');
    exit;
}
global $pdo;
$stmt = $pdo->prepare("SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || $user['role_name'] !== 'admin') {
    set_flash_message('error', 'You must be an administrator to switch roles.');
    redirect('../login.php');
    exit;
}
// Get the requested role from URL parameter
$role = isset($_GET['role']) ? $_GET['role'] : 'admin';
// Validate the role
$valid_roles = ['admin', 'volunteer', 'customer', 'donor', 'manager', 'cashier'];
 if (!in_array($role, $valid_roles)) {
     set_flash_message('error', 'Invalid role specified.');
     redirect('/dashboard.php');
     exit;
 }
 // Set the view_as_role in session
 $_SESSION['view_as_role'] = $role;
 // Redirect to the appropriate dashboard based on role
 switch ($role) {
     case 'volunteer':
         redirect('../volunteer/dashboard.php');
         break;
     case 'customer':
         header('Location: ../customer/dashboard.php');
         exit;
     case 'donor':
         redirect('../donor/dashboard.php');
         break;
     case 'manager':
         redirect('../manager/dashboard.php');
         break;
     case 'cashier':
         redirect('../cashier/dashboard.php');
         break;
     default:
         // Reset view_as_role when switching back to admin
         unset($_SESSION['view_as_role']);
         redirect('/dashboard.php');
 }
// Remove the final closing brace and comment at the end of the file