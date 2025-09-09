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
        
        echo "<h2>Set Rule Test:</h2>";
        echo "<p><strong>Test 1:</strong> Try to activate Furnace 2 (same set as Furnace 1)</p>";
        $furnace2 = Furnace::find(2);
        $result = $furnace2->changeStatusWithSetRule('active');
        echo "<p>Result: " . json_encode($result) . "</p>";
        
        echo "<h2>Final Status After Test:</h2>";
        foreach (Furnace::all() as $furnace) {
            $statusColor = $furnace->status === 'active' ? 'green' : 'orange';
            echo "<p>Ocak {$furnace->id}: {$furnace->name} - <span style='color: {$statusColor}'><strong>{$furnace->status}</strong></span> (Set: {$furnace->furnace_set_id})</p>";
        }
        
        echo "<p><a href='/furnaces'>← Back to Furnaces</a></p>";
    }
    
    public function activeCastings()
    {
        echo "<h1>Active Castings Debug</h1>";
        
        $activeCastings = \App\Models\Casting::where('status', 'active')->with('furnace.furnaceSet')->get();
        
        echo "<h2>Aktif Döküm Sayısı: " . $activeCastings->count() . "</h2>";
        
        if ($activeCastings->count() > 0) {
            echo "<h3>Aktif Dökümler:</h3>";
            foreach ($activeCastings as $casting) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<p><strong>Döküm ID:</strong> {$casting->id}</p>";
                echo "<p><strong>Döküm No:</strong> {$casting->casting_number}</p>";
                echo "<p><strong>Ocak ID:</strong> {$casting->furnace_id}</p>";
                echo "<p><strong>Ocak:</strong> {$casting->furnace->furnaceSet->name} - {$casting->furnace->name}</p>";
                echo "<p><strong>Durum:</strong> {$casting->status}</p>";
                echo "<p><strong>Başlangıç:</strong> {$casting->started_at}</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Aktif döküm bulunmuyor.</p>";
        }
        
        echo "<h3>Ocak Bazında Döküm Kontrolü:</h3>";
        $furnaces = \App\Models\Furnace::all();
        foreach ($furnaces as $furnace) {
            $hasActive = \App\Models\Casting::hasActiveCastingInFurnace($furnace->id);
            $statusColor = $hasActive ? 'red' : 'green';
            echo "<p>Ocak {$furnace->id} ({$furnace->name}): <span style='color: {$statusColor}'>" . ($hasActive ? 'AKTİF DÖKÜM VAR' : 'Aktif döküm yok') . "</span></p>";
        }
        
        echo "<p><a href='/castings'>← Back to Castings</a></p>";
    }
}
