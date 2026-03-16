<?php
/**
 * Authentication & Session Management
 * Faydev Dashboard
 */

require_once __DIR__ . '/db.php';

/** Session timeout: 2 hours in seconds */
define('SESSION_TIMEOUT', 7200);

/**
 * Initialize session with secure settings.
 * Call at the top of every page/API that needs auth.
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Check if the current session is authenticated.
 */
function isLoggedIn(): bool
{
    initSession();

    if (empty($_SESSION['admin_id'])) {
        return false;
    }

    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            destroySession();
            return false;
        }
    }

    // Refresh last activity timestamp
    $_SESSION['last_activity'] = time();

    return true;
}

/**
 * Require authentication. Redirects to login for page requests,
 * returns 401 JSON for API requests.
 */
function requireAuth(): void
{
    if (isLoggedIn()) {
        return;
    }

    // Detect if this is an API request
    $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false
          || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isApi) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Page request — redirect to login
    header('Location: /login.php');
    exit;
}

/**
 * Get the current admin's data.
 *
 * @return array{id: int, username: string}|null
 */
function getCurrentAdmin(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id'       => (int) $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'] ?? '',
    ];
}

/**
 * Attempt to log in with the given credentials.
 *
 * @return array{success: bool, message?: string}
 */
function attemptLogin(string $username, string $password): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    // Regenerate session ID to prevent session fixation
    initSession();
    session_regenerate_id(true);

    $_SESSION['admin_id']       = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['last_activity']  = time();

    return ['success' => true];
}

/**
 * Destroy the current session (logout).
 */
function destroySession(): void
{
    initSession();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
