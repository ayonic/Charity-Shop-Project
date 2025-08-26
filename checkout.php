<?php
/**
 * Checkout Page
 * 
 * This page handles the checkout process including shipping details and payment.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';
require_once 'includes/payment/PaymentProcessor.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    set_flash_message('error', 'Your cart is empty.');
    redirect('cart.php');
}

// Get user's saved addresses if logged in
$saved_addresses = [];
if (isset($_SESSION['user_id'])) {
    $address_query = "SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC";
    $address_stmt = $connection->prepare($address_query);
    $address_stmt->execute([$_SESSION['user_id']]);
    $saved_addresses = $address_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get available shipping methods
$shipping_query = "SELECT sm.*, sz.* 
                  FROM shipping_methods sm 
                  JOIN shipping_zones sz ON sm.zone_id = sz.id 
                  WHERE sm.status = 'active'";
$shipping_stmt = $connection->prepare($shipping_query);
$shipping_stmt->execute();
$shipping_methods = $shipping_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get enabled payment gateways
$payment_processor = new PaymentProcessor();
$payment_gateways = $payment_processor->getEnabledGateways();

// Get shipping methods with prices
$shipping_query = "SELECT * FROM shipping_methods WHERE status = 'active' ORDER BY base_cost ASC";
$shipping_stmt = $connection->prepare($shipping_query);
$shipping_stmt->execute();
$shipping_methods = $shipping_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate order totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Get tax rate
$tax_query = "SELECT rate FROM tax_rates WHERE is_default = 1 LIMIT 1";
$tax_stmt = $connection->prepare($tax_query);
$tax_stmt->execute();
$tax_rate = $tax_stmt->fetch(PDO::FETCH_ASSOC)['rate'] ?? 0;

$tax = $subtotal * ($tax_rate / 100);
$shipping = 5.00; // Default shipping rate
$total = $subtotal + $tax + $shipping;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate payment gateway selection
    if (empty($_POST['payment_gateway'])) {
        set_flash_message('error', 'Please select a payment method.');
        redirect('checkout.php');
    }

    // Validate stock availability
    $stock_valid = true;
    foreach ($_SESSION['cart'] as $item_id => $item) {
        $stock_query = "SELECT quantity FROM inventory WHERE id = ?";
        $stock_stmt = $connection->prepare($stock_query);
        $stock_stmt->execute([$item_id]);
        $available = $stock_stmt->fetch(PDO::FETCH_ASSOC)['quantity'];
        
        if ($available < $item['quantity']) {
            $stock_valid = false;
            set_flash_message('error', 'Some items are no longer available in the requested quantity.');
            redirect('cart.php');
        }
    }
    
    if ($stock_valid) {
        try {
            $connection->beginTransaction();
            
            // Create order
            $order_query = "INSERT INTO orders (user_id, subtotal, tax, shipping, total, status, shipping_name, 
                                               shipping_email, shipping_phone, shipping_address, shipping_city, 
                                               shipping_state, shipping_zip, shipping_country) 
                           VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)";
            $order_stmt = $connection->prepare($order_query);
            $order_stmt->execute([
                $_SESSION['user_id'] ?? null,
                $subtotal,
                $tax,
                $shipping,
                $total,
                $_POST['shipping_name'],
                $_POST['shipping_email'],
                $_POST['shipping_phone'],
                $_POST['shipping_address'],
                $_POST['shipping_city'],
                $_POST['shipping_state'],
                $_POST['shipping_zip'],
                $_POST['shipping_country']
            ]);
            
            $order_id = $connection->lastInsertId();
            
            // Create order items
            foreach ($_SESSION['cart'] as $item_id => $item) {
                $item_query = "INSERT INTO order_items (order_id, inventory_id, quantity, price) 
                               VALUES (?, ?, ?, ?)";
                $item_stmt = $connection->prepare($item_query);
                $item_stmt->execute([
                    $order_id,
                    $item_id,
                    $item['quantity'],
                    $item['price']
                ]);
                
                // Update inventory
                $update_query = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
                $update_stmt = $connection->prepare($update_query);
                $update_stmt->execute([$item['quantity'], $item_id]);
            }
            
            // Process payment
            $payment_processor = new PaymentProcessor();
            $payment_result = $payment_processor->processPayment([
                'order_id' => $order_id,
                'amount' => $total,
                'currency' => 'GBP',
                'gateway_id' => $_POST['payment_gateway'],
                'customer' => [
                    'name' => $_POST['shipping_name'],
                    'email' => $_POST['shipping_email'],
                    'phone' => $_POST['shipping_phone'],
                    'address' => [
                        'line1' => $_POST['shipping_address'],
                        'city' => $_POST['shipping_city'],
                        'state' => $_POST['shipping_state'],
                        'postal_code' => $_POST['shipping_zip'],
                        'country' => $_POST['shipping_country']
                    ]
                ]
            ]);

            if ($payment_result['success']) {
                $connection->commit();
                
                // Clear cart
                unset($_SESSION['cart']);
                
                // Redirect to success page
                redirect('order-success.php?order_id=' . $order_id);
            } else {
                throw new Exception($payment_result['error']);
            }
            
        } catch (Exception $e) {
            $connection->rollBack();
            set_flash_message('error', 'An error occurred while processing your order. Please try again.');
        }
    }
}
?>

<!-- Checkout Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Checkout</h1>

    <form action="checkout.php" method="POST" class="flex flex-col lg:flex-row -mx-4">
        <!-- Shipping Information -->
        <div class="lg:w-2/3 px-4 mb-8 lg:mb-0">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">Shipping Information</h2>
                
                <?php if (!empty($saved_addresses)): ?>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Saved Address
                    </label>
                    <select id="saved_address" class="w-full rounded-lg border-gray-300">
                        <option value="">Use a new address</option>
                        <?php foreach ($saved_addresses as $address): ?>
                            <option value="<?php echo htmlspecialchars(json_encode($address)); ?>">
                                <?php echo htmlspecialchars($address['address_line1']); ?>
                                <?php echo $address['is_default'] ? ' (Default)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="shipping_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name
                        </label>
                        <input type="text" name="shipping_name" id="shipping_name" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label for="shipping_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" name="shipping_email" id="shipping_email" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label for="shipping_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone
                        </label>
                        <input type="tel" name="shipping_phone" id="shipping_phone" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div class="md:col-span-2">
                        <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <input type="text" name="shipping_address" id="shipping_address" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-2">
                            City
                        </label>
                        <input type="text" name="shipping_city" id="shipping_city" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label for="shipping_state" class="block text-sm font-medium text-gray-700 mb-2">
                            State
                        </label>
                        <input type="text" name="shipping_state" id="shipping_state" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label for="shipping_zip" class="block text-sm font-medium text-gray-700 mb-2">
                            ZIP Code
                        </label>
                        <input type="text" name="shipping_zip" id="shipping_zip" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label for="shipping_country" class="block text-sm font-medium text-gray-700 mb-2">
                            Country
                        </label>
                        <input type="text" name="shipping_country" id="shipping_country" required 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">Payment Method</h2>
                <div class="space-y-4">
                    <!-- Stripe Payment Option -->
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition duration-150 group">
                        <input type="radio" name="payment_gateway" value="stripe" data-code="stripe"
                               class="h-5 w-5 text-primary-600 border-gray-300 focus:ring-primary-500" required>
                        <div class="ml-4 flex justify-between w-full">
                            <div>
                                <span class="block font-medium text-gray-900 group-hover:text-primary-600">Pay with Card (Stripe)</span>
                                <span class="text-sm text-gray-500">Safe and secure card payment</span>
                            </div>
                            <img src="assets/images/payment/stripe.svg" alt="Stripe" class="h-8 object-contain">
                        </div>
                    </label>

                    <!-- PayPal Payment Option -->
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition duration-150 group">
                        <input type="radio" name="payment_gateway" value="paypal" data-code="paypal"
                               class="h-5 w-5 text-primary-600 border-gray-300 focus:ring-primary-500">
                        <div class="ml-4 flex justify-between w-full">
                            <div>
                                <span class="block font-medium text-gray-900 group-hover:text-primary-600">PayPal</span>
                                <span class="text-sm text-gray-500">Fast and convenient PayPal payment</span>
                            </div>
                            <img src="assets/images/payment/paypal.svg" alt="PayPal" class="h-8 object-contain">
                        </div>
                    </label>

                    <!-- Bank Transfer Option -->
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition duration-150 group">
                        <input type="radio" name="payment_gateway" value="bank_transfer" data-code="bank_transfer"
                               class="h-5 w-5 text-primary-600 border-gray-300 focus:ring-primary-500">
                        <div class="ml-4 flex justify-between w-full">
                            <div>
                                <span class="block font-medium text-gray-900 group-hover:text-primary-600">Bank Transfer</span>
                                <span class="text-sm text-gray-500">Direct bank transfer payment</span>
                            </div>
                            <img src="assets/images/payment/bank.svg" alt="Bank Transfer" class="h-8 object-contain">
                        </div>
                    </label>
                </div>
            </div>

            <!-- Shipping Method -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">Shipping Method</h2>
                <div class="space-y-4">
                    <?php foreach ($shipping_methods as $method): ?>
                        <label class="block p-4 border-2 rounded-lg cursor-pointer transition duration-200 ease-in-out hover:border-primary-500 hover:shadow-md group relative <?php echo $method['is_default'] ? 'border-primary-500 bg-primary-50' : 'border-gray-200'; ?>">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 pt-1">
                                    <input type="radio" name="shipping_method" value="<?php echo $method['id']; ?>" 
                                           class="h-5 w-5 text-primary-600 border-gray-300 focus:ring-primary-500" 
                                           <?php echo $method['is_default'] ? 'checked' : ''; ?> required>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-lg font-semibold text-gray-900 group-hover:text-primary-600">
                                            <?php echo htmlspecialchars($method['name']); ?>
                                        </span>
                                        <span class="text-xl font-bold text-primary-600">
                                            <?php echo format_currency($method['base_cost']); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <?php echo htmlspecialchars($method['description']); ?>
                                    </p>
                                    <div class="inline-flex items-center space-x-2">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-500">
                                            Estimated delivery: <?php echo htmlspecialchars($method['delivery_time']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:w-1/3 px-4">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
                <div class="space-y-4">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="flex items-center">
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
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-900"><?php echo format_currency($subtotal); ?></span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-gray-500">Tax (<?php echo $tax_rate; ?>%)</span>
                            <span class="text-gray-900"><?php echo format_currency($tax); ?></span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-gray-500">Shipping</span>
                            <span class="text-gray-900"><?php echo format_currency($shipping); ?></span>
                        </div>
                        <div class="flex justify-between mt-4 pt-4 border-t border-gray-200">
                            <span class="text-lg font-medium text-gray-900">Total</span>
                            <span class="text-lg font-medium text-gray-900"><?php echo format_currency($total); ?></span>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
// Handle saved address selection
document.getElementById('saved_address')?.addEventListener('change', function() {
    const address = JSON.parse(this.value || '{}');
    if (address.address_line1) {
        document.getElementById('shipping_name').value = address.full_name || '';
        document.getElementById('shipping_email').value = address.email || '';
        document.getElementById('shipping_phone').value = address.phone || '';
        document.getElementById('shipping_address').value = address.address_line1 || '';
        document.getElementById('shipping_city').value = address.city || '';
        document.getElementById('shipping_state').value = address.state || '';
        document.getElementById('shipping_zip').value = address.zip_code || '';
        document.getElementById('shipping_country').value = address.country || '';
    } else {
        document.getElementById('shipping_name').value = '';
        document.getElementById('shipping_email').value = '';
        document.getElementById('shipping_phone').value = '';
        document.getElementById('shipping_address').value = '';
        document.getElementById('shipping_city').value = '';
        document.getElementById('shipping_state').value = '';
        document.getElementById('shipping_zip').value = '';
        document.getElementById('shipping_country').value = '';
    }
});

// Initialize Stripe
let stripe;
let elements;
let card;

// Handle payment method selection
document.querySelectorAll('input[name="payment_gateway"]').forEach(radio => {
    radio.addEventListener('change', async function() {
        const gatewayCode = this.getAttribute('data-code');
        
        // Remove any existing payment form elements
        const existingForms = document.querySelectorAll('.payment-form');
        existingForms.forEach(form => form.remove());
        
        // Create container for payment form
        const container = document.createElement('div');
        container.className = 'payment-form mt-4';
        this.closest('label').appendChild(container);
        
        
        } else if (gatewayCode === 'bank_transfer') {
            container.innerHTML = `
                <div class="mt-2 text-sm text-gray-600">
                    <p>Please use the following bank details for your transfer:</p>
                    <p class="mt-2">Bank: Example Bank</p>
                    <p>Account Name: Charity Shop</p>
                    <p>Sort Code: 12-34-56</p>
                    <p>Account Number: 12345678</p>
                    <p>Reference: [Will be provided after order placement]</p>
                </div>
            `;
        }
    });
});

// Handle form submission
document.querySelector('form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';
    
    try {
        const selectedGateway = document.querySelector('input[name="payment_gateway"]:checked');
        const gatewayCode = selectedGateway.getAttribute('data-code');
        
        
        }
        
        this.submit();
    } catch (error) {
        alert(error.message);
        submitButton.disabled = false;
        submitButton.textContent = 'Place Order';
    }
});
</script>

<?php require_once 'includes/public-footer.php'; ?>