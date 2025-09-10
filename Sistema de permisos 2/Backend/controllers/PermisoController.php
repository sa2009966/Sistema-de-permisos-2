<?php
/**
 * Controlador de permisos
 */

require_once __DIR__ . '/../services/PermisoService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class PermisoController {
    private $permisoService;
    
    public function __construct($db) {
        $this->permisoService = new PermisoService($db);
    }
    
    /**
     * Obtener todos los permisos
     */
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        
        // Filtros
        $filters = [];
        if (isset($_GET['estado'])) {
            $filters['estado'] = $_GET['estado'];
        }
        if (isset($_GET['id_alumno'])) {
            $filters['id_alumno'] = $_GET['id_alumno'];
        }
        
        try {
            // Si es alumno, solo mostrar sus permisos
            if ($payload['rol'] === 'alumno') {
                $result = $this->permisoService->getPermisosByUser($payload['user_id'], $page, $perPage);
            } else {
                // Maestros y directores pueden ver todos los permisos
                $result = $this->permisoService->getAllPermisos($page, $perPage, $filters);
            }
            
            Response::paginated($result['items'], $result['pagination'], 'Permisos obtenidos exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Crear nuevo permiso
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Datos JSON inválidos');
        }
        
        try {
            $permiso = $this->permisoService->createPermiso($input, $payload['user_id']);
            Response::success($permiso, 'Permiso creado exitosamente', 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Obtener permiso por ID
     */
    public function show($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        try {
            $permiso = $this->permisoService->getPermisoById($id);
            
            // Verificar que el usuario puede acceder a este permiso
            if ($payload['rol'] === 'alumno' && $permiso['id_alumno'] != $payload['user_id']) {
                Response::forbidden('No tienes permisos para ver este permiso');
            }
            
            Response::success($permiso, 'Permiso obtenido exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }
    
    /**
     * Actualizar estado del permiso
     */
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::methodNotAllowed();
        }
        
        // Solo maestros y directores pueden actualizar estados
        $payload = AuthMiddleware::requireAnyRole(['maestro', 'director']);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Datos JSON inválidos');
        }
        
        // Validar campos requeridos
        $errors = Validator::required($input, ['estado']);
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        $estado = $input['estado'];
        $comentarios = $input['comentarios'] ?? '';
        
        try {
            $permiso = $this->permisoService->updatePermisoStatus($id, $estado, $comentarios, $payload['user_id']);
            Response::success($permiso, 'Estado del permiso actualizado exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Eliminar permiso
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        try {
            $this->permisoService->deletePermiso($id, $payload['user_id']);
            Response::success(null, 'Permiso eliminado exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Obtener permisos pendientes
     */
    public function pending() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Solo maestros y directores pueden ver permisos pendientes
        $payload = AuthMiddleware::requireAnyRole(['maestro', 'director']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        
        try {
            $result = $this->permisoService->getPendingPermisos($page, $perPage);
            Response::paginated($result['items'], $result['pagination'], 'Permisos pendientes obtenidos exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener estadísticas de permisos
     */
    public function stats() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        $userId = null;
        
        // Si es alumno, solo mostrar sus estadísticas
        if ($payload['rol'] === 'alumno') {
            $userId = $payload['user_id'];
        }
        
        try {
            $stats = $this->permisoService->getPermisoStats($userId);
            Response::success($stats, 'Estadísticas obtenidas exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar permisos
     */
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            Response::error('Parámetro de búsqueda requerido');
        }
        
        $query = $_GET['q'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        
        try {
            $result = $this->permisoService->searchPermisos($query, $page, $perPage);
            Response::paginated($result['items'], $result['pagination'], 'Búsqueda completada exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
?>
