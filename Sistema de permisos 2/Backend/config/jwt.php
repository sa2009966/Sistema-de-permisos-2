<?php
/**
 * Configuración JWT para autenticación
 */

class JWTConfig {
    // Clave secreta para firmar tokens (en producción usar una clave más segura)
    const SECRET_KEY = 'tu_clave_secreta_muy_segura_aqui_2024';
    
    // Algoritmo de encriptación
    const ALGORITHM = 'HS256';
    
    // Tiempo de expiración del token (en segundos)
    const ACCESS_TOKEN_EXPIRY = 3600; // 1 hora
    const REFRESH_TOKEN_EXPIRY = 604800; // 7 días
    
    // Issuer y Audience
    const ISSUER = 'sistema-permisos';
    const AUDIENCE = 'sistema-permisos-users';
    
    /**
     * Obtener la clave secreta
     */
    public static function getSecretKey() {
        return self::SECRET_KEY;
    }
    
    /**
     * Obtener el algoritmo
     */
    public static function getAlgorithm() {
        return self::ALGORITHM;
    }
    
    /**
     * Obtener tiempo de expiración del access token
     */
    public static function getAccessTokenExpiry() {
        return self::ACCESS_TOKEN_EXPIRY;
    }
    
    /**
     * Obtener tiempo de expiración del refresh token
     */
    public static function getRefreshTokenExpiry() {
        return self::REFRESH_TOKEN_EXPIRY;
    }
    
    /**
     * Obtener issuer
     */
    public static function getIssuer() {
        return self::ISSUER;
    }
    
    /**
     * Obtener audience
     */
    public static function getAudience() {
        return self::AUDIENCE;
    }
}
?>
