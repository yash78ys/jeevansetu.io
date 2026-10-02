<?php
/**
 * Hospital Controller
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/Hospital.php';
require_once __DIR__ . '/../includes/validator.php';
require_once __DIR__ . '/../includes/functions.php';

class HospitalController
{
    private Hospital $hospitalModel;

    public function __construct()
    {
        $this->hospitalModel = new Hospital();
    }

    /**
     * Search nearest hospitals based on frontend supplied coordinates
     */
    public function getNearestHospitals(array $params): void
    {
        $lat = $params['latitude'] ?? null;
        $lng = $params['longitude'] ?? null;

        if ($lat !== null && $lng !== null && Validator::isValidLatitude($lat) && Validator::isValidLongitude($lng)) {
            $hospitals = $this->hospitalModel->getNearest((float)$lat, (float)$lng);
        } else {
            // Return all hospitals if location not provided
            $hospitals = $this->hospitalModel->getAll();
        }

        jsonResponse(true, 'Hospitals fetched successfully.', ['hospitals' => $hospitals]);
    }

    /**
     * Add a hospital (Admin function)
     */
    public function addHospital(array $data): void
    {
        $name    = sanitizeInput($data['name'] ?? '');
        $phone   = sanitizeInput($data['phone'] ?? '');
        $address = sanitizeInput($data['address'] ?? '');
        $lat     = $data['latitude'] ?? null;
        $lng     = $data['longitude'] ?? null;

        if (empty($name) || empty($phone) || empty($address) || $lat === null || $lng === null) {
            jsonResponse(false, 'All hospital details (name, phone, address, latitude, longitude) are required.', null, 400);
        }

        if (!Validator::isValidLatitude($lat) || !Validator::isValidLongitude($lng)) {
            jsonResponse(false, 'Invalid hospital coordinates.', null, 400);
        }

        try {
            $id = $this->hospitalModel->create($name, $phone, $address, (float)$lat, (float)$lng);
            jsonResponse(true, 'Hospital added successfully.', ['hospital_id' => $id], 201);
        } catch (Exception $e) {
            logAppError("Add Hospital Error: " . $e->getMessage());
            jsonResponse(false, 'Failed to save hospital details.', null, 500);
        }
    }
}
