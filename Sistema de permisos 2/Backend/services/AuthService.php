<?php
/**
 * Servicio de autenticación
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthService {
    private $userModel;
    
    public function __construct($db) {
        $this->userModel = new User($db);
    }
    
    /**
     * Autenticar usuario
     */
    public function login($email, $password) {
        // Validar datos de entrada
        if (!Validator::email($email)) {
            throw new Exception('Email no válido');
        }
        
        if (!Validator::password($password)) {
            throw new Exception('Contraseña no válida');
        }
        
        // Buscar usuario
        $user = $this->userModel->getByEmail($email);
        
        if (!$user) {
            throw new Exception('Credenciales incorrectas');
        }
        
        // Verificar contraseña
        if (!password_verify($password, $user['contraseña_hash'])) {
            throw new Exception('Credenciales incorrectas');
        }
        
        // Verificar que el usuario esté activo
        if (!$user['activo']) {
            throw new Exception('Usuario inactivo');
        }
        
        // Generar tokens
        $accessToken = JWT::generateAccessToken($user);
        $refreshToken = JWT::generateRefreshToken($user);
        
        // Remover datos sensibles
        unset($user['contraseña_hash']);
        
        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($userData) {
        // Validar datos
        $errors = Validator::validateUser($userData);
        
        if (!empty($errors)) {
            throw new Exception('Datos de validación incorrectos: ' . implode(', ', $errors));
        }
        
        // Verificar si el email ya existe
        if ($this->userModel->emailExists($userData['correo_institucional'])) {
            throw new Exception('El email ya está registrado');
        }
        
        // Verificar si el código de estudiante ya existe
        if ($this->userModel->codigoExists($userData['codigo_estudiante'])) {
            throw new Exception('El código de estudiante ya está registrado');
        }
        
        // Preparar datos del usuario
        $user = [
            'nombre' => Validator::sanitizeString($userData['nombre']),
            'apellidos' => Validator::sanitizeString($userData['apellidos']),
            'correo_institucional' => Validator::sanitizeString($userData['correo_institucional']),
            'codigo_estudiante' => Validator::sanitizeString($userData['codigo_estudiante']),
            'contraseña_hash' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'rol' => Validator::sanitizeString($userData['rol'])
        ];
        
        // Crear usuario
        $userId = $this->userModel->create($user);
        
        if (!$userId) {
            throw new Exception('Error al crear el usuario');
        }
        
        // Obtener usuario creado
        $newUser = $this->userModel->getById($userId);
        unset($newUser['contraseña_hash']);
        
        return $newUser;
    }
    
    /**
     * Refrescar token de acceso
     */
    public function refreshToken($refreshToken) {
        $payload = JWT::verify($refreshToken);
        
        if (!$payload || $payload['type'] !== 'refresh') {
            throw new Exception('Token de refresh inválido');
        }
        
        // Obtener usuario
        $user = $this->userModel->getById($payload['user_id']);
        
        if (!$user || !$user['activo']) {
            throw new Exception('Usuario no válido');
        }
        
        // Generar nuevo access token
        $accessToken = JWT::generateAccessToken($user);
        
        return [
            'access_token' => $accessToken
        ];
    }
    
    /**
     * Obtener perfil del usuario autenticado
     */
    public function getProfile($userId) {
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        unset($user['contraseña_hash']);
        return $user;
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Obtener usuario
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        // Verificar contraseña actual
        if (!password_verify($currentPassword, $user['contraseña_hash'])) {
            throw new Exception('Contraseña actual incorrecta');
        }
        
        // Validar nueva contraseña
        if (!Validator::password($newPassword)) {
            throw new Exception('La nueva contraseña debe tener al menos 6 caracteres');
        }
        
        // Actualizar contraseña
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->userModel->updatePassword($userId, $newPasswordHash);
    }
}
?>
