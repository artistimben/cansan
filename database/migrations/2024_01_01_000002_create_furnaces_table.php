<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ocakları oluşturan migration
     * Toplam 6 ocak: Her sette 2 ocak, sadece 1 tanesi aktif çalışır
     */
    public function up(): void
    {
        Schema::create('furnaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('furnace_set_id')->constrained('furnace_sets')->onDelete('cascade');
            $table->string('name'); // Ocak 1, Ocak 2, vb.
            $table->integer('furnace_number'); // 1, 2, 3, 4, 5, 6
            $table->enum('status', ['active', 'idle', 'maintenance', 'inactive'])->default('idle'); // Ocak durumu
            $table->boolean('is_operational')->default(true); // Ocak çalışır durumda mı?
            $table->text('description')->nullable();
            $table->decimal('capacity', 8, 2)->nullable(); // Ton cinsinden kapasite
            $table->decimal('max_temperature', 8, 2)->nullable(); // Maksimum sıcaklık
            $table->decimal('current_temperature', 8, 2)->nullable(); // Mevcut sıcaklık
            $table->enum('fuel_type', ['natural_gas', 'electricity', 'coal', 'oil', 'mixed'])->nullable();
            $table->date('installation_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->integer('maintenance_interval_days')->default(100); // Döküm sayısı bazında
            $table->timestamp('status_updated_at')->nullable();
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
