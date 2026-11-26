<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Ineova - Plataforma de ofertas laborales para estudiantes, universidades y empresas" />
    <title>Ineova · Ofertas Laborales</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>" />
</head>
<body style="opacity:0; transition:opacity 0.15s ease;">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-wrapper">
                <a href="<?= base_url('dashboard') ?>" class="logo-ineova" style="padding:0; background:transparent; border-radius:14px; overflow:hidden;"><img src="<?= base_url('img/ineova-logo.svg') ?>" alt="Ineova" style="width:100%; height:100%; display:block;" /></a>
                <span class="logo-text">Ine<span>ova</span></span>
                <button class="toggle-btn" id="toggleSidebarBtn" aria-label="Toggle sidebar">
                    <i class="fas fa-chevron-left" id="toggleIcon"></i>
                </button>
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Principal</div>
            <a href="<?= base_url('dashboard') ?>" class="active" id="feedSidebarBtn"><i class="fas fa-home"></i><span>Inicio</span></a>
            <a href="#" id="notifSidebarBtn"><i class="fas fa-bell"></i><span>Notificaciones</span></a>
            <a href="#" id="messagesSidebarBtn">
                <i class="fas fa-comments"></i>
                <span>Conversaciones</span>
                <span class="sidebar-badge" id="sidebarMsgBadge" style="display:none;">0</span>
            </a>
            <a href="#" id="savedSidebarBtn" style="display:none;"><i class="fas fa-bookmark"></i><span>Ofertas Guardadas</span></a>
            <a href="#" id="myOffersSidebarBtn" style="display:none;"><i class="fas fa-briefcase"></i><span>Mis Ofertas</span></a>
            <a href="#" id="universityStudentsSidebarBtn" style="display:none;"><i class="fas fa-user-graduate"></i><span>Estudiantes</span></a>
            <a href="#" id="conveniosSidebarBtn" style="display:none;"><i class="fas fa-handshake"></i><span>Convenios</span></a>
            <a href="#" id="adminSidebarBtn" style="display:none;"><i class="fas fa-shield-alt"></i><span>Panel Admin</span></a>

            <div class="menu-label">Gestión</div>
            <a href="<?= base_url('perfil') ?>" id="profileSidebarBtn"><i class="fas fa-user"></i><span>Mi Perfil</span></a>
        </nav>

        <div class="sidebar-footer">
            <a href="#" id="helpBtn"><i class="fas fa-question-circle"></i><span>Ayuda</span></a>
            <a href="<?= base_url('login') ?>" id="logoutBtn"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content" id="mainContent">

        <!-- ===== HEADER ===== -->
        <div class="dashboard-header">
            <div class="header-left">
                <h1 id="pageTitle">Ofertas</h1>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar ofertas, empresas..." id="searchInput" />
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-primary" id="newOfferBtn" style="display:none;">
                    <i class="fas fa-plus"></i> Publicar Oferta
                </button>
                <div class="user-profile" id="profileBtn">
                    <span class="user-name" id="userNameDisplay">Usuario</span>
                    <div class="user-avatar">
                        <img src="" alt="Foto de perfil" id="avatarImg" style="display:none;" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== FILTROS ===== -->
        <div class="filters-bar" id="filtersBar">
            <button class="filter-btn active" data-filter="all">Todas</button>
            <button class="filter-btn" data-filter="remoto">Remoto</button>
            <button class="filter-btn" data-filter="presencial">Presencial</button>
            <button class="filter-btn" data-filter="hibrido">Híbrido</button>
        </div>

        <!-- ===== NOTIFICACIONES ===== -->
        <div class="notif-panel" id="notifPanel">
            <div class="notif-header">
                <h3><i class="fas fa-bell"></i> Notificaciones</h3>
                <div class="notif-actions">
                    <button id="markAllNotif"><i class="fas fa-check-double"></i> Marcar todas</button>
                    <button class="danger" id="clearAllNotif"><i class="fas fa-trash"></i> Limpiar</button>
                </div>
            </div>
            <div class="notif-search">
                <input type="text" id="notifSearchInput" placeholder="Buscar notificaciones..." />
            </div>
            <div class="notif-filters" id="notifFilters">
                <button class="active" data-filter="all">Todas</button>
                <button data-filter="unread">No leídas</button>
                <button data-filter="read">Leídas</button>
            </div>
            <div class="notif-list" id="notifList"></div>
        </div>

        <!-- ===== CONVERSACIONES ===== -->
        <div class="conversations-panel" id="conversationsPanel">
            <div class="conv-header">
                <h3><i class="fas fa-comments"></i> Conversaciones</h3>
                <div class="conv-header-actions"></div>
            </div>

            <div class="conv-search">
                <input type="text" id="convSearchInput" placeholder="Buscar por nombre, mensaje o empresa..." />
            </div>

            <div class="conv-tabs" id="convTabs">
                <button class="active" data-tab="all"><i class="fas fa-list"></i> Todas</button>
                <button data-tab="unread"><i class="fas fa-envelope"></i> No leídas</button>
                <button data-tab="archived"><i class="fas fa-archive"></i> Archivadas</button>
                <button data-tab="blocked"><i class="fas fa-ban"></i> Bloqueadas</button>
            </div>

            <div class="conv-list" id="convList"></div>
        </div>

        <!-- ===== CHAT PANEL ===== -->
        <div class="chat-panel" id="chatPanel">
            <div class="chat-header" id="chatHeader"></div>
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input-area">
                <input type="text" id="chatInput" placeholder="Escribe un mensaje..." />
                <button class="btn-send" id="chatSendBtn" title="Enviar mensaje">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <!-- ===== FEED DE OFERTAS ===== -->
        <div class="feed-container" id="feedContainer"></div>

        <div class="footer-note">
            <i class="fas fa-circle"></i> Todos los datos actualizados · Ineova v2.0
        </div>
    </div>

    <!-- ===== MODAL CONTACTO ===== -->
    <div class="modal-overlay" id="contactModal">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-comments"></i> <span id="contactModalTitle">Chatear</span></h2>
                <button class="modal-close" id="closeContactModal"><i class="fas fa-times"></i></button>
            </div>
            <form id="contactForm">
                <div class="form-group">
                    <label for="contactTo"><i class="fas fa-user"></i> Para</label>
                    <input type="text" id="contactTo" readonly />
                </div>
                <div class="form-group" id="contactContextGroup" style="display:none;">
                    <label><i class="fas fa-briefcase"></i> Sobre la oferta</label>
                    <input type="text" id="contactContext" readonly />
                </div>
                <div class="form-group" id="recommendStudentGroup" style="display:none;">
                    <label for="recommendStudentSelect"><i class="fas fa-user-graduate"></i> Estudiante a recomendar</label>
                    <select id="recommendStudentSelect" style="width:100%; padding:10px 12px; border:1.5px solid #d6e5f5; border-radius:12px; font-size:14px; background:#fff;">
                        <option value="">Selecciona un estudiante de tu universidad...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contactSubject"><i class="fas fa-tag"></i> Asunto</label>
                    <input type="text" id="contactSubject" placeholder="Asunto del mensaje..." />
                </div>
                <div class="form-group">
                    <label for="contactBody"><i class="fas fa-align-left"></i> Mensaje</label>
                    <textarea id="contactBody" placeholder="Escribe tu mensaje..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelContactBtn">Cancelar</button>
                    <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL NUEVO MENSAJE ===== -->
    <div class="modal-overlay" id="newMsgModal">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-pen"></i> Nuevo Mensaje</h2>
                <button class="modal-close" id="closeNewMsgModal"><i class="fas fa-times"></i></button>
            </div>
            <form id="newMsgForm">
                <div class="form-group">
                    <label for="newMsgTo"><i class="fas fa-user"></i> Para</label>
                    <select id="newMsgTo" required>
                        <option value="">Selecciona un destinatario...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="newMsgSubject"><i class="fas fa-tag"></i> Asunto</label>
                    <input type="text" id="newMsgSubject" placeholder="Breve descripción..." />
                </div>
                <div class="form-group">
                    <label for="newMsgBody"><i class="fas fa-align-left"></i> Mensaje</label>
                    <textarea id="newMsgBody" placeholder="Escribe tu mensaje..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelNewMsgBtn">Cancelar</button>
                    <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== GESTIÓN DE ORGANISMOS ===== -->
    <div class="modal-overlay" id="organizationModal">
        <div class="modal organization-modal">
            <div class="modal-header">
                <h2><i class="fas fa-building"></i> Organismos</h2>
                <button class="modal-close" id="closeOrganizationModal" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            </div>
            <div class="organization-mode-switch" role="tablist" aria-label="Acción de organismo">
                <button type="button" class="active" id="createOrganizationMode" role="tab" aria-selected="true">Crear</button>
                <button type="button" id="joinOrganizationMode" role="tab" aria-selected="false">Solicitar ingreso</button>
            </div>
            <form id="createOrganizationForm">
                <div class="form-group">
                    <label for="organizationName"><i class="fas fa-building"></i> Nombre</label>
                    <input type="text" id="organizationName" maxlength="150" required />
                </div>
                <div class="form-group" id="organizationIdentityGroup">
                    <label for="organizationIdentity" id="organizationIdentityLabel"><i class="fas fa-id-card"></i> Identificación</label>
                    <input type="text" id="organizationIdentity" maxlength="20" required />
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-send">Crear organismo</button>
                </div>
            </form>
            <form id="joinOrganizationForm" hidden>
                <div class="form-group">
                    <label for="joinOrganizationIdentity" id="joinOrganizationIdentityLabel"><i class="fas fa-id-card"></i> RIF o siglas</label>
                    <input type="text" id="joinOrganizationIdentity" maxlength="20" required />
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-send">Enviar solicitud</button>
                </div>
            </form>
            <section class="organization-requests" id="organizationRequests" hidden>
                <h3>Solicitudes pendientes</h3>
                <div id="organizationRequestList"></div>
            </section>
            <section class="organization-memberships">
                <h3>Mis organismos</h3>
                <div id="organizationList" aria-live="polite">Cargando...</div>
            </section>
        </div>
    </div>

    <!-- ===== MODAL POSTULANTES ===== -->
    <div class="modal-overlay" id="applicantsModal">
        <div class="modal" style="max-width: 620px;">
            <div class="modal-header">
                <h2><i class="fas fa-users"></i> <span id="applicantsModalTitle">Postulantes</span></h2>
                <button class="modal-close" id="closeApplicantsModal"><i class="fas fa-times"></i></button>
            </div>
            <div id="applicantsList" style="max-height: 60vh; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
                Cargando postulantes...
            </div>
        </div>
    </div>

    <!-- ===== MODAL AYUDA ===== -->
    <div class="modal-overlay" id="helpModal">
        <div class="modal" style="max-width: 540px;">
            <div class="modal-header">
                <h2><i class="fas fa-question-circle"></i> Centro de Ayuda · Ineova</h2>
                <button class="modal-close" id="closeHelpModal"><i class="fas fa-times"></i></button>
            </div>
            <div style="display: flex; flex-direction: column; gap: 14px; font-size: 14px; line-height: 1.65; color: #334155;">
                <p><strong>Ineova</strong> conecta a estudiantes universitarios becarios con empresas verificadas y universidades aliadas para impulsar la formación práctica con <strong>créditos académicos</strong>.</p>
                <div style="background:#f4f8ff; padding:14px; border-radius:12px; border:1px solid #dbeafe;">
                    <strong style="color:#0a66c2;"><i class="fas fa-graduation-cap"></i> Para Estudiantes</strong>
                    <p style="margin-top:4px;">Completa tu perfil con tu universidad, GitHub, constancia de notas y habilidades. Postúlate a ofertas de becarios; cuando una empresa acepte tu postulación, se habilitará el chat de coordinación.</p>
                </div>
                <div style="background:#f4f8ff; padding:14px; border-radius:12px; border:1px solid #dbeafe;">
                    <strong style="color:#0a66c2;"><i class="fas fa-building"></i> Para Empresas y Universidades</strong>
                    <p style="margin-top:4px;">Las empresas verificadas publican vacantes y seleccionan becarios. Las universidades validan la vigencia y notas de sus estudiantes y establecen convenios de créditos académicos.</p>
                </div>
                <p style="font-size:13px; color:#64748b;">¿Necesitas soporte adicional? Escríbenos a <a href="mailto:contacto@ineova.com" style="color:#0a66c2; font-weight:600;">contacto@ineova.com</a>.</p>
            </div>
        </div>
    </div>

    <!-- ===== LIGHTBOX ===== -->
    <div class="lightbox" id="lightbox">
        <button class="close-lightbox" id="closeLightbox"><i class="fas fa-times"></i></button>
        <img src="" alt="Imagen ampliada" id="lightboxImg" />
    </div>

    <!-- ===== TOAST ===== -->
    <div class="toast" id="toast"></div>

    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.API_BASE = "<?= base_url('api/v1') ?>";
    </script>
    <script src="<?= base_url('js/dashboard.js') ?>"></script>
</body>
</html>
