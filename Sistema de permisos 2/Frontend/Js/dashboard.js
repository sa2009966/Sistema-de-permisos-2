// Verificar autenticación al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    checkAuthentication();
    loadDashboardData();
    setupEventListeners();
});

// Variables globales
let requests = [];
let currentFilter = 'all';
let currentPage = 1;
const itemsPerPage = 5;

// Funciones principales
function checkAuthentication() {
    if (!Utils.isAuthenticated()) {
        window.location.href = 'login.html';
        return;
    }
    
    const sessionData = Utils.getSessionData();
    if (!sessionData) {
        window.location.href = 'login.html';
        return;
    }
    
    // Verificar que el usuario tenga permisos de maestro o director
    if (!['maestro', 'director'].includes(sessionData.rol)) {
        window.location.href = 'home.html';
        return;
    }
    
    // Actualizar información del usuario en la interfaz
    updateUserInfo(sessionData);
}

function loadDashboardData() {
    // Cargar permisos pendientes
    permisoService.getPendingPermisos()
        .then(response => {
            if (response.success) {
                requests = response.data.items || [];
                renderRequests();
                updatePagination();
                updateStats();
            }
        })
        .catch(error => {
            console.error('Error cargando datos del dashboard:', error);
            Utils.showNotification('Error al cargar datos del dashboard', 'error');
        });
}

function setupEventListeners() {
    // Toggle sidebar on mobile
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Pagination
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderRequests();
                updatePagination();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', () => {
            const filteredRequests = getFilteredRequests();
            const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);
            
            if (currentPage < totalPages) {
                currentPage++;
                renderRequests();
                updatePagination();
            }
        });
    }
}

function updateUserInfo(sessionData) {
    // Actualizar información del usuario en el sidebar
    const profileName = document.querySelector('.absolute.bottom-0 .font-medium');
    const profileRole = document.querySelector('.absolute.bottom-0 .text-blue-300');
    
    if (profileName && sessionData.nombre) {
        profileName.textContent = `${sessionData.nombre} ${sessionData.apellidos}`;
    }
    
    if (profileRole) {
        profileRole.textContent = sessionData.rol === 'director' ? 'Director' : 'Maestro';
    }
}

function updateStats() {
    const totalRequests = document.getElementById('total-requests');
    if (totalRequests) {
        totalRequests.textContent = requests.length;
    }
}

function getFilteredRequests() {
    if (currentFilter === 'all') {
        return requests;
    }
    return requests.filter(req => req.estado === currentFilter);
}

function renderRequests() {
    const requestsTable = document.getElementById('requests-table');
    if (!requestsTable) return;
    
    requestsTable.innerHTML = '';
    
    // Filter requests
    const filteredRequests = getFilteredRequests();
    
    // Paginate
    const startIndex = (currentPage - 1) * itemsPerPage;
    const paginatedRequests = filteredRequests.slice(startIndex, startIndex + itemsPerPage);
    
    // Update showing counts
    const showingFrom = document.getElementById('showing-from');
    const showingTo = document.getElementById('showing-to');
    
    if (showingFrom) showingFrom.textContent = startIndex + 1;
    if (showingTo) showingTo.textContent = Math.min(startIndex + itemsPerPage, filteredRequests.length);
    
    // Render rows
    if (paginatedRequests.length === 0) {
        requestsTable.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay solicitudes que coincidan con el filtro</td>
            </tr>
        `;
        return;
    }
    
    paginatedRequests.forEach(request => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/${request.id % 2 === 0 ? 'women' : 'men'}/${request.id}.jpg" alt="">
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${request.nombre} ${request.apellidos}</div>
                        <div class="text-sm text-gray-500">${request.codigo_estudiante || 'N/A'}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${request.grado || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${Utils.formatDate(request.fecha_solicitud)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${request.motivo}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(request.estado)}">
                    ${getStatusText(request.estado)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="viewRequest(${request.id})" class="text-blue-600 hover:text-blue-900 mr-3">Ver</button>
                ${request.estado === 'pendiente' ? `
                <button onclick="approveRequest(${request.id})" class="text-green-600 hover:text-green-900 mr-3">Aprobar</button>
                <button onclick="rejectRequest(${request.id})" class="text-red-600 hover:text-red-900">Rechazar</button>
                ` : ''}
            </td>
        `;
        requestsTable.appendChild(row);
    });
}

