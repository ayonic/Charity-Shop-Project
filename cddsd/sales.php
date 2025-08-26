<?php
/**
 * Sales & POS Page
 * 
 * This page handles sales transactions and point of sale functionality.
 */

// Include initialization file
require_once 'config/init.php';

// Require login
require_login();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'process_sale':
                $sale_data = [
                    'sale_date' => date('Y-m-d H:i:s'),
                    'user_id' => $_SESSION['user_id'],
                    'customer_name' => sanitize_input($_POST['customer_name']),
                    'customer_email' => sanitize_input($_POST['customer_email']),
                    'customer_phone' => sanitize_input($_POST['customer_phone']),
                    'subtotal' => (float) $_POST['subtotal'],
                    'tax' => (float) $_POST['tax'],
                    'discount' => (float) $_POST['discount'],
                    'total_amount' => (float) $_POST['total_amount'],
                    'payment_method' => sanitize_input($_POST['payment_method']),
                    'payment_reference' => sanitize_input($_POST['payment_reference']),
                    'notes' => sanitize_input($_POST['notes'])
                ];
                
                $sale_id = db_insert('sales', $sale_data);
                
                if ($sale_id) {
                    // Process sale items
                    $items = json_decode($_POST['items'], true);
                    $all_items_processed = true;
                    
                    foreach ($items as $item) {
                        $sale_item_data = [
                            'sale_id' => $sale_id,
                            'inventory_id' => $item['inventory_id'],
                            'name' => $item['name'],
                            'sku' => $item['sku'],
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                            'subtotal' => $item['subtotal']
                        ];
                        
                        if (db_insert('sale_items', $sale_item_data)) {
                            // Update inventory quantity
                            $current_item = get_inventory_item($item['inventory_id']);
                            $new_quantity = $current_item['quantity'] - $item['quantity'];
                            db_update('inventory', ['quantity' => $new_quantity], "id = {$item['inventory_id']}");
                        } else {
                            $all_items_processed = false;
                        }
                    }
                    
                    if ($all_items_processed) {
                        set_flash_message('success', 'Sale processed successfully!');
                    } else {
                        set_flash_message('warning', 'Sale processed but some items may not have been updated.');
                    }
                } else {
                    set_flash_message('error', 'Failed to process sale.');
                }
                break;
        }
        
        redirect('sales.php');
    }
}

// Get recent sales
$recent_sales = get_sales(10);

// Get available inventory for POS
$available_inventory = get_inventory_items(null, 0, 'quantity > 0');

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Sales & POS</h1>
            <p class="mt-1 text-sm text-gray-600">Process sales and manage transactions</p>
        </div>
        <button data-modal-trigger data-modal-target="newSaleModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
            <i class="ri-shopping-cart-line -ml-1 mr-2"></i>
            New Sale
        </button>
    </div>
</div>

