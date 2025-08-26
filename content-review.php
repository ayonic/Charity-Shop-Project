<?php
/**
 * Content Review Page
 * 
 * This page allows moderators to review and manage content submissions.
 */

require_once 'config/init.php';

// Ensure user is logged in and has moderator role
if (!is_logged_in() || !has_role('moderator')) {
    set_flash_message('error', 'Unauthorized access.');
    redirect('login.php');
}

// Handle content review actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    
    if ($content_id > 0) {
        switch ($action) {
            case 'approve':
                $update_query = "UPDATE content_reviews SET 
                                status = 'approved',
                                reviewed_by = ?,
                                reviewed_at = NOW(),
                                review_notes = ?
                                WHERE id = ?";
                db_query($update_query, [$_SESSION['user_id'], $reason, $content_id]);
                
                // Log activity
                log_activity('content_review', 'Approved content #' . $content_id);
                set_flash_message('success', 'Content approved successfully.');
                break;
                
            case 'reject':
                if (empty($reason)) {
                    set_flash_message('error', 'Please provide a reason for rejection.');
                    break;
                }
                
                $update_query = "UPDATE content_reviews SET 
                                status = 'rejected',
                                reviewed_by = ?,
                                reviewed_at = NOW(),
                                review_notes = ?
                                WHERE id = ?";
                db_query($update_query, [$_SESSION['user_id'], $reason, $content_id]);
                
                // Log activity
                log_activity('content_review', 'Rejected content #' . $content_id . ': ' . $reason);
                set_flash_message('success', 'Content rejected successfully.');
                break;
        }
    }
    
    // Redirect to prevent form resubmission
    redirect('content-review.php');
}

// Get content items for review with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total count for pagination
$total_query = "SELECT COUNT(*) as count FROM content_reviews WHERE status = 'pending'";
$total_items = db_fetch_row($total_query)['count'];
$total_pages = ceil($total_items / $items_per_page);

// Get pending content reviews
$content_query = "SELECT cr.*, u.first_name, u.last_name 
                 FROM content_reviews cr
                 LEFT JOIN users u ON cr.submitted_by = u.id
                 WHERE cr.status = 'pending'
                 ORDER BY cr.created_at ASC
                 LIMIT ? OFFSET ?";
$pending_content = db_fetch_all($content_query, [$items_per_page, $offset]);

// Page title
$page_title = 'Content Review';

// Include header
include 'includes/header.php';

// Include content start
include 'includes/content-start.php';
?>

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Pending Content Reviews</h2>
        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
            <?php echo $total_items; ?> pending
        </span>
    </div>

    <?php if (empty($pending_content)): ?>
    <div class="text-center py-8">
        <i class="ri-check-double-line text-4xl text-gray-400 mb-3"></i>
        <p class="text-gray-500">No pending content to review</p>
    </div>
    <?php else: ?>
    <div class="space-y-6">
        <?php foreach ($pending_content as $content): ?>
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($content['title']); ?></h3>
                    <p class="text-sm text-gray-500">
                        Submitted by <?php echo htmlspecialchars($content['first_name'] . ' ' . $content['last_name']); ?>
                        on <?php echo format_date($content['created_at']); ?>
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                    <?php echo ucfirst($content['content_type']); ?>
                </span>
            </div>
            
            <div class="prose max-w-none mb-4">
                <?php echo nl2br(htmlspecialchars($content['content'])); ?>
            </div>
            
            <div class="flex items-center justify-end space-x-4">
                <button onclick="showRejectModal(<?php echo $content['id']; ?>)" 
                        class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700">
                    <i class="ri-close-circle-line mr-1"></i> Reject
                </button>
                <form action="content-review.php" method="POST" class="inline-block">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">
                <input type="hidden" name="action" value="approve">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="ri-check-line mr-1"></i> Approve
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-6 space-x-2">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" 
           class="px-3 py-1 text-sm <?php echo $i === $page ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'; ?> rounded">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Reject Content</h3>
        <form action="content-review.php" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="content_id" id="rejectContentId">
                <input type="hidden" name="action" value="reject">
            
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                <textarea name="reason" id="reason" rows="3" 
                          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                          required></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideRejectModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-700">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(contentId) {
    document.getElementById('rejectContentId').value = contentId;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('reason').value = '';
}
</script>

<?php
// Include content end
include 'includes/content-end.php';

// Include footer
include 'includes/footer.php';
?>