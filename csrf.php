<?php
/**
 * CSRF Protection Helper
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/session.php';

class CSRF
{
    /**
     * Generate or return existing CSRF token
     */
    public static function generateToken(): string
    {
        initSession();
        if (empty($_SESSION[SESSION_CSRF_KEY])) {
            $_SESSION[SESSION_CSRF_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[SESSION_CSRF_KEY];
    }

    /**
     * Validate incoming CSRF token
     */
    public static function validateToken(?string $token): bool
    {
        initSession();
        if (empty($_SESSION[SESSION_CSRF_KEY]) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION[SESSION_CSRF_KEY], $token);
    }
}
