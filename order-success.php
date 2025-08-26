<?php
/**
 * Order Success Page
 * 
 * This page displays the order confirmation and details after successful checkout.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';

// Redirect if no order ID
if (!isset($_GET['order_id'])) {
    redirect('shop.php');
}

$order_id = (int)$_GET['order_id'];

// Get order details
$order_query = "SELECT o.*, 
                      u.email as user_email,
                      u.first_name,
                      u.last_name,
                      pm.name as payment_method,
                      pg.name as payment_gateway,
                      pg.code as gateway_code
               FROM orders o
               LEFT JOIN users u ON o.user_id = u.id
               LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
               LEFT JOIN payment_gateways pg ON pm.gateway_id = pg.id
               WHERE o.id = ?";
$order_stmt = $connection->prepare($order_query);
$order_stmt->execute([$order_id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if order not found or doesn't belong to current user
if (!$order || (isset($_SESSION['user_id']) && $order['user_id'] != $_SESSION['user_id'])) {
    redirect('shop.php');
}

// Get order items
$items_query = "SELECT oi.*, i.name, i.image_url 
                FROM order_items oi
                JOIN inventory i ON oi.inventory_id = i.id
                WHERE oi.order_id = ?";
$items_stmt = $connection->prepare($items_query);
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Order Success Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Thank You for Your Order!</h1>
        <p class="text-gray-500 mt-2">Order #<?php echo str_pad($order_id, 8, '0', STR_PAD_LEFT); ?></p>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Order Details -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Shipping Address</h3>
                    <p class="text-gray-900"><?php echo htmlspecialchars($order['shipping_name']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                    <p class="text-gray-600">
                        <?php echo htmlspecialchars($order['shipping_city']); ?>, 
                        <?php echo htmlspecialchars($order['shipping_state']); ?> 
                        <?php echo htmlspecialchars($order['shipping_zip']); ?>
                    </p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($order['shipping_country']); ?></p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Contact Information</h3>
                    <p class="text-gray-900"><?php echo htmlspecialchars($order['shipping_email']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Items</h2>
            <div class="divide-y divide-gray-200">
                <?php foreach ($order_items as $item): ?>
                    <div class="flex items-center py-4">
                        <div class="flex-shrink-0 w-16 h-16">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="w-full h-full object-cover rounded-lg">
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                Qty: <?php echo $item['quantity']; ?>
                            </p>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">
                                <?php echo format_currency($item['price'] * $item['quantity']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Order Summary -->
            <div class="mt-6 border-t border-gray-200 pt-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="text-gray-900"><?php echo format_currency($order['subtotal']); ?></span>
                </div>
                <div class="flex justify-between text-sm mt-2">
                    <span class="text-gray-600">Tax</span>
                    <span class="text-gray-900"><?php echo format_currency($order['tax']); ?></span>
                </div>
                <div class="flex justify-between text-sm mt-2">
                    <span class="text-gray-600">Shipping</span>
                    <span class="text-gray-900"><?php echo format_currency($order['shipping']); ?></span>
                </div>
                <div class="flex justify-between mt-4 pt-4 border-t border-gray-200">
                    <span class="text-base font-medium text-gray-900">Total</span>
                    <span class="text-base font-medium text-gray-900"><?php echo format_currency($order['total']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h2>
            <div class="text-sm text-gray-600">
                <p><span class="font-medium">Payment Method:</span> <?php echo htmlspecialchars($order['payment_gateway']); ?></p>
                <p><span class="font-medium">Status:</span> <?php echo ucfirst(htmlspecialchars($order['status'])); ?></p>
                
                <?php if ($order['gateway_code'] === 'bank_transfer'): ?>
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Bank Transfer Details</h3>
                    <p class="mb-2">Please use the following details to complete your bank transfer:</p>
                    <div class="space-y-1">
                        <p><span class="font-medium">Bank:</span> Example Bank</p>
                        <p><span class="font-medium">Account Name:</span> Charity Shop</p>
                        <p><span class="font-medium">Sort Code:</span> 12-34-56</p>
                        <p><span class="font-medium">Account Number:</span> 12345678</p>
                        <p><span class="font-medium">Reference:</span> ORD-<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Please include the reference number when making your transfer to help us match your payment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="p-6 bg-gray-50">
            <div class="space-y-2">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span><?php echo format_currency($order['subtotal']); ?></span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Tax</span>
                    <span><?php echo format_currency($order['tax']); ?></span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Shipping</span>
                    <span><?php echo format_currency($order['shipping']); ?></span>
                </div>
                <div class="flex justify-between text-lg font-medium text-gray-900 pt-2 border-t border-gray-200">
                    <span>Total</span>
                    <span><?php echo format_currency($order['total']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-8">
        <a href="shop.php" class="inline-block px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark">
            Continue Shopping
        </a>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>