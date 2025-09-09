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
        // Mevcut döküm sayılarını senkronize et
        $furnaces = \App\Models\Furnace::all();
        
        foreach ($furnaces as $furnace) {
            $totalCastings = $furnace->castings()->count();
            $currentCycleCastings = $furnace->castings()->where('created_at', '>=', $furnace->last_refractory_change ?? '1900-01-01')->count();
            
            $furnace->update([
                'total_castings_count' => $totalCastings,
                'current_cycle_castings' => $currentCycleCastings,
                'castings_since_refractory' => $currentCycleCastings
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
