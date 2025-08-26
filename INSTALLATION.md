# Installation Guide - Charity Shop Management System

## Quick Start

### 1. Download and Extract
- Download all project files
- Extract to your web server directory (e.g., `/var/www/html/charity-shop/` or `C:\xampp\htdocs\charity-shop\`)

### 2. Database Configuration
Edit `config/database.php`:
\`\`\`php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
define('DB_NAME', 'charity_shop');
\`\`\`

### 3. Run Database Setup
- Open your browser
- Navigate to: `http://localhost/charity-shop/database/setup.php`
- Wait for the setup to complete

### 4. Login
Navigate to: `http://localhost/charity-shop/`

**Default Accounts:**
- **Admin**: admin@example.com / admin123
- **Manager**: john.doe@example.com / password123
- **Volunteer**: jane.smith@example.com / password123

## System Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache or Nginx
- **Browser**: Modern browser with JavaScript enabled

## File Permissions

Ensure your web server has:
- Read access to all files
- Write access for PHP sessions (usually handled automatically)

## Troubleshooting

### Database Connection Issues
1. Verify MySQL is running
2. Check database credentials in `config/database.php`
3. Ensure the database user has CREATE and INSERT privileges

### Login Issues
1. Ensure database setup completed successfully
2. Try the default admin account: admin@example.com / admin123
3. Check browser console for JavaScript errors

### Permission Errors
1. Verify file permissions on your web server
2. Check PHP error logs
3. Ensure session handling is working

## Production Deployment

For production use, consider:

1. **Security Hardening**
   - Change default passwords
   - Use HTTPS
   - Implement additional input validation
   - Add CSRF protection
   - Regular security updates

2. **Performance Optimization**
   - Enable PHP OPcache
   - Optimize database queries
   - Implement caching
   - Compress static assets

3. **Backup Strategy**
   - Regular database backups
   - File system backups
   - Test restore procedures

4. **Monitoring**
   - Error logging
   - Performance monitoring
   - Security monitoring

## Support

If you encounter issues:
1. Check the README.md file
2. Review error logs
3. Verify system requirements
4. Check file permissions

---

**Important**: This system includes sample data for demonstration. Remove or replace sample data before production use.
