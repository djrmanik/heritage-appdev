<?php
$pageTitle = 'Family Details';
require_once __DIR__ . '/../layouts/header.php';

use App\Models\Family;
use App\Models\Person;
use App\Models\Relationship;

$familyId = $_GET['id'] ?? null;

if (!$familyId || !isValidUUID($familyId)) {
    header('Location: families.php');
    exit;
}

$familyModel = new Family();
$personModel = new Person();
$relationshipModel = new Relationship();

$family = $familyModel->findById($familyId);

if (!$family) {
    header('Location: families.php');
    exit;
}

$members = $familyModel->getMembers($familyId);
$relationships = $relationshipModel->findByFamily($familyId);
$allPersons = $personModel->findAll(500, 0);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-people-fill"></i> <?php echo htmlspecialchars($family['family_name']); ?></h1>
        <p class="text-muted mb-0">Created by <?php echo htmlspecialchars($family['creator_name'] ?? 'Unknown'); ?> on <?php echo formatDate($family['created_at']); ?></p>
    </div>
    <div class="col-md-4 text-end">
        <a href="tree.php?family=<?php echo $familyId; ?>" class="btn btn-success">
            <i class="bi bi-diagram-3"></i> View Family Tree
        </a>
        <a href="families.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Family Info -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Family Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Family Name:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($family['family_name']); ?></dd>
                            
                            <dt class="col-sm-4">Total Members:</dt>
                            <dd class="col-sm-8"><span class="badge bg-primary"><?php echo count($members); ?></span></dd>
                            
                            <dt class="col-sm-4">Relationships:</dt>
                            <dd class="col-sm-8"><span class="badge bg-info"><?php echo count($relationships); ?></span></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Created By:</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($family['creator_name'] ?? 'Unknown'); ?></dd>
                            
                            <dt class="col-sm-4">Created:</dt>
                            <dd class="col-sm-8"><?php echo formatDate($family['created_at']); ?></dd>
                            
                            <dt class="col-sm-4">Last Updated:</dt>
                            <dd class="col-sm-8"><?php echo formatDate($family['updated_at']); ?></dd>
                        </dl>
                    </div>
                </div>
                
                <?php if (!empty($family['description'])): ?>
                <hr>
                <h6>Description:</h6>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($family['description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Members Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person"></i> Family Members</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="bi bi-person-plus"></i> Add Member
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($members)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">No members yet. Add your first family member!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Birth Date</th>
                                    <th>Status</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($member['fullname']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($member['gender']): ?>
                                            <i class="bi bi-<?php echo $member['gender'] === 'male' ? 'gender-male text-primary' : 'gender-female text-danger'; ?>"></i>
                                            <?php echo ucfirst($member['gender']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($member['birthdate']); ?></td>
                                    <td>
                                        <?php if ($member['is_alive']): ?>
                                            <span class="badge bg-success">Alive</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Deceased</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $member['role_in_family'])); ?></span>
                                    </td>
                                    <td class="table-actions">
                                        <a href="persons/show.php?id=<?php echo $member['person_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="removeMember('<?php echo $member['person_id']; ?>', '<?php echo htmlspecialchars($member['fullname']); ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Relationships Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-heart"></i> Family Relationships</h5>
            </div>
            <div class="card-body">
                <?php if (empty($relationships)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-heart" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">No relationships defined yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Member 1</th>
                                    <th>Relationship</th>
                                    <th>Member 2</th>
                                    <th>Started</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($relationships as $rel): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rel['member_1_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $rel['relation_type'] === 'parent' ? 'primary' : 'danger'; ?>">
                                            <?php echo ucfirst($rel['relation_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($rel['member_2_name']); ?></td>
                                    <td><?php echo formatDate($rel['started_at']); ?></td>
                                    <td><?php echo htmlspecialchars($rel['note'] ?? '-'); ?></td>
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

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add Member to Family</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMemberForm">
                    <div class="mb-3">
                        <label for="person_id" class="form-label">Select Person</label>
                        <select class="form-select" id="person_id" name="person_id" required>
                            <option value="">Choose a person...</option>
                            <?php foreach ($allPersons as $person): ?>
                                <option value="<?php echo $person['person_id']; ?>">
                                    <?php echo htmlspecialchars($person['fullname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role_in_family" class="form-label">Role in Family</label>
                        <select class="form-select" id="role_in_family" name="role_in_family">
                            <option value="bloodline">Bloodline</option>
                            <option value="married_in">Married In</option>
                            <option value="honorary">Honorary</option>
                            <option value="founder">Founder</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note" class="form-label">Note (Optional)</label>
                        <input type="text" class="form-control" id="note" name="note" placeholder="Additional information">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addMember()">Add Member</button>
            </div>
        </div>
    </div>
</div>

<script>
const familyId = '<?php echo $familyId; ?>';

async function addMember() {
    const personId = document.getElementById('person_id').value;
    const role = document.getElementById('role_in_family').value;
    const note = document.getElementById('note').value;
    
    if (!personId) {
        showToast('Please select a person', 'error');
        return;
    }
    
    try {
        await apiCall(`/families/${familyId}/members`, 'POST', {
            person_id: personId,
            role_in_family: role,
            note: note
        });
        
        showToast('Member added successfully!', 'success');
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        showToast(error.message || 'Failed to add member', 'error');
    }
}

async function removeMember(personId, personName) {
    if (!confirm(`Remove ${personName} from this family?`)) {
        return;
    }
    
    try {
        await apiCall(`/families/${familyId}/members/${personId}`, 'DELETE');
        showToast('Member removed successfully!', 'success');
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        showToast(error.message || 'Failed to remove member', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>