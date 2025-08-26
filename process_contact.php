<?php
/**
 * Process Contact Form
 * 
 * This script handles the contact form submissions, validates input,
 * stores messages in the database, and sends email notifications.
 */

require_once 'config/init.php';

// Verify if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: contact.php');
    exit;
}

// Validate CSRF token if implemented
// TODO: Implement CSRF protection

// Sanitize and validate input
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

// Validate required fields
if (!$name || !$email || !$subject || !$message) {
    $_SESSION['error'] = 'All fields are required.';
    $_SESSION['form_data'] = $_POST; // Store form data for repopulation
    header('Location: contact.php');
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    $_SESSION['form_data'] = $_POST;
    header('Location: contact.php');
    exit;
}

try {
    // Create messages table if it doesn't exist
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    if (!$connection->query($create_table_sql)) {
        throw new Exception('Failed to create messages table: ' . $connection->error);
    }

    // Prepare and execute the insert statement
    $stmt = $connection->prepare("
        INSERT INTO messages (name, email, subject, message)
        VALUES (?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $connection->error);
    }

    $stmt->bind_param('ssss', $name, $email, $subject, $message);

    if (!$stmt->execute()) {
        throw new Exception('Failed to store message: ' . $stmt->error);
    }

    // Send email notification to admin
    $to = 'admin@charityshop.org'; // Replace with actual admin email
    $email_subject = 'New Contact Form Submission';
    $email_message = "New message received from the contact form:\n\n";
    $email_message .= "Name: $name\n";
    $email_message .= "Email: $email\n";
    $email_message .= "Subject: $subject\n";
    $email_message .= "Message:\n$message";
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Attempt to send email (commented out for now)
    // mail($to, $email_subject, $email_message, $headers);

    // Set success message
    $_SESSION['success'] = 'Thank you for your message. We will get back to you soon!';

} catch (Exception $e) {
    // Log the error
    error_log('Contact form error: ' . $e->getMessage());
    $_SESSION['error'] = 'Sorry, there was an error processing your message. Please try again later.';
    $_SESSION['form_data'] = $_POST;
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}

// Redirect back to contact page
header('Location: contact.php');
exit;