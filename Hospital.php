<?php
/**
 * Hospital Data Model
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Hospital
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Create hospital record (Admin feature)
     */
    public function create(string $name, string $phone, string $address, float $latitude, float $longitude): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO hospitals (name, phone, address, latitude, longitude)
            VALUES (:name, :phone, :address, :latitude, :longitude)
        ");
        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':address' => $address,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get list of hospitals sorted by distance using Haversine Formula
     * 
     * @param float $lat User Latitude
     * @param float $lng User Longitude
     * @param int $limit Max results
     * @return array
     */
    public function getNearest(float $lat, float $lng, int $limit = 20): array
    {
        $sql = "
            SELECT id, name, phone, address, latitude, longitude,
                ( 6371 * acos( cos( radians(:lat) ) 
                * cos( radians( latitude ) ) 
                * cos( radians( longitude ) - radians(:lng) ) 
                + sin( radians(:lat) ) 
                * sin( radians( latitude ) ) ) ) AS distance_km
            FROM hospitals
            ORDER BY distance_km ASC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lat', $lat);
        $stmt->bindValue(':lng', $lng);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        foreach ($results as &$row) {
            $row['distance_km'] = round((float)$row['distance_km'], 2);
        }
        return $results;
    }

    /**
     * Fetch all hospitals
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM hospitals ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Find hospital by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM hospitals WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}
