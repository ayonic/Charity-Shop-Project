<?php

class PaymentProcessor {
    private $db;
    private $gateways = [];
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
        $this->loadGateways();
    }
    
    private function loadGateways() {
        // Load all enabled payment gateways
        $query = "SELECT * FROM payment_gateways WHERE is_enabled = 1";
        $stmt = $this->db->query($query);
        
        while ($gateway = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $config = json_decode($gateway['configuration'], true);
            $className = ucfirst($gateway['code']) . 'Gateway';
            $classFile = __DIR__ . '/' . $className . '.php';
            
            if (file_exists($classFile)) {
                require_once $classFile;
                $this->gateways[$gateway['code']] = [
                    'instance' => new $className($config, $gateway['test_mode']),
                    'config' => $gateway
                ];
            }
        }
    }
    
    public function getEnabledGateways() {
        $gateways = [];
        foreach ($this->gateways as $code => $gateway) {
            $gateways[] = [
                'code' => $code,
                'name' => $gateway['config']['name'],
                'description' => $gateway['config']['description'],
                'test_mode' => $gateway['config']['test_mode']
            ];
        }
        return $gateways;
    }
    
    public function processPayment($gatewayCode, $amount, $currency, $description, $metadata = []) {
        if (!isset($this->gateways[$gatewayCode])) {
            throw new Exception('Payment gateway not found or disabled');
        }
        
        try {
            $gateway = $this->gateways[$gatewayCode]['instance'];
            $result = $gateway->processPayment($amount, $currency, $description, $metadata);
            
            // Additional processing if needed
            if ($result['success']) {
                // Update order status, send notifications, etc.
                $this->handleSuccessfulPayment($result, $metadata);
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_activity('payment_error', 'Payment processing failed', [
                'gateway' => $gatewayCode,
                'error' => $e->getMessage(),
                'metadata' => $metadata
            ]);
            throw $e;
        }
    }
    
    public function processRefund($gatewayCode, $transactionId, $amount = null) {
        if (!isset($this->gateways[$gatewayCode])) {
            throw new Exception('Payment gateway not found or disabled');
        }
        
        try {
            $gateway = $this->gateways[$gatewayCode]['instance'];
            $result = $gateway->processRefund($transactionId, $amount);
            
            if ($result['success']) {
                // Update order status, send notifications, etc.
                $this->handleSuccessfulRefund($result, $transactionId);
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_activity('refund_error', 'Refund processing failed', [
                'gateway' => $gatewayCode,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    private function handleSuccessfulPayment($result, $metadata) {
        // Update order status if this is an order payment
        if (isset($metadata['order_id'])) {
            $query = "UPDATE orders 
                     SET payment_status = 'paid',
                         updated_at = NOW()
                     WHERE id = :order_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':order_id' => $metadata['order_id']]);
            
            // Send confirmation email to customer
            // TODO: Implement email notification
        }
        
        // Update donation status if this is a donation payment
        if (isset($metadata['donation_id'])) {
            $query = "UPDATE donations 
                     SET payment_status = 'paid',
                         updated_at = NOW()
                     WHERE id = :donation_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':donation_id' => $metadata['donation_id']]);
            
            // Send thank you email to donor
            // TODO: Implement email notification
        }
    }
    
    private function handleSuccessfulRefund($result, $transactionId) {
        // Get the original transaction
        $query = "SELECT reference_id, type FROM payment_transactions 
                 WHERE gateway_transaction_id = :transaction_id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':transaction_id' => $transactionId]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transaction) {
            // Update order or donation status based on transaction type
            switch ($transaction['type']) {
                case 'order':
                    $query = "UPDATE orders 
                             SET payment_status = 'refunded',
                                 updated_at = NOW()
                             WHERE id = :id";
                    break;
                    
                case 'donation':
                    $query = "UPDATE donations 
                             SET payment_status = 'refunded',
                                 updated_at = NOW()
                             WHERE id = :id";
                    break;
            }
            
            if (isset($query)) {
                $stmt = $this->db->prepare($query);
                $stmt->execute([':id' => $transaction['reference_id']]);
                
                // Send refund notification email
                // TODO: Implement email notification
            }
        }
    }
}