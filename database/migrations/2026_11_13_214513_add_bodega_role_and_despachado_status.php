<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Amplía el ENUM de estados en service_requests
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pendiente', 'confirmada', 'cancelada', 'despachado', 'instalado') NOT NULL DEFAULT 'pendiente'");

        // Amplía el ENUM de roles en users
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('cliente', 'proveedor', 'bodega') NOT NULL DEFAULT 'cliente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pendiente', 'confirmada', 'cancelada', 'despachado', 'instalado') NOT NULL DEFAULT 'pendiente'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('cliente', 'proveedor', 'bodega') NOT NULL DEFAULT 'cliente'");
    }
};
