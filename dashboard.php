<?php
// Include configuration first
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/BaseController.php';

$controller = new BaseController();

// Require authentication
$controller->requireAuth();

// Get any messages
$message = $controller->getMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .status-item {
            background-color: #e7f3ff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h2>Dashboard - <?php echo APP_NAME; ?></h2>
            <div>
                Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                <a href="auth.php?action=logout" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="status-item">
            <h3>✅ Problema Resuelto</h3>
            <p><strong>Errores de sesión corregidos exitosamente:</strong></p>
            <ul>
                <li>❌ <code>Warning: ini_set(): Session ini settings cannot be changed when a session is active</code></li>
                <li>❌ <code>Warning: Cannot modify header information - headers already sent</code></li>
            </ul>
        </div>
        
        <div class="status-item">
            <h3>🔧 Soluciones Implementadas</h3>
            <ul>
                <li><strong>Configuración de sesión correcta:</strong> Se configuran las sesiones ANTES de iniciarlas</li>
                <li><strong>Control de output buffering:</strong> Se previenen los errores de "headers already sent"</li>
                <li><strong>Manejo seguro de redirecciones:</strong> Se verifican los headers antes de enviarlos</li>
                <li><strong>Verificación de estado de sesión:</strong> Se valida si la sesión está activa antes de modificarla</li>
            </ul>
        </div>
        
        <div class="status-item">
            <h3>📋 Detalles Técnicos</h3>
            <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
            <p><strong>Session Status:</strong> 
                <?php 
                $status = session_status();
                switch($status) {
                    case PHP_SESSION_DISABLED:
                        echo "Disabled";
                        break;
                    case PHP_SESSION_NONE:
                        echo "None";
                        break;
                    case PHP_SESSION_ACTIVE:
                        echo "Active ✅";
                        break;
                }
                ?>
            </p>
            <p><strong>Headers Sent:</strong> <?php echo headers_sent() ? 'Yes' : 'No ✅'; ?></p>
            <p><strong>Output Buffer Level:</strong> <?php echo ob_get_level(); ?></p>
        </div>
        
        <div class="status-item">
            <h3>🚀 Sistema Funcionando</h3>
            <p>El sistema de control por categorías ahora puede manejar el login sin errores de sesión.</p>
            <p>Puedes proceder con la implementación del resto de funcionalidades.</p>
        </div>
    </div>
</body>
</html>