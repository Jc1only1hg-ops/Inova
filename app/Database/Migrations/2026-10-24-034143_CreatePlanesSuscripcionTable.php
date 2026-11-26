<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanesSuscripcionTable extends Migration
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
                'constraint' => 50,
            ],
            'precio' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'limite_busquedas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'limite_ofertas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('nombre', 'nombre_unico');
        $this->forge->createTable('planes_suscripcion', true);
    }

    public function down()
    {
        $this->forge->dropTable('planes_suscripcion', true);
    }
}
