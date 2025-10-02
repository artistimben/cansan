<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kalite kontrol değerleri tablosunu oluşturur
     * Karbon, Silisyum, Mangan gibi prova değerleri
     */
    public function up(): void
    {
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('casting_id')->constrained('castings')->onDelete('cascade');
            $table->decimal('carbon_percentage', 5, 3)->nullable(); // Karbon yüzdesi
            $table->decimal('silicon_percentage', 5, 3)->nullable(); // Silisyum yüzdesi
            $table->decimal('manganese_percentage', 5, 3)->nullable(); // Mangan yüzdesi
            $table->decimal('phosphorus_percentage', 5, 3)->nullable(); // Fosfor yüzdesi
            $table->decimal('sulfur_percentage', 5, 3)->nullable(); // Kükürt yüzdesi
            $table->decimal('chromium_percentage', 5, 3)->nullable(); // Krom yüzdesi
            $table->decimal('nickel_percentage', 5, 3)->nullable(); // Nikel yüzdesi
            $table->decimal('temperature', 6, 2)->nullable(); // Sıcaklık (°C)
            $table->enum('test_result', ['passed', 'failed', 'pending'])->default('pending');
            $table->timestamp('test_time')->nullable(); // Test zamanı
            $table->string('tested_by')->nullable(); // Testi yapan kişi
            $table->text('remarks')->nullable(); // Açıklamalar
            $table->timestamps();
            
            // İndeks
            $table->index(['casting_id']);
            $table->index(['test_result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_controls');
    }
};
