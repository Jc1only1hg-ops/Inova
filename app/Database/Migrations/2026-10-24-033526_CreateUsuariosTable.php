<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuariosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => false,
                'auto_increment' => true,
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'rol' => [
                'type'       => 'ENUM',
                'constraint' => ['estudiante', 'participante_empresa', 'participante_universidad', 'admin'],
                'default'    => 'estudiante',
            ],
            'avatar' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'apellido' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'cedula' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'inactivo', 'suspendido'],
                'default'    => 'activo',
            ],
            'verificado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'token_verificacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'ultimo_login' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'fecha_registro datetime default current_timestamp',
            'fecha_actualizacion datetime default current_timestamp on update current_timestamp',
        ]);

        // Llave primaria
        $this->forge->addPrimaryKey('id');

        $this->forge->addUniqueKey('correo', 'correo_unico');
        $this->forge->addUniqueKey('cedula', 'cedula_unico');

        $this->forge->addKey('rol', false, false, 'idx_rol');
        $this->forge->addKey('estado', false, false, 'idx_estado');

        $this->forge->createTable('usuarios', true);
    }

    public function down()
    {
        $this->forge->dropTable('usuarios', true);
    }
}