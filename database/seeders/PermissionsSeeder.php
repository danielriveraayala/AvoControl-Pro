<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Permisos de Lotes
            [
                'name' => 'lots.view',
                'display_name' => 'Ver lotes',
                'module' => 'lots',
                'description' => 'Ver listado y detalles de lotes'
            ],
            [
                'name' => 'lots.create',
                'display_name' => 'Crear lotes',
                'module' => 'lots',
                'description' => 'Registrar nuevos lotes de aguacate'
            ],
            [
                'name' => 'lots.edit',
                'display_name' => 'Editar lotes',
                'module' => 'lots',
                'description' => 'Modificar información de lotes existentes'
            ],
            [
                'name' => 'lots.delete',
                'display_name' => 'Eliminar lotes',
                'module' => 'lots',
                'description' => 'Eliminar lotes del sistema'
            ],
            [
                'name' => 'lots.export',
                'display_name' => 'Exportar lotes',
                'module' => 'lots',
                'description' => 'Exportar datos de lotes a Excel/PDF'
            ],

            // Permisos de Ventas
            [
                'name' => 'sales.view',
                'display_name' => 'Ver ventas',
                'module' => 'sales',
                'description' => 'Ver listado y detalles de ventas'
            ],
            [
                'name' => 'sales.create',
                'display_name' => 'Crear ventas',
                'module' => 'sales',
                'description' => 'Registrar nuevas ventas'
            ],
            [
                'name' => 'sales.edit',
                'display_name' => 'Editar ventas',
                'module' => 'sales',
                'description' => 'Modificar información de ventas existentes'
            ],
            [
                'name' => 'sales.delete',
                'display_name' => 'Eliminar ventas',
                'module' => 'sales',
                'description' => 'Eliminar ventas del sistema'
            ],
            [
                'name' => 'sales.cancel',
                'display_name' => 'Cancelar ventas',
                'module' => 'sales',
                'description' => 'Cancelar ventas confirmadas'
            ],
            [
                'name' => 'sales.deliver',
                'display_name' => 'Marcar como entregado',
                'module' => 'sales',
                'description' => 'Marcar ventas como entregadas'
            ],

            // Permisos de Pagos
            [
                'name' => 'payments.view',
                'display_name' => 'Ver pagos',
                'module' => 'payments',
                'description' => 'Ver listado y detalles de pagos'
            ],
            [
                'name' => 'payments.create',
                'display_name' => 'Registrar pagos',
                'module' => 'payments',
                'description' => 'Registrar nuevos pagos'
            ],
            [
                'name' => 'payments.edit',
                'display_name' => 'Editar pagos',
                'module' => 'payments',
                'description' => 'Modificar información de pagos'
            ],
            [
                'name' => 'payments.delete',
                'display_name' => 'Eliminar pagos',
                'module' => 'payments',
                'description' => 'Eliminar pagos del sistema'
            ],
            [
                'name' => 'payments.approve',
                'display_name' => 'Aprobar pagos',
                'module' => 'payments',
                'description' => 'Aprobar pagos pendientes'
            ],

            // Permisos de Proveedores
            [
                'name' => 'suppliers.view',
                'display_name' => 'Ver proveedores',
                'module' => 'suppliers',
                'description' => 'Ver listado y detalles de proveedores'
            ],
            [
                'name' => 'suppliers.create',
                'display_name' => 'Crear proveedores',
                'module' => 'suppliers',
                'description' => 'Registrar nuevos proveedores'
            ],
            [
                'name' => 'suppliers.edit',
                'display_name' => 'Editar proveedores',
                'module' => 'suppliers',
                'description' => 'Modificar información de proveedores'
            ],
            [
                'name' => 'suppliers.delete',
                'display_name' => 'Eliminar proveedores',
                'module' => 'suppliers',
                'description' => 'Eliminar proveedores del sistema'
            ],
            [
                'name' => 'suppliers.view_balance',
                'display_name' => 'Ver saldos de proveedores',
                'module' => 'suppliers',
                'description' => 'Ver información financiera de proveedores'
            ],

            // Permisos de Clientes
            [
                'name' => 'customers.view',
                'display_name' => 'Ver clientes',
                'module' => 'customers',
                'description' => 'Ver listado y detalles de clientes'
            ],
            [
                'name' => 'customers.create',
                'display_name' => 'Crear clientes',
                'module' => 'customers',
                'description' => 'Registrar nuevos clientes'
            ],
            [
                'name' => 'customers.edit',
                'display_name' => 'Editar clientes',
                'module' => 'customers',
                'description' => 'Modificar información de clientes'
            ],
            [
                'name' => 'customers.delete',
                'display_name' => 'Eliminar clientes',
                'module' => 'customers',
                'description' => 'Eliminar clientes del sistema'
            ],
            [
                'name' => 'customers.view_balance',
                'display_name' => 'Ver saldos de clientes',
                'module' => 'customers',
                'description' => 'Ver información financiera de clientes'
            ],

            // Permisos de Reportes
            [
                'name' => 'reports.view_basic',
                'display_name' => 'Ver reportes básicos',
                'module' => 'reports',
                'description' => 'Ver reportes operativos básicos'
            ],
            [
                'name' => 'reports.view_financial',
                'display_name' => 'Ver reportes financieros',
                'module' => 'reports',
                'description' => 'Ver reportes de rentabilidad y finanzas'
            ],
            [
                'name' => 'reports.view_analytics',
                'display_name' => 'Ver análisis avanzados',
                'module' => 'reports',
                'description' => 'Ver análisis de clientes y proveedores'
            ],
            [
                'name' => 'reports.export',
                'display_name' => 'Exportar reportes',
                'module' => 'reports',
                'description' => 'Exportar reportes a Excel/PDF'
            ],

            // Permisos de Configuración
            [
                'name' => 'configuration.view',
                'display_name' => 'Ver configuración',
                'module' => 'configuration',
                'description' => 'Ver configuración del sistema'
            ],
            [
                'name' => 'configuration.edit_company',
                'display_name' => 'Editar datos de empresa',
                'module' => 'configuration',
                'description' => 'Modificar información de la empresa'
            ],
            [
                'name' => 'configuration.edit_system',
                'display_name' => 'Editar configuración del sistema',
                'module' => 'configuration',
                'description' => 'Modificar configuración del sistema'
            ],
            [
                'name' => 'configuration.manage_qualities',
                'display_name' => 'Gestionar calidades',
                'module' => 'configuration',
                'description' => 'Crear, editar y eliminar calidades de aguacate'
            ],

            // Permisos de Usuarios
            [
                'name' => 'users.view',
                'display_name' => 'Ver usuarios',
                'module' => 'users',
                'description' => 'Ver listado de usuarios'
            ],
            [
                'name' => 'users.create',
                'display_name' => 'Crear usuarios',
                'module' => 'users',
                'description' => 'Registrar nuevos usuarios'
            ],
            [
                'name' => 'users.edit',
                'display_name' => 'Editar usuarios',
                'module' => 'users',
                'description' => 'Modificar información de usuarios'
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'Eliminar usuarios',
                'module' => 'users',
                'description' => 'Eliminar usuarios del sistema'
            ],
            [
                'name' => 'users.manage_roles',
                'display_name' => 'Gestionar roles',
                'module' => 'users',
                'description' => 'Asignar y modificar roles de usuarios'
            ],

            // Permisos de Dashboard
            [
                'name' => 'dashboard.view_basic',
                'display_name' => 'Ver dashboard básico',
                'module' => 'dashboard',
                'description' => 'Ver información básica del dashboard'
            ],
            [
                'name' => 'dashboard.view_financial',
                'display_name' => 'Ver dashboard financiero',
                'module' => 'dashboard',
                'description' => 'Ver información financiera en dashboard'
            ],
            [
                'name' => 'dashboard.view_analytics',
                'display_name' => 'Ver analytics del dashboard',
                'module' => 'dashboard',
                'description' => 'Ver gráficos y análisis en dashboard'
            ],

            // Permisos especiales
            [
                'name' => 'system.backup',
                'display_name' => 'Realizar respaldos',
                'module' => 'system',
                'description' => 'Crear respaldos del sistema'
            ],
            [
                'name' => 'system.restore',
                'display_name' => 'Restaurar sistema',
                'module' => 'system',
                'description' => 'Restaurar respaldos del sistema'
            ],
            [
                'name' => 'system.audit_logs',
                'display_name' => 'Ver logs de auditoría',
                'module' => 'system',
                'description' => 'Ver registros de auditoría del sistema'
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'module' => $permission['module'],
                'description' => $permission['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Asignar permisos a roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles()
    {
        $rolePermissions = [
            'super_admin' => '*', // Todos los permisos
            
            'admin' => [
                'lots.*',
                'sales.*',
                'payments.*',
                'suppliers.*',
                'customers.*',
                'reports.*',
                'configuration.*',
                'users.*',
                'dashboard.*',
                'system.backup',
                'system.audit_logs'
            ],
            
            'gerente' => [
                'lots.*',
                'sales.*',
                'payments.view',
                'payments.create',
                'payments.edit',
                'suppliers.*',
                'customers.*',
                'reports.*',
                'configuration.view',
                'configuration.manage_qualities',
                'users.view',
                'dashboard.*'
            ],
            
            'contador' => [
                'lots.view',
                'lots.export',
                'sales.view',
                'payments.*',
                'suppliers.view',
                'suppliers.view_balance',
                'customers.view',
                'customers.view_balance',
                'reports.*',
                'dashboard.view_basic',
                'dashboard.view_financial'
            ],
            
            'vendedor' => [
                'lots.view',
                'sales.*',
                'payments.view',
                'payments.create',
                'customers.view',
                'customers.create',
                'customers.edit',
                'reports.view_basic',
                'dashboard.view_basic'
            ],
            
            'comprador' => [
                'lots.*',
                'suppliers.view',
                'suppliers.create',
                'suppliers.edit',
                'payments.view',
                'payments.create',
                'reports.view_basic',
                'dashboard.view_basic'
            ],
            
            'operario' => [
                'lots.view',
                'lots.create',
                'lots.edit',
                'sales.view',
                'suppliers.view',
                'customers.view',
                'dashboard.view_basic'
            ],
            
            'visualizador' => [
                'lots.view',
                'sales.view',
                'payments.view',
                'suppliers.view',
                'customers.view',
                'reports.view_basic',
                'dashboard.view_basic'
            ]
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = DB::table('roles')->where('name', $roleName)->first();
            
            if (!$role) continue;

            if ($permissions === '*') {
                // Asignar todos los permisos
                $allPermissions = DB::table('permissions')->get();
                foreach ($allPermissions as $permission) {
                    DB::table('role_permission')->insertOrIgnore([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                foreach ($permissions as $permissionPattern) {
                    if (str_contains($permissionPattern, '*')) {
                        // Permisos con wildcard
                        $module = str_replace('.*', '', $permissionPattern);
                        $modulePermissions = DB::table('permissions')
                            ->where('module', $module)
                            ->get();
                        
                        foreach ($modulePermissions as $permission) {
                            DB::table('role_permission')->insertOrIgnore([
                                'role_id' => $role->id,
                                'permission_id' => $permission->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    } else {
                        // Permiso específico
                        $permission = DB::table('permissions')
                            ->where('name', $permissionPattern)
                            ->first();
                        
                        if ($permission) {
                            DB::table('role_permission')->insertOrIgnore([
                                'role_id' => $role->id,
                                'permission_id' => $permission->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}