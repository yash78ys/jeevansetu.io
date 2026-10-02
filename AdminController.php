<?php
/**
 * Admin Dashboard & Management Controller
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Emergency.php';
require_once __DIR__ . '/../includes/functions.php';

class AdminController
{
    private Admin $adminModel;
    private Emergency $emergencyModel;

    public function __construct()
    {
        $this->adminModel = new Admin();
        $this->emergencyModel = new Emergency();
    }

    /**
     * Get dashboard metrics summary
     */
    public function getDashboardMetrics(): void
    {
        try {
            $stats = $this->adminModel->getDashboardStats();
            jsonResponse(true, 'Admin dashboard metrics retrieved.', ['stats' => $stats]);
        } catch (Exception $e) {
            logAppError("Admin Dashboard Metrics Error: " . $e->getMessage());
            jsonResponse(false, 'Failed to load admin metrics.', null, 500);
        }
    }

    /**
     * Fetch list of all emergency calls for triage & response
     */
    public function listEmergencies(): void
    {
        try {
            $emergencies = $this->emergencyModel->getAll();
            jsonResponse(true, 'Emergency requests list retrieved.', ['emergencies' => $emergencies]);
        } catch (Exception $e) {
            logAppError("Admin List Emergencies Error: " . $e->getMessage());
            jsonResponse(false, 'Failed to fetch emergency records.', null, 500);
        }
    }

    /**
     * Update emergency status (Pending -> In Progress -> Resolved)
     */
    public function updateEmergencyStatus(array $data): void
    {
        $id     = isset($data['id']) ? (int)$data['id'] : 0;
        $status = sanitizeInput($data['status'] ?? '');

        $validStatuses = [STATUS_PENDING, STATUS_IN_PROGRESS, STATUS_RESOLVED];

        if ($id <= 0 || !in_array($status, $validStatuses, true)) {
            jsonResponse(false, 'Invalid emergency ID or status value.', null, 400);
        }

        try {
            $success = $this->emergencyModel->updateStatus($id, $status);
            if ($success) {
                jsonResponse(true, 'Emergency status updated successfully.', ['id' => $id, 'status' => $status]);
            } else {
                jsonResponse(false, 'Failed to update emergency status.', null, 400);
            }
        } catch (Exception $e) {
            logAppError("Update Emergency Status Error: " . $e->getMessage());
            jsonResponse(false, 'Database error while updating status.', null, 500);
        }
    }
}
