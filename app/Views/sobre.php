<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Sobre Ineova: misión, visión y valores de la comunidad digital que conecta estudiantes universitarios con empresas en Venezuela.">
    <meta name="keywords" content="Ineova, sobre nosotros, Universidad, pasantías, empleos, Venezuela, talento joven">
    <meta name="author" content="Ineova CSN">
    <title>Sobre Ineova | Comunidad digital de talento universitario</title>

    <!-- Fuente y estilos compartidos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/sobre.css">
</head>

<body>
    <header>
        <div class="container">
            <div class="navbar">
                <div class="logo-wrapper">
                    <!-- De aquí en adelante se aplican los punteros de dirección de páginas -->
                    <a href="<?= base_url() ?>" class="logo-linkedin">
                        <span>Ine</span>
                    </a>
                    <div class="logo-text">Ine<span>ova</span></div>
                </div>
                <div class="nav-links">
                    <a href="index.html">Inicio</a>
                    <a href="<?= base_url('sobre') ?>">Sobre nosotros</a>
                    <a href="#contacto">Contacto</a>
                    <a href="<?= base_url('login') ?>" class="btn-outline-blue">Ingresar</a>
                    <a href="<?= base_url('login#register') ?>" class="btn-solid-blue">Regístrate</a>
                    <!-- Aquí se cierra el contenedor de enlaces -->
                </div>
            </div>
        </div>
    </header>

    <footer>
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
                © 2025 Ineova CSN — Conectando talento universitario con oportunidades reales en Venezuela.
            </div>
        </div>
    </footer>

    <script src="scripts/sobre.js"></script>
</body>
</html>