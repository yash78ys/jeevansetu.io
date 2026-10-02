<?php
/**
 * Data Validator Utility
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

class Validator
{
    /**
     * Validate Email Address
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate Phone Number (10 to 15 digits)
     */
    public static function isValidPhone(string $phone): bool
    {
        return preg_match('/^[0-9]{10,15}$/', $phone) === 1;
    }

    /**
     * Validate GPS Latitude (-90 to 90)
     */
    public static function isValidLatitude(float|string $lat): bool
    {
        $val = (float)$lat;
        return is_numeric($lat) && $val >= -90.0 && $val <= 90.0;
    }

    /**
     * Validate GPS Longitude (-180 to 180)
     */
    public static function isValidLongitude(float|string $lng): bool
    {
        $val = (float)$lng;
        return is_numeric($lng) && $val >= -180.0 && $val <= 180.0;
    }

    /**
     * Validate Blood Group format
     */
    public static function isValidBloodGroup(string $bg): bool
    {
        $validGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        return in_array(strtoupper(trim($bg)), $validGroups, true);
    }
}
