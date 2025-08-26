<?php
/**
 * Settings Page
 * 
 * This page handles system settings and configuration.
 */

// Include initialization file
require_once 'config/init.php';

// Require login
require_login();

// Get user role
$user_role = get_user_role();
if (!$user_role) {
    redirect('login.php');
}

// Set view permissions based on role
$can_edit_system_settings = has_permission('admin');
$can_manage_categories = has_permission('manager');
$can_view_reports = has_permission('volunteer');

// Store role-based permissions in session for view control
$_SESSION['user_permissions'] = [
    'can_edit_system_settings' => $can_edit_system_settings,
    'can_manage_categories' => $can_manage_categories,
    'can_view_reports' => $can_view_reports
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validate_csrf_or_die();
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                // All users can update their profile
                $user_id = $_SESSION['user_id'];
                $data = [
                    'first_name' => sanitize_input($_POST['first_name']),
                    'last_name' => sanitize_input($_POST['last_name']),
                    'email' => sanitize_input($_POST['email']),
                    'phone' => sanitize_input($_POST['phone']),
                    'address' => sanitize_input($_POST['address']),
                    'city' => sanitize_input($_POST['city']),
                    'state' => sanitize_input($_POST['state']),
                    'postal_code' => sanitize_input($_POST['postal_code'])
                ];
                
                if (db_update('users', $data, "id = {$user_id}")) {
                    set_flash_message('success', 'Profile updated successfully!');
                } else {
                    set_flash_message('error', 'Failed to update profile.');
                }
                break;
                
            case 'change_password':
                // All users can change their password
                $user_id = $_SESSION['user_id'];
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Get current user
                $user = get_user($user_id);
                
                if (!password_verify($current_password, $user['password'])) {
                    set_flash_message('error', 'Current password is incorrect.');
                } elseif ($new_password !== $confirm_password) {
                    set_flash_message('error', 'New passwords do not match.');
                } elseif (strlen($new_password) < 6) {
                    set_flash_message('error', 'Password must be at least 6 characters long.');
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    if (db_update('users', ['password' => $hashed_password], "id = {$user_id}")) {
                        set_flash_message('success', 'Password changed successfully!');
                    } else {
                        set_flash_message('error', 'Failed to change password.');
                    }
                }
                break;
                
            case 'add_category':
                // Only admin and manager can add categories
                if (!$can_edit_system_settings && !$can_manage_categories) {
                    set_flash_message('error', 'You do not have permission to add categories.');
                    break;
                }
                
                $data = [
                    'name' => sanitize_input($_POST['name']),
                    'code' => strtoupper(sanitize_input($_POST['code'])),
                    'description' => sanitize_input($_POST['description'])
                ];
                
                if (db_insert('categories', $data)) {
                    set_flash_message('success', 'Category added successfully!');
                } else {
                    set_flash_message('error', 'Failed to add category.');
                }
                break;
                
            case 'update_category':
                // Only admin and manager can update categories
                if (!$can_edit_system_settings && !$can_manage_categories) {
                    set_flash_message('error', 'You do not have permission to update categories.');
                    break;
                }
                $category_id = (int) $_POST['category_id'];
                $data = [
                    'name' => sanitize_input($_POST['name']),
                    'code' => strtoupper(sanitize_input($_POST['code'])),
                    'description' => sanitize_input($_POST['description'])
                ];
                
                if (db_update('categories', $data, "id = {$category_id}")) {
                    set_flash_message('success', 'Category updated successfully!');
                } else {
                    set_flash_message('error', 'Failed to update category.');
                }
                break;
                
            case 'delete_category':
                $category_id = (int) $_POST['category_id'];
                
                // Check if category is in use
                $in_use = db_fetch_row("SELECT COUNT(*) as count FROM inventory WHERE category_id = {$category_id}");
                
                if ($in_use['count'] > 0) {
                    set_flash_message('error', 'Cannot delete category that is in use by inventory items.');
                } else {
                    if (db_delete('categories', "id = {$category_id}")) {
                        set_flash_message('success', 'Category deleted successfully!');
                    } else {
                        set_flash_message('error', 'Failed to delete category.');
                    }
                }
                break;
        }
        
        redirect('settings.php');
    }
}

