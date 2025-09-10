<?php
/**
 * API Endpoint: Autenticación
 * Rutas: /api/v1/auth/*
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/CorsMiddleware.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Configurar CORS y headers de seguridad
CorsMiddleware::handle();
CorsMiddleware::setSecurityHeaders();

// Inicializar conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar controlador
$authController = new AuthController($db);

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Determinar la acción basada en la ruta
$action = end($pathParts);

// Manejar las diferentes rutas de autenticación
switch ($action) {
    case 'login':
        $authController->login();
        break;
        
    case 'register':
        $authController->register();
        break;
        
    case 'refresh':
        $authController->refresh();
        break;
        
    case 'profile':
        $authController->profile();
        break;
        
    case 'change-password':
        $authController->changePassword();
        break;
        
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'timestamp' => date('c')
        ]);
        break;
}
?>
