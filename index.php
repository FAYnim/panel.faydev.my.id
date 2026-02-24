<?php
session_start();
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle = 'Dashboard';

require_once __DIR__ . '/includes/header.php';

$pdo = getDB();

$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$appConfigCount = $pdo->query("SELECT COUNT(*) FROM app_config")->fetchColumn();
$portfolioConfigCount = $pdo->query("SELECT COUNT(*) FROM portfolio_config")->fetchColumn();

$recentProjects = $pdo->query("SELECT * FROM projects WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentLogs = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-primary mb-2"><i class="fas fa-folder fa-2x"></i></div>
                <h3 class="mb-0"><?= $totalProjects ?></h3>
                <small class="text-muted">Total Projects</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-success mb-2"><i class="fas fa-tags fa-2x"></i></div>
                <h3 class="mb-0"><?= $totalCategories ?></h3>
                <small class="text-muted">Categories</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-info mb-2"><i class="fas fa-mobile-alt fa-2x"></i></div>
                <h3 class="mb-0"><?= $appConfigCount ?></h3>
                <small class="text-muted">App Projects</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="text-warning mb-2"><i class="fas fa-briefcase fa-2x"></i></div>
                <h3 class="mb-0"><?= $portfolioConfigCount ?></h3>
                <small class="text-muted">Portfolio Projects</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Projects</h5>
                <a href="projects.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentProjects)): ?>
                    <p class="text-muted p-3 mb-0">No projects yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentProjects as $project): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= sanitize($project['name']) ?></strong>
                                        <br><small class="text-muted"><?= $project['slug'] ?></small>
                                    </div>
                                    <span class="badge bg-<?= $project['status'] == 'Live' ? 'success' : ($project['status'] == 'Development' ? 'warning' : 'secondary') ?>">
                                        <?= $project['status'] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Activity</h5>
                <a href="logs.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentLogs)): ?>
                    <p class="text-muted p-3 mb-0">No activity yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <small><?= sanitize($log['description']) ?></small>
                                    <small class="text-muted"><?= date('M d, H:i', strtotime($log['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
