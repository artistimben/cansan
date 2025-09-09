<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ocak sıcaklık geçmişi tablosu
     * Her ocağın sıcaklık değişikliklerini kayıt eder
     */
    public function up(): void
    {
        Schema::create('furnace_temperature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('furnace_id')->constrained('furnaces')->onDelete('cascade');
            $table->decimal('temperature', 8, 2); // Kaydedilen sıcaklık
            $table->enum('log_type', ['working', 'shutdown', 'maintenance', 'manual'])->default('working');
            $table->text('notes')->nullable(); // Sıcaklık ile ilgili notlar (ör: "1720 derecede devrildi")
            $table->string('recorded_by')->nullable(); // Kaydı yapan kişi
            $table->datetime('recorded_at'); // Sıcaklığın kaydedildiği zaman
            $table->timestamps();
            
            // Index'ler
            $table->index(['furnace_id', 'recorded_at']);
            $table->index(['furnace_id', 'log_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furnace_temperature_logs');
    }
};
