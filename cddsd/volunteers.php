<?php
/**
 * Volunteer Management Page
 * 
 * This page handles volunteer management including scheduling and hour tracking.
 */

// Include initialization file
require_once 'config/init.php';

// Require login
require_login();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_volunteer':
                $data = [
                    'first_name' => sanitize_input($_POST['first_name']),
                    'last_name' => sanitize_input($_POST['last_name']),
                    'email' => sanitize_input($_POST['email']),
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'role' => 'volunteer',
                    'phone' => sanitize_input($_POST['phone']),
                    'address' => sanitize_input($_POST['address']),
                    'city' => sanitize_input($_POST['city']),
                    'state' => sanitize_input($_POST['state']),
                    'postal_code' => sanitize_input($_POST['postal_code']),
                    'notes' => sanitize_input($_POST['notes'])
                ];
                
                if (db_insert('users', $data)) {
                    set_flash_message('success', 'Volunteer added successfully!');
                } else {
                    set_flash_message('error', 'Failed to add volunteer.');
                }
                break;
                
            case 'log_hours':
                $data = [
                    'user_id' => (int) $_POST['user_id'],
                    'work_date' => sanitize_input($_POST['work_date']),
                    'hours_worked' => (float) $_POST['hours_worked'],
                    'task' => sanitize_input($_POST['task']),
                    'notes' => sanitize_input($_POST['notes'])
                ];
                
                if (db_insert('volunteer_hours', $data)) {
                    set_flash_message('success', 'Hours logged successfully!');
                } else {
                    set_flash_message('error', 'Failed to log hours.');
                }
                break;
                
            case 'add_event':
                $data = [
                    'title' => sanitize_input($_POST['title']),
                    'description' => sanitize_input($_POST['description']),
                    'location' => sanitize_input($_POST['location']),
                    'start_date' => sanitize_input($_POST['start_date']),
                    'end_date' => sanitize_input($_POST['end_date']),
                    'assigned_to' => !empty($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null,
                    'status' => sanitize_input($_POST['status'])
                ];
                
                if (db_insert('events', $data)) {
                    set_flash_message('success', 'Event added successfully!');
                } else {
                    set_flash_message('error', 'Failed to add event.');
                }
                break;
        }
        
        redirect('volunteers.php');
    }
}

// Get volunteers
$volunteers = get_volunteers();

