# 📚 Documentación de la API REST - Sistema de Permisos

## 🚀 Descripción General

El Sistema de Permisos ha sido refactorizado para implementar una **API REST moderna** con autenticación JWT, separación clara de capas y arquitectura escalable.

## 🏗️ Arquitectura

```
Backend/
├── api/v1/                 # Endpoints de la API REST
│   ├── auth.php           # Autenticación
│   ├── usuarios.php       # Gestión de usuarios
│   └── permisos.php       # Gestión de permisos
├── config/                # Configuración
│   ├── config.php         # Configuración general
│   ├── database.php       # Conexión a BD
│   └── jwt.php           # Configuración JWT
├── controllers/           # Controladores
│   ├── AuthController.php
│   ├── UserController.php
│   └── PermisoController.php
├── models/               # Modelos de datos
│   ├── BaseModel.php
│   ├── User.php
│   └── Permiso.php
├── services/             # Lógica de negocio
│   ├── AuthService.php
│   ├── UserService.php
│   └── PermisoService.php
├── middleware/           # Middleware
│   ├── AuthMiddleware.php
│   └── CorsMiddleware.php
└── utils/               # Utilidades
    ├── Response.php
    ├── Validator.php
    └── JWT.php
```

## 🔐 Autenticación

### Base URL
```
http://localhost/sistema-permisos/Backend/api/v1
```

### Headers Requeridos
```http
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

## 📋 Endpoints de la API

### 🔑 Autenticación (`/auth`)

#### POST `/auth/login`
Iniciar sesión de usuario.

**Request:**
```json
{
  "email": "usuario@institucion.edu",
  "password": "contraseña123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "nombre": "Juan",
      "apellidos": "Pérez",
      "email": "usuario@institucion.edu",
      "codigo": "EST001",
      "rol": "alumno"
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

#### POST `/auth/register`
Registrar nuevo usuario.

**Request:**
```json
{
  "nombre": "Juan",
  "apellidos": "Pérez García",
  "correo_institucional": "juan.perez@estudiante.edu",
  "password": "contraseña123",
  "codigo_estudiante": "EST001",
  "rol": "alumno"
}
```

#### GET `/auth/profile`
Obtener perfil del usuario autenticado.

#### POST `/auth/change-password`
Cambiar contraseña del usuario.

**Request:**
```json
{
  "current_password": "contraseña_actual",
  "new_password": "nueva_contraseña"
}
```

#### POST `/auth/refresh`
Refrescar token de acceso.

**Request:**
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### 👥 Usuarios (`/usuarios`)

#### GET `/usuarios`
Obtener lista de usuarios (solo maestros/directores).

**Query Parameters:**
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (default: 20)
- `rol`: Filtrar por rol (alumno, maestro, director)
- `activo`: Filtrar por estado activo (true/false)

#### GET `/usuarios/{id}`
Obtener usuario por ID.

#### PUT `/usuarios/{id}`
Actualizar usuario.

#### DELETE `/usuarios/{id}`
Eliminar usuario (solo directores).

#### GET `/usuarios/students`
Obtener todos los estudiantes.

#### GET `/usuarios/{id}/stats`
Obtener estadísticas del usuario.

#### GET `/usuarios/search`
Buscar usuarios.

**Query Parameters:**
- `q`: Término de búsqueda
- `page`: Número de página
- `per_page`: Elementos por página

### 📝 Permisos (`/permisos`)

#### GET `/permisos`
Obtener permisos del usuario autenticado.

**Query Parameters:**
- `page`: Número de página
- `per_page`: Elementos por página
- `estado`: Filtrar por estado (pendiente, aprobado, rechazado)
- `id_alumno`: Filtrar por alumno (solo maestros/directores)

#### POST `/permisos/create`
Crear nuevo permiso.

**Request:**
```json
{
  "motivo": "Motivo del permiso",
  "fecha_inicio": "2024-01-15",
  "fecha_fin": "2024-01-15"
}
```

#### GET `/permisos/{id}`
Obtener permiso por ID.

#### PUT `/permisos/{id}/update`
Actualizar estado del permiso (solo maestros/directores).

**Request:**
```json
{
  "estado": "aprobado",
  "comentarios": "Permiso aprobado"
}
```

#### DELETE `/permisos/{id}`
Eliminar permiso (solo el propietario, si está pendiente).

#### GET `/permisos/pending`
Obtener permisos pendientes (solo maestros/directores).

#### GET `/permisos/stats`
Obtener estadísticas de permisos.

#### GET `/permisos/search`
Buscar permisos.

## 🔒 Seguridad

### JWT (JSON Web Tokens)
- **Access Token**: Válido por 1 hora
- **Refresh Token**: Válido por 7 días
- **Algoritmo**: HS256
- **Headers**: `Authorization: Bearer <token>`

### Roles y Permisos
- **alumno**: Puede crear y ver sus propios permisos
- **maestro**: Puede aprobar/rechazar permisos y ver todos los usuarios
- **director**: Acceso completo al sistema

### Validaciones
- Contraseñas: mínimo 6 caracteres
- Emails: formato válido
- Fechas: formato YYYY-MM-DD
- Motivos: entre 10 y 500 caracteres

## 📊 Respuestas de la API

### Formato Estándar
```json
{
  "success": true|false,
  "message": "Mensaje descriptivo",
  "data": {}, // Datos de respuesta
  "errors": {}, // Errores de validación (si aplica)
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Códigos de Estado HTTP
- `200`: Éxito
- `201`: Creado exitosamente
- `400`: Error en la petición
- `401`: No autorizado
- `403`: Acceso denegado
- `404`: Recurso no encontrado
- `422`: Error de validación
- `500`: Error interno del servidor

## 🚀 Frontend

### Servicios JavaScript
```javascript
// Autenticación
await authService.login(email, password);
await authService.register(userData);
await authService.logout();

// Usuarios
await userService.getUsers(page, perPage, filters);
await userService.getUserById(id);

// Permisos
await permisoService.getPermisos(page, perPage, filters);
await permisoService.createPermiso(permisoData);
await permisoService.updatePermisoStatus(id, estado, comentarios);
```

### Utilidades
```javascript
// Verificar autenticación
Utils.isAuthenticated();

// Obtener datos de sesión
Utils.getSessionData();

// Mostrar notificaciones
Utils.showNotification(message, type);

// Formatear fechas
Utils.formatDate(date);
Utils.formatDateTime(date);
```

## 🔧 Configuración

### Base de Datos
- **Motor**: PostgreSQL
- **Tablas**: usuarios, permisos, asistencias
- **Índices**: Optimizados para consultas frecuentes

### Servidor Web
- **Apache**: Con mod_rewrite habilitado
- **PHP**: Versión 7.4 o superior
- **Extensiones**: PDO, JSON, OpenSSL

### Variables de Entorno
```php
// config/config.php
define('APP_ENV', 'development'); // development, production
define('SESSION_TIMEOUT', 3600);
define('PASSWORD_MIN_LENGTH', 6);
```

## 📱 Características del Frontend

### Responsive Design
- **TailwindCSS**: Framework CSS moderno
- **Mobile First**: Diseño adaptativo
- **Componentes**: Reutilizables y modulares

### Funcionalidades
- **Autenticación**: Login/registro con JWT
- **Dashboard**: Personalizado por rol
- **Gestión de Permisos**: CRUD completo
- **Notificaciones**: Sistema de alertas
- **Paginación**: Para listas grandes
- **Búsqueda**: Filtros avanzados

## 🧪 Testing

### Endpoints de Prueba
```bash
# Login
curl -X POST http://localhost/sistema-permisos/Backend/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@sistema.edu","password":"password"}'

# Obtener permisos
curl -X GET http://localhost/sistema-permisos/Backend/api/v1/permisos \
  -H "Authorization: Bearer <token>"
```

## 📈 Mejoras Implementadas

### Backend
- ✅ API REST organizada
- ✅ Autenticación JWT
- ✅ Separación de capas (MVC)
- ✅ Validación robusta
- ✅ Manejo de errores estandarizado
- ✅ CORS configurado
- ✅ Headers de seguridad

### Frontend
- ✅ Consumo de API REST
- ✅ Gestión de tokens JWT
- ✅ Interfaz responsiva
- ✅ Código optimizado
- ✅ Servicios organizados
- ✅ Utilidades reutilizables

### Seguridad
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Tokens JWT seguros
- ✅ Validación de entrada
- ✅ Headers de seguridad
- ✅ CORS configurado

## 🚀 Instalación

1. **Configurar Base de Datos**
   ```sql
   -- Ejecutar schema.sql en PostgreSQL
   ```

2. **Configurar Servidor**
   ```bash
   # Asegurar que mod_rewrite esté habilitado
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. **Configurar Permisos**
   ```bash
   chmod 755 Backend/
   chmod 644 Backend/.htaccess
   ```

4. **Acceder al Sistema**
   ```
   http://localhost/sistema-permisos/Frontend/login.html
   ```

## 📞 Soporte

Para soporte técnico o reportar bugs, contactar al equipo de desarrollo.

---

**Versión**: 2.0.0  
**Última actualización**: Enero 2024  
**Desarrollado con**: PHP 7.4+, PostgreSQL, JavaScript ES6+, TailwindCSS
