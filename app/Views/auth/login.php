<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="<?= base_url('css/loginhex.css') ?>" />
    <title>Acceder | Ineova</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>" />
</head>

<body>
    <div style="position: absolute; top: 20px; left: 20px; z-index: 999;">
        <a href="<?= base_url('/') ?>" style="color: #0a66c2; text-decoration: none; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.9); padding: 8px 14px; border-radius: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
            <i class="fas fa-arrow-left"></i> Volver al inicio
        </a>
    </div>

    <div class="container" id="container">
        <!-- ===== FORMULARIO DE REGISTRO (PASOS) ===== -->
        <div class="form-container sign-up">
            <form id="registerForm" novalidate>
                <h1>Crear cuenta</h1>
                <span>¡Registrarse es fácil y gratis!</span>

                <!-- PASO 1 -->
                <div id="stepOne" style="width:100%;">
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nombre" placeholder="Nombre" required />
                    </div>

                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="apellido" placeholder="Apellido" required />
                    </div>

                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Correo electrónico" required />
                    </div>

                    <div class="input-group custom-block-wrapper" id="accountTypeWrapper">
                        <div class="custom-block-select" id="fusionSelect">
                            <div class="custom-block-trigger">
                                <i class="fas fa-chevron-down custom-block-arrow"></i>
                                <span class="custom-block-placeholder">Selecciona tipo de cuenta</span>
                            </div>
                            <div class="custom-block-options">
                                <span class="custom-block-option" data-value="estudiante">
                                    <i class="fas fa-graduation-cap"></i> Estudiante
                                </span>
                                <span class="custom-block-option" data-value="participante_universidad">
                                    <i class="fas fa-user-check"></i> Participante de universidad
                                </span>
                                <span class="custom-block-option" data-value="participante_empresa">
                                    <i class="fas fa-user-check"></i> Participante de empresa
                                </span>
                            </div>
                        </div>
                        <input type="hidden" name="userType" id="fusionHiddenInput" required />
                    </div>

                    <button type="button" class="btn btn-full" id="nextStepBtn">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- PASO 2 -->
                <div id="stepTwo" style="display:none; width:100%;">
                    <!-- Panel Estudiante -->
                    <div class="password-panel" id="panelEstudiante">
                        <div class="panel-title">
                            <i class="fas fa-graduation-cap"></i> Estudiante
                        </div>
                        <div class="input-group">
                            <i class="fas fa-university"></i>
                            <select name="universidad_id_estudiante" id="studentUniversitySelect" style="width:100%; height:44px; padding:0 14px 0 42px; border:none; border-radius:8px; background:#eee; font-size:13px; color:#333;">
                                <option value="">Universidad (opcional, puedes elegirla después)</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="telefono_estudiante" placeholder="Teléfono (ej: 0412-1234567)" />
                            <div class="validation-message">Formato: 0412-1234567</div>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="cedula_estudiante" placeholder="Cédula (ej: V-12345678)" />
                            <div class="validation-message">Formato: V-12345678 o E-12345678</div>
                        </div>
                        <div class="panel-divider">Contraseña</div>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password_estudiante" placeholder="Contraseña" minlength="6" />
                        </div>
                        <div class="input-group">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" name="confirmPassword_estudiante" placeholder="Confirmar contraseña" minlength="6" />
                        </div>
                    </div>

                    <!-- Panel Universidad -->
                    <div class="password-panel" id="panelUniversidad">
                        <div class="panel-title">
                            <i class="fas fa-university"></i> Coordinador de universidad
                        </div>
                        <div class="input-group">
                            <i class="fas fa-university"></i>
                            <select name="universidad_id_universidad" id="coordUniversitySelect" style="width:100%; height:44px; padding:0 14px 0 42px; border:none; border-radius:8px; background:#eee; font-size:13px; color:#333;">
                                <option value="">Selecciona la universidad que representas...</option>
                            </select>
                        </div>
                        <div style="font-size:11px; color:#64748b; text-align:left; margin: -4px 0 8px 2px;">
                            <i class="fas fa-shield-alt" style="color:#0A66C2;"></i> Tu solicitud será aprobada por un administrador de Ineova para evitar suplantaciones.
                        </div>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="telefono_universidad" placeholder="Teléfono (ej: 0412-1234567)" />
                            <div class="validation-message">Formato: 0412-1234567</div>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="cedula_universidad" placeholder="Cédula (ej: V-12345678)" />
                            <div class="validation-message">Formato: V-12345678 o E-12345678</div>
                        </div>
                        <div class="panel-divider">Contraseña</div>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password_universidad" placeholder="Contraseña" minlength="6" />
                        </div>
                        <div class="input-group">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" name="confirmPassword_universidad" placeholder="Confirmar contraseña" minlength="6" />
                        </div>
                    </div>

                    <!-- Panel Empresa -->
                    <div class="password-panel" id="panelEmpresa">
                        <div class="panel-title">
                            <i class="fas fa-user-check"></i> Participante de empresa
                        </div>
                        <div class="input-group">
                            <i class="fas fa-building"></i>
                            <input type="text" name="organismo_nombre_empresa" placeholder="Nombre de la empresa" />
                        </div>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="organismo_identidad_empresa" placeholder="RIF (ej: J-1234567890)" maxlength="14" />
                            <div class="validation-message">Máximo 10 dígitos numéricos (ej: J-1234567890)</div>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="telefono_empresa" placeholder="Teléfono (ej: 0412-1234567)" />
                            <div class="validation-message">Formato: 0412-1234567</div>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="cedula_empresa" placeholder="Cédula (ej: V-12345678)" />
                            <div class="validation-message">Formato: V-12345678 o E-12345678</div>
                        </div>
                        <div class="panel-divider">Contraseña</div>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password_empresa" placeholder="Contraseña" minlength="6" />
                        </div>
                        <div class="input-group">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" name="confirmPassword_empresa" placeholder="Confirmar contraseña" minlength="6" />
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" id="backStepBtn">
                            <i class="fas fa-arrow-left"></i> Atrás
                        </button>
                        <button type="submit" class="btn" id="registerSubmitBtn">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </button>
                    </div>
                </div>

                <div id="formMessage" class="message"></div>
            </form>
        </div>

        <!-- ===== FORMULARIO DE LOGIN ===== -->
        <div class="form-container sign-in">
            <form id="loginForm" novalidate>
                <h1>Acceder</h1>
                <span>Coloca tu correo y contraseña</span>

                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Correo electrónico" required />
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required minlength="6" />
                </div>

                <a href="#" id="forgotPassword">¿Olvidaste tu contraseña?</a>
                <button type="submit" id="loginSubmitBtn">Acceder</button>

                <!-- ===== PANEL DE RECUPERAR CONTRASEÑA ===== -->
                <div class="forgot-password-panel" id="forgotPasswordPanel">
                    <div class="panel-title">
                        <i class="fas fa-key"></i> Recuperar contraseña
                    </div>
                    <div class="panel-subtitle">
                        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                    </div>

                    <div class="input-group" id="recoveryEmailGroup">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="recoveryEmail" placeholder="Correo electrónico registrado" />
                        <div class="validation-message" id="recoveryEmailMsg">Ingresa tu correo registrado</div>
                    </div>

                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" id="backToLoginBtn">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                        <button type="button" class="btn" id="sendRecoveryBtn">
                            <i class="fas fa-paper-plane"></i> Enviar enlace
                        </button>
                    </div>

                    <div id="recoveryMessage" class="message"></div>

                    <!-- ===== ESTADO "CORREO ENVIADO" ===== -->
                    <div class="email-sent-state" id="emailSentState">
                        <div class="sent-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <h3>Revisa tu correo</h3>
                        <p>Te hemos enviado un enlace para restablecer tu contraseña a:</p>
                        <div class="email-shown" id="sentEmailShown">correo@ejemplo.com</div>

                        <div class="hint">
                            <i class="fas fa-info-circle"></i>
                            Si no ves el correo, revisa tu carpeta de <strong>spam</strong> o <strong>correo no deseado</strong>.
                            El enlace expirará en 30 minutos.
                        </div>
                        <button type="button" class="btn btn-full" id="backToLoginFromSentBtn" style="margin-top: 16px;">
                            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== TOGGLE ===== -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>¡Bienvenido de nuevo!</h1>
                    <p>Ingresa tus datos para usar todas las funciones del sitio</p>
                    <button class="hidden" id="login">Iniciar sesión</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>¡Hola, amigo!</h1>
                    <p>Regístrate con tus datos para usar todas las funciones del sitio</p>
                    <button class="hidden" id="register">Registrarse</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.API_BASE = "<?= base_url('api/v1') ?>";
    </script>
    <script src="<?= base_url('js/loginhex.js') ?>"></script>
</body>
</html>
