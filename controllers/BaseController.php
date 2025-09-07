<?php
/**
 * Base Controller class
 * Handles common functionality and prevents header modification errors
 */

class BaseController {
    
    public function __construct() {
        // Ensure output buffering is active
        if (!ob_get_level()) {
            ob_start();
        }
    }
    
    /**
     * Redirect to a specific URL
     * This method ensures headers can be sent properly
     * Line 45 from the error was likely a redirect or header modification
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
        
        // Safe to send headers - this is likely line 45 from the error
        header("Location: $url");
        exit();
    }
    
    /**
     * Send JSON response
     * Ensures proper content-type headers
     */
    protected function jsonResponse($data, $status = 200) {
        if (headers_sent()) {
            // Cannot set headers, just output JSON
            echo json_encode($data);
            return;
        }
        
        // Clean output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set headers
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Set session message
     * Handles session messages safely
     */
    protected function setMessage($message, $type = 'info') {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    
    /**
     * Get and clear session message
     */
    protected function getMessage() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        
        // Clear the message
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        return $message ? ['message' => $message, 'type' => $type] : null;
    }
    
    /**
     * Check if user is logged in
     */
    protected function isLoggedIn() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login.php');
        }
    }
}

?>