<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class NotificationsController extends BaseController
{
    public function index(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        $notifications = db_connect()->table('notificaciones')->where('usuario_id', $user['id'])
            ->orderBy('fecha_creacion', 'DESC')->get()->getResultArray();
        return $this->json(['ok' => true, 'notifications' => $notifications]);
    }

    public function read(int $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        $read = (bool) (($this->request->getJSON(true) ?? [])['read'] ?? true);
        $db = db_connect();
        $db->table('notificaciones')->where(['id' => $id, 'usuario_id' => $user['id']])
            ->update(['leido_en' => $read ? date('Y-m-d H:i:s') : null]);
        return $this->json(['ok' => true]);
    }

    public function readAll(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        db_connect()->table('notificaciones')->where('usuario_id', $user['id'])
            ->where('leido_en', null)->update(['leido_en' => date('Y-m-d H:i:s')]);
        return $this->json(['ok' => true]);
    }

    public function delete(int $id): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        db_connect()->table('notificaciones')->where(['id' => $id, 'usuario_id' => $user['id']])->delete();
        return $this->json(['ok' => true]);
    }

    public function clear(): ResponseInterface
    {
        $user = $this->currentUser();
        if (! $user) {
            return $this->unauthorized();
        }
        db_connect()->table('notificaciones')->where('usuario_id', $user['id'])->delete();
        return $this->json(['ok' => true]);
    }
}
