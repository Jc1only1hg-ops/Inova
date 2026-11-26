<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class IneovaSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // 1. Planes de suscripción base
        if ($db->table('planes_suscripcion')->where('id', 1)->countAllResults() === 0) {
            $db->table('planes_suscripcion')->insert([
                'id'               => 1,
                'nombre'           => 'Básico',
                'precio'           => 0.00,
                'limite_busquedas' => 50,
                'limite_ofertas'   => 10,
            ]);
        }

        // 2. Habilidades base
        $skills = [
            ['nombre' => 'PHP', 'tipo_habilidad' => 'Técnica'],
            ['nombre' => 'JavaScript', 'tipo_habilidad' => 'Técnica'],
            ['nombre' => 'Python', 'tipo_habilidad' => 'Técnica'],
            ['nombre' => 'SQL', 'tipo_habilidad' => 'Técnica'],
            ['nombre' => 'Diseño UI/UX', 'tipo_habilidad' => 'Técnica'],
            ['nombre' => 'Trabajo en equipo', 'tipo_habilidad' => 'Blanda'],
            ['nombre' => 'Comunicación efectiva', 'tipo_habilidad' => 'Blanda'],
        ];
        foreach ($skills as $skill) {
            if ($db->table('habilidades')->where('nombre', $skill['nombre'])->countAllResults() === 0) {
                $db->table('habilidades')->insert($skill);
            }
        }

        // 3. Usuarios de prueba (clave: 123456)
        $passwordHash = password_hash('123456', PASSWORD_DEFAULT);

        $adminUserId = $this->ensureUser($db, [
            'correo'        => 'admin@ineova.com',
            'password_hash' => $passwordHash,
            'rol'           => 'admin',
            'nombre'        => 'Administrador',
            'apellido'      => 'Ineova',
            'cedula'        => 'V-10000000',
            'telefono'      => '04120000000',
            'estado'        => 'activo',
        ]);

        $uniUserId = $this->ensureUser($db, [
            'correo'        => 'universidad@ineova.com',
            'password_hash' => $passwordHash,
            'rol'           => 'participante_universidad',
            'nombre'        => 'Coordinación',
            'apellido'      => 'UCV',
            'cedula'        => 'V-10000001',
            'telefono'      => '04121000001',
            'estado'        => 'activo',
        ]);

        $companyUserId = $this->ensureUser($db, [
            'correo'        => 'empresa@ineova.com',
            'password_hash' => $passwordHash,
            'rol'           => 'participante_empresa',
            'nombre'        => 'Carlos',
            'apellido'      => 'Mendoza',
            'cedula'        => 'V-10000002',
            'telefono'      => '04141000002',
            'estado'        => 'activo',
        ]);

        $studentUserId = $this->ensureUser($db, [
            'correo'        => 'estudiante@ineova.com',
            'password_hash' => $passwordHash,
            'rol'           => 'estudiante',
            'nombre'        => 'María',
            'apellido'      => 'González',
            'cedula'        => 'V-28000003',
            'telefono'      => '04241000003',
            'estado'        => 'activo',
        ]);

        // 4. Catálogo fijo de Universidades oficiales
        $ucvId = $this->ensureOrganism($db, 'siglas', 'UCV', [
            'tipo'        => 'universidad',
            'creado_por'  => $adminUserId,
            'nombre'      => 'Universidad Central de Venezuela',
            'siglas'      => 'UCV',
            'sitio_web'   => 'https://www.ucv.ve',
            'ubicacion'   => 'Caracas, Venezuela',
            'descripcion' => 'Casa de estudios superiores aliada al programa de becarios y créditos académicos.',
            'ranking_uni' => 1,
            'verificado'  => 1,
            'plan_id'     => 1,
        ], $uniUserId);

        $extraUniversities = [
            ['Universidad Simón Bolívar', 'USB', 'https://www.usb.ve', 'Sartenejas, Miranda', 2],
            ['Universidad Católica Andrés Bello', 'UCAB', 'https://www.ucab.edu.ve', 'Montalbán, Caracas', 3],
            ['Universidad de Los Andes', 'ULA', 'https://www.ula.ve', 'Mérida, Venezuela', 4],
            ['Universidad del Zulia', 'LUZ', 'https://www.luz.edu.ve', 'Maracaibo, Zulia', 5],
            ['Universidad de Carabobo', 'UC', 'https://www.uc.edu.ve', 'Valencia, Carabobo', 6],
            ['Universidad Metropolitana', 'UNIMET', 'https://www.unimet.edu.ve', 'Caracas, Venezuela', 7],
        ];
        foreach ($extraUniversities as [$name, $siglas, $web, $loc, $rank]) {
            $this->ensureOrganism($db, 'siglas', $siglas, [
                'tipo'        => 'universidad',
                'creado_por'  => $adminUserId,
                'nombre'      => $name,
                'siglas'      => $siglas,
                'sitio_web'   => $web,
                'ubicacion'   => $loc,
                'descripcion' => 'Institución universitaria oficial registrada en Ineova.',
                'ranking_uni' => $rank,
                'verificado'  => 1,
                'plan_id'     => 1,
            ]);
        }

        // 5. Empresa demo verificada
        $companyData = [
            'tipo'        => 'empresa',
            'creado_por'  => $companyUserId,
            'nombre'      => 'TechNova Venezuela C.A.',
            'rif'         => 'J-40123456-7',
            'sitio_web'   => 'https://technova.example.com',
            'ubicacion'   => 'Caracas, Venezuela',
            'descripcion' => 'Empresa de desarrollo de software que impulsa el talento universitario mediante convenios de créditos académicos.',
            'verificado'  => 1,
            'plan_id'     => 1,
        ];
        if ($db->fieldExists('estado_verificacion', 'organismos')) {
            $companyData['estado_verificacion'] = 'verificado';
        }
        $companyOrgId = $this->ensureOrganism($db, 'rif', 'J-40123456-7', $companyData, $companyUserId);
        if ($db->fieldExists('estado_verificacion', 'organismos')) {
            $db->table('organismos')->where('id', $companyOrgId)->update([
                'verificado'          => 1,
                'estado_verificacion' => 'verificado',
            ]);
        }

        // 6. Perfil del estudiante demo (vigente y con notas certificadas)
        $studentProfile = $db->table('perfil_estudiantes')->where('usuario_id', $studentUserId)->get()->getRowArray();
        $studentUpdate = [
            'organismo_id'  => $ucvId,
            'autobiografia' => 'Estudiante de Ingeniería en Computación apasionada por el desarrollo web full-stack y disponible para proyectos de becarios.',
            'github_url'    => 'https://github.com/mariagonzalez-dev',
        ];
        if ($db->fieldExists('estado_vigencia', 'perfil_estudiantes')) {
            $studentUpdate['estado_vigencia'] = 'vigente';
            $studentUpdate['promedio'] = 17.85;
            $studentUpdate['notas_certificadas'] = 1;
        }
        if (! $studentProfile) {
            $studentUpdate['usuario_id'] = $studentUserId;
            $db->table('perfil_estudiantes')->insert($studentUpdate);
        } else {
            $db->table('perfil_estudiantes')->where('id', $studentProfile['id'])->update($studentUpdate);
        }

        // 7. Convenio demo activo entre TechNova y UCV
        if ($db->tableExists('convenios') && $db->table('convenios')->where(['empresa_id' => $companyOrgId, 'universidad_id' => $ucvId])->countAllResults() === 0) {
            $db->table('convenios')->insert([
                'empresa_id'          => $companyOrgId,
                'universidad_id'      => $ucvId,
                'solicitado_por'      => $companyUserId,
                'titulo'              => 'Convenio de Pasantías y Desarrollo Tecnológico UCV - TechNova',
                'descripcion'         => 'Alianza académica para reconocer créditos académicos a estudiantes becarios de la UCV que completen proyectos en TechNova Venezuela C.A.',
                'creditos_academicos' => 6,
                'estado'              => 'activo',
            ]);
        }
    }

    private function ensureUser($db, array $data): int
    {
        $existing = $db->table('usuarios')->where('correo', $data['correo'])->get()->getRowArray();
        if ($existing) {
            return (int) $existing['id'];
        }
        $db->table('usuarios')->insert($data);
        return (int) $db->insertID();
    }

    private function ensureOrganism($db, string $keyCol, string $keyVal, array $data, ?int $ownerId = null): int
    {
        $existing = $db->table('organismos')->where($keyCol, $keyVal)->get()->getRowArray();
        if ($existing) {
            $orgId = (int) $existing['id'];
        } else {
            $db->table('organismos')->insert($data);
            $orgId = (int) $db->insertID();
        }

        if ($ownerId !== null) {
            $hasMembership = $db->table('participante_organismo')
                ->where(['usuario_id' => $ownerId, 'organismo_id' => $orgId])
                ->countAllResults() > 0;
            if (! $hasMembership) {
                $db->table('participante_organismo')->insert([
                    'usuario_id'       => $ownerId,
                    'organismo_id'     => $orgId,
                    'rol_en_organismo' => 'dueño',
                    'activo'           => 1,
                ]);
            }
        }

        return $orgId;
    }
}
