<?php
/**
 * Authentication API
 * POST ?action=login  — Authenticate admin
 * POST ?action=logout — Destroy session
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

switch ($action) {
    case 'login':
        handleLogin();
        break;

    case 'logout':
        handleLogout();
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleLogin(): void
{
    // Read JSON body or form data
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';
        $csrf     = $body['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $csrf     = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    // Validate CSRF
    if (!validateCsrfToken($csrf)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        return;
    }

    $result = attemptLogin($username, $password);

    if ($result['success']) {
        // Regenerate CSRF token for new session
        unset($_SESSION['csrf_token']);
        generateCsrfToken();

        echo json_encode([
            'success' => true,
            'data'    => ['message' => 'Login successful'],
        ]);
    } else {
        http_response_code(401);
        echo json_encode($result);
    }
}

function handleLogout(): void
{
    destroySession();
    echo json_encode([
        'success' => true,
        'data'    => ['message' => 'Logged out'],
    ]);
}
