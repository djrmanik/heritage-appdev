<?php
namespace App\Controllers;

use App\Models\Person;
use App\Middleware\AuthMiddleware;

/**
 * Person Controller
 * Heritage Family Tree Application
 */
class PersonController {
    private Person $personModel;

    public function __construct() {
        $this->personModel = new Person();
    }

    /**
     * GET /api/persons
     */
    public function index(): void {
        AuthMiddleware::requireAuth();

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $familyId = $_GET['family'] ?? null;

        if ($familyId) {
            if (!isValidUUID($familyId)) {
                jsonResponse(['error' => 'Invalid family ID'], 400);
            }
            $persons = $this->personModel->findByFamily($familyId);
            $total = count($persons);
        } else {
            $persons = $this->personModel->findAll($limit, $offset);
            $total = $this->personModel->count();
        }

        jsonResponse([
            'persons' => $persons,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * GET /api/persons/:id
     */
    public function show(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid person ID'], 400);
        }

        $person = $this->personModel->findById($id);

        if (!$person) {
            jsonResponse(['error' => 'Person not found'], 404);
        }

        // Get related data
        $families = $this->personModel->getFamilies($id);
        $parents = $this->personModel->getParents($id);
        $children = $this->personModel->getChildren($id);
        $spouses = $this->personModel->getSpouses($id);

        jsonResponse([
            'person' => $person,
            'families' => $families,
            'parents' => $parents,
            'children' => $children,
            'spouses' => $spouses
        ]);
    }

    /**
     * POST /api/persons
     */
    public function create(): void {
        AuthMiddleware::requireAuth();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validation
        if (empty($data['fullname'])) {
            jsonResponse(['error' => 'Full name is required'], 400);
        }

        $user = AuthMiddleware::getCurrentUser();
        $data['created_by'] = $user['user_id'];

        // Prepare person data
        $personData = [
            'fullname' => sanitize($data['fullname']),
            'user_id' => $data['user_id'] ?? null,
            'gender' => $data['gender'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'deathdate' => $data['deathdate'] ?? null,
            'is_alive' => $data['is_alive'] ?? true,
            'birthplace' => sanitize($data['birthplace'] ?? ''),
            'photo_url' => sanitize($data['photo_url'] ?? ''),
            'notes' => sanitize($data['notes'] ?? ''),
            'created_by' => $data['created_by']
        ];

        $person = $this->personModel->create($personData);

        if ($person) {
            jsonResponse([
                'message' => 'Person created successfully',
                'person' => $person
            ], 201);
        } else {
            jsonResponse(['error' => 'Failed to create person'], 500);
        }
    }

    /**
     * PUT /api/persons/:id
     */
    public function update(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid person ID'], 400);
        }

        $person = $this->personModel->findById($id);
        if (!$person) {
            jsonResponse(['error' => 'Person not found'], 404);
        }

        // Check ownership
        if (!AuthMiddleware::ownsResource($person['created_by'])) {
            jsonResponse(['error' => 'Forbidden'], 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $updateData = [];
        $allowedFields = ['fullname', 'user_id', 'gender', 'birthdate', 'deathdate', 
                         'is_alive', 'birthplace', 'photo_url', 'notes'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['fullname', 'birthplace', 'photo_url', 'notes'])) {
                    $updateData[$field] = sanitize($data[$field]);
                } else {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        $success = $this->personModel->update($id, $updateData);

        if ($success) {
            $updatedPerson = $this->personModel->findById($id);
            jsonResponse([
                'message' => 'Person updated successfully',
                'person' => $updatedPerson
            ]);
        } else {
            jsonResponse(['error' => 'Failed to update person'], 500);
        }
    }

    /**
     * DELETE /api/persons/:id
     */
    public function delete(string $id): void {
        AuthMiddleware::requireAuth();

        if (!isValidUUID($id)) {
            jsonResponse(['error' => 'Invalid person ID'], 400);
        }

        $person = $this->personModel->findById($id);
        if (!$person) {
            jsonResponse(['error' => 'Person not found'], 404);
        }

        // Check ownership
        if (!AuthMiddleware::ownsResource($person['created_by'])) {
            jsonResponse(['error' => 'Forbidden'], 403);
        }

        $success = $this->personModel->delete($id);

        if ($success) {
            jsonResponse(['message' => 'Person deleted successfully']);
        } else {
            jsonResponse(['error' => 'Failed to delete person'], 500);
        }
    }

    /**
     * GET /api/persons/search
     */
    public function search(): void {
        AuthMiddleware::requireAuth();

        $query = $_GET['q'] ?? '';

        if (strlen($query) < 2) {
            jsonResponse(['error' => 'Search query must be at least 2 characters'], 400);
        }

        $persons = $this->personModel->search($query);

        jsonResponse([
            'persons' => $persons,
            'total' => count($persons)
        ]);
    }
}