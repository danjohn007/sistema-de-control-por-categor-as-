<?php
/**
 * Login Controller
 * Demonstrates proper session handling without errors
 */

// Include the configuration file FIRST (this sets up sessions properly)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/BaseController.php';

class LoginController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Process login attempt
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login.php');
            return;
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Simple authentication (in real app, use database and password hashing)
        if ($this->authenticate($username, $password)) {
            // Set session variables
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            
            $this->setMessage('Login successful', 'success');
            $this->redirect('dashboard.php');
        } else {
            $this->setMessage('Invalid username or password', 'error');
            $this->redirect('login.php');
        }
    }
    
    /**
     * Simple authentication method
     * In a real application, this would check against a database
     */
    private function authenticate($username, $password) {
        // Simple demo authentication (replace with database check)
        $validUsers = [
            'admin' => 'admin123',
            'user' => 'user123'
        ];
        
        return isset($validUsers[$username]) && $validUsers[$username] === $password;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // Clear session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        
        $this->redirect('login.php');
    }
}

// Handle requests
$controller = new LoginController();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'login':
            $controller->processLogin();
            break;
        case 'logout':
            $controller->logout();
            break;
        default:
            $controller->redirect('login.php');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->processLogin();
}

?>