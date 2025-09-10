<?php
/**
 * Modelo base con funcionalidades comunes
 */

require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected $conn;
    protected $table_name;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Ejecutar consulta preparada
     */
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Error en la base de datos");
        }
    }
    
    /**
     * Obtener todos los registros
     */
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->executeQuery($query, ['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: false;
    }
    
    /**
     * Contar registros
     */
    public function count($conditions = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "$field = :$field";
            }
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->executeQuery($query, $conditions);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
    
    /**
     * Eliminar por ID
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->executeQuery($query, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si existe un registro
     */
    public function exists($conditions) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE ";
        
        $whereClause = [];
        foreach ($conditions as $field => $value) {
            $whereClause[] = "$field = :$field";
        }
        $query .= implode(' AND ', $whereClause);
        
        $stmt = $this->executeQuery($query, $conditions);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'] > 0;
    }
    
    /**
     * Obtener registros con paginación
     */
    public function paginate($page = 1, $perPage = 20, $conditions = []) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM " . $this->table_name;
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "$field = :$field";
            }
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($conditions as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->count($conditions);
        
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
