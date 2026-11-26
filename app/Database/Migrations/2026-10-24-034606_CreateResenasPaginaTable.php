<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateResenasPaginaTable extends Migration
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
            'comentario' => [
                'type' => 'TEXT',
            ],
            'puntuacion' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 5,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'aprobada', 'rechazada'],
                'default'    => 'pendiente',
            ],
            'revisado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'fecha datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('usuario_id', false, false, 'idx_usuario');
        $this->forge->addKey('estado', false, false, 'idx_estado');
        $this->forge->addKey('revisado_por', false, false, 'idx_revisor');

        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_resp_usuario');
        $this->forge->addForeignKey('revisado_por', 'usuarios', 'id', 'SET NULL', 'CASCADE', 'fk_resp_revisor');

        $this->forge->createTable('resenas_pagina', true);
    }

    public function down()
    {
        $this->forge->dropTable('resenas_pagina', true);
    }
}
