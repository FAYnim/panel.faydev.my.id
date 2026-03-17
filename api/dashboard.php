<?php
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDB();

    $projectsCount = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $socialCount = (int) $pdo->query('SELECT COUNT(*) FROM social_links')->fetchColumn();

    $projectsLastUpdated = $pdo->query('SELECT MAX(updated_at) FROM projects')->fetchColumn();
    $socialLastUpdated = $pdo->query('SELECT MAX(updated_at) FROM social_links')->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => [
            'projects_count' => $projectsCount,
            'social_links_count' => $socialCount,
            'projects_last_updated' => $projectsLastUpdated,
            'social_links_last_updated' => $socialLastUpdated,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load dashboard metrics']);
}
