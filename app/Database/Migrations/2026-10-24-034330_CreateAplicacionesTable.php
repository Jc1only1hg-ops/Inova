<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAplicacionesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'uuid' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'oferta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'carta_presentacion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cv_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'revisado', 'entrevista', 'aceptado', 'rechazado'],
                'default'    => 'pendiente',
            ],
            'revisado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'fecha_revision' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'fecha_aplicacion datetime default current_timestamp',
            'fecha_actualizacion datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('uuid', 'uuid_unico');
        $this->forge->addUniqueKey(['usuario_id', 'oferta_id'], 'unico_par');

        $this->forge->addKey('oferta_id', false, false, 'idx_oferta');
        $this->forge->addKey('estado', false, false, 'idx_estado');
        $this->forge->addKey('revisado_por', false, false, 'idx_revisor');

        $this->forge->addForeignKey('oferta_id', 'ofertas', 'id', 'CASCADE', 'CASCADE', 'fk_app_oferta');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_app_usuario');
        $this->forge->addForeignKey('revisado_por', 'usuarios', 'id', 'SET NULL', 'CASCADE', 'fk_app_revisor');

        $this->forge->createTable('aplicaciones', true);
    }

    public function down()
    {
        $this->forge->dropTable('aplicaciones', true);
    }
}
