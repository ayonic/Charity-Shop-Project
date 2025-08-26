<?php
require_once 'includes/dashboard-header.php';

// Mark all notifications as read when viewing the page
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$current_user['id']]);

// Fetch all notifications for the user, ordered by newest first
$stmt = $pdo->prepare("
    SELECT id, title, message, type, is_read, created_at 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$current_user['id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Notifications</h2>
                <span class="text-sm text-gray-500"><?php echo count($notifications); ?> notifications</span>
            </div>

            <?php if (empty($notifications)): ?>
            <div class="text-center py-8">
                <i class="ri-notification-off-line text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-500">No notifications yet</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($notifications as $notification): ?>
                <div class="flex items-start p-4 <?php echo $notification['is_read'] ? 'bg-white' : 'bg-blue-50'; ?> rounded-lg transition hover:bg-gray-50">
                    <div class="flex-shrink-0 pt-1">
                        <?php
                        $icon_class = 'text-2xl ';
                        switch ($notification['type']) {
                            case 'success':
                                $icon_class .= 'text-green-500 ri-checkbox-circle-line';
                                break;
                            case 'warning':
                                $icon_class .= 'text-yellow-500 ri-error-warning-line';
                                break;
                            case 'error':
                                $icon_class .= 'text-red-500 ri-close-circle-line';
                                break;
                            default:
                                $icon_class .= 'text-blue-500 ri-information-line';
                        }
                        ?>
                        <i class="<?php echo $icon_class; ?>"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($notification['title']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?></p>
                        </div>
                        <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($notification['message']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/dashboard-footer.php'; ?>