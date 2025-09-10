<?php
/**
 * API Endpoint: Usuarios
 * Rutas: /api/v1/usuarios/*
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/CorsMiddleware.php';
require_once __DIR__ . '/../../controllers/UserController.php';

// Configurar CORS y headers de seguridad
CorsMiddleware::handle();
CorsMiddleware::setSecurityHeaders();

// Inicializar conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar controlador
$userController = new UserController($db);

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Extraer ID si está presente
$id = null;
$action = 'index';

// Determinar la acción basada en la ruta
if (count($pathParts) >= 4) {
    $lastPart = end($pathParts);
    
    // Si el último elemento es un número, es un ID
    if (is_numeric($lastPart)) {
        $id = (int)$lastPart;
        $action = 'show';
    } else {
        $action = $lastPart;
    }
    
    // Si hay un penúltimo elemento y es un número, es un ID
    if (count($pathParts) >= 5 && is_numeric($pathParts[count($pathParts) - 2])) {
        $id = (int)$pathParts[count($pathParts) - 2];
    }
}

// Manejar las diferentes rutas de usuarios
switch ($action) {
    case 'index':
        $userController->index();
        break;
        
    case 'show':
        if ($id) {
            $userController->show($id);
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario requerido',
                'timestamp' => date('c')
            ]);
        }
        break;
        
    case 'update':
        if ($id) {
            $userController->update($id);
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario requerido',
                'timestamp' => date('c')
            ]);
        }
        break;
        
    case 'delete':
        if ($id) {
            $userController->delete($id);
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario requerido',
                'timestamp' => date('c')
            ]);
        }
        break;
        
    case 'students':
        $userController->students();
        break;
        
    case 'stats':
        if ($id) {
            $userController->stats($id);
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario requerido',
                'timestamp' => date('c')
            ]);
        }
        break;
        
    case 'search':
        $userController->search();
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
