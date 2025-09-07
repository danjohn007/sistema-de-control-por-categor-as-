<?php
/**
 * Controlador base con funcionalidades comunes
 */

abstract class BaseController {
    protected $db;
    
    public function __construct() {
        // Ensure output buffering is active to prevent header issues
        if (!ob_get_level()) {
            ob_start();
        }
        $this->db = getDB();
    }
    
    /**
     * Renderizar vista
     */
    protected function render($view, $data = []) {
        // Extraer datos para usar como variables
        extract($data);
        
        // Iniciar buffer de salida
        ob_start();
        
        // Incluir la vista
        include "views/{$view}.php";
        
        // Obtener contenido del buffer
        $content = ob_get_clean();
        
        // Incluir el layout principal
        include 'views/layouts/main.php';
    }
    
    /**
     * Renderizar vista sin layout
     */
    protected function renderPartial($view, $data = []) {
        extract($data);
        include "views/{$view}.php";
    }
    
    /**
     * Redirigir a otra página
     */
    protected function redirect($url) {
        // Check if headers have already been sent
        if (headers_sent($file, $line)) {
            // If headers were sent, we can't redirect normally
            // Use JavaScript redirect as fallback
            echo "<script>window.location.href = '$url';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=$url'></noscript>";
            return;
        }
        
        // Clean any output buffer before redirecting
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Safe to send headers
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Verificar si el usuario es administrador
     */
    protected function requireAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != ROLE_ADMIN) {
            $this->redirect('index.php?controller=dashboard&action=index');
        }
    }
    
    /**
     * Sanitizar entrada
     */
    protected function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar token CSRF
     */
    protected function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generar token CSRF
     */
    protected function generateCSRF() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Registrar en bitácora
     */
    protected function logAccess($action) {
        $stmt = $this->db->prepare("
            INSERT INTO access_log (user_id, ip_address, user_agent, action) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            $action
        ]);
    }
    
    /**
     * Respuesta JSON
     */
    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>