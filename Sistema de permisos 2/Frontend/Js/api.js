/**
 * Configuración y utilidades para la API REST del Sistema de Permisos
 */

// Configuración de la API
const API_CONFIG = {
    baseURL: 'http://localhost/sistema-permisos/Backend/api/v1',
    timeout: 10000, // Timeout de 10 segundos
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

/**
 * Clase para manejar las llamadas a la API REST
 */
class ApiClient {
    constructor() {
        this.baseURL = API_CONFIG.baseURL;
        this.timeout = API_CONFIG.timeout;
        this.headers = { ...API_CONFIG.headers };
        this.token = localStorage.getItem('access_token');
    }

    /**
     * Realizar petición HTTP
     * @param {string} endpoint - Endpoint de la API
     * @param {Object} options - Opciones de la petición
     * @returns {Promise} - Respuesta de la API
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            method: 'GET',
            headers: { ...this.headers },
            ...options
        };

        // Agregar token de autorización si existe
        if (this.token) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.timeout);

            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            const data = await response.json();

            if (!response.ok) {
                // Si el token expiró, intentar refrescarlo
                if (response.status === 401 && this.token) {
                    await this.refreshToken();
                    // Reintentar la petición con el nuevo token
                    config.headers['Authorization'] = `Bearer ${this.token}`;
                    const retryResponse = await fetch(url, {
                        ...config,
                        signal: controller.signal
                    });
                    const retryData = await retryResponse.json();
                    
                    if (!retryResponse.ok) {
                        throw new Error(retryData.message || 'Error en la petición');
                    }
                    return retryData;
                }
                throw new Error(data.message || 'Error en la petición');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw this.handleError(error);
        }
    }

    /**
     * Refrescar token de acceso
     */
    async refreshToken() {
        const refreshToken = localStorage.getItem('refresh_token');
        if (!refreshToken) {
            throw new Error('No hay token de refresh disponible');
        }

        const response = await this.request('/auth/refresh', {
            method: 'POST',
            body: JSON.stringify({
                refresh_token: refreshToken
            })
        });

        if (response.success && response.data.access_token) {
            this.token = response.data.access_token;
            localStorage.setItem('access_token', response.data.access_token);
        }

        return response;
    }

    /**
     * GET request
     * @param {string} endpoint - Endpoint de la API
     * @param {Object} params - Parámetros de consulta
     * @returns {Promise} - Respuesta de la API
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url);
    }

    /**
     * POST request
     * @param {string} endpoint - Endpoint de la API
     * @param {Object} data - Datos a enviar
     * @returns {Promise} - Respuesta de la API
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     * @param {string} endpoint - Endpoint de la API
     * @param {Object} data - Datos a enviar
     * @returns {Promise} - Respuesta de la API
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     * @param {string} endpoint - Endpoint de la API
     * @returns {Promise} - Respuesta de la API
     */
    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }

    /**
     * Manejar errores de la API
     * @param {Error} error - Error capturado
     * @returns {Error} - Error procesado
     */
    handleError(error) {
        if (error.name === 'AbortError') {
            return new Error('La petición tardó demasiado tiempo. Por favor, inténtalo de nuevo.');
        }

        if (error.message.includes('401')) {
            return new Error('No autorizado. Por favor, inicia sesión nuevamente.');
        }

        if (error.message.includes('403')) {
            return new Error('Acceso denegado. No tienes permisos para realizar esta acción.');
        }

        if (error.message.includes('404')) {
            return new Error('Recurso no encontrado.');
        }

        if (error.message.includes('500')) {
            return new Error('Error interno del servidor. Por favor, contacta al administrador.');
        }

        return error;
    }
}

/**
 * Servicios específicos de la API REST
 */
class AuthService {
    constructor(apiClient) {
        this.api = apiClient;
    }

