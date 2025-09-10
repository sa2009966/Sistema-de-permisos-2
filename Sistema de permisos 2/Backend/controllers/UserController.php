<?php
/**
 * Controlador de usuarios
 */

require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UserController {
    private $userService;
    
    public function __construct($db) {
        $this->userService = new UserService($db);
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación y rol
        $payload = AuthMiddleware::requireAnyRole(['maestro', 'director']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        
        // Filtros
        $filters = [];
        if (isset($_GET['rol'])) {
            $filters['rol'] = $_GET['rol'];
        }
        if (isset($_GET['activo'])) {
            $filters['activo'] = $_GET['activo'];
        }
        
        try {
            $result = $this->userService->getAllUsers($page, $perPage, $filters);
            Response::paginated($result['items'], $result['pagination'], 'Usuarios obtenidos exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener usuario por ID
     */
    public function show($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        // Verificar que el usuario puede acceder a este recurso
        AuthMiddleware::requireOwnershipOrAdmin($id);
        
        try {
            $user = $this->userService->getUserById($id);
            Response::success($user, 'Usuario obtenido exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        // Verificar que el usuario puede actualizar este recurso
        AuthMiddleware::requireOwnershipOrAdmin($id);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Datos JSON inválidos');
        }
        
        try {
            $user = $this->userService->updateUser($id, $input);
            Response::success($user, 'Usuario actualizado exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            Response::methodNotAllowed();
        }
        
        // Solo directores pueden eliminar usuarios
        $payload = AuthMiddleware::requireRole('director');
        
        try {
            $this->userService->deleteUser($id);
            Response::success(null, 'Usuario eliminado exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Obtener todos los estudiantes
     */
    public function students() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación y rol
        $payload = AuthMiddleware::requireAnyRole(['maestro', 'director']);
        
        try {
            $students = $this->userService->getAllStudents();
            Response::success($students, 'Estudiantes obtenidos exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener estadísticas del usuario
     */
    public function stats($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación
        $payload = AuthMiddleware::authenticate();
        
        // Verificar que el usuario puede acceder a este recurso
        AuthMiddleware::requireOwnershipOrAdmin($id);
        
        try {
            $stats = $this->userService->getUserStats($id);
            Response::success($stats, 'Estadísticas obtenidas exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }
    
    /**
     * Buscar usuarios
     */
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Verificar autenticación y rol
        $payload = AuthMiddleware::requireAnyRole(['maestro', 'director']);
        
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            Response::error('Parámetro de búsqueda requerido');
        }
        
        $query = $_GET['q'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        
        try {
            $result = $this->userService->searchUsers($query, $page, $perPage);
            Response::paginated($result['items'], $result['pagination'], 'Búsqueda completada exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
?>
