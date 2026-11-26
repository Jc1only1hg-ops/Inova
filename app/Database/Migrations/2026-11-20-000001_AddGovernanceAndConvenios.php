<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGovernanceAndConvenios extends Migration
{
    public function up()
    {
        // 1. Campos de vigencia, promedio, notas certificadas y cambio de universidad en perfil_estudiantes
        if ($this->db->tableExists('perfil_estudiantes')) {
            $studentCols = [];
            if (! $this->db->fieldExists('estado_vigencia', 'perfil_estudiantes')) {
                $studentCols['estado_vigencia'] = [
                    'type'       => 'ENUM',
                    'constraint' => ['pendiente', 'vigente', 'rechazado'],
                    'default'    => 'pendiente',
                ];
            }
            if (! $this->db->fieldExists('promedio', 'perfil_estudiantes')) {
                $studentCols['promedio'] = [
                    'type'       => 'DECIMAL',
                    'constraint' => '4,2',
                    'null'       => true,
                ];
            }
            if (! $this->db->fieldExists('notas_certificadas', 'perfil_estudiantes')) {
                $studentCols['notas_certificadas'] = [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ];
            }
            if (! $this->db->fieldExists('constancia_notas_url', 'perfil_estudiantes')) {
                $studentCols['constancia_notas_url'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'null'       => true,
                ];
            }
            if (! $this->db->fieldExists('cambio_universidad_solicitado', 'perfil_estudiantes')) {
                $studentCols['cambio_universidad_solicitado'] = [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ];
            }
            if (! $this->db->fieldExists('cambio_universidad_habilitado', 'perfil_estudiantes')) {
                $studentCols['cambio_universidad_habilitado'] = [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ];
            }
            if ($studentCols !== []) {
                $this->forge->addColumn('perfil_estudiantes', $studentCols);
            }
        }

        // 2. Campos de verificación documental en organismos (empresas)
        if ($this->db->tableExists('organismos')) {
            $orgCols = [];
            if (! $this->db->fieldExists('documento_verificacion_url', 'organismos')) {
                $orgCols['documento_verificacion_url'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'null'       => true,
                ];
            }
            if (! $this->db->fieldExists('estado_verificacion', 'organismos')) {
                $orgCols['estado_verificacion'] = [
                    'type'       => 'ENUM',
                    'constraint' => ['no_enviado', 'pendiente', 'verificado', 'rechazado'],
                    'default'    => 'no_enviado',
                ];
            }
            if ($orgCols !== []) {
                $this->forge->addColumn('organismos', $orgCols);
            }
        }

        // 3. Tabla de Convenios (Alianzas Universidad <-> Empresa para créditos académicos)
        if (! $this->db->tableExists('convenios')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'auto_increment' => true,
                ],
                'empresa_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'universidad_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'solicitado_por' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'titulo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 180,
                ],
                'descripcion' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'creditos_academicos' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'estado' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pendiente', 'activo', 'rechazado'],
                    'default'    => 'pendiente',
                ],
                'fecha_creacion datetime default current_timestamp',
                'fecha_actualizacion datetime default current_timestamp on update current_timestamp',
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('empresa_id');
            $this->forge->addKey('universidad_id');
            $this->forge->addForeignKey('empresa_id', 'organismos', 'id', 'CASCADE', 'CASCADE', 'fk_convenio_empresa');
            $this->forge->addForeignKey('universidad_id', 'organismos', 'id', 'CASCADE', 'CASCADE', 'fk_convenio_universidad');
            $this->forge->addForeignKey('solicitado_por', 'usuarios', 'id', 'CASCADE', 'CASCADE', 'fk_convenio_solicitante');
            $this->forge->createTable('convenios', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('convenios')) {
            $this->forge->dropTable('convenios', true);
        }
        foreach (['documento_verificacion_url', 'estado_verificacion'] as $col) {
            if ($this->db->tableExists('organismos') && $this->db->fieldExists($col, 'organismos')) {
                $this->forge->dropColumn('organismos', $col);
            }
        }
        foreach ([
            'estado_vigencia', 'promedio', 'notas_certificadas',
            'constancia_notas_url', 'cambio_universidad_solicitado', 'cambio_universidad_habilitado',
        ] as $col) {
            if ($this->db->tableExists('perfil_estudiantes') && $this->db->fieldExists($col, 'perfil_estudiantes')) {
                $this->forge->dropColumn('perfil_estudiantes', $col);
            }
        }
    }
}
