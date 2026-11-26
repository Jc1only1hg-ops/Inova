<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UniversityController extends BaseController
{
    public function students(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'participante_universidad') {
            return $this->json(['ok' => false, 'error' => 'Solo los coordinadores universitarios pueden gestionar estudiantes.'], 403);
        }

        $db = db_connect();
        $uni = $this->getCoordinatorUniversity($db, (int) $user['id']);
        if (! $uni) {
            return $this->json(['ok' => false, 'error' => 'No tienes una universidad asignada activa.'], 403);
        }

        $rows = $db->table('perfil_estudiantes pe')
            ->select('pe.id AS perfil_id, pe.usuario_id, pe.autobiografia, pe.github_url, pe.estado_vigencia, pe.promedio, pe.notas_certificadas, pe.constancia_notas_url, pe.cambio_universidad_solicitado, pe.cambio_universidad_habilitado, u.nombre, u.apellido, u.correo, u.cedula, u.telefono, u.avatar')
            ->join('usuarios u', 'u.id = pe.usuario_id')
            ->where('pe.organismo_id', $uni['id'])
            ->where('u.estado', 'activo')
            ->orderBy('pe.cambio_universidad_solicitado', 'DESC')
            ->orderBy('FIELD(pe.estado_vigencia, "pendiente", "vigente", "rechazado")', '', false)
            ->orderBy('u.nombre', 'ASC')
            ->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['notas_certificadas'] = (bool) ($row['notas_certificadas'] ?? false);
            $row['cambio_universidad_solicitado'] = (bool) ($row['cambio_universidad_solicitado'] ?? false);
            $row['cambio_universidad_habilitado'] = (bool) ($row['cambio_universidad_habilitado'] ?? false);
            $row['habilidades'] = array_column($db->table('estudiante_habilidad eh')
                ->select('h.nombre')->join('habilidades h', 'h.id = eh.habilidad_id')
                ->where('eh.estudiante_id', $row['perfil_id'])->get()->getResultArray(), 'nombre');
        }

        return $this->json([
            'ok'         => true,
            'university' => $uni,
            'students'   => $rows,
        ]);
    }

    public function updateStudent(int $studentUserId): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] !== 'participante_universidad') {
            return $this->json(['ok' => false, 'error' => 'Solo los coordinadores universitarios pueden actualizar estudiantes.'], 403);
        }

        $db = db_connect();
        $uni = $this->getCoordinatorUniversity($db, (int) $user['id']);
        if (! $uni) {
            return $this->json(['ok' => false, 'error' => 'No tienes una universidad asignada activa.'], 403);
        }

        $profile = $db->table('perfil_estudiantes')
            ->where(['usuario_id' => $studentUserId, 'organismo_id' => $uni['id']])
            ->get()->getRowArray();
        if (! $profile) {
            return $this->json(['ok' => false, 'error' => 'Ese estudiante no está asociado a tu universidad.'], 404);
        }

        $data = $this->request->getJSON(true) ?? [];
        $update = [];
        $notifParts = [];

        if (isset($data['estado_vigencia']) && in_array($data['estado_vigencia'], ['pendiente', 'vigente', 'rechazado'], true)) {
            $update['estado_vigencia'] = $data['estado_vigencia'];
            $update['verificado'] = $data['estado_vigencia'] === 'vigente' ? 1 : 0;
            $notifParts[] = 'vigencia: ' . $data['estado_vigencia'];
        }

        if (array_key_exists('promedio', $data)) {
            $rawPromedio = trim((string) $data['promedio']);
            if ($rawPromedio === '') {
                $update['promedio'] = null;
            } elseif (is_numeric($rawPromedio) && (float) $rawPromedio >= 0 && (float) $rawPromedio <= 20) {
                $update['promedio'] = round((float) $rawPromedio, 2);
                $notifParts[] = 'promedio: ' . $update['promedio'];
            } else {
                return $this->json(['ok' => false, 'error' => 'El promedio debe ser un número entre 0 y 20.'], 422);
            }
        }

        if (array_key_exists('notas_certificadas', $data)) {
            $cert = (bool) $data['notas_certificadas'];
            $update['notas_certificadas'] = $cert ? 1 : 0;
            if ($cert) {
                $notifParts[] = 'notas certificadas ✓';
            }
        }

        if (array_key_exists('cambio_universidad_habilitado', $data)) {
            $enabled = (bool) $data['cambio_universidad_habilitado'];
            $update['cambio_universidad_habilitado'] = $enabled ? 1 : 0;
            if ($enabled) {
                $update['cambio_universidad_solicitado'] = 0;
                $notifParts[] = 'cambio de universidad habilitado';
            }
        }

        if ($update === []) {
            return $this->json(['ok' => false, 'error' => 'No se enviaron cambios válidos.'], 422);
        }

        $db->table('perfil_estudiantes')->where('id', $profile['id'])->update($update);

        if ($notifParts !== []) {
            $db->table('notificaciones')->insert([
                'usuario_id'   => $studentUserId,
                'tipo'         => 'success',
                'icono'        => 'fa-university',
                'titulo'       => 'Actualización de tu universidad (' . ($uni['siglas'] ?: $uni['nombre']) . ')',
                'descripcion'  => 'Tu universidad actualizó tu ficha académica: ' . implode(', ', $notifParts) . '.',
                'url_accion'   => base_url('perfil'),
                'entidad_tipo' => 'sistema',
            ]);
        }

        return $this->json(['ok' => true]);
    }

    private function getCoordinatorUniversity($db, int $userId): ?array
    {
        $row = $db->table('participante_organismo po')
            ->select('org.id, org.nombre, org.siglas, org.ubicacion')
            ->join('organismos org', 'org.id = po.organismo_id')
            ->where(['po.usuario_id' => $userId, 'po.activo' => 1, 'org.tipo' => 'universidad'])
            ->orderBy('po.id')
            ->get()->getRowArray();

        return $row ?: null;
    }
}
