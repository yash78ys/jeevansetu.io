<?php
/**
 * User/Admin Logout Endpoint
 * POST / GET /api/logout.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();
