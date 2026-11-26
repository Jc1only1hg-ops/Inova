<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfertaHabilidadesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'oferta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'habilidad_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['oferta_id', 'habilidad_id'], 'unico_par');
        $this->forge->addKey('habilidad_id', false, false, 'idx_habilidad');

        $this->forge->addForeignKey('oferta_id', 'ofertas', 'id', 'CASCADE', 'CASCADE', 'fk_oh_oferta');
        $this->forge->addForeignKey('habilidad_id', 'habilidades', 'id', 'CASCADE', 'CASCADE', 'fk_oh_habilidad');

        $this->forge->createTable('oferta_habilidades', true);
    }

    public function down()
    {
        $this->forge->dropTable('oferta_habilidades', true);
    }
}
