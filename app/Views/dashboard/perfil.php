<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Ineova - Mi Perfil" />
    <title>Ineova · Mi Perfil</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        /* ============================================================
           INEOVA · MI PERFIL (v5.2)
           Alineado con:
             · DB v5.1: usuarios.avatar / empresas.logo / perfil_estudiantes
             · Dashboard: claves localStorage (ofertasGuardadas, userOffers,
               ineova_conversations, ineova_messages)
             · Roles: estudiante | empresa (participante_empresa)
           ============================================================ */

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f2f6fe;
            display: flex;
            min-height: 100vh;
            color: #0b253b;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 280px;
            background: #0e1d34;
            color: #b4c9e6;
            display: flex;
            flex-direction: column;
            padding: 28px 20px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            flex-shrink: 0;
            transition: width 0.3s ease, padding 0.3s ease;
            z-index: 100;
            border-right: 1px solid #1d3150;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.03);
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .sidebar::-webkit-scrollbar { display: none; }

        .sidebar.collapsed { width: 80px; padding: 28px 12px; }
.sidebar.collapsed .sidebar-brand-wrapper { flex-direction: column; align-items: center; gap: 10px; }
.sidebar.collapsed .toggle-btn { margin-left: 0; }

        .sidebar.collapsed .sidebar-brand .logo-text,
        .sidebar.collapsed .sidebar-menu a span,
        .sidebar.collapsed .sidebar-footer a span,
        .sidebar.collapsed .menu-label { display: none; }
        .sidebar.collapsed .sidebar-menu a,
        .sidebar.collapsed .sidebar-footer a { justify-content: center; padding: 14px 0; }
        .sidebar.collapsed .sidebar-menu a i,
        .sidebar.collapsed .sidebar-footer a i { font-size: 22px; width: auto; }
        .sidebar.collapsed .logo-ineova { width: 44px; height: 44px; font-size: 18px; padding: 6px; }
        .sidebar.collapsed .sidebar-menu a .sidebar-badge { display: none !important; }

        .sidebar-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; color: white; }
        .sidebar-brand-wrapper { display: flex; align-items: center; width: 100%; gap: 10px; }

        .logo-ineova {
            display: flex; align-items: center; justify-content: center;
            width: 48px; height: 48px; background: #0A66C2;
            border-radius: 10% 30% 50% 70%; text-decoration: none;
            font-weight: 900; font-size: 20px; color: #fff; letter-spacing: -0.5px;
            transition: transform 0.2s ease, border-radius 0.3s ease;
            flex-shrink: 0; padding: 6px; line-height: 1;
        }
        .logo-ineova span { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; font-size: inherit; }
        .logo-ineova:hover { transform: scale(1.05); border-radius: 70% 50% 30% 10%; }

        .sidebar-brand .logo-text { font-size: 22px; font-weight: 800; color: white; letter-spacing: -0.5px; white-space: nowrap; }
        .sidebar-brand .logo-text span { color: #6ab0f5; }

        .toggle-btn {
            background: transparent; border: none; color: #8aa3c4;
            font-size: 18px; cursor: pointer; padding: 6px 10px;
            border-radius: 10px; transition: 0.2s; margin-left: auto;
            flex-shrink: 0; position: relative; z-index: 10;
            min-width: 36px; min-height: 36px;
            display: flex; align-items: center; justify-content: center;
        }
        .toggle-btn:hover { background: #1d3150; color: white; }

        .sidebar-menu { display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .sidebar-menu .menu-label {
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
            color: #5f78a0; margin-top: 16px; margin-bottom: 6px;
            font-weight: 600; white-space: nowrap;
        }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 14px;
            padding: 11px 16px; border-radius: 14px; color: #b4c9e6;
            text-decoration: none; font-weight: 500; font-size: 14px;
            transition: 0.15s ease; white-space: nowrap;
            position: relative;
        }
        .sidebar-menu a i { width: 22px; font-size: 17px; text-align: center; flex-shrink: 0; }
        .sidebar-menu a:hover { background: #1d3150; color: white; }
        .sidebar-menu a.active {
            background: #1a6dff; color: white;
            box-shadow: 0 6px 14px rgba(26, 109, 255, 0.25);
        }
        .sidebar-menu a.active i { color: white; }

        .sidebar-menu a .sidebar-badge {
            position: absolute; top: 6px; right: 10px;
            background: #e74c3c; color: white;
            font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 20px;
            min-width: 18px; text-align: center;
            line-height: 1.2; pointer-events: none;
        }
        .sidebar-menu a.active .sidebar-badge { background: white; color: #1a6dff; }

        .sidebar-footer { margin-top: 30px; border-top: 1px solid #1d3150; padding-top: 20px; }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 14px;
            padding: 10px 16px; border-radius: 14px; color: #b4c9e6;
            text-decoration: none; font-weight: 500; font-size: 14px;
            transition: 0.15s; white-space: nowrap;
        }
        .sidebar-footer a:hover { background: #1d3150; color: white; }
        .sidebar-footer a i { width: 22px; text-align: center; flex-shrink: 0; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1; padding: 28px 36px;
            max-width: calc(100% - 280px);
            transition: max-width 0.3s ease, padding 0.3s ease;
            overflow-y: auto; overflow-x: hidden;
            max-height: 100vh;
            scrollbar-width: thin;
            scrollbar-color: #d6e5f5 transparent;
        }
        .main-content::-webkit-scrollbar { width: 8px; }
        .main-content::-webkit-scrollbar-track { background: transparent; }
        .main-content::-webkit-scrollbar-thumb { background: #d6e5f5; border-radius: 4px; }
        .sidebar.collapsed ~ .main-content { max-width: calc(100% - 80px); }

        /* ===== HEADER ===== */
        .dashboard-header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; margin-bottom: 28px; gap: 12px;
        }
        .header-left { display: flex; align-items: center; gap: 16px; flex: 1; min-width: 200px; }
        .header-left h1 {
            font-weight: 700; font-size: 24px; color: #0b253b;
            letter-spacing: -0.3px; white-space: nowrap;
        }
        .header-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

        .header-btn {
            background: white; border: 1px solid #d6e5f5;
            padding: 10px 14px; border-radius: 40px;
            font-size: 14px; font-weight: 500; color: #1a4d8c;
            cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; gap: 8px;
            position: relative;
        }
        .header-btn:hover { border-color: #1a6dff; background: #f6faff; }
        .header-btn i { color: #1a6dff; font-size: 16px; }
        .header-btn .badge {
            background: #1a6dff; color: white; font-size: 10px;
            font-weight: 700; padding: 2px 8px; border-radius: 20px;
            min-width: 18px; text-align: center;
        }
        .header-btn .badge.messages-badge { background: #e74c3c; }

        .user-profile {
            display: flex; align-items: center; gap: 10px;
            background: white; padding: 5px 14px 5px 18px;
            border-radius: 50px; border: 1.5px solid #d6e5f5;
            cursor: pointer; transition: all 0.2s ease;
        }
        .user-profile:hover { border-color: #1a6dff; box-shadow: 0 4px 16px rgba(26, 109, 255, 0.08); }
        .user-profile .user-name { font-weight: 600; font-size: 14px; color: #0b253b; white-space: nowrap; }
        .user-profile .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; overflow: hidden; border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            background: linear-gradient(135deg, #1a6dff, #6ab0ff);
            position: relative;
        }
        .user-profile .user-avatar img {
            width: 100%; height: 100%; object-fit: cover;
            border-radius: 50%; display: block;
        }
        .user-profile .user-avatar > span {
            width: 100%; height: 100%; display: flex;
            align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 14px;
        }

        /* ===== PAGE CONTENT ===== */
        .page-content { min-height: 300px; }

        /* ============================================================
           PERFIL
           ============================================================ */
        .profile-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
            align-items: start;
        }

        .profile-card {
            background: white; border-radius: 28px;
            border: 1px solid #d6e5f5; padding: 32px 24px;
            text-align: center; position: sticky; top: 20px;
            box-shadow: 0 4px 20px rgba(11, 37, 59, 0.04);
        }

        .profile-avatar-wrapper {
            position: relative; width: 130px; height: 130px;
            margin: 0 auto 20px;
        }

        .profile-avatar {
            width: 130px; height: 130px; border-radius: 50%;
            background: linear-gradient(135deg, #1a6dff, #6ab0ff);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; border: 4px solid #f2f6fe;
            box-shadow: 0 8px 24px rgba(26, 109, 255, 0.15);
            position: relative;
        }
        .profile-avatar.company {
            border-radius: 22px;
            background: linear-gradient(135deg, #0b7e3d, #4CAF50);
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .profile-avatar > span {
            color: white; font-weight: 800; font-size: 48px;
            letter-spacing: -1px;
        }

        .avatar-edit-btn {
            position: absolute; bottom: 6px; right: 6px;
            width: 38px; height: 38px; border-radius: 50%;
            background: #1a6dff; color: white; border: 3px solid white;
            cursor: pointer; display: flex; align-items: center;
            justify-content: center; font-size: 15px;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(26, 109, 255, 0.3);
        }
        .avatar-edit-btn:hover { background: #0b5ad9; transform: scale(1.08); }

        .profile-name {
            font-size: 22px; font-weight: 800; color: #0b253b;
            margin-bottom: 4px; letter-spacing: -0.3px;
        }
        .profile-role {
            font-size: 13px; color: #1a6dff; font-weight: 600;
            background: #e8f1ff; display: inline-block;
            padding: 4px 14px; border-radius: 50px;
            margin-bottom: 16px; text-transform: capitalize;
        }
        .profile-email {
            font-size: 13px; color: #8ba0b8;
            margin-bottom: 20px; word-break: break-all;
        }
        .profile-email i { margin-right: 6px; color: #1a6dff; }

        .profile-divider { height: 1px; background: #e6edf6; margin: 20px 0; }

        .profile-stats { display: flex; justify-content: space-around; margin-bottom: 20px; }
        .stat-item { flex: 1; cursor: pointer; transition: 0.2s; }
        .stat-item:hover { transform: translateY(-2px); }
        .stat-value {
            font-size: 22px; font-weight: 800; color: #0b253b;
            line-height: 1;
        }
        .stat-label {
            font-size: 11px; color: #8ba0b8;
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-top: 6px; font-weight: 600;
        }

        .profile-actions {
            display: flex; flex-direction: column;
            gap: 10px; margin-top: 20px;
        }

        /* --- Formulario --- */
        .profile-form-card {
            background: white; border-radius: 28px;
            border: 1px solid #d6e5f5; padding: 32px;
            box-shadow: 0 4px 20px rgba(11, 37, 59, 0.04);
        }

        .form-section-title {
            font-size: 16px; font-weight: 700; color: #0b253b;
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 2px solid #f2f6fe;
            display: flex; align-items: center; gap: 10px;
        }
        .form-section-title i {
            color: #1a6dff; font-size: 17px;
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            background: #e8f1ff; border-radius: 10px;
        }

        .form-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 18px;
        }
        .form-grid .full { grid-column: 1 / -1; }

        .form-group { display: flex; flex-direction: column; gap: 7px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #2c5a7a; }
        .form-group label .req { color: #e53e3e; margin-left: 2px; }

        .form-control {
            padding: 12px 16px;
            border: 1.5px solid #d6e5f5; border-radius: 14px;
            font-size: 14px; font-family: inherit;
            color: #0b253b; background: #fafcff;
            transition: 0.2s; outline: none; width: 100%;
        }
        .form-control:focus {
            border-color: #1a6dff; background: white;
            box-shadow: 0 0 0 4px rgba(26, 109, 255, 0.08);
        }
        .form-control:disabled {
            background: #f2f6fe; color: #8ba0b8;
            cursor: not-allowed;
        }
        .form-control::placeholder { color: #b0c0d4; }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3e%3cpath fill='%238ba0b8' d='M6 8L2 4h8z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
            cursor: pointer;
        }

        textarea.form-control {
            resize: vertical; min-height: 110px; line-height: 1.6;
        }

        .char-counter {
            font-size: 11px; color: #8ba0b8;
            text-align: right; margin-top: 2px;
        }

        /* --- Botones --- */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; padding: 12px 22px; border-radius: 14px;
            font-size: 14px; font-weight: 600; font-family: inherit;
            cursor: pointer; border: none; transition: 0.2s ease;
            text-decoration: none; white-space: nowrap;
        }
        .btn:active { transform: scale(0.97); }

        .btn-primary {
            background: #1a6dff; color: white;
            box-shadow: 0 6px 16px rgba(26, 109, 255, 0.25);
        }
        .btn-primary:hover {
            background: #0b5ad9;
            box-shadow: 0 8px 20px rgba(26, 109, 255, 0.35);
        }

        .btn-outline {
            background: white; color: #1a6dff;
            border: 1.5px solid #d6e5f5;
        }
        .btn-outline:hover { border-color: #1a6dff; background: #f5f9ff; }

        .btn-danger-outline {
            background: white; color: #e53e3e;
            border: 1.5px solid #fbd5d5;
        }
        .btn-danger-outline:hover { background: #fff5f5; border-color: #e53e3e; }

        .btn-block { width: 100%; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .form-actions {
            display: flex; justify-content: flex-end; gap: 12px;
            margin-top: 28px; padding-top: 24px;
            border-top: 2px solid #f2f6fe;
        }

        .verified-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 700;
            color: #0b7e3d; background: #e6f7ee;
            padding: 4px 12px; border-radius: 50px;
            margin-top: 8px;
        }
        .verified-badge i { font-size: 12px; }

        .skills-container {
            display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;
        }
        .skill-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: #e8f1ff; color: #1a6dff;
            padding: 6px 12px; border-radius: 50px;
            font-size: 13px; font-weight: 600;
        }
        .skill-chip i {
            cursor: pointer; font-size: 11px;
            color: #6a9eff; transition: 0.2s;
        }
        .skill-chip i:hover { color: #e53e3e; }

        .skill-input-wrapper { display: flex; gap: 8px; margin-top: 10px; }
        .skill-input-wrapper .form-control { flex: 1; }
        .skill-input-wrapper .btn { padding: 12px 16px; }

        .save-status {
            font-size: 12px; color: #0b7e3d; font-weight: 600;
            display: none; align-items: center; gap: 6px;
            margin-right: auto;
        }
        .save-status.show { display: flex; }

        /* --- Campos exclusivos por rol --- */
        .student-only, .company-only, .university-only { display: none; }
        body[data-role="estudiante"] .student-only { display: flex; }
        body[data-role="empresa"] .company-only,
        body[data-role="universidad"] .company-only { display: flex; }
        body[data-role="universidad"] .university-only { display: flex; }

        /* --- Insignia de puntos crediticios --- */
        .credit-points-badge {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 10px; padding: 6px 14px; border-radius: 999px;
            background: linear-gradient(135deg, #fff7e6, #ffe8b3);
            color: #9a5b00; font-size: 13px; font-weight: 700;
            border: 1px solid #f5d280;
        }
        .credit-points-badge i { color: #d97706; }

        /* --- Modales de seguridad (contraseña / eliminar cuenta) --- */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(11, 37, 59, 0.55);
            display: none; align-items: center; justify-content: center;
            z-index: 2500; padding: 20px; backdrop-filter: blur(3px);
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #ffffff; border-radius: 20px; width: 100%; max-width: 440px;
            padding: 26px; box-shadow: 0 20px 50px rgba(11, 37, 59, 0.25);
        }
        .modal-box-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 18px;
        }
        .modal-box-header h3 { font-size: 18px; color: #0b253b; display: flex; align-items: center; gap: 8px; }
        .modal-close-btn {
            background: #f2f6fe; border: none; width: 34px; height: 34px; border-radius: 50%;
            cursor: pointer; color: #5f78a0; font-size: 15px;
        }
        .modal-close-btn:hover { background: #e3edfc; color: #0b253b; }
        .modal-box .form-group { margin-bottom: 14px; }
        .modal-box .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .btn-danger-solid {
            background: #c53e3e; color: white; border: none; padding: 11px 20px;
            border-radius: 12px; font-weight: 600; cursor: pointer;
        }
        .btn-danger-solid:hover { background: #a82e2e; }

        /* ===== FOOTER ===== */
        .footer-note {
            text-align: center; margin-top: 36px;
            font-size: 13px; color: #8ba0b8;
            border-top: 1px solid #e6edf6; padding-top: 22px;
        }
        .footer-note i { color: #0b7e3d; font-size: 8px; margin-right: 6px; }

        /* ===== TOAST ===== */
        .toast {
            position: fixed; bottom: 30px; right: 30px;
            background: #0b253b; color: white;
            padding: 16px 28px; border-radius: 20px;
            font-size: 14px; font-weight: 500;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(100px); opacity: 0;
            transition: 0.3s ease; z-index: 3000;
            max-width: 360px;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { background: #0b7e3d; }
        .toast.error { background: #c53e3e; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .profile-grid { grid-template-columns: 1fr; }
            .profile-card { position: static; }
        }

        @media (max-width: 1024px) {
            .sidebar { width: 80px; padding: 28px 12px; }
            .sidebar .sidebar-brand .logo-text,
            .sidebar .sidebar-menu a span,
            .sidebar .sidebar-footer a span,
            .sidebar .menu-label { display: none; }
            .sidebar .sidebar-menu a,
            .sidebar .sidebar-footer a { justify-content: center; padding: 14px 0; }
            .sidebar .sidebar-menu a i,
            .sidebar .sidebar-footer a i { font-size: 22px; width: auto; }
            .sidebar .sidebar-menu a .sidebar-badge { display: none !important; }
            .main-content { max-width: calc(100% - 80px); padding: 20px; }
            .sidebar.collapsed { width: 60px; padding: 20px 8px; }
.sidebar.collapsed .sidebar-brand-wrapper { flex-direction: column; align-items: center; gap: 10px; }
.sidebar.collapsed .toggle-btn { margin-left: 0; }

            .sidebar.collapsed ~ .main-content { max-width: calc(100% - 60px); }
            .user-profile .user-name { display: none; }
            .user-profile { padding: 5px 10px; }
            .header-left h1 { font-size: 20px; }
        }

        @media (max-width: 768px) {
            .sidebar { width: 60px; padding: 16px 8px; }
            .main-content { max-width: calc(100% - 60px); padding: 16px; }
            .dashboard-header { flex-direction: column; align-items: stretch; gap: 12px; }
            .header-left { flex-wrap: wrap; }
            .header-actions { justify-content: flex-start; }
            .sidebar.collapsed { width: 50px; padding: 12px 6px; }
.sidebar.collapsed .sidebar-brand-wrapper { flex-direction: column; align-items: center; gap: 10px; }
.sidebar.collapsed .toggle-btn { margin-left: 0; }

            .sidebar.collapsed ~ .main-content { max-width: calc(100% - 50px); }
            .user-profile .user-name { display: none; }
            .user-profile { padding: 5px 8px; }

            .form-grid { grid-template-columns: 1fr; }
            .profile-form-card { padding: 22px; }
            .profile-card { padding: 24px 18px; }
            .form-actions { flex-direction: column-reverse; }
            .form-actions .btn { width: 100%; }
        }

        @media (max-width: 500px) {
            .sidebar { width: 50px; padding: 12px 6px; }
            .main-content { max-width: calc(100% - 50px); padding: 12px; }
            .sidebar.collapsed { width: 40px; padding: 10px 4px; }
.sidebar.collapsed .sidebar-brand-wrapper { flex-direction: column; align-items: center; gap: 10px; }
.sidebar.collapsed .toggle-btn { margin-left: 0; }

            .sidebar.collapsed ~ .main-content { max-width: calc(100% - 40px); }
            .user-profile { padding: 5px; }
            .user-profile .user-avatar { width: 30px; height: 30px; }
        }
    </style>
</head>
<body data-role="estudiante" style="opacity:0; transition:opacity 0.15s ease;">

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
            <a href="<?= base_url('dashboard') ?>" id="feedSidebarBtn">
                <i class="fas fa-home"></i><span>Inicio</span>
            </a>
            <a href="#" id="notifSidebarBtn">
                <i class="fas fa-bell"></i><span>Notificaciones</span>
            </a>
            <a href="#" id="messagesSidebarBtn">
                <i class="fas fa-comments"></i>
                <span>Conversaciones</span>
                <span class="sidebar-badge" id="sidebarMsgBadge" style="display:none;">0</span>
            </a>
            <a href="#" id="savedSidebarBtn">
                <i class="fas fa-bookmark"></i><span>Ofertas Guardadas</span>
            </a>
            <a href="#" id="myOffersSidebarBtn">
                <i class="fas fa-briefcase"></i><span>Mis Ofertas</span>
            </a>
            <a href="#" id="universityStudentsSidebarBtn" style="display:none;">
                <i class="fas fa-user-graduate"></i><span>Estudiantes</span>
            </a>
            <a href="#" id="conveniosSidebarBtn" style="display:none;">
                <i class="fas fa-handshake"></i><span>Convenios</span>
            </a>
            <a href="#" id="adminSidebarBtn" style="display:none;">
                <i class="fas fa-shield-alt"></i><span>Panel Admin</span>
            </a>

            <div class="menu-label">Gestión</div>
            <a href="<?= base_url('perfil') ?>" id="profileSidebarBtn" class="active">
                <i class="fas fa-user"></i><span>Mi Perfil</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="#" id="helpBtn">
                <i class="fas fa-question-circle"></i><span>Ayuda</span>
            </a>
            <a href="#" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i><span>Salir</span>
            </a>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content" id="mainContent">

        <!-- ===== HEADER ===== -->
        <div class="dashboard-header">
            <div class="header-left">
                <h1 id="pageTitle">Mi Perfil</h1>
            </div>
            <div class="header-actions">
                <div class="user-profile" id="profileBtn">
                    <span class="user-name" id="userNameDisplay">Usuario</span>
                    <div class="user-avatar">
                        <img src="" alt="Foto de perfil" id="avatarImg" style="display:none;" />
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== CONTENIDO: MI PERFIL ===== -->
        <div class="page-content" id="pageContent">

            <div class="profile-grid">

                <!-- ===== TARJETA LATERAL ===== -->
                <div class="profile-card">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar" id="profileAvatar">
                            <span id="profileInitial">U</span>
                            <img src="" alt="Avatar" id="profileAvatarImg" style="display:none;" />
                        </div>
                        <button class="avatar-edit-btn" id="avatarEditBtn" title="Cambiar foto">
                            <i class="fas fa-camera"></i>
                        </button>
                        <input type="file" id="avatarFileInput" accept="image/*" style="display:none;" />
                    </div>

                    <h2 class="profile-name" id="profileNameDisplay">Usuario Ineova</h2>
                    <span class="profile-role" id="profileRoleDisplay">estudiante</span>
                    <p class="profile-email" id="profileEmailDisplay">
                        <i class="fas fa-envelope"></i> usuario@ineova.com
                    </p>

                    <div id="statusBadgesContainer" style="display:flex; flex-direction:column; align-items:center; gap:6px; margin-top:8px;"></div>

                    <div class="profile-divider"></div>

                    <div class="profile-stats" id="profileStatsContainer" style="visibility:hidden;"></div>

                    <div class="profile-divider"></div>

                    <div class="profile-actions" id="ownProfileActions">
                        <button class="btn btn-outline btn-block" id="changePasswordBtn">
                            <i class="fas fa-lock"></i> Cambiar contraseña
                        </button>
                        <button class="btn btn-danger-outline btn-block" id="deleteAccountBtn">
                            <i class="fas fa-trash-alt"></i> Eliminar cuenta
                        </button>
                    </div>
                    <div class="profile-actions" id="publicProfileActions" style="display:none;">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline btn-block" style="text-align:center; text-decoration:none;">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>

                <!-- ===== FORMULARIO ===== -->
                <div class="profile-form-card">
                    <!-- ===== PESTAÑAS EXCLUSIVAS DE EMPRESA (PERFIL / PLANES) ===== -->
                    <div id="companyProfileTabs" style="display:none; gap:10px; margin-bottom:24px; border-bottom:1.5px solid #e2ecf8; padding-bottom:14px;">
                        <button type="button" id="tabBtnProfileData" class="btn btn-primary" style="padding:9px 18px; font-size:13.5px;">
                            <i class="fas fa-building"></i> Datos de la empresa
                        </button>
                        <button type="button" id="tabBtnCompanyPlans" class="btn btn-outline" style="padding:9px 18px; font-size:13.5px;">
                            <i class="fas fa-crown"></i> Planes
                        </button>
                    </div>

                    <!-- ===== SECCIÓN PLANES (SOLO EMPRESAS) ===== -->
                    <div id="companyPlansSection" style="display:none;"></div>

                    <form id="profileForm" autocomplete="off">

                        <!-- ===== DATOS PERSONALES ===== -->
                        <div class="form-section-title">
                            <i class="fas fa-user"></i> <span id="formSectionTitle">Datos personales</span>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="inputNombre">Nombre <span class="req">*</span></label>
                                <input type="text" id="inputNombre" class="form-control"
                                       placeholder="Tu nombre" maxlength="100" required />
                            </div>
                            <div class="form-group">
                                <label for="inputApellido">Apellido <span class="req">*</span></label>
                                <input type="text" id="inputApellido" class="form-control"
                                       placeholder="Tu apellido" maxlength="100" required />
                            </div>

                            <!-- ===== CAMPOS EXCLUSIVOS DE ESTUDIANTE ===== -->
                            <div class="form-group student-only">
                                <label for="inputCedula">Cédula</label>
                                <input type="text" id="inputCedula" class="form-control"
                                       placeholder="V-12345678" maxlength="20" />
                            </div>
                            <div class="form-group student-only">
                                <label for="inputTelefono">Teléfono <span class="req">*</span></label>
                                <input type="tel" id="inputTelefono" class="form-control"
                                       placeholder="0412-1234567" maxlength="20" />
                            </div>

                            <!-- ===== CAMPOS EXCLUSIVOS DE EMPRESA / UNIVERSIDAD ===== -->
                            <div class="form-group full company-only">
                                <label for="inputOrgName" id="orgNameLabel">Nombre del organismo</label>
                                <input type="text" id="inputOrgName" class="form-control"
                                       placeholder="Nombre de la empresa o universidad" maxlength="150" />
                            </div>
                            <div class="form-group company-only">
                                <label for="inputRif" id="rifLabel">RIF</label>
                                <input type="text" id="inputRif" class="form-control"
                                       placeholder="J-12345678-9" maxlength="20" />
                            </div>
                            <div class="form-group university-only">
                                <label for="inputRankingUni">Ranking universitario</label>
                                <input type="number" id="inputRankingUni" class="form-control"
                                       placeholder="Ej: 1" min="1" max="9999" />
                            </div>
                            <div class="form-group company-only">
                                <label for="inputSitioWeb">Sitio web</label>
                                <input type="url" id="inputSitioWeb" class="form-control"
                                       placeholder="https://tuorganizacion.com" maxlength="255" />
                            </div>

                            <div class="form-group full">
                                <label for="inputCorreo">Correo electrónico</label>
                                <input type="email" id="inputCorreo" class="form-control"
                                       placeholder="tu@correo.com" disabled />
                                <span class="char-counter">El correo no se puede modificar</span>
                            </div>

                            <!-- Universidad (solo estudiante) -->
                            <div class="form-group full student-only">
                                <label for="inputUniversidad">Universidad</label>
                                <select id="inputUniversidad" class="form-control">
                                    <option value="">Selecciona tu universidad</option>
                                </select>
                                <div id="universityChangeNotice" style="display:none; margin-top:8px; font-size:12.5px; color:#516378; background:#f4f8ff; border:1px solid #dbeafe; border-radius:10px; padding:10px 12px;">
                                    <span id="universityChangeText">Tu universidad está bloqueada. Para cambiarla, solicita la habilitación a tu universidad actual.</span>
                                    <button type="button" id="requestUniChangeBtn" class="btn btn-outline" style="margin-top:8px; padding:6px 12px; font-size:12px;">
                                        <i class="fas fa-exchange-alt"></i> Solicitar cambio de universidad
                                    </button>
                                </div>
                            </div>

                            <!-- Constancia de notas (solo estudiante) -->
                            <div class="form-group full student-only" id="gradesDocGroup">
                                <label>Constancia de notas / Promedio académico</label>
                                <div id="gradesStatusInfo" style="font-size:13px; color:#334155; margin-bottom:8px;"></div>
                                <div id="gradesUploadBox" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <input type="file" id="gradesFileInput" accept=".pdf,image/jpeg,image/png,image/webp" style="display:none;" />
                                    <button type="button" class="btn btn-outline" id="uploadGradesBtn" style="padding:8px 14px; font-size:13px;">
                                        <i class="fas fa-file-upload"></i> Subir constancia de notas (PDF/Imagen)
                                    </button>
                                    <a href="#" id="viewGradesDocLink" target="_blank" style="display:none; font-size:13px; color:#0a66c2; font-weight:600; text-decoration:none;">
                                        <i class="fas fa-file-alt"></i> Ver documento subido
                                    </a>
                                </div>
                            </div>

                            <!-- Verificación de Empresa (solo empresa) -->
                            <div class="form-group full company-only" id="companyVerifGroup" style="display:none;">
                                <label><i class="fas fa-check-circle" style="color:#1d9bf0;"></i> Verificación oficial de empresa</label>
                                <div id="companyVerifStatus" style="font-size:13px; color:#334155; margin-bottom:8px;"></div>
                                <div id="companyVerifUploadBox" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <input type="file" id="companyVerifFileInput" accept=".pdf,image/jpeg,image/png,image/webp" style="display:none;" />
                                    <button type="button" class="btn btn-outline" id="uploadCompanyVerifBtn" style="padding:8px 14px; font-size:13px;">
                                        <i class="fas fa-file-upload"></i> Enviar documento de empresa (RIF)
                                    </button>
                                    <a href="#" id="viewCompanyVerifLink" target="_blank" style="display:none; font-size:13px; color:#0a66c2; font-weight:600; text-decoration:none;">
                                        <i class="fas fa-file-alt"></i> Ver documento enviado
                                    </a>
                                </div>
                            </div>

                            <!-- Ubicación (solo empresa) — empresas.ubicacion -->
                            <div class="form-group full company-only">
                                <label for="inputUbicacion">Ubicación</label>
                                <input type="text" id="inputUbicacion" class="form-control"
                                       placeholder="Caracas, Venezuela" maxlength="150" />
                            </div>

                            <div class="form-group full">
                                <label for="inputBiografia" id="bioLabel">Biografía</label>
                                <textarea id="inputBiografia" class="form-control"
                                          placeholder="Cuéntanos sobre ti, tu experiencia y tus intereses..."
                                          maxlength="500"></textarea>
                                <div class="char-counter">
                                    <span id="bioCount">0</span>/500
                                </div>
                            </div>

                            <!-- GitHub (solo estudiante) — perfil_estudiantes.github_url -->
                            <div class="form-group full student-only">
                                <label for="inputGithub">GitHub</label>
                                <input type="url" id="inputGithub" class="form-control"
                                       placeholder="https://github.com/tu-usuario" maxlength="255" />
                            </div>
                        </div>

                        <!-- ===== HABILIDADES (solo estudiante) ===== -->
                        <div class="form-section-title student-only" style="margin-top: 36px;">
                            <i class="fas fa-lightbulb"></i> Habilidades
                        </div>

                        <div class="skills-container student-only" id="skillsContainer"></div>
                        <div class="skill-input-wrapper student-only">
                            <input type="text" id="skillInput" class="form-control"
                                   placeholder="Añadir habilidad (ej: JavaScript)" maxlength="50" />
                            <button type="button" class="btn btn-primary" id="addSkillBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <!-- ===== ACCIONES ===== -->
                        <div class="form-actions">
                            <span class="save-status" id="saveStatus">
                                <i class="fas fa-check-circle"></i> Guardado
                            </span>
                            <button type="button" class="btn btn-outline" id="resetFormBtn">
                                <i class="fas fa-undo"></i> Restablecer
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                                <i class="fas fa-save"></i> Guardar cambios
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>

        <!-- ===== FOOTER ===== -->
        <div class="footer-note">
            <i class="fas fa-circle"></i> Todos los datos actualizados · Ineova v2.0
        </div>
    </div>


    <!-- ===== MODAL CAMBIAR CONTRASEÑA ===== -->
    <div class="modal-overlay" id="changePasswordModal">
        <div class="modal-box">
            <div class="modal-box-header">
                <h3><i class="fas fa-lock"></i> Cambiar contraseña</h3>
                <button type="button" class="modal-close-btn" id="closePasswordModal"><i class="fas fa-times"></i></button>
            </div>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="currentPasswordInput">Contraseña actual</label>
                    <input type="password" id="currentPasswordInput" class="form-control" required minlength="6" />
                </div>
                <div class="form-group">
                    <label for="newPasswordInput">Nueva contraseña</label>
                    <input type="password" id="newPasswordInput" class="form-control" required minlength="6" maxlength="72" />
                </div>
                <div class="form-group">
                    <label for="confirmNewPasswordInput">Confirmar nueva contraseña</label>
                    <input type="password" id="confirmNewPasswordInput" class="form-control" required minlength="6" maxlength="72" />
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" id="cancelPasswordModal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitPasswordBtn"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL ELIMINAR CUENTA ===== -->
    <div class="modal-overlay" id="deleteAccountModal">
        <div class="modal-box">
            <div class="modal-box-header">
                <h3 style="color:#c53e3e;"><i class="fas fa-exclamation-triangle"></i> Eliminar cuenta</h3>
                <button type="button" class="modal-close-btn" id="closeDeleteModal"><i class="fas fa-times"></i></button>
            </div>
            <p style="font-size:13.5px; color:#516378; line-height:1.6; margin-bottom:16px;">
                Esta acción desactivará y eliminará tu acceso permanentemente. Ingresa tu contraseña actual para confirmar.
            </p>
            <form id="deleteAccountForm">
                <div class="form-group">
                    <label for="deletePasswordInput">Contraseña actual</label>
                    <input type="password" id="deletePasswordInput" class="form-control" required />
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" id="cancelDeleteModal">Cancelar</button>
                    <button type="submit" class="btn-danger-solid" id="confirmDeleteBtn"><i class="fas fa-trash-alt"></i> Eliminar definitivamente</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== TOAST ===== -->
    <div class="toast" id="toast"></div>

    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.API_BASE = "<?= base_url('api/v1') ?>";
    </script>
    <script src="<?= base_url('js/perfil.js') ?>"></script>
</body>
</html>