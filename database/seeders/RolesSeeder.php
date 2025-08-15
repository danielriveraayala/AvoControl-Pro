<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrador',
                'description' => 'Control total del sistema, sin restricciones',
                'hierarchy_level' => 100,
                'is_system' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Administración completa del centro de acopio',
                'hierarchy_level' => 90,
                'is_system' => true,
            ],
            [
                'name' => 'gerente',
                'display_name' => 'Gerente',
                'description' => 'Gestión operativa y supervisión del centro',
                'hierarchy_level' => 70,
                'is_system' => true,
            ],
            [
                'name' => 'contador',
                'display_name' => 'Contador',
                'description' => 'Gestión financiera, pagos y reportes contables',
                'hierarchy_level' => 60,
                'is_system' => true,
            ],
            [
                'name' => 'vendedor',
                'display_name' => 'Vendedor',
                'description' => 'Gestión de ventas y atención a clientes',
                'hierarchy_level' => 50,
                'is_system' => true,
            ],
            [
                'name' => 'comprador',
                'display_name' => 'Comprador',
                'description' => 'Gestión de compras y relación con proveedores',
                'hierarchy_level' => 50,
                'is_system' => true,
            ],
            [
                'name' => 'operario',
                'display_name' => 'Operario',
                'description' => 'Registro de entradas y salidas, pesaje',
                'hierarchy_level' => 30,
                'is_system' => true,
            ],
            [
                'name' => 'visualizador',
                'display_name' => 'Visualizador',
                'description' => 'Solo puede ver información, sin capacidad de edición',
                'hierarchy_level' => 10,
                'is_system' => true,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore([
                'name' => $role['name'],
                'display_name' => $role['display_name'],
                'description' => $role['description'],
                'hierarchy_level' => $role['hierarchy_level'],
                'is_system' => $role['is_system'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
