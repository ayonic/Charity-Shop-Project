-- Payment Gateway Configuration Tables

-- Payment gateways table
CREATE TABLE payment_gateways (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    is_enabled BOOLEAN DEFAULT FALSE,
    test_mode BOOLEAN DEFAULT TRUE,
    configuration JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add default payment gateways
INSERT INTO payment_gateways (name, code, description, is_enabled, test_mode, configuration) VALUES
('PayPal', 'paypal', 'PayPal payment gateway integration', 0, 1, '{"client_id": "", "client_secret": "", "webhook_id": ""}'),
('Stripe', 'stripe', 'Stripe payment gateway integration', 0, 1, '{"publishable_key": "", "secret_key": "", "webhook_secret": ""}'),
('Bank Transfer', 'bank_transfer', 'Direct bank transfer payment option', 1, 0, '{"bank_name": "", "account_name": "", "account_number": "", "sort_code": "", "iban": "", "swift_bic": ""}');

-- Update payment_methods table to link with gateways
ALTER TABLE payment_methods
ADD COLUMN gateway_id INT NULL,
ADD FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE SET NULL;

-- Add default payment methods linked to gateways
INSERT INTO payment_methods (name, code, description, gateway_id)
SELECT 'PayPal Standard', 'paypal', 'Pay with your PayPal account', id FROM payment_gateways WHERE code = 'paypal';

INSERT INTO payment_methods (name, code, description, gateway_id)
SELECT 'Credit/Debit Card', 'card', 'Pay with Credit/Debit Card via Stripe', id FROM payment_gateways WHERE code = 'stripe';

INSERT INTO payment_methods (name, code, description, gateway_id)
SELECT 'Bank Transfer', 'bank_transfer', 'Pay via Bank Transfer', id FROM payment_gateways WHERE code = 'bank_transfer';

-- Update payment_transactions table to store gateway-specific data
ALTER TABLE payment_transactions
ADD COLUMN gateway_id INT NULL,
ADD COLUMN gateway_transaction_id VARCHAR(255),
ADD COLUMN gateway_status VARCHAR(50),
ADD FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE SET NULL;