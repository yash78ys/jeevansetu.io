<?php
/**
 * Application Constants
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

// Application Settings
define('APP_NAME', 'JEEVANSETU');
define('APP_VERSION', '1.0.0');

// Database Configuration Defaults
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'jeevansetu');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session Keys
define('SESSION_USER_KEY', 'user_session');
define('SESSION_ADMIN_KEY', 'admin_session');
define('SESSION_CSRF_KEY', 'csrf_token');

// Status Codes / Strings
define('STATUS_PENDING', 'Pending');
define('STATUS_IN_PROGRESS', 'In Progress');
define('STATUS_RESOLVED', 'Resolved');

// Logging Path
define('LOG_DIR', __DIR__ . '/../logs/');
