# 🚀 Guía de Instalación - Sistema de Permisos Académicos

## 📋 Requisitos del Sistema

### Servidor Web
- **Apache 2.4+** o **Nginx 1.18+**
- **PHP 7.4+** con las siguientes extensiones:
  - PDO
  - PDO_PGSQL
  - bcrypt
  - json
  - session
  - curl
- **PostgreSQL 12+**

### Cliente
- Navegador web moderno (Chrome, Firefox, Safari, Edge)
- JavaScript habilitado

## 🛠️ Instalación Paso a Paso

### 1. Configurar el Servidor Web

#### Para Apache:
```bash
# Habilitar módulos necesarios
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod deflate
sudo a2enmod expires

# Reiniciar Apache
sudo systemctl restart apache2
```

#### Para Nginx:
```nginx
# Configuración básica en /etc/nginx/sites-available/sistema-permisos
server {
    listen 80;
    server_name localhost;
    root /ruta/a/tu/proyecto;
    index index.html index.php;

    # Configuración para el frontend
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Configuración para la API
    location /api/ {
        rewrite ^/api/(.*)$ /Backend/$1 last;
    }

    # Configuración para archivos estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Configuración para PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 2. Configurar PostgreSQL

```bash
# Crear base de datos
sudo -u postgres createdb sistema_permisos

# Crear usuario (opcional)
sudo -u postgres createuser --interactive sistema_user

# Ejecutar script de creación
psql -U postgres -d sistema_permisos -f Backend/database/schema.sql
```

### 3. Configurar la Aplicación

#### 3.1 Configurar Base de Datos
Editar `Backend/config/database.php`:

```php
<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'sistema_permisos';
    private $username = 'tu_usuario';
    private $password = 'tu_contraseña';
    private $port = '5432';
    // ... resto del código
}
```

#### 3.2 Configurar Permisos
```bash
# Crear directorio de logs
mkdir -p Backend/logs
chmod 755 Backend/logs

# Configurar permisos de escritura
chmod 755 Backend/
chmod 644 Backend/config/*.php
```

### 4. Configurar Virtual Host (Apache)

Crear archivo `/etc/apache2/sites-available/sistema-permisos.conf`:

```apache
<VirtualHost *:80>
    ServerName sistema-permisos.local
    DocumentRoot /ruta/a/tu/proyecto
    
    <Directory /ruta/a/tu/proyecto>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sistema-permisos_error.log
    CustomLog ${APACHE_LOG_DIR}/sistema-permisos_access.log combined
</VirtualHost>
```

Habilitar el sitio:
```bash
sudo a2ensite sistema-permisos.conf
sudo systemctl reload apache2
```

### 5. Configurar Hosts Local (Opcional)

Editar `/etc/hosts`:
```
127.0.0.1 sistema-permisos.local
```

## 🔧 Configuración Avanzada

### Variables de Entorno

Crear archivo `Backend/.env` (opcional):
```env
APP_ENV=development
DB_HOST=localhost
DB_NAME=sistema_permisos
DB_USER=tu_usuario
DB_PASS=tu_contraseña
DB_PORT=5432
```

### Configuración de Seguridad

#### Para Producción:
1. Cambiar `APP_ENV` a `'production'` en `Backend/config/config.php`
2. Configurar SSL/HTTPS
3. Cambiar contraseñas por defecto
4. Configurar firewall
5. Habilitar logs de seguridad

#### Configuración SSL (Apache):
```bash
# Instalar certificado SSL
sudo certbot --apache -d tu-dominio.com
```

## 🧪 Verificación de la Instalación

### 1. Verificar Servicios
```bash
# Verificar Apache
sudo systemctl status apache2

# Verificar PostgreSQL
sudo systemctl status postgresql

# Verificar PHP
php -v
```

### 2. Probar la API
```bash
# Probar endpoint principal
curl http://localhost/api/

# Probar autenticación
curl -X POST http://localhost/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@sistema.edu","password":"password"}'
```

### 3. Acceder al Sistema
1. Abrir navegador en `http://localhost` o `http://sistema-permisos.local`
2. Verificar que carga la página principal
3. Probar login con usuarios de prueba

## 👥 Usuarios de Prueba

El sistema incluye usuarios de ejemplo:

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@sistema.edu | password | Director |
| juan.perez@estudiante.edu | password | Alumno |
| carlos.gonzalez@maestro.edu | password | Maestro |

## 🚨 Solución de Problemas

### Error de Conexión a Base de Datos
```bash
# Verificar que PostgreSQL esté ejecutándose
sudo systemctl status postgresql

# Verificar conexión
psql -U postgres -d sistema_permisos -c "SELECT version();"

# Verificar credenciales en database.php
```

### Error 500 - Internal Server Error
```bash
# Verificar logs de Apache
sudo tail -f /var/log/apache2/error.log

# Verificar logs de PHP
sudo tail -f /var/log/php_errors.log

# Verificar permisos
ls -la Backend/
```

### Error de Permisos
```bash
# Corregir permisos
sudo chown -R www-data:www-data /ruta/a/tu/proyecto
sudo chmod -R 755 /ruta/a/tu/proyecto
sudo chmod -R 777 Backend/logs/
```

### Error de CORS
- Verificar configuración en `.htaccess`
- Verificar headers en `includes/security.php`
- Verificar configuración del navegador

### Error de Sesiones
```bash
# Verificar configuración de sesiones en PHP
php -i | grep session

# Verificar permisos de directorio de sesiones
ls -la /var/lib/php/sessions/
```

## 📊 Monitoreo y Mantenimiento

### Logs Importantes
- **Apache**: `/var/log/apache2/`
- **PHP**: `/var/log/php_errors.log`
- **Aplicación**: `Backend/logs/`
- **PostgreSQL**: `/var/log/postgresql/`

### Backup
```bash
# Backup de base de datos
pg_dump -U postgres sistema_permisos > backup_$(date +%Y%m%d).sql

# Backup de archivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz /ruta/a/tu/proyecto
```

### Actualizaciones
1. Hacer backup completo
2. Actualizar archivos
3. Ejecutar migraciones de BD si las hay
4. Verificar funcionamiento
5. Restaurar backup si hay problemas

## 📞 Soporte

Para soporte técnico:
- **Email**: soporte@sistema.edu
- **Documentación**: Ver `README.md` y `ESTRUCTURA.md`
- **Logs**: Revisar archivos de log para diagnóstico

---

**¡Instalación completada!** 🎉

Tu Sistema de Permisos Académicos está listo para usar.
