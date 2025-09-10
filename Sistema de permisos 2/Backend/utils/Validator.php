<?php
/**
 * Clase para validación de datos
 */

class Validator {
    
    /**
     * Validar email
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar contraseña
     */
    public static function password($password, $minLength = 6) {
        return strlen($password) >= $minLength;
    }
    
    /**
     * Validar campos requeridos
     */
    public static function required($data, $fields) {
        $errors = [];
        
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[$field] = "El campo {$field} es requerido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitizar string
     */
    public static function sanitizeString($string) {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar rol
     */
    public static function role($role) {
        $validRoles = ['alumno', 'maestro', 'director'];
        return in_array($role, $validRoles);
    }
    
    /**
     * Validar estado de permiso
     */
    public static function permisoEstado($estado) {
        $validEstados = ['pendiente', 'aprobado', 'rechazado'];
        return in_array($estado, $validEstados);
    }
    
    /**
     * Validar fecha
     */
    public static function date($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validar que una fecha sea futura
     */
    public static function futureDate($date, $format = 'Y-m-d') {
        if (!self::date($date, $format)) {
            return false;
        }
        
        $inputDate = DateTime::createFromFormat($format, $date);
        $today = new DateTime();
        
        return $inputDate > $today;
    }
    
    /**
     * Validar rango de fechas
     */
    public static function dateRange($startDate, $endDate, $format = 'Y-m-d') {
        if (!self::date($startDate, $format) || !self::date($endDate, $format)) {
            return false;
        }
        
        $start = DateTime::createFromFormat($format, $startDate);
        $end = DateTime::createFromFormat($format, $endDate);
        
        return $start <= $end;
    }
    
    /**
     * Validar longitud de texto
     */
    public static function textLength($text, $min = 0, $max = null) {
        $length = strlen($text);
        
        if ($length < $min) {
            return false;
        }
        
        if ($max !== null && $length > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar número entero
     */
    public static function integer($value, $min = null, $max = null) {
        if (!is_numeric($value) || (int)$value != $value) {
            return false;
        }
        
        $intValue = (int)$value;
        
        if ($min !== null && $intValue < $min) {
            return false;
        }
        
        if ($max !== null && $intValue > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar datos de usuario
     */
    public static function validateUser($data) {
        $errors = [];
        
        // Campos requeridos
        $requiredFields = ['nombre', 'apellidos', 'correo_institucional', 'password', 'codigo_estudiante', 'rol'];
        $requiredErrors = self::required($data, $requiredFields);
        $errors = array_merge($errors, $requiredErrors);
        
        // Validar email
        if (isset($data['correo_institucional']) && !self::email($data['correo_institucional'])) {
            $errors['correo_institucional'] = 'Email no válido';
        }
        
        // Validar contraseña
        if (isset($data['password']) && !self::password($data['password'])) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        // Validar rol
        if (isset($data['rol']) && !self::role($data['rol'])) {
            $errors['rol'] = 'Rol no válido';
        }
        
        // Validar longitud de campos
        if (isset($data['nombre']) && !self::textLength($data['nombre'], 2, 100)) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 100 caracteres';
        }
        
        if (isset($data['apellidos']) && !self::textLength($data['apellidos'], 2, 100)) {
            $errors['apellidos'] = 'Los apellidos deben tener entre 2 y 100 caracteres';
        }
        
        return $errors;
    }
    
    /**
     * Validar datos de permiso
     */
    public static function validatePermiso($data) {
        $errors = [];
        
        // Campos requeridos
        $requiredFields = ['motivo', 'fecha_inicio', 'fecha_fin'];
        $requiredErrors = self::required($data, $requiredFields);
        $errors = array_merge($errors, $requiredErrors);
        
        // Validar fechas
        if (isset($data['fecha_inicio']) && !self::date($data['fecha_inicio'])) {
            $errors['fecha_inicio'] = 'Fecha de inicio no válida';
        }
        
        if (isset($data['fecha_fin']) && !self::date($data['fecha_fin'])) {
            $errors['fecha_fin'] = 'Fecha de fin no válida';
        }
        
        // Validar rango de fechas
        if (isset($data['fecha_inicio']) && isset($data['fecha_fin']) && 
            self::date($data['fecha_inicio']) && self::date($data['fecha_fin'])) {
            if (!self::dateRange($data['fecha_inicio'], $data['fecha_fin'])) {
                $errors['fecha_fin'] = 'La fecha de fin debe ser posterior a la fecha de inicio';
            }
        }
        
        // Validar motivo
        if (isset($data['motivo']) && !self::textLength($data['motivo'], 10, 500)) {
            $errors['motivo'] = 'El motivo debe tener entre 10 y 500 caracteres';
        }
        
        return $errors;
    }
}
?>
