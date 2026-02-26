<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ambulances', function (Blueprint $table) {
            $table->string('type', 32)->default('basic')->after('plate_number')->comment('basic, advanced, icu');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('driver_skill', 32)->nullable()->after('hospital_id')->comment('basic, advanced, critical_care');
        });
    }

    public function down(): void
    {
        Schema::table('ambulances', fn (Blueprint $table) => $table->dropColumn('type'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('driver_skill'));
    }
};
