<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class OffersController extends BaseController
{
    private const MODALITIES = ['remoto', 'presencial', 'hibrido'];
    private const CONTRACTS = ['tiempo_completo', 'medio_tiempo', 'pasantia', 'freelance', 'temporal'];
    private const EXPERIENCE = ['sin_experiencia', 'junior', 'semi_senior', 'senior'];

    public function index(): ResponseInterface
    {
        $db = db_connect();
        $builder = $db->table('ofertas o')
            ->select('o.*, org.tipo AS organismo_tipo, org.nombre AS organismo_nombre, org.siglas AS organismo_siglas, org.rif AS organismo_rif, org.logo AS organismo_logo, org.verificado AS organismo_verificado, ps.destaca_ofertas AS plan_destaca_ofertas, u.id AS publicado_por_id, u.correo AS publicado_por_correo, u.nombre AS publicado_por_nombre, u.apellido AS publicado_por_apellido, u.avatar AS publicado_por_avatar')
            ->join('organismos org', 'org.id = o.organismo_id')
            ->join('planes_suscripcion ps', 'ps.id = org.plan_id', 'left')
            ->join('usuarios u', 'u.id = o.publicado_por')
            ->where('o.estado', 'activa');

        $view = (string) $this->request->getGet('view');
        $user = $this->currentUser();
        if (in_array($view, ['mine', 'saved'], true) && ! $user) {
            return $this->unauthorized();
        }
        if ($view === 'mine') {
            $builder->where('o.publicado_por', $user['id']);
        } elseif ($view === 'saved') {
            if ($user['rol'] !== 'estudiante') {
                return $this->json(['ok' => true, 'offers' => []]);
            }
            $builder->join('ofertas_guardadas saved', 'saved.oferta_id = o.id AND saved.usuario_id = ' . (int) $user['id']);
        }

        $filter = (string) $this->request->getGet('filter');
        if (in_array($filter, self::MODALITIES, true)) {
            $builder->where('o.modalidad', $filter);
        }
        $query = trim((string) $this->request->getGet('q'));
        if ($query !== '') {
            $builder->groupStart()->like('o.titulo', $query)->orLike('o.descripcion', $query)->orLike('org.nombre', $query)->groupEnd();
        }

        $studentSkillsLower = [];
        if ($user && $user['rol'] === 'estudiante') {
            $studentProfile = $db->table('perfil_estudiantes')->select('id')->where('usuario_id', $user['id'])->get()->getRowArray();
            if ($studentProfile) {
                $rawSkills = array_column($db->table('estudiante_habilidad eh')
                    ->select('h.nombre')->join('habilidades h', 'h.id = eh.habilidad_id')
                    ->where('eh.estudiante_id', $studentProfile['id'])->get()->getResultArray(), 'nombre');
                $studentSkillsLower = array_map(static fn($s) => mb_strtolower(trim((string) $s)), $rawSkills);
            }
        }

        $offers = $builder->orderBy('o.fecha_creacion', 'DESC')->get()->getResultArray();
        foreach ($offers as &$offer) {
            $offerId = (int) $offer['id'];
            $isExtraPlan = (int) ($offer['plan_destaca_ofertas'] ?? 0) === 1;
            unset($offer['puntos_crediticios'], $offer['salario_min'], $offer['salario_max'], $offer['salario_moneda'], $offer['plan_destaca_ofertas']);
            $offer['organismo_verificado'] = (bool) ($offer['organismo_verificado'] ?? false);
            $offer['habilidades'] = $db->table('oferta_habilidades oh')
                ->select('h.nombre')->join('habilidades h', 'h.id = oh.habilidad_id')
                ->where('oh.oferta_id', $offerId)->get()->getResultArray();
            $offer['habilidades'] = array_column($offer['habilidades'], 'nombre');
            $offer['imagenes'] = array_column($db->table('oferta_imagenes')->select('url')
                ->where('oferta_id', $offerId)->orderBy('posicion')->get()->getResultArray(), 'url');
            $offer['guardada'] = ($user && $user['rol'] === 'estudiante')
                ? $db->table('ofertas_guardadas')->where(['usuario_id' => $user['id'], 'oferta_id' => $offerId])->countAllResults() > 0
                : false;
            $offer['aplicada'] = ($user && $user['rol'] === 'estudiante')
                ? $db->table('aplicaciones')->where(['usuario_id' => $user['id'], 'oferta_id' => $offerId])->countAllResults() > 0
                : false;

            $matchedSkills = [];
            if ($isExtraPlan && $studentSkillsLower !== []) {
                foreach ($offer['habilidades'] as $offerSkill) {
                    if (in_array(mb_strtolower(trim((string) $offerSkill)), $studentSkillsLower, true)) {
                        $matchedSkills[] = $offerSkill;
                    }
                }
                if ($matchedSkills === []) {
                    $haystack = mb_strtolower($offer['titulo'] . ' ' . $offer['descripcion']);
                    foreach ($studentSkillsLower as $sk) {
                        if ($sk !== '' && str_contains($haystack, $sk)) {
                            $matchedSkills[] = $sk;
                        }
                    }
                }
            }
            $offer['destacada'] = $isExtraPlan && $matchedSkills !== [];
            $offer['coincidencias'] = array_values(array_unique($matchedSkills));
        }
        unset($offer);

        if ($user && $user['rol'] === 'estudiante') {
            usort($offers, static function (array $a, array $b): int {
                $da = ! empty($a['destacada']) ? 1 : 0;
                $dbVal = ! empty($b['destacada']) ? 1 : 0;
                if ($da !== $dbVal) {
                    return $dbVal <=> $da;
                }
                return strcmp((string) $b['fecha_creacion'], (string) $a['fecha_creacion']);
            });
        }

        return $this->json(['ok' => true, 'offers' => $offers]);
    }

    public function organisms(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }

        $organisms = db_connect()->table('participante_organismo po')
            ->select('org.id, org.tipo, org.nombre, org.siglas, org.rif, org.logo, org.verificado, po.rol_en_organismo')
            ->join('organismos org', 'org.id = po.organismo_id')
            ->where('po.usuario_id', $user['id'])->where('po.activo', 1)
            ->get()->getResultArray();

        return $this->json(['ok' => true, 'organisms' => $organisms]);
    }

    public function create(): ResponseInterface
    {
        return $this->saveOffer();
    }

    public function update(int $id): ResponseInterface
    {
        return $this->saveOffer($id);
    }

    public function delete(int $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'participante_empresa') {
            return $this->json(['ok' => false, 'error' => 'Solo las empresas pueden eliminar ofertas.'], 403);
        }

        $db = db_connect();
        $db->table('ofertas')->where(['id' => $id, 'publicado_por' => $user['id']])->delete();
        if ($db->affectedRows() === 0) {
            return $this->json(['ok' => false, 'error' => 'No se encontró una oferta tuya con ese identificador.'], 404);
        }

        return $this->json(['ok' => true]);
    }

    public function toggleSaved(int $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'estudiante') {
            return $this->json(['ok' => false, 'error' => 'Solo los estudiantes pueden guardar ofertas.'], 403);
        }

        $db = db_connect();
        if ($db->table('ofertas')->where(['id' => $id, 'estado' => 'activa'])->countAllResults() === 0) {
            return $this->json(['ok' => false, 'error' => 'La oferta ya no está disponible.'], 404);
        }
        $existing = $db->table('ofertas_guardadas')->where(['usuario_id' => $user['id'], 'oferta_id' => $id])->get()->getRowArray();
        if ($existing) {
            $db->table('ofertas_guardadas')->where('id', $existing['id'])->delete();
            return $this->json(['ok' => true, 'saved' => false]);
        }

        $db->table('ofertas_guardadas')->insert(['usuario_id' => $user['id'], 'oferta_id' => $id]);
        return $this->json(['ok' => true, 'saved' => true]);
    }

    public function apply(int $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'estudiante') {
            return $this->json(['ok' => false, 'error' => 'Solo las cuentas de estudiante pueden postularse.'], 403);
        }

        $offer = db_connect()->table('ofertas')->select('publicado_por, titulo')
            ->where(['id' => $id, 'estado' => 'activa'])->get()->getRowArray();
        if (! $offer) {
            return $this->json(['ok' => false, 'error' => 'La oferta ya no está disponible.'], 404);
        }
        if ((int) $offer['publicado_por'] === (int) $user['id']) {
            return $this->json(['ok' => false, 'error' => 'No puedes postularte a tu propia oferta.'], 403);
        }

        $db = db_connect();
        if ($db->table('aplicaciones')->where(['oferta_id' => $id, 'usuario_id' => $user['id']])->countAllResults() > 0) {
            return $this->json(['ok' => false, 'error' => 'Ya te postulaste a esta oferta.'], 409);
        }

        $data = $this->request->getJSON(true) ?? [];
        $db->transBegin();
        try {
            $db->table('aplicaciones')->insert([
                'uuid'               => $this->uuid(),
                'oferta_id'          => $id,
                'usuario_id'         => $user['id'],
                'carta_presentacion' => trim((string) ($data['carta_presentacion'] ?? '')) ?: null,
                'cv_url'             => trim((string) ($data['cv_url'] ?? '')) ?: null,
            ]);
            $db->table('notificaciones')->insert([
                'usuario_id'   => $offer['publicado_por'],
                'tipo'         => 'info',
                'icono'        => 'fa-briefcase',
                'titulo'       => 'Nueva postulación de becario',
                'descripcion'  => ($user['nombre'] ?? 'Un estudiante') . ' se postuló a tu oferta "' . $offer['titulo'] . '".',
                'url_accion'   => base_url('dashboard') . '#misofertas',
                'entidad_tipo' => 'oferta',
                'entidad_id'   => $id,
            ]);
            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo guardar la postulación.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Postulación Ineova fallida: {message}', ['message' => $exception->getMessage()]);
            return $this->json(['ok' => false, 'error' => 'No se pudo enviar la postulación.'], 500);
        }

        return $this->json(['ok' => true], 201);
    }

    public function applicants(int $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }

        $db = db_connect();
        $offer = $db->table('ofertas')->select('id, titulo')
            ->where(['id' => $id, 'publicado_por' => $user['id']])->get()->getRowArray();
        if (! $offer) {
            return $this->json(['ok' => false, 'error' => 'No se encontró una oferta tuya con ese identificador.'], 404);
        }

        $rows = $db->table('aplicaciones a')
            ->select('a.id, a.estado, a.carta_presentacion, a.fecha_aplicacion, u.id AS usuario_id, u.nombre, u.apellido, u.correo, u.avatar, pe.id AS perfil_id, pe.github_url, pe.autobiografia, pe.estado_vigencia, pe.promedio, pe.notas_certificadas, pe.constancia_notas_url, org.nombre AS universidad_nombre, org.siglas AS universidad_siglas')
            ->join('usuarios u', 'u.id = a.usuario_id')
            ->join('perfil_estudiantes pe', 'pe.usuario_id = u.id', 'left')
            ->join('organismos org', 'org.id = pe.organismo_id', 'left')
            ->where('a.oferta_id', $id)
            ->orderBy('a.fecha_aplicacion', 'DESC')
            ->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['notas_certificadas'] = (bool) ($row['notas_certificadas'] ?? false);
            $row['habilidades'] = ! empty($row['perfil_id'])
                ? array_column($db->table('estudiante_habilidad eh')
                    ->select('h.nombre')->join('habilidades h', 'h.id = eh.habilidad_id')
                    ->where('eh.estudiante_id', $row['perfil_id'])->get()->getResultArray(), 'nombre')
                : [];
        }

        return $this->json(['ok' => true, 'offer' => $offer, 'applicants' => $rows]);
    }

    public function myApplications(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }

        $db = db_connect();
        $rows = $db->table('aplicaciones a')
            ->select('a.id, a.estado, a.fecha_aplicacion, o.id AS oferta_id, o.titulo, o.modalidad, o.ubicacion, o.publicado_por, org.nombre AS organismo_nombre, org.logo AS organismo_logo, org.verificado AS organismo_verificado')
            ->join('ofertas o', 'o.id = a.oferta_id')
            ->join('organismos org', 'org.id = o.organismo_id')
            ->where('a.usuario_id', $user['id'])
            ->orderBy('a.fecha_aplicacion', 'DESC')
            ->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['organismo_verificado'] = (bool) ($row['organismo_verificado'] ?? false);
        }

        return $this->json(['ok' => true, 'applications' => $rows]);
    }

    public function updateApplicationStatus(int $applicationId): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }

        $data = $this->request->getJSON(true) ?? [];
        $newStatus = (string) ($data['estado'] ?? '');
        if (! in_array($newStatus, ['pendiente', 'revisada', 'entrevista', 'aceptada', 'rechazada'], true)) {
            return $this->json(['ok' => false, 'error' => 'Estado de postulación no válido.'], 422);
        }

        $db = db_connect();
        $application = $db->table('aplicaciones a')
            ->select('a.id, a.usuario_id, a.estado, a.oferta_id, o.titulo, o.publicado_por')
            ->join('ofertas o', 'o.id = a.oferta_id')
            ->where('a.id', $applicationId)
            ->get()->getRowArray();
        if (! $application || (int) $application['publicado_por'] !== (int) $user['id']) {
            return $this->json(['ok' => false, 'error' => 'No tienes permiso para actualizar esta postulación.'], 403);
        }

        $conversationId = null;
        $db->transBegin();
        try {
            $db->table('aplicaciones')->where('id', $applicationId)->update(['estado' => $newStatus]);

            
            if ($newStatus === 'aceptada') {
                $u1 = min((int) $user['id'], (int) $application['usuario_id']);
                $u2 = max((int) $user['id'], (int) $application['usuario_id']);
                $existingConv = $db->table('conversaciones')
                    ->where(['usuario1_id' => $u1, 'usuario2_id' => $u2])
                    ->get()->getRowArray();

                $welcomeText = 'Tu postulación a "' . $application['titulo'] . '" fue aceptada. Este chat se ha habilitado para coordinar los detalles de tu pasantía / beca.';
                if ($existingConv) {
                    $conversationId = $existingConv['id'];
                    $db->table('conversaciones')->where('id', $conversationId)->update([
                        'estado'               => 'activa',
                        'oferta_id'            => $application['oferta_id'],
                        'ultimo_mensaje'       => $welcomeText,
                        'ultimo_mensaje_fecha' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $conversationId = $this->uuid();
                    $db->table('conversaciones')->insert([
                        'id'                   => $conversationId,
                        'usuario1_id'          => $u1,
                        'usuario2_id'          => $u2,
                        'oferta_id'            => $application['oferta_id'],
                        'tipo'                 => 'oferta',
                        'estado'               => 'activa',
                        'ultimo_mensaje'       => $welcomeText,
                        'ultimo_mensaje_fecha' => date('Y-m-d H:i:s'),
                    ]);
                }
                if ($application['estado'] !== 'aceptada') {
                    $db->table('mensajes')->insert([
                        'id'              => $this->uuid(),
                        'conversacion_id' => $conversationId,
                        'remitente_id'    => $user['id'],
                        'cuerpo'          => $welcomeText,
                    ]);
                }
            }

            $statusLabels = [
                'revisada'   => 'fue revisada',
                'entrevista' => 'pasó a etapa de entrevista',
                'aceptada'   => 'fue aceptada. ¡Se habilitó el chat con la empresa para coordinar!',
                'rechazada'  => 'no fue seleccionada en esta ocasión',
                'pendiente'  => 'volvió a estado pendiente',
            ];
            $db->table('notificaciones')->insert([
                'usuario_id'   => $application['usuario_id'],
                'tipo'         => $newStatus === 'aceptada' ? 'success' : ($newStatus === 'rechazada' ? 'warning' : 'info'),
                'icono'        => $newStatus === 'aceptada' ? 'fa-check-circle' : 'fa-briefcase',
                'titulo'       => 'Actualización de postulación',
                'descripcion'  => 'Tu postulación a "' . $application['titulo'] . '" ' . ($statusLabels[$newStatus] ?? 'fue actualizada') . '.',
                'url_accion'   => base_url('dashboard'),
                'entidad_tipo' => 'oferta',
                'entidad_id'   => $application['oferta_id'],
            ]);

            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo actualizar la postulación.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Actualización de postulación fallida: {message}', ['message' => $exception->getMessage()]);
            return $this->json(['ok' => false, 'error' => 'No se pudo actualizar el estado.'], 500);
        }

        return $this->json(['ok' => true, 'estado' => $newStatus, 'conversation_id' => $conversationId]);
    }

    public function selectRecommended(int $offerId): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'participante_empresa') {
            return $this->json(['ok' => false, 'error' => 'Solo las empresas pueden seleccionar estudiantes para sus ofertas.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $studentId = (int) ($data['student_id'] ?? 0);
        if ($studentId < 1) {
            return $this->json(['ok' => false, 'error' => 'Estudiante no válido.'], 422);
        }

        $db = db_connect();
        $offer = $db->table('ofertas')->select('id, titulo, publicado_por')
            ->where(['id' => $offerId, 'publicado_por' => $user['id'], 'estado' => 'activa'])
            ->get()->getRowArray();
        if (! $offer) {
            return $this->json(['ok' => false, 'error' => 'No se encontró una oferta activa tuya con ese identificador.'], 404);
        }

        $student = $db->table('usuarios')->select('id, nombre, apellido, correo')
            ->where(['id' => $studentId, 'rol' => 'estudiante', 'estado' => 'activo'])
            ->get()->getRowArray();
        if (! $student) {
            return $this->json(['ok' => false, 'error' => 'El estudiante recomendado no está disponible.'], 404);
        }

        $conversationId = null;
        $db->transBegin();
        try {
            $existingApp = $db->table('aplicaciones')->where(['oferta_id' => $offerId, 'usuario_id' => $studentId])->get()->getRowArray();
            if ($existingApp) {
                $db->table('aplicaciones')->where('id', $existingApp['id'])->update(['estado' => 'aceptada']);
            } else {
                $db->table('aplicaciones')->insert([
                    'uuid'               => $this->uuid(),
                    'oferta_id'          => $offerId,
                    'usuario_id'         => $studentId,
                    'estado'             => 'aceptada',
                    'carta_presentacion' => 'Seleccionado directamente por recomendación universitaria.',
                ]);
            }

            
            $u1 = min((int) $user['id'], $studentId);
            $u2 = max((int) $user['id'], $studentId);
            $existingConv = $db->table('conversaciones')->where(['usuario1_id' => $u1, 'usuario2_id' => $u2])->get()->getRowArray();
            $welcomeText = 'Has sido seleccionado por recomendación universitaria para la oferta "' . $offer['titulo'] . '". Este chat se ha habilitado para coordinar los detalles.';

            if ($existingConv) {
                $conversationId = $existingConv['id'];
                $db->table('conversaciones')->where('id', $conversationId)->update([
                    'estado'               => 'activa',
                    'oferta_id'            => $offerId,
                    'ultimo_mensaje'       => $welcomeText,
                    'ultimo_mensaje_fecha' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $conversationId = $this->uuid();
                $db->table('conversaciones')->insert([
                    'id'                   => $conversationId,
                    'usuario1_id'          => $u1,
                    'usuario2_id'          => $u2,
                    'oferta_id'            => $offerId,
                    'tipo'                 => 'oferta',
                    'estado'               => 'activa',
                    'ultimo_mensaje'       => $welcomeText,
                    'ultimo_mensaje_fecha' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->table('mensajes')->insert([
                'id'              => $this->uuid(),
                'conversacion_id' => $conversationId,
                'remitente_id'    => $user['id'],
                'cuerpo'          => $welcomeText,
            ]);

            $db->table('notificaciones')->insert([
                'usuario_id'   => $studentId,
                'tipo'         => 'success',
                'icono'        => 'fa-check-circle',
                'titulo'       => '¡Fuiste seleccionado en una oferta!',
                'descripcion'  => 'Fuiste seleccionado por recomendación universitaria para "' . $offer['titulo'] . '". Ya tienes el chat habilitado en Conversaciones.',
                'url_accion'   => base_url('dashboard'),
                'entidad_tipo' => 'oferta',
                'entidad_id'   => $offerId,
            ]);

            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo seleccionar al estudiante.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Selección de recomendado fallida: {message}', ['message' => $exception->getMessage()]);
            return $this->json(['ok' => false, 'error' => 'No se pudo completar la selección.'], 500);
        }

        return $this->json([
            'ok'              => true,
            'message'         => 'Estudiante seleccionado para "' . $offer['titulo'] . '" y chat habilitado.',
            'conversation_id' => $conversationId,
        ]);
    }

    private function saveOffer(?int $id = null): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'participante_empresa') {
            return $this->json(['ok' => false, 'error' => 'Solo las empresas pueden publicar ofertas.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $title = trim((string) ($data['titulo'] ?? ''));
        $description = trim((string) ($data['descripcion'] ?? ''));
        $location = trim((string) ($data['ubicacion'] ?? ''));
        $modality = (string) ($data['modalidad'] ?? '');
        $contract = (string) ($data['tipo_contrato'] ?? 'pasantia');
        $organizationId = (int) ($data['organismo_id'] ?? 0);

        if ($title === '' || mb_strlen($title) > 200 || $description === '' || $location === '' || mb_strlen($location) > 120
            || ! in_array($modality, self::MODALITIES, true) || ! in_array($contract, self::CONTRACTS, true)) {
            return $this->json(['ok' => false, 'error' => 'Revisa el título, descripción, ubicación, modalidad y tipo de contrato.'], 422);
        }
        if (! in_array((string) ($data['nivel_experiencia'] ?? 'sin_experiencia'), self::EXPERIENCE, true)) {
            return $this->json(['ok' => false, 'error' => 'El nivel de experiencia no es válido.'], 422);
        }

        $db = db_connect();
        $membership = $db->table('participante_organismo po')
            ->join('organismos org', 'org.id = po.organismo_id')
            ->where(['po.usuario_id' => $user['id'], 'po.organismo_id' => $organizationId, 'po.activo' => 1, 'org.tipo' => 'empresa'])
            ->countAllResults();
        if ($membership === 0) {
            return $this->json(['ok' => false, 'error' => 'No tienes una empresa activa asociada con ese identificador.'], 403);
        }
        if ($id !== null && $db->table('ofertas')->where(['id' => $id, 'publicado_por' => $user['id']])->countAllResults() === 0) {
            return $this->json(['ok' => false, 'error' => 'No se encontró una oferta tuya con ese identificador.'], 404);
        }

        if ($id === null) {
            $orgRow = $db->table('organismos')->where('id', $organizationId)->get()->getRowArray();
            $planId = (int) ($orgRow['plan_id'] ?? 1);
            $planRow = $db->table('planes_suscripcion')->where('id', $planId)->get()->getRowArray();
            if ($planId === 1) {
                $trialEnd = $orgRow['trial_fin'] ?? date('Y-m-d H:i:s', strtotime(($orgRow['fecha_registro'] ?? 'now') . ' +7 days'));
                if (time() > strtotime($trialEnd)) {
                    return $this->json([
                        'ok'    => false,
                        'error' => 'Tu período de prueba gratuito de 7 días ha finalizado. Elige el Plan Estándar ($29.99) o Extra ($79.99) en Mi Perfil y solicita su activación a un administrador.',
                    ], 403);
                }
            } elseif ($planId === 2) {
                $limit = (int) ($planRow['limite_ofertas'] ?? 10);
                $activeOffers = $db->table('ofertas')->where(['organismo_id' => $organizationId, 'estado' => 'activa'])->countAllResults();
                if ($limit > 0 && $activeOffers >= $limit) {
                    return $this->json([
                        'ok'    => false,
                        'error' => 'Has alcanzado el límite de ' . $limit . ' publicaciones del Plan Estándar. Actualiza al Plan Extra ($79.99) desde Mi Perfil.',
                    ], 403);
                }
            }
        }

        $offer = [
            'organismo_id'      => $organizationId,
            'publicado_por'     => $user['id'],
            'titulo'            => $title,
            'descripcion'       => $description,
            'ubicacion'         => $location,
            'modalidad'         => $modality,
            'tipo_contrato'     => $contract,
            'salario_min'       => null,
            'salario_max'       => null,
            'categoria'         => 'general',
            'nivel_experiencia' => (string) ($data['nivel_experiencia'] ?? 'sin_experiencia'),
            'vacantes'          => max(1, min(99, (int) ($data['vacantes'] ?? 1))),
            'fecha_expiracion'  => ($data['fecha_expiracion'] ?? '') !== '' ? $data['fecha_expiracion'] : null,
        ];
        if ($db->fieldExists('puntos_crediticios', 'ofertas')) {
            $offer['puntos_crediticios'] = 0;
        }

        $images = $data['imagenes'] ?? [];
        if (! is_array($images) || count($images) > 3) {
            return $this->json(['ok' => false, 'error' => 'Puedes adjuntar hasta 3 imágenes.'], 422);
        }

        $isNew = $id === null;
        $db->transBegin();
        try {
            if ($id === null) {
                $offer['uuid'] = $this->uuid();
                $db->table('ofertas')->insert($offer);
                $id = (int) $db->insertID();
            } else {
                $db->table('ofertas')->where('id', $id)->update($offer);
                $db->table('oferta_habilidades')->where('oferta_id', $id)->delete();
                $db->table('oferta_imagenes')->where('oferta_id', $id)->delete();
            }

            $this->saveSkills($id, $data['habilidades'] ?? []);
            $this->saveImages($id, $images);
            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo guardar la oferta.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Guardado de oferta fallido: {message}', ['message' => $exception->getMessage()]);
            return $this->json(['ok' => false, 'error' => 'No se pudo guardar la oferta. Verifica los datos e inténtalo de nuevo.'], 422);
        }

        return $this->json(['ok' => true, 'id' => $id], $isNew ? 201 : 200);
    }

    private function saveSkills(int $offerId, mixed $skills): void
    {
        if (! is_array($skills)) {
            return;
        }
        $db = db_connect();
        foreach (array_unique(array_slice(array_map('trim', $skills), 0, 20)) as $name) {
            if ($name === '' || mb_strlen($name) > 100) {
                continue;
            }
            $skill = $db->table('habilidades')->where('nombre', $name)->get()->getRowArray();
            if (! $skill) {
                $db->table('habilidades')->insert(['nombre' => $name, 'tipo_habilidad' => 'Técnica']);
                $skillId = (int) $db->insertID();
            } else {
                $skillId = (int) $skill['id'];
            }
            $db->table('oferta_habilidades')->insert(['oferta_id' => $offerId, 'habilidad_id' => $skillId]);
        }
    }

    private function saveImages(int $offerId, array $images): void
    {
        $db = db_connect();
        foreach ($images as $position => $image) {
            if (! is_string($image)) {
                continue;
            }
            $url = $image;
            if (preg_match('#^data:image/(jpeg|png|gif|webp);base64,(.+)$#', $image, $matches)) {
                $contents = base64_decode($matches[2], true);
                if ($contents === false || strlen($contents) > 5 * 1024 * 1024) {
                    throw new \RuntimeException('La imagen no es válida o supera 5 MB.');
                }
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
                $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if (! isset($extensions[$mime])) {
                    throw new \RuntimeException('Formato de imagen no permitido.');
                }
                $directory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'offers';
                if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                    throw new \RuntimeException('No se pudo crear el directorio de imágenes.');
                }
                $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                if (file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $contents) === false) {
                    throw new \RuntimeException('No se pudo guardar la imagen.');
                }
                $url = base_url('uploads/offers/' . $filename);
            } elseif (filter_var($image, FILTER_VALIDATE_URL)) {
                $scheme = strtolower((string) parse_url($image, PHP_URL_SCHEME));
                if (! in_array($scheme, ['http', 'https'], true)) {
                    continue;
                }
            } elseif (! str_contains($image, 'uploads/offers/')) {
                continue;
            }
            $db->table('oferta_imagenes')->insert(['oferta_id' => $offerId, 'url' => $url, 'posicion' => $position]);
        }
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
