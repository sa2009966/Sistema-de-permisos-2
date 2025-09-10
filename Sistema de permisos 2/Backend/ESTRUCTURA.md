# Estructura del Sistema de Permisos

## 📁 Estructura de Directorios

```
Backend/
├── config/
│   ├── database.php              # Configuración de BD
│   ├── database.example.php      # Ejemplo de configuración
│   └── config.php                # Configuración general
├── database/
│   └── schema.sql                # Script de creación de BD
├── includes/
│   ├── security.php              # Middleware de seguridad
│   ├── User.php                  # Clase para manejo de usuarios
│   ├── Permiso.php               # Clase para manejo de permisos
│   └── Asistencia.php            # Clase para manejo de asistencias
├── logs/
│   └── .gitkeep                  # Mantener directorio en git
├── auth.php                      # Módulo de autenticación
├── dashboard.php                 # Dashboard por roles
├── solicitar_permiso.php         # Solicitar permisos (estudiantes)
├── gestionar_permiso.php         # Gestionar permisos (maestros/directores)
├── estadisticas.php              # Endpoints de estadísticas
├── index.php                     # Punto de entrada principal
├── install.php                   # Script de instalación
├── .htaccess                     # Configuración de Apache
├── README.md                     # Documentación principal
├── API_DOCUMENTATION.md          # Documentación de la API
└── ESTRUCTURA.md                 # Este archivo
```

## 🏗️ Arquitectura del Sistema

### Capa de Presentación
- **Frontend**: HTML, CSS, JavaScript (en directorio Frontend/)
- **API REST**: Endpoints PHP que devuelven JSON

### Capa de Lógica de Negocio
- **Controladores**: auth.php, dashboard.php, solicitar_permiso.php, etc.
- **Modelos**: User.php, Permiso.php, Asistencia.php
- **Middleware**: security.php (autenticación, autorización, validación)

### Capa de Datos
- **Base de Datos**: PostgreSQL
- **Conexión**: PDO con consultas preparadas
- **Configuración**: database.php

## 🔄 Flujo de Datos

```
Cliente (Frontend) 
    ↓ HTTP Request
Apache/Nginx
    ↓
PHP Backend
    ↓
Middleware (security.php)
    ↓ Validación
Controladores
    ↓
Modelos (User, Permiso, Asistencia)
    ↓
Base de Datos (PostgreSQL)
    ↓
Respuesta JSON
    ↓
Cliente (Frontend)
```

## 🛡️ Seguridad

### Autenticación
- Sesiones PHP
- Contraseñas hasheadas con bcrypt
- Tokens CSRF

### Autorización
- Validación de roles por endpoint
- Verificación de propiedad de recursos
- Middleware de seguridad

### Validación
- Sanitización de inputs
- Validación de tipos de datos
- Consultas preparadas (PDO)

### Logs
- Registro de eventos de seguridad
- Logs de errores
- Auditoría de acciones

## 📊 Base de Datos

### Tablas Principales
1. **usuarios**: Información de usuarios y roles
2. **permisos**: Solicitudes de permisos
3. **asistencias**: Registro de asistencias

### Relaciones
- usuarios.id → permisos.id_alumno
- usuarios.id → asistencias.id_alumno
- usuarios.id → permisos.id_aprobador

### Índices
- Optimización de consultas frecuentes
- Índices en campos de búsqueda

## 🎯 Roles y Permisos

### Alumno
- ✅ Ver su dashboard
- ✅ Solicitar permisos
- ✅ Ver sus estadísticas
- ❌ Gestionar otros usuarios

### Maestro
- ✅ Ver dashboard general
- ✅ Aprobar/rechazar permisos
- ✅ Registrar asistencias
- ✅ Ver estadísticas generales
- ❌ Acceso completo del sistema

### Director
- ✅ Acceso completo al sistema
- ✅ Todas las funciones de maestro
- ✅ Gestión de usuarios
- ✅ Reportes avanzados

## 🔌 API Endpoints

### Autenticación
- `POST /auth.php?action=login`
- `POST /auth.php?action=register`
- `POST /auth.php?action=logout`
- `GET /auth.php?action=check_session`

### Dashboard
- `GET /dashboard.php?action=main`
- `GET /dashboard.php?action=student_data`
- `GET /dashboard.php?action=teacher_data`
- `GET /dashboard.php?action=director_data`

### Permisos
- `POST /solicitar_permiso.php?action=create`
- `GET /solicitar_permiso.php?action=list`
- `GET /solicitar_permiso.php?action=get&id=X`

### Gestión
- `POST /gestionar_permiso.php?action=approve`
- `POST /gestionar_permiso.php?action=reject`
- `GET /gestionar_permiso.php?action=pending`
- `POST /gestionar_permiso.php?action=add_attendance`

### Estadísticas
- `GET /estadisticas.php?action=permisos_por_estado`
- `GET /estadisticas.php?action=permisos_por_mes`
- `GET /estadisticas.php?action=asistencias_por_estado`
- `GET /estadisticas.php?action=estadisticas_generales`

## 🚀 Instalación

1. **Configurar Base de Datos**
   ```bash
   sudo -u postgres createdb sistema_permisos
   psql -U postgres -d sistema_permisos -f database/schema.sql
   ```

2. **Configurar PHP**
   ```bash
   cp config/database.example.php config/database.php
   # Editar config/database.php con tus credenciales
   ```

3. **Ejecutar Instalador**
   ```bash
   php install.php
   ```

4. **Configurar Servidor Web**
   - Habilitar mod_rewrite
   - Configurar virtual host
   - Configurar SSL (producción)

## 🔧 Configuración

### Desarrollo
- `APP_ENV = 'development'`
- Logs detallados
- Errores visibles

### Producción
- `APP_ENV = 'production'`
- Logs de errores
- Errores ocultos
- SSL habilitado

## 📈 Escalabilidad

### Optimizaciones Implementadas
- Consultas preparadas
- Índices de base de datos
- Validación en múltiples capas
- Logs estructurados

### Futuras Mejoras
- Caché de consultas
- Paginación avanzada
- API versioning
- Microservicios

## 🧪 Testing

### Usuarios de Prueba
- **admin@sistema.edu** / password (Director)
- **juan.perez@estudiante.edu** / password (Alumno)
- **carlos.gonzalez@maestro.edu** / password (Maestro)

### Casos de Prueba
1. Login/Logout
2. Solicitar permiso
3. Aprobar/Rechazar permiso
4. Registrar asistencia
5. Ver estadísticas

## 📝 Mantenimiento

### Logs
- `logs/security.log`: Eventos de seguridad
- `logs/error.log`: Errores del sistema

### Backup
- Base de datos PostgreSQL
- Archivos de configuración
- Logs importantes

### Monitoreo
- Verificar logs regularmente
- Monitorear rendimiento de BD
- Revisar espacio en disco
