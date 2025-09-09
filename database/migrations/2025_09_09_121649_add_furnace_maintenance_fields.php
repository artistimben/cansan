<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('furnaces', function (Blueprint $table) {
            // Döküm sayacı alanları
            $table->integer('total_castings_count')->default(0)->comment('Toplam döküm sayısı (fabrika geneli)');
            $table->integer('current_cycle_castings')->default(0)->comment('Mevcut döngüdeki döküm sayısı (refraktör değişiminden sonra)');
            $table->date('last_refractory_change')->nullable()->comment('Son refraktör değişim tarihi');
            $table->integer('castings_since_refractory')->default(0)->comment('Refraktör değişiminden sonraki döküm sayısı');
            
            // Bakım ve durum notları
            $table->text('maintenance_notes')->nullable()->comment('Bakım notları');
            $table->text('shutdown_reason')->nullable()->comment('Duruş nedeni');
            $table->text('refractory_notes')->nullable()->comment('Refraktör değişim notları');
            $table->text('operational_notes')->nullable()->comment('Operasyonel notlar');
            
            // Döküm sayacı sıfırlama tarihleri
            $table->timestamp('last_count_reset')->nullable()->comment('Son döküm sayacı sıfırlama tarihi');
            $table->enum('reset_type', ['refractory_change', 'maintenance', 'manual'])->nullable()->comment('Sıfırlama türü');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('furnaces', function (Blueprint $table) {
            $table->dropColumn([
                'total_castings_count',
                'current_cycle_castings', 
                'last_refractory_change',
                'castings_since_refractory',
                'maintenance_notes',
                'shutdown_reason',
                'refractory_notes',
                'operational_notes',
                'last_count_reset',
                'reset_type'
            ]);
        });
    }
};
