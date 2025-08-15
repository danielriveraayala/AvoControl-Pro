<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class FixDeveloperRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:developer-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix developer role assignment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Checking developer role assignment...');
        
        // Find or create developer user
        $user = User::where('email', 'developer@avocontrol.com')->first();
        
        if (!$user) {
            $this->error('❌ Developer user not found. Creating one...');
            $user = User::create([
                'name' => 'Developer',
                'email' => 'developer@avocontrol.com',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);
            $this->info('✅ Developer user created.');
        } else {
            $this->info('✅ Developer user found: ' . $user->email);
        }
        
        // Find or create super_admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        if (!$superAdminRole) {
            $this->error('❌ Super admin role not found. Creating one...');
            $superAdminRole = Role::create([
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'System super administrator with full access',
                'hierarchy_level' => 8,
                'is_system' => true
            ]);
            $this->info('✅ Super admin role created.');
        } else {
            $this->info('✅ Super admin role found: ' . $superAdminRole->display_name);
        }
        
        // Check if user already has the role
        if ($user->hasRole('super_admin')) {
            $this->info('✅ User already has super_admin role.');
        } else {
            // Assign role
            $user->roles()->attach($superAdminRole->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info('✅ Super admin role assigned to developer user.');
        }
        
        // Reset password to ensure it works
        $user->password = bcrypt('password123');
        $user->save();
        $this->info('🔑 Password reset to: password123');
        
        // Test the role
        $user->refresh();
        if ($user->hasRole('super_admin')) {
            $this->info('🎉 SUCCESS: Developer user now has super_admin role!');
            $this->line('   Email: ' . $user->email);
            $this->line('   Name: ' . $user->name);
            $this->line('   Role: Super Admin');
            $this->line('   Password: password123');
        } else {
            $this->error('❌ FAILED: Role assignment did not work properly.');
        }
        
        return Command::SUCCESS;
    }
}