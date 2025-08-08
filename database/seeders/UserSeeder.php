<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear usuarios del sistema
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Vendedor Principal',
            'email' => 'vendedor@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'vendedor',
        ]);

        User::create([
            'name' => 'Contador General',
            'email' => 'contador@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'contador',
        ]);

        // Crear usuarios adicionales para pruebas
        User::create([
            'name' => 'María García',
            'email' => 'maria.garcia@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'vendedor',
        ]);

        User::create([
            'name' => 'Carlos Ruiz',
            'email' => 'carlos.ruiz@avocontrol.com',
            'password' => Hash::make('password123'),
            'role' => 'contador',
        ]);
    }
}
