<?php
$pageTitle = 'Person Details';
require_once __DIR__ . '/../layouts/header.php';

use App\Models\Person;

$personId = $_GET['id'] ?? null;

if (!$personId || !isValidUUID($personId)) {
    header('Location: persons.php');
    exit;
}

$personModel = new Person();
$person = $personModel->findById($personId);

if (!$person) {
    header('Location: persons.php');
    exit;
}

$families = $personModel->getFamilies($personId);
$parents = $personModel->getParents($personId);
$children = $personModel->getChildren($personId);
$spouses = $personModel->getSpouses($personId);

$age = calculateAge($person['birthdate'], $person['deathdate']);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($person['fullname']); ?></h1>
        <?php if ($person['linked_username']): ?>
            <p class="text-muted">Linked to user: @<?php echo htmlspecialchars($person['linked_username']); ?></p>
        <?php endif; ?>
    </div>
    <div class="col-md-4 text-end">
        <a href="persons.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Person Information -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card person-card">
            <?php if ($person['gender']): ?>
                <span class="gender-badge badge bg-<?php echo $person['gender'] === 'male' ? 'primary' : 'danger'; ?>">
                    <i class="bi bi-gender-<?php echo $person['gender'] === 'male' ? 'male' : 'female'; ?>"></i>
                    <?php echo ucfirst($person['gender']); ?>
                </span>
            <?php endif; ?>
            
            <div class="card-body text-center">
                <?php if ($person['photo_url']): ?>
                    <img src="<?php echo htmlspecialchars($person['photo_url']); ?>" 
                         alt="<?php echo htmlspecialchars($person['fullname']); ?>" 
                         class="person-avatar mb-3">
                <?php else: ?>
                    <div class="person-avatar mb-3 bg-light d-flex align-items-center justify-content-center mx-auto">
                        <i class="bi bi-person" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                <?php endif; ?>
                
                <h4><?php echo htmlspecialchars($person['fullname']); ?></h4>
                
                <?php if ($person['is_alive']): ?>
                    <span class="badge bg-success mb-2">Alive</span>
                <?php else: ?>
                    <span class="badge bg-secondary mb-2">Deceased</span>
                <?php endif; ?>
                
                <?php if ($age !== null): ?>
                    <p class="text-muted mb-0"><?php echo $age; ?> years old</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Personal Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Full Name:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($person['fullname']); ?></dd>
                    
                    <dt class="col-sm-3">Gender:</dt>
                    <dd class="col-sm-9"><?php echo $person['gender'] ? ucfirst($person['gender']) : 'Not specified'; ?></dd>
                    
                    <dt class="col-sm-3">Birth Date:</dt>
                    <dd class="col-sm-9"><?php echo formatDate($person['birthdate']); ?></dd>
                    
                    <dt class="col-sm-3">Birth Place:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($person['birthplace'] ?? 'Not specified'); ?></dd>
                    
                    <?php if (!$person['is_alive'] && $person['deathdate']): ?>
                    <dt class="col-sm-3">Death Date:</dt>
                    <dd class="col-sm-9"><?php echo formatDate($person['deathdate']); ?></dd>
                    <?php endif; ?>
                    
                    <dt class="col-sm-3">Age:</dt>
                    <dd class="col-sm-9">
                        <?php if ($age !== null): ?>
                            <?php echo $age; ?> years
                            <?php if (!$person['is_alive']): ?>
                                (at death)
                            <?php endif; ?>
                        <?php else: ?>
                            Not available
                        <?php endif; ?>
                    </dd>
                </dl>
                
                <?php if ($person['notes']): ?>
                <hr>
                <h6>Notes:</h6>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($person['notes'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Families -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Families (<?php echo count($families); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($families)): ?>
                    <p class="text-muted mb-0">Not a member of any family yet.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($families as $family): ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6><?php echo htmlspecialchars($family['family_name']); ?></h6>
                                    <p class="text-muted mb-2">
                                        Role: <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $family['role_in_family'])); ?></span>
                                    </p>
                                    <?php if ($family['family_note']): ?>
                                        <p class="small mb-2"><?php echo htmlspecialchars($family['family_note']); ?></p>
                                    <?php endif; ?>
                                    <a href="families/show.php?id=<?php echo $family['family_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Family
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Relationships -->
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-arrow-up"></i> Parents (<?php echo count($parents); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($parents)): ?>
                    <p class="text-muted small mb-0">No parents recorded</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($parents as $parent): ?>
                        <li class="mb-2">
                            <a href="persons/show.php?id=<?php echo $parent['person_id']; ?>">
                                <?php echo htmlspecialchars($parent['fullname']); ?>
                            </a>
                            <?php if ($parent['gender']): ?>
                                <i class="bi bi-gender-<?php echo $parent['gender'] === 'male' ? 'male text-primary' : 'female text-danger'; ?>"></i>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-heart"></i> Spouses (<?php echo count($spouses); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($spouses)): ?>
                    <p class="text-muted small mb-0">No spouses recorded</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($spouses as $spouse): ?>
                        <li class="mb-2">
                            <a href="persons/show.php?id=<?php echo $spouse['person_id']; ?>">
                                <?php echo htmlspecialchars($spouse['fullname']); ?>
                            </a>
                            <?php if ($spouse['started_at']): ?>
                                <br><small class="text-muted">Since: <?php echo formatDate($spouse['started_at']); ?></small>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-arrow-down"></i> Children (<?php echo count($children); ?>)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($children)): ?>
                    <p class="text-muted small mb-0">No children recorded</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($children as $child): ?>
                        <li class="mb-2">
                            <a href="persons/show.php?id=<?php echo $child['person_id']; ?>">
                                <?php echo htmlspecialchars($child['fullname']); ?>
                            </a>
                            <?php if ($child['birthdate']): ?>
                                <br><small class="text-muted">Born: <?php echo formatDate($child['birthdate']); ?></small>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>