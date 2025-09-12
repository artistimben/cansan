<?php

namespace App\Http\Controllers;

use App\Models\Furnace;
use App\Models\FurnaceSet;
use App\Models\Casting;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Ocak Yönetimi Controller'ı
 * Ocak durumu, döküm başlatma/durdurma işlemleri
 */
class FurnaceController extends Controller
{
    /**
     * Tüm ocakları listele
     */
    public function index(Request $request)
    {
        $furnaceSets = FurnaceSet::with(['furnaces' => function($query) {
            $query->with(['castings' => function($subQuery) {
                $subQuery->latest()->take(5);
            }]);
        }])->get();
        
        $furnaces = Furnace::with(['furnaceSet', 'castings' => function($query) {
            $query->latest()->take(5);
        }])->get();
        
        // Durum istatistikleri
        $statusCounts = [
            'active' => $furnaces->where('status', 'active')->count(),
            'idle' => $furnaces->where('status', 'idle')->count(),
            'maintenance' => $furnaces->where('status', 'maintenance')->count(),
            'inactive' => $furnaces->where('status', 'inactive')->count(),
        ];
        
        // Tüm dökümler için filtreleme (iptal edilenler hariç)
        $query = Casting::with(['furnace.furnaceSet', 'samples', 'adjustments'])
            ->where('status', '!=', 'cancelled'); // İptal edilen dökümleri filtrele
        
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
        
        // Önce aktif dökümler, sonra tamamlanmış dökümler
        // Aynı ocağın dökümleri sıralı olacak şekilde
        $allCastings = $query->orderBy('status', 'asc') // active önce gelir
            ->orderBy('furnace_id', 'asc') // Aynı ocağın dökümleri birlikte
            ->orderBy('casting_number', 'asc') // Döküm numarasına göre sırala (OCAK1-1, OCAK1-2, OCAK1-3)
            ->get();
        
        // Aktif ocakları al (set kuralı ile - her setten sadece bir aktif ocak)
        $activeFurnaces = $furnaces->where('status', 'active')
            ->groupBy('furnace_set_id')
            ->map(function($setFurnaces) {
                return $setFurnaces->first(); // Her setten sadece ilk ocağı al
            })
            ->values();
        
        // Aktif ocakları CHARGING olarak döküm listesinin başına ekle
        $chargingItems = collect();
        foreach ($activeFurnaces as $furnace) {
            // Bu ocağın aktif dökümü var mı kontrol et
            $hasActiveCasting = $allCastings->where('furnace_id', $furnace->id)->where('status', 'active')->count() > 0;
            
            // Eğer aktif dökümü yoksa, CHARGING item'ı ekle
            if (!$hasActiveCasting) {
                $chargingItem = (object) [
                    'id' => 'charging_' . $furnace->id,
                    'furnace_id' => $furnace->id,
                    'furnace' => $furnace,
                    'status' => 'charging',
                    'casting_number' => 'CHARGING',
                    'started_at' => now(),
                    'completed_at' => null,
                    'final_temperature' => null,
                    'target_temperature' => $furnace->current_temperature ?? 1600,
                    'operator_name' => 'Sistem',
                    'notes' => 'Yeni döküm için hazır',
                    'samples' => collect(),
                    'adjustments' => collect(),
                    'is_charging' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $chargingItems->push($chargingItem);
            }
        }
        
        // CHARGING item'larını döküm listesinin başına ekle
        $allCastings = $chargingItems->concat($allCastings);
        
        // Pagination için manuel olarak böl
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $allCastings->slice($offset, $perPage)->values();
        
        // Pagination objesi oluştur
        $allCastings = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $allCastings->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
        
        // Her ocağın döküm sayısını hesapla (iptal edilenler hariç)
        $furnaceCastingCounts = Casting::selectRaw('furnace_id, COUNT(*) as casting_count')
            ->where('status', '!=', 'cancelled')
            ->groupBy('furnace_id')
            ->pluck('casting_count', 'furnace_id');
        
        // Her döküm için o ocağın o dökümdeki sırasını hesapla (iptal edilenler hariç)
        $castingFurnaceSequence = [];
        foreach ($allCastings as $casting) {
            $furnaceId = $casting->furnace_id;
            
            // CHARGING item'ları için özel işlem
            if (isset($casting->is_charging) && $casting->is_charging) {
                // CHARGING item'ları için ocağın mevcut döküm sayısını al
                $sequenceNumber = $furnaceCastingCounts[$furnaceId] ?? 0;
            } else {
                // Normal dökümler için created_at kullan
                $castingDate = $casting->created_at;
                
                // Bu ocağın bu dökümden önceki döküm sayısını hesapla (iptal edilenler hariç)
                $sequenceNumber = Casting::where('furnace_id', $furnaceId)
                    ->where('status', '!=', 'cancelled')
                    ->where('created_at', '<=', $castingDate)
                    ->count();
            }
            
            $castingFurnaceSequence[$casting->id] = $sequenceNumber;
        }
        
        // Toplam döküm sayısını hesapla (iptal edilenler hariç)
        $totalCastings = Casting::where('status', '!=', 'cancelled')->count();
        
        return view('furnaces.index', compact('furnaceSets', 'furnaces', 'statusCounts', 'allCastings', 'totalCastings', 'furnaceCastingCounts', 'castingFurnaceSequence'));
    }
    
    /**
     * Belirli bir ocağın detaylarını göster
     */
    public function show(Furnace $furnace)
    {
        $furnace->load(['furnaceSet', 'castings.samples']);
        
        // Son 30 günlük performans
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $recentCastings = $furnace->castings()
            ->where('casting_date', '>=', $thirtyDaysAgo)
            ->with('samples')
            ->orderBy('casting_date', 'desc')
            ->get();
        
        // Toplam döküm sayısı (tüm zamanlar)
        $totalCastings = $furnace->castings()->count();
        $nextCastingNumber = $totalCastings + 1;
        
        // Bakım takibi için veriler
        $maxCastingsBeforeMaintenance = $furnace->maintenance_interval_days ?? 100; // Varsayılan 100 döküm
        $castingsSinceLastMaintenance = $furnace->castings()
            ->where('casting_date', '>', $furnace->last_maintenance_date ?? '1900-01-01')
            ->count();
        $maintenanceProgress = ($castingsSinceLastMaintenance / $maxCastingsBeforeMaintenance) * 100;
        $needsMaintenance = $castingsSinceLastMaintenance >= $maxCastingsBeforeMaintenance;
        
        // Performans istatistikleri
        $stats = [
            'total_castings' => $totalCastings,
            'next_casting_number' => $nextCastingNumber,
            'castings_since_maintenance' => $castingsSinceLastMaintenance,
            'max_castings_before_maintenance' => $maxCastingsBeforeMaintenance,
            'maintenance_progress' => min($maintenanceProgress, 100),
            'needs_maintenance' => $needsMaintenance,
            'total_castings_30d' => $recentCastings->count(),
            'total_samples_30d' => $recentCastings->sum(function($casting) {
                return $casting->samples->count();
            }),
            'approved_samples_30d' => $recentCastings->sum(function($casting) {
                return $casting->samples->where('quality_status', 'approved')->count();
            }),
            'rejected_samples_30d' => $recentCastings->sum(function($casting) {
                return $casting->samples->where('quality_status', 'rejected')->count();
            }),
            'avg_samples_per_casting' => $recentCastings->count() > 0 
                ? round($recentCastings->sum(function($casting) { return $casting->samples->count(); }) / $recentCastings->count(), 1)
                : 0
        ];
        
        // Kalite oranı
        $totalSamples = $stats['approved_samples_30d'] + $stats['rejected_samples_30d'];
        $stats['quality_rate'] = $totalSamples > 0 
            ? round(($stats['approved_samples_30d'] / $totalSamples) * 100, 1)
            : 0;
        
        // Aktif döküm
        $activeCasting = $furnace->getActiveCasting();
        
        return view('furnaces.show', compact('furnace', 'recentCastings', 'stats', 'activeCasting'));
    }
    
    /**
     * Ocak durumunu değiştir (set kuralları ile)
     */
    public function toggleStatus(Request $request, Furnace $furnace)
    {
        $request->validate([
            'status' => 'required|in:active,idle,maintenance,inactive',
            'current_temperature' => 'nullable|numeric|min:0|max:2000',
            'status_notes' => 'nullable|string|max:500'
        ]);
        
        // Set kuralına göre durum değiştir
        $result = $furnace->changeStatusWithSetRule($request->status);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        
        // Sıcaklık güncelleme
        if ($request->filled('current_temperature')) {
            $furnace->update([
                'current_temperature' => $request->current_temperature
            ]);
            
            // Sıcaklık geçmişine kaydet
            $furnace->addTemperatureLog(
                $request->current_temperature,
                $request->status === 'active' ? 'working' : 'shutdown',
                $request->status_notes ?? "Durum değişikliği: {$request->status}",
                'Sistem'
            );
        }
            
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'affected_furnaces' => $result['affected_furnaces'],
            'furnace' => $furnace->fresh()
        ]);
    }
    
    /**
     * Yeni döküm başlat
     */
    public function startCasting(Request $request, Furnace $furnace)
    {
        $request->validate([
            'shift' => 'required|string|max:10',
            'temperature' => 'nullable|numeric|min:0|max:2000',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        // Ocak aktif mi kontrol et
        if ($furnace->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece aktif ocaklarda döküm başlatılabilir'
            ], 400);
        }

        // Aktif döküm kontrolü
        if (Casting::hasActiveCastingInFurnace($furnace->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu ocakta zaten aktif bir döküm bulunuyor. Önce mevcut dökümü tamamlayın.'
            ], 400);
        }

        // Aynı sette başka aktif ocak var mı kontrol et
        $activeFurnaceInSameSet = Furnace::where('furnace_set_id', $furnace->furnace_set_id)
            ->where('id', '!=', $furnace->id)
            ->where('status', 'active')
            ->first();

        if ($activeFurnaceInSameSet) {
            return response()->json([
                'success' => false,
                'message' => "Aynı sette zaten aktif bir ocak bulunuyor: {$activeFurnaceInSameSet->name}. Sadece set başına bir ocak çalışabilir."
            ], 400);
        }
        
        // Döküm numarası formatı: "OCAK3-3.DÖKÜM"
        $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
        
        // Bu ocak için en son döküm numarasını bul
        $lastCasting = $furnace->castings()
            ->where('casting_number', 'like', $furnaceName . '-%.DÖKÜM')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastCasting) {
            // Son döküm numarasından bir sonraki numarayı al
            preg_match('/' . preg_quote($furnaceName) . '-(\d+)\.DÖKÜM/', $lastCasting->casting_number, $matches);
            $nextCastingNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            // İlk döküm
            $nextCastingNumber = 1;
        }
        
        $castingNumber = $furnaceName . '-' . $nextCastingNumber . '.DÖKÜM';
        
        // Aynı numara varsa tekrar dene
        $counter = 1;
        while (Casting::where('casting_number', $castingNumber)->exists()) {
            $castingNumber = $furnaceName . '-' . ($nextCastingNumber + $counter) . '.DÖKÜM';
            $counter++;
        }
        
        // Yeni döküm oluştur
        $casting = $furnace->castings()->create([
            'casting_number' => $castingNumber,
            'casting_date' => now(),
            'shift' => $request->shift,
            'status' => 'active',
            'started_at' => now(),
            'notes' => $request->notes,
            'target_temperature' => $request->temperature
        ]);
        
        // Sıcaklık kaydı
        if ($request->filled('temperature')) {
            $furnace->addTemperatureLog(
                $request->temperature, 
                'working', 
                "Döküm {$castingNumber} başlatıldı", 
                'Sistem'
            );
        }
        
        // Ocak döküm sayısını hesapla (iptal edilenler hariç)
        $castingCount = $furnace->castings()->where('status', '!=', 'cancelled')->count();
        
        return response()->json([
            'success' => true,
            'message' => "Yeni döküm başlatıldı: {$castingNumber}",
            'casting' => $casting,
            'casting_number' => $castingNumber,
            'casting_count' => $castingCount,
            'next_number' => $nextCastingNumber,
            'furnace_name' => $furnace->name,
            'furnace_set' => $furnace->furnaceSet->name
        ]);
    }
    
    /**
     * Dökümü tamamla
     */
    public function completeCasting(Request $request, Furnace $furnace, Casting $casting)
    {
        // Döküm furnace'a ait mi kontrol et
        if ($casting->furnace_id !== $furnace->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu döküm bu ocağa ait değil'
            ], 400);
        }

        // Döküm aktif mi kontrol et
        if ($casting->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece aktif dökümler tamamlanabilir'
            ], 400);
        }

        // Minimum prova sayısı kontrolü
        $sampleCount = $casting->samples()->count();
        if ($sampleCount < 1) {
            return response()->json([
                'success' => false,
                'message' => "Döküm tamamlanamaz. En az 1 prova gerekli (Şu an: {$sampleCount} prova)"
            ], 400);
        }

        // Döküm tamamlama
        $request->validate([
            'final_temperature' => 'required|numeric|min:0|max:2000',
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        // Dökümü tamamla
        $casting->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_temperature' => $request->final_temperature,
            'completion_notes' => $request->completion_notes
        ]);

        // Ocağın sıcaklık geçmişine kaydet
        if ($request->filled('final_temperature')) {
            $furnace->addTemperatureLog(
                $request->final_temperature, 
                'shutdown', 
                "Döküm {$casting->casting_number} tamamlandı", 
                'Sistem'
            );
        }

        // Otomatik yeni döküm açma
        $newCasting = null;
        if ($request->get('auto_start_next', true)) {
            try {
                \Log::info("Otomatik yeni döküm başlatılıyor...");
                
                // Yeni döküm numarası hesapla
                $nextCastingNumber = $this->getNextCastingNumber($furnace);
                \Log::info("Sonraki döküm numarası: " . $nextCastingNumber);
                
                // Döküm numarası formatı: "OCAK1-2.DÖKÜM"
                $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
                $castingNumber = $furnaceName . '-' . $nextCastingNumber . '.DÖKÜM';
                \Log::info("Döküm numarası: " . $castingNumber);
                
                // Yeni döküm oluştur
                $newCasting = Casting::create([
                    'furnace_id' => $furnace->id,
                    'casting_number' => $castingNumber,
                    'casting_date' => now(),
                    'shift' => $request->get('shift', 'A'),
                    'operator_name' => $request->get('operator_name', 'Sistem'),
                    'target_temperature' => $request->get('target_temperature', 1600),
                    'status' => 'active',
                    'started_at' => now(),
                    'notes' => 'Otomatik başlatılan döküm'
                ]);
                
                \Log::info("Yeni döküm oluşturuldu: " . $newCasting->id);
                
                // Ocağın döküm sayacını artır
                $furnace->incrementCastingCount();
                \Log::info("Ocak döküm sayacı artırıldı");
                
            } catch (\Exception $e) {
                \Log::error("Yeni döküm oluşturulurken hata: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
            }
        } else {
            \Log::info("Otomatik yeni döküm başlatma devre dışı");
        }

        return response()->json([
            'success' => true,
            'message' => "Döküm {$casting->casting_number} başarıyla tamamlandı" . ($newCasting ? " ve yeni döküm {$newCasting->casting_number} başlatıldı" : ""),
            'casting' => $casting->fresh(),
            'sample_count' => $sampleCount,
            'new_casting' => $newCasting,
            'furnace_info' => [
                'id' => $furnace->id,
                'name' => $furnace->name,
                'casting_count' => $furnace->casting_count,
                'next_casting_number' => $newCasting ? $newCasting->casting_number : null
            ]
        ]);
    }
    
    
    /**
     * Ocak performans raporu
     */
    public function performanceReport(Furnace $furnace, Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        $castings = $furnace->castings()
            ->whereBetween('casting_date', [$startDate, $endDate])
            ->with('samples.adjustments')
            ->get();
        
        // Günlük döküm sayıları
        $dailyCounts = $castings->groupBy(function($casting) {
            return $casting->casting_date->format('Y-m-d');
        })->map(function($group) {
            return $group->count();
        });
        
        // Vardiya bazında dağılım
        $shiftDistribution = $castings->groupBy('shift')->map(function($group) {
            return $group->count();
        });
        
        // Kalite istatistikleri
        $allSamples = $castings->flatMap(function($casting) {
            return $casting->samples;
        });
        
        $qualityStats = [
            'total_samples' => $allSamples->count(),
            'approved' => $allSamples->where('quality_status', 'approved')->count(),
            'rejected' => $allSamples->where('quality_status', 'rejected')->count(),
            'pending' => $allSamples->where('quality_status', 'pending')->count(),
            'needs_adjustment' => $allSamples->where('quality_status', 'needs_adjustment')->count()
        ];
        
        // Ham madde ekleme istatistikleri
        $allAdjustments = $allSamples->flatMap(function($sample) {
            return $sample->adjustments;
        });
        
        $adjustmentStats = [
            'total_adjustments' => $allAdjustments->count(),
            'successful_adjustments' => $allAdjustments->where('is_successful', true)->count(),
            'materials_used' => $allAdjustments->groupBy('material_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount_kg')
                ];
            })
        ];
        
        return response()->json([
            'furnace' => $furnace,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'casting_stats' => [
                'total_castings' => $castings->count(),
                'daily_counts' => $dailyCounts,
                'shift_distribution' => $shiftDistribution
            ],
            'quality_stats' => $qualityStats,
            'adjustment_stats' => $adjustmentStats
        ]);
    }
    
    /**
     * Ocak durumu güncelle (operasyonel/bakım)
     */
    public function updateOperationalStatus(Request $request, Furnace $furnace)
    {
        $request->validate([
            'is_operational' => 'required|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $furnace->update([
            'is_operational' => $request->is_operational,
            'description' => $request->notes
        ]);
        
        $message = $request->is_operational 
            ? $furnace->name . ' operasyonel duruma getirildi'
            : $furnace->name . ' bakım durumuna alındı';
            
        return response()->json([
            'success' => true,
            'message' => $message,
            'furnace' => $furnace->fresh()
        ]);
    }
    
    /**
     * Yeni ocak oluşturma formu
     */
    public function create()
    {
        $furnaceSets = FurnaceSet::all();
        
        return view('furnaces.create', compact('furnaceSets'));
    }
    
    /**
     * Yeni ocak kaydet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'furnace_set_id' => 'required|exists:furnace_sets,id',
            'name' => 'required|string|max:100|unique:furnaces,name',
            'description' => 'nullable|string|max:500',
            'capacity' => 'nullable|numeric|min:0|max:1000',
            'max_temperature' => 'nullable|numeric|min:0|max:3000',
            'fuel_type' => 'nullable|in:natural_gas,electricity,coal,oil,mixed',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,idle,maintenance,inactive',
            'current_temperature' => 'nullable|numeric|min:0|max:3000',
            'last_maintenance_date' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1|max:365'
        ]);
        
        $furnace = Furnace::create($validated + [
            'status_updated_at' => now()
        ]);
        
        return redirect()
            ->route('furnaces.show', $furnace)
            ->with('success', 'Ocak başarıyla oluşturuldu!');
    }
    
    /**
     * Ocak düzenleme formu
     */
    public function edit(Furnace $furnace)
    {
        $furnaceSets = FurnaceSet::all();
        
        return view('furnaces.edit', compact('furnace', 'furnaceSets'));
    }
    
    /**
     * Ocak güncelle
     */
    public function update(Request $request, Furnace $furnace)
    {
        $validated = $request->validate([
            'furnace_set_id' => 'required|exists:furnace_sets,id',
            'name' => 'required|string|max:100|unique:furnaces,name,' . $furnace->id,
            'description' => 'nullable|string|max:500',
            'capacity' => 'nullable|numeric|min:0|max:1000',
            'max_temperature' => 'nullable|numeric|min:0|max:3000',
            'fuel_type' => 'nullable|in:natural_gas,electricity,coal,oil,mixed',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,idle,maintenance,inactive',
            'current_temperature' => 'nullable|numeric|min:0|max:3000',
            'last_maintenance_date' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1|max:365'
        ]);
        
        // Durum değişikliği varsa timestamp güncelle
        if ($furnace->status !== $validated['status']) {
            $validated['status_updated_at'] = now();
        }
        
        $furnace->update($validated);
        
        return redirect()
            ->route('furnaces.show', $furnace)
            ->with('success', 'Ocak bilgileri güncellendi!');
    }
    
    /**
     * Ocak sil
     */
    public function destroy(Furnace $furnace)
    {
        // Aktif dökümleri kontrol et
        if ($furnace->castings()->where('status', 'active')->exists()) {
            return back()->with('error', 'Aktif dökümü olan ocak silinemez!');
        }
        
        $furnace->delete();
        
        return redirect()
            ->route('furnaces.index')
            ->with('success', 'Ocak silindi!');
    }
    
    /**
     * Ocak durumu güncelle
     */
    public function updateStatus(Request $request, Furnace $furnace)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,idle,maintenance,inactive',
            'status_notes' => 'nullable|string|max:1000',
            'current_temperature' => 'nullable|numeric|min:0|max:3000'
        ]);
        
        // Set kuralına göre durum değiştir
        $result = $furnace->changeStatusWithSetRule($validated['status']);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        
        // Sıcaklık güncelleme
        if (isset($validated['current_temperature'])) {
            $furnace->update([
                'current_temperature' => $validated['current_temperature']
            ]);
            
            // Sıcaklık geçmişine kaydet
            $furnace->addTemperatureLog(
                $validated['current_temperature'],
                $validated['status'] === 'active' ? 'working' : 'shutdown',
                $validated['status_notes'] ?? "Durum değişikliği: {$validated['status']}",
                'Sistem'
            );
        }
        
        $statusNames = [
            'active' => 'Aktif',
            'idle' => 'Beklemede',
            'maintenance' => 'Bakımda',
            'inactive' => 'Kapalı'
        ];
        
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'affected_furnaces' => $result['affected_furnaces']
        ]);
    }
    
    /**
     * Ocak listesi export
     */
    public function export()
    {
        $furnaces = Furnace::with('furnaceSet')->get();
        
        // Excel export için gerekli kod buraya eklenecek
        // Şimdilik JSON olarak döndür
        return response()->json($furnaces);
    }
    
    /**
     * Ocak için bir sonraki döküm numarasını al
     */
    public function getNextCastingNumber(Furnace $furnace)
    {
        // Bu ocak için en son döküm numarasını bul (iptal edilenler hariç)
        $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
        $lastCasting = $furnace->castings()
            ->where('status', '!=', 'cancelled')
            ->where('casting_number', 'like', $furnaceName . '-%.DÖKÜM')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastCasting) {
            // Son döküm numarasından bir sonraki numarayı al
            preg_match('/' . preg_quote($furnaceName) . '-(\d+)\.DÖKÜM/', $lastCasting->casting_number, $matches);
            $nextCastingNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            // İlk döküm
            $nextCastingNumber = 1;
        }
        
        return $nextCastingNumber;
    }
    
    /**
     * API: Ocak için bir sonraki döküm numarasını al (JSON response)
     */
    public function getNextCastingNumberApi(Furnace $furnace)
    {
        // Bu ocak için en son döküm numarasını bul (iptal edilenler hariç)
        $furnaceName = strtoupper(str_replace(' ', '', $furnace->name));
        $lastCasting = $furnace->castings()
            ->where('status', '!=', 'cancelled')
            ->where('casting_number', 'like', $furnaceName . '-%.DÖKÜM')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastCasting) {
            // Son döküm numarasından bir sonraki numarayı al
            preg_match('/' . preg_quote($furnaceName) . '-(\d+)\.DÖKÜM/', $lastCasting->casting_number, $matches);
            $nextCastingNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            // İlk döküm
            $nextCastingNumber = 1;
        }
        
        $castingNumber = $furnaceName . '-' . $nextCastingNumber . '.DÖKÜM';
        $castingCount = $furnace->castings()->count();
        
        return response()->json([
            'success' => true,
            'casting_number' => $castingNumber,
            'casting_count' => $castingCount,
            'next_number' => $nextCastingNumber,
            'furnace_name' => $furnace->name,
            'furnace_set' => $furnace->furnaceSet->name
        ]);
    }
    
    /**
     * Aktif ocakları getir (API) - Set kuralı ile
     */
    public function getActiveFurnaces()
    {
        // Set başına sadece bir aktif ocak olabilir
        $furnaces = Furnace::where('status', 'active')
            ->with('furnaceSet')
            ->get()
            ->groupBy('furnace_set_id')
            ->map(function($setFurnaces) {
                // Her setten sadece ilk ocağı al
                return $setFurnaces->first();
            })
            ->values()
            ->map(function($furnace) {
                return [
                    'id' => $furnace->id,
                    'name' => $furnace->name,
                    'furnace_set_name' => $furnace->furnaceSet->name,
                    'casting_count' => $furnace->castings()->where('status', '!=', 'cancelled')->count()
                ];
            });
            
        return response()->json($furnaces);
    }
    
    /**
     * Dökümün ocağını güncelle
     */
    public function updateCastingFurnace(Request $request, Casting $casting)
    {
        $request->validate([
            'furnace_id' => 'required|exists:furnaces,id'
        ]);
        
        $furnace = Furnace::findOrFail($request->furnace_id);
        
        // Dökümün ocağını güncelle
        $casting->update([
            'furnace_id' => $furnace->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Döküm {$casting->casting_number} başarıyla {$furnace->name} ocağına taşındı",
            'casting' => $casting->fresh(),
            'furnace' => $furnace
        ]);
    }
    
    /**
     * Dökümü iptal et
     */
    public function cancelCasting(Request $request, Casting $casting)
    {
        // Sadece aktif dökümler iptal edilebilir
        if ($casting->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Sadece aktif dökümler iptal edilebilir'
            ], 400);
        }
        
        // Dökümü iptal et
        $casting->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'notes' => $casting->notes . ' [İptal edildi: ' . now()->format('d.m.Y H:i') . ']'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Döküm {$casting->casting_number} başarıyla iptal edildi",
            'casting' => $casting->fresh()
        ]);
    }

    /**
     * Sıcaklık kaydı ekle
     */
    public function addTemperatureLog(Request $request, Furnace $furnace)
    {
        $request->validate([
            'temperature' => 'required|numeric|min:0|max:2000',
            'log_type' => 'required|in:working,shutdown,maintenance,manual',
            'notes' => 'nullable|string|max:500',
            'recorded_by' => 'nullable|string|max:100'
        ]);

        $temperatureLog = $furnace->addTemperatureLog(
            $request->temperature,
            $request->log_type,
            $request->notes,
            $request->recorded_by
        );

        // Ocağın current_temperature'ını güncelle
        $furnace->update([
            'current_temperature' => $request->temperature
        ]);

        return response()->json([
            'success' => true,
            'message' => "Sıcaklık kaydı eklendi: {$request->temperature}°C",
            'temperature_log' => $temperatureLog,
            'furnace' => $furnace->fresh()
        ]);
    }

    /**
     * Sıcaklık geçmişi
     */
    public function temperatureHistory(Furnace $furnace)
    {
        $temperatureLogs = $furnace->temperatureLogs()
            ->latest('recorded_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'temperature_logs' => $temperatureLogs,
            'last_temperature' => $furnace->getLastRecordedTemperature()
        ]);
    }
    
    /**
     * Ocakta aktif döküm var mı kontrol et
     */
    public function checkActiveCasting(Furnace $furnace)
    {
        $hasActiveCasting = Casting::hasActiveCastingInFurnace($furnace->id);
        
        return response()->json([
            'has_active_casting' => $hasActiveCasting,
            'furnace_name' => $furnace->name,
            'furnace_set' => $furnace->furnaceSet->name
        ]);
    }

    /**
     * Aynı set içindeki ocakların durumlarını değiştir
     */
    public function swapFurnaceStatus(Request $request)
    {
        try {
            $furnace1Id = $request->input('furnace1_id');
            $furnace2Id = $request->input('furnace2_id');

            if (!$furnace1Id || !$furnace2Id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Her iki ocak ID\'si de gerekli.'
                ], 400);
            }

            $furnace1 = Furnace::findOrFail($furnace1Id);
            $furnace2 = Furnace::findOrFail($furnace2Id);

            // Aynı set içinde mi kontrol et
            if ($furnace1->furnace_set_id !== $furnace2->furnace_set_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sadece aynı set içindeki ocaklar değiştirilebilir.'
                ], 400);
            }

            // Set kuralı: Aynı sette sadece bir ocak aktif olabilir
            $activeFurnaceInSet = Furnace::where('furnace_set_id', $furnace1->furnace_set_id)
                ->where('status', 'active')
                ->whereNotIn('id', [$furnace1->id, $furnace2->id])
                ->first();

            if ($activeFurnaceInSet) {
                return response()->json([
                    'success' => false,
                    'message' => "Aynı sette zaten aktif bir ocak bulunuyor: {$activeFurnaceInSet->name}. Sadece set başına bir ocak çalışabilir."
                ], 400);
            }

            // Durumları değiştir
            $tempStatus = $furnace1->status;
            $tempTemperature = $furnace1->current_temperature;
            
            $furnace1->update([
                'status' => $furnace2->status,
                'current_temperature' => $furnace2->current_temperature
            ]);
            
            $furnace2->update([
                'status' => $tempStatus,
                'current_temperature' => $tempTemperature
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ocak durumları başarıyla değiştirildi.',
                'furnace1' => $furnace1->fresh(),
                'furnace2' => $furnace2->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Ocak durumu değiştirme hatası: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocak durumları değiştirilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ocak detay bilgilerini getir
     */
    public function getFurnaceInfo(Furnace $furnace)
    {
        try {
            // Son durum değişikliği tarihini bul
            $lastStatusChange = $furnace->statusLogs()
                ->latest('created_at')
                ->first();

            // Aktif olmayan süreyi hesapla (dakika/saat formatında)
            $inactiveDuration = null;
            if ($furnace->status !== 'active' && $lastStatusChange) {
                $diffInMinutes = $lastStatusChange->created_at->diffInMinutes(now());
                $hours = floor($diffInMinutes / 60);
                $minutes = $diffInMinutes % 60;
                
                if ($hours > 0) {
                    $inactiveDuration = $hours . ' saat ' . $minutes . ' dakika';
                } else {
                    $inactiveDuration = $minutes . ' dakika';
                }
            }

            // Tüm dökümleri getir (sadece bu ocağın dökümleri)
            $allCastings = $furnace->castings()
                ->with(['samples', 'adjustments'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Genel döküm sıralamasını al (tüm ocakların dökümleri)
            $allGlobalCastings = Casting::where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'asc')
                ->get();

            // Sadece bu ocağın dökümleri için genel sıra numarasını hesapla
            $castingsWithGlobalOrder = $allCastings->map(function($casting) use ($allGlobalCastings) {
                // Bu dökümün genel sıralamadaki pozisyonunu bul
                $globalPosition = $allGlobalCastings->search(function($globalCasting) use ($casting) {
                    return $globalCasting->id === $casting->id;
                });
                
                $casting->global_order = $globalPosition !== false ? $globalPosition + 1 : null;
                $casting->furnace_order = $casting->casting_number; // Ocağın kendi sırası
                return $casting;
            });

            // İstatistikler
            $stats = [
                'total_castings' => $furnace->castings()->count(),
                'active_castings' => $furnace->castings()->where('status', 'active')->count(),
                'completed_castings' => $furnace->castings()->where('status', 'completed')->count(),
                'cancelled_castings' => $furnace->castings()->where('status', 'cancelled')->count(),
                'average_temperature' => $furnace->temperatureLogs()->avg('temperature'),
                'last_casting_date' => $furnace->castings()->latest()->first()?->created_at,
            ];

            return response()->json([
                'success' => true,
                'furnace' => $furnace,
                'inactive_duration' => $inactiveDuration,
                'all_castings' => $castingsWithGlobalOrder,
                'stats' => $stats,
                'last_status_change' => $lastStatusChange
            ]);

        } catch (\Exception $e) {
            \Log::error('Ocak bilgi getirme hatası: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocak bilgileri alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
