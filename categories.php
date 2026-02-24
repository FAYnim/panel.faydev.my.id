<?php
session_start();
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $name = sanitize($_POST['name'] ?? '');
        $slug = generateSlug($_POST['slug'] ?? '');
        
        if (empty($name)) {
            $error = 'Name is required';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            logActivity('create_category', "Created category: $name");
            header('Location: categories.php?success=1');
            exit;
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $cat = $stmt->fetch();
            
            $pdo->exec("DELETE FROM project_categories WHERE category_id = $id");
            $pdo->exec("DELETE FROM categories WHERE id = $id");
            logActivity('delete_category', "Deleted category: " . $cat['name']);
            header('Location: categories.php?success=1');
            exit;
        }
    }
}

$categories = $pdo->query("
    SELECT c.*, COUNT(pc.project_id) as project_count 
    FROM categories c 
    LEFT JOIN project_categories pc ON c.id = pc.category_id 
    GROUP BY c.id 
    ORDER BY c.name
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add Category</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= sanitize($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug (optional)</label>
                        <input type="text" name="slug" class="form-control" placeholder="Auto-generated if empty">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Projects</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><strong><?= sanitize($cat['name']) ?></strong></td>
                                        <td><code><?= sanitize($cat['slug']) ?></code></td>
                                        <td><?= $cat['project_count'] ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-action" onclick="deleteCategory(<?= $cat['id'] ?>, '<?= sanitize($cat['name']) ?>')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCategory(id, name) {
    if (confirm('Are you sure you want to delete the category "' + name + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
