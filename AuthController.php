<?php
/**
 * Authentication Controller
 * JEEVANSETU - Rural Emergency Response System
 */

declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../includes/validator.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

class AuthController
{
    private User $userModel;
    private Admin $adminModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->adminModel = new Admin();
    }

    /**
     * User Registration Logic
     */
    public function register(array $data): void
    {
        $name       = sanitizeInput($data['name'] ?? '');
        $email      = sanitizeInput($data['email'] ?? '');
        $phone      = sanitizeInput($data['phone'] ?? '');
        $password   = $data['password'] ?? '';
        $bloodGroup = sanitizeInput($data['blood_group'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($bloodGroup)) {
            jsonResponse(false, 'All fields are required.', null, 400);
        }

        if (!Validator::isValidEmail($email)) {
            jsonResponse(false, 'Invalid email address format.', null, 400);
        }

        if (!Validator::isValidPhone($phone)) {
            jsonResponse(false, 'Invalid phone number format (must be 10-15 digits).', null, 400);
        }

        if (!Validator::isValidBloodGroup($bloodGroup)) {
            jsonResponse(false, 'Invalid blood group specified.', null, 400);
        }

        if (strlen($password) < 6) {
            jsonResponse(false, 'Password must be at least 6 characters long.', null, 400);
        }

        if ($this->userModel->findByEmail($email)) {
            jsonResponse(false, 'Email address is already registered.', null, 409);
        }

        if ($this->userModel->findByPhone($phone)) {
            jsonResponse(false, 'Phone number is already registered.', null, 409);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $userId = $this->userModel->create($name, $email, $phone, $hashedPassword, $bloodGroup);
            jsonResponse(true, 'Registration successful.', ['user_id' => $userId], 201);
        } catch (Exception $e) {
            logAppError("User Register Error: " . $e->getMessage());
            jsonResponse(false, 'Registration failed. Please try again.', null, 500);
        }
    }

    /**
     * User Login Logic
     */
    public function login(array $data): void
    {
        $loginInput = sanitizeInput($data['email_or_phone'] ?? '');
        $password   = $data['password'] ?? '';

        if (empty($loginInput) || empty($password)) {
            jsonResponse(false, 'Email/Phone and password are required.', null, 400);
        }

        $user = Validator::isValidEmail($loginInput) 
            ? $this->userModel->findByEmail($loginInput) 
            : $this->userModel->findByPhone($loginInput);

        if (!$user || !password_verify($password, $user['password'])) {
            jsonResponse(false, 'Invalid login credentials.', null, 401);
        }

        regenerateSession();
        $_SESSION[SESSION_USER_KEY] = [
            'id'          => $user['id'],
            'name'        => $user['name'],
            'email'       => $user['email'],
            'phone'       => $user['phone'],
            'blood_group' => $user['blood_group']
        ];

        jsonResponse(true, 'Login successful.', [
            'user' => $_SESSION[SESSION_USER_KEY]
        ]);
    }

    /**
     * Admin Login Logic
     */
    public function adminLogin(array $data): void
    {
        $username = sanitizeInput($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            jsonResponse(false, 'Username and password are required.', null, 400);
        }

        $admin = $this->adminModel->findByUsername($username);

        if (!$admin || !password_verify($password, $admin['password'])) {
            jsonResponse(false, 'Invalid admin credentials.', null, 401);
        }

        regenerateSession();
        $_SESSION[SESSION_ADMIN_KEY] = [
            'id'       => $admin['id'],
            'username' => $admin['username']
        ];

        jsonResponse(true, 'Admin authentication successful.', [
            'admin' => $_SESSION[SESSION_ADMIN_KEY]
        ]);
    }

    /**
     * Logout Logic
     */
    public function logout(): void
    {
        destroySession();
        jsonResponse(true, 'Successfully logged out.');
    }
}