    /**
     * Iniciar sesión
     * @param {string} email - Correo electrónico
     * @param {string} password - Contraseña
     * @returns {Promise} - Respuesta del login
     */
    async login(email, password) {
        const response = await this.api.post('/auth/login', {
            email,
            password
        });
        
        // Guardar tokens
        if (response.success && response.data.access_token) {
            this.api.token = response.data.access_token;
            localStorage.setItem('access_token', response.data.access_token);
            localStorage.setItem('refresh_token', response.data.refresh_token);
            localStorage.setItem('user_data', JSON.stringify(response.data.user));
        }
        
        return response;
    }

    /**
     * Registrar usuario
     * @param {Object} userData - Datos del usuario
     * @returns {Promise} - Respuesta del registro
     */
    async register(userData) {
        return this.api.post('/auth/register', userData);
    }

    /**
     * Cerrar sesión
     * @returns {Promise} - Respuesta del logout
     */
    async logout() {
        // Limpiar tokens y datos de usuario
        this.api.token = null;
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user_data');
        return { success: true, message: 'Sesión cerrada exitosamente' };
    }

    /**
     * Obtener perfil del usuario
     * @returns {Promise} - Perfil del usuario
     */
    async getProfile() {
        return this.api.get('/auth/profile');
    }

    /**
     * Cambiar contraseña
     * @param {string} currentPassword - Contraseña actual
     * @param {string} newPassword - Nueva contraseña
     * @returns {Promise} - Respuesta del cambio
     */
    async changePassword(currentPassword, newPassword) {
        return this.api.post('/auth/change-password', {
            current_password: currentPassword,
            new_password: newPassword
        });
    }
}

class UserService {
    constructor(apiClient) {
        this.api = apiClient;
    }

    /**
     * Obtener usuarios con paginación
     * @param {number} page - Página
     * @param {number} perPage - Elementos por página
     * @param {Object} filters - Filtros
     * @returns {Promise} - Lista de usuarios
     */
    async getUsers(page = 1, perPage = 20, filters = {}) {
        return this.api.get('/usuarios', {
            page,
            per_page: perPage,
            ...filters
        });
    }

    /**
     * Obtener usuario por ID
     * @param {number} id - ID del usuario
     * @returns {Promise} - Datos del usuario
     */
    async getUserById(id) {
        return this.api.get(`/usuarios/${id}`);
    }

    /**
     * Actualizar usuario
     * @param {number} id - ID del usuario
     * @param {Object} userData - Datos a actualizar
     * @returns {Promise} - Respuesta de la actualización
     */
    async updateUser(id, userData) {
        return this.api.put(`/usuarios/${id}`, userData);
    }

    /**
     * Eliminar usuario
     * @param {number} id - ID del usuario
     * @returns {Promise} - Respuesta de la eliminación
     */
    async deleteUser(id) {
        return this.api.delete(`/usuarios/${id}`);
    }

    /**
     * Obtener todos los estudiantes
     * @returns {Promise} - Lista de estudiantes
     */
    async getStudents() {
        return this.api.get('/usuarios/students');
    }

    /**
     * Obtener estadísticas del usuario
     * @param {number} id - ID del usuario
     * @returns {Promise} - Estadísticas del usuario
     */
    async getUserStats(id) {
        return this.api.get(`/usuarios/${id}/stats`);
    }

    /**
     * Buscar usuarios
     * @param {string} query - Término de búsqueda
     * @param {number} page - Página
     * @param {number} perPage - Elementos por página
     * @returns {Promise} - Resultados de búsqueda
     */
    async searchUsers(query, page = 1, perPage = 20) {
        return this.api.get('/usuarios/search', {
            q: query,
            page,
            per_page: perPage
        });
    }
}

class PermisoService {
    constructor(apiClient) {
        this.api = apiClient;
    }

    /**
     * Obtener permisos con paginación
     * @param {number} page - Página
     * @param {number} perPage - Elementos por página
     * @param {Object} filters - Filtros
     * @returns {Promise} - Lista de permisos
     */
    async getPermisos(page = 1, perPage = 20, filters = {}) {
        return this.api.get('/permisos', {
            page,
            per_page: perPage,
            ...filters
        });
    }

    /**
     * Obtener permiso por ID
     * @param {number} id - ID del permiso
     * @returns {Promise} - Datos del permiso
     */
    async getPermisoById(id) {
        return this.api.get(`/permisos/${id}`);
    }

