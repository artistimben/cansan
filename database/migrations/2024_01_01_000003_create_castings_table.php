<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dökümleri oluşturan migration
     * Her ocaktan alınan döküm kayıtları
     */
    public function up(): void
    {
        Schema::create('castings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('furnace_id')->constrained('furnaces')->onDelete('cascade');
            $table->string('casting_number', 100); // Döküm numarası (string: "OCAK1-1.DÖKÜM")
            $table->datetime('casting_date'); // Döküm tarihi
            $table->string('shift'); // Vardiya bilgisi (Gündüz, Gece)
            $table->string('operator_name')->nullable(); // Operatör adı
            $table->decimal('target_temperature', 8, 2)->nullable(); // Hedef sıcaklık
            $table->decimal('final_temperature', 8, 2)->nullable(); // Son sıcaklık
            $table->text('notes')->nullable(); // Döküm notları
            $table->text('completion_notes')->nullable(); // Tamamlama notları
            $table->text('cancellation_reason')->nullable(); // İptal nedeni
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('started_at')->nullable(); // Başlama zamanı
            $table->timestamp('completed_at')->nullable(); // Bitiş zamanı
            $table->timestamps();
            
            // Döküm numarası benzersiz olmalı
            $table->unique('casting_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('castings');
    }
};
