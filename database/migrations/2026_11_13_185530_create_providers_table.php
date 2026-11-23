<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('business_name'); // Razón social Distribuidor -> Nombre Proveedor
            $table->string('nit')->nullable(); // NIT Distribuidor -> Identificación Proveedor
            $table->string('city')->nullable(); // Ciudad
            $table->string('address')->nullable(); // Dirección
            $table->string('phone')->nullable(); // Teléfono
            $table->string('email')->nullable(); // Correo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
