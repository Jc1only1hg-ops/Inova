<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificacionesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['success', 'warning', 'danger', 'info', 'purple'],
                'default'    => 'info',
            ],
            'icono' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'url_accion' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'entidad_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'entidad_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'entidad_uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
            ],
            'leido_en' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'fecha_creacion datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['usuario_id', 'fecha_creacion'], false, false, 'idx_usuario_fecha');
        $this->forge->addKey(['usuario_id', 'leido_en'], false, false, 'idx_no_leidas');
        $this->forge->addKey('entidad_uuid', false, false, 'idx_entidad_uuid');

        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_notif_usuario');

        $this->forge->createTable('notificaciones', true);
    }

    public function down()
    {
        $this->forge->dropTable('notificaciones', true);
    }
}
