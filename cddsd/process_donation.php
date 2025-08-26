<?php
/**
 * Process Monetary Donation
 */

require_once 'config/init.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message('error', 'Invalid request method.');
    redirect('donate.php');
}

// Validate amount
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
if (!$amount || $amount <= 0) {
    set_flash_message('error', 'Please enter a valid donation amount.');
    redirect('donate.php');
}

// Round to 2 decimal places
$amount = round($amount, 2);

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Create donation record
    $sql = "INSERT INTO donations (user_id, amount, type, status, created_at) VALUES (?, ?, ?, ?, NOW())";
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $amount, 'monetary', 'pending']);
    $donation_id = $pdo->lastInsertId();
    
    // Commit transaction
    $pdo->commit();
    
    // Set success message
    set_flash_message('success', 'Thank you for your donation! You will be redirected to complete the payment.');
    
    // Redirect to payment page
    redirect('payment.php?donation_id=' . $donation_id);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    // Log error
    error_log('Error processing donation: ' . $e->getMessage());
    
    // Set error message
    set_flash_message('error', 'An error occurred while processing your donation. Please try again.');
    redirect('donate.php');
}