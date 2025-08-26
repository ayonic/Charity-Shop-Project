<?php
/**
 * Helper Functions
 * 
 * This file contains various helper functions used throughout the application.
 */

require_once __DIR__ . '/csrf_protection.php';

// Display flash messages
function display_flash_messages() {
    if (isset($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $type => $message) {
            $bg_color = $type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
            echo "<div class=\"p-4 mb-4 {$bg_color} border-l-4 rounded\" role=\"alert\">{$message}</div>";
        }
        unset($_SESSION['flash_messages']);
    }
}

// Get user role ID
function get_user_role_id($user_id = null) {
    global $pdo;
    if ($user_id === null) {
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    }
    $stmt = $pdo->prepare('SELECT role_id FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['role_id'] : null;
}

// Fetch a single row from database
function db_fetch_one($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



// Get user initials
function get_user_initials($first_name, $last_name) {
    return strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
}

// Get status label class for badges
function get_status_label_class($status) {
    switch (strtolower($status)) {
        case 'active':
        case 'confirmed':
        case 'processed':
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'pending':
        case 'in-progress':
            return 'bg-yellow-100 text-yellow-800';
        case 'inactive':
        case 'cancelled':
        case 'declined':
            return 'bg-red-100 text-red-800';
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-blue-100 text-blue-800';
    }
}

// Get status dot class
function get_status_dot_class($status) {
    switch (strtolower($status)) {
        case 'in-stock':
            return 'status-dot in-stock';
        case 'low-stock':
            return 'status-dot low-stock';
        case 'out-of-stock':
            return 'status-dot out-of-stock';
        default:
            return 'status-dot';
    }
}

// Get category badge class
function get_category_badge_class($category) {
    $classes = [
        'clothing' => 'bg-pink-100 text-pink-800',
        'books' => 'bg-blue-100 text-blue-800',
        'furniture' => 'bg-brown-100 text-brown-800',
        'electronics' => 'bg-purple-100 text-purple-800',
        'toys' => 'bg-yellow-100 text-yellow-800',
        'home' => 'bg-green-100 text-green-800',
        'kitchenware' => 'bg-green-100 text-green-800',
        'accessories' => 'bg-indigo-100 text-indigo-800',
        'other' => 'bg-gray-100 text-gray-800'
    ];
    return $classes[strtolower($category)] ?? 'bg-gray-100 text-gray-800';
}



// Get inventory items with optional filters
function get_inventory_items($limit = null, $offset = 0, $where = '1') {
    global $pdo;
    try {
        $sql = "SELECT i.*, c.name as category_name 
               FROM inventory i 
               LEFT JOIN categories c ON i.category_id = c.id 
               WHERE {$where} 
               ORDER BY i.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in get_inventory_items: " . $e->getMessage());
        return [];
    }
}

// Get sales with optional filtering and pagination
function get_sales($limit = 10, $offset = 0, $where = '') {
    global $pdo;
    $query = "SELECT s.*, u.first_name, u.last_name, p.name as product_name
              FROM sales s 
              LEFT JOIN users u ON s.user_id = u.id
              LEFT JOIN products p ON s.product_id = p.id";
    
    if ($where) {
        $query .= " WHERE {$where}";
    }
    
    $query .= " ORDER BY s.sale_date DESC";
    
    if ($limit) {
        $query .= " LIMIT {$limit}";
        if ($offset) {
            $query .= " OFFSET {$offset}";
        }
    }
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in get_sales: " . $e->getMessage());
        return [];
    }
}

// Update record in database
function db_update($table, $data, $where) {
    global $pdo;
    $set = implode(' = ?, ', array_keys($data)) . ' = ?';
    $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(array_values($data));
}



// Get notification icon class based on type
function get_notification_icon_class($type) {
    $classes = [
        'success' => 'text-green-500 ri-checkbox-circle-line',
        'warning' => 'text-yellow-500 ri-error-warning-line',
        'error' => 'text-red-500 ri-close-circle-line',
        'info' => 'text-blue-500 ri-information-line'
    ];
    
    return $classes[$type] ?? 'text-blue-500 ri-information-line';
}

// Get inventory status
function get_inventory_status($quantity, $threshold) {
    if ($quantity <= 0) {
        return 'out-of-stock';
    } elseif ($quantity <= $threshold) {
        return 'low-stock';
    } else {
        return 'in-stock';
    }
}

// Generate SKU
function generate_sku($category_code, $item_id) {
    return $category_code . '-' . str_pad($item_id, 3, '0', STR_PAD_LEFT);
}

// Get dashboard statistics
function get_dashboard_stats() {
    $connection = db_connect();
    $stats = [];
    
    try {
        // Sales today
        $query = "SELECT COALESCE(SUM(total_amount), 0) as sales_today FROM sales WHERE DATE(sale_date) = CURDATE()";
        $stmt = $connection->query($query);
        $stats['sales_today'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['sales_today'] : 0;
        
        // New donations (pending)
        $query = "SELECT COUNT(*) as new_donations FROM donations WHERE status = 'pending'";
        $stmt = $connection->query($query);
        $stats['new_donations'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['new_donations'] : 0;
        
        // Active volunteers
        $query = "SELECT COUNT(*) as active_volunteers FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'volunteer') AND status = 'active'";
        $stmt = $connection->query($query);
        $stats['active_volunteers'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['active_volunteers'] : 0;
        
        // Stock items
        $query = "SELECT COUNT(*) as stock_items FROM inventory WHERE quantity > 0";
        $stmt = $connection->query($query);
        $stats['stock_items'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['stock_items'] : 0;
        
        // Total donations
        $query = "SELECT COUNT(*) as total_donations FROM donations";
        $stmt = $connection->query($query);
        $stats['total_donations'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['total_donations'] : 0;
        
        // Pending donations
        $query = "SELECT COUNT(*) as pending_donations FROM donations WHERE status = 'pending'";
        $stmt = $connection->query($query);
        $stats['pending_donations'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['pending_donations'] : 0;
        
        // Value this month
        $query = "SELECT COALESCE(SUM(estimated_value), 0) as value_this_month FROM donations WHERE MONTH(donation_date) = MONTH(CURDATE()) AND YEAR(donation_date) = YEAR(CURDATE())";
        $stmt = $connection->query($query);
        $stats['value_this_month'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['value_this_month'] : 0;
        // Average donation value
        $query = "SELECT COALESCE(AVG(estimated_value), 0) as average_donation_value FROM donations WHERE estimated_value > 0";
        $stmt = $connection->query($query);
        $stats['average_donation_value'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['average_donation_value'] : 0;
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Database error in get_dashboard_stats: " . $e->getMessage());
        return [];
    }
}

// Get manager dashboard statistics
function get_manager_dashboard_stats() {
    $connection = db_connect();
    $stats = [];
    
    try {
        // Sales today
        $query = "SELECT COALESCE(SUM(total_amount), 0) as sales_today FROM sales WHERE DATE(sale_date) = CURDATE()";
        $stmt = $connection->query($query);
        $stats['sales_today'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['sales_today'] : 0;
        
        // Pending donations
        $query = "SELECT COUNT(*) as pending_donations FROM donations WHERE status = 'pending'";
        $stmt = $connection->query($query);
        $stats['pending_donations'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['pending_donations'] : 0;
        
        // Active volunteers
        $query = "SELECT COUNT(*) as active_volunteers FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'volunteer') AND status = 'active'";
        $stmt = $connection->query($query);
        $stats['active_volunteers'] = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC)['active_volunteers'] : 0;
           return $stats;
    } catch (PDOException $e) {
        error_log("Database error in get_manager_dashboard_stats: " . $e->getMessage());
        return [];
    }
}

// Get volunteer dashboard statistics
function get_volunteer_dashboard_stats($user_id) {
    $connection = db_connect();
    $stats = [];
    
    // Hours this month
    $query = "SELECT COALESCE(SUM(hours_worked), 0) as hours_this_month FROM volunteer_hours WHERE user_id = :user_id AND MONTH(work_date) = MONTH(CURDATE()) AND YEAR(work_date) = YEAR(CURDATE())";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats['hours_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['hours_this_month'];
    
    // Total hours
    $query = "SELECT COALESCE(SUM(hours_worked), 0) as total_hours FROM volunteer_hours WHERE user_id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats['total_hours'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_hours'];
    
    // Days active
    $query = "SELECT COUNT(DISTINCT work_date) as days_active FROM volunteer_hours WHERE user_id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats['days_active'] = $stmt->fetch(PDO::FETCH_ASSOC)['days_active'];
    
    db_close($connection);
    return $stats;
}

// Get donor dashboard statistics
function get_donor_dashboard_stats($user_id) {
    $connection = db_connect();
    $stats = [];
    
    // Total donations
    $query = "SELECT COUNT(*) as total_donations FROM donations WHERE donor_id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats['total_donations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_donations'];
    
    // Total value
    $query = "SELECT COALESCE(SUM(estimated_value), 0) as total_value FROM donations WHERE donor_id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats['total_value'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_value'];
    
    // Value this year
    $query = "SELECT COALESCE(SUM(estimated_value), 0) as value_this_year FROM donations WHERE donor_id = :user_id AND YEAR(donation_date) = YEAR(CURDATE())";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats['value_this_year'] = $stmt->fetch(PDO::FETCH_ASSOC)['value_this_year'];
    
    // Calculate impact score (simplified)
    $stats['impact_score'] = min(100, ($stats['total_donations'] * 10) + ($stats['total_value'] / 10));
    
    // Total items donated (estimated)
    $stats['total_items'] = $stats['total_donations'] * 3; // Estimate 3 items per donation
    
    // Items sold (from inventory linked to donations)
    $query = "SELECT COUNT(*) as items_sold FROM sale_items si 
              JOIN inventory i ON si.inventory_id = i.id 
              JOIN donations d ON i.donation_id = d.id 
              WHERE d.donor_id = :user_id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $stats['items_sold'] = $stmt->fetch(PDO::FETCH_ASSOC)['items_sold'];
    
    // Lives impacted (estimated)
    $stats['lives_impacted'] = $stats['items_sold'] + ($stats['total_value'] / 20);
    
    db_close($connection);
    return $stats;
}

// Get donations
function get_donations($limit = null, $offset = 0, $where = '') {
    $connection = db_connect();
    
    $query = "SELECT d.*, u.first_name, u.last_name, u.email, CONCAT('RN-', LPAD(d.id, 6, '0')) as receipt_number 
              FROM donations d 
              LEFT JOIN users u ON d.donor_id = u.id";
    
    if ($where) {
        $query .= " WHERE {$where}";
    }
    
    $query .= " ORDER BY d.donation_date DESC";
    
    if ($limit) {
        $query .= " LIMIT {$limit}";
        if ($offset) {
            $query .= " OFFSET {$offset}";
        }
    }
    
    try {
        $stmt = $connection->query($query);
        $donations = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return $donations;
    } catch (PDOException $e) {
        error_log("Database error in get_donations: " . $e->getMessage());
        return [];
    }
}

// Get sales




// Get inventory item by ID
function get_inventory_item($id) {
    $connection = db_connect();
    
    try {
        $query = "SELECT i.*, c.name as category_name 
                  FROM inventory i 
                  JOIN categories c ON i.category_id = c.id 
                  WHERE i.id = :id";
        
        $stmt = $connection->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    } catch (PDOException $e) {
        error_log("Database error in get_inventory_item: " . $e->getMessage());
        return null;
    }
}

// Get categories
function get_categories() {
    $connection = db_connect();
    
    try {
        $query = "SELECT * FROM categories ORDER BY name";
        $stmt = $connection->query($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (PDOException $e) {
        error_log("Database error in get_categories: " . $e->getMessage());
        return [];
    }
}

// Get category by ID
function get_category($id) {
    $connection = db_connect();
    
    $query = "SELECT * FROM categories WHERE id = :id";
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    db_close($connection);
    return $category;
}

// Get volunteers
function get_volunteers() {
    $connection = db_connect();
    
    $query = "SELECT * FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'volunteer') ORDER BY last_name, first_name";
    $stmt = $connection->query($query);
    $volunteers = [];
    
    if ($stmt) {
        $volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    db_close($connection);
    return $volunteers;
}

// Get inventory status distribution
function get_inventory_status_distribution() {
    $connection = db_connect();
    $stats = [
        'in_stock' => 0,
        'low_stock' => 0,
        'out_of_stock' => 0
    ];
    
    // In stock
    $query = "SELECT COUNT(*) as count FROM inventory WHERE quantity > low_stock_threshold";
    $stmt = $connection->query($query);
    if ($stmt) {
        $stats['in_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    // Low stock
    $query = "SELECT COUNT(*) as count FROM inventory WHERE quantity > 0 AND quantity <= low_stock_threshold";
    $stmt = $connection->query($query);
    if ($stmt) {
        $stats['low_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    // Out of stock
    $query = "SELECT COUNT(*) as count FROM inventory WHERE quantity <= 0";
    $stmt = $connection->query($query);
    if ($stmt) {
        $stats['out_of_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    db_close($connection);
    return $stats;
}

// Get daily sales trend
function get_daily_sales_trend($days = 7) {
    $connection = db_connect();
    
    $query = "SELECT DATE(sale_date) as date, SUM(total_amount) as sales_amount 
              FROM sales 
              WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
              GROUP BY DATE(sale_date) 
              ORDER BY date";
    
    $stmt = $connection->query($query);
    $data = [];
    
    if ($stmt) {
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    db_close($connection);
    return $data;
}

// Get donation categories distribution
function get_donation_categories_distribution() {
    $connection = db_connect();
    
    $query = "SELECT c.name, COUNT(di.id) as item_count 
              FROM categories c 
              LEFT JOIN donation_items di ON c.id = di.category_id 
              GROUP BY c.id, c.name 
              ORDER BY item_count DESC";
    
    $stmt = $connection->query($query);
    $data = [];
    
    if ($stmt) {
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    db_close($connection);
    return $data;
}

// Log activity
function log_activity($user_id, $type, $description, $reference_id = null) {
    $connection = db_connect();
    
    $query = "INSERT INTO activities (user_id, activity_type, description, reference_id) 
              VALUES (:user_id, :type, :description, :reference_id)";
    
    $stmt = $connection->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':reference_id', $reference_id, $reference_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
    
    $result = $stmt->execute();
    db_close($connection);
    
    return $result;
}

?>
