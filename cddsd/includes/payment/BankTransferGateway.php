<?php

require_once 'PaymentGateway.php';

class BankTransferGateway extends PaymentGateway {
    public function __construct($config, $isTestMode = true) {
        parent::__construct($config, $isTestMode);
    }
    
    public function processPayment($amount, $currency, $description, $metadata = []) {
        // Generate a unique reference number for the bank transfer
        $reference = 'BT-' . strtoupper(uniqid());
        
        // Store transaction details
        $transactionData = [
            'type' => $metadata['type'] ?? 'sale',
            'reference_id' => $metadata['reference_id'] ?? 0,
            'payment_method_id' => $metadata['payment_method_id'],
            'gateway_id' => $metadata['gateway_id'],
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'gateway_transaction_id' => $reference,
            'gateway_status' => 'awaiting_transfer',
            'gateway_response' => [
                'reference' => $reference,
                'bank_details' => [
                    'bank_name' => $this->config['bank_name'],
                    'account_name' => $this->config['account_name'],
                    'account_number' => $this->config['account_number'],
                    'sort_code' => $this->config['sort_code'],
                    'iban' => $this->config['iban'],
                    'swift_bic' => $this->config['swift_bic']
                ],
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description
            ]
        ];
        
        if ($this->createPaymentTransaction($transactionData)) {
            return [
                'success' => true,
                'transaction_id' => $reference,
                'bank_details' => $this->config,
                'reference' => $reference,
                'instructions' => $this->getPaymentInstructions($reference, $amount, $currency)
            ];
        }
        
        throw new Exception('Failed to process bank transfer payment');
    }
    
    private function getPaymentInstructions($reference, $amount, $currency) {
        return [
            'title' => 'Bank Transfer Payment Instructions',
            'steps' => [
                'Please make a bank transfer with the following details:',
                'Bank Name: ' . $this->config['bank_name'],
                'Account Name: ' . $this->config['account_name'],
                'Account Number: ' . $this->config['account_number'],
                'Sort Code: ' . $this->config['sort_code'],
                'IBAN: ' . $this->config['iban'],
                'SWIFT/BIC: ' . $this->config['swift_bic'],
                'Amount: ' . number_format($amount, 2) . ' ' . $currency,
                'Reference: ' . $reference
            ],
            'important_notes' => [
                'Please use the reference number provided when making the transfer.',
                'Your order will be processed once we receive the payment.',
                'Please allow 2-3 working days for the payment to be processed.',
                'If you have any questions, please contact our support team.'
            ]
        ];
    }
    
    public function processRefund($transactionId, $amount = null) {
        // For bank transfers, refunds are handled manually
        // This method will just update the transaction status
        try {
            $this->updatePaymentTransaction($transactionId, [
                'status' => 'refund_initiated',
                'gateway_status' => 'manual_refund_required',
                'gateway_response' => [
                    'refund_amount' => $amount,
                    'initiated_at' => date('Y-m-d H:i:s'),
                    'notes' => 'Manual refund process initiated'
                ]
            ]);
            
            return [
                'success' => true,
                'message' => 'Refund process initiated. Please process the refund manually.'
            ];
            
        } catch (Exception $e) {
            $this->logError('Failed to initiate bank transfer refund', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            throw new Exception('Failed to initiate refund process');
        }
    }
    
    public function validateWebhook($payload, $signature) {
        // Bank transfers don't use webhooks
        // This method exists to satisfy the abstract class requirement
        return false;
    }
    
    // Additional methods for bank transfer specific operations
    
    public function markPaymentReceived($transactionId, $bankReference = null) {
        try {
            $this->updatePaymentTransaction($transactionId, [
                'status' => 'completed',
                'gateway_status' => 'payment_received',
                'gateway_response' => [
                    'bank_reference' => $bankReference,
                    'received_at' => date('Y-m-d H:i:s'),
                    'verified_by' => $_SESSION['user_id'] ?? 0
                ]
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logError('Failed to mark bank transfer as received', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            return false;
        }
    }
    
    public function markPaymentFailed($transactionId, $reason) {
        try {
            $this->updatePaymentTransaction($transactionId, [
                'status' => 'failed',
                'gateway_status' => 'payment_failed',
                'gateway_response' => [
                    'failure_reason' => $reason,
                    'failed_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $_SESSION['user_id'] ?? 0
                ]
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logError('Failed to mark bank transfer as failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            return false;
        }
    }
}