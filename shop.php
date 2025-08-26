<?php
/**
 * Shop Page
 * 
 * This page displays products available for purchase and handles shopping functionality.
 */

// Include necessary files
require_once 'config/init.php';
require_once 'includes/public-header.php';
require_once 'includes/payment/PaymentProcessor.php';

// Get category filter from URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 12;

// Build the base query
$query = "SELECT i.*, c.name as category_name 
         FROM inventory i 
         JOIN categories c ON i.category_id = c.id 
         WHERE i.quantity > 0";
$params = [];

// Add category filter
if ($category_id) {
    $query .= " AND i.category_id = ?";
    $params[] = $category_id;
}

// Add search filter
if ($search_term) {
    $query .= " AND (i.name LIKE ? OR i.description LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}

// Add sorting
switch ($sort_by) {
    case 'price_low':
        $query .= " ORDER BY i.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY i.price DESC";
        break;
    case 'oldest':
        $query .= " ORDER BY i.created_at ASC";
        break;
    default: // newest
        $query .= " ORDER BY i.created_at DESC";
}

// Get total count for pagination
$count_query = str_replace("i.*, c.name as category_name", "COUNT(*) as total", $query);
$count_stmt = $connection->prepare($count_query);

// Bind parameters for count query
if (!empty($params)) {
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key + 1, $value);
    }
}

$count_stmt->execute();
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Add pagination
$offset = ($page - 1) * $items_per_page;
$query .= " LIMIT $items_per_page OFFSET $offset";

// Execute the main query
$stmt = $connection->prepare($query);

// Bind parameters for main query
if (!empty($params)) {
    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
}

$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_stmt = $connection->query($categories_query);
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Payment Methods Banner -->
<div class="bg-gray-100 py-4 mb-8">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-center space-x-8">
            <?php
            $payment_processor = new PaymentProcessor();
            $payment_gateways = $payment_processor->getEnabledGateways();
            foreach ($payment_gateways as $gateway): ?>
                <div class="flex items-center">
                    <img src="assets/images/payment/<?php echo strtolower($gateway['code']); ?>.svg" 
                         alt="<?php echo htmlspecialchars($gateway['name']); ?>" 
                         class="h-8 w-auto">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Image Slider Section -->
<div class="relative w-full overflow-hidden mb-8" style="height: min(60vh, 600px)">
    <div class="flex w-full transition-transform duration-500 ease-in-out" id="slider" style="height: min(60vh, 600px)">
        <div class="w-full flex-shrink-0">
            <img src="uploads/1.jpg" alt="Slide 1" class="w-full h-full object-cover object-center">
        </div>
        <div class="w-full flex-shrink-0">
            <img src="uploads/2.jpg" alt="Slide 2" class="w-full h-full object-cover object-center">
        </div>
        <div class="w-full flex-shrink-0">
            <img src="uploads/3.jpg" alt="Slide 3" class="w-full h-full object-cover object-center">
        </div>
    </div>
    <!-- Navigation Buttons -->
    <button onclick="moveSlide(-1)" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full">&lt;</button>
    <button onclick="moveSlide(1)" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full">&gt;</button>
    <!-- Dots Indicator -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
        <button onclick="goToSlide(0)" class="w-3 h-3 rounded-full bg-white" id="dot0"></button>
        <button onclick="goToSlide(1)" class="w-3 h-3 rounded-full bg-white opacity-50" id="dot1"></button>
        <button onclick="goToSlide(2)" class="w-3 h-3 rounded-full bg-white opacity-50" id="dot2"></button>
    </div>
</div>

<!-- Shop Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Filters and Search -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 space-y-4 md:space-y-0">
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
            <!-- Category Filter -->
            <select name="category" onchange="window.location.href=this.value" class="rounded-lg border-gray-300">
                <option value="shop.php">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="shop.php?category=<?php echo $category['id']; ?>" 
                            <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Sort Filter -->
            <select name="sort" onchange="window.location.href=this.value" class="rounded-lg border-gray-300">
                <option value="shop.php?sort=newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="shop.php?sort=oldest" <?php echo $sort_by == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="shop.php?sort=price_low" <?php echo $sort_by == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="shop.php?sort=price_high" <?php echo $sort_by == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </div>

        <!-- Search Form -->
        <form action="shop.php" method="GET" class="w-full md:w-auto">
            <div class="flex space-x-2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" 
                       placeholder="Search items..." 
                       class="rounded-lg border-gray-300 flex-1">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Items Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($items as $item): ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden group">
                <a href="item.php?id=<?php echo $item['id']; ?>" class="block relative">
                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'uploads/placeholder.svg'); ?>" 
                         alt="<?php echo htmlspecialchars($item['name'] ?? 'Product Image'); ?>" 
                         class="w-full h-48 object-cover object-center">
                    <div class="absolute inset-0 bg-black bg-opacity-25 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 truncate">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </h3>
                    <p class="text-sm text-gray-500 mb-2">
                        <?php echo htmlspecialchars($item['category_name']); ?>
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-primary">
                            <?php echo format_currency($item['price']); ?>
                        </span>
                        <form action="cart.php" method="POST" class="inline">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="add_to_cart" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center space-x-2 mt-8">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="shop.php?page=<?php echo $i; ?><?php echo $category_id ? '&category='.$category_id : ''; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?><?php echo $sort_by ? '&sort='.$sort_by : ''; ?>" 
               class="px-4 py-2 rounded-lg <?php echo $page == $i ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php ?>
<script>
let currentSlide = 0;
const totalSlides = 3;
const slider = document.getElementById('slider');
const dots = [document.getElementById('dot0'), document.getElementById('dot1'), document.getElementById('dot2')];

function updateSlider() {
    slider.style.transform = `translateX(-${currentSlide * 100}%)`;
    dots.forEach((dot, index) => {
        dot.classList.toggle('opacity-50', index !== currentSlide);
    });
}

function moveSlide(direction) {
    currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
    updateSlider();
}

function goToSlide(slideIndex) {
    currentSlide = slideIndex;
    updateSlider();
}

// Auto-advance slides every 5 seconds
setInterval(() => moveSlide(1), 5000);
</script>
<?php
require_once 'includes/public-footer.php'; ?>