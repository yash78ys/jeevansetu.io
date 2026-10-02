<?php
/**
 * Admin Model
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class Admin
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Find admin by username
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Get aggregate statistics for Admin Dashboard
     */
    public function getDashboardStats(): array
    {
        // Total Users
        $totalUsers = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        // Total Hospitals
        $totalHospitals = (int)$this->db->query("SELECT COUNT(*) FROM hospitals")->fetchColumn();

        // Total Blood Donors
        $totalDonors = (int)$this->db->query("SELECT COUNT(*) FROM blood_donors")->fetchColumn();

        // Today's Emergencies
        $todaysEmergencies = (int)$this->db->query("
            SELECT COUNT(*) FROM emergencies 
            WHERE DATE(created_at) = CURDATE()
        ")->fetchColumn();

        // Pending Emergencies
        $pendingEmergencies = (int)$this->db->query("
            SELECT COUNT(*) FROM emergencies WHERE status = 'Pending'
        ")->fetchColumn();

        // Resolved Emergencies
        $resolvedEmergencies = (int)$this->db->query("
            SELECT COUNT(*) FROM emergencies WHERE status = 'Resolved'
        ")->fetchColumn();

        return [
            'total_users'          => $totalUsers,
            'total_hospitals'      => $totalHospitals,
            'total_donors'         => $totalDonors,
            'todays_emergencies'   => $todaysEmergencies,
            'pending_emergencies'  => $pendingEmergencies,
            'resolved_emergencies' => $resolvedEmergencies
        ];
    }
}
