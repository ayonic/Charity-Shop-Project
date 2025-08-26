<?php
/**
 * Dashboard Footer Template
 * 
 * This file contains the common footer elements for all role-specific dashboards.
 * It includes closing tags, common scripts, and footer content.
 */
?>
            </main>

            <footer class="bg-white border-t border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">&copy; <?php echo date('Y'); ?> Charity Shop Management System. All rights reserved.</p>
                        <div class="flex items-center space-x-4">
                            <a href="terms.php" class="text-sm text-gray-600 hover:text-gray-900">Terms of Service</a>
                            <a href="privacy.php" class="text-sm text-gray-600 hover:text-gray-900">Privacy Policy</a>
                            <a href="help.php" class="text-sm text-gray-600 hover:text-gray-900">Help Center</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Common Dashboard Scripts -->
    <script>
        // Initialize notifications dropdown
        document.addEventListener('DOMContentLoaded', function() {
            // Add any common dashboard JavaScript functionality here
            console.log('Dashboard initialized');
        });

        // Handle sidebar toggle for mobile
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebar = document.querySelector('[data-sidebar]');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('translate-x-0');
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Handle notification clicks
        document.querySelectorAll('[data-notification]').forEach(notification => {
            notification.addEventListener('click', async (e) => {
                const notificationId = notification.dataset.notificationId;
                try {
                    const response = await fetch('api/notifications/mark-read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ notification_id: notificationId })
                    });
                    if (response.ok) {
                        notification.classList.remove('bg-blue-50');
                        notification.classList.add('bg-white');
                    }
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            });
        });
    </script>
</body>
</html>