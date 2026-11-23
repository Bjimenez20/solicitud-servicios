<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            // Usuario que creó el registro (cliente o proveedor)
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Cliente beneficiario del servicio (tabla normalizada clients)
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            // Proveedor / distribuidor asociado (tabla normalizada providers)
            $table->foreignId('provider_id')
                ->nullable()
                ->constrained('providers')
                ->nullOnDelete();

            // Instalador asignado por Bodega al agendar
            $table->foreignId('installer_id')
                ->nullable()
                ->constrained('installers')
                ->nullOnDelete();

            // Tipo de servicio: cliente (instalacion, mantenimiento) / proveedor (instalacion, mantenimiento, auditoria)
            $table->enum('type', ['instalacion', 'mantenimiento', 'auditoria']);

            // Programación y seguimiento
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->text('message_subject')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', [
                'pendiente',
                'confirmada',
                'despachado',
                'instalado',
                'realizado',
                'cancelada',
            ])->default('pendiente');

            $table->text('provider_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
