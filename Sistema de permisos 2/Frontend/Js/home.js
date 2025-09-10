// Verificar autenticación al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    checkAuthentication();
    loadDashboardData();
    setupEventListeners();
});

// Elementos del DOM
const requestPermissionBtn = document.getElementById('requestPermissionBtn');
const permissionModal = document.getElementById('permissionModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const cancelRequestBtn = document.getElementById('cancelRequestBtn');

// Funciones principales
function checkAuthentication() {
    if (!Utils.isAuthenticated()) {
        window.location.href = 'login.html';
        return;
    }
    
    // Actualizar información del usuario en la interfaz
    const sessionData = Utils.getSessionData();
    if (sessionData) {
        updateUserInfo(sessionData);
    }
}

function loadDashboardData() {
    // Cargar permisos del usuario
    permisoService.getPermisos()
        .then(response => {
            if (response.success) {
                updateDashboard(response.data);
            }
        })
        .catch(error => {
            console.error('Error cargando datos del dashboard:', error);
            Utils.showNotification('Error al cargar datos del dashboard', 'error');
        });
}

function setupEventListeners() {
    requestPermissionBtn.addEventListener('click', () => {
        permissionModal.classList.remove('hidden');
    });

    closeModalBtn.addEventListener('click', () => {
        permissionModal.classList.add('hidden');
    });

    cancelRequestBtn.addEventListener('click', () => {
        permissionModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === permissionModal) {
            permissionModal.classList.add('hidden');
        }
    });

    // Today's date as default in the form
    document.getElementById('date').valueAsDate = new Date();
    
    // Manejar envío del formulario de permiso
    const permissionForm = document.querySelector('#permissionModal form');
    if (permissionForm) {
        permissionForm.addEventListener('submit', handlePermissionSubmit);
    }
}

function updateUserInfo(sessionData) {
    const userNameElement = document.querySelector('h2 span');
    if (userNameElement && sessionData.nombre) {
        userNameElement.textContent = `${sessionData.nombre} ${sessionData.apellidos}`;
    }
}

function updateDashboard(data) {
    // Actualizar estadísticas
    if (data.items && Array.isArray(data.items)) {
        const permisos = data.items;
        const pendientes = permisos.filter(p => p.estado === 'pendiente').length;
        const aprobados = permisos.filter(p => p.estado === 'aprobado').length;
        
        const pendingElement = document.querySelector('.bg-white.rounded-lg.shadow.p-6:nth-child(2) p');
        if (pendingElement) {
            pendingElement.textContent = pendientes;
        }
        
        const approvedElement = document.querySelector('.bg-white.rounded-lg.shadow.p-6:nth-child(3) p');
        if (approvedElement) {
            approvedElement.textContent = aprobados;
        }
        
        // Actualizar tabla de permisos
        updatePermissionsTable(permisos);
    }
}

function updatePermissionsTable(permisos) {
    const tbody = document.querySelector('tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    permisos.forEach(permiso => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${Utils.formatDate(permiso.fecha_solicitud)}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${permiso.motivo}</div>
                <div class="text-sm text-gray-500">${Utils.formatDate(permiso.fecha_inicio)} - ${Utils.formatDate(permiso.fecha_fin)}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(permiso.estado)}">${getStatusText(permiso.estado)}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <button class="text-indigo-600 hover:text-indigo-900 mr-3" onclick="viewPermission(${permiso.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="text-gray-600 hover:text-gray-900" onclick="printPermission(${permiso.id})">
                    <i class="fas fa-print"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function handlePermissionSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const permisoData = {
        motivo: formData.get('reason') || document.getElementById('reason').value,
        fecha_inicio: formData.get('date') || document.getElementById('date').value,
        fecha_fin: formData.get('date') || document.getElementById('date').value, // Por simplicidad, mismo día
        detalles: formData.get('details') || document.getElementById('details').value
    };
    
    // Validar datos
    if (!permisoData.motivo || !permisoData.fecha_inicio) {
        Utils.showNotification('Por favor complete todos los campos requeridos', 'error');
        return;
    }
    
    // Enviar solicitud
    permisoService.createPermiso(permisoData)
        .then(response => {
            if (response.success) {
                Utils.showNotification('Permiso solicitado exitosamente', 'success');
                permissionModal.classList.add('hidden');
                event.target.reset();
                loadDashboardData(); // Recargar datos
            } else {
                Utils.showNotification(response.message || 'Error al solicitar permiso', 'error');
            }
        })
        .catch(error => {
            console.error('Error al solicitar permiso:', error);
            Utils.showNotification('Error al conectar con el servidor', 'error');
        });
}

function getStatusClass(status) {
    switch (status) {
        case 'pendiente': return 'status-pending';
        case 'aprobado': return 'status-approved';
        case 'rechazado': return 'status-rejected';
        default: return '';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'pendiente': return 'Pendiente';
        case 'aprobado': return 'Aprobado';
        case 'rechazado': return 'Rechazado';
        default: return status;
    }
}

function viewPermission(id) {
    permisoService.getPermisoById(id)
        .then(response => {
            if (response.success) {
                // Mostrar detalles del permiso en un modal
                showPermissionDetails(response.data);
            }
        })
        .catch(error => {
            console.error('Error al obtener permiso:', error);
            Utils.showNotification('Error al cargar detalles del permiso', 'error');
        });
}

function printPermission(id) {
    // Implementar funcionalidad de impresión
    Utils.showNotification('Funcionalidad de impresión en desarrollo', 'info');
}

function showPermissionDetails(permiso) {
    // Crear modal con detalles del permiso
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Detalles del Permiso</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Motivo:</label>
                    <p class="text-gray-900">${permiso.motivo}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Fecha de Inicio:</label>
                    <p class="text-gray-900">${Utils.formatDate(permiso.fecha_inicio)}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Fecha de Fin:</label>
                    <p class="text-gray-900">${Utils.formatDate(permiso.fecha_fin)}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Estado:</label>
                    <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(permiso.estado)}">${getStatusText(permiso.estado)}</span>
                </div>
                ${permiso.comentarios ? `
                <div>
                    <label class="text-sm font-medium text-gray-500">Comentarios:</label>
                    <p class="text-gray-900">${permiso.comentarios}</p>
                </div>
                ` : ''}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}