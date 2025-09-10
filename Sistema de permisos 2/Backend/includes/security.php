<?php
/**
 * Middleware de seguridad para el sistema de permisos
 */

session_start();

// Función para verificar si el usuario está autenticado
function requireAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado. Debe iniciar sesión.']);
        exit();
    }
}

// Función para verificar roles específicos
function requireRole($allowedRoles) {
    requireAuth();
    
    if (!in_array($_SESSION['user_rol'], $allowedRoles)) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado. Rol insuficiente.']);
        exit();
    }
}

// Función para sanitizar inputs
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Función para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para validar que el usuario sea el propietario del recurso o tenga permisos de administrador
function requireOwnershipOrAdmin($resourceUserId) {
    requireAuth();
    
    if ($_SESSION['user_id'] != $resourceUserId && !in_array($_SESSION['user_rol'], ['maestro', 'director'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado. No tiene permisos para este recurso.']);
        exit();
    }
}

// Función para generar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Función para establecer headers de seguridad
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Content-Type: application/json; charset=utf-8');
}

// Función para log de seguridad
function logSecurityEvent($event, $details = '') {
    $logEntry = date('Y-m-d H:i:s') . " - " . $event . " - " . $details . "\n";
    file_put_contents('../logs/security.log', $logEntry, FILE_APPEND | LOCK_EX);
}

// Función para validar datos de entrada
function validateRequiredFields($fields, $data) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode(['error' => 'Campos requeridos faltantes: ' . implode(', ', $missing)]);
        exit();
    }
}

// Función para manejar errores de manera segura
function handleError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    logSecurityEvent('ERROR', $message);
    exit();
}

// Función para respuesta exitosa
function sendSuccessResponse($data = null, $message = 'Operación exitosa') {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}
?>
