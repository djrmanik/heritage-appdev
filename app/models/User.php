<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * User Model
 * Heritage Family Tree Application
 */
class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new user
     */
    public function create(array $data): ?array {
        try {
            $uuid = generateUUID();
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            $sql = "INSERT INTO users (user_id, username, email, password, role) 
                    VALUES (:user_id, :username, :email, :password, :role)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $uuid,
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => $hashedPassword,
                ':role' => $data['role'] ?? 'user'
            ]);

            return $this->findById($uuid);
        } catch (PDOException $e) {
            error_log("User Create Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by ID
     */
    public function findById(string $userId): ?array {
        try {
            $sql = "SELECT user_id, username, email, role, last_login, created_at, updated_at 
                    FROM users WHERE user_id = :user_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("User FindById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("User FindByEmail Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array {
        try {
            $sql = "SELECT * FROM users WHERE username = :username";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);
            
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("User FindByUsername Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all users
     */
    public function findAll(int $limit = 100, int $offset = 0): array {
        try {
            $sql = "SELECT user_id, username, email, role, last_login, created_at, updated_at 
                    FROM users 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("User FindAll Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user
     */
    public function update(string $userId, array $data): bool {
        try {
            $fields = [];
            $params = [':user_id' => $userId];

            if (isset($data['username'])) {
                $fields[] = "username = :username";
                $params[':username'] = $data['username'];
            }
            if (isset($data['email'])) {
                $fields[] = "email = :email";
                $params[':email'] = $data['email'];
            }
            if (isset($data['password'])) {
                $fields[] = "password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            if (isset($data['role'])) {
                $fields[] = "role = :role";
                $params[':role'] = $data['role'];
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("User Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(string $userId): bool {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log("User UpdateLastLogin Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function delete(string $userId): bool {
        try {
            $sql = "DELETE FROM users WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log("User Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool {
        return $this->findByEmail($email) !== null;
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username): bool {
        return $this->findByUsername($username) !== null;
    }

    /**
     * Count total users
     */
    public function count(): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("User Count Error: " . $e->getMessage());
            return 0;
        }
    }
}