# 🎓 Sistema de Permisos Académicos

Sistema completo de gestión de permisos estudiantiles desarrollado en PHP con PostgreSQL, incluyendo frontend moderno y API REST.

## 🚀 Características

- **Autenticación segura** con bcrypt y sesiones PHP
- **Sistema de roles** (alumno, maestro, director)
- **Gestión de permisos** con estados (pendiente, aprobado, rechazado)
- **Control de asistencias** con diferentes estados
- **API REST** con endpoints JSON
- **Frontend moderno** con Tailwind CSS
- **Seguridad robusta** con validación de inputs y protección CSRF
- **Estadísticas y reportes** con datos para gráficas
- **Diseño responsivo** para móviles y escritorio

## 📋 Requisitos

- PHP 7.4 o superior
- PostgreSQL 12 o superior
- Apache/Nginx con mod_rewrite habilitado
- Extensiones PHP: PDO, PDO_PGSQL, bcrypt

## 🛠️ Instalación Rápida

### Opción 1: Instalación Automática (Recomendada)

```bash
# Clonar o descargar el proyecto
cd "Sistema de permisos 2"

# Ejecutar script de instalación
chmod +x install.sh
./install.sh
```

### Opción 2: Instalación Manual

Ver la [Guía de Instalación Detallada](INSTALACION.md) para instrucciones paso a paso.

## 🚀 Cómo Ejecutar

### Archivo Principal
- **Frontend**: `index.html` - Página principal del sistema
- **Backend API**: `Backend/index.php` - Documentación de la API

### URLs de Acceso
- **Sistema Principal**: `http://localhost` o `http://sistema-permisos.local`
- **API Backend**: `http://localhost/api/`
- **Login**: `http://localhost/Frontend/login.html`

## 👥 Usuarios de Prueba

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@sistema.edu | password | Director |
| juan.perez@estudiante.edu | password | Alumno |
| carlos.gonzalez@maestro.edu | password | Maestro |

## 📁 Estructura del Proyecto

```
Sistema de permisos 2/
├── index.html                 # Página principal
├── .htaccess                  # Configuración del servidor web
├── install.sh                 # Script de instalación automática
├── INSTALACION.md             # Guía de instalación detallada
├── README.md                  # Este archivo
├── Frontend/                  # Interfaz de usuario
│   ├── login.html            # Página de login
│   ├── home.html             # Dashboard de estudiante
│   ├── dahsboard.html        # Dashboard de maestro/director
│   └── Js/                   # JavaScript del frontend
│       ├── api.js            # Cliente API
│       ├── login.js          # Lógica de autenticación
│       ├── home.js           # Dashboard estudiante
│       └── dashboard.js      # Dashboard maestro/director
└── Backend/                   # API y lógica del servidor
    ├── index.php             # Punto de entrada de la API
    ├── auth.php              # Autenticación
    ├── dashboard.php         # Dashboard por roles
    ├── solicitar_permiso.php # Gestión de permisos
    ├── gestionar_permiso.php # Aprobación de permisos
    ├── estadisticas.php      # Estadísticas y reportes
    ├── config/               # Configuración
    ├── includes/             # Clases y utilidades
    ├── database/             # Esquema de base de datos
    └── logs/                 # Archivos de log
```

## 🔌 API Endpoints

### Autenticación
- `POST /api/auth.php?action=login` - Iniciar sesión
- `POST /api/auth.php?action=register` - Registrar usuario
- `POST /api/auth.php?action=logout` - Cerrar sesión
- `GET /api/auth.php?action=check_session` - Verificar sesión

### Dashboard
- `GET /api/dashboard.php?action=main` - Datos principales
- `GET /api/dashboard.php?action=student_data` - Datos de estudiante
- `GET /api/dashboard.php?action=teacher_data` - Datos de maestro
- `GET /api/dashboard.php?action=director_data` - Datos de director

### Permisos
- `POST /api/solicitar_permiso.php?action=create` - Solicitar permiso
- `GET /api/solicitar_permiso.php?action=list` - Listar permisos
- `GET /api/solicitar_permiso.php?action=get&id=X` - Obtener permiso

### Gestión
- `POST /api/gestionar_permiso.php?action=approve` - Aprobar permiso
- `POST /api/gestionar_permiso.php?action=reject` - Rechazar permiso
- `GET /api/gestionar_permiso.php?action=pending` - Permisos pendientes

### Estadísticas
- `GET /api/estadisticas.php?action=permisos_por_estado` - Gráfica permisos
- `GET /api/estadisticas.php?action=estadisticas_generales` - Estadísticas generales

## 🎯 Roles y Permisos

### 👨‍🎓 Alumno
- ✅ Ver su dashboard personal
- ✅ Solicitar permisos
- ✅ Ver sus estadísticas
- ✅ Consultar historial de permisos
- ❌ Gestionar otros usuarios

### 👨‍🏫 Maestro
- ✅ Ver dashboard general
- ✅ Aprobar/rechazar permisos
- ✅ Registrar asistencias
- ✅ Ver estadísticas generales
- ❌ Acceso completo del sistema

### 👨‍💼 Director
- ✅ Acceso completo al sistema
- ✅ Todas las funciones de maestro
- ✅ Gestión de usuarios
- ✅ Reportes avanzados
- ✅ Configuración del sistema

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

## 📊 Base de Datos

### Tablas Principales
1. **usuarios**: Información de usuarios y roles
2. **permisos**: Solicitudes de permisos
3. **asistencias**: Registro de asistencias

### Relaciones
- usuarios.id → permisos.id_alumno
- usuarios.id → asistencias.id_alumno
- usuarios.id → permisos.id_aprobador

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

## 📝 Logs

Los logs de seguridad se guardan en `Backend/logs/` e incluyen:
- Intentos de login exitosos/fallidos
- Creación de permisos
- Aprobación/rechazo de permisos
- Registro de asistencias

## 🚨 Solución de Problemas

### Error de Conexión a BD
- Verificar credenciales en `Backend/config/database.php`
- Asegurar que PostgreSQL esté ejecutándose
- Verificar que la base de datos existe

### Error 500
- Revisar logs de Apache/PHP
- Verificar permisos de escritura en `Backend/logs/`
- Comprobar sintaxis PHP

### Error de Sesión
- Verificar que las sesiones están habilitadas
- Comprobar configuración de `session_start()`

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
- Caché de archivos estáticos

### Futuras Mejoras
- Caché de consultas
- Paginación avanzada
- API versioning
- Microservicios

## 📞 Soporte

Para soporte técnico o reportar bugs:
- **Email**: soporte@sistema.edu
- **Documentación**: Ver archivos README.md y INSTALACION.md
- **Logs**: Revisar archivos de log para diagnóstico

## 📄 Licencia

Este proyecto está desarrollado para uso educativo y académico.

---

**Versión**: 1.0.0  
**Última actualización**: 2024  
**Desarrollado con**: PHP, PostgreSQL, HTML5, CSS3, JavaScript, Tailwind CSS

¡Disfruta tu nuevo Sistema de Permisos Académicos! 🎉
