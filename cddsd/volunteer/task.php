<?php
require_once '../config/init.php';
require_once INCLUDES_PATH . '/dashboard-header.php';

// Check if user is logged in and is a volunteer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'volunteer') {
    set_flash_message('error', 'You must be a volunteer to access this page.');
    redirect('../login.php');
}

// Get task ID from URL
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$task_id) {
    set_flash_message('error', 'Invalid task ID');
    redirect('dashboard.php');
}

// Get task details
$task_query = "SELECT t.*, p.name as project_name, p.description as project_description 
              FROM tasks t 
              LEFT JOIN projects p ON t.project_id = p.id 
              WHERE t.id = ? AND t.volunteer_id = ?";
$task = db_fetch_row($task_query, [$task_id, $_SESSION['user_id']]);

if (!$task) {
    set_flash_message('error', 'Task not found or access denied');
    redirect('dashboard.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = trim($_POST['status']);
    $valid_statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
    
    if (in_array($new_status, $valid_statuses)) {
        $update_query = "UPDATE tasks 
                        SET status = ?, 
                            updated_at = NOW(), 
                            completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE completed_at END 
                        WHERE id = ? AND volunteer_id = ?";
        
        if (db_query($update_query, [$new_status, $new_status, $task_id, $_SESSION['user_id']])) {
            // Add task update to activity log
            $log_query = "INSERT INTO activity_log (user_id, activity_type, related_id, description, created_at) 
                         VALUES (?, 'task_update', ?, ?, NOW())";
            $description = "Updated task status to " . ucfirst($new_status);
            db_query($log_query, [$_SESSION['user_id'], $task_id, $description]);
            
            set_flash_message('success', 'Task status updated successfully');
            redirect('task.php?id=' . $task_id);
        } else {
            set_flash_message('error', 'Failed to update task status');
        }
    } else {
        set_flash_message('error', 'Invalid status');
    }
}

// Get task comments
$comments_query = "SELECT c.*, u.username, u.first_name, u.last_name 
                  FROM task_comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.task_id = ? 
                  ORDER BY c.created_at DESC";
$comments = db_fetch_all($comments_query, [$task_id]);

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment_text = trim($_POST['comment']);
    if (!empty($comment_text)) {
        $comment_query = "INSERT INTO task_comments (task_id, user_id, comment, created_at) 
                         VALUES (?, ?, ?, NOW())";
        
        if (db_query($comment_query, [$task_id, $_SESSION['user_id'], $comment_text])) {
            set_flash_message('success', 'Comment added successfully');
            redirect('task.php?id=' . $task_id);
        } else {
            set_flash_message('error', 'Failed to add comment');
        }
    }
}
?>

<div class="container px-6 mx-auto">
    <div class="my-6 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-700">Task Details</h2>
        <a href="dashboard.php" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
            Back to Dashboard
        </a>
    </div>

    <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2"><?php echo htmlspecialchars($task['title']); ?></h3>
            <p class="text-sm text-gray-600 mb-4"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Project:</p>
                    <p class="font-medium"><?php echo htmlspecialchars($task['project_name']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Due Date:</p>
                    <p class="font-medium"><?php echo date('M j, Y', strtotime($task['due_date'])); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status:</p>
                    <span class="px-2 py-1 font-semibold text-sm rounded-full <?php echo get_status_badge_class($task['status']); ?>">
                        <?php echo ucfirst($task['status']); ?>
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Priority:</p>
                    <span class="px-2 py-1 font-semibold text-sm rounded-full <?php echo get_priority_badge_class($task['priority']); ?>">
                        <?php echo ucfirst($task['priority']); ?>
                    </span>
                </div>
            </div>

            <!-- Status Update Form -->
            <form action="task.php?id=<?php echo $task_id; ?>" method="POST" class="mt-6">
                <div class="flex items-center space-x-4">
                    <select name="status" class="block w-48 mt-1 text-sm border-gray-300 rounded-md focus:border-purple-400 focus:outline-none focus:shadow-outline-purple">
                        <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $task['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                        Update Status
                    </button>
                </div>
            </form>
        </div>

        <!-- Comments Section -->
        <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-700 mb-4">Comments</h4>
            
            <!-- New Comment Form -->
            <form action="task.php?id=<?php echo $task_id; ?>" method="POST" class="mb-6">
                <div class="mb-4">
                    <textarea name="comment" rows="3" class="block w-full mt-1 text-sm border-gray-300 rounded-md focus:border-purple-400 focus:outline-none focus:shadow-outline-purple" placeholder="Add a comment..."></textarea>
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Add Comment
                </button>
            </form>

            <!-- Comments List -->
            <div class="space-y-4">
                <?php if (empty($comments)): ?>
                    <p class="text-gray-500 text-center">No comments yet</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center">
                                    <span class="font-medium text-gray-700">
                                        <?php 
                                        echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']) ?: 
                                             htmlspecialchars($comment['username']); 
                                        ?>
                                    </span>
                                    <span class="text-sm text-gray-500 ml-2">
                                        <?php echo format_datetime($comment['created_at']); ?>
                                    </span>
                                </div>
                            </div>
                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/dashboard-footer.php'; ?>