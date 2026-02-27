<?php
session_start();
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle = 'Projects';

$showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == 1;

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$where = [];
$params = [];

if ($showDeleted) {
    $where[] = "deleted_at IS NOT NULL";
} else {
    $where[] = "deleted_at IS NULL";
}

if ($search) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($category) {
    $where[] = "id IN (SELECT project_id FROM project_categories WHERE category_id = ?)";
    $params[] = $category;
}

$whereClause = implode(' AND ', $where);

$countSql = "SELECT COUNT(*) FROM projects" . ($whereClause ? " WHERE $whereClause" : "");
$total = $pdo->prepare($countSql);
$total->execute($params);
$totalProjects = $total->fetchColumn();

$totalPages = ceil($totalProjects / $perPage);
$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM projects" . ($whereClause ? " WHERE $whereClause" : "") . " ORDER BY created_at DESC LIMIT $offset, $perPage";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

foreach ($projects as &$project) {
    $catStmt = $pdo->prepare("
        SELECT c.name FROM categories c 
        JOIN project_categories pc ON c.id = pc.category_id 
        WHERE pc.project_id = ?
    ");
    $catStmt->execute([$project['id']]);
    $project['categories'] = $catStmt->fetchAll(PDO::FETCH_COLUMN);
}
unset($project);

require_once __DIR__ . '/includes/header.php';
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search projects..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="Draft" <?= $status == 'Draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="Development" <?= $status == 'Development' ? 'selected' : '' ?>>Development</option>
                    <option value="Live" <?= $status == 'Live' ? 'selected' : '' ?>>Live</option>
                    <option value="Archived" <?= $status == 'Archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="show_deleted" value="1" id="showDeleted" <?= $showDeleted ? 'checked' : '' ?>>
                    <label class="form-check-label" for="showDeleted">Show Deleted</label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i></button>
                <a href="project-form.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Project</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Categories</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr>                        <td colspan="5" class="text-center text-muted py-4">No projects found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><strong><?= sanitize($project['name']) ?></strong></td>
                                <td><code><?= sanitize($project['slug']) ?></code></td>
                                <td>
                                    <?php if (!empty($project['categories'])): ?>
                                        <?php foreach ($project['categories'] as $cat): ?>
                                            <span class="badge bg-secondary me-1"><?= sanitize($cat) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $project['status'] == 'Live' ? 'success' : ($project['status'] == 'Development' ? 'warning' : ($project['status'] == 'Draft' ? 'info' : 'secondary')) ?>">
                                        <?= $project['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($showDeleted): ?>
                                        <button class="btn btn-success btn-action" onclick="restoreProject(<?= $project['id'] ?>)" title="Restore"><i class="fas fa-trash-restore"></i></button>
                                        <button class="btn btn-danger btn-action" onclick="permanentDelete(<?= $project['id'] ?>)" title="Permanent Delete"><i class="fas fa-times"></i></button>
                                    <?php else: ?>
                                        <a href="project-form.php?id=<?= $project['id'] ?>" class="btn btn-primary btn-action" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-danger btn-action" onclick="deleteProject(<?= $project['id'] ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination mb-0 justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&category=<?= $category ?>&show_deleted=<?= $showDeleted ? 1 : 0 ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteProject(id) {
    if (confirm('Are you sure you want to delete this project?')) {
        $.post('api/project-delete.php', { id: id }, function(res) {
            if (res.success) {
                showToast('Success', res.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error', res.message, 'danger');
            }
        }, 'json');
    }
}

function restoreProject(id) {
    if (confirm('Are you sure you want to restore this project?')) {
        $.post('api/project-restore.php', { id: id }, function(res) {
            if (res.success) {
                showToast('Success', res.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error', res.message, 'danger');
            }
        }, 'json');
    }
}

function permanentDelete(id) {
    if (confirm('WARNING: This will permanently delete the project. This action cannot be undone!')) {
        $.post('api/project-permanent-delete.php', { id: id }, function(res) {
            if (res.success) {
                showToast('Success', res.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error', res.message, 'danger');
            }
        }, 'json');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
