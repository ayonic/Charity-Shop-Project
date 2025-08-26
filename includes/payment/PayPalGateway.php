<?php

class PayPalGateway extends PaymentGateway {
    private $apiEndpoint;
    private $accessToken;

    public function __construct(array $config = [], bool $isEnabled = false, bool $isTestMode = true) {
        parent::__construct($config, $isEnabled, $isTestMode);
        $this->apiEndpoint = $isTestMode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public function validateConfig(): bool {
        return !empty($this->config['client_id']) && !empty($this->config['client_secret']);
    }
    
    public function testConnection() {
        if (!$this->validateConfig()) {
            throw new Exception('Invalid PayPal configuration: Missing client ID or secret');
        }
        
        try {
            // Test connection by attempting to get an access token
            $this->getAccessToken();
            return true;
        } catch (Exception $e) {
            throw new Exception('PayPal connection test failed: ' . $e->getMessage());
        }
    }

    private function getAccessToken(): string {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $ch = curl_init($this->apiEndpoint . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['client_id'] . ':' . $this->config['client_secret']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->logError('Failed to get PayPal access token', ['http_code' => $httpCode, 'response' => $response]);
            throw new Exception('Failed to authenticate with PayPal');
        }

        $data = json_decode($response, true);
        $this->accessToken = $data['access_token'];
        return $this->accessToken;
    }

    public function processPayment($amount, $currency, $description, $metadata = []) {
        if (!$this->isEnabled) {
            throw new Exception('PayPal gateway is not enabled');
        }

        try {
            $accessToken = $this->getAccessToken();
            
            $payload = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', '')
                    ],
                    'description' => $description
                ]]
            ];

            $ch = curl_init($this->apiEndpoint . '/v2/checkout/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 201) {
                $this->logError('PayPal payment creation failed', [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'amount' => $amount,
                    'currency' => $currency
                ]);
                throw new Exception('Failed to create PayPal payment');
            }

            $data = json_decode($response, true);
            $this->logInfo('PayPal payment created successfully', ['order_id' => $data['id']]);

            return [
                'success' => true,
                'transaction_id' => $data['id'],
                'status' => $data['status'],
                'redirect_url' => $data['links'][1]['href']
            ];

        } catch (Exception $e) {
            $this->logError('PayPal payment processing error', [
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
            throw new Exception('PayPal gateway is not enabled');
        }

        try {
            $accessToken = $this->getAccessToken();
            
            $payload = [];
            if ($amount !== null) {
                $payload['amount'] = [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => 'USD'
                ];
            }

            $ch = curl_init($this->apiEndpoint . "/v2/payments/captures/{$transactionId}/refund");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 201) {
                $this->logError('PayPal refund failed', [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'transaction_id' => $transactionId
                ]);
                throw new Exception('Failed to process PayPal refund');
            }

            $data = json_decode($response, true);
            $this->logInfo('PayPal refund processed successfully', ['refund_id' => $data['id']]);

            return [
                'success' => true,
                'refund_id' => $data['id'],
                'status' => $data['status']
            ];

        } catch (Exception $e) {
            $this->logError('PayPal refund processing error', [
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
        if (!$this->isEnabled || empty($this->config['webhook_id'])) {
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();
            
            $verificationData = [
                'auth_algo' => $signature['auth_algo'],
                'cert_url' => $signature['cert_url'],
                'transmission_id' => $signature['transmission_id'],
                'transmission_sig' => $signature['transmission_sig'],
                'transmission_time' => $signature['transmission_time'],
                'webhook_id' => $this->config['webhook_id'],
                'webhook_event' => $payload
            ];

            $ch = curl_init($this->apiEndpoint . '/v1/notifications/verify-webhook-signature');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verificationData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                $this->logError('PayPal webhook verification failed', [
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                return false;
            }

            $data = json_decode($response, true);
            return $data['verification_status'] === 'SUCCESS';

        } catch (Exception $e) {
            $this->logError('PayPal webhook verification error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}