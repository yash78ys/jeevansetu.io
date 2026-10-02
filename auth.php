<?php
/**
 * Authentication Guards & Verification
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

/**
 * Ensure user is logged in
 * 
 * @return array Authenticated user array
 */
function requireUserAuth(): array
{
    initSession();
    if (empty($_SESSION[SESSION_USER_KEY])) {
        jsonResponse(false, 'Unauthorized. Please login to continue.', null, 401);
    }
    return $_SESSION[SESSION_USER_KEY];
}

/**
 * Ensure admin is logged in
 * 
 * @return array Authenticated admin array
 */
function requireAdminAuth(): array
{
    initSession();
    if (empty($_SESSION[SESSION_ADMIN_KEY])) {
        jsonResponse(false, 'Unauthorized. Admin access required.', null, 401);
    }
    return $_SESSION[SESSION_ADMIN_KEY];
}

/**
 * Check if user session active
 */
function isUserLoggedIn(): bool
{
    initSession();
    return !empty($_SESSION[SESSION_USER_KEY]);
}

/**
 * Check if admin session active
 */
function isAdminLoggedIn(): bool
{
    initSession();
    return !empty($_SESSION[SESSION_ADMIN_KEY]);
}
