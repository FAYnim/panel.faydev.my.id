<?php
/**
 * Login Page
 * Faydev Dashboard
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

// Already logged in? Go to dashboard
if (isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Faydev Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        body.login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: var(--clr-bg);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }

        .login-card {
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-lg);
            padding: 2.5rem 2rem;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo h1 {
            font-family: var(--font-heading);
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--clr-green-400);
            margin: 0 0 0.25rem 0;
        }

        .login-logo p {
            font-size: 0.875rem;
            color: var(--clr-text-muted);
            margin: 0;
        }

        .login-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: none;
        }

        .login-error.visible {
            display: block;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--clr-text);
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--clr-bg);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-md);
            color: var(--clr-text);
            font-family: var(--font-body);
            font-size: 0.9375rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--clr-green-400);
            box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.15);
        }

        .login-btn {
            width: 100%;
            padding: 0.875rem;
            background: var(--clr-green-500);
            color: #000;
            border: none;
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .login-btn:hover {
            background: var(--clr-green-400);
        }

        .login-btn:active {
            transform: scale(0.98);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <h1>Faydev</h1>
                <p>Dashboard Admin Panel</p>
            </div>

            <div id="loginError" class="login-error"></div>

            <form id="loginForm" method="POST" action="api/auth.php?action=login">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>
