<?php
namespace App\Controllers;

use App\Models\Family;
use App\Middleware\AuthMiddleware;

/**
 * Family Controller
 * Heritage Family Tree Application
 */
class FamilyController {
    private Family $familyModel;

    public function __construct() {
        $this->familyModel = new Family();
    }

    /**
     * GET /api/families
     */
    public function index(): void {
        AuthMiddleware::requireAuth();

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $families = $this->familyModel->findAll($limit, $offset);
        $total = $this->familyModel->count();

        jsonResponse([
            'families' => $families,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * GET /api/families/:id
     */
    public function show(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid family ID'], 400);
        }

        $family = $this->familyModel->findById($id);

        if (!$family) {
            jsonResponse(['error' => 'Family not found'], 404);
        }

        // Get members
        $members = $this->familyModel->getMembers($id);

        jsonResponse([
            'family' => $family,
            'members' => $members
        ]);
    }

    /**
     * POST /api/families
     */
    public function create(): void {
        AuthMiddleware::requireAuth();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        if (empty($data['family_name'])) {
            jsonResponse(['error' => 'Family name is required'], 400);
        }

        $user = AuthMiddleware::getCurrentUser();
        $data['created_by'] = $user['user_id'];

        $family = $this->familyModel->create([
            'family_name' => sanitize($data['family_name']),
            'description' => sanitize($data['description'] ?? ''),
            'created_by' => $data['created_by']
        ]);

        if ($family) {
            jsonResponse([
                'message' => 'Family created successfully',
                'family' => $family
            ], 201);
        } else {
            jsonResponse(['error' => 'Failed to create family'], 500);
        }
    }

    /**
     * PUT /api/families/:id
     */
    public function update(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid family ID'], 400);
        }

        $family = $this->familyModel->findById($id);
        if (!$family) {
            jsonResponse(['error' => 'Family not found'], 404);
        }

        // Check ownership
        if (!AuthMiddleware::ownsResource($family['created_by'])) {
            jsonResponse(['error' => 'Forbidden'], 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $updateData = [];
        if (isset($data['family_name'])) {
            $updateData['family_name'] = sanitize($data['family_name']);
        }
        if (isset($data['description'])) {
            $updateData['description'] = sanitize($data['description']);
        }

        $success = $this->familyModel->update($id, $updateData);

        if ($success) {
            $updatedFamily = $this->familyModel->findById($id);
            jsonResponse([
                'message' => 'Family updated successfully',
                'family' => $updatedFamily
            ]);
        } else {
            jsonResponse(['error' => 'Failed to update family'], 500);
        }
    }

    /**
     * DELETE /api/families/:id
     */
    public function delete(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid family ID'], 400);
        }

        $family = $this->familyModel->findById($id);
        if (!$family) {
            jsonResponse(['error' => 'Family not found'], 404);
        }

        // Check ownership
        if (!AuthMiddleware::ownsResource($family['created_by'])) {
            jsonResponse(['error' => 'Forbidden'], 403);
        }

        $success = $this->familyModel->delete($id);

        if ($success) {
            jsonResponse(['message' => 'Family deleted successfully']);
        } else {
            jsonResponse(['error' => 'Failed to delete family'], 500);
        }
    }

    /**
     * POST /api/families/:id/members
     */
    public function addMember(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid family ID'], 400);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['person_id'])) {
            jsonResponse(['error' => 'Person ID is required'], 400);
        }

        $success = $this->familyModel->addMember(
            $id,
            $data['person_id'],
            $data['role_in_family'] ?? 'bloodline',
            $data['note'] ?? null
        );

        if ($success) {
            jsonResponse(['message' => 'Member added successfully'], 201);
        } else {
            jsonResponse(['error' => 'Failed to add member'], 500);
        }
    }

    /**
     * DELETE /api/families/:id/members/:personId
     */
    public function removeMember(string $id, string $personId): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id) || !isValidUUID($personId)) {
            jsonResponse(['error' => 'Invalid ID'], 400);
        }

        $success = $this->familyModel->removeMember($id, $personId);

        if ($success) {
            jsonResponse(['message' => 'Member removed successfully']);
        } else {
            jsonResponse(['error' => 'Failed to remove member'], 500);
        }
    }
}