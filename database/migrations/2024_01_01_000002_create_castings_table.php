<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dökümler tablosunu oluşturur
     * Her döküm yaklaşık 120 dakika sürer
     * Her ocağın kendi döküm listesi + genel döküm listesi
     */
    public function up(): void
    {
        Schema::create('castings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('furnace_id')->constrained('furnaces')->onDelete('cascade');
            $table->integer('casting_number_per_furnace'); // Ocak bazında döküm sırası (1, 2, 3...)
            $table->integer('global_casting_number'); // Genel döküm sırası (tüm ocaklar)
            $table->timestamp('start_time'); // Döküm başlangıç zamanı
            $table->timestamp('end_time')->nullable(); // Döküm bitiş zamanı
            $table->integer('duration_minutes')->default(120); // Döküm süresi (dakika)
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->date('production_date'); // Üretim tarihi (günlük raporlama için)
            $table->text('notes')->nullable(); // Ek notlar
            $table->timestamps();
            
            // İndeksler
            $table->index(['furnace_id', 'casting_number_per_furnace']);
            $table->index(['production_date']);
            $table->index(['global_casting_number']);
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
