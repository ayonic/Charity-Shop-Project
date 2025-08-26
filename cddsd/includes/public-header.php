<?php
/**
 * Public Header Include
 * 
 * This file contains common header elements for the public-facing pages.
 */

// Include initialization file (which already starts the session and includes database connection)
require_once __DIR__ . '/../config/init.php';

// Check if user is logged in and get user data
$is_logged_in = is_logged_in();
$current_user = $is_logged_in ? get_logged_in_user() : null;

// Set user information only if properly logged in
if ($is_logged_in && $current_user) {
    $user_role = $current_user['role'] ?? '';
    $user_name = trim(($current_user['first_name'] ?? '') . ' ' . ($current_user['last_name'] ?? ''));
} else {
    $user_role = '';
    $user_name = '';
    $is_logged_in = false; // Ensure logged out state if user data is invalid
}

// Get cart count from session
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Get site settings
$site_name = "Charity Shop";
$site_tagline = "Supporting Our Community";
$connection = db_connect();

$settings_query = "SELECT * FROM settings WHERE name IN ('site_name', 'site_tagline')";
$stmt = $connection->query($settings_query);

if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['name'] == 'site_name' && !empty($row['value'])) {
            $site_name = $row['value'];
        } elseif ($row['name'] == 'site_tagline' && !empty($row['value'])) {
            $site_tagline = $row['value'];
        }
    }
}

