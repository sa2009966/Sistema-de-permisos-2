document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const switchToLogin = document.getElementById('switch-to-login');
            
            // Verificar si ya hay una sesión activa
            checkExistingSession();
            
            // Toggle password visibility
            function setupPasswordToggle(buttonId, inputId) {
                const toggleButton = document.getElementById(buttonId);
                const passwordInput = document.getElementById(inputId);
                
                toggleButton.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    toggleButton.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
            }
            
            // Setup password toggles
            setupPasswordToggle('toggle-login-password', 'login-password');
            setupPasswordToggle('toggle-register-password', 'register-password');
            setupPasswordToggle('toggle-confirm-password', 'confirm-password');
            
            // Switch between login and register forms
            function showLoginForm() {
                loginTab.classList.add('text-blue-600', 'border-blue-600');
                loginTab.classList.remove('text-gray-500', 'border-transparent');
                registerTab.classList.add('text-gray-500', 'border-transparent');
                registerTab.classList.remove('text-blue-600', 'border-blue-600');
                
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
            }
            
            function showRegisterForm() {
                registerTab.classList.add('text-blue-600', 'border-blue-600');
                registerTab.classList.remove('text-gray-500', 'border-transparent');
                loginTab.classList.add('text-gray-500', 'border-transparent');
                loginTab.classList.remove('text-blue-600', 'border-blue-600');
                
                registerForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
            }
            
            // Event listeners for tab switching
            loginTab.addEventListener('click', showLoginForm);
            registerTab.addEventListener('click', showRegisterForm);
            switchToLogin.addEventListener('click', showLoginForm);
            
            // Form validation
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            // Login form validation
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                
                const email = document.getElementById('login-email');
                const password = document.getElementById('login-password');
                const emailError = document.getElementById('login-email-error');
                const passwordError = document.getElementById('login-password-error');
                
                // Reset errors
                email.classList.remove('input-error');
                password.classList.remove('input-error');
                emailError.classList.add('hidden');
                passwordError.classList.add('hidden');
                
                // Validate email
                if (!email.value.trim()) {
                    email.classList.add('input-error');
                    emailError.textContent = 'El correo es requerido';
                    emailError.classList.remove('hidden');
                    isValid = false;
                } else if (!validateEmail(email.value.trim())) {
                    email.classList.add('input-error');
                    emailError.textContent = 'Por favor ingrese un correo válido';
                    emailError.classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate password
                if (!password.value.trim()) {
                    password.classList.add('input-error');
                    passwordError.textContent = 'La contraseña es requerida';
                    passwordError.classList.remove('hidden');
                    isValid = false;
                }
                
                if (isValid) {
                    // Enviar datos al servidor
                    handleLogin(email.value.trim(), password.value.trim());
                }
            });
            
            // Register form validation
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                
                const email = document.getElementById('register-email');
                const password = document.getElementById('register-password');
                const confirmPassword = document.getElementById('confirm-password');
                const terms = document.getElementById('terms');
                
                const emailError = document.getElementById('register-email-error');
                const passwordError = document.getElementById('register-password-error');
                const confirmPasswordError = document.getElementById('confirm-password-error');
                const termsError = document.getElementById('terms-error');
                
                // Reset errors
                email.classList.remove('input-error');
                password.classList.remove('input-error');
                confirmPassword.classList.remove('input-error');
                emailError.classList.add('hidden');
                passwordError.classList.add('hidden');
                confirmPasswordError.classList.add('hidden');
                termsError.classList.add('hidden');
                
                // Validate email
                if (!email.value.trim()) {
                    email.classList.add('input-error');
                    emailError.textContent = 'El correo es requerido';
                    emailError.classList.remove('hidden');
                    isValid = false;
                } else if (!validateEmail(email.value.trim())) {
                    email.classList.add('input-error');
                    emailError.textContent = 'Por favor ingrese un correo válido';
                    emailError.classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate password
                if (!password.value.trim()) {
                    password.classList.add('input-error');
                    passwordError.textContent = 'La contraseña es requerida';
                    passwordError.classList.remove('hidden');
                    isValid = false;
                } else if (password.value.trim().length < 8) {
                    password.classList.add('input-error');
                    passwordError.textContent = 'La contraseña debe tener al menos 8 caracteres';
                    passwordError.classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate confirm password
                if (password.value.trim() !== confirmPassword.value.trim()) {
                    confirmPassword.classList.add('input-error');
                    confirmPasswordError.textContent = 'Las contraseñas no coinciden';
                    confirmPasswordError.classList.remove('hidden');
                    isValid = false;
                }
                
                // Validate terms
                if (!terms.checked) {
                    termsError.classList.remove('hidden');
                    isValid = false;
                }
                
                if (isValid) {
                    // Enviar datos al servidor
                    handleRegister({
                        email: email.value.trim(),
                        password: password.value.trim(),
                        nombre: '', // Se puede agregar campo de nombre si es necesario
                        apellidos: '', // Se puede agregar campo de apellidos si es necesario
                        codigo_estudiante: '', // Se puede agregar campo de código si es necesario
                        rol: 'alumno' // Rol por defecto
                    });
                }
            });
        });

        // Función para verificar sesión existente
        async function checkExistingSession() {
            try {
                if (Utils.isAuthenticated()) {
                    // Verificar si la sesión sigue siendo válida
                    const response = await authService.getProfile();
                    if (response.success) {
                        // Redirigir al dashboard apropiado según el rol
                        redirectToDashboard(response.data.rol);
                    } else {
                        // Limpiar datos de sesión inválidos
                        Utils.clearSessionData();
                    }
                }
            } catch (error) {
                console.log('No hay sesión activa o error al verificar:', error);
                Utils.clearSessionData();
            }
        }

        // Función para manejar el login
        async function handleLogin(email, password) {
            const submitBtn = document.querySelector('#loginForm button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            try {
                // Mostrar estado de carga
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Iniciando sesión...';
                
                const response = await authService.login(email, password);
                
                if (response.success) {
                    // Mostrar mensaje de éxito
                    Utils.showNotification('Inicio de sesión exitoso!', 'success');
                    
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        redirectToDashboard(response.data.user.rol);
                    }, 1000);
                } else {
                    Utils.showNotification(response.message || 'Error al iniciar sesión', 'error');
                }
            } catch (error) {
                console.error('Error en login:', error);
                Utils.showNotification(error.message || 'Error al conectar con el servidor', 'error');
            } finally {
                // Restaurar botón
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }

        // Función para manejar el registro
        async function handleRegister(userData) {
            const submitBtn = document.querySelector('#registerForm button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            try {
                // Mostrar estado de carga
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Registrando...';
                
                const response = await authService.register(userData);
                
                if (response.success) {
                    Utils.showNotification('Registro exitoso! Por favor inicia sesión.', 'success');
                    
                    // Reset form and show login
                    document.getElementById('registerForm').reset();
                    showLoginForm();
                } else {
                    Utils.showNotification(response.message || 'Error al registrar usuario', 'error');
                }
            } catch (error) {
                console.error('Error en registro:', error);
                Utils.showNotification(error.message || 'Error al conectar con el servidor', 'error');
            } finally {
                // Restaurar botón
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }

        // Función para redirigir al dashboard según el rol
        function redirectToDashboard(rol) {
            switch (rol) {
                case 'alumno':
                    window.location.href = 'home.html';
                    break;
                case 'maestro':
                case 'director':
                    window.location.href = 'dahsboard.html';
                    break;
                default:
                    window.location.href = 'home.html';
            }
        }