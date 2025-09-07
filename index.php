<?php
/**
 * Sistema de Control de Gastos e Ingresos por Categorías
 * Punto de entrada principal de la aplicación
 */

// Incluir archivos de configuración ANTES de iniciar sesión
require_once 'config/config.php';
require_once 'config/database.php';

// Now safe to start session (config has set up session parameters)
if (php_sapi_name() !== 'cli') {
    session_start();
}

// Incluir controladores
require_once 'controllers/BaseController.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/CategoryController.php';
require_once 'controllers/MovementController.php';
require_once 'controllers/CalendarController.php';

// Obtener la acción solicitada
$action = $_GET['action'] ?? 'login';
$controller = $_GET['controller'] ?? 'auth';

// Verificar si el usuario está logueado (excepto para login)
if ($action !== 'login' && $action !== 'authenticate' && !isset($_SESSION['user_id'])) {
    header('Location: index.php?controller=auth&action=login');
    exit;
}

// Enrutamiento simple
switch ($controller) {
    case 'auth':
        $authController = new AuthController();
        switch ($action) {
            case 'login':
                $authController->login();
                break;
            case 'authenticate':
                $authController->authenticate();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                $authController->login();
        }
        break;
        
    case 'dashboard':
        $dashboardController = new DashboardController();
        switch ($action) {
            case 'index':
                $dashboardController->index();
                break;
            default:
                $dashboardController->index();
        }
        break;
        
    case 'category':
        $categoryController = new CategoryController();
        switch ($action) {
            case 'index':
                $categoryController->index();
                break;
            case 'create':
                $categoryController->create();
                break;
            case 'store':
                $categoryController->store();
                break;
            case 'edit':
                $categoryController->edit();
                break;
            case 'update':
                $categoryController->update();
                break;
            case 'delete':
                $categoryController->delete();
                break;
            default:
                $categoryController->index();
        }
        break;
        
    case 'movement':
        $movementController = new MovementController();
        switch ($action) {
            case 'index':
                $movementController->index();
                break;
            case 'create':
                $movementController->create();
                break;
            case 'store':
                $movementController->store();
                break;
            case 'edit':
                $movementController->edit();
                break;
            case 'update':
                $movementController->update();
                break;
            case 'delete':
                $movementController->delete();
                break;
            default:
                $movementController->index();
        }
        break;
        
    case 'calendar':
        $calendarController = new CalendarController();
        switch ($action) {
            case 'index':
                $calendarController->index();
                break;
            default:
                $calendarController->index();
        }
        break;
        
    default:
        // Redirigir al dashboard si está logueado, sino al login
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=dashboard&action=index');
        } else {
            header('Location: index.php?controller=auth&action=login');
        }
        exit;
}
?>