// Get current user data
$current_user = get_logged_in_user();

// Get all categories
$categories = get_categories();

// Get system statistics
$system_stats = [
    'total_users' => db_count('users'),
    'total_inventory' => db_count('inventory'),
    'total_sales' => db_count('sales'),
    'total_donations' => db_count('donations'),
    'database_size' => 'N/A' // Would require additional query to get actual size
];

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
    <p class="mt-1 text-sm text-gray-600">Manage your account settings and system configuration</p>
</div>

<!-- Settings Tabs -->
<div class="bg-white shadow rounded-lg">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <button onclick="showTab('profile')" id="profile-tab" class="border-transparent text-primary border-b-2 py-4 px-1 text-sm font-medium">
                Profile
            </button>
            <button onclick="showTab('security')" id="security-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 text-sm font-medium">
                Security
            </button>
            <?php if ($can_manage_categories): ?>
            <button onclick="showTab('categories')" id="categories-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 text-sm font-medium">
                Categories
            </button>
            <?php endif; ?>
            <?php if ($can_edit_system_settings): ?>
            <button onclick="showTab('system')" id="system-tab" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 text-sm font-medium">
                System
            </button>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Profile Tab -->
    <div id="profile-content" class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>
        <form method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update_profile">
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" required 
                           value="<?php echo htmlspecialchars($current_user['first_name']); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required 
                           value="<?php echo htmlspecialchars($current_user['last_name']); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required 
                           value="<?php echo htmlspecialchars($current_user['email']); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" name="phone" id="phone" 
                           value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
            </div>
            
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="address" id="address" 
                       value="<?php echo htmlspecialchars($current_user['address'] ?? ''); ?>"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" id="city" 
                           value="<?php echo htmlspecialchars($current_user['city'] ?? ''); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                    <input type="text" name="state" id="state" 
                           value="<?php echo htmlspecialchars($current_user['state'] ?? ''); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                    <input type="text" name="postal_code" id="postal_code" 
                           value="<?php echo htmlspecialchars($current_user['postal_code'] ?? ''); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Update Profile
                </button>
            </div>
        </form>
    </div>

    <!-- Security Tab -->
    <div id="security-content" class="p-6 hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
        <form method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="change_password">
            
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" name="current_password" id="current_password" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>
            
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="new_password" id="new_password" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Change Password
                </button>
            </div>
        </form>
    </div>

    <!-- Categories Tab -->
    <?php if ($can_manage_categories): ?>
    <div id="categories-content" class="p-6 hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Manage Categories</h3>
            <button data-modal-trigger data-modal-target="addCategoryModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="ri-add-line -ml-1 mr-2"></i>
                Add Category
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($category['code']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)" class="text-primary hover:text-indigo-900 mr-3">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')" class="text-red-600 hover:text-red-900">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Tab -->
    <?php if ($can_edit_system_settings): ?>
    <div id="system-content" class="p-6 hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">System Information</h3>
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Database Statistics</h4>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Total Users:</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo $system_stats['total_users']; ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Inventory Items:</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo $system_stats['total_inventory']; ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Total Sales:</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo $system_stats['total_sales']; ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Total Donations:</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo $system_stats['total_donations']; ?></dd>
                    </div>
                </dl>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-3">System Actions</h4>
                <div class="space-y-3">
                    <button onclick="backupDatabase()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="ri-database-2-line -ml-1 mr-2"></i>
                        Backup Database
                    </button>
                    <button onclick="clearCache()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="ri-refresh-line -ml-1 mr-2"></i>
                        Clear Cache
                    </button>
                    <button onclick="exportData()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="ri-download-line -ml-1 mr-2"></i>
                        Export Data
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-alert-line text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">System Maintenance</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Regular maintenance tasks should be performed to keep the system running smoothly. Consider scheduling regular database backups and clearing old log files.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Category</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add_category">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="add_cat_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="name" id="add_cat_name" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_cat_code" class="block text-sm font-medium text-gray-700">Category Code</label>
                        <input type="text" name="code" id="add_cat_code" required maxlength="10" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                               placeholder="e.g., CLO, BOO, FUR">
                    </div>
                </div>
                
                <div>
                    <label for="add_cat_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="add_cat_description" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Category</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="category_id" id="edit_category_id">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_cat_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="name" id="edit_cat_name" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="edit_cat_code" class="block text-sm font-medium text-gray-700">Category Code</label>
                        <input type="text" name="code" id="edit_cat_code" required maxlength="10" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="edit_cat_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="edit_cat_description" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div id="deleteCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="ri-delete-bin-line text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Category</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete "<span id="deleteCategoryName"></span>"? This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form method="POST" id="deleteCategoryForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" id="deleteCategoryId">
                    <div class="flex justify-center space-x-3">
                        <button type="button" data-modal-close class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function closeModal(element) {
    var modal = element.closest('.fixed');
    if (modal) modal.classList.add('hidden');
}

