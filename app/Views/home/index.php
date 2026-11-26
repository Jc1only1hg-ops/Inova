<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Ineova conecta a estudiantes universitarios becarios con empresas y universidades mediante trabajos evaluados por créditos académicos.">
    <meta name="keywords" content="becarios, créditos académicos, pasantías, empleos universitarios, Venezuela, estudiantes, empresas, Ineova">
    <meta name="author" content="Ineova CSN">
    <title>Ineova | Contratación de becarios universitarios por créditos académicos</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>">
    
    <!-- Fuente Inter de Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    <!-- Iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Nuestros estilos personalizados -->
    <link rel="stylesheet" href="<?= base_url('css/index.css') ?>">
</head>
<body>
<!-- Encabezado de la página -->
<header>
    <div class="container">
        <div class="navbar">
            <div class="logo-wrapper">
                <a href="<?= base_url('/') ?>" class="logo-linkedin" style="padding:0; background:transparent; border-radius:14px; overflow:hidden;">
                    <img src="<?= base_url('img/ineova-logo.svg') ?>" alt="Ineova" style="width:100%; height:100%; display:block;">
                </a>
                <div class="logo-text">Ine<span>ova</span></div>
            </div>
            <div class="nav-links">
                <a href="<?= base_url('/') ?>#inicio">Inicio</a>
                <a href="<?= base_url('sobre') ?>">Sobre nosotros</a>
                <a href="<?= base_url('/') ?>#contacto">Contacto</a>
                <!-- Botón INGRESAR: redirige al login -->
                <a href="<?= base_url('login') ?>" class="btn-outline-blue">Ingresar</a>
                <!-- Botón REGÍSTRATE: redirige al login con hash para mostrar registro -->
                <a href="<?= base_url('login#register') ?>" class="btn-solid-blue">Regístrate</a>
            </div>
        </div>
    </div>
</header>

<main>
    <!-- Sección principal (hero) - primer impacto visual -->
    <section class="hero" id="inicio">
        <div class="container hero-grid">
            <div class="hero-text">
                <h1>Conectamos <span class="highlight">becarios universitarios</span> con empresas por créditos académicos</h1>
                <p>Las empresas publican proyectos reales y contratan estudiantes calificados acreditando <strong>créditos académicos</strong> en tu perfil universitario.</p>
                <a href="<?= base_url('login') ?>" class="btn-primary-blue"><i class="fas fa-arrow-right"></i> Explorar oportunidades</a>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=2070&auto=format&fit=crop" alt="Estudiantes universitarios colaborando">
            </div>
        </div>
    </section>

    <!-- Sección de información: qué es Ineova y su razón de ser -->
    <section class="info-section">
        <div class="container">
            <div class="cards-grid">
                <!-- Tarjeta: ¿Qué es Ineova? -->
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>¿Qué es Ineova?</h3>
                    <p>Ineova es la <strong>comunidad digital de contratación de becarios</strong> donde empresas buscan estudiantes universitarios calificados para proyectos y trabajos evaluados por <strong>créditos académicos</strong>.</p>
                    <ul class="blue-list">
                        <li><i class="fas fa-check-circle"></i> Ofertas de becarios con créditos académicos</li>
                        <li><i class="fas fa-check-circle"></i> Perfiles enlazados entre estudiantes y empresas</li>
                        <li><i class="fas fa-check-circle"></i> 100% gratis para estudiantes</li>
                    </ul>
                </div>
                <!-- Tarjeta: Razón de ser -->
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Razón de ser</h3>
                    <p>Creemos que el <strong>talento joven universitario merece oportunidades claras, seguras y medibles</strong>. Impulsamos la experiencia práctica antes de egresar, vinculando el esfuerzo laboral con créditos académicos respaldados por universidades y empresas.</p>
                    <ul class="blue-list">
                        <li><i class="fas fa-chart-line"></i> Reducir el desempleo juvenil</li>
                        <li><i class="fas fa-hand-holding-heart"></i> Apoyar la primera experiencia laboral</li>
                        <li><i class="fas fa-globe-americas"></i> Crecer el tejido empresarial desde la base</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de reseñas con carrusel automático -->
    <section class="reviews-section">
        <div class="container">
            <h2 class="section-title">Lo que dicen de nosotros</h2>
            <p class="section-subtitle">Opiniones de estudiantes, empresas y universidades que ya confían en Ineova</p>
            <div class="reviews-grid" id="reviewsGrid"></div>
            <div class="carousel-dots-original" id="carouselDotsOriginal"></div>
        </div>
    </section>

    <!-- Estadísticas con contexto para generar confianza (FOMO) -->
    <div class="container">
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">+1.200</div>
                <div class="stat-label">Estudiantes registrados</div>
                <div class="stat-context">en los últimos 6 meses</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">85+</div>
                <div class="stat-label">Empresas aliadas</div>
                <div class="stat-context">activas en Venezuela</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">4.9/5</div>
                <div class="stat-label">Satisfacción</div>
                <div class="stat-context">basado en 150 reseñas</div>
            </div>
        </div>
    </div>

    <!-- Llamado a la acción específico para universidades -->
    <div class="container">
        <div class="university-cta">
            <h3>¿Eres una universidad?</h3>
            <p>Aliados estratégicos. Recibe datos, estadísticas y sugerencias para mejorar tus planes de estudio. <strong>Únete gratis</strong> y forma parte del cambio.</p>
            <a href="<?= base_url('login#register') ?>" class="btn-cta">Solicitar alianza →</a>
        </div>
    </div>
</main>

<!-- Pie de página -->
<footer id="contacto">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo">Ine<span style="color:#6ab0f5;">ova</span></div>
            <div class="footer-contact">
                <a href="<?= base_url('sobre') ?>" class="contact-item"><i class="fas fa-map-marker-alt"></i> Sobre nosotros</a>
                <a href="mailto:contacto@ineova.com" class="contact-item"><i class="fas fa-envelope"></i> contacto@ineova.com</a>
                <a href="tel:+584241942272" class="contact-item"><i class="fas fa-phone"></i> +58 424 194 2272</a>
            </div>
        </div>
        <div class="copyright">
            © 2026 Ineova CSN — La primera comunidad digital que conecta estudiantes, universidades y empresas en Venezuela. Talento joven, oportunidades reales.
        </div>
    </div>
</footer>

<script>
    window.BASE_URL = "<?= base_url() ?>";
    window.API_BASE = "<?= base_url('api/v1') ?>";
</script>
<script src="<?= base_url('js/index.js') ?>"></script>
</body>
</html>
