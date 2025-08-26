<?php
/**
 * Dashboard Page
 * 
 * This is the main dashboard page showing overview statistics and charts.
 */

// Include initialization file
require_once 'config/init.php';

require_once 'includes/session_manager.php';

// Validate user role
validate_user_role('admin', 'login.php');

// Get dashboard statistics
$stats = get_dashboard_stats();

// Get recent activities
$recent_donations = get_donations(5);
$recent_sales = get_sales(5);
$low_stock_items = get_inventory_items(5, 0, 'quantity <= low_stock_threshold AND quantity > 0');

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Dashboard Header -->
<div class="mb-8 flex flex-col items-center justify-center">
    <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
    <p class="mt-1 text-sm text-gray-600">Welcome back! Here's what's happening at your charity shop today.</p>
    <div class="flex flex-row items-center gap-4 mt-4">
        <!-- Role Switcher -->
        <div class="relative inline-block text-left">
            <button id="roleSwitcherButton" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Switch Role
                <i class="ri-arrow-down-s-line ml-2"></i>
            </button>
            <div id="roleSwitcherDropdown" class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <a href="includes/switch_role.php?role=customer" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                        <i class="ri-user-line mr-2"></i>Customer View
                    </a>
                    <a href="includes/switch_role.php?role=donor" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                        <i class="ri-heart-line mr-2"></i>Donor View
                    </a>
                    <a href="includes/switch_role.php?role=volunteer" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                        <i class="ri-team-line mr-2"></i>Volunteer View
                    </a>
                    <a href="includes/switch_role.php?role=manager" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                        <i class="ri-briefcase-line mr-2"></i>Manager View
                    </a>
                    <a href="includes/switch_role.php?role=cashier" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                        <i class="ri-cash-line mr-2"></i>Cashier View
                    </a>
                    <a href="includes/switch_role.php?role=moderator" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                        <i class="ri-shield-check-line mr-2"></i>Moderator View
                    </a>
                </div>
            </div>
        </div>
        <!-- Give Roles Button -->
        <a href="users.php#give-roles" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <i class="ri-user-settings-line mr-2"></i>Give Roles
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <!-- Sales Today -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <i class="ri-money-dollar-circle-line text-white"></i>
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

    <!-- New Donations -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <i class="ri-gift-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">New Donations</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['new_donations']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Volunteers -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <i class="ri-team-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Volunteers</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $stats['active_volunteers']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Items -->
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

<!-- Charts and Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Sales Chart -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Trend (Last 7 Days)</h3>
        <div id="salesChart" style="height: 300px;"></div>
    </div>

    <!-- Donation Categories -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Donation Categories</h3>
        <div id="donationChart" style="height: 300px;"></div>
    </div>
</div>

<!-- Recent Activities -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Recent Donations -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Donations</h3>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (empty($recent_donations)): ?>
                <div class="px-6 py-4 text-sm text-gray-500">No recent donations</div>
            <?php else: ?>
                <?php foreach ($recent_donations as $donation): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $donation['first_name'] . ' ' . $donation['last_name']; ?>
                                </p>
                                <p class="text-sm text-gray-500"><?php echo format_datetime($donation['donation_date']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900"><?php echo format_currency($donation['estimated_value']); ?></p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo get_status_label_class($donation['status']); ?>">
                                    <?php echo ucfirst($donation['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="px-6 py-3 bg-gray-50">
            <a href="donations.php" class="text-sm font-medium text-primary hover:text-indigo-500">View all donations →</a>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Sales</h3>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (empty($recent_sales)): ?>
                <div class="px-6 py-4 text-sm text-gray-500">No recent sales</div>
            <?php else: ?>
                <?php foreach ($recent_sales as $sale): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?>
                                </p>
                                <p class="text-sm text-gray-500"><?php echo format_datetime($sale['sale_date']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900"><?php echo format_currency($sale['total_amount']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo ucfirst($sale['payment_method']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="px-6 py-3 bg-gray-50">
            <a href="sales.php" class="text-sm font-medium text-primary hover:text-indigo-500">View all sales →</a>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Low Stock Alert</h3>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (empty($low_stock_items)): ?>
                <div class="px-6 py-4 text-sm text-gray-500">No low stock items</div>
            <?php else: ?>
                <?php foreach ($low_stock_items as $item): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900"><?php echo $item['name']; ?></p>
                                <p class="text-sm text-gray-500"><?php echo $item['sku']; ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">Qty: <?php echo $item['quantity']; ?></p>
                                <span class="<?php echo get_status_dot_class(get_inventory_status($item['quantity'], $item['low_stock_threshold'])); ?>"></span>
                                <span class="text-xs text-gray-500"><?php echo ucfirst(str_replace('-', ' ', get_inventory_status($item['quantity'], $item['low_stock_threshold']))); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="px-6 py-3 bg-gray-50">
            <a href="inventory.php" class="text-sm font-medium text-primary hover:text-indigo-500">View inventory →</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesChart = echarts.init(document.getElementById('salesChart'));
    const salesData = <?php echo json_encode(get_daily_sales_trend(7)); ?>;
    
    const salesOption = {
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            type: 'category',
            data: salesData.map(item => item.date)
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            data: salesData.map(item => item.sales_amount),
            type: 'line',
            smooth: true,
            itemStyle: {
                color: '#4F46E5'
            }
        }]
    };
    
    salesChart.setOption(salesOption);
    
    // Donation Categories Chart
    const donationChart = echarts.init(document.getElementById('donationChart'));
    const donationData = <?php echo json_encode(get_donation_categories_distribution()); ?>;
    
    const donationOption = {
        tooltip: {
            trigger: 'item'
        },
        series: [{
            type: 'pie',
            radius: '50%',
            data: donationData.map(item => ({
                value: item.item_count,
                name: item.name
            })),
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    
    donationChart.setOption(donationOption);
    
    // Resize charts on window resize
    window.addEventListener('resize', function() {
        salesChart.resize();
        donationChart.resize();
    });

    // Role Switcher toggle
    const button = document.getElementById('roleSwitcherButton');
    const dropdown = document.getElementById('roleSwitcherDropdown');

    button.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', function(event) {
        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
});
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
