<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name'        => 'DISPENSADOR AZUR MINI 2',
                'filename'    => 'disp. azur mini 2.JPG',
                'description' => 'Dispensador compacto ideal para baños y áreas de tráfico moderado.',
            ],
            [
                'name'        => 'DISPENSADOR CENTER PULL',
                'filename'    => 'dispensador CENTER PULL.png',
                'description' => 'Dispensador de extracción central para toallas de papel en rollo.',
            ],
            [
                'name'        => 'DISPENSADOR HIGIÉNICO ACERO CHAPA',
                'filename'    => 'dispensador hig. ACERO CHAPA.png',
                'description' => 'Dispensador de papel higiénico fabricado en acero inoxidable con cerradura de seguridad.',
            ],
            [
                'name'        => 'DISPENSADOR LIMPIÓN PARED',
                'filename'    => 'dispensador LIMPION PARED.png',
                'description' => 'Dispensador de pared para rollos de limpión industrial de alto rendimiento.',
            ],
            [
                'name'        => 'DISPENSADOR TOALLAS M-FOLD PEQUEÑO',
                'filename'    => 'dispensador toallas M- FOLF PEQ.png',
                'description' => 'Dispensador compacto para toallas interfoliadas tipo M-Fold.',
            ],
            [
                'name'        => 'DISPENSADOR TOALLAS M-FOLD GRANDE',
                'filename'    => 'dispensador toallas M-FOLD GDE.png',
                'description' => 'Dispensador de alta capacidad para toallas interfoliadas tipo M-Fold.',
            ],
            [
                'name'        => 'DISPENSADOR TOALLAS PRECORTE DCO',
                'filename'    => 'dispensador toallas PRECORTE DCO.png',
                'description' => 'Dispensador de toallas en rollo con sistema de precorte controlado.',
            ],
        ];

        // Ruta donde tienes la carpeta original con las imágenes (ajusta si está en public/products)
        $sourceDir = database_path('seeders/products');

        Storage::disk('public')->makeDirectory('products');

        foreach ($products as $item) {
            $sourceFile = $sourceDir . DIRECTORY_SEPARATOR . $item['filename'];
            $storagePath = 'products/' . $item['filename'];
            $savedImage = null;

            if (File::exists($sourceFile)) {
                Storage::disk('public')->put($storagePath, File::get($sourceFile));
                $savedImage = $storagePath;
            } elseif (Storage::disk('public')->exists($storagePath)) {
                // Si ya están directamente en storage/app/public/products
                $savedImage = $storagePath;
            }

            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'image'       => $savedImage,
                    'description' => $item['description'],
                ]
            );
        }
    }
}
