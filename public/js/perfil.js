(function() {
    'use strict';

    const BASE_URL = (typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '').replace(/\/?$/, '/');
    const API_BASE = (typeof window.API_BASE !== 'undefined' ? window.API_BASE : 'api/v1').replace(/\/+$/, '');

    async function apiRequest(path, options = {}) {
        const response = await fetch(API_BASE + path, {
            credentials: 'same-origin',
            ...options,
            headers: { 'Content-Type': 'application/json', ...(options.headers || {}) }
        });
        const result = await response.json();
        if (!response.ok || !result.ok) {
            const error = new Error(result.error || 'No se pudo completar la solicitud.');
            error.status = response.status;
            throw error;
        }
        return result;
    }

    let UNIVERSIDADES_SEED = [];
    let currentAuthUser = null;
    let activeProfile = null;
    let isReadOnlyView = false;

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const toggleIcon = document.getElementById('toggleIcon');

    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        if (toggleIcon) toggleIcon.className = 'fas fa-chevron-right';
    }
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            toggleIcon.className = isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    }

    let toastTimeout;
    function showToast(msg, type = '', dur = 3200) {
        const t = document.getElementById('toast');
        if (!t) return;
        t.textContent = msg;
        t.className = 'toast' + (type ? ' ' + type : '');
        t.classList.add('show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => t.classList.remove('show'), dur);
    }

    const AVATAR_COLORS = ['#1a6dff', '#0b7e3d', '#7c3aed', '#c53e3e', '#b26e1a', '#0a7e8c', '#d946a1', '#5c6bc0'];
    function getColorFromName(name) {
        if (!name) return AVATAR_COLORS[0];
        let hash = 0;
        for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
        return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    }
    function isValidAvatarUrl(url) {
        if (!url || typeof url !== 'string') return false;
        const t = url.trim();
        return t.startsWith('http://') || t.startsWith('https://') || t.startsWith('data:') || t.startsWith('blob:') || t.startsWith('/');
    }
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = String(str ?? '');
        return div.innerHTML;
    }
    function getRoleKey(profile) {
        const role = (profile && profile.role) || 'estudiante';
        if (role === 'admin') return 'admin';
        if (role === 'participante_universidad' || (profile && profile.organismo_tipo === 'universidad')) return 'universidad';
        if (role === 'participante_empresa' || (profile && profile.organismo_tipo === 'empresa')) return 'empresa';
        return 'estudiante';
    }
    function getRoleLabel(roleKey) {
        if (roleKey === 'admin') return 'Administrador';
        if (roleKey === 'universidad') return 'Coordinador Universitario';
        if (roleKey === 'empresa') return 'Empresa';
        return 'Estudiante becario';
    }

    function loadHeaderUser() {
        if (!currentAuthUser) return;
        const fullName = ((currentAuthUser.nombre || '') + ' ' + (currentAuthUser.apellido || '')).trim() || 'Usuario';
        const nameEl = document.getElementById('userNameDisplay');
        if (nameEl) nameEl.textContent = fullName;

        const avatarImg = document.getElementById('avatarImg');
        if (!avatarImg) return;
        const avatarContainer = avatarImg.parentNode;
        const url = currentAuthUser.avatar || localStorage.getItem('userAvatar') || '';
        if (isValidAvatarUrl(url)) {
            avatarImg.src = url;
            avatarImg.style.display = 'block';
            const existingSpan = avatarContainer.querySelector('span');
            if (existingSpan) existingSpan.remove();
        } else {
            avatarImg.style.display = 'none';
            let span = avatarContainer.querySelector('span');
            if (!span) {
                span = document.createElement('span');
                avatarContainer.appendChild(span);
            }
            span.style.background = getColorFromName(fullName);
            span.textContent = fullName.charAt(0).toUpperCase();
        }
    }

    async function loadSidebarBadges() {
        try {
            const convRes = await apiRequest('/conversations');
            const unreadMsgs = (convRes.conversations || []).reduce((sum, c) => sum + (parseInt(c.unread, 10) || 0), 0);
            const sideMsgEl = document.getElementById('sidebarMsgBadge');
            if (sideMsgEl) {
                sideMsgEl.textContent = unreadMsgs;
                sideMsgEl.style.display = unreadMsgs > 0 ? 'inline-block' : 'none';
            }
        } catch (e) {}
    }

    function initSidebar() {
        const rol = currentAuthUser ? currentAuthUser.rol : 'estudiante';

        const savedBtn = document.getElementById('savedSidebarBtn');
        const myOffersBtn = document.getElementById('myOffersSidebarBtn');
        const uniStudentsBtn = document.getElementById('universityStudentsSidebarBtn');
        const conveniosBtn = document.getElementById('conveniosSidebarBtn');
        const adminBtn = document.getElementById('adminSidebarBtn');

        if (rol === 'estudiante') {
            if (savedBtn) savedBtn.style.display = 'flex';
            if (myOffersBtn) {
                myOffersBtn.style.display = 'flex';
                const sp = myOffersBtn.querySelector('span');
                if (sp) sp.textContent = 'Mis Postulaciones';
            }
        } else if (rol === 'participante_empresa') {
            if (savedBtn) savedBtn.style.display = 'none';
            if (myOffersBtn) myOffersBtn.style.display = 'flex';
            if (conveniosBtn) conveniosBtn.style.display = 'flex';
        } else if (rol === 'participante_universidad') {
            if (savedBtn) savedBtn.style.display = 'none';
            if (myOffersBtn) myOffersBtn.style.display = 'none';
            if (uniStudentsBtn) uniStudentsBtn.style.display = 'flex';
            if (conveniosBtn) conveniosBtn.style.display = 'flex';
        } else if (rol === 'admin') {
            if (savedBtn) savedBtn.style.display = 'none';
            if (myOffersBtn) myOffersBtn.style.display = 'none';
            if (adminBtn) adminBtn.style.display = 'flex';
            if (conveniosBtn) conveniosBtn.style.display = 'flex';
            const msgBtn = document.getElementById('messagesSidebarBtn');
            if (msgBtn) msgBtn.style.display = 'none';
            const profBtn = document.getElementById('profileSidebarBtn');
            if (profBtn) profBtn.style.display = 'none';
            document.querySelectorAll('.sidebar-menu .menu-label').forEach(lbl => {
                if (lbl.textContent.trim().toLowerCase() === 'gestión') lbl.style.display = 'none';
            });
        }

        const links = [
            { id: 'notifSidebarBtn',              returnTo: 'notif' },
            { id: 'messagesSidebarBtn',           returnTo: 'messages' },
            { id: 'savedSidebarBtn',              returnTo: 'saved' },
            { id: 'myOffersSidebarBtn',           returnTo: 'mine' },
            { id: 'universityStudentsSidebarBtn', returnTo: 'university_students' },
            { id: 'conveniosSidebarBtn',          returnTo: 'convenios' },
            { id: 'adminSidebarBtn',              returnTo: 'admin' },
            { id: 'helpBtn',                      returnTo: 'help' }
        ];
        links.forEach(({ id, returnTo }) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('click', (e) => {
                e.preventDefault();
                localStorage.setItem('returnTo', returnTo);
                window.location.href = BASE_URL + 'dashboard';
            });
        });

        document.getElementById('logoutBtn')?.addEventListener('click', (e) => {
            e.preventDefault();
            fetch(API_BASE + '/auth/logout', { method: 'POST', credentials: 'same-origin' })
                .finally(() => {
                    localStorage.clear();
                    window.location.href = BASE_URL + 'login';
                });
        });

        document.getElementById('profileBtn')?.addEventListener('click', () => {
            if (isReadOnlyView) window.location.href = BASE_URL + 'perfil';
            else window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    function renderUniversidadesSelect(selectedId) {
        const select = document.getElementById('inputUniversidad');
        if (!select) return;
        select.innerHTML = '<option value="">Selecciona tu universidad</option>';
        UNIVERSIDADES_SEED.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = `${u.nombre}${u.siglas ? ' (' + u.siglas + ')' : ''}`;
            if (String(u.id) === String(selectedId)) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function renderRoleStats(roleKey, stats) {
        const container = document.getElementById('profileStatsContainer');
        if (!container) return;
        stats = stats || {};
        let items = [];
        if (roleKey === 'estudiante') {
            items = [
                { val: stats.aplicaciones || 0, label: 'Postulaciones', target: 'mine' },
                { val: stats.guardadas || 0,    label: 'Guardadas',     target: 'saved' }
            ];
        } else if (roleKey === 'empresa') {
            items = [
                { val: stats.publicadas || 0, label: 'Ofertas',   target: 'mine' },
                { val: stats.recibidas || 0,  label: 'Recibidas', target: 'mine' },
                { val: stats.convenios || 0,  label: 'Convenios', target: 'convenios' }
            ];
        } else if (roleKey === 'universidad') {
            items = [
                { val: stats.estudiantes_vigentes || 0,   label: 'Vigentes',   target: 'university_students' },
                { val: stats.estudiantes_pendientes || 0, label: 'Pendientes', target: 'university_students' },
                { val: stats.convenios || 0,              label: 'Convenios',  target: 'convenios' }
            ];
        } else {
            items = [
                { val: stats.coordinadores_pendientes || 0, label: 'Coord. pend.', target: 'admin' },
                { val: stats.empresas_pendientes || 0,      label: 'Emp. pend.',   target: 'admin' },
                { val: stats.convenios || 0,                label: 'Convenios',    target: 'convenios' }
            ];
        }

        container.innerHTML = items.map(it => `
            <div class="stat-item" data-return="${it.target}">
                <div class="stat-value">${it.val}</div>
                <div class="stat-label">${it.label}</div>
            </div>
        `).join('');
        container.style.visibility = 'visible';

        if (!isReadOnlyView) {
            container.querySelectorAll('.stat-item').forEach(el => {
                el.addEventListener('click', () => {
                    localStorage.setItem('returnTo', el.dataset.return);
                    window.location.href = BASE_URL + 'dashboard';
                });
            });
        }
    }

    function renderProfileCard(data) {
        const roleKey = getRoleKey(data);
        document.body.dataset.role = roleKey;

        const avatarImg = document.getElementById('profileAvatarImg');
        const initialEl = document.getElementById('profileInitial');
        const nameDisplay = document.getElementById('profileNameDisplay');
        const roleDisplay = document.getElementById('profileRoleDisplay');
        const emailDisplay = document.getElementById('profileEmailDisplay');
        const badgesContainer = document.getElementById('statusBadgesContainer');
        const avatarWrap = document.getElementById('profileAvatar');

        let avatarToShow = data.avatar || '';
        if (roleKey === 'empresa' || roleKey === 'universidad') {
            avatarWrap.classList.add('company');
            if (isValidAvatarUrl(data.logo)) avatarToShow = data.logo;
        } else {
            avatarWrap.classList.remove('company');
        }

        const fullName = ((data.nombre || '') + ' ' + (data.apellido || '')).trim();
        const displayName = (roleKey === 'empresa' || roleKey === 'universidad')
            ? (data.nombre_empresa || fullName || 'Organismo')
            : (fullName || 'Usuario');

        if (isValidAvatarUrl(avatarToShow)) {
            avatarImg.src = avatarToShow;
            avatarImg.style.display = 'block';
            initialEl.style.display = 'none';
        } else {
            avatarImg.style.display = 'none';
            initialEl.style.display = 'flex';
            initialEl.textContent = displayName.charAt(0).toUpperCase();
        }

        const verifiedIcon = (roleKey === 'empresa' && data.verificado)
            ? ' <i class="fas fa-check-circle" style="color:#1d9bf0; font-size:17px;" title="Empresa verificada"></i>'
            : '';
        nameDisplay.innerHTML = escapeHtml(displayName) + verifiedIcon;
        roleDisplay.textContent = getRoleLabel(roleKey);
        emailDisplay.innerHTML = '<i class="fas fa-envelope"></i> ' + escapeHtml(data.correo || '');

        let badgesHtml = '';
        if (roleKey === 'estudiante') {
            const vig = data.estado_vigencia || 'pendiente';
            if (vig === 'vigente') {
                badgesHtml += `<span style="background:#dcfce7; color:#166534; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;"><i class="fas fa-check-circle"></i> Estudiante vigente${data.universidad_siglas ? ' · ' + escapeHtml(data.universidad_siglas) : ''}</span>`;
            } else if (vig === 'rechazado') {
                badgesHtml += `<span style="background:#fee2e2; color:#991b1b; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;"><i class="fas fa-times-circle"></i> Vigencia rechazada</span>`;
            } else {
                badgesHtml += `<span style="background:#fef3c7; color:#92400e; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;"><i class="fas fa-clock"></i> Vigencia pendiente por universidad</span>`;
            }
            if (data.notas_certificadas) {
                const avgText = data.promedio !== null && data.promedio !== '' ? ` · Promedio ${escapeHtml(data.promedio)}` : '';
                badgesHtml += `<span style="background:#e0f2fe; color:#0369a1; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;"><i class="fas fa-certificate"></i> Notas certificadas${avgText}</span>`;
            }
        } else if (roleKey === 'empresa') {
            if (data.verificado) {
                badgesHtml += `<span style="background:#e0f2fe; color:#0369a1; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;"><i class="fas fa-check-circle" style="color:#1d9bf0;"></i> Empresa verificada</span>`;
            } else if (data.estado_verificacion === 'pendiente') {
                badgesHtml += `<span style="background:#fef3c7; color:#92400e; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700;"><i class="fas fa-clock"></i> Verificación en revisión</span>`;
            }
        }
        badgesContainer.innerHTML = badgesHtml;

        renderRoleStats(roleKey, data.stats);
    }

    function renderSkills(skills) {
        const container = document.getElementById('skillsContainer');
        if (!container) return;
        container.innerHTML = '';
        if (!skills || !skills.length) {
            container.innerHTML = '<span style="font-size:13px; color:#8ba0b8;">Sin habilidades registradas.</span>';
            return;
        }
        skills.forEach((skill, index) => {
            const name = typeof skill === 'string' ? skill : (skill.nombre || '');
            const chip = document.createElement('span');
            chip.className = 'skill-chip';
            chip.innerHTML = `${escapeHtml(name)}${isReadOnlyView ? '' : `<i class="fas fa-times" data-index="${index}" title="Eliminar"></i>`}`;
            container.appendChild(chip);
        });
        if (!isReadOnlyView) {
            container.querySelectorAll('.skill-chip i').forEach(icon => {
                icon.addEventListener('click', function() {
                    const idx = parseInt(this.dataset.index, 10);
                    activeProfile.habilidades.splice(idx, 1);
                    renderSkills(activeProfile.habilidades);
                });
            });
        }
    }

    function renderForm(data) {
        const roleKey = getRoleKey(data);
        document.getElementById('inputNombre').value = data.nombre || '';
        document.getElementById('inputApellido').value = data.apellido || '';
        document.getElementById('inputCorreo').value = data.correo || '';

        const bioLabel = document.getElementById('bioLabel');
        const sectionTitle = document.getElementById('formSectionTitle');
        const companyVerifGroup = document.getElementById('companyVerifGroup');
        if (companyVerifGroup) companyVerifGroup.style.display = 'none';

        if (roleKey === 'empresa' || roleKey === 'universidad') {
            sectionTitle.textContent = roleKey === 'universidad' ? 'Datos de la universidad' : 'Datos de la empresa';
            bioLabel.textContent = 'Descripción';

            const orgNameLabel = document.getElementById('orgNameLabel');
            const orgNameInput = document.getElementById('inputOrgName');
            const rifLabel = document.getElementById('rifLabel');
            const rifInput = document.getElementById('inputRif');
            const rankingInput = document.getElementById('inputRankingUni');

            if (orgNameLabel) orgNameLabel.textContent = roleKey === 'universidad' ? 'Nombre de la universidad (oficial)' : 'Nombre de la empresa';
            if (orgNameInput) {
                orgNameInput.value = data.nombre_empresa || '';
                orgNameInput.disabled = roleKey === 'universidad' || isReadOnlyView;
            }
            if (rifLabel) rifLabel.textContent = roleKey === 'universidad' ? 'Siglas (oficiales)' : 'RIF';
            if (rifInput) {
                rifInput.value = roleKey === 'universidad' ? (data.siglas || '') : (data.rif || '');
                rifInput.disabled = roleKey === 'universidad' || isReadOnlyView;
            }
            if (rankingInput) {
                rankingInput.value = data.ranking_uni || '';
                rankingInput.disabled = true; // Coordinadores no pueden alterar el ranking
            }

            document.getElementById('inputSitioWeb').value = data.sitio_web || '';
            document.getElementById('inputUbicacion').value = data.ubicacion || '';
            document.getElementById('inputBiografia').value = data.descripcion || '';

            if (roleKey === 'empresa' && companyVerifGroup && !isReadOnlyView) {
                companyVerifGroup.style.display = 'flex';
                const statusEl = document.getElementById('companyVerifStatus');
                const viewLink = document.getElementById('viewCompanyVerifLink');
                const uploadBtn = document.getElementById('uploadCompanyVerifBtn');
                const st = data.estado_verificacion || 'no_enviado';
                if (data.verificado || st === 'verificado') {
                    statusEl.innerHTML = '<span style="color:#0369a1; font-weight:600;"><i class="fas fa-check-circle" style="color:#1d9bf0;"></i> Tu empresa ya está verificada oficialmente.</span>';
                    if (uploadBtn) uploadBtn.style.display = 'none';
                } else if (st === 'pendiente') {
                    statusEl.innerHTML = '<span style="color:#92400e; font-weight:600;"><i class="fas fa-clock"></i> Documento enviado. En espera de revisión por un administrador.</span>';
                } else if (st === 'rechazado') {
                    statusEl.innerHTML = '<span style="color:#991b1b; font-weight:600;"><i class="fas fa-exclamation-circle"></i> Tu solicitud anterior fue rechazada. Puedes enviar un nuevo documento.</span>';
                } else {
                    statusEl.textContent = 'Envía tu RIF (PDF o imagen) para obtener el sello azul de verificación.';
                }
                if (data.documento_verificacion_url && viewLink) {
                    viewLink.href = data.documento_verificacion_url;
                    viewLink.style.display = 'inline-flex';
                }
            }
        } else if (roleKey === 'estudiante') {
            sectionTitle.textContent = 'Datos del estudiante becario';
            bioLabel.textContent = 'Biografía / Perfil académico';

            document.getElementById('inputCedula').value = data.cedula || '';
            document.getElementById('inputTelefono').value = data.telefono || '';
            document.getElementById('inputGithub').value = data.github_url || '';
            document.getElementById('inputBiografia').value = data.autobiografia || '';
            renderUniversidadesSelect(data.universidad_id || '');
            renderSkills(data.habilidades || []);

            const uniSelect = document.getElementById('inputUniversidad');
            const uniNotice = document.getElementById('universityChangeNotice');
            const uniNoticeText = document.getElementById('universityChangeText');
            const reqBtn = document.getElementById('requestUniChangeBtn');
            if (!isReadOnlyView && data.universidad_id) {
                if (data.cambio_universidad_habilitado) {
                    uniSelect.disabled = false;
                    uniNotice.style.display = 'block';
                    uniNoticeText.innerHTML = '<strong style="color:#166534;">Tu universidad habilitó el cambio.</strong> Selecciona tu nueva universidad y guarda los cambios.';
                    reqBtn.style.display = 'none';
                } else {
                    uniSelect.disabled = true;
                    uniNotice.style.display = 'block';
                    if (data.cambio_universidad_solicitado) {
                        uniNoticeText.innerHTML = '<i class="fas fa-clock" style="color:#d97706;"></i> Solicitud enviada a tu universidad actual. Espera a que habiliten el cambio.';
                        reqBtn.style.display = 'none';
                    } else {
                        uniNoticeText.textContent = 'Para cambiar de universidad debes enviar una solicitud a tu universidad actual para que habilite el cambio.';
                        reqBtn.style.display = 'inline-flex';
                    }
                }
            } else if (uniNotice) {
                uniNotice.style.display = 'none';
            }

            const gradesStatus = document.getElementById('gradesStatusInfo');
            const viewGradesLink = document.getElementById('viewGradesDocLink');
            const gradesUploadBox = document.getElementById('gradesUploadBox');
            if (gradesStatus) {
                if (data.notas_certificadas) {
                    gradesStatus.innerHTML = `<span style="color:#0369a1; font-weight:600;"><i class="fas fa-certificate"></i> Notas certificadas por tu universidad${data.promedio ? ' · Promedio: ' + escapeHtml(data.promedio) : ''}</span>`;
                } else if (data.promedio) {
                    gradesStatus.innerHTML = `Promedio registrado: <strong>${escapeHtml(data.promedio)}</strong> (pendiente de certificación).`;
                } else {
                    gradesStatus.textContent = isReadOnlyView
                        ? 'Sin notas certificadas aún.'
                        : 'Sube tu constancia de notas para que tu universidad valide tu promedio y active el sello de Notas Certificadas.';
                }
            }
            if (data.constancia_notas_url && viewGradesLink) {
                viewGradesLink.href = data.constancia_notas_url;
                viewGradesLink.style.display = 'inline-flex';
            }
            if (isReadOnlyView && gradesUploadBox) {
                document.getElementById('uploadGradesBtn').style.display = 'none';
            }
        } else {
            sectionTitle.textContent = 'Datos del administrador';
            bioLabel.textContent = 'Notas';
        }

        updateBioCounter();

        if (isReadOnlyView) {
            document.getElementById('pageTitle').textContent = 'Perfil de ' + ((roleKey === 'empresa' || roleKey === 'universidad')
                ? (data.nombre_empresa || data.nombre || 'Organismo')
                : ((data.nombre || '') + ' ' + (data.apellido || '')).trim());
            document.querySelectorAll('#profileForm input, #profileForm select, #profileForm textarea').forEach(el => {
                el.disabled = true;
            });
            document.getElementById('avatarEditBtn').style.display = 'none';
            const skillWrap = document.querySelector('.skill-input-wrapper');
            if (skillWrap) skillWrap.style.display = 'none';
            const formActions = document.querySelector('#profileForm .form-actions');
            if (formActions) formActions.style.display = 'none';
            const cedulaGroup = document.getElementById('inputCedula')?.closest('.form-group');
            if (cedulaGroup) cedulaGroup.style.display = 'none';
            document.getElementById('ownProfileActions').style.display = 'none';
            document.getElementById('publicProfileActions').style.display = 'flex';
        }
    }

    function updateBioCounter() {
        const bio = document.getElementById('inputBiografia');
        const counter = document.getElementById('bioCount');
        if (bio && counter) counter.textContent = bio.value.length;
    }

    function addSkill() {
        if (isReadOnlyView) return;
        const input = document.getElementById('skillInput');
        const value = (input?.value || '').trim();
        if (!value) return;
        if (value.length > 50) { showToast('Máximo 50 caracteres por habilidad', 'error'); return; }
        if (!activeProfile.habilidades) activeProfile.habilidades = [];
        if (activeProfile.habilidades.some(h => h.toLowerCase() === value.toLowerCase())) {
            showToast('Esa habilidad ya está añadida', 'error');
            return;
        }
        if (activeProfile.habilidades.length >= 20) {
            showToast('Máximo 20 habilidades', 'error');
            return;
        }
        activeProfile.habilidades.push(value);
        renderSkills(activeProfile.habilidades);
        input.value = '';
        input.focus();
    }

    function setupFileUploads() {
        const editBtn = document.getElementById('avatarEditBtn');
        const fileInput = document.getElementById('avatarFileInput');
        if (editBtn && fileInput) {
            editBtn.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;
                if (!file.type.startsWith('image/') || file.size > 2 * 1024 * 1024) {
                    showToast('Selecciona una imagen válida (máximo 2MB)', 'error');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    apiRequest('/profile/avatar', { method: 'PUT', body: JSON.stringify({ avatar: e.target.result }) })
                        .then(res => {
                            activeProfile.avatar = res.url;
                            activeProfile.logo = res.url;
                            currentAuthUser.avatar = res.url;
                            localStorage.setItem('userAvatar', res.url);
                            renderProfileCard(activeProfile);
                            loadHeaderUser();
                            showToast('Foto actualizada correctamente', 'success');
                        }).catch(err => showToast(err.message, 'error'));
                };
                reader.readAsDataURL(file);
                this.value = '';
            });
        }

        const upGradesBtn = document.getElementById('uploadGradesBtn');
        const gradesInput = document.getElementById('gradesFileInput');
        if (upGradesBtn && gradesInput) {
            upGradesBtn.addEventListener('click', () => gradesInput.click());
            gradesInput.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;
                if (file.size > 5 * 1024 * 1024) { showToast('El archivo no debe superar 5MB', 'error'); this.value = ''; return; }
                const reader = new FileReader();
                reader.onload = (e) => {
                    apiRequest('/profile/grades-document', { method: 'PUT', body: JSON.stringify({ document: e.target.result }) })
                        .then(res => {
                            activeProfile.constancia_notas_url = res.url;
                            renderForm(activeProfile);
                            showToast('Constancia de notas subida correctamente', 'success');
                        }).catch(err => showToast(err.message, 'error'));
                };
                reader.readAsDataURL(file);
                this.value = '';
            });
        }

        const upVerifBtn = document.getElementById('uploadCompanyVerifBtn');
        const verifInput = document.getElementById('companyVerifFileInput');
        if (upVerifBtn && verifInput) {
            upVerifBtn.addEventListener('click', () => verifInput.click());
            verifInput.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;
                if (file.size > 5 * 1024 * 1024) { showToast('El archivo no debe superar 5MB', 'error'); this.value = ''; return; }
                const reader = new FileReader();
                reader.onload = (e) => {
                    apiRequest('/profile/verification-document', { method: 'PUT', body: JSON.stringify({ document: e.target.result }) })
                        .then(res => {
                            activeProfile.documento_verificacion_url = res.url;
                            activeProfile.estado_verificacion = res.estado_verificacion;
                            renderProfileCard(activeProfile);
                            renderForm(activeProfile);
                            showToast('Documento enviado para verificación oficial', 'success');
                        }).catch(err => showToast(err.message, 'error'));
                };
                reader.readAsDataURL(file);
                this.value = '';
            });
        }

        document.getElementById('requestUniChangeBtn')?.addEventListener('click', async () => {
            try {
                const res = await apiRequest('/profile/request-university-change', { method: 'POST', body: JSON.stringify({}) });
                activeProfile.cambio_universidad_solicitado = true;
                renderForm(activeProfile);
                showToast(res.message || 'Solicitud enviada', 'success');
            } catch (err) {
                showToast(err.message, 'error');
            }
        });
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        if (isReadOnlyView) return;

        const nombre = document.getElementById('inputNombre').value.trim();
        const apellido = document.getElementById('inputApellido').value.trim();
        if (nombre.length < 2 || apellido.length < 2) {
            showToast('Nombre y apellido deben tener al menos 2 caracteres', 'error');
            return;
        }

        const roleKey = getRoleKey(activeProfile);
        const payload = { nombre, apellido };

        if (roleKey === 'empresa' || roleKey === 'universidad') {
            const web = (document.getElementById('inputSitioWeb').value || '').trim();
            if (web && !/^https?:\/\/.+/i.test(web)) {
                showToast('El sitio web debe comenzar con http:// o https://', 'error');
                return;
            }
            payload.sitio_web = web;
            payload.ubicacion = (document.getElementById('inputUbicacion').value || '').trim();
            payload.descripcion = document.getElementById('inputBiografia').value.trim();
            if (roleKey === 'empresa') {
                payload.nombre_empresa = (document.getElementById('inputOrgName').value || '').trim() || nombre;
                payload.rif = (document.getElementById('inputRif').value || '').trim();
            }
        } else if (roleKey === 'estudiante') {
            const github = (document.getElementById('inputGithub').value || '').trim();
            if (github && !/^https?:\/\/.+/i.test(github)) {
                showToast('La URL de GitHub debe comenzar con http:// o https://', 'error');
                return;
            }
            const uniRaw = document.getElementById('inputUniversidad').value;
            payload.universidad_id = uniRaw ? parseInt(uniRaw, 10) : '';
            payload.cedula = (document.getElementById('inputCedula').value || '').trim();
            payload.telefono = (document.getElementById('inputTelefono').value || '').trim();
            payload.autobiografia = document.getElementById('inputBiografia').value.trim();
            payload.github_url = github;
            payload.habilidades = activeProfile.habilidades || [];
        }

        apiRequest('/profile', { method: 'PUT', body: JSON.stringify(payload) })
            .then(async () => {
                const refreshed = await apiRequest('/profile');
                activeProfile = refreshed.profile;
                currentAuthUser.nombre = activeProfile.nombre;
                currentAuthUser.apellido = activeProfile.apellido;
                localStorage.setItem('userNombre', activeProfile.nombre);
                localStorage.setItem('userApellido', activeProfile.apellido);
                localStorage.setItem('userName', (activeProfile.nombre + ' ' + activeProfile.apellido).trim());
                renderProfileCard(activeProfile);
                renderForm(activeProfile);
                loadHeaderUser();

                const status = document.getElementById('saveStatus');
                status.classList.add('show');
                setTimeout(() => status.classList.remove('show'), 2500);
                showToast('Perfil guardado correctamente', 'success');
            }).catch(err => showToast(err.message, 'error'));
    }

    function setupCompanyPlansTab(data) {
        const tabsBar = document.getElementById('companyProfileTabs');
        const plansSection = document.getElementById('companyPlansSection');
        const profileForm = document.getElementById('profileForm');
        const tabDataBtn = document.getElementById('tabBtnProfileData');
        const tabPlansBtn = document.getElementById('tabBtnCompanyPlans');

        if (!tabsBar || !plansSection || !profileForm) return;
        const isOwnCompany = getRoleKey(data) === 'empresa' && !isReadOnlyView && data.plan;
        if (!isOwnCompany) {
            tabsBar.style.display = 'none';
            plansSection.style.display = 'none';
            return;
        }

        tabsBar.style.display = 'flex';
        const plan = data.plan;
        let statusBanner = '';
        if (plan.id === 1) {
            if (plan.trial_activo) {
                statusBanner = `
                    <div style="background:#f0fdf4; border:1.5px solid #86efac; border-radius:16px; padding:18px 20px; margin-bottom:22px;">
                        <div style="font-weight:700; color:#166534; font-size:15px;"><i class="fas fa-gift"></i> Período de prueba gratuita activo</div>
                        <p style="font-size:13.5px; color:#15803d; margin-top:6px;">Te quedan <strong>${plan.trial_dias_restantes} día(s)</strong> gratis para publicar ofertas. Al finalizar el plazo deberás elegir entre el Plan Estándar o el Plan Extra.</p>
                    </div>`;
            } else {
                statusBanner = `
                    <div style="background:#fef2f2; border:1.5px solid #fca5a5; border-radius:16px; padding:18px 20px; margin-bottom:22px;">
                        <div style="font-weight:700; color:#991b1b; font-size:15px;"><i class="fas fa-exclamation-triangle"></i> Tu período de prueba gratuita de 7 días ha finalizado</div>
                        <p style="font-size:13.5px; color:#b91c1c; margin-top:6px;">Para continuar publicando ofertas, elige un plan y solicita su activación a un administrador de Ineova.</p>
                    </div>`;
            }
        } else if (plan.id === 2) {
            statusBanner = `
                <div style="background:#eff6ff; border:1.5px solid #93c5fd; border-radius:16px; padding:18px 20px; margin-bottom:22px;">
                    <div style="font-weight:700; color:#1d4ed8; font-size:15px;"><i class="fas fa-check-circle"></i> Plan Estándar activo ($29.99)</div>
                    <p style="font-size:13.5px; color:#1e40af; margin-top:6px;">Publicaciones activas: <strong>${plan.ofertas_publicadas} / ${plan.limite_ofertas || 10}</strong>.</p>
                </div>`;
        } else {
            statusBanner = `
                <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7); border:1.5px solid #fcd34d; border-radius:16px; padding:18px 20px; margin-bottom:22px;">
                    <div style="font-weight:700; color:#92400e; font-size:15px;"><i class="fas fa-crown"></i> Plan Extra activo ($79.99)</div>
                    <p style="font-size:13.5px; color:#b45309; margin-top:6px;">Publicaciones sin límite y tus ofertas aparecen <strong>destacadas</strong> para los estudiantes que coincidan con tus requisitos.</p>
                </div>`;
        }

        const isStd = plan.id === 2;
        const isExtra = plan.id === 3;
        plansSection.innerHTML = `
            ${statusBanner}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:18px; margin-bottom:20px;">
                <div style="border:${isStd ? '2px solid #0A66C2' : '1.5px solid #d6e5f5'}; border-radius:18px; padding:22px; background:${isStd ? '#f4f9ff' : '#ffffff'}; display:flex; flex-direction:column; gap:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="font-size:18px; color:#0b253b;">Plan Estándar</h3>
                        ${isStd ? '<span style="background:#0A66C2; color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:999px;">Plan actual</span>' : ''}
                    </div>
                    <div style="font-size:28px; font-weight:800; color:#0A66C2;">$29.99 <span style="font-size:13px; font-weight:500; color:#64748b;">USD</span></div>
                    <ul style="list-style:none; display:flex; flex-direction:column; gap:8px; font-size:13.5px; color:#334155;">
                        <li><i class="fas fa-check-circle" style="color:#0A66C2;"></i> Hasta <strong>10 publicaciones</strong> activas</li>
                        <li><i class="fas fa-check-circle" style="color:#0A66C2;"></i> Gestión de postulantes y chat habilitado al aceptar</li>
                        <li><i class="fas fa-check-circle" style="color:#0A66C2;"></i> Convenios con universidades</li>
                    </ul>
                </div>

                <div style="border:${isExtra ? '2px solid #d97706' : '1.5px solid #fde68a'}; border-radius:18px; padding:22px; background:${isExtra ? '#fffbeb' : '#fffdf7'}; display:flex; flex-direction:column; gap:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="font-size:18px; color:#0b253b;"><i class="fas fa-star" style="color:#d97706;"></i> Plan Extra</h3>
                        ${isExtra ? '<span style="background:#d97706; color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:999px;">Plan actual</span>' : ''}
                    </div>
                    <div style="font-size:28px; font-weight:800; color:#b45309;">$79.99 <span style="font-size:13px; font-weight:500; color:#64748b;">USD</span></div>
                    <ul style="list-style:none; display:flex; flex-direction:column; gap:8px; font-size:13.5px; color:#334155;">
                        <li><i class="fas fa-check-circle" style="color:#d97706;"></i> <strong>Publicaciones sin límite</strong></li>
                        <li><i class="fas fa-star" style="color:#d97706;"></i> <strong>Ofertas destacadas</strong> para los estudiantes que coincidan con tus especificaciones</li>
                        <li><i class="fas fa-check-circle" style="color:#d97706;"></i> Prioridad al inicio del feed de becarios compatibles</li>
                    </ul>
                </div>
            </div>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; font-size:13px; color:#475569;">
                <i class="fas fa-shield-alt" style="color:#0A66C2;"></i> <strong>Gestión de planes:</strong> La activación o cambio de suscripción es configurada directamente por un <strong>administrador de Ineova</strong>.
            </div>
        `;

        function switchTab(tab) {
            if (tab === 'planes') {
                profileForm.style.display = 'none';
                plansSection.style.display = 'block';
                tabPlansBtn.className = 'btn btn-primary';
                tabDataBtn.className = 'btn btn-outline';
            } else {
                plansSection.style.display = 'none';
                profileForm.style.display = 'block';
                tabDataBtn.className = 'btn btn-primary';
                tabPlansBtn.className = 'btn btn-outline';
            }
        }

        tabDataBtn?.addEventListener('click', () => switchTab('datos'));
        tabPlansBtn?.addEventListener('click', () => switchTab('planes'));

        if (window.location.hash === '#planes') {
            switchTab('planes');
        }
    }

    function setupSecurityModals() {
        const passModal = document.getElementById('changePasswordModal');
        const delModal = document.getElementById('deleteAccountModal');

        document.getElementById('changePasswordBtn')?.addEventListener('click', () => {
            document.getElementById('changePasswordForm').reset();
            passModal.classList.add('active');
        });
        document.getElementById('closePasswordModal')?.addEventListener('click', () => passModal.classList.remove('active'));
        document.getElementById('cancelPasswordModal')?.addEventListener('click', () => passModal.classList.remove('active'));

        document.getElementById('changePasswordForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const current_password = document.getElementById('currentPasswordInput').value;
            const new_password = document.getElementById('newPasswordInput').value;
            const confirm_password = document.getElementById('confirmNewPasswordInput').value;
            if (new_password !== confirm_password) { showToast('Las contraseñas nuevas no coinciden', 'error'); return; }
            try {
                await apiRequest('/profile/password', { method: 'PUT', body: JSON.stringify({ current_password, new_password, confirm_password }) });
                passModal.classList.remove('active');
                showToast('Contraseña actualizada correctamente', 'success');
            } catch (err) { showToast(err.message, 'error'); }
        });

        document.getElementById('deleteAccountBtn')?.addEventListener('click', () => {
            document.getElementById('deleteAccountForm').reset();
            delModal.classList.add('active');
        });
        document.getElementById('closeDeleteModal')?.addEventListener('click', () => delModal.classList.remove('active'));
        document.getElementById('cancelDeleteModal')?.addEventListener('click', () => delModal.classList.remove('active'));

        document.getElementById('deleteAccountForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('deletePasswordInput').value;
            try {
                await apiRequest('/profile', { method: 'DELETE', body: JSON.stringify({ password }) });
                localStorage.clear();
                delModal.classList.remove('active');
                showToast('Cuenta eliminada. Redirigiendo...', 'success');
                setTimeout(() => { window.location.href = BASE_URL + 'login'; }, 1000);
            } catch (err) { showToast(err.message, 'error'); }
        });
    }

    async function init() {
        try {
            const meResult = await apiRequest('/auth/me');
            currentAuthUser = meResult.user;
            localStorage.setItem('userEmail', currentAuthUser.correo || '');
            localStorage.setItem('userType', currentAuthUser.rol || 'estudiante');
            localStorage.setItem('userIdNumeric', String(currentAuthUser.id || ''));
            localStorage.setItem('userNombre', currentAuthUser.nombre || '');
            localStorage.setItem('userApellido', currentAuthUser.apellido || '');
            localStorage.setItem('userName', ((currentAuthUser.nombre || '') + ' ' + (currentAuthUser.apellido || '')).trim() || 'Usuario');
            localStorage.setItem('userAvatar', currentAuthUser.avatar || '');

            const params = new URLSearchParams(window.location.search);
            const targetUserId = parseInt(params.get('user') || '0', 10);
            isReadOnlyView = targetUserId > 0 && targetUserId !== parseInt(currentAuthUser.id, 10);

            if (currentAuthUser.rol === 'admin' && !isReadOnlyView) {
                window.location.href = BASE_URL + 'dashboard';
                return;
            }

            const [profileRes, uniRes] = await Promise.all([
                apiRequest(isReadOnlyView ? '/profiles/' + targetUserId : '/profile'),
                apiRequest('/universities')
            ]);
            activeProfile = profileRes.profile;
            UNIVERSIDADES_SEED = uniRes.universities || [];
        } catch (err) {
            showToast(err.message || 'No se pudo cargar el perfil.', 'error');
            if (err.status === 401) window.location.href = BASE_URL + 'login';
            return;
        }

        loadHeaderUser();
        loadSidebarBadges();
        initSidebar();
        renderProfileCard(activeProfile);
        renderForm(activeProfile);
        setupCompanyPlansTab(activeProfile);

        if (!isReadOnlyView) {
            document.getElementById('profileForm')?.addEventListener('submit', handleFormSubmit);
            document.getElementById('resetFormBtn')?.addEventListener('click', () => {
                renderForm(activeProfile);
                showToast('Formulario restablecido');
            });
            document.getElementById('inputBiografia')?.addEventListener('input', updateBioCounter);
            document.getElementById('addSkillBtn')?.addEventListener('click', addSkill);
            document.getElementById('skillInput')?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); addSkill(); }
            });
            setupFileUploads();
            setupSecurityModals();
        }

        localStorage.removeItem('returnTo');
        setTimeout(() => { document.body.style.opacity = '1'; }, 120);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
