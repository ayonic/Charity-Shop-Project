<?php
/**
 * Logout Page
 * 
 * This page handles user logout.
 */

// Include initialization file
require_once 'config/init.php';

// Destroy session
session_destroy();

// Redirect to login page
redirect('login.php');
?>
