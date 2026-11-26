

(function() {
    'use strict';

    const BASE_URL = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '';
    const API_BASE = (typeof window.API_BASE !== 'undefined' ? window.API_BASE : 'backend/public/index.php/api/v1').replace(/\/+$/, '');

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

        let ORGANISMOS_SEED = [];
        let PARTICIPANTE_ORGANISMO_SEED = [];

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
            toggleIcon.className = isCollapsed ?
                'fas fa-chevron-right' : 'fas fa-chevron-left';
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    }

    let toastTimeout;
    function showToast(msg, dur = 3000) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => t.classList.remove('show'), dur);
    }

    function getUserId() {
        const email = localStorage.getItem('userEmail') || '';
        if (email) return email;
        let fallback = localStorage.getItem('userId_fallback');
        if (!fallback) {
            fallback = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('userId_fallback', fallback);
        }
        return fallback;
    }

    function getUserIdNumeric() {
        const raw = localStorage.getItem('userIdNumeric');
        return raw ? parseInt(raw, 10) : 0;
    }

    function getCurrentUser() {
        const name = localStorage.getItem('userName') ||
                     ((localStorage.getItem('userNombre') || '') + ' ' +
                      (localStorage.getItem('userApellido') || '')).trim() ||
                     'Usuario Ineova';
        const type = localStorage.getItem('userType') || 'estudiante';
        return {
            userId: getUserId(),
            userIdNumeric: getUserIdNumeric(),
            name: name,
            avatar: localStorage.getItem('userAvatar') || '',
            type: type,
            email: localStorage.getItem('userEmail') || '',
            initial: name.charAt(0).toUpperCase()
        };
    }

    function getRoleSimple() {
        const t = (getCurrentUser().type || '').toLowerCase();
        if (t === 'estudiante') return 'estudiante';
        if (t === 'participante_empresa') return 'participante';
        if (t === 'participante_universidad') return 'participante';
        if (t === 'empresa') return 'participante';
        if (t === 'universidad') return 'participante';
        if (t === 'admin') return 'participante';
        return 'estudiante';
    }

    function getTipoParticipante() {
        const t = (getCurrentUser().type || '').toLowerCase();
        if (t === 'participante_empresa' || t === 'empresa') return 'empresa';
        if (t === 'participante_universidad' || t === 'universidad') return 'universidad';
        return null;
    }

    function isParticipant() { return getTipoParticipante() === 'empresa'; }
    function isStudent() { return !isParticipant(); }

    const AVATAR_COLORS = [
        '#1a6dff', '#0b7e3d', '#7c3aed', '#c53e3e', '#b26e1a',
        '#0a7e8c', '#d946a1', '#5c6bc0', '#00897b', '#e65100'
    ];

    function getColorFromName(name) {
        if (!name) return AVATAR_COLORS[0];
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    }

    function isValidAvatarUrl(url) {
        if (!url || typeof url !== 'string') return false;
        const t = url.trim();
        return t.startsWith('http') || t.startsWith('data:') || t.startsWith('blob:') || t.startsWith('/');
    }

    function loadProfile() {
        const user = getCurrentUser();
        const nameEl = document.getElementById('userNameDisplay');
        if (nameEl) nameEl.textContent = user.name;

        const avatarImg = document.getElementById('avatarImg');
        if (!avatarImg) return;
        const avatarContainer = avatarImg.parentNode;

        if (isValidAvatarUrl(user.avatar)) {
            avatarImg.src = user.avatar;
            avatarImg.style.display = 'block';
        } else {
            avatarImg.style.display = 'none';
            avatarContainer.innerHTML =
                `<span style="background:${getColorFromName(user.name)};">${user.initial}</span>`;
        }
    }

    function updateMessagesBadge() {
        let totalUnread = 0;
        try {
            const convs = JSON.parse(localStorage.getItem('ineova_conversations') || '[]');
            totalUnread = convs
                .filter(c => c.status === 'active')
                .reduce((sum, c) => sum + (c.unread || 0), 0);
        } catch (e) {}

        const headerBadge = document.getElementById('messagesCount');
        const sidebarBadge = document.getElementById('sidebarMsgBadge');
        if (headerBadge) {
            headerBadge.textContent = totalUnread;
            headerBadge.style.display = totalUnread > 0 ? 'inline-block' : 'none';
        }
        if (sidebarBadge) {
            sidebarBadge.textContent = totalUnread;
            sidebarBadge.style.display = totalUnread > 0 ? 'inline-block' : 'none';
        }
    }

    function updateNotifBadge() {
        let unread = 0;
        try {
            unread = parseInt(localStorage.getItem('ineova_notif_unread') || '0', 10);
        } catch (e) {}
        const headerBadge = document.getElementById('notificationCount');
        const sidebarBadge = document.getElementById('sidebarNotifBadge');
        if (headerBadge) {
            headerBadge.textContent = unread;
            headerBadge.style.display = unread > 0 ? 'inline-block' : 'none';
        }
        if (sidebarBadge) {
            sidebarBadge.textContent = unread;
            sidebarBadge.style.display = unread > 0 ? 'inline-block' : 'none';
        }
    }

    function loadOrganismos() {
        return ORGANISMOS_SEED;
    }

    function loadMiAdscripcion() {
        if (!isParticipant()) return [];
        const uid = getCurrentUser().userIdNumeric;
        return PARTICIPANTE_ORGANISMO_SEED
            .filter(po => po.usuario_id === uid && po.activo === 1);
    }

    function getMyOrganismos() {
        if (!isParticipant()) return [];
        const orgs = loadOrganismos();
        const adsc = loadMiAdscripcion();
        return adsc.map(a => {
            const org = orgs.find(o => Number(o.id) === Number(a.organismo_id));
            return org ? { ...org, id: Number(org.id), rol_en_organismo: a.rol_en_organismo } : null;
        }).filter(Boolean);
    }

    function getActiveOrganismId() {
        if (!isParticipant()) return null;
        const raw = localStorage.getItem('activeOrganismId');
        const myOrgs = getMyOrganismos();
        if (!myOrgs.length) return null;
        const saved = raw ? parseInt(raw, 10) : null;
        if (saved && myOrgs.some(o => Number(o.id) === saved)) return saved;
        return myOrgs[0].id;
    }

    function setActiveOrganismId(id) {
        localStorage.setItem('activeOrganismId', String(id));
    }

    function getActiveOrganism() {
        if (!isParticipant()) return null;
        const id = getActiveOrganismId();
        if (!id) return null;
        return getMyOrganismos().find(o => Number(o.id) === Number(id)) || null;
    }

    function renderOrganismSelector() {
        const card = document.getElementById('organismCard');
        const select = document.getElementById('organismSelect');
        const meta = document.getElementById('organismMeta');

        if (isStudent()) {
            card.style.display = 'none';
            document.getElementById('studentWarning').style.display = 'block';
            return;
        }

        document.getElementById('studentWarning').style.display = 'none';

        const myOrgs = getMyOrganismos();
        if (!myOrgs.length) {
            card.style.display = 'none';
            return;
        }

        card.style.display = 'block';
        select.innerHTML = '';
        myOrgs.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.id;
            const tipoLabel = o.tipo === 'universidad' ? 'Universidad' : 'Empresa';
            opt.textContent = `${o.nombre} · ${tipoLabel}`;
            if (o.id === getActiveOrganismId()) opt.selected = true;
            select.appendChild(opt);
        });

        const active = getActiveOrganism();
        if (active) {
            const tipoLabel = active.tipo === 'universidad' ? 'Universidad' : 'Empresa';
            const rolLabel = active.rol_en_organismo || 'miembro';
            meta.innerHTML = `
                <span class="rol-pill">
                    <i class="fas fa-user-tag"></i> ${rolLabel}
                </span>
                <span>${tipoLabel}</span>
            `;
        }

        select.onchange = (e) => {
            setActiveOrganismId(parseInt(e.target.value, 10));
            renderOrganismSelector();
            loadPublisherPreview();
            showToast('Organismo activo cambiado');
        };
    }

    function loadPublisherPreview() {
        const user = getCurrentUser();
        const avatarEl = document.getElementById('publisherAvatar');
        const nameEl = document.getElementById('publisherName');
        const typeEl = document.getElementById('publisherType');
        if (!avatarEl) return;

        if (isStudent()) {
            avatarEl.className = 'avatar';
            avatarEl.innerHTML = '';
            if (isValidAvatarUrl(user.avatar)) {
                const img = document.createElement('img');
                img.src = user.avatar;
                img.onerror = function() {
                    this.remove();
                    avatarEl.innerHTML = `<span style="background:${getColorFromName(user.name)};">${user.initial}</span>`;
                };
                avatarEl.appendChild(img);
            } else {
                avatarEl.innerHTML = `<span style="background:${getColorFromName(user.name)};">${user.initial}</span>`;
            }
            nameEl.textContent = user.name;
            typeEl.textContent = 'Cuenta de estudiante';
            return;
        }

        const activeOrg = getActiveOrganism();
        if (!activeOrg) {
            avatarEl.className = 'avatar';
            avatarEl.innerHTML = `<span style="background:${getColorFromName(user.name)};">${user.initial}</span>`;
            nameEl.textContent = user.name;
            typeEl.textContent = 'Sin organismo activo';
            return;
        }

        const isUni = activeOrg.tipo === 'universidad';
        avatarEl.className = 'avatar ' + (isUni ? 'university' : 'company');
        avatarEl.innerHTML = '';

        const logoToUse = activeOrg.logo;
        if (isValidAvatarUrl(logoToUse)) {
            const img = document.createElement('img');
            img.src = logoToUse;
            img.alt = activeOrg.nombre;
            img.onerror = function() {
                this.remove();
                avatarEl.innerHTML = `<span style="background:${getColorFromName(activeOrg.nombre)};">${activeOrg.nombre.charAt(0).toUpperCase()}</span>`;
            };
            avatarEl.appendChild(img);
        } else {
            avatarEl.innerHTML = `<span style="background:${getColorFromName(activeOrg.nombre)};">${activeOrg.nombre.charAt(0).toUpperCase()}</span>`;
        }

        nameEl.textContent = activeOrg.nombre;
        typeEl.textContent = isUni ? 'Publicando como Universidad' : 'Publicando como Empresa';

        
    }

    let imagenes = [];
    const MAX_IMAGENES = 3;
    const MAX_SIZE_MB = 5;

    function renderImagePreviews() {
        const container = document.getElementById('imagePreviews');
        const hint = document.getElementById('imagesHint');
        if (!container) return;

        if (imagenes.length === 0) {
            container.innerHTML = '';
            hint.innerHTML = '<i class="fas fa-info-circle"></i> Puedes subir hasta 3 imágenes. Se guardarán como URLs en <code>oferta_imagenes</code>.';
            return;
        }

        container.innerHTML = imagenes.map((img, i) => `
            <div class="image-preview">
                <img src="${img}" alt="Imagen ${i+1}" />
                <button type="button" class="remove-btn" data-index="${i}" title="Eliminar imagen">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');

        hint.innerHTML = `<i class="fas fa-check-circle"></i> ${imagenes.length} de ${MAX_IMAGENES} imágenes cargadas`;

        container.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const i = parseInt(this.dataset.index);
                imagenes.splice(i, 1);
                renderImagePreviews();
            });
        });
    }

    function handleFiles(files) {
        const remaining = MAX_IMAGENES - imagenes.length;
        if (remaining <= 0) {
            showToast(`Ya tienes ${MAX_IMAGENES} imágenes (máximo)`);
            return;
        }

        const filesToProcess = Array.from(files).slice(0, remaining);

        filesToProcess.forEach(file => {
            if (!file.type.startsWith('image/')) {
                showToast(`"${file.name}" no es una imagen`);
                return;
            }
            if (file.size > MAX_SIZE_MB * 1024 * 1024) {
                showToast(`"${file.name}" supera los ${MAX_SIZE_MB}MB`);
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                imagenes.push(e.target.result);
                renderImagePreviews();
            };
            reader.readAsDataURL(file);
        });
    }

    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
            this.value = '';
        });
    }

    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(ev => {
            dropZone.addEventListener(ev, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(ev => {
            dropZone.addEventListener(ev, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            });
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            handleFiles(e.dataTransfer.files);
        });
    }

    async function loadExistingOffer() {
        const editingId = localStorage.getItem('editingOfferId');
        if (!editingId) return;

        try {
            const result = await apiRequest('/offers?view=mine');
            const offer = result.offers.find(item => Number(item.id) === Number(editingId));
            if (!offer) return;

            document.getElementById('pageTitle').textContent = 'Editar Oferta';
            document.getElementById('cardTitle').textContent = 'Editar Oferta';
            document.getElementById('cardSubtitle').textContent = 'Modifica los datos de tu oferta';
            document.getElementById('saveBtn').innerHTML = '<i class="fas fa-save"></i> Guardar cambios';

            document.getElementById('offerId').value = offer.id;
            document.getElementById('offerTitle').value = offer.titulo || offer.title || '';
            document.getElementById('offerModality').value = offer.modalidad || '';
            document.getElementById('offerContract').value = offer.tipo_contrato || '';
                        document.getElementById('offerExperience').value = offer.nivel_experiencia || 'sin_experiencia';
            document.getElementById('offerLocation').value = offer.ubicacion || '';
            document.getElementById('offerVacancies').value = offer.vacantes || 1;
                        document.getElementById('offerExpiration').value = offer.fecha_expiracion ? offer.fecha_expiracion.substring(0, 10) : '';
            document.getElementById('offerDescription').value = offer.descripcion || '';
            document.getElementById('offerTags').value = (offer.habilidades || []).join(', ');

            imagenes = [...(offer.imagenes || [])];
            renderImagePreviews();

            if (offer.organismo_id) {
                setActiveOrganismId(offer.organismo_id);
                renderOrganismSelector();
                loadPublisherPreview();
            }
        } catch (e) {
            showToast(e.message || 'No se pudo cargar la oferta.');
        }
    }

    const offerForm = document.getElementById('offerForm');
    if (offerForm) {
        offerForm.addEventListener('submit', (e) => {
            e.preventDefault();

            if (isStudent()) {
                showToast('Solo participantes pueden publicar ofertas');
                return;
            }

            const activeOrg = getActiveOrganism();
            if (!activeOrg) {
                showToast('No hay organismo activo. Crea uno desde "Mis Organismos"');
                return;
            }

            const user = getCurrentUser();
            const title = document.getElementById('offerTitle').value.trim();
            const modality = document.getElementById('offerModality').value;
            const contract = document.getElementById('offerContract').value;
                        const experience = document.getElementById('offerExperience').value;
            const location = document.getElementById('offerLocation').value.trim();
            const vacancies = parseInt(document.getElementById('offerVacancies').value, 10) || 1;
                        const expiration = document.getElementById('offerExpiration').value;
            const description = document.getElementById('offerDescription').value.trim();
            const tagsRaw = document.getElementById('offerTags').value.trim();
            const tags = tagsRaw ? tagsRaw.split(',').map(t => t.trim()).filter(t => t) : [];
            const editingId = document.getElementById('offerId').value;

            if (!title || !modality || !contract || !location || !description) {
                showToast('Completa los campos obligatorios (*)');
                return;
            }

            
            const payload = {
                organismo_id: activeOrg.id,
                titulo: title,
                modalidad: modality,
                tipo_contrato: contract,
                categoria: 'general',
                nivel_experiencia: experience,
                ubicacion: location,
                vacantes: vacancies,
                                fecha_expiracion: expiration || null,
                descripcion: description,
                habilidades: tags,
                imagenes: imagenes,
                estado: 'activa'
            };

            const submitBtn = document.getElementById('saveBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publicando...';

            apiRequest('/offers' + (editingId ? '/' + encodeURIComponent(editingId) : ''), {
                method: editingId ? 'PUT' : 'POST',
                body: JSON.stringify(payload)
            })
                .then(() => {
                    localStorage.removeItem('editingOfferId');
                    showToast(editingId ? 'Oferta actualizada correctamente' : 'Oferta publicada exitosamente');
                    setTimeout(() => { window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL + 'dashboard#misofertas' : 'dashboard.html#misofertas'); }, 800);
                })
                .catch(error => {
                    showToast(error.message || 'No se pudo guardar la oferta.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        });
    }

    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            if (confirm('¿Descartar los cambios?')) {
                localStorage.removeItem('editingOfferId');
                localStorage.removeItem('returnTo');
                window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'dashboard' : 'dashboard.html';
            }
        });
    }

    const navLinks = [
        { id: 'notifSidebarBtn',    returnTo: 'notif' },
        { id: 'messagesSidebarBtn', returnTo: 'messages' },
        { id: 'savedSidebarBtn',    returnTo: 'saved' },
        { id: 'myOffersSidebarBtn', returnTo: 'mine' },
        { id: 'conveniosSidebarBtn', returnTo: 'convenios' }
    ];
    navLinks.forEach(({ id, returnTo }) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('click', (e) => {
            e.preventDefault();
            localStorage.setItem('returnTo', returnTo);
            window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'dashboard' : 'dashboard.html';
        });
    });

    const notifBtn = document.getElementById('notificationBadge');
    if (notifBtn) {
        notifBtn.addEventListener('click', () => {
            localStorage.setItem('returnTo', 'notif');
            window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'dashboard' : 'dashboard.html';
        });
    }
    const msgBtn = document.getElementById('messagesBtn');
    if (msgBtn) {
        msgBtn.addEventListener('click', () => {
            localStorage.setItem('returnTo', 'messages');
            window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'dashboard' : 'dashboard.html';
        });
    }

    const profileBtn = document.getElementById('profileBtn');
    if (profileBtn) {
        profileBtn.addEventListener('click', () => {
            window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'perfil' : 'dashboard_perfil.html';
        });
    }

    const helpBtn = document.getElementById('helpBtn');
    if (helpBtn) {
        helpBtn.addEventListener('click', (e) => {
            e.preventDefault();
            localStorage.setItem('returnTo', 'help');
            window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'dashboard' : 'dashboard';
        });
    }

    

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            apiRequest('/auth/logout', { method: 'POST', body: JSON.stringify({}) })
                .finally(() => {
                    localStorage.clear();
                    window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'login' : 'loginhex.html';
                });
        });
    }

    async function init() {
        try {
            const sessionResult = await apiRequest('/auth/me');
            const user = sessionResult.user;
            localStorage.setItem('userEmail', user.correo || '');
            localStorage.setItem('userType', user.rol || 'estudiante');
            localStorage.setItem('userIdNumeric', String(user.id || ''));
            localStorage.setItem('userNombre', user.nombre || '');
            localStorage.setItem('userApellido', user.apellido || '');
            localStorage.setItem('userName', ((user.nombre || '') + ' ' + (user.apellido || '')).trim() || 'Usuario Ineova');
            localStorage.setItem('userAvatar', user.avatar || '');

            const organizationResult = await apiRequest('/organisms');
            ORGANISMOS_SEED = organizationResult.organisms;
            PARTICIPANTE_ORGANISMO_SEED = organizationResult.organisms.map(organization => ({
                usuario_id: Number(user.id),
                organismo_id: Number(organization.id),
                rol_en_organismo: organization.rol_en_organismo,
                activo: 1
            }));
        } catch (error) {
            if (error.status === 401) {
                window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'login' : 'loginhex.html';
                return;
            }
            showToast(error.message || 'No se pudo cargar tu cuenta.');
            return;
        }

        loadProfile();
        updateNotifBadge();
        updateMessagesBadge();
        renderOrganismSelector();
        loadPublisherPreview();
        await loadExistingOffer();
        renderImagePreviews();

        if (isStudent()) {
            document.getElementById('offerForm').querySelectorAll('input, select, textarea, button[type="submit"]')
                .forEach(el => el.disabled = true);
        } else if (!document.getElementById('offerId').value) {
            try {
                const profRes = await apiRequest('/profile');
                const plan = profRes.profile && profRes.profile.plan;
                if (plan && !plan.puede_publicar) {
                    const warn = document.getElementById('studentWarning');
                    if (warn) {
                        warn.style.display = 'block';
                        warn.innerHTML = `
                            <div class="org-header">
                                <i class="fas fa-crown" style="color:#b26e1a;"></i>
                                <label style="color:#8a5410;">Límite de publicaciones / Prueba finalizada</label>
                            </div>
                            <p style="font-size:13px; color:#8a5410; line-height:1.5; margin-top:4px;">${plan.motivo_bloqueo || 'Actualiza tu plan de suscripción para seguir publicando.'}</p>
                            <a href="${BASE_URL}perfil#planes" style="display:inline-flex; align-items:center; gap:6px; margin-top:10px; background:#0A66C2; color:#fff; padding:8px 14px; border-radius:10px; font-size:12.5px; font-weight:600; text-decoration:none;">
                                <i class="fas fa-crown"></i> Ver Planes en Mi Perfil
                            </a>`;
                    }
                    document.getElementById('offerForm').querySelectorAll('input, select, textarea, button[type="submit"]')
                        .forEach(el => el.disabled = true);
                }
            } catch (e) {}
        }

        localStorage.removeItem('returnTo');

        console.log('Crear Oferta v6.2 listo');
        console.log('rol:', getRoleSimple());
        console.log('tipo participante:', getTipoParticipante());
        console.log('organismo activo:', getActiveOrganism());
        setTimeout(() => { document.body.style.opacity = '1'; }, 120);
    }

    init();

})();
