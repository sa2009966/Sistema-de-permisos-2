# 🎓 Sistema de Permisos Académicos

Sistema completo de gestión de permisos estudiantiles desarrollado en PHP con PostgreSQL, incluyendo frontend moderno y API REST.

## 🚀 Características

- **Autenticación segura** con bcrypt y sesiones PHP
- **Sistema de roles** (alumno, maestro, director)
- **Gestión de permisos** con estados (pendiente, aprobado, rechazado)
- **Control de asistencias** con diferentes estados
- **API REST** con endpoints JSON
- **Seguridad robusta** con validación de inputs y protección CSRF
- **Estadísticas y reportes** con datos para gráficas

## 📋 Requisitos

- PHP 7.4 o superior
- PostgreSQL 12 o superior
- Apache/Nginx con mod_rewrite habilitado
- Extensiones PHP: PDO, PDO_PGSQL, bcrypt

## 🛠️ Instalación

### 1. Configurar Base de Datos

```bash
# Crear base de datos
sudo -u postgres createdb sistema_permisos

# Ejecutar script de creación
psql -U postgres -d sistema_permisos -f database/schema.sql
```

### 2. Configurar Conexión

Editar `config/database.php` con tus credenciales:

```php
private $host = 'localhost';
private $db_name = 'sistema_permisos';
private $username = 'tu_usuario';
private $password = 'tu_contraseña';
private $port = '5432';
```

### 3. Configurar Permisos

```bash
# Crear directorio de logs
mkdir -p logs
chmod 755 logs

# Configurar permisos de escritura
chmod 755 Backend/
```

## 📚 API Endpoints

### Autenticación (`/auth.php`)

| Método | Endpoint | Descripción | Roles |
|--------|----------|-------------|-------|
| POST | `?action=login` | Iniciar sesión | Todos |
| POST | `?action=register` | Registrar usuario | Todos |
| POST | `?action=logout` | Cerrar sesión | Autenticados |
| GET | `?action=check_session` | Verificar sesión | Todos |

### Dashboard (`/dashboard.php`)

| Método | Endpoint | Descripción | Roles |
|--------|----------|-------------|-------|
| GET | `?action=main` | Datos principales | Todos |
| GET | `?action=student_data` | Datos de estudiante | Alumno |
| GET | `?action=teacher_data` | Datos de maestro | Maestro/Director |
| GET | `?action=director_data` | Datos de director | Director |

### Permisos (`/solicitar_permiso.php`)

| Método | Endpoint | Descripción | Roles |
|--------|----------|-------------|-------|
| POST | `?action=create` | Solicitar permiso | Alumno |
| GET | `?action=list` | Listar permisos | Alumno |
| GET | `?action=get&id=X` | Obtener permiso | Alumno |

### Gestión (`/gestionar_permiso.php`)

| Método | Endpoint | Descripción | Roles |
|--------|----------|-------------|-------|
| POST | `?action=approve` | Aprobar permiso | Maestro/Director |
| POST | `?action=reject` | Rechazar permiso | Maestro/Director |
| GET | `?action=pending` | Permisos pendientes | Maestro/Director |
| POST | `?action=add_attendance` | Registrar asistencia | Maestro/Director |
| GET | `?action=attendance` | Obtener asistencias | Maestro/Director |

### Estadísticas (`/estadisticas.php`)

| Método | Endpoint | Descripción | Roles |
|--------|----------|-------------|-------|
| GET | `?action=permisos_por_estado` | Gráfica permisos por estado | Todos |
| GET | `?action=permisos_por_mes` | Gráfica permisos por mes | Todos |
| GET | `?action=asistencias_por_estado` | Gráfica asistencias por estado | Todos |
| GET | `?action=estadisticas_generales` | Estadísticas generales | Todos |

## 🔐 Seguridad

### Características de Seguridad

- **Contraseñas hasheadas** con bcrypt
- **Validación de inputs** y sanitización
- **Protección CSRF** con tokens
- **Validación de roles** en cada endpoint
- **Logs de seguridad** para auditoría
- **Headers de seguridad** configurados
- **Consultas preparadas** para prevenir SQL injection

### Headers de Seguridad

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Content-Type: application/json; charset=utf-8`

## 📊 Estructura de Base de Datos

### Tabla `usuarios`
- `id` (SERIAL PRIMARY KEY)
- `nombre` (VARCHAR)
- `apellidos` (VARCHAR)
- `correo_institucional` (VARCHAR UNIQUE)
- `codigo_estudiante` (VARCHAR UNIQUE)
- `contraseña_hash` (VARCHAR)
- `rol` (VARCHAR: alumno, maestro, director)
- `fecha_registro` (TIMESTAMP)
- `activo` (BOOLEAN)

### Tabla `permisos`
- `id` (SERIAL PRIMARY KEY)
- `id_alumno` (INTEGER REFERENCES usuarios)
- `motivo` (TEXT)
- `fecha_solicitud` (TIMESTAMP)
- `fecha_inicio` (DATE)
- `fecha_fin` (DATE)
- `estado` (VARCHAR: pendiente, aprobado, rechazado)
- `comentarios` (TEXT)
- `id_aprobador` (INTEGER REFERENCES usuarios)
- `fecha_aprobacion` (TIMESTAMP)

### Tabla `asistencias`
- `id` (SERIAL PRIMARY KEY)
- `id_alumno` (INTEGER REFERENCES usuarios)
- `fecha` (DATE)
- `estado_asistencia` (VARCHAR: presente, ausente, tardanza, justificado)
- `observaciones` (TEXT)
- `fecha_registro` (TIMESTAMP)

## 🔄 Flujo del Sistema

### Para Estudiantes
1. **Registro/Login** → Acceso al sistema
2. **Dashboard** → Ver estadísticas personales
3. **Solicitar Permiso** → Crear nueva solicitud
4. **Ver Historial** → Consultar permisos anteriores

### Para Maestros/Directores
1. **Login** → Acceso al sistema
2. **Dashboard** → Ver resumen general
3. **Gestionar Permisos** → Aprobar/rechazar solicitudes
4. **Registrar Asistencias** → Control de asistencia
5. **Ver Estadísticas** → Reportes y gráficas

## 🧪 Usuarios de Prueba

El sistema incluye usuarios de ejemplo:

- **Admin**: `admin@sistema.edu` / `password` (Director)
- **Estudiante**: `juan.perez@estudiante.edu` / `password` (Alumno)
- **Maestro**: `carlos.gonzalez@maestro.edu` / `password` (Maestro)

## 📝 Logs

Los logs de seguridad se guardan en `logs/security.log` e incluyen:
- Intentos de login exitosos/fallidos
- Creación de permisos
- Aprobación/rechazo de permisos
- Registro de asistencias

## 🚨 Solución de Problemas

### Error de Conexión a BD
- Verificar credenciales en `config/database.php`
- Asegurar que PostgreSQL esté ejecutándose
- Verificar que la base de datos existe

### Error 500
- Revisar logs de Apache/PHP
- Verificar permisos de escritura en `logs/`
- Comprobar sintaxis PHP

### Error de Sesión
- Verificar que las sesiones están habilitadas
- Comprobar configuración de `session_start()`

## 📞 Soporte

Para soporte técnico o reportar bugs, contactar al equipo de desarrollo.

---

**Versión**: 1.0.0  
**Última actualización**: 2024
