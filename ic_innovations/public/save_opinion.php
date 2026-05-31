<?php

/**
 * Save user opinion/query to database
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

setSecurityHeaders();

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $response['message'] = 'Security validation failed';
        echo json_encode($response);
        exit();
    }

    $name = sanitizeInput($_POST['name'] ?? '');
    $contact = sanitizeInput($_POST['contact'] ?? '');
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : null;
    $message = sanitizeInput($_POST['message'] ?? '');

    // Validate required fields
    if (empty($name) || empty($contact)) {
        $response['message'] = 'Name and contact number are required';
        echo json_encode($response);
        exit();
    }

    // Validate email if provided
    if (!empty($email) && !validateEmail($email)) {
        $response['message'] = 'Invalid email address';
        echo json_encode($response);
        exit();
    }

    // Validate mobile number format
    if (!validateMobile($contact)) {
        $response['message'] = 'Invalid mobile number format. Use 03XXXXXXXXX or +923XXXXXXXXX';
        echo json_encode($response);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO user_opinions (name, contact, email, message) VALUES (?, ?, ?, ?)");

        if ($stmt->execute([$name, $contact, $email, $message])) {
            $response['status'] = 'success';
            $response['message'] = 'Thank you for your opinion! We will respond within 48 hours.';
            securityLog('OPINION_SUBMITTED', "From: $name, Contact: $contact");
        } else {
            $response['message'] = 'Failed to save your message. Please try again.';
        }
    } catch (PDOException $e) {
        securityLog('DB_ERROR', 'save_opinion: ' . $e->getMessage());
        $response['message'] = 'Database error. Please try again later.';
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
