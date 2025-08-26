<?php
/**
 * Email Logs
 * Only accessible by admin and moderator roles.
 */
require_once 'config/init.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'moderator'])) {
    set_flash_message('error', 'You do not have permission to access this page.');
    redirect('login.php');
    exit;
}
$email_logs = db_fetch_all("SELECT * FROM email_logs ORDER BY sent_at DESC LIMIT 100");
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex overflow-hidden bg-gray-100">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="flex-1 overflow-auto focus:outline-none" tabindex="0">
        <main class="flex-1 relative overflow-y-auto focus:outline-none">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Email Logs</h1>
                    </div>
                    <div class="mt-6">
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul role="list" class="divide-y divide-gray-200">
                                <?php foreach ($email_logs as $log): ?>
                                <li>
                                    <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium text-blue-600">
                                                <?php echo htmlspecialchars($log['recipient']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Subject: <?php echo htmlspecialchars($log['subject']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Sent: <?php echo date('M j, Y H:i', strtotime($log['sent_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="view_email_log.php?id=<?php echo $log['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>