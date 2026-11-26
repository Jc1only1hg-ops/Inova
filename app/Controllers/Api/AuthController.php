<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function register(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? [];
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $role = (string) ($data['userType'] ?? '');
        $password = (string) ($data['password'] ?? '');
        $firstName = trim((string) ($data['nombre'] ?? ''));
        $lastName = trim((string) ($data['apellido'] ?? ''));
        $phone = preg_replace('/[\s-]+/', '', (string) ($data['telefono'] ?? ''));
        $identity = strtoupper(preg_replace('/[\s-]+/', '', (string) ($data['cedula'] ?? '')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            return $this->json(['ok' => false, 'error' => 'Ingresa un correo válido.'], 422);
        }
        if (! in_array($role, ['estudiante', 'participante_empresa', 'participante_universidad'], true)) {
            return $this->json(['ok' => false, 'error' => 'El tipo de cuenta no es válido.'], 422);
        }
        if ($firstName === '' || mb_strlen($firstName) > 100 || $lastName === '' || mb_strlen($lastName) > 100
            || strlen($password) < 6 || strlen($password) > 72) {
            return $this->json(['ok' => false, 'error' => 'Completa tus datos y usa una contraseña de 6 a 72 caracteres.'], 422);
        }
        if (! preg_match('/^[VE]\d{1,8}$/', $identity) || ! preg_match('/^04(?:12|14|16|22|24|26)\d{7}$/', $phone)) {
            return $this->json(['ok' => false, 'error' => 'La cédula (1 a 8 dígitos) o el teléfono no tienen un formato válido.'], 422);
        }

        $db = db_connect();
        $organizationName = trim((string) ($data['organismo_nombre'] ?? ''));
        $organizationRif = strtoupper(trim((string) ($data['organismo_rif'] ?? '')));
        $rifDigits = preg_replace('/\D+/', '', $organizationRif);
        $selectedUniversityId = (int) ($data['universidad_id'] ?? 0);

        if ($role === 'participante_empresa' && ($organizationName === '' || mb_strlen($organizationName) > 150
            || $organizationRif === '' || mb_strlen($organizationRif) > 20
            || strlen($rifDigits) < 1 || strlen($rifDigits) > 10)) {
            return $this->json(['ok' => false, 'error' => 'Indica el nombre y un RIF válido (máximo 10 dígitos numéricos).'], 422);
        }

        if ($role === 'participante_universidad') {
            $uniExists = $selectedUniversityId > 0 && $db->table('organismos')
                ->where(['id' => $selectedUniversityId, 'tipo' => 'universidad'])
                ->countAllResults() > 0;
            if (! $uniExists) {
                return $this->json(['ok' => false, 'error' => 'Selecciona una universidad oficial del catálogo.'], 422);
            }
        }

        if ($role === 'estudiante' && $selectedUniversityId > 0) {
            $uniExists = $db->table('organismos')
                ->where(['id' => $selectedUniversityId, 'tipo' => 'universidad'])
                ->countAllResults() > 0;
            if (! $uniExists) {
                return $this->json(['ok' => false, 'error' => 'La universidad seleccionada no es válida.'], 422);
            }
        }

        if ($db->table('usuarios')->where('correo', $email)->countAllResults() > 0) {
            return $this->json(['ok' => false, 'error' => 'Ese correo ya está registrado.'], 409);
        }

        $isPendingCoordinator = $role === 'participante_universidad';

        $db->transBegin();
        try {
            $db->table('usuarios')->insert([
                'correo'        => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'rol'           => $role,
                'nombre'        => $firstName,
                'apellido'      => $lastName,
                'cedula'        => substr($identity, 0, 1) . '-' . substr($identity, 1),
                'telefono'      => $phone,
                'estado'        => $isPendingCoordinator ? 'inactivo' : 'activo',
            ]);
            $userId = (int) $db->insertID();

            if ($role === 'estudiante') {
                $studentRow = [
                    'usuario_id'   => $userId,
                    'organismo_id' => $selectedUniversityId > 0 ? $selectedUniversityId : null,
                ];
                if ($db->fieldExists('estado_vigencia', 'perfil_estudiantes')) {
                    $studentRow['estado_vigencia'] = 'pendiente';
                }
                $db->table('perfil_estudiantes')->insert($studentRow);
            } elseif ($role === 'participante_universidad') {
                
                $db->table('participante_organismo')->insert([
                    'usuario_id'       => $userId,
                    'organismo_id'     => $selectedUniversityId,
                    'rol_en_organismo' => 'administrador',
                    'activo'           => 0,
                ]);
            } else {
                
                if ($db->tableExists('planes_suscripcion') && $db->table('planes_suscripcion')->where('id', 1)->countAllResults() === 0) {
                    $db->table('planes_suscripcion')->insert([
                        'id'               => 1,
                        'nombre'           => 'Básico',
                        'precio'           => 0.00,
                        'limite_busquedas' => 50,
                        'limite_ofertas'   => 10,
                    ]);
                }
                $organization = [
                    'tipo'       => 'empresa',
                    'creado_por' => $userId,
                    'nombre'     => $organizationName,
                    'rif'        => $organizationRif,
                    'plan_id'    => 1,
                ];
                if ($db->fieldExists('trial_fin', 'organismos')) {
                    $organization['trial_fin'] = date('Y-m-d H:i:s', strtotime('+7 days'));
                }
                $db->table('organismos')->insert($organization);
                $organizationId = (int) $db->insertID();
                $db->table('participante_organismo')->insert([
                    'usuario_id'       => $userId,
                    'organismo_id'     => $organizationId,
                    'rol_en_organismo' => 'dueño',
                    'activo'           => 1,
                ]);
            }

            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo guardar la cuenta.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Registro Ineova fallido: {message}', ['message' => $exception->getMessage()]);
            $databaseError = $db->error();
            if ((int) ($databaseError['code'] ?? 0) === 1062) {
                return $this->json(['ok' => false, 'error' => 'El correo, la cédula o el RIF de la empresa ya están registrados.'], 409);
            }
            return $this->json(['ok' => false, 'error' => 'No se pudo crear la cuenta por un error de base de datos.'], 500);
        }

        if ($isPendingCoordinator) {
            return $this->json([
                'ok'               => true,
                'pending_approval' => true,
                'message'          => 'Solicitud enviada. Un administrador de Ineova debe aprobar tu cuenta de coordinador universitario antes de iniciar sesión.',
            ], 201);
        }

        $user = $this->userData($userId);
        session()->regenerate(true);
        session()->set('user', $user);
        return $this->json(['ok' => true, 'user' => $user], 201);
    }

    public function login(): ResponseInterface
    {
        $data = $this->request->getJSON(true) ?? [];
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $user = db_connect()->table('usuarios')
            ->select('id, correo, password_hash, rol, nombre, apellido, avatar, estado')
            ->where('correo', $email)
            ->get()->getRowArray();

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return $this->json(['ok' => false, 'error' => 'Correo o contraseña incorrectos.'], 401);
        }

        if ($user['estado'] !== 'activo') {
            if ($user['rol'] === 'participante_universidad' && $user['estado'] === 'inactivo') {
                return $this->json([
                    'ok'    => false,
                    'error' => 'Tu cuenta de coordinador universitario está pendiente de aprobación por un administrador.',
                ], 403);
            }
            return $this->json(['ok' => false, 'error' => 'Tu cuenta no se encuentra activa.'], 403);
        }

        db_connect()->table('usuarios')->where('id', $user['id'])->update(['ultimo_login' => date('Y-m-d H:i:s')]);
        $safeUser = $this->userData((int) $user['id']);
        session()->regenerate(true);
        session()->set('user', $safeUser);
        return $this->json(['ok' => true, 'user' => $safeUser]);
    }

    public function me(): ResponseInterface
    {
        $sessionUser = $this->currentUser();
        if (! $sessionUser) {
            return $this->unauthorized();
        }

        $user = $this->userData((int) $sessionUser['id']);
        if (! $user) {
            session()->destroy();
            return $this->unauthorized();
        }

        session()->set('user', $user);
        return $this->json(['ok' => true, 'user' => $user]);
    }

    public function logout(): ResponseInterface
    {
        session()->destroy();
        return $this->json(['ok' => true]);
    }

    private function userData(int $id): ?array
    {
        $user = db_connect()->table('usuarios')
            ->select('id, correo, rol, nombre, apellido, avatar')
            ->where('id', $id)->where('estado', 'activo')->get()->getRowArray();

        return $user ?: null;
    }
}
