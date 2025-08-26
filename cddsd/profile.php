<?php
/**
 * User Profile Page
 * 
 * This page displays and allows editing of user profile information.
 */

// Include customer header
require_once 'includes/customer-header.php';

// Get user ID from URL or use current user
$user_id = isset($_GET['id']) ? (int) $_GET['id'] : $_SESSION['user_id'];

// Check if user can view this profile
if ($user_id !== $_SESSION['user_id'] && !has_permission('admin')) {
    set_flash_message('error', 'You do not have permission to view this profile.');
    redirect('dashboard.php');
}

// Get user data
$user = get_user($user_id);

if (!$user) {
    set_flash_message('error', 'User not found.');
    redirect('dashboard.php');
}

// Get user statistics if it's a volunteer
$user_stats = [];
if ($user['role'] === 'volunteer') {
    $user_stats = [
        'total_hours' => db_fetch_row("SELECT SUM(hours_worked) as total FROM volunteer_hours WHERE user_id = {$user_id}")['total'] ?: 0,
        'sessions_count' => db_count('volunteer_hours', "user_id = {$user_id}"),
        'avg_hours_per_session' => 0
    ];
    
    if ($user_stats['sessions_count'] > 0) {
        $user_stats['avg_hours_per_session'] = $user_stats['total_hours'] / $user_stats['sessions_count'];
    }
}

