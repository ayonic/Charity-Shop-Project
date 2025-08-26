<?php
/**
 * Dashboard Header Template
 * 
 * This file contains the common header elements for all role-specific dashboards.
 * It includes role validation, page title generation, and common UI elements.
 */

require_once __DIR__ . '/../config/init.php';

// Require login for all dashboard pages
require_login();

// Get current user and role
$current_user = get_logged_in_user();
$user_role = isset($current_user['role']) ? $current_user['role'] : 'Guest';
$first_name = isset($current_user['first_name']) ? $current_user['first_name'] : '';
$last_name = isset($current_user['last_name']) ? $current_user['last_name'] : '';

// Generate page title based on role
$page_title = ucfirst($user_role) . ' Dashboard';

// Common header HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/charity-shop/assets/images/favicon.svg">
    <title><?php echo $page_title; ?> - Charity Shop Management System</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <script>
        function toggleDropdown(dropdownId, buttonId) {
            const dropdown = document.getElementById(dropdownId);
            const button = document.getElementById(buttonId);
            const allDropdowns = document.querySelectorAll('[id$="Dropdown"]');
            
            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== dropdownId) {
                    d.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
            
            // Close dropdown when clicking outside
            const closeDropdown = (e) => {
                if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                    dropdown.classList.add('hidden');
                    document.removeEventListener('click', closeDropdown);
                }
            };
            
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    document.addEventListener('click', closeDropdown);
                }, 0);
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex overflow-hidden">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white shadow-sm z-10">
                <div class="px-4 py-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 mb-1"><?php echo $page_title; ?></h1>
                            <p class="text-gray-600 text-sm">Welcome back, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>!</p>
                        </div>
                        <?php
                        // Initialize notifications count
                        $notification_result = db_fetch_row("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [isset($current_user['id']) ? $current_user['id'] : 0]);
$notifications_count = ($notification_result && isset($notification_result['count'])) ? $notification_result['count'] : 0;
                        ?>
                        <div class="flex items-center space-x-4">
                            <!-- Notifications Button -->
                            <button id="notificationsButton" class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none" onclick="toggleDropdown('notificationsDropdown', 'notificationsButton')">
                                <i class="ri-notification-3-line text-xl"></i>
                                <?php if ($notifications_count > 0): ?>
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"><?php echo $notifications_count; ?></span>
                                <?php endif; ?>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div id="notificationsDropdown" class="hidden absolute right-16 mt-12 w-80 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50 max-h-96 overflow-y-auto">
                                <?php
                                $recent_notifications = db_fetch_all("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$current_user['id']]);
                                if (!empty($recent_notifications)) {
                                    foreach ($recent_notifications as $notification) {
                                        $type = isset($notification['type']) ? $notification['type'] : '';
                                        $message = isset($notification['message']) ? $notification['message'] : '';
                                        $created_at = isset($notification['created_at']) ? $notification['created_at'] : '';
                                        ?>
                                        <a href="notifications.php" class="block px-4 py-3 hover:bg-gray-50 transition">
                                            <div class="flex items-start">
                                                <i class="<?php echo get_notification_icon_class($type); ?> mr-3 mt-0.5"></i>
                                                <div class="flex-1">
                                                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($message); ?></p>
                                                    <p class="text-xs text-gray-500 mt-1"><?php echo format_datetime($created_at); ?></p>
                                                </div>
                                            </div>
                                        </a>
                                        <?php
                                    }
                                    ?>
                                    <a href="notifications.php" class="block text-center text-sm text-primary hover:text-primary-dark py-2 border-t">View all notifications</a>
                                <?php } else { ?>
                                    <div class="text-center py-4 text-gray-600">No new notifications</div>
                                <?php } ?>
                            </div>

                            <!-- Profile Button -->
                            <button id="profileButton" class="flex items-center space-x-2 p-2 text-gray-600 hover:text-gray-900 focus:outline-none" onclick="toggleDropdown('profileDropdown', 'profileButton')">
                                <img class="h-8 w-8 rounded-full object-cover" src="https://ui-avatars.com/api/?name=<?php echo urlencode($first_name . '+' . $last_name); ?>" alt="Profile">
                                <span class="text-sm font-medium"><?php echo htmlspecialchars($first_name); ?></span>
                                <i class="ri-arrow-down-s-line"></i>
                            </button>

                            <!-- Profile Dropdown -->
                            <div id="profileDropdown" class="hidden absolute right-4 mt-12 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                                <?php if ($user_role === 'admin'): ?>
                                <a href="payment_settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Payment Settings</a>
                                <?php endif; ?>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                <div class="border-t border-gray-100"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <!-- Removed: <div class="flex-1 overflow-auto"> -->
            <!-- Main dashboard content goes here -->
            <!-- Removed: </div> -->
            <script>
            function toggleDropdown(dropdownId, buttonId) {
                const dropdown = document.getElementById(dropdownId);
                
                // Toggle current dropdown
                dropdown.classList.toggle('hidden');
                
                // Close dropdown when clicking outside
                const closeDropdown = (e) => {
                    if (!document.getElementById(buttonId).contains(e.target) && 
                        !dropdown.contains(e.target)) {
                        dropdown.classList.add('hidden');
                        document.removeEventListener('click', closeDropdown);
                    }
                };
                
                if (!dropdown.classList.contains('hidden')) {
                    setTimeout(() => {
                        document.addEventListener('click', closeDropdown);
                    }, 0);
                }
            }
            </script>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">