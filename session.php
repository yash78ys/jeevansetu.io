<?php
/**
 * Secure Session Manager
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';

/**
 * Initialize secure PHP session
 * 
 * @return void
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_samesite', 'Lax');

        session_start();
    }
}

/**
 * Regenerate session ID upon authentication to prevent session fixation
 * 
 * @return void
 */
function regenerateSession(): void
{
    initSession();
    session_regenerate_id(true);
}

/**
 * Destroy current session
 * 
 * @return void
 */
function destroySession(): void
{
    initSession();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
