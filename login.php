<?php
// Include configuration first to set up sessions properly
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/BaseController.php';

// Create controller instance to use its methods
class LoginPageController extends BaseController {
    public function showPage() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard.php');
            exit;
        }

        // Get any messages
        $message = $this->getMessage();
        return $message;
    }
}

$controller = new LoginPageController();
$message = $controller->showPage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .demo-info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><?php echo APP_NAME; ?></h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="demo-info">
            <strong>Credenciales de prueba:</strong><br>
            Usuario: admin | Contraseña: admin123<br>
            Usuario: user | Contraseña: user123
        </div>
        
        <form action="auth.php?action=login" method="POST">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 12px; color: #666;">
            <strong>Nota técnica:</strong> Esta implementación resuelve los errores de sesión:<br>
            • Configura sesiones ANTES de iniciarlas<br>
            • Usa output buffering para evitar errores de headers<br>
            • Maneja redirecciones de forma segura
        </div>
    </div>
</body>
</html>