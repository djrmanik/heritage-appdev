<?php
$pageTitle = 'Create Family';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Family</h4>
            </div>
            <div class="card-body">
                <form id="createFamilyForm">
                    <div class="mb-3">
                        <label for="family_name" class="form-label">Family Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="family_name" name="family_name" required>
                        <small class="text-muted">Example: Smith Family, Johnson Lineage</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe this family's history, origin, or any notable information..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> After creating the family, you can add members and build relationships.
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Family
                        </button>
                        <a href="families.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('createFamilyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        family_name: document.getElementById('family_name').value,
        description: document.getElementById('description').value
    };
    
    try {
        const result = await apiCall('/families', 'POST', formData);
        
        if (result.family) {
            showToast('Family created successfully!', 'success');
            setTimeout(() => {
                window.location.href = `families/show.php?id=${result.family.family_id}`;
            }, 1000);
        }
    } catch (error) {
        showToast(error.message || 'Failed to create family', 'error');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>