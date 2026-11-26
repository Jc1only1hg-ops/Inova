<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerfilEstudiantesTable extends Migration
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
            'organismo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'autobiografia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'github_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'verificado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'fecha_registro datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('usuario_id', 'usuario_unico');
        $this->forge->addKey('organismo_id', false, false, 'idx_organismo');

        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_perfil_usuario');
        $this->forge->addForeignKey('organismo_id', 'organismos', 'id', 'RESTRICT', 'CASCADE', 'fk_perfil_organismo');

        $this->forge->createTable('perfil_estudiantes', true);
    }

    public function down()
    {
        $this->forge->dropTable('perfil_estudiantes', true);
    }
}
