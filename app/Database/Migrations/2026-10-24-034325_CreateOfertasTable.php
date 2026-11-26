<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOfertasTable extends Migration
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
            'organismo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'publicado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'descripcion' => [
                'type' => 'TEXT',
            ],
            'ubicacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'modalidad' => [
                'type'       => 'ENUM',
                'constraint' => ['remoto', 'presencial', 'hibrido'],
                'default'    => 'presencial',
            ],
            'tipo_contrato' => [
                'type'       => 'ENUM',
                'constraint' => ['tiempo_completo', 'medio_tiempo', 'pasantia', 'freelance', 'temporal'],
                'default'    => 'tiempo_completo',
            ],
            'salario_min' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'salario_max' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'salario_moneda' => [
                'type'       => 'ENUM',
                'constraint' => ['USD', 'EUR', 'VES', 'COP', 'PEN'],
                'default'    => 'USD',
            ],
            'categoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'otros',
            ],
            'nivel_experiencia' => [
                'type'       => 'ENUM',
                'constraint' => ['sin_experiencia', 'junior', 'semi_senior', 'senior'],
                'default'    => 'sin_experiencia',
            ],
            'vacantes' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['borrador', 'activa', 'pausada', 'cerrada', 'expirada'],
                'default'    => 'activa',
            ],
            'fecha_expiracion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'vistas' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'aplicantes' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'fecha_creacion datetime default current_timestamp',
            'fecha_actualizacion datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('uuid', 'uuid_unico');

        $this->forge->addKey('organismo_id', false, false, 'idx_organismo');
        $this->forge->addKey('publicado_por', false, false, 'idx_publicado_por');
        $this->forge->addKey('estado', false, false, 'idx_estado');
        $this->forge->addKey('categoria', false, false, 'idx_categoria');
        $this->forge->addKey('modalidad', false, false, 'idx_modalidad');
        $this->forge->addKey('fecha_creacion', false, false, 'idx_fecha');

        $this->forge->addForeignKey('organismo_id', 'organismos', 'id', 'CASCADE', 'CASCADE', 'fk_ofertas_organismo');
        $this->forge->addForeignKey('publicado_por', 'usuarios', 'id', 'RESTRICT', 'CASCADE', 'fk_ofertas_publicador');

        $this->forge->createTable('ofertas', true);

        // Índice FULLTEXT para búsquedas eficientes (verificando si no existe previamente)
        $indexExists = $this->db->query("SHOW INDEX FROM `ofertas` WHERE Key_name = 'ft_busqueda'")->getRow();
        if (! $indexExists) {
            $this->db->query("ALTER TABLE `ofertas` ADD FULLTEXT KEY `ft_busqueda` (`titulo`, `descripcion`)");
        }
    }

    public function down()
    {
        $this->forge->dropTable('ofertas', true);
    }
}
