<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->unsignedTinyInteger('level')->default(1)->after('is_active')->comment('1=primary, 2=secondary, 3=tertiary');
        });
        Schema::table('emergencies', function (Blueprint $table) {
            $table->unsignedSmallInteger('eta_minutes')->nullable()->after('arrived_at')->comment('Estimated minutes for ambulance to arrive');
            $table->string('severity_category', 32)->nullable()->after('severity_label')->comment('Critical, Moderate, Mild');
        });
    }

    public function down(): void
    {
        Schema::table('hospitals', fn (Blueprint $table) => $table->dropColumn('level'));
        Schema::table('emergencies', fn (Blueprint $table) => $table->dropColumn(['eta_minutes', 'severity_category']));
    }
};
