<?php
/**
 * Clase User para manejo de usuarios
 */

require_once __DIR__ . '/../models/BaseModel.php';

class User extends BaseModel {

    public $id;
    public $nombre;
    public $apellidos;
    public $correo_institucional;
    public $codigo_estudiante;
    public $contraseña_hash;
    public $rol;
    public $fecha_registro;
    public $activo;

    public function __construct($db) {
        parent::__construct($db);
        $this->table_name = "usuarios";
    }

    // Crear nuevo usuario
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, apellidos, correo_institucional, codigo_estudiante, contraseña_hash, rol) 
                  VALUES (:nombre, :apellidos, :correo_institucional, :codigo_estudiante, :contraseña_hash, :rol)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellidos = htmlspecialchars(strip_tags($this->apellidos));
        $this->correo_institucional = htmlspecialchars(strip_tags($this->correo_institucional));
        $this->codigo_estudiante = htmlspecialchars(strip_tags($this->codigo_estudiante));
        $this->rol = htmlspecialchars(strip_tags($this->rol));

        // Bind parameters
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellidos", $this->apellidos);
        $stmt->bindParam(":correo_institucional", $this->correo_institucional);
        $stmt->bindParam(":codigo_estudiante", $this->codigo_estudiante);
        $stmt->bindParam(":contraseña_hash", $this->contraseña_hash);
        $stmt->bindParam(":rol", $this->rol);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verificar credenciales de login
    public function login($email, $password) {
        $query = "SELECT id, nombre, apellidos, correo_institucional, codigo_estudiante, contraseña_hash, rol, activo 
                  FROM " . $this->table_name . " 
                  WHERE correo_institucional = :email AND activo = true";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $row['contraseña_hash'])) {
                $this->id = $row['id'];
                $this->nombre = $row['nombre'];
                $this->apellidos = $row['apellidos'];
                $this->correo_institucional = $row['correo_institucional'];
                $this->codigo_estudiante = $row['codigo_estudiante'];
                $this->rol = $row['rol'];
                $this->activo = $row['activo'];
                return true;
            }
        }
        return false;
    }

    // Obtener usuario por ID
    public function getById($id) {
        $query = "SELECT id, nombre, apellidos, correo_institucional, codigo_estudiante, rol, fecha_registro, activo 
                  FROM " . $this->table_name . " 
                  WHERE id = :id AND activo = true";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->apellidos = $row['apellidos'];
            $this->correo_institucional = $row['correo_institucional'];
            $this->codigo_estudiante = $row['codigo_estudiante'];
            $this->rol = $row['rol'];
            $this->fecha_registro = $row['fecha_registro'];
            $this->activo = $row['activo'];
            return true;
        }
        return false;
    }

    // Verificar si el email ya existe
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE correo_institucional = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Verificar si el código de estudiante ya existe
    public function codigoExists($codigo) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE codigo_estudiante = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Obtener todos los alumnos
    public function getAllStudents() {
        $query = "SELECT id, nombre, apellidos, correo_institucional, codigo_estudiante, fecha_registro 
                  FROM " . $this->table_name . " 
                  WHERE rol = 'alumno' AND activo = true 
                  ORDER BY apellidos, nombre";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas del usuario
    public function getUserStats($userId) {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM permisos WHERE id_alumno = :user_id) as total_permisos,
                    (SELECT COUNT(*) FROM permisos WHERE id_alumno = :user_id AND estado = 'aprobado') as permisos_aprobados,
                    (SELECT COUNT(*) FROM permisos WHERE id_alumno = :user_id AND estado = 'pendiente') as permisos_pendientes,
                    (SELECT COUNT(*) FROM asistencias WHERE id_alumno = :user_id) as total_asistencias,
                    (SELECT COUNT(*) FROM asistencias WHERE id_alumno = :user_id AND estado_asistencia = 'presente') as asistencias_presente,
                    (SELECT COUNT(*) FROM asistencias WHERE id_alumno = :user_id AND estado_asistencia = 'ausente') as asistencias_ausente";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener usuario por email
    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE correo_institucional = :email AND activo = true";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    // Crear usuario y retornar ID
    public function create($userData) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, apellidos, correo_institucional, codigo_estudiante, contraseña_hash, rol) 
                  VALUES (:nombre, :apellidos, :correo_institucional, :codigo_estudiante, :contraseña_hash, :rol)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":nombre", $userData['nombre']);
        $stmt->bindParam(":apellidos", $userData['apellidos']);
        $stmt->bindParam(":correo_institucional", $userData['correo_institucional']);
        $stmt->bindParam(":codigo_estudiante", $userData['codigo_estudiante']);
        $stmt->bindParam(":contraseña_hash", $userData['contraseña_hash']);
        $stmt->bindParam(":rol", $userData['rol']);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    // Actualizar usuario
    public function update($id, $updateData) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($updateData as $field => $value) {
            $fields[] = "$field = :$field";
            $params[$field] = $value;
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        return $stmt->execute();
    }
    
    // Actualizar contraseña
    public function updatePassword($id, $passwordHash) {
        $query = "UPDATE " . $this->table_name . " SET contraseña_hash = :password_hash WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password_hash", $passwordHash);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }
    
    // Buscar usuarios
    public function search($query, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $searchQuery = "SELECT * FROM " . $this->table_name . " 
                       WHERE (nombre ILIKE :query OR apellidos ILIKE :query OR correo_institucional ILIKE :query OR codigo_estudiante ILIKE :query)
                       AND activo = true
                       ORDER BY apellidos, nombre
                       LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($searchQuery);
        $searchTerm = "%$query%";
        $stmt->bindParam(":query", $searchTerm);
        $stmt->bindParam(":limit", $perPage, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contar total
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                      WHERE (nombre ILIKE :query OR apellidos ILIKE :query OR correo_institucional ILIKE :query OR codigo_estudiante ILIKE :query)
                      AND activo = true";
        
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->bindParam(":query", $searchTerm);
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return [
            'items' => $items,
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
}
?>
