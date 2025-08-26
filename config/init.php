<?php
// Buffer output to prevent header modification issues
ob_start();

// Start session
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Include database configuration and establish connection
require_once BASE_PATH . '/config/database.php';
$pdo = db_connect();

// Include functions
require_once INCLUDES_PATH . '/functions.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

// Check user permission
function has_permission($required_permission) {
    if (!is_logged_in()) {
        return false;
    }
    
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    // Get user's role
    $stmt = $pdo->prepare('SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return false;
    }
    
    // Define role hierarchy and permissions
    $role_permissions = [
        'admin' => ['admin', 'manager', 'volunteer', 'user'],
        'manager' => ['manager', 'volunteer', 'user'],
        'volunteer' => ['volunteer', 'user'],
        'user' => ['user']
    ];
    
    // Get allowed permissions for the user's role
    $allowed_permissions = isset($role_permissions[$user['role_name']]) ? $role_permissions[$user['role_name']] : [];
    
    // Check if the required permission is in the allowed permissions list
    return in_array($required_permission, $allowed_permissions);
}

// Require admin role
function require_admin() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
    
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare('SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $user['role_name'] !== 'admin') {
        redirect('index.php');
    }
}

// Get user role
function get_user_role() {
    if (!is_logged_in()) {
        return 'guest';
    }
    
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare('SELECT r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Allow admin to view as other roles while maintaining admin access
    if ($user && $user['role_name'] === 'admin' && isset($_SESSION['view_as_role'])) {
        return $_SESSION['view_as_role'];
    }
    
    return $user ? $user['role_name'] : 'guest';
}

// Redirect function
function redirect($location) {
    if (!headers_sent()) {
        header("Location: {$location}");
        exit;
    } else {
        // If headers are already sent, use JavaScript or meta refresh
        echo "<script>window.location.href='{$location}';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url={$location}'></noscript>";
        exit;
    }
}

// Get current page name
function current_page() {
    return basename($_SERVER['PHP_SELF']);
}

// Format currency
function format_currency($amount) {
    return '£' . number_format($amount, 2);
}

// Format date
function format_date($date) {
    return date('M d, Y', strtotime($date));
}

// Format time
function format_time($time) {
    return date('H:i', strtotime($time));
}

// Format datetime
function format_datetime($datetime) {
    return date('M d, Y H:i', strtotime($datetime));
}

// Check if user has specific role
function has_role($required_role) {
    if (!is_logged_in()) {
        return false;
    }
    
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare('
        SELECT 1 FROM users u
        JOIN roles r ON r.id = u.role_id
        WHERE u.id = ? AND r.name = ?
    ');
    $stmt->execute([$user_id, $required_role]);
    
    return (bool)$stmt->fetch();
}

// Get user data by ID
function get_user($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT u.*, r.name as role
        FROM users u
        JOIN roles r ON r.id = u.role_id
        WHERE u.id = ?
    ');
    $stmt->execute([$user_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get current user data
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT u.*, r.name as role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Set flash message
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get flash message
function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    
    return null;
}

// Display flash message
function display_flash_message() {
    $flash = get_flash_message();
    
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        
        $icon_map = [
            'success' => 'ri-check-line',
            'error' => 'ri-error-warning-line',
            'warning' => 'ri-alert-line',
            'info' => 'ri-information-line'
        ];
        
        $icon = $icon_map[$type] ?? 'ri-information-line';
        
        echo "<div class='flash-message {$type} mb-6 p-4 rounded-xl border flex items-start space-x-3 animate-slide-down' id='flash-message'>";
        echo "<i class='{$icon} text-lg mt-0.5'></i>";
        echo "<div class='flex-1'>";
        echo "<p class='font-medium'>" . ucfirst($type) . "</p>";
        echo "<p class='text-sm opacity-90'>{$message}</p>";
        echo "</div>";
        
        // Add JavaScript for auto-dismiss after 5 seconds
        if ($type === 'success' && strpos($message, 'Welcome back') !== false) {
            echo "<script>
                setTimeout(function() {
                    const flashMessage = document.getElementById('flash-message');
                    if (flashMessage) {
                        flashMessage.style.transition = 'opacity 0.5s ease-out';
                        flashMessage.style.opacity = '0';
                        setTimeout(function() {
                            flashMessage.remove();
                        }, 500);
                    }
                }, 5000);
            </script>";
        }
        echo "<button onclick='this.parentElement.remove()' class='text-current opacity-70 hover:opacity-100'>";
        echo "<i class='ri-close-line'></i>";
        echo "</button>";
        echo "</div>";
    }
}

// Sanitize input
function sanitize_input($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize_input($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
    }
    
    return $input;
}
