<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use Illuminate\Http\Request;

class ForceSetRuleController extends Controller
{
    public function enforceSetRule()
    {
        echo "<h1>Force Set Rule Enforcement</h1>";
        
        // Tüm ocakları idle yap
        Furnace::query()->update(['status' => 'idle', 'status_updated_at' => now()]);
        echo "<p>✅ All furnaces set to idle</p>";
        
        // Set 1'den sadece Ocak 1'i aktif yap
        $furnace1 = Furnace::find(1);
        $furnace1->update(['status' => 'active', 'status_updated_at' => now()]);
        echo "<p>✅ Furnace 1 set to active</p>";
        
        // Set 2'den sadece Ocak 3'ü aktif yap
        $furnace3 = Furnace::find(3);
        $furnace3->update(['status' => 'active', 'status_updated_at' => now()]);
        echo "<p>✅ Furnace 3 set to active</p>";
        
        // Set 3'ten sadece Ocak 5'i aktif yap
        $furnace5 = Furnace::find(5);
        $furnace5->update(['status' => 'active', 'status_updated_at' => now()]);
        echo "<p>✅ Furnace 5 set to active</p>";
        
        echo "<h2>Final Status:</h2>";
        foreach (Furnace::all() as $furnace) {
            $statusColor = $furnace->status === 'active' ? 'green' : 'orange';
            echo "<p>Ocak {$furnace->id}: {$furnace->name} - <span style='color: {$statusColor}'><strong>{$furnace->status}</strong></span> (Set: {$furnace->furnace_set_id})</p>";
        }
        
        echo "<h2>Set Analysis:</h2>";
        $sets = [1, 2, 3];
        foreach ($sets as $setId) {
            $activeCount = Furnace::where('furnace_set_id', $setId)->where('status', 'active')->count();
            echo "<p>Set {$setId}: <strong>{$activeCount}</strong> active furnace(s)</p>";
        }
        
        echo "<p><a href='/furnaces'>← Back to Furnaces</a></p>";
    }
}
