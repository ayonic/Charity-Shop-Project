<?php
/**
 * My Orders Page (Customer)
 * 
 * This page displays all orders for the logged-in customer.
 */

// Include customer header
require_once '../includes/customer-header.php';

// Get all orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total orders count
$count_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$total_orders = db_fetch_row($count_query, [$_SESSION['user_id']]);
$total_pages = ceil($total_orders['total'] / $per_page);

// Get orders for current page
$orders_query = "SELECT o.*, COUNT(oi.id) as items_count 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = ? 
                GROUP BY o.id 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";
$orders = db_fetch_all($orders_query, [$_SESSION['user_id'], $per_page, $offset]);

// Page title
$page_title = 'My Orders';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-gray-800">My Orders</h1>
            <p class="text-gray-600 mt-2">View and track all your orders</p>
        </div>

        <?php if (empty($orders)): ?>
        <div class="p-6 text-center">
            <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p>
            <a href="../shop.php" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition-colors">
                Start Shopping
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">#<?php echo $order['id']; ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500"><?php echo format_date($order['created_at']); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500"><?php echo $order['items_count']; ?> items</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full <?php echo get_order_status_class($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <a href="../order-details.php?id=<?php echo $order['id']; ?>" class="text-primary hover:text-primary-dark">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_orders['total']); ?> of <?php echo $total_orders['total']; ?> orders
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">&larr; Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Next &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
// Include content end
include '../includes/content-end.php';

// Include footer
include '../includes/footer.php';
?>