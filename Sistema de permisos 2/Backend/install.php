<?php
/**
 * Script de instalación del Sistema de Permisos
 * Ejecutar una sola vez para configurar el sistema
 */

// Configuración
$config = [
    'db_host' => 'localhost',
    'db_name' => 'sistema_permisos',
    'db_user' => 'postgres',
    'db_pass' => 'password', // Cambiar por tu contraseña
    'db_port' => '5432'
];

echo "=== INSTALADOR DEL SISTEMA DE PERMISOS ===\n\n";

// Verificar extensiones PHP
echo "1. Verificando extensiones PHP...\n";
$required_extensions = ['pdo', 'pdo_pgsql', 'json', 'session'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "❌ Extensiones faltantes: " . implode(', ', $missing_extensions) . "\n";
    echo "Instale las extensiones requeridas y vuelva a ejecutar.\n";
    exit(1);
}
echo "✅ Extensiones PHP verificadas\n\n";

// Verificar conexión a base de datos
echo "2. Verificando conexión a PostgreSQL...\n";
try {
    $dsn = "pgsql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión a base de datos exitosa\n\n";
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "Verifique la configuración de la base de datos.\n";
    exit(1);
}

// Crear archivo de configuración
echo "3. Creando archivo de configuración...\n";
$config_content = "<?php
/**
 * Configuración de la base de datos PostgreSQL
 */

class Database {
    private \$host = '{$config['db_host']}';
    private \$db_name = '{$config['db_name']}';
    private \$username = '{$config['db_user']}';
    private \$password = '{$config['db_pass']}';
    private \$port = '{$config['db_port']}';
    private \$conn;

    public function getConnection() {
        \$this->conn = null;

        try {
            \$dsn = \"pgsql:host=\" . \$this->host . \";port=\" . \$this->port . \";dbname=\" . \$this->db_name;
            \$this->conn = new PDO(\$dsn, \$this->username, \$this->password);
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException \$exception) {
            echo \"Error de conexión: \" . \$exception->getMessage();
        }

        return \$this->conn;
    }
}
?>";

if (file_put_contents('config/database.php', $config_content)) {
    echo "✅ Archivo de configuración creado\n\n";
} else {
    echo "❌ Error al crear archivo de configuración\n";
    exit(1);
}

// Verificar si las tablas ya existen
echo "4. Verificando estructura de base de datos...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name IN ('usuarios', 'permisos', 'asistencias')");
    $table_count = $stmt->fetchColumn();
    
    if ($table_count == 3) {
        echo "✅ Tablas ya existen\n\n";
    } else {
        echo "⚠️  Tablas no encontradas. Ejecute el script schema.sql manualmente:\n";
        echo "   psql -U {$config['db_user']} -d {$config['db_name']} -f database/schema.sql\n\n";
    }
} catch (PDOException $e) {
    echo "❌ Error al verificar tablas: " . $e->getMessage() . "\n";
    echo "Ejecute el script schema.sql manualmente.\n\n";
}

// Crear directorio de logs
echo "5. Configurando directorio de logs...\n";
if (!is_dir('logs')) {
    if (mkdir('logs', 0755, true)) {
        echo "✅ Directorio de logs creado\n\n";
    } else {
        echo "❌ Error al crear directorio de logs\n";
        exit(1);
    }
} else {
    echo "✅ Directorio de logs ya existe\n\n";
}

// Verificar permisos de escritura
echo "6. Verificando permisos de escritura...\n";
if (is_writable('logs')) {
    echo "✅ Permisos de escritura verificados\n\n";
} else {
    echo "❌ Sin permisos de escritura en directorio logs\n";
    echo "Ejecute: chmod 755 logs/\n";
    exit(1);
}

// Verificar archivo .htaccess
echo "7. Verificando configuración de Apache...\n";
if (file_exists('.htaccess')) {
    echo "✅ Archivo .htaccess encontrado\n\n";
} else {
    echo "⚠️  Archivo .htaccess no encontrado. Asegúrese de que mod_rewrite esté habilitado.\n\n";
}

// Mostrar información de usuarios de prueba
echo "8. Usuarios de prueba creados:\n";
echo "   📧 admin@sistema.edu / password (Director)\n";
echo "   📧 juan.perez@estudiante.edu / password (Alumno)\n";
echo "   📧 carlos.gonzalez@maestro.edu / password (Maestro)\n\n";

// Verificación final
echo "9. Verificación final del sistema...\n";
try {
    require_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Sistema configurado correctamente\n\n";
    } else {
        echo "❌ Error en la configuración final\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "🎉 INSTALACIÓN COMPLETADA EXITOSAMENTE\n\n";
echo "Próximos pasos:\n";
echo "1. Configure su servidor web (Apache/Nginx)\n";
echo "2. Acceda a index.php para verificar la API\n";
echo "3. Integre con el frontend\n";
echo "4. Configure SSL en producción\n\n";
echo "Para más información, consulte README.md\n";
?>
