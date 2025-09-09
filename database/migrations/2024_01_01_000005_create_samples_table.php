<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prova örneklerini oluşturan migration
     * Her dökümden alınan prova değerleri
     */
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('casting_id')->constrained('castings')->onDelete('cascade');
            $table->integer('sample_number'); // Prova numarası
            $table->datetime('sample_time'); // Prova alınma tarihi ve saati
            $table->enum('sample_type', ['regular', 'ladle', 'final', 'control'])->default('regular'); // Prova tipi
            
            // Kimyasal değerler
            $table->decimal('carbon_content', 5, 3); // Karbon % (zorunlu)
            $table->decimal('manganese_content', 5, 3); // Mangan % (zorunlu)
            $table->decimal('silicon_content', 5, 3)->nullable(); // Silisyum %
            
            // Fiziksel değerler
            $table->decimal('temperature', 8, 2); // Sıcaklık (zorunlu)
            
            // Kalite kontrol
            $table->foreignId('quality_standard_id')->nullable()->constrained('quality_standards')->onDelete('set null');
            $table->enum('quality_status', ['pending', 'approved', 'rejected', 'needs_adjustment'])->default('pending');
            $table->text('quality_notes')->nullable(); // Kalite kontrol notları
            $table->string('sampled_by')->nullable(); // Prova alan kişi
            $table->string('analyzed_by')->nullable(); // Analiz yapan kişi
            $table->datetime('analyzed_at')->nullable(); // Analiz tarihi
            $table->text('notes')->nullable(); // Genel notlar
            
            // Telsiz ile bildirilen değerler
            $table->boolean('reported_via_radio')->default(false); // Telsiz ile bildirildi mi?
            $table->datetime('reported_at')->nullable(); // Bildirim tarihi
            $table->string('reported_by')->nullable(); // Bildiren kişi
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
