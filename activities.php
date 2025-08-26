<?php
/**
 * Activity Logs Page
 * 
 * Displays a comprehensive log of all system activities with filtering options.
 * Accessible by all authenticated users to view relevant activities.
 */

$page_title = 'Activity Logs';
require_once 'includes/dashboard-header.php';

// Include sidebar
include_once INCLUDES_PATH . '/sidebar.php';

// Start content wrapper
include_once INCLUDES_PATH . '/content-start.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect_to('login.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filtering
$activity_type = isset($_GET['type']) ? $_GET['type'] : '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query conditions
$conditions = [];
$params = [];

if ($activity_type) {
    $conditions[] = 'a.activity_type = ?';
    $params[] = $activity_type;
}

if ($user_id) {
    $conditions[] = 'a.user_id = ?';
    $params[] = $user_id;
}

if ($date_from) {
    $conditions[] = 'DATE(a.created_at) >= ?';
    $params[] = $date_from;
}

if ($date_to) {
    $conditions[] = 'DATE(a.created_at) <= ?';
    $params[] = $date_to;
}

$where_clause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM activities a $where_clause";
$total_activities = (int)db_fetch_row($count_query, $params)['total'];

// Get activities with user details
$query = "SELECT a.*, u.first_name, u.last_name, u.email 
         FROM activities a 
         LEFT JOIN users u ON a.user_id = u.id 
         $where_clause 
         ORDER BY a.created_at DESC 
         LIMIT $per_page OFFSET $offset";
$activities = db_fetch_all($query, $params);

// Calculate total pages
$total_pages = ceil($total_activities / $per_page);
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Activities</h2>
        <form method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="login" <?php echo $activity_type === 'login' ? 'selected' : ''; ?>>Login</option>
                    <option value="donation" <?php echo $activity_type === 'donation' ? 'selected' : ''; ?>>Donation</option>
                    <option value="sale" <?php echo $activity_type === 'sale' ? 'selected' : ''; ?>>Sale</option>
                    <option value="inventory" <?php echo $activity_type === 'inventory' ? 'selected' : ''; ?>>Inventory</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Apply Filters</button>
            </div>
        </form>
    </div>

    <!-- Activities Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Activity Log</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('Y-m-d H:i:s', strtotime($activity['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo get_activity_type_class($activity['activity_type']); ?>">
                                    <?php echo ucfirst($activity['activity_type']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo htmlspecialchars($activity['description']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $activity_type ? '&type=' . urlencode($activity_type) : ''; ?><?php echo $date_from ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo $date_to ? '&date_to=' . urlencode($date_to) : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $activity_type ? '&type=' . urlencode($activity_type) : ''; ?><?php echo $date_from ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo $date_to ? '&date_to=' . urlencode($date_to) : ''; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</main>
</div>

<?php
require_once 'includes/dashboard-footer.php';

// Helper function for activity type styling
function get_activity_type_class($type) {
    switch ($type) {
        case 'login':
            return 'bg-blue-100 text-blue-800';
        case 'donation':
            return 'bg-green-100 text-green-800';
        case 'sale':
            return 'bg-purple-100 text-purple-800';
        case 'inventory':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}