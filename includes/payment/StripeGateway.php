<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Webhook;

class StripeGateway extends PaymentGateway {
    public function __construct(array $config = [], bool $isEnabled = false, bool $isTestMode = true) {
        parent::__construct($config, $isEnabled, $isTestMode);
        if ($this->validateConfig()) {
            Stripe::setApiKey($this->config['secret_key']);
        }
    }

    public function validateConfig(): bool {
        return !empty($this->config['publishable_key']) && !empty($this->config['secret_key']);
    }
    
    public function testConnection() {
        if (!$this->validateConfig()) {
            throw new Exception('Invalid Stripe configuration: Missing publishable key or secret key');
        }
        
        try {
            // Test connection by retrieving account information
            $account = \Stripe\Account::retrieve();
            return true;
        } catch (\Stripe\Exception\AuthenticationException $e) {
            throw new Exception('Stripe authentication failed: Invalid API keys');
        } catch (\Exception $e) {
            throw new Exception('Stripe connection test failed: ' . $e->getMessage());
        }
    }

    public function processPayment($amount, $currency, $description, $metadata = []) {
        if (!$this->isEnabled) {
            throw new Exception('Stripe gateway is not enabled');
        }

        try {
            $intent = PaymentIntent::create([
                'amount' => (int)($amount * 100), // Convert to cents
                'currency' => strtolower($currency),
                'description' => $description,
                'metadata' => $metadata
            ]);

            $this->logInfo('Stripe payment intent created', ['intent_id' => $intent->id]);

            return [
                'success' => true,
                'client_secret' => $intent->client_secret,
                'transaction_id' => $intent->id,
                'status' => $intent->status
            ];

        } catch (\Exception $e) {
            $this->logError('Stripe payment processing error', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency' => $currency
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function processRefund($transactionId, $amount = null) {
        if (!$this->isEnabled) {
            throw new Exception('Stripe gateway is not enabled');
        }

        try {
            $refundData = ['payment_intent' => $transactionId];
            if ($amount !== null) {
                $refundData['amount'] = (int)($amount * 100); // Convert to cents
            }

            $refund = Refund::create($refundData);

            $this->logInfo('Stripe refund processed successfully', ['refund_id' => $refund->id]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status
            ];

        } catch (\Exception $e) {
            $this->logError('Stripe refund processing error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function validateWebhook($payload, $signature) {
        if (!$this->isEnabled || empty($this->config['webhook_secret'])) {
            return false;
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature['stripe-signature'],
                $this->config['webhook_secret']
            );

            $this->logInfo('Stripe webhook validated successfully', ['event_id' => $event->id]);
            return true;

        } catch (\Exception $e) {
            $this->logError('Stripe webhook validation error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getPublicKey() {
        return $this->config['publishable_key'];
    }
}