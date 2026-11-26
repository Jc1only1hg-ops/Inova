<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdminController extends BaseController
{
    public function pending(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'admin') {
            return $this->json(['ok' => false, 'error' => 'Acceso restringido a administradores.'], 403);
        }

        $db = db_connect();
        $coordinators = $db->table('usuarios u')
            ->select('u.id, u.correo, u.nombre, u.apellido, u.cedula, u.telefono, u.estado, u.fecha_registro, org.nombre AS universidad_nombre, org.siglas AS universidad_siglas')
            ->join('participante_organismo po', 'po.usuario_id = u.id')
            ->join('organismos org', 'org.id = po.organismo_id')
            ->where('u.rol', 'participante_universidad')
            ->where('po.activo', 0)
            ->orderBy('u.fecha_registro', 'DESC')
            ->get()->getResultArray();

        $companies = $db->table('organismos org')
            ->select('org.id, org.nombre, org.rif, org.ubicacion, org.sitio_web, org.verificado, org.estado_verificacion, org.documento_verificacion_url, org.plan_id, org.trial_fin, ps.nombre AS plan_nombre, ps.precio AS plan_precio, u.nombre AS rep_nombre, u.apellido AS rep_apellido, u.correo AS rep_correo')
            ->join('usuarios u', 'u.id = org.creado_por')
            ->join('planes_suscripcion ps', 'ps.id = org.plan_id', 'left')
            ->where('org.tipo', 'empresa')
            ->orderBy('FIELD(org.estado_verificacion, "pendiente", "no_enviado", "verificado", "rechazado")', '', false)
            ->orderBy('org.fecha_registro', 'DESC')
            ->get()->getResultArray();

        $plans = $db->table('planes_suscripcion')
            ->select('id, nombre, precio, limite_ofertas, destaca_ofertas')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        return $this->json([
            'ok'           => true,
            'coordinators' => $coordinators,
            'companies'    => $companies,
            'plans'        => $plans,
        ]);
    }

    public function reviewCoordinator(int $coordinatorUserId): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'admin') {
            return $this->json(['ok' => false, 'error' => 'Acceso restringido a administradores.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $action = (string) ($data['action'] ?? '');
        if (! in_array($action, ['approve', 'reject'], true)) {
            return $this->json(['ok' => false, 'error' => 'Acción no válida.'], 422);
        }

        $db = db_connect();
        $target = $db->table('usuarios')->where(['id' => $coordinatorUserId, 'rol' => 'participante_universidad'])->get()->getRowArray();
        if (! $target) {
            return $this->json(['ok' => false, 'error' => 'Coordinador no encontrado.'], 404);
        }

        if ($action === 'approve') {
            $db->table('usuarios')->where('id', $coordinatorUserId)->update(['estado' => 'activo', 'verificado' => 1]);
            $db->table('participante_organismo')->where('usuario_id', $coordinatorUserId)->update(['activo' => 1]);
        } else {
            $db->table('usuarios')->where('id', $coordinatorUserId)->update(['estado' => 'suspendido']);
            $db->table('participante_organismo')->where('usuario_id', $coordinatorUserId)->delete();
        }

        return $this->json(['ok' => true]);
    }

    public function verifyCompany(int $organismId): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'admin') {
            return $this->json(['ok' => false, 'error' => 'Acceso restringido a administradores.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $action = (string) ($data['action'] ?? '');
        if (! in_array($action, ['approve', 'reject'], true)) {
            return $this->json(['ok' => false, 'error' => 'Acción no válida.'], 422);
        }

        $db = db_connect();
        $company = $db->table('organismos')->where(['id' => $organismId, 'tipo' => 'empresa'])->get()->getRowArray();
        if (! $company) {
            return $this->json(['ok' => false, 'error' => 'Empresa no encontrada.'], 404);
        }

        $approved = $action === 'approve';
        $db->table('organismos')->where('id', $organismId)->update([
            'verificado'          => $approved ? 1 : 0,
            'estado_verificacion' => $approved ? 'verificado' : 'rechazado',
        ]);

        $db->table('notificaciones')->insert([
            'usuario_id'   => $company['creado_por'],
            'tipo'         => $approved ? 'success' : 'warning',
            'icono'        => 'fa-check-circle',
            'titulo'       => $approved ? 'Empresa verificada ✓' : 'Verificación de empresa rechazada',
            'descripcion'  => $approved
                ? 'Tu empresa "' . $company['nombre'] . '" ahora cuenta con el sello oficial de verificación.'
                : 'La solicitud de verificación de "' . $company['nombre'] . '" fue rechazada. Puedes subir un nuevo documento desde Mi Perfil.',
            'url_accion'   => base_url('perfil'),
            'entidad_tipo' => 'sistema',
        ]);

        return $this->json(['ok' => true]);
    }

    public function updateCompanyPlan(int $organismId): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'admin') {
            return $this->json(['ok' => false, 'error' => 'Acceso restringido a administradores.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $planId = (int) ($data['plan_id'] ?? 0);

        $db = db_connect();
        $plan = $db->table('planes_suscripcion')->where('id', $planId)->get()->getRowArray();
        if (! $plan) {
            return $this->json(['ok' => false, 'error' => 'El plan seleccionado no existe.'], 422);
        }

        $company = $db->table('organismos')->where(['id' => $organismId, 'tipo' => 'empresa'])->get()->getRowArray();
        if (! $company) {
            return $this->json(['ok' => false, 'error' => 'Empresa no encontrada.'], 404);
        }

        $update = ['plan_id' => $planId];
        if ($planId === 1 && $db->fieldExists('trial_fin', 'organismos')) {
            $update['trial_fin'] = date('Y-m-d H:i:s', strtotime('+7 days'));
        }
        $db->table('organismos')->where('id', $organismId)->update($update);

        $db->table('notificaciones')->insert([
            'usuario_id'   => $company['creado_por'],
            'tipo'         => 'success',
            'icono'        => 'fa-crown',
            'titulo'       => 'Plan de suscripción actualizado',
            'descripcion'  => 'Un administrador configuró el plan de "' . $company['nombre'] . '" a: ' . $plan['nombre'] . '.',
            'url_accion'   => base_url('perfil'),
            'entidad_tipo' => 'sistema',
        ]);

        return $this->json(['ok' => true, 'plan' => $plan]);
    }
}
