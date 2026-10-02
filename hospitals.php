<?php
/**
 * Hospitals Endpoint
 * GET / POST /api/hospitals.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../controllers/HospitalController.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$controller = new HospitalController();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $params = $_GET;
    $controller->getNearestHospitals($params);
} elseif ($method === 'POST') {
    requireAdminAuth();
    $data = getRequestData();
    $controller->addHospital($data);
} else {
    jsonResponse(false, 'Method not allowed.', null, 405);
}
