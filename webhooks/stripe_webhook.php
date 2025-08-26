<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/payment/StripeGateway.php';

// Get webhook payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Initialize Stripe gateway
$query = "SELECT * FROM payment_gateways WHERE gateway_code = 'stripe' AND is_enabled = 1 LIMIT 1";
$gateway = db_query($query)->fetch(PDO::FETCH_ASSOC);

if (!$gateway) {
    http_response_code(503);
    exit('Stripe gateway not configured');
}

$config = json_decode($gateway['config'], true);
$stripe = new StripeGateway($config, true, $gateway['test_mode']);

// Validate webhook
if (!$stripe->validateWebhook($payload, ['stripe-signature' => $signature])) {
    http_response_code(400);
    exit('Invalid webhook signature');
}

$data = json_decode($payload, true);

// Process webhook event
switch ($data['type']) {
    case 'payment_intent.succeeded':
        $paymentIntent = $data['data']['object'];
        $transactionId = $paymentIntent['id'];
        $amount = $paymentIntent['amount'] / 100; // Convert from cents
        $currency = $paymentIntent['currency'];
        
        // Update payment status in database
        $query = "UPDATE payment_transactions 
                 SET status = 'completed', 
                     updated_at = NOW() 
                 WHERE transaction_id = :transaction_id";
        db_query($query, [':transaction_id' => $transactionId]);
        break;
        
    case 'charge.refunded':
        $charge = $data['data']['object'];
        $transactionId = $charge['payment_intent'];
        
        // Update payment status in database
        $query = "UPDATE payment_transactions 
                 SET status = 'refunded', 
                     updated_at = NOW() 
                 WHERE transaction_id = :transaction_id";
        db_query($query, [':transaction_id' => $transactionId]);
        break;
        
    case 'payment_intent.payment_failed':
        $paymentIntent = $data['data']['object'];
        $transactionId = $paymentIntent['id'];
        
        // Update payment status in database
        $query = "UPDATE payment_transactions 
                 SET status = 'failed', 
                     error_message = :error_message,
                     updated_at = NOW() 
                 WHERE transaction_id = :transaction_id";
        $params = [
            ':transaction_id' => $transactionId,
            ':error_message' => $paymentIntent['last_payment_error']['message'] ?? 'Payment failed'
        ];
        db_query($query, $params);
        break;
}

// Return success response
http_response_code(200);
echo 'Webhook processed successfully';