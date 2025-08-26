<?php
/**
 * Products Management
 * Only accessible by admin and manager roles.
 */
require_once 'config/init.php';
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'manager'])) {
    set_flash_message('error', 'You do not have permission to access this page.');
    redirect('login.php');
    exit;
}
$products = db_fetch_all("SELECT * FROM products ORDER BY created_at DESC");
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex overflow-hidden bg-gray-100">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="flex-1 overflow-auto focus:outline-none" tabindex="0">
        <main class="flex-1 relative overflow-y-auto focus:outline-none">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
                        <a href="add_product.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                            <i class="ri-add-line mr-2"></i>Add Product
                        </a>
                    </div>
                    <div class="mt-6">
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul role="list" class="divide-y divide-gray-200">
                                <?php foreach ($products as $product): ?>
                                <li>
                                    <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium text-blue-600">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($product['description']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Price: <?php echo format_currency($product['price']); ?> | Stock: <?php echo $product['quantity']; ?>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this product?');">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>