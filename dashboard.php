<?php
/**
 * Dashboard Summary API Endpoint
 * GET /api/dashboard.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../includes/auth.php';

// Requires admin authentication
requireAdminAuth();

$controller = new AdminController();
$controller->getDashboardMetrics();
