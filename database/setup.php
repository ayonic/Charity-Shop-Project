<?php
/**
 * Database Setup Script
 * 
 * This script creates the database and tables for the Charity Shop Management System.
 */

// Include initialization file
require_once dirname(__DIR__) . '/config/init.php';

// Create database if it doesn't exist
function create_database() {
    try {
        $connection = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
        
        if ($connection->exec($sql) !== false) {
            echo "Database created successfully or already exists.<br>";
        }
    } catch(PDOException $e) {
        die("Error creating database: " . $e->getMessage() . "<br>");
    }
    
    $connection = null;
}

// Create tables from schema file
function create_tables() {
    $connection = db_connect();
    
    try {
        // Disable foreign key checks
        $connection->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Read schema file
        $schema_file = file_get_contents(__DIR__ . '/schema.sql');
        
        // Split into individual queries
        $queries = explode(';', $schema_file);
        
        // Execute each query
        foreach ($queries as $query) {
            $query = trim($query);
            
            if (!empty($query)) {
                if ($connection->exec($query) !== false) {
                    echo "Query executed successfully.<br>";
                }
            }
        }
        
        // Enable foreign key checks
        $connection->exec('SET FOREIGN_KEY_CHECKS = 1');
    } catch(PDOException $e) {
        echo "Error executing query: " . $e->getMessage() . "<br>";
        echo "Query: " . $query . "<br><br>";
    }
}

