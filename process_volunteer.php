<?php
/**
 * Process Volunteer Application
 * 
 * This script handles volunteer application form submissions,
 * validates input, and stores applications in the database.
 */

require_once 'config/init.php';

// Verify if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: volunteer.php');
    exit;
}

// Sanitize and validate input
$first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_STRING);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
$city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
$state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
$zip = filter_input(INPUT_POST, 'zip', FILTER_SANITIZE_STRING);

// Get availability (array of days)
$availability = isset($_POST['availability']) ? $_POST['availability'] : [];
$availability_json = json_encode($availability);

// Get interests (array of areas)
$interests = isset($_POST['interests']) ? $_POST['interests'] : [];
$interests_json = json_encode($interests);

// Emergency contact information
$emergency_name = filter_input(INPUT_POST, 'emergency_name', FILTER_SANITIZE_STRING);
$emergency_phone = filter_input(INPUT_POST, 'emergency_phone', FILTER_SANITIZE_STRING);
$emergency_relation = filter_input(INPUT_POST, 'emergency_relation', FILTER_SANITIZE_STRING);

// Additional information
$experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_STRING);
$why_volunteer = filter_input(INPUT_POST, 'why_volunteer', FILTER_SANITIZE_STRING);

// Validate required fields
$required_fields = [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'phone' => $phone,
    'birth_date' => $birth_date,
    'address' => $address,
    'city' => $city,
    'state' => $state,
    'zip' => $zip,
    'emergency_name' => $emergency_name,
    'emergency_phone' => $emergency_phone,
    'emergency_relation' => $emergency_relation
];

foreach ($required_fields as $field => $value) {
    if (!$value) {
        $_SESSION['error'] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        $_SESSION['form_data'] = $_POST;
        header('Location: volunteer.php');
        exit;
    }
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    $_SESSION['form_data'] = $_POST;
    header('Location: volunteer.php');
    exit;
}

try {
    // Create volunteer_applications table if it doesn't exist
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS volunteer_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            birth_date DATE NOT NULL,
            address TEXT NOT NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(50) NOT NULL,
            zip VARCHAR(20) NOT NULL,
            availability JSON,
            interests JSON,
            emergency_name VARCHAR(200) NOT NULL,
            emergency_phone VARCHAR(20) NOT NULL,
            emergency_relation VARCHAR(100) NOT NULL,
            experience TEXT,
            why_volunteer TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    if (!$connection->query($create_table_sql)) {
        throw new Exception('Failed to create volunteer_applications table: ' . $connection->error);
    }

    // Prepare and execute the insert statement
    $stmt = $connection->prepare("
        INSERT INTO volunteer_applications (
            first_name, last_name, email, phone, birth_date, address, city, state, zip,
            availability, interests, emergency_name, emergency_phone, emergency_relation,
            experience, why_volunteer
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $connection->error);
    }

    $stmt->bind_param(
        'ssssssssssssssss',
        $first_name, $last_name, $email, $phone, $birth_date, $address, $city, $state, $zip,
        $availability_json, $interests_json, $emergency_name, $emergency_phone, $emergency_relation,
        $experience, $why_volunteer
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to store application: ' . $stmt->error);
    }

    // Send email notification to admin
    $to = 'volunteer@charityshop.org'; // Replace with actual volunteer coordinator email
    $email_subject = 'New Volunteer Application';
    $email_message = "New volunteer application received:\n\n";
    $email_message .= "Name: $first_name $last_name\n";
    $email_message .= "Email: $email\n";
    $email_message .= "Phone: $phone\n";
    $email_message .= "Birth Date: $birth_date\n";
    $email_message .= "\nPlease review the application in the admin dashboard.";
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Attempt to send email (commented out for now)
    // mail($to, $email_subject, $email_message, $headers);

    // Set success message
    $_SESSION['success'] = 'Thank you for your volunteer application! We will review it and contact you soon.';

} catch (Exception $e) {
    // Log the error
    error_log('Volunteer application error: ' . $e->getMessage());
    $_SESSION['error'] = 'Sorry, there was an error processing your application. Please try again later.';
    $_SESSION['form_data'] = $_POST;
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}

// Redirect back to volunteer page
header('Location: volunteer.php');
exit;