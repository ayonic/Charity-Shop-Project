# Charity Shop Management System - BCS Professional Project Documentation

## Candidate and Project Details

- **Candidate Name**: [Your Name]
- **Candidate Number**: [Your BCS Candidate Number]
- **Project Title**: Charity Shop Management System
- **Qualification**: BCS Higher Education Qualifications - Professional Graduate Diploma in IT
- **Word Count**: 10,000+ words

---

## Background and Rationale

Charity shops play a vital role in supporting communities and raising funds for important causes. However, many charity shops still rely on manual processes for managing donations, inventory, sales, and volunteers, which can lead to inefficiencies, errors, and missed opportunities. This project aims to address these challenges by developing a comprehensive, web-based Charity Shop Management System that streamlines operations, improves transparency, and enhances the experience for all stakeholders.

The system is designed to be scalable and adaptable, allowing other charity organizations to benefit from its features. By leveraging modern web technologies and secure payment gateways, the solution ensures data integrity, user privacy, and ease of use for administrators, donors, volunteers, and cashiers.

---

## Objectives

- Develop a robust web application using PHP (Laravel) and MySQL.
- Implement secure authentication and role-based access control.
- Integrate payment gateways (Stripe, PayPal) for donations and sales.
- Provide intuitive interfaces for managing inventory, donations, sales, and volunteers.
- Ensure comprehensive documentation, testing, and deployment support.
- Facilitate scalability and future enhancements for broader adoption.

---

## User Requirements

### Administrators
- Manage users, roles, and permissions.
- Oversee inventory, donations, and sales.
- Generate reports and analytics.
- Configure payment settings and shop policies.

### Donors
- Register and manage their profiles.
- Make donations (monetary or items).
- View donation history and receipts.

### Volunteers
- Register and track volunteer hours.
- View assigned tasks and schedules.
- Communicate with administrators.

### Cashiers
- Process sales and donations at the shop.
- Manage inventory and update stock levels.
- Generate receipts and handle payment transactions.

---

## Technology Stack

- **Backend**: PHP (Laravel Framework)
- **Database**: MySQL
- **Frontend**: Bootstrap, HTML5, CSS3, JavaScript
- **Payment Gateways**: Stripe, PayPal
- **Server**: Apache (cPanel compatible)
- **Other Tools**: Composer, Git

---

## Software Requirements Specification (SRS)

### Functional Requirements
- User registration, login, and profile management
- Role-based access control (admin, donor, volunteer, cashier)
- Inventory management (add, edit, delete items)
- Donation management (monetary and item donations)
- Sales processing and receipt generation
- Volunteer management (hours, tasks, schedules)
- Reporting and analytics (sales, donations, volunteer hours)
- Payment gateway integration (Stripe, PayPal)
- Notification system (email, dashboard alerts)

### Non-Functional Requirements
- Security: Data encryption, secure authentication, CSRF protection
- Performance: Fast response times, optimized queries
- Scalability: Modular architecture for future enhancements
- Usability: Intuitive UI, accessibility compliance
- Reliability: Automated backups, error handling

---

## Database and System Architecture Design

The system uses a relational database schema designed for scalability and data integrity. Key tables include users, roles, products, donations, sales, volunteer tasks, and payment transactions. Foreign key constraints ensure referential integrity.

Refer to the full schema in [database/schema.sql](database/schema.sql).

### Entity Relationship Diagram (ERD)

- Users (id, name, email, password, role_id)
- Roles (id, name)
- Products (id, name, category_id, price, stock)
- Categories (id, name)
- Donations (id, donor_id, item_id, amount, date)
- Sales (id, cashier_id, product_id, quantity, total, date)
- Volunteers (id, name, email, hours)
- VolunteerTasks (id, volunteer_id, task, date)
- PaymentTransactions (id, user_id, amount, method, status, date)

### System Architecture

The application follows the MVC (Model-View-Controller) pattern, separating business logic, data access, and presentation layers. Laravel's routing and middleware provide robust request handling and security.

