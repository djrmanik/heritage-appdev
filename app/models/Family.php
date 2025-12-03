<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Family Model
 * Heritage Family Tree Application
 */
class Family {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new family
     */
    public function create(array $data): ?array {
        try {
            $uuid = generateUUID();

            $sql = "INSERT INTO families (family_id, family_name, description, created_by) 
                    VALUES (:family_id, :family_name, :description, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':family_id' => $uuid,
                ':family_name' => $data['family_name'],
                ':description' => $data['description'] ?? null,
                ':created_by' => $data['created_by'] ?? null
            ]);

            return $this->findById($uuid);
        } catch (PDOException $e) {
            error_log("Family Create Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find family by ID
     */
    public function findById(string $familyId): ?array {
        try {
            $sql = "SELECT f.*, u.username as creator_name 
                    FROM families f
                    LEFT JOIN users u ON f.created_by = u.user_id
                    WHERE f.family_id = :family_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':family_id' => $familyId]);
            
            $family = $stmt->fetch();
            return $family ?: null;
        } catch (PDOException $e) {
            error_log("Family FindById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all families
     */
    public function findAll(int $limit = 100, int $offset = 0): array {
        try {
            $sql = "SELECT f.*, u.username as creator_name,
                    (SELECT COUNT(*) FROM person_families pf WHERE pf.family_id = f.family_id) as member_count
                    FROM families f
                    LEFT JOIN users u ON f.created_by = u.user_id
                    ORDER BY f.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Family FindAll Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update family
     */
    public function update(string $familyId, array $data): bool {
        try {
            $fields = [];
            $params = [':family_id' => $familyId];

            if (isset($data['family_name'])) {
                $fields[] = "family_name = :family_name";
                $params[':family_name'] = $data['family_name'];
            }
            if (isset($data['description'])) {
                $fields[] = "description = :description";
                $params[':description'] = $data['description'];
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE families SET " . implode(', ', $fields) . " WHERE family_id = :family_id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Family Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete family
     */
    public function delete(string $familyId): bool {
        try {
            $sql = "DELETE FROM families WHERE family_id = :family_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':family_id' => $familyId]);
        } catch (PDOException $e) {
            error_log("Family Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get family members
     */
    public function getMembers(string $familyId): array {
        try {
            $sql = "SELECT p.*, pf.role_in_family, pf.note as family_note
                    FROM persons p
                    INNER JOIN person_families pf ON p.person_id = pf.person_id
                    WHERE pf.family_id = :family_id
                    ORDER BY p.birthdate ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':family_id' => $familyId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Family GetMembers Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add person to family
     */
    public function addMember(string $familyId, string $personId, string $role = 'bloodline', ?string $note = null): bool {
        try {
            $uuid = generateUUID();
            
            $sql = "INSERT INTO person_families (id, person_id, family_id, role_in_family, note) 
                    VALUES (:id, :person_id, :family_id, :role_in_family, :note)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $uuid,
                ':person_id' => $personId,
                ':family_id' => $familyId,
                ':role_in_family' => $role,
                ':note' => $note
            ]);
        } catch (PDOException $e) {
            error_log("Family AddMember Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove person from family
     */
    public function removeMember(string $familyId, string $personId): bool {
        try {
            $sql = "DELETE FROM person_families WHERE family_id = :family_id AND person_id = :person_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':family_id' => $familyId,
                ':person_id' => $personId
            ]);
        } catch (PDOException $e) {
            error_log("Family RemoveMember Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get families by user (creator)
     */
    public function findByCreator(string $userId): array {
        try {
            $sql = "SELECT f.*,
                    (SELECT COUNT(*) FROM person_families pf WHERE pf.family_id = f.family_id) as member_count
                    FROM families f
                    WHERE f.created_by = :created_by
                    ORDER BY f.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':created_by' => $userId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Family FindByCreator Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total families
     */
    public function count(): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM families";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Family Count Error: " . $e->getMessage());
            return 0;
        }
    }
}