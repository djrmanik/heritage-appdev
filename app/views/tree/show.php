<?php
$pageTitle = 'Family Tree';
require_once __DIR__ . '/../layouts/header.php';

use App\Models\Family;

$familyId = $_GET['family'] ?? null;

if (!$familyId || !isValidUUID($familyId)) {
    header('Location: families.php');
    exit;
}

$familyModel = new Family();
$family = $familyModel->findById($familyId);

if (!$family) {
    header('Location: families.php');
    exit;
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-diagram-3"></i> <?php echo htmlspecialchars($family['family_name']); ?> - Family Tree</h1>
        <p class="text-muted">Interactive family tree visualization</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="families/show.php?id=<?php echo $familyId; ?>" class="btn btn-primary">
            <i class="bi bi-info-circle"></i> Family Details
        </a>
        <a href="families.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Tree Controls -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="resetZoom()">
                            <i class="bi bi-arrow-clockwise"></i> Reset View
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="expandAll()">
                            <i class="bi bi-arrows-fullscreen"></i> Expand All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="collapseAll()">
                            <i class="bi bi-arrows-collapse"></i> Collapse All
                        </button>
                    </div>
                    <div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary active" onclick="setLayout('vertical')" id="btnVertical">
                                <i class="bi bi-arrow-down"></i> Vertical
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="setLayout('horizontal')" id="btnHorizontal">
                                <i class="bi bi-arrow-right"></i> Horizontal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tree Container -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div id="tree-container"></div>
            </div>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-info-circle"></i> Legend</h6>
                <div class="row">
                    <div class="col-md-3">
                        <i class="bi bi-circle-fill text-primary"></i> Male
                    </div>
                    <div class="col-md-3">
                        <i class="bi bi-circle-fill text-danger"></i> Female
                    </div>
                    <div class="col-md-3">
                        <i class="bi bi-circle"></i> Gender Not Specified
                    </div>
                    <div class="col-md-3">
                        <i class="bi bi-dash-lg"></i> Click nodes to expand/collapse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Person Details Modal -->
<div class="modal fade" id="personModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="personModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="personModalBody">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="personModalLink" class="btn btn-primary" target="_blank">View Full Profile</a>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/tree.js"></script>
<script>
    const familyId = '<?php echo $familyId; ?>';
    
    // Load tree data and render
    async function loadTree() {
        try {
            const result = await apiCall(`/tree/${familyId}/simple`);
            renderTree(result);
        } catch (error) {
            console.error('Failed to load tree:', error);
            showToast('Failed to load family tree', 'error');
        }
    }
    
    // Load tree on page load
    document.addEventListener('DOMContentLoaded', loadTree);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>