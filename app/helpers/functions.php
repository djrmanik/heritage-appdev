<?php
/**
 * Global Helper Functions
 * Heritage Family Tree Application
 */

/**
 * Generate UUID v4
 */
function generateUUID(): string {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Sanitize input
 */
function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate UUID format
 */
function isValidUUID(string $uuid): bool {
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
}

/**
 * Redirect helper
 */
function redirect(string $path): void {
    $config = require __DIR__ . '/../config/app.php';
    header("Location: {$config['base_url']}/{$path}");
    exit;
}

/**
 * JSON response helper
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get current user from session
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated(): bool {
    return isset($_SESSION['user']);
}

/**
 * Check if user is admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Format date for display
 */
function formatDate(?string $date, string $format = 'M d, Y'): string {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Calculate age from birthdate
 */
function calculateAge(?string $birthdate, ?string $deathdate = null): ?int {
    if (!$birthdate) return null;
    
    $birth = new DateTime($birthdate);
    $end = $deathdate ? new DateTime($deathdate) : new DateTime();
    
    return $birth->diff($end)->y;
}