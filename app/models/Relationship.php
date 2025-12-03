<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Relationship Model
 * Heritage Family Tree Application
 */
class Relationship {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new relationship
     */
    public function create(array $data): ?array {
        try {
            $uuid = generateUUID();

            $sql = "INSERT INTO relationships (
                        relationship_id, member_id_1, member_id_2, relation_type,
                        started_at, ended_at, note
                    ) VALUES (
                        :relationship_id, :member_id_1, :member_id_2, :relation_type,
                        :started_at, :ended_at, :note
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':relationship_id' => $uuid,
                ':member_id_1' => $data['member_id_1'],
                ':member_id_2' => $data['member_id_2'],
                ':relation_type' => $data['relation_type'],
                ':started_at' => $data['started_at'] ?? null,
                ':ended_at' => $data['ended_at'] ?? null,
                ':note' => $data['note'] ?? null
            ]);

            return $this->findById($uuid);
        } catch (PDOException $e) {
            error_log("Relationship Create Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find relationship by ID
     */
    public function findById(string $relationshipId): ?array {
        try {
            $sql = "SELECT r.*,
                    p1.fullname as member_1_name,
                    p2.fullname as member_2_name
                    FROM relationships r
                    INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                    INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                    WHERE r.relationship_id = :relationship_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':relationship_id' => $relationshipId]);
            
            $relationship = $stmt->fetch();
            return $relationship ?: null;
        } catch (PDOException $e) {
            error_log("Relationship FindById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all relationships
     */
    public function findAll(int $limit = 100, int $offset = 0): array {
        try {
            $sql = "SELECT r.*,
                    p1.fullname as member_1_name,
                    p2.fullname as member_2_name
                    FROM relationships r
                    INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                    INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                    ORDER BY r.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Relationship FindAll Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update relationship
     */
    public function update(string $relationshipId, array $data): bool {
        try {
            $fields = [];
            $params = [':relationship_id' => $relationshipId];

            $allowedFields = ['started_at', 'ended_at', 'note'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE relationships SET " . implode(', ', $fields) . " WHERE relationship_id = :relationship_id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Relationship Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete relationship
     */
    public function delete(string $relationshipId): bool {
        try {
            $sql = "DELETE FROM relationships WHERE relationship_id = :relationship_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':relationship_id' => $relationshipId]);
        } catch (PDOException $e) {
            error_log("Relationship Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get relationships by person
     */
    public function findByPerson(string $personId): array {
        try {
            $sql = "SELECT r.*,
                    p1.fullname as member_1_name,
                    p2.fullname as member_2_name
                    FROM relationships r
                    INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                    INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                    WHERE r.member_id_1 = :person_id OR r.member_id_2 = :person_id
                    ORDER BY r.relation_type, r.started_at ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':person_id' => $personId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Relationship FindByPerson Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get relationships by family
     */
    public function findByFamily(string $familyId): array {
        try {
            $sql = "SELECT DISTINCT r.*,
                    p1.fullname as member_1_name,
                    p2.fullname as member_2_name
                    FROM relationships r
                    INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                    INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                    INNER JOIN person_families pf1 ON p1.person_id = pf1.person_id
                    INNER JOIN person_families pf2 ON p2.person_id = pf2.person_id
                    WHERE pf1.family_id = :family_id AND pf2.family_id = :family_id
                    ORDER BY r.relation_type, r.started_at ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':family_id' => $familyId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Relationship FindByFamily Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if relationship exists
     */
    public function exists(string $member1, string $member2, string $relationType): bool {
        try {
            $sql = "SELECT COUNT(*) as count FROM relationships 
                    WHERE member_id_1 = :member_id_1 
                    AND member_id_2 = :member_id_2 
                    AND relation_type = :relation_type";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':member_id_1' => $member1,
                ':member_id_2' => $member2,
                ':relation_type' => $relationType
            ]);
            
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Relationship Exists Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count total relationships
     */
    public function count(): int {
        try {
            $sql = "SELECT COUNT(*) as total FROM relationships";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Relationship Count Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get parent-child relationships for tree generation
     */
    public function getParentChildRelationships(?string $familyId = null): array {
        try {
            if ($familyId) {
                $sql = "SELECT DISTINCT r.*,
                        p1.fullname as parent_name, p1.gender as parent_gender,
                        p2.fullname as child_name, p2.gender as child_gender,
                        p1.birthdate as parent_birthdate, p2.birthdate as child_birthdate
                        FROM relationships r
                        INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                        INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                        INNER JOIN person_families pf1 ON p1.person_id = pf1.person_id
                        INNER JOIN person_families pf2 ON p2.person_id = pf2.person_id
                        WHERE r.relation_type = 'parent'
                        AND pf1.family_id = :family_id 
                        AND pf2.family_id = :family_id
                        ORDER BY p2.birthdate ASC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':family_id' => $familyId]);
            } else {
                $sql = "SELECT r.*,
                        p1.fullname as parent_name, p1.gender as parent_gender,
                        p2.fullname as child_name, p2.gender as child_gender,
                        p1.birthdate as parent_birthdate, p2.birthdate as child_birthdate
                        FROM relationships r
                        INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                        INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                        WHERE r.relation_type = 'parent'
                        ORDER BY p2.birthdate ASC";
                
                $stmt = $this->db->query($sql);
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Relationship GetParentChild Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get spouse relationships
     */
    public function getSpouseRelationships(?string $familyId = null): array {
        try {
            if ($familyId) {
                $sql = "SELECT DISTINCT r.*,
                        p1.fullname as spouse_1_name, p1.gender as spouse_1_gender,
                        p2.fullname as spouse_2_name, p2.gender as spouse_2_gender
                        FROM relationships r
                        INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                        INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                        INNER JOIN person_families pf1 ON p1.person_id = pf1.person_id
                        INNER JOIN person_families pf2 ON p2.person_id = pf2.person_id
                        WHERE r.relation_type = 'spouse'
                        AND pf1.family_id = :family_id 
                        AND pf2.family_id = :family_id
                        ORDER BY r.started_at ASC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':family_id' => $familyId]);
            } else {
                $sql = "SELECT r.*,
                        p1.fullname as spouse_1_name, p1.gender as spouse_1_gender,
                        p2.fullname as spouse_2_name, p2.gender as spouse_2_gender
                        FROM relationships r
                        INNER JOIN persons p1 ON r.member_id_1 = p1.person_id
                        INNER JOIN persons p2 ON r.member_id_2 = p2.person_id
                        WHERE r.relation_type = 'spouse'
                        ORDER BY r.started_at ASC";
                
                $stmt = $this->db->query($sql);
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Relationship GetSpouse Error: " . $e->getMessage());
            return [];
        }
    }
}