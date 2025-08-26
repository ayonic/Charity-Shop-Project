<?php
require_once 'config/init.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = false;

// Check if this is the first user
$result = db_query('SELECT COUNT(*) as count FROM users');
$user_count = $result->fetch_assoc()['count'];
$is_first_user = ($user_count === 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validate_csrf_or_die();
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // If first user, force admin role
    $role = $is_first_user ? 'admin' : trim($_POST['role'] ?? '');

    // Validation
    if (empty($first_name)) $errors[] = 'First name is required';
    if (empty($last_name)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
    // Validate role selection
    if ($is_first_user) {
        if ($role !== 'admin') $errors[] = 'First user must be an administrator';
    } else {
        if (!in_array($role, ['moderator', 'volunteer', 'donor', 'customer'])) $errors[] = 'Invalid role selected';
    }

    // Check if email already exists
    $stmt = $connection->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) $errors[] = 'Email already exists';

    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Insert user
            // Start transaction
            $connection->begin_transaction();
            
            // Create roles if first user
            if ($is_first_user) {
                // Create all basic roles
                $roles = [
                    ['admin', 'Administrator with full system access'],
                    ['moderator', 'Content moderator with limited administrative access'],
                    ['volunteer', 'Volunteer with access to volunteer-specific features'],
                    ['donor', 'Donor with access to donation features'],
                    ['customer', 'Customer with basic shopping access']
                ];
                
                foreach ($roles as $role_data) {
                    $stmt = $connection->prepare('INSERT INTO roles (name, description) VALUES (?, ?)');
                    $stmt->bind_param('ss', $role_data[0], $role_data[1]);
                    $stmt->execute();
                    
                    if ($role_data[0] === 'admin') {
                        $admin_role_id = $connection->insert_id;
                    }
                }
                
                // Admin role ID is already set from the roles creation loop
                
                // Create basic permissions
                $permissions = [
                    ['manage_users', 'User management', 'admin'],
                    ['manage_roles', 'Role management', 'admin'],
                    ['manage_inventory', 'Inventory management', 'admin'],
                    ['manage_donations', 'Donation management', 'admin'],
                    ['manage_sales', 'Sales management', 'admin'],
                    ['manage_settings', 'System settings', 'admin']
                ];
                
                foreach ($permissions as $perm) {
                    $stmt = $connection->prepare('INSERT INTO permissions (name, description, category) VALUES (?, ?, ?)');
                    $stmt->bind_param('sss', $perm[0], $perm[1], $perm[2]);
                    $stmt->execute();
                    $perm_id = $connection->insert_id;
                    
                    // Assign permission to admin role
                    $stmt = $connection->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
                    $stmt->bind_param('ii', $admin_role_id, $perm_id);
                    $stmt->execute();
                }
            }
            
            // Get role_id
            $stmt = $connection->prepare('SELECT id FROM roles WHERE name = ?');
            $stmt->bind_param('s', $role);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if (!$row) {
                throw new Exception('Role not found');
            }
            $role_id = $row['id'];
            
            // Insert user
            $stmt = $connection->prepare('INSERT INTO users (first_name, last_name, email, password, role_id, status) VALUES (?, ?, ?, ?, ?, "active")');
            $stmt->bind_param('ssssi', $first_name, $last_name, $email, $hashed_password, $role_id);            
            $stmt->execute();
            
            // Log activity
            $user_id = $connection->insert_id;
            $stmt = $connection->prepare('INSERT INTO activities (user_id, activity_type, description) VALUES (?, ?, ?)');
            $activity_type = 'registration';
            $description = 'New user registered with role: ' . $role;
            $stmt->bind_param('iss', $user_id, $activity_type, $description);
            $stmt->execute();
            
            // Commit transaction
            $connection->commit();
            $success = true;
        } catch (Exception $e) {
            $connection->rollback();
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Setup - Charity Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Role Setup</h2>
                <?php if ($is_first_user): ?>
                    <p class="mt-2 text-center text-sm text-gray-600">Create your administrator account</p>
                <?php else: ?>
                    <p class="mt-2 text-center text-sm text-gray-600">Create your account with appropriate role</p>
                <?php endif; ?>    
            </div>

            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Account created successfully.</span>
                <a href="login.php" class="block mt-2 text-green-700 underline">Proceed to login</a>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <?php foreach ($errors as $error): ?>
                <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST">
                <?php echo csrf_field(); ?>
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="first_name" class="sr-only">First Name</label>
                            <input id="first_name" name="first_name" type="text" required 
                                class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                                placeholder="First Name">
                        </div>
                        <div>
                            <label for="last_name" class="sr-only">Last Name</label>
                            <input id="last_name" name="last_name" type="text" required 
                                class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                                placeholder="Last Name">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Email address">
                    </div>
                    <?php if (!$is_first_user): ?>
                    <div class="mb-4">
                        <label for="role" class="sr-only">Role</label>
                        <?php if ($is_first_user): ?>
                            <input type="hidden" name="role" value="admin">
                        <?php else: ?>
                        <select id="role" name="role" required 
                            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                            <option value="">Select your role</option>
                            <option value="moderator">Moderator</option>
                            <option value="volunteer">Volunteer</option>
                            <option value="donor">Donor</option>
                            <option value="customer">Customer</option>
                        </select>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="role" value="admin">
                    <?php endif; ?>
                    <div class="mb-4">
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" required 
                            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Password">
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="sr-only">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Confirm Password">
                    </div>

                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Account
                    </button>
                </div>

                <div class="text-sm text-center">
                    <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">Already have an account? Sign in</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>