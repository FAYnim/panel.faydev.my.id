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
        /* CSS Custom Properties for Theming */
        :root {
            --bg-body: #f8f9fa;
            --bg-sidebar: #343a40;
            --bg-sidebar-hover: #2d3338;
            --bg-card: #ffffff;
            --bg-table-header: #f8f9fa;
            --bg-hover: #f8f9fa;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --text-sidebar: #adb5bd;
            --text-sidebar-active: #ffffff;
            --border-color: #dee2e6;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            --accent-color: #0d6efd;
        }
        
        [data-theme="dark"] {
            --bg-body: #1a1d20;
            --bg-sidebar: #0d1117;
            --bg-sidebar-hover: #161b22;
            --bg-card: #22272e;
            --bg-table-header: #2d333b;
            --bg-hover: #2d333b;
            --text-primary: #e6edf3;
            --text-secondary: #7d8590;
            --text-sidebar: #7d8590;
            --text-sidebar-active: #e6edf3;
            --border-color: #444c56;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0,0,0,0.3);
            --accent-color: #539bf5;
        }
        
        body { background: var(--bg-body); color: var(--text-primary); transition: background-color 0.3s ease, color 0.3s ease; }
        .sidebar { min-height: 100vh; background: var(--bg-sidebar); }
        .sidebar a { color: var(--text-sidebar); text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.3s ease; }
        .sidebar a:hover, .sidebar a.active { background: var(--bg-sidebar-hover); color: var(--text-sidebar-active); border-left-color: var(--accent-color); }
		#btn-logout { border-left: 1px solid var(--border-color); }
        .main-content { min-height: 100vh; }
        .card { border: none; box-shadow: var(--shadow-sm); background: var(--bg-card); color: var(--text-primary); transition: background-color 0.3s ease, color 0.3s ease; }
        .table th { font-weight: 600; background: var(--bg-table-header); color: var(--text-primary); }
        .table { color: var(--text-primary); }
        .btn-action { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .sortable-item { cursor: move; }
        .sortable-item:hover { background: var(--bg-hover); }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        
        /* Hamburger Button */
        #hamburger-btn {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1050;
            background: var(--bg-sidebar);
            border: none;
            color: var(--text-sidebar-active);
            width: 45px;
            height: 45px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        #hamburger-btn:hover {
            background: var(--bg-sidebar-hover);
        }
        
        /* Sidebar Mobile Styles */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                z-index: 1040;
                transition: left 0.3s ease-in-out;
                box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
            }
        }
        
        /* Backdrop */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1030;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .sidebar-backdrop.show {
            display: block;
            opacity: 1;
        }
        
        /* Close button in sidebar */
        .sidebar-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: transparent;
            border: none;
            color: var(--text-sidebar-active);
            font-size: 24px;
            cursor: pointer;
            padding: 5px 10px;
            line-height: 1;
        }
        .sidebar-close-btn:hover {
            color: var(--accent-color);
        }
        
        @media (min-width: 768px) {
            #hamburger-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger Button for Mobile -->
    <button id="hamburger-btn" class="d-md-none" aria-label="Toggle Sidebar">
        <i class="fas fa-bars fa-lg"></i>
    </button>
    
    <!-- Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
                <!-- Close Button for Mobile -->
                <button class="sidebar-close-btn d-md-none" id="sidebar-close-btn" aria-label="Close Sidebar">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="position-sticky pt-3">
                    <div class="px-3 mt-1 text-white">
                        <h3><i class="fas fa-code me-2"></i>Faydev</h3>
                    </div>
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
                    <div class="px-3 mb-3">
                        <button id="theme-toggle" class="btn btn-outline-secondary btn-sm w-100" title="Toggle Theme">
                            <i class="fas fa-moon me-1" id="theme-icon"></i>
                            <span id="theme-text">Dark Mode</span>
                        </button>
                    </div>
                    <div class="px-3">
                        <a href="logout.php" id="btn-logout" class="btn btn-outline-light btn-sm w-100">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $pageTitle ?? 'Dashboard' ?></h1>
                </div>
