document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const ROLES_VALIDOS = ['estudiante', 'participante_empresa', 'participante_universidad'];
    const API_BASE = (typeof window.API_BASE !== 'undefined' ? window.API_BASE : 'backend/public/index.php/api/v1').replace(/\/+$/, '');

    function apiRequest(path, options = {}) {
        return fetch(API_BASE + path, {
            credentials: 'same-origin',
            ...options,
            headers: { 'Content-Type': 'application/json', ...(options.headers || {}) }
        }).then(async response => {
            const result = await response.json();
            if (!response.ok || !result.ok) throw new Error(result.error || 'No se pudo completar la solicitud.');
            return result;
        });
    }

    function saveSession(user) {
        localStorage.setItem('userEmail', user.correo || user.email || '');
        localStorage.setItem('userType', user.rol || 'estudiante');
        localStorage.setItem('userIdNumeric', String(user.id || 0));
        localStorage.setItem('userNombre', user.nombre || '');
        localStorage.setItem('userApellido', user.apellido || '');
        localStorage.setItem('userName', ((user.nombre || '') + ' ' + (user.apellido || '')).trim() || 'Usuario Ineova');
        localStorage.setItem('userAvatar', user.avatar || '');
        localStorage.setItem('isLoggedIn', 'true');
    }

    const wrapper = document.querySelector('.custom-block-wrapper');
    const selectContainer = document.getElementById('fusionSelect');
    const trigger = selectContainer.querySelector('.custom-block-trigger');
    const placeholder = selectContainer.querySelector('.custom-block-placeholder');
    const options = selectContainer.querySelectorAll('.custom-block-option');
    const hiddenInput = document.getElementById('fusionHiddenInput');

    const panelEstudiante = document.getElementById('panelEstudiante');
    const panelUniversidad = document.getElementById('panelUniversidad');
    const panelEmpresa = document.getElementById('panelEmpresa');

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        wrapper.classList.toggle('active');
    });

    function closeDropdown() { wrapper.classList.remove('active'); }

    options.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const value = this.dataset.value;
            const textHTML = this.innerHTML;

            placeholder.innerHTML = textHTML;
            hiddenInput.value = value;

            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            wrapper.classList.remove('error');
            closeDropdown();
            updateActivePanel(value);
        });
    });

    function updateActivePanel(value) {
        if (!panelEstudiante || !panelUniversidad || !panelEmpresa) return;
        panelEstudiante.classList.remove('active');
        panelUniversidad.classList.remove('active');
        panelEmpresa.classList.remove('active');

        if (value === 'estudiante') panelEstudiante.classList.add('active');
        else if (value === 'participante_universidad') panelUniversidad.classList.add('active');
        else if (value === 'participante_empresa') panelEmpresa.classList.add('active');
    }

    document.addEventListener('click', function(e) {
        if (wrapper && !wrapper.contains(e.target)) closeDropdown();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDropdown();
    });

    const stepOne = document.getElementById('stepOne');
    const stepTwo = document.getElementById('stepTwo');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const backStepBtn = document.getElementById('backStepBtn');
    const form = document.getElementById('registerForm');
    const messageEl = document.getElementById('formMessage');

    const nombreInput = document.querySelector('input[name="nombre"]');
    const apellidoInput = document.querySelector('input[name="apellido"]');
    const emailInput = document.querySelector('input[name="email"]');

    function validateStepOne() {
        let valid = true;
        if (nombreInput) nombreInput.classList.remove('error');
        if (apellidoInput) apellidoInput.classList.remove('error');
        if (emailInput) emailInput.classList.remove('error');
        if (wrapper) wrapper.classList.remove('error');

        if (!nombreInput || !nombreInput.value.trim()) { if (nombreInput) nombreInput.classList.add('error'); valid = false; }
        if (!apellidoInput || !apellidoInput.value.trim()) { if (apellidoInput) apellidoInput.classList.add('error'); valid = false; }
        if (!emailInput || !emailInput.value.trim() || !emailInput.value.includes('@')) { if (emailInput) emailInput.classList.add('error'); valid = false; }
        if (!hiddenInput || !hiddenInput.value || !ROLES_VALIDOS.includes(hiddenInput.value)) { if (wrapper) wrapper.classList.add('error'); valid = false; }
        return valid;
    }

    function showMessage(text, type = 'error') {
        if (!messageEl) return;
        messageEl.textContent = text;
        messageEl.className = 'message show ' + type;
        setTimeout(() => messageEl.classList.remove('show'), 4000);
    }

    if (nextStepBtn) {
        nextStepBtn.addEventListener('click', function() {
            if (!validateStepOne()) {
                showMessage('Por favor, completa todos los campos y selecciona un tipo de cuenta.', 'error');
                return;
            }
            updateActivePanel(hiddenInput.value);
            if (stepOne) stepOne.style.display = 'none';
            if (stepTwo) stepTwo.style.display = 'block';
        });
    }

    if (backStepBtn) {
        backStepBtn.addEventListener('click', function() {
            if (stepTwo) stepTwo.style.display = 'none';
            if (stepOne) stepOne.style.display = 'block';
            if (messageEl) messageEl.classList.remove('show');
        });
    }

    function validateVenezuelanPhone(phone) {
        const clean = phone.replace(/[\s-]/g, '');
        if (!/^\d+$/.test(clean)) return { valid: false, message: 'Solo números permitidos' };
        const validPrefixes = ['0412', '0414', '0416', '0422', '0424', '0426'];
        if (clean.length !== 11) return { valid: false, message: 'Debe tener 11 dígitos (ej: 04121234567)' };
        const prefix = clean.substring(0, 4);
        if (!validPrefixes.includes(prefix)) return { valid: false, message: 'Prefijo inválido. Usa: 0412, 0414, 0416, 0422, 0424 o 0426' };
        return { valid: true, message: 'Teléfono válido ✓' };
    }

    function validateVenezuelanCedula(cedula) {
        const clean = cedula.replace(/[\s-]/g, '');
        if (!/^[VEve]/.test(clean)) return { valid: false, message: 'Debe comenzar con V o E' };
        const letter = clean.substring(0, 1).toUpperCase();
        const numberPart = clean.substring(1);
        if (!/^\d+$/.test(numberPart)) return { valid: false, message: 'Solo números después de la letra' };
        if (numberPart.length < 1 || numberPart.length > 8) return { valid: false, message: 'El número debe tener entre 1 y 8 dígitos' };
        return { valid: true, message: `Cédula ${letter}-${numberPart} válida ✓` };
    }

    function setupRealtimeValidation() {
        const panels = document.querySelectorAll('.password-panel');
        panels.forEach(panel => {
            const telefonoInput = panel.querySelector('input[name^="telefono_"]');
            const cedulaInput = panel.querySelector('input[name^="cedula_"]');

            if (telefonoInput) {
                const msgEl = telefonoInput.parentElement.querySelector('.validation-message');
                telefonoInput.addEventListener('input', function() {
                    const result = validateVenezuelanPhone(this.value);
                    this.classList.remove('error', 'success');
                    if (msgEl) msgEl.classList.remove('error', 'success');
                    if (this.value.length === 0) {
                        if (msgEl) { msgEl.textContent = 'Formato: 0412-1234567'; msgEl.className = 'validation-message'; }
                        return;
                    }
                    if (result.valid) { this.classList.add('success'); if (msgEl) msgEl.classList.add('success'); }
                    else { this.classList.add('error'); if (msgEl) msgEl.classList.add('error'); }
                    if (msgEl) msgEl.textContent = result.message;
                });
            }

            if (cedulaInput) {
                const msgEl = cedulaInput.parentElement.querySelector('.validation-message');
                if (msgEl) {
                    cedulaInput.addEventListener('input', function() {
                        const result = validateVenezuelanCedula(this.value);
                        this.classList.remove('error', 'success');
                        msgEl.classList.remove('error', 'success');
                        if (this.value.length === 0) {
                            msgEl.textContent = 'Formato: V-12345678 o E-12345678';
                            msgEl.className = 'validation-message';
                            return;
                        }
                        if (result.valid) { this.classList.add('success'); msgEl.classList.add('success'); }
                        else { this.classList.add('error'); msgEl.classList.add('error'); }
                        msgEl.textContent = result.message;
                    });
                }
            }
        });
    }

    const rifInputEmpresa = document.querySelector('input[name="organismo_identidad_empresa"]');
    if (rifInputEmpresa) {
        rifInputEmpresa.addEventListener('input', function() {
            let digits = 0;
            let filtered = '';
            for (const ch of this.value.toUpperCase()) {
                if (/\d/.test(ch)) {
                    if (digits < 10) { filtered += ch; digits++; }
                } else if (/[JVEGP-]/.test(ch)) {
                    filtered += ch;
                }
            }
            if (filtered !== this.value) this.value = filtered;
            const msgEl = this.parentElement.querySelector('.validation-message');
            if (msgEl) {
                this.classList.remove('error', 'success');
                msgEl.classList.remove('error', 'success');
                if (digits === 0) {
                    msgEl.textContent = 'Máximo 10 dígitos numéricos (ej: J-1234567890)';
                } else if (digits <= 10) {
                    this.classList.add('success');
                    msgEl.classList.add('success');
                    msgEl.textContent = `RIF válido (${digits}/10 dígitos) ✓`;
                }
            }
        });
    }

    function loadUniversitiesCatalog() {
        apiRequest('/universities').then(res => {
            const list = res.universities || [];
            ['coordUniversitySelect', 'studentUniversitySelect'].forEach(selId => {
                const sel = document.getElementById(selId);
                if (!sel) return;
                list.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = `${u.nombre}${u.siglas ? ' (' + u.siglas + ')' : ''}`;
                    sel.appendChild(opt);
                });
            });
        }).catch(() => {});
    }
    loadUniversitiesCatalog();

    function getActivePanelFields() {
        const activePanel = document.querySelector('.password-panel.active');
        if (!activePanel) return null;
        return {
            telefono: activePanel.querySelector('input[name^="telefono_"]'),
            cedula: activePanel.querySelector('input[name^="cedula_"]'),
            organizationName: activePanel.querySelector('input[name^="organismo_nombre_"]'),
            organizationIdentity: activePanel.querySelector('input[name^="organismo_identidad_"]'),
            universitySelect: activePanel.querySelector('select[name^="universidad_id_"]'),
            password: activePanel.querySelector('input[name^="password_"]'),
            confirmPassword: activePanel.querySelector('input[name^="confirmPassword_"]')
        };
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const fields = getActivePanelFields();
            if (!fields) { showMessage('Error interno. Por favor, vuelve a intentar.', 'error'); return; }

            Object.values(fields).forEach(el => { if (el) el.classList.remove('error'); });

            const telefono = fields.telefono ? fields.telefono.value.trim() : '';
            const cedula = fields.cedula ? fields.cedula.value.trim() : '';
            const organizationName = fields.organizationName ? fields.organizationName.value.trim() : '';
            const organizationIdentity = fields.organizationIdentity ? fields.organizationIdentity.value.trim() : '';
            const universityId = fields.universitySelect ? fields.universitySelect.value : '';
            const pass = fields.password ? fields.password.value.trim() : '';
            const confirm = fields.confirmPassword ? fields.confirmPassword.value.trim() : '';

            const phoneResult = validateVenezuelanPhone(telefono);
            if (!phoneResult.valid) { if (fields.telefono) fields.telefono.classList.add('error'); showMessage(phoneResult.message, 'error'); return; }

            const cedulaResult = validateVenezuelanCedula(cedula);
            if (!cedulaResult.valid) { if (fields.cedula) fields.cedula.classList.add('error'); showMessage(cedulaResult.message, 'error'); return; }

            if (hiddenInput.value === 'participante_empresa') {
                const rifDigits = organizationIdentity.replace(/\D+/g, '');
                if (!organizationName || !organizationIdentity || rifDigits.length < 1 || rifDigits.length > 10) {
                    if (fields.organizationName && !organizationName) fields.organizationName.classList.add('error');
                    if (fields.organizationIdentity) fields.organizationIdentity.classList.add('error');
                    showMessage('Indica el nombre y un RIF válido (máximo 10 dígitos numéricos).', 'error');
                    return;
                }
            }
            if (hiddenInput.value === 'participante_universidad' && !universityId) {
                if (fields.universitySelect) fields.universitySelect.classList.add('error');
                showMessage('Selecciona la universidad oficial que representas.', 'error');
                return;
            }

            if (pass.length < 6) { if (fields.password) fields.password.classList.add('error'); showMessage('La contraseña debe tener al menos 6 caracteres.', 'error'); return; }
            if (pass !== confirm) { if (fields.confirmPassword) fields.confirmPassword.classList.add('error'); showMessage('Las contraseñas no coinciden.', 'error'); return; }

            const submitBtn = document.getElementById('registerSubmitBtn');
            const originalText = submitBtn ? submitBtn.innerHTML : 'Registrarse';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner"></span> Registrando...';
            }

            apiRequest('/auth/register', {
                method: 'POST',
                body: JSON.stringify({
                    nombre: nombreInput.value.trim(),
                    apellido: apellidoInput.value.trim(),
                    email: emailInput.value.trim(),
                    userType: hiddenInput.value,
                    telefono,
                    cedula,
                    password: pass,
                    organismo_nombre: organizationName,
                    organismo_rif: hiddenInput.value === 'participante_empresa' ? organizationIdentity : '',
                    universidad_id: universityId ? parseInt(universityId, 10) : 0
                })
            })
                .then(result => {
                    if (result.pending_approval) {
                        showMessage(result.message || 'Solicitud enviada. Espera la aprobación del administrador.', 'success');
                        form.reset();
                        return;
                    }
                    saveSession(result.user);
                    showMessage('¡Cuenta creada exitosamente! Redirigiendo...', 'success');
                    setTimeout(() => { window.location.href = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL + 'dashboard' : 'dashboard'; }, 1000);
                })
                .catch(error => showMessage(error.message || 'No se pudo conectar con el servidor.', 'error'))
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
        });
    }

    const sign_in_btn = document.querySelector("#login");
    const sign_up_btn = document.querySelector("#register");
    const container = document.querySelector("#container");

    function updateAuthMode() {
        const isReg = window.location.hash === '#register' || window.location.pathname.endsWith('/registro');
        if (container) container.classList.toggle('active', isReg);
    }

    updateAuthMode();
    window.addEventListener('hashchange', updateAuthMode);

    if (sign_up_btn && sign_in_btn && container) {
        sign_up_btn.addEventListener("click", () => { container.classList.add("active"); window.location.hash = "register"; });
        sign_in_btn.addEventListener("click", () => { container.classList.remove("active"); history.replaceState(null, "", window.location.pathname); });
    }

    const loginForm = document.getElementById('loginForm');
    const loginSubmitBtn = document.getElementById('loginSubmitBtn');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const emailField = loginForm.querySelector('input[name="email"]');
            const passField = loginForm.querySelector('input[name="password"]');
            const email = emailField ? emailField.value.trim() : '';
            const pass = passField ? passField.value.trim() : '';

            if (!email || !email.includes('@')) {
                if (emailField) emailField.classList.add('error');
                alert('Ingresa un correo válido');
                return;
            }
            if (pass.length < 6) {
                if (passField) passField.classList.add('error');
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }

            const originalText = loginSubmitBtn.innerHTML;
            loginSubmitBtn.disabled = true;
            loginSubmitBtn.innerHTML = '<span class="loading-spinner"></span> Accediendo...';

            apiRequest('/auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password: pass })
            })
                .then(result => {
                    saveSession(result.user);
                    window.location.href = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL + 'dashboard' : 'dashboard.html';
                })
                .catch(error => {
                    alert(error.message || 'No se pudo conectar con el servidor.');
                    loginSubmitBtn.disabled = false;
                    loginSubmitBtn.innerHTML = originalText;
                });
        });
    }

    const forgotPasswordLink = document.getElementById('forgotPassword');
    const forgotPanel = document.getElementById('forgotPasswordPanel');
    const backToLoginBtn = document.getElementById('backToLoginBtn');
    const recoveryEmail = document.getElementById('recoveryEmail');
    const recoveryEmailMsg = document.getElementById('recoveryEmailMsg');
    const sendRecoveryBtn = document.getElementById('sendRecoveryBtn');
    const recoveryMessage = document.getElementById('recoveryMessage');
    const emailSentState = document.getElementById('emailSentState');
    const sentEmailShown = document.getElementById('sentEmailShown');
    const backToLoginFromSentBtn = document.getElementById('backToLoginFromSentBtn');

    function showRecoveryMessage(text, type = 'error') {
        if (!recoveryMessage) return;
        recoveryMessage.textContent = text;
        recoveryMessage.className = 'message show ' + type;
        setTimeout(() => recoveryMessage.classList.remove('show'), 5000);
    }

    function validateRecoveryEmail(email) {
        if (!email || !email.trim()) return { valid: false, message: 'El correo electrónico es obligatorio' };
        if (!email.includes('@') || !email.includes('.')) return { valid: false, message: 'Ingresa un correo electrónico válido' };
        return { valid: true, message: 'Correo válido ✓' };
    }

    function resetRecoveryPanel() {
        forgotPanel.classList.remove('active');
        loginSubmitBtn.style.display = 'block';
        recoveryMessage.classList.remove('show');
        emailSentState.classList.remove('active');

        if (recoveryEmail) {
            recoveryEmail.value = '';
            recoveryEmail.disabled = false;
            recoveryEmail.classList.remove('error', 'success');
        }
        if (recoveryEmailMsg) {
            recoveryEmailMsg.textContent = 'Ingresa tu correo registrado';
            recoveryEmailMsg.className = 'validation-message';
        }
        if (sendRecoveryBtn) {
            sendRecoveryBtn.disabled = false;
            sendRecoveryBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar enlace';
        }
    }

    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            forgotPanel.classList.add('active');
            loginSubmitBtn.style.display = 'none';
            recoveryMessage.classList.remove('show');
            emailSentState.classList.remove('active');

            if (recoveryEmail) recoveryEmail.classList.remove('error', 'success');
            if (recoveryEmailMsg) recoveryEmailMsg.className = 'validation-message';
        });
    }

    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function() {
            resetRecoveryPanel();
        });
    }

    if (backToLoginFromSentBtn) {
        backToLoginFromSentBtn.addEventListener('click', function() {
            resetRecoveryPanel();
        });
    }

    if (recoveryEmail) {
        recoveryEmail.addEventListener('input', function() {
            const result = validateRecoveryEmail(this.value);
            this.classList.remove('error', 'success');
            if (recoveryEmailMsg) recoveryEmailMsg.classList.remove('error', 'success');

            if (this.value.length === 0) {
                if (recoveryEmailMsg) {
                    recoveryEmailMsg.textContent = 'Ingresa tu correo registrado';
                    recoveryEmailMsg.className = 'validation-message';
                }
                return;
            }
            if (result.valid) { this.classList.add('success'); if (recoveryEmailMsg) recoveryEmailMsg.classList.add('success'); }
            else { this.classList.add('error'); if (recoveryEmailMsg) recoveryEmailMsg.classList.add('error'); }
            if (recoveryEmailMsg) recoveryEmailMsg.textContent = result.message;
        });
    }

    if (sendRecoveryBtn) {
        sendRecoveryBtn.addEventListener('click', function() {
            const email = recoveryEmail.value.trim();
            const result = validateRecoveryEmail(email);

            if (!result.valid) {
                recoveryEmail.classList.add('error');
                if (recoveryEmailMsg) {
                    recoveryEmailMsg.textContent = result.message;
                    recoveryEmailMsg.className = 'validation-message error';
                }
                showRecoveryMessage(result.message, 'error');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="recovery-loading"></span> Enviando...';

            sendRecoveryBtn.disabled = false;
            sendRecoveryBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar enlace';
            showRecoveryMessage('La recuperación por correo aún no está configurada.', 'error');
        });
    }

    setupRealtimeValidation();

    console.log('Ineova Login v6.4 listo');
});
