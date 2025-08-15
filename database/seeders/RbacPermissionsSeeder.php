<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RbacPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createCrudPermissions();
        $this->assignPermissionsToRoles();
    }

    private function createCrudPermissions()
    {
        $modules = [
            'lots' => 'Lotes',
            'sales' => 'Ventas', 
            'payments' => 'Pagos',
            'suppliers' => 'Proveedores',
            'customers' => 'Clientes',
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'reports' => 'Reportes',
            'configuration' => 'Configuración',
            'system' => 'Sistema'
        ];

        $actions = [
            'create' => 'Crear',
            'read' => 'Ver',
            'update' => 'Editar', 
            'delete' => 'Eliminar',
            'export' => 'Exportar'
        ];

        foreach ($modules as $moduleKey => $moduleName) {
            foreach ($actions as $actionKey => $actionName) {
                Permission::firstOrCreate([
                    'name' => "{$moduleKey}.{$actionKey}",
                ], [
                    'display_name' => "{$actionName} {$moduleName}",
                    'description' => "Permite {$actionName} {$moduleName}",
                    'module' => $moduleKey,
                ]);
            }

            // Special permissions for each module
            if ($moduleKey === 'reports') {
                Permission::firstOrCreate([
                    'name' => 'reports.financial',
                ], [
                    'display_name' => 'Reportes Financieros',
                    'description' => 'Acceso a reportes de rentabilidad y finanzas',
                    'module' => 'reports',
                ]);
            }

            if ($moduleKey === 'system') {
                Permission::firstOrCreate([
                    'name' => 'system.maintenance',
                ], [
                    'display_name' => 'Modo Mantenimiento',
                    'description' => 'Activar/desactivar modo mantenimiento',
                    'module' => 'system',
                ]);

                Permission::firstOrCreate([
                    'name' => 'system.backups',
                ], [
                    'display_name' => 'Gestión de Backups',
                    'description' => 'Crear y gestionar backups del sistema',
                    'module' => 'system',
                ]);
            }
        }
    }

    private function assignPermissionsToRoles()
    {
        // Super Admin - todos los permisos
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $allPermissions = Permission::all();
            $superAdmin->permissions()->sync($allPermissions->pluck('id'));
        }

        // Admin - la mayoría de permisos excepto sistema
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::where('module', '!=', 'system')
                ->where('name', 'not like', '%.delete%')
                ->orWhere('module', 'system')
                ->where('name', 'system.backups')
                ->get();
            $admin->permissions()->sync($adminPermissions->pluck('id'));
        }

        // Manager - permisos de gestión operativa
        $manager = Role::firstOrCreate([
            'name' => 'manager',
        ], [
            'display_name' => 'Gerente',
            'description' => 'Gestión operativa del sistema',
            'hierarchy_level' => 80,
            'is_system' => false,
        ]);

        $managerPermissions = Permission::whereIn('module', ['lots', 'sales', 'payments', 'suppliers', 'customers', 'reports'])
            ->where('name', 'not like', '%.delete%')
            ->get();
        $manager->permissions()->sync($managerPermissions->pluck('id'));

        // Vendedor - permisos de ventas y clientes
        $vendedor = Role::where('name', 'vendedor')->first();
        if ($vendedor) {
            $vendedorPermissions = Permission::whereIn('module', ['sales', 'customers', 'lots'])
                ->whereIn('name', [
                    'sales.create', 'sales.read', 'sales.update',
                    'customers.create', 'customers.read', 'customers.update',
                    'lots.read', 'reports.read'
                ])
                ->get();
            $vendedor->permissions()->sync($vendedorPermissions->pluck('id'));
        }

        // Contador - permisos de pagos y reportes
        $contador = Role::where('name', 'contador')->first();
        if ($contador) {
            $contadorPermissions = Permission::whereIn('module', ['payments', 'suppliers', 'lots', 'reports'])
                ->whereIn('name', [
                    'payments.create', 'payments.read', 'payments.update',
                    'suppliers.create', 'suppliers.read', 'suppliers.update',
                    'lots.create', 'lots.read', 'lots.update',
                    'reports.read', 'reports.financial', 'reports.export'
                ])
                ->get();
            $contador->permissions()->sync($contadorPermissions->pluck('id'));
        }
    }
}
