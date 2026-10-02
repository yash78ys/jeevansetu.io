<?php
/**
 * SOS Emergency Endpoint
 * POST / GET /api/sos.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../controllers/EmergencyController.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Require active authenticated user session
$user = requireUserAuth();
$controller = new EmergencyController();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = getRequestData();
    $controller->sendSOS((int)$user['id'], $data);
} elseif ($method === 'GET') {
    $controller->getUserHistory((int)$user['id']);
} else {
    jsonResponse(false, 'Method not allowed.', null, 405);
}
