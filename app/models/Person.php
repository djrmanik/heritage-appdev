<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Person Model
 * Heritage Family Tree Application
 */
class Person {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new person
     */
    public function create(array $data): ?array {
        try {
            $uuid = generateUUID();

            $sql = "INSERT INTO persons (
                        person_id, user_id, fullname, gender, birthdate, deathdate, 
                        is_alive, birthplace, photo_url, notes, created_by
                    ) VALUES (
                        :person_id, :user_id, :fullname, :gender, :birthdate, :deathdate,
                        :is_alive, :birthplace, :photo_url, :notes, :created_by
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':person_id' => $uuid,
                ':user_id' => $data['user_id'] ?? null,
                ':fullname' => $data['fullname'],
                ':gender' => $data['gender'] ?? null,
                ':birthdate' => $data['birthdate'] ?? null,
                ':deathdate' => $data['deathdate'] ?? null,
                ':is_alive' => $data['is_alive'] ?? true,
                ':birthplace' => $data['birthplace'] ?? null,
                ':photo_url' => $data['photo_url'] ?? null,
                ':notes' => $data['notes'] ?? null,
                ':created_by' => $data['created_by'] ?? null
            ]);

            return $this->findById($uuid);
        } catch (PDOException $e) {
            error_log("Person Create Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find person by ID
     */
    public function findById(string $personId): ?array {
        try {
            $sql = "SELECT p.*, u.username as linked_username, c.username as creator_name
                    FROM persons p
                    LEFT JOIN users u ON p.user_id = u.user_id
                    LEFT JOIN users c ON p.created_by = c.user_id
                    WHERE p.person_id = :person_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':person_id' => $personId]);
            
            $person = $stmt->fetch();
            return $person ?: null;
        } catch (PDOException $e) {
            error_log("Person FindById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all persons
     */
    public function findAll(int $limit = 100, int $offset = 0): array {
        try {
            $sql = "SELECT p.*, u.username as linked_username
                    FROM persons p
                    LEFT JOIN users u ON p.user_id = u.user_id
                    ORDER BY p.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person FindAll Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update person
     */
    public function update(string $personId, array $data): bool {
        try {
            $fields = [];
            $params = [':person_id' => $personId];

            $allowedFields = ['user_id', 'fullname', 'gender', 'birthdate', 'deathdate', 
                             'is_alive', 'birthplace', 'photo_url', 'notes'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE persons SET " . implode(', ', $fields) . " WHERE person_id = :person_id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Person Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete person
     */
    public function delete(string $personId): bool {
        try {
            $sql = "DELETE FROM persons WHERE person_id = :person_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':person_id' => $personId]);
        } catch (PDOException $e) {
            error_log("Person Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get person's families
     */
    public function getFamilies(string $personId): array {
        try {
            $sql = "SELECT f.*, pf.role_in_family, pf.note as family_note
                    FROM families f
                    INNER JOIN person_families pf ON f.family_id = pf.family_id
                    WHERE pf.person_id = :person_id
                    ORDER BY f.family_name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':person_id' => $personId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person GetFamilies Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get person's parents
     */
    public function getParents(string $personId): array {
        try {
            $sql = "SELECT p.*, r.started_at as relationship_start
                    FROM persons p
                    INNER JOIN relationships r ON p.person_id = r.member_id_1
                    WHERE r.member_id_2 = :person_id AND r.relation_type = 'parent'
                    ORDER BY p.gender DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':person_id' => $personId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person GetParents Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get person's children
     */
    public function getChildren(string $personId): array {
        try {
            $sql = "SELECT p.*, r.started_at as relationship_start
                    FROM persons p
                    INNER JOIN relationships r ON p.person_id = r.member_id_2
                    WHERE r.member_id_1 = :person_id AND r.relation_type = 'parent'
                    ORDER BY p.birthdate ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':person_id' => $personId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person GetChildren Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get person's spouses
     */
    public function getSpouses(string $personId): array {
        try {
            $sql = "SELECT p.*, r.started_at, r.ended_at
                    FROM persons p
                    INNER JOIN relationships r ON (
                        (r.member_id_1 = :person_id AND r.member_id_2 = p.person_id) OR
                        (r.member_id_2 = :person_id AND r.member_id_1 = p.person_id)
                    )
                    WHERE r.relation_type = 'spouse'
                    ORDER BY r.started_at ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':person_id' => $personId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person GetSpouses Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get persons by family
     */
    public function findByFamily(string $familyId): array {
        try {
            $sql = "SELECT p.*, pf.role_in_family
                    FROM persons p
                    INNER JOIN person_families pf ON p.person_id = pf.person_id
                    WHERE pf.family_id = :family_id
                    ORDER BY p.birthdate ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':family_id' => $familyId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person FindByFamily Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search persons by name
     */
    public function search(string $query): array {
        try {
            $sql = "SELECT p.*, u.username as linked_username
                    FROM persons p
                    LEFT JOIN users u ON p.user_id = u.user_id
                    WHERE p.fullname LIKE :query
                    ORDER BY p.fullname ASC
                    LIMIT 50";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':query' => "%{$query}%"]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person Search Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total persons
     */
    public function count(): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM persons";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Person Count Error: " . $e->getMessage());
            return 0;
        }
    }
}