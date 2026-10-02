<?php
/**
 * User Registration Endpoint
 * POST /api/register.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST request method allowed.', null, 405);
}

$data = getRequestData();
$controller = new AuthController();
$controller->register($data);
