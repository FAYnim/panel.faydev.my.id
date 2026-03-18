<?php
$pageTitle = 'Certificates';
$activePage = 'certificates';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="page-header page-header-between">
        <h1 class="page-title">Certificates</h1>
        <a class="btn btn-primary" href="certificate-form.php">
            <i class="fas fa-plus"></i>
            <span>Add Certificate</span>
        </a>
    </div>

    <div class="panel table-panel">
        <div class="table-wrapper">
            <table class="data-table" aria-label="Certificates table">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Issuer</th>
                        <th>Issue Date</th>
                        <th>Credential</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="certificatesTableBody">
                    <tr>
                        <td colspan="6" class="table-loading">Loading certificates...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="empty-state" id="certificatesEmptyState" hidden>
            <p>No certificates found.</p>
        </div>
    </div>
</section>

<script src="src/js/certificates.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
