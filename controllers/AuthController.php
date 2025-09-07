<?php
/**
 * Controlador de autenticación
 */

class AuthController extends BaseController {
    
    /**
     * Mostrar formulario de login
     */
    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('index.php?controller=dashboard&action=index');
        }
        
        $data = [
            'title' => 'Iniciar Sesión',
            'error' => $_SESSION['error'] ?? null
        ];
        
        // Limpiar mensajes de error
        unset($_SESSION['error']);
        
        $this->render('auth/login', $data);
    }
    
    /**
     * Procesar autenticación
     */
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=auth&action=login');
        }
        
        $username = $this->sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validar campos
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Usuario y contraseña son requeridos';
            $this->redirect('index.php?controller=auth&action=login');
        }
        
        // Buscar usuario
        $stmt = $this->db->prepare("
            SELECT id, username, email, password_hash, full_name, role, active 
            FROM users 
            WHERE (username = ? OR email = ?) AND active = 1
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        // Verificar usuario y contraseña
        if ($user && password_verify($password, $user['password_hash'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Registrar acceso
            $this->logAccess('login');
            
            // Redirigir al dashboard
            $this->redirect('index.php?controller=dashboard&action=index');
        } else {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';
            $this->redirect('index.php?controller=auth&action=login');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        // Registrar salida
        $this->logAccess('logout');
        
        // Destruir sesión
        session_destroy();
        
        // Redirigir al login
        $this->redirect('index.php?controller=auth&action=login');
    }
}
?>