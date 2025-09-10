<?php
/**
 * Servicio de usuarios
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Validator.php';

class UserService {
    private $userModel;
    
    public function __construct($db) {
        $this->userModel = new User($db);
    }
    
    /**
     * Obtener todos los usuarios con paginación
     */
    public function getAllUsers($page = 1, $perPage = 20, $filters = []) {
        $conditions = [];
        
        // Aplicar filtros
        if (isset($filters['rol']) && !empty($filters['rol'])) {
            $conditions['rol'] = $filters['rol'];
        }
        
        if (isset($filters['activo']) && $filters['activo'] !== '') {
            $conditions['activo'] = $filters['activo'] === 'true' ? true : false;
        }
        
        $result = $this->userModel->paginate($page, $perPage, $conditions);
        
        // Remover contraseñas de los resultados
        foreach ($result['items'] as &$user) {
            unset($user['contraseña_hash']);
        }
        
        return $result;
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getUserById($id) {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        unset($user['contraseña_hash']);
        return $user;
    }
    
    /**
     * Obtener todos los estudiantes
     */
    public function getAllStudents() {
        $students = $this->userModel->getAllStudents();
        
        // Remover contraseñas
        foreach ($students as &$student) {
            unset($student['contraseña_hash']);
        }
        
        return $students;
    }
    
    /**
     * Actualizar usuario
     */
    public function updateUser($id, $userData) {
        // Verificar que el usuario existe
        $existingUser = $this->userModel->getById($id);
        
        if (!$existingUser) {
            throw new Exception('Usuario no encontrado');
        }
        
        // Validar datos
        $errors = [];
        
        if (isset($userData['nombre']) && !Validator::textLength($userData['nombre'], 2, 100)) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 100 caracteres';
        }
        
        if (isset($userData['apellidos']) && !Validator::textLength($userData['apellidos'], 2, 100)) {
            $errors['apellidos'] = 'Los apellidos deben tener entre 2 y 100 caracteres';
        }
        
        if (isset($userData['correo_institucional'])) {
            if (!Validator::email($userData['correo_institucional'])) {
                $errors['correo_institucional'] = 'Email no válido';
            } elseif ($userData['correo_institucional'] !== $existingUser['correo_institucional'] && 
                     $this->userModel->emailExists($userData['correo_institucional'])) {
                $errors['correo_institucional'] = 'El email ya está registrado';
            }
        }
        
        if (isset($userData['rol']) && !Validator::role($userData['rol'])) {
            $errors['rol'] = 'Rol no válido';
        }
        
        if (!empty($errors)) {
            throw new Exception('Datos de validación incorrectos: ' . implode(', ', $errors));
        }
        
        // Preparar datos para actualización
        $updateData = [];
        
        if (isset($userData['nombre'])) {
            $updateData['nombre'] = Validator::sanitizeString($userData['nombre']);
        }
        
        if (isset($userData['apellidos'])) {
            $updateData['apellidos'] = Validator::sanitizeString($userData['apellidos']);
        }
        
        if (isset($userData['correo_institucional'])) {
            $updateData['correo_institucional'] = Validator::sanitizeString($userData['correo_institucional']);
        }
        
        if (isset($userData['rol'])) {
            $updateData['rol'] = Validator::sanitizeString($userData['rol']);
        }
        
        if (isset($userData['activo'])) {
            $updateData['activo'] = $userData['activo'] === 'true' || $userData['activo'] === true;
        }
        
        // Actualizar usuario
        $success = $this->userModel->update($id, $updateData);
        
        if (!$success) {
            throw new Exception('Error al actualizar el usuario');
        }
        
        // Obtener usuario actualizado
        return $this->getUserById($id);
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function deleteUser($id) {
        $user = $this->userModel->getById($id);
        
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        // Soft delete - marcar como inactivo
        return $this->userModel->update($id, ['activo' => false]);
    }
    
    /**
     * Obtener estadísticas del usuario
     */
    public function getUserStats($userId) {
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        return $this->userModel->getUserStats($userId);
    }
    
    /**
     * Buscar usuarios
     */
    public function searchUsers($query, $page = 1, $perPage = 20) {
        $users = $this->userModel->search($query, $page, $perPage);
        
        // Remover contraseñas
        foreach ($users['items'] as &$user) {
            unset($user['contraseña_hash']);
        }
        
        return $users;
    }
}
?>
