<?php
$socialId = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $socialId > 0;

$pageTitle = $isEdit ? 'Edit Social Link' : 'Add Social Link';
$activePage = 'social';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="page-header page-header-between">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <a class="btn btn-secondary" href="social.php">Back to Social Links</a>
    </div>

    <div class="panel form-panel">
        <form id="socialForm" class="dashboard-form" novalidate>
            <input type="hidden" name="id" id="socialId" value="<?= $isEdit ? (int) $socialId : '' ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required maxlength="50" placeholder="Instagram">
                </div>

                <div class="form-group">
                    <label for="icon">Icon Class</label>
                    <div class="input-with-preview">
                        <input type="text" id="icon" name="icon" required maxlength="120" placeholder="fab fa-instagram">
                        <span class="icon-preview" id="iconPreview"><i id="iconPreviewIcon"></i></span>
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="url">URL</label>
                    <input type="url" id="url" name="url" required placeholder="https://instagram.com/faydev">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="saveSocialBtn">
                    <i class="fas fa-save"></i>
                    <span>Save Social Link</span>
                </button>
            </div>
        </form>
    </div>
</section>

<script>
(() => {
    const socialId = Number(<?= (int) $socialId ?>);
    const isEdit = socialId > 0;
    const form = document.getElementById('socialForm');
    const saveBtn = document.getElementById('saveSocialBtn');
    const iconInput = document.getElementById('icon');
    const iconPreviewIcon = document.getElementById('iconPreviewIcon');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function updateIconPreview() {
        const classes = iconInput.value.trim();
        iconPreviewIcon.className = classes;
    }

    iconInput.addEventListener('input', updateIconPreview);

    async function loadSocialLink() {
        if (!isEdit) {
            return;
        }

        try {
            const response = await fetch(`/api/social.php?id=${socialId}`, {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Failed to load social link');
            }

            const item = result.data;
            document.getElementById('name').value = item.name || '';
            document.getElementById('icon').value = item.icon || '';
            document.getElementById('url').value = item.url || '';
            updateIconPreview();
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load social link', 'error');
            }
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append('csrf_token', csrfToken);

        const action = isEdit ? 'update' : 'create';
        saveBtn.disabled = true;

        try {
            const response = await fetch(`/api/social.php?action=${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to save social link');
            }

            if (typeof window.showToast === 'function') {
                window.showToast(`Social link ${isEdit ? 'updated' : 'created'} successfully`, 'success');
            }

            window.setTimeout(() => {
                window.location.href = 'social.php';
            }, 500);
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to save social link', 'error');
            }
        } finally {
            saveBtn.disabled = false;
        }
    });

    loadSocialLink();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