    /**
     * Crear nuevo permiso
     * @param {Object} permisoData - Datos del permiso
     * @returns {Promise} - Respuesta de la creación
     */
    async createPermiso(permisoData) {
        return this.api.post('/permisos/create', permisoData);
    }

    /**
     * Actualizar estado del permiso
     * @param {number} id - ID del permiso
     * @param {string} estado - Nuevo estado
     * @param {string} comentarios - Comentarios
     * @returns {Promise} - Respuesta de la actualización
     */
    async updatePermisoStatus(id, estado, comentarios = '') {
        return this.api.put(`/permisos/${id}/update`, {
            estado,
            comentarios
        });
    }

    /**
     * Eliminar permiso
     * @param {number} id - ID del permiso
     * @returns {Promise} - Respuesta de la eliminación
     */
    async deletePermiso(id) {
        return this.api.delete(`/permisos/${id}`);
    }

    /**
     * Obtener permisos pendientes
     * @param {number} page - Página
     * @param {number} perPage - Elementos por página
     * @returns {Promise} - Lista de permisos pendientes
     */
    async getPendingPermisos(page = 1, perPage = 20) {
        return this.api.get('/permisos/pending', {
            page,
            per_page: perPage
        });
    }

    /**
     * Obtener estadísticas de permisos
     * @returns {Promise} - Estadísticas de permisos
     */
    async getPermisoStats() {
        return this.api.get('/permisos/stats');
    }

    /**
     * Buscar permisos
     * @param {string} query - Término de búsqueda
     * @param {number} page - Página
     * @param {number} perPage - Elementos por página
     * @returns {Promise} - Resultados de búsqueda
     */
    async searchPermisos(query, page = 1, perPage = 20) {
        return this.api.get('/permisos/search', {
            q: query,
            page,
            per_page: perPage
        });
    }
}

// Crear instancias globales
const apiClient = new ApiClient();
const authService = new AuthService(apiClient);
const userService = new UserService(apiClient);
const permisoService = new PermisoService(apiClient);

// Utilidades adicionales
const Utils = {
    /**
     * Mostrar notificación
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo de notificación (success, error, warning, info)
     */
    showNotification(message, type = 'info') {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-black' :
            'bg-blue-500 text-white'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' :
                    'fa-info-circle'
                } mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            notification.remove();
        }, 5000);
    },

    /**
     * Formatear fecha
     * @param {string|Date} date - Fecha a formatear
     * @returns {string} - Fecha formateada
     */
    formatDate(date) {
        const d = new Date(date);
        return d.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    /**
     * Formatear fecha y hora
     * @param {string|Date} date - Fecha a formatear
     * @returns {string} - Fecha y hora formateada
     */
    formatDateTime(date) {
        const d = new Date(date);
        return d.toLocaleString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Validar email
     * @param {string} email - Email a validar
     * @returns {boolean} - Es válido
     */
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    /**
     * Obtener datos de sesión
     * @returns {Object|null} - Datos de la sesión
     */
    getSessionData() {
        const userData = localStorage.getItem('user_data');
        return userData ? JSON.parse(userData) : null;
    },

    /**
     * Guardar datos de sesión
     * @param {Object} data - Datos a guardar
     */
    setSessionData(data) {
        localStorage.setItem('user_data', JSON.stringify(data));
    },

    /**
     * Limpiar datos de sesión
     */
    clearSessionData() {
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('user_data');
    },

    /**
     * Verificar si el usuario está autenticado
     * @returns {boolean} - Está autenticado
     */
    isAuthenticated() {
        return !!localStorage.getItem('access_token');
    },

    /**
     * Obtener rol del usuario
     * @returns {string|null} - Rol del usuario
     */
    getUserRole() {
        const userData = this.getSessionData();
        return userData ? userData.rol : null;
    }
};

// Exportar para uso global
window.ApiClient = ApiClient;
window.authService = authService;
window.userService = userService;
window.permisoService = permisoService;
window.Utils = Utils;