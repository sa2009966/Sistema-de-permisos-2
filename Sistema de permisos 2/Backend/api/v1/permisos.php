<?php
/**
 * API Endpoint: Permisos
 * Rutas: /api/v1/permisos/*
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/CorsMiddleware.php';
require_once __DIR__ . '/../../controllers/PermisoController.php';

// Configurar CORS y headers de seguridad
CorsMiddleware::handle();
CorsMiddleware::setSecurityHeaders();

// Inicializar conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar controlador
$permisoController = new PermisoController($db);

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

// Manejar las diferentes rutas de permisos
switch ($action) {
    case 'index':
        $permisoController->index();
        break;
        
    case 'create':
        $permisoController->create();
        break;
        
    case 'show':
        if ($id) {
            $permisoController->show($id);
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de permiso requerido',
                'timestamp' => date('c')
            ]);
        }
        break;
        
    case 'update':
        if ($id) {
            $permisoController->updateStatus($id);
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de permiso requerido',
                'timestamp' => date('c')
            ]);
        }
        break;
        
    case 'delete':
        if ($id) {
            $permisoController->delete($id);
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID de permiso requerido',
                'timestamp' => date('c')
            ]);
        }
        break;
        
    case 'pending':
        $permisoController->pending();
        break;
        
    case 'stats':
        $permisoController->stats();
        break;
        
    case 'search':
        $permisoController->search();
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
