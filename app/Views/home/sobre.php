<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Sobre Ineova: misión, visión y funcionamiento de la plataforma que conecta estudiantes universitarios becarios con empresas por créditos académicos.">
    <meta name="keywords" content="Ineova, sobre nosotros, becarios, créditos académicos, pasantías, universidades, empresas, Venezuela">
    <meta name="author" content="Ineova CSN">
    <title>Sobre Nosotros | Ineova</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>">

    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/index.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/sobre.css') ?>">
</head>

<body>
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
                    <a href="<?= base_url('/') ?>">Inicio</a>
                    <a href="<?= base_url('sobre') ?>" class="active">Sobre nosotros</a>
                    <a href="<?= base_url('/') ?>#contacto">Contacto</a>
                    <a href="<?= base_url('login') ?>" class="btn-outline-blue">Ingresar</a>
                    <a href="<?= base_url('login#register') ?>" class="btn-solid-blue">Regístrate</a>
                </div>
            </div>
        </div>
    </header>

    <main class="about-page">
        <!-- Hero Sobre Nosotros -->
        <section class="about-hero">
            <div class="container about-hero-grid">
                <div class="about-hero-content">
                    <span class="about-pill"><i class="fas fa-award"></i> Programa de Becarios por créditos académicos</span>
                    <h1>Impulsamos el talento universitario con <span class="highlight">experiencia real</span></h1>
                    <p>
                        En <strong>Ineova</strong> conectamos a estudiantes universitarios calificados con empresas y universidades aliadas. Las organizaciones publican trabajos y proyectos de becarios evaluados por <strong>créditos académicos</strong>, permitiendo que los estudiantes ganen experiencia práctica verificable mientras avanzan en su formación académica.
                    </p>
                    <div class="about-hero-actions">
                        <a href="<?= base_url('login#register') ?>" class="btn-primary-blue">
                            <i class="fas fa-user-plus"></i> Crear cuenta gratis
                        </a>
                        <a href="<?= base_url('login') ?>" class="btn-outline-blue">
                            <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                        </a>
                    </div>
                </div>
                <div class="about-stats-card">
                    <div class="about-stat">
                        <div class="about-stat-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <strong>Estudiantes Calificados</strong>
                            <span>Perfiles con habilidades técnicas, portafolio y puntos acumulados</span>
                        </div>
                    </div>
                    <div class="about-stat">
                        <div class="about-stat-icon"><i class="fas fa-building"></i></div>
                        <div>
                            <strong>Empresas Aliadas</strong>
                            <span>Publican proyectos reales para contratar becarios por créditos académicos</span>
                        </div>
                    </div>
                    <div class="about-stat">
                        <div class="about-stat-icon"><i class="fas fa-university"></i></div>
                        <div>
                            <strong>Universidades</strong>
                            <span>Respaldan la vinculación académica y el reconocimiento crediticio</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Misión, Visión y Valores -->
        <section class="about-mvv">
            <div class="container">
                <h2 class="section-title">Nuestra Identidad</h2>
                <p class="section-subtitle">Construimos el puente entre las aulas universitarias y el sector productivo</p>

                <div class="mvv-grid">
                    <article class="mvv-card">
                        <div class="mvv-icon"><i class="fas fa-bullseye"></i></div>
                        <h3>Misión</h3>
                        <p>
                            Facilitar la contratación de becarios universitarios mediante un ecosistema transparente basado en créditos académicos, conectando el potencial académico con retos reales de empresas.
                        </p>
                    </article>

                    <article class="mvv-card">
                        <div class="mvv-icon"><i class="fas fa-eye"></i></div>
                        <h3>Visión</h3>
                        <p>
                            Ser la plataforma de referencia en Venezuela y Latinoamérica para la formación práctica universitaria, donde cada estudiante egrese con experiencia real y respaldo profesional.
                        </p>
                    </article>

                    <article class="mvv-card">
                        <div class="mvv-icon"><i class="fas fa-handshake"></i></div>
                        <h3>Valores</h3>
                        <ul class="mvv-list">
                            <li><i class="fas fa-check-circle"></i> Transparencia y mérito académico</li>
                            <li><i class="fas fa-check-circle"></i> Oportunidades accesibles sin barreras</li>
                            <li><i class="fas fa-check-circle"></i> Colaboración entre universidad y empresa</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <!-- Cómo funciona -->
        <section class="about-steps">
            <div class="container">
                <h2 class="section-title">¿Cómo funciona el sistema de créditos académicos?</h2>
                <p class="section-subtitle">Un flujo simple y directo para estudiantes, empresas y universidades</p>

                <div class="steps-grid">
                    <div class="step-card">
                        <span class="step-number">01</span>
                        <h3>Crea tu perfil</h3>
                        <p>Regístrate como estudiante, empresa o universidad. Los estudiantes detallan su casa de estudios, habilidades y GitHub; las organizaciones registran su RIF o siglas.</p>
                    </div>
                    <div class="step-card">
                        <span class="step-number">02</span>
                        <h3>Publica o postúlate</h3>
                        <p>Las empresas publican vacantes de becarios indicando los <strong>créditos académicos</strong> que otorga cada trabajo y las habilidades requeridas.</p>
                    </div>
                    <div class="step-card">
                        <span class="step-number">03</span>
                        <h3>Conecta y contrata</h3>
                        <p>Las empresas revisan los perfiles de los estudiantes postulados, chatean directamente desde la plataforma y seleccionan al becario ideal.</p>
                    </div>
                    <div class="step-card">
                        <span class="step-number">04</span>
                        <h3>Acredita tus puntos</h3>
                        <p>Al ser aceptado y completar el trabajo asignado, los créditos académicos se suman automáticamente al perfil del estudiante.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="about-cta-section">
            <div class="container">
                <div class="about-cta-box">
                    <div>
                        <h2>¿Listo para formar parte de Ineova?</h2>
                        <p>Únete hoy y conecta talento universitario con proyectos que generan impacto y créditos académicos.</p>
                    </div>
                    <a href="<?= base_url('login#register') ?>" class="btn-cta">
                        Comenzar ahora <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer id="contacto">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">Ine<span style="color:#6ab0f5;">ova</span></div>
                <div class="footer-contact">
                    <a href="<?= base_url('sobre') ?>" class="contact-item"><i class="fas fa-map-marker-alt"></i> Sobre nosotros</a>
                    <a href="mailto:contacto@ineova.com" class="contact-item"><i class="fas fa-envelope"></i> contacto@ineova.com</a>
                    <a href="tel:+582121234567" class="contact-item"><i class="fas fa-phone"></i> +58 212 123 4567</a>
                </div>
            </div>
            <div class="copyright">
                © 2026 Ineova CSN — Plataforma de contratación de becarios por créditos académicos en Venezuela.
            </div>
        </div>
    </footer>

    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.API_BASE = "<?= base_url('api/v1') ?>";
    </script>
    <script src="<?= base_url('js/sobre.js') ?>"></script>
</body>
</html>
