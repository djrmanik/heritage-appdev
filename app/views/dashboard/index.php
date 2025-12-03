<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';

use App\Models\Family;
use App\Models\Person;
use App\Models\Relationship;

$familyModel = new Family();
$personModel = new Person();
$relationshipModel = new Relationship();

$currentUser = getCurrentUser();

// Get statistics
$totalFamilies = $familyModel->count();
$totalPersons = $personModel->count();
$totalRelationships = $relationshipModel->count();
$myFamilies = $familyModel->findByCreator($currentUser['user_id']);
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Families</h6>
                        <h2 class="mb-0"><?php echo $totalFamilies; ?></h2>
                    </div>
                    <i class="bi bi-people stat-icon text-success"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Persons</h6>
                        <h2 class="mb-0"><?php echo $totalPersons; ?></h2>
                    </div>
                    <i class="bi bi-person stat-icon text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Relationships</h6>
                        <h2 class="mb-0"><?php echo $totalRelationships; ?></h2>
                    </div>
                    <i class="bi bi-heart stat-icon text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">My Families</h6>
                        <h2 class="mb-0"><?php echo count($myFamilies); ?></h2>
                    </div>
                    <i class="bi bi-person-check stat-icon text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="families/create.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle"></i> Create New Family
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="persons/create.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-person-plus"></i> Add New Person
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="families.php" class="btn btn-outline-info w-100">
                            <i class="bi bi-eye"></i> View All Families
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Families -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-folder"></i> My Families</h5>
            </div>
            <div class="card-body">
                <?php if (empty($myFamilies)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">You haven't created any families yet.</p>
                        <a href="families/create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Your First Family
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Family Name</th>
                                    <th>Members</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myFamilies as $family): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($family['family_name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $family['member_count']; ?> members</span>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($family['description'] ?? '', 0, 50)); ?><?php echo strlen($family['description'] ?? '') > 50 ? '...' : ''; ?></td>
                                    <td><?php echo formatDate($family['created_at']); ?></td>
                                    <td class="table-actions">
                                        <a href="families/show.php?id=<?php echo $family['family_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="tree.php?family=<?php echo $family['family_id']; ?>" class="btn btn-sm btn-success">
                                            <i class="bi bi-diagram-3"></i> Tree
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>