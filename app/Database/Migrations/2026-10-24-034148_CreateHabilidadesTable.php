<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHabilidadesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tipo_habilidad' => [
                'type'       => 'ENUM',
                'constraint' => ['Técnica', 'Blanda'],
                'default'    => 'Técnica',
            ],
            'fecha_registro datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('nombre', 'nombre_unico');

        $this->forge->createTable('habilidades', true);
    }

    public function down()
    {
        $this->forge->dropTable('habilidades', true);
    }
}