function initializePage() {
    showTab('profile');
    
    var closeButtons = document.querySelectorAll('[data-modal-close]');
    for (var i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            closeModal(this);
        };
    }
}

function showTab(tabName) {
    // Hide all tab contents
    var contents = ['profile-content', 'security-content'];
    <?php if ($can_manage_categories): ?>
    contents.push('categories-content');
    <?php endif; ?>
    <?php if ($can_edit_system_settings): ?>
    contents.push('system-content');
    <?php endif; ?>
    
    for (var i = 0; i < contents.length; i++) {
        var element = document.getElementById(contents[i]);
        if (element) element.classList.add('hidden');
    }
    
    // Remove active class from all tabs
    var tabs = ['profile-tab', 'security-tab'];
    <?php if ($can_manage_categories): ?>
    tabs.push('categories-tab');
    <?php endif; ?>
    <?php if ($can_edit_system_settings): ?>
    tabs.push('system-tab');
    <?php endif; ?>
    
    for (var i = 0; i < tabs.length; i++) {
        var element = document.getElementById(tabs[i]);
        if (element) {
            element.classList.remove('text-primary', 'border-b-2');
            element.classList.add('text-gray-500', 'border-transparent');
        }
    }
    
    // Show selected tab content
    var contentElement = document.getElementById(tabName + '-content');
    if (contentElement) contentElement.classList.remove('hidden');
    
    // Add active class to selected tab
    var activeTab = document.getElementById(tabName + '-tab');
    if (activeTab) {
        activeTab.classList.remove('text-gray-500', 'border-transparent');
        activeTab.classList.add('text-primary', 'border-b-2');
    }
}

function editCategory(category) {
    if (!category) return;
    
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_cat_name').value = category.name;
    document.getElementById('edit_cat_code').value = category.code;
    document.getElementById('edit_cat_description').value = category.description || '';
    
    document.getElementById('editCategoryModal').classList.remove('hidden');
}

function deleteCategory(categoryId, categoryName) {
    if (!categoryId || !categoryName) return;
    
    document.getElementById('deleteCategoryId').value = categoryId;
    document.getElementById('deleteCategoryName').textContent = categoryName;
    document.getElementById('deleteCategoryModal').classList.remove('hidden');
}

function backupDatabase() {
    alert('Database backup functionality would be implemented here');
}

function clearCache() {
    alert('Cache clearing functionality would be implemented here');
}

function exportData() {
    alert('Data export functionality would be implemented here');
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializePage);
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
