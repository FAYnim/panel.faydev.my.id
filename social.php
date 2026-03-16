<?php
$pageTitle = 'Social Links';
$activePage = 'social';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-section">
    <div class="page-header page-header-between">
        <h1 class="page-title">Social Links</h1>
        <a class="btn btn-primary" href="social-form.php">
            <i class="fas fa-plus"></i>
            <span>Add Social Link</span>
        </a>
    </div>

    <div class="panel table-panel">
        <div class="table-wrapper">
            <table class="data-table" aria-label="Social links table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="socialTableBody">
                    <tr>
                        <td colspan="5" class="table-loading">Loading social links...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="empty-state" id="socialEmptyState" hidden>
            <p>No social links found.</p>
        </div>
    </div>
</section>

<script src="assets/js/social.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
