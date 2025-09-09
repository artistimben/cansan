<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ham madde ekleme kayıtlarını oluşturan migration
     * Prova sonuçlarına göre ocağa eklenen malzemeler
     */
    public function up(): void
    {
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->onDelete('cascade');
            $table->foreignId('casting_id')->constrained('castings')->onDelete('cascade');
            
            $table->string('material_type'); // Eklenen malzeme (karbon, mangan, vb.)
            $table->decimal('amount_kg', 10, 2); // Eklenen miktar (kg)
            $table->decimal('target_percentage', 5, 3)->nullable(); // Hedef yüzde
            $table->decimal('actual_percentage_before', 5, 3)->nullable(); // Ekleme öncesi yüzde
            $table->decimal('actual_percentage_after', 5, 3)->nullable(); // Ekleme sonrası yüzde
            
            $table->datetime('adjustment_date'); // Ekleme tarihi
            $table->string('added_by'); // Ekleyen kişi
            $table->enum('adjustment_reason', ['low_content', 'high_content', 'quality_improvement', 'customer_requirement']);
            $table->text('notes')->nullable(); // Ekleme notları
            
            $table->boolean('is_successful')->nullable(); // Ekleme başarılı mı?
            $table->datetime('verified_at')->nullable(); // Doğrulama tarihi
            $table->string('verified_by')->nullable(); // Doğrulayan kişi
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
