<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Duplicate döküm numaralarını düzelt
        $castings = DB::table('castings')
            ->select('id', 'casting_number', 'furnace_id', 'created_at')
            ->orderBy('furnace_id')
            ->orderBy('created_at')
            ->get();

        $furnaceCastingNumbers = [];
        
        foreach ($castings as $casting) {
            $furnaceId = $casting->furnace_id;
            
            if (!isset($furnaceCastingNumbers[$furnaceId])) {
                $furnaceCastingNumbers[$furnaceId] = 1;
            }
            
            // Ocak adını al
            $furnace = DB::table('furnaces')->where('id', $furnaceId)->first();
            if (!$furnace) continue;
            
            $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
            $newCastingNumber = $furnaceName . '-' . $furnaceCastingNumbers[$furnaceId] . '.DÖKÜM';
            
            // Döküm numarasını güncelle
            DB::table('castings')
                ->where('id', $casting->id)
                ->update(['casting_number' => $newCastingNumber]);
            
            $furnaceCastingNumbers[$furnaceId]++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri alınamaz - döküm numaraları değiştirildi
    }
};