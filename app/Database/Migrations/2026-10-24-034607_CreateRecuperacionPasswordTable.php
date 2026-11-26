<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecuperacionPasswordTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'expira_en' => [
                'type' => 'DATETIME',
            ],
            'usado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'fecha_creacion datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('correo', false, false, 'idx_correo');
        $this->forge->addKey('codigo', false, false, 'idx_codigo');
        $this->forge->addKey(['correo', 'usado'], false, false, 'idx_correo_usado');

        $this->forge->createTable('recuperacion_password', true);
    }

    public function down()
    {
        $this->forge->dropTable('recuperacion_password', true);
    }
}
