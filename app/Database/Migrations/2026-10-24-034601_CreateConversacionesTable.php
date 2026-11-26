<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConversacionesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'usuario1_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'usuario2_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['directa', 'consulta', 'aplicacion', 'sistema'],
                'default'    => 'directa',
            ],
            'oferta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activa', 'archivada', 'bloqueada'],
                'default'    => 'activa',
            ],
            'bloqueado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'ultimo_mensaje' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ultimo_mensaje_fecha' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'fecha_creacion datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['usuario1_id', 'usuario2_id', 'oferta_id'], 'unico_par_oferta');

        $this->forge->addKey('estado', false, false, 'idx_estado');
        $this->forge->addKey('ultimo_mensaje_fecha', false, false, 'idx_ultimo');
        $this->forge->addKey('usuario2_id', false, false, 'idx_u2');
        $this->forge->addKey('oferta_id', false, false, 'idx_oferta');
        $this->forge->addKey('bloqueado_por', false, false, 'idx_block');

        $this->forge->addForeignKey('usuario1_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_conv_u1');
        $this->forge->addForeignKey('usuario2_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_conv_u2');
        $this->forge->addForeignKey('oferta_id', 'ofertas', 'id', 'SET NULL', 'CASCADE', 'fk_conv_oferta');
        $this->forge->addForeignKey('bloqueado_por', 'usuarios', 'id', 'SET NULL', 'CASCADE', 'fk_conv_block');

        $this->forge->createTable('conversaciones', true);
    }

    public function down()
    {
        $this->forge->dropTable('conversaciones', true);
    }
}
