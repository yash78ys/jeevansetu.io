<?php
/**
 * User Data Model
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Create a new user record
     */
    public function create(string $name, string $email, string $phone, string $hashedPassword, string $bloodGroup): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, phone, password, blood_group)
            VALUES (:name, :email, :phone, :password, :blood_group)
        ");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':password' => $hashedPassword,
            ':blood_group' => $bloodGroup
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Find user by Email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by Phone
     */
    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = :phone LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name, email, phone, blood_group, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Update user profile information
     */
    public function updateProfile(int $id, string $name, string $phone, string $bloodGroup): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET name = :name, phone = :phone, blood_group = :blood_group
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':phone' => $phone,
            ':blood_group' => $bloodGroup
        ]);
    }

    /**
     * Get emergency contacts for a user
     */
    public function getEmergencyContacts(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM emergency_contacts WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Add emergency contact
     */
    public function addEmergencyContact(int $userId, string $contactName, string $phone): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO emergency_contacts (user_id, contact_name, phone)
            VALUES (:user_id, :contact_name, :phone)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':contact_name' => $contactName,
            ':phone' => $phone
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Delete emergency contact
     */
    public function deleteEmergencyContact(int $contactId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM emergency_contacts WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $contactId, ':user_id' => $userId]);
    }
}
