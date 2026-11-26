<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConfigureCompanyPlansAndTrial extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('planes_suscripcion')) {
            $planCols = [];
            if (! $this->db->fieldExists('destaca_ofertas', 'planes_suscripcion')) {
                $planCols['destaca_ofertas'] = [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ];
            }
            if (! $this->db->fieldExists('descripcion', 'planes_suscripcion')) {
                $planCols['descripcion'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ];
            }
            if ($planCols !== []) {
                $this->forge->addColumn('planes_suscripcion', $planCols);
            }

            $plans = [
                [
                    'id'               => 1,
                    'nombre'           => 'Prueba Gratis (7 días)',
                    'precio'           => 0.00,
                    'limite_busquedas' => 50,
                    'limite_ofertas'   => 0,
                    'destaca_ofertas'  => 0,
                    'descripcion'      => 'Período gratuito de 7 días para publicar ofertas de becarios.',
                ],
                [
                    'id'               => 2,
                    'nombre'           => 'Estándar',
                    'precio'           => 29.99,
                    'limite_busquedas' => 200,
                    'limite_ofertas'   => 10,
                    'destaca_ofertas'  => 0,
                    'descripcion'      => 'Permite hasta un máximo de 10 publicaciones activas de ofertas.',
                ],
                [
                    'id'               => 3,
                    'nombre'           => 'Extra',
                    'precio'           => 79.99,
                    'limite_busquedas' => 1000,
                    'limite_ofertas'   => 0,
                    'destaca_ofertas'  => 1,
                    'descripcion'      => 'Publicaciones sin límite y ofertas destacadas para estudiantes compatibles.',
                ],
            ];

            foreach ($plans as $plan) {
                $existing = $this->db->table('planes_suscripcion')->where('id', $plan['id'])->get()->getRowArray();
                if ($existing) {
                    $this->db->table('planes_suscripcion')->where('id', $plan['id'])->update($plan);
                } else {
                    $this->db->table('planes_suscripcion')->insert($plan);
                }
            }
        }

        if ($this->db->tableExists('organismos') && ! $this->db->fieldExists('trial_fin', 'organismos')) {
            $this->forge->addColumn('organismos', [
                'trial_fin' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->db->query("UPDATE organismos SET trial_fin = DATE_ADD(fecha_registro, INTERVAL 7 DAY) WHERE tipo = 'empresa' AND trial_fin IS NULL");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('organismos') && $this->db->fieldExists('trial_fin', 'organismos')) {
            $this->forge->dropColumn('organismos', 'trial_fin');
        }
        foreach (['destaca_ofertas', 'descripcion'] as $col) {
            if ($this->db->tableExists('planes_suscripcion') && $this->db->fieldExists($col, 'planes_suscripcion')) {
                $this->forge->dropColumn('planes_suscripcion', $col);
            }
        }
    }
}