// Get recent volunteer hours
$recent_hours = db_fetch_all("
    SELECT vh.*, u.first_name, u.last_name 
    FROM volunteer_hours vh 
    JOIN users u ON vh.user_id = u.id 
    ORDER BY vh.work_date DESC 
    LIMIT 10
");

// Get upcoming events
$upcoming_events = db_fetch_all("
    SELECT e.*, u.first_name, u.last_name 
    FROM events e 
    LEFT JOIN users u ON e.assigned_to = u.id 
    WHERE e.start_date >= NOW() 
    ORDER BY e.start_date ASC 
    LIMIT 5
");

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Volunteer Management</h1>
            <p class="mt-1 text-sm text-gray-600">Manage volunteers, schedules, and track hours</p>
        </div>
        <div class="flex space-x-3">
            <button data-modal-trigger data-modal-target="addVolunteerModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="ri-user-add-line -ml-1 mr-2"></i>
                Add Volunteer
            </button>
            <button data-modal-trigger data-modal-target="logHoursModal" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="ri-time-line -ml-1 mr-2"></i>
                Log Hours
            </button>
            <button data-modal-trigger data-modal-target="addEventModal" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="ri-calendar-event-line -ml-1 mr-2"></i>
                Add Event
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <i class="ri-team-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Volunteers</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo count($volunteers); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

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
                        <dt class="text-sm font-medium text-gray-500 truncate">Hours This Week</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            <?php 
                            $week_hours = db_fetch_row("SELECT SUM(hours_worked) as total FROM volunteer_hours WHERE work_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)");
                            echo $week_hours['total'] ? number_format($week_hours['total'], 1) : '0';
                            ?>
                        </dd>
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
                        <i class="ri-calendar-event-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Events</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo count($upcoming_events); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                        <i class="ri-bar-chart-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Avg Hours/Week</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            <?php 
                            $avg_hours = db_fetch_row("SELECT AVG(weekly_hours) as average FROM (SELECT SUM(hours_worked) as weekly_hours FROM volunteer_hours WHERE work_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK) GROUP BY WEEK(work_date)) as weekly_totals");
                            echo $avg_hours['average'] ? number_format($avg_hours['average'], 1) : '0';
                            ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Volunteers List -->
    <div class="lg:col-span-2">
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Volunteers</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($volunteers)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No volunteers found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($volunteers as $volunteer): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center text-white font-medium">
                                                    <?php echo get_user_initials($volunteer['first_name'], $volunteer['last_name']); ?>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $volunteer['first_name'] . ' ' . $volunteer['last_name']; ?>
                                                </div>
                                                <div class="text-sm text-gray-500"><?php echo $volunteer['email']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $volunteer['phone']; ?></div>
                                        <div class="text-sm text-gray-500"><?php echo $volunteer['city'] . ', ' . $volunteer['state']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo get_status_label_class($volunteer['status']); ?>">
                                            <?php echo ucfirst($volunteer['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="viewVolunteerDetails(<?php echo $volunteer['id']; ?>)" class="text-primary hover:text-indigo-900 mr-3">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button onclick="editVolunteer(<?php echo $volunteer['id']; ?>)" class="text-green-600 hover:text-green-900">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div>
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Upcoming Events</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php if (empty($upcoming_events)): ?>
                    <div class="px-6 py-4 text-sm text-gray-500">No upcoming events</div>
                <?php else: ?>
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo $event['title']; ?></p>
                                    <p class="text-sm text-gray-500"><?php echo format_datetime($event['start_date']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo $event['location']; ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo get_status_label_class($event['status']); ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                    <?php if ($event['first_name']): ?>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo $event['first_name'] . ' ' . $event['last_name']; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Hours -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Recent Hours Logged</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volunteer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($recent_hours)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hours logged recently</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_hours as $hours): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo $hours['first_name'] . ' ' . $hours['last_name']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo format_date($hours['work_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo number_format($hours['hours_worked'], 1); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $hours['task']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo $hours['notes']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Volunteer Modal -->
<div id="addVolunteerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Volunteer</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add_volunteer">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="add_first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" id="add_first_name" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" id="add_last_name" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="add_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="add_email" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="phone" id="add_phone" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="add_password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="add_password" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                
                <div>
                    <label for="add_address" class="block text-sm font-medium text-gray-700">Address</label>
                    <input type="text" name="address" id="add_address" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="add_city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="city" id="add_city" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_state" class="block text-sm font-medium text-gray-700">State</label>
                        <input type="text" name="state" id="add_state" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" name="postal_code" id="add_postal_code" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="add_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="add_notes" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Add Volunteer
                    </button>
                </div>
            </form>
        </div>
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
                <input type="hidden" name="action" value="delete_volunteer">
                
                <div>
                    <label for="log_user_id" class="block text-sm font-medium text-gray-700">Volunteer</label>
                    <select name="user_id" id="log_user_id" required 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                        <option value="">Select Volunteer</option>
                        <?php foreach ($volunteers as $volunteer): ?>
                            <option value="<?php echo $volunteer['id']; ?>">
                                <?php echo $volunteer['first_name'] . ' ' . $volunteer['last_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="log_work_date" class="block text-sm font-medium text-gray-700">Work Date</label>
                        <input type="date" name="work_date" id="log_work_date" required 
                               value="<?php echo date('Y-m-d'); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="log_hours_worked" class="block text-sm font-medium text-gray-700">Hours Worked</label>
                        <input type="number" name="hours_worked" id="log_hours_worked" step="0.5" min="0" max="24" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="log_task" class="block text-sm font-medium text-gray-700">Task</label>
                    <input type="text" name="task" id="log_task" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                           placeholder="e.g., Sorting donations, Customer service, etc.">
                </div>
                
                <div>
                    <label for="log_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="log_notes" rows="3" 
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

<!-- Add Event Modal -->
<div id="addEventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Event</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_volunteer">
                
                <div>
                    <label for="event_title" class="block text-sm font-medium text-gray-700">Event Title</label>
                    <input type="text" name="title" id="event_title" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                
                <div>
                    <label for="event_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="event_description" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div>
                    <label for="event_location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" id="event_location" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="event_start_date" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                        <input type="datetime-local" name="start_date" id="event_start_date" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="event_end_date" class="block text-sm font-medium text-gray-700">End Date & Time</label>
                        <input type="datetime-local" name="end_date" id="event_end_date" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="event_assigned_to" class="block text-sm font-medium text-gray-700">Assign To</label>
                        <select name="assigned_to" id="event_assigned_to" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="">Unassigned</option>
                            <?php foreach ($volunteers as $volunteer): ?>
                                <option value="<?php echo $volunteer['id']; ?>">
                                    <?php echo $volunteer['first_name'] . ' ' . $volunteer['last_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="event_status" class="block text-sm font-medium text-gray-700">Status</label>
                        
                            <select name="status" id="event_status" required 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Add Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewVolunteerDetails(volunteerId) {
    // In a real application, this would open a modal with volunteer details
    alert('Volunteer details for ID: ' + volunteerId);
}

function editVolunteer(volunteerId) {
    // In a real application, this would open an edit modal
    alert('Edit volunteer with ID: ' + volunteerId);
}
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
