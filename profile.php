<?php
/**
 * User Profile & Emergency Contacts Endpoint
 * GET / POST / DELETE /api/profile.php
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validator.php';
require_once __DIR__ . '/../includes/functions.php';

$user = requireUserAuth();
$userModel = new User();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $profileData = $userModel->findById((int)$user['id']);
    $contacts = $userModel->getEmergencyContacts((int)$user['id']);
    jsonResponse(true, 'User profile fetched.', [
        'profile'  => $profileData,
        'contacts' => $contacts
    ]);
} elseif ($method === 'POST') {
    $data = getRequestData();
    $action = $data['action'] ?? 'update_profile';

    if ($action === 'add_contact') {
        $contactName = sanitizeInput($data['contact_name'] ?? '');
        $phone       = sanitizeInput($data['phone'] ?? '');

        if (empty($contactName) || empty($phone)) {
            jsonResponse(false, 'Contact name and phone number are required.', null, 400);
        }

        if (!Validator::isValidPhone($phone)) {
            jsonResponse(false, 'Invalid phone number format.', null, 400);
        }

        $contactId = $userModel->addEmergencyContact((int)$user['id'], $contactName, $phone);
        jsonResponse(true, 'Emergency contact added.', ['contact_id' => $contactId], 201);
    } else {
        // Update profile
        $name       = sanitizeInput($data['name'] ?? '');
        $phone      = sanitizeInput($data['phone'] ?? '');
        $bloodGroup = sanitizeInput($data['blood_group'] ?? '');

        if (empty($name) || empty($phone) || empty($bloodGroup)) {
            jsonResponse(false, 'Name, phone, and blood group are required.', null, 400);
        }

        if (!Validator::isValidPhone($phone) || !Validator::isValidBloodGroup($bloodGroup)) {
            jsonResponse(false, 'Invalid input parameters.', null, 400);
        }

        $userModel->updateProfile((int)$user['id'], $name, $phone, $bloodGroup);

        // Refresh session
        $_SESSION[SESSION_USER_KEY]['name'] = $name;
        $_SESSION[SESSION_USER_KEY]['phone'] = $phone;
        $_SESSION[SESSION_USER_KEY]['blood_group'] = $bloodGroup;

        jsonResponse(true, 'Profile updated successfully.');
    }
} elseif ($method === 'DELETE') {
    $data = getRequestData();
    $contactId = (int)($data['contact_id'] ?? $_GET['contact_id'] ?? 0);

    if ($contactId <= 0) {
        jsonResponse(false, 'Valid contact ID required.', null, 400);
    }

    $success = $userModel->deleteEmergencyContact($contactId, (int)$user['id']);
    jsonResponse($success, $success ? 'Emergency contact deleted.' : 'Contact not found or access denied.');
} else {
    jsonResponse(false, 'Method not allowed.', null, 405);
}
