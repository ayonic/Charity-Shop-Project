# Admin Files Migration Plan

This document outlines the migration plan for admin files to their new locations.

## Files to Migrate

1. `admin/dashboard.php` → `/dashboard.php`
   - Main admin dashboard functionality
   - Role switching buttons
   - Admin-specific features

2. `admin/add_item.php` → `/inventory.php`
   - Item addition functionality to be integrated into inventory management

3. `admin/edit_item.php` → `/inventory.php`
   - Item editing functionality to be integrated into inventory management

4. `admin/inventory.php` → `/inventory.php`
   - Merge inventory management features

5. `admin/add_user.php` → `/users.php`
   - User creation functionality to be integrated into user management

6. `admin/edit_user.php` → `/users.php`
   - User editing functionality to be integrated into user management

7. `admin/delete_user.php` → `/users.php`
   - User deletion functionality to be integrated into user management

8. `admin/users.php` → `/users.php`
   - Merge user management features

9. `admin/payment_settings.php` → `/payment_settings.php`
   - Payment configuration and settings

10. `admin/test_gateway.php` → `/payment_settings.php`
    - Payment gateway testing functionality

11. `admin/switch_role.php` → `/includes/switch_role.php`
    - Role switching functionality to be moved to includes directory

## Migration Steps

1. Create backup of existing files
2. Migrate each file to its new location
3. Update all references and paths
4. Test functionality in new locations
5. Remove admin directory after successful migration