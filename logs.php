<?php
session_start();
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle = 'Activity Logs';

$page = (int)($_GET['page'] ?? 1);
$perPage = 20;

$totalLogs = $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);
$offset = ($page - 1) * $perPage;

$logs = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT $offset, $perPage")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No activity yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?= getBadgeColor($log['action']) ?>">
                                        <?= sanitize($log['action']) ?>
                                    </span>
                                </td>
                                <td><?= sanitize($log['description']) ?></td>
                                <td><small class="text-muted"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></small></td>
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
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php 
function getBadgeColor($action) {
    $colors = [
        'login' => 'primary',
        'logout' => 'secondary',
        'create_project' => 'success',
        'update_project' => 'info',
        'delete_project' => 'danger',
        'restore_project' => 'warning',
        'permanent_delete' => 'danger',
        'update_app_config' => 'primary',
        'update_portfolio_config' => 'primary',
        'json_generated' => 'success',
        'create_category' => 'success',
        'delete_category' => 'danger'
    ];
    return $colors[$action] ?? 'secondary';
}

require_once __DIR__ . '/includes/footer.php'; 
?>
