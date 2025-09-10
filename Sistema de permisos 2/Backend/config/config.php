<?php
/**
 * Configuración general del sistema
 */

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Permisos');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production

// Configuración de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores según el entorno
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Configuración de logs
define('LOG_DIR', __DIR__ . '/../logs/');
define('SECURITY_LOG', LOG_DIR . 'security.log');
define('ERROR_LOG', LOG_DIR . 'error.log');

// Configuración de seguridad
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// Configuración de validación
define('MAX_MOTIVO_LENGTH', 500);
define('MIN_MOTIVO_LENGTH', 10);

// Configuración de paginación
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// Configuración de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Configuración de notificaciones (para futuras implementaciones)
define('EMAIL_ENABLED', false);
define('EMAIL_FROM', 'noreply@sistema.edu');
define('EMAIL_FROM_NAME', 'Sistema de Permisos');

// Configuración de caché (para futuras implementaciones)
define('CACHE_ENABLED', false);
define('CACHE_TTL', 300); // 5 minutos

// Función para obtener configuración
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Función para verificar si estamos en desarrollo
function isDevelopment() {
    return APP_ENV === 'development';
}

// Función para verificar si estamos en producción
function isProduction() {
    return APP_ENV === 'production';
}
?>
