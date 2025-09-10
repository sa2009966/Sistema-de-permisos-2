<?php
/**
 * Clase para manejo de JWT (JSON Web Tokens)
 */

require_once __DIR__ . '/../config/jwt.php';

class JWT {
    
    /**
     * Generar un token JWT
     */
    public static function generate($payload, $expiry = null) {
        $header = [
            'typ' => 'JWT',
            'alg' => JWTConfig::getAlgorithm()
        ];
        
        $defaultPayload = [
            'iss' => JWTConfig::getIssuer(),
            'aud' => JWTConfig::getAudience(),
            'iat' => time(),
            'exp' => time() + ($expiry ?? JWTConfig::getAccessTokenExpiry())
        ];
        
        $payload = array_merge($defaultPayload, $payload);
        
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWTConfig::getSecretKey(), true);
        $signatureEncoded = self::base64UrlEncode($signature);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }
    
    /**
     * Verificar y decodificar un token JWT
     */
    public static function verify($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Verificar firma
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, JWTConfig::getSecretKey(), true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        
        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Extraer token del header Authorization
     */
    public static function extractFromHeader() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            return null;
        }
        
        $authHeader = $headers['Authorization'];
        
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return null;
        }
        
        return substr($authHeader, 7);
    }
    
    /**
     * Generar access token
     */
    public static function generateAccessToken($user) {
        return self::generate([
            'user_id' => $user['id'],
            'email' => $user['correo_institucional'],
            'rol' => $user['rol'],
            'type' => 'access'
        ]);
    }
    
    /**
     * Generar refresh token
     */
    public static function generateRefreshToken($user) {
        return self::generate([
            'user_id' => $user['id'],
            'type' => 'refresh'
        ], JWTConfig::getRefreshTokenExpiry());
    }
    
    /**
     * Codificar en base64 URL-safe
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decodificar base64 URL-safe
     */
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
?>
