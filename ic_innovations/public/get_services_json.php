<?php

/**
 * API Endpoint: Get all services as JSON
 * Used by frontend JavaScript to populate service checklist
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';

setSecurityHeaders();

// Allow CORS for same origin
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, category, service_name, charge, description FROM services ORDER BY category, service_name");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sanitize output
    foreach ($services as &$service) {
        $service['service_name'] = htmlspecialchars($service['service_name'], ENT_QUOTES, 'UTF-8');
        $service['category'] = htmlspecialchars($service['category'], ENT_QUOTES, 'UTF-8');
        $service['description'] = htmlspecialchars($service['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $service['charge'] = (float)$service['charge'];
    }

    echo json_encode($services, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    securityLog('API_ERROR', 'get_services_json: ' . $e->getMessage());
    echo json_encode(['error' => 'Unable to fetch services']);
}
