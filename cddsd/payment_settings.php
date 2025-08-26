<?php
/**
 * Payment Settings Page
 * 
 * This page handles configuration of payment gateways and their settings.
 */

// Include initialization file
require_once 'config/init.php';
require_once 'includes/payment/PaymentProcessor.php';

// Require admin access
require_admin();

// Get user role
$user_role = get_user_role();

$success_message = '';
$error_message = '';
$payment_processor = new PaymentProcessor();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $gateway_code = $_POST['gateway_code'];
        $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;
        $test_mode = isset($_POST['test_mode']) ? 1 : 0;
        
        // Prepare configuration based on gateway type
        $config = [];
        switch ($gateway_code) {
            case 'bank_transfer':
                $config['bank_name'] = $_POST['bank_name'];
                $config['account_name'] = $_POST['account_name'];
                $config['account_number'] = $_POST['account_number'];
                $config['sort_code'] = $_POST['sort_code'];
                $config['iban'] = $_POST['iban'];
                $config['swift_bic'] = $_POST['swift_bic'];
                break;
            case 'stripe':
                $config['publishable_key'] = $_POST['stripe_publishable_key'];
                $config['secret_key'] = $_POST['stripe_secret_key'];
                $config['webhook_secret'] = $_POST['stripe_webhook_secret'];
                break;
            case 'paypal':
                $config['client_id'] = $_POST['paypal_client_id'];
                $config['client_secret'] = $_POST['paypal_client_secret'];
                $config['webhook_id'] = $_POST['paypal_webhook_id'];
                break;
        }
        
        // Update gateway settings
        $query = "UPDATE payment_gateways 
                 SET is_enabled = :is_enabled,
                     test_mode = :test_mode,
                     configuration = :configuration,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE code = :code";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':is_enabled' => $is_enabled,
            ':test_mode' => $test_mode,
            ':configuration' => json_encode($config),
            ':code' => $gateway_code
        ]);
        
        $success_message = 'Payment gateway settings updated successfully.';
    } catch (Exception $e) {
        $error_message = 'Error updating payment gateway settings: ' . $e->getMessage();
    }
}

// Get current gateway settings
$query = "SELECT * FROM payment_gateways ORDER BY name";
$gateways = db_fetch_all($query);

// Include header
include_once INCLUDES_PATH . '/header.php';
?>

<?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
<?php include_once INCLUDES_PATH . '/content-start.php'; ?>

<!-- Payment Settings Header -->
<div class="mb-8">
    <h1 class="text-2xl font-semibold text-gray-900">Payment Settings</h1>
    <p class="mt-1 text-sm text-gray-600">Configure and manage your payment gateway settings.</p>
</div>

<div class="max-w-7xl mx-auto">
    <?php if ($success_message): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-8">
        <!-- Bank Transfer Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 lg:col-span-2">
            <div class="flex items-center mb-4">
                <i class="ri-bank-line text-2xl text-primary mr-2"></i>
                <h2 class="text-xl font-semibold">Bank Transfer Configuration</h2>
            </div>
            <form method="POST" class="space-y-4 max-w-3xl mx-auto">
                <input type="hidden" name="gateway_code" value="bank_transfer">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                        <input type="text" name="bank_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="<?php echo htmlspecialchars($config['bank_name'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Name</label>
                        <input type="text" name="account_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="<?php echo htmlspecialchars($config['account_name'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Number</label>
                        <input type="text" name="account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="<?php echo htmlspecialchars($config['account_number'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sort Code</label>
                        <input type="text" name="sort_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="<?php echo htmlspecialchars($config['sort_code'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">IBAN</label>
                        <input type="text" name="iban" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="<?php echo htmlspecialchars($config['iban'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SWIFT/BIC</label>
                        <input type="text" name="swift_bic" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" value="<?php echo htmlspecialchars($config['swift_bic'] ?? ''); ?>">
                    </div>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_enabled" class="rounded border-gray-300 text-primary focus:ring-primary" <?php echo ($gateway['is_enabled'] ?? false) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm text-gray-600">Enable Bank Transfer</span>
                    </label>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full md:w-auto flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Save Bank Transfer Settings</button>
                </div>
            </form>
        </div>

        <!-- Stripe Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 lg:col-span-2 mt-6">
            <div class="flex items-center mb-4">
                <i class="ri-bank-card-line text-2xl text-primary mr-2"></i>
                <h2 class="text-xl font-semibold">Stripe Configuration</h2>
            </div>
            <form method="POST" class="space-y-4 max-w-3xl mx-auto">
                <input type="hidden" name="gateway_code" value="stripe">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Publishable Key</label>
                        <input type="text" name="stripe_publishable_key" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm font-mono" value="<?php echo htmlspecialchars($config['publishable_key'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Secret Key</label>
                        <input type="password" name="stripe_secret_key" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm font-mono" value="<?php echo htmlspecialchars($config['secret_key'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Webhook Secret</label>
                        <input type="password" name="stripe_webhook_secret" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm font-mono" value="<?php echo htmlspecialchars($config['webhook_secret'] ?? ''); ?>">
                        <p class="mt-1 text-sm text-gray-500">Used for handling Stripe webhook events securely</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_enabled" class="rounded border-gray-300 text-primary focus:ring-primary" <?php echo ($gateway['is_enabled'] ?? false) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm text-gray-600">Enable Stripe</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="test_mode" class="rounded border-gray-300 text-primary focus:ring-primary" <?php echo ($gateway['test_mode'] ?? false) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm text-gray-600">Test Mode</span>
                    </label>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full md:w-auto flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Save Stripe Settings</button>
                </div>
            </form>
        </div>

        <!-- PayPal Settings -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 lg:col-span-2 mt-6">
            <div class="flex items-center mb-4">
                <i class="ri-paypal-line text-2xl text-primary mr-2"></i>
                <h2 class="text-xl font-semibold">PayPal Configuration</h2>
            </div>
            <form method="POST" class="space-y-4 max-w-3xl mx-auto">
                <input type="hidden" name="gateway_code" value="paypal">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Client ID</label>
                        <input type="text" name="paypal_client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm font-mono" value="<?php echo htmlspecialchars($config['client_id'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Client Secret</label>
                        <input type="password" name="paypal_client_secret" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm font-mono" value="<?php echo htmlspecialchars($config['client_secret'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Webhook ID</label>
                        <input type="text" name="paypal_webhook_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm font-mono" value="<?php echo htmlspecialchars($config['webhook_id'] ?? ''); ?>">
                        <p class="mt-1 text-sm text-gray-500">Required for handling PayPal IPN notifications</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4 mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_enabled" class="rounded border-gray-300 text-primary focus:ring-primary" <?php echo ($gateway['is_enabled'] ?? false) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm text-gray-600">Enable PayPal</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="test_mode" class="rounded border-gray-300 text-primary focus:ring-primary" <?php echo ($gateway['test_mode'] ?? false) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm text-gray-600">Sandbox Mode</span>
                    </label>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full md:w-auto flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">Save PayPal Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once INCLUDES_PATH . '/content-end.php'; ?>
<?php include_once INCLUDES_PATH . '/footer.php'; ?>