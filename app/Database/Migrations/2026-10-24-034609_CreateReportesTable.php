<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'reportante_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'entidad_tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['oferta', 'usuario', 'mensaje', 'organismo', 'resena', 'resena_pagina'],
            ],
            'entidad_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'motivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'detalles' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'revisado', 'descartado', 'accionado'],
                'default'    => 'pendiente',
            ],
            'revisado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'fecha_creacion datetime default current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('estado', false, false, 'idx_estado');
        $this->forge->addKey(['entidad_tipo', 'entidad_id'], false, false, 'idx_entidad');
        $this->forge->addKey('revisado_por', false, false, 'idx_revisor');

        $this->forge->addForeignKey('reportante_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_rep_reportante');
        $this->forge->addForeignKey('revisado_por', 'usuarios', 'id', 'SET NULL', 'CASCADE', 'fk_rep_revisor');

        $this->forge->createTable('reportes', true);
    }

    public function down()
    {
        $this->forge->dropTable('reportes', true);
    }
}
