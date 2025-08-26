<?php
/**
 * Signup Page
 * 
 * This page handles user registration.
 */

// Check if system is installed
if (!file_exists('config/installed.lock')) {
    header('Location: install/index.php');
    exit;
}

// Include initialization file
require_once 'config/init.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error_message = '';
$success_message = '';

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validate_csrf_or_die();
    
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = 'Email already registered.';
        } else {
            // Check if this is the first user (will be admin)
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get role ID (admin for first user, customer for others)
            $roleName = $userCount === 0 ? 'admin' : 'customer';
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$roleName]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            $role_id = $role ? $role['id'] : null;
            
            if (!$role_id) {
                $error_message = 'System error: Default role not found.';
            } else {
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
                if ($stmt->execute([$first_name, $last_name, $email, password_hash($password, PASSWORD_DEFAULT), $role_id])) {
                    $success_message = 'Registration successful! Please login.';
                } else {
                    $error_message = 'Error creating account. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Charity Shop Management System</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#10B981',
                        accent: '#F59E0B'
                    },
                    fontFamily: {
                        'display': ['Inter', 'sans-serif'],
                        'body': ['Inter', 'sans-serif']
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.8s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'float': 'float 6s ease-in-out infinite'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="bg-gray-50 font-body">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg animate-fade-in">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Create your account</h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <a href="login.php" class="font-medium text-primary hover:text-primary-dark">sign in to your account</a>
                </p>
            </div>

            <?php if ($error_message): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ri-error-warning-line text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error_message); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ri-checkbox-circle-line text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700"><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="signup.php" method="POST">
                <?php echo csrf_field(); ?>
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="first_name" class="sr-only">First Name</label>
                            <input id="first_name" name="first_name" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="First Name">
                        </div>
                        <div>
                            <label for="last_name" class="sr-only">Last Name</label>
                            <input id="last_name" name="last_name" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Last Name">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Email address">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Password">
                    </div>
                    <div>
                        <label for="confirm_password" class="sr-only">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Confirm Password">
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="ri-user-add-line text-primary-light group-hover:text-primary-lighter"></i>
                        </span>
                        Sign up
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>