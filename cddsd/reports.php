<?php
/**
 * Reports & Analytics Page
 * 
 * This page displays various reports and analytics for the charity shop.
 */

// Include initialization file
require_once 'config/init.php';

// Require login
require_login();

// Get date range from query parameters
$start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : date('Y-m-d');

// Get report data
$sales_data = db_fetch_all("
    SELECT DATE(sale_date) as date, SUM(total_amount) as total, COUNT(*) as count 
    FROM sales 
    WHERE sale_date BETWEEN '{$start_date}' AND '{$end_date} 23:59:59'
    GROUP BY DATE(sale_date) 
    ORDER BY date
");

$donations_data = db_fetch_all("
    SELECT DATE(donation_date) as date, SUM(estimated_value) as total, COUNT(*) as count 
    FROM donations 
    WHERE donation_date BETWEEN '{$start_date}' AND '{$end_date} 23:59:59'
    GROUP BY DATE(donation_date) 
    ORDER BY date
");

$category_sales = db_fetch_all("
    SELECT c.name, SUM(si.total_price) as total, COUNT(si.id) as count
    FROM sale_items si
    JOIN inventory i ON si.inventory_id = i.id
    JOIN categories c ON i.category_id = c.id
    JOIN sales s ON si.sale_id = s.id
    WHERE s.sale_date BETWEEN '{$start_date}' AND '{$end_date} 23:59:59'
    GROUP BY c.id
    ORDER BY total DESC
");

$top_items = db_fetch_all("
    SELECT i.name, i.sku, SUM(si.quantity) as sold_quantity, SUM(si.total_price) as total_revenue
    FROM sale_items si
    JOIN inventory i ON si.inventory_id = i.id
    JOIN sales s ON si.sale_id = s.id
    WHERE s.sale_date BETWEEN '{$start_date}' AND '{$end_date} 23:59:59'
    GROUP BY i.id
    ORDER BY sold_quantity DESC
    LIMIT 10
");

$volunteer_stats = db_fetch_all("
    SELECT u.first_name, u.last_name, SUM(vh.hours_worked) as total_hours, COUNT(vh.id) as sessions
    FROM volunteer_hours vh
    JOIN users u ON vh.user_id = u.id
    WHERE vh.work_date BETWEEN '{$start_date}' AND '{$end_date}'
    GROUP BY u.id
    ORDER BY total_hours DESC
    LIMIT 10
");

// Calculate summary statistics
$total_sales = array_sum(array_column($sales_data, 'total'));
$total_transactions = array_sum(array_column($sales_data, 'count'));
$total_donations_value = array_sum(array_column($donations_data, 'total'));
$total_donations_count = array_sum(array_column($donations_data, 'count'));

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Reports & Analytics</h1>
            <p class="mt-1 text-sm text-gray-600">View detailed reports and analytics for your charity shop</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="exportReport()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="ri-download-line -ml-1 mr-2"></i>
                Export
            </button>
            <button onclick="printReport()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <i class="ri-printer-line -ml-1 mr-2"></i>
                Print
            </button>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="bg-white shadow rounded-lg mb-8">
    <div class="px-6 py-4">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <i class="ri-search-line -ml-1 mr-2"></i>
                    Update Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Sales</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo format_currency($total_sales); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <i class="ri-shopping-bag-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Transactions</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $total_transactions; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <i class="ri-gift-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Donations Value</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo format_currency($total_donations_value); ?></dd>
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
                        <i class="ri-bar-chart-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Avg Transaction</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            <?php echo $total_transactions > 0 ? format_currency($total_sales / $total_transactions) : '£0.00'; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Sales Trend Chart -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales Trend</h3>
        <div id="salesTrendChart" style="height: 300px;"></div>
    </div>

    <!-- Category Performance Chart -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales by Category</h3>
        <div id="categoryChart" style="height: 300px;"></div>
    </div>
</div>

<!-- Data Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Top Selling Items -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Selling Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($top_items)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No sales data available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($top_items as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['sku']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $item['sold_quantity']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo format_currency($item['total_revenue']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Volunteers -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Volunteers by Hours</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volunteer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($volunteer_stats)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No volunteer data available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($volunteer_stats as $volunteer): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $volunteer['first_name'] . ' ' . $volunteer['last_name']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo number_format($volunteer['total_hours'], 1); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $volunteer['sessions']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    const salesTrendChart = echarts.init(document.getElementById('salesTrendChart'));
    const salesData = <?php echo json_encode($sales_data); ?>;
    
    const salesOption = {
        tooltip: {
            trigger: 'axis',
            formatter: function(params) {
                return params[0].name + '<br/>' +
                       'Sales: $' + params[0].value.toFixed(2) + '<br/>' +
                       'Transactions: ' + params[1].value;
            }
        },
        legend: {
            data: ['Sales Amount', 'Transaction Count']
        },
        xAxis: {
            type: 'category',
            data: salesData.map(item => item.date)
        },
        yAxis: [
            {
                type: 'value',
                name: 'Sales ($)',
                position: 'left'
            },
            {
                type: 'value',
                name: 'Transactions',
                position: 'right'
            }
        ],
        series: [
            {
                name: 'Sales Amount',
                type: 'line',
                data: salesData.map(item => parseFloat(item.total)),
                itemStyle: {
                    color: '#4F46E5'
                }
            },
            {
                name: 'Transaction Count',
                type: 'bar',
                yAxisIndex: 1,
                data: salesData.map(item => parseInt(item.count)),
                itemStyle: {
                    color: '#10B981'
                }
            }
        ]
    };
    
    salesTrendChart.setOption(salesOption);
    
    // Category Chart
    const categoryChart = echarts.init(document.getElementById('categoryChart'));
    const categoryData = <?php echo json_encode($category_sales); ?>;
    
    const categoryOption = {
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: ${c} ({d}%)'
        },
        series: [{
            name: 'Sales by Category',
            type: 'pie',
            radius: '50%',
            data: categoryData.map(item => ({
                value: parseFloat(item.total),
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
    
    categoryChart.setOption(categoryOption);
    
    // Resize charts on window resize
    window.addEventListener('resize', function() {
        salesTrendChart.resize();
        categoryChart.resize();
    });
});

function exportReport() {
    // In a real application, this would generate and download a report file
    alert('Export functionality would be implemented here');
}

function printReport() {
    window.print();
}
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
