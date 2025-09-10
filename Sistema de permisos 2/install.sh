#!/bin/bash

# Script de instalación automática para el Sistema de Permisos Académicos
# Compatible con Ubuntu/Debian

set -e  # Salir si hay algún error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Verificar si se ejecuta como root
if [[ $EUID -eq 0 ]]; then
   print_error "Este script no debe ejecutarse como root"
   exit 1
fi

print_header "Sistema de Permisos Académicos - Instalador"
echo "Este script instalará y configurará el Sistema de Permisos Académicos"
echo ""

# Verificar sistema operativo
if [[ ! -f /etc/debian_version ]]; then
    print_error "Este script está diseñado para sistemas Ubuntu/Debian"
    exit 1
fi

print_message "Sistema detectado: $(lsb_release -d | cut -f2)"

# Actualizar sistema
print_header "Actualizando sistema"
sudo apt update && sudo apt upgrade -y

# Instalar dependencias
print_header "Instalando dependencias del sistema"
sudo apt install -y \
    apache2 \
    postgresql \
    postgresql-contrib \
    php \
    php-pgsql \
    php-json \
    php-curl \
    php-mbstring \
    php-xml \
    libapache2-mod-php \
    curl \
    wget \
    unzip

# Habilitar módulos de Apache
print_message "Configurando Apache"
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod deflate
sudo a2enmod expires
sudo systemctl restart apache2

# Configurar PostgreSQL
print_header "Configurando PostgreSQL"
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Crear base de datos y usuario
print_message "Creando base de datos"
sudo -u postgres psql -c "CREATE DATABASE sistema_permisos;"
sudo -u postgres psql -c "CREATE USER sistema_user WITH PASSWORD 'sistema123';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE sistema_permisos TO sistema_user;"

# Ejecutar script de creación de tablas
print_message "Creando tablas de la base de datos"
if [[ -f "Backend/database/schema.sql" ]]; then
    sudo -u postgres psql -d sistema_permisos -f Backend/database/schema.sql
    print_message "Tablas creadas exitosamente"
else
    print_error "No se encontró el archivo schema.sql"
    exit 1
fi

# Configurar archivo de base de datos
print_message "Configurando conexión a base de datos"
if [[ -f "Backend/config/database.php" ]]; then
    # Hacer backup del archivo original
    cp Backend/config/database.php Backend/config/database.php.backup
    
    # Actualizar configuración
    sed -i "s/private \$username = 'postgres';/private \$username = 'sistema_user';/" Backend/config/database.php
    sed -i "s/private \$password = 'password';/private \$password = 'sistema123';/" Backend/config/database.php
    
    print_message "Configuración de base de datos actualizada"
else
    print_error "No se encontró el archivo de configuración de base de datos"
    exit 1
fi

# Crear directorio de logs
print_message "Creando directorio de logs"
mkdir -p Backend/logs
chmod 755 Backend/logs

# Configurar permisos
print_message "Configurando permisos"
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 Backend/logs/

# Crear virtual host
print_header "Configurando Virtual Host"
CURRENT_DIR=$(pwd)
VIRTUAL_HOST_CONFIG="/etc/apache2/sites-available/sistema-permisos.conf"

sudo tee $VIRTUAL_HOST_CONFIG > /dev/null <<EOF
<VirtualHost *:80>
    ServerName sistema-permisos.local
    DocumentRoot $CURRENT_DIR
    
    <Directory $CURRENT_DIR>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/sistema-permisos_error.log
    CustomLog \${APACHE_LOG_DIR}/sistema-permisos_access.log combined
</VirtualHost>
EOF

# Habilitar sitio
sudo a2ensite sistema-permisos.conf
sudo systemctl reload apache2

# Configurar hosts local
print_message "Configurando hosts local"
echo "127.0.0.1 sistema-permisos.local" | sudo tee -a /etc/hosts

# Verificar instalación
print_header "Verificando instalación"

# Verificar Apache
if systemctl is-active --quiet apache2; then
    print_message "✓ Apache está ejecutándose"
else
    print_error "✗ Apache no está ejecutándose"
fi

# Verificar PostgreSQL
if systemctl is-active --quiet postgresql; then
    print_message "✓ PostgreSQL está ejecutándose"
else
    print_error "✗ PostgreSQL no está ejecutándose"
fi

# Verificar PHP
if php -v > /dev/null 2>&1; then
    print_message "✓ PHP está instalado: $(php -v | head -n1)"
else
    print_error "✗ PHP no está instalado correctamente"
fi

# Verificar conexión a base de datos
if sudo -u postgres psql -d sistema_permisos -c "SELECT 1;" > /dev/null 2>&1; then
    print_message "✓ Conexión a base de datos exitosa"
else
    print_error "✗ Error de conexión a base de datos"
fi

# Crear usuarios de prueba
print_message "Creando usuarios de prueba"
sudo -u postgres psql -d sistema_permisos <<EOF
-- Insertar usuarios de prueba
INSERT INTO usuarios (nombre, apellidos, correo_institucional, codigo_estudiante, contraseña_hash, rol, activo) VALUES
('Admin', 'Sistema', 'admin@sistema.edu', 'ADMIN001', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'director', true),
('Juan', 'Pérez', 'juan.perez@estudiante.edu', 'EST001', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumno', true),
('Carlos', 'González', 'carlos.gonzalez@maestro.edu', 'MAE001', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maestro', true);
EOF

print_message "✓ Usuarios de prueba creados"

# Mostrar información final
print_header "Instalación Completada"
echo ""
echo -e "${GREEN}¡Instalación exitosa!${NC}"
echo ""
echo "Información de acceso:"
echo "• URL: http://sistema-permisos.local o http://localhost"
echo "• Usuarios de prueba:"
echo "  - Director: admin@sistema.edu / password"
echo "  - Estudiante: juan.perez@estudiante.edu / password"
echo "  - Maestro: carlos.gonzalez@maestro.edu / password"
echo ""
echo "Archivos importantes:"
echo "• Configuración BD: Backend/config/database.php"
echo "• Logs: Backend/logs/"
echo "• Documentación: README.md, INSTALACION.md"
echo ""
echo "Para desinstalar:"
echo "• sudo a2dissite sistema-permisos.conf"
echo "• sudo rm /etc/apache2/sites-available/sistema-permisos.conf"
echo "• sudo -u postgres dropdb sistema_permisos"
echo "• sudo -u postgres dropuser sistema_user"
echo ""
print_message "El sistema está listo para usar. ¡Disfruta tu nuevo Sistema de Permisos Académicos!"
