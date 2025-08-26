# Charity Shop Management System

A comprehensive PHP-based management system for charity shops, featuring inventory management, donation tracking, sales processing, volunteer management, and detailed reporting.

## Features

### 🏪 **Core Management**
- **Dashboard**: Overview with key metrics and recent activities
- **Inventory Management**: Track items, stock levels, categories, and locations
- **Donation Management**: Process donations, track donors, and manage donation items
- **Sales & POS**: Point-of-sale system with transaction processing
- **Volunteer Management**: Schedule volunteers, track hours, and manage events

### 📊 **Analytics & Reporting**
- Sales trends and performance metrics
- Donation analytics and category breakdowns
- Volunteer hour tracking and statistics
- Inventory status and low stock alerts
- Exportable reports and data visualization

### 👥 **User Management**
- Role-based access control (Admin, Manager, Volunteer, Donor)
- User profiles and authentication
- Permission management
- Activity tracking

### ⚙️ **System Features**
- Responsive design with Tailwind CSS
- Modern PHP architecture
- MySQL database with comprehensive schema
- Secure authentication and session management
- Flash messaging system
- Modal-based interactions

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone or download the project files**
   \`\`\`bash
   git clone [repository-url]
   cd charity-shop-system
   \`\`\`

2. **Configure the database**
   - Edit `config/database.php` with your MySQL credentials:
   \`\`\`php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'charity_shop');
   \`\`\`

3. **Set up the database**
   - Navigate to `http://your-domain/database/setup.php`
   - This will create the database, tables, and insert sample data

4. **Configure web server**
   - Ensure your web server points to the project root directory
   - Make sure PHP has write permissions for session handling

### Default Login Credentials

After setup, you can log in with these accounts:

- **Admin**: admin@example.com / admin123
- **Manager**: john.doe@example.com / password123
- **Volunteer**: jane.smith@example.com / password123

## File Structure

\`\`\`
charity-shop-system/
├── config/
│   ├── database.php          # Database configuration
│   └── init.php             # Application initialization
├── includes/
│   ├── functions.php        # Helper functions
│   ├── header.php          # HTML header template
│   ├── sidebar.php         # Navigation sidebar
│   ├── navbar.php          # Top navigation bar
│   ├── footer.php          # HTML footer template
│   ├── content-start.php   # Content area opening
│   └── content-end.php     # Content area closing
├── database/
│   ├── schema.sql          # Database schema
│   └── setup.php           # Database setup script
├── dashboard.php           # Main dashboard
├── inventory.php           # Inventory management
├── donations.php           # Donation management
├── sales.php              # Sales & POS system
├── volunteers.php         # Volunteer management
├── reports.php            # Reports & analytics
├── settings.php           # System settings
├── profile.php            # User profiles
├── login.php              # User authentication
├── logout.php             # Session termination
└── index.php              # Main entry point
\`\`\`

## Database Schema

The system uses a comprehensive MySQL schema with the following main tables:

- **users**: User accounts and profiles
- **categories**: Item categories
- **inventory**: Shop inventory items
- **donations**: Donation records
- **donation_items**: Individual donated items
- **sales**: Sales transactions
- **sale_items**: Items sold in each transaction
- **volunteer_hours**: Volunteer time tracking
- **events**: Scheduled events and activities

## Key Features Explained

### Inventory Management
- Add, edit, and delete inventory items
- Track stock levels with low stock alerts
- Categorize items with custom categories
- Generate SKUs automatically
- Location tracking within the shop

### Donation Processing
- Record donations from donors
- Track individual items within donations
- Process donations into inventory
- Generate donation receipts
- Donor management and history

### Sales System
- Point-of-sale interface
- Shopping cart functionality
- Multiple payment methods
- Automatic inventory updates
- Sales reporting and analytics

### Volunteer Management
- Volunteer registration and profiles
- Hour tracking and reporting
- Event scheduling and assignment
- Performance statistics

### Reporting & Analytics
- Interactive charts and graphs
- Date range filtering
- Export capabilities
- Key performance indicators
- Trend analysis

## Security Features

- Password hashing with PHP's password_hash()
- SQL injection prevention with prepared statements
- Input sanitization and validation
- Session-based authentication
- Role-based access control
- CSRF protection considerations

## Customization

The system is designed to be easily customizable:

- **Styling**: Modify Tailwind CSS classes or add custom CSS
- **Categories**: Add/edit item categories through the settings page
- **User Roles**: Extend the role system in the database and code
- **Reports**: Add custom reports by extending the reports page
- **Features**: Add new functionality by following the existing patterns

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (responsive design)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support and questions:
- Check the documentation
- Review the code comments
- Create an issue in the repository

## Changelog

### Version 1.0.0
- Initial release
- Core functionality implemented
- Basic reporting system
- User management
- Responsive design

---

**Note**: This is a demonstration system. For production use, additional security measures, testing, and optimization should be implemented.
\`\`\`

Now I'll create a final summary file to complete the system:
