<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ConveniosController extends BaseController
{
    public function index(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }

        $db = db_connect();
        $builder = $db->table('convenios c')
            ->select('c.*, emp.nombre AS empresa_nombre, emp.rif AS empresa_rif, emp.verificado AS empresa_verificada, uni.nombre AS universidad_nombre, uni.siglas AS universidad_siglas, u.rol AS solicitante_rol')
            ->join('organismos emp', 'emp.id = c.empresa_id')
            ->join('organismos uni', 'uni.id = c.universidad_id')
            ->join('usuarios u', 'u.id = c.solicitado_por')
            ->orderBy('c.fecha_creacion', 'DESC');

        $myOrg = null;
        if (in_array($user['rol'], ['participante_empresa', 'participante_universidad'], true)) {
            $myOrg = $db->table('participante_organismo po')
                ->select('org.id, org.tipo, org.nombre')
                ->join('organismos org', 'org.id = po.organismo_id')
                ->where(['po.usuario_id' => $user['id'], 'po.activo' => 1])
                ->orderBy('po.id')->get()->getRowArray();
            if ($myOrg) {
                $col = $myOrg['tipo'] === 'empresa' ? 'c.empresa_id' : 'c.universidad_id';
                $builder->where($col, $myOrg['id']);
            } else {
                $builder->where('1 = 0', null, false);
            }
        } elseif ($user['rol'] === 'estudiante') {
            $student = $db->table('perfil_estudiantes')->select('organismo_id')
                ->where('usuario_id', $user['id'])->get()->getRowArray();
            if ($student && ! empty($student['organismo_id'])) {
                $builder->where('c.universidad_id', (int) $student['organismo_id'])
                        ->where('c.estado', 'activo');
            } else {
                $builder->where('1 = 0', null, false);
            }
        }

        $convenios = $builder->get()->getResultArray();

        
        $partners = [];
        if ($myOrg) {
            $targetType = $myOrg['tipo'] === 'empresa' ? 'universidad' : 'empresa';
            $partners = $db->table('organismos')
                ->select('id, nombre, siglas, rif, verificado')
                ->where('tipo', $targetType)
                ->orderBy('nombre')->get()->getResultArray();
        }

        return $this->json([
            'ok'        => true,
            'myOrg'     => $myOrg,
            'convenios' => $convenios,
            'partners'  => $partners,
        ]);
    }

    public function create(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if (! in_array($user['rol'], ['participante_empresa', 'participante_universidad'], true)) {
            return $this->json(['ok' => false, 'error' => 'Solo empresas y universidades pueden proponer convenios.'], 403);
        }

        $db = db_connect();
        $myOrg = $db->table('participante_organismo po')
            ->select('org.id, org.tipo, org.nombre')
            ->join('organismos org', 'org.id = po.organismo_id')
            ->where(['po.usuario_id' => $user['id'], 'po.activo' => 1])
            ->orderBy('po.id')->get()->getRowArray();
        if (! $myOrg) {
            return $this->json(['ok' => false, 'error' => 'No tienes un organismo activo asociado.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $partnerId = (int) ($data['partner_id'] ?? 0);
        $title = trim((string) ($data['titulo'] ?? ''));
        $description = trim((string) ($data['descripcion'] ?? ''));
        $credits = max(0, min(1000, (int) ($data['creditos_academicos'] ?? 0)));

        if ($partnerId <= 0 || $title === '' || mb_strlen($title) > 180) {
            return $this->json(['ok' => false, 'error' => 'Completa el título y selecciona la institución aliada.'], 422);
        }

        $targetType = $myOrg['tipo'] === 'empresa' ? 'universidad' : 'empresa';
        $partner = $db->table('organismos')->where(['id' => $partnerId, 'tipo' => $targetType])->get()->getRowArray();
        if (! $partner) {
            return $this->json(['ok' => false, 'error' => 'La institución seleccionada no es válida.'], 404);
        }

        $empresaId = $myOrg['tipo'] === 'empresa' ? (int) $myOrg['id'] : $partnerId;
        $universidadId = $myOrg['tipo'] === 'universidad' ? (int) $myOrg['id'] : $partnerId;

        $db->table('convenios')->insert([
            'empresa_id'          => $empresaId,
            'universidad_id'      => $universidadId,
            'solicitado_por'      => $user['id'],
            'titulo'              => $title,
            'descripcion'         => $description ?: null,
            'creditos_academicos' => $credits,
            'estado'              => 'pendiente',
        ]);
        $convenioId = (int) $db->insertID();

        
        $counterparts = $db->table('participante_organismo')->select('usuario_id')
            ->where(['organismo_id' => $partnerId, 'activo' => 1])->get()->getResultArray();
        foreach ($counterparts as $cp) {
            $db->table('notificaciones')->insert([
                'usuario_id'   => $cp['usuario_id'],
                'tipo'         => 'info',
                'icono'        => 'fa-handshake',
                'titulo'       => 'Nueva propuesta de convenio',
                'descripcion'  => $myOrg['nombre'] . ' propuso el convenio "' . $title . '".',
                'url_accion'   => base_url('dashboard'),
                'entidad_tipo' => 'sistema',
            ]);
        }

        return $this->json(['ok' => true, 'id' => $convenioId], 201);
    }

    public function updateStatus(int $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }

        $data = $this->request->getJSON(true) ?? [];
        $status = (string) ($data['estado'] ?? '');
        if (! in_array($status, ['activo', 'rechazado'], true)) {
            return $this->json(['ok' => false, 'error' => 'Estado no válido.'], 422);
        }

        $db = db_connect();
        $convenio = $db->table('convenios')->where('id', $id)->get()->getRowArray();
        if (! $convenio) {
            return $this->json(['ok' => false, 'error' => 'Convenio no encontrado.'], 404);
        }

        if ($user['rol'] !== 'admin') {
            $requester = $db->table('usuarios')->select('rol')->where('id', $convenio['solicitado_por'])->get()->getRowArray();
            $reqRole = $requester['rol'] ?? '';
            $myOrg = $db->table('participante_organismo po')
                ->select('org.id, org.tipo')
                ->join('organismos org', 'org.id = po.organismo_id')
                ->where(['po.usuario_id' => $user['id'], 'po.activo' => 1])
                ->orderBy('po.id')->get()->getRowArray();
            $isCounterpart = $myOrg && (
                ($reqRole === 'participante_empresa' && $myOrg['tipo'] === 'universidad' && (int) $convenio['universidad_id'] === (int) $myOrg['id']) ||
                ($reqRole === 'participante_universidad' && $myOrg['tipo'] === 'empresa' && (int) $convenio['empresa_id'] === (int) $myOrg['id'])
            );
            if (! $isCounterpart) {
                return $this->json(['ok' => false, 'error' => 'Solo la institución contraparte o un administrador puede aprobar/rechazar este convenio.'], 403);
            }
        }

        $db->table('convenios')->where('id', $id)->update(['estado' => $status]);
        $db->table('notificaciones')->insert([
            'usuario_id'   => $convenio['solicitado_por'],
            'tipo'         => $status === 'activo' ? 'success' : 'warning',
            'icono'        => 'fa-handshake',
            'titulo'       => 'Convenio ' . ($status === 'activo' ? 'aprobado' : 'rechazado'),
            'descripcion'  => 'El convenio "' . $convenio['titulo'] . '" fue ' . ($status === 'activo' ? 'activado' : 'rechazado') . '.',
            'url_accion'   => base_url('dashboard'),
            'entidad_tipo' => 'sistema',
        ]);

        return $this->json(['ok' => true, 'estado' => $status]);
    }
}
