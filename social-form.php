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

<script src="src/js/social-form.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
