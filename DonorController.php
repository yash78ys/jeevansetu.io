<?php
/**
 * Blood Donor Controller
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/Donor.php';
require_once __DIR__ . '/../includes/validator.php';
require_once __DIR__ . '/../includes/functions.php';

class DonorController
{
    private Donor $donorModel;

    public function __construct()
    {
        $this->donorModel = new Donor();
    }

    /**
     * Search blood donors by blood group and optional village
     */
    public function search(array $params): void
    {
        $bloodGroup = sanitizeInput($params['blood_group'] ?? '');
        $village    = sanitizeInput($params['village'] ?? '');

        if (empty($bloodGroup)) {
            jsonResponse(false, 'Blood group parameter is required for search.', null, 400);
        }

        if (!Validator::isValidBloodGroup($bloodGroup)) {
            jsonResponse(false, 'Invalid blood group requested.', null, 400);
        }

        try {
            $donors = $this->donorModel->search($bloodGroup, $village);
            jsonResponse(true, 'Blood donors fetched successfully.', ['donors' => $donors]);
        } catch (Exception $e) {
            logAppError("Donor Search Error: " . $e->getMessage());
            jsonResponse(false, 'Failed to fetch blood donors.', null, 500);
        }
    }

    /**
     * Register a new donor
     */
    public function registerDonor(array $data): void
    {
        $name       = sanitizeInput($data['name'] ?? '');
        $bloodGroup = sanitizeInput($data['blood_group'] ?? '');
        $village    = sanitizeInput($data['village'] ?? '');
        $phone      = sanitizeInput($data['phone'] ?? '');

        if (empty($name) || empty($bloodGroup) || empty($village) || empty($phone)) {
            jsonResponse(false, 'All donor fields are required.', null, 400);
        }

        if (!Validator::isValidBloodGroup($bloodGroup)) {
            jsonResponse(false, 'Invalid blood group specified.', null, 400);
        }

        if (!Validator::isValidPhone($phone)) {
            jsonResponse(false, 'Invalid phone number format.', null, 400);
        }

        try {
            $id = $this->donorModel->create($name, $bloodGroup, $village, $phone);
            jsonResponse(true, 'Blood donor registered successfully.', ['donor_id' => $id], 201);
        } catch (Exception $e) {
            logAppError("Register Donor Error: " . $e->getMessage());
            jsonResponse(false, 'Failed to register blood donor.', null, 500);
        }
    }
}
