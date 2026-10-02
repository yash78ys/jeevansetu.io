<?php
/**
 * Admin Login Endpoint
 * POST /api/admin_login.php
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
$controller->adminLogin($data);
