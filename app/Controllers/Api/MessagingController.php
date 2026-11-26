<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class MessagingController extends BaseController
{
    public function index(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => true, 'conversations' => []]);
        }

        $db = db_connect();
        $rows = $db->table('conversaciones')->groupStart()
            ->where('usuario1_id', $user['id'])->orWhere('usuario2_id', $user['id'])
            ->groupEnd()->orderBy('ultimo_mensaje_fecha', 'DESC')->get()->getResultArray();
        $conversations = [];
        foreach ($rows as $row) {
            $otherId = (int) ($row['usuario1_id'] == $user['id'] ? $row['usuario2_id'] : $row['usuario1_id']);
            $other = $db->table('usuarios')->select('id, correo, nombre, apellido, avatar, rol')
                ->where('id', $otherId)->get()->getRowArray();
            if (! $other) {
                continue;
            }
            $organization = $db->table('participante_organismo po')->select('org.nombre, org.logo, org.tipo')
                ->join('organismos org', 'org.id = po.organismo_id')->where('po.usuario_id', $otherId)
                ->where('po.activo', 1)->orderBy('po.id')->get()->getRowArray();
            $offer = $row['oferta_id'] ? $db->table('ofertas')->select('titulo')->where('id', $row['oferta_id'])->get()->getRowArray() : null;
            $unread = $db->table('mensajes')->where([
                'conversacion_id' => $row['id'], 'leido_en' => null, 'eliminado_en' => null,
            ])->where('remitente_id !=', $user['id'])->countAllResults();
            $conversations[] = [
                'id'                   => $row['id'],
                'tipo'                 => $row['tipo'],
                'estado'               => $row['estado'],
                'oferta_id'            => $row['oferta_id'],
                'oferta_titulo'        => $offer['titulo'] ?? null,
                'fecha_creacion'       => $row['fecha_creacion'],
                'ultimo_mensaje'       => $row['ultimo_mensaje'],
                'ultimo_mensaje_fecha' => $row['ultimo_mensaje_fecha'],
                'no_leidos'            => $unread,
                'otro_usuario'         => [
                    'id'     => $otherId,
                    'correo' => $other['correo'],
                    'nombre' => trim(($other['nombre'] ?? '') . ' ' . ($other['apellido'] ?? '')),
                    'avatar' => $organization['logo'] ?? $other['avatar'],
                    'tipo'   => $organization['tipo'] ?? $other['rol'],
                ],
            ];
        }

        return $this->json(['ok' => true, 'conversations' => $conversations]);
    }

    public function create(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }

        $data = $this->request->getJSON(true) ?? [];
        $offerId = isset($data['oferta_id']) && $data['oferta_id'] !== '' ? (int) $data['oferta_id'] : null;

        if ($user['rol'] !== 'participante_universidad' || $offerId === null) {
            return $this->json([
                'ok'    => false,
                'error' => 'El chat solo se habilita automáticamente cuando una empresa acepta una postulación o cuando una universidad recomienda un estudiante en una oferta.',
            ], 403);
        }

        $recipientId = (int) ($data['destinatario_id'] ?? 0);
        $body = trim((string) ($data['cuerpo'] ?? ''));
        $subject = trim((string) ($data['asunto'] ?? ''));
        if ($recipientId < 1 || $recipientId === (int) $user['id'] || $body === '' || strlen($body) > 5000) {
            return $this->json(['ok' => false, 'error' => 'Indica destinatario y mensaje válido.'], 422);
        }

        $db = db_connect();
        if ($db->table('usuarios')->where(['id' => $recipientId, 'estado' => 'activo'])->countAllResults() === 0) {
            return $this->json(['ok' => false, 'error' => 'El destinatario no está disponible.'], 404);
        }
        $offer = $db->table('ofertas')->select('organismo_id, publicado_por')
            ->where(['id' => $offerId, 'estado' => 'activa'])->get()->getRowArray();
        if (! $offer) {
            return $this->json(['ok' => false, 'error' => 'La oferta relacionada ya no está disponible.'], 404);
        }
        $isOfferParticipant = (int) $offer['publicado_por'] === $recipientId
            || $db->table('participante_organismo')->where([
                'usuario_id' => $recipientId, 'organismo_id' => $offer['organismo_id'], 'activo' => 1,
            ])->countAllResults() > 0;
        if (! $isOfferParticipant) {
            return $this->json(['ok' => false, 'error' => 'El destinatario no pertenece a la empresa de esa oferta.'], 403);
        }

        $firstId = min((int) $user['id'], $recipientId);
        $secondId = max((int) $user['id'], $recipientId);
        $conversation = $db->table('conversaciones')
            ->where(['usuario1_id' => $firstId, 'usuario2_id' => $secondId, 'oferta_id' => $offerId])
            ->get()->getRowArray();

        if (! $conversation) {
            $conversationId = $this->uuid();
            $db->table('conversaciones')->insert([
                'id'                   => $conversationId,
                'usuario1_id'          => $firstId,
                'usuario2_id'          => $secondId,
                'oferta_id'            => $offerId,
                'tipo'                 => 'consulta',
                'estado'               => 'activa',
                'ultimo_mensaje'       => $body,
                'ultimo_mensaje_fecha' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $conversationId = $conversation['id'];
            if ($conversation['estado'] === 'bloqueada') {
                return $this->json(['ok' => false, 'error' => 'No se puede enviar mensajes en esta conversación.'], 403);
            }
            if ($conversation['estado'] === 'archivada') {
                $db->table('conversaciones')->where('id', $conversationId)->update(['estado' => 'activa']);
            }
        }

        $this->insertMessage($conversationId, (int) $user['id'], $subject === '' ? $body : $subject . "\n" . $body);
        return $this->json(['ok' => true, 'conversation_id' => $conversationId], $conversation ? 200 : 201);
    }

    public function messages(string $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Los administradores no tienen conversaciones.'], 403);
        }
        if (! $this->conversationBelongsTo($id, (int) $user['id'])) {
            return $this->json(['ok' => false, 'error' => 'Conversación no encontrada.'], 404);
        }

        $db = db_connect();
        $db->table('mensajes')
            ->where(['conversacion_id' => $id, 'leido_en' => null])
            ->where('remitente_id !=', $user['id'])
            ->update(['leido_en' => date('Y-m-d H:i:s')]);

        $messages = $db->table('mensajes')
            ->select('id, conversacion_id, remitente_id, cuerpo, leido_en, fecha_envio')
            ->where(['conversacion_id' => $id, 'eliminado_en' => null])
            ->orderBy('fecha_envio', 'ASC')
            ->get()->getResultArray();

        return $this->json(['ok' => true, 'messages' => $messages]);
    }

    public function send(string $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Los administradores no tienen conversaciones.'], 403);
        }
        $conversation = $this->conversationBelongsTo($id, (int) $user['id']);
        if (! $conversation) {
            return $this->json(['ok' => false, 'error' => 'Conversación no encontrada.'], 404);
        }
        if ($conversation['estado'] === 'bloqueada') {
            return $this->json(['ok' => false, 'error' => 'La conversación está bloqueada.'], 403);
        }

        $data = $this->request->getJSON(true) ?? [];
        $body = trim((string) ($data['cuerpo'] ?? ''));
        if ($body === '' || strlen($body) > 5000) {
            return $this->json(['ok' => false, 'error' => 'Escribe un mensaje válido.'], 422);
        }

        $this->insertMessage($id, (int) $user['id'], $body);
        return $this->json(['ok' => true], 201);
    }

    public function status(string $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Los administradores no tienen conversaciones.'], 403);
        }
        if (! $this->conversationBelongsTo($id, (int) $user['id'])) {
            return $this->json(['ok' => false, 'error' => 'Conversación no encontrada.'], 404);
        }

        $data = $this->request->getJSON(true) ?? [];
        $status = (string) ($data['estado'] ?? '');
        if (! in_array($status, ['activa', 'archivada', 'bloqueada'], true)) {
            return $this->json(['ok' => false, 'error' => 'Estado no válido.'], 422);
        }

        db_connect()->table('conversaciones')->where('id', $id)->update(['estado' => $status]);
        return $this->json(['ok' => true]);
    }

    public function delete(string $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        if ($user['rol'] === 'admin') {
            return $this->json(['ok' => false, 'error' => 'Los administradores no tienen conversaciones.'], 403);
        }
        if (! $this->conversationBelongsTo($id, (int) $user['id'])) {
            return $this->json(['ok' => false, 'error' => 'Conversación no encontrada.'], 404);
        }

        db_connect()->table('conversaciones')->where('id', $id)->delete();
        return $this->json(['ok' => true]);
    }

    private function conversationBelongsTo(string $id, int $userId): ?array
    {
        $row = db_connect()->table('conversaciones')->where('id', $id)->groupStart()
            ->where('usuario1_id', $userId)->orWhere('usuario2_id', $userId)->groupEnd()
            ->get()->getRowArray();

        return $row ?: null;
    }

    private function insertMessage(string $conversationId, int $senderId, string $body): void
    {
        $db = db_connect();
        $db->transBegin();
        try {
            $db->table('mensajes')->insert([
                'id'              => $this->uuid(),
                'conversacion_id' => $conversationId,
                'remitente_id'    => $senderId,
                'cuerpo'          => $body,
            ]);
            $cleanPreview = trim((string) preg_replace('/\[\[STUDENT_RECOMMENDATION:[^\]]+\]\]/', '', $body));
            if ($cleanPreview === '') {
                $cleanPreview = 'Perfil de estudiante recomendado';
            }
            $conversation = $db->table('conversaciones')->select('usuario1_id, usuario2_id')
                ->where('id', $conversationId)->get()->getRowArray();
            if ($conversation) {
                $recipientId = (int) ($conversation['usuario1_id'] == $senderId ? $conversation['usuario2_id'] : $conversation['usuario1_id']);
                $db->table('notificaciones')->insert([
                    'usuario_id'   => $recipientId,
                    'tipo'         => 'info',
                    'icono'        => 'fa-comments',
                    'titulo'       => 'Nuevo mensaje',
                    'descripcion'  => mb_substr($cleanPreview, 0, 240),
                    'url_accion'   => base_url('dashboard') . '#mensajes',
                    'entidad_tipo' => 'mensaje',
                    'entidad_uuid' => $conversationId,
                ]);
            }
            if (! $db->transStatus()) {
                throw new \RuntimeException('No se pudo guardar el mensaje.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Envío de mensaje fallido: {message}', ['message' => $exception->getMessage()]);
            throw $exception;
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
