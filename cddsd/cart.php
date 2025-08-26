<?php
/**
 * Shopping Cart Page
 * 
 * This page handles the shopping cart functionality including adding, updating, and removing items.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $item_id = (int)$_POST['item_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        // Get item details
        $query = "SELECT i.*, COALESCE(i.image, 'uploads/placeholder.svg') as image_url FROM inventory i WHERE i.id = ? AND i.quantity >= ? AND i.public_visible = 1";
        $stmt = $connection->prepare($query);
        $stmt->execute([$item_id, $quantity]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            // Add or update cart item
            if (isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$item_id] = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $quantity,
                    'image_url' => $item['image_url']
                ];
            }
            set_flash_message('success', 'Item added to cart.');
        } else {
            set_flash_message('error', 'Item not available in requested quantity.');
        }
        redirect('cart.php');
    }
    
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $item_id => $quantity) {
            $item_id = (int)$item_id;
            $quantity = (int)$quantity;
            
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$item_id]);
            } else {
                // Verify stock availability
                $query = "SELECT quantity FROM inventory WHERE id = ?";
                $stmt = $connection->prepare($query);
                $stmt->execute([$item_id]);
                $available = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($available && $available['quantity'] >= $quantity) {
                    $_SESSION['cart'][$item_id]['quantity'] = $quantity;
                } else {
                    set_flash_message('error', 'Some items are not available in requested quantity.');
                }
            }
        }
        set_flash_message('success', 'Cart updated successfully.');
        redirect('cart.php');
    }
    
    if (isset($_POST['remove_item'])) {
        $item_id = (int)$_POST['item_id'];
        unset($_SESSION['cart'][$item_id]);
        set_flash_message('success', 'Item removed from cart.');
        redirect('cart.php');
    }
}

// Calculate cart totals
$subtotal = 0;
$shipping = 5.00; // Fixed shipping rate
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping;
?>

<!-- Cart Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="text-center py-12">
            <p class="text-gray-500 mb-4">Your cart is empty</p>
            <a href="shop.php" class="inline-block px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <form action="cart.php" method="POST">
            <div class="flex flex-col lg:flex-row -mx-4">
                <!-- Cart Items -->
                <div class="lg:w-2/3 px-4 mb-8 lg:mb-0">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($_SESSION['cart'] as $item_id => $item): ?>
                                <div class="flex items-center p-4 hover:bg-gray-50">
                                    <div class="flex-shrink-0 w-24 h-24">
                                        <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'uploads/placeholder.svg'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>" 
                                             class="w-full h-full object-cover rounded-lg">
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            <?php echo format_currency($item['price'] ?? 0); ?> each
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <input type="number" name="quantities[<?php echo $item_id; ?>]" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="0" 
                                               class="w-20 rounded-lg border-gray-300">
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-lg font-medium text-gray-900">
                                            <?php echo format_currency($item['price'] * $item['quantity']); ?>
                                        </p>
                                        <form action="cart.php" method="POST" class="inline">
                                            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                            <button type="submit" name="remove_item" 
                                                    class="text-sm text-red-600 hover:text-red-800">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="p-4 border-t border-gray-200">
                            <button type="submit" name="update_cart" 
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Update Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:w-1/3 px-4">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="text-gray-900"><?php echo format_currency($subtotal); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Shipping</span>
                                <span class="text-gray-900"><?php echo format_currency($shipping); ?></span>
                            </div>
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between">
                                    <span class="text-lg font-medium text-gray-900">Total</span>
                                    <span class="text-lg font-medium text-gray-900"><?php echo format_currency($total); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="checkout.php" 
                               class="block w-full px-6 py-3 bg-primary text-white text-center rounded-lg hover:bg-primary-dark">
                                Proceed to Checkout
                            </a>
                        </div>
                        <div class="mt-4">
                            <a href="shop.php" class="text-primary hover:text-primary-dark text-sm">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/public-footer.php'; ?>