<?php
session_start();

require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && $password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            
            logActivity('login', 'Admin logged in');
            
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Faydev Control Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Custom Properties for Theming */
        :root {
            --bg-body: #f5f5f5;
            --bg-card: #ffffff;
            --text-primary: #212529;
            --text-muted: #6c757d;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        
        [data-theme="dark"] {
            --bg-body: #1a1d20;
            --bg-card: #22272e;
            --text-primary: #e6edf3;
            --text-muted: #7d8590;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.3);
        }
        
        body { 
            background: var(--bg-body); 
            color: var(--text-primary);
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .login-card { max-width: 400px; width: 100%; }
        .card { 
            background: var(--bg-card); 
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .text-muted { color: var(--text-muted) !important; }
        
        /* Theme toggle button */
        #theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: var(--bg-card);
            color: var(--text-primary);
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        #theme-toggle:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <button id="theme-toggle" title="Toggle Theme">
        <i class="fas fa-moon" id="theme-icon"></i>
    </button>
    
    <div class="container">
        <div class="login-card card shadow-sm mx-auto">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h4 class="mb-1">Faydev Control Panel</h4>
                    <p class="text-muted">Sign in to your account</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= sanitize($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign In</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlElement = document.documentElement;
        
        // Function to apply theme
        function applyTheme(theme) {
            if (theme === 'dark') {
                htmlElement.setAttribute('data-theme', 'dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                htmlElement.removeAttribute('data-theme');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }
        
        // Load saved theme or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        applyTheme(savedTheme);
        
        // Toggle theme on button click
        themeToggle.addEventListener('click', function() {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>
</body>
</html>
