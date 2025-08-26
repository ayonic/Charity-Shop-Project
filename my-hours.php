<?php
/**
 * My Hours Page - Volunteer Hours Management
 * 
 * This page allows volunteers to view and log their work hours.
 */

// Include initialization file
require_once 'config/init.php';

// Require login and volunteer role
require_login();
if (!has_permission('volunteer') && !has_permission('admin')) {
    redirect('dashboard.php');
}

// Get current user
$current_user = get_logged_in_user();
$user_id = $current_user['id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'log_hours') {
        $data = [
            'user_id' => $user_id,
            'work_date' => sanitize_input($_POST['work_date']),
            'hours_worked' => (float) $_POST['hours_worked'],
            'task' => sanitize_input($_POST['task']),
            'notes' => sanitize_input($_POST['notes'])
        ];
        
        if (db_insert('volunteer_hours', $data)) {
            set_flash_message('success', 'Hours logged successfully!');
            log_activity($user_id, 'volunteer', 'Logged ' . $data['hours_worked'] . ' hours for: ' . $data['task']);
        } else {
            set_flash_message('error', 'Failed to log hours.');
        }
        
        redirect('my-hours.php');
    }
}

// Get volunteer's hours
$volunteer_hours = db_fetch_all("
    SELECT * FROM volunteer_hours 
    WHERE user_id = {$user_id}
    ORDER BY work_date DESC 
    LIMIT 20
");

// Get statistics
$stats = get_volunteer_dashboard_stats($user_id);

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">My Hours</h1>
            <p class="mt-1 text-sm text-gray-600">Track and manage your volunteer hours</p>
        </div>
        <button data-modal-trigger data-modal-target="logHoursModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            <i class="ri-add-line -ml-1 mr-2"></i>
            Log Hours
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <i class="ri-time-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['hours_this_month'], 1); ?> hrs</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <i class="ri-trophy-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Hours</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo number_format($stats['total_hours'], 1); ?> hrs</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <i class="ri-calendar-check-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Days Active</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['days_active']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hours Table -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Recent Hours</h3>
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
                <?php if (empty($volunteer_hours)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No hours logged yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($volunteer_hours as $hours): ?>
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
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Log Hours Modal -->
<div id="logHoursModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Log Volunteer Hours</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="log_hours">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="work_date" class="block text-sm font-medium text-gray-700">Work Date</label>
                        <input type="date" name="work_date" id="work_date" required 
                               value="<?php echo date('Y-m-d'); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="hours_worked" class="block text-sm font-medium text-gray-700">Hours Worked</label>
                        <input type="number" name="hours_worked" id="hours_worked" step="0.5" min="0" max="24" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="task" class="block text-sm font-medium text-gray-700">Task</label>
                    <input type="text" name="task" id="task" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                           placeholder="e.g., Sorting donations, Customer service, etc.">
                </div>
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Log Hours
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
