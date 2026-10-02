<?php
/**
 * Admin Emergencies Management Endpoint
 * GET / POST / PUT /api/admin_emergencies.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$controller = new AdminController();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $controller->listEmergencies();
} elseif ($method === 'POST' || $method === 'PUT') {
    $data = getRequestData();
    $controller->updateEmergencyStatus($data);
} else {
    jsonResponse(false, 'Method not allowed.', null, 405);
}
