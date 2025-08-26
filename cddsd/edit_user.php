<?php
/**
 * Edit User
 * 
 * This page allows administrators to edit existing users in the system.
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

// Get user data
$user = db_fetch_row("SELECT * FROM users WHERE id = ?", [$user_id]);
if (!$user) {
    set_flash_message('error', 'User not found.');
    redirect('users.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitize_input($_POST['first_name']),
        'last_name' => sanitize_input($_POST['last_name']),
        'email' => sanitize_input($_POST['email']),
        'role' => sanitize_input($_POST['role']),
        'phone' => sanitize_input($_POST['phone']),
        'address' => sanitize_input($_POST['address']),
        'city' => sanitize_input($_POST['city']),
        'state' => sanitize_input($_POST['state']),
        'postal_code' => sanitize_input($_POST['postal_code']),
        'notes' => sanitize_input($_POST['notes'])
    ];
    
    // Only update password if a new one is provided
    if (!empty($_POST['password'])) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    
    if (db_update('users', $data, ['id' => $user_id])) {
        set_flash_message('success', 'User updated successfully!');
        log_activity($_SESSION['user_id'], 'user', 'Updated user: ' . $data['first_name'] . ' ' . $data['last_name']);
        redirect('users.php');
    } else {
        set_flash_message('error', 'Failed to update user.');
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Charity Shop</title>
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
                        <h1 class="text-2xl font-semibold text-gray-900">Edit User</h1>
                        <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md flex items-center">
                            <i class="ri-arrow-left-line mr-2"></i>
                            Back to Users
                        </a>
                    </div>
                    
                    <!-- Edit User Form -->
                    <div class="mt-6">
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
                            <form method="POST" class="space-y-6">
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                        <input type="text" name="first_name" id="first_name" required 
                                               value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                        <input type="text" name="last_name" id="last_name" required 
                                               value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email" required 
                                               value="<?php echo htmlspecialchars($user['email']); ?>"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                                        <input type="password" name="password" id="password" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                        <select name="role" id="role" required 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="volunteer" <?php echo (isset($user['role']) && $user['role'] === 'volunteer') ? 'selected' : ''; ?>>Volunteer</option>
                                            <option value="donor" <?php echo (isset($user['role']) && $user['role'] === 'donor') ? 'selected' : ''; ?>>Donor</option>
                                            <option value="customer" <?php echo (isset($user['role']) && $user['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                            <option value="manager" <?php echo (isset($user['role']) && $user['role'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                                            <option value="cashier" <?php echo (isset($user['role']) && $user['role'] === 'cashier') ? 'selected' : ''; ?>>Cashier</option>
                                            <option value="moderator" <?php echo (isset($user['role']) && $user['role'] === 'moderator') ? 'selected' : ''; ?>>Moderator</option>
                                            <option value="admin" <?php echo (isset($user['role']) && $user['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="tel" name="phone" id="phone" 
                                               value="<?php echo htmlspecialchars(isset($user['phone']) ? $user['phone'] : ''); ?>"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <input type="text" name="address" id="address" 
                                           value="<?php echo htmlspecialchars(isset($user['address']) ? $user['address'] : ''); ?>"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                        <input type="text" name="city" id="city" 
                                               value="<?php echo htmlspecialchars(isset($user['city']) ? $user['city'] : ''); ?>"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                        <input type="text" name="state" id="state" 
                                               value="<?php echo htmlspecialchars(isset($user['state']) ? $user['state'] : ''); ?>"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                                        <input type="text" name="postal_code" id="postal_code" 
                                               value="<?php echo htmlspecialchars(isset($user['postal_code']) ? $user['postal_code'] : ''); ?>"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo htmlspecialchars(isset($user['notes']) ? $user['notes'] : ''); ?></textarea>
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <a href="users.php" 
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Update User
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>