<!-- Sales Stats -->
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Today's Sales</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo format_currency(get_dashboard_stats()['sales_today']); ?></dd>
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
                        <dd class="text-lg font-medium text-gray-900"><?php echo count($recent_sales); ?></dd>
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Avg. Sale</dt>
                        <dd class="text-lg font-medium text-gray-900">
                            <?php 
                            $total = array_sum(array_column($recent_sales, 'total_amount'));
                            $avg = count($recent_sales) > 0 ? $total / count($recent_sales) : 0;
                            echo format_currency($avg);
                            ?>
                        </dd>
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Available Items</dt>
                        <dd class="text-lg font-medium text-gray-900"><?php echo count($available_inventory); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Sales -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Recent Sales</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($recent_sales)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No sales found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_sales as $sale): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo format_datetime($sale['sale_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?>
                                </div>
                                <div class="text-sm text-gray-500"><?php echo $sale['customer_email']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo format_currency($sale['total_amount']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo ucfirst($sale['payment_method']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $sale['first_name'] . ' ' . $sale['last_name']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="viewSaleDetails(<?php echo $sale['id']; ?>)" class="text-primary hover:text-indigo-900">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Initialize cart and constants
let cart = [];
const TAX_RATE = 0.20; // 20% tax rate

// Function to format currency
function format_currency(amount) {
    return '£' + parseFloat(amount).toFixed(2);
}

// Function to add item to cart
function addToCart(element) {
    const item = {
        inventory_id: parseInt(element.dataset.id),
        name: element.dataset.name,
        sku: element.dataset.sku,
        price: parseFloat(element.dataset.price),
        available_quantity: parseInt(element.dataset.quantity),
        quantity: 1,
        subtotal: parseFloat(element.dataset.price)
    };
    
    const existingIndex = cart.findIndex(cartItem => cartItem.inventory_id === item.inventory_id);
    
    if (existingIndex !== -1) {
        if (cart[existingIndex].quantity < item.available_quantity) {
            cart[existingIndex].quantity++;
            cart[existingIndex].subtotal = cart[existingIndex].quantity * cart[existingIndex].price;
        } else {
            alert('Not enough stock available');
            return;
        }
    } else {
        cart.push(item);
    }
    
    updateCartDisplay();
}
</script>

<!-- New Sale Modal -->
<div id="newSaleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-5 mx-auto p-5 border w-11/12 md:w-5/6 lg:w-4/5 shadow-lg rounded-md bg-white" data-modal-overlay>
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">New Sale</h3>
                <button data-modal-close class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Product Selection -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Select Items</h4>
                    <div class="mb-4">
                        <input type="text" id="productSearch" placeholder="Search products..." 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                    <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
                        <div id="productList" class="divide-y divide-gray-200">
                            <?php foreach ($available_inventory as $item): ?>
                                <div class="p-3 hover:bg-gray-50 cursor-pointer product-item" 
                                     data-id="<?php echo $item['id']; ?>"
                                     data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                     data-sku="<?php echo htmlspecialchars($item['sku']); ?>"
                                     data-price="<?php echo $item['price']; ?>"
                                     data-quantity="<?php echo $item['quantity']; ?>"
                                     onclick="addToCart(this)">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['sku']); ?> • Qty: <?php echo $item['quantity']; ?></div>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo format_currency($item['price']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Cart and Checkout -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Shopping Cart</h4>
                    <div class="border border-gray-200 rounded-md p-4 mb-4">
                        <div id="cartItems" class="space-y-2 mb-4">
                            <div class="text-sm text-gray-500 text-center py-4">No items in cart</div>
                        </div>
                        
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span id="subtotal">£0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Tax (8%):</span>
                                <span id="tax">£0.00</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Discount:</span>
                                <span id="discount">£0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-medium border-t pt-2">
                                <span>Total:</span>
                                <span id="total">£0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" id="saleForm" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="process_sale">
                        <input type="hidden" name="items" id="cartItemsData">
                        <input type="hidden" name="subtotal" id="subtotalInput">
                        <input type="hidden" name="tax" id="taxInput">
                        <input type="hidden" name="discount" id="discountInput">
                        <input type="hidden" name="total_amount" id="totalInput">
                        
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                                <input type="text" name="customer_name" id="customer_name" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label for="customer_email" class="block text-sm font-medium text-gray-700">Customer Email</label>
                                <input type="email" name="customer_email" id="customer_email" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="customer_phone" class="block text-sm font-medium text-gray-700">Customer Phone</label>
                                <input type="tel" name="customer_phone" id="customer_phone" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select name="payment_method" id="payment_method" required 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700">Payment Reference</label>
                            <input type="text" name="payment_reference" id="payment_reference" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="2" 
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" data-modal-close class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Cancel
                            </button>
                            <button type="submit" id="processSaleBtn" disabled class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:bg-gray-300 disabled:cursor-not-allowed">
                                Process Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
const TAX_RATE = 0.08;

function addToCart(element) {
    const item = {
        inventory_id: parseInt(element.dataset.id),
        name: element.dataset.name,
        sku: element.dataset.sku,
        price: parseFloat(element.dataset.price),
        available_quantity: parseInt(element.dataset.quantity),
        quantity: 1,
        subtotal: parseFloat(element.dataset.price)
    };
    
    // Check if item already in cart
    const existingIndex = cart.findIndex(cartItem => cartItem.inventory_id === item.inventory_id);
    
    if (existingIndex >= 0) {
        // Increase quantity if available
        if (cart[existingIndex].quantity < item.available_quantity) {
            cart[existingIndex].quantity++;
            cart[existingIndex].subtotal = cart[existingIndex].quantity * cart[existingIndex].price;
        } else {
            alert('Not enough stock available');
            return;
        }
    } else {
        cart.push(item);
    }
    
    updateCartDisplay();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function updateQuantity(index, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(index);
        return;
    }
    
    if (newQuantity > cart[index].available_quantity) {
        alert('Not enough stock available');
        return;
    }
    
    cart[index].quantity = newQuantity;
    cart[index].subtotal = cart[index].quantity * cart[index].price;
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItemsDiv = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<div class="text-sm text-gray-500 text-center py-4">No items in cart</div>';
        document.getElementById('processSaleBtn').disabled = true;
    } else {
        let html = '';
        cart.forEach((item, index) => {
            html += `
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <div class="flex-1">
                        <div class="text-sm font-medium">${item.name}</div>
                        <div class="text-xs text-gray-500">${item.sku}</div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="number" value="${item.quantity}" min="1" max="${item.available_quantity}" 
                               onchange="updateQuantity(${index}, parseInt(this.value))"
                               class="w-16 text-center border-gray-300 rounded text-sm">
                        <span class="text-sm">× ${item.price.toFixed(2)}</span>
                        <span class="text-sm font-medium w-16 text-right">$${item.subtotal.toFixed(2)}</span>
                        <button onclick="removeFromCart(${index})" class="text-red-600 hover:text-red-800">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        cartItemsDiv.innerHTML = html;
        document.getElementById('processSaleBtn').disabled = false;
    }
    
    // Calculate totals
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const tax = subtotal * TAX_RATE;
    const discount = 0; // Could be implemented later
    const total = subtotal + tax - discount;
    
    // Update display
    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('discount').textContent = `$${discount.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;
    
    // Update hidden form fields
    document.getElementById('cartItemsData').value = JSON.stringify(cart);
    document.getElementById('subtotalInput').value = subtotal.toFixed(2);
    document.getElementById('taxInput').value = tax.toFixed(2);
    document.getElementById('discountInput').value = discount.toFixed(2);
    document.getElementById('totalInput').value = total.toFixed(2);
}

// Product search functionality
document.getElementById('productSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const productItems = document.querySelectorAll('.product-item');
    
    productItems.forEach(item => {
        const name = item.dataset.name.toLowerCase();
        const sku = item.dataset.sku.toLowerCase();
        
        if (name.includes(searchTerm) || sku.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

function viewSaleDetails(saleId) {
    // In a real application, this would open a modal with sale details
    alert('Sale details for sale ID: ' + saleId);
}

// Reset cart when modal is closed
document.addEventListener('click', function(event) {
    if (event.target.id === 'newSaleModal') {
        cart = [];
        updateCartDisplay();
        document.getElementById('saleForm').reset();
    }
});
</script>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>
