<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    public function universities(): ResponseInterface
    {
        $universities = db_connect()->table('organismos')
            ->select('id, nombre, siglas')->where('tipo', 'universidad')
            ->orderBy('nombre')->get()->getResultArray();

        return $this->json(['ok' => true, 'universities' => $universities]);
    }

    public function show(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Los administradores no tienen perfil.'], 403);
        }

        $db = db_connect();
        $account = $db->table('usuarios')->select('id, correo, rol, nombre, apellido, cedula, telefono, avatar')
            ->where('id', $user['id'])->where('estado', 'activo')->get()->getRowArray();
        if (! $account) {
            session()->destroy();
            return $this->unauthorized();
        }

        $profile = $this->buildProfilePayload($db, $account, true);
        return $this->json(['ok' => true, 'profile' => $profile]);
    }

    public function publicProfile(int $userId): ResponseInterface
    {
        $currentUser = $this->currentUser();
        if (! $currentUser) {
            return $this->unauthorized();
        }

        $db = db_connect();
        $account = $db->table('usuarios')->select('id, correo, rol, nombre, apellido, telefono, avatar')
            ->where('id', $userId)->where('estado', 'activo')->where('rol !=', 'admin')->get()->getRowArray();
        if (! $account) {
            return $this->json(['ok' => false, 'error' => 'Perfil no encontrado.'], 404);
        }

        $profile = $this->buildProfilePayload($db, $account, (int) $currentUser['id'] === $userId);
        return $this->json(['ok' => true, 'profile' => $profile]);
    }

    public function update(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Los administradores no tienen perfil editable.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $firstName = trim((string) ($data['nombre'] ?? ''));
        $lastName = trim((string) ($data['apellido'] ?? ''));
        if (mb_strlen($firstName) < 2 || mb_strlen($firstName) > 100 || mb_strlen($lastName) < 2 || mb_strlen($lastName) > 100) {
            return $this->json(['ok' => false, 'error' => 'Nombre y apellido deben tener entre 2 y 100 caracteres.'], 422);
        }

        $db = db_connect();
        $db->transBegin();
        try {
            $userUpdate = [
                'nombre'   => $firstName,
                'apellido' => $lastName,
            ];
            if (array_key_exists('cedula', $data)) {
                $userUpdate['cedula'] = trim((string) $data['cedula']) ?: null;
            }
            if (array_key_exists('telefono', $data)) {
                $userUpdate['telefono'] = trim((string) $data['telefono']) ?: null;
            }
            $db->table('usuarios')->where('id', $user['id'])->update($userUpdate);

            if ($user['rol'] === 'estudiante') {
                $student = $db->table('perfil_estudiantes')->where('usuario_id', $user['id'])->get()->getRowArray();
                $universityId = trim((string) ($data['universidad_id'] ?? ''));
                $newUniId = $universityId === '' ? null : (int) $universityId;

                if ($newUniId !== null && $db->table('organismos')->where(['id' => $newUniId, 'tipo' => 'universidad'])->countAllResults() === 0) {
                    throw new \InvalidArgumentException('La universidad seleccionada no existe.');
                }

                $currentUniId = $student && ! empty($student['organismo_id']) ? (int) $student['organismo_id'] : null;
                $changeAllowed = $student && (int) ($student['cambio_universidad_habilitado'] ?? 0) === 1;

                
                if ($currentUniId !== null && $newUniId !== $currentUniId && ! $changeAllowed) {
                    throw new \InvalidArgumentException('Para cambiar de universidad debes solicitar primero la habilitación a tu universidad actual.');
                }

                $studentData = [
                    'autobiografia' => trim((string) ($data['autobiografia'] ?? '')),
                    'github_url'    => trim((string) ($data['github_url'] ?? '')) ?: null,
                ];

                if ($currentUniId === null || ($newUniId !== $currentUniId && $changeAllowed)) {
                    $studentData['organismo_id'] = $newUniId;
                    if ($newUniId !== $currentUniId) {
                        $studentData['estado_vigencia'] = 'pendiente';
                        $studentData['notas_certificadas'] = 0;
                        $studentData['cambio_universidad_habilitado'] = 0;
                        $studentData['cambio_universidad_solicitado'] = 0;
                    }
                }

                if ($student) {
                    $db->table('perfil_estudiantes')->where('id', $student['id'])->update($studentData);
                    $studentId = (int) $student['id'];
                } else {
                    $studentData['usuario_id'] = $user['id'];
                    $db->table('perfil_estudiantes')->insert($studentData);
                    $studentId = (int) $db->insertID();
                }
                $this->saveStudentSkills($studentId, $data['habilidades'] ?? []);
            } elseif (in_array($user['rol'], ['participante_empresa', 'participante_universidad'], true)) {
                $organization = $db->table('participante_organismo po')
                    ->select('org.id, org.tipo')->join('organismos org', 'org.id = po.organismo_id')
                    ->where('po.usuario_id', $user['id'])->where('po.activo', 1)
                    ->orderBy('po.id')->get()->getRowArray();
                if ($organization) {
                    $orgData = [
                        'sitio_web'   => trim((string) ($data['sitio_web'] ?? '')) ?: null,
                        'ubicacion'   => trim((string) ($data['ubicacion'] ?? '')) ?: null,
                        'descripcion' => trim((string) ($data['descripcion'] ?? '')) ?: null,
                    ];
                    
                    if ($organization['tipo'] === 'empresa') {
                        $orgData['nombre'] = trim((string) ($data['nombre_empresa'] ?? '')) ?: $firstName;
                        $rif = strtoupper(trim((string) ($data['rif'] ?? '')));
                        $orgData['rif'] = $rif ?: null;
                    }
                    $db->table('organismos')->where('id', $organization['id'])->update($orgData);
                }
            }

            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo guardar el perfil.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            if ($exception instanceof \InvalidArgumentException) {
                return $this->json(['ok' => false, 'error' => $exception->getMessage()], 422);
            }
            log_message('error', 'Actualización de perfil fallida: {message}', ['message' => $exception->getMessage()]);
            return $this->json(['ok' => false, 'error' => 'No se pudo guardar el perfil. Revisa los datos e inténtalo de nuevo.'], 422);
        }

        $sessionUser = $this->currentUser();
        if ($sessionUser) {
            $sessionUser['nombre'] = $firstName;
            $sessionUser['apellido'] = $lastName;
            session()->set('user', $sessionUser);
        }

        return $this->json(['ok' => true]);
    }

    public function requestUniversityChange(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'estudiante') {
            return $this->json(['ok' => false, 'error' => 'Solo los estudiantes pueden solicitar cambio de universidad.'], 403);
        }

        $db = db_connect();
        $student = $db->table('perfil_estudiantes')->where('usuario_id', $user['id'])->get()->getRowArray();
        if (! $student || empty($student['organismo_id'])) {
            return $this->json(['ok' => false, 'error' => 'Aún no tienes una universidad asignada.'], 422);
        }
        if ((int) ($student['cambio_universidad_habilitado'] ?? 0) === 1) {
            return $this->json(['ok' => true, 'message' => 'Tu cambio de universidad ya está habilitado.']);
        }

        $db->table('perfil_estudiantes')->where('id', $student['id'])->update(['cambio_universidad_solicitado' => 1]);

        $coordinators = $db->table('participante_organismo')->select('usuario_id')
            ->where(['organismo_id' => $student['organismo_id'], 'activo' => 1])->get()->getResultArray();
        $studentName = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '')) ?: $user['correo'];
        foreach ($coordinators as $coord) {
            $db->table('notificaciones')->insert([
                'usuario_id'   => $coord['usuario_id'],
                'tipo'         => 'warning',
                'icono'        => 'fa-exchange-alt',
                'titulo'       => 'Solicitud de cambio de universidad',
                'descripcion'  => $studentName . ' solicitó habilitar el cambio de universidad desde su perfil.',
                'url_accion'   => base_url('dashboard'),
                'entidad_tipo' => 'sistema',
            ]);
        }

        return $this->json(['ok' => true, 'message' => 'Solicitud enviada a tu universidad actual para habilitar el cambio.']);
    }

    public function uploadGradesDocument(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'estudiante') {
            return $this->json(['ok' => false, 'error' => 'Solo los estudiantes pueden subir constancias de notas.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $fileData = (string) ($data['document'] ?? '');
        $savedUrl = $this->saveDocumentBase64($fileData, 'grades');
        if ($savedUrl === null) {
            return $this->json(['ok' => false, 'error' => 'Archivo inválido. Sube un PDF o imagen (JPG/PNG/WEBP) de máximo 5 MB.'], 422);
        }

        $db = db_connect();
        $student = $db->table('perfil_estudiantes')->where('usuario_id', $user['id'])->get()->getRowArray();
        if ($student) {
            $db->table('perfil_estudiantes')->where('id', $student['id'])->update(['constancia_notas_url' => $savedUrl]);
        }

        return $this->json(['ok' => true, 'url' => $savedUrl]);
    }

    public function uploadVerificationDocument(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'participante_empresa') {
            return $this->json(['ok' => false, 'error' => 'Solo las empresas pueden enviar documentos de verificación.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $fileData = (string) ($data['document'] ?? '');
        $savedUrl = $this->saveDocumentBase64($fileData, 'verifications');
        if ($savedUrl === null) {
            return $this->json(['ok' => false, 'error' => 'Archivo inválido. Sube un PDF o imagen (JPG/PNG/WEBP) de máximo 5 MB.'], 422);
        }

        $db = db_connect();
        $org = $db->table('participante_organismo po')
            ->select('org.id, org.nombre')
            ->join('organismos org', 'org.id = po.organismo_id')
            ->where(['po.usuario_id' => $user['id'], 'po.activo' => 1, 'org.tipo' => 'empresa'])
            ->orderBy('po.id')->get()->getRowArray();
        if (! $org) {
            return $this->json(['ok' => false, 'error' => 'No tienes una empresa activa asociada.'], 404);
        }

        $db->table('organismos')->where('id', $org['id'])->update([
            'documento_verificacion_url' => $savedUrl,
            'estado_verificacion'        => 'pendiente',
        ]);

        
        $admins = $db->table('usuarios')->select('id')->where(['rol' => 'admin', 'estado' => 'activo'])->get()->getResultArray();
        foreach ($admins as $adm) {
            $db->table('notificaciones')->insert([
                'usuario_id'   => $adm['id'],
                'tipo'         => 'info',
                'icono'        => 'fa-building',
                'titulo'       => 'Nueva solicitud de verificación de empresa',
                'descripcion'  => 'La empresa "' . $org['nombre'] . '" envió un documento para verificación oficial.',
                'url_accion'   => base_url('dashboard'),
                'entidad_tipo' => 'sistema',
            ]);
        }

        return $this->json(['ok' => true, 'url' => $savedUrl, 'estado_verificacion' => 'pendiente']);
    }

    public function avatar(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Los administradores no tienen perfil.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $avatar = (string) ($data['avatar'] ?? '');
        if (! preg_match('#^data:image/(jpeg|png|gif|webp);base64,(.+)$#', $avatar, $matches)) {
            return $this->json(['ok' => false, 'error' => 'El archivo de imagen no es válido.'], 422);
        }
        $contents = base64_decode($matches[2], true);
        if ($contents === false || strlen($contents) > 2 * 1024 * 1024) {
            return $this->json(['ok' => false, 'error' => 'La imagen no puede superar 2 MB.'], 422);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        if (! isset($extensions[$mime])) {
            return $this->json(['ok' => false, 'error' => 'Formato de imagen no permitido.'], 422);
        }

        $directory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return $this->json(['ok' => false, 'error' => 'No se pudo crear el directorio de imágenes.'], 500);
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $contents) === false) {
            return $this->json(['ok' => false, 'error' => 'No se pudo guardar la imagen.'], 500);
        }

        $url = base_url('uploads/profiles/' . $filename);
        $db = db_connect();
        if ($user['rol'] === 'participante_empresa' || $user['rol'] === 'participante_universidad') {
            $organization = $db->table('participante_organismo')->select('organismo_id')
                ->where('usuario_id', $user['id'])->where('activo', 1)->orderBy('id')->get()->getRowArray();
            if ($organization) {
                $db->table('organismos')->where('id', $organization['organismo_id'])->update(['logo' => $url]);
            }
            $db->table('usuarios')->where('id', $user['id'])->update(['avatar' => $url]);
        } else {
            $db->table('usuarios')->where('id', $user['id'])->update(['avatar' => $url]);
        }

        $sessionUser = $this->currentUser();
        if ($sessionUser) {
            $sessionUser['avatar'] = $url;
            session()->set('user', $sessionUser);
        }

        return $this->json(['ok' => true, 'url' => $url]);
    }

    public function changePassword(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Operación no permitida para administradores desde perfil.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $currentPassword = (string) ($data['current_password'] ?? '');
        $newPassword = (string) ($data['new_password'] ?? '');
        $confirmPassword = (string) ($data['confirm_password'] ?? '');

        if ($currentPassword === '' || strlen($newPassword) < 6 || strlen($newPassword) > 72) {
            return $this->json(['ok' => false, 'error' => 'Ingresa tu contraseña actual y una nueva contraseña de 6 a 72 caracteres.'], 422);
        }
        if ($confirmPassword !== '' && $newPassword !== $confirmPassword) {
            return $this->json(['ok' => false, 'error' => 'La nueva contraseña y su confirmación no coinciden.'], 422);
        }

        $db = db_connect();
        $account = $db->table('usuarios')->select('id, password_hash')
            ->where('id', $user['id'])->where('estado', 'activo')->get()->getRowArray();
        if (! $account || ! password_verify($currentPassword, $account['password_hash'])) {
            return $this->json(['ok' => false, 'error' => 'La contraseña actual es incorrecta.'], 403);
        }

        $db->table('usuarios')->where('id', $user['id'])->update([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return $this->json(['ok' => true, 'message' => 'Contraseña actualizada correctamente.']);
    }

    public function deleteAccount(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'No se puede eliminar una cuenta de administrador.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $password = (string) ($data['password'] ?? '');
        if ($password === '') {
            return $this->json(['ok' => false, 'error' => 'Debes confirmar tu contraseña para eliminar la cuenta.'], 422);
        }

        $db = db_connect();
        $account = $db->table('usuarios')->select('id, password_hash')
            ->where('id', $user['id'])->where('estado', 'activo')->get()->getRowArray();
        if (! $account || ! password_verify($password, $account['password_hash'])) {
            return $this->json(['ok' => false, 'error' => 'Contraseña incorrecta.'], 403);
        }

        $db->transBegin();
        try {
            $db->table('ofertas')->where('publicado_por', $user['id'])->update(['estado' => 'cerrada']);
            $db->table('participante_organismo')->where('usuario_id', $user['id'])->update(['activo' => 0]);

            $hasRestrictedRefs = $db->table('organismos')->where('creado_por', $user['id'])->countAllResults() > 0
                || $db->table('ofertas')->where('publicado_por', $user['id'])->countAllResults() > 0;

            if ($hasRestrictedRefs) {
                $deletedEmail = 'deleted_' . $user['id'] . '_' . time() . '@ineova.local';
                $db->table('usuarios')->where('id', $user['id'])->update([
                    'correo'        => $deletedEmail,
                    'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'cedula'        => null,
                    'telefono'      => null,
                    'avatar'        => null,
                    'estado'        => 'inactivo',
                ]);
            } else {
                $db->table('usuarios')->where('id', $user['id'])->delete();
            }

            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo eliminar la cuenta.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Eliminación de cuenta fallida: {message}', ['message' => $exception->getMessage()]);
            return $this->json(['ok' => false, 'error' => 'No se pudo eliminar la cuenta en este momento.'], 500);
        }

        session()->destroy();
        return $this->json(['ok' => true, 'message' => 'Tu cuenta ha sido eliminada.']);
    }

    private function saveDocumentBase64(string $dataUrl, string $subfolder): ?string
    {
        if (! preg_match('#^data:(application/pdf|image/(?:jpeg|png|webp));base64,(.+)$#', $dataUrl, $matches)) {
            return null;
        }
        $contents = base64_decode($matches[2], true);
        if ($contents === false || strlen($contents) > 5 * 1024 * 1024) {
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        $extensions = [
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
        ];
        if (! isset($extensions[$mime])) {
            return null;
        }

        $directory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $subfolder;
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return null;
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $contents) === false) {
            return null;
        }

        return base_url('uploads/' . $subfolder . '/' . $filename);
    }

    private function buildProfilePayload($db, array $account, bool $includePrivate): array
    {
        $profile = [
            'id'          => (int) $account['id'],
            'nombre'      => $account['nombre'] ?? '',
            'apellido'    => $account['apellido'] ?? '',
            'correo'      => $account['correo'],
            'telefono'    => $account['telefono'] ?? '',
            'avatar'      => $account['avatar'] ?? '',
            'role'        => $account['rol'],
            'habilidades' => [],
        ];
        if ($includePrivate) {
            $profile['cedula'] = $account['cedula'] ?? '';
        }

        if ($account['rol'] === 'estudiante') {
            $student = $db->table('perfil_estudiantes')->where('usuario_id', $account['id'])->get()->getRowArray();
            $profile['universidad_id'] = '';
            $profile['universidad_nombre'] = '';
            $profile['universidad_siglas'] = '';
            $profile['autobiografia'] = '';
            $profile['github_url'] = '';
            $profile['estado_vigencia'] = 'pendiente';
            $profile['promedio'] = null;
            $profile['notas_certificadas'] = false;
            $profile['constancia_notas_url'] = '';
            $profile['cambio_universidad_solicitado'] = false;
            $profile['cambio_universidad_habilitado'] = false;

            if ($student) {
                $profile['universidad_id'] = $student['organismo_id'] ? (int) $student['organismo_id'] : '';
                $profile['autobiografia'] = $student['autobiografia'] ?? '';
                $profile['github_url'] = $student['github_url'] ?? '';
                $profile['estado_vigencia'] = $student['estado_vigencia'] ?? 'pendiente';
                $profile['promedio'] = $student['promedio'] ?? null;
                $profile['notas_certificadas'] = (bool) ($student['notas_certificadas'] ?? false);
                $profile['constancia_notas_url'] = $student['constancia_notas_url'] ?? '';
                $profile['cambio_universidad_solicitado'] = (bool) ($student['cambio_universidad_solicitado'] ?? false);
                $profile['cambio_universidad_habilitado'] = (bool) ($student['cambio_universidad_habilitado'] ?? false);
                if (! empty($student['organismo_id'])) {
                    $uni = $db->table('organismos')->select('nombre, siglas')
                        ->where('id', $student['organismo_id'])->get()->getRowArray();
                    if ($uni) {
                        $profile['universidad_nombre'] = $uni['nombre'] ?? '';
                        $profile['universidad_siglas'] = $uni['siglas'] ?? '';
                    }
                }
                $profile['habilidades'] = array_column($db->table('estudiante_habilidad eh')
                    ->select('h.nombre')->join('habilidades h', 'h.id = eh.habilidad_id')
                    ->where('eh.estudiante_id', $student['id'])->get()->getResultArray(), 'nombre');
            }

            $profile['stats'] = [
                'aplicaciones' => $db->table('aplicaciones')->where('usuario_id', $account['id'])->countAllResults(),
                'guardadas'    => $db->table('ofertas_guardadas')->where('usuario_id', $account['id'])->countAllResults(),
            ];
        } elseif ($account['rol'] === 'participante_empresa') {
            $organization = $db->table('participante_organismo po')
                ->select('org.*, po.rol_en_organismo')->join('organismos org', 'org.id = po.organismo_id')
                ->where('po.usuario_id', $account['id'])->where('po.activo', 1)
                ->orderBy('po.id')->get()->getRowArray();
            if ($organization) {
                $profile['organismo_id'] = (int) $organization['id'];
                $profile['organismo_tipo'] = 'empresa';
                $profile['nombre_empresa'] = $organization['nombre'];
                $profile['rif'] = $organization['rif'] ?? '';
                $profile['sitio_web'] = $organization['sitio_web'] ?? '';
                $profile['ubicacion'] = $organization['ubicacion'] ?? '';
                $profile['descripcion'] = $organization['descripcion'] ?? '';
                $profile['logo'] = $organization['logo'] ?? '';
                $profile['verificado'] = (bool) ($organization['verificado'] ?? false);
                $profile['estado_verificacion'] = $organization['estado_verificacion'] ?? 'no_enviado';
                $profile['documento_verificacion_url'] = $organization['documento_verificacion_url'] ?? '';
            }
            $recibidas = $db->table('aplicaciones a')
                ->join('ofertas o', 'o.id = a.oferta_id')
                ->where('o.publicado_por', $account['id'])->countAllResults();
            $convenios = ! empty($profile['organismo_id']) && $db->tableExists('convenios')
                ? $db->table('convenios')->where(['empresa_id' => $profile['organismo_id'], 'estado' => 'activo'])->countAllResults()
                : 0;
            $publicadas = $db->table('ofertas')->where('publicado_por', $account['id'])->where('estado', 'activa')->countAllResults();
            $profile['stats'] = [
                'publicadas' => $publicadas,
                'recibidas'  => $recibidas,
                'convenios'  => $convenios,
            ];

            if ($organization && $includePrivate) {
                $planId = (int) ($organization['plan_id'] ?? 1);
                $planRow = $db->table('planes_suscripcion')->where('id', $planId)->get()->getRowArray();
                $trialEnd = $organization['trial_fin'] ?? date('Y-m-d H:i:s', strtotime(($organization['fecha_registro'] ?? 'now') . ' +7 days'));
                $secondsLeft = strtotime($trialEnd) - time();
                $trialActive = $planId === 1 && $secondsLeft > 0;
                $daysLeft = $trialActive ? (int) ceil($secondsLeft / 86400) : 0;
                $orgOffersCount = $db->table('ofertas')->where(['organismo_id' => $organization['id'], 'estado' => 'activa'])->countAllResults();
                $limitOffers = (int) ($planRow['limite_ofertas'] ?? 0);

                $canPublish = true;
                $blockReason = null;
                if ($planId === 1 && ! $trialActive) {
                    $canPublish = false;
                    $blockReason = 'Tu período de prueba gratuito de 7 días ha finalizado. Elige el Plan Estándar ($29.99) o Extra ($79.99) y solicita a un administrador su activación.';
                } elseif ($planId === 2 && $limitOffers > 0 && $orgOffersCount >= $limitOffers) {
                    $canPublish = false;
                    $blockReason = 'Alcanzaste el máximo de ' . $limitOffers . ' publicaciones del Plan Estándar. Actualiza al Plan Extra ($79.99) con un administrador.';
                }

                $availablePlans = $db->table('planes_suscripcion')
                    ->select('id, nombre, precio, limite_ofertas, destaca_ofertas, descripcion')
                    ->whereIn('id', [2, 3])
                    ->orderBy('id', 'ASC')
                    ->get()->getResultArray();

                $profile['plan'] = [
                    'id'                   => $planId,
                    'nombre'               => $planRow['nombre'] ?? 'Prueba Gratis (7 días)',
                    'precio'               => (float) ($planRow['precio'] ?? 0),
                    'limite_ofertas'       => $limitOffers,
                    'destaca_ofertas'      => (bool) ($planRow['destaca_ofertas'] ?? false),
                    'trial_fin'            => $trialEnd,
                    'trial_activo'         => $trialActive,
                    'trial_dias_restantes' => $daysLeft,
                    'ofertas_publicadas'   => $orgOffersCount,
                    'puede_publicar'       => $canPublish,
                    'motivo_bloqueo'       => $blockReason,
                    'planes_disponibles'   => $availablePlans,
                ];
            }
        } elseif ($account['rol'] === 'participante_universidad') {
            $organization = $db->table('participante_organismo po')
                ->select('org.*, po.rol_en_organismo')->join('organismos org', 'org.id = po.organismo_id')
                ->where('po.usuario_id', $account['id'])->where('po.activo', 1)
                ->orderBy('po.id')->get()->getRowArray();
            if ($organization) {
                $profile['organismo_id'] = (int) $organization['id'];
                $profile['organismo_tipo'] = 'universidad';
                $profile['nombre_empresa'] = $organization['nombre'];
                $profile['siglas'] = $organization['siglas'] ?? '';
                $profile['ranking_uni'] = $organization['ranking_uni'] ?? '';
                $profile['sitio_web'] = $organization['sitio_web'] ?? '';
                $profile['ubicacion'] = $organization['ubicacion'] ?? '';
                $profile['descripcion'] = $organization['descripcion'] ?? '';
                $profile['logo'] = $organization['logo'] ?? '';
                $profile['verificado'] = true;
            }
            $orgId = $profile['organismo_id'] ?? 0;
            $vigentes = $orgId ? $db->table('perfil_estudiantes')->where(['organismo_id' => $orgId, 'estado_vigencia' => 'vigente'])->countAllResults() : 0;
            $pendientes = $orgId ? $db->table('perfil_estudiantes')->where(['organismo_id' => $orgId, 'estado_vigencia' => 'pendiente'])->countAllResults() : 0;
            $convenios = ($orgId && $db->tableExists('convenios'))
                ? $db->table('convenios')->where(['universidad_id' => $orgId, 'estado' => 'activo'])->countAllResults()
                : 0;
            $profile['stats'] = [
                'estudiantes_vigentes'   => $vigentes,
                'estudiantes_pendientes' => $pendientes,
                'convenios'              => $convenios,
            ];
        } else {
            
            $profile['stats'] = [
                'coordinadores_pendientes' => $db->table('participante_organismo po')
                    ->join('usuarios u', 'u.id = po.usuario_id')
                    ->where(['u.rol' => 'participante_universidad', 'po.activo' => 0])->countAllResults(),
                'empresas_pendientes' => $db->table('organismos')
                    ->where(['tipo' => 'empresa', 'estado_verificacion' => 'pendiente'])->countAllResults(),
                'convenios' => $db->tableExists('convenios') ? $db->table('convenios')->where('estado', 'activo')->countAllResults() : 0,
            ];
        }

        return $profile;
    }

    private function saveStudentSkills(int $studentId, mixed $skills): void
    {
        $db = db_connect();
        $db->table('estudiante_habilidad')->where('estudiante_id', $studentId)->delete();
        if (! is_array($skills)) {
            return;
        }

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
            $db->table('estudiante_habilidad')->insert(['estudiante_id' => $studentId, 'habilidad_id' => $skillId]);
        }
    }
}
