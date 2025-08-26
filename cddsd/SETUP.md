# Charity Shop Management System - Setup Guide

## Prerequisites

1. Web Server (XAMPP/WAMP/LAMP)
   - PHP 8.0 or higher
   - MySQL 5.7 or higher
   - Apache web server

2. Composer (PHP package manager)
3. Web browser (Chrome/Firefox/Safari)

## Installation Steps

### 1. Server Setup

1. Install XAMPP/WAMP/LAMP on your system
2. Start Apache and MySQL services
3. Ensure PHP is configured with:
   - PDO MySQL extension
   - GD Library for image processing
   - mod_rewrite enabled

### 2. Database Setup

1. Create a new MySQL database:
   ```sql
   CREATE DATABASE charity_shop;
   ```

2. Import the database schema:
   - Navigate to phpMyAdmin
   - Select the charity_shop database
   - Import the following files in order:
     1. `database/schema.sql`
     2. `database/payment_schema.sql`
     3. `database/shop_data.sql` (optional sample data)

### 3. Application Setup

1. Import folder to cpanel public_html in file manager location

2. Install dependencies:
   ```bash
   cd charity-shop
   composer install
   ```

3. Configure database connection:
   - Copy `config/database.example.php` to `config/database.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'charity_shop');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

4. Set up file permissions:
   - Make sure the `uploads` directory is writable
   - Set appropriate permissions for config files

### 4. Payment Gateway Setup

1. PayPal Configuration:
   - Create a PayPal Developer account
   - Generate API credentials (Client ID & Secret)
   - Configure in Admin Dashboard → Payment Settings

2. Stripe Configuration:
   - Create a Stripe account
   - Get API keys (Publishable & Secret)
   - Set up webhook endpoints
   - Configure in Admin Dashboard → Payment Settings

### 5. Initial Access

1. Access the application:
   ```
   http://localhost/charity-shop
   ```

2. Default admin credentials:
   - Email: admin@example.com
   - Password: admin123
   (Change these immediately after first login)

### 6. Post-Installation

1. Update system settings:
   - Site name
   - Contact information
   - Email settings
   - Currency settings

2. Create user roles and permissions

3. Configure inventory categories

4. Set up payment methods

5. Test the system:
   - User registration
   - Donation processing
   - Inventory management
   - Sales transactions
   - Payment processing

## Security Recommendations

1. Change default credentials immediately
2. Use strong passwords
3. Keep PHP and all dependencies updated
4. Configure SSL/HTTPS
5. Regular database backups
6. Implement rate limiting
7. Enable error logging

## Troubleshooting

1. File Permissions:
   - Ensure proper write permissions for uploads directory
   - Check log files for permission errors

2. Database Connection:
   - Verify database credentials
   - Check MySQL service status

3. Payment Processing:
   - Confirm API keys are correct
   - Test in sandbox/test mode first
   - Check webhook configurations

4. Common Issues:
   - Clear browser cache
   - Check PHP error logs
   - Verify mod_rewrite is enabled
   - Confirm .htaccess file is present

## Support

For additional support:
1. Check documentation in the `docs` directory
2. Review issue tracker
3. Contact system administrator

## Updates

1. Backup before updating:
   - Database backup
   - File backup
   - Configuration files

2. Update process:
   - Pull latest changes
   - Run database migrations
   - Update dependencies
   - Clear cache

## License

This project is licensed under the MIT License - see the LICENSE file for details.