<?php
/**
 * Test Payment Gateway
 * 
 * This page allows administrators to test payment gateway configurations.
 */

require_once 'config/init.php';
require_once 'includes/payment/PaymentProcessor.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    set_flash_message('error', 'You must be an administrator to access this page.');
    redirect('login.php');
    exit;
}

// Get available payment gateways
$available_gateways = [
    'paypal' => 'PayPal',
    'stripe' => 'Stripe',
    'bank_transfer' => 'Bank Transfer'
];

// Get current gateway settings
$payment_settings = db_get_results("SELECT * FROM payment_settings");
$settings_map = [];
foreach ($payment_settings as $setting) {
    $settings_map[$setting['gateway'] . '_' . $setting['setting_key']] = $setting['setting_value'];
}

$test_result = '';
$test_status = '';

// Handle test request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gateway'])) {
    $gateway = sanitize_input($_POST['gateway']);
    
    if (!array_key_exists($gateway, $available_gateways)) {
        set_flash_message('error', 'Invalid payment gateway selected.');
        redirect('test_gateway.php');
        exit;
    }
    
    try {
        $processor = new PaymentProcessor();
        $gateway_instance = $processor->getGateway($gateway);
        
        if ($gateway_instance->testConnection()) {
            $test_status = 'success';
            $test_result = 'Connection test successful! The gateway is properly configured.';
            log_activity($_SESSION['user_id'], 'payment', "Tested {$available_gateways[$gateway]} gateway - Success");
        } else {
            $test_status = 'error';
            $test_result = 'Connection test failed. Please check your configuration settings.';
            log_activity($_SESSION['user_id'], 'payment', "Tested {$available_gateways[$gateway]} gateway - Failed");
        }
    } catch (Exception $e) {
        $test_status = 'error';
        $test_result = 'Error: ' . $e->getMessage();
        log_activity($_SESSION['user_id'], 'payment', "Tested {$available_gateways[$gateway]} gateway - Error: {$e->getMessage()}");
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Payment Gateway - Charity Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex overflow-hidden bg-gray-100">
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="flex-1 overflow-auto focus:outline-none" tabindex="0">
        <main class="flex-1 relative overflow-y-auto focus:outline-none">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-semibold text-gray-900">Test Payment Gateway</h1>
                        <a href="payment_settings.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md flex items-center">
                            <i class="ri-settings-3-line mr-2"></i>
                            Payment Settings
                        </a>
                    </div>
                    
                    <!-- Test Gateway Form -->
                    <div class="mt-6">
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
                            <form method="POST" class="space-y-6">
                                <div>
                                    <label for="gateway" class="block text-sm font-medium text-gray-700">Select Payment Gateway</label>
                                    <select name="gateway" id="gateway" required 
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="">Choose a gateway...</option>
                                        <?php foreach ($available_gateways as $key => $name): ?>
                                            <?php $enabled = isset($settings_map[$key . '_enabled']) && $settings_map[$key . '_enabled'] === '1'; ?>
                                            <option value="<?php echo $key; ?>" <?php echo !$enabled ? 'disabled' : ''; ?>>
                                                <?php echo $name; ?> <?php echo !$enabled ? '(Disabled)' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="ri-test-tube-line mr-2"></i>
                                        Test Connection
                                    </button>
                                </div>
                            </form>
                            
                            <?php if ($test_result): ?>
                                <div class="mt-6">
                                    <div class="rounded-md <?php echo $test_status === 'success' ? 'bg-green-50' : 'bg-red-50'; ?> p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <?php if ($test_status === 'success'): ?>
                                                    <i class="ri-checkbox-circle-fill text-green-400 text-xl"></i>
                                                <?php else: ?>
                                                    <i class="ri-error-warning-fill text-red-400 text-xl"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium <?php echo $test_status === 'success' ? 'text-green-800' : 'text-red-800'; ?>">
                                                    Test Result
                                                </h3>
                                                <div class="mt-2 text-sm <?php echo $test_status === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                                                    <?php echo htmlspecialchars($test_result); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Gateway Status Overview -->
                    <div class="mt-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Gateway Status Overview</h2>
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($available_gateways as $key => $name): ?>
                                    <?php 
                                    $enabled = isset($settings_map[$key . '_enabled']) && $settings_map[$key . '_enabled'] === '1';
                                    $test_mode = false;
                                    if ($key === 'paypal') {
                                        $test_mode = isset($settings_map['paypal_sandbox_mode']) && $settings_map['paypal_sandbox_mode'] === '1';
                                    } elseif ($key === 'stripe') {
                                        $test_mode = isset($settings_map['stripe_test_mode']) && $settings_map['stripe_test_mode'] === '1';
                                    }
                                    ?>
                                    <div class="px-4 py-4 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <span class="text-lg font-medium text-gray-900"><?php echo $name; ?></span>
                                                <?php if ($test_mode): ?>
                                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Test Mode
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($enabled): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Enabled
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Disabled
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>