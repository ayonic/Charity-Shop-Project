<?php
/**
 * User Reports Page
 * 
 * This page allows moderators to view and handle user-reported issues.
 */

require_once 'config/init.php';

// Ensure user is logged in and has moderator role
if (!is_logged_in() || !has_role('moderator')) {
    set_flash_message('error', 'Unauthorized access.');
    redirect('login.php');
}

// Handle report actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
    $action = $_POST['action'];
    $resolution = isset($_POST['resolution']) ? trim($_POST['resolution']) : '';
    
    if ($report_id > 0) {
        switch ($action) {
            case 'resolve':
                if (empty($resolution)) {
                    set_flash_message('error', 'Please provide resolution details.');
                    break;
                }
                
                $update_query = "UPDATE user_reports SET 
                                status = 'resolved',
                                resolved_by = ?,
                                resolved_at = NOW(),
                                resolution = ?
                                WHERE id = ?";
                db_query($update_query, [$_SESSION['user_id'], $resolution, $report_id]);
                
                // Log activity
                log_activity('user_report', 'Resolved report #' . $report_id);
                set_flash_message('success', 'Report marked as resolved.');
                break;
                
            case 'dismiss':
                if (empty($resolution)) {
                    set_flash_message('error', 'Please provide a reason for dismissal.');
                    break;
                }
                
                $update_query = "UPDATE user_reports SET 
                                status = 'dismissed',
                                resolved_by = ?,
                                resolved_at = NOW(),
                                resolution = ?
                                WHERE id = ?";
                db_query($update_query, [$_SESSION['user_id'], $resolution, $report_id]);
                
                // Log activity
                log_activity('user_report', 'Dismissed report #' . $report_id);
                set_flash_message('success', 'Report dismissed successfully.');
                break;
        }
    }
    
    // Redirect to prevent form resubmission
    redirect('user-reports.php');
}

// Get reports with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'open';
$valid_statuses = ['open', 'resolved', 'dismissed'];
$status_filter = in_array($status_filter, $valid_statuses) ? $status_filter : 'open';

// Get total count for pagination
$total_query = "SELECT COUNT(*) as count FROM user_reports WHERE status = ?";
$total_items = db_fetch_row($total_query, [$status_filter])['count'];
$total_pages = ceil($total_items / $items_per_page);

// Get reports
$reports_query = "SELECT r.*, 
                        u1.first_name as reporter_first_name, 
                        u1.last_name as reporter_last_name,
                        u2.first_name as reported_first_name,
                        u2.last_name as reported_last_name,
                        u3.first_name as resolver_first_name,
                        u3.last_name as resolver_last_name
                 FROM user_reports r
                 LEFT JOIN users u1 ON r.reporter_id = u1.id
                 LEFT JOIN users u2 ON r.reported_user_id = u2.id
                 LEFT JOIN users u3 ON r.resolved_by = u3.id
                 WHERE r.status = ?
                 ORDER BY r.created_at DESC
                 LIMIT ? OFFSET ?";
$reports = db_fetch_all($reports_query, [$status_filter, $items_per_page, $offset]);

// Page title
$page_title = 'User Reports';

// Include header
include 'includes/header.php';

// Include content start
include 'includes/content-start.php';
?>

<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Status Filter Tabs -->
    <div class="flex space-x-4 mb-6">
        <?php foreach ($valid_statuses as $status): ?>
        <a href="?status=<?php echo $status; ?>" 
           class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $status === $status_filter ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
            <?php echo ucfirst($status); ?>
            <?php if ($status === 'open'): ?>
            <span class="ml-2 bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-xs">
                <?php echo db_fetch_row("SELECT COUNT(*) as count FROM user_reports WHERE status = 'open'")['count']; ?>
            </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($reports)): ?>
    <div class="text-center py-8">
        <i class="ri-file-list-3-line text-4xl text-gray-400 mb-3"></i>
        <p class="text-gray-500">No <?php echo $status_filter; ?> reports found</p>
    </div>
    <?php else: ?>
    <div class="space-y-6">
        <?php foreach ($reports as $report): ?>
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-sm <?php echo get_report_type_class($report['report_type']); ?> mb-2">
                        <?php echo ucfirst($report['report_type']); ?>
                    </span>
                    <h3 class="font-medium text-gray-800">
                        Report against <?php echo htmlspecialchars($report['reported_first_name'] . ' ' . $report['reported_last_name']); ?>
                    </h3>
                    <p class="text-sm text-gray-500">
                        Reported by <?php echo htmlspecialchars($report['reporter_first_name'] . ' ' . $report['reporter_last_name']); ?>
                        on <?php echo format_date($report['created_at']); ?>
                    </p>
                </div>
                <?php if ($report['status'] !== 'open'): ?>
                <div class="text-right text-sm text-gray-500">
                    <p>Handled by <?php echo htmlspecialchars($report['resolver_first_name'] . ' ' . $report['resolver_last_name']); ?></p>
                    <p><?php echo format_date($report['resolved_at']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="prose max-w-none mb-4">
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                <?php if ($report['status'] !== 'open'): ?>
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm font-medium text-gray-700">Resolution:</p>
                    <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($report['resolution'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($report['status'] === 'open'): ?>
            <div class="flex items-center justify-end space-x-4">
                <button onclick="showActionModal('dismiss', <?php echo $report['id']; ?>)" 
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-700">
                    <i class="ri-close-circle-line mr-1"></i> Dismiss
                </button>
                <button onclick="showActionModal('resolve', <?php echo $report['id']; ?>)" 
                        class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-dark">
                    <i class="ri-check-line mr-1"></i> Resolve
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-6 space-x-2">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>" 
           class="px-3 py-1 text-sm <?php echo $i === $page ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'; ?> rounded">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Action Modal -->
<div id="actionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 id="modalTitle" class="text-lg font-semibold text-gray-800 mb-4"></h3>
        <form action="user-reports.php" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="report_id" id="reportId">
            <input type="hidden" name="action" id="actionType">
            
            <div class="mb-4">
                <label for="resolution" class="block text-sm font-medium text-gray-700 mb-2">
                    <span id="resolutionLabel"></span>
                </label>
                <textarea name="resolution" id="resolution" rows="3" 
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                          required></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideActionModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-700">
                    Cancel
                </button>
                <button type="submit" id="confirmButton" 
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg">
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showActionModal(action, reportId) {
    const modal = document.getElementById('actionModal');
    const titleEl = document.getElementById('modalTitle');
    const labelEl = document.getElementById('resolutionLabel');
    const buttonEl = document.getElementById('confirmButton');
    const actionEl = document.getElementById('actionType');
    const reportIdEl = document.getElementById('reportId');
    
    if (action === 'resolve') {
        titleEl.textContent = 'Resolve Report';
        labelEl.textContent = 'Resolution Details';
        buttonEl.textContent = 'Confirm Resolution';
        buttonEl.className = 'px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-dark';
    } else {
        titleEl.textContent = 'Dismiss Report';
        labelEl.textContent = 'Reason for Dismissal';
        buttonEl.textContent = 'Confirm Dismissal';
        buttonEl.className = 'px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700';
    }
    
    actionEl.value = action;
    reportIdEl.value = reportId;
    modal.classList.remove('hidden');
}

function hideActionModal() {
    const modal = document.getElementById('actionModal');
    const resolutionEl = document.getElementById('resolution');
    
    modal.classList.add('hidden');
    resolutionEl.value = '';
}
</script>

<?php
// Include content end
include 'includes/content-end.php';

// Include footer
include 'includes/footer.php';
?>