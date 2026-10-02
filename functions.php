<?php
/**
 * Global Helper Functions
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

/**
 * Send standard JSON API response and exit
 * 
 * @param bool $success
 * @param string $message
 * @param mixed|null $data
 * @param int $statusCode
 * @return void
 */
function jsonResponse(bool $success, string $message, mixed $data = null, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => $success,
        'message' => $message,
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Sanitize string input to prevent XSS
 * 
 * @param string $data
 * @return string
 */
function sanitizeInput(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Read incoming JSON request body or POST data
 * 
 * @return array
 */
function getRequestData(): array
{
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST ?? [];
}

/**
 * Log error messages securely to log file
 * 
 * @param string $message
 * @return void
 */
function logAppError(string $message): void
{
    $dir = __DIR__ . '/../logs/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $logMsg = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
    @file_put_contents($dir . 'app.log', $logMsg, FILE_APPEND);
}
