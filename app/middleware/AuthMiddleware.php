<?php
namespace App\Middleware;

/**
 * Authentication Middleware
 * Heritage Family Tree Application
 */
class AuthMiddleware {
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool {
        return isset($_SESSION['user']);
    }

    /**
     * Require authentication
     */
    public static function requireAuth(): void {
        if (!self::isAuthenticated()) {
            if (self::isApiRequest()) {
                jsonResponse(['error' => 'Unauthorized'], 401);
            } else {
                redirect('login.php');
            }
        }
    }

    /**
     * Require admin role
     */
    public static function requireAdmin(): void {
        self::requireAuth();
        
        if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
            if (self::isApiRequest()) {
                jsonResponse(['error' => 'Forbidden - Admin access required'], 403);
            } else {
                redirect('dashboard.php?error=access_denied');
            }
        }
    }

    /**
     * Check if request is API request
     */
    private static function isApiRequest(): bool {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/api/') !== false;
    }

    /**
     * Get current user
     */
    public static function getCurrentUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user owns resource
     */
    public static function ownsResource(string $createdBy): bool {
        $user = self::getCurrentUser();
        if (!$user) return false;
        
        return $user['user_id'] === $createdBy || $user['role'] === 'admin';
    }
}