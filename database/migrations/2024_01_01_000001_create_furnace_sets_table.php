<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ocak setlerini oluşturan migration
     * Her set 2 ocak içerir (Set 1: Ocak 1-2, Set 2: Ocak 3-4, Set 3: Ocak 5-6)
     */
    public function up(): void
    {
        Schema::create('furnace_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Set 1, Set 2, Set 3
            $table->integer('set_number'); // 1, 2, 3
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furnace_sets');
    }
};
