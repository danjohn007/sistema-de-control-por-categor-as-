<?php
/**
 * Index page - Entry point for the application
 * Demonstrates the session fix in action
 */

// Include configuration first to set up sessions properly
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/BaseController.php';

$controller = new BaseController();

// If logged in, redirect to dashboard
if ($controller->isLoggedIn()) {
    $controller->redirect('dashboard.php');
} else {
    // Not logged in, redirect to login
    $controller->redirect('login.php');
}
?>