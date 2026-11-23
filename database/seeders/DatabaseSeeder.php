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
        $providerUser = User::create([
            'name' => 'Proveedor Principal',
            'email' => 'proveedor@sistema.com',
            'password' => Hash::make('Proveedor123*'),
            'role' => 'proveedor',
        ]);

        $provider = Provider::create([
            'user_id' => $providerUser->id,
            'business_name' => 'Distribuidora Principal S.A.S.',
            'nit' => '900123456-1',
            'city' => 'Bogotá',
            'address' => 'Cra 15 # 100-20',
            'phone' => '3001112233',
            'email' => 'proveedor@sistema.com',
        ]);

        // 2. Usuario + Perfil Cliente
        $clientUser = User::create([
            'name' => 'Cliente Ejemplo',
            'email' => 'cliente@sistema.com',
            'password' => Hash::make('Cliente123*'),
            'role' => 'cliente',
        ]);

        $client = Client::create([
            'user_id' => $clientUser->id,
            'business_name' => 'Cliente Ejemplo S.A.S.',
            'nit' => '900987654-3',
            'city' => 'Bogotá',
            'address' => 'Calle 72 # 10-34',
            'phone' => '3004445566',
            'email' => 'cliente@sistema.com',
        ]);

        // Sede o cliente adicional sin login propio (útil para pruebas de asignación del proveedor)
        $secondClient = Client::create([
            'user_id' => null,
            'business_name' => 'Comercializadora Sur Ltda.',
            'nit' => '800555444-2',
            'city' => 'Medellín',
            'address' => 'Cll 50 # 45-12',
            'phone' => '3017778899',
            'email' => 'contacto@comercializadorasur.com',
        ]);

        // Relación en tabla pivote client_provider
        $provider->clients()->syncWithoutDetaching([$client->id, $secondClient->id]);

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
    }
}
