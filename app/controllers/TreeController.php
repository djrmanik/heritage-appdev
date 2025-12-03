<?php
namespace App\Controllers;

use App\Models\Person;
use App\Models\Relationship;
use App\Models\Family;
use App\Middleware\AuthMiddleware;

/**
 * Tree Controller - Family Tree Generation
 * Heritage Family Tree Application
 */
class TreeController {
    private Person $personModel;
    private Relationship $relationshipModel;
    private Family $familyModel;

    public function __construct() {
        $this->personModel = new Person();
        $this->relationshipModel = new Relationship();
        $this->familyModel = new Family();
    }

    /**
     * GET /api/tree/:familyId
     * Generate tree data for D3.js visualization
     */
    public function getTreeData(string $familyId): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($familyId)) {
            jsonResponse(['error' => 'Invalid family ID'], 400);
        }

        $family = $this->familyModel->findById($familyId);
        if (!$family) {
            jsonResponse(['error' => 'Family not found'], 404);
        }

        // Get all persons in this family
        $persons = $this->personModel->findByFamily($familyId);

        // Get all relationships in this family
        $relationships = $this->relationshipModel->findByFamily($familyId);

        // Build tree structure
        $tree = $this->buildTree($persons, $relationships);

        jsonResponse([
            'family' => $family,
            'tree' => $tree,
            'persons' => $persons,
            'relationships' => $relationships
        ]);
    }

    /**
     * Build hierarchical tree structure
     */
    private function buildTree(array $persons, array $relationships): array {
        // Create person lookup
        $personLookup = [];
        foreach ($persons as $person) {
            $personLookup[$person['person_id']] = [
                'id' => $person['person_id'],
                'name' => $person['fullname'],
                'gender' => $person['gender'],
                'birthdate' => $person['birthdate'],
                'deathdate' => $person['deathdate'],
                'is_alive' => $person['is_alive'],
                'birthplace' => $person['birthplace'],
                'photo_url' => $person['photo_url'],
                'children' => [],
                'spouses' => [],
                'parents' => []
            ];
        }

        // Process parent-child relationships
        foreach ($relationships as $rel) {
            if ($rel['relation_type'] === 'parent') {
                $parentId = $rel['member_id_1'];
                $childId = $rel['member_id_2'];

                if (isset($personLookup[$parentId]) && isset($personLookup[$childId])) {
                    $personLookup[$parentId]['children'][] = $childId;
                    $personLookup[$childId]['parents'][] = $parentId;
                }
            }
        }

        // Process spouse relationships
        foreach ($relationships as $rel) {
            if ($rel['relation_type'] === 'spouse') {
                $spouse1 = $rel['member_id_1'];
                $spouse2 = $rel['member_id_2'];

                if (isset($personLookup[$spouse1]) && isset($personLookup[$spouse2])) {
                    $personLookup[$spouse1]['spouses'][] = $spouse2;
                    $personLookup[$spouse2]['spouses'][] = $spouse1;
                }
            }
        }

        // Find root persons (those with no parents in the family)
        $roots = [];
        foreach ($personLookup as $personId => $person) {
            if (empty($person['parents'])) {
                $roots[] = $this->buildPersonNode($personId, $personLookup);
            }
        }

        return [
            'name' => 'Root',
            'children' => $roots
        ];
    }

    /**
     * Build individual person node with children
     */
    private function buildPersonNode(string $personId, array &$personLookup, array &$visited = []): array {
        // Prevent infinite loops
        if (isset($visited[$personId])) {
            return $personLookup[$personId];
        }
        $visited[$personId] = true;

        $person = $personLookup[$personId];
        $node = [
            'id' => $person['id'],
            'name' => $person['name'],
            'gender' => $person['gender'],
            'birthdate' => $person['birthdate'],
            'deathdate' => $person['deathdate'],
            'is_alive' => $person['is_alive'],
            'birthplace' => $person['birthplace'],
            'photo_url' => $person['photo_url'],
            'spouses' => $person['spouses'],
            'children' => []
        ];

        // Add children recursively
        foreach ($person['children'] as $childId) {
            if (isset($personLookup[$childId])) {
                $node['children'][] = $this->buildPersonNode($childId, $personLookup, $visited);
            }
        }

        return $node;
    }

    /**
     * GET /api/tree/:familyId/simple
     * Generate simple hierarchical tree for D3
     */
    public function getSimpleTree(string $familyId): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($familyId)) {
            jsonResponse(['error' => 'Invalid family ID'], 400);
        }

        $persons = $this->personModel->findByFamily($familyId);
        $relationships = $this->relationshipModel->getParentChildRelationships($familyId);

        // Create adjacency map
        $childrenMap = [];
        $allPersonIds = [];
        
        foreach ($persons as $person) {
            $allPersonIds[$person['person_id']] = $person;
            $childrenMap[$person['person_id']] = [];
        }

        foreach ($relationships as $rel) {
            $parentId = $rel['member_id_1'];
            $childId = $rel['member_id_2'];
            
            if (isset($childrenMap[$parentId])) {
                $childrenMap[$parentId][] = $childId;
            }
        }

        // Find roots (persons with no parents)
        $hasParent = [];
        foreach ($relationships as $rel) {
            $hasParent[$rel['member_id_2']] = true;
        }

        $roots = [];
        foreach ($allPersonIds as $personId => $person) {
            if (!isset($hasParent[$personId])) {
                $roots[] = $this->buildSimpleNode($personId, $allPersonIds, $childrenMap);
            }
        }

        jsonResponse([
            'name' => 'Family Tree',
            'children' => $roots
        ]);
    }

    /**
     * Build simple node for D3 hierarchy
     */
    private function buildSimpleNode(string $personId, array $persons, array $childrenMap): array {
        $person = $persons[$personId];
        
        $node = [
            'name' => $person['fullname'],
            'id' => $person['person_id'],
            'gender' => $person['gender'],
            'birthdate' => $person['birthdate'],
            'deathdate' => $person['deathdate']
        ];

        if (!empty($childrenMap[$personId])) {
            $node['children'] = [];
            foreach ($childrenMap[$personId] as $childId) {
                $node['children'][] = $this->buildSimpleNode($childId, $persons, $childrenMap);
            }
        }

        return $node;
    }
}