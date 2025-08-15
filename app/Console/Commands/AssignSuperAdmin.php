<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignSuperAdmin extends Command
{
    protected $signature = 'user:make-super-admin {email}';
    protected $description = 'Assign super admin role to a user';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ User with email {$email} not found.");
            return Command::FAILURE;
        }
        
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if (!$superAdminRole) {
            $this->error("❌ Super admin role not found.");
            return Command::FAILURE;
        }
        
        if ($user->hasRole('super_admin')) {
            $this->info("✅ User {$email} already has super admin role.");
        } else {
            $user->roles()->attach($superAdminRole->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info("✅ Super admin role assigned to {$email}");
        }
        
        // Reset password too
        $user->password = bcrypt('password123');
        $user->save();
        $this->info("🔑 Password reset to: password123");
        
        return Command::SUCCESS;
    }
}