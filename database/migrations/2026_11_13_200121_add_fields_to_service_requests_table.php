<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('distributor_name')->after('client_id');
            $table->string('distributor_nit')->after('distributor_name');
            $table->string('associated_client')->nullable()->after('distributor_nit');
            $table->string('client_nit')->nullable()->after('associated_client');
            $table->string('city')->after('client_nit');
            $table->string('executive')->after('city');
            $table->string('segment')->after('type'); // Segmento (Educación, etc.)
            $table->date('negotiation_date')->after('segment');
            $table->string('responsible_name')->after('negotiation_date');
            $table->string('responsible_phone')->after('responsible_name');
            $table->string('responsible_email')->after('responsible_phone');
            $table->text('message_subject')->after('responsible_email');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'distributor_name',
                'distributor_nit',
                'associated_client',
                'client_nit',
                'city',
                'executive',
                'segment',
                'negotiation_date',
                'responsible_name',
                'responsible_phone',
                'responsible_email',
                'message_subject'
            ]);
        });
    }
};