// Determine current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/charity-shop/assets/images/favicon.svg">
    <title><?php echo htmlspecialchars($site_name); ?> - <?php echo htmlspecialchars($site_tagline); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        'primary-dark': '#4338CA',
                    }
                }
            }
        }
    </script>
    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            const body = document.body;
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                body.style.overflow = 'hidden';
            } else {
                mobileMenu.classList.add('hidden');
                body.style.overflow = '';
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header/Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($site_name); ?></h1>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($site_tagline); ?></p>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <button type="button" class="md:hidden text-gray-600 hover:text-primary" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-6">
                    <a href="shop.php" class="nav-link <?php echo $current_page == 'shop.php' || $current_page == 'index.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Shop</a>
                    <a href="donate.php" class="nav-link <?php echo $current_page == 'donate.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Donate</a>
                    <a href="volunteer.php" class="nav-link <?php echo $current_page == 'volunteer.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Volunteer</a>
                    <a href="about.php" class="nav-link <?php echo $current_page == 'about.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">About Us</a>
                    <a href="contact.php" class="nav-link <?php echo $current_page == 'contact.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Contact</a>
                    <?php if (!$is_logged_in): ?>
                        <a href="signup.php" class="nav-link inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200 mr-2">Sign Up</a>
                        <a href="login.php" class="nav-link inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Login
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Mobile Navigation -->
                <div id="mobile-menu" class="hidden fixed inset-0 z-50 md:hidden">
                    <div class="fixed inset-0 bg-gray-600 bg-opacity-75" onclick="toggleMobileMenu()"></div>
                    <nav class="relative bg-white h-full w-64 max-w-sm py-4 px-6 flex flex-col overflow-y-auto">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-bold text-gray-900">Menu</h2>
                            <button type="button" class="text-gray-600" onclick="toggleMobileMenu()">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <a href="shop.php" class="block py-2.5 text-base font-medium <?php echo $current_page == 'shop.php' || $current_page == 'index.php' ? 'text-primary' : 'text-gray-900 hover:text-primary'; ?>">Shop</a>
                            <a href="donate.php" class="block py-2.5 text-base font-medium <?php echo $current_page == 'donate.php' ? 'text-primary' : 'text-gray-900 hover:text-primary'; ?>">Donate</a>
                            <a href="volunteer.php" class="block py-2.5 text-base font-medium <?php echo $current_page == 'volunteer.php' ? 'text-primary' : 'text-gray-900 hover:text-primary'; ?>">Volunteer</a>
                            <a href="about.php" class="block py-2.5 text-base font-medium <?php echo $current_page == 'about.php' ? 'text-primary' : 'text-gray-900 hover:text-primary'; ?>">About Us</a>
                            <a href="contact.php" class="block py-2.5 text-base font-medium <?php echo $current_page == 'contact.php' ? 'text-primary' : 'text-gray-900 hover:text-primary'; ?>">Contact</a>
                            <?php if (!$is_logged_in): ?>
                                <a href="signup.php" class="flex items-center px-4 py-2 text-base font-medium text-white bg-primary hover:bg-primary-dark rounded-md shadow-sm transition-colors duration-200 mb-2">
                                    Sign Up
                                </a>
                                <a href="login.php" class="flex items-center px-4 py-2 text-base font-medium text-white bg-primary hover:bg-primary-dark rounded-md shadow-sm transition-colors duration-200">
                                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    Login
                                </a>
                            <?php endif; ?>
                        </div>
                    </nav>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="relative group">
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-primary rounded-full">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <?php if ($is_logged_in): ?>
                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="flex items-center text-gray-500 hover:text-gray-900" :class="{ 'text-gray-900': open }">
                                My Account
                                <span class="hidden sm:inline text-sm"><?php echo htmlspecialchars($user_name); ?></span>
                                <i class="ri-arrow-down-s-line ml-1"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50" x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95">
                                <?php if ($user_role == 'admin'): ?>
                                    <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Dashboard</a>
                                <?php elseif ($user_role == 'manager'): ?>
                                    <a href="manager/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manager Dashboard</a>
                                <?php elseif ($user_role == 'volunteer'): ?>
                                    <a href="volunteer/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Volunteer Dashboard</a>
                                <?php endif; ?>
                                <a href="account.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Account</a>
                                <a href="orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                                <a href="donations.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Donations</a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-600 hover:text-primary">
                            <i class="ri-user-line text-xl"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($is_logged_in): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-600 hover:text-gray-900 relative">
                            <i class="ri-notification-3-line text-xl"></i>
                            <?php
                            // Get unread notifications count and recent notifications
                            $notifications_count = 0;
                            $recent_notifications = [];
                            
                            if ($current_user && isset($current_user['id'])) {
                                $result = db_fetch_row("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$current_user['id']]);
                                $notifications_count = $result ? $result['count'] : 0;
                                
                                $stmt = $connection->prepare("SELECT id, title, type, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                                $stmt->execute([$current_user['id']]);
                                $recent_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            }
                            
                            if ($notifications_count > 0):
                            ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                                <?php echo $notifications_count; ?>
                            </span>
                            <?php endif; ?>
                        </button>

                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                    <?php if ($notifications_count > 0): ?>
                                    <span class="text-xs font-medium text-blue-600"><?php echo $notifications_count; ?> new</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (empty($recent_notifications)): ?>
                            <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                No notifications yet
                            </div>
                            <?php else: ?>
                                <?php foreach ($recent_notifications as $notification): ?>
                                <a href="notifications.php" class="block px-4 py-3 hover:bg-gray-50 transition">
                                    <div class="flex items-start">
                                        <?php
                                        $icon_class = 'text-lg ';
                                        switch ($notification['type']) {
                                            case 'success':
                                                $icon_class .= 'text-green-500 ri-checkbox-circle-line';
                                                break;
                                            case 'warning':
                                                $icon_class .= 'text-yellow-500 ri-error-warning-line';
                                                break;
                                            case 'error':
                                                $icon_class .= 'text-red-500 ri-close-circle-line';
                                                break;
                                            default:
                                                $icon_class .= 'text-blue-500 ri-information-line';
                                        }
                                        ?>
                                        <i class="<?php echo $icon_class; ?> mt-0.5"></i>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm text-gray-900"><?php echo htmlspecialchars($notification['title']); ?></p>
                                            <p class="text-xs text-gray-500 mt-0.5"><?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?></p>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                                
                                <div class="px-4 py-2 border-t border-gray-100">
                                    <a href="notifications.php" class="block text-sm text-center font-medium text-blue-600 hover:text-blue-700">View all notifications</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <a href="cart.php" class="text-gray-600 hover:text-primary relative">
                        <i class="ri-shopping-bag-line text-xl"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <button class="md:hidden text-gray-600" id="mobileMenuButton">
                        <i class="ri-menu-line text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu (Hidden by default) -->
            <div class="md:hidden hidden" id="mobileMenu">
                <nav class="flex flex-col space-y-3 mt-4 pb-4">
                    <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Home</a>
                    <a href="shop.php" class="<?php echo $current_page == 'shop.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Shop</a>
                    <a href="donate.php" class="<?php echo $current_page == 'donate.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Donate</a>
                    <a href="volunteer.php" class="<?php echo $current_page == 'volunteer.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Volunteer</a>
                    <a href="about.php" class="<?php echo $current_page == 'about.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">About Us</a>
                    <a href="contact.php" class="<?php echo $current_page == 'contact.php' ? 'text-primary font-medium' : 'text-gray-600 hover:text-primary'; ?>">Contact</a>
                    
                    <?php if ($is_logged_in): ?>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <p class="text-sm font-medium text-gray-900 mb-2">Account</p>
                            <?php if ($user_role == 'admin'): ?>
                                <a href="admin/dashboard.php" class="block py-1 text-sm text-gray-600 hover:text-primary">Admin Dashboard</a>
                            <?php elseif ($user_role == 'manager'): ?>
                                <a href="manager/dashboard.php" class="block py-1 text-sm text-gray-600 hover:text-primary">Manager Dashboard</a>
                            <?php elseif ($user_role == 'volunteer'): ?>
                                <a href="volunteer/dashboard.php" class="block py-1 text-sm text-gray-600 hover:text-primary">Volunteer Dashboard</a>
                            <?php endif; ?>
                            <a href="account.php" class="block py-1 text-sm text-gray-600 hover:text-primary">My Account</a>
                            <a href="orders.php" class="block py-1 text-sm text-gray-600 hover:text-primary">My Orders</a>
                            <a href="donations.php" class="block py-1 text-sm text-gray-600 hover:text-primary">My Donations</a>
                            <a href="logout.php" class="block py-1 text-sm text-red-600 hover:text-red-800">Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <a href="login.php" class="block py-1 text-sm text-primary hover:text-indigo-800">Login</a>
                            <a href="signup.php" class="block py-1 text-sm text-primary hover:text-indigo-800">Sign Up</a>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