function updatePagination() {
    const filteredRequests = getFilteredRequests();
    const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);
    
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    
    if (prevPageBtn) {
        prevPageBtn.disabled = currentPage === 1;
    }
    
    if (nextPageBtn) {
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;
    }
}

// Funciones de filtrado
function filterRequests(filter) {
    currentFilter = filter;
    currentPage = 1;
    
    // Update active filter button
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.classList.remove('active', 'bg-blue-600', 'text-white');
        btn.classList.add('border', 'border-gray-300', 'text-gray-700', 'hover:bg-gray-100');
    });
    
    event.target.classList.add('active', 'bg-blue-600', 'text-white');
    event.target.classList.remove('border', 'border-gray-300', 'text-gray-700', 'hover:bg-gray-100');
    
    renderRequests();
    updatePagination();
}

// Funciones de gestión de permisos
function viewRequest(id) {
    const request = requests.find(req => req.id == id);
    if (!request) return;
    
    const modalContent = document.getElementById('modal-content');
    const requestModal = document.getElementById('request-modal');
    const approveBtn = document.getElementById('approve-btn');
    const rejectBtn = document.getElementById('reject-btn');
    
    if (modalContent) {
        modalContent.innerHTML = `
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <img class="h-16 w-16 rounded-full" src="https://randomuser.me/api/portraits/${id % 2 === 0 ? 'women' : 'men'}/${id}.jpg" alt="">
                    <div>
                        <h4 class="font-bold text-lg">${request.nombre} ${request.apellidos}</h4>
                        <p class="text-gray-600">${request.codigo_estudiante || 'N/A'}</p>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(request.estado)}">
                            ${getStatusText(request.estado)}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Fecha del permiso</p>
                        <p class="font-medium">${Utils.formatDate(request.fecha_inicio)} - ${Utils.formatDate(request.fecha_fin)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Motivo</p>
                        <p class="font-medium">${request.motivo}</p>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Detalles</p>
                    <p class="mt-1 text-gray-700">${request.motivo}</p>
                </div>
            </div>
        `;
    }
    
    // Show appropriate buttons
    if (approveBtn && rejectBtn) {
        approveBtn.classList.add('hidden');
        rejectBtn.classList.add('hidden');
        
        if (request.estado === 'pendiente') {
            approveBtn.classList.remove('hidden');
            rejectBtn.classList.remove('hidden');
            
            // Set up button actions
            approveBtn.onclick = () => {
                approveRequest(id);
                closeModal();
            };
            
            rejectBtn.onclick = () => {
                rejectRequest(id);
                closeModal();
            };
        }
    }
    
    if (requestModal) {
        requestModal.classList.remove('hidden');
    }
}

function closeModal() {
    const requestModal = document.getElementById('request-modal');
    if (requestModal) {
        requestModal.classList.add('hidden');
    }
}

function approveRequest(id) {
    const request = requests.find(req => req.id == id);
    if (!request) return;
    
    permisoService.updatePermisoStatus(id, 'aprobado', 'Permiso aprobado')
        .then(response => {
            if (response.success) {
                request.estado = 'aprobado';
                renderRequests();
                Utils.showNotification(`Permiso #${id} aprobado`, 'success');
            } else {
                Utils.showNotification(response.message || 'Error al aprobar permiso', 'error');
            }
        })
        .catch(error => {
            console.error('Error al aprobar permiso:', error);
            Utils.showNotification('Error al conectar con el servidor', 'error');
        });
}

function rejectRequest(id) {
    const request = requests.find(req => req.id == id);
    if (!request) return;
    
    const reason = prompt('Ingrese el motivo del rechazo:');
    if (!reason) return;
    
    permisoService.updatePermisoStatus(id, 'rechazado', reason)
        .then(response => {
            if (response.success) {
                request.estado = 'rechazado';
                renderRequests();
                Utils.showNotification(`Permiso #${id} rechazado`, 'error');
            } else {
                Utils.showNotification(response.message || 'Error al rechazar permiso', 'error');
            }
        })
        .catch(error => {
            console.error('Error al rechazar permiso:', error);
            Utils.showNotification('Error al conectar con el servidor', 'error');
        });
}

// Helper functions
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