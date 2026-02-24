<?php 
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Faydev Control Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #343a40; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: #2d3338; color: #fff; border-left-color: #0d6efd; }
        .main-content { min-height: 100vh; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .table th { font-weight: 600; background: #f8f9fa; }
        .btn-action { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .sortable-item { cursor: move; }
        .sortable-item:hover { background: #f8f9fa; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
                <div class="position-sticky pt-3">
                    <div class="px-3 mb-3 text-white">
                        <h5><i class="fas fa-code me-2"></i>Faydev</h5>
                        <small class="text-muted">Control Panel</small>
                    </div>
                    <hr>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="index.php" class="<?= $currentPage == 'index' ? 'active' : '' ?>">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="projects.php" class="<?= in_array($currentPage, ['projects', 'project-form']) ? 'active' : '' ?>">
                                <i class="fas fa-folder me-2"></i> Projects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="categories.php" class="<?= $currentPage == 'categories' ? 'active' : '' ?>">
                                <i class="fas fa-tags me-2"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="app-config.php" class="<?= $currentPage == 'app-config' ? 'active' : '' ?>">
                                <i class="fas fa-mobile-alt me-2"></i> App Config
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="portfolio-config.php" class="<?= $currentPage == 'portfolio-config' ? 'active' : '' ?>">
                                <i class="fas fa-briefcase me-2"></i> Portfolio Config
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logs.php" class="<?= $currentPage == 'logs' ? 'active' : '' ?>">
                                <i class="fas fa-history me-2"></i> Activity Logs
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="px-3">
                        <a href="logout.php" class="btn btn-outline-light btn-sm w-100">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $pageTitle ?? 'Dashboard' ?></h1>
                </div>
