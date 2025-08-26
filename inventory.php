<?php
/**
 * Inventory Management Page
 * 
 * This page handles inventory management including adding, editing, and viewing items.
 */

// Include initialization file
require_once 'config/init.php';

// Require login
require_login();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_item':
                $data = [
                    'name' => sanitize_input($_POST['name']),
                    'category_id' => (int) $_POST['category_id'],
                    'condition' => sanitize_input($_POST['condition']),
                    'price' => (float) $_POST['price'],
                    'quantity' => (int) $_POST['quantity'],
                    'low_stock_threshold' => (int) $_POST['low_stock_threshold'],
                    'description' => sanitize_input($_POST['description']),
                    'location' => sanitize_input($_POST['location'])
                ];
                
                $item_id = db_insert('inventory', $data);
                
                if ($item_id) {
                    // Generate SKU
                    $category = get_category($data['category_id']);
                    $sku = generate_sku($category['code'], $item_id);
                    db_update('inventory', ['sku' => $sku], "id = {$item_id}");
                    
                    set_flash_message('success', 'Item added successfully!');
                } else {
                    set_flash_message('error', 'Failed to add item.');
                }
                break;
                
            case 'update_item':
                $item_id = (int) $_POST['item_id'];
                $data = [
                    'name' => sanitize_input($_POST['name']),
                    'category_id' => (int) $_POST['category_id'],
                    'condition' => sanitize_input($_POST['condition']),
                    'price' => (float) $_POST['price'],
                    'quantity' => (int) $_POST['quantity'],
                    'low_stock_threshold' => (int) $_POST['low_stock_threshold'],
                    'description' => sanitize_input($_POST['description']),
                    'location' => sanitize_input($_POST['location'])
                ];
                
                if (db_update('inventory', $data, "id = {$item_id}")) {
                    set_flash_message('success', 'Item updated successfully!');
                } else {
                    set_flash_message('error', 'Failed to update item.');
                }
                break;
                
            case 'delete_item':
                $item_id = (int) $_POST['item_id'];
                if (db_delete('inventory', "id = {$item_id}")) {
                    set_flash_message('success', 'Item deleted successfully!');
                } else {
                    set_flash_message('error', 'Failed to delete item.');
                }
                break;
        }
        
        redirect('inventory.php');
    }
}

// Get filter parameters
$category_filter = isset($_GET['category']) ? (int) $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build where clause
$where_conditions = [];

if ($category_filter) {
    $where_conditions[] = "i.category_id = {$category_filter}";
}

if ($status_filter) {
    switch ($status_filter) {
        case 'in-stock':
            $where_conditions[] = "i.quantity > i.low_stock_threshold";
            break;
        case 'low-stock':
            $where_conditions[] = "i.quantity > 0 AND i.quantity <= i.low_stock_threshold";
            break;
        case 'out-of-stock':
            $where_conditions[] = "i.quantity <= 0";
            break;
    }
}

if ($search) {
    $search_escaped = db_escape($search);
    $where_conditions[] = "(i.name LIKE '%{$search_escaped}%' OR i.sku LIKE '%{$search_escaped}%' OR i.description LIKE '%{$search_escaped}%')";
}

$where_clause = !empty($where_conditions) ? implode(' AND ', $where_conditions) : '';

// Get inventory items
$inventory_items = get_inventory_items(null, 0, $where_clause);

// Get categories for filter and form
$categories = get_categories();

// Get inventory statistics
$inventory_stats = get_inventory_status_distribution();

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Inventory Management</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your shop's inventory items</p>
        </div>
        <button data-modal-trigger data-modal-target="addItemModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            <i class="ri-add-line -ml-1 mr-2"></i>
            Add Item
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <span class="status-dot in-stock"></span>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">In Stock</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $inventory_stats['in_stock']; ?></dd>
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
                        <span class="status-dot low-stock"></span>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Low Stock</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $inventory_stats['low_stock']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                        <span class="status-dot out-of-stock"></span>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Out of Stock</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo $inventory_stats['out_of_stock']; ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white shadow rounded-lg mb-8">
    <div class="px-6 py-4">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm" 
                       placeholder="Search items...">
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo $category['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    <option value="">All Status</option>
                    <option value="in-stock" <?php echo $status_filter === 'in-stock' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="low-stock" <?php echo $status_filter === 'low-stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="out-of-stock" <?php echo $status_filter === 'out-of-stock' ? 'selected' : ''; ?>>Out of Stock</option>
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

<!-- Inventory Table -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Inventory Items</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($inventory_items)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No items found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($inventory_items as $item): ?>
                        <?php $status = get_inventory_status($item['quantity'], $item['low_stock_threshold']); ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['sku']); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="<?php echo get_category_badge_class(strtolower($item['category_name'])); ?>">
                                    <?php echo htmlspecialchars($item['category_name']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo format_currency($item['price']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $item['quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="<?php echo get_status_dot_class($status); ?>"></span>
                                <span class="text-sm text-gray-900"><?php echo ucfirst(str_replace('-', ' ', $status)); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($item['location'] ?? ''); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item) ?? '{}'); ?>)" class="text-primary hover:text-indigo-900 mr-3">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name'] ?? ''); ?>')" class="text-red-600 hover:text-red-900">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Item Modal -->
<div id="addItemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Item</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add_item">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="add_name" class="block text-sm font-medium text-gray-700">Item Name</label>
                        <input type="text" name="name" id="add_name" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="add_category_id" required 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="add_condition" class="block text-sm font-medium text-gray-700">Condition</label>
                        <select name="condition" id="add_condition" required 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="new">New</option>
                            <option value="good" selected>Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                    <div>
                        <label for="add_price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="price" id="add_price" step="0.01" min="0" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="add_quantity" min="0" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="add_low_stock_threshold" class="block text-sm font-medium text-gray-700">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" id="add_low_stock_threshold" min="0" value="5" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="add_location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" id="add_location" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="add_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="add_description" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="editItemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Item</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="edit_item">
                <input type="hidden" name="item_id" id="edit_item_id">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700">Item Name</label>
                        <input type="text" name="name" id="edit_name" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="edit_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="edit_category_id" required 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="edit_condition" class="block text-sm font-medium text-gray-700">Condition</label>
                        <select name="condition" id="edit_condition" required 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="new">New</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="edit_quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity" id="edit_quantity" min="0" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="edit_low_stock_threshold" class="block text-sm font-medium text-gray-700">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" id="edit_low_stock_threshold" min="0" required 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div>
                        <label for="edit_location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" id="edit_location" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="edit_description" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteItemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="ri-delete-bin-line text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Item</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete "<span id="deleteItemName"></span>"? This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form method="POST" id="deleteItemForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="delete_item">
                    <input type="hidden" name="item_id" id="deleteItemId">
                    <div class="flex justify-center space-x-3">
                        <button type="button" data-modal-close class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('edit_item_id').value = item.id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_category_id').value = item.category_id;
    document.getElementById('edit_condition').value = item.condition;
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_quantity').value = item.quantity;
    document.getElementById('edit_low_stock_threshold').value = item.low_stock_threshold;
    document.getElementById('edit_location').value = item.location || '';
    document.getElementById('edit_description').value = item.description || '';
    
    document.getElementById('editItemModal').classList.remove('hidden');
}

function deleteItem(itemId, itemName) {
    document.getElementById('deleteItemId').value = itemId;
    document.getElementById('deleteItemName').textContent = itemName;
    document.getElementById('deleteItemModal').classList.remove('hidden');
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const modals = ['addItemModal', 'editItemModal', 'deleteItemModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
