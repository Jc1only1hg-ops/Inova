<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropUnusedLegacyTables extends Migration
{
    public function up()
    {
        foreach (['v_conversaciones_contadores', 'v_conversaciones_full', 'v_ofertas_completas', 'v_participante_organismos'] as $view) {
            $this->db->query("DROP VIEW IF EXISTS `{$view}`");
        }
        foreach (['resenas', 'resenas_pagina', 'recuperacion_password', 'sesiones', 'reportes'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }

    public function down()
    {
    }
}
