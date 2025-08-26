<?php
/**
 * Donations Management Page
 * 
 * This page handles donation management including viewing, processing, and adding donations.
 */

// Include initialization file
require_once 'config/init.php';

// Require login and admin role
require_login();

// Check if user has admin role
if (!has_role('admin')) {
    set_flash_message('error', 'Access denied. Only administrators can access this page.');
    redirect('index.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_donation':
                $data = [
                    'donor_id' => !empty($_POST['donor_id']) ? (int) $_POST['donor_id'] : null,
                    'donation_date' => sanitize_input($_POST['donation_date']),
                    'estimated_value' => (float) $_POST['estimated_value'],
                    'status' => sanitize_input($_POST['status']),
                    'notes' => sanitize_input($_POST['notes']),
                    'receipt_number' => sanitize_input($_POST['receipt_number'])
                ];
                
                if (db_insert('donations', $data)) {
                    set_flash_message('success', 'Donation added successfully!');
                } else {
                    set_flash_message('error', 'Failed to add donation.');
                }
                break;
                
            case 'update_donation_status':
                $donation_id = (int) $_POST['donation_id'];
                $status = sanitize_input($_POST['status']);
                
                if (db_update('donations', ['status' => $status], "id = {$donation_id}")) {
                    set_flash_message('success', 'Donation status updated successfully!');
                } else {
                    set_flash_message('error', 'Failed to update donation status.');
                }
                break;
                
            case 'process_donation_item':
                $item_id = (int) $_POST['item_id'];
                $donation_item = db_fetch_row("SELECT * FROM donation_items WHERE id = {$item_id}");
                
                if ($donation_item) {
                    // Create inventory item
                    $inventory_data = [
                        'name' => $donation_item['name'],
                        'category_id' => $donation_item['category_id'],
                        'condition' => $donation_item['condition'],
                        'price' => (float) $_POST['price'],
                        'quantity' => $donation_item['quantity'],
                        'low_stock_threshold' => 5,
                        'description' => $donation_item['description'],
                        'donation_id' => $donation_item['donation_id'],
                        'location' => sanitize_input($_POST['location'])
                    ];
                    
                    $inventory_id = db_insert('inventory', $inventory_data);
                    
                    if ($inventory_id) {
                        // Generate SKU
                        $category = get_category($donation_item['category_id']);
                        $sku = generate_sku($category['code'], $inventory_id);
                        db_update('inventory', ['sku' => $sku], "id = {$inventory_id}");
                        
                        // Mark donation item as processed
                        db_update('donation_items', ['processed' => 1, 'inventory_id' => $inventory_id], "id = {$item_id}");
                        
                        set_flash_message('success', 'Donation item processed successfully!');
                    } else {
                        set_flash_message('error', 'Failed to process donation item.');
                    }
                }
                break;
        }
        
        redirect('donations.php');
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build where clause for donations
$where_conditions = [];

if ($status_filter) {
    $where_conditions[] = "d.status = '{$status_filter}'";
}

if ($search) {
    $search_escaped = db_escape($search);
    $where_conditions[] = "(u.first_name LIKE '%{$search_escaped}%' OR u.last_name LIKE '%{$search_escaped}%' OR u.email LIKE '%{$search_escaped}%' OR d.receipt_number LIKE '%{$search_escaped}%')";
}

$where_clause = !empty($where_conditions) ? implode(' AND ', $where_conditions) : '';

// Get donations
$donations = get_donations(null, 0);
if ($where_clause) {
    $query = "SELECT d.*, u.first_name, u.last_name, u.email 
              FROM donations d 
              LEFT JOIN users u ON d.donor_id = u.id 
              WHERE {$where_clause}
              ORDER BY d.donation_date DESC";
    $donations = db_fetch_all($query);
}

// Get donors for dropdown
$donors = db_fetch_all("SELECT u.* FROM users u INNER JOIN roles r ON u.role_id = r.id WHERE r.name = 'donor' ORDER BY u.last_name, u.first_name");

// Get categories for processing items
$categories = get_categories();

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Donations Management</h1>
            <p class="mt-1 text-sm text-gray-600">Track and manage donations from your community</p>
        </div>
        <button data-modal-trigger data-modal-target="addDonationModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            <i class="ri-add-line -ml-1 mr-2"></i>
            Add Donation
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Donations</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo get_dashboard_stats()['total_donations']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <i class="ri-time-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo get_dashboard_stats()['pending_donations']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

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
                        <dt class="text-sm font-medium text-gray-500 truncate">Value This Month</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo format_currency(get_dashboard_stats()['value_this_month']); ?></dd>
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
                        <i class="ri-bar-chart-line text-white"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Average Value</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo format_currency(get_dashboard_stats()['average_donation_value']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white shadow rounded-lg mb-8">
    <div class="px-6 py-4">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" 
                       placeholder="Search donations...">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processed" <?php echo $status_filter === 'processed' ? 'selected' : ''; ?>>Processed</option>
                    <option value="declined" <?php echo $status_filter === 'declined' ? 'selected' : ''; ?>>Declined</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <i class="ri-search-line -ml-1 mr-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Donations Table -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Donations</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($donations)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No donations found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $donation['first_name'] ? $donation['first_name'] . ' ' . $donation['last_name'] : 'Anonymous'; ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo $donation['email']; ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo format_datetime($donation['donation_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo format_currency($donation['estimated_value']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo get_status_label_class($donation['status']); ?>">
                                    <?php echo ucfirst($donation['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $donation['receipt_number']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="viewDonationItems(<?php echo $donation['id']; ?>)" class="text-primary hover:text-indigo-900 mr-3">
                                    <i class="ri-eye-line"></i>
                                </button>
                                <button onclick="updateDonationStatus(<?php echo $donation['id']; ?>, '<?php echo $donation['status']; ?>')" class="text-green-600 hover:text-green-900">
                                    <i class="ri-edit-line"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Donation Modal -->
<div id="addDonationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Donation</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add_donation">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="add_donor_id" class="block text-sm font-medium text-gray-700">Donor</label>
                        <select name="donor_id" id="add_donor_id" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="">Anonymous Donor</option>
                            <?php foreach ($donors as $donor): ?>
                                <option value="<?php echo $donor['id']; ?>"><?php echo $donor['first_name'] . ' ' . $donor['last_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="add_donation_date" class="block text-sm font-medium text-gray-700">Donation Date</label>
                        <input type="datetime-local" name="donation_date" id="add_donation_date" required 
                               value="<?php echo date('Y-m-d\TH:i'); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="add_estimated_value" class="block text-sm font-medium text-gray-700">Estimated Value</label>
                        <input type="number" name="estimated_value" id="add_estimated_value" step="0.01" min="0" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="add_status" required 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="pending">Pending</option>
                            <option value="processed">Processed</option>
                            <option value="declined">Declined</option>
                        </select>
                    </div>
                    <div>
                        <label for="add_receipt_number" class="block text-sm font-medium text-gray-700">Receipt Number</label>
                        <input type="text" name="receipt_number" id="add_receipt_number" 
                               value="DON-<?php echo date('Y'); ?>-<?php echo str_pad(count($donations) + 1, 3, '0', STR_PAD_LEFT); ?>"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="add_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="add_notes" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Add Donation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Update Status</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="delete_donation">
                <input type="hidden" name="donation_id" id="update_donation_id">
                
                <div class="mb-4">
                    <label for="update_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="update_status" required 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                        <option value="pending">Pending</option>
                        <option value="processed">Processed</option>
                        <option value="declined">Declined</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Donation Items Modal -->
<div id="viewItemsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Donation Items</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div id="donationItemsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
function updateDonationStatus(donationId, currentStatus) {
    document.getElementById('update_donation_id').value = donationId;
    document.getElementById('update_status').value = currentStatus;
    document.getElementById('updateStatusModal').classList.remove('hidden');
}

function viewDonationItems(donationId) {
    // Show loading
    document.getElementById('donationItemsContent').innerHTML = '<div class="text-center py-4">Loading...</div>';
    document.getElementById('viewItemsModal').classList.remove('hidden');
    
    // Fetch donation items (in a real application, this would be an AJAX call)
    // For now, we'll simulate with a simple message
    setTimeout(() => {
        document.getElementById('donationItemsContent').innerHTML = `
            <div class="text-center py-8">
                <p class="text-gray-500">Donation items would be displayed here.</p>
                <p class="text-sm text-gray-400 mt-2">This would typically load via AJAX from a separate endpoint.</p>
            </div>
        `;
    }, 500);
}
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
