<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPuntosCrediticios extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('ofertas') && ! $this->db->fieldExists('puntos_crediticios', 'ofertas')) {
            $this->forge->addColumn('ofertas', [
                'puntos_crediticios' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'default'    => 0,
                    'after'      => 'tipo_contrato',
                ],
            ]);
        }

        if ($this->db->tableExists('perfil_estudiantes') && ! $this->db->fieldExists('puntos_crediticios', 'perfil_estudiantes')) {
            $this->forge->addColumn('perfil_estudiantes', [
                'puntos_crediticios' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'default'    => 0,
                    'after'      => 'github_url',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('ofertas') && $this->db->fieldExists('puntos_crediticios', 'ofertas')) {
            $this->forge->dropColumn('ofertas', 'puntos_crediticios');
        }

        if ($this->db->tableExists('perfil_estudiantes') && $this->db->fieldExists('puntos_crediticios', 'perfil_estudiantes')) {
            $this->forge->dropColumn('perfil_estudiantes', 'puntos_crediticios');
        }
    }
}
