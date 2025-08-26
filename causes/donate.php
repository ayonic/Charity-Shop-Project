<?php
require_once '../config/init.php';
require_once INCLUDES_PATH . '/dashboard-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    set_flash_message('error', 'Please log in to make a donation.');
    redirect('../login.php');
}

// Get cause ID from URL
$cause_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$cause_id) {
    set_flash_message('error', 'Invalid cause ID');
    redirect('../causes/index.php');
}

// Get cause details
$cause_query = "SELECT * FROM causes WHERE id = ? AND status = 'active'";
$cause = db_fetch_row($cause_query, [$cause_id]);

if (!$cause) {
    set_flash_message('error', 'Cause not found or inactive');
    redirect('../causes/index.php');
}

// Handle donation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $anonymous = isset($_POST['anonymous']) ? 1 : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate input
    $errors = [];
    if ($amount <= 0) {
        $errors[] = 'Please enter a valid donation amount';
    }
    if (empty($payment_method)) {
        $errors[] = 'Please select a payment method';
    }

    if (empty($errors)) {
        try {
            // Start transaction
            db_query("START TRANSACTION");

            // Create donation record
            $donation_query = "INSERT INTO donations (donor_id, cause_id, amount, payment_method, anonymous, message, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $donation_id = db_insert($donation_query, [
                $_SESSION['user_id'],
                $cause_id,
                $amount,
                $payment_method,
                $anonymous,
                $message
            ]);

            if ($donation_id) {
                // Initialize payment gateway
                $gateway_class = ucfirst($payment_method) . 'Gateway';
                $gateway_file = INCLUDES_PATH . '/payment/' . $gateway_class . '.php';

                if (file_exists($gateway_file)) {
                    require_once $gateway_file;
                    $gateway = new $gateway_class();

                    // Process payment
                    $payment_result = $gateway->processPayment([
                        'amount' => $amount,
                        'currency' => 'GBP',
                        'donation_id' => $donation_id,
                        'cause_name' => $cause['name'],
                        'donor_email' => $_SESSION['user_email']
                    ]);

                    if ($payment_result['success']) {
                        // Update donation status
                        db_query("UPDATE donations SET status = 'completed', transaction_id = ? WHERE id = ?", 
                                [$payment_result['transaction_id'], $donation_id]);

                        // Update cause statistics
                        db_query("UPDATE causes SET total_donations = total_donations + 1, 
                                  total_amount = total_amount + ? WHERE id = ?", 
                                [$amount, $cause_id]);

                        db_query("COMMIT");
                        set_flash_message('success', 'Thank you for your donation!');
                        redirect('../donor/dashboard.php');
                    } else {
                        throw new Exception($payment_result['error'] ?? 'Payment processing failed');
                    }
                } else {
                    throw new Exception('Payment method not supported');
                }
            } else {
                throw new Exception('Failed to create donation record');
            }
        } catch (Exception $e) {
            db_query("ROLLBACK");
            set_flash_message('error', 'Error processing donation: ' . $e->getMessage());
        }
    } else {
        set_flash_message('error', implode('<br>', $errors));
    }
}

// Get available payment methods
$payment_methods_query = "SELECT * FROM payment_gateways WHERE status = 'active'";
$payment_methods = db_fetch_all($payment_methods_query);
?>

<div class="container px-6 mx-auto">
    <div class="my-6">
        <h2 class="text-2xl font-semibold text-gray-700">Make a Donation</h2>
        <p class="mt-2 text-gray-600">Support <?php echo htmlspecialchars($cause['name']); ?></p>
    </div>

    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <!-- Cause Information -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
            <h4 class="mb-4 font-semibold text-gray-600">Cause Details</h4>
            <div class="mb-4">
                <h5 class="text-lg font-semibold text-gray-700"><?php echo htmlspecialchars($cause['name']); ?></h5>
                <p class="mt-2 text-gray-600"><?php echo nl2br(htmlspecialchars($cause['description'])); ?></p>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Category:</p>
                    <p class="font-medium"><?php echo htmlspecialchars($cause['category']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Goal:</p>
                    <p class="font-medium">£<?php echo number_format($cause['goal_amount'], 2); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Raised:</p>
                    <p class="font-medium">£<?php echo number_format($cause['total_amount'], 2); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Donors:</p>
                    <p class="font-medium"><?php echo $cause['total_donations']; ?></p>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <?php $progress = ($cause['total_amount'] / $cause['goal_amount']) * 100; ?>
                <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo min(100, $progress); ?>%"></div>
            </div>
            <p class="mt-2 text-sm text-gray-600 text-right"><?php echo round($progress); ?>% of goal reached</p>
        </div>

        <!-- Donation Form -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-xs">
            <h4 class="mb-4 font-semibold text-gray-600">Donation Details</h4>
            <form action="donate.php?id=<?php echo $cause_id; ?>" method="POST">
                <!-- Amount -->
                <div class="mb-4">
                    <label class="block text-sm">
                        <span class="text-gray-700">Donation Amount</span>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">£</span>
                            </div>
                            <input type="number" name="amount" min="1" step="0.01" required
                                   class="block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="0.00">
                        </div>
                    </label>
                </div>

                <!-- Payment Method -->
                <div class="mb-4">
                    <label class="block text-sm">
                        <span class="text-gray-700">Payment Method</span>
                        <select name="payment_method" required
                                class="block w-full mt-1 text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Select payment method</option>
                            <?php foreach ($payment_methods as $method): ?>
                                <option value="<?php echo htmlspecialchars($method['code']); ?>">
                                    <?php echo htmlspecialchars($method['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <!-- Anonymous Donation -->
                <div class="mb-4">
                    <label class="inline-flex items-center text-gray-600">
                        <input type="checkbox" name="anonymous" value="1"
                               class="text-purple-600 form-checkbox focus:border-purple-400 focus:outline-none focus:shadow-outline-purple">
                        <span class="ml-2">Make this donation anonymous</span>
                    </label>
                </div>

                <!-- Message -->
                <div class="mb-4">
                    <label class="block text-sm">
                        <span class="text-gray-700">Message (Optional)</span>
                        <textarea name="message"
                                  class="block w-full mt-1 text-sm border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"
                                  rows="3"
                                  placeholder="Leave a message of support..."></textarea>
                    </label>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Donate Now
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/dashboard-footer.php'; ?>