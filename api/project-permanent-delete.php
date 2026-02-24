<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$id = (int)($_POST['id'] ?? 0);

if (!$id) {
    jsonResponse(false, 'Project ID is required');
}

try {
    $stmt = $pdo->prepare("SELECT name, preview_image FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        jsonResponse(false, 'Project not found');
    }
    
    $pdo->beginTransaction();
    
    $pdo->exec("DELETE FROM project_categories WHERE project_id = $id");
    $pdo->exec("DELETE FROM app_config WHERE project_id = $id");
    $pdo->exec("DELETE FROM portfolio_config WHERE project_id = $id");
    
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($project['preview_image'] && file_exists(UPLOAD_PATH . $project['preview_image'])) {
        unlink(UPLOAD_PATH . $project['preview_image']);
    }
    
    generateProjectsJson();
    
    $pdo->commit();
    
    logActivity('permanent_delete', "Permanently deleted project: " . $project['name']);
    jsonResponse(true, 'Project permanently deleted');
    
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

function generateProjectsJson() {
    global $pdo;
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
}
