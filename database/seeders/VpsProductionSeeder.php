<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\QualityGrade;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class VpsProductionSeeder extends Seeder
{
    /**
     * Run the VPS production database seeds.
     * This seeder is specifically designed for the VPS production environment.
     */
    public function run(): void
    {
        $this->call([
            // Essential seeders for production
            RolesSeeder::class,
            PermissionsSeeder::class,
            QualityGradeSeeder::class,
            CompanySettingsSeeder::class,
        ]);

        // Create essential production users
        $this->createProductionUsers();
        
        // Setup essential settings
        $this->setupProductionSettings();
        
        $this->command->info('✅ VPS Production seeding completed successfully!');
    }

    /**
     * Create essential users for production
     */
    private function createProductionUsers(): void
    {
        $this->command->info('🔐 Creating production users...');

        // Create super admin user
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        // Developer/Super Admin User
        $developer = User::firstOrCreate([
            'email' => 'developer@avocontrol.com'
        ], [
            'name' => 'Developer AvoControl',
            'email' => 'developer@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        if ($superAdminRole && !$developer->hasRole('super_admin')) {
            $developer->roles()->attach($superAdminRole->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Main Admin User  
        $admin = User::firstOrCreate([
            'email' => 'admin@avocontrol.com'
        ], [
            'name' => 'Administrador AvoControl',
            'email' => 'admin@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Production Salesperson
        $vendedorRole = Role::where('name', 'vendedor')->first();
        $vendedor = User::firstOrCreate([
            'email' => 'vendedor@avocontrol.com'
        ], [
            'name' => 'Vendedor AvoControl',
            'email' => 'vendedor@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'vendedor',
            'email_verified_at' => now(),
        ]);

        if ($vendedorRole && !$vendedor->hasRole('vendedor')) {
            $vendedor->roles()->attach($vendedorRole->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('✅ Production users created successfully');
    }

    /**
     * Setup production-specific settings
     */
    private function setupProductionSettings(): void
    {
        $this->command->info('⚙️ Setting up production configuration...');

        // Company settings for production
        $companySettings = [
            'company_name' => 'AvoControl Pro - Centro de Acopio',
            'company_rfc' => '',
            'company_address' => '',
            'company_city' => 'Uruapan',
            'company_state' => 'Michoacán',
            'company_postal_code' => '',
            'company_phone' => '',
            'company_email' => 'contacto@avocontrol.com',
            'company_website' => '',
            'company_logo' => '',
            'company_description' => 'Sistema de Control de Aguacate para Centros de Acopio',
        ];

        foreach ($companySettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'company'],
                [
                    'value' => $value,
                    'description' => $this->getSettingDescription($key),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Email/SMTP settings (empty for manual configuration)
        $emailSettings = [
            'mail_mailer' => 'smtp',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@avocontrol.com',
            'mail_from_name' => 'AvoControl Pro',
        ];

        foreach ($emailSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'email'],
                [
                    'value' => $value,
                    'description' => $this->getEmailSettingDescription($key),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Push notification settings
        $pushSettings = [
            'vapid_public_key' => '',
            'vapid_private_key' => '',
            'vapid_subject' => 'mailto:admin@avocontrol.com',
            'push_enabled' => false,
        ];

        foreach ($pushSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'push'],
                [
                    'value' => $value,
                    'description' => $this->getPushSettingDescription($key),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('✅ Production settings configured successfully');
    }

    /**
     * Get setting description for company fields
     */
    private function getSettingDescription(string $field): string
    {
        $descriptions = [
            'company_name' => 'Nombre de la empresa',
            'company_rfc' => 'RFC de la empresa',
            'company_address' => 'Dirección de la empresa',
            'company_city' => 'Ciudad donde se ubica la empresa',
            'company_state' => 'Estado donde se ubica la empresa',
            'company_postal_code' => 'Código postal de la empresa',
            'company_phone' => 'Teléfono de contacto de la empresa',
            'company_email' => 'Email de contacto de la empresa',
            'company_website' => 'Sitio web de la empresa',
            'company_logo' => 'URL del logo de la empresa',
            'company_description' => 'Descripción de la empresa para reportes'
        ];

        return $descriptions[$field] ?? '';
    }

    /**
     * Get email setting description
     */
    private function getEmailSettingDescription(string $field): string
    {
        $descriptions = [
            'mail_mailer' => 'Proveedor de email (smtp, sendmail, etc.)',
            'mail_host' => 'Servidor SMTP para envío de emails',
            'mail_port' => 'Puerto del servidor SMTP',
            'mail_username' => 'Usuario para autenticación SMTP',
            'mail_password' => 'Contraseña para autenticación SMTP',
            'mail_encryption' => 'Tipo de encriptación (tls, ssl)',
            'mail_from_address' => 'Dirección de email remitente',
            'mail_from_name' => 'Nombre del remitente para emails'
        ];

        return $descriptions[$field] ?? '';
    }

    /**
     * Get push setting description
     */
    private function getPushSettingDescription(string $field): string
    {
        $descriptions = [
            'vapid_public_key' => 'Clave pública VAPID para push notifications',
            'vapid_private_key' => 'Clave privada VAPID para push notifications',
            'vapid_subject' => 'Subject para identificación VAPID',
            'push_enabled' => 'Estado de las notificaciones push'
        ];

        return $descriptions[$field] ?? '';
    }
}