<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
    <link rel="stylesheet" href="<?= base_url('css/loginhex.css') ?>" />
    <title>Acceder | Ineova</title>
  </head>

  <body>
    <div class="container" id="container">
      <div class="form-container sign-up">
        <form>
          <h1>Crear cuenta</h1>
          <span>o usa tu correo para registrarte</span>
          <input type="text" placeholder="Nombre" />
          <input type="email" placeholder="Correo electrónico" />
          <input type="password" placeholder="Contraseña" />
          <button>Registrarse</button>
        </form>
      </div>
      <div class="form-container sign-in">
        <form>
          <h1>Acceder</h1>
          <span>Coloca tu correo y contraseña</span>
          <input type="email" placeholder="Correo electrónico" />
          <input type="password" placeholder="Contraseña" />
          <a href="#">¿Olvidaste tu contraseña?</a>
          <button>Acceder</button>
        </form>
      </div>
      <div class="toggle-container">
        <div class="toggle">
          <div class="toggle-panel toggle-left">
            <h1>¡Bienvenido de nuevo!</h1>
            <p>Ingresa tus datos para usar todas las funciones del sitio</p>
            <button class="hidden" id="login">Iniciar sesión</button>
          </div>
          <div class="toggle-panel toggle-right">
            <h1>¡Hola, amigo!</h1>
            <p>
              Regístrate con tus datos para usar todas las funciones del sitio
            </p>
            <button class="hidden" id="register">Registrarse</button>
          </div>
        </div>
      </div>
    </div>

    <script src="<?= base_url('scripts/loginhex.js') ?>"></script>
  </body>
</html>