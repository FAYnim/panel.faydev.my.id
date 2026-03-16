<?php
$pageTitle = 'Projects';
$activePage = 'projects';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="page-header page-header-between">
        <h1 class="page-title">Projects</h1>
        <a class="btn btn-primary" href="project-form.php">
            <i class="fas fa-plus"></i>
            <span>Add Project</span>
        </a>
    </div>

    <div class="panel table-panel">
        <div class="table-wrapper">
            <table class="data-table" aria-label="Projects table">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Demo Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="projectsTableBody">
                    <tr>
                        <td colspan="5" class="table-loading">Loading projects...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="empty-state" id="projectsEmptyState" hidden>
            <p>No projects found.</p>
        </div>
    </div>
</section>

<script src="src/js/projects.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
