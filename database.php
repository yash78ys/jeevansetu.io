<?php
/**
 * Reusable Database Connection Manager using PDO
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/constants.php';

class Database
{
    private static ?PDO $instance = null;

    /**
     * Get single PDO database instance
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", DB_HOST, DB_PORT, DB_NAME);
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log connection error silently to avoid exposing credentials
                error_log("Database Connection Error: " . $e->getMessage(), 3, LOG_DIR . 'error.log');
                throw new Exception("Database connection error. Please try again later.");
            }
        }

        return self::$instance;
    }
}
