<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSesionesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'payload' => [
                'type' => 'TEXT',
            ],
            'ultima_actividad' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('usuario_id', false, false, 'idx_usuario');
        $this->forge->addKey('ultima_actividad', false, false, 'idx_actividad');

        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_sesiones_usuario');

        $this->forge->createTable('sesiones', true);
    }

    public function down()
    {
        $this->forge->dropTable('sesiones', true);
    }
}
