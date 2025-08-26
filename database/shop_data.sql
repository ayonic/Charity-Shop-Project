-- Shop Data Population

-- Insert sample categories if not exists
INSERT IGNORE INTO categories (name, code, description) VALUES
('Electronics', 'ELEC', 'Electronic devices and accessories'),
('Home & Garden', 'HOME', 'Home decor and garden items'),
('Fashion', 'FASH', 'Clothing and accessories'),
('Books', 'BOOK', 'Books and publications'),
('Toys', 'TOYS', 'Children toys and games'),
('Sports', 'SPRT', 'Sports equipment and gear');

-- Insert sample inventory items for 2x2 grid display
INSERT INTO inventory (name, sku, category_id, item_condition, price, quantity, description, image, public_visible) VALUES
-- Row 1
('Vintage Table Lamp', 'LAMP001', (SELECT id FROM categories WHERE code = 'HOME'), 'good', 29.99, 1, 'Beautiful vintage table lamp in working condition', 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15', 1),
('Leather Jacket', 'JACK001', (SELECT id FROM categories WHERE code = 'FASH'), 'good', 45.99, 1, 'Classic brown leather jacket, size M', 'https://images.unsplash.com/photo-1551028719-00167b16eac5', 1),

-- Row 2
('Retro Radio', 'ELEC001', (SELECT id FROM categories WHERE code = 'ELEC'), 'fair', 35.00, 1, 'Vintage radio from the 70s, fully functional', 'https://images.unsplash.com/photo-1593078165899-c7d2ac0d6aea', 1),
('Classic Novel Collection', 'BOOK001', (SELECT id FROM categories WHERE code = 'BOOK'), 'good', 25.50, 1, 'Set of classic novels in excellent condition', 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e', 1),

-- Row 3
('LEGO Classic Set', 'TOYS001', (SELECT id FROM categories WHERE code = 'TOYS'), 'excellent', 19.99, 2, 'Classic LEGO building blocks set', 'https://images.unsplash.com/photo-1587654780291-39c9404d746b', 1),
('Yoga Mat', 'SPRT001', (SELECT id FROM categories WHERE code = 'SPRT'), 'good', 15.99, 3, 'Premium yoga mat, lightly used', 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f', 1),

-- Row 4
('Vintage Camera', 'ELEC002', (SELECT id FROM categories WHERE code = 'ELEC'), 'good', 89.99, 1, 'Collectible film camera from the 1960s', 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f', 1),
('Garden Tools Set', 'HOME002', (SELECT id FROM categories WHERE code = 'HOME'), 'good', 34.99, 1, 'Complete set of essential garden tools', 'https://images.unsplash.com/photo-1617576683096-00fc8eecb3aa', 1);

-- Grant permissions to roles for shop management
INSERT IGNORE INTO permissions (name, description, category) VALUES
('manage_shop', 'Can manage shop items and inventory', 'shop'),
('edit_shop_items', 'Can edit shop items', 'shop'),
('view_shop_analytics', 'Can view shop statistics and reports', 'shop');

-- Assign permissions to admin role (full access)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'admin'),
    id
FROM permissions
WHERE category = 'shop';

-- Assign permissions to moderator role (edit and view)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'moderator'),
    id
FROM permissions
WHERE name IN ('edit_shop_items', 'view_shop_analytics');

-- Assign permissions to volunteer role (edit items only)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'volunteer'),
    id
FROM permissions
WHERE name = 'edit_shop_items';