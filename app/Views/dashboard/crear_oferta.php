<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Ineova - Publica una nueva oferta laboral" />
    <title>Publicar Oferta · Ineova</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= base_url('css/crear_oferta.css') ?>" />
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
            <a href="<?= base_url('dashboard') ?>" id="feedSidebarBtn"><i class="fas fa-home"></i><span>Inicio</span></a>
            <a href="<?= base_url('dashboard') ?>" id="notifSidebarBtn">
                <i class="fas fa-bell"></i><span>Notificaciones</span>
                <span class="sidebar-badge" id="sidebarNotifBadge" style="display:none;">0</span>
            </a>
            <a href="<?= base_url('dashboard') ?>" id="messagesSidebarBtn">
                <i class="fas fa-comments"></i>
                <span>Conversaciones</span>
                <span class="sidebar-badge" id="sidebarMsgBadge" style="display:none;">0</span>
            </a>
            <a href="<?= base_url('dashboard') ?>" id="myOffersSidebarBtn"><i class="fas fa-briefcase"></i><span>Mis Ofertas</span></a>
            <a href="<?= base_url('dashboard') ?>" id="conveniosSidebarBtn"><i class="fas fa-handshake"></i><span>Convenios</span></a>

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
                <h1 id="pageTitle">Publicar Oferta</h1>
            </div>
            <div class="header-actions">
                <div class="user-profile" id="profileBtn">
                    <span class="user-name" id="userNameDisplay">Usuario Ineova</span>
                    <div class="user-avatar">
                        <img src="" alt="Foto de perfil" id="avatarImg" style="display:none;" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== CARD PRINCIPAL ===== -->
        <div class="create-offer-card">

            <div class="create-offer-header">
                <h2>
                    <i class="fas fa-briefcase"></i>
                    <span id="cardTitle">Nueva Oferta Laboral</span>
                </h2>
                <p id="cardSubtitle">Completa los datos para publicar tu oferta</p>
            </div>

            <!-- ===== SELECTOR DE ORGANISMO (solo participantes) ===== -->
            <div class="organism-card" id="organismCard" style="display: none;">
                <div class="org-header">
                    <i class="fas fa-building"></i>
                    <label>Publicar como</label>
                </div>
                <select id="organismSelect"></select>
                <div class="org-meta" id="organismMeta"></div>
            </div>

            <!-- ===== AVISO PARA NO EMPRESAS ===== -->
            <div class="organism-card" id="studentWarning" style="display: none; background: linear-gradient(135deg, #fff8e6, #fff3d6); border-color: #f5d78e;">
                <div class="org-header">
                    <i class="fas fa-exclamation-triangle" style="color: #b26e1a;"></i>
                    <label style="color: #8a5410;">Solo empresas pueden publicar ofertas</label>
                </div>
                <p style="font-size: 13px; color: #8a5410; line-height: 1.5; margin-top: 4px;">
                    Únicamente las cuentas de <strong>empresa</strong> pueden publicar vacantes para becarios.
                </p>
            </div>

            <!-- Preview del publicador -->
            <div class="publisher-preview">
                <div class="avatar-wrapper">
                    <div class="avatar" id="publisherAvatar"></div>
                </div>
                <div class="info">
                    <div class="name" id="publisherName">Cargando...</div>
                    <div class="type" id="publisherType">Cargando...</div>
                </div>
            </div>

            <!-- Formulario -->
            <form id="offerForm">
                <input type="hidden" id="offerId" />

                <div class="form-group">
                    <label for="offerTitle">
                        <i class="fas fa-briefcase"></i> Título del puesto
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="offerTitle" placeholder="Ej: Desarrollador Full Stack Senior" required />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="offerModality">
                            <i class="fas fa-map-marker-alt"></i> Modalidad
                            <span class="required">*</span>
                        </label>
                        <select id="offerModality" required>
                            <option value="">Selecciona...</option>
                            <option value="remoto">Remoto</option>
                            <option value="hibrido">Híbrido</option>
                            <option value="presencial">Presencial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="offerContract">
                            <i class="fas fa-clock"></i> Tipo de contrato
                            <span class="required">*</span>
                        </label>
                        <select id="offerContract" required>
                            <option value="">Selecciona...</option>
                            <option value="tiempo_completo">Tiempo completo</option>
                            <option value="medio_tiempo">Medio tiempo</option>
                            <option value="pasantia">Pasantía</option>
                            <option value="freelance">Freelance</option>
                            <option value="temporal">Temporal</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="offerExperience">
                            <i class="fas fa-chart-line"></i> Nivel de experiencia
                        </label>
                        <select id="offerExperience">
                            <option value="sin_experiencia">Sin experiencia</option>
                            <option value="junior">Junior</option>
                            <option value="semi_senior">Semi-Senior</option>
                            <option value="senior">Senior</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="offerVacancies">
                            <i class="fas fa-users"></i> Vacantes
                        </label>
                        <input type="number" id="offerVacancies" placeholder="1" min="1" max="99" value="1" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="offerLocation">
                        <i class="fas fa-map-pin"></i> Ubicación
                        <span class="required">*</span>
                    </label>
                    <input type="text" id="offerLocation" placeholder="Ej: Caracas, Venezuela" required />
                </div>

                <div class="form-group">
                    <label for="offerExpiration">
                        <i class="fas fa-calendar-alt"></i> Fecha de expiración
                        <span class="optional-tag">Opcional</span>
                    </label>
                    <input type="date" id="offerExpiration" />
                    <div class="field-hint">
                        <i class="fas fa-info-circle"></i> Si no la defines, la oferta no expira automáticamente
                    </div>
                </div>

                <div class="form-group">
                    <label for="offerDescription">
                        <i class="fas fa-align-left"></i> Descripción
                        <span class="required">*</span>
                    </label>
                    <textarea id="offerDescription" placeholder="Describe el puesto, responsabilidades, requisitos y beneficios..." required></textarea>
                    <div class="field-hint">
                        <i class="fas fa-info-circle"></i> Una buena descripción recibe 3x más aplicaciones
                    </div>
                </div>

                <div class="form-group">
                    <label for="offerTags">
                        <i class="fas fa-tags"></i> Etiquetas / Habilidades
                    </label>
                    <input type="text" id="offerTags" placeholder="Ej: JavaScript, React, Node.js" />
                    <div class="field-hint">
                        <i class="fas fa-info-circle"></i> Separa cada etiqueta con una coma. Se vincularán a la tabla <code>habilidades</code>
                    </div>
                </div>

                <!-- Imágenes -->
                <div class="form-group images-section">
                    <label>
                        <i class="fas fa-image"></i> Imágenes
                        <span class="optional-tag">Opcional</span>
                    </label>

                    <div class="image-uploader">
                        <label class="drop-zone" id="dropZone">
                            <div class="drop-zone-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div class="main-text">Haz clic o arrastra imágenes aquí</div>
                                <div class="sub-text">JPG, PNG o GIF · Máximo 3 imágenes · 5MB cada una</div>
                            </div>
                            <input type="file" id="fileInput" accept="image/*" multiple />
                        </label>

                        <div class="image-previews" id="imagePreviews"></div>

                        <div class="images-hint" id="imagesHint">
                            <i class="fas fa-info-circle"></i>
                            Puedes subir hasta 3 imágenes. Se guardarán como URLs en <code>oferta_imagenes</code>.
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-save" id="saveBtn">
                        <i class="fas fa-paper-plane"></i> Publicar Oferta
                    </button>
                </div>
            </form>
        </div>

        <!-- ===== TIPS ===== -->
        <div class="tips-card">
            <h3><i class="fas fa-lightbulb"></i> Consejos para una buena oferta</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Sé específico en el título del puesto</li>
                <li><i class="fas fa-check-circle"></i> Indica claramente si es remoto, híbrido o presencial</li>
                <li><i class="fas fa-check-circle"></i> Describe las responsabilidades principales</li>
                <li><i class="fas fa-check-circle"></i> Agrega imágenes de tu equipo o ambiente laboral</li>
                <li><i class="fas fa-check-circle"></i> Usa etiquetas para que te encuentren más fácil</li>
            </ul>
        </div>

        <!-- ===== FOOTER ===== -->
        <div class="footer-note">
            <i class="fas fa-circle"></i> Todos los datos actualizados · Ineova v2.0
        </div>
    </div>

    <!-- ===== TOAST ===== -->
    <div class="toast" id="toast"></div>

    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.API_BASE = "<?= base_url('api/v1') ?>";
    </script>
    <script src="<?= base_url('js/crear_oferta.js') ?>"></script>
</body>
</html>
