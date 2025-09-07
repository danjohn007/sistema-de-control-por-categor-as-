<?php
/**
 * Index page - Entry point for the application
 * Demonstrates the session fix in action
 */

// Include configuration first to set up sessions properly
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/BaseController.php';

class IndexController extends BaseController {
    public function handleRequest() {
        // If logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard.php');
        } else {
            // Not logged in, redirect to login
            $this->redirect('login.php');
        }
    }
}

$controller = new IndexController();
$controller->handleRequest();
?>