<?php

namespace App\Http\Controllers;

use App\Models\Casting;
use App\Models\Furnace;
use App\Models\FurnaceSet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CastingController extends Controller
{
    /**
     * Döküm listesi
     * Tüm dökümleri listeler, filtreleme ve arama özelliği sağlar
     */
    public function index(Request $request)
    {
        $query = Casting::with(['furnace.furnaceSet', 'samples']);
        
        // Filtreleme
        if ($request->filled('furnace_id')) {
            $query->where('furnace_id', $request->furnace_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('casting_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('casting_date', '<=', $request->date_to);
        }
        
        // Sıralama
        $castings = $query->orderBy('casting_date', 'desc')->paginate(15);
        
        // Dropdown verileri
        $furnaces = Furnace::with('furnaceSet')->get();
        $statuses = [
            'active' => 'Aktif',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal Edildi'
        ];
        $shifts = ['Gündüz', 'Gece'];
        
        return view('castings.index', compact('castings', 'furnaces', 'statuses', 'shifts'));
    }
    
    /**
     * Yeni döküm oluşturma formu
     */
    public function create(Request $request)
    {
        // Debug: Method'a ulaşıldığını kontrol et
        \Log::info('CastingController@create method called');
        
        // Aktif ocakları getir
        $activeFurnaces = Furnace::where('status', 'active')->with('furnaceSet')->get();
        
        // Eğer furnace_id parametresi varsa, o ocağı seç
        $selectedFurnace = null;
        if ($request->filled('furnace_id')) {
            $selectedFurnace = Furnace::find($request->furnace_id);
            
            // Seçilen ocakta aktif döküm var mı kontrol et
            if ($selectedFurnace && Casting::hasActiveCastingInFurnace($selectedFurnace->id)) {
                return redirect()->back()->with('error', "Bu ocakta zaten aktif bir döküm bulunuyor. Önce mevcut dökümü tamamlayın.");
            }
        }
        
        // Tüm ocakların aktif döküm durumunu kontrol et
        $furnacesWithActiveCastings = [];
        foreach ($activeFurnaces as $furnace) {
            if (Casting::hasActiveCastingInFurnace($furnace->id)) {
                $furnacesWithActiveCastings[] = $furnace->name;
            }
        }
        
        // Bugünkü istatistikler
        $today = now()->toDateString();
        $todayStats = [
            'total_castings' => Casting::whereDate('casting_date', $today)->count(),
            'active_castings' => Casting::where('status', 'active')->count(),
            'total_samples' => \App\Models\Sample::whereHas('casting', function($query) use ($today) {
                $query->whereDate('casting_date', $today);
            })->count(),
            'active_furnaces' => Furnace::where('status', 'active')->count()
        ];
        
        return view('castings.create', compact('activeFurnaces', 'selectedFurnace', 'todayStats', 'furnacesWithActiveCastings'));
    }
    
    /**
     * Yeni döküm kaydet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'furnace_id' => 'required|exists:furnaces,id',
            'casting_number' => 'required|string|max:50',
            'shift' => 'required|in:Gündüz,Gece',
            'casting_date' => 'required|date',
            'casting_time' => 'required',
            'operator_name' => 'required|string|max:100',
            'target_temperature' => 'nullable|numeric|min:0|max:3000',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        // Furnace bilgisini al
        $furnace = Furnace::findOrFail($validated['furnace_id']);
        
        // Aktif döküm kontrolü - Aynı ocakta aktif döküm var mı?
        if (Casting::hasActiveCastingInFurnace($furnace->id)) {
            return back()
                ->withErrors(['furnace_id' => 'Bu ocakta zaten aktif bir döküm bulunuyor. Önce mevcut dökümü tamamlayın.'])
                ->withInput();
        }
        
        // Eğer döküm numarası otomatik oluşturulacaksa
        if (empty($validated['casting_number']) || strpos($validated['casting_number'], 'HATA') !== false) {
            $castingCount = $furnace->castings()->count();
            $nextCastingNumber = $castingCount + 1;
            $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
            $validated['casting_number'] = $furnaceName . '-' . $nextCastingNumber . '.DÖKÜM';
        }
        
        // Unique kontrolü
        if (Casting::where('casting_number', $validated['casting_number'])->exists()) {
            return back()
                ->withErrors(['casting_number' => 'Bu döküm numarası zaten kullanılmış!'])
                ->withInput();
        }
        
        // Tarih ve saati birleştir
        $castingDateTime = Carbon::parse($validated['casting_date'] . ' ' . $validated['casting_time']);
        
        $casting = Casting::create([
            'furnace_id' => $validated['furnace_id'],
            'casting_number' => $validated['casting_number'],
            'shift' => $validated['shift'],
            'casting_date' => $castingDateTime,
            'operator_name' => $validated['operator_name'],
            'target_temperature' => $validated['target_temperature'],
            'notes' => $validated['notes'],
            'status' => 'active',
            'started_at' => now()
        ]);
        
        return redirect()
            ->route('castings.show', $casting)
            ->with('success', "Döküm başarıyla başlatıldı! Döküm No: {$casting->casting_number}");
    }
    
    /**
     * Döküm detayları
     */
    public function show(Casting $casting)
    {
        $casting->load(['furnace.furnaceSet', 'samples.qualityStandard']);
        
        // İstatistikler
        $stats = [
            'total_samples' => $casting->samples->count(),
            'approved_samples' => $casting->samples->where('quality_status', 'approved')->count(),
            'pending_samples' => $casting->samples->where('quality_status', 'pending')->count(),
            'rejected_samples' => $casting->samples->where('quality_status', 'rejected')->count(),
            'duration' => $casting->completed_at 
                ? $casting->started_at->diffInMinutes($casting->completed_at) 
                : $casting->started_at->diffInMinutes(now())
        ];
        
        return view('castings.show', compact('casting', 'stats'));
    }
    
    /**
     * Döküm düzenleme formu
     */
    public function edit(Casting $casting)
    {
        $furnaces = Furnace::with('furnaceSet')->get();
        
        return view('castings.edit', compact('casting', 'furnaces'));
    }
    
    /**
     * Döküm güncelle
     */
    public function update(Request $request, Casting $casting)
    {
        $validated = $request->validate([
            'furnace_id' => 'required|exists:furnaces,id',
            'casting_number' => 'required|string|max:50|unique:castings,casting_number,' . $casting->id,
            'shift' => 'required|in:Gündüz,Gece',
            'casting_date' => 'required|date',
            'casting_time' => 'required',
            'operator_name' => 'required|string|max:100',
            'target_temperature' => 'nullable|numeric|min:0|max:3000',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        // Tarih ve saati birleştir
        $castingDateTime = Carbon::parse($validated['casting_date'] . ' ' . $validated['casting_time']);
        
        $casting->update([
            'furnace_id' => $validated['furnace_id'],
            'casting_number' => $validated['casting_number'],
            'shift' => $validated['shift'],
            'casting_date' => $castingDateTime,
            'operator_name' => $validated['operator_name'],
            'target_temperature' => $validated['target_temperature'],
            'notes' => $validated['notes']
        ]);
        
        return redirect()
            ->route('castings.show', $casting)
            ->with('success', 'Döküm bilgileri güncellendi!');
    }
    
    /**
     * Döküm sil
     */
    public function destroy(Casting $casting)
    {
        // Sadece aktif olmayan dökümleri sil
        if ($casting->status === 'active') {
            return back()->with('error', 'Aktif döküm silinemez!');
        }
        
        $casting->delete();
        
        return redirect()
            ->route('castings.index')
            ->with('success', 'Döküm silindi!');
    }
    
    /**
     * Döküm tamamla
     */
    public function complete(Request $request, Casting $casting)
    {
        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
            'final_temperature' => 'nullable|numeric|min:0|max:3000'
        ]);
        
        $casting->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $validated['completion_notes'],
            'final_temperature' => $validated['final_temperature']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Döküm tamamlandı!'
        ]);
    }
    
    /**
     * Döküm iptal et
     */
    public function cancel(Request $request, Casting $casting)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000'
        ]);
        
        $casting->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Döküm iptal edildi!'
        ]);
    }
}