// Insert sample data
function insert_sample_data() {
    // Execute shop data population script
    $shop_data = file_get_contents(__DIR__ . '/shop_data.sql');
    $queries = explode(';', $shop_data);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (db_query($query)) {
                echo "Shop data query executed successfully.<br>";
            } else {
                echo "Error executing shop data query.<br>";
            }
        }
    }
    // Sample roles
    $roles = [
        [
            'name' => 'admin',
            'description' => 'Administrator with full system access'
        ],
        [
            'name' => 'moderator',
            'description' => 'Moderator with limited administrative access'
        ],
        [
            'name' => 'volunteer',
            'description' => 'Volunteer staff member'
        ],
        [
            'name' => 'customer',
            'description' => 'Regular customer account'
        ]
    ];

    foreach ($roles as $role) {
        db_insert('roles', $role);
    }

    // Sample users
    $users = [
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role_id' => 1, // Admin/Manager role
            'phone' => '555-123-4567',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '12345',
            'country' => 'USA'
        ],
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role_id' => 2, // Volunteer role
            'phone' => '555-987-6543',
            'address' => '456 Oak Ave',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '12345',
            'country' => 'USA'
        ],
        [
            'first_name' => 'Robert',
            'last_name' => 'Johnson',
            'email' => 'robert.johnson@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role_id' => 3, // Donor role
            'phone' => '555-567-8901',
            'address' => '789 Pine St',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '12345',
            'country' => 'USA'
        ]
    ];
    
    foreach ($users as $user) {
        db_insert('users', $user);
    }
    
    // Sample donations
    $donations = [
        [
            'donor_id' => 3,
            'donation_date' => '2023-01-15 10:00:00',
            'estimated_value' => 150.00,
            'status' => 'processed',
            'notes' => 'Clothing and books donation'
        ],
        [
            'donor_id' => 3,
            'donation_date' => '2023-02-01 14:30:00',
            'estimated_value' => 75.50,
            'status' => 'processed',
            'notes' => 'Kitchen items'
        ]
    ];
    
    foreach ($donations as $donation) {
        db_insert('donations', $donation);
    }

    // Sample categories
    $categories = [
        [
            'name' => 'Clothing',
            'code' => 'CLO',
            'description' => 'All types of clothing items'
        ],
        [
            'name' => 'Books',
            'code' => 'BOK',
            'description' => 'Books and educational materials'
        ],
        [
            'name' => 'Furniture',
            'code' => 'FUR',
            'description' => 'Home and office furniture'
        ]
    ];

    foreach ($categories as $category) {
        db_insert('categories', $category);
    }
    
    // Sample donation items
    $donation_items = [
        [
            'donation_id' => 1,
            'item_name' => 'Men\'s Winter Jacket',
            'category_id' => 1,
            'item_condition' => 'good',
            'quantity' => 1,
            'estimated_value' => 45.00,
            'notes' => 'Navy blue winter jacket, size L'
        ],
        [
            'donation_id' => 1,
            'item_name' => 'Women\'s Dress',
            'category_id' => 1,
            'item_condition' => 'good',
            'quantity' => 2,
            'estimated_value' => 60.00,
            'notes' => 'Two summer dresses, size M'
        ],
        [
            'donation_id' => 2,
            'item_name' => 'Fiction Books',
            'category_id' => 2,
            'item_condition' => 'good',
            'quantity' => 15,
            'estimated_value' => 75.00,
            'notes' => 'Collection of fiction books'
        ]
    ];
    
    foreach ($donation_items as $item) {
        db_insert('donation_items', $item);
    }
    
    // Sample inventory items
    $inventory_items = [
        [
            'name' => 'Men\'s Winter Jacket',
            'sku' => 'CLO-2024-001',
            'category_id' => 1,
            'item_condition' => 'good',
            'price' => 25.00,
            'quantity' => 1,
            'low_stock_threshold' => 1,
            'description' => 'Navy blue winter jacket, size L',
            'location' => 'A1-01'
        ],
        [
            'name' => 'Women\'s Summer Dress',
            'sku' => 'CLO-2024-002',
            'category_id' => 1,
            'item_condition' => 'good',
            'price' => 18.00,
            'quantity' => 2,
            'low_stock_threshold' => 1,
            'description' => 'Floral summer dress, size M',
            'location' => 'A1-02'
        ],
        [
            'name' => 'Coffee Table',
            'sku' => 'FUR-2024-001',
            'category_id' => 3,
            'item_condition' => 'good',
            'price' => 45.00,
            'quantity' => 1,
            'low_stock_threshold' => 1,
            'description' => 'Wooden coffee table with glass top',
            'location' => 'B2-01'
        ],
        [
            'name' => 'Children\'s Books Set',
            'sku' => 'BOO-2024-001',
            'category_id' => 2,
            'item_condition' => 'good',
            'price' => 12.00,
            'quantity' => 5,
            'low_stock_threshold' => 2,
            'description' => 'Set of 10 children\'s picture books',
            'location' => 'C1-01'
        ]
    ];
    
    foreach ($inventory_items as $item) {
        db_insert('inventory', $item);
    }
    
    // Sample sales
    $sales = [
        [
            'sale_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'customer_name' => 'Mary Wilson',
            'total_amount' => 46.44,
            'payment_method' => 'card',
            'notes' => 'Payment reference: TXN-001',
            'created_by' => 2
        ],
        [
            'sale_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'customer_name' => 'David Brown',
            'total_amount' => 19.44,
            'payment_method' => 'cash',
            'created_by' => 2
        ]
    ];
    
    foreach ($sales as $sale) {
        db_insert('sales', $sale);
    }
    
    // Sample sale items
    $sale_items = [
        [
            'sale_id' => 1,
            'inventory_id' => 1,
            'quantity' => 1,
            'unit_price' => 25.00,
            'total_price' => 25.00
        ],
        [
            'sale_id' => 1,
            'inventory_id' => 2,
            'quantity' => 1,
            'unit_price' => 18.00,
            'total_price' => 18.00
        ],
        [
            'sale_id' => 2,
            'inventory_id' => 2,
            'quantity' => 1,
            'unit_price' => 18.00,
            'total_price' => 18.00
        ]
    ];
    
    foreach ($sale_items as $item) {
        db_insert('sale_items', $item);
    }
    
    // Sample events
    $events = [
        [
            'title' => 'Volunteer Training Session',
            'description' => 'Training for new volunteers on shop procedures',
            'location' => 'Main Shop',
            'start_date' => date('Y-m-d H:i:s', strtotime('+3 days 10:00')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+3 days 12:00')),
            'assigned_to' => 1,
            'status' => 'confirmed'
        ],
        [
            'title' => 'Donation Drive',
            'description' => 'Community donation collection event',
            'location' => 'Community Center',
            'start_date' => date('Y-m-d H:i:s', strtotime('+7 days 09:00')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+7 days 17:00')),
            'assigned_to' => 2,
            'status' => 'pending'
        ]
    ];
    
    foreach ($events as $event) {
        db_insert('events', $event);
    }
    
    // Sample volunteer hours
    $volunteer_hours = [
        [
            'user_id' => 2,
            'work_date' => date('Y-m-d', strtotime('-6 days')),
            'hours_worked' => 4.0,
            'task' => 'Sorting donations',
            'notes' => 'Processed clothing donations'
        ],
        [
            'user_id' => 2,
            'work_date' => date('Y-m-d', strtotime('-4 days')),
            'hours_worked' => 6.0,
            'task' => 'Shop floor assistance',
            'notes' => 'Helped customers and managed till'
        ],
        [
            'user_id' => 2,
            'work_date' => date('Y-m-d', strtotime('-2 days')),
            'hours_worked' => 5.0,
            'task' => 'Inventory management',
            'notes' => 'Updated inventory records'
        ]
    ];
    
    foreach ($volunteer_hours as $hours) {
        db_insert('volunteer_hours', $hours);
    }
    
    echo "Sample data inserted successfully.<br>";
}

// Main setup function
function setup_database() {
    echo "<h2>Setting up Charity Shop Management System Database</h2>";
    
    echo "<h3>Creating Database...</h3>";
    create_database();
    
    echo "<h3>Creating Tables...</h3>";
    create_tables();
    
    echo "<h3>Inserting Sample Data...</h3>";
    insert_sample_data();
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>You can now <a href='login.php'>login to the system</a> using:</p>";
    echo "<ul>";
    echo "<li>Admin: admin@example.com / admin123</li>";
    echo "<li>Manager: john.doe@example.com / password123</li>";
    echo "<li>Volunteer: jane.smith@example.com / password123</li>";
    echo "</ul>";
}

// Run setup if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'setup.php') {
    setup_database();
}
?>
