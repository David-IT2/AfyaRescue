<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ambulance_id')->constrained()->cascadeOnDelete();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->unsignedSmallInteger('eta_minutes')->nullable();
            $table->string('assignment_reason', 64)->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();
            $table->index(['ambulance_id', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_logs');
    }
};
