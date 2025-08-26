<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../includes/payment/PayPalGateway.php';

// Get webhook payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Get PayPal signature from headers
$signature = [
    'auth_algo' => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
    'cert_url' => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
    'transmission_id' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
    'transmission_sig' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
    'transmission_time' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? ''
];

// Initialize PayPal gateway
$query = "SELECT * FROM payment_gateways WHERE gateway_code = 'paypal' AND is_enabled = 1 LIMIT 1";
$gateway = db_query($query)->fetch(PDO::FETCH_ASSOC);

if (!$gateway) {
    http_response_code(503);
    exit('PayPal gateway not configured');
}

$config = json_decode($gateway['config'], true);
$paypal = new PayPalGateway($config, true, $gateway['test_mode']);

// Validate webhook
if (!$paypal->validateWebhook($payload, $signature)) {
    http_response_code(400);
    exit('Invalid webhook signature');
}

// Process webhook event
switch ($data['event_type']) {
    case 'PAYMENT.CAPTURE.COMPLETED':
        $resource = $data['resource'];
        $transactionId = $resource['id'];
        $amount = $resource['amount']['value'];
        $currency = $resource['amount']['currency_code'];
        
        // Update payment status in database
        $query = "UPDATE payment_transactions 
                 SET status = 'completed', 
                     updated_at = NOW() 
                 WHERE transaction_id = :transaction_id";
        db_query($query, [':transaction_id' => $transactionId]);
        break;
        
    case 'PAYMENT.CAPTURE.REFUNDED':
        $resource = $data['resource'];
        $transactionId = $resource['id'];
        
        // Update payment status in database
        $query = "UPDATE payment_transactions 
                 SET status = 'refunded', 
                     updated_at = NOW() 
                 WHERE transaction_id = :transaction_id";
        db_query($query, [':transaction_id' => $transactionId]);
        break;
        
    case 'PAYMENT.CAPTURE.DENIED':
        $resource = $data['resource'];
        $transactionId = $resource['id'];
        
        // Update payment status in database
        $query = "UPDATE payment_transactions 
                 SET status = 'failed', 
                     updated_at = NOW() 
                 WHERE transaction_id = :transaction_id";
        db_query($query, [':transaction_id' => $transactionId]);
        break;
}

// Return success response
http_response_code(200);
echo 'Webhook processed successfully';