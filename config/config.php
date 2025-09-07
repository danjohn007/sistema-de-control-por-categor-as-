<?php
/**
 * Configuración general de la aplicación
 */

// Check if we're in CLI mode
$isCLI = php_sapi_name() === 'cli';

// Start output buffering to prevent "headers already sent" errors (only for web)
if (!$isCLI && !ob_get_level()) {
    ob_start();
}

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Control de Gastos e Ingresos');
define('APP_VERSION', '1.0.0');

// Configuración de URL base - Se ajusta automáticamente (only for web requests)
if (!$isCLI) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    define('BASE_URL', $protocol . '://' . $host . $path);
} else {
    define('BASE_URL', '/');
}

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure session settings ONLY if session is not already active
if (session_status() !== PHP_SESSION_ACTIVE && !$isCLI) {
    // Configuración de sesión - only configure if session is not active
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
}

// Roles de usuario
define('ROLE_ADMIN', 1);
define('ROLE_USER', 2);

// Tipos de movimiento
define('TYPE_INCOME', 'income');
define('TYPE_EXPENSE', 'expense');

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);
?>