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
        Schema::create('furnace_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('furnace_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'idle', 'maintenance', 'inactive', 'refractory_change', 'shutdown'])->comment('Ocak durumu');
            $table->enum('previous_status', ['active', 'idle', 'maintenance', 'inactive', 'refractory_change', 'shutdown'])->nullable()->comment('Önceki durum');
            $table->text('reason')->nullable()->comment('Durum değişiklik nedeni');
            $table->text('notes')->nullable()->comment('Ek notlar');
            $table->string('operator_name')->nullable()->comment('Operatör adı');
            $table->timestamp('status_changed_at')->useCurrent()->comment('Durum değişiklik tarihi');
            $table->integer('castings_count_at_change')->default(0)->comment('Durum değişikliğindeki döküm sayısı');
            $table->boolean('count_reset')->default(false)->comment('Döküm sayacı sıfırlandı mı?');
            $table->enum('reset_type', ['refractory_change', 'maintenance', 'manual'])->nullable()->comment('Sıfırlama türü');
            $table->timestamps();
            
            $table->index(['furnace_id', 'status_changed_at']);
            $table->index(['status', 'status_changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furnace_status_logs');
    }
};
