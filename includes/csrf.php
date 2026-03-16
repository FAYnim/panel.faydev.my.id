<?php
/**
 * CSRF Token Generation & Validation
 * Faydev Dashboard
 */

require_once __DIR__ . '/auth.php';

/**
 * Generate or retrieve the current CSRF token.
 * One token per session, regenerated on login.
 */
function generateCsrfToken(): string
{
    initSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the session token.
 */
function validateCsrfToken(?string $token): bool
{
    initSession();

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require a valid CSRF token on POST requests.
 * Checks both the `csrf_token` POST field and the `X-CSRF-Token` header.
 * Returns 403 JSON on failure.
 */
function requireCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = $_POST['csrf_token']
          ?? $_SERVER['HTTP_X_CSRF_TOKEN']
          ?? null;

    if (!validateCsrfToken($token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}
