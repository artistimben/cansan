<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prova kayıtları için ek alanlar ekler
     * Bakır (copper) ve çoklu prova kayıtları (prova_data JSON)
     */
    public function up(): void
    {
        Schema::table('quality_controls', function (Blueprint $table) {
            $table->decimal('copper_percentage', 5, 3)->nullable()->after('nickel_percentage'); // Bakır (CU) yüzdesi
            $table->json('prova_data')->nullable()->after('remarks'); // Çoklu prova kayıtları
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_controls', function (Blueprint $table) {
            $table->dropColumn(['copper_percentage', 'prova_data']);
        });
    }
};

