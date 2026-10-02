<?php
/**
 * Emergency SOS Controller
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/Emergency.php';
require_once __DIR__ . '/../includes/validator.php';
require_once __DIR__ . '/../includes/functions.php';

class EmergencyController
{
    private Emergency $emergencyModel;

    public function __construct()
    {
        $this->emergencyModel = new Emergency();
    }

    /**
     * Dispatch SOS Emergency Alert
     */
    public function sendSOS(int $userId, array $data): void
    {
        $latitude  = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;

        if ($latitude === null || $longitude === null) {
            jsonResponse(false, 'GPS coordinates (latitude and longitude) are required.', null, 400);
        }

        if (!Validator::isValidLatitude($latitude) || !Validator::isValidLongitude($longitude)) {
            jsonResponse(false, 'Invalid GPS coordinates provided.', null, 400);
        }

        try {
            $emergencyId = $this->emergencyModel->create($userId, (float)$latitude, (float)$longitude);

            jsonResponse(true, 'Emergency Recorded. Rescue services & contacts alerted.', [
                'emergency_id' => $emergencyId,
                'status'       => STATUS_PENDING,
                'latitude'     => (float)$latitude,
                'longitude'    => (float)$longitude,
                'timestamp'    => date('Y-m-d H:i:s')
            ], 201);
        } catch (Exception $e) {
            logAppError("SOS Controller Error: " . $e->getMessage());
            jsonResponse(false, 'Failed to record emergency SOS. Please try again or call emergency directly.', null, 500);
        }
    }

    /**
     * Get user emergency history
     */
    public function getUserHistory(int $userId): void
    {
        try {
            $history = $this->emergencyModel->getHistoryByUser($userId);
            jsonResponse(true, 'User emergency history fetched.', ['history' => $history]);
        } catch (Exception $e) {
            logAppError("Emergency History Error: " . $e->getMessage());
            jsonResponse(false, 'Failed to retrieve emergency history.', null, 500);
        }
    }
}
