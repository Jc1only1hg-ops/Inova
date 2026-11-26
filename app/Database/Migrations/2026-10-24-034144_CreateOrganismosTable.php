<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrganismosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['empresa', 'universidad'],
            ],
            'creado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'siglas' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'rif' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'sitio_web' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ubicacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'ranking_uni' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
            ],
            'verificado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'plan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'fecha_registro datetime default current_timestamp',
            'fecha_actualizacion datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('siglas', 'siglas_unico');
        $this->forge->addUniqueKey('rif', 'rif_unico');

        $this->forge->addKey('tipo', false, false, 'idx_tipo');
        $this->forge->addKey('creado_por', false, false, 'idx_creador');
        $this->forge->addKey('plan_id', false, false, 'idx_plan');
        $this->forge->addKey('verificado', false, false, 'idx_verificado');

        $this->forge->addForeignKey('creado_por', 'usuarios', 'id', 'RESTRICT', 'CASCADE', 'fk_org_creador');
        $this->forge->addForeignKey('plan_id', 'planes_suscripcion', 'id', 'RESTRICT', 'CASCADE', 'fk_org_plan');

        $this->forge->createTable('organismos', true);
    }

    public function down()
    {
        $this->forge->dropTable('organismos', true);
    }
}
