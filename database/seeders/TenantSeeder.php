<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default tenant
        $defaultTenant = Tenant::create([
            'name' => 'AvoControl Demo',
            'slug' => 'default',
            'uuid' => Str::uuid(),
            'email' => 'demo@avocontrol.com',
            'domain' => null, // Will use subdomain
            'plan' => 'basic',
            'status' => 'active',
            'max_users' => 50,
            'max_storage_mb' => 5000,
            'features' => [
                'notifications_email',
                'notifications_push',
                'reports_basic',
                'api_access',
                'data_export'
            ],
            'settings' => [
                'company_name' => 'AvoControl Demo',
                'timezone' => 'America/Mexico_City',
                'currency' => 'MXN',
                'locale' => 'es'
            ]
        ]);

        // Create premium tenant for testing
        $premiumTenant = Tenant::create([
            'name' => 'AvoControl Premium',
            'slug' => 'premium',
            'uuid' => Str::uuid(),
            'email' => 'premium@avocontrol.com',
            'domain' => null,
            'plan' => 'premium',
            'status' => 'active',
            'max_users' => 100,
            'max_storage_mb' => 10000,
            'features' => [
                'notifications_email',
                'notifications_push',
                'notifications_sms',
                'reports_basic',
                'reports_advanced',
                'reports_custom',
                'api_access',
                'api_webhooks',
                'data_export',
                'data_import',
                'multi_location',
                'custom_branding'
            ],
            'settings' => [
                'company_name' => 'AvoControl Premium Company',
                'timezone' => 'America/Mexico_City',
                'currency' => 'MXN',
                'locale' => 'es'
            ]
        ]);

        // Create trial tenant
        $trialTenant = Tenant::create([
            'name' => 'AvoControl Trial',
            'slug' => 'trial',
            'uuid' => Str::uuid(),
            'email' => 'trial@avocontrol.com',
            'domain' => null,
            'plan' => 'trial',
            'status' => 'active',
            'max_users' => 5,
            'max_storage_mb' => 1000,
            'trial_ends_at' => now()->addDays(30),
            'features' => [
                'notifications_email',
                'reports_basic',
                'data_export'
            ],
            'settings' => [
                'company_name' => 'AvoControl Trial Company',
                'timezone' => 'America/Mexico_City',
                'currency' => 'MXN',
                'locale' => 'es'
            ]
        ]);

        // Create tenant settings for default tenant
        $this->createTenantSettings($defaultTenant);
        $this->createTenantSettings($premiumTenant);
        $this->createTenantSettings($trialTenant);

        // Assign existing users to tenants
        $this->assignUsersToTenants($defaultTenant, $premiumTenant, $trialTenant);

        $this->command->info('✅ Tenants created successfully!');
        $this->command->info('📋 Created tenants:');
        $this->command->table(
            ['Name', 'Slug', 'Plan', 'Status', 'Max Users'],
            [
                [$defaultTenant->name, $defaultTenant->slug, $defaultTenant->plan, $defaultTenant->status, $defaultTenant->max_users],
                [$premiumTenant->name, $premiumTenant->slug, $premiumTenant->plan, $premiumTenant->status, $premiumTenant->max_users],
                [$trialTenant->name, $trialTenant->slug, $trialTenant->plan, $trialTenant->status, $trialTenant->max_users],
            ]
        );
    }

    /**
     * Create tenant settings
     */
    private function createTenantSettings(Tenant $tenant): void
    {
        $settings = [
            // App settings
            [
                'key' => 'app.name',
                'value' => $tenant->name,
                'type' => 'string',
                'category' => 'general',
                'description' => 'Nombre de la aplicación',
                'is_public' => true
            ],
            [
                'key' => 'app.timezone',
                'value' => 'America/Mexico_City',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Zona horaria',
                'is_public' => false
            ],
            [
                'key' => 'app.locale',
                'value' => 'es',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Idioma de la aplicación',
                'is_public' => true
            ],
            [
                'key' => 'app.currency',
                'value' => 'MXN',
                'type' => 'string',
                'category' => 'general',
                'description' => 'Moneda del sistema',
                'is_public' => true
            ],
            
            // Company settings
            [
                'key' => 'company.address',
                'value' => 'Morelia, Michoacán, México',
                'type' => 'text',
                'category' => 'company',
                'description' => 'Dirección de la empresa',
                'is_public' => true
            ],
            [
                'key' => 'company.phone',
                'value' => '+52 443 123 4567',
                'type' => 'string',
                'category' => 'company',
                'description' => 'Teléfono de la empresa',
                'is_public' => true
            ],
            [
                'key' => 'company.email',
                'value' => $tenant->email,
                'type' => 'email',
                'category' => 'company',
                'description' => 'Email de contacto',
                'is_public' => true
            ],
            
            // Notification settings
            [
                'key' => 'notifications.email.enabled',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'notifications',
                'description' => 'Habilitar notificaciones por email',
                'is_public' => false
            ],
            [
                'key' => 'notifications.push.enabled',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'notifications',
                'description' => 'Habilitar notificaciones push',
                'is_public' => false
            ],
            [
                'key' => 'notifications.database.enabled',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'notifications',
                'description' => 'Habilitar notificaciones en sistema',
                'is_public' => false
            ],

            // Security settings
            [
                'key' => 'security.session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'category' => 'security',
                'description' => 'Duración de sesión en minutos',
                'is_public' => false
            ],
            [
                'key' => 'security.password.min_length',
                'value' => '8',
                'type' => 'integer',
                'category' => 'security',
                'description' => 'Longitud mínima de contraseña',
                'is_public' => false
            ],
            
            // Reports settings
            [
                'key' => 'reports.default_format',
                'value' => 'pdf',
                'type' => 'string',
                'category' => 'reports',
                'description' => 'Formato por defecto de reportes',
                'is_public' => true
            ]
        ];

        foreach ($settings as $setting) {
            TenantSetting::create(array_merge($setting, ['tenant_id' => $tenant->id]));
        }
    }

    /**
     * Assign existing users to tenants
     */
    private function assignUsersToTenants(Tenant $defaultTenant, Tenant $premiumTenant, Tenant $trialTenant): void
    {
        // Get existing users
        $adminUser = User::where('email', 'admin@avocontrol.com')->first();
        $vendedorUser = User::where('email', 'vendedor@avocontrol.com')->first();
        $contadorUser = User::where('email', 'contador@avocontrol.com')->first();

        // Assign admin user to all tenants as owner
        if ($adminUser) {
            $this->assignUserToTenant($adminUser, $defaultTenant, 'owner');
            $this->assignUserToTenant($adminUser, $premiumTenant, 'owner');
            $this->assignUserToTenant($adminUser, $trialTenant, 'owner');
            
            // Set default tenant for admin
            $adminUser->update(['current_tenant_id' => $defaultTenant->id]);
        }

        // Assign vendedor to default and premium tenants
        if ($vendedorUser) {
            $this->assignUserToTenant($vendedorUser, $defaultTenant, 'vendedor');
            $this->assignUserToTenant($vendedorUser, $premiumTenant, 'vendedor');
            
            // Set default tenant for vendedor
            $vendedorUser->update(['current_tenant_id' => $defaultTenant->id]);
        }

        // Assign contador to all tenants
        if ($contadorUser) {
            $this->assignUserToTenant($contadorUser, $defaultTenant, 'contador');
            $this->assignUserToTenant($contadorUser, $premiumTenant, 'contador');
            $this->assignUserToTenant($contadorUser, $trialTenant, 'contador');
            
            // Set default tenant for contador
            $contadorUser->update(['current_tenant_id' => $defaultTenant->id]);
        }

        // Get super admin users and assign them to all tenants
        $superAdmins = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->get();

        foreach ($superAdmins as $superAdmin) {
            $this->assignUserToTenant($superAdmin, $defaultTenant, 'admin');
            $this->assignUserToTenant($superAdmin, $premiumTenant, 'admin');
            $this->assignUserToTenant($superAdmin, $trialTenant, 'admin');
            
            // Set default tenant if not already set
            if (!$superAdmin->current_tenant_id) {
                $superAdmin->update(['current_tenant_id' => $defaultTenant->id]);
            }
        }
    }

    /**
     * Assign user to tenant
     */
    private function assignUserToTenant(User $user, Tenant $tenant, string $role): void
    {
        TenantUser::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role_within_tenant' => $role,
            'status' => 'active',
            'joined_at' => now(),
            'permissions' => $this->getPermissionsForRole($role),
            'settings' => [
                'theme' => 'default',
                'notifications' => [
                    'email' => true,
                    'push' => true,
                    'database' => true
                ]
            ]
        ]);
    }

    /**
     * Get permissions for role
     */
    private function getPermissionsForRole(string $role): array
    {
        $permissions = [
            'owner' => ['*'], // All permissions
            'admin' => [
                'users.manage',
                'suppliers.manage',
                'customers.manage',
                'lots.manage',
                'sales.manage',
                'payments.manage',
                'reports.view',
                'reports.export',
                'settings.manage'
            ],
            'manager' => [
                'suppliers.view',
                'customers.manage',
                'lots.manage',
                'sales.manage',
                'payments.view',
                'reports.view',
                'reports.export'
            ],
            'vendedor' => [
                'customers.view',
                'customers.create',
                'customers.edit',
                'sales.manage',
                'lots.view',
                'reports.view'
            ],
            'contador' => [
                'suppliers.manage',
                'payments.manage',
                'lots.view',
                'sales.view',
                'reports.view',
                'reports.export'
            ],
            'member' => [
                'lots.view',
                'sales.view',
                'customers.view',
                'suppliers.view'
            ],
            'viewer' => [
                'lots.view',
                'sales.view',
                'reports.view'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}