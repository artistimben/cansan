<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kalite standartlarını oluşturan migration
     * Çelik türlerine göre kabul edilebilir değer aralıkları
     */
    public function up(): void
    {
        Schema::create('quality_standards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standart adı (ST37, ST52, vb.)
            $table->string('steel_grade'); // Çelik sınıfı
            
            // Karbon limitleri
            $table->decimal('carbon_min', 5, 3)->nullable();
            $table->decimal('carbon_max', 5, 3)->nullable();
            
            // Mangan limitleri
            $table->decimal('manganese_min', 5, 3)->nullable();
            $table->decimal('manganese_max', 5, 3)->nullable();
            
            // Silisyum limitleri
            $table->decimal('silicon_min', 5, 3)->nullable();
            $table->decimal('silicon_max', 5, 3)->nullable();
            
            // Fosfor limitleri
            $table->decimal('phosphorus_min', 5, 3)->nullable();
            $table->decimal('phosphorus_max', 5, 3)->nullable();
            
            // Kükürt limitleri
            $table->decimal('sulfur_min', 5, 3)->nullable();
            $table->decimal('sulfur_max', 5, 3)->nullable();
            
            // Krom limitleri
            $table->decimal('chromium_min', 5, 3)->nullable();
            $table->decimal('chromium_max', 5, 3)->nullable();
            
            // Nikel limitleri
            $table->decimal('nickel_min', 5, 3)->nullable();
            $table->decimal('nickel_max', 5, 3)->nullable();
            
            // Molibden limitleri
            $table->decimal('molybdenum_min', 5, 3)->nullable();
            $table->decimal('molybdenum_max', 5, 3)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_standards');
    }
};
