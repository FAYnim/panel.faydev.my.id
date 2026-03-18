<?php
$certificateId = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $certificateId > 0;

$pageTitle = $isEdit ? 'Edit Certificate' : 'Add Certificate';
$activePage = 'certificates';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="page-header page-header-between">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <a class="btn btn-secondary" href="certificates.php">Back to Certificates</a>
    </div>

    <div class="panel form-panel">
        <form id="certificateForm" class="dashboard-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" id="certificateId" value="<?= $isEdit ? (int) $certificateId : '' ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required maxlength="150">
                </div>

                <div class="form-group">
                    <label for="issuer">Issuer</label>
                    <input type="text" id="issuer" name="issuer" required maxlength="150">
                </div>

                <div class="form-group">
                    <label for="issue_date">Issue Date</label>
                    <input type="date" id="issue_date" name="issue_date" required>
                </div>

                <div class="form-group">
                    <label for="credential_link">Credential Link <span class="text-muted">(optional)</span></label>
                    <input type="url" id="credential_link" name="credential_link" placeholder="https://example.com/cert">
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
                <button type="submit" class="btn btn-primary" id="saveCertificateBtn">
                    <i class="fas fa-save"></i>
                    <span>Save Certificate</span>
                </button>
            </div>
        </form>
    </div>
</section>

<script src="src/js/certificate-form.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
