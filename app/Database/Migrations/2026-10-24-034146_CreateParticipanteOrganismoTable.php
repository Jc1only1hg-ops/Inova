<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateParticipanteOrganismoTable extends Migration
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
            ],
            'rol_en_organismo' => [
                'type'       => 'ENUM',
                'constraint' => ['dueño', 'editor', 'miembro'],
                'default'    => 'miembro',
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'fecha_adscripcion datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['usuario_id', 'organismo_id'], 'unico_par');

        $this->forge->addKey('organismo_id', false, false, 'idx_organismo');
        $this->forge->addKey('rol_en_organismo', false, false, 'idx_rol_org');

        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_po_usuario');
        $this->forge->addForeignKey('organismo_id', 'organismos', 'id', 'CASCADE', 'CASCADE', 'fk_po_organismo');

        $this->forge->createTable('participante_organismo', true);
    }

    public function down()
    {
        $this->forge->dropTable('participante_organismo', true);
    }
}
