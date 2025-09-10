<?php
/**
 * Archivo de ejemplo para configuración de base de datos
 * Copiar este archivo como database.php y configurar con tus datos
 */

class Database {
    // Configuración de la base de datos PostgreSQL
    private $host = 'localhost';           // Host de la base de datos
    private $db_name = 'sistema_permisos'; // Nombre de la base de datos
    private $username = 'postgres';        // Usuario de PostgreSQL
    private $password = 'tu_contraseña';   // Contraseña de PostgreSQL
    private $port = '5432';                // Puerto de PostgreSQL (por defecto 5432)
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

/*
INSTRUCCIONES DE CONFIGURACIÓN:

1. Copia este archivo como database.php:
   cp config/database.example.php config/database.php

2. Edita config/database.php con tus datos:
   - host: Dirección del servidor PostgreSQL
   - db_name: Nombre de tu base de datos
   - username: Usuario de PostgreSQL
   - password: Contraseña de PostgreSQL
   - port: Puerto (por defecto 5432)

3. Asegúrate de que la base de datos existe:
   sudo -u postgres createdb sistema_permisos

4. Ejecuta el script de creación de tablas:
   psql -U postgres -d sistema_permisos -f database/schema.sql

5. Verifica la conexión accediendo a index.php
*/
?>
