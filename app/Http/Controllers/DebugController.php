<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use App\Models\FurnaceSet;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function furnaceStatus()
    {
        echo "<h1>Furnace Status Debug</h1>";
        
        echo "<h2>Database Status:</h2>";
        foreach (Furnace::all() as $furnace) {
            echo "<p>Ocak {$furnace->id}: {$furnace->name} - <strong>{$furnace->status}</strong> (Set: {$furnace->furnace_set_id})</p>";
        }
        
        echo "<h2>Status Counts:</h2>";
        $counts = [
            'active' => Furnace::where('status', 'active')->count(),
            'idle' => Furnace::where('status', 'idle')->count(),
            'maintenance' => Furnace::where('status', 'maintenance')->count(),
            'inactive' => Furnace::where('status', 'inactive')->count(),
        ];
        
        foreach ($counts as $status => $count) {
            echo "<p><strong>{$status}:</strong> {$count}</p>";
        }
        
        echo "<h2>Set Analysis:</h2>";
        $sets = FurnaceSet::with('furnaces')->get();
        foreach ($sets as $set) {
            echo "<h3>Set {$set->id}: {$set->name}</h3>";
            $activeInSet = $set->furnaces->where('status', 'active')->count();
            echo "<p>Active furnaces in this set: <strong>{$activeInSet}</strong></p>";
            foreach ($set->furnaces as $furnace) {
                echo "<p>- Ocak {$furnace->id}: {$furnace->name} - <strong>{$furnace->status}</strong></p>";
            }
        }
    }
}
