<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfertaImagenesTable extends Migration
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
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'alt_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'posicion' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'fecha_registro datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['oferta_id', 'posicion'], false, false, 'idx_oferta_pos');
        $this->forge->addForeignKey('oferta_id', 'ofertas', 'id', 'CASCADE', 'CASCADE', 'fk_imagenes_oferta');

        $this->forge->createTable('oferta_imagenes', true);
    }

    public function down()
    {
        $this->forge->dropTable('oferta_imagenes', true);
    }
}
