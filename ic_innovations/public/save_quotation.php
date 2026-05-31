<?php

/**
 * Save quotation request to database
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

setSecurityHeaders();

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $response['message'] = 'Invalid data format';
        echo json_encode($response);
        exit();
    }

    $services = $input['services'] ?? [];
    $subtotal = (float)($input['subtotal'] ?? 0);
    $tax = (float)($input['tax'] ?? 0);
    $total = (float)($input['total'] ?? 0);

    // Validate data
    if (empty($services) || $total <= 0) {
        $response['message'] = 'No services selected or invalid amount';
        echo json_encode($response);
        exit();
    }

    $session_id = session_id();
    $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
    $services_text = json_encode($services);

    try {
        $stmt = $pdo->prepare("INSERT INTO quotation_requests (session_id, user_id, selected_services, subtotal, tax, total, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");

        if ($stmt->execute([$session_id, $user_id, $services_text, $subtotal, $tax, $total])) {
            $response['status'] = 'success';
            $response['message'] = 'Quotation requested successfully!';
            securityLog('QUOTATION_REQUEST', "Session: $session_id, Total: $total");

            // If user is logged in, store user_id for future reference
            if ($user_id) {
                $quote_id = $pdo->lastInsertId();
                $stmt2 = $pdo->prepare("UPDATE quotation_requests SET user_id = ? WHERE id = ?");
                $stmt2->execute([$user_id, $quote_id]);
            }
        } else {
            $response['message'] = 'Failed to save quotation. Please try again.';
        }
    } catch (PDOException $e) {
        securityLog('DB_ERROR', 'save_quotation: ' . $e->getMessage());
        $response['message'] = 'Database error. Please try again later.';
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
