<?php
namespace App\Controllers;

use App\Models\Relationship;
use App\Models\Person;
use App\Middleware\AuthMiddleware;

/**
 * Relationship Controller
 * Heritage Family Tree Application
 */
class RelationshipController {
    private Relationship $relationshipModel;
    private Person $personModel;

    public function __construct() {
        $this->relationshipModel = new Relationship();
        $this->personModel = new Person();
    }

    /**
     * GET /api/relationships
     */
    public function index(): void {
        AuthMiddleware::requireAuth();

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $familyId = $_GET['family'] ?? null;
        $personId = $_GET['person'] ?? null;

        if ($familyId) {
            if (!isValidUUID($familyId)) {
                jsonResponse(['error' => 'Invalid family ID'], 400);
            }
            $relationships = $this->relationshipModel->findByFamily($familyId);
            $total = count($relationships);
        } elseif ($personId) {
            if (!isValidUUID($personId)) {
                jsonResponse(['error' => 'Invalid person ID'], 400);
            }
            $relationships = $this->relationshipModel->findByPerson($personId);
            $total = count($relationships);
        } else {
            $relationships = $this->relationshipModel->findAll($limit, $offset);
            $total = $this->relationshipModel->count();
        }

        jsonResponse([
            'relationships' => $relationships,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * GET /api/relationships/:id
     */
    public function show(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid relationship ID'], 400);
        }

        $relationship = $this->relationshipModel->findById($id);

        if (!$relationship) {
            jsonResponse(['error' => 'Relationship not found'], 404);
        }

        jsonResponse(['relationship' => $relationship]);
    }

    /**
     * POST /api/relationships
     */
    public function create(): void {
        AuthMiddleware::requireAuth();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        $errors = $this->validateRelationship($data);
        if (!empty($errors)) {
            jsonResponse(['errors' => $errors], 400);
        }

        // Check if persons exist
        $person1 = $this->personModel->findById($data['member_id_1']);
        $person2 = $this->personModel->findById($data['member_id_2']);

        if (!$person1 || !$person2) {
            jsonResponse(['error' => 'One or both persons not found'], 404);
        }

        // Check if relationship already exists
        if ($this->relationshipModel->exists($data['member_id_1'], $data['member_id_2'], $data['relation_type'])) {
            jsonResponse(['error' => 'Relationship already exists'], 409);
        }

        // For spouse relationships, also check reverse
        if ($data['relation_type'] === 'spouse') {
            if ($this->relationshipModel->exists($data['member_id_2'], $data['member_id_1'], 'spouse')) {
                jsonResponse(['error' => 'Spouse relationship already exists'], 409);
            }
        }

        $relationshipData = [
            'member_id_1' => $data['member_id_1'],
            'member_id_2' => $data['member_id_2'],
            'relation_type' => $data['relation_type'],
            'started_at' => $data['started_at'] ?? null,
            'ended_at' => $data['ended_at'] ?? null,
            'note' => sanitize($data['note'] ?? '')
        ];

        $relationship = $this->relationshipModel->create($relationshipData);

        if ($relationship) {
            jsonResponse([
                'message' => 'Relationship created successfully',
                'relationship' => $relationship
            ], 201);
        } else {
            jsonResponse(['error' => 'Failed to create relationship'], 500);
        }
    }

    /**
     * PUT /api/relationships/:id
     */
    public function update(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid relationship ID'], 400);
        }

        $relationship = $this->relationshipModel->findById($id);
        if (!$relationship) {
            jsonResponse(['error' => 'Relationship not found'], 404);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $updateData = [];
        if (isset($data['started_at'])) {
            $updateData['started_at'] = $data['started_at'];
        }
        if (isset($data['ended_at'])) {
            $updateData['ended_at'] = $data['ended_at'];
        }
        if (isset($data['note'])) {
            $updateData['note'] = sanitize($data['note']);
        }

        $success = $this->relationshipModel->update($id, $updateData);

        if ($success) {
            $updatedRelationship = $this->relationshipModel->findById($id);
            jsonResponse([
                'message' => 'Relationship updated successfully',
                'relationship' => $updatedRelationship
            ]);
        } else {
            jsonResponse(['error' => 'Failed to update relationship'], 500);
        }
    }

    /**
     * DELETE /api/relationships/:id
     */
    public function delete(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid relationship ID'], 400);
        }

        $relationship = $this->relationshipModel->findById($id);
        if (!$relationship) {
            jsonResponse(['error' => 'Relationship not found'], 404);
        }

        $success = $this->relationshipModel->delete($id);

        if ($success) {
            jsonResponse(['message' => 'Relationship deleted successfully']);
        } else {
            jsonResponse(['error' => 'Failed to delete relationship'], 500);
        }
    }

    /**
     * Validate relationship data
     */
    private function validateRelationship(array $data): array {
        $errors = [];

        if (empty($data['member_id_1']) || !isValidUUID($data['member_id_1'])) {
            $errors[] = 'Valid member_id_1 is required';
        }

        if (empty($data['member_id_2']) || !isValidUUID($data['member_id_2'])) {
            $errors[] = 'Valid member_id_2 is required';
        }

        if ($data['member_id_1'] === $data['member_id_2']) {
            $errors[] = 'A person cannot have a relationship with themselves';
        }

        if (empty($data['relation_type']) || !in_array($data['relation_type'], ['parent', 'spouse'])) {
            $errors[] = 'Valid relation_type is required (parent or spouse)';
        }

        return $errors;
    }
}