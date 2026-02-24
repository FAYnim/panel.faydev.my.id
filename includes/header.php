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
		#btn-logout { border-left: 1px solid #f8f9fa; }
        .main-content { min-height: 100vh; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .table th { font-weight: 600; background: #f8f9fa; }
        .btn-action { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
        .sortable-item { cursor: move; }
        .sortable-item:hover { background: #f8f9fa; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        
        /* Hamburger Button */
        #hamburger-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
            background: #343a40;
            border: none;
            color: white;
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
            background: #2d3338;
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
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px 10px;
            line-height: 1;
        }
        .sidebar-close-btn:hover {
            color: #0d6efd;
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
