<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Dispensador Jumbo
        $url1 = 'https://http2.mlstatic.com/D_NQ_NP_923699-MLA116765554383_082026-O.webp';
        $name1 = 'dispensador_jumbo.webp';
        Storage::disk('public')->put('products/' . $name1, file_get_contents($url1));

        Product::create([
            'name' => 'DISPENSADOR JUMBO ACERO CHAPA PAN',
            'image' => 'products/' . $name1,
            'description' => 'Instalación y mantenimiento de unidades residenciales.'
        ]);

        // 2. Dispensador Toa Center Pull
        $url2 = 'https://media.falabella.com/sodimacCO/553728_2/w=1160';
        $name2 = 'dispensador_toa_center_pull.webp';
        Storage::disk('public')->put('products/' . $name2, file_get_contents($url2));

        Product::create([
            'name' => 'DISPENSADOR TOA CENTER PULL',
            'image' => 'products/' . $name2,
            'description' => 'Instalación y mantenimiento de unidades residenciales.'
        ]);

        // 3. Dispensador Limpión Pared
        $url3 = 'https://torkco.vteximg.com.br/arquivos/ids/156235/81060_01.jpg?v=639126621756470000';
        $name3 = 'dispensador_limpion_pared.jpg'; // Nota: esta URL original es .jpg
        Storage::disk('public')->put('products/' . $name3, file_get_contents($url3));

        Product::create([
            'name' => 'DISPENSADOR LIMPION PARED',
            'image' => 'products/' . $name3,
            'description' => 'Instalación y mantenimiento de unidades residenciales.'
        ]);
    }
}