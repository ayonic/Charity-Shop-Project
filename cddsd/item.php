<?php
/**
 * Item Details Page
 * 
 * This page displays detailed information about a specific item.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';

// Get item ID from URL
$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get item details
$query = "SELECT i.*, c.name as category_name 
         FROM inventory i 
         JOIN categories c ON i.category_id = c.id 
         WHERE i.id = ? AND i.public_visible = 1";
$stmt = $connection->prepare($query);
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if item not found or not visible
if (!$item) {
    set_flash_message('error', 'Item not found.');
    redirect('shop.php');
}

// Get item attributes
$attributes_query = "SELECT pa.name, pa.value, pa.price_adjustment 
                    FROM product_attributes pa 
                    WHERE pa.product_id = ?";
$attributes_stmt = $connection->prepare($attributes_query);
$attributes_stmt->execute([$item_id]);
$attributes = $attributes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related items
$related_query = "SELECT i.*, c.name as category_name 
                 FROM inventory i 
                 JOIN categories c ON i.category_id = c.id 
                 WHERE i.category_id = ? 
                   AND i.id != ? 
                   AND i.quantity > 0 
                   AND i.public_visible = 1 
                 LIMIT 4";
$related_stmt = $connection->prepare($related_query);
$related_stmt->execute([$item['category_id'], $item_id]);
$related_items = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Item Details Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row -mx-4">
        <!-- Item Image -->
        <div class="lg:w-1/2 px-4 mb-8 lg:mb-0">
            <div class="bg-white rounded-lg overflow-hidden">
                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'uploads/placeholder.svg'); ?>" 
                     alt="<?php echo htmlspecialchars($item['name'] ?? 'Product Image'); ?>" 
                     class="w-full h-96 object-cover">
            </div>
        </div>

        <!-- Item Info -->
        <div class="lg:w-1/2 px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($item['name']); ?>
            </h1>
            <div class="flex items-center space-x-4 mb-4">
                <span class="text-2xl font-bold text-primary">
                    <?php echo format_currency($item['price']); ?>
                </span>
                <span class="text-sm text-gray-500">
                    Category: <?php echo htmlspecialchars($item['category_name']); ?>
                </span>
            </div>
            <div class="prose prose-sm text-gray-500 mb-6">
                <?php echo nl2br(htmlspecialchars($item['description'])); ?>
            </div>

            <?php if (!empty($attributes)): ?>
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Options</h3>
                <?php foreach ($attributes as $attribute): ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo htmlspecialchars($attribute['name']); ?>
                        </label>
                        <?php if ($attribute['option_type'] === 'select'): ?>
                            <select name="options[<?php echo htmlspecialchars($attribute['name']); ?>]" 
                                    class="w-full rounded-lg border-gray-300">
                                <option value="<?php echo htmlspecialchars($attribute['value']); ?>">
                                    <?php echo htmlspecialchars($attribute['value']); ?>
                                    <?php if ($attribute['price_adjustment'] > 0): ?>
                                        (+<?php echo format_currency($attribute['price_adjustment']); ?>)
                                    <?php endif; ?>
                                </option>
                            </select>
                        <?php elseif ($attribute['option_type'] === 'radio'): ?>
                            <div class="space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="options[<?php echo htmlspecialchars($attribute['name']); ?>]" 
                                           value="<?php echo htmlspecialchars($attribute['value']); ?>" 
                                           class="rounded-full border-gray-300 text-primary">
                                    <span class="ml-2">
                                        <?php echo htmlspecialchars($attribute['value']); ?>
                                        <?php if ($attribute['price_adjustment'] > 0): ?>
                                            (+<?php echo format_currency($attribute['price_adjustment']); ?>)
                                        <?php endif; ?>
                                    </span>
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <form action="cart.php" method="POST" class="mb-8">
                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                <div class="flex items-center space-x-4">
                    <div class="w-24">
                        <label for="quantity" class="sr-only">Quantity</label>
                        <input type="number" name="quantity" id="quantity" 
                               min="1" max="<?php echo $item['quantity']; ?>" value="1" 
                               class="w-full rounded-lg border-gray-300">
                    </div>
                    <button type="submit" name="add_to_cart" 
                            class="flex-1 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark">
                        Add to Cart
                    </button>
                </div>
            </form>

            <!-- Stock Status -->
            <div class="text-sm text-gray-500">
                <?php if ($item['quantity'] > 10): ?>
                    <span class="text-green-600">In Stock</span>
                <?php elseif ($item['quantity'] > 0): ?>
                    <span class="text-yellow-600">Low Stock (<?php echo $item['quantity']; ?> left)</span>
                <?php else: ?>
                    <span class="text-red-600">Out of Stock</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Items -->
    <?php if (!empty($related_items)): ?>
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Items</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($related_items as $related_item): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group">
                    <a href="item.php?id=<?php echo $related_item['id']; ?>" class="block relative">
                        <img src="<?php echo htmlspecialchars($related_item['image_url'] ?? 'uploads/placeholder.svg'); ?>" 
                             alt="<?php echo htmlspecialchars($related_item['name'] ?? 'Related Product'); ?>" 
                             class="w-full h-48 object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-25 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </a>
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-900 truncate">
                            <?php echo htmlspecialchars($related_item['name']); ?>
                        </h3>
                        <p class="text-sm text-gray-500 mb-2">
                            <?php echo htmlspecialchars($related_item['category_name']); ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-primary">
                                <?php echo format_currency($related_item['price']); ?>
                            </span>
                            <a href="item.php?id=<?php echo $related_item['id']; ?>" 
                               class="text-primary hover:text-primary-dark">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/public-footer.php'; ?>