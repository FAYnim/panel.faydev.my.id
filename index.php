<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>

    <div class="kpi-grid">
        <article class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></div>
            <div class="kpi-content">
                <p class="kpi-label">Total Projects</p>
                <p class="kpi-value" id="projectsCount">0</p>
            </div>
        </article>

        <article class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-share-alt" aria-hidden="true"></i></div>
            <div class="kpi-content">
                <p class="kpi-label">Total Social Links</p>
                <p class="kpi-value" id="socialCount">0</p>
            </div>
        </article>
    </div>

    <section class="panel quick-actions-panel">
        <h2 class="panel-title">Quick Actions</h2>
        <div class="quick-actions-grid">
            <a class="btn btn-primary" href="project-form.php">
                <i class="fas fa-plus"></i>
                <span>Add Project</span>
            </a>
            <a class="btn btn-secondary" href="social.php">
                <i class="fas fa-share-alt"></i>
                <span>Manage Social Links</span>
            </a>
        </div>
    </section>
</section>

<script src="assets/js/index.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
