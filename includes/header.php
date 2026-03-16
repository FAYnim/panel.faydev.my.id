<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

requireAuth();

$currentAdmin = getCurrentAdmin();
$csrfToken = generateCsrfToken();
$pageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Dashboard';
$activePage = isset($activePage) && is_string($activePage) ? $activePage : '';

$cookieTheme = $_COOKIE['theme'] ?? 'dark';
$initialTheme = in_array($cookieTheme, ['dark', 'light'], true) ? $cookieTheme : 'dark';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($initialTheme, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> — Faydev Dashboard</title>
    <script>
        (function () {
            var stored = localStorage.getItem('theme');
            if (stored === 'dark' || stored === 'light') {
                document.documentElement.setAttribute('data-theme', stored);
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-logo">Faydev</h1>
            <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
        </div>
        <nav class="sidebar-nav">
            <a class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="/index.php">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
            <a class="nav-item <?= $activePage === 'projects' ? 'active' : '' ?>" href="/pages/projects.php">
                <i class="fas fa-folder-open"></i><span>Projects</span>
            </a>
            <a class="nav-item <?= $activePage === 'social' ? 'active' : '' ?>" href="/pages/social.php">
                <i class="fas fa-share-alt"></i><span>Social Links</span>
            </a>
            <div class="nav-divider"></div>
            <a class="nav-item" href="#" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <button class="theme-toggle" id="themeToggle" title="Toggle theme" type="button">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <header class="topbar">
            <div class="topbar-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="topbar-admin">Hi, <?= htmlspecialchars($currentAdmin['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></div>
        </header>
