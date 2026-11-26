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

    function saveCurrentUser(user) {
        localStorage.setItem('userEmail', user.correo || '');
        localStorage.setItem('userType', user.rol || 'estudiante');
        localStorage.setItem('userIdNumeric', String(user.id || ''));
        localStorage.setItem('userNombre', user.nombre || '');
        localStorage.setItem('userApellido', user.apellido || '');
        localStorage.setItem('userName', ((user.nombre || '') + ' ' + (user.apellido || '')).trim() || 'Usuario Ineova');
        localStorage.setItem('userAvatar', user.avatar || '');
    }

    function escapeHtml(value) {
        const element = document.createElement('span');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleSidebarBtn');
    const toggleIcon = document.getElementById('toggleIcon');

    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        if (toggleIcon) toggleIcon.className = 'fas fa-chevron-right';
    }

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        const isCollapsed = sidebar.classList.contains('collapsed');
        toggleIcon.className = isCollapsed ?
            'fas fa-chevron-right' : 'fas fa-chevron-left';
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });

    let toastTimeout;
    function showToast(msg, dur = 3000) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => t.classList.remove('show'), dur);
    }

    const AVATAR_COLORS = [
        '#1a6dff', '#0b7e3d', '#7c3aed', '#c53e3e', '#b26e1a',
        '#0a7e8c', '#d946a1', '#5c6bc0', '#00897b', '#e65100',
        '#4527a0', '#ad1457'
    ];

    function getColorFromName(name) {
        if (!name || typeof name !== 'string') return AVATAR_COLORS[0];
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    }

    function isValidAvatarUrl(url) {
        if (!url || typeof url !== 'string') return false;
        const trimmed = url.trim();
        if (trimmed === '') return false;
        return trimmed.startsWith('http://') ||
               trimmed.startsWith('https://') ||
               trimmed.startsWith('data:') ||
               trimmed.startsWith('blob:') ||
               trimmed.startsWith('/');
    }

    function getAvatarHtml(name, avatarUrl, isSent = false, isCompany = false) {
        const rawName = (name || '').trim();
        const cleanName = escapeHtml(rawName);
        const initial = rawName ? escapeHtml(rawName.charAt(0).toUpperCase()) : '?';
        let bgColor;
        if (isSent) bgColor = '#4CAF50';
        else if (isCompany) bgColor = '#0b7e3d';
        else bgColor = getColorFromName(cleanName);

        if (isValidAvatarUrl(avatarUrl)) {
            return `<img src="${escapeHtml(avatarUrl)}"
                         alt="${cleanName}"
                         loading="lazy"
                         data-initial="${initial}"
                         data-bg="${bgColor}"
                         onerror="window.handleAvatarError(this)" />`;
        } else {
            return `<span style="background:${bgColor};">${initial}</span>`;
        }
    }

    window.handleAvatarError = function(img) {
        const initial = img.dataset.initial || '?';
        const bgColor = img.dataset.bg || AVATAR_COLORS[0];
        const parent = img.parentNode;
        if (!parent) return;
        img.remove();
        const span = document.createElement('span');
        span.style.background = bgColor;
        span.textContent = initial;
        parent.appendChild(span);
    };

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

    function getCurrentUser() {
        const name = localStorage.getItem('userName') ||
                     ((localStorage.getItem('userNombre') || '') + ' ' +
                      (localStorage.getItem('userApellido') || '')).trim() ||
                     'Usuario Ineova';
        return {
            userId: getUserId(),
            name: name,
            avatar: localStorage.getItem('userAvatar') || '',
            companyLogo: localStorage.getItem('userCompanyLogo') || '',
            type: localStorage.getItem('userType') || 'estudiante',
            email: localStorage.getItem('userEmail') || '',
            initial: name.charAt(0).toUpperCase()
        };
    }

    function isCompany() {
        const user = getCurrentUser();
        return user.type === 'participante_empresa' || user.type === 'participante_universidad';
    }

    function loadProfile() {
        const user = getCurrentUser();
        const nameEl = document.getElementById('userNameDisplay');
        if (nameEl) nameEl.textContent = user.name;

        const avatarToUse = isCompany() ? (user.companyLogo || user.avatar) : user.avatar;
        const avatarImg = document.getElementById('avatarImg');
        if (!avatarImg) return;
        const avatarContainer = avatarImg.parentNode;

        if (isValidAvatarUrl(avatarToUse)) {
            avatarImg.src = avatarToUse;
            avatarImg.style.display = 'block';
            avatarImg.dataset.initial = user.initial;
            avatarImg.dataset.bg = getColorFromName(user.name);
            const fallbackSpan = avatarContainer.querySelector('span');
            if (fallbackSpan) fallbackSpan.remove();
        } else {
            avatarImg.style.display = 'none';
            let fallbackSpan = avatarContainer.querySelector('span');
            if (!fallbackSpan) {
                fallbackSpan = document.createElement('span');
                avatarContainer.appendChild(fallbackSpan);
            }
            fallbackSpan.style.background = getColorFromName(user.name);
            fallbackSpan.textContent = user.initial;
        }
    }

    function timeAgo(date) {
        const now = new Date();
        const diff = Math.floor((now - new Date(date)) / 1000);
        if (diff < 5) return 'hace unos segundos';
        if (diff < 60) return 'hace ' + diff + 's';
        if (diff < 3600) return 'hace ' + Math.floor(diff / 60) + 'm';
        if (diff < 86400) return 'hace ' + Math.floor(diff / 3600) + 'h';
        if (diff < 172800) return 'ayer';
        if (diff < 2592000) return 'hace ' + Math.floor(diff / 86400) + 'd';
        if (diff < 31536000) return 'hace ' + Math.floor(diff / 2592000) + 'mes';
        return 'hace ' + Math.floor(diff / 31536000) + 'año';
    }

    function getFullDate(date) {
        const d = new Date(date);
        const months = ['enero','febrero','marzo','abril','mayo','junio',
                        'julio','agosto','septiembre','octubre','noviembre','diciembre'];
        const day = d.getDate();
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const mins = String(d.getMinutes()).padStart(2, '0');
        return `${day} de ${month} de ${year}, ${hours}:${mins}`;
    }

    const ahora = new Date();

    let ofertasData = [{
        id: 1,
        publisher: {
            name: 'Mercado Libre', avatar: '', type: 'empresa',
            verified: true, ownerId: 'system'
        },
        title: 'Desarrollador Full Stack Senior',
        location: 'Remoto',
        salary: '$2,500 - $3,200 USD',
        type: 'Tiempo completo',
        description: 'Buscamos un desarrollador Full Stack con experiencia en React y Node.js.',
        images: [],
        applicants: 15, views: 84, saved: false,
        tags: ['JavaScript', 'React', 'Node.js', 'Remoto', 'Full-time'],
        category: 'tecnologia',
        timestamp: new Date(ahora.getTime() - 2 * 60000)
    }, {
        id: 2,
        publisher: {
            name: 'Globant', avatar: '', type: 'empresa',
            verified: true, ownerId: 'system'
        },
        title: 'Marketing Manager',
        location: 'Hibrido',
        salary: '$1,800 - $2,400 USD',
        type: 'Tiempo completo',
        description: 'Lidera estrategias de marketing digital.',
        images: [],
        applicants: 8, views: 52, saved: false,
        tags: ['Marketing Digital', 'SEO', 'Hibrido', 'Full-time'],
        category: 'marketing',
        timestamp: new Date(ahora.getTime() - 15 * 60000)
    }, {
        id: 3,
        publisher: {
            name: 'Carlos Profesor',
            avatar: 'https://randomuser.me/api/portraits/men/32.jpg',
            type: 'usuario', verified: false, ownerId: 'system'
        },
        title: 'Asistente de Investigación',
        location: 'Presencial',
        salary: '$800 - $1,200 USD',
        type: 'Medio tiempo',
        description: 'Apoyo en proyecto de investigación sobre IA.',
        images: [],
        applicants: 5, views: 34, saved: false,
        tags: ['Investigación', 'IA', 'Presencial', 'Medio tiempo'],
        category: 'tecnologia',
        timestamp: new Date(ahora.getTime() - 2 * 3600000)
    }];

    function loadUserOffers() {
        try {
            const userOffers = JSON.parse(localStorage.getItem('userOffers') || '[]');
            userOffers.forEach(uo => {
                if (!ofertasData.some(o => o.id === uo.id)) {
                    uo.timestamp = new Date(uo.timestamp);
                    ofertasData.unshift(uo);
                }
            });
        } catch (e) {}
    }

    function normalizeOffer(offer) {
        const modalityLabels = { remoto: 'Remoto', presencial: 'Presencial', hibrido: 'Híbrido' };
        const contractLabels = {
            tiempo_completo: 'Tiempo completo', medio_tiempo: 'Medio tiempo',
            pasantia: 'Pasantía / Becario', freelance: 'Freelance', temporal: 'Temporal'
        };
        const publishedBy = offer.publicado_por_correo || String(offer.publicado_por_id || '');
        const publisherName = [offer.publicado_por_nombre, offer.publicado_por_apellido].filter(Boolean).join(' ');

        return {
            id: Number(offer.id),
            publisher: {
                name: offer.organismo_nombre || publisherName || 'Organismo',
                avatar: offer.organismo_logo || offer.publicado_por_avatar || '',
                type: offer.organismo_tipo || 'empresa',
                verified: Boolean(offer.organismo_verificado),
                ownerId: publishedBy,
                userId: Number(offer.publicado_por_id)
            },
            title: offer.titulo,
            location: offer.ubicacion,
            type: contractLabels[offer.tipo_contrato] || offer.tipo_contrato,
            description: offer.descripcion,
            images: offer.imagenes || [],
            applicants: Number(offer.aplicantes || 0),
            views: Number(offer.vistas || 0),
            saved: Boolean(offer.guardada),
            applied: Boolean(offer.aplicada),
            destacada: Boolean(offer.destacada),
            coincidencias: offer.coincidencias || [],
            tags: [...(offer.habilidades || []), modalityLabels[offer.modalidad] || offer.modalidad],
            category: offer.categoria,
            timestamp: new Date(String(offer.fecha_creacion).replace(' ', 'T'))
        };
    }

    async function loadOffersFromApi(view = '') {
        const query = view ? '?view=' + encodeURIComponent(view) : '';
        const result = await apiRequest('/offers' + query);
        ofertasData = result.offers.map(normalizeOffer);
        if (view === 'mine') renderOfertas('mine');
        else if (view === 'saved') renderOfertas('saved');
        else renderOfertas(currentFilter);
    }

    function loadSavedState() {
        try {
            const saved = JSON.parse(localStorage.getItem('ofertasGuardadas') || '[]');
            ofertasData.forEach(o => { o.saved = saved.includes(o.id); });
        } catch (e) {}
    }

    function saveSavedState() {
        const savedIds = ofertasData.filter(o => o.saved).map(o => o.id);
        localStorage.setItem('ofertasGuardadas', JSON.stringify(savedIds));
    }

    let notificaciones = [{
        id: 1, icon: 'fa-user-plus', type: 'success',
        title: 'Nuevo usuario', desc: 'Carlos Pérez creó su cuenta',
        read: false, tag: 'Nuevo',
        timestamp: new Date(ahora.getTime() - 5 * 60000)
    }, {
        id: 2, icon: 'fa-briefcase', type: 'success',
        title: 'Nueva oferta', desc: 'Pasantía en Mercado Libre',
        read: false, tag: 'Nuevo',
        timestamp: new Date(ahora.getTime() - 15 * 60000)
    }, {
        id: 3, icon: 'fa-check-circle', type: 'success',
        title: 'Solicitud aprobada', desc: 'Tu solicitud fue aprobada',
        read: true, tag: 'Completado',
        timestamp: new Date(ahora.getTime() - 2 * 3600000)
    }, {
        id: 4, icon: 'fa-user-minus', type: 'danger',
        title: 'Usuario eliminado', desc: 'Ana Martínez desactivada',
        read: true, tag: 'Aviso',
        timestamp: new Date(ahora.getTime() - 24 * 3600000)
    }, {
        id: 5, icon: 'fa-calendar-check', type: 'purple',
        title: 'Evento próximo', desc: 'Webinar mañana 10:00 AM',
        read: false, tag: 'Recordatorio',
        timestamp: new Date(ahora.getTime() - 2 * 86400000)
    }];
    notificaciones = [];

    let conversations = [];
    let messages = [];
    let recipientUsers = [];

    async function loadNotificationsFromApi() {
        const result = await apiRequest('/notifications');
        notificaciones = result.notifications.map(item => ({
            id: Number(item.id),
            icon: item.icono || 'fa-bell',
            type: item.tipo || 'info',
            title: item.titulo,
            desc: item.descripcion || '',
            read: Boolean(item.leido_en),
            tag: '',
            timestamp: new Date(String(item.fecha_creacion).replace(' ', 'T'))
        }));
        updateNotifCount();
        if (currentView === 'notifications') renderNotif();
    }

    async function loadConversationsFromApi() {
        const result = await apiRequest('/conversations');
        const typeMap = { directa: 'direct', consulta: 'inquiry', aplicacion: 'application', sistema: 'system' };
        const statusMap = { activa: 'active', archivada: 'archived', bloqueada: 'blocked' };
        conversations = result.conversations.map(item => ({
            id: item.id,
            participants: [String(item.otro_usuario.id), String(localStorage.getItem('userIdNumeric') || '')],
            otherUser: {
                id: Number(item.otro_usuario.id),
                name: item.otro_usuario.nombre || 'Usuario',
                avatar: item.otro_usuario.avatar || '',
                type: item.otro_usuario.tipo === 'empresa' ? 'empresa' : item.otro_usuario.tipo === 'universidad' ? 'universidad' : 'usuario',
                email: item.otro_usuario.correo
            },
            type: typeMap[item.tipo] || 'direct',
            relatedOfferId: item.oferta_id ? Number(item.oferta_id) : null,
            relatedOfferTitle: item.oferta_titulo || null,
            createdAt: new Date(String(item.fecha_creacion).replace(' ', 'T')),
            lastMessageAt: item.ultimo_mensaje_fecha ? new Date(String(item.ultimo_mensaje_fecha).replace(' ', 'T')) : new Date(String(item.fecha_creacion).replace(' ', 'T')),
            lastMessagePreview: item.ultimo_mensaje || '',
            unread: Number(item.no_leidos || 0),
            status: statusMap[item.estado] || 'active'
        }));
        updateMessagesBadge();
        if (currentView === 'messages') renderConversations();
    }

    async function loadConversationMessages(conversationId) {
        const result = await apiRequest(`/conversations/${encodeURIComponent(conversationId)}/messages`);
        const ownId = Number(localStorage.getItem('userIdNumeric') || 0);
        const ownEmail = getCurrentUser().userId;
        messages = messages.filter(message => message.conversationId !== conversationId);
        result.messages.forEach(item => {
            const sent = Number(item.remitente_id) === ownId;
            messages.push({
                id: item.id,
                conversationId,
                from: sent ? ownEmail : item.remitente_correo,
                to: sent ? conversations.find(c => c.id === conversationId)?.otherUser.email : ownEmail,
                fromName: [item.remitente_nombre, item.remitente_apellido].filter(Boolean).join(' '),
                body: item.cuerpo,
                timestamp: new Date(String(item.fecha_envio).replace(' ', 'T')),
                read: Boolean(item.leido_en)
            });
        });
    }

    async function loadRecipientUsers() {
        const result = await apiRequest('/users');
        recipientUsers = result.users;
        const select = document.getElementById('newMsgTo');
        select.replaceChildren(new Option('Selecciona un destinatario...', ''));
        recipientUsers.forEach(user => {
            const name = [user.nombre, user.apellido].filter(Boolean).join(' ') || user.correo;
            const suffix = user.organismo_nombre ? ` · ${user.organismo_nombre}` : '';
            select.add(new Option(name + suffix, String(user.id)));
        });
    }

    function loadConversations() {
        try {
            conversations = JSON.parse(localStorage.getItem('ineova_conversations') || '[]');
            messages = JSON.parse(localStorage.getItem('ineova_messages') || '[]');
            conversations.forEach(c => c.createdAt = new Date(c.createdAt));
            conversations.forEach(c => c.lastMessageAt = new Date(c.lastMessageAt));
            messages.forEach(m => m.timestamp = new Date(m.timestamp));
        } catch (e) {
            conversations = [];
            messages = [];
        }
    }

    function saveConversations() {
        localStorage.setItem('ineova_conversations', JSON.stringify(conversations));
        localStorage.setItem('ineova_messages', JSON.stringify(messages));
    }

    function seedConversations() {
        if (conversations.length > 0) return;
        const user = getCurrentUser();
        const fakeConvs = [{
            id: 'conv_seed_1',
            participants: ['laura@mail.com', user.userId],
            otherUser: { name: 'Laura Martinez', avatar: 'https://randomuser.me/api/portraits/women/65.jpg', type: 'usuario', email: 'laura@mail.com' },
            type: 'direct',
            relatedOfferId: null, relatedOfferTitle: null,
            createdAt: new Date(ahora.getTime() - 30 * 60000),
            lastMessageAt: new Date(ahora.getTime() - 10 * 60000),
            lastMessagePreview: 'Hola, ¿aún estás interesado en la oferta?',
            unread: 1, status: 'active'
        }, {
            id: 'conv_seed_2',
            participants: ['mercadolibre@ineova.com', user.userId],
            otherUser: { name: 'Mercado Libre', avatar: '', type: 'empresa', email: 'mercadolibre@ineova.com', verified: true },
            type: 'inquiry',
            relatedOfferId: 1,
            relatedOfferTitle: 'Desarrollador Full Stack Senior',
            createdAt: new Date(ahora.getTime() - 2 * 3600000),
            lastMessageAt: new Date(ahora.getTime() - 2 * 3600000),
            lastMessagePreview: 'Gracias por tu interés, revisaremos tu perfil.',
            unread: 0, status: 'active'
        }, {
            id: 'conv_seed_3',
            participants: ['ana@mail.com', user.userId],
            otherUser: { name: 'Ana Torres', avatar: '', type: 'usuario', email: 'ana@mail.com' },
            type: 'direct',
            relatedOfferId: null, relatedOfferTitle: null,
            createdAt: new Date(ahora.getTime() - 5 * 3600000),
            lastMessageAt: new Date(ahora.getTime() - 5 * 3600000),
            lastMessagePreview: 'Conversación archivada de ejemplo.',
            unread: 0, status: 'archived'
        }, {
            id: 'conv_seed_4',
            participants: ['spam@mail.com', user.userId],
            otherUser: { name: 'Usuario Spam', avatar: '', type: 'usuario', email: 'spam@mail.com' },
            type: 'direct',
            relatedOfferId: null, relatedOfferTitle: null,
            createdAt: new Date(ahora.getTime() - 8 * 3600000),
            lastMessageAt: new Date(ahora.getTime() - 8 * 3600000),
            lastMessagePreview: 'Conversación bloqueada de ejemplo.',
            unread: 0, status: 'blocked'
        }];

        const fakeMsgs = [{
            id: 'msg_seed_1', conversationId: 'conv_seed_1',
            from: 'laura@mail.com', to: user.userId, fromName: 'Laura Martinez',
            body: 'Hola, ¿aún estás interesado en la oferta de Desarrollador Full Stack?',
            timestamp: new Date(ahora.getTime() - 10 * 60000), read: false
        }, {
            id: 'msg_seed_2', conversationId: 'conv_seed_2',
            from: 'mercadolibre@ineova.com', to: user.userId, fromName: 'Mercado Libre',
            body: 'Gracias por tu interés, revisaremos tu perfil.',
            timestamp: new Date(ahora.getTime() - 2 * 3600000), read: true
        }, {
            id: 'msg_seed_3', conversationId: 'conv_seed_2',
            from: user.userId, to: 'mercadolibre@ineova.com', fromName: user.name,
            body: 'Hola, me interesa la posición. Adjunto mi CV.',
            timestamp: new Date(ahora.getTime() - 2.5 * 3600000), read: true
        }];

        conversations = fakeConvs;
        messages = fakeMsgs;
        saveConversations();
    }

    conversations = [];
    messages = [];

    let currentView = 'feed';
    let currentFilter = 'all';
    let currentViewMode = 'all';
    let filtroNotif = 'all';
    let notifSearchQuery = '';
    let currentConvTab = 'all';
    let convSearchQuery = '';
    let activeConversationId = null;
    const currentUser = getCurrentUser();

    const feedContainer = document.getElementById('feedContainer');
    const notifPanel = document.getElementById('notifPanel');
    const notifList = document.getElementById('notifList');
    const filtersBar = document.getElementById('filtersBar');
    const pageTitle = document.getElementById('pageTitle');

    function renderOfertas(filter = 'all') {
        let data = [...ofertasData];

        if (filter === 'saved') {
            data = data.filter(o => o.saved === true);
            pageTitle.textContent = 'Ofertas Guardadas';
            filtersBar.style.display = 'none';
            currentViewMode = 'saved';
        } else if (filter === 'mine') {
            const user = getCurrentUser();
            data = data.filter(o => o.publisher.ownerId === user.userId);
            pageTitle.textContent = 'Mis Ofertas';
            filtersBar.style.display = 'none';
            currentViewMode = 'mine';
        } else {
            pageTitle.textContent = 'Ofertas';
            filtersBar.style.display = 'flex';
            currentViewMode = 'all';
            if (filter !== 'all') {
                const normalize = (v) => String(v || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                const normFilter = normalize(filter);
                data = data.filter(o =>
                    normalize(o.category) === normFilter ||
                    o.tags.some(t => normalize(t) === normFilter)
                );
            }
        }

        data.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

        if (data.length === 0) {
            if (filter === 'mine') {
                feedContainer.innerHTML = `
                    <div class="my-offers-empty">
                        <i class="fas fa-briefcase"></i>
                        <h4>Aún no has publicado ofertas</h4>
                        <p>Crea tu primera oferta laboral y empieza a recibir aplicaciones de estudiantes universitarios.</p>
                        <button onclick="window.location.href=(typeof BASE_URL !== 'undefined' ? BASE_URL + 'ofertas/crear' : 'crear_oferta.html')">
                            <i class="fas fa-plus"></i> Publicar mi primera oferta
                        </button>
                    </div>
                `;
            } else {
                feedContainer.innerHTML = `
                    <div style="text-align:center;padding:60px 20px;color:#8ba0b8;background:white;border-radius:32px;border:1px solid #d6e5f5;">
                        <i class="fas fa-briefcase" style="font-size:40px;color:#d6e5f5;display:block;margin-bottom:12px;"></i>
                        <h4 style="color:#2c5a7a;">${filter === 'saved' ? 'Sin ofertas guardadas' : 'No se encontraron ofertas'}</h4>
                        <p style="font-size:13px;">${filter === 'saved' ? 'Guarda ofertas para verlas aquí.' : 'Prueba con otro filtro.'}</p>
                    </div>
                `;
            }
            return;
        }

        let headerHtml = '';
        if (filter === 'mine') {
            const totalApplicants = data.reduce((sum, o) => sum + (o.applicants || 0), 0);
            const totalViews = data.reduce((sum, o) => sum + (o.views || 0), 0);

            headerHtml = `
                <div class="my-offers-header">
                    <div class="info">
                        <h2><i class="fas fa-briefcase"></i> Mis Ofertas</h2>
                        <p>Gestiona tus publicaciones y revisa su rendimiento</p>
                    </div>
                    <div class="my-offers-stats">
                        <div class="stat">
                            <span class="num">${data.length}</span>
                            <span class="lbl">Activas</span>
                        </div>
                        <div class="stat">
                            <span class="num">${totalViews}</span>
                            <span class="lbl">Vistas</span>
                        </div>
                        <div class="stat">
                            <span class="num">${totalApplicants}</span>
                            <span class="lbl">Aplicantes</span>
                        </div>
                    </div>
                    <button class="my-offers-cta" onclick="window.location.href=(typeof BASE_URL !== 'undefined' ? BASE_URL + 'ofertas/crear' : 'crear_oferta.html')">
                        <i class="fas fa-plus"></i> Nueva Oferta
                    </button>
                </div>
            `;
        }

        let html = headerHtml;
        data.forEach(o => {
            const savedClass = o.saved ? 'saved' : '';
            const savedIcon = o.saved ? 'fas' : 'far';
            const savedText = o.saved ? 'Guardado' : 'Guardar';
            const timeStr = timeAgo(o.timestamp);
            const fullDate = getFullDate(o.timestamp);
            const isOwner = o.publisher.ownerId === currentUser.userId;
            const isCompanyPub = o.publisher.type === 'empresa';

            const avatarHtml = getAvatarHtml(
                o.publisher.name, o.publisher.avatar, false, isCompanyPub
            );

            const typeTag = o.publisher.type === 'universidad' ?
                '<span class="publisher-type-tag" style="background:#ede9fe; color:#6d28d9;">Universidad</span>' :
                (isCompanyPub ? '<span class="publisher-type-tag empresa">Empresa</span>' : '<span class="publisher-type-tag">Usuario</span>');

            const statusBadge = (filter === 'mine' && isOwner) ?
                '<span class="offer-status-badge">Activa</span>' : '';

            const ownerActions = isOwner ? `
                <div class="offer-owner-actions">
                    <button onclick="openApplicantsModal(${o.id})"><i class="fas fa-users"></i> Postulantes (${o.applicants || 0})</button>
                    <button onclick="editOffer(${o.id})"><i class="fas fa-edit"></i> Editar</button>
                    <button class="btn-delete" onclick="deleteOffer(${o.id})"><i class="fas fa-trash"></i> Eliminar</button>
                </div>
            ` : '';

            let imagesHtml = '';
            if (o.images && o.images.length > 0) {
                const gridClass = o.images.length === 1 ? 'single' :
                                 o.images.length === 2 ? 'double' : '';
                imagesHtml = `
                    <div class="offer-images ${gridClass}">
                        ${o.images.map(img => `<img src="${escapeHtml(img)}" alt="Imagen" data-lightbox-src="${escapeHtml(img)}" onerror="this.style.display='none'" />`).join('')}
                    </div>
                `;
            }

            let statsHtml = '';
            if (filter === 'mine' && isOwner) {
                statsHtml = `
                    <div class="my-offer-stats">
                        <div class="my-offer-stat-box">
                            <span class="num">${o.views || 0}</span>
                            <span class="lbl">Vistas</span>
                        </div>
                        <div class="my-offer-stat-box">
                            <span class="num">${o.applicants || 0}</span>
                            <span class="lbl">Aplicantes</span>
                        </div>
                        <div class="my-offer-stat-box">
                            <span class="num">${o.saved ? '★' : '—'}</span>
                            <span class="lbl">Guardada</span>
                        </div>
                    </div>
                `;
            }

            const currentUserRole = localStorage.getItem('userType') || 'estudiante';
            const applyBtnHtml = currentUserRole === 'estudiante'
                ? (o.applied
                    ? `<button class="btn-apply" data-id="${o.id}" disabled style="opacity:0.7; cursor:default;"><i class="fas fa-check"></i> Postulado</button>`
                    : `<button class="btn-apply" data-id="${o.id}"><i class="fas fa-paper-plane"></i> Postularse</button>`)
                : '';

            const saveBtnHtml = currentUserRole === 'estudiante'
                ? `<button class="btn-save ${savedClass}" data-id="${o.id}"><i class="${savedIcon} fa-bookmark"></i> ${savedText}</button>`
                : '';

            const recommendBtnHtml = currentUserRole === 'participante_universidad'
                ? `<button class="btn-recommend" data-id="${o.id}" style="background:#6d28d9; color:#fff; border:none; padding:9px 16px; border-radius:12px; font-weight:600; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;"><i class="fas fa-user-graduate"></i> Recomendar estudiante</button>`
                : '';

            const actionsHtml = !isOwner ? `${applyBtnHtml}${saveBtnHtml}${recommendBtnHtml}` : '';

            const verifiedMark = o.publisher.verified
                ? '<i class="fas fa-check-circle" style="color:#1d9bf0; margin-left:4px; font-size:14px;" title="Empresa verificada"></i>'
                : '';

            const profileHref = o.publisher.userId ? `${BASE_URL}perfil?user=${o.publisher.userId}` : '#';

            const featuredStyle = o.destacada ? 'border: 2px solid #f59e0b; background: linear-gradient(180deg, #fffdf5 0%, #ffffff 42%); box-shadow: 0 8px 26px rgba(245, 158, 11, 0.14);' : '';
            const featuredBanner = o.destacada
                ? `<div style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#fef3c7,#fde68a); color:#92400e; padding:5px 12px; border-radius:999px; font-size:12px; font-weight:700; margin-bottom:10px; width:fit-content;">
                       <i class="fas fa-star" style="color:#d97706;"></i> Oferta destacada para ti${o.coincidencias && o.coincidencias.length ? ' · Coincide con: ' + escapeHtml(o.coincidencias.slice(0, 3).join(', ')) : ''}
                   </div>`
                : '';

            html += `
                <div class="offer-card" data-id="${o.id}" style="${featuredStyle}">
                    ${featuredBanner}
                    <div class="offer-header">
                        <a href="${profileHref}" class="offer-publisher" style="text-decoration:none; color:inherit;" title="Ver perfil de ${escapeHtml(o.publisher.name)}">
                            <div class="publisher-avatar ${isCompanyPub ? 'company' : ''}">
                                ${avatarHtml}
                            </div>
                            <div class="publisher-info">
                                <div class="publisher-name">
                                    ${escapeHtml(o.publisher.name)}${verifiedMark}
                                    ${typeTag}
                                    ${statusBadge}
                                </div>
                                <div class="publisher-time" data-tooltip="${escapeHtml(fullDate)}">
                                    <i class="far fa-clock"></i> ${timeStr}
                                </div>
                            </div>
                        </a>
                        ${ownerActions}
                    </div>

                    <div class="offer-title">${escapeHtml(o.title)}</div>

                    <div class="offer-meta">
                        <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(o.location)}</span>
                        <span><i class="fas fa-clock"></i> ${escapeHtml(o.type)}</span>
                        <span><i class="fas fa-users"></i> ${o.applicants || 0} postulantes</span>
                    </div>

                    ${o.description ? `<div class="offer-description">${escapeHtml(o.description)}</div>` : ''}

                    ${imagesHtml}

                    <div class="offer-tags">
                        ${o.tags.map(t => {
                            let cls = 'offer-tag';
                            if (t === 'Remoto') cls += ' remote';
                            if (t === 'Híbrido' || t === 'Hibrido') cls += ' hybrid';
                            if (t === 'Full-time') cls += ' fulltime';
                            return `<span class="${cls}">${escapeHtml(t)}</span>`;
                        }).join('')}
                    </div>

                    ${statsHtml}

                    <div class="offer-footer">
                        <div class="offer-stats">
                            <span><i class="far fa-eye"></i> ${o.views || 0} vistas</span>
                            <span><i class="far fa-bookmark"></i> ${o.saved ? 'Guardado' : 'No guardado'}</span>
                        </div>
                        <div class="offer-actions">
                            ${actionsHtml}
                        </div>
                    </div>
                </div>
            `;
        });

        feedContainer.innerHTML = html;
        feedContainer.querySelectorAll('[data-lightbox-src]').forEach(image => {
            image.addEventListener('click', () => window.openLightbox(image.dataset.lightboxSrc));
        });
        setupOfferEvents();
    }

    function setupOfferEvents() {
        document.querySelectorAll('.btn-apply').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id);
                const offer = ofertasData.find(o => o.id === id);
                if (offer && !offer.applied) {
                    apiRequest(`/offers/${id}/apply`, { method: 'POST', body: JSON.stringify({}) })
                        .then(() => {
                            offer.applied = true;
                            offer.applicants = (offer.applicants || 0) + 1;
                            renderOfertas(currentView === 'saved' ? 'saved' : currentFilter);
                            showToast(`Postulación enviada a: ${offer.title}`);
                        })
                        .catch(error => showToast(error.message));
                }
            });
        });

        document.querySelectorAll('.btn-recommend').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id, 10);
                const offer = ofertasData.find(o => o.id === id);
                if (!offer) return;

                openContactModal({
                    toName: offer.publisher.name,
                    toEmail: offer.publisher.ownerId || '',
                    recipientId: offer.publisher.userId,
                    toType: offer.publisher.type,
                    toAvatar: offer.publisher.avatar,
                    offerId: offer.id,
                    offerTitle: offer.title,
                    type: 'inquiry'
                });
                document.getElementById('contactModalTitle').textContent = 'Recomendar estudiante';
                document.getElementById('contactSubject').value = `Recomendación de estudiante para: ${offer.title}`;

                const recGroup = document.getElementById('recommendStudentGroup');
                const recSelect = document.getElementById('recommendStudentSelect');
                if (recGroup && recSelect) {
                    recGroup.style.display = 'block';
                    recSelect.innerHTML = '<option value="">Cargando estudiantes de tu universidad...</option>';
                    try {
                        const res = await apiRequest('/university/students');
                        const students = res.students || [];
                        recSelect.innerHTML = '<option value="">Selecciona un estudiante para recomendar...</option>';
                        students.forEach(st => {
                            const fullName = [st.nombre, st.apellido].filter(Boolean).join(' ') || st.correo;
                            const opt = document.createElement('option');
                            opt.value = String(st.usuario_id);
                            opt.textContent = `${fullName}${st.promedio ? ' (Promedio: ' + st.promedio + ')' : ''}${st.estado_vigencia === 'vigente' ? ' · Vigente' : ''}`;
                            opt.dataset.name = fullName;
                            opt.dataset.promedio = st.promedio || '';
                            opt.dataset.cert = st.notas_certificadas ? '1' : '0';
                            recSelect.appendChild(opt);
                        });
                        recSelect.onchange = function() {
                            const selected = this.options[this.selectedIndex];
                            if (!this.value) {
                                if (contactData) contactData.recommendedStudent = null;
                                return;
                            }
                            if (contactData) {
                                contactData.recommendedStudent = {
                                    id: Number(this.value),
                                    name: selected.dataset.name
                                };
                            }
                            const avgNote = selected.dataset.promedio ? ` con promedio ${selected.dataset.promedio}` : '';
                            const certNote = selected.dataset.cert === '1' ? ' (notas certificadas por la universidad)' : '';
                            document.getElementById('contactBody').value =
                                `Hola ${offer.publisher.name}, desde nuestra universidad recomendamos al estudiante ${selected.dataset.name}${avgNote}${certNote} para la oferta "${offer.title}".`;
                        };
                    } catch (err) {
                        recSelect.innerHTML = '<option value="">No se pudieron cargar los estudiantes</option>';
                    }
                }
            });
        });

        document.querySelectorAll('.btn-chat').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id);
                const offer = ofertasData.find(o => o.id === id);
                if (offer) openContactModal({
                    toName: offer.publisher.name,
                    toEmail: 'empresa_' + offer.id + '@ineova.com',
                    recipientId: offer.publisher.userId,
                    toType: offer.publisher.type,
                    toAvatar: offer.publisher.avatar,
                    offerId: offer.id,
                    offerTitle: offer.title,
                    type: 'inquiry'
                });
            });
        });

        document.querySelectorAll('.btn-save').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id);
                const offer = ofertasData.find(o => o.id === id);
                if (offer) {
                    apiRequest(`/offers/${id}/save`, { method: 'POST', body: JSON.stringify({}) })
                        .then(result => {
                            offer.saved = result.saved;
                            if (currentView === 'saved' && !result.saved) {
                                ofertasData = ofertasData.filter(item => item.id !== id);
                            }
                            renderOfertas(currentView === 'saved' ? 'saved' : currentFilter);
                            showToast(result.saved ? 'Oferta guardada' : 'Oferta eliminada');
                        })
                        .catch(error => showToast(error.message));
                }
            });
        });
    }

    window.editOffer = function(id) {
        localStorage.setItem('editingOfferId', id);
        localStorage.setItem('returnTo', 'mine');
        window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'ofertas/crear' : 'crear_oferta.html';
    };

    window.deleteOffer = function(id) {
        if (confirm('¿Estás seguro de eliminar esta oferta? Esta acción no se puede deshacer.')) {
            apiRequest(`/offers/${id}`, { method: 'DELETE' })
                .then(() => {
                    ofertasData = ofertasData.filter(offer => offer.id !== Number(id));
                    if (currentViewMode === 'mine') renderOfertas('mine');
                    else renderOfertas(currentFilter);
                    showToast('Oferta eliminada correctamente');
                })
                .catch(error => showToast(error.message));
        }
    };

    window.openLightbox = function(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.add('active');
    };

    document.getElementById('closeLightbox').addEventListener('click', () => {
        document.getElementById('lightbox').classList.remove('active');
    });
    document.getElementById('lightbox').addEventListener('click', (e) => {
        if (e.target.id === 'lightbox') {
            e.currentTarget.classList.remove('active');
        }
    });

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (currentView === 'saved' || currentView === 'mine') return;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            renderOfertas(currentFilter);
        });
    });

    function getTypeClass(t) {
        const map = { warning: 'warning', success: 'success', danger: 'danger', purple: 'purple' };
        return map[t] || '';
    }

    function renderNotif() {
        let data = [...notificaciones];
        if (filtroNotif === 'unread') data = data.filter(n => !n.read);
        if (filtroNotif === 'read') data = data.filter(n => n.read);
        if (notifSearchQuery) {
            const q = notifSearchQuery.toLowerCase();
            data = data.filter(n =>
                n.title.toLowerCase().includes(q) ||
                n.desc.toLowerCase().includes(q)
            );
        }
        data.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

        if (data.length === 0) {
            notifList.innerHTML = `
                <div class="notif-empty">
                    <i class="fas fa-bell-slash"></i>
                    <h4>Sin notificaciones</h4>
                    <p style="font-size:13px;">No hay notificaciones que coincidan.</p>
                </div>
            `;
            return;
        }

        let html = '';
        data.forEach(n => {
            const cls = getTypeClass(n.type);
            const unread = !n.read ? 'unread' : '';
            const tagCls = n.tag === 'Nuevo' ? 'new' : '';
            const timeStr = timeAgo(n.timestamp);
            const fullDate = getFullDate(n.timestamp);

            html += `
                <div class="notif-item ${unread}" data-id="${n.id}">
                    <div class="n-icon ${cls}"><i class="fas ${escapeHtml(n.icon)}"></i></div>
                    <div class="n-content">
                        <div class="n-title">${escapeHtml(n.title)}</div>
                        <div class="n-desc">${escapeHtml(n.desc)}</div>
                        <div class="n-time" data-tooltip="${escapeHtml(fullDate)}">
                            <i class="far fa-clock"></i> ${timeStr}
                        </div>
                    </div>
                    ${n.tag ? `<span class="n-tag ${tagCls}">${escapeHtml(n.tag)}</span>` : ''}
                    <div class="n-actions">
                        ${!n.read ?
                            `<button class="mark-notif" data-id="${n.id}" title="Marcar leído"><i class="fas fa-check"></i></button>` :
                            `<button class="unread-btn" data-id="${n.id}" title="Marcar no leído"><i class="fas fa-envelope"></i></button>`
                        }
                        <button class="del-notif" data-id="${n.id}" title="Eliminar"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `;
        });
        notifList.innerHTML = html;
        bindNotifEvents();
        updateNotifCount();
    }

    function bindNotifEvents() {
        document.querySelectorAll('.mark-notif').forEach(b => {
            b.addEventListener('click', e => {
                e.stopPropagation();
                setNotificationRead(parseInt(b.dataset.id, 10), true);
            });
        });
        document.querySelectorAll('.unread-btn').forEach(b => {
            b.addEventListener('click', e => {
                e.stopPropagation();
                setNotificationRead(parseInt(b.dataset.id, 10), false);
            });
        });
        document.querySelectorAll('.del-notif').forEach(b => {
            b.addEventListener('click', e => {
                e.stopPropagation();
                const id = parseInt(b.dataset.id, 10);
                apiRequest(`/notifications/${id}`, { method: 'DELETE' })
                    .then(() => {
                        notificaciones = notificaciones.filter(item => item.id !== id);
                        renderNotif();
                    })
                    .catch(error => showToast(error.message));
            });
        });
        document.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', function() {
                const n = notificaciones.find(x => x.id === parseInt(this.dataset.id));
                if (n && !n.read) setNotificationRead(n.id, true);
            });
        });
    }

    function setNotificationRead(id, read) {
        apiRequest(`/notifications/${id}/read`, {
            method: 'PUT',
            body: JSON.stringify({ read })
        }).then(() => {
            const notification = notificaciones.find(item => item.id === id);
            if (notification) notification.read = read;
            renderNotif();
        }).catch(error => showToast(error.message));
    }

    function updateNotifCount() {
        const unread = notificaciones.filter(n => !n.read).length;
        const notifCountEl = document.getElementById('notificationCount');
        if (notifCountEl) notifCountEl.textContent = unread;
    }

    document.getElementById('markAllNotif').addEventListener('click', () => {
        apiRequest('/notifications/read-all', { method: 'PUT', body: JSON.stringify({}) })
            .then(() => {
                notificaciones.forEach(notification => notification.read = true);
                renderNotif();
                showToast('Todas las notificaciones marcadas como leídas');
            })
            .catch(error => showToast(error.message));
    });

    document.getElementById('clearAllNotif').addEventListener('click', () => {
        if (confirm('¿Eliminar todas las notificaciones?')) {
            apiRequest('/notifications', { method: 'DELETE' })
                .then(() => {
                    notificaciones = [];
                    renderNotif();
                    showToast('Notificaciones eliminadas');
                })
                .catch(error => showToast(error.message));
        }
    });

    document.getElementById('notifSearchInput').addEventListener('input', function() {
        notifSearchQuery = this.value;
        renderNotif();
    });

    document.querySelectorAll('#notifFilters button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#notifFilters button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filtroNotif = this.dataset.filter;
            renderNotif();
        });
    });

    function getFilteredConversations() {
        let data = [...conversations];

        if (currentConvTab === 'unread') {
            data = data.filter(c => c.unread > 0 && c.status === 'active');
        } else if (currentConvTab === 'archived') {
            data = data.filter(c => c.status === 'archived');
        } else if (currentConvTab === 'blocked') {
            data = data.filter(c => c.status === 'blocked');
        } else {
            data = data.filter(c => c.status === 'active');
        }

        if (convSearchQuery) {
            const q = convSearchQuery.toLowerCase();
            data = data.filter(c =>
                c.otherUser.name.toLowerCase().includes(q) ||
                (c.relatedOfferTitle && c.relatedOfferTitle.toLowerCase().includes(q)) ||
                (c.lastMessagePreview && c.lastMessagePreview.toLowerCase().includes(q))
            );
        }

        data.sort((a, b) => new Date(b.lastMessageAt) - new Date(a.lastMessageAt));
        return data;
    }

    function renderConversations() {
        const data = getFilteredConversations();
        const list = document.getElementById('convList');

        if (data.length === 0) {
            list.innerHTML = `
                <div class="conv-empty">
                    <i class="fas fa-comments"></i>
                    <h4>No hay conversaciones</h4>
                    <p>${currentConvTab === 'all' ? 'Empieza contactando a alguien desde una oferta.' : 'Prueba con otro filtro.'}</p>
                </div>
            `;
            return;
        }

        const typeLabels = {
            direct: 'Mensaje', inquiry: 'Consulta',
            application: 'Aplicación', system: 'Sistema'
        };
        const typeIcons = {
            direct: 'fa-comment', inquiry: 'fa-briefcase',
            application: 'fa-paper-plane', system: 'fa-cog'
        };

        let html = '';
        data.forEach(c => {
            const isCompanyUser = c.otherUser.type === 'empresa';
            const avatarHtml = getAvatarHtml(c.otherUser.name, c.otherUser.avatar, false, isCompanyUser);
            const timeStr = timeAgo(c.lastMessageAt);
            const unreadClass = c.unread > 0 ? 'unread' : '';
            const dot = c.unread > 0 ? '<span class="conv-unread-dot"></span>' : '';
            const typeCls = c.type || 'direct';

            html += `
                <div class="conv-item ${unreadClass}" data-id="${c.id}" data-type="${typeCls}">
                    <div class="conv-avatar ${isCompanyUser ? 'company' : ''}">
                        ${avatarHtml}
                        <span class="type-badge ${typeCls}">
                            <i class="fas ${typeIcons[typeCls] || 'fa-comment'}"></i>
                        </span>
                    </div>
                    <div class="conv-info">
                        <div class="conv-name">
                            <a href="${BASE_URL}perfil?user=${c.otherUser.id}" onclick="event.stopPropagation()" style="color:inherit; text-decoration:none; font-weight:700;" title="Ver perfil de ${escapeHtml(c.otherUser.name)}">${escapeHtml(c.otherUser.name)}</a>
                            ${isCompanyUser ? '<span class="publisher-type-tag empresa">Empresa</span>' : (c.otherUser.type === 'universidad' ? '<span class="publisher-type-tag" style="background:#ede9fe;color:#6d28d9;">Universidad</span>' : '')}
                            <span class="conv-type-tag ${typeCls}">${typeLabels[typeCls] || 'Mensaje'}</span>
                        </div>
                        ${c.relatedOfferTitle ? `
                            <div class="conv-context">
                                <i class="fas fa-briefcase"></i>
                                ${escapeHtml(c.relatedOfferTitle)}
                            </div>
                        ` : ''}
                        <div class="conv-preview">${escapeHtml((c.lastMessagePreview || '').replace(/\[\[STUDENT_RECOMMENDATION[\s\S]*$/g, '').trim() || 'Perfil de estudiante recomendado')}</div>
                        <div class="conv-time"><i class="far fa-clock"></i> ${timeStr}</div>
                    </div>
                    ${dot}
                    <div class="conv-actions">
                        <a href="${BASE_URL}perfil?user=${c.otherUser.id}" onclick="event.stopPropagation()" title="Ver perfil" style="display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; background:#edf4ff; color:#0a66c2; text-decoration:none;">
                            <i class="fas fa-user"></i>
                        </a>
                        ${c.status === 'active' ? `
                            <button class="archive-btn" data-id="${c.id}" title="Archivar">
                                <i class="fas fa-archive"></i>
                            </button>
                            <button class="block-btn" data-id="${c.id}" title="Bloquear">
                                <i class="fas fa-ban"></i>
                            </button>
                        ` : ''}
                        ${c.status === 'archived' ? `
                            <button class="unarchive-btn" data-id="${c.id}" title="Desarchivar">
                                <i class="fas fa-undo"></i>
                            </button>
                        ` : ''}
                        ${c.status === 'blocked' ? `
                            <button class="unblock-btn" data-id="${c.id}" title="Desbloquear">
                                <i class="fas fa-undo"></i>
                            </button>
                        ` : ''}
                        <button class="delete-btn" data-id="${c.id}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        list.innerHTML = html;
        setupConvEvents();
    }

    function setupConvEvents() {
        document.querySelectorAll('.conv-item').forEach(el => {
            el.addEventListener('click', function(e) {
                if (e.target.closest('button')) return;
                const id = this.dataset.id;
                openChat(id);
            });
        });

        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                updateConversationStatus(this.dataset.id, 'archivada', 'Conversación archivada');
            });
        });

        document.querySelectorAll('.unarchive-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                updateConversationStatus(this.dataset.id, 'activa', 'Conversación restaurada');
            });
        });

        document.querySelectorAll('.block-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const conv = conversations.find(c => c.id === this.dataset.id);
                if (conv && confirm(`¿Bloquear a ${conv.otherUser.name}?`)) {
                    updateConversationStatus(this.dataset.id, 'bloqueada', `${conv.otherUser.name} bloqueado`);
                }
            });
        });

        document.querySelectorAll('.unblock-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                updateConversationStatus(this.dataset.id, 'activa', 'Usuario desbloqueado');
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.dataset.id;
                if (confirm('¿Eliminar esta conversación?')) {
                    apiRequest(`/conversations/${encodeURIComponent(id)}`, { method: 'DELETE' })
                        .then(() => loadConversationsFromApi())
                        .then(() => showToast('Conversación eliminada'))
                        .catch(error => showToast(error.message));
                }
            });
        });
    }

    function updateConversationStatus(id, status, successMessage) {
        apiRequest(`/conversations/${encodeURIComponent(id)}/status`, {
            method: 'PUT',
            body: JSON.stringify({ estado: status })
        }).then(() => loadConversationsFromApi())
            .then(() => showToast(successMessage))
            .catch(error => showToast(error.message));
    }

    async function openChat(convId) {
        let conv = conversations.find(c => c.id === convId);
        if (!conv) return;

        activeConversationId = convId;
        try {
            await loadConversationMessages(convId);
            await loadConversationsFromApi();
            conv = conversations.find(item => item.id === convId);
        } catch (error) {
            showToast(error.message);
            return;
        }
        if (!conv) return;

        document.getElementById('conversationsPanel').classList.remove('active');
        document.getElementById('chatPanel').classList.add('active');
        document.getElementById('pageTitle').textContent = 'Conversación';
        document.getElementById('filtersBar').style.display = 'none';

        renderChat();
    }

    function renderChat() {
        const conv = conversations.find(c => c.id === activeConversationId);
        if (!conv) return;

        const isCompanyUser = conv.otherUser.type === 'empresa';
        const avatarHtml = getAvatarHtml(conv.otherUser.name, conv.otherUser.avatar, false, isCompanyUser);

        document.getElementById('chatHeader').innerHTML = `
            <a href="${BASE_URL}perfil?user=${conv.otherUser.id}" class="conv-avatar ${isCompanyUser ? 'company' : ''}" style="text-decoration:none;" title="Ver perfil">
                ${avatarHtml}
            </a>
            <div class="chat-info">
                <a href="${BASE_URL}perfil?user=${conv.otherUser.id}" class="chat-name" style="color:inherit; text-decoration:none;" title="Ver perfil">${escapeHtml(conv.otherUser.name)}</a>
                <div class="chat-status">
                    <span class="online-dot"></span>
                    ${isCompanyUser ? 'Empresa' : (conv.otherUser.type === 'universidad' ? 'Universidad' : 'Estudiante')}
                    ${conv.relatedOfferTitle ? ' · ' + escapeHtml(conv.relatedOfferTitle) : ''}
                </div>
            </div>
            <a href="${BASE_URL}perfil?user=${conv.otherUser.id}" style="padding:7px 12px; border-radius:10px; background:#edf4ff; color:#0a66c2; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-right:6px;">
                <i class="fas fa-id-card"></i> Ver perfil
            </a>
            <button class="conv-btn close-btn" id="closeChatBtn"><i class="fas fa-times"></i></button>
        `;

        document.getElementById('closeChatBtn').addEventListener('click', closeChat);

        const convMsgs = messages
            .filter(m => m.conversationId === activeConversationId)
            .sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));

        const isEmpresaViewer = (localStorage.getItem('userType') || '') === 'participante_empresa';
        const myCompanyOffers = isEmpresaViewer
            ? ofertasData.filter(o => o.publisher.ownerId === getUserId())
            : [];

        let html = '';
        convMsgs.forEach(m => {
            const isSent = m.from === getUserId();
            const cls = isSent ? 'sent' : 'received';
            const recMatch = String(m.body || '').match(/\[\[STUDENT_RECOMMENDATION:(\d+)\|([^\]]+)\]\]/);
            const cleanBody = String(m.body || '').replace(/\[\[STUDENT_RECOMMENDATION:[^\]]+\]\]/g, '').trim();

            let recCardHtml = '';
            if (recMatch) {
                const recStudentId = Number(recMatch[1]);
                const recStudentName = recMatch[2];
                const offerSelectHtml = isEmpresaViewer && myCompanyOffers.length > 0
                    ? `<div style="margin-top:10px; display:flex; flex-direction:column; gap:8px;">
                           <select class="rec-offer-select" data-student-id="${recStudentId}" style="width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:8px; font-size:12.5px; color:#0b253b; background:#fff;">
                               ${myCompanyOffers.map(of => `<option value="${of.id}">${escapeHtml(of.title)}</option>`).join('')}
                           </select>
                           <button type="button" class="btn-select-recommended" data-student-id="${recStudentId}" style="background:#0b7e3d; color:#fff; border:none; padding:8px 12px; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                               <i class="fas fa-user-check"></i> Seleccionar en esta oferta
                           </button>
                       </div>`
                    : '';

                recCardHtml = `
                    <div style="${cleanBody ? 'margin-top:10px; ' : ''}padding:12px; border-radius:12px; background:#ffffff; border:1px solid #dbe7f6; color:#0b253b;">
                        <div style="font-size:12px; font-weight:700; color:#6d28d9; margin-bottom:6px;">
                            <i class="fas fa-user-graduate"></i> Estudiante recomendado: ${escapeHtml(recStudentName)}
                        </div>
                        <a href="${BASE_URL}perfil?user=${recStudentId}" style="display:inline-flex; align-items:center; gap:6px; background:#0A66C2; color:#fff; padding:7px 12px; border-radius:8px; font-size:12.5px; font-weight:600; text-decoration:none;">
                            <i class="fas fa-id-card"></i> Ver perfil del estudiante
                        </a>
                        ${offerSelectHtml}
                    </div>
                `;
            }

            html += `
                <div class="chat-bubble ${cls}">
                    ${cleanBody ? escapeHtml(cleanBody).replace(/\n/g, '<br>') : ''}
                    ${recCardHtml}
                    <span class="bubble-time">${timeAgo(m.timestamp)}</span>
                </div>
            `;
        });

        if (convMsgs.length === 0) {
            html = `
                <div class="chat-bubble system">
                    Inicio de la conversación · ${getFullDate(conv.createdAt)}
                </div>
            `;
        }

        const msgsEl = document.getElementById('chatMessages');
        msgsEl.innerHTML = html;
        msgsEl.querySelectorAll('.btn-select-recommended').forEach(btn => {
            btn.addEventListener('click', async () => {
                const studentId = Number(btn.dataset.studentId);
                const selectEl = btn.parentElement.querySelector('.rec-offer-select');
                const offerId = selectEl ? Number(selectEl.value) : 0;
                if (!offerId) { showToast('Selecciona una oferta primero'); return; }
                btn.disabled = true;
                try {
                    const res = await apiRequest(`/offers/${offerId}/select-recommended`, {
                        method: 'POST',
                        body: JSON.stringify({ student_id: studentId })
                    });
                    showToast(res.message || 'Estudiante seleccionado en la oferta.');
                    btn.innerHTML = '<i class="fas fa-check"></i> Seleccionado';
                    loadConversationsFromApi().catch(() => {});
                } catch (err) {
                    showToast(err.message);
                    btn.disabled = false;
                }
            });
        });
        msgsEl.scrollTop = msgsEl.scrollHeight;

        setTimeout(() => document.getElementById('chatInput').focus(), 100);
    }

    function closeChat() {
        activeConversationId = null;
        document.getElementById('chatPanel').classList.remove('active');
        document.getElementById('conversationsPanel').classList.add('active');
        document.getElementById('pageTitle').textContent = 'Conversaciones';
        renderConversations();
    }

    async function sendChatMessage() {
        const input = document.getElementById('chatInput');
        const body = input.value.trim();
        if (!body || !activeConversationId) return;

        const conv = conversations.find(c => c.id === activeConversationId);
        if (!conv) return;

        try {
            await apiRequest(`/conversations/${encodeURIComponent(conv.id)}/messages`, {
                method: 'POST',
                body: JSON.stringify({ cuerpo: body })
            });
            input.value = '';
            await loadConversationMessages(conv.id);
            await loadConversationsFromApi();
            renderChat();
        } catch (error) {
            showToast(error.message);
        }
    }

    document.getElementById('chatSendBtn').addEventListener('click', sendChatMessage);
    document.getElementById('chatInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); sendChatMessage(); }
    });

    function updateMessagesBadge() {
        const totalUnread = conversations
            .filter(c => c.status === 'active')
            .reduce((sum, c) => sum + (c.unread || 0), 0);

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

    let contactData = {};

    function openContactModal(data) {
        contactData = data;
        const modal = document.getElementById('contactModal');
        const title = document.getElementById('contactModalTitle');
        const toInput = document.getElementById('contactTo');
        const contextGroup = document.getElementById('contactContextGroup');
        const contextInput = document.getElementById('contactContext');
        const subjectInput = document.getElementById('contactSubject');

        title.textContent = 'Chatear';
        toInput.value = data.toName + (data.toType === 'empresa' ? ' (Empresa)' : ' (Usuario)');

        if (data.offerTitle) {
            contextGroup.style.display = 'block';
            contextInput.value = data.offerTitle;
        } else {
            contextGroup.style.display = 'none';
        }

        if (data.type === 'inquiry') {
            subjectInput.value = 'Consulta sobre: ' + (data.offerTitle || '');
            document.getElementById('contactBody').placeholder = 'Escribe tu consulta sobre la oferta...';
        } else {
            subjectInput.value = '';
            document.getElementById('contactBody').placeholder = 'Escribe tu mensaje...';
        }

        document.getElementById('contactBody').value = '';
        modal.classList.add('active');
    }

    document.getElementById('closeContactModal').addEventListener('click', () => {
        document.getElementById('contactModal').classList.remove('active');
    });
    document.getElementById('cancelContactBtn').addEventListener('click', () => {
        document.getElementById('contactModal').classList.remove('active');
    });
    document.getElementById('contactModal').addEventListener('click', (e) => {
        if (e.target.id === 'contactModal') e.currentTarget.classList.remove('active');
    });

    document.getElementById('contactForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const body = document.getElementById('contactBody').value.trim();
        const subject = document.getElementById('contactSubject').value.trim();
        if (!body) { showToast('Escribe un mensaje'); return; }

        sendMessage({
            recipientId: contactData.recipientId,
            toName: contactData.toName,
            toEmail: contactData.toEmail,
            toType: contactData.toType,
            toAvatar: contactData.toAvatar,
            offerId: contactData.offerId,
            offerTitle: contactData.offerTitle,
            type: contactData.type,
            subject: subject,
            body: body,
            recommendedStudent: contactData.recommendedStudent || null
        }).then(() => {
            if (contactData) contactData.recommendedStudent = null;
            const recGroup = document.getElementById('recommendStudentGroup');
            if (recGroup) recGroup.style.display = 'none';
            document.getElementById('contactModal').classList.remove('active');
        }).catch(error => showToast(error.message));
    });

    async function sendMessage({ recipientId, toName, offerId, type, subject, body, recommendedStudent }) {
        const result = await apiRequest('/conversations', {
            method: 'POST',
            body: JSON.stringify({
                destinatario_id: recipientId,
                oferta_id: offerId || null,
                tipo: type === 'inquiry' ? 'consulta' : 'directa',
                asunto: subject,
                cuerpo: body
            })
        });
        if (recommendedStudent && recommendedStudent.id && result.conversation_id) {
            await apiRequest(`/conversations/${encodeURIComponent(result.conversation_id)}/messages`, {
                method: 'POST',
                body: JSON.stringify({
                    cuerpo: `[[STUDENT_RECOMMENDATION:${recommendedStudent.id}|${recommendedStudent.name}]]`
                })
            });
        }
        await loadConversationsFromApi();
        await showMessages();
        await openChat(result.conversation_id);
        showToast(`Mensaje enviado a ${toName}`);
    }

    document.getElementById('newConvBtn')?.addEventListener('click', () => {
        document.getElementById('newMsgModal').classList.add('active');
    });
    document.getElementById('closeNewMsgModal').addEventListener('click', () => {
        document.getElementById('newMsgModal').classList.remove('active');
    });
    document.getElementById('cancelNewMsgBtn').addEventListener('click', () => {
        document.getElementById('newMsgModal').classList.remove('active');
    });
    document.getElementById('newMsgModal').addEventListener('click', (e) => {
        if (e.target.id === 'newMsgModal') e.currentTarget.classList.remove('active');
    });

    document.getElementById('newMsgForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const to = Number(document.getElementById('newMsgTo').value);
        const subject = document.getElementById('newMsgSubject').value.trim();
        const body = document.getElementById('newMsgBody').value.trim();
        if (!to || !body) { showToast('Completa todos los campos'); return; }
        const recipient = recipientUsers.find(user => Number(user.id) === to);
        if (!recipient) { showToast('Selecciona un destinatario válido'); return; }

        sendMessage({ recipientId: to, toName: [recipient.nombre, recipient.apellido].filter(Boolean).join(' ') || recipient.correo, type: 'direct', subject, body })
            .then(() => {
                document.getElementById('newMsgModal').classList.remove('active');
                document.getElementById('newMsgForm').reset();
            })
            .catch(error => showToast(error.message));
    });

    function hideAllPanels() {
        notifPanel.classList.remove('active');
        document.getElementById('conversationsPanel').classList.remove('active');
        document.getElementById('chatPanel').classList.remove('active');
        feedContainer.style.display = 'flex';
        filtersBar.style.display = 'flex';
        const sb = document.querySelector('.search-box');
        if (sb) sb.style.display = 'flex';
        activeConversationId = null;
    }

    async function showFeed() {
        document.body.style.cursor = 'progress';
        try {
            const result = await apiRequest('/offers');
            ofertasData = result.offers.map(normalizeOffer);
            hideAllPanels();
            currentView = 'feed';
            currentViewMode = 'all';
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.getElementById('feedSidebarBtn')?.classList.add('active');
            document.getElementById('pageTitle').textContent = 'Ofertas';
            renderOfertas(currentFilter);
        } catch (error) {
            showToast(error.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    async function showMessages() {
        document.body.style.cursor = 'progress';
        try {
            await loadConversationsFromApi();
            hideAllPanels();
            currentView = 'messages';
            document.getElementById('conversationsPanel').classList.add('active');
            feedContainer.style.display = 'none';
            filtersBar.style.display = 'none';
            const sb = document.querySelector('.search-box');
            if (sb) sb.style.display = 'none';
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.getElementById('messagesSidebarBtn')?.classList.add('active');
            document.getElementById('pageTitle').textContent = 'Conversaciones';
            renderConversations();
        } catch (error) {
            showToast(error.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    async function showNotifPanel() {
        document.body.style.cursor = 'progress';
        try {
            await loadNotificationsFromApi();
            hideAllPanels();
            currentView = 'notifications';
            notifPanel.classList.add('active');
            feedContainer.style.display = 'none';
            filtersBar.style.display = 'none';
            const sb = document.querySelector('.search-box');
            if (sb) sb.style.display = 'none';
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.getElementById('notifSidebarBtn')?.classList.add('active');
            document.getElementById('pageTitle').textContent = 'Notificaciones';
            renderNotif();
        } catch (error) {
            showToast(error.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    async function showSaved() {
        document.body.style.cursor = 'progress';
        try {
            const result = await apiRequest('/offers?view=saved');
            ofertasData = result.offers.map(normalizeOffer);
            hideAllPanels();
            currentView = 'saved';
            currentViewMode = 'saved';
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.getElementById('savedSidebarBtn')?.classList.add('active');
            document.getElementById('pageTitle').textContent = 'Ofertas Guardadas';
            renderOfertas('saved');
        } catch (error) {
            showToast(error.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    function renderStudentApplications(applications) {
        const feedContainer = document.getElementById('feedContainer');
        document.getElementById('pageTitle').textContent = 'Mis Postulaciones';
        document.getElementById('filtersBar').style.display = 'none';
        feedContainer.style.display = 'flex';

        if (!applications.length) {
            feedContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-briefcase"></i>
                    <h3>Aún no te has postulado a ninguna oferta</h3>
                    <p>Explora las ofertas de becarios disponibles y postúlate para ganar experiencia y créditos académicos.</p>
                </div>
            `;
            return;
        }

        const statusColors = {
            pendiente:  { bg: '#fef3c7', color: '#92400e', label: 'Pendiente' },
            revisada:   { bg: '#e0f2fe', color: '#075985', label: 'Revisada' },
            entrevista: { bg: '#ede9fe', color: '#5b21b6', label: 'Entrevista' },
            aceptada:   { bg: '#dcfce7', color: '#166534', label: 'Aceptada ✓' },
            rechazada:  { bg: '#fee2e2', color: '#991b1b', label: 'No seleccionada' }
        };

        feedContainer.innerHTML = applications.map(app => {
            const st = statusColors[app.estado] || statusColors.pendiente;
            const profileUrl = app.publicado_por ? `${BASE_URL}perfil?user=${app.publicado_por}` : '#';
            const verifIcon = app.organismo_verificado ? ' <i class="fas fa-check-circle" style="color:#1d9bf0; font-size:13px;" title="Empresa verificada"></i>' : '';
            return `
                <div class="offer-card">
                    <div class="offer-header">
                        <a href="${profileUrl}" class="offer-publisher" style="text-decoration:none; color:inherit;">
                            <div class="publisher-info">
                                <div class="publisher-name">${escapeHtml(app.organismo_nombre || 'Empresa')}${verifIcon}</div>
                                <div class="publisher-time"><i class="far fa-clock"></i> Postulado el ${escapeHtml(String(app.fecha_aplicacion || '').substring(0, 10))}</div>
                            </div>
                        </a>
                        <span style="background:${st.bg}; color:${st.color}; padding:5px 12px; border-radius:999px; font-size:12px; font-weight:700;">${st.label}</span>
                    </div>
                    <div class="offer-title">${escapeHtml(app.titulo)}</div>
                    <div class="offer-meta">
                        <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(app.ubicacion || '')}</span>
                        <span><i class="fas fa-laptop-house"></i> ${escapeHtml(app.modalidad || '')}</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function showMyOffers() {
        document.body.style.cursor = 'progress';
        try {
            if ((localStorage.getItem('userType') || 'estudiante') === 'estudiante') {
                const res = await apiRequest('/applications');
                hideAllPanels();
                currentView = 'mine';
                currentViewMode = 'mine';
                document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
                document.getElementById('myOffersSidebarBtn')?.classList.add('active');
                renderStudentApplications(res.applications || []);
            } else {
                const result = await apiRequest('/offers?view=mine');
                ofertasData = result.offers.map(normalizeOffer);
                hideAllPanels();
                currentView = 'mine';
                currentViewMode = 'mine';
                document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
                document.getElementById('myOffersSidebarBtn')?.classList.add('active');
                document.getElementById('pageTitle').textContent = 'Mis Ofertas';
                renderOfertas('mine');
            }
        } catch (error) {
            showToast(error.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    window.openApplicantsModal = async function(offerId) {
        const modal = document.getElementById('applicantsModal');
        const list = document.getElementById('applicantsList');
        const titleEl = document.getElementById('applicantsModalTitle');
        if (!modal || !list) return;
        list.innerHTML = '<p style="color:#64748b; padding:12px;">Cargando postulantes...</p>';
        modal.classList.add('active');

        try {
            const res = await apiRequest(`/offers/${offerId}/applicants`);
            const offer = res.offer || {};
            const applicants = res.applicants || [];
            titleEl.textContent = `Postulantes · ${offer.titulo || 'Oferta'}`;

            if (!applicants.length) {
                list.innerHTML = '<p style="color:#64748b; padding:16px; text-align:center;">Aún no hay estudiantes postulados a esta oferta.</p>';
                return;
            }

            list.innerHTML = applicants.map(a => {
                const fullName = [a.nombre, a.apellido].filter(Boolean).join(' ') || a.correo;
                const skillsHtml = (a.habilidades || []).slice(0, 6).map(s => `<span class="offer-tag" style="font-size:11px;">${escapeHtml(s)}</span>`).join('');
                const isAccepted = a.estado === 'aceptada';
                const isRejected = a.estado === 'rechazada';
                const vigBadge = a.estado_vigencia === 'vigente'
                    ? '<span style="background:#dcfce7; color:#166534; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:700;"><i class="fas fa-check-circle"></i> Vigente</span>'
                    : '<span style="background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:700;"><i class="fas fa-clock"></i> Vigencia pendiente</span>';
                const gradesBadge = a.notas_certificadas
                    ? `<span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:700;"><i class="fas fa-certificate"></i> Notas certificadas${a.promedio ? ' (' + escapeHtml(a.promedio) + ')' : ''}</span>`
                    : '';
                return `
                    <div style="border:1px solid #dbe7f6; border-radius:14px; padding:16px; background:#f8fbff; display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                            <div>
                                <a href="${BASE_URL}perfil?user=${a.usuario_id}" style="font-weight:700; color:#0a66c2; text-decoration:none; font-size:15px;">
                                    <i class="fas fa-user-graduate"></i> ${escapeHtml(fullName)}
                                </a>
                                <div style="font-size:12px; color:#64748b; margin-top:4px; display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                                    <span>${escapeHtml(a.universidad_nombre || 'Universidad no especificada')}</span>
                                    ${vigBadge} ${gradesBadge}
                                </div>
                            </div>
                            <span style="font-size:11px; font-weight:700; padding:4px 10px; border-radius:999px; background:${isAccepted ? '#dcfce7' : (isRejected ? '#fee2e2' : '#e0f2fe')}; color:${isAccepted ? '#166534' : (isRejected ? '#991b1b' : '#075985')};">
                                ${escapeHtml(a.estado)}
                            </span>
                        </div>
                        ${skillsHtml ? `<div style="display:flex; flex-wrap:wrap; gap:6px;">${skillsHtml}</div>` : ''}
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <a href="${BASE_URL}perfil?user=${a.usuario_id}" class="btn-outline" style="padding:7px 12px; font-size:12px; text-decoration:none; border-radius:8px; border:1px solid #cbd5e1; color:#0b253b;">
                                <i class="fas fa-id-card"></i> Ver perfil
                            </a>
                            <button type="button" class="btn-accept-app" data-app-id="${a.id}" data-offer-id="${offerId}" ${isAccepted ? 'disabled' : ''} style="padding:7px 12px; font-size:12px; border:none; border-radius:8px; background:#0b7e3d; color:#fff; cursor:pointer; font-weight:600; opacity:${isAccepted ? '0.6' : '1'};">
                                <i class="fas fa-check"></i> ${isAccepted ? 'Aceptado (Chat habilitado)' : 'Aceptar y habilitar chat'}
                            </button>
                            <button type="button" class="btn-reject-app" data-app-id="${a.id}" data-offer-id="${offerId}" ${isRejected ? 'disabled' : ''} style="padding:7px 12px; font-size:12px; border:none; border-radius:8px; background:#c53e3e; color:#fff; cursor:pointer; font-weight:600; opacity:${isRejected ? '0.6' : '1'};">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            list.querySelectorAll('.btn-accept-app').forEach(btn => {
                btn.addEventListener('click', async () => {
                    try {
                        await apiRequest(`/applications/${btn.dataset.appId}/status`, { method: 'PUT', body: JSON.stringify({ estado: 'aceptada' }) });
                        showToast('Estudiante aceptado. ¡Se habilitó el chat en Conversaciones!');
                        loadConversationsFromApi().catch(() => {});
                        window.openApplicantsModal(Number(btn.dataset.offerId));
                    } catch (err) { showToast(err.message); }
                });
            });
            list.querySelectorAll('.btn-reject-app').forEach(btn => {
                btn.addEventListener('click', async () => {
                    try {
                        await apiRequest(`/applications/${btn.dataset.appId}/status`, { method: 'PUT', body: JSON.stringify({ estado: 'rechazada' }) });
                        showToast('Postulación marcada como rechazada.');
                        window.openApplicantsModal(Number(btn.dataset.offerId));
                    } catch (err) { showToast(err.message); }
                });
            });
        } catch (err) {
            list.innerHTML = `<p style="color:#c53e3e; padding:12px;">${escapeHtml(err.message)}</p>`;
        }
    };

    document.getElementById('closeApplicantsModal')?.addEventListener('click', () => {
        document.getElementById('applicantsModal').classList.remove('active');
    });

    function renderOrganizations(organizations) {
        const list = document.getElementById('organizationList');
        if (!organizations.length) {
            list.textContent = 'Aún no tienes organismos asociados.';
            return;
        }
        list.innerHTML = organizations.map(organization => {
            const identity = organization.tipo === 'empresa' ? organization.rif : organization.siglas;
            const role = organization.rol_en_organismo === 'dueño' ? 'Dueño' : organization.rol_en_organismo;
            return `<div class="organization-list-item"><div><strong>${escapeHtml(organization.nombre)}</strong><small>${escapeHtml(identity || '')}</small></div><span>${escapeHtml(role)}</span></div>`;
        }).join('');
    }

    async function loadOrganizations() {
        const result = await apiRequest('/organisms');
        renderOrganizations(result.organisms || []);
    }

    async function loadOrganizationRequests() {
        const result = await apiRequest('/organisms/requests');
        const section = document.getElementById('organizationRequests');
        const list = document.getElementById('organizationRequestList');
        section.hidden = !(result.requests || []).length;
        list.innerHTML = (result.requests || []).map(request => `<div class="organization-request-item"><div><strong>${escapeHtml([request.nombre, request.apellido].filter(Boolean).join(' '))}</strong><small>${escapeHtml(request.correo)} · ${escapeHtml(request.organismo_nombre)}</small></div><button type="button" data-membership-id="${Number(request.id)}">Aprobar</button></div>`).join('');
        list.querySelectorAll('[data-membership-id]').forEach(button => {
            button.addEventListener('click', async () => {
                button.disabled = true;
                try {
                    await apiRequest(`/organisms/requests/${button.dataset.membershipId}/approve`, { method: 'POST', body: JSON.stringify({}) });
                    showToast('Solicitud aprobada.');
                    await Promise.all([loadOrganizationRequests(), loadOrganizations()]);
                } catch (error) {
                    button.disabled = false;
                    showToast(error.message);
                }
            });
        });
    }

    function setOrganizationMode(mode) {
        const creating = mode === 'create';
        document.getElementById('createOrganizationForm').hidden = !creating;
        document.getElementById('joinOrganizationForm').hidden = creating;
        document.getElementById('createOrganizationMode').classList.toggle('active', creating);
        document.getElementById('joinOrganizationMode').classList.toggle('active', !creating);
        document.getElementById('createOrganizationMode').setAttribute('aria-selected', String(creating));
        document.getElementById('joinOrganizationMode').setAttribute('aria-selected', String(!creating));
    }

    async function showUniversityStudents() {
        document.body.style.cursor = 'progress';
        try {
            const res = await apiRequest('/university/students');
            hideAllPanels();
            currentView = 'university_students';
            document.getElementById('filtersBar').style.display = 'none';
            const sb = document.querySelector('.search-box');
            if (sb) sb.style.display = 'none';
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.getElementById('universityStudentsSidebarBtn')?.classList.add('active');
            document.getElementById('pageTitle').textContent = 'Estudiantes de mi Universidad';
            const uni = res.university || {};
            const students = res.students || [];

            if (!students.length) {
                feedContainer.innerHTML = `
                    <div class="offer-card" style="text-align:center; padding:48px 24px; color:#64748b;">
                        <i class="fas fa-user-graduate" style="font-size:38px; color:#cbd5e1; margin-bottom:12px;"></i>
                        <h3 style="color:#0b253b;">Aún no hay estudiantes asociados a ${escapeHtml(uni.nombre || 'tu universidad')}</h3>
                        <p style="font-size:13.5px; margin-top:6px;">Cuando los estudiantes se registren y seleccionen tu universidad aparecerán aquí para validar su vigencia y notas.</p>
                    </div>`;
                return;
            }

            feedContainer.innerHTML = students.map(st => {
                const fullName = [st.nombre, st.apellido].filter(Boolean).join(' ') || st.correo;
                const vigBg = st.estado_vigencia === 'vigente' ? '#dcfce7' : (st.estado_vigencia === 'rechazado' ? '#fee2e2' : '#fef3c7');
                const vigColor = st.estado_vigencia === 'vigente' ? '#166534' : (st.estado_vigencia === 'rechazado' ? '#991b1b' : '#92400e');
                const docLink = st.constancia_notas_url
                    ? `<a href="${escapeHtml(st.constancia_notas_url)}" target="_blank" style="color:#0a66c2; font-size:12.5px; font-weight:600; text-decoration:none;"><i class="fas fa-file-alt"></i> Ver constancia de notas</a>`
                    : '<span style="font-size:12px; color:#94a3b8;">Sin constancia subida</span>';
                const changeBadge = st.cambio_universidad_solicitado
                    ? `<div style="background:#fff7ed; border:1px solid #fed7aa; padding:10px 12px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                         <span style="font-size:12.5px; color:#9a3412;"><i class="fas fa-exchange-alt"></i> Solicitó habilitar <strong>cambio de universidad</strong></span>
                         <button type="button" class="btn-enable-uni-change" data-uid="${st.usuario_id}" style="background:#ea580c; color:#fff; border:none; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">Habilitar cambio</button>
                       </div>`
                    : (st.cambio_universidad_habilitado ? '<span style="font-size:12px; color:#0b7e3d; font-weight:600;"><i class="fas fa-check"></i> Cambio de universidad habilitado</span>' : '');

                return `
                    <div class="offer-card" style="display:flex; flex-direction:column; gap:18px; padding:24px;">
                        <div class="offer-header" style="margin-bottom:0; align-items:flex-start; gap:12px;">
                            <div style="display:flex; flex-direction:column; gap:4px;">
                                <a href="${BASE_URL}perfil?user=${st.usuario_id}" style="font-size:16px; font-weight:700; color:#0a66c2; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                                    <i class="fas fa-user-graduate"></i> ${escapeHtml(fullName)}
                                </a>
                                <div style="font-size:13px; color:#64748b;">
                                    ${escapeHtml(st.correo)} ${st.cedula ? '· ' + escapeHtml(st.cedula) : ''}
                                </div>
                            </div>
                            <span style="background:${vigBg}; color:${vigColor}; padding:6px 14px; border-radius:999px; font-size:12px; font-weight:700; white-space:nowrap;">
                                Vigencia: ${escapeHtml(st.estado_vigencia || 'pendiente')}
                            </span>
                        </div>
                        ${changeBadge}
                        <div style="display:flex; gap:18px; flex-wrap:wrap; align-items:center; background:#f8fbff; padding:14px 18px; border-radius:14px; border:1px solid #e2ecf8;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <label style="font-size:13px; font-weight:600; color:#334155;">Promedio (0-20):</label>
                                <input type="number" step="0.01" min="0" max="20" class="uni-st-promedio" data-uid="${st.usuario_id}" value="${st.promedio ?? ''}" placeholder="Ej: 17.50" style="width:96px; padding:7px 10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;" />
                            </div>
                            <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#0369a1; cursor:pointer;">
                                <input type="checkbox" class="uni-st-cert" data-uid="${st.usuario_id}" ${st.notas_certificadas ? 'checked' : ''} />
                                Notas certificadas ✓
                            </label>
                            <div>${docLink}</div>
                        </div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; padding-top:4px;">
                            <button type="button" class="btn-uni-save" data-uid="${st.usuario_id}" data-vigencia="vigente" style="background:#0b7e3d; color:#fff; border:none; padding:9px 16px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                <i class="fas fa-check-circle"></i> Validar vigente y guardar notas
                            </button>
                            <button type="button" class="btn-uni-save" data-uid="${st.usuario_id}" data-vigencia="rechazado" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:9px 16px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                <i class="fas fa-times-circle"></i> Marcar no vigente
                            </button>
                            <a href="${BASE_URL}perfil?user=${st.usuario_id}" style="padding:9px 16px; border-radius:10px; border:1px solid #cbd5e1; color:#0b253b; text-decoration:none; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                                <i class="fas fa-id-card"></i> Ver perfil
                            </a>
                        </div>
                    </div>`;
            }).join('');

            feedContainer.querySelectorAll('.btn-uni-save').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const uid = btn.dataset.uid;
                    const promedio = feedContainer.querySelector(`.uni-st-promedio[data-uid="${uid}"]`)?.value ?? '';
                    const notas_certificadas = Boolean(feedContainer.querySelector(`.uni-st-cert[data-uid="${uid}"]`)?.checked);
                    try {
                        await apiRequest(`/university/students/${uid}`, {
                            method: 'PUT',
                            body: JSON.stringify({ estado_vigencia: btn.dataset.vigencia, promedio, notas_certificadas })
                        });
                        showToast('Ficha académica del estudiante actualizada.');
                        showUniversityStudents();
                    } catch (err) { showToast(err.message); }
                });
            });

            feedContainer.querySelectorAll('.btn-enable-uni-change').forEach(btn => {
                btn.addEventListener('click', async () => {
                    try {
                        await apiRequest(`/university/students/${btn.dataset.uid}`, {
                            method: 'PUT',
                            body: JSON.stringify({ cambio_universidad_habilitado: true })
                        });
                        showToast('Cambio de universidad habilitado para el estudiante.');
                        showUniversityStudents();
                    } catch (err) { showToast(err.message); }
                });
            });
        } catch (err) {
            showToast(err.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    async function showConvenios() {
        document.body.style.cursor = 'progress';
        try {
            const res = await apiRequest('/convenios');
            hideAllPanels();
            currentView = 'convenios';
            document.getElementById('filtersBar').style.display = 'none';
            const sb = document.querySelector('.search-box');
            if (sb) sb.style.display = 'none';
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.getElementById('conveniosSidebarBtn')?.classList.add('active');
            document.getElementById('pageTitle').textContent = 'Convenios Académicos';
            const convenios = res.convenios || [];
            const partners = res.partners || [];
            const myOrg = res.myOrg;
            const role = localStorage.getItem('userType') || 'estudiante';
            const myUserId = Number(localStorage.getItem('userIdNumeric') || '0');

            let createFormHtml = '';
            if (myOrg && (role === 'participante_empresa' || role === 'participante_universidad')) {
                const partnerLabel = myOrg.tipo === 'empresa' ? 'Universidad aliada' : 'Empresa aliada';
                createFormHtml = `
                    <div class="offer-card" style="background:linear-gradient(135deg,#f8fbff,#eef5ff); border:1.5px solid #cfe0f5;">
                        <h3 style="color:#0b253b; font-size:17px; margin-bottom:10px;"><i class="fas fa-handshake" style="color:#0a66c2;"></i> Proponer nuevo convenio de créditos académicos</h3>
                        <form id="createConvenioForm" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div style="grid-column:1/-1;">
                                <label style="font-size:12.5px; font-weight:600; display:block; margin-bottom:4px;">Título del convenio</label>
                                <input type="text" id="convenioTitle" required maxlength="180" placeholder="Ej: Alianza de pasantías e innovación tecnológica" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px;" />
                            </div>
                            <div>
                                <label style="font-size:12.5px; font-weight:600; display:block; margin-bottom:4px;">${partnerLabel}</label>
                                <select id="convenioPartner" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; background:#fff;">
                                    <option value="">Selecciona...</option>
                                    ${partners.map(p => `<option value="${p.id}">${escapeHtml(p.nombre)}${p.siglas ? ' (' + escapeHtml(p.siglas) + ')' : ''}</option>`).join('')}
                                </select>
                            </div>
                            <div>
                                <label style="font-size:12.5px; font-weight:600; display:block; margin-bottom:4px;">Créditos académicos del convenio (uso interno)</label>
                                <input type="number" id="convenioCredits" min="0" max="1000" value="6" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px;" />
                            </div>
                            <div style="grid-column:1/-1;">
                                <label style="font-size:12.5px; font-weight:600; display:block; margin-bottom:4px;">Descripción / Alcance</label>
                                <textarea id="convenioDesc" rows="2" placeholder="Describe los términos del convenio entre la empresa y la universidad..." style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px;"></textarea>
                            </div>
                            <div style="grid-column:1/-1; display:flex; justify-content:flex-end;">
                                <button type="submit" class="btn-primary" style="padding:10px 18px; border-radius:10px;"><i class="fas fa-paper-plane"></i> Enviar propuesta de convenio</button>
                            </div>
                        </form>
                    </div>`;
            }

            const listHtml = !convenios.length
                ? `<div class="offer-card" style="text-align:center; padding:42px 20px; color:#64748b;">
                     <i class="fas fa-handshake" style="font-size:36px; color:#cbd5e1; margin-bottom:10px;"></i>
                     <h4 style="color:#0b253b;">No hay convenios registrados aún</h4>
                   </div>`
                : convenios.map(c => {
                    const bg = c.estado === 'activo' ? '#dcfce7' : (c.estado === 'rechazado' ? '#fee2e2' : '#fef3c7');
                    const col = c.estado === 'activo' ? '#166534' : (c.estado === 'rechazado' ? '#991b1b' : '#92400e');
                    const canApprove = c.estado === 'pendiente' && (role === 'admin' || Number(c.solicitado_por) !== myUserId) && role !== 'estudiante';
                    const verif = Number(c.empresa_verificada) === 1 ? ' <i class="fas fa-check-circle" style="color:#1d9bf0;"></i>' : '';
                    return `
                        <div class="offer-card">
                            <div class="offer-header">
                                <div>
                                    <div style="font-size:16px; font-weight:700; color:#0b253b;">${escapeHtml(c.titulo)}</div>
                                    <div style="font-size:13px; color:#475569; margin-top:4px;">
                                        <i class="fas fa-building" style="color:#0a66c2;"></i> <strong>${escapeHtml(c.empresa_nombre)}</strong>${verif}
                                        &nbsp;↔&nbsp;
                                        <i class="fas fa-university" style="color:#6d28d9;"></i> <strong>${escapeHtml(c.universidad_nombre)}</strong> (${escapeHtml(c.universidad_siglas || '')})
                                    </div>
                                </div>
                                <span style="background:${bg}; color:${col}; padding:5px 12px; border-radius:999px; font-size:12px; font-weight:700; text-transform:capitalize;">${escapeHtml(c.estado)}</span>
                            </div>
                            ${c.descripcion ? `<div class="offer-description">${escapeHtml(c.descripcion)}</div>` : ''}
                            ${canApprove ? `
                                <div style="display:flex; gap:8px; margin-top:8px;">
                                    <button type="button" class="btn-conv-status" data-id="${c.id}" data-status="activo" style="background:#0b7e3d; color:#fff; border:none; padding:8px 14px; border-radius:10px; font-size:12.5px; font-weight:600; cursor:pointer;"><i class="fas fa-check"></i> Aprobar convenio</button>
                                    <button type="button" class="btn-conv-status" data-id="${c.id}" data-status="rechazado" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:8px 14px; border-radius:10px; font-size:12.5px; font-weight:600; cursor:pointer;"><i class="fas fa-times"></i> Rechazar</button>
                                </div>` : ''}
                        </div>`;
                }).join('');

            feedContainer.innerHTML = createFormHtml + listHtml;

            document.getElementById('createConvenioForm')?.addEventListener('submit', async (e) => {
                e.preventDefault();
                try {
                    await apiRequest('/convenios', {
                        method: 'POST',
                        body: JSON.stringify({
                            titulo: document.getElementById('convenioTitle').value.trim(),
                            partner_id: Number(document.getElementById('convenioPartner').value),
                            creditos_academicos: Number(document.getElementById('convenioCredits').value || 0),
                            descripcion: document.getElementById('convenioDesc').value.trim()
                        })
                    });
                    showToast('Propuesta de convenio enviada correctamente.');
                    showConvenios();
                } catch (err) { showToast(err.message); }
            });

            feedContainer.querySelectorAll('.btn-conv-status').forEach(btn => {
                btn.addEventListener('click', async () => {
                    try {
                        await apiRequest(`/convenios/${btn.dataset.id}/status`, {
                            method: 'PUT',
                            body: JSON.stringify({ estado: btn.dataset.status })
                        });
                        showToast('Estado del convenio actualizado.');
                        showConvenios();
                    } catch (err) { showToast(err.message); }
                });
            });
        } catch (err) {
            showToast(err.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    async function showAdminPanel() {
        document.body.style.cursor = 'progress';
        try {
            const res = await apiRequest('/admin/pending');
            hideAllPanels();
            currentView = 'admin';
            document.getElementById('filtersBar').style.display = 'none';
            const sb = document.querySelector('.search-box');
            if (sb) sb.style.display = 'none';
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            document.getElementById('adminSidebarBtn')?.classList.add('active');
            document.getElementById('pageTitle').textContent = 'Panel de Administración';
            const coords = res.coordinators || [];
            const companies = res.companies || [];
            const plans = res.plans || [];

            const coordsHtml = !coords.length
                ? '<p style="font-size:13.5px; color:#64748b;">No hay coordinadores universitarios pendientes de aprobación.</p>'
                : coords.map(c => `
                    <div style="border:1px solid #dbe7f6; border-radius:12px; padding:14px; background:#f8fbff; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                        <div>
                            <strong style="color:#0b253b;">${escapeHtml(c.nombre)} ${escapeHtml(c.apellido)}</strong> · <span style="color:#6d28d9; font-weight:700;">${escapeHtml(c.universidad_nombre)} (${escapeHtml(c.universidad_siglas || '')})</span>
                            <div style="font-size:12.5px; color:#64748b;">${escapeHtml(c.correo)} · Cédula: ${escapeHtml(c.cedula || '-')}</div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn-admin-coord" data-id="${c.id}" data-action="approve" style="background:#0b7e3d; color:#fff; border:none; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;"><i class="fas fa-check"></i> Aprobar</button>
                            <button type="button" class="btn-admin-coord" data-id="${c.id}" data-action="reject" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;"><i class="fas fa-times"></i> Rechazar</button>
                        </div>
                    </div>`).join('');

            const companiesHtml = !companies.length
                ? '<p style="font-size:13.5px; color:#64748b;">No hay empresas registradas.</p>'
                : companies.map(emp => {
                    const isVerif = Number(emp.verificado) === 1;
                    const docHtml = emp.documento_verificacion_url
                        ? `<a href="${escapeHtml(emp.documento_verificacion_url)}" target="_blank" style="color:#0a66c2; font-size:12.5px; font-weight:600; text-decoration:none;"><i class="fas fa-file-alt"></i> Ver documento</a>`
                        : '<span style="font-size:12px; color:#94a3b8;">Sin documento subido</span>';
                    const planOptions = plans.map(pl => {
                        const priceText = Number(pl.precio) > 0 ? ` ($${Number(pl.precio).toFixed(2)})` : '';
                        const sel = Number(emp.plan_id || 1) === Number(pl.id) ? 'selected' : '';
                        return `<option value="${pl.id}" ${sel}>${escapeHtml(pl.nombre)}${priceText}</option>`;
                    }).join('');
                    return `
                        <div style="border:1px solid #dbe7f6; border-radius:12px; padding:14px; background:#f8fbff; display:flex; flex-direction:column; gap:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                                <div>
                                    <strong style="color:#0b253b;">${escapeHtml(emp.nombre)}</strong> ${isVerif ? '<i class="fas fa-check-circle" style="color:#1d9bf0;"></i>' : ''} · <span style="font-size:12.5px; color:#475569;">RIF: ${escapeHtml(emp.rif || '-')}</span>
                                    <div style="font-size:12.5px; color:#64748b;">Rep: ${escapeHtml(emp.rep_nombre)} ${escapeHtml(emp.rep_apellido)} (${escapeHtml(emp.rep_correo)}) · Verificación: <strong>${escapeHtml(emp.estado_verificacion)}</strong> · ${docHtml}</div>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" class="btn-admin-comp" data-id="${emp.id}" data-action="approve" ${isVerif ? 'disabled' : ''} style="background:#1d9bf0; color:#fff; border:none; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; opacity:${isVerif ? '0.6' : '1'};"><i class="fas fa-check-circle"></i> ${isVerif ? 'Verificada' : 'Verificar empresa'}</button>
                                    <button type="button" class="btn-admin-comp" data-id="${emp.id}" data-action="reject" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;"><i class="fas fa-times"></i> Rechazar</button>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; border-top:1px solid #e2ecf8; padding-top:10px;">
                                <span style="font-size:12.5px; font-weight:600; color:#334155;"><i class="fas fa-crown" style="color:#d97706;"></i> Plan actual:</span>
                                <select class="admin-company-plan-select" data-id="${emp.id}" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:8px; font-size:12.5px; background:#fff;">
                                    ${planOptions}
                                </select>
                                <button type="button" class="btn-admin-save-plan" data-id="${emp.id}" style="background:#0A66C2; color:#fff; border:none; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                    <i class="fas fa-save"></i> Guardar plan
                                </button>
                            </div>
                        </div>`;
                }).join('');

            feedContainer.innerHTML = `
                <div class="offer-card" style="gap:12px;">
                    <h3 style="color:#0b253b; font-size:17px;"><i class="fas fa-university" style="color:#6d28d9;"></i> Coordinadores Universitarios Pendientes (${coords.length})</h3>
                    ${coordsHtml}
                </div>
                <div class="offer-card" style="gap:12px;">
                    <h3 style="color:#0b253b; font-size:17px;"><i class="fas fa-check-circle" style="color:#1d9bf0;"></i> Verificación de Empresas</h3>
                    ${companiesHtml}
                </div>`;

            feedContainer.querySelectorAll('.btn-admin-coord').forEach(btn => {
                btn.addEventListener('click', async () => {
                    try {
                        await apiRequest(`/admin/coordinators/${btn.dataset.id}`, { method: 'PUT', body: JSON.stringify({ action: btn.dataset.action }) });
                        showToast(btn.dataset.action === 'approve' ? 'Coordinador universitario aprobado.' : 'Solicitud rechazada.');
                        showAdminPanel();
                    } catch (err) { showToast(err.message); }
                });
            });
            feedContainer.querySelectorAll('.btn-admin-comp').forEach(btn => {
                btn.addEventListener('click', async () => {
                    try {
                        await apiRequest(`/admin/companies/${btn.dataset.id}/verify`, { method: 'PUT', body: JSON.stringify({ action: btn.dataset.action }) });
                        showToast(btn.dataset.action === 'approve' ? 'Empresa verificada oficialmente ✓' : 'Verificación rechazada.');
                        showAdminPanel();
                    } catch (err) { showToast(err.message); }
                });
            });
            feedContainer.querySelectorAll('.btn-admin-save-plan').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const compId = btn.dataset.id;
                    const sel = feedContainer.querySelector(`.admin-company-plan-select[data-id="${compId}"]`);
                    const planId = sel ? Number(sel.value) : 0;
                    if (!planId) return;
                    try {
                        const r = await apiRequest(`/admin/companies/${compId}/plan`, { method: 'PUT', body: JSON.stringify({ plan_id: planId }) });
                        showToast(`Plan actualizado a: ${r.plan?.nombre || 'seleccionado'}`);
                        showAdminPanel();
                    } catch (err) { showToast(err.message); }
                });
            });
        } catch (err) {
            showToast(err.message);
        } finally {
            document.body.style.cursor = '';
        }
    }

    document.getElementById('feedSidebarBtn').addEventListener('click', (e) => { e.preventDefault(); showFeed(); });
    document.getElementById('messagesSidebarBtn').addEventListener('click', (e) => { e.preventDefault(); showMessages(); });
    document.getElementById('notifSidebarBtn').addEventListener('click', (e) => { e.preventDefault(); showNotifPanel(); });
    document.getElementById('savedSidebarBtn')?.addEventListener('click', (e) => { e.preventDefault(); showSaved(); });
    document.getElementById('myOffersSidebarBtn')?.addEventListener('click', (e) => { e.preventDefault(); showMyOffers(); });
    document.getElementById('universityStudentsSidebarBtn')?.addEventListener('click', (e) => { e.preventDefault(); showUniversityStudents(); });
    document.getElementById('conveniosSidebarBtn')?.addEventListener('click', (e) => { e.preventDefault(); showConvenios(); });
    document.getElementById('adminSidebarBtn')?.addEventListener('click', (e) => { e.preventDefault(); showAdminPanel(); });

    const organizationsSidebarBtn = document.getElementById('organizationsSidebarBtn');
    const organizationModal = document.getElementById('organizationModal');
    if (organizationsSidebarBtn && organizationModal) {
        organizationsSidebarBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            organizationModal.classList.add('active');
        });
    }
    document.getElementById('closeOrganizationModal')?.addEventListener('click', () => organizationModal?.classList.remove('active'));
    document.getElementById('createOrganizationForm').addEventListener('submit', async event => {
        event.preventDefault();
        const button = event.currentTarget.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            await apiRequest('/organisms', {
                method: 'POST',
                body: JSON.stringify({
                    name: document.getElementById('organizationName').value.trim(),
                    identity: document.getElementById('organizationIdentity').value.trim()
                })
            });
            event.currentTarget.reset();
            showToast('Organismo creado. Ya eres su dueño.');
            await Promise.all([loadOrganizations(), loadOrganizationRequests()]);
        } catch (error) {
            showToast(error.message);
        } finally {
            button.disabled = false;
        }
    });
    document.getElementById('joinOrganizationForm').addEventListener('submit', async event => {
        event.preventDefault();
        const button = event.currentTarget.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            await apiRequest('/organisms/join', {
                method: 'POST',
                body: JSON.stringify({ identity: document.getElementById('joinOrganizationIdentity').value.trim() })
            });
            event.currentTarget.reset();
            showToast('Solicitud enviada. El dueño debe aprobarla.');
        } catch (error) {
            showToast(error.message);
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('messagesBtn')?.addEventListener('click', showMessages);
    document.getElementById('notificationBadge')?.addEventListener('click', showNotifPanel);

    document.getElementById('closeConvPanel')?.addEventListener('click', showFeed);
    document.getElementById('closeNotifPanel')?.addEventListener('click', showFeed);

    document.getElementById('newOfferBtn').addEventListener('click', () => {
        window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'ofertas/crear' : 'crear_oferta.html';
    });

    document.getElementById('profileBtn').addEventListener('click', () => {
        if ((localStorage.getItem('userType') || '') === 'admin') return;
        window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'perfil' : 'perfil';
    });

    document.getElementById('profileSidebarBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        if ((localStorage.getItem('userType') || '') === 'admin') return;
        window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'perfil' : 'perfil';
    });

    document.getElementById('helpBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('helpModal')?.classList.add('active');
    });

    document.getElementById('closeHelpModal')?.addEventListener('click', () => {
        document.getElementById('helpModal')?.classList.remove('active');
    });

    document.getElementById('logoutBtn').addEventListener('click', function(e) {
        e.preventDefault();
        apiRequest('/auth/logout', { method: 'POST', body: JSON.stringify({}) })
            .finally(() => {
                localStorage.clear();
                window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'login' : 'loginhex.html';
            });
    });

    function applySearchFilter(showFeedback = false) {
        const input = document.getElementById('searchInput');
        if (!input) return;
        const normalize = (v) => String(v || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        const query = normalize(input.value.trim());

        if (!query) {
            if (currentView === 'feed') renderOfertas(currentFilter);
            else if (currentView === 'saved') renderOfertas('saved');
            else if (currentView === 'mine' && (localStorage.getItem('userType') || 'estudiante') !== 'estudiante') renderOfertas('mine');
            return;
        }

        const results = ofertasData.filter(o =>
            normalize(o.title).includes(query) ||
            normalize(o.description).includes(query) ||
            normalize(o.publisher.name).includes(query) ||
            o.tags.some(t => normalize(t).includes(query))
        );

        const feedContainer = document.getElementById('feedContainer');
        const oldEmpty = document.getElementById('searchEmptyState');
        if (oldEmpty) oldEmpty.remove();

        const allCards = document.querySelectorAll('.offer-card');
        allCards.forEach(card => {
            const id = parseInt(card.dataset.id, 10);
            const match = results.some(r => r.id === id);
            card.style.display = match ? '' : 'none';
        });

        if (results.length > 0) {
            if (showFeedback) showToast(`Encontradas ${results.length} ofertas para "${input.value.trim()}"`);
        } else {
            if (showFeedback) showToast(`No se encontraron ofertas para "${input.value.trim()}"`);
            if (feedContainer) {
                const empty = document.createElement('div');
                empty.id = 'searchEmptyState';
                empty.style.cssText = 'text-align:center;padding:50px 20px;color:#8ba0b8;background:white;border-radius:28px;border:1px solid #d6e5f5;';
                empty.innerHTML = `<i class="fas fa-search" style="font-size:36px;color:#d6e5f5;display:block;margin-bottom:12px;"></i><h4 style="color:#2c5a7a;">No se encontraron ofertas para "${escapeHtml(input.value.trim())}"</h4><p style="font-size:13px;">Intenta con otra palabra clave o borra la búsqueda.</p>`;
                feedContainer.appendChild(empty);
            }
        }
    }

    const searchInputEl = document.getElementById('searchInput');
    if (searchInputEl) {
        searchInputEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applySearchFilter(true);
            }
        });
        searchInputEl.addEventListener('input', function() {
            if (this.value.trim() === '') {
                applySearchFilter(false);
            }
        });
    }

    document.querySelectorAll('#convTabs button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#convTabs button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentConvTab = this.dataset.tab;
            renderConversations();
        });
    });

    document.getElementById('convSearchInput').addEventListener('input', function() {
        convSearchQuery = this.value;
        renderConversations();
    });

    setInterval(() => {
        if (currentView === 'feed') loadOffersFromApi().catch(error => showToast(error.message));
        else if (currentView === 'messages') loadConversationsFromApi().catch(error => showToast(error.message));
        else if (currentView === 'notifications') loadNotificationsFromApi().catch(error => showToast(error.message));
        else if (currentView === 'saved') loadOffersFromApi('saved').catch(error => showToast(error.message));
        else if (currentView === 'mine') loadOffersFromApi('mine').catch(error => showToast(error.message));
    }, 60000);

    async function init() {
        loadProfile();
        updateNotifCount();
        updateMessagesBadge();

        try {
            const result = await apiRequest('/auth/me');
            saveCurrentUser(result.user);
            loadProfile();
            const rol = result.user.rol;
            const newOfferBtn = document.getElementById('newOfferBtn');
            const savedBtn = document.getElementById('savedSidebarBtn');
            const myOffersBtn = document.getElementById('myOffersSidebarBtn');
            const uniStudentsBtn = document.getElementById('universityStudentsSidebarBtn');
            const conveniosBtn = document.getElementById('conveniosSidebarBtn');
            const adminBtn = document.getElementById('adminSidebarBtn');

            if (rol === 'estudiante') {
                if (newOfferBtn) newOfferBtn.style.display = 'none';
                if (savedBtn) savedBtn.style.display = 'flex';
                if (myOffersBtn) {
                    myOffersBtn.style.display = 'flex';
                    const sp = myOffersBtn.querySelector('span');
                    if (sp) sp.textContent = 'Mis Postulaciones';
                }
                if (conveniosBtn) conveniosBtn.style.display = 'flex';
            } else if (rol === 'participante_empresa') {
                if (newOfferBtn) newOfferBtn.style.display = 'inline-flex';
                if (savedBtn) savedBtn.style.display = 'none';
                if (myOffersBtn) myOffersBtn.style.display = 'flex';
                if (conveniosBtn) conveniosBtn.style.display = 'flex';
            } else if (rol === 'participante_universidad') {
                if (newOfferBtn) newOfferBtn.style.display = 'none';
                if (savedBtn) savedBtn.style.display = 'none';
                if (myOffersBtn) myOffersBtn.style.display = 'none';
                if (uniStudentsBtn) uniStudentsBtn.style.display = 'flex';
                if (conveniosBtn) conveniosBtn.style.display = 'flex';
            } else if (rol === 'admin') {
                if (newOfferBtn) newOfferBtn.style.display = 'none';
                if (savedBtn) savedBtn.style.display = 'none';
                if (myOffersBtn) myOffersBtn.style.display = 'none';
                if (adminBtn) adminBtn.style.display = 'flex';
                if (conveniosBtn) conveniosBtn.style.display = 'flex';
                const msgBtnSide = document.getElementById('messagesSidebarBtn');
                if (msgBtnSide) msgBtnSide.style.display = 'none';
                const profBtnSide = document.getElementById('profileSidebarBtn');
                if (profBtnSide) profBtnSide.style.display = 'none';
                document.querySelectorAll('.sidebar-menu .menu-label').forEach(lbl => {
                    if (lbl.textContent.trim().toLowerCase() === 'gestión') lbl.style.display = 'none';
                });
                const topProfile = document.getElementById('profileBtn');
                if (topProfile) { topProfile.style.cursor = 'default'; topProfile.title = 'Administrador'; }
            }

            const initTasks = [
                loadOffersFromApi(),
                loadNotificationsFromApi()
            ];
            if (rol !== 'admin') {
                initTasks.push(loadConversationsFromApi());
            }
            await Promise.all(initTasks);
        } catch (error) {
            if (error.status === 401) {
                window.location.href = typeof BASE_URL !== 'undefined' ? BASE_URL + 'login' : 'login';
                return;
            }
            ofertasData = [];
            renderOfertas('all');
            showToast(error.message || 'No se pudo cargar el contenido.');
        }

        const returnTo = localStorage.getItem('returnTo');
        if (returnTo) {
            localStorage.removeItem('returnTo');
            setTimeout(() => {
                if (returnTo === 'mine') document.getElementById('myOffersSidebarBtn')?.click();
                else if (returnTo === 'saved') document.getElementById('savedSidebarBtn')?.click();
                else if (returnTo === 'notif') document.getElementById('notifSidebarBtn')?.click();
                else if (returnTo === 'messages') document.getElementById('messagesSidebarBtn')?.click();
                else if (returnTo === 'university_students') document.getElementById('universityStudentsSidebarBtn')?.click();
                else if (returnTo === 'convenios') document.getElementById('conveniosSidebarBtn')?.click();
                else if (returnTo === 'admin') document.getElementById('adminSidebarBtn')?.click();
                else if (returnTo === 'help') document.getElementById('helpBtn')?.click();
            }, 120);
        }

        setTimeout(() => { document.body.style.opacity = '1'; }, 120);

        console.log('Dashboard Ineova listo');
        console.log('userId actual:', getUserId());
    }

    init();

})();
