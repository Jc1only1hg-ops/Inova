<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEstudianteHabilidadTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'estudiante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'habilidad_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['estudiante_id', 'habilidad_id'], 'unico_par');
        $this->forge->addKey('habilidad_id', false, false, 'idx_habilidad');

        $this->forge->addForeignKey('estudiante_id', 'perfil_estudiantes', 'id', 'CASCADE', 'CASCADE', 'fk_eh_estudiante');
        $this->forge->addForeignKey('habilidad_id', 'habilidades', 'id', 'CASCADE', 'CASCADE', 'fk_eh_habilidad');

        $this->forge->createTable('estudiante_habilidad', true);
    }

    public function down()
    {
        $this->forge->dropTable('estudiante_habilidad', true);
    }
}
