<?php
/**
 * Session Manager
 * 
 * This file provides centralized session management functions to ensure consistent
 * user role handling across all dashboard pages and prevent redirect loops.
 */

require_once __DIR__ . '/../config/init.php';

/**
 * Ensures the user's role is properly set in the session
 * This prevents redirect loops caused by inconsistent role information
 */
function ensure_user_role() {
    if (!is_logged_in()) {
        return false;
    }
    
    // If role is already set in session and we're not forcing a refresh, return it
    if (isset($_SESSION['role_name']) && !isset($_GET['refresh_role'])) {
        return $_SESSION['role_name'];
    }
    
    // Get fresh role data from database
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare('SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Update session with fresh role data
        $_SESSION['role_name'] = $user['role_name'];
        return $user['role_name'];
    }
    
    return false;
}

/**
 * Validates that the user has the required role to access a page
 * If not, redirects to the appropriate page
 * 
 * @param string $required_role The role required to access the current page
 * @param string $redirect_url The URL to redirect to if validation fails
 * @return bool True if user has the required role, false otherwise
 */
function validate_user_role($required_role, $redirect_url = 'login.php') {
    if (!is_logged_in()) {
        redirect($redirect_url);
        return false;
    }
    
    // Ensure role is up-to-date
    $current_role = ensure_user_role();
    
    if ($current_role === false) {
        clear_role_session();
        session_destroy();
        redirect($redirect_url);
        return false;
    }
    
    // Admin can view any page if they're using the role switcher
    if ($current_role === 'admin' && isset($_SESSION['view_as_role']) && $_SESSION['view_as_role'] === $required_role) {
        return true;
    }
    
    // Check if user has the required role
    $allowed = false;
    if (is_array($required_role)) {
        if (in_array($current_role, $required_role)) $allowed = true;
    } else {
        if ($current_role === $required_role) $allowed = true;
    }
    if (!$allowed) {
        // Redirect to appropriate dashboard based on actual role
        switch ($current_role) {
            case 'admin':
                redirect('/dashboard.php');
                break;
            case 'customer':
                redirect('/customer/dashboard.php');
                break;
            case 'donor':
                redirect('/donor/dashboard.php');
                break;
            case 'manager':
                redirect('/manager/dashboard.php');
                break;
            case 'volunteer':
                redirect('/volunteer/dashboard.php');
                break;
            case 'moderator':
                redirect('/dashboard.php');
                break;
            default:
                redirect($redirect_url);
                break;
        }
        return false;
    }
    
    return true;
}

/**
 * Clears all role-related session variables
 * Useful for logout or troubleshooting redirect loops
 */
function clear_role_session() {
    unset($_SESSION['role_name']);
    unset($_SESSION['view_as_role']);
    unset($_SESSION['role_id']);
}