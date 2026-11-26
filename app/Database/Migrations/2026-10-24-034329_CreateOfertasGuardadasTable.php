<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfertasGuardadasTable extends Migration
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
            'oferta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'fecha_guardado datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['usuario_id', 'oferta_id'], 'unico_par');
        $this->forge->addKey('oferta_id', false, false, 'idx_oferta');

        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_guardadas_usuario');
        $this->forge->addForeignKey('oferta_id', 'ofertas', 'id', 'CASCADE', 'CASCADE', 'fk_guardadas_oferta');

        $this->forge->createTable('ofertas_guardadas', true);
    }

    public function down()
    {
        $this->forge->dropTable('ofertas_guardadas', true);
    }
}
