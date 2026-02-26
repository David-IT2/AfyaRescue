<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emergencies', function (Blueprint $table) {
            $table->text('doctor_notes')->nullable()->after('notes');
            $table->text('admission_info')->nullable()->after('doctor_notes');
            $table->text('discharge_summary')->nullable()->after('admission_info');
        });
    }

    public function down(): void
    {
        Schema::table('emergencies', fn (Blueprint $table) => $table->dropColumn(['doctor_notes', 'admission_info', 'discharge_summary']));
    }
};
