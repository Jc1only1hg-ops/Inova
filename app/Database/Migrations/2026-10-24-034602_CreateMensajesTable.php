<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMensajesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'conversacion_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'remitente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'cuerpo' => [
                'type' => 'TEXT',
            ],
            'adjunto_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'adjunto_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'leido_en' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'editado_en' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'eliminado_en' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'fecha_envio datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['conversacion_id', 'fecha_envio'], false, false, 'idx_conversacion_fecha');
        $this->forge->addKey(['conversacion_id', 'leido_en'], false, false, 'idx_no_leidos');
        $this->forge->addKey('remitente_id', false, false, 'idx_remitente');

        $this->forge->addForeignKey('conversacion_id', 'conversaciones', 'id', 'CASCADE', 'CASCADE', 'fk_msg_conv');
        $this->forge->addForeignKey('remitente_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_msg_remitente');

        $this->forge->createTable('mensajes', true);
    }

    public function down()
    {
        $this->forge->dropTable('mensajes', true);
    }
}
