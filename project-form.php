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

<script>
(() => {
    const projectId = Number(<?= (int) $projectId ?>);
    const isEdit = projectId > 0;
    const form = document.getElementById('projectForm');
    const saveBtn = document.getElementById('saveProjectBtn');
    const fileInput = document.getElementById('thumbnail');
    const dropzone = document.getElementById('thumbnailDropzone');
    const previewWrap = document.getElementById('thumbnailPreviewWrap');
    const previewImage = document.getElementById('thumbnailPreview');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function showPreview(src) {
        previewImage.src = src;
        previewWrap.hidden = false;
    }

    function handleFilePreview(file) {
        if (!file || !file.type.startsWith('image/')) {
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            if (typeof reader.result === 'string') {
                showPreview(reader.result);
            }
        };
        reader.readAsDataURL(file);
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
            handleFilePreview(fileInput.files[0]);
        }
    });

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('is-dragover');
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragover');

        const files = event.dataTransfer?.files;
        if (!files || files.length === 0) {
            return;
        }

        fileInput.files = files;
        handleFilePreview(files[0]);
    });

    async function loadProject() {
        if (!isEdit) {
            return;
        }

        try {
            const response = await fetch(`/api/projects.php?id=${projectId}`, {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Failed to load project');
            }

            const project = result.data;
            document.getElementById('title').value = project.title || '';
            document.getElementById('project_date').value = project.project_date || '';
            document.getElementById('demo_link').value = project.demo_link || '';

            if (project.thumbnail) {
                showPreview(`/${String(project.thumbnail).replace(/^\/+/, '')}`);
            }
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load project', 'error');
            }
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append('csrf_token', csrfToken);

        if (!isEdit && (!fileInput.files || fileInput.files.length === 0)) {
            if (typeof window.showToast === 'function') {
                window.showToast('Thumbnail is required', 'error');
            }
            return;
        }

        const action = isEdit ? 'update' : 'create';
        saveBtn.disabled = true;

        try {
            const response = await fetch(`/api/projects.php?action=${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to save project');
            }

            if (typeof window.showToast === 'function') {
                window.showToast(`Project ${isEdit ? 'updated' : 'created'} successfully`, 'success');
            }

            window.setTimeout(() => {
                window.location.href = 'projects.php';
            }, 500);
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to save project', 'error');
            }
        } finally {
            saveBtn.disabled = false;
        }
    });

    loadProject();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