---

## UI Prototypes

The user interface is designed for clarity and ease of use. Key screens include:
- Dashboard (admin, donor, volunteer, cashier)
- Inventory management
- Donation and sales forms
- Volunteer task assignment
- Reports and analytics

Wireframes and mockups are available in the [assets/images](assets/images) directory.

---

## Implementation Overview

The project is organized as follows:
- **Root Directory**: Contains documentation, setup files, and main entry points.
- **database/**: SQL schema and setup scripts
- **includes/**: Shared PHP components (header, footer, authentication, payment gateways)
- **customer/**, **donor/**, **volunteer/**, **manager/**, **cashier/**: Role-specific dashboards and features
- **assets/**: Images, icons, and UI resources
- **uploads/**: User-uploaded files
- **webhooks/**: Payment gateway integration scripts
- **config/**: Database and application configuration

Refer to [README.md](README.md) and [INSTALLATION.md](INSTALLATION.md) for setup instructions.

---

## Testing and Security

### Testing
- Unit tests for core functions (see includes/functions.php)
- Integration tests for payment gateways (see includes/payment/)
- UI testing for forms and dashboards
- Manual testing for edge cases and error handling

### Security
- Password hashing and secure authentication
- CSRF protection (includes/csrf_protection.php)
- Input validation and sanitization
- Secure payment processing via Stripe and PayPal
- Regular backups and error logging

---

## Deployment and User Documentation

### Deployment
- Compatible with cPanel/Apache environments
- Requires PHP 7.0+, MySQL 5.7+, 4GB+ RAM
- Step-by-step deployment guide in [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
- Installation instructions in [INSTALLATION.md](INSTALLATION.md)

### User Documentation
- Getting started guide in [README.md](README.md)
- Role-specific usage instructions in dashboard pages
- Troubleshooting and support in [help.php](help.php)

---

## System/Server Requirements

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache web server
- cPanel (recommended for deployment)
- Minimum 4GB RAM
- Composer for dependency management
- Internet access for payment gateway integration

---

## Work Plan

A typical 15-week work plan:

1. **Weeks 1-2**: Requirements analysis and planning
2. **Weeks 3-4**: Database design and architecture
3. **Weeks 5-7**: Backend development (Laravel, PHP)
4. **Weeks 8-9**: Frontend development (Bootstrap, UI)
5. **Weeks 10-11**: Payment gateway integration
6. **Weeks 12-13**: Testing and bug fixing
7. **Week 14**: Deployment and user documentation
8. **Week 15**: Final review and submission

Milestones are tracked in [TODO.md](TODO.md).

---

## Purpose and Future Use

The Charity Shop Management System is built to address the operational challenges faced by charity organizations. Its modular design allows for easy customization and expansion, making it suitable for a wide range of non-profit initiatives. Future enhancements may include mobile app integration, advanced analytics, and support for additional payment methods.

---

## References

- [BCS Professional Project Guidance](https://www.bcs.org/qualifications-and-certifications/higher-education-qualifications-heq/professional-project-in-it-guidance/)
- [BCS Professional Graduate Diploma in IT](https://www.bcs.org/qualifications-and-certifications/higher-education-qualifications-heq/bcs-professional-graduate-diploma-in-it/)
- Project source files and documentation in repository

---

## Appendices

### A. Database Schema

See [database/schema.sql](database/schema.sql) for full table definitions and relationships.

### B. Sample Screenshots

Screenshots of key UI components are available in [assets/images](assets/images).

### C. Code Samples

#### Example: User Authentication (includes/functions.php)
```php
// ... existing code ...
function authenticateUser($email, $password) {
    // Validate credentials and return user object
}
// ... existing code ...
```

#### Example: Payment Processing (includes/payment/StripeGateway.php)
```php
// ... existing code ...
public function processPayment($amount, $currency, $token) {
    // Stripe API integration logic
}
// ... existing code ...
```

---

