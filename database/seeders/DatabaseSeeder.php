<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Proveedor
        User::create([
            'name' => 'Proveedor Principal',
            'email' => 'proveedor@sistema.com',
            'password' => Hash::make('Proveedor123*'),
            'role' => 'proveedor',
        ]);

        // Crear Cliente de prueba
        User::create([
            'name' => 'Cliente Ejemplo',
            'email' => 'cliente@sistema.com',
            'password' => Hash::make('Cliente123*'),
            'role' => 'cliente',
        ]);

        // Crear Bodega de prueba
        User::create([
            'name' => 'Bodega Ejemplo',
            'email' => 'bodega@sistema.com',
            'password' => Hash::make('Bodega123*'),
            'role' => 'bodega',
        ]);
    }
}
