<?php
session_start();
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle = 'App Configuration';

$pdo = getDB();

$allProjects = $pdo->query("
    SELECT p.id, p.name, p.slug, p.status 
    FROM projects p 
    WHERE p.deleted_at IS NULL 
    ORDER BY p.name
")->fetchAll();

$selectedIds = $pdo->query("SELECT project_id FROM app_config ORDER BY order_index")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedProjectIds = $_POST['project_ids'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        $pdo->exec("DELETE FROM app_config");
        
        $order = 0;
        foreach ($selectedProjectIds as $projectId) {
            $stmt = $pdo->prepare("INSERT INTO app_config (project_id, order_index) VALUES (?, ?)");
            $stmt->execute([$projectId, $order++]);
        }
        
        generateAppConfigJson();
        
        $pdo->commit();
        
        header('Location: app-config.php?success=1');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

function generateAppConfigJson() {
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT p.slug 
        FROM app_config ac 
        JOIN projects p ON ac.project_id = p.id 
        ORDER BY ac.order_index
    ");
    $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $output = ['projects' => $slugs];
    
    $jsonPath = JSON_PATH . 'app-config.json';
    if (file_exists($jsonPath)) {
        $backupName = 'app-config_' . date('Ymd_His') . '.json';
        copy($jsonPath, BACKUP_PATH . $backupName);
    }
    
    file_put_contents($jsonPath, json_encode($output, JSON_PRETTY_PRINT));
    logActivity('update_app_config', 'Updated app config');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Configure Projects for App (app.faydev.my.id)</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" id="configForm">
            <p class="text-muted">Drag and drop to reorder. Select projects to include in the app.</p>
            
            <div class="mb-3">
                <label class="form-label">Available Projects</label>
                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($allProjects as $project): ?>
                        <div class="form-check">
                            <input class="form-check-input project-checkbox" type="checkbox" 
                                   name="project_ids[]" value="<?= $project['id'] ?>" 
                                   id="proj_<?= $project['id'] ?>"
                                   <?= in_array($project['id'], $selectedIds) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="proj_<?= $project['id'] ?>">
                                <?= sanitize($project['name']) ?>
                                <span class="badge bg-<?= $project['status'] == 'Live' ? 'success' : 'secondary' ?>"><?= $project['status'] ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Configuration
            </button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Current Order</h5>
    </div>
    <div class="card-body">
        <?php if (empty($selectedIds)): ?>
            <p class="text-muted mb-0">No projects selected.</p>
        <?php else: ?>
            <ul class="list-group" id="sortableList">
                <?php foreach ($selectedIds as $index => $projectId): ?>
                    <?php 
                    $project = array_filter($allProjects, fn($p) => $p['id'] == $projectId);
                    $project = reset($project);
                    if ($project):
                    ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center sortable-item" data-id="<?= $projectId ?>">
                            <span><i class="fas fa-grip-lines handle me-2 text-muted"></i> <?= sanitize($project['name']) ?></span>
                            <span class="badge bg-secondary"><?= $index + 1 ?></span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#sortableList').sortable({
        handle: '.handle',
        placeholder: 'ui-state-highlight'
    });
    
    $('#configForm').on('submit', function(e) {
        const orderedIds = [];
        $('#sortableList .sortable-item').each(function() {
            orderedIds.push($(this).data('id'));
        });
        
        const checkboxes = $('.project-checkbox:checked');
        const selectedIds = [];
        checkboxes.each(function() {
            selectedIds.push($(this).val());
        });
        
        orderedIds.forEach((id, index) => {
            if (selectedIds.includes(String(id))) {
                selectedIds.splice(selectedIds.indexOf(String(id)), 1);
                selectedIds.splice(index, 0, String(id));
            }
        });
        
        if (selectedIds.length > 0) {
            const hiddenInput = $('<input type="hidden" name="project_ids[]">');
            selectedIds.forEach(id => {
                hiddenInput.clone().val(id).appendTo('#configForm');
            });
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
