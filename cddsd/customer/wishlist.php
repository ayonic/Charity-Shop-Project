<?php
/**
 * Wishlist Page (Customer)
 * 
 * This page displays all wishlist items for the logged-in customer.
 */

require_once '../includes/customer-header.php';

if (isset($_POST['remove_from_wishlist']) && isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $delete_query = "DELETE FROM wishlist WHERE user_id = ? AND item_id = ?";
    if (db_query($delete_query, [$_SESSION['user_id'], $item_id])) {
        set_flash_message('success', 'Item removed from wishlist.');
    } else {
        set_flash_message('error', 'Failed to remove item from wishlist.');
    }
    redirect('wishlist.php');
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?";
$total_items = db_fetch_row($count_query, [$_SESSION['user_id']]);
$total_pages = ceil($total_items['total'] / $per_page);

$wishlist_query = "SELECT w.*, i.name, i.description, i.price, i.image, i.public_visible 
                  FROM wishlist w 
                  LEFT JOIN inventory i ON w.item_id = i.id 
                  WHERE w.user_id = ? 
                  ORDER BY w.added_at DESC 
                  LIMIT ? OFFSET ?";
$wishlist_items = db_fetch_all($wishlist_query, [$_SESSION['user_id'], $per_page, $offset]);

$page_title = 'My Wishlist';

include '../includes/header.php';
include '../includes/content-start.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-semibold text-gray-800">My Wishlist</h1>
            <p class="text-gray-600 mt-2">Items you're interested in purchasing</p>
        </div>

        <?php if (empty($wishlist_items)): ?>
        <div class="p-6 text-center">
            <p class="text-gray-500 mb-4">Your wishlist is empty</p>
            <a href="../shop.php" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition-colors">
                Browse Shop
            </a>
        </div>
        <?php else: ?>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($wishlist_items as $item): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group relative">
                    <div class="relative aspect-w-4 aspect-h-3">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="w-full h-full object-cover">
                        <?php if (!$item['public_visible']): ?>
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                            <span class="text-white text-sm">No longer available</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-gray-900"><?php echo format_currency($item['price']); ?></span>
                            <span class="text-sm text-gray-500">Added <?php echo format_date($item['added_at']); ?></span>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100 bg-gray-50">
                        <div class="flex space-x-2">
                            <?php if ($item['public_visible']): ?>
                            <a href="../item.php?id=<?php echo $item['item_id']; ?>" 
                               class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                View Item
                            </a>
                            <?php endif; ?>
                            <form method="POST" class="flex-1">
                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                <button type="submit" name="remove_from_wishlist" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_items['total']); ?> of <?php echo $total_items['total']; ?> items
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">&larr; Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Next &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
include '../includes/content-end.php';
include '../includes/footer.php';
?>