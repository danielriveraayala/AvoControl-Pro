<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure super_admin role exists
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        if (!$superAdminRole) {
            $this->command->error('Super Admin role not found. Please run RolesSeeder first.');
            return;
        }

        // Create super admin user for developer
        $superAdmin = User::firstOrCreate([
            'email' => 'developer@avocontrol.com'
        ], [
            'name' => 'Daniel Rivera (Developer)',
            'email' => 'developer@avocontrol.com',
            'password' => Hash::make('DevPassword2024!'),
            'role' => 'super_admin', // Legacy field for backward compatibility
            'email_verified_at' => now(),
        ]);

        // Assign super_admin role
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->roles()->attach($superAdminRole->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Super Admin role assigned to developer@avocontrol.com');
        }

        // Create secondary developer account for testing
        $testDeveloper = User::firstOrCreate([
            'email' => 'test.developer@avocontrol.com'
        ], [
            'name' => 'Test Developer Account',
            'email' => 'test.developer@avocontrol.com',
            'password' => Hash::make('TestDev123!'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // Assign super_admin role to test account
        if (!$testDeveloper->hasRole('super_admin')) {
            $testDeveloper->roles()->attach($superAdminRole->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Super Admin role assigned to test.developer@avocontrol.com');
        }

        $this->command->info('');
        $this->command->info('=== DEVELOPER ACCOUNTS CREATED ===');
        $this->command->info('Primary Developer:');
        $this->command->info('  Email: developer@avocontrol.com');
        $this->command->info('  Password: DevPassword2024!');
        $this->command->info('  Access: /developer (Super Admin Panel)');
        $this->command->info('');
        $this->command->info('Test Developer:');
        $this->command->info('  Email: test.developer@avocontrol.com');
        $this->command->info('  Password: TestDev123!');
        $this->command->info('  Access: /developer (Super Admin Panel)');
        $this->command->info('');
        $this->command->warn('IMPORTANT: Change these passwords in production!');
    }
}