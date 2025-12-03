<?php
$pageTitle = 'Add Person';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> Add New Person</h4>
            </div>
            <div class="card-body">
                <form id="createPersonForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select gender...</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="birthdate" class="form-label">Birth Date</label>
                            <input type="date" class="form-control" id="birthdate" name="birthdate">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="birthplace" class="form-label">Birth Place</label>
                            <input type="text" class="form-control" id="birthplace" name="birthplace" placeholder="City, Country">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="is_alive" class="form-label">Status</label>
                            <select class="form-select" id="is_alive" name="is_alive">
                                <option value="1">Alive</option>
                                <option value="0">Deceased</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="deathDateRow" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="deathdate" class="form-label">Death Date</label>
                            <input type="date" class="form-control" id="deathdate" name="deathdate">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo_url" class="form-label">Photo URL</label>
                        <input type="url" class="form-control" id="photo_url" name="photo_url" placeholder="https://example.com/photo.jpg">
                        <small class="text-muted">Enter a URL to a photo of this person</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Any additional information about this person..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> After creating the person, you can add them to families and create relationships.
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Person
                        </button>
                        <a href="persons.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle death date field
document.getElementById('is_alive').addEventListener('change', function() {
    const deathDateRow = document.getElementById('deathDateRow');
    if (this.value === '0') {
        deathDateRow.style.display = '';
    } else {
        deathDateRow.style.display = 'none';
        document.getElementById('deathdate').value = '';
    }
});

// Form submission
document.getElementById('createPersonForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        fullname: document.getElementById('fullname').value,
        gender: document.getElementById('gender').value || null,
        birthdate: document.getElementById('birthdate').value || null,
        birthplace: document.getElementById('birthplace').value || null,
        is_alive: document.getElementById('is_alive').value === '1',
        deathdate: document.getElementById('deathdate').value || null,
        photo_url: document.getElementById('photo_url').value || null,
        notes: document.getElementById('notes').value || null
    };
    
    try {
        const result = await apiCall('/persons', 'POST', formData);
        
        if (result.person) {
            showToast('Person created successfully!', 'success');
            setTimeout(() => {
                window.location.href = `persons/show.php?id=${result.person.person_id}`;
            }, 1000);
        }
    } catch (error) {
        showToast(error.message || 'Failed to create person', 'error');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>