<?php
/**
 * Controlador de autenticación
 */

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthController {
    private $authService;
    
    public function __construct($db) {
        $this->authService = new AuthService($db);
    }
    
    /**
     * Manejar login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Datos JSON inválidos');
        }
        
        // Validar campos requeridos
        $errors = Validator::required($input, ['email', 'password']);
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        try {
            $result = $this->authService->login($input['email'], $input['password']);
            Response::success($result, 'Login exitoso');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 401);
        }
    }
    
    /**
     * Manejar registro
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Datos JSON inválidos');
        }
        
        try {
            $user = $this->authService->register($input);
            Response::success($user, 'Usuario registrado exitosamente', 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Refrescar token
     */
    public function refresh() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['refresh_token'])) {
            Response::error('Token de refresh requerido');
        }
        
        try {
            $result = $this->authService->refreshToken($input['refresh_token']);
            Response::success($result, 'Token refrescado exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 401);
        }
    }
    
    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::methodNotAllowed();
        }
        
        // Obtener usuario del token JWT
        require_once __DIR__ . '/../middleware/AuthMiddleware.php';
        $payload = AuthMiddleware::authenticate();
        
        try {
            $profile = $this->authService->getProfile($payload['user_id']);
            Response::success($profile, 'Perfil obtenido exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }
        
        // Obtener usuario del token JWT
        require_once __DIR__ . '/../middleware/AuthMiddleware.php';
        $payload = AuthMiddleware::authenticate();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            Response::error('Datos JSON inválidos');
        }
        
        // Validar campos requeridos
        $errors = Validator::required($input, ['current_password', 'new_password']);
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        try {
            $this->authService->changePassword(
                $payload['user_id'],
                $input['current_password'],
                $input['new_password']
            );
            Response::success(null, 'Contraseña cambiada exitosamente');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
?>
