<?php
require_once '../config/init.php';
require_once INCLUDES_PATH . '/dashboard-header.php';
require_once INCLUDES_PATH . '/session_manager.php';

// Validate user role
validate_user_role('donor', '/login.php');

// Get donor's statistics
$stats_query = "SELECT 
                COUNT(d.id) as total_donations,
                SUM(d.estimated_value) as total_amount,
                COUNT(DISTINCT c.id) as supported_causes,
                MAX(d.created_at) as last_donation
               FROM donations d 
               LEFT JOIN donation_items di ON di.donation_id = d.id 
               LEFT JOIN causes c ON di.category_id = c.id 
               WHERE d.donor_id = ?";
$stats = db_fetch_row($stats_query, [$_SESSION['user_id']]);

// Get recent donations
$donations_query = "SELECT d.*, c.name as cause_name, c.description as cause_description 
                   FROM donations d 
                   LEFT JOIN donation_items di ON di.donation_id = d.id 
                   LEFT JOIN causes c ON di.category_id = c.id 
                   WHERE d.donor_id = ? 
                   ORDER BY d.created_at DESC 
                   LIMIT 5";
$recent_donations = db_fetch_all($donations_query, [$_SESSION['user_id']]);

// Get impact statistics
$impact_query = "SELECT c.name as category, COUNT(d.id) as donation_count, SUM(d.estimated_value) as total_amount 
                FROM donations d 
                JOIN donation_items di ON di.donation_id = d.id 
                JOIN causes c ON di.category_id = c.id 
                WHERE d.donor_id = ? 
                GROUP BY c.name";
$impact_stats = db_fetch_all($impact_query, [$_SESSION['user_id']]);

// Get recommended causes based on donor's interests
$recommended_query = "SELECT c.*, 
                            (SELECT COUNT(*) FROM donation_items di WHERE di.category_id = c.id) as donation_count 
                     FROM causes c 
                     WHERE c.status = 'active' 
                     AND c.id NOT IN (SELECT di.category_id FROM donation_items di JOIN donations d ON di.donation_id = d.id WHERE d.donor_id = ?) 
                     ORDER BY donation_count DESC 
                     LIMIT 3";
$recommended_causes = db_fetch_all($recommended_query, [$_SESSION['user_id']]);
?>

<div class="container px-6 mx-auto grid">
    <!-- Statistics Cards -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Donations</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['total_donations'] ?? 0; ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Amount</p>
                <p class="text-lg font-semibold text-gray-700">£<?php echo number_format($stats['total_amount'] ?? 0, 2); ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Causes Supported</p>
                <p class="text-lg font-semibold text-gray-700"><?php echo $stats['supported_causes'] ?? 0; ?></p>
            </div>
        </div>

        <div class="flex items-center p-4 bg-white rounded-lg shadow-xs">
            <div class="p-3 mr-4 text-teal-500 bg-teal-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Last Donation</p>
                <p class="text-lg font-semibold text-gray-700">
                    <?php echo $stats['last_donation'] ? date('M j, Y', strtotime($stats['last_donation'])) : 'Never'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Recent Donations -->
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Recent Donations</h2>
    <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Cause</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y">
                    <?php if (empty($recent_donations)): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-center text-gray-500">No donations yet</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_donations as $donation): ?>
                            <tr class="text-gray-700">
                                <td class="px-4 py-3">
                                    <div class="flex items-center text-sm">
                                        <div>
                                            <p class="font-semibold"><?php echo htmlspecialchars($donation['cause_name']); ?></p>
                                            <p class="text-xs text-gray-600 truncate w-48"><?php echo htmlspecialchars($donation['cause_description']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">$<?php echo number_format($donation['amount'], 2); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo date('M j, Y', strtotime($donation['created_at'])); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo get_status_badge_class($donation['status']); ?>">
                                        <?php echo ucfirst($donation['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Impact Summary -->
    <h2 class="my-6 text-2xl font-semibold text-gray-700">Your Impact</h2>
    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
            <h4 class="mb-4 font-semibold text-gray-600">Donations by Category</h4>
            <div class="chart-container" style="position: relative; height:200px;">
                <canvas id="impactChart"></canvas>
            </div>
        </div>

        <!-- Recommended Causes -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
            <h4 class="mb-4 font-semibold text-gray-600">Recommended Causes</h4>
            <?php if (empty($recommended_causes)): ?>
                <p class="text-gray-500">No recommendations available</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recommended_causes as $cause): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($cause['name']); ?></p>
                                <p class="text-sm text-gray-600 truncate w-64"><?php echo htmlspecialchars($cause['description']); ?></p>
                            </div>
                            <a href="../causes/donate.php?id=<?php echo $cause['id']; ?>" class="px-3 py-1 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-md active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                                Donate
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('impactChart').getContext('2d');
    const impactData = <?php echo json_encode($impact_stats); ?>;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: impactData.map(item => item.category),
            datasets: [{
                data: impactData.map(item => item.total_amount),
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

<?php require_once INCLUDES_PATH . '/dashboard-footer.php'; ?>