// Get recent activity
$recent_hours = [];
if ($user['role'] === 'volunteer') {
    $recent_hours = db_fetch_all("
        SELECT * FROM volunteer_hours 
        WHERE user_id = {$user_id} 
        ORDER BY work_date DESC 
        LIMIT 10
    ");
}

// Get recent orders for customers
$recent_orders = [];
if ($user['role'] === 'customer') {
    $orders_query = "SELECT o.*, COUNT(oi.id) as items_count 
                    FROM orders o 
                    LEFT JOIN order_items oi ON o.id = oi.order_id 
                    WHERE o.user_id = ? 
                    GROUP BY o.id 
                    ORDER BY o.created_at DESC LIMIT 5";
    $recent_orders = db_fetch_all($orders_query, [$user_id]);
}

// Get wishlist items for customers
$wishlist_items = [];
if ($user['role'] === 'customer') {
    $wishlist_query = "SELECT w.*, i.name, i.price, i.image 
                      FROM wishlist w 
                      LEFT JOIN inventory i ON w.item_id = i.id 
                      WHERE w.user_id = ? 
                      ORDER BY w.added_at DESC LIMIT 4";
    $wishlist_items = db_fetch_all($wishlist_query, [$user_id]);
}

// Include dashboard header for consistent notification system
include_once INCLUDES_PATH . '/dashboard-header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Profile Header -->
<div class="bg-white shadow rounded-lg mb-8">
    <div class="px-6 py-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-20 w-20 rounded-full bg-primary flex items-center justify-center text-white text-2xl font-bold">
                    <?php echo get_user_initials($user['first_name'], $user['last_name']); ?>
                </div>
            </div>
            <div class="ml-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    <?php echo $user['first_name'] . ' ' . $user['last_name']; ?>
                </h1>
                <p class="text-sm text-gray-600"><?php echo ucfirst($user['role']); ?></p>
                <p class="text-sm text-gray-500"><?php echo $user['email']; ?></p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo get_status_label_class($user['status']); ?> mt-2">
                    <?php echo ucfirst($user['status']); ?>
                </span>
            </div>
            <div class="ml-auto">
                <?php if ($user_id === $_SESSION['user_id']): ?>
                    <a href="settings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Edit Profile
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Content -->
    <div class="space-y-6">
        <?php if ($user['role'] === 'customer'): ?>
        
        <?php endif; ?>

        <?php if ($user['role'] === 'volunteer'): ?>
        <!-- Volunteer Stats -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Volunteer Statistics</h3>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600">Total Hours</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1"><?php echo number_format($user_stats['total_hours'], 1); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600">Sessions</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1"><?php echo $user_stats['sessions_count']; ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-600">Average Hours/Session</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1"><?php echo number_format($user_stats['avg_hours_per_session'], 1); ?></p>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-600">Contact Info</label>
                        <p class="mt-1 text-base font-medium text-gray-900"><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-600">Location</label>
                        <p class="mt-1 text-base font-medium text-gray-900">
                            <?php 
                            $address_parts = array_filter([
                                $user['city'],
                                $user['state'],
                                $user['country']
                            ]);
                            echo !empty($address_parts) ? htmlspecialchars(implode(', ', $address_parts)) : 'Not provided';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Profile Content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 bg-pink-50 p-6 rounded-lg">
    <!-- Main Content -->
    <div class="lg:col-span-2">
        <!-- Personal Information Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-600">Full Name</label>
                    <p class="mt-1 text-base font-medium text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-600">Email</label>
                    <p class="mt-1 text-base font-medium text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-600">Role</label>
                    <p class="mt-1 text-base font-medium text-gray-900"><?php echo ucfirst($user['role']); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-600">Member Since</label>
                    <p class="mt-1 text-base font-medium text-gray-900"><?php echo format_date($user['created_at']); ?></p>
                </div>
            </div>
        </div>

        <?php if ($user['role'] === 'customer'): ?>
        <!-- Recent Orders Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Recent Orders</h3>
                <a href="my-orders.php" class="text-sm text-primary hover:text-primary-dark">View all orders →</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($recent_orders)): ?>
                <p class="text-gray-500 text-center py-4">No orders yet. Start shopping!</p>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                    <div class="border-b border-gray-100 pb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-800">Order #<?php echo $order['id']; ?></p>
                                <p class="text-sm text-gray-500"><?php echo $order['items_count']; ?> items</p>
                                <p class="text-sm text-gray-500"><?php echo format_date($order['created_at']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-gray-800">$<?php echo number_format($order['total_amount'], 2); ?></p>
                                <span class="inline-block px-2 py-1 text-xs rounded-full <?php echo get_order_status_class($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
            </div>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-600">Full Name</label>
                        <p class="mt-1 text-base font-medium text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-600">Email</label>
                        <p class="mt-1 text-base font-medium text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-600">Role</label>
                        <p class="mt-1 text-base font-medium text-gray-900"><?php echo ucfirst($user['role']); ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-600">Member Since</label>
                        <p class="mt-1 text-base font-medium text-gray-900"><?php echo format_date($user['created_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity for Volunteers -->
        <?php if ($user['role'] === 'volunteer' && !empty($recent_hours)): ?>
            <div class="bg-white shadow rounded-lg mt-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Volunteer Hours</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_hours as $hours): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo format_date($hours['work_date']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo number_format($hours['hours_worked'], 1); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($hours['task']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars($hours['notes']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Statistics Sidebar -->
    <div>
        <?php if ($user['role'] === 'volunteer'): ?>
            <!-- Volunteer Statistics -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Volunteer Statistics</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Hours</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo number_format($user_stats['total_hours'], 1); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Sessions</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo $user_stats['sessions_count']; ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Avg Hours/Session</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo number_format($user_stats['avg_hours_per_session'], 1); ?></dd>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="px-6 py-4 space-y-3">
                <?php if ($user['role'] === 'volunteer'): ?>
                    <a href="volunteers.php" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="ri-time-line -ml-1 mr-2"></i>
                        Log Hours
                    </a>
                <?php endif; ?>
                
                <?php if (has_permission('admin')): ?>
                    <button onclick="resetPassword(<?php echo $user_id; ?>)" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="ri-lock-line -ml-1 mr-2"></i>
                        Reset Password
                    </button>
                    
                    <button onclick="toggleUserStatus(<?php echo $user_id; ?>, '<?php echo $user['status']; ?>')" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="ri-user-settings-line -ml-1 mr-2"></i>
                        <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?> User
                    </button>
                <?php endif; ?>
                
                <a href="mailto:<?php echo $user['email']; ?>" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <i class="ri-mail-line -ml-1 mr-2"></i>
                    Send Email
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function resetPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password?')) {
        // In a real application, this would make an AJAX call to reset the password
        alert('Password reset functionality would be implemented here');
    }
}

function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        // In a real application, this would make an AJAX call to update the user status
        alert(`User ${action} functionality would be implemented here`);
    }
}
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
