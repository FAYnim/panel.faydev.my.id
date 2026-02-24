<?php
session_start();
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle = 'Add Project';
$isEdit = false;
$project = [
    'id' => '',
    'name' => '',
    'slug' => '',
    'description' => '',
    'tech_stack' => '',
    'demo_url' => '',
    'github_url' => '',
    'preview_image' => '',
    'status' => 'Draft'
];

$pdo = getDB();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if (isset($_GET['id'])) {
    $pageTitle = 'Edit Project';
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $project = $stmt->fetch();
    
    if (!$project) {
        header('Location: projects.php');
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT category_id FROM project_categories WHERE project_id = ?");
    $stmt->execute([$project['id']]);
    $projectCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $projectCategories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $description = $_POST['description'] ?? '';
    $techStack = $_POST['tech_stack'] ?? '';
    $demoUrl = $_POST['demo_url'] ?? '';
    $githubUrl = $_POST['github_url'] ?? '';
    $status = $_POST['status'] ?? 'Draft';
    $selectedCategories = $_POST['categories'] ?? [];
    
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($description)) $errors[] = 'Description is required';
    if (empty($status)) $errors[] = 'Status is required';
    if (empty($selectedCategories)) $errors[] = 'At least one category is required';
    
    if (empty($slug)) {
        $slug = generateSlug($name);
    } else {
        $slug = generateSlug($slug);
    }
    
    $slugCheck = $pdo->prepare("SELECT id FROM projects WHERE slug = ? AND id != ?");
    $slugCheck->execute([$slug, $isEdit ? $project['id'] : 0]);
    if ($slugCheck->fetch()) {
        $errors[] = 'Slug already exists';
    }
    
    $previewImage = $project['preview_image'];
    if (!empty($_FILES['preview_image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['preview_image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP';
        } else {
            $ext = pathinfo($_FILES['preview_image']['name'], PATHINFO_EXTENSION);
            $newFilename = $slug . '_' . time() . '.' . $ext;
            $uploadPath = UPLOAD_PATH . $newFilename;
            
            if (move_uploaded_file($_FILES['preview_image']['tmp_name'], $uploadPath)) {
                if ($previewImage && file_exists(UPLOAD_PATH . $previewImage)) {
                    unlink(UPLOAD_PATH . $previewImage);
                }
                $previewImage = $newFilename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            if ($isEdit) {
                $stmt = $pdo->prepare("
                    UPDATE projects SET name = ?, slug = ?, description = ?, tech_stack = ?, 
                    demo_url = ?, github_url = ?, preview_image = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $techStack, $demoUrl, $githubUrl, $previewImage, $status, $project['id']]);
                $projectId = $project['id'];
                logActivity('update_project', "Updated project: $name");
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO projects (name, slug, description, tech_stack, demo_url, github_url, preview_image, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $description, $techStack, $demoUrl, $githubUrl, $previewImage, $status]);
                $projectId = $pdo->lastInsertId();
                logActivity('create_project', "Created project: $name");
            }
            
            $pdo->exec("DELETE FROM project_categories WHERE project_id = $projectId");
            foreach ($selectedCategories as $catId) {
                $pdo->exec("INSERT INTO project_categories (project_id, category_id) VALUES ($projectId, $catId)");
            }
            
            generateProjectsJson();
            
            $pdo->commit();
            
            header('Location: projects.php?success=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

function logActivity($action, $description) {
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO activity_logs (action, description) VALUES (?, ?)');
    $stmt->execute([$action, $description]);
}

function generateProjectsJson() {
    $pdo = getDB();
    $stmt = $pdo->query("
        SELECT p.*, GROUP_CONCAT(c.name) as categories 
        FROM projects p 
        LEFT JOIN project_categories pc ON p.id = pc.project_id 
        LEFT JOIN categories c ON pc.category_id = c.id 
        WHERE p.deleted_at IS NULL 
        GROUP BY p.id
    ");
    $projects = $stmt->fetchAll();
    
    $output = [];
    foreach ($projects as $p) {
        $output[] = [
            'id' => (int)$p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'description' => $p['description'],
            'tech_stack' => $p['tech_stack'],
            'demo_url' => $p['demo_url'],
            'github_url' => $p['github_url'],
            'preview_image' => $p['preview_image'] ? UPLOAD_URL . $p['preview_image'] : null,
            'status' => $p['status'],
            'categories' => $p['categories'] ? explode(',', $p['categories']) : []
        ];
    }
    
    $jsonPath = JSON_PATH . 'projects.json';
    if (file_exists($jsonPath)) {
        $backupName = 'projects_' . date('Ymd_His') . '.json';
        copy($jsonPath, BACKUP_PATH . $backupName);
    }
    
    file_put_contents($jsonPath, json_encode($output, JSON_PRETTY_PRINT));
    logActivity('json_generated', 'Generated projects.json');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= sanitize($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Project Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= sanitize($project['name']) ?>" required oninput="generateSlug(this.value)">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug *</label>
                        <input type="text" name="slug" class="form-control" value="<?= sanitize($project['slug']) ?>" id="slugInput">
                        <small class="text-muted">Auto-generated from name if empty</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="5" required><?= sanitize($project['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tech Stack</label>
                        <input type="text" name="tech_stack" class="form-control" value="<?= sanitize($project['tech_stack']) ?>" placeholder="PHP, MySQL, Bootstrap">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Demo URL</label>
                                <input type="url" name="demo_url" class="form-control" value="<?= sanitize($project['demo_url']) ?>" placeholder="https://...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github_url" class="form-control" value="<?= sanitize($project['github_url']) ?>" placeholder="https://github.com/...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="Draft" <?= $project['status'] == 'Draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="Development" <?= $project['status'] == 'Development' ? 'selected' : '' ?>>Development</option>
                            <option value="Live" <?= $project['status'] == 'Live' ? 'selected' : '' ?>>Live</option>
                            <option value="Archived" <?= $project['status'] == 'Archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Categories * (select at least one)</label>
                        <select name="categories[]" class="form-select" multiple required size="5">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $projectCategories) ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">Preview Image</div>
                        <div class="card-body text-center">
                            <?php if ($project['preview_image']): ?>
                                <img src="<?= UPLOAD_URL . $project['preview_image'] ?>" alt="Preview" class="img-fluid rounded mb-3" id="previewImg">
                            <?php else: ?>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Crect fill='%23dee2e6' width='200' height='200'/%3E%3Ctext fill='%236c757d' x='50%25' y='50%25' text-anchor='middle' dy='.3em'%3ENo Image%3C/text%3E%3C/svg%3E" alt="No Image" class="img-fluid rounded mb-3" id="previewImg">
                            <?php endif; ?>
                            <input type="file" name="preview_image" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Project</button>
                <a href="projects.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function generateSlug(name) {
    if (document.getElementById('slugInput').value === '') {
        document.getElementById('slugInput').value = name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-|-$/g, '');
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
