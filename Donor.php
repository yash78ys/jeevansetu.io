<?php
/**
 * Blood Donor Model
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Donor
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Search blood donors by blood group and optional village
     */
    public function search(string $bloodGroup, string $village = ''): array
    {
        $sql = "SELECT id, name, blood_group, village, phone FROM blood_donors WHERE blood_group = :bg";
        $params = [':bg' => strtoupper(trim($bloodGroup))];

        if (!empty($village)) {
            $sql .= " AND village LIKE :village";
            $params[':village'] = '%' . trim($village) . '%';
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Add a new blood donor
     */
    public function create(string $name, string $bloodGroup, string $village, string $phone): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO blood_donors (name, blood_group, village, phone)
            VALUES (:name, :blood_group, :village, :phone)
        ");
        $stmt->execute([
            ':name' => $name,
            ':blood_group' => strtoupper(trim($bloodGroup)),
            ':village' => $village,
            ':phone' => $phone
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get all blood donors
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM blood_donors ORDER BY id DESC");
        return $stmt->fetchAll();
    }
}
