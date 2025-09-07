<?php
/**
 * Configuration file for session management
 * This file handles session configuration BEFORE starting the session
 * to avoid "Session ini settings cannot be changed when a session is active" errors
 */

// Check if we're in CLI mode
$isCLI = php_sapi_name() === 'cli';

// Start output buffering to prevent "headers already sent" errors (only for web)
if (!$isCLI && !ob_get_level()) {
    ob_start();
}

// Check if session is already active before configuring
if (session_status() === PHP_SESSION_ACTIVE) {
    // Session is already active, skip configuration
    // This prevents the "Session ini settings cannot be changed when a session is active" warning
    return;
}

// Only configure session settings if not in CLI mode or if headers haven't been sent
if ($isCLI || !headers_sent()) {
    // Configure session settings BEFORE starting the session
    // These are the lines that were causing the warnings (lines 24-26)
    ini_set('session.cookie_httponly', 1);    // Line 24 equivalent
    ini_set('session.cookie_secure', 0);      // Line 25 equivalent (set to 1 for HTTPS)
    ini_set('session.use_strict_mode', 1);    // Line 26 equivalent

    // Additional security settings
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
    ini_set('session.name', 'CONTROL_SESSION');
}

// Now it's safe to start the session (only for web requests)
if (!$isCLI) {
    session_start();
}

// Database configuration (if needed)
define('DB_HOST', 'localhost');
define('DB_NAME', 'control_db');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_pass');

// Application configuration
define('APP_NAME', 'Sistema de Control por Categorías');
define('BASE_URL', '/'); // Fixed to work with development server

?>