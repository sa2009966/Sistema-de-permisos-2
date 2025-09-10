-- Script de creación de base de datos para Sistema de Permisos
-- Ejecutar en PostgreSQL

-- Crear base de datos (ejecutar como superusuario)
-- CREATE DATABASE sistema_permisos;

-- Conectar a la base de datos sistema_permisos
-- \c sistema_permisos;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo_institucional VARCHAR(150) UNIQUE NOT NULL,
    codigo_estudiante VARCHAR(20) UNIQUE,
    contraseña_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL CHECK (rol IN ('alumno', 'maestro', 'director')),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de permisos
CREATE TABLE IF NOT EXISTS permisos (
    id SERIAL PRIMARY KEY,
    id_alumno INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    motivo TEXT NOT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'aprobado', 'rechazado')),
    comentarios TEXT,
    id_aprobador INTEGER REFERENCES usuarios(id),
    fecha_aprobacion TIMESTAMP
);

-- Tabla de asistencias
CREATE TABLE IF NOT EXISTS asistencias (
    id SERIAL PRIMARY KEY,
    id_alumno INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    fecha DATE NOT NULL,
    estado_asistencia VARCHAR(20) NOT NULL CHECK (estado_asistencia IN ('presente', 'ausente', 'tardanza', 'justificado')),
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para optimizar consultas
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);
CREATE INDEX IF NOT EXISTS idx_usuarios_codigo ON usuarios(codigo_estudiante);
CREATE INDEX IF NOT EXISTS idx_permisos_alumno ON permisos(id_alumno);
CREATE INDEX IF NOT EXISTS idx_permisos_estado ON permisos(estado);
CREATE INDEX IF NOT EXISTS idx_asistencias_alumno ON asistencias(id_alumno);
CREATE INDEX IF NOT EXISTS idx_asistencias_fecha ON asistencias(fecha);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, apellidos, correo_institucional, codigo_estudiante, contraseña_hash, rol) 
VALUES ('Admin', 'Sistema', 'admin@sistema.edu', 'ADMIN001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'director')
ON CONFLICT (correo_institucional) DO NOTHING;

-- Insertar algunos datos de ejemplo
INSERT INTO usuarios (nombre, apellidos, correo_institucional, codigo_estudiante, contraseña_hash, rol) 
VALUES 
    ('Juan', 'Pérez García', 'juan.perez@estudiante.edu', 'EST001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumno'),
    ('María', 'López Rodríguez', 'maria.lopez@estudiante.edu', 'EST002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumno'),
    ('Carlos', 'González Martín', 'carlos.gonzalez@maestro.edu', 'MAE001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maestro')
ON CONFLICT (correo_institucional) DO NOTHING;
