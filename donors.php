<?php
/**
 * Blood Donors Search & Register Endpoint
 * GET / POST /api/donors.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../controllers/DonorController.php';
require_once __DIR__ . '/../includes/functions.php';

$controller = new DonorController();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $params = $_GET;
    $controller->search($params);
} elseif ($method === 'POST') {
    $data = getRequestData();
    $controller->registerDonor($data);
} else {
    jsonResponse(false, 'Method not allowed.', null, 405);
}
