<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResenasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'organismo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'autor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'estudiante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'comentario' => [
                'type' => 'TEXT',
            ],
            'puntuacion' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 5,
            ],
            'fecha datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organismo_id', 'estudiante_id'], 'unico_par');

        $this->forge->addKey('organismo_id', false, false, 'idx_organismo');
        $this->forge->addKey('estudiante_id', false, false, 'idx_estudiante');
        $this->forge->addKey('autor_id', false, false, 'idx_autor');

        $this->forge->addForeignKey('organismo_id', 'organismos', 'id', 'CASCADE', 'CASCADE', 'fk_res_organismo');
        $this->forge->addForeignKey('autor_id', 'usuarios', 'id', 'RESTRICT', 'CASCADE', 'fk_res_autor');
        $this->forge->addForeignKey('estudiante_id', 'perfil_estudiantes', 'id', 'RESTRICT', 'CASCADE', 'fk_res_estudiante');

        $this->forge->createTable('resenas', true);
    }

    public function down()
    {
        $this->forge->dropTable('resenas', true);
    }
}
