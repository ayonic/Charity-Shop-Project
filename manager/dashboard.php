<?php
require_once '../config/init.php';
require_once INCLUDES_PATH . '/dashboard-header.php';
require_once INCLUDES_PATH . '/session_manager.php';

// Validate user role
validate_user_role('manager', '../login.php');

// Get overall statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'volunteer') as total_volunteers,
    (SELECT COUNT(*) FROM users WHERE role = 'donor') as total_donors,
    (SELECT COUNT(*) FROM users WHERE role = 'customer') as total_customers,
    (SELECT COUNT(*) FROM products) as total_products,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COUNT(*) FROM donations) as total_donations,
    (SELECT SUM(amount) FROM donations) as total_donation_amount,
    (SELECT SUM(total_amount) FROM orders) as total_sales_amount";
$stats = db_fetch_row($stats_query);

// Get recent activities
$activities_query = "(
    SELECT 'order' as type, o.id, o.total_amount as amount, o.status, o.created_at, u.username,
           CONCAT('Order #', o.id) as title
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
)
UNION ALL
(
    SELECT 'donation' as type, d.id, d.amount, d.status, d.created_at, u.username,
           CONCAT('Donation to ', c.name) as title
    FROM donations d
    JOIN users u ON d.donor_id = u.id
    JOIN causes c ON d.cause_id = c.id
    ORDER BY d.created_at DESC
    LIMIT 5
)
ORDER BY created_at DESC
LIMIT 10";
$recent_activities = db_fetch_all($activities_query);

// Get staff performance metrics
$staff_query = "SELECT u.id, u.username, u.role,
                COUNT(DISTINCT CASE WHEN t.type = 'task' THEN t.id END) as tasks_completed,
                COUNT(DISTINCT CASE WHEN t.type = 'review' THEN t.id END) as reviews_completed,
                MAX(t.completed_at) as last_activity
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assigned_to AND t.status = 'completed'
                WHERE u.role IN ('volunteer', 'moderator')
                GROUP BY u.id
                ORDER BY tasks_completed DESC, reviews_completed DESC
                LIMIT 5";
$staff_performance = db_fetch_all($staff_query);

// Get inventory alerts
$inventory_query = "SELECT p.id, p.name, p.stock_quantity, p.reorder_level,
                    c.name as category
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.stock_quantity <= p.reorder_level
                    ORDER BY (p.stock_quantity / p.reorder_level)
                    LIMIT 5";
$inventory_alerts = db_fetch_all($inventory_query);

// Get pending tasks
$tasks_query = "SELECT t.*, u.username as assigned_to_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.status = 'pending'
                ORDER BY t.priority DESC, t.due_date ASC
                LIMIT 5";
$pending_tasks = db_fetch_all($tasks_query);
?>

<div class="container px-6 mx-auto grid">
    <!-- Statistics Cards -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <!-- Volunteer Stats -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Volunteers</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['total_volunteers'] ?? 0; ?></p>
            </div>
        </div>

        <!-- Donor Stats -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Donations</p>
                <p class="text-lg font-semibold text-gray-700">$<?php echo number_format($stats['total_donation_amount'] ?? 0, 2); ?></p>
            </div>
        </div>

        <!-- Customer Stats -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Sales</p>
                <p class="text-lg font-semibold text-gray-700">$<?php echo number_format($stats['total_sales_amount'] ?? 0, 2); ?></p>
            </div>
        </div>

        <!-- Product Stats -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Products</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['total_products'] ?? 0; ?></p>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Recent Activities</h2>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Activity</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    <?php foreach ($recent_activities as $activity): ?>
                        <tr class="text-gray-700">
                            <td class="px-4 py-3">
                                <div class="flex items-center text-sm">
                                    <div class="relative hidden w-8 h-8 mr-3 rounded-full md:block">
                                        <div class="p-1 text-<?php echo $activity['type'] === 'order' ? 'blue' : 'green'; ?>-500">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <?php if ($activity['type'] === 'order'): ?>
                                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                                                <?php else: ?>
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                <?php endif; ?>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($activity['title']); ?></p>
                                        <p class="text-xs text-gray-600"><?php echo ucfirst($activity['type']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($activity['username']); ?></td>
                            <td class="px-4 py-3 text-sm">$<?php echo number_format($activity['amount'], 2); ?></td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo get_status_badge_class($activity['status']); ?>">
                                    <?php echo ucfirst($activity['status']); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm"><?php echo date('M j, Y H:i', strtotime($activity['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Staff Performance and Inventory Alerts -->
    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <!-- Staff Performance -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
            <h4 class="mb-4 font-semibold text-gray-600">Staff Performance</h4>
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b">
                            <th class="px-4 py-2">Staff</th>
                            <th class="px-4 py-2">Role</th>
                            <th class="px-4 py-2">Tasks</th>
                            <th class="px-4 py-2">Reviews</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($staff_performance as $staff): ?>
                            <tr class="text-gray-700">
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($staff['username']); ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo ucfirst($staff['role']); ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo $staff['tasks_completed']; ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo $staff['reviews_completed']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory Alerts -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
            <h4 class="mb-4 font-semibold text-gray-600">Low Stock Alerts</h4>
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b">
                            <th class="px-4 py-2">Product</th>
                            <th class="px-4 py-2">Category</th>
                            <th class="px-4 py-2">Stock</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($inventory_alerts as $product): ?>
                            <tr class="text-gray-700">
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($product['category']); ?></td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $product['stock_quantity'] === 0 ? 'text-red-700 bg-red-100' : 'text-orange-700 bg-orange-100'; ?>">
                                        <?php echo $product['stock_quantity']; ?> left
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <a href="../inventory/restock.php?id=<?php echo $product['id']; ?>" 
                                       class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Pending Tasks</h2>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Assigned To</th>
                        <th class="px-4 py-3">Due Date</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    <?php if (empty($pending_tasks)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-gray-500">No pending tasks</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_tasks as $task): ?>
                            <tr class="text-gray-700">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold"><?php echo htmlspecialchars($task['title']); ?></p>
                                            <p class="text-xs text-gray-600"><?php echo htmlspecialchars($task['description']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($task['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo date('M j, Y', strtotime($task['due_date'])); ?></td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo get_priority_badge_class($task['priority']); ?>">
                                        <?php echo ucfirst($task['priority']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="../tasks/edit.php?id=<?php echo $task['id']; ?>" 
                                       class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/dashboard-footer.php'; ?>