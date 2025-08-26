<?php
/**
 * User Account Management Page
 */

require_once 'config/init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    set_flash_message('error', 'Please login to access your account.');
    redirect('login.php');
}

// Get user data
$user_id = $_SESSION['user_id'];
$user = db_fetch_row("SELECT * FROM users WHERE id = ?", [$user_id]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validate inputs
    $errors = [];
    if (empty($first_name)) $errors[] = 'First name is required';
    if (empty($last_name)) $errors[] = 'Last name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    
    if (empty($errors)) {
        // Update user information
        $result = db_query(
            "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?",
            [$first_name, $last_name, $email, $phone, $user_id]
        );
        
        if ($result) {
            set_flash_message('success', 'Account information updated successfully.');
            redirect('account.php');
        } else {
            set_flash_message('error', 'Failed to update account information.');
        }
    }
}

// Include header
include 'includes/public-header.php';
?>

<!-- Account Management Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Account Settings</h1>
        
        <?php display_flash_messages(); ?>
        
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Personal Information</h2>
            <form action="account.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                        <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required
                               class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                        <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required
                               class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required
                           class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           class="mt-1 focus:ring-primary focus:border-primary block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-primary text-white font-semibold py-2 px-4 rounded hover:bg-primary-dark transition duration-150">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-semibold mb-4">Password</h2>
            <p class="text-gray-600 mb-4">Want to change your password?</p>
            <a href="change-password.php" class="inline-block bg-gray-100 text-gray-800 font-semibold py-2 px-4 rounded hover:bg-gray-200 transition duration-150">
                Change Password
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/public-footer.php'; ?>

