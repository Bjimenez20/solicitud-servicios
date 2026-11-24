<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Installer;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuario + Perfil Proveedor / Distribuidor
        User::create([
            'name' => 'Papeles Nacionales',
            'email' => 'bjimenez@overall.com.co',
            'password' => Hash::make('1234'),
            'role' => 'proveedor',
        ]);

        User::create([
            'name' => 'Distribuidor',
            'email' => 'jimenezbrayan825@gmail.com',
            'password' => Hash::make('1234'),
            'role' => 'cliente',
        ]);

        // 3. Usuario Bodega (rol operativo)
        User::create([
            'name' => 'Bodega Ejemplo',
            'email' => 'bodega@sistema.com',
            'password' => Hash::make('Bodega123*'),
            'role' => 'bodega',
        ]);

        // 4. Instalador base
        if (Installer::count() === 0) {
            Installer::create([
                'name' => 'Técnico Instalador 1',
                'phone' => '3009990011',
                'active' => true,
            ]);
        }

        // 5. Productos / Equipos
        $this->call([
            ProductSeeder::class,
        ]);

        $this->call([
            ProviderSeeder::class,
        ]);
    }
}
