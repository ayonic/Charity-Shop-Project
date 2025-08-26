<?php
/**
 * Add Item
 * 
 * This page allows administrators to add new items to the inventory.
 */

require_once 'config/init.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'You must be an administrator to access this page.');
    redirect('login.php');
    exit;
}

// Get categories for dropdown
$categories = db_get_results("SELECT * FROM categories ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize_input($_POST['name']),
        'sku' => sanitize_input($_POST['sku']),
        'description' => sanitize_input($_POST['description']),
        'category_id' => (int)$_POST['category_id'],
        'price' => (float)$_POST['price'],
        'quantity' => (int)$_POST['quantity'],
        'status' => sanitize_input($_POST['status']),
        'created_by' => $_SESSION['user_id'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/items/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $data['image_url'] = $upload_path;
        }
    }
    
    if (db_insert('inventory', $data)) {
        set_flash_message('success', 'Item added successfully!');
        log_activity($_SESSION['user_id'], 'inventory', 'Added new item: ' . $data['name']);
        redirect('inventory.php');
    } else {
        set_flash_message('error', 'Failed to add item.');
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - Charity Shop</title>
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
                        <h1 class="text-2xl font-semibold text-gray-900">Add New Item</h1>
                        <a href="inventory.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md flex items-center">
                            <i class="ri-arrow-left-line mr-2"></i>
                            Back to Inventory
                        </a>
                    </div>
                    
                    <!-- Add Item Form -->
                    <div class="mt-6">
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
                            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Item Name</label>
                                        <input type="text" name="name" id="name" required 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                                        <input type="text" name="sku" id="sku" required 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3" 
                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                                        <select name="category_id" id="category_id" required 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Select a category</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                        <select name="status" id="status" required 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="available">Available</option>
                                            <option value="out_of_stock">Out of Stock</option>
                                            <option value="low_stock">Low Stock</option>
                                            <option value="discontinued">Discontinued</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">£</span>
                                            </div>
                                            <input type="number" name="price" id="price" required 
                                                   step="0.01" min="0"
                                                   class="mt-1 block w-full pl-7 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" name="quantity" id="quantity" required 
                                               min="0" step="1"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <label for="image" class="block text-sm font-medium text-gray-700">Item Image</label>
                                    <input type="file" name="image" id="image" accept="image/*" 
                                           class="mt-1 block w-full text-sm text-gray-500
                                                  file:mr-4 file:py-2 file:px-4
                                                  file:rounded-md file:border-0
                                                  file:text-sm file:font-semibold
                                                  file:bg-blue-50 file:text-blue-700
                                                  hover:file:bg-blue-100">
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <a href="inventory.php" 
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Add Item
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>