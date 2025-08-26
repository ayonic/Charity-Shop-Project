-- Charity Shop Management System Database Schema

-- Drop existing tables if they exist
-- Drop tables in correct order (child tables first)
DROP TABLE IF EXISTS support_ticket_messages;
DROP TABLE IF EXISTS email_logs;
DROP TABLE IF EXISTS payment_transactions;
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS product_attributes;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS item_tags;
DROP TABLE IF EXISTS item_ratings;
DROP TABLE IF EXISTS donation_items;
DROP TABLE IF EXISTS wishlist;

DROP TABLE IF EXISTS support_tickets;
DROP TABLE IF EXISTS email_templates;
DROP TABLE IF EXISTS payment_methods;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS inventory;
DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS discounts;
DROP TABLE IF EXISTS shipping_methods;
DROP TABLE IF EXISTS product_options;
DROP TABLE IF EXISTS contact_form_submissions;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS content_reviews;
DROP TABLE IF EXISTS user_reports;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS error_logs;
DROP TABLE IF EXISTS system_logs;
DROP TABLE IF EXISTS shipping_zones;
DROP TABLE IF EXISTS tax_rates;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS loyalty_points;
DROP TABLE IF EXISTS gift_cards;
DROP TABLE IF EXISTS shipping_addresses;
DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS volunteer_hours;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS causes;
DROP TABLE IF EXISTS tasks;

DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

-- Roles table
DROP TABLE IF EXISTS roles;
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO roles (name, description) VALUES
    ('admin', 'Administrator with access to all roles and pages'),
    ('manager', 'Manager (includes cashier) with access to management and cashier functions'),
    ('cashier', 'Cashier role for handling sales and transactions'),
    ('volunteer', 'Volunteer with limited access'),
    ('donor', 'Donor with donation privileges'),
    ('customer', 'Customer with shopping privileges');

-- Permissions table
DROP TABLE IF EXISTS permissions;
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Role permissions table
DROP TABLE IF EXISTS role_permissions;
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Users table
DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
DROP TABLE IF EXISTS categories;
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Causes table
DROP TABLE IF EXISTS causes;
CREATE TABLE IF NOT EXISTS causes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    goal_amount DECIMAL(10,2),
    raised_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tasks table
DROP TABLE IF EXISTS tasks;
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    assigned_to INT,
    due_date DATE,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Products table
DROP TABLE IF EXISTS products;
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    sku VARCHAR(50) UNIQUE,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;
-- Move product_options table after products
CREATE TABLE IF NOT EXISTS product_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    value VARCHAR(255),
    option_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (product_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Events table
DROP TABLE IF EXISTS events;
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    assigned_to INT,
    status ENUM('confirmed', 'pending', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Volunteer hours table
DROP TABLE IF EXISTS volunteer_hours;
CREATE TABLE IF NOT EXISTS volunteer_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    work_date DATE NOT NULL,
    hours_worked DECIMAL(5,2) NOT NULL,
    task VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Inventory table
DROP TABLE IF EXISTS inventory;
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sku VARCHAR(20) UNIQUE,
    category_id INT NOT NULL,
    item_condition ENUM('new', 'good', 'fair', 'poor') NOT NULL DEFAULT 'good',
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    low_stock_threshold INT NOT NULL DEFAULT 5,
    description TEXT,
    donation_id INT,
    image VARCHAR(255),
    barcode VARCHAR(50),
    location VARCHAR(50),
    public_visible BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Donations table
DROP TABLE IF EXISTS donations;
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    donation_date DATETIME NOT NULL,
    estimated_value DECIMAL(10,2),
    status ENUM('pending', 'processed', 'declined') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Donation items table
DROP TABLE IF EXISTS donation_items;
CREATE TABLE IF NOT EXISTS donation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    category_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    item_condition ENUM('new', 'good', 'fair', 'poor') NOT NULL DEFAULT 'good',
    estimated_value DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Sales table
DROP TABLE IF EXISTS sales;
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATETIME NOT NULL,
    customer_name VARCHAR(100),
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'other') NOT NULL DEFAULT 'cash',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Sale items table
DROP TABLE IF EXISTS sale_items;
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    inventory_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE RESTRICT
);

-- Activities table for logging
DROP TABLE IF EXISTS activities;
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sessions table for CSRF protection
DROP TABLE IF EXISTS sessions;
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    user_id INT,
    csrf_token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Payment Gateway Configuration Tables
DROP TABLE IF EXISTS payment_gateways;
CREATE TABLE IF NOT EXISTS payment_gateways (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    configuration TEXT,
    test_mode TINYINT(1) DEFAULT 1,
    is_enabled TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add default payment gateways
INSERT INTO payment_gateways (name, code, description, is_enabled, test_mode, configuration) VALUES
('PayPal', 'paypal', 'PayPal payment gateway integration', 0, 1, '{"client_id": "", "client_secret": "", "webhook_id": ""}'),
('Stripe', 'stripe', 'Stripe payment gateway integration', 0, 1, '{"publishable_key": "", "secret_key": "", "webhook_secret": ""}'),
('Bank Transfer', 'bank_transfer', 'Direct bank transfer payment option', 1, 0, '{"bank_name": "", "account_name": "", "account_number": "", "sort_code": "", "iban": "", "swift_bic": ""}');

-- Payment methods table
DROP TABLE IF EXISTS payment_methods;
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    code VARCHAR(20) NOT NULL,
    description TEXT,
    gateway_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE SET NULL
);

-- Add default payment methods linked to gateways
INSERT INTO payment_methods (name, code, description, gateway_id)
SELECT 'PayPal Standard', 'paypal', 'Pay with your PayPal account', id FROM payment_gateways WHERE code = 'paypal';

INSERT INTO payment_methods (name, code, description, gateway_id)
SELECT 'Credit/Debit Card', 'card', 'Pay with Credit/Debit Card via Stripe', id FROM payment_gateways WHERE code = 'stripe';

INSERT INTO payment_methods (name, code, description, gateway_id)
SELECT 'Bank Transfer', 'bank_transfer', 'Pay via Bank Transfer', id FROM payment_gateways WHERE code = 'bank_transfer';

-- Payment transactions table
DROP TABLE IF EXISTS payment_transactions;
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    method_id INT,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'USD',
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    transaction_date DATETIME NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
);
-- Update payment_transactions table to store gateway-specific data
ALTER TABLE payment_transactions
ADD COLUMN gateway_id INT NULL,
ADD COLUMN gateway_transaction_id VARCHAR(255),
ADD COLUMN gateway_status VARCHAR(50),
ADD FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE SET NULL;

-- Settings table
DROP TABLE IF EXISTS settings;
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    
);

-- Notifications table
DROP TABLE IF EXISTS notifications;
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders table
DROP TABLE IF EXISTS orders;
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
DROP TABLE IF EXISTS order_items;
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    item_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (item_id) REFERENCES donation_items(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    value VARCHAR(255),
    price_adjustment DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
