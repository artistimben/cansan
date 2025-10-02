<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Günlük raporlar tablosunu oluşturur
     * Her gün sabah 08:00'da günü bitirip rapor oluşturur
     */
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date'); // Rapor tarihi
            $table->integer('total_castings'); // Toplam döküm sayısı
            $table->json('furnace_castings'); // Her ocağın döküm sayısı (JSON)
            $table->integer('active_furnaces_count'); // Aktif ocak sayısı
            $table->json('maintenance_activities')->nullable(); // Bakım faaliyetleri
            $table->decimal('production_efficiency', 5, 2)->nullable(); // Üretim verimliliği %
            $table->time('shift_start_time')->default('08:00:00'); // Vardiya başlangıç
            $table->time('shift_end_time')->default('08:00:00'); // Vardiya bitiş (ertesi gün)
            $table->text('notes')->nullable(); // Günlük notlar
            $table->timestamps();
            
            // İndeks
            $table->unique('report_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
