<?php
require_once __DIR__ . '/../config/init.php';

// Ensure user is logged in and has customer role
if (!isset($_SESSION['user_id'])) {
    set_flash_message('error', 'Please log in to access this area.');
    redirect('login.php');
}

if (!has_role('customer')) {
    set_flash_message('error', 'Unauthorized access.');
    redirect('login.php');
}

// Get current user data
$user_query = "SELECT * FROM users WHERE id = ?";
$current_user = db_fetch_one($user_query, [$_SESSION['user_id']]);

// Get unread notifications count
$notifications_query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$notifications_count = db_fetch_one($notifications_query, [$_SESSION['user_id']])['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/charity-shop/assets/images/favicon.svg">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Charity Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <a href="customer-dashboard.php" class="flex items-center">
                        <span class="text-xl font-semibold text-blue-600">Customer Portal</span>
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="my-orders.php" class="flex items-center text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="ri-shopping-bag-line mr-2"></i>My Orders
                    </a>
                    <a href="wishlist.php" class="flex items-center text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="ri-heart-line mr-2"></i>Wishlist
                    </a>
                    <a href="profile.php" class="flex items-center text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="ri-user-line mr-2"></i>Profile
                    </a>
                    <a href="notifications.php" class="flex items-center text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium relative">
                        <i class="ri-notification-line mr-2"></i>Notifications
                        <?php if ($notifications_count > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?php echo $notifications_count; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" class="flex items-center text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="ri-logout-box-line mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">