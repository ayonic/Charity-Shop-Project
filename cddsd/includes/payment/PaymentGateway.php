<?php

abstract class PaymentGateway {
    protected $config;
    protected $isEnabled;
    protected $isTestMode;
    protected $pdo;
    protected $logger;
    
    public function __construct(array $config = [], bool $isEnabled = false, bool $isTestMode = true) {
        global $pdo;
        $this->pdo = $pdo;
        $this->config = $config;
        $this->isEnabled = $isEnabled;
        $this->isTestMode = $isTestMode;
        $this->initializeLogger();
    }
    
    protected function initializeLogger() {
        $logDir = __DIR__ . '/../../logs/payments';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/' . strtolower(get_class($this)) . '.log';
        error_log("Payment Gateway Log: " . date('Y-m-d H:i:s') . " - Initializing " . get_class($this) . "\n", 3, $logFile);
    }
    
    public function isEnabled(): bool {
        return $this->isEnabled;
    }
    
    public function isTestMode(): bool {
        return $this->isTestMode;
    }
    
    abstract public function processPayment($amount, $currency, $description, $metadata = []);
    abstract public function processRefund($transactionId, $amount = null);
    abstract public function validateWebhook($payload, $signature);
    
    public function testConnection() {
        if (!$this->validateConfig()) {
            throw new Exception('Invalid gateway configuration');
        }
        return true;
    }
    
    abstract public function validateConfig(): bool;
    
    protected function createPaymentTransaction($data) {
        $query = "INSERT INTO payment_transactions 
                 (transaction_type, reference_id, payment_method_id, gateway_id, amount, currency,
                  status, gateway_transaction_id, gateway_status, gateway_response, created_by)
                 VALUES 
                 (:type, :ref_id, :method_id, :gateway_id, :amount, :currency,
                  :status, :gateway_txn_id, :gateway_status, :gateway_response, :created_by)";
                  
        $params = [
            ':type' => $data['type'],
            ':ref_id' => $data['reference_id'],
            ':method_id' => $data['payment_method_id'],
            ':gateway_id' => $data['gateway_id'],
            ':amount' => $data['amount'],
            ':currency' => $data['currency'],
            ':status' => $data['status'],
            ':gateway_txn_id' => $data['gateway_transaction_id'],
            ':gateway_status' => $data['gateway_status'],
            ':gateway_response' => json_encode($data['gateway_response']),
            ':created_by' => $_SESSION['user_id'] ?? 0
        ];
        
        return db_query($query, $params);
    }
    
    protected function updatePaymentTransaction($transactionId, $data) {
        $query = "UPDATE payment_transactions 
                 SET status = :status,
                     gateway_status = :gateway_status,
                     gateway_response = :gateway_response
                 WHERE gateway_transaction_id = :txn_id";
                 
        $params = [
            ':status' => $data['status'],
            ':gateway_status' => $data['gateway_status'],
            ':gateway_response' => json_encode($data['gateway_response']),
            ':txn_id' => $transactionId
        ];
        
        return db_query($query, $params);
    }
    
    protected function logError($message, $context = []) {
        $logFile = __DIR__ . '/../../logs/payments/' . strtolower(get_class($this)) . '.log';
        $logMessage = date('Y-m-d H:i:s') . " ERROR: " . $message . "\n";
        if (!empty($context)) {
            $logMessage .= "Context: " . json_encode($context) . "\n";
        }
        error_log($logMessage, 3, $logFile);
        
        $query = "INSERT INTO error_logs 
                 (error_type, error_message, error_code, request_data)
                 VALUES 
                 ('payment_gateway', :message, :code, :data)";
                 
        $params = [
            ':message' => $message,
            ':code' => $context['code'] ?? null,
            ':data' => json_encode($context)
        ];
        
        return db_query($query, $params);
    }
    
    protected function logInfo($message, $context = []) {
        $logFile = __DIR__ . '/../../logs/payments/' . strtolower(get_class($this)) . '.log';
        $logMessage = date('Y-m-d H:i:s') . " INFO: " . $message . "\n";
        if (!empty($context)) {
            $logMessage .= "Context: " . json_encode($context) . "\n";
        }
        error_log($logMessage, 3, $logFile);
    }
    
    public function validateConfig(): bool {
        return !empty($this->config);
    }
}