<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();
            $table->unsignedInteger('total_units')->default(0);
            $table->string('vehicle_type', 50);
            $table->decimal('transport_price', 12, 2)->default(0);
            $table->decimal('unit_installation_price', 12, 2)->default(0);
            $table->decimal('installation_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->timestamps();

            $table->unique('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_tariffs');
    }
};
