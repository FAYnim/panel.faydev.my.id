<?php
require_once __DIR__ . '/db.php';

$db = new Database();
$pdo = $db->getConnection();

define('SITE_URL', 'http://localhost/faydev/control-panel');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('BACKUP_PATH', dirname(__DIR__) . '/backup/');
define('JSON_PATH', dirname(__DIR__) . '/data/');

function getDB() {
    global $pdo;
    return $pdo;
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function logActivity($action, $description) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO activity_logs (action, description) VALUES (?, ?)');
    $stmt->execute([$action, $description]);
}
