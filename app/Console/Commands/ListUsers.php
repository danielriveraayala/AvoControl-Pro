<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users with their roles';

    public function handle()
    {
        $this->info('👥 Listing all users in the system:');
        $this->newLine();
        
        $users = User::with('roles')->get();
        
        foreach ($users as $user) {
            $this->line("📧 Email: {$user->email}");
            $this->line("👤 Name: {$user->name}");
            $this->line("🔐 Created: {$user->created_at->format('Y-m-d H:i')}");
            
            if ($user->roles->count() > 0) {
                $this->line("🎭 Roles: " . $user->roles->pluck('display_name')->join(', '));
                $this->line("🔑 Has super_admin: " . ($user->hasRole('super_admin') ? 'YES' : 'NO'));
            } else {
                $this->line("🎭 Roles: None assigned");
            }
            
            $this->line("➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖➖");
        }
        
        return Command::SUCCESS;
    }
}