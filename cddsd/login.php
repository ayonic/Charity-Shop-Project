<?php
/**
 * Login Page
 * 
 * This page handles user authentication.
 */

// Check if system is installed
if (!file_exists('config/installed.lock')) {
    header('Location: install/index.php');
    exit;
}

// Include initialization file
require_once 'config/init.php';

// Handle existing session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND u.status = 'active'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        switch ($user['role_name']) {
            case 'admin':
                redirect('dashboard.php');
                break;
            case 'customer':
                redirect('customer/dashboard.php');
                break;
            case 'donor':
                redirect('donor/dashboard.php');
                break;
            case 'volunteer':
                redirect('volunteer/dashboard.php');
                break;
            case 'manager':
                redirect('manager/dashboard.php');
                break;
            case 'moderator':
                redirect('dashboard.php'); // or create moderator dashboard if needed
                break;
            default:
                redirect('dashboard.php');
                break;
        }
    } else {
        // Invalid session, clear it
        session_destroy();
    }
}

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    validate_csrf_or_die();
    
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        // Check user credentials using prepared statement
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['user_role'] = $user['role_name'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            set_flash_message('success', 'Welcome back, ' . $user['first_name'] . '!');
            switch ($user['role_name']) {
                case 'admin':
                    redirect('dashboard.php');
                    break;
                case 'customer':
                    redirect('customer/dashboard.php');
                    break;
                case 'donor':
                    redirect('donor/dashboard.php');
                    break;
                case 'manager':
                    redirect('manager/dashboard.php');
                    break;
                case 'volunteer':
                    redirect('volunteer/dashboard.php');
                    break;
                default:
                    redirect('dashboard.php');
                    break;
            }
        } else {
            $error_message = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Charity Shop Management System</title>
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
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            transform: translateY(-24px) scale(0.8);
            color: #4F46E5;
        }
        
        .input-group label {
            position: absolute;
            left: 12px;
            top: 12px;
            transition: all 0.2s ease;
            pointer-events: none;
            color: #6B7280;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338CA 0%, #6D28D9 100%);
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body class="font-body">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-md w-full space-y-8 animate-fade-in">
            <!-- Header -->
            <div class="flex justify-center space-x-4 mb-4">
                <a href="login.php" class="text-lg font-semibold text-white border-b-2 border-white pb-2">Login</a>
                <a href="signup.php" class="text-lg font-semibold text-white/70 hover:text-white hover:border-b-2 hover:border-white pb-2">Sign Up</a>
            </div>
            <div class="text-center">
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-white shadow-lg mb-6 animate-float">
                    <i class="ri-store-2-line text-primary text-3xl"></i>
                </div>
                <h2 class="text-4xl font-bold text-white mb-2">
                    Welcome Back
                </h2>
                <h1 class="text-2xl font-display text-white/90 mb-2">Charity Shop</h1>
                <p class="text-white/70">
                    Sign in to your management system
                </p>
            </div>

            <!-- Login Form -->
            <div class="login-card rounded-2xl p-8 animate-slide-up">
                <?php if ($error_message): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center animate-slide-up">
                        <i class="ri-error-warning-line text-red-500 mr-3"></i>
                        <span><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>
                
                <form class="space-y-6" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-4">
                        <div class="input-group">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   placeholder=" "
                                   class="appearance-none relative block w-full px-3 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent focus:z-10 transition duration-200" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <label for="email">Email address</label>
                        </div>
                        
                        <div class="input-group">
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                   placeholder=" "
                                   class="appearance-none relative block w-full px-3 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent focus:z-10 transition duration-200">
                            <label for="password">Password</label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" 
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="#" class="font-medium text-primary hover:text-indigo-500 transition duration-200">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="btn-primary group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i class="ri-lock-line text-indigo-300 group-hover:text-indigo-200"></i>
                            </span>
                            Sign in to your account
                        </button>
                    </div>
                </form>
                

            </div>
        </div>
    </div>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus email field
            document.getElementById('email').focus();
            
            // Add loading state to form submission
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i>Signing in...';
            });
            
            // Add demo account quick fill
            const demoAccounts = document.querySelectorAll('.text-primary');
            demoAccounts.forEach(account => {
                if (account.textContent.includes('@')) {
                    account.style.cursor = 'pointer';
                    account.addEventListener('click', function() {
                        document.getElementById('email').value = this.textContent;
                        document.getElementById('password').value = 'password123';
                        document.getElementById('email').dispatchEvent(new Event('input'));
                        document.getElementById('password').dispatchEvent(new Event('input'));
                    });
                }
            });
        });
    </script>
</body>
</html>
