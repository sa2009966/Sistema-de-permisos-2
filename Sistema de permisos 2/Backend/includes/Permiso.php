<?php
/**
 * Clase Permiso para manejo de permisos
 */

require_once __DIR__ . '/../models/BaseModel.php';

class Permiso extends BaseModel {

    public $id;
    public $id_alumno;
    public $motivo;
    public $fecha_solicitud;
    public $fecha_inicio;
    public $fecha_fin;
    public $estado;
    public $comentarios;
    public $id_aprobador;
    public $fecha_aprobacion;

    public function __construct($db) {
        parent::__construct($db);
        $this->table_name = "permisos";
    }

    // Crear nuevo permiso
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_alumno, motivo, fecha_inicio, fecha_fin) 
                  VALUES (:id_alumno, :motivo, :fecha_inicio, :fecha_fin)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->id_alumno = htmlspecialchars(strip_tags($this->id_alumno));
        $this->motivo = htmlspecialchars(strip_tags($this->motivo));
        $this->fecha_inicio = htmlspecialchars(strip_tags($this->fecha_inicio));
        $this->fecha_fin = htmlspecialchars(strip_tags($this->fecha_fin));

        // Bind parameters
        $stmt->bindParam(":id_alumno", $this->id_alumno);
        $stmt->bindParam(":motivo", $this->motivo);
        $stmt->bindParam(":fecha_inicio", $this->fecha_inicio);
        $stmt->bindParam(":fecha_fin", $this->fecha_fin);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar estado del permiso
    public function updateStatus($id, $estado, $comentarios = '', $id_aprobador = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado, comentarios = :comentarios, id_aprobador = :id_aprobador, fecha_aprobacion = CURRENT_TIMESTAMP 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":comentarios", $comentarios);
        $stmt->bindParam(":id_aprobador", $id_aprobador);

        return $stmt->execute();
    }

    // Obtener permisos por alumno
    public function getByStudent($id_alumno) {
        $query = "SELECT p.*, u.nombre, u.apellidos, u.codigo_estudiante,
                         a.nombre as aprobador_nombre, a.apellidos as aprobador_apellidos
                  FROM " . $this->table_name . " p
                  LEFT JOIN usuarios u ON p.id_alumno = u.id
                  LEFT JOIN usuarios a ON p.id_aprobador = a.id
                  WHERE p.id_alumno = :id_alumno
                  ORDER BY p.fecha_solicitud DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_alumno", $id_alumno);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los permisos (para maestros/directores)
    public function getAll($estado = null) {
        $query = "SELECT p.*, u.nombre, u.apellidos, u.codigo_estudiante,
                         a.nombre as aprobador_nombre, a.apellidos as aprobador_apellidos
                  FROM " . $this->table_name . " p
                  LEFT JOIN usuarios u ON p.id_alumno = u.id
                  LEFT JOIN usuarios a ON p.id_aprobador = a.id";
        
        if ($estado) {
            $query .= " WHERE p.estado = :estado";
        }
        
        $query .= " ORDER BY p.fecha_solicitud DESC";

        $stmt = $this->conn->prepare($query);
        if ($estado) {
            $stmt->bindParam(":estado", $estado);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener permiso por ID
    public function getById($id) {
        $query = "SELECT p.*, u.nombre, u.apellidos, u.codigo_estudiante,
                         a.nombre as aprobador_nombre, a.apellidos as aprobador_apellidos
                  FROM " . $this->table_name . " p
                  LEFT JOIN usuarios u ON p.id_alumno = u.id
                  LEFT JOIN usuarios a ON p.id_aprobador = a.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    // Obtener estadísticas de permisos
    public function getStats($id_alumno = null) {
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                    COUNT(CASE WHEN estado = 'aprobado' THEN 1 END) as aprobados,
                    COUNT(CASE WHEN estado = 'rechazado' THEN 1 END) as rechazados
                  FROM " . $this->table_name;
        
        if ($id_alumno) {
            $query .= " WHERE id_alumno = :id_alumno";
        }

        $stmt = $this->conn->prepare($query);
        if ($id_alumno) {
            $stmt->bindParam(":id_alumno", $id_alumno);
        }
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener permisos pendientes
    public function getPending() {
        return $this->getAll('pendiente');
    }
    
    // Crear permiso y retornar ID
    public function create($permisoData) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_alumno, motivo, fecha_inicio, fecha_fin) 
                  VALUES (:id_alumno, :motivo, :fecha_inicio, :fecha_fin)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":id_alumno", $permisoData['id_alumno']);
        $stmt->bindParam(":motivo", $permisoData['motivo']);
        $stmt->bindParam(":fecha_inicio", $permisoData['fecha_inicio']);
        $stmt->bindParam(":fecha_fin", $permisoData['fecha_fin']);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    // Buscar permisos
    public function search($query, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $searchQuery = "SELECT p.*, u.nombre, u.apellidos, u.codigo_estudiante,
                               a.nombre as aprobador_nombre, a.apellidos as aprobador_apellidos
                        FROM " . $this->table_name . " p
                        LEFT JOIN usuarios u ON p.id_alumno = u.id
                        LEFT JOIN usuarios a ON p.id_aprobador = a.id
                        WHERE p.motivo ILIKE :query
                        ORDER BY p.fecha_solicitud DESC
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
                      WHERE motivo ILIKE :query";
        
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
