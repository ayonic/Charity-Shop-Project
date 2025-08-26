<?php
/**
 * Cashier Dashboard Page
 *
 * This page is for users with the cashier role only.
 */
require_once '../config/init.php';
require_once '../includes/session_manager.php';

// Validate user role
validate_user_role('cashier', '../login.php');

// Get cashier-specific stats (reuse admin stats for now)
$stats = get_dashboard_stats();
$recent_sales = get_sales(5);
$low_stock_items = get_inventory_items(5, 0, 'quantity <= low_stock_threshold AND quantity > 0');

include_once INCLUDES_PATH . '/header.php';
include_once INCLUDES_PATH . '/sidebar.php';
include_once INCLUDES_PATH . '/content-start.php';
?>

<div class="mb-8">
    <h1 class="text-2xl font-semibold text-gray-900">Cashier Dashboard</h1>
    <p class="mt-1 text-sm text-gray-600">Welcome! Here are your cashier tools and recent sales activity.</p>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <i class="ri-money-pound-circle-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Sales Today</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo format_currency($stats['sales_today']); ?></dd>
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
                        <i class="ri-store-2-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Stock Items</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['stock_items']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Sales</h3>
        <div class="divide-y divide-gray-200">
            <?php if (empty($recent_sales)): ?>
                <div class="px-6 py-4 text-sm text-gray-500">No recent sales</div>
            <?php else: ?>
                <?php foreach ($recent_sales as $sale): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $sale['customer_name']; ?>
                                </p>
                                <p class="text-sm text-gray-500"><?php echo format_datetime($sale['sale_date']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900"><?php echo format_currency($sale['total_amount']); ?></p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo get_status_label_class($sale['status']); ?>">
                                    <?php echo ucfirst($sale['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Low Stock Items</h3>
        <div class="divide-y divide-gray-200">
            <?php if (empty($low_stock_items)): ?>
                <div class="px-6 py-4 text-sm text-gray-500">No low stock items</div>
            <?php else: ?>
                <?php foreach ($low_stock_items as $item): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $item['name']; ?>
                                </p>
                                <p class="text-sm text-gray-500">SKU: <?php echo $item['sku']; ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>