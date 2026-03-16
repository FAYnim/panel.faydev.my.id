<?php
$projectId = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $projectId > 0;

$pageTitle = $isEdit ? 'Edit Project' : 'Add Project';
$activePage = 'projects';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="page-header page-header-between">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <a class="btn btn-secondary" href="projects.php">Back to Projects</a>
    </div>

    <div class="panel form-panel">
        <form id="projectForm" class="dashboard-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" id="projectId" value="<?= $isEdit ? (int) $projectId : '' ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required maxlength="150">
                </div>

                <div class="form-group">
                    <label for="project_date">Project Date</label>
                    <input type="date" id="project_date" name="project_date" required>
                </div>

                <div class="form-group form-group-full">
                    <label for="demo_link">Demo Link</label>
                    <input type="url" id="demo_link" name="demo_link" placeholder="https://example.com">
                </div>

                <div class="form-group form-group-full">
                    <label for="thumbnail">Thumbnail</label>
                    <div class="dropzone" id="thumbnailDropzone">
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp" <?= $isEdit ? '' : 'required' ?>>
                        <p class="dropzone-text">Drop image here or click to browse</p>
                    </div>
                    <div class="image-preview-wrap" id="thumbnailPreviewWrap" hidden>
                        <img src="" alt="Thumbnail preview" id="thumbnailPreview" class="image-preview">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="saveProjectBtn">
                    <i class="fas fa-save"></i>
                    <span>Save Project</span>
                </button>
            </div>
        </form>
    </div>
</section>

<script src="assets/js/project-form.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
