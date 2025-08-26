<?php
/**
 * User Management
 * 
 * This page allows administrators to manage users, including viewing, adding, editing, and deleting users.
 */

require_once 'config/init.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'You must be an administrator to access this page.');
    redirect('login.php');
    exit;
}

// Get all users
$users = db_fetch_all("SELECT * FROM users ORDER BY created_at DESC");

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Charity Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex overflow-hidden bg-gray-100">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="flex-1 overflow-auto focus:outline-none" tabindex="0">
        <main class="flex-1 relative overflow-y-auto focus:outline-none">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">User Management</h1>
                        <a href="add_user.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                            <i class="ri-user-add-line mr-2"></i>
                            Add New User
                        </a>
                    </div>
                    
                    <!-- User List -->
                    <div class="mt-6">
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul role="list" class="divide-y divide-gray-200">
                                <?php foreach ($users as $user): ?>
                                <li>
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
                                                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-blue-600">
                                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <?php
    if ((!isset($user['role']) || $user['role'] === null || $user['role'] === '') && $user['id'] == 1) {
        echo 'Admin';
    } else {
        echo isset($user['role']) && $user['role'] !== null ? ucfirst($user['role']) : 'Unknown';
    }
?>
                                                </span>
                                                <div class="flex space-x-2">
                                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?');">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <!-- Give Roles Section -->
                    <div id="give-roles" class="mt-10">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="ri-user-settings-line mr-2"></i>Give Roles
                        </h2>
                        <form action="process_give_roles.php" method="POST" class="bg-white p-6 rounded-md shadow-md">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="user_id" class="block text-sm font-medium text-gray-700">Select User</label>
                                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Select Role</label>
                                    <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="admin">Admin</option>
                                        <option value="manager">Manager</option>
                                        <option value="cashier">Cashier</option>
                                        <option value="moderator">Moderator</option>
                                        <option value="volunteer">Volunteer</option>
                                        <option value="donor">Donor</option>
                                        <option value="customer">Customer</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center">
                                        <i class="ri-user-settings-line mr-2"></i>Assign Role
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
