<?php
/**
 * Emergency SOS Model
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Emergency
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Create a new emergency SOS dispatch
     */
    public function create(int $userId, float $latitude, float $longitude): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO emergencies (user_id, latitude, longitude, status)
            VALUES (:user_id, :latitude, :longitude, :status)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':status' => STATUS_PENDING
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get active emergency for user
     */
    public function getActiveForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM emergencies 
            WHERE user_id = :user_id AND status != 'Resolved'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Fetch user emergency history
     */
    public function getHistoryByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, latitude, longitude, status, created_at
            FROM emergencies
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Fetch all emergencies (Admin report / triage)
     */
    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT e.id, e.user_id, u.name as user_name, u.phone as user_phone, 
                   e.latitude, e.longitude, e.status, e.created_at
            FROM emergencies e
            JOIN users u ON e.user_id = u.id
            ORDER BY e.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update emergency status (Pending, In Progress, Resolved)
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE emergencies SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
    }
}
