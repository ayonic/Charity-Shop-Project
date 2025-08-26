<?php
require_once '../config/init.php';
require_once INCLUDES_PATH . '/dashboard-header.php';
require_once INCLUDES_PATH . '/session_manager.php';

// Validate user role
validate_user_role('volunteer', '../login.php');

// Get volunteer's tasks
$tasks_query = "SELECT t.*, p.name as project_name 
               FROM tasks t 
               LEFT JOIN projects p ON t.project_id = p.id 
               WHERE t.volunteer_id = ? 
               ORDER BY t.due_date ASC";
$tasks = db_fetch_all($tasks_query, [$_SESSION['user_id']]);

// Get volunteer's schedule
$schedule_query = "SELECT * FROM volunteer_schedules 
                  WHERE volunteer_id = ? 
                  AND schedule_date >= CURDATE() 
                  ORDER BY schedule_date ASC, start_time ASC 
                  LIMIT 5";
$schedule = db_fetch_all($schedule_query, [$_SESSION['user_id']]);

// Get volunteer's stats
$stats_query = "SELECT 
                COUNT(DISTINCT t.id) as total_tasks,
                SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                COUNT(DISTINCT p.id) as total_projects,
                SUM(TIME_TO_SEC(TIMEDIFF(vs.end_time, vs.start_time)))/3600 as total_hours
               FROM volunteers v 
               LEFT JOIN tasks t ON v.user_id = t.volunteer_id
               LEFT JOIN projects p ON t.project_id = p.id
               LEFT JOIN volunteer_schedules vs ON v.user_id = vs.volunteer_id
               WHERE v.user_id = ?";
$stats = db_fetch_row($stats_query, [$_SESSION['user_id']]);
?>

<div class="container px-6 mx-auto grid">
    <!-- Stats Cards -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Tasks</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['total_tasks'] ?? 0; ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Completed Tasks</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['completed_tasks'] ?? 0; ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Projects</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['total_projects'] ?? 0; ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Hours</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo round($stats['total_hours'] ?? 0, 1); ?></p>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Current Tasks</h2>
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">Due Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-gray-500">No tasks assigned</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr class="text-gray-700">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold"><?php echo htmlspecialchars($task['title']); ?></p>
                                            <p class="text-xs text-gray-600"><?php echo htmlspecialchars($task['description']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($task['project_name']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo date('M j, Y', strtotime($task['due_date'])); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo get_status_badge_class($task['status']); ?>">
                                        <?php echo ucfirst($task['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="task.php?id=<?php echo $task['id']; ?>" class="text-purple-600 hover:text-purple-900">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Schedule -->
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Upcoming Schedule</h2>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Role</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    <?php if (empty($schedule)): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-center text-gray-500">No upcoming shifts scheduled</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($schedule as $shift): ?>
                            <tr class="text-gray-700">
                                <td class="px-4 py-3 text-sm"><?php echo date('M j, Y', strtotime($shift['schedule_date'])); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <?php 
                                    echo date('g:i A', strtotime($shift['start_time'])) . ' - ' . 
                                         date('g:i A', strtotime($shift['end_time']));
                                    ?>
                                </td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($shift['location']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($shift['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/dashboard-footer.php'; ?>