# Charity Shop Management System

# Candidate & Project Details
- Candidate Name: Everest Otoju Itibi
- BCS Membership Number: 995143655
- Course Provider: Compunet Limited
- Project Title: Charity Shop Management System
- Planned Submission Date: 31st August, 2025

---

# 1. Background & Rationale
Charity shops play a vital role in supporting communities, but many still rely on manual processes that can be inefficient and error-prone. Through my experience volunteering and working with local charities, I noticed how difficult it was to keep track of donations, inventory, and volunteer hours. This inspired me to develop the Charity Shop Management System—a web-based platform designed to make these operations smoother, more transparent, and scalable. My hope is that this system will not only help individual shops but also be adaptable for wider use by NGOs and similar organizations.

# 2. Objectives
My main goal was to create a secure and user-friendly management system that would:
- Give administrators a clear dashboard to monitor donations, inventory, and sales.
- Make it easy to list items, register donors, track donations, and process sales.
- Help volunteers manage their schedules and log their hours.
- Provide comprehensive reports to help everyone understand how the shop is performing.

# 3. User Requirements
# Administrator
Administrators need tools to manage donations and inventory, view and download reports, monitor sales and volunteer activity, and handle user accounts. I focused on making these features intuitive and accessible.

# Donor
Donors should be able to register, log in, submit donations, and track their contributions. I wanted to make the donation process as simple and rewarding as possible.

# Volunteer
Volunteers are the backbone of charity shops. The system lets them view their schedules, log hours, and update task completion, helping them stay organized and recognized for their efforts.

# Cashier
Cashiers process sales, apply discounts, and generate receipts. Integrating a POS system was essential for smooth transactions and accurate record-keeping.

# 4. Technology Stack
I chose technologies that are reliable and widely supported:
- Backend: PHP (Core PHP)
- Database: MySQL
- Frontend: HTML, CSS (Bootstrap), JavaScript (jQuery)
- Payments: Stripe, PayPal
- Hosting: cPanel-based server with Apache

# 5. Software Requirements Specification (SRS)
# Functional Requirements
The system supports secure logins, role-based access, CRUD operations for all major entities, POS integration, reporting, and notifications. Each feature was designed with real-world charity shop workflows in mind.

# Non-Functional Requirements
Security and usability were top priorities. I implemented data encryption, responsive design, and optimized queries to ensure the system is both safe and fast. The architecture is modular, making future updates easier.

# 6. Database & System Architecture Design
The database uses MySQL, with tables for users, products, donations, orders, volunteers, and more. Relationships are enforced with foreign keys to maintain data integrity. The system architecture separates backend logic, frontend presentation, and data storage for clarity and maintainability.

# 7. User Interface (UI) Prototypes
I designed the UI to be clean and straightforward:
- Admin Dashboard: Shows key metrics and activities at a glance.
- Donor Portal: Simple registration and donation submission.
- Volunteer Portal: Easy access to schedules and hour logging.
- Cashier POS: Fast sales processing and receipt generation.
- Reports: Downloadable summaries for transparency and analysis.

# 8. Implementation Overview
The backend is built with PHP, handling business logic and data management. The frontend uses Bootstrap and jQuery for responsive, interactive pages. Payment integration with Stripe and PayPal ensures secure transactions.

# 9. Testing & Security
I tested the system manually and with automated scripts, focusing on backend logic, user workflows, and UI forms. Security measures include input validation, CSRF protection, password hashing, and regular vulnerability scans.

# 10. Deployment & User Documentation
Deployment is straightforward: upload files to cPanel, configure Apache, set up the MySQL database, and add payment gateway credentials. The user guide walks each role through their tasks, with screenshots and troubleshooting tips included. Maintenance instructions cover updates, backups, and system health checks.

# 11. System Requirements
- Backend: PHP 7.0 or later
- Database: MySQL 5.7 or later
- Frontend: HTML, CSS (Bootstrap), JavaScript
- Server: Apache
- Dependencies: Stripe/PayPal SDKs, Bootstrap, jQuery

# 12. Server Requirements
- CPU: Minimum Intel Core i3, Recommended i5/i7
- RAM: Minimum 4GB, Recommended 8GB
- Storage: Minimum 25GB SSD, Recommended 100GB+

# 13. Work Plan and Milestones
| Activity               | Weeks      |
|------------------------|------------|
| Requirement Analysis   | 1-2        |
| Design                 | 3-4        |
| Coding                 | 5-8        |
| Testing                | 9-13       |
| Documentation          | 14-15      |

# 14. Purpose and Future Use
Ultimately, I want this system to help charity shops run more efficiently, keep better records, and engage donors and volunteers more effectively. With transparent reporting and scalable architecture, it can be adapted for other organizations and expanded with new features as needs evolve.

---
If you would like to see diagrams, screenshots, or more details about any part of the system, please let me know. I'm happy to provide additional information or walk you through specific features.