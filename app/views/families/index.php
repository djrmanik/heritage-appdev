<?php
$pageTitle = 'Families';
require_once __DIR__ . '/../layouts/header.php';

use App\Models\Family;

$familyModel = new Family();
$families = $familyModel->findAll(50, 0);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-people"></i> Families</h1>
        <p class="text-muted">Manage and explore family trees</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="families/create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Family
        </a>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search families...">
        </div>
    </div>
    <div class="col-md-6 text-end">
        <span class="text-muted">Total: <strong><?php echo count($families); ?></strong> families</span>
    </div>
</div>

<!-- Families Grid -->
<div class="row g-4" id="familiesGrid">
    <?php if (empty($families)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.2;"></i>
                    <h4 class="mt-3">No Families Found</h4>
                    <p class="text-muted">Start by creating your first family tree.</p>
                    <a href="families/create.php" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle"></i> Create Family
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($families as $family): ?>
        <div class="col-md-6 col-lg-4 family-item" data-name="<?php echo strtolower($family['family_name']); ?>">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill"></i>
                        <?php echo htmlspecialchars($family['family_name']); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted">
                        <?php echo htmlspecialchars(substr($family['description'] ?? 'No description', 0, 100)); ?>
                        <?php echo strlen($family['description'] ?? '') > 100 ? '...' : ''; ?>
                    </p>
                    
                    <div class="mb-3">
                        <span class="badge bg-info">
                            <i class="bi bi-person"></i> <?php echo $family['member_count']; ?> members
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            By: <?php echo htmlspecialchars($family['creator_name'] ?? 'Unknown'); ?>
                        </small>
                        <small class="text-muted">
                            <?php echo formatDate($family['created_at']); ?>
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex gap-2">
                        <a href="families/show.php?id=<?php echo $family['family_id']; ?>" class="btn btn-sm btn-primary flex-fill">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <a href="tree.php?family=<?php echo $family['family_id']; ?>" class="btn btn-sm btn-success flex-fill">
                            <i class="bi bi-diagram-3"></i> Tree
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const familyItems = document.querySelectorAll('.family-item');
    
    familyItems.forEach(item => {
        const familyName = item.dataset.name;
        if (familyName.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>