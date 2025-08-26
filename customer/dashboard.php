<?php
require_once '../config/init.php';
require_once INCLUDES_PATH . '/dashboard-header.php';
require_once INCLUDES_PATH . '/session_manager.php';

// Validate user role
validate_user_role('customer', '../login.php');

// Get customer's statistics
$stats_query = "SELECT 
                COUNT(o.id) as total_orders,
                SUM(o.total_amount) as total_spent,
                COUNT(DISTINCT p.id) as products_bought,
                MAX(o.created_at) as last_order
               FROM orders o 
               LEFT JOIN order_items oi ON o.id = oi.order_id 
               LEFT JOIN inventory p ON oi.item_id = p.id 
               WHERE o.user_id = ?";
$stats = db_fetch_row($stats_query, [$_SESSION['user_id']]);

// Get recent orders
$orders_query = "SELECT o.*, 
                        COUNT(oi.id) as item_count,
                        GROUP_CONCAT(p.name SEPARATOR ', ') as product_names 
                 FROM orders o 
                 LEFT JOIN order_items oi ON o.id = oi.order_id 
                 LEFT JOIN inventory p ON oi.item_id = p.id 
                 WHERE o.user_id = ? 
                 GROUP BY o.id 
                 ORDER BY o.created_at DESC 
                 LIMIT 5";
$recent_orders = db_fetch_all($orders_query, [$_SESSION['user_id']]);

// Get purchase history by category
$category_query = "SELECT c.name as category, 
                         COUNT(oi.id) as purchase_count, 
                         SUM(oi.quantity * oi.unit_price) as total_amount 
                  FROM orders o 
                  JOIN order_items oi ON o.id = oi.order_id 
                  JOIN products p ON oi.product_id = p.id 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE o.user_id = ? 
                  GROUP BY c.id";
$category_stats = db_fetch_all($category_query, [$_SESSION['user_id']]);

// Get recommended products based on purchase history
$recommended_query = "SELECT p.*, 
                            (SELECT COUNT(*) FROM order_items WHERE product_id = p.id) as purchase_count 
                     FROM inventory p 
                     WHERE p.public_visible = 1 
                     AND p.category_id IN (SELECT DISTINCT p2.category_id 
                                          FROM orders o2 
                                          JOIN order_items oi2 ON o2.id = oi2.order_id 
                                          JOIN inventory p2 ON oi2.item_id = p2.id 
                                          WHERE o2.user_id = ?) 
                     AND p.id NOT IN (SELECT oi3.item_id 
                                     FROM orders o3 
                                     JOIN order_items oi3 ON o3.id = oi3.order_id 
                                     WHERE o3.user_id = ?) 
                     ORDER BY purchase_count DESC 
                     LIMIT 3";
$recommended_products = db_fetch_all($recommended_query, [$_SESSION['user_id'], $_SESSION['user_id']]);

?>

<div class="container px-2 mx-auto grid">
    <!-- Statistics Cards -->
    <div class="grid gap-4 mb-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Orders</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['total_orders'] ?? 0; ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Spent</p>
                <p class="text-lg font-semibold text-gray-700">$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Products Bought</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['products_bought'] ?? 0; ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Last Order</p>
                <p class="text-lg font-semibold text-gray-700">
                    <?php echo $stats['last_order'] ? date('M j, Y', strtotime($stats['last_order'])) : 'Never'; ?>
                </p>
            </div>
        </div>
    </div>

   

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = <?php echo json_encode($category_stats); ?>;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.category),
            datasets: [{
                data: categoryData.map(item => item.total_amount),
                backgroundColor: [
                    '#7e3af2',
                    '#047481',
                    '#0e9f6e',
                    '#ff5a1f',
                    '#e02424'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });
});
</script>
 <!-- Purchase History -->
    <h2 class="my-4 text-xl font-semibold text-gray-700">Purchase History</h2>
    <div class="grid gap-4 mb-6 md:grid-cols-2">
        <div class="min-w-0 p-3 bg-white rounded-lg shadow-xs">
            <h4 class="mb-2 font-semibold text-gray-600">Purchases by Category</h4>
            <div class="chart-container" style="position: relative; height:180px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <!-- Recommended Products -->
        <div class="min-w-0 p-3 bg-white rounded-lg shadow-xs">
            <h4 class="mb-2 font-semibold text-gray-600">Recommended Products</h4>
            <?php if (empty($recommended_products)): ?>
                <p class="text-gray-500">No recommendations available</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recommended_products as $product): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($product['name']); ?></p>
                                <p class="text-sm text-gray-600 truncate w-64"><?php echo htmlspecialchars($product['description']); ?></p>
                            </div>
                            <a href="../shop/product.php?id=<?php echo $product['id']; ?>"
                               class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                View Product
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Recent Orders -->
    <h2 class="my-4 text-xl font-semibold text-gray-700">Recent Orders</h2>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-4">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Order ID</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-center text-gray-500">No orders yet</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr class="text-gray-700">
                                <td class="px-4 py-3 text-sm">#<?php echo $order['id']; ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold"><?php echo $order['item_count']; ?> items</p>
                                            <p class="text-xs text-gray-600 truncate w-48"><?php echo htmlspecialchars($order['product_names']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo get_status_badge_class($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>"
                                       class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once INCLUDES_PATH . '/dashboard-footer.php'; ?>