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
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT name FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        jsonResponse(false, 'Project not found');
    }
    
    $stmt = $pdo->prepare("UPDATE projects SET deleted_at = NULL WHERE id = ?");
    $stmt->execute([$id]);
    
    generateProjectsJson();
    
    logActivity('restore_project', "Restored project: " . $project['name']);
    jsonResponse(true, 'Project restored successfully');
    
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
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
}
