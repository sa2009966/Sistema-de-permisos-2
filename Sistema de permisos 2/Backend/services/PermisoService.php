<?php
/**
 * Servicio de permisos
 */

require_once __DIR__ . '/../models/Permiso.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Validator.php';

class PermisoService {
    private $permisoModel;
    private $userModel;
    
    public function __construct($db) {
        $this->permisoModel = new Permiso($db);
        $this->userModel = new User($db);
    }
    
    /**
     * Crear nuevo permiso
     */
    public function createPermiso($permisoData, $userId) {
        // Validar datos
        $errors = Validator::validatePermiso($permisoData);
        
        if (!empty($errors)) {
            throw new Exception('Datos de validación incorrectos: ' . implode(', ', $errors));
        }
        
        // Verificar que el usuario existe
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }
        
        // Preparar datos del permiso
        $permiso = [
            'id_alumno' => $userId,
            'motivo' => Validator::sanitizeString($permisoData['motivo']),
            'fecha_inicio' => $permisoData['fecha_inicio'],
            'fecha_fin' => $permisoData['fecha_fin']
        ];
        
        // Crear permiso
        $permisoId = $this->permisoModel->create($permiso);
        
        if (!$permisoId) {
            throw new Exception('Error al crear el permiso');
        }
        
        // Obtener permiso creado con información del usuario
        return $this->getPermisoById($permisoId);
    }
    
    /**
     * Obtener permiso por ID
     */
    public function getPermisoById($id) {
        $permiso = $this->permisoModel->getById($id);
        
        if (!$permiso) {
            throw new Exception('Permiso no encontrado');
        }
        
        return $permiso;
    }
    
    /**
     * Obtener permisos del usuario
     */
    public function getPermisosByUser($userId, $page = 1, $perPage = 20) {
        $permisos = $this->permisoModel->getByStudent($userId);
        
        // Aplicar paginación manual
        $total = count($permisos);
        $offset = ($page - 1) * $perPage;
        $paginatedPermisos = array_slice($permisos, $offset, $perPage);
        
        return [
            'items' => $paginatedPermisos,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Obtener todos los permisos (para maestros/directores)
     */
    public function getAllPermisos($page = 1, $perPage = 20, $filters = []) {
        $conditions = [];
        
        // Aplicar filtros
        if (isset($filters['estado']) && !empty($filters['estado'])) {
            $conditions['estado'] = $filters['estado'];
        }
        
        if (isset($filters['id_alumno']) && !empty($filters['id_alumno'])) {
            $conditions['id_alumno'] = $filters['id_alumno'];
        }
        
        $result = $this->permisoModel->paginate($page, $perPage, $conditions);
        
        // Obtener información completa de los permisos
        $permisosCompletos = [];
        foreach ($result['items'] as $permiso) {
            $permisoCompleto = $this->permisoModel->getById($permiso['id']);
            if ($permisoCompleto) {
                $permisosCompletos[] = $permisoCompleto;
            }
        }
        
        $result['items'] = $permisosCompletos;
        
        return $result;
    }
    
    /**
     * Actualizar estado del permiso
     */
    public function updatePermisoStatus($id, $estado, $comentarios = '', $aprobadorId = null) {
        // Validar estado
        if (!Validator::permisoEstado($estado)) {
            throw new Exception('Estado de permiso no válido');
        }
        
        // Verificar que el permiso existe
        $permiso = $this->permisoModel->getById($id);
        
        if (!$permiso) {
            throw new Exception('Permiso no encontrado');
        }
        
        // Verificar que el aprobador existe (si se proporciona)
        if ($aprobadorId) {
            $aprobador = $this->userModel->getById($aprobadorId);
            
            if (!$aprobador) {
                throw new Exception('Aprobador no encontrado');
            }
            
            // Verificar que el aprobador tenga permisos para aprobar
            if (!in_array($aprobador['rol'], ['maestro', 'director'])) {
                throw new Exception('El usuario no tiene permisos para aprobar permisos');
            }
        }
        
        // Actualizar estado
        $success = $this->permisoModel->updateStatus($id, $estado, $comentarios, $aprobadorId);
        
        if (!$success) {
            throw new Exception('Error al actualizar el estado del permiso');
        }
        
        // Obtener permiso actualizado
        return $this->getPermisoById($id);
    }
    
    /**
     * Obtener permisos pendientes
     */
    public function getPendingPermisos($page = 1, $perPage = 20) {
        return $this->getAllPermisos($page, $perPage, ['estado' => 'pendiente']);
    }
    
    /**
     * Obtener estadísticas de permisos
     */
    public function getPermisoStats($userId = null) {
        return $this->permisoModel->getStats($userId);
    }
    
    /**
     * Eliminar permiso
     */
    public function deletePermiso($id, $userId) {
        $permiso = $this->permisoModel->getById($id);
        
        if (!$permiso) {
            throw new Exception('Permiso no encontrado');
        }
        
        // Verificar que el usuario sea el propietario del permiso
        if ($permiso['id_alumno'] != $userId) {
            throw new Exception('No tienes permisos para eliminar este permiso');
        }
        
        // Solo permitir eliminar permisos pendientes
        if ($permiso['estado'] !== 'pendiente') {
            throw new Exception('Solo se pueden eliminar permisos pendientes');
        }
        
        return $this->permisoModel->delete($id);
    }
    
    /**
     * Buscar permisos
     */
    public function searchPermisos($query, $page = 1, $perPage = 20) {
        $permisos = $this->permisoModel->search($query, $page, $perPage);
        
        // Obtener información completa de los permisos
        $permisosCompletos = [];
        foreach ($permisos['items'] as $permiso) {
            $permisoCompleto = $this->permisoModel->getById($permiso['id']);
            if ($permisoCompleto) {
                $permisosCompletos[] = $permisoCompleto;
            }
        }
        
        $permisos['items'] = $permisosCompletos;
        
        return $permisos;
    }
}
?>
