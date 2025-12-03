<?php
$pageTitle = 'Persons';
require_once __DIR__ . '/../layouts/header.php';

use App\Models\Person;

$personModel = new Person();
$persons = $personModel->findAll(100, 0);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-person"></i> Persons</h1>
        <p class="text-muted">Manage all individuals in your family trees</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="persons/create.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add New Person
        </a>
    </div>
</div>

<!-- Search -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search by name...">
        </div>
    </div>
    <div class="col-md-6 text-end">
        <span class="text-muted">Total: <strong><?php echo count($persons); ?></strong> persons</span>
    </div>
</div>

<!-- Persons Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($persons)): ?>
            <div class="text-center py-5">
                <i class="bi bi-person" style="font-size: 4rem; opacity: 0.2;"></i>
                <h4 class="mt-3">No Persons Found</h4>
                <p class="text-muted">Start by adding individuals to your family trees.</p>
                <a href="persons/create.php" class="btn btn-primary mt-3">
                    <i class="bi bi-person-plus"></i> Add Person
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="personsTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Birth Date</th>
                            <th>Age</th>
                            <th>Status</th>
                            <th>Birthplace</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($persons as $person): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($person['fullname']); ?></strong>
                                <?php if ($person['linked_username']): ?>
                                    <br><small class="text-muted">@<?php echo htmlspecialchars($person['linked_username']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($person['gender']): ?>
                                    <i class="bi bi-<?php echo $person['gender'] === 'male' ? 'gender-male text-primary' : ($person['gender'] === 'female' ? 'gender-female text-danger' : 'circle'); ?>"></i>
                                    <?php echo ucfirst($person['gender']); ?>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($person['birthdate']); ?></td>
                            <td>
                                <?php 
                                $age = calculateAge($person['birthdate'], $person['deathdate']);
                                echo $age !== null ? $age . ' years' : 'N/A';
                                ?>
                            </td>
                            <td>
                                <?php if ($person['is_alive']): ?>
                                    <span class="badge bg-success">Alive</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Deceased</span>
                                    <?php if ($person['deathdate']): ?>
                                        <br><small><?php echo formatDate($person['deathdate']); ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($person['birthplace'] ?? 'N/A'); ?></td>
                            <td class="table-actions">
                                <a href="persons/show.php?id=<?php echo $person['person_id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
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

<script>
// Simple search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const table = document.getElementById('personsTable');
    const rows = table?.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr');
    
    if (rows) {
        Array.from(rows).forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            if (name.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>