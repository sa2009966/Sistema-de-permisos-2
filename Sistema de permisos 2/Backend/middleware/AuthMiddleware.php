<?php
/**
 * Middleware de autenticación JWT
 */

require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware {
    
    /**
     * Verificar autenticación
     */
    public static function authenticate() {
        $token = JWT::extractFromHeader();
        
        if (!$token) {
            Response::unauthorized('Token de acceso requerido');
        }
        
        $payload = JWT::verify($token);
        
        if (!$payload) {
            Response::unauthorized('Token inválido o expirado');
        }
        
        if (!isset($payload['user_id']) || $payload['type'] !== 'access') {
            Response::unauthorized('Token inválido');
        }
        
        return $payload;
    }
    
    /**
     * Verificar rol específico
     */
    public static function requireRole($requiredRole) {
        $payload = self::authenticate();
        
        if (!isset($payload['rol']) || $payload['rol'] !== $requiredRole) {
            Response::forbidden('Acceso denegado: rol insuficiente');
        }
        
        return $payload;
    }
    
    /**
     * Verificar múltiples roles
     */
    public static function requireAnyRole($roles) {
        $payload = self::authenticate();
        
        if (!isset($payload['rol']) || !in_array($payload['rol'], $roles)) {
            Response::forbidden('Acceso denegado: rol insuficiente');
        }
        
        return $payload;
    }
    
    /**
     * Verificar que el usuario sea el propietario del recurso o tenga rol de administrador
     */
    public static function requireOwnershipOrAdmin($resourceUserId) {
        $payload = self::authenticate();
        
        $userRoles = ['maestro', 'director'];
        
        if ($payload['user_id'] != $resourceUserId && !in_array($payload['rol'], $userRoles)) {
            Response::forbidden('Acceso denegado: no tienes permisos para este recurso');
        }
        
        return $payload;
    }
    
    /**
     * Middleware opcional - no falla si no hay token
     */
    public static function optional() {
        $token = JWT::extractFromHeader();
        
        if (!$token) {
            return null;
        }
        
        $payload = JWT::verify($token);
        
        if (!$payload || !isset($payload['user_id']) || $payload['type'] !== 'access') {
            return null;
        }
        
        return $payload;
    }
}
?>
