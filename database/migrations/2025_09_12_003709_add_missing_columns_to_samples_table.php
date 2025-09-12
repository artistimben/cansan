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
        Schema::table('samples', function (Blueprint $table) {
            $table->decimal('sulfur_content', 8, 3)->nullable()->after('silicon_content');
            $table->decimal('phosphorus_content', 8, 3)->nullable()->after('sulfur_content');
            $table->decimal('copper_content', 8, 3)->nullable()->after('phosphorus_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->dropColumn(['sulfur_content', 'phosphorus_content', 'copper_content']);
        });
    }
};
