<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ocaklar tablosunu oluşturur
     * 6 ocak: Set 1 (1,2), Set 2 (3,4), Set 3 (5,6)
     * Her anda 3 ocak aktif olmalı
     */
    public function up(): void
    {
        Schema::create('furnaces', function (Blueprint $table) {
            $table->id();
            $table->integer('furnace_number')->unique(); // 1-6 arası ocak numarası
            $table->integer('furnace_set'); // 1, 2 veya 3 (hangi sette olduğu)
            $table->enum('status', ['active', 'maintenance', 'standby'])->default('standby');
            $table->integer('total_castings')->default(0); // Toplam döküm sayısı
            $table->integer('max_castings_before_maintenance')->default(30); // Bakım öncesi maks döküm
            $table->timestamp('last_maintenance_date')->nullable();
            $table->timestamp('next_maintenance_due')->nullable();
            $table->boolean('is_charging')->default(false); // Charging aşamasında mı
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furnaces');
    }
};
