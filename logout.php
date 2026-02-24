<?php
session_start();

require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    logActivity('logout', 'Admin logged out');
}

session_destroy();
header('Location: ' . SITE_URL . '/login.php');
exit;
