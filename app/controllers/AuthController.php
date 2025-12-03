<?php
namespace App\Controllers;

use App\Models\User;

/**
 * Authentication Controller
 * Heritage Family Tree Application
 */
class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * POST /api/register
     */
    public function register(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            jsonResponse(['errors' => $errors], 400);
        }

        // Check if user exists
        if ($this->userModel->emailExists($data['email'])) {
            jsonResponse(['error' => 'Email already registered'], 409);
        }

        if ($this->userModel->usernameExists($data['username'])) {
            jsonResponse(['error' => 'Username already taken'], 409);
        }

        // Create user
        $user = $this->userModel->create([
            'username' => sanitize($data['username']),
            'email' => sanitize($data['email']),
            'password' => $data['password'],
            'role' => 'user'
        ]);

        if ($user) {
            unset($user['password']);
            jsonResponse([
                'message' => 'Registration successful',
                'user' => $user
            ], 201);
        } else {
            jsonResponse(['error' => 'Registration failed'], 500);
        }
    }

    /**
     * POST /api/login
     */
    public function login(): void {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['password'])) {
            jsonResponse(['error' => 'Email and password required'], 400);
        }

        // Find user
        $user = $this->userModel->findByEmail($data['email']);

        if (!$user || !$this->userModel->verifyPassword($data['password'], $user['password'])) {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }

        // Update last login
        $this->userModel->updateLastLogin($user['user_id']);

        // Set session
        unset($user['password']);
        $_SESSION['user'] = $user;

        jsonResponse([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(): void {
        session_destroy();
        jsonResponse(['message' => 'Logout successful']);
    }

    /**
     * GET /api/me
     */
    public function getCurrentUser(): void {
        if (!isset($_SESSION['user'])) {
            jsonResponse(['error' => 'Not authenticated'], 401);
        }

        jsonResponse(['user' => $_SESSION['user']]);
    }

    /**
     * Validate registration data
     */
    private function validateRegistration(array $data): array {
        $errors = [];

        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }

        if (empty($data['email']) || !validateEmail($data['email'])) {
            $errors[] = 'Valid email is required';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        return $errors;
    }
}