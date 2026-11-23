<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'DISPENSADOR JUMBO ACERO CHAPA PAN',
                'filename' => 'dispensador_jumbo.webp',
                'url' => 'https://http2.mlstatic.com/D_NQ_NP_923699-MLA116765554383_082026-O.webp',
                'description' => 'Instalación y mantenimiento de unidades residenciales.',
            ],
            [
                'name' => 'DISPENSADOR TOA CENTER PULL',
                'filename' => 'dispensador_toa_center_pull.webp',
                'url' => 'https://media.falabella.com/sodimacCO/553728_2/w=1160',
                'description' => 'Instalación y mantenimiento de unidades residenciales.',
            ],
            [
                'name' => 'DISPENSADOR LIMPION PARED',
                'filename' => 'dispensador_limpion_pared.jpg',
                'url' => 'https://torkco.vteximg.com.br/arquivos/ids/156235/81060_01.jpg?v=639126621756470000',
                'description' => 'Instalación y mantenimiento de unidades residenciales.',
            ],
        ];

        Storage::disk('public')->makeDirectory('products');

        foreach ($products as $item) {
            $storagePath = 'products/' . $item['filename'];
            $savedImage = null;

            try {
                $response = Http::timeout(10)->get($item['url']);
                if ($response->successful() && $response->body() !== '') {
                    Storage::disk('public')->put($storagePath, $response->body());
                    $savedImage = $storagePath;
                }
            } catch (Throwable $e) {
                $savedImage = null;
            }

            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'image' => $savedImage,
                    'description' => $item['description'],
                ]
            );
        }
    }
}
