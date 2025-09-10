<?php
/**
 * Clase Asistencia para manejo de asistencias
 */

require_once 'config/database.php';

class Asistencia {
    private $conn;
    private $table_name = "asistencias";

    public $id;
    public $id_alumno;
    public $fecha;
    public $estado_asistencia;
    public $observaciones;
    public $fecha_registro;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear nueva asistencia
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_alumno, fecha, estado_asistencia, observaciones) 
                  VALUES (:id_alumno, :fecha, :estado_asistencia, :observaciones)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->id_alumno = htmlspecialchars(strip_tags($this->id_alumno));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha));
        $this->estado_asistencia = htmlspecialchars(strip_tags($this->estado_asistencia));
        $this->observaciones = htmlspecialchars(strip_tags($this->observaciones));

        // Bind parameters
        $stmt->bindParam(":id_alumno", $this->id_alumno);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":estado_asistencia", $this->estado_asistencia);
        $stmt->bindParam(":observaciones", $this->observaciones);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtener asistencias por alumno
    public function getByStudent($id_alumno, $limit = null) {
        $query = "SELECT a.*, u.nombre, u.apellidos, u.codigo_estudiante
                  FROM " . $this->table_name . " a
                  LEFT JOIN usuarios u ON a.id_alumno = u.id
                  WHERE a.id_alumno = :id_alumno
                  ORDER BY a.fecha DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_alumno", $id_alumno);
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todas las asistencias (para maestros/directores)
    public function getAll($fecha_desde = null, $fecha_hasta = null) {
        $query = "SELECT a.*, u.nombre, u.apellidos, u.codigo_estudiante
                  FROM " . $this->table_name . " a
                  LEFT JOIN usuarios u ON a.id_alumno = u.id";
        
        $conditions = [];
        if ($fecha_desde) {
            $conditions[] = "a.fecha >= :fecha_desde";
        }
        if ($fecha_hasta) {
            $conditions[] = "a.fecha <= :fecha_hasta";
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY a.fecha DESC, u.apellidos, u.nombre";

        $stmt = $this->conn->prepare($query);
        if ($fecha_desde) {
            $stmt->bindParam(":fecha_desde", $fecha_desde);
        }
        if ($fecha_hasta) {
            $stmt->bindParam(":fecha_hasta", $fecha_hasta);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas de asistencias
    public function getStats($id_alumno = null, $fecha_desde = null, $fecha_hasta = null) {
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN estado_asistencia = 'presente' THEN 1 END) as presentes,
                    COUNT(CASE WHEN estado_asistencia = 'ausente' THEN 1 END) as ausentes,
                    COUNT(CASE WHEN estado_asistencia = 'tardanza' THEN 1 END) as tardanzas,
                    COUNT(CASE WHEN estado_asistencia = 'justificado' THEN 1 END) as justificados
                  FROM " . $this->table_name;
        
        $conditions = [];
        if ($id_alumno) {
            $conditions[] = "id_alumno = :id_alumno";
        }
        if ($fecha_desde) {
            $conditions[] = "fecha >= :fecha_desde";
        }
        if ($fecha_hasta) {
            $conditions[] = "fecha <= :fecha_hasta";
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($query);
        if ($id_alumno) {
            $stmt->bindParam(":id_alumno", $id_alumno);
        }
        if ($fecha_desde) {
            $stmt->bindParam(":fecha_desde", $fecha_desde);
        }
        if ($fecha_hasta) {
            $stmt->bindParam(":fecha_hasta", $fecha_hasta);
        }
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verificar si ya existe asistencia para un alumno en una fecha
    public function existsForDate($id_alumno, $fecha) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE id_alumno = :id_alumno AND fecha = :fecha";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_alumno", $id_alumno);
        $stmt->bindParam(":fecha", $fecha);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Obtener asistencias por rango de fechas
    public function getByDateRange($fecha_desde, $fecha_hasta) {
        $query = "SELECT a.*, u.nombre, u.apellidos, u.codigo_estudiante
                  FROM " . $this->table_name . " a
                  LEFT JOIN usuarios u ON a.id_alumno = u.id
                  WHERE a.fecha BETWEEN :fecha_desde AND :fecha_hasta
                  ORDER BY a.fecha DESC, u.apellidos, u.nombre";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha_desde", $fecha_desde);
        $stmt->bindParam(":fecha_hasta", $fecha_hasta